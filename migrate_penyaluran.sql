USE pertamina_app;

-- Tabel laporan harian penyaluran elpiji (menggantikan form lama berbasis BBM).
-- Tabel lama (transaksi) TIDAK diubah atau dihapus.
CREATE TABLE IF NOT EXISTS penyaluran (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  tanggal DATE NOT NULL,
  agen VARCHAR(100) NOT NULL,
  jumlah TINYINT UNSIGNED NOT NULL DEFAULT 1,
  plat VARCHAR(15) NOT NULL DEFAULT '',
  tabung INT NOT NULL,
  kg INT NOT NULL,
  sopir VARCHAR(100) NOT NULL DEFAULT '',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_tanggal (tanggal),
  INDEX idx_agen (agen)
) ENGINE=InnoDB;
