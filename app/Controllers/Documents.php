<?php

namespace App\Controllers;

use App\Controllers\Traits\HrRbacTrait;
use App\Controllers\Traits\PmModuleTrait;
use App\Services\Hr\HrDocumentService;

class Documents extends BaseController
{
    use PmModuleTrait;
    use HrRbacTrait;

    protected ?string $workspaceRequired = null;

    private const TABLE = 'documents';

    private HrDocumentService $hrDocs;

    public function __construct()
    {
        $this->hrDocs = new HrDocumentService();
    }

    public function index()
    {
        $module = trim((string) ($this->request->getGet('module') ?? ''));
        $refId  = (int) ($this->request->getGet('ref_id') ?? 0);

        if ($module === 'employee' && $refId > 0 && ! $this->hrCan('employee.documents.view')) {
            return redirect()->back()->with('error', 'You do not have permission to view employee documents.');
        }

        if (! $this->pmTableExists(self::TABLE)) {
            return view('documents/panel', $this->viewData([
                'title'             => 'Documents',
                'migrationRequired' => true,
                'missingTable'      => self::TABLE,
                'module'            => $module,
                'refId'             => $refId,
                'documents'         => [],
                'embed'             => $refId > 0,
            ]));
        }

        $documents = $this->loadDocuments($module, $refId);
        $context   = $this->documentContext($module, $refId);

        return view('documents/panel', $this->viewData(array_merge($context, [
            'title'     => 'Documents',
            'module'    => $module,
            'refId'     => $refId,
            'documents' => $documents,
            'embed'     => $refId > 0 && $module !== '',
        ])));
    }

    public function store()
    {
        if (! $this->request->is('post')) {
            return redirect()->back();
        }

        if (! $this->pmTableExists(self::TABLE)) {
            return redirect()->back()->with('error', 'Documents module is not available. Run database migration first.');
        }

        $module = trim((string) $this->request->getPost('module'));
        $refId  = (int) $this->request->getPost('ref_id');

        if ($module === 'employee' && ! $this->hrCan('employee.documents.upload')) {
            return redirect()->back()->with('error', 'You do not have permission to upload employee documents.');
        }

        $rules = [
            'title' => 'required|max_length[255]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $file = $this->request->getFile('document');
        $filePath = null;
        if ($file && $file->isValid() && ! $file->hasMoved()) {
            $dir = WRITEPATH . 'uploads/documents';
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $newName  = $file->getRandomName();
            $file->move($dir, $newName);
            $filePath = 'documents/' . $newName;
        }

        $categoryId = (int) $this->request->getPost('category_id') ?: null;
        $docType    = esc($this->request->getPost('doc_type')) ?: 'general';
        if ($categoryId && $this->db->tableExists('hr_document_categories')) {
            $cat = $this->db->table('hr_document_categories')->where('id', $categoryId)->get()->getRowArray();
            if ($cat) {
                $docType = $cat['code'];
            }
        }

        $data = [
            'module'      => $module ?: null,
            'ref_id'      => $refId > 0 ? $refId : null,
            'title'       => esc($this->request->getPost('title')),
            'doc_type'    => $docType,
            'description' => esc($this->request->getPost('description')) ?: null,
            'file_path'   => $filePath,
            'doc_number'  => esc($this->request->getPost('doc_number')) ?: null,
            'issued_by'   => esc($this->request->getPost('issued_by')) ?: null,
            'doc_date'    => $this->request->getPost('doc_date') ?: null,
            'issue_date'  => $this->request->getPost('issue_date') ?: null,
            'expiry_date' => $this->request->getPost('expiry_date') ?: null,
            'uploaded_by' => $this->currentUser()['id'] ?: null,
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ];

        if ($this->db->fieldExists('category_id', self::TABLE) && $categoryId) {
            $data['category_id'] = $categoryId;
        }

        if ($this->db->fieldExists('facility_id', self::TABLE)) {
            $facilityId = (int) $this->request->getPost('facility_id') ?: null;
            if ($module === 'employee' && $refId > 0) {
                $emp = $this->db->table('employees')->select('facility_id')->where('id', $refId)->get()->getRowArray();
                $facilityId = (int) ($emp['facility_id'] ?? 0) ?: $facilityId;
            }
            if ($facilityId) {
                $this->assertFacilityAccess($facilityId);
            }
            $data['facility_id'] = $facilityId;
        }

        if ($this->db->fieldExists('status', self::TABLE)) {
            $data['status'] = $this->hrDocs->expiryStatus($data['expiry_date']);
        }

        $this->db->table(self::TABLE)->insert($data);
        $id = (int) $this->db->insertID();

        $this->logActivity('upload', 'documents', $id, 'Document uploaded: ' . $data['title']);

        return redirect()->to($this->documentRedirect($module, $refId))->with('success', 'Document uploaded.');
    }

    public function delete(int $id)
    {
        if (! $this->pmTableExists(self::TABLE)) {
            return redirect()->back()->with('error', 'Documents module is not available. Run database migration first.');
        }

        $doc = $this->pmFind(self::TABLE, $id, null, $this->db->fieldExists('facility_id', self::TABLE) ? 'facility_id' : null);
        if (! $doc) {
            return redirect()->back()->with('error', 'Document not found.');
        }

        if (($doc['module'] ?? '') === 'employee' && ! $this->hrCan('employee.documents.upload')) {
            return redirect()->back()->with('error', 'You do not have permission to delete employee documents.');
        }

        if (! empty($doc['file_path'])) {
            $path = WRITEPATH . 'uploads/' . ltrim(str_replace('uploads/', '', $doc['file_path']), '/');
            if (is_file($path)) {
                unlink($path);
            }
        }

        $this->db->table(self::TABLE)->where('id', $id)->delete();
        $this->logActivity('delete', 'documents', $id, 'Document deleted: ' . ($doc['title'] ?? ''));

        return redirect()->to($this->documentRedirect((string) ($doc['module'] ?? ''), (int) ($doc['ref_id'] ?? 0)))->with('success', 'Document removed.');
    }

    /** @return list<array<string, mixed>> */
    private function loadDocuments(string $module, int $refId): array
    {
        if ($module === 'employee' && $refId > 0) {
            return $this->hrDocs->forEmployee($refId);
        }

        $q = $this->db->table(self::TABLE . ' d')
            ->select('d.*, u.name AS uploaded_by_name')
            ->join('users u', 'u.id = d.uploaded_by', 'left')
            ->orderBy('d.created_at', 'DESC');

        if ($module !== '') {
            $q->where('d.module', $module);
        }
        if ($refId > 0) {
            $q->where('d.ref_id', $refId);
        }
        if ($this->db->fieldExists('facility_id', self::TABLE)) {
            $this->scopeFacilities($q, 'd.facility_id');
        }

        return $q->limit(100)->get()->getResultArray();
    }

    /** @return array<string, mixed> */
    private function documentContext(string $module, int $refId): array
    {
        $context = [
            'categories'  => [],
            'facilityId'  => null,
            'canUpload'   => true,
            'canDelete'   => true,
            'hrDocs'      => $this->hrDocs,
        ];

        if ($module === 'employee' && $refId > 0) {
            $emp = $this->db->table('employees')->where('id', $refId)->get()->getRowArray();
            $context['facilityId']  = $emp['facility_id'] ?? null;
            $context['categories']  = $this->hrDocs->categories($emp['company_id'] ?? null);
            $context['canUpload']   = $this->hrCan('employee.documents.upload');
            $context['canDelete']   = $context['canUpload'];
            $context['canView']     = $this->hrCan('employee.documents.view');
        }

        return $context;
    }

    private function documentRedirect(string $module, int $refId): string
    {
        if ($module === 'employee' && $refId > 0) {
            return base_url('employees/view/' . $refId . '?tab=documents');
        }
        if ($module !== '' && $refId > 0) {
            return base_url('documents?module=' . urlencode($module) . '&ref_id=' . $refId);
        }

        return base_url('documents');
    }
}
