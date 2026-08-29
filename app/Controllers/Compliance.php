<?php
namespace App\Controllers;

class Compliance extends BaseController
{
    protected ?string $workspaceRequired = 'fm';
    public function index()
    {
        $auditQ = $this->db->table('compliance_audits ca')
            ->select('ca.*, f.name as facility_name, u.name as created_by_name')
            ->join('facilities f', 'f.id=ca.facility_id', 'left')
            ->join('users u', 'u.id=ca.created_by', 'left');
        $this->scopeFacilities($auditQ, 'ca.facility_id');
        $audits = $auditQ->orderBy('ca.audit_date', 'DESC')->limit(20)->get()->getResultArray();

        $incQ = $this->db->table('incidents i')
            ->select('i.*, f.name as facility_name')
            ->join('facilities f', 'f.id=i.facility_id', 'left');
        $this->scopeFacilities($incQ, 'i.facility_id');
        $incidents = $incQ->orderBy('i.incident_date', 'DESC')->limit(10)->get()->getResultArray();
        $expiring  = $this->db->table('compliance_documents')->where('expiry_date <=', date('Y-m-d', strtotime('+30 days')))->where('expiry_date >=', date('Y-m-d'))->get()->getResultArray();

        return view('compliance/index', $this->viewData([
            'title'     => 'Compliance & Safety',
            'audits'    => $audits,
            'incidents' => $incidents,
            'expiring'  => $expiring,
        ]));
    }

    public function createAudit()
    {
        $facilities = $this->db->table('facilities')->where('status','active')->get()->getResultArray();
        return view('compliance/create_audit', $this->viewData(['title'=>'New Safety Audit','facilities'=>$facilities]));
    }

    public function unitInspections()
    {
        $facilityId = (int) ($this->request->getGet('facility_id') ?? 0);
        $status     = $this->request->getGet('status') ?? '';

        $q = $this->db->table('units u')
            ->select('u.id, u.unit_number, u.floor, u.status, u.tenant_name, f.id AS facility_id, f.name AS facility_name')
            ->join('facilities f', 'f.id = u.facility_id', 'left')
            ->where('u.deleted_at', null);
        $this->scopeFacilities($q, 'u.facility_id');
        if ($facilityId > 0) {
            $q->where('u.facility_id', $facilityId);
        }
        if ($status !== '') {
            $q->where('u.status', $status);
        }
        $units = $q->orderBy('f.name', 'ASC')->orderBy('u.unit_number', 'ASC')->limit(200)->get()->getResultArray();

        $facQ = $this->db->table('facilities')->where('status', 'active')->orderBy('name', 'ASC');
        $facilities = $this->scopeFacilities($facQ)->get()->getResultArray();

        return view('compliance/unit_inspections', $this->viewData([
            'title'          => 'Move-In / Move-Out Inspections',
            'units'          => $units,
            'facilities'     => $facilities,
            'filterFacility' => $facilityId,
            'filterStatus'   => $status,
        ]));
    }

    public function inspections()
    {
        $facilityId = $this->request->getGet('facility_id') ?? '';
        $status     = $this->request->getGet('status') ?? '';

        $q = $this->db->table('inspection_checklists ic')
            ->select('ic.*, f.name as facility_name, u.name as created_by_name, u2.name as completed_by_name')
            ->join('facilities f', 'f.id=ic.facility_id', 'left')
            ->join('users u', 'u.id=ic.created_by', 'left')
            ->join('users u2', 'u2.id=ic.completed_by', 'left');
        $this->scopeFacilities($q, 'ic.facility_id');
        if ($facilityId) {
            $q->where('ic.facility_id', $facilityId);
        }
        if ($status)     $q->where('ic.status',$status);
        $checklists = $q->orderBy('ic.created_at','DESC')->get()->getResultArray();
        $facilities = $this->db->table('facilities')->where('status','active')->get()->getResultArray();

        return view('compliance/inspections', $this->viewData([
            'title'       => 'Inspection Checklists',
            'checklists'  => $checklists,
            'facilities'  => $facilities,
            'filterFacility' => $facilityId,
            'filterStatus'   => $status,
        ]));
    }

    public function createInspection()
    {
        $facilities = $this->db->table('facilities')->where('status','active')->get()->getResultArray();
        return view('compliance/create_inspection', $this->viewData(['title'=>'New Inspection Checklist','facilities'=>$facilities]));
    }

    public function storeInspection()
    {
        $rules = ['facility_id'=>'required','title'=>'required','inspection_date'=>'required','type'=>'required'];
        if (!$this->validate($rules)) return redirect()->back()->withInput()->with('errors',$this->validator->getErrors());

        $items = $this->request->getPost('items') ?? [];
        $this->db->table('inspection_checklists')->insert([
            'facility_id'     => $this->request->getPost('facility_id'),
            'title'           => esc($this->request->getPost('title')),
            'type'            => $this->request->getPost('type'),
            'inspection_date' => $this->request->getPost('inspection_date'),
            'inspector_name'  => esc($this->request->getPost('inspector_name')),
            'notes'           => esc($this->request->getPost('notes')),
            'status'          => 'pending',
            'created_by'      => session()->get('user_id'),
        ]);
        $checklistId = $this->db->insertID();

        foreach ($items as $item) {
            if (!empty(trim($item))) {
                $this->db->table('inspection_items')->insert([
                    'checklist_id' => $checklistId,
                    'item_text'    => esc($item),
                    'result'       => 'pending',
                ]);
            }
        }
        return redirect()->to(base_url('compliance/inspections'))->with('success','Inspection checklist created.');
    }

    public function viewInspection(int $id)
    {
        $checklist = $this->db->table('inspection_checklists ic')
            ->select('ic.*, f.name as facility_name, u.name as created_by_name')
            ->join('facilities f','f.id=ic.facility_id','left')
            ->join('users u','u.id=ic.created_by','left')
            ->where('ic.id',$id)->get()->getRowArray();
        if (!$checklist) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();

        $items = $this->db->table('inspection_items')->where('checklist_id',$id)->orderBy('id','ASC')->get()->getResultArray();

        return view('compliance/view_inspection', $this->viewData([
            'title'     => 'Inspection: '.esc($checklist['title']),
            'checklist' => $checklist,
            'items'     => $items,
        ]));
    }

    public function submitInspection(int $id)
    {
        $results  = $this->request->getPost('results') ?? [];
        $remarks  = $this->request->getPost('remarks') ?? [];

        foreach ($results as $itemId => $result) {
            $this->db->table('inspection_items')->where('id',$itemId)->update([
                'result'  => $result,
                'remarks' => esc($remarks[$itemId] ?? ''),
            ]);
        }

        $items    = $this->db->table('inspection_items')->where('checklist_id',$id)->get()->getResultArray();
        $total    = count($items);
        $passed   = count(array_filter($items, fn($i) => $i['result']==='pass'));
        $failed   = count(array_filter($items, fn($i) => $i['result']==='fail'));
        $score    = $total > 0 ? round($passed / $total * 100) : 0;
        $status   = $failed > 0 ? 'failed' : ($score >= 100 ? 'passed' : 'in_progress');

        $this->db->table('inspection_checklists')->where('id',$id)->update([
            'status'       => $status,
            'score'        => $score,
            'completed_by' => session()->get('user_id'),
            'completed_at' => date('Y-m-d H:i:s'),
            'overall_remarks' => esc($this->request->getPost('overall_remarks')),
        ]);

        return redirect()->to(base_url('compliance/inspections/view/'.$id))->with('success',"Inspection submitted. Score: {$score}%");
    }

    public function storeAudit()
    {
        $rules = ['facility_id'=>'required','audit_type'=>'required','audit_date'=>'required'];
        if (!$this->validate($rules)) return redirect()->back()->withInput()->with('errors',$this->validator->getErrors());
        $this->db->table('compliance_audits')->insert([
            'facility_id' => $this->request->getPost('facility_id'),
            'audit_type'  => esc($this->request->getPost('audit_type')),
            'audit_date'  => $this->request->getPost('audit_date'),
            'score'       => (int)$this->request->getPost('score'),
            'findings'    => esc($this->request->getPost('findings')),
            'status'      => $this->request->getPost('status') ?? 'open',
            'created_by'  => session()->get('user_id'),
        ]);
        return redirect()->to(base_url('compliance'))->with('success','Audit recorded.');
    }

    public function createIncident()
    {
        $facilities = $this->db->table('facilities')->where('status','active')->get()->getResultArray();
        return view('compliance/create_incident', $this->viewData(['title'=>'Report Incident','facilities'=>$facilities]));
    }

    public function storeIncident()
    {
        $rules = ['facility_id'=>'required','title'=>'required','incident_date'=>'required','severity'=>'required'];
        if (!$this->validate($rules)) return redirect()->back()->withInput()->with('errors',$this->validator->getErrors());
        $this->db->table('incidents')->insert([
            'facility_id'   => $this->request->getPost('facility_id'),
            'title'         => esc($this->request->getPost('title')),
            'description'   => esc($this->request->getPost('description')),
            'incident_date' => $this->request->getPost('incident_date'),
            'severity'      => $this->request->getPost('severity'),
            'status'        => 'open',
            'reported_by'   => session()->get('user_id'),
        ]);
        return redirect()->to(base_url('compliance'))->with('success','Incident reported.');
    }

    public function convertToInvoice(int $id)
    {
        $checklist = $this->db->table('inspection_checklists')->where('id', $id)->get()->getRowArray();
        if (!$checklist) {
            return redirect()->to(base_url('compliance/inspections'))->with('error', 'Inspection not found.');
        }

        $amount  = (float) ($this->request->getPost('amount') ?? 0);
        $notes   = trim((string) ($this->request->getPost('notes') ?? ''));
        $linkTo  = $this->request->getPost('link_to') ?? 'invoice';

        if ($amount <= 0) {
            return redirect()->to(base_url('compliance/inspections/view/'.$id))->with('error', 'Amount must be greater than zero.');
        }

        $companyId  = $this->companyScope()->activeCompanyId();
        $userId     = (int) session()->get('user_id');
        $facilityId = (int) $checklist['facility_id'];

        if ($linkTo === 'lease_payment' && $this->db->tableExists('lease_payments')) {
            $num = $this->generateNumber('LP', 'lease_payments', 'payment_number');
            $this->db->table('lease_payments')->insert([
                'company_id'     => $companyId,
                'payment_number' => $num,
                'facility_id'    => $facilityId,
                'payment_type'   => 'damage_charge',
                'payment_method' => 'pending',
                'amount'         => $amount,
                'status'         => 'pending',
                'notes'          => $notes ?: 'Damage charge from inspection #'.$id.': '.esc($checklist['title']),
                'created_by'     => $userId,
                'created_at'     => date('Y-m-d H:i:s'),
            ]);
            $this->logActivity('create', 'lease_payments', (int) $this->db->insertID(), 'From inspection '.$id);
            return redirect()->to(base_url('compliance/inspections/view/'.$id))->with('success', 'Lease payment record created.');
        }

        // Default: create invoice
        $invTable = $this->db->tableExists('invoices') ? 'invoices' : null;
        if ($invTable) {
            $invNum = $this->generateNumber('INV', $invTable, 'invoice_number');
            $this->db->table($invTable)->insert([
                'company_id'     => $companyId,
                'invoice_number' => $invNum,
                'facility_id'    => $facilityId,
                'invoice_type'   => 'damage',
                'subtotal'       => $amount,
                'total_amount'   => $amount,
                'status'         => 'draft',
                'notes'          => $notes ?: 'Damage charge from inspection #'.$id.': '.esc($checklist['title']),
                'issue_date'     => date('Y-m-d'),
                'due_date'       => date('Y-m-d', strtotime('+30 days')),
                'created_by'     => $userId,
                'created_at'     => date('Y-m-d H:i:s'),
            ]);
            $invId = (int) $this->db->insertID();
            $this->logActivity('create', 'invoices', $invId, 'From inspection '.$id);
            return redirect()->to(base_url('compliance/inspections/view/'.$id))->with('success', 'Invoice #'.$invNum.' created.');
        }

        return redirect()->to(base_url('compliance/inspections/view/'.$id))->with('error', 'Invoice table not available.');
    }

    public function printInspection(int $id)
    {
        $checklist = $this->db->table('inspection_checklists ic')
            ->select('ic.*, f.name as facility_name')
            ->join('facilities f', 'f.id=ic.facility_id', 'left')
            ->where('ic.id', $id)
            ->get()
            ->getRowArray();
        if (!$checklist) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        $this->assertFacilityAccess((int) $checklist['facility_id']);

        $items = $this->db->table('inspection_items')->where('checklist_id', $id)->orderBy('id', 'ASC')->get()->getResultArray();
        $printItems = [];
        foreach ($items as $item) {
            $result = $item['result'] ?? 'pending';
            $printItems[] = [
                'label'   => $item['item_text'],
                'checked' => $result === 'pass',
                'status'  => match ($result) {
                    'pass' => 'ok',
                    'fail' => 'issue',
                    default => 'na',
                },
                'notes'   => $item['remarks'] ?? '',
            ];
        }

        return view('compliance/checklist_print', $this->viewData([
            'checklistTitle' => $checklist['title'],
            'checklistType'  => $checklist['type'] ?? 'routine',
            'unitRef'        => $checklist['inspector_name'] ?? '—',
            'facilityName'   => $checklist['facility_name'],
            'inspectionDate' => date('d M Y', strtotime($checklist['inspection_date'])),
            'inspectorName'  => $checklist['inspector_name'],
            'refNumber'      => 'INS-' . $checklist['id'],
            'sections'       => [['title' => 'Inspection Items', 'items' => $printItems]],
            'usePdf'         => true,
        ]));
    }
}
