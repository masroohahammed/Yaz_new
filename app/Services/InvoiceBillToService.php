<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

/**
 * Resolve bill-to customer for invoices (facility vs non-facility work orders).
 */
class InvoiceBillToService
{
    public function __construct(private BaseConnection $db)
    {
    }

    /** @return array{name: string, email: string, phone: string, address: string, service_customer_id: ?int} */
    public function resolveFromWorkOrder(array $wo): array
    {
        $name    = trim((string) ($wo['requester_name'] ?? ''));
        $email   = trim((string) ($wo['requester_email'] ?? ''));
        $phone   = trim((string) ($wo['requester_phone'] ?? ''));
        $address = trim((string) ($wo['requester_location'] ?? ''));
        $custId  = ! empty($wo['service_customer_id']) ? (int) $wo['service_customer_id'] : null;

        if ($custId && $this->db->tableExists('service_customers')) {
            $sc = $this->db->table('service_customers')->where('id', $custId)->get()->getRowArray();
            if ($sc) {
                $name    = $name ?: trim((string) ($sc['name'] ?? ''));
                $email   = $email ?: trim((string) ($sc['email'] ?? ''));
                $phone   = $phone ?: trim((string) ($sc['phone'] ?? ''));
                $address = $address ?: trim((string) ($sc['location'] ?? ''));
            }
        }

        if ($name === '' && ! empty($wo['facility_name'])) {
            $name = (string) $wo['facility_name'];
        }
        if ($name === '') {
            $name = 'Customer';
        }

        return [
            'name'                 => $name,
            'email'                => $email,
            'phone'                => $phone,
            'address'              => $address,
            'service_customer_id'  => $custId,
        ];
    }

    /** @param array<string, mixed> $insert */
    public function applyWorkOrderToInsert(array $insert, array $wo): array
    {
        $facilityId = ! empty($wo['facility_id']) ? (int) $wo['facility_id'] : null;
        $insert['facility_id'] = $facilityId;

        $bill = $this->resolveFromWorkOrder($wo);

        if ($this->db->fieldExists('bill_to_name', 'invoices')) {
            $insert['bill_to_name']    = $bill['name'];
            $insert['bill_to_email']   = $bill['email'] ?: null;
            $insert['bill_to_phone']   = $bill['phone'] ?: null;
            $insert['bill_to_address'] = $bill['address'] ?: null;
        }
        if ($this->db->fieldExists('service_customer_id', 'invoices') && $bill['service_customer_id']) {
            $insert['service_customer_id'] = $bill['service_customer_id'];
        }

        if ($facilityId === null) {
            $note = 'Bill to: ' . $bill['name'];
            if ($bill['address'] !== '') {
                $note .= ' — ' . $bill['address'];
            }
            $insert['notes'] = trim(($insert['notes'] ?? '') . "\n" . $note);
        }

        if ($this->db->fieldExists('company_id', 'invoices') && $facilityId) {
            $fac = $this->db->table('facilities')->select('company_id')->where('id', $facilityId)->get()->getRowArray();
            $insert['company_id'] = $fac['company_id'] ?? null;
        }

        return $insert;
    }

    public function loadWorkOrderForInvoice(int $woId): ?array
    {
        $q = $this->db->table('work_orders w')
            ->select('w.*, f.name AS facility_name')
            ->join('facilities f', 'f.id = w.facility_id', 'left')
            ->where('w.id', $woId);

        if ($this->db->fieldExists('deleted_at', 'work_orders')) {
            $q->where('w.deleted_at', null);
        }

        $wo = $q->get()->getRowArray();
        if (! $wo) {
            return null;
        }

        if (empty($wo['service_customer_id']) && $this->db->tableExists('maintenance_requests')) {
            $mrId = null;
            if ($this->db->fieldExists('maintenance_request_id', 'work_orders') && ! empty($wo['maintenance_request_id'])) {
                $mrId = (int) $wo['maintenance_request_id'];
            } elseif ($this->db->fieldExists('converted_to_wo', 'maintenance_requests')) {
                $mr = $this->db->table('maintenance_requests')->select('id, service_customer_id, requester_location, requester_name, requester_phone, requester_email')
                    ->where('converted_to_wo', $woId)->get()->getRowArray();
                if ($mr) {
                    $wo['service_customer_id'] = $wo['service_customer_id'] ?? $mr['service_customer_id'];
                    $wo['requester_location']  = $wo['requester_location'] ?? ($mr['requester_location'] ?? '');
                    $wo['requester_name']      = $wo['requester_name'] ?: ($mr['requester_name'] ?? '');
                    $wo['requester_phone']     = $wo['requester_phone'] ?: ($mr['requester_phone'] ?? '');
                    $wo['requester_email']     = $wo['requester_email'] ?: ($mr['requester_email'] ?? '');
                }
            }
            if ($mrId && $this->db->fieldExists('service_customer_id', 'maintenance_requests')) {
                $mr = $this->db->table('maintenance_requests')->where('id', $mrId)->get()->getRowArray();
                if ($mr) {
                    $wo['service_customer_id'] = $wo['service_customer_id'] ?? $mr['service_customer_id'];
                    $wo['requester_location']  = $wo['requester_location'] ?? ($mr['requester_location'] ?? '');
                }
            }
        }

        return $wo;
    }

    /** Display name on invoice PDF / view */
    public function displayBillToName(array $invoice): string
    {
        if (! empty($invoice['bill_to_name'])) {
            return (string) $invoice['bill_to_name'];
        }
        if (! empty($invoice['facility_name'])) {
            return (string) $invoice['facility_name'];
        }

        return 'Customer';
    }
}
