<?php

/**
 * Helpdesk workflow helpers — facility vs non-facility permissions.
 */

if (! function_exists('helpdesk_is_non_facility')) {
    function helpdesk_is_non_facility(?array $complaint): bool
    {
        if (! $complaint) {
            return false;
        }
        if (($complaint['work_type'] ?? '') === 'non_facility') {
            return true;
        }

        return empty($complaint['facility_id']);
    }
}

if (! function_exists('helpdesk_is_forwarded_to_fm')) {
    function helpdesk_is_forwarded_to_fm(?array $complaint): bool
    {
        if (! $complaint) {
            return false;
        }
        if (! empty($complaint['forwarded_to_fm'])) {
            return true;
        }

        return ($complaint['status'] ?? '') === 'forwarded_to_fm';
    }
}

if (! function_exists('helpdesk_can_verify')) {
    function helpdesk_can_verify(string $role, ?array $complaint): bool
    {
        if (! in_array($role, ['super_admin', 'facility_manager', 'property_manager', 'real_estate_manager', 'supervisor', 'salesman'], true)) {
            return false;
        }
        if (helpdesk_is_non_facility($complaint)) {
            return in_array($role, ['super_admin', 'salesman', 'supervisor'], true);
        }

        return in_array($role, ['super_admin', 'facility_manager', 'property_manager', 'real_estate_manager', 'supervisor'], true);
    }
}

if (! function_exists('helpdesk_can_approve')) {
    function helpdesk_can_approve(string $role, ?array $complaint): bool
    {
        if (helpdesk_is_non_facility($complaint)) {
            return in_array($role, ['super_admin', 'facility_manager', 'finance_manager'], true);
        }

        // Facility: RE / Property Manager approve, then pass to Facility Manager for WO.
        return in_array($role, ['super_admin', 'property_manager', 'real_estate_manager'], true);
    }
}

if (! function_exists('helpdesk_can_forward_to_fm')) {
    /**
     * Facility work: RE / Property Manager passes approved complaint to Facility Manager for WO creation.
     */
    function helpdesk_can_forward_to_fm(string $role, ?array $complaint): bool
    {
        if (! $complaint || helpdesk_is_non_facility($complaint)) {
            return false;
        }
        if (($complaint['status'] ?? '') === 'converted' || helpdesk_is_forwarded_to_fm($complaint)) {
            return false;
        }
        if (($complaint['approval_status'] ?? '') !== 'approved') {
            return false;
        }

        return in_array($role, ['super_admin', 'property_manager', 'real_estate_manager'], true);
    }
}

if (! function_exists('helpdesk_can_convert_to_wo')) {
    /**
     * Only Facility Manager (and super admin) creates work orders and assigns supervisors.
     * Facility complaints must be forwarded from RE/Property Manager first.
     */
    function helpdesk_can_convert_to_wo(string $role, ?array $complaint, ?array $user = null): bool
    {
        if (! $complaint || ($complaint['status'] ?? '') === 'converted') {
            return false;
        }
        if (($complaint['approval_status'] ?? '') !== 'approved') {
            return false;
        }

        if (! in_array($role, ['super_admin', 'facility_manager'], true)) {
            return false;
        }

        if (helpdesk_is_non_facility($complaint)) {
            return true;
        }

        if ($role === 'super_admin') {
            return true;
        }

        return helpdesk_is_forwarded_to_fm($complaint);
    }
}

if (! function_exists('helpdesk_map_wo_category')) {
    function helpdesk_map_wo_category(string $category): string
    {
        $key = strtolower(str_replace([' ', '-'], '_', trim($category)));
        $allowed = ['electrical', 'hvac', 'plumbing', 'cleaning', 'civil', 'it', 'fire_safety', 'security', 'other'];

        return in_array($key, $allowed, true) ? $key : 'other';
    }
}

if (! function_exists('helpdesk_format_actor')) {
    function helpdesk_format_actor(?string $name, ?string $roleLabel = null): string
    {
        $name = trim((string) $name);
        if ($name === '') {
            return '—';
        }
        if ($roleLabel) {
            return $name . ' (' . $roleLabel . ')';
        }

        return $name;
    }
}

if (! function_exists('helpdesk_workflow_timeline')) {
    /**
     * Approval cycle steps with who completed each stage and who is next.
     *
     * @return list<array{key:string,label:string,state:string,by:?string,at:?string,next:?string}>
     */
    function helpdesk_workflow_timeline(?array $complaint): array
    {
        if (! $complaint) {
            return [];
        }

        $isNonFacility = helpdesk_is_non_facility($complaint);
        $rejected      = ($complaint['approval_status'] ?? '') === 'rejected' || ($complaint['status'] ?? '') === 'rejected';
        $converted     = ($complaint['status'] ?? '') === 'converted';
        $verified      = ! empty($complaint['verified_at']);
        $approved      = ($complaint['approval_status'] ?? '') === 'approved';
        $forwarded     = helpdesk_is_forwarded_to_fm($complaint);

        if ($isNonFacility) {
            $defs = [
                ['key' => 'received', 'label' => 'Complaint received', 'next' => 'Salesman / Supervisor'],
                ['key' => 'verify', 'label' => 'Verification', 'next' => 'Facility Manager'],
                ['key' => 'approve', 'label' => 'Facility Manager approval', 'next' => 'Facility Manager'],
                ['key' => 'wo', 'label' => 'Work order & supervisor', 'next' => 'Facility Manager'],
            ];
            $activeKey = 'received';
            if ($converted) {
                $activeKey = 'done';
            } elseif ($rejected) {
                $activeKey = $verified ? 'approve' : 'verify';
            } elseif ($approved) {
                $activeKey = 'wo';
            } elseif ($verified) {
                $activeKey = 'approve';
            } else {
                $activeKey = 'verify';
            }
        } else {
            $defs = [
                ['key' => 'received', 'label' => 'Complaint received', 'next' => 'Real Estate / Property Manager'],
                ['key' => 'verify', 'label' => 'Verification', 'next' => 'Real Estate / Property Manager'],
                ['key' => 'approve', 'label' => 'Approval', 'next' => 'Real Estate / Property Manager'],
                ['key' => 'forward', 'label' => 'Pass to Facility Manager', 'next' => 'Real Estate / Property Manager'],
                ['key' => 'wo', 'label' => 'Work order & supervisor', 'next' => 'Facility Manager'],
            ];
            $activeKey = 'received';
            if ($converted) {
                $activeKey = 'done';
            } elseif ($rejected) {
                if (! $verified) {
                    $activeKey = 'verify';
                } elseif (! $approved) {
                    $activeKey = 'approve';
                } else {
                    $activeKey = 'forward';
                }
            } elseif ($forwarded) {
                $activeKey = 'wo';
            } elseif ($approved) {
                $activeKey = 'forward';
            } elseif ($verified) {
                $activeKey = 'approve';
            } else {
                $activeKey = 'verify';
            }
        }

        $order    = array_column($defs, 'key');
        $activeIdx = $activeKey === 'done' ? count($order) : array_search($activeKey, $order, true);
        $steps    = [];

        foreach ($defs as $def) {
            $key = $def['key'];
            $idx = array_search($key, $order, true);
            $state = 'pending';
            if ($activeKey === 'done' || ($activeIdx !== false && $idx !== false && $idx < $activeIdx)) {
                $state = 'done';
            } elseif ($key === $activeKey) {
                $state = $rejected ? 'rejected' : 'active';
            }

            $by  = null;
            $at  = null;
            $next = ($state === 'active' && ! $rejected) ? $def['next'] : null;

            switch ($key) {
                case 'received':
                    $at = $complaint['created_at'] ?? null;
                    break;
                case 'verify':
                    if ($verified) {
                        $by = helpdesk_format_actor($complaint['verified_by_name'] ?? null, $complaint['verified_by_role'] ?? null);
                        $at = $complaint['verified_at'] ?? null;
                    }
                    break;
                case 'approve':
                    if ($approved) {
                        $by = helpdesk_format_actor($complaint['approved_by_name'] ?? null, $complaint['approved_by_role'] ?? null);
                        $at = $complaint['approved_at'] ?? null;
                    }
                    break;
                case 'forward':
                    if ($forwarded) {
                        $by = helpdesk_format_actor($complaint['forwarded_by_name'] ?? null, $complaint['forwarded_by_role'] ?? null);
                        $at = $complaint['forwarded_at'] ?? null;
                    }
                    break;
                case 'wo':
                    if ($converted) {
                        $by = $complaint['converted_wo_number'] ?? 'Work order created';
                        $next = null;
                    }
                    break;
            }

            $steps[] = [
                'key'   => $key,
                'label' => $def['label'],
                'state' => $state,
                'by'    => $by,
                'at'    => $at,
                'next'  => $next,
            ];
        }

        return $steps;
    }
}

if (! function_exists('helpdesk_list_stage_label')) {
    function helpdesk_list_stage_label(array $row): string
    {
        if (($row['status'] ?? '') === 'converted') {
            return 'Work order created';
        }
        if (($row['approval_status'] ?? '') === 'rejected' || ($row['status'] ?? '') === 'rejected') {
            return 'Rejected';
        }
        if (helpdesk_is_forwarded_to_fm($row)) {
            return 'With FM — create WO';
        }
        if (($row['approval_status'] ?? '') === 'approved') {
            $isFacility = ($row['work_type'] ?? '') === 'facility' || ! empty($row['facility_id']);

            return $isFacility ? 'Approved — pass to FM' : 'Approved — FM creates WO';
        }
        if (! empty($row['verified_at'])) {
            return 'Verified — awaiting approval';
        }

        return 'Received — awaiting verification';
    }
}
