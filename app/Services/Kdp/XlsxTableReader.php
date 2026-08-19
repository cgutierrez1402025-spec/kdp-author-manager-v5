<?php

namespace App\Services\Kdp;

use RuntimeException;
use SimpleXMLElement;
use ZipArchive;

class XlsxTableReader
{
    /** @return array<string, array<int, array<int, string|null>>> */
    public function read(string $path): array
    {
        $zip = new ZipArchive;

        if ($zip->open($path) !== true) {
            throw new RuntimeException('No se pudo abrir el archivo XLSX.');
        }

        try {
            $sharedStrings = $this->sharedStrings($zip);
            $relationships = $this->relationships($zip);
            $workbook = $this->xml($zip, 'xl/workbook.xml');
            $workbook->registerXPathNamespace('m', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
            $workbook->registerXPathNamespace('r', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships');
            $tables = [];

            foreach ($workbook->xpath('//m:sheets/m:sheet') ?: [] as $sheet) {
                $attributes = $sheet->attributes();
                $relationshipAttributes = $sheet->attributes('r', true);
                $name = (string) $attributes['name'];
                $relationshipId = (string) $relationshipAttributes['id'];
                $target = $relationships[$relationshipId] ?? null;

                if (! $target) {
                    continue;
                }

                $target = str_starts_with($target, '/')
                    ? ltrim($target, '/')
                    : 'xl/'.ltrim(preg_replace('#^\.\./#', '', $target), '/');
                $tables[$name] = $this->worksheet($zip, $target, $sharedStrings);
            }

            return $tables;
        } finally {
            $zip->close();
        }
    }

    /** @return array<int, string> */
    private function sharedStrings(ZipArchive $zip): array
    {
        if ($zip->locateName('xl/sharedStrings.xml') === false) {
            return [];
        }

        $xml = $this->xml($zip, 'xl/sharedStrings.xml');
        $xml->registerXPathNamespace('m', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

        return array_map(function (SimpleXMLElement $item): string {
            $item->registerXPathNamespace('m', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

            return implode('', array_map('strval', $item->xpath('.//m:t') ?: []));
        }, $xml->xpath('//m:si') ?: []);
    }

    /** @return array<string, string> */
    private function relationships(ZipArchive $zip): array
    {
        $xml = $this->xml($zip, 'xl/_rels/workbook.xml.rels');
        $xml->registerXPathNamespace('r', 'http://schemas.openxmlformats.org/package/2006/relationships');
        $result = [];

        foreach ($xml->xpath('//r:Relationship') ?: [] as $relationship) {
            $attributes = $relationship->attributes();
            $result[(string) $attributes['Id']] = (string) $attributes['Target'];
        }

        return $result;
    }

    /** @param array<int, string> $sharedStrings
     * @return array<int, array<int, string|null>>
     */
    private function worksheet(ZipArchive $zip, string $target, array $sharedStrings): array
    {
        $xml = $this->xml($zip, $target);
        $xml->registerXPathNamespace('m', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $rows = [];

        foreach ($xml->xpath('//m:sheetData/m:row') ?: [] as $row) {
            $row->registerXPathNamespace('m', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
            $values = [];

            foreach ($row->xpath('./m:c') ?: [] as $cell) {
                $cell->registerXPathNamespace('m', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
                $attributes = $cell->attributes();
                preg_match('/^[A-Z]+/', (string) $attributes['r'], $match);
                $column = $this->columnIndex($match[0] ?? 'A');
                $type = (string) ($attributes['t'] ?? '');
                $raw = (string) (($cell->xpath('./m:v')[0] ?? null));

                if ($type === 's') {
                    $value = $sharedStrings[(int) $raw] ?? '';
                } elseif ($type === 'inlineStr') {
                    $value = implode('', array_map('strval', $cell->xpath('.//m:t') ?: []));
                } else {
                    $value = $raw;
                }

                $values[$column] = $value;
            }

            if ($values !== []) {
                $width = max(array_keys($values));
                $rows[] = array_replace(array_fill(0, $width + 1, null), $values);
            }
        }

        return $rows;
    }

    private function columnIndex(string $letters): int
    {
        $index = 0;
        foreach (str_split($letters) as $letter) {
            $index = ($index * 26) + ord($letter) - 64;
        }

        return $index - 1;
    }

    private function xml(ZipArchive $zip, string $path): SimpleXMLElement
    {
        $content = $zip->getFromName($path);
        if ($content === false) {
            throw new RuntimeException("El XLSX no contiene {$path}.");
        }

        $xml = simplexml_load_string($content);
        if (! $xml) {
            throw new RuntimeException("El XML {$path} no es válido.");
        }

        return $xml;
    }
}
