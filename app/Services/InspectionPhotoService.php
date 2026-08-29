<?php

namespace App\Services;

use CodeIgniter\HTTP\Files\UploadedFile;

class InspectionPhotoService
{
    public const UPLOAD_DIR = 'uploads/inspections';

    public static function storeUpload(?UploadedFile $file): ?string
    {
        if (! $file || ! $file->isValid() || $file->hasMoved()) {
            return null;
        }

        $dir = FCPATH . self::UPLOAD_DIR;
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $name = $file->getRandomName();
        $file->move($dir, $name);

        return self::UPLOAD_DIR . '/' . $name;
    }

    /** @return list<string> */
    public static function pathsFromJson(?string $json): array
    {
        $decoded = json_decode($json ?? '[]', true);

        if (! is_array($decoded)) {
            return [];
        }

        $paths = [];
        foreach ($decoded as $path) {
            if (is_string($path) && $path !== '') {
                $paths[] = $path;
            }
        }

        return $paths;
    }

    /** @param list<string> $newPaths */
    public static function mergePhotosJson(?string $existingJson, array $newPaths): string
    {
        $merged = array_values(array_unique(array_merge(self::pathsFromJson($existingJson), $newPaths)));

        return json_encode($merged);
    }

    /**
     * @param array<string, UploadedFile|list<UploadedFile>|null> $fileMap keyed by item id
     * @return array<string, list<string>> paths per item id
     */
    public static function collectUploadsFromMap(array $fileMap): array
    {
        $out = [];

        foreach ($fileMap as $itemId => $entry) {
            if ($entry === null) {
                continue;
            }

            $files = is_array($entry) ? $entry : [$entry];
            foreach ($files as $file) {
                if (! $file instanceof UploadedFile) {
                    continue;
                }
                $path = self::storeUpload($file);
                if ($path !== null) {
                    $out[(string) $itemId][] = $path;
                }
            }
        }

        return $out;
    }
}
