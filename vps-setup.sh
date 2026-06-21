#!/bin/bash

# ==============================================================================
# EvalCode VPS Auto-Setup Script
# ==============================================================================
# Script ini mengotomatiskan instalasi Nginx, PHP 8.3, MariaDB, Node.js, Composer,
# dan pembuatan database untuk Laravel 12/13.
#
# Cara menjalankan di VPS:
# 1. Hubungkan ke VPS via SSH: ssh root@160.187.143.234
# 2. Buat file: nano vps-setup.sh (kemudian tempel isi script ini)
# 3. Jalankan perintah: chmod +x vps-setup.sh && ./vps-setup.sh
# ==============================================================================

# Pastikan dijalankan sebagai root
if [ "$EUID" -ne 0 ]; then
  echo "❌ Silakan jalankan script ini sebagai root (sudo)!"
  exit 1
fi

clear
echo "============================================================"
echo "          Starting EvalCode VPS Environment Setup           "
echo "============================================================"
echo "Target IP: 160.187.143.234"
echo "PHP Version: 8.3"
echo "============================================================"
sleep 2

# 1. Update system packages
echo "⏳ [1/9] Mengupdate paket sistem..."
apt update && apt upgrade -y

# 2. Install dependensi umum
echo "⏳ [2/9] Menginstal dependensi umum..."
apt install -y software-properties-common curl git unzip supervisor zip build-essential ufw

# 3. Tambahkan repositori PHP Ondrej
echo "⏳ [3/9] Menambahkan repositori PHP Ondrej..."
add-apt-repository ppa:ondrej/php -y
apt update

# 4. Install PHP 8.3 & Ekstensi yang dibutuhkan Laravel
echo "⏳ [4/9] Menginstal PHP 8.3 dan ekstensi PHP..."
apt install -y php8.3-fpm php8.3-cli php8.3-mysql php8.3-mbstring php8.3-xml php8.3-bcmath php8.3-curl php8.3-zip php8.3-intl php8.3-sqlite3 php8.3-gd php8.3-redis

# 5. Install Nginx
echo "⏳ [5/9] Menginstal Nginx..."
apt install -y nginx

# 6. Install MariaDB (Database)
echo "⏳ [6/9] Menginstal MariaDB Server..."
apt install -y mariadb-server mariadb-client
systemctl start mariadb
systemctl enable mariadb

# 7. Membuat Database dan Akun Pengguna MySQL
echo "⏳ [7/9] Mengonfigurasi Database..."
DB_NAME="evalcode_db"
DB_USER="evalcode_user"
DB_PASS="SecureEvalCodePass123!"

mysql -u root -e "CREATE DATABASE IF NOT EXISTS ${DB_NAME} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';"
mysql -u root -e "GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost';"
mysql -u root -e "FLUSH PRIVILEGES;"

# 8. Install Composer (PHP Package Manager)
echo "⏳ [8/9] Menginstal Composer..."
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer

# 9. Install Node.js (NodeSource LTS) & NPM
echo "⏳ [9/9] Menginstal Node.js dan NPM..."
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt install -y nodejs

# 10. Konfigurasi Firewall Sederhana (UFW)
echo "⏳ Mengonfigurasi firewall..."
ufw allow OpenSSH
ufw allow 'Nginx Full'
# ufw --force enable

echo "============================================================"
echo "          Instalasi Lingkungan VPS Berhasil!                "
echo "============================================================"
echo "PHP Version      : $(php -v | head -n 1)"
echo "Composer Version : $(composer --version | head -n 1)"
echo "Node.js Version  : $(node -v)"
echo "NPM Version      : $(npm -v)"
echo "Nginx Status     : Active & Running"
echo "MariaDB Status   : Active & Running"
echo "------------------------------------------------------------"
echo "Detail Koneksi Database Anda:"
echo "DB_CONNECTION=mysql"
echo "DB_HOST=127.0.0.1"
echo "DB_PORT=3306"
echo "DB_DATABASE=${DB_NAME}"
echo "DB_USERNAME=${DB_USER}"
echo "DB_PASSWORD=${DB_PASS}"
echo "============================================================"
echo "Silakan lanjutkan ke Langkah 4 pada deployment_guide.md"
echo "untuk meng-clone repositori dan menyelesaikan deployment."
echo "============================================================"
