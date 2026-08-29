<?php
namespace App\Controllers;

/**
 * FileServe — serves files stored in WRITEPATH/uploads/ securely.
 * Replaces direct public access to uploaded files.
 *
 * Route: GET file/(:segment)/(:any)  → FileServe::serve/$1/$2
 * Example: /file/workorders/abc123.pdf
 */
class FileServe extends BaseController
{
    private const MIME_MAP = [
        'pdf'  => 'application/pdf',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'gif'  => 'image/gif',
        'webp' => 'image/webp',
        'svg'  => 'image/svg+xml',
        'doc'  => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls'  => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ];

    /**
     * Serve a file from WRITEPATH/uploads/{subDir}/{filename}
     * @param string $subDir  e.g. "workorders", "receipts", "logos"
     * @param string $filename
     */
    public function serve(string $subDir, string $filename): \CodeIgniter\HTTP\Response
    {
        // Branding logos are shown on the public login page — no session required.
        $subDirNormalized = basename($subDir);
        if ($subDirNormalized !== 'logos' && ! session()->get('user_id')) {
            return $this->response->setStatusCode(403)->setBody('Authentication required.');
        }

        // Strip path traversal attempts
        $subDir   = basename($subDir);
        $filename = basename($filename);

        $path = WRITEPATH . 'uploads/' . $subDir . '/' . $filename;

        // Security: verify path is inside uploads directory
        $realPath    = realpath($path);
        $uploadsRoot = realpath(WRITEPATH . 'uploads/');

        if (!$realPath || !$uploadsRoot || strpos($realPath, $uploadsRoot) !== 0) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if (!file_exists($realPath)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Role-based access for sensitive uploads
        $role = (string) session()->get('user_role');
        $financeRoles = ['super_admin', 'facility_manager', 'finance_manager', 'finance_user', 'property_manager'];
        if (in_array($subDir, ['receipts', 'invoices', 'documents'], true) && ! in_array($role, $financeRoles, true)) {
            return $this->response->setStatusCode(403)->setBody('Access denied.');
        }

        $ext  = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $mime = self::MIME_MAP[$ext] ?? mime_content_type($realPath) ?: 'application/octet-stream';

        // Decide inline vs download
        $inline = in_array($mime, ['image/jpeg','image/png','image/gif','image/webp','image/svg+xml','application/pdf']);
        $cacheHdr = ($subDir === 'logos') ? 'public, max-age=86400, immutable' : 'private, max-age=3600';

        return $this->response
            ->setHeader('Content-Type', $mime)
            ->setHeader('Content-Length', (string)filesize($realPath))
            ->setHeader('Content-Disposition', ($inline ? 'inline' : 'attachment') . '; filename="' . $filename . '"')
            ->setHeader('Cache-Control', $cacheHdr)
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setBody(file_get_contents($realPath));
    }
    /**
     * Legacy logos stored directly in writable/uploads/{filename}
     */
    public function logo(string $filename): \CodeIgniter\HTTP\Response
    {
        $filename = basename($filename);
        $ext      = strtolower((string) pathinfo($filename, PATHINFO_EXTENSION));
        if (! in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'], true)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        $path     = WRITEPATH . 'uploads/' . $filename;
        $realPath = realpath($path);
        $uploadsRoot = realpath(WRITEPATH . 'uploads/');

        if (! $realPath || ! $uploadsRoot || strpos($realPath, $uploadsRoot) !== 0 || ! is_file($realPath)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $ext  = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $mime = self::MIME_MAP[$ext] ?? mime_content_type($realPath) ?: 'application/octet-stream';

        return $this->response
            ->setHeader('Content-Type', $mime)
            ->setHeader('Content-Length', (string) filesize($realPath))
            ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
            ->setHeader('Cache-Control', 'private, max-age=3600')
            ->setBody(file_get_contents($realPath));
    }

}
