<?php

namespace App\Controllers\Traits;

use App\Services\ParkingContractService;
use App\Services\UnitLeaseSyncService;

trait ParkingContractTrait
{
    /**
     * @param array<string, mixed> $d
     */
    protected function renderParkingContractDocument(array $d, bool $wantPdf = false, string $tenantSignatureB64 = ''): \CodeIgniter\HTTP\Response|string
    {
        $svc = new ParkingContractService($this->db);
        $durationMonths = $svc->durationMonths(
            (string) ($d['start_date'] ?? ''),
            (string) ($d['end_date'] ?? '')
        );
        $contractDate = (string) ($d['contract_date'] ?? date('Y-m-d'));

        $data = $this->viewData([
            'title'              => 'Parking Contract',
            'd'                  => $d,
            'settings'           => $this->settings,
            'durationMonths'     => $durationMonths,
            'englishDay'         => $svc->englishDayName($contractDate),
            'arabicDay'          => $svc->arabicDayName($contractDate),
            'contractDateEn'     => $svc->formatDateEnLong($contractDate),
            'contractDateAr'     => $svc->formatDateAr($contractDate),
            'startDateEn'        => $svc->formatDateEn((string) ($d['start_date'] ?? '')),
            'endDateEn'          => $svc->formatDateEn((string) ($d['end_date'] ?? '')),
            'startDateAr'        => $svc->formatDateAr((string) ($d['start_date'] ?? '')),
            'endDateAr'          => $svc->formatDateAr((string) ($d['end_date'] ?? '')),
            'poaDateFmt'         => $svc->formatPoaDate((string) ($d['poa_date'] ?? '')),
            'vehicleEn'          => $svc->vehicleTypeEnglish((string) ($d['vehicle_type'] ?? '')),
            'usePdf'             => true,
            'pdfUrl'             => '',
            'tenantSignatureB64' => $tenantSignatureB64,
        ]);

        if ($wantPdf && class_exists(\Dompdf\Dompdf::class)) {
            $html = view('leases/parking_contract_print', $data);
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $filename = 'Parking_Contract_' . preg_replace('/[^A-Za-z0-9_-]/', '_', (string) ($d['parking_unit_no'] ?? 'unit')) . '.pdf';

            return $this->response
                ->setHeader('Content-Type', 'application/pdf')
                ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->setBody($dompdf->output());
        }

        return view('leases/parking_contract_print', $data);
    }

    /** @param array<string, mixed> $unit */
    protected function isParkingUnitRow(array $unit): bool
    {
        return strtolower(trim((string) ($unit['unit_type'] ?? ''))) === 'parking';
    }

    /**
     * @param array<string, mixed> $d
     * @return array<string, mixed>
     */
    protected function persistParkingContractFields(int $unitId, ?int $leaseId, array $d): array
    {
        if ($this->db->fieldExists('plate_number', 'units') && ! empty($d['plate_number'])) {
            $this->db->table('units')->where('id', $unitId)->update([
                'plate_number' => esc((string) $d['plate_number']),
            ]);
        }

        if ($leaseId && $this->db->tableExists('lease_contracts')) {
            $patch = [];
            if ($this->db->fieldExists('contract_kind', 'lease_contracts')) {
                $patch['contract_kind'] = 'parking';
            }
            if ($this->db->fieldExists('plate_number', 'lease_contracts') && ! empty($d['plate_number'])) {
                $patch['plate_number'] = esc((string) $d['plate_number']);
            }
            if ($this->db->fieldExists('vehicle_type', 'lease_contracts') && ! empty($d['vehicle_type'])) {
                $patch['vehicle_type'] = esc((string) $d['vehicle_type']);
            }
            if ($this->db->fieldExists('vehicle_description', 'lease_contracts')) {
                $patch['vehicle_description'] = esc((string) ($d['vehicle_description'] ?? ''));
            }
            if ($this->db->fieldExists('title_deed_no', 'lease_contracts')) {
                $patch['title_deed_no'] = esc((string) ($d['title_deed_no'] ?? ''));
            }
            if ($this->db->fieldExists('zone_no', 'lease_contracts')) {
                $patch['zone_no'] = esc((string) ($d['zone_no'] ?? ''));
            }
            if ($this->db->fieldExists('street_no', 'lease_contracts')) {
                $patch['street_no'] = esc((string) ($d['street_no'] ?? ''));
            }
            if ($this->db->fieldExists('building_no', 'lease_contracts')) {
                $patch['building_no'] = esc((string) ($d['building_no'] ?? ''));
            }
            if ($this->db->fieldExists('tenant_qid', 'lease_contracts')) {
                $qid = trim((string) ($d['tenant_qid'] ?? ''));
                $patch['tenant_qid'] = $qid !== '' ? esc($qid) : null;
            }
            foreach (['start_date', 'end_date', 'rent_amount'] as $field) {
                if ($this->db->fieldExists($field, 'lease_contracts') && ! empty($d[$field])) {
                    $patch[$field] = $field === 'rent_amount' ? (float) $d[$field] : esc((string) $d[$field]);
                }
            }
            if (! empty($d['contract_number']) && $this->db->fieldExists('contract_number', 'lease_contracts')) {
                $patch['contract_number'] = esc((string) $d['contract_number']);
            }
            if (! empty($d['payment_terms']) && $this->db->fieldExists('payment_type', 'lease_contracts')) {
                $patch['payment_type'] = esc((string) $d['payment_terms']);
            }
            if (! empty($d['contract_date']) && $this->db->fieldExists('signed_date', 'lease_contracts')) {
                $patch['signed_date'] = esc((string) $d['contract_date']);
            }
            if ($patch !== []) {
                $patch['updated_at'] = date('Y-m-d H:i:s');
                $this->db->table('lease_contracts')->where('id', $leaseId)->update($patch);
            }

            if (! empty($patch['tenant_qid']) && $this->db->fieldExists('qid_no', 'tenants')) {
                $leaseRow = $this->db->table('lease_contracts')
                    ->select('tenant_id')
                    ->where('id', $leaseId)
                    ->get()->getRowArray();
                $tenantId = (int) ($leaseRow['tenant_id'] ?? 0);
                if ($tenantId > 0) {
                    $this->db->table('tenants')->where('id', $tenantId)->update([
                        'qid_no'     => $patch['tenant_qid'],
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                }
            }

            $d = $this->syncParkingContractPhotos($leaseId, $d);
        }

        return $d;
    }

    /**
     * Ensure a lease_contracts row exists when printing parking agreements from a unit.
     *
     * @param array<string, mixed> $d
     */
    protected function ensureLeaseFromParkingData(int $unitId, array $d): int
    {
        $leaseId = (int) ($d['lease_contract_id'] ?? 0);
        if ($leaseId > 0) {
            return $leaseId;
        }

        if (! $this->db->tableExists('lease_contracts') || ! $this->db->tableExists('units')) {
            return 0;
        }

        $unit = $this->db->table('units u')
            ->select('u.*, f.company_id as facility_company_id')
            ->join('facilities f', 'f.id = u.facility_id', 'left')
            ->where('u.id', $unitId)
            ->get()->getRowArray();
        if (! $unit) {
            return 0;
        }

        $unitPatch = [];
        if (! empty($d['start_date'])) {
            $unitPatch['contract_start'] = (string) $d['start_date'];
        }
        if (! empty($d['end_date'])) {
            $unitPatch['contract_end'] = (string) $d['end_date'];
        }
        if (isset($d['rent_amount']) && $d['rent_amount'] !== '') {
            $unitPatch['rent_amount'] = (float) $d['rent_amount'];
        }
        if (! empty($d['tenant_name'])) {
            $unitPatch['tenant_name'] = esc((string) $d['tenant_name']);
        }
        if (! empty($d['tenant_phone'])) {
            $unitPatch['tenant_mobile'] = esc((string) $d['tenant_phone']);
        }
        if (! empty($d['contract_number'])) {
            $unitPatch['contract_number'] = esc((string) $d['contract_number']);
        }
        if (! empty($d['plate_number']) && $this->db->fieldExists('plate_number', 'units')) {
            $unitPatch['plate_number'] = esc((string) $d['plate_number']);
        }
        if ($unitPatch !== []) {
            $this->db->table('units')->where('id', $unitId)->update($unitPatch);
            $unit = array_merge($unit, $unitPatch);
        }

        helper('fm');
        (new UnitLeaseSyncService($this->db))->syncUnitRow(
            $unit,
            function_exists('fm_session_user_id') ? fm_session_user_id() : null
        );

        $existing = $this->db->table('lease_contracts')
            ->where('unit_id', $unitId)
            ->where('deleted_at', null)
            ->whereIn('status', ['active', 'draft'])
            ->orderBy('id', 'DESC')
            ->limit(1)
            ->get()->getRowArray();

        return (int) ($existing['id'] ?? 0);
    }

    /**
     * Merge optional contract photos from the request and persist to lease_contracts.photos_json.
     *
     * @param array<string, mixed> $d
     * @return array<string, mixed>
     */
    protected function syncParkingContractPhotos(int $leaseId, array $d): array
    {
        if ($leaseId < 1 || ! $this->db->fieldExists('photos_json', 'lease_contracts')) {
            return $d;
        }

        $row = $this->db->table('lease_contracts')
            ->select('photos_json')
            ->where('id', $leaseId)
            ->get()->getRowArray();

        $existing = \App\Services\ParkingContractPhotoService::pathsFromJson($row['photos_json'] ?? null);
        $remove   = array_map('strval', (array) $this->request->getPost('remove_photos'));
        $kept     = array_values(array_filter(
            $existing,
            static fn (string $path): bool => ! in_array($path, $remove, true)
        ));

        $uploads = \App\Services\ParkingContractPhotoService::storeUploads(
            $this->request->getFileMultiple('contract_photos') ?? []
        );
        $final   = \App\Services\ParkingContractPhotoService::mergePhotos($kept, $uploads);

        $this->db->table('lease_contracts')->where('id', $leaseId)->update([
            'photos_json' => \App\Services\ParkingContractPhotoService::encodePhotos($final),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);

        $d['contract_photos'] = $final;

        return $d;
    }

    /**
     * @return array<string, mixed>
     */
    protected function collectParkingFormData(int $unitId, ?int $leaseId = null): array
    {
        $svc      = new ParkingContractService($this->db);
        $defaults = $svc->buildDefaults($unitId, $leaseId);
        $merged   = $svc->mergeFormInput($defaults, array_merge(
            $this->request->getPost() ?? [],
            $this->request->getGet() ?? []
        ));
        $postLeaseId = (int) ($this->request->getPost('lease_contract_id') ?? $this->request->getGet('contract_id') ?? 0);
        if ($postLeaseId > 0) {
            $merged['lease_contract_id'] = $postLeaseId;
        }

        return $merged;
    }

    /**
     * Save parking contract form data to unit + lease_contracts (no print).
     *
     * @return array{lease_id: int, d: array<string, mixed>}
     */
    protected function saveParkingContractData(int $unitId, bool $isRenew = false): array
    {
        $leaseId = (int) ($this->request->getPost('lease_contract_id') ?? $this->request->getGet('contract_id') ?? 0);
        $d       = $this->collectParkingFormData($unitId, $leaseId > 0 ? $leaseId : null);

        if ($isRenew && $leaseId > 0) {
            $leaseId = $this->renewParkingLease($unitId, $leaseId, $d);
        } else {
            $leaseId = $this->ensureLeaseFromParkingData($unitId, $d);
        }

        if ($leaseId > 0) {
            $d['lease_contract_id'] = $leaseId;
            $d                      = $this->persistParkingContractFields($unitId, $leaseId, $d);
            $this->persistParkingFormSnapshot($leaseId, $d);
        }

        return ['lease_id' => $leaseId, 'd' => $d];
    }

    /**
     * Archive signed/unsigned parking agreement PDF under unit documents, mark lease renewed, create new lease row.
     *
     * @param array<string, mixed> $d
     */
    protected function renewParkingLease(int $unitId, int $oldLeaseId, array &$d): int
    {
        if (! $this->db->tableExists('lease_contracts')) {
            return $oldLeaseId;
        }

        $old = $this->db->table('lease_contracts')->where('id', $oldLeaseId)->get()->getRowArray();
        if (! $old) {
            return $oldLeaseId;
        }

        $svc         = new ParkingContractService($this->db);
        $archiveData = $svc->buildDefaults($unitId, $oldLeaseId);
        $sigB64      = (new \App\Services\ContractSignatureService($this->db))
            ->signatureDataUri($old['tenant_signature_path'] ?? '');
        $this->archiveParkingContractDocument($unitId, $oldLeaseId, $archiveData, $sigB64);

        $this->db->table('lease_contracts')->where('id', $oldLeaseId)->update([
            'status'     => 'renewed',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $newNumber = trim((string) ($d['contract_number'] ?? ''));
        if ($newNumber === '' || $newNumber === ($old['contract_number'] ?? '')) {
            $newNumber = $this->generateRenewedContractNumber((string) ($old['contract_number'] ?? 'PK'));
            $d['contract_number'] = $newNumber;
        }

        $newRow = [
            'contract_number'      => esc($newNumber),
            'tenant_id'            => (int) ($old['tenant_id'] ?? 0),
            'facility_id'          => (int) ($old['facility_id'] ?? 0),
            'unit_id'              => $unitId,
            'status'               => 'active',
            'signed_date'          => ! empty($d['contract_date']) ? esc((string) $d['contract_date']) : ($d['start_date'] ?? date('Y-m-d')),
            'billing_start_date'   => $d['start_date'] ?? $old['start_date'],
            'start_date'           => $d['start_date'] ?? $old['start_date'],
            'end_date'             => $d['end_date'] ?? $old['end_date'],
            'rent_amount'          => (float) ($d['rent_amount'] ?? $old['rent_amount'] ?? 0),
            'payment_frequency'    => $d['payment_frequency'] ?? $old['payment_frequency'] ?? 'monthly',
            'payment_type'         => $d['payment_terms'] ?? $old['payment_type'] ?? 'cheque',
            'created_by'           => function_exists('fm_session_user_id') ? fm_session_user_id() : null,
            'created_at'           => date('Y-m-d H:i:s'),
            'updated_at'           => date('Y-m-d H:i:s'),
        ];

        if ($this->db->fieldExists('company_id', 'lease_contracts')) {
            $newRow['company_id'] = $old['company_id'] ?? null;
        }
        if ($this->db->fieldExists('contract_kind', 'lease_contracts')) {
            $newRow['contract_kind'] = 'parking';
        }
        if ($this->db->fieldExists('tenant_signature_path', 'lease_contracts')) {
            $newRow['tenant_signature_path'] = null;
            $newRow['signature_token']       = null;
            $newRow['tenant_signed_at']      = null;
        }

        $this->db->table('lease_contracts')->insert($newRow);
        $newId = (int) $this->db->insertID();

        return $newId > 0 ? $newId : $oldLeaseId;
    }

    protected function generateRenewedContractNumber(string $base): string
    {
        $base = trim($base) !== '' ? trim($base) : 'PK';
        $base = preg_replace('/-R\d+$/', '', $base) ?? $base;

        for ($i = 2; $i <= 99; $i++) {
            $candidate = $base . '-R' . $i;
            if ($this->db->table('lease_contracts')->where('contract_number', $candidate)->countAllResults() === 0) {
                return $candidate;
            }
        }

        return $base . '-R' . date('ymd');
    }

    /**
     * @param array<string, mixed> $d
     */
    protected function persistParkingFormSnapshot(int $leaseId, array $d): void
    {
        if ($leaseId < 1 || ! $this->db->fieldExists('notes', 'lease_contracts')) {
            return;
        }

        $snapshot = [];
        foreach ($d as $key => $val) {
            if (is_scalar($val) || $val === null) {
                $snapshot[$key] = $val;
            }
        }

        $this->db->table('lease_contracts')->where('id', $leaseId)->update([
            'notes'      => json_encode(['parking_form' => $snapshot], JSON_UNESCAPED_UNICODE),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * @param array<string, mixed> $d
     */
    protected function archiveParkingContractDocument(int $unitId, int $leaseId, array $d, string $tenantSignatureB64 = ''): void
    {
        if (! $this->db->tableExists('documents') || $unitId < 1) {
            return;
        }

        $dir = WRITEPATH . 'uploads/documents';
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $contractNo = (string) ($d['contract_number'] ?? $leaseId);
        $period     = ($d['start_date'] ?? '') . '_' . ($d['end_date'] ?? '');
        $baseName   = 'parking_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $contractNo) . '_' . preg_replace('/[^0-9_-]/', '', $period);
        $relPath    = null;

        try {
            if (class_exists(\Dompdf\Dompdf::class)) {
                $html   = $this->renderParkingContractHtml($d, $tenantSignatureB64);
                $dompdf = new \Dompdf\Dompdf();
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();
                $fileName = $baseName . '.pdf';
                $fullPath = $dir . '/' . $fileName;
                file_put_contents($fullPath, $dompdf->output());
                $relPath = 'documents/' . $fileName;
            } else {
                $html     = $this->renderParkingContractHtml($d, $tenantSignatureB64);
                $fileName = $baseName . '.html';
                $fullPath = $dir . '/' . $fileName;
                file_put_contents($fullPath, $html);
                $relPath = 'documents/' . $fileName;
            }
        } catch (\Throwable $e) {
            log_message('error', 'Parking contract archive failed: ' . $e->getMessage());

            return;
        }

        if ($relPath === null) {
            return;
        }

        $title = 'Parking Contract ' . $contractNo . ' (' . ($d['start_date'] ?? '') . ' – ' . ($d['end_date'] ?? '') . ')';
        $row   = [
            'module'      => 'unit',
            'ref_id'      => $unitId,
            'title'       => $title,
            'doc_type'    => 'parking_contract',
            'description' => 'Archived lease #' . $leaseId . ' before renewal',
            'file_path'   => $relPath,
            'doc_number'  => $contractNo,
            'doc_date'    => $d['contract_date'] ?? $d['start_date'] ?? null,
            'uploaded_by' => function_exists('fm_session_user_id') ? fm_session_user_id() : null,
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ];

        if ($this->db->fieldExists('facility_id', 'documents')) {
            $unit = $this->db->table('units')->select('facility_id')->where('id', $unitId)->get()->getRowArray();
            $row['facility_id'] = (int) ($unit['facility_id'] ?? 0) ?: null;
        }

        $this->db->table('documents')->insert($row);
    }

    /**
     * @param array<string, mixed> $d
     */
    protected function renderParkingContractHtml(array $d, string $tenantSignatureB64 = ''): string
    {
        $svc          = new ParkingContractService($this->db);
        $contractDate = (string) ($d['contract_date'] ?? date('Y-m-d'));
        $duration     = $svc->durationMonths((string) ($d['start_date'] ?? ''), (string) ($d['end_date'] ?? ''));

        return view('leases/parking_contract_print', $this->viewData([
            'title'              => 'Parking Contract',
            'd'                  => $d,
            'settings'           => $this->settings,
            'durationMonths'     => $duration,
            'englishDay'         => $svc->englishDayName($contractDate),
            'arabicDay'          => $svc->arabicDayName($contractDate),
            'contractDateEn'     => $svc->formatDateEnLong($contractDate),
            'contractDateAr'     => $svc->formatDateAr($contractDate),
            'startDateEn'        => $svc->formatDateEn((string) ($d['start_date'] ?? '')),
            'endDateEn'          => $svc->formatDateEn((string) ($d['end_date'] ?? '')),
            'startDateAr'        => $svc->formatDateAr((string) ($d['start_date'] ?? '')),
            'endDateAr'          => $svc->formatDateAr((string) ($d['end_date'] ?? '')),
            'poaDateFmt'         => $svc->formatPoaDate((string) ($d['poa_date'] ?? '')),
            'vehicleEn'          => $svc->vehicleTypeEnglish((string) ($d['vehicle_type'] ?? '')),
            'usePdf'             => true,
            'pdfUrl'             => '',
            'tenantSignatureB64' => $tenantSignatureB64,
        ]));
    }

    protected function parkingContractRedirect(int $unitId, ?int $leaseId = null, bool $renew = false): string
    {
        helper('fm');
        $url = fm_unit_parking_contract_url($unitId, $leaseId);
        if ($renew) {
            $url .= (str_contains($url, '?') ? '&' : '?') . 'renew=1';
        }

        return $url;
    }
}
