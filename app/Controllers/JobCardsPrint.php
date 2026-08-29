<?php

namespace App\Controllers;

use App\Models\JobCardModel;

/**
 * JobCardsPrint — printCard() action to add inside JobCards controller.
 *
 * INTEGRATION: copy the printCard() method below into your existing
 * JobCards controller class. It is provided as a separate file only
 * to make the diff clear.
 *
 * Route to add in Routes.php:
 *   $routes->get('/job-cards/(:num)/print', 'JobCards::printCard/$1');
 */
class JobCardsPrint extends BaseController
{
    /**
     * GET /job-cards/{id}/print
     *
     * Renders a self-contained, print-optimised HTML page.
     * The user can press "Print / Save as PDF" in the browser.
     *
     * Optional: append ?auto=1 to trigger the print dialog automatically.
     *
     * DOMPDF integration:
     *   If composer package dompdf/dompdf is installed, append ?pdf=1
     *   to force a server-side PDF download instead of the HTML print page.
     */
    public function printCard(int $id)
    {
        $jcModel = new JobCardModel();
        $jc      = $jcModel->getDetail($id);

        if (! $jc) {
            return redirect()->to('/job-cards')->with('error', 'Job card not found.');
        }

        // Role-based access: same rules as show()
        $user = $this->currentUser();
        $role = $user['role_name'];
        if (! in_array($role, ['super_admin', 'facility_manager'])) {
            if ($role === 'supervisor' && (int) $jc['supervisor_id'] !== (int) $user['id']) {
                return redirect()->to('/job-cards')->with('error', 'Access denied.');
            }
            if ($role === 'technician' && (int) $jc['assigned_to'] !== (int) $user['id']) {
                return redirect()->to('/job-cards')->with('error', 'Access denied.');
            }
        }

        // Fetch related data
        $materials = $jcModel->getMaterialsForCard($id);

        // Company / branding from system_settings
        $settings     = $this->settings;
        $companyName  = $settings['company_name']    ?? 'FM ERP';
        $companyLogo  = $settings['company_logo']    ?? null;
        $companyAddr  = $settings['company_address'] ?? null;
        $companyPhone = $settings['company_phone']   ?? null;
        $companyEmail = $settings['company_email']   ?? null;
        $primaryColor = $settings['primary_color']   ?? '#76002b';
        $currency     = $settings['currency']        ?? 'QAR';
        $laborRate    = (float) ($settings['default_labor_rate'] ?? 0);

        $data = [
            'jc'           => $jc,
            'materials'    => $materials,
            'companyName'  => $companyName,
            'companyLogo'  => $companyLogo,
            'companyAddress'=> $companyAddr,
            'companyPhone' => $companyPhone,
            'companyEmail' => $companyEmail,
            'primaryColor' => $primaryColor,
            'currency'     => $currency,
            'laborRate'    => $laborRate,
            'autoPrint'    => (bool) $this->request->getGet('auto'),
        ];

        // -------------------------------------------------------
        // Optional: server-side PDF via DOMPDF (?pdf=1)
        // Requires: composer require dompdf/dompdf
        // -------------------------------------------------------
        if ($this->request->getGet('pdf') && class_exists(\Dompdf\Dompdf::class)) {
            return $this->renderDompdf($data);
        }

        // Default: return the HTML print view (browser prints to PDF)
        // Bypass the main layout — print view is fully self-contained
        return view('job_cards/print', $data);
    }

    // ----------------------------------------------------------
    // DOMPDF server-side render (only called when dompdf installed)
    // ----------------------------------------------------------

    private function renderDompdf(array $data): \CodeIgniter\HTTP\ResponseInterface
    {
        $html = view('job_cards/print', $data);

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);   // allows base_url() images
        $options->set('defaultFont', 'Arial');
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'JobCard_' . $data['jc']['jc_number'] . '_' . date('Ymd') . '.pdf';

        return $this->response
                    ->setHeader('Content-Type', 'application/pdf')
                    ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
                    ->setBody($dompdf->output());
    }

    // ----------------------------------------------------------
    // Load all system_settings into a flat key→value array
    // ----------------------------------------------------------

}
