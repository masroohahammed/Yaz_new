<?php

namespace App\Services;

/**
 * Lightweight CSV + XLSX (first sheet) reader without external dependencies.
 */
class SpreadsheetImportService
{
    /**
     * @return list<array<string, string>>
     */
    public function rowsFromUpload(?\CodeIgniter\HTTP\Files\UploadedFile $file): array
    {
        if (! $file || ! $file->isValid()) {
            return [];
        }

        $ext = strtolower($file->getExtension());
        $path = $file->getTempName();

        if (in_array($ext, ['xlsx', 'xlsm'], true)) {
            return $this->rowsFromXlsx($path);
        }

        return $this->rowsFromCsv($path);
    }

    /** @return list<array<string, string>> */
    public function rowsFromCsv(string $path): array
    {
        $content = file_get_contents($path);
        if ($content === false) {
            return [];
        }

        $lines   = explode("\n", str_replace("\r", '', $content));
        $headers = null;
        $rows    = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            $cols = str_getcsv($line);
            if ($headers === null) {
                $headers = array_map(fn ($h) => $this->normalizeHeader((string) $h), $cols);
                continue;
            }
            $row = array_combine($headers, array_pad($cols, count($headers), ''));
            if ($row !== false) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    /** @return list<array<string, string>> */
    public function rowsFromXlsx(string $path): array
    {
        if (! class_exists('ZipArchive')) {
            return [];
        }

        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            return [];
        }

        $shared = [];
        $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedXml) {
            $sx = @simplexml_load_string($sharedXml);
            if ($sx) {
                foreach ($sx->si as $si) {
                    if (isset($si->t)) {
                        $shared[] = (string) $si->t;
                    } elseif (isset($si->r)) {
                        $parts = [];
                        foreach ($si->r as $r) {
                            $parts[] = (string) ($r->t ?? '');
                        }
                        $shared[] = implode('', $parts);
                    } else {
                        $shared[] = '';
                    }
                }
            }
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();
        if (! $sheetXml) {
            return [];
        }

        $sheet = @simplexml_load_string($sheetXml);
        if (! $sheet || ! isset($sheet->sheetData->row)) {
            return [];
        }

        $grid    = [];
        $maxCol  = 0;
        foreach ($sheet->sheetData->row as $row) {
            $rIdx = (int) ($row['r'] ?? 0);
            foreach ($row->c as $cell) {
                $ref  = (string) ($cell['r'] ?? '');
                $col  = preg_replace('/\d+/', '', $ref) ?? 'A';
                $colI = $this->columnIndex($col);
                $maxCol = max($maxCol, $colI);
                $type = (string) ($cell['t'] ?? '');
                $val  = '';
                if ($type === 's') {
                    $idx = (int) ($cell->v ?? 0);
                    $val = $shared[$idx] ?? '';
                } elseif (isset($cell->v)) {
                    $val = (string) $cell->v;
                } elseif (isset($cell->is->t)) {
                    $val = (string) $cell->is->t;
                }
                $grid[$rIdx][$colI] = trim($val);
            }
        }

        if ($grid === []) {
            return [];
        }

        ksort($grid);
        $matrix = array_values($grid);
        $headers = [];
        foreach ($matrix[0] as $colI => $val) {
            $headers[$colI] = $this->normalizeHeader($val !== '' ? $val : 'col_' . $colI);
        }

        $rows = [];
        for ($i = 1, $c = count($matrix); $i < $c; $i++) {
            $assoc = [];
            foreach ($headers as $colI => $header) {
                $assoc[$header] = (string) ($matrix[$i][$colI] ?? '');
            }
            if (implode('', $assoc) !== '') {
                $rows[] = $assoc;
            }
        }

        return $rows;
    }

    private function normalizeHeader(string $header): string
    {
        $h = strtolower(trim($header));
        $h = str_replace([' ', '-'], '_', $h);

        return $h;
    }

    private function columnIndex(string $letters): int
    {
        $letters = strtoupper($letters);
        $n       = 0;
        for ($i = 0, $len = strlen($letters); $i < $len; $i++) {
            $n = $n * 26 + (ord($letters[$i]) - 64);
        }

        return $n;
    }
}
