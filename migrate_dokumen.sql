USE pertamina_app;

CREATE TABLE IF NOT EXISTS dokumen (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  judul VARCHAR(150) NOT NULL,
  nama_asli VARCHAR(255) NOT NULL,
  nama_file CHAR(32) NOT NULL,
  ekstensi VARCHAR(5) NOT NULL,
  ukuran INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_user (user_id)
) ENGINE=InnoDB;
