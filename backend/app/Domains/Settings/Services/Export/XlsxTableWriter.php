<?php

namespace App\Domains\Settings\Services\Export;

use RuntimeException;
use ZipArchive;

/**
 * A minimal Office Open XML workbook (one sheet, inline strings, bold header row), written
 * without a spreadsheet package (no dependency change, M14). Rows are streamed to a temporary
 * sheet file and zipped on close.
 */
class XlsxTableWriter implements TableWriter
{
    private string $path = '';

    private string $sheetPath = '';

    /** @var resource|null */
    private $sheet = null;

    private int $rowNumber = 0;

    public function open(string $path): void
    {
        $this->path = $path;
        $this->sheetPath = $path.'.sheet.xml';
        $sheet = fopen($this->sheetPath, 'wb');

        if ($sheet === false) {
            throw new RuntimeException('The export file could not be opened.');
        }

        $this->sheet = $sheet;
        $this->rowNumber = 0;
        fwrite($sheet, '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>');
    }

    public function row(array $cells): void
    {
        $this->rowNumber++;
        $style = $this->rowNumber === 1 ? ' s="1"' : '';
        $xml = '<row r="'.$this->rowNumber.'">';

        foreach (array_values($cells) as $index => $value) {
            $text = htmlspecialchars(FormulaGuard::clean($this->xmlSafe($value)), ENT_XML1 | ENT_QUOTES, 'UTF-8');
            $xml .= '<c r="'.$this->column($index).$this->rowNumber.'" t="inlineStr"'.$style.'><is><t xml:space="preserve">'.$text.'</t></is></c>';
        }

        fwrite($this->sheet, $xml.'</row>');
    }

    public function close(): void
    {
        if ($this->sheet === null) {
            return;
        }

        fwrite($this->sheet, '</sheetData></worksheet>');
        fclose($this->sheet);
        $this->sheet = null;

        $zip = new ZipArchive;

        if ($zip->open($this->path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('The export file could not be written.');
        }

        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            .'<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            .'<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            .'</Types>');
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            .'</Relationships>');
        $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<sheets><sheet name="Export" sheetId="1" r:id="rId1"/></sheets></workbook>');
        $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            .'<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            .'</Relationships>');
        $zip->addFromString('xl/styles.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="11"/><name val="Calibri"/></font></fonts>'
            .'<fills count="2"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill></fills>'
            .'<borders count="1"><border/></borders>'
            .'<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            .'<cellXfs count="2"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"/></cellXfs>'
            .'</styleSheet>');
        $zip->addFile($this->sheetPath, 'xl/worksheets/sheet1.xml');
        $zip->close();

        @unlink($this->sheetPath);
    }

    public function extension(): string
    {
        return 'xlsx';
    }

    public function mimeType(): string
    {
        return 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
    }

    private function column(int $index): string
    {
        $name = '';

        for ($index++; $index > 0; $index = intdiv($index - 1, 26)) {
            $name = chr(65 + (($index - 1) % 26)).$name;
        }

        return $name;
    }

    /**
     * Drop characters XML 1.0 cannot carry.
     */
    private function xmlSafe(string $value): string
    {
        return (string) preg_replace('/[^\x{9}\x{A}\x{D}\x{20}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u', '', $value);
    }
}
