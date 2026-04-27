#!/bin/bash

# =====================================================================
# INSTALLER OTOMATIS - POS Toko Waris
# =====================================================================
# Jalankan: bash install.sh
# =====================================================================

set -e

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

echo ""
echo -e "${GREEN}╔══════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║    INSTALLER - POS TOKO WARIS (Laravel 11)          ║${NC}"
echo -e "${GREEN}╚══════════════════════════════════════════════════════╝${NC}"
echo ""

# Cek PHP
if ! command -v php &> /dev/null; then
    echo -e "${RED}[ERROR] PHP tidak ditemukan. Install PHP 8.2+ terlebih dahulu.${NC}"
    exit 1
fi

PHP_VERSION=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;")
echo -e "${GREEN}[✓] PHP ${PHP_VERSION} ditemukan.${NC}"

# Cek Composer
if ! command -v composer &> /dev/null; then
    echo -e "${RED}[ERROR] Composer tidak ditemukan.${NC}"
    exit 1
fi
echo -e "${GREEN}[✓] Composer ditemukan.${NC}"

# Copy .env
if [ ! -f ".env" ]; then
    cp .env.example .env
    echo -e "${YELLOW}[!] File .env dibuat dari .env.example.${NC}"
    echo -e "${YELLOW}[!] Edit .env untuk konfigurasi database Anda, lalu jalankan ulang.${NC}"
    echo ""
    echo "    DB_DATABASE=pos_toko_waris"
    echo "    DB_USERNAME=root"
    echo "    DB_PASSWORD=your_password"
    echo ""
    read -p "Tekan ENTER setelah mengedit .env untuk melanjutkan..."
fi

# Install dependencies
echo ""
echo -e "${YELLOW}[*] Menginstall dependencies Composer...${NC}"
composer install --no-interaction --prefer-dist --optimize-autoloader

# Generate key
echo -e "${YELLOW}[*] Generate APP_KEY...${NC}"
php artisan key:generate --ansi

# Migrate + Seed
echo ""
echo -e "${YELLOW}[*] Menjalankan migrasi database...${NC}"
php artisan migrate --force

echo -e "${YELLOW}[*] Menjalankan seeder (data dummy)...${NC}"
php artisan db:seed --force

# Storage link
php artisan storage:link 2>/dev/null || true

# Cache clear
php artisan config:clear
php artisan route:clear
php artisan cache:clear

echo ""
echo -e "${GREEN}╔══════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║  INSTALASI SELESAI!                                  ║${NC}"
echo -e "${GREEN}╠══════════════════════════════════════════════════════╣${NC}"
echo -e "${GREEN}║  Jalankan: php artisan serve                         ║${NC}"
echo -e "${GREEN}║  API URL:  http://localhost:8000/api                 ║${NC}"
echo -e "${GREEN}║                                                      ║${NC}"
echo -e "${GREEN}║  Login Pemilik: pemilik / password123                ║${NC}"
echo -e "${GREEN}║  Login Kasir:   kasir1  / password123                ║${NC}"
echo -e "${GREEN}╚══════════════════════════════════════════════════════╝${NC}"
echo ""
