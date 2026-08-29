<?php

namespace App\Controllers;

use App\Services\MaintenanceScopeQuery;

/**
 * Authenticated maintenance request list (PM read-only history, FM overview).
 * Separate from Helpdesk workflow and from the public entity maintenance form.
 */
class MaintenanceList extends BaseController
{
    public function index()
    {
        $user    = $this->currentUser();
        $filters = $this->request->getGet();
        $perPage = 20;
        $page    = max(1, (int) ($filters['page'] ?? 1));
        $offset  = ($page - 1) * $perPage;

        $readOnly = $this->isPmWorkspace();

        $total    = MaintenanceScopeQuery::countForUser($this->db, $user, $filters);
        $requests = MaintenanceScopeQuery::listForUser($this->db, $user, $filters, $perPage, $offset);

        return view('helpdesk/index', $this->viewData([
            'title'       => 'Maintenance',
            'pageTitle'   => $readOnly ? 'Maintenance — History (Read-only)' : 'Maintenance Requests',
            'requests'    => $requests,
            'filters'     => $filters,
            'total'       => $total,
            'perPage'     => $perPage,
            'currentPage' => $page,
            'readOnly'    => $readOnly,
            'listUrl'     => base_url('maintenance/list'),
            'resetUrl'    => base_url('maintenance/list'),
            'detailPath'  => 'helpdesk/view/',
        ]));
    }
}
