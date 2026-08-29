<?php

namespace App\Services;

/**
 * Save canvas signature data URLs to writable/uploads/signatures/.
 */
class SignatureStorageService
{
    public function storeFromPost(?string $dataUrl, string $prefix = 'sig'): ?string
    {
        if ($dataUrl === null || $dataUrl === '') {
            return null;
        }
        if (! preg_match('#^data:image/(png|jpeg);base64,#i', $dataUrl, $m)) {
            return null;
        }
        $raw = base64_decode(substr($dataUrl, strpos($dataUrl, ',') + 1), true);
        if ($raw === false || strlen($raw) < 50) {
            return null;
        }
        $ext  = strtolower($m[1]) === 'jpeg' ? 'jpg' : 'png';
        $dir  = WRITEPATH . 'uploads/signatures';
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $name = $prefix . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $path = $dir . '/' . $name;
        if (! file_put_contents($path, $raw)) {
            return null;
        }

        return 'uploads/signatures/' . $name;
    }
}
