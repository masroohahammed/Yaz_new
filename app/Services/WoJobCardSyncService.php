<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

/**
 * Sync job card labor/materials into work order tables (idempotent).
 */
class WoJobCardSyncService
{
    public function __construct(
        private BaseConnection $db,
        private array $settings = []
    ) {
    }

    /**
     * Push one or all job cards on a WO into wo_labor / wo_materials and return cost totals.
     *
     * @return array{labor_total: float, material_total: float, total: float}
     */
    public function syncWorkOrderFromJobCards(int $woId, ?int $onlyJcId = null): array
    {
        $builder = $this->db->table('job_cards')
            ->where('wo_id', $woId)
            ->where('deleted_at', null)
            ->whereIn('status', ['in_progress', 'completed', 'approved']);

        if ($onlyJcId) {
            $builder->where('id', $onlyJcId);
        }

        $cards = $builder->get()->getResultArray();
        if ($cards === []) {
            return $this->totalsForWo($woId);
        }

        $uid  = (int) (session()->get('user_id') ?: 0);
        $rate = (float) ($this->settings['default_hourly_rate'] ?? 35);

        $laborNotes = $this->db->table('wo_labor')
            ->select('notes')
            ->where('wo_id', $woId)
            ->get()
            ->getResultArray();
        $existingLaborNotes = array_flip(array_map(
            static fn ($r) => (string) ($r['notes'] ?? ''),
            $laborNotes
        ));

        $matRows = $this->db->table('wo_materials')
            ->select('item_name, notes')
            ->where('wo_id', $woId)
            ->get()
            ->getResultArray();
        $existingMatKeys = [];
        foreach ($matRows as $r) {
            $existingMatKeys[(string) ($r['item_name'] ?? '') . '|' . (string) ($r['notes'] ?? '')] = true;
        }

        $jcIds = array_map(static fn ($c) => (int) $c['id'], $cards);
        $allJcMaterials = [];
        if ($jcIds !== []) {
            foreach ($this->db->table('jc_materials')->whereIn('jc_id', $jcIds)->get()->getResultArray() as $m) {
                $allJcMaterials[(int) $m['jc_id']][] = $m;
            }
        }

        $laborBatch = [];
        $matBatch   = [];
        $now        = date('Y-m-d H:i:s');

        foreach ($cards as $jc) {
            $marker = 'Job Card ' . $jc['jc_number'];
            $hours  = (float) ($jc['labor_hours'] ?? 0);

            if ($hours > 0 && ! isset($existingLaborNotes[$marker])) {
                $workDate = ! empty($jc['completed_at'])
                    ? date('Y-m-d', strtotime($jc['completed_at']))
                    : date('Y-m-d');
                $laborBatch[] = [
                    'wo_id'        => $woId,
                    'user_id'      => (int) ($jc['assigned_to'] ?? $uid),
                    'work_date'    => $workDate,
                    'hours_worked' => $hours,
                    'hourly_rate'  => $rate,
                    'labor_cost'   => $hours * $rate,
                    'notes'        => $marker,
                    'created_by'   => $uid,
                    'created_at'   => $now,
                ];
                $existingLaborNotes[$marker] = true;
            }

            foreach ($allJcMaterials[(int) $jc['id']] ?? [] as $m) {
                $matKey = (string) ($m['item_name'] ?? '') . '|' . $marker;
                if (isset($existingMatKeys[$matKey])) {
                    continue;
                }
                $qty  = (float) ($m['quantity'] ?? 1);
                $cost = (float) ($m['unit_cost'] ?? 0);
                $matBatch[] = [
                    'wo_id'               => $woId,
                    'item_id'             => ! empty($m['item_id']) ? (int) $m['item_id'] : null,
                    'item_name'           => $m['item_name'],
                    'quantity'            => $qty,
                    'unit_cost'           => $cost,
                    'total_cost'          => (float) ($m['total_cost'] ?? ($qty * $cost)),
                    'deducted_from_stock' => 0,
                    'notes'               => $marker,
                    'added_by'            => $uid,
                    'created_at'          => $now,
                ];
                $existingMatKeys[$matKey] = true;
            }
        }

        if ($laborBatch !== []) {
            $this->db->table('wo_labor')->insertBatch($laborBatch);
        }
        if ($matBatch !== []) {
            $this->db->table('wo_materials')->insertBatch($matBatch);
        }

        return $this->totalsForWo($woId);
    }

    /** @return array{labor_total: float, material_total: float, total: float} */
    private function totalsForWo(int $woId): array
    {
        $laborTotal = (float) ($this->db->table('wo_labor')
            ->selectSum('labor_cost', 't')->where('wo_id', $woId)->get()->getRowArray()['t'] ?? 0);
        $materialTotal = (float) ($this->db->table('wo_materials')
            ->selectSum('total_cost', 't')->where('wo_id', $woId)->get()->getRowArray()['t'] ?? 0);

        return [
            'labor_total'    => $laborTotal,
            'material_total' => $materialTotal,
            'total'          => $laborTotal + $materialTotal,
        ];
    }
}
