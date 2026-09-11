<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

class ChequeImportService
{
    public function __construct(private BaseConnection $db)
    {
    }

    /**
     * @param list<array<string, string>> $rows
     * @param array{
     *   forced_contract_id?: int|null,
     *   company_id?: int|null,
     *   default_payable_to_type?: string,
     *   default_payable_to_id?: int|null,
     *   default_received_date?: string|null
     * } $options
     * @return array{count: int, errors: list<string>}
     */
    public function importRows(array $rows, int $userId, array $options = []): array
    {
        $forcedContractId = (int) ($options['forced_contract_id'] ?? 0) ?: null;
        $companyId        = (int) ($options['company_id'] ?? 0) ?: null;
        $defaultPayable   = strtolower(trim((string) ($options['default_payable_to_type'] ?? 'company')));
        if (! in_array($defaultPayable, ['company', 'landlord'], true)) {
            $defaultPayable = 'company';
        }
        $defaultPayableId   = (int) ($options['default_payable_to_id'] ?? 0) ?: null;
        $defaultReceivedDate = trim((string) ($options['default_received_date'] ?? '')) ?: null;

        $forcedContract = null;
        if ($forcedContractId && $this->db->tableExists('lease_contracts')) {
            $forcedContract = $this->db->table('lease_contracts')->where('id', $forcedContractId)->get()->getRowArray() ?: null;
        }

        $count  = 0;
        $errors = [];
        $chequeSvc = new ChequeTrackingService($this->db);
        $paySync   = new ChequePaymentSyncService($this->db);

        foreach ($rows as $lineNum => $row) {
            $chequeNo = trim($row['cheque_no'] ?? $row['cheque_number'] ?? '');
            $amount   = trim($row['amount'] ?? '');
            if ($chequeNo === '' || $amount === '') {
                $errors[] = 'Row ' . ($lineNum + 2) . ': cheque_no and amount required';
                continue;
            }

            $contractId = null;
            $tenantId   = null;
            $facilityId = null;
            $rowCompany = $companyId;

            if ($forcedContract) {
                $contractId = $forcedContractId;
                $tenantId   = (int) ($forcedContract['tenant_id'] ?? 0) ?: null;
                $facilityId = (int) ($forcedContract['facility_id'] ?? 0) ?: null;
                $rowCompany = (int) ($forcedContract['company_id'] ?? 0) ?: $companyId;
            } else {
                $cid = (int) ($row['contract_id'] ?? 0);
                if ($cid > 0 && $this->db->tableExists('lease_contracts')) {
                    $contract = $this->db->table('lease_contracts')->where('id', $cid)->get()->getRowArray();
                    if ($contract) {
                        $contractId = $cid;
                        $tenantId   = (int) ($contract['tenant_id'] ?? 0) ?: null;
                        $facilityId = (int) ($contract['facility_id'] ?? 0) ?: null;
                        $rowCompany = (int) ($contract['company_id'] ?? 0) ?: $companyId;
                    }
                }
            }

            $payableType = strtolower(trim($row['payable_to_type'] ?? $row['payable_to'] ?? $defaultPayable));
            if (! in_array($payableType, ['company', 'landlord'], true)) {
                $payableType = $defaultPayable;
            }
            $payableId = (int) ($row['payable_to_id'] ?? $row['landlord_id'] ?? 0) ?: $defaultPayableId;
            $landlordId = $payableType === 'landlord' ? $payableId : null;

            $paymentId = (int) ($row['payment_id'] ?? 0) ?: null;

            $insert = [
                'company_id'      => $rowCompany,
                'contract_id'     => $contractId,
                'tenant_id'       => $tenantId,
                'facility_id'     => $facilityId,
                'payment_id'      => $paymentId,
                'landlord_id'     => $landlordId,
                'payable_to_type' => $payableType,
                'payable_to_id'   => $payableId,
                'cheque_no'       => esc($chequeNo),
                'amount'          => $amount,
                'bank_name'       => esc(trim($row['bank_name'] ?? '')) ?: null,
                'account_name'    => esc(trim($row['account_name'] ?? '')) ?: null,
                'account_no'      => esc(trim($row['account_no'] ?? '')) ?: null,
                'cheque_date'     => trim($row['cheque_date'] ?? $row['issue_date'] ?? '') ?: null,
                'due_date'        => trim($row['due_date'] ?? '') ?: null,
                'received_date'   => trim($row['received_date'] ?? '') ?: $defaultReceivedDate,
                'status'          => 'pending',
            ];

            $newId = $chequeSvc->createCheque($insert, $userId);
            $insert['id'] = $newId;
            if ($paymentId) {
                if ($this->db->tableExists('lease_payments')) {
                    $this->db->table('lease_payments')->where('id', $paymentId)->update([
                        'payment_method' => 'cheque',
                        'cheque_no'      => esc($chequeNo),
                        'updated_at'     => date('Y-m-d H:i:s'),
                    ]);
                }
                $paySync->onChequeRegistered($insert, $userId);
            }

            $count++;
        }

        return ['count' => $count, 'errors' => $errors];
    }
}
