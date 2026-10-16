USE pertamina_app;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(30) NOT NULL UNIQUE,
  nama VARCHAR(100) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','user') NOT NULL DEFAULT 'user',
  status ENUM('aktif','pending','nonaktif') NOT NULL DEFAULT 'pending',
  gagal_login TINYINT NOT NULL DEFAULT 0,
  terkunci_sampai DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

ALTER TABLE transaksi ADD COLUMN IF NOT EXISTS user_id INT NULL;
ALTER TABLE transaksi ADD INDEX IF NOT EXISTS idx_user (user_id);
