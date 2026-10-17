<?php
require_once __DIR__ . '/auth.php';

// Pilihan "Total Tabung" dan berat per tabung (3 kg)
const PENYALURAN_TABUNG = [560, 1120, 1680, 2240, 2800];
const KG_PER_TABUNG = 3;

function tgl_valid(string $s): bool {
    return (bool)preg_match('/^\d{4}-\d{2}-\d{2}$/', $s)
        && checkdate((int)substr($s, 5, 2), (int)substr($s, 8, 2), (int)substr($s, 0, 4));
}

// "Hari ini" mengikuti WIB, bukan zona waktu server
function hari_ini(): string {
    return (new DateTime('now', new DateTimeZone('Asia/Jakarta')))->format('Y-m-d');
}
