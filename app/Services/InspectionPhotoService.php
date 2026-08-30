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
    public static function normalizePhotoEntry(mixed $entry): array
    {
        if ($entry === null || $entry === '') {
            return [];
        }

        if (is_array($entry)) {
            $paths = [];
            foreach ($entry as $path) {
                if (is_string($path) && $path !== '') {
                    $paths[] = $path;
                }
            }

            return array_values(array_unique($paths));
        }

        if (is_string($entry)) {
            $trimmed = trim($entry);
            if ($trimmed === '') {
                return [];
            }
            if ($trimmed[0] === '[') {
                return self::pathsFromJson($trimmed);
            }

            return [$trimmed];
        }

        return [];
    }

    /**
     * Store all uploads for one checklist area index.
     *
     * @return list<string>
     */
    public static function storeAreaUploads(int $areaIndex): array
    {
        $paths = [];
        $key   = 'area_photos';

        if (! isset($_FILES[$key]['name'][$areaIndex])) {
            return $paths;
        }

        $names = $_FILES[$key]['name'][$areaIndex];
        $types = $_FILES[$key]['type'][$areaIndex];
        $tmps  = $_FILES[$key]['tmp_name'][$areaIndex];
        $errors = $_FILES[$key]['error'][$areaIndex];
        $sizes = $_FILES[$key]['size'][$areaIndex];

        if (! is_array($names)) {
            $names  = [$names];
            $types  = [$types];
            $tmps   = [$tmps];
            $errors = [$errors];
            $sizes  = [$sizes];
        }

        $dir = FCPATH . self::UPLOAD_DIR;
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        foreach ($names as $i => $originalName) {
            if ($originalName === '' || (int) ($errors[$i] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                continue;
            }

            $ext = pathinfo($originalName, PATHINFO_EXTENSION);
            $ext = $ext ? '.' . strtolower($ext) : '';
            $random = bin2hex(random_bytes(8)) . $ext;
            $target = $dir . DIRECTORY_SEPARATOR . $random;

            if (@move_uploaded_file($tmps[$i], $target)) {
                $paths[] = self::UPLOAD_DIR . '/' . $random;
            }
        }

        return $paths;
    }

    /** @param list<string> $paths */
    public static function encodePhotos(array $paths): string
    {
        return json_encode(array_values(array_unique(array_filter($paths))));
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
