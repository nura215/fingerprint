<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use RuntimeException;
use SimpleXMLElement;
use ZipArchive;

class SpreadsheetImportReader
{
    /**
     * @return array<int, array<string, string|null>>
     */
    public function read(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());

        return match ($extension) {
            'csv', 'txt' => $this->readCsv($file->getRealPath()),
            'xlsx' => $this->readXlsx($file->getRealPath()),
            default => throw new RuntimeException('Format file harus .xlsx atau .csv.'),
        };
    }

    /**
     * @return array<int, array<string, string|null>>
     */
    private function readCsv(string $path): array
    {
        $handle = fopen($path, 'r');

        if ($handle === false) {
            throw new RuntimeException('File tidak bisa dibaca.');
        }

        $headers = null;
        $rows = [];

        while (($data = fgetcsv($handle, 0, ',')) !== false) {
            if ($headers === null) {
                $headers = $this->normalizeHeaders($data);
                continue;
            }

            $rows[] = $this->combineRow($headers, $data);
        }

        fclose($handle);

        return $this->removeEmptyRows($rows);
    }

    /**
     * @return array<int, array<string, string|null>>
     */
    private function readXlsx(string $path): array
    {
        $zip = new ZipArchive();

        if ($zip->open($path) !== true) {
            throw new RuntimeException('File Excel tidak bisa dibuka.');
        }

        $sharedStrings = $this->readSharedStrings($zip);
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if ($sheetXml === false) {
            throw new RuntimeException('Sheet pertama tidak ditemukan.');
        }

        $sheet = new SimpleXMLElement($sheetXml);
        $rawRows = [];

        foreach ($sheet->sheetData->row as $row) {
            $cells = [];

            foreach ($row->c as $cell) {
                $reference = (string) $cell['r'];
                $column = preg_replace('/\d+/', '', $reference);
                $index = $this->columnIndex($column);
                $type = (string) $cell['t'];
                $value = isset($cell->v) ? (string) $cell->v : '';

                if ($type === 's') {
                    $value = $sharedStrings[(int) $value] ?? '';
                } elseif ($type === 'inlineStr') {
                    $value = (string) ($cell->is->t ?? '');
                }

                $cells[$index] = trim($value);
            }

            if ($cells !== []) {
                ksort($cells);
                $rawRows[] = $cells;
            }
        }

        if ($rawRows === []) {
            return [];
        }

        $maxIndex = max(array_keys($rawRows[0]));
        $headers = $this->normalizeHeaders($this->expandRow($rawRows[0], $maxIndex));
        $rows = [];

        foreach (array_slice($rawRows, 1) as $row) {
            $rows[] = $this->combineRow($headers, $this->expandRow($row, $maxIndex));
        }

        return $this->removeEmptyRows($rows);
    }

    /**
     * @return array<int, string>
     */
    private function readSharedStrings(ZipArchive $zip): array
    {
        $xml = $zip->getFromName('xl/sharedStrings.xml');

        if ($xml === false) {
            return [];
        }

        $shared = new SimpleXMLElement($xml);
        $strings = [];

        foreach ($shared->si as $item) {
            if (isset($item->t)) {
                $strings[] = (string) $item->t;
                continue;
            }

            $text = '';
            foreach ($item->r as $run) {
                $text .= (string) $run->t;
            }
            $strings[] = $text;
        }

        return $strings;
    }

    /**
     * @param array<int, string|null> $headers
     * @return array<int, string>
     */
    private function normalizeHeaders(array $headers): array
    {
        return array_map(fn ($header) => str((string) $header)->lower()->trim()->replace([' ', '-'], '_')->toString(), $headers);
    }

    /**
     * @param array<int, string> $headers
     * @param array<int, string|null> $data
     * @return array<string, string|null>
     */
    private function combineRow(array $headers, array $data): array
    {
        $row = [];

        foreach ($headers as $index => $header) {
            if ($header === '') {
                continue;
            }

            $value = $data[$index] ?? null;
            $row[$header] = is_string($value) ? trim($value) : $value;
        }

        return $row;
    }

    /**
     * @param array<int, array<string, string|null>> $rows
     * @return array<int, array<string, string|null>>
     */
    private function removeEmptyRows(array $rows): array
    {
        return array_values(array_filter($rows, fn (array $row) => collect($row)->filter(fn ($value) => filled($value))->isNotEmpty()));
    }

    /**
     * @param array<int, string|null> $row
     * @return array<int, string|null>
     */
    private function expandRow(array $row, int $maxIndex): array
    {
        $expanded = [];

        for ($index = 0; $index <= $maxIndex; $index++) {
            $expanded[] = $row[$index] ?? null;
        }

        return $expanded;
    }

    private function columnIndex(string $column): int
    {
        $index = 0;

        foreach (str_split($column) as $letter) {
            $index = ($index * 26) + (ord(strtoupper($letter)) - 64);
        }

        return $index - 1;
    }
}
