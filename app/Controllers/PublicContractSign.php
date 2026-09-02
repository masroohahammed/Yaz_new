<?php

namespace App\Controllers;

use App\Services\ContractSignatureService;
use App\Services\SignatureStorageService;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Public tenant contract signing — no login required.
 */
class PublicContractSign extends Controller
{
    /** @var \CodeIgniter\Database\BaseConnection */
    protected $db;

    /** @var array<string, string> */
    protected array $settings = [];

    public function initController(
        RequestInterface $request,
        ResponseInterface $response,
        LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->db = \Config\Database::connect();
        $this->settings = self::loadSettings($this->db);
    }

    public function show(string $token)
    {
        $svc      = new ContractSignatureService($this->db);
        $contract = $svc->contractByToken($token);
        if (! $contract) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        helper('fm');
        $branding = fm_company_branding($this->settings, (int) ($contract['company_id'] ?? 1) ?: 1);
        $signed   = trim((string) ($contract['tenant_signature_path'] ?? '')) !== '';

        return view('public/contract_sign', [
            'title'              => 'Sign Lease Contract',
            'settings'           => $branding['settings'],
            'companyLogoUrl'     => $branding['logoUrl'],
            'contract'           => $contract,
            'tenantQid'          => $svc->tenantQid($contract),
            'token'              => $token,
            'alreadySigned'      => $signed,
            'signaturePreview'   => $signed ? $svc->signatureDataUri($contract['tenant_signature_path']) : '',
            'signedAt'           => $contract['tenant_signed_at'] ?? null,
        ]);
    }

    public function submit(string $token)
    {
        if (! $this->request->is('post')) {
            return redirect()->to(base_url('contract/sign/' . rawurlencode($token)));
        }

        $svc      = new ContractSignatureService($this->db);
        $contract = $svc->contractByToken($token);
        if (! $contract) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if (trim((string) ($contract['tenant_signature_path'] ?? '')) !== '') {
            return redirect()->to(base_url('contract/sign/' . rawurlencode($token)))
                ->with('info', 'This contract has already been signed.');
        }

        if (! $this->db->fieldExists('tenant_signature_path', 'lease_contracts')) {
            return redirect()->back()->with('error', 'Digital signature is not available on this system yet.');
        }

        $path = (new SignatureStorageService())->storeFromPost(
            $this->request->getPost('tenant_signature'),
            'lease_tenant_' . (int) $contract['id']
        );

        if ($path === null) {
            return redirect()->back()->with('error', 'Please draw your signature before submitting.');
        }

        $this->db->table('lease_contracts')->where('id', (int) $contract['id'])->update([
            'tenant_signature_path' => $path,
            'tenant_signed_at'      => date('Y-m-d H:i:s'),
            'updated_at'            => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to(base_url('contract/sign/' . rawurlencode($token)))
            ->with('success', 'Thank you — your signature has been saved.');
    }

    /** @return array<string, string> */
    private static function loadSettings($db): array
    {
        $out = [];
        if (! $db->tableExists('system_settings')) {
            return $out;
        }
        foreach ($db->table('system_settings')->get()->getResultArray() as $row) {
            $out[$row['setting_key']] = (string) ($row['setting_value'] ?? '');
        }

        return $out;
    }
}
