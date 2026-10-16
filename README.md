# Pencatatan BBM (tema Pertamina) - PHP + JS + MariaDB

## Instalasi di STB (Armbian/Debian)
```
sudo apt update
sudo apt install apache2 php php-mysql mariadb-server
sudo mkdir -p /var/www/html/pertamina
sudo cp -r ./* /var/www/html/pertamina/
sudo chown -R www-data:www-data /var/www/html/pertamina
```
## Database
```
sudo mysql < /var/www/html/pertamina/setup.sql
```
Lalu ganti password di `setup.sql` (sebelum dijalankan) DAN di `config.php` agar sama.

## Akses
Dari HP/laptop satu WiFi: `http://IP-STB/pertamina/`
Cek IP STB: `hostname -I`

## Catatan
- Harga di setup.sql hanya contoh. Ubah lewat tombol "Atur Harga".
- Belum ada login. Pakai hanya di jaringan lokal terpercaya.
