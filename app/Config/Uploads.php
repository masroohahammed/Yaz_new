<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Upload directory conventions (public web root).
 */
class Uploads extends BaseConfig
{
    /** Relative to FCPATH, e.g. public/uploads */
    public string $publicRoot = 'uploads';

    public function publicPath(string $subdir = ''): string
    {
        $root = rtrim(FCPATH . trim($this->publicRoot, '/'), DIRECTORY_SEPARATOR);
        if ($subdir === '') {
            return $root . DIRECTORY_SEPARATOR;
        }

        return $root . DIRECTORY_SEPARATOR . trim($subdir, '/') . DIRECTORY_SEPARATOR;
    }

    public function __construct()
    {
        parent::__construct();
        $this->publicRoot = (string) env('uploads.publicRoot', $this->publicRoot);
    }
}
