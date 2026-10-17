<?php
/* Rekap penyaluran ke Excel (.xlsx), per tanggal atau per bulan.
   Dibuat langsung dengan PHP (tanpa pustaka tambahan, tanpa ekstensi zip).
   Pakai:  penyaluran_export.php?mode=tanggal&dari=2026-10-01&sampai=2026-10-17
           penyaluran_export.php?mode=bulan&bulan=2026-10                          */
require __DIR__ . '/penyaluran_lib.php';

function gagal(int $code, string $msg): void {
    http_response_code($code);
    header('Content-Type: text/plain; charset=utf-8');
    exit($msg);
}

function tgl_id(string $s): string {
    return substr($s, 8, 2) . '/' . substr($s, 5, 2) . '/' . substr($s, 0, 4);
}

// Nomor seri tanggal Excel
function serial(string $tgl): int {
    return (int)((strtotime($tgl . ' UTC') - strtotime('1899-12-30 UTC')) / 86400);
}

function cal_days_in_month_safe(int $thn, int $bln): int {
    return (int)date('t', mktime(0, 0, 0, $bln, 1, $thn));
}

function kol(int $i): string {           // 0 -> A, 1 -> B, ...
    $s = '';
    $i++;
    while ($i > 0) { $m = ($i - 1) % 26; $s = chr(65 + $m) . $s; $i = intdiv($i - 1, 26); }
    return $s;
}

function xs(string $s): string {         // aman untuk XML
    $s = preg_replace('/[^\x09\x0A\x0D\x20-\x{D7FF}\x{E000}-\x{FFFD}]/u', '', $s) ?? '';
    return htmlspecialchars($s, ENT_XML1 | ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function c_teks(string $ref, string $v, int $s): string {
    return '<c r="' . $ref . '" t="inlineStr" s="' . $s . '"><is><t xml:space="preserve">' . xs($v) . '</t></is></c>';
}
function c_angka(string $ref, $v, int $s): string {
    return '<c r="' . $ref . '" s="' . $s . '"><v>' . (int)$v . '</v></c>';
}
function c_kosong(string $ref, int $s): string {
    return '<c r="' . $ref . '" s="' . $s . '"/>';
}

/* Satu lembar kerja.
   $tipe: per kolom 'no' (nomor urut), 's' (teks), 'd' (tanggal Y-m-d), 'i' (angka)
   $jumlahKol: indeks kolom yang dijumlahkan di baris TOTAL */
function sheet_xml(string $judul, string $sub, array $head, array $tipe, array $rows, array $lebar, array $jumlahKol): string {
    $nc = count($head);
    $akhir = kol($nc - 1);
    $n = count($rows);
    $x = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
       . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
       . '<sheetViews><sheetView workbookViewId="0"><pane ySplit="4" topLeftCell="A5" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>'
       . '<sheetFormatPr defaultRowHeight="15"/><cols>';
    foreach ($lebar as $i => $w) {
        $x .= '<col min="' . ($i + 1) . '" max="' . ($i + 1) . '" width="' . $w . '" customWidth="1"/>';
    }
    $x .= '</cols><sheetData>';

    $x .= '<row r="1" ht="24" customHeight="1">' . c_teks('A1', $judul, 1) . '</row>';
    $x .= '<row r="2">' . c_teks('A2', $sub, 8) . '</row>';

    $x .= '<row r="4" ht="20" customHeight="1">';
    foreach ($head as $j => $h) $x .= c_teks(kol($j) . '4', $h, 2);
    $x .= '</row>';

    $jml = array_fill(0, $nc, 0);
    foreach ($rows as $i => $row) {
        $r = 5 + $i;
        $x .= '<row r="' . $r . '">';
        for ($j = 0; $j < $nc; $j++) {
            $ref = kol($j) . $r;
            $v = $row[$j] ?? '';
            if ($tipe[$j] === 'no')      $x .= c_angka($ref, $i + 1, 4);
            elseif ($tipe[$j] === 's')   $x .= c_teks($ref, (string)$v, 3);
            elseif ($tipe[$j] === 'd')   $x .= c_angka($ref, serial((string)$v), 5);
            else { $x .= c_angka($ref, $v, 4); if (in_array($j, $jumlahKol, true)) $jml[$j] += (int)$v; }
        }
        $x .= '</row>';
    }

    // Baris TOTAL
    $t = 5 + $n;
    $k = min($jumlahKol);
    $x .= '<row r="' . $t . '">';
    for ($j = 0; $j < $nc; $j++) {
        $ref = kol($j) . $t;
        if ($j === 0)                          $x .= c_teks($ref, 'TOTAL', 6);
        elseif ($j < $k)                       $x .= c_kosong($ref, 6);
        elseif (in_array($j, $jumlahKol, true)) {
            $x .= $n > 0
                ? '<c r="' . $ref . '" s="7"><f>SUM(' . kol($j) . '5:' . kol($j) . ($t - 1) . ')</f><v>' . $jml[$j] . '</v></c>'
                : c_angka($ref, 0, 7);
        }
        else                                   $x .= c_kosong($ref, 6);
    }
    $x .= '</row></sheetData><mergeCells count="' . ($k > 1 ? 2 : 1) . '">'
        . '<mergeCell ref="A1:' . $akhir . '1"/>'
        . ($k > 1 ? '<mergeCell ref="A' . $t . ':' . kol($k - 1) . $t . '"/>' : '')
        . '</mergeCells></worksheet>';
    return $x;
}

// ZIP sederhana (tanpa kompresi) - cukup untuk format .xlsx
function zip_simpan(array $files): string {
    $isi = ''; $cd = ''; $n = 0;
    $w = getdate();
    $dt = (($w['year'] - 1980) << 9) | ($w['mon'] << 5) | $w['mday'];
    $tm = ($w['hours'] << 11) | ($w['minutes'] << 5) | intdiv($w['seconds'], 2);
    foreach ($files as $nama => $data) {
        $crc = crc32($data);
        $len = strlen($data);
        $off = strlen($isi);
        $nl  = strlen($nama);
        $isi .= pack('VvvvvvVVVvv', 0x04034b50, 20, 0, 0, $tm, $dt, $crc, $len, $len, $nl, 0) . $nama . $data;
        $cd  .= pack('VvvvvvvVVVvvvvvVV', 0x02014b50, 20, 20, 0, 0, $tm, $dt, $crc, $len, $len, $nl, 0, 0, 0, 0, 0, $off) . $nama;
        $n++;
    }
    return $isi . $cd . pack('VvvvvVVv', 0x06054b50, 0, 0, $n, $n, strlen($cd), strlen($isi), 0);
}

// ---------- 1. Login & parameter ----------
$me = current_user();
if (!$me) gagal(401, 'Sesi berakhir, silakan login lagi.');

$NAMA_BULAN = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$mode = (string)($_GET['mode'] ?? 'tanggal');

if ($mode === 'bulan') {
    $b = (string)($_GET['bulan'] ?? '');
    if (!preg_match('/^(\d{4})-(\d{2})$/', $b, $m) || (int)$m[2] < 1 || (int)$m[2] > 12) {
        gagal(422, 'Bulan tidak valid.');
    }
    $dari   = $b . '-01';
    $sampai = $b . '-' . str_pad((string)cal_days_in_month_safe((int)$m[1], (int)$m[2]), 2, '0', STR_PAD_LEFT);
    $judulPeriode = $NAMA_BULAN[(int)$m[2]] . ' ' . $m[1];
    $berkas = 'rekap-penyaluran-' . $b . '.xlsx';
} else {
    $dari   = (string)($_GET['dari'] ?? '');
    $sampai = (string)($_GET['sampai'] ?? '');
    if ($dari === '' && $sampai === '') $dari = $sampai = hari_ini();
    elseif ($sampai === '') $sampai = $dari;
    elseif ($dari === '') $dari = $sampai;
    if (!tgl_valid($dari) || !tgl_valid($sampai)) gagal(422, 'Tanggal tidak valid.');
    if ($dari > $sampai) { $tmp = $dari; $dari = $sampai; $sampai = $tmp; }
    $judulPeriode = $dari === $sampai ? tgl_id($dari) : tgl_id($dari) . ' s.d. ' . tgl_id($sampai);
    $berkas = 'rekap-penyaluran-' . ($dari === $sampai ? $dari : $dari . '_sd_' . $sampai) . '.xlsx';
}

// ---------- 2. Ambil data ----------
try {
    $st = db()->prepare('SELECT tanggal, agen, plat, tabung, kg, sopir FROM penyaluran
                         WHERE tanggal BETWEEN ? AND ? ORDER BY tanggal, id LIMIT 20000');
    $st->execute([$dari, $sampai]);
    $data = $st->fetchAll();
} catch (Throwable $e) {
    error_log('[pertamina] export: ' . $e->getMessage());
    gagal(500, 'Gagal mengambil data. Pastikan migrate_penyaluran.sql sudah dijalankan.');
}

$rincian = []; $perAgen = []; $perTgl = [];
foreach ($data as $r) {
    $rincian[] = [0, $r['tanggal'], $r['agen'], $r['plat'], (int)$r['tabung'], (int)$r['kg'], $r['sopir']];
    $a = (string)$r['agen'];
    if (!isset($perAgen[$a])) $perAgen[$a] = [0, 0, 0];
    $perAgen[$a][0]++; $perAgen[$a][1] += (int)$r['tabung']; $perAgen[$a][2] += (int)$r['kg'];
    $d = (string)$r['tanggal'];
    if (!isset($perTgl[$d])) $perTgl[$d] = [0, 0, 0];
    $perTgl[$d][0]++; $perTgl[$d][1] += (int)$r['tabung']; $perTgl[$d][2] += (int)$r['kg'];
}
ksort($perAgen, SORT_STRING);
ksort($perTgl, SORT_STRING);

$rowsAgen = []; foreach ($perAgen as $a => $v) $rowsAgen[] = [0, (string)$a, $v[0], $v[1], $v[2]];
$rowsTgl  = []; foreach ($perTgl as $d => $v)  $rowsTgl[]  = [(string)$d, $v[0], $v[1], $v[2]];

// ---------- 3. Susun file xlsx ----------
$judul = 'Rekap Penyaluran Elpiji - PT. GAS ALAM BUMI REOG';
$sub   = 'Periode: ' . $judulPeriode;

$s1 = sheet_xml($judul, $sub,
    ['No', 'Tanggal', 'Agen', 'Plat Nomor', 'Total Tabung', 'Penyaluran (kg)', 'Nama Sopir'],
    ['no', 'd', 's', 's', 'i', 'i', 's'], $rincian, [6, 13, 30, 15, 14, 17, 26], [4, 5]);
$s2 = sheet_xml($judul . ' (Per Agen)', $sub,
    ['No', 'Agen', 'Jumlah Transaksi', 'Total Tabung', 'Penyaluran (kg)'],
    ['no', 's', 'i', 'i', 'i'], $rowsAgen, [6, 34, 18, 14, 17], [2, 3, 4]);
$s3 = sheet_xml($judul . ' (Per Tanggal)', $sub,
    ['Tanggal', 'Jumlah Transaksi', 'Total Tabung', 'Penyaluran (kg)'],
    ['d', 'i', 'i', 'i'], $rowsTgl, [14, 18, 14, 17], [1, 2, 3]);

$NS  = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';
$REL = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';
$XML = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
$bd  = '<border><left style="thin"><color rgb="FFBFC8D2"/></left><right style="thin"><color rgb="FFBFC8D2"/></right>'
     . '<top style="thin"><color rgb="FFBFC8D2"/></top><bottom style="thin"><color rgb="FFBFC8D2"/></bottom><diagonal/></border>';

$files = [
  '[Content_Types].xml' => $XML . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
    . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
    . '<Default Extension="xml" ContentType="application/xml"/>'
    . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
    . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
    . '<Override PartName="/xl/worksheets/sheet2.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
    . '<Override PartName="/xl/worksheets/sheet3.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
    . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
    . '</Types>',
  '_rels/.rels' => $XML . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
    . '<Relationship Id="rId1" Type="' . $REL . '/officeDocument" Target="xl/workbook.xml"/></Relationships>',
  'xl/workbook.xml' => $XML . '<workbook xmlns="' . $NS . '" xmlns:r="' . $REL . '"><sheets>'
    . '<sheet name="Rincian" sheetId="1" r:id="rId1"/>'
    . '<sheet name="Per Agen" sheetId="2" r:id="rId2"/>'
    . '<sheet name="Per Tanggal" sheetId="3" r:id="rId3"/>'
    . '</sheets></workbook>',
  'xl/_rels/workbook.xml.rels' => $XML . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
    . '<Relationship Id="rId1" Type="' . $REL . '/worksheet" Target="worksheets/sheet1.xml"/>'
    . '<Relationship Id="rId2" Type="' . $REL . '/worksheet" Target="worksheets/sheet2.xml"/>'
    . '<Relationship Id="rId3" Type="' . $REL . '/worksheet" Target="worksheets/sheet3.xml"/>'
    . '<Relationship Id="rId4" Type="' . $REL . '/styles" Target="styles.xml"/></Relationships>',
  'xl/styles.xml' => $XML . '<styleSheet xmlns="' . $NS . '">'
    . '<numFmts count="1"><numFmt numFmtId="164" formatCode="dd/mm/yyyy"/></numFmts>'
    . '<fonts count="5">'
    .   '<font><sz val="11"/><name val="Calibri"/></font>'
    .   '<font><b/><sz val="11"/><name val="Calibri"/></font>'
    .   '<font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>'
    .   '<font><b/><sz val="14"/><name val="Calibri"/></font>'
    .   '<font><i/><sz val="10"/><color rgb="FF52606D"/><name val="Calibri"/></font>'
    . '</fonts>'
    . '<fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill>'
    .   '<fill><patternFill patternType="solid"><fgColor rgb="FF0072BC"/><bgColor indexed="64"/></patternFill></fill></fills>'
    . '<borders count="2"><border><left/><right/><top/><bottom/><diagonal/></border>' . $bd . '</borders>'
    . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
    . '<cellXfs count="9">'
    .   '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
    .   '<xf numFmtId="0" fontId="3" fillId="0" borderId="0" xfId="0" applyFont="1"/>'
    .   '<xf numFmtId="0" fontId="2" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>'
    .   '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1"/>'
    .   '<xf numFmtId="3" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyBorder="1"/>'
    .   '<xf numFmtId="164" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyBorder="1" applyAlignment="1"><alignment horizontal="left"/></xf>'
    .   '<xf numFmtId="0" fontId="1" fillId="0" borderId="1" xfId="0" applyFont="1" applyBorder="1"/>'
    .   '<xf numFmtId="3" fontId="1" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyFont="1" applyBorder="1"/>'
    .   '<xf numFmtId="0" fontId="4" fillId="0" borderId="0" xfId="0" applyFont="1"/>'
    . '</cellXfs>'
    . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
    . '</styleSheet>',
  'xl/worksheets/sheet1.xml' => $s1,
  'xl/worksheets/sheet2.xml' => $s2,
  'xl/worksheets/sheet3.xml' => $s3,
];

$bin = zip_simpan($files);

while (ob_get_level()) ob_end_clean();
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $berkas . '"');
header('Content-Length: ' . strlen($bin));
header('Cache-Control: private, no-store');
header('X-Content-Type-Options: nosniff');
echo $bin;
