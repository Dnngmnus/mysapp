CREATE DATABASE IF NOT EXISTS pertamina_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'pertamina'@'localhost' IDENTIFIED BY 'ganti_password_ini';
GRANT ALL PRIVILEGES ON pertamina_app.* TO 'pertamina'@'localhost';
FLUSH PRIVILEGES;
USE pertamina_app;

CREATE TABLE IF NOT EXISTS bbm (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(50) NOT NULL UNIQUE,
  harga INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

-- Harga contoh, SESUAIKAN lewat menu "Atur Harga" di aplikasi
INSERT IGNORE INTO bbm (nama, harga) VALUES
 ('Pertalite', 10000),
 ('Pertamax', 12500),
 ('Pertamax Turbo', 14000),
 ('Dexlite', 13000),
 ('Biosolar', 6800);

CREATE TABLE IF NOT EXISTS transaksi (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tanggal DATE NOT NULL,
  pelanggan VARCHAR(100) NOT NULL,
  plat VARCHAR(15) NOT NULL DEFAULT '',
  bbm_id INT NOT NULL,
  liter DECIMAL(8,2) NOT NULL,
  harga INT NOT NULL,
  total BIGINT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (tanggal),
  FOREIGN KEY (bbm_id) REFERENCES bbm(id)
) ENGINE=InnoDB;
