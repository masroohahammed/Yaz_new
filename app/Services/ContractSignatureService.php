<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

/**
 * Token-based tenant digital signatures for lease contracts.
 */
class ContractSignatureService
{
    public function __construct(private BaseConnection $db)
    {
    }

    public function tableReady(): bool
    {
        return $this->db->tableExists('lease_contracts')
            && $this->db->fieldExists('signature_token', 'lease_contracts');
    }

    public function ensureToken(int $contractId): ?string
    {
        if (! $this->tableReady()) {
            return null;
        }

        $row = $this->db->table('lease_contracts')
            ->select('signature_token, tenant_signature_path')
            ->where('id', $contractId)
            ->get()->getRowArray();

        if (! $row) {
            return null;
        }

        $existing = trim((string) ($row['signature_token'] ?? ''));
        if ($existing !== '') {
            return $existing;
        }

        return $this->issueToken($contractId);
    }

    /** Clear tenant signature and invalidate the public signing link (renewal / re-sign). */
    public function clearSignature(int $contractId, bool $invalidateToken = true): void
    {
        if (! $this->tableReady()) {
            return;
        }

        $update = [
            'tenant_signature_path' => null,
            'tenant_signed_at'      => null,
            'updated_at'            => date('Y-m-d H:i:s'),
        ];
        if ($invalidateToken) {
            $update['signature_token'] = null;
        }

        $this->db->table('lease_contracts')->where('id', $contractId)->update($update);
    }

    /** Reset signature and issue a fresh signing token for a new tenant sign round. */
    public function regenerateSigningLink(int $contractId): ?string
    {
        if (! $this->tableReady()) {
            return null;
        }

        $this->clearSignature($contractId, true);

        return $this->issueToken($contractId);
    }

    private function issueToken(int $contractId): ?string
    {
        $token = bin2hex(random_bytes(24));
        $this->db->table('lease_contracts')->where('id', $contractId)->update([
            'signature_token' => $token,
            'updated_at'      => date('Y-m-d H:i:s'),
        ]);

        return $token;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function contractByToken(string $token): ?array
    {
        if (! $this->tableReady() || $token === '') {
            return null;
        }

        return $this->db->table('lease_contracts lc')
            ->select('lc.*, t.full_name AS tenant_name, t.phone AS tenant_phone, t.whatsapp AS tenant_whatsapp, t.qid_no, t.passport_no, f.name AS facility_name, u.unit_number, u.unit_type')
            ->join('tenants t', 't.id = lc.tenant_id', 'left')
            ->join('facilities f', 'f.id = lc.facility_id', 'left')
            ->join('units u', 'u.id = lc.unit_id', 'left')
            ->where('lc.signature_token', $token)
            ->get()->getRowArray() ?: null;
    }

    public function signUrl(string $token): string
    {
        return base_url('contract/sign/' . rawurlencode($token));
    }

    public function signatureDataUri(?string $storedPath): string
    {
        helper('fm');

        return fm_logo_data_uri(trim((string) $storedPath));
    }

    public function tenantQid(array $contract): string
    {
        return trim((string) ($contract['tenant_qid'] ?? $contract['qid_no'] ?? $contract['passport_no'] ?? ''));
    }
}
