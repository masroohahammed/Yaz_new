<?php

namespace App\Services;

use CodeIgniter\HTTP\Files\UploadedFile;

/**
 * Optional parking contract photos (up to 3) stored on lease_contracts.photos_json.
 */
class ParkingContractPhotoService
{
    public const UPLOAD_DIR = 'uploads/parking_contracts';

    public const MAX_PHOTOS = 3;

    public const MAX_BYTES = 5_242_880;

    /** @var list<string> */
    private const ALLOWED_MIMES = ['image/jpeg', 'image/png', 'image/webp'];

    /** @return list<string> */
    public static function pathsFromJson(?string $json): array
    {
        $decoded = json_decode($json ?? '[]', true);
        if (! is_array($decoded)) {
            return [];
        }

        $paths = [];
        foreach ($decoded as $path) {
            if (is_string($path) && trim($path) !== '') {
                $paths[] = trim($path);
            }
        }

        return array_values(array_unique($paths));
    }

    /** @param list<string> $paths */
    public static function encodePhotos(array $paths): string
    {
        $filtered = array_values(array_unique(array_filter($paths, static fn ($p) => is_string($p) && $p !== '')));

        return json_encode(array_slice($filtered, 0, self::MAX_PHOTOS)) ?: '[]';
    }

    /**
     * @param list<UploadedFile|null> $files
     * @return list<string>
     */
    public static function storeUploads(array $files): array
    {
        $paths = [];
        $dir   = WRITEPATH . 'uploads/parking_contracts';
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile || ! $file->isValid() || $file->hasMoved()) {
                continue;
            }
            if ($file->getSize() > self::MAX_BYTES) {
                continue;
            }
            $mime = (string) $file->getMimeType();
            if (! in_array($mime, self::ALLOWED_MIMES, true)) {
                continue;
            }

            $name = $file->getRandomName();
            $file->move($dir, $name);
            $paths[] = self::UPLOAD_DIR . '/' . $name;

            if (count($paths) >= self::MAX_PHOTOS) {
                break;
            }
        }

        return $paths;
    }

    /**
     * @param list<string> $kept
     * @param list<string> $new
     * @return list<string>
     */
    public static function mergePhotos(array $kept, array $new): array
    {
        return array_slice(array_values(array_unique(array_merge($kept, $new))), 0, self::MAX_PHOTOS);
    }

    public static function photoSrc(string $storedPath, bool $usePdf = false): string
    {
        helper('fm');

        return $usePdf ? fm_logo_data_uri($storedPath) : fm_logo_url($storedPath);
    }
}
