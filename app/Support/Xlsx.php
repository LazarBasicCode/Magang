<?php

namespace App\Support;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

/**
 * Helper XLSX umum (tulis & baca) tanpa dependensi tambahan — hanya butuh ekstensi PHP zip.
 *
 * Kenapa XLSX: tiap nilai ada di selnya sendiri, jadi tidak bergantung pada pemisah
 * ";" / "," dan pengaturan regional Excel/Google Sheets seperti pada CSV.
 *
 * Bentuk sheet untuk download():
 *  [
 *    'name'        => 'Data',
 *    'widths'      => [10, 20, ...],                 // lebar tiap kolom
 *    'rows'        => [ [cell, cell, ...], ... ],    // cell = string atau ['value'=>..., 'style'=>0|1|2|3]
 *    'validations' => [ ['range'=>'D2:D1001', 'list'=>['a','b'], 'error'=>'...'] ],
 *    'freeze'      => true,                          // bekukan baris judul
 *    'autoborder'  => ['cols'=>8,'from'=>2,'to'=>1001], // kotak otomatis untuk baris yang berisi
 *    'autofilter'  => true,
 *  ]
 * Style: 0 polos, 1 kotak + wrap, 2 judul tebal (biru muda), 3 judul besar tanpa kotak.
 */
class Xlsx
{
    public const STYLE_PLAIN = 0;
    public const STYLE_CELL = 1;
    public const STYLE_HEADER = 2;
    public const STYLE_TITLE = 3;

    private const NS_MAIN = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';
    private const NS_REL = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';

    // =====================================================================
    // TULIS
    // =====================================================================

    /** @param  array<int,array>  $sheets */
    public static function download(string $filename, array $sheets): BinaryFileResponse
    {
        if (!class_exists(ZipArchive::class)) {
            abort(500, 'Ekstensi PHP ZIP (ZipArchive) belum aktif. Aktifkan extension=zip pada php.ini.');
        }

        $tmp = tempnam(sys_get_temp_dir(), 'xlsx_');
        $zip = new ZipArchive();
        if ($zip->open($tmp, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            abort(500, 'Gagal membuat file Excel.');
        }

        $sheets = array_values($sheets);
        $head = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';

        $types = $head . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>';
        $wbSheets = '';
        $wbRels = '';
        foreach ($sheets as $i => $sheet) {
            $n = $i + 1;
            $types .= '<Override PartName="/xl/worksheets/sheet' . $n . '.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
            $wbSheets .= '<sheet name="' . self::esc($sheet['name']) . '" sheetId="' . $n . '" r:id="rId' . $n . '"/>';
            $wbRels .= '<Relationship Id="rId' . $n . '" Type="' . self::NS_REL . '/worksheet" Target="worksheets/sheet' . $n . '.xml"/>';
        }
        $types .= '</Types>';
        $styleRel = '<Relationship Id="rIdStyles" Type="' . self::NS_REL . '/styles" Target="styles.xml"/>';

        $zip->addFromString('[Content_Types].xml', $types);
        $zip->addFromString('_rels/.rels', $head . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="' . self::NS_REL . '/officeDocument" Target="xl/workbook.xml"/></Relationships>');
        $zip->addFromString('xl/workbook.xml', $head . '<workbook xmlns="' . self::NS_MAIN . '" xmlns:r="' . self::NS_REL . '"><sheets>' . $wbSheets . '</sheets></workbook>');
        $zip->addFromString('xl/_rels/workbook.xml.rels', $head . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' . $wbRels . $styleRel . '</Relationships>');
        $zip->addFromString('xl/styles.xml', $head . self::styles());
        foreach ($sheets as $i => $sheet) {
            $zip->addFromString('xl/worksheets/sheet' . ($i + 1) . '.xml', $head . self::sheetXml($sheet));
        }
        $zip->close();

        return response()->download($tmp, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    private static function sheetXml(array $sheet): string
    {
        $rows = $sheet['rows'] ?? [];
        $xml = '<worksheet xmlns="' . self::NS_MAIN . '">';
        if (!empty($sheet['freeze'])) {
            $xml .= '<sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>';
        }
        $xml .= '<sheetFormatPr defaultRowHeight="20"/>';
        if (!empty($sheet['widths'])) {
            $xml .= '<cols>';
            foreach ($sheet['widths'] as $i => $width) {
                $n = $i + 1;
                $xml .= '<col min="' . $n . '" max="' . $n . '" width="' . (float) $width . '" customWidth="1"/>';
            }
            $xml .= '</cols>';
        }

        $xml .= '<sheetData>';
        foreach ($rows as $r => $cells) {
            $rowNum = $r + 1;
            $xml .= '<row r="' . $rowNum . '">';
            foreach ($cells as $c => $cell) {
                $value = is_array($cell) ? (string) ($cell['value'] ?? '') : (string) $cell;
                $style = is_array($cell) ? (int) ($cell['style'] ?? self::STYLE_CELL) : self::STYLE_CELL;
                $ref = self::columnName($c + 1) . $rowNum;
                // Semua nilai ditulis sebagai TEKS agar NIM "0012345" tidak kehilangan angka 0 di depan.
                $xml .= '<c r="' . $ref . '" s="' . $style . '" t="inlineStr"><is><t xml:space="preserve">' . self::esc($value) . '</t></is></c>';
            }
            $xml .= '</row>';
        }
        $xml .= '</sheetData>';

        if (!empty($sheet['autofilter']) && $rows) {
            $xml .= '<autoFilter ref="A1:' . self::columnName(count($rows[0])) . count($rows) . '"/>';
        }

        // Kotak otomatis: baris mana pun yang ada isinya (di kolom mana pun) diberi garis kotak;
        // kalau semua selnya dikosongkan, kotaknya hilang lagi. Tanpa makro, murni conditional formatting.
        if (!empty($sheet['autoborder'])) {
            $ab = $sheet['autoborder'];
            $last = self::columnName((int) $ab['cols']);
            $from = (int) ($ab['from'] ?? 2);
            $xml .= '<conditionalFormatting sqref="A' . $from . ':' . $last . (int) $ab['to'] . '">'
                . '<cfRule type="expression" dxfId="0" priority="1"><formula>COUNTA($A' . $from . ':$' . $last . $from . ')&gt;0</formula></cfRule>'
                . '</conditionalFormatting>';
        }

        $validations = $sheet['validations'] ?? [];
        if ($validations) {
            // Semua dropdown harus berada dalam SATU elemen <dataValidations>.
            $xml .= '<dataValidations count="' . count($validations) . '">';
            foreach ($validations as $v) {
                $xml .= '<dataValidation type="list" allowBlank="1" showErrorMessage="1" errorStyle="stop" errorTitle="Pilihan tidak valid" error="' . self::esc($v['error'] ?? 'Pilih salah satu dari daftar.') . '" sqref="' . self::esc($v['range']) . '">'
                    . '<formula1>"' . self::esc(implode(',', $v['list'])) . '"</formula1></dataValidation>';
            }
            $xml .= '</dataValidations>';
        }

        return $xml . '<pageMargins left="0.25" right="0.25" top="0.5" bottom="0.5" header="0.2" footer="0.2"/></worksheet>';
    }

    private static function styles(): string
    {
        return '<styleSheet xmlns="' . self::NS_MAIN . '">'
            . '<fonts count="3"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="14"/><name val="Calibri"/></font></fonts>'
            . '<fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FFE8F0FE"/><bgColor indexed="64"/></patternFill></fill></fills>'
            . '<borders count="2"><border><left/><right/><top/><bottom/><diagonal/></border><border><left style="thin"/><right style="thin"/><top style="thin"/><bottom style="thin"/><diagonal/></border></borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="4">'
            . '<xf numFmtId="49" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1" applyAlignment="1"><alignment vertical="center"/></xf>'
            . '<xf numFmtId="49" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyBorder="1" applyAlignment="1"><alignment vertical="center" wrapText="1"/></xf>'
            . '<xf numFmtId="49" fontId="1" fillId="2" borderId="1" xfId="0" applyNumberFormat="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>'
            . '<xf numFmtId="49" fontId="2" fillId="0" borderId="0" xfId="0" applyNumberFormat="1" applyFont="1" applyAlignment="1"><alignment vertical="center"/></xf>'
            . '</cellXfs>'
            . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            . '<dxfs count="1"><dxf><border>'
            . '<left style="thin"><color rgb="FF000000"/></left><right style="thin"><color rgb="FF000000"/></right>'
            . '<top style="thin"><color rgb="FF000000"/></top><bottom style="thin"><color rgb="FF000000"/></bottom>'
            . '</border></dxf></dxfs></styleSheet>';
    }

    /** 1 -> A, 27 -> AA */
    public static function columnName(int $n): string
    {
        $s = '';
        while ($n > 0) {
            $n--;
            $s = chr(65 + ($n % 26)) . $s;
            $n = intdiv($n, 26);
        }

        return $s;
    }

    private static function columnIndex(string $ref): int
    {
        $letters = preg_replace('/[^A-Z]/', '', strtoupper($ref));
        $n = 0;
        foreach (str_split($letters) as $ch) {
            $n = $n * 26 + (ord($ch) - 64);
        }

        return max(0, $n - 1);
    }

    private static function esc(string $value): string
    {
        // Buang karakter kontrol yang membuat XML (dan Excel) menolak file.
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $value);

        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    // =====================================================================
    // BACA
    // =====================================================================

    /**
     * Baca satu sheet XLSX. Bentuk hasil sama dengan Csv::read():
     * [header[], rows[ ['line' => nomor baris Excel, 'data' => [kolom => nilai]] ]].
     * Baris kosong dan baris yang sel pertamanya diawali "#" dilewati.
     *
     * @param  string|null  $sheetName  nama sheet yang dicari; kalau tidak ada dipakai sheet "data" pertama
     *                                  (sheet bernama "Petunjuk" dilewati), lalu sheet pertama.
     *
     * @throws \RuntimeException
     */
    public static function read(string $path, array $aliases = [], ?string $sheetName = null): array
    {
        if (!class_exists(ZipArchive::class)) {
            throw new \RuntimeException('Ekstensi PHP ZIP (ZipArchive) belum aktif.');
        }
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            throw new \RuntimeException('File Excel tidak dapat dibuka. Pastikan file .xlsx valid.');
        }

        try {
            $wb = simplexml_load_string((string) $zip->getFromName('xl/workbook.xml'));
            $rels = simplexml_load_string((string) $zip->getFromName('xl/_rels/workbook.xml.rels'));
            if (!$wb || !$rels) {
                throw new \RuntimeException('Struktur file Excel tidak valid.');
            }

            $wb->registerXPathNamespace('m', self::NS_MAIN);
            $sheets = $wb->xpath('//m:sheets/m:sheet') ?: [];
            if (!$sheets) {
                throw new \RuntimeException('Sheet data tidak ditemukan.');
            }

            $target = null;
            foreach ($sheets as $sh) {
                if ($sheetName !== null && strcasecmp((string) $sh['name'], $sheetName) === 0) {
                    $target = $sh;
                    break;
                }
            }
            if (!$target) {
                foreach ($sheets as $sh) {
                    if (strcasecmp((string) $sh['name'], 'Petunjuk') !== 0) {
                        $target = $sh;
                        break;
                    }
                }
            }
            $target ??= $sheets[0];

            $rid = (string) $target->attributes(self::NS_REL)->id;
            $sheetPath = null;
            foreach ($rels->Relationship as $rel) {
                if ((string) $rel['Id'] === $rid) {
                    $t = (string) $rel['Target'];
                    $sheetPath = str_starts_with($t, '/') ? ltrim($t, '/') : 'xl/' . $t;
                    break;
                }
            }
            if (!$sheetPath || $zip->locateName($sheetPath) === false) {
                throw new \RuntimeException('Sheet data Excel tidak dapat dibaca.');
            }

            $shared = [];
            if ($zip->locateName('xl/sharedStrings.xml') !== false) {
                $ss = simplexml_load_string((string) $zip->getFromName('xl/sharedStrings.xml'));
                if ($ss) {
                    $ss->registerXPathNamespace('m', self::NS_MAIN);
                    foreach ($ss->xpath('//m:si') ?: [] as $si) {
                        $texts = $si->xpath('.//m:t') ?: [];
                        $shared[] = implode('', array_map(fn ($t) => (string) $t, $texts));
                    }
                }
            }

            $dateStyles = self::dateStyles((string) $zip->getFromName('xl/styles.xml'));

            $sx = simplexml_load_string((string) $zip->getFromName($sheetPath));
            if (!$sx) {
                throw new \RuntimeException('Isi sheet Excel tidak valid.');
            }
        } finally {
            $zip->close();
        }

        $sx->registerXPathNamespace('m', self::NS_MAIN);

        $header = null;
        $rows = [];
        foreach ($sx->xpath('//m:sheetData/m:row') ?: [] as $rowEl) {
            $line = (int) $rowEl['r'];
            $cells = [];
            foreach ($rowEl->c as $c) {
                $idx = self::columnIndex((string) $c['r']);
                $cells[$idx] = trim(self::cellValue($c, $shared, $dateStyles));
            }
            if (!$cells) {
                continue;
            }
            $cells = array_replace(array_fill(0, max(array_keys($cells)) + 1, ''), $cells);

            if (!array_filter($cells, fn ($v) => $v !== '')) {
                continue; // baris kosong
            }
            if (str_starts_with($cells[0], '#')) {
                continue; // baris contoh / komentar
            }

            if ($header === null) {
                $header = array_map(function ($h) use ($aliases) {
                    $h = Csv::normalizeKey($h);

                    return $aliases[$h] ?? $h;
                }, $cells);
                continue;
            }

            $data = [];
            foreach ($header as $i => $name) {
                if ($name !== '' && !isset($data[$name])) {
                    $data[$name] = Csv::unsafeCell($cells[$i] ?? '');
                }
            }
            $rows[] = ['line' => $line, 'data' => $data];
        }

        if ($header === null) {
            throw new \RuntimeException('Baris judul kolom tidak ditemukan. Gunakan template dari menu Unduh Template.');
        }

        return [array_values(array_filter($header)), $rows];
    }

    /**
     * Daftar indeks style (atribut s pada sel) yang berformat tanggal.
     *
     * @return array<int,bool>
     */
    private static function dateStyles(string $stylesXml): array
    {
        $sx = $stylesXml !== '' ? simplexml_load_string($stylesXml) : false;
        if (!$sx) {
            return [];
        }
        $sx->registerXPathNamespace('m', self::NS_MAIN);

        $custom = [];
        foreach ($sx->xpath('//m:numFmts/m:numFmt') ?: [] as $nf) {
            $custom[(int) $nf['numFmtId']] = (string) $nf['formatCode'];
        }

        $out = [];
        foreach ($sx->xpath('//m:cellXfs/m:xf') ?: [] as $i => $xf) {
            $id = (int) $xf['numFmtId'];
            if (($id >= 14 && $id <= 22) || ($id >= 27 && $id <= 36) || ($id >= 45 && $id <= 47) || ($id >= 50 && $id <= 58)) {
                $out[$i] = true;
            } elseif (isset($custom[$id])) {
                $code = preg_replace('/"[^"]*"|\[[^\]]*\]|\\./', '', $custom[$id]);
                $out[$i] = (bool) preg_match('/[dmyhs]/i', $code);
            }
        }

        return $out;
    }

    private static function cellValue(\SimpleXMLElement $c, array $shared, array $dateStyles = []): string
    {
        $type = (string) $c['t'];
        if ($type === 'inlineStr') {
            $c->registerXPathNamespace('m', self::NS_MAIN);

            return implode('', array_map(fn ($t) => (string) $t, $c->xpath('.//m:t') ?: []));
        }
        $v = (string) $c->v;
        if ($type === 's') {
            return $shared[(int) $v] ?? '';
        }
        if ($type === 'b') {
            return $v === '1' ? 'TRUE' : 'FALSE';
        }
        // Sel berformat tanggal disimpan Excel sebagai angka serial (mis. 46082 = 2026-03-01).
        if ($v !== '' && is_numeric($v) && !empty($dateStyles[(int) $c['s']]) && (float) $v >= 1) {
            return gmdate('Y-m-d', (int) round(((float) $v - 25569) * 86400));
        }
        // Angka dari Excel: "2026" tersimpan "2026" (bulat), buang ".0" supaya NIM/tahun terbaca benar.
        if ($v !== '' && is_numeric($v) && str_contains($v, '.') && (float) $v == (int) (float) $v) {
            return (string) (int) (float) $v;
        }

        return $v;
    }
}
