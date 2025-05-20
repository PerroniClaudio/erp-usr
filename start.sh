#!/bin/bash
set -e

echo "Iniziando lo script di post-deploy per Laravel 12 (GitHub Actions)..."



# Impostazione dell'ambiente di lavoro
PROJECT_DIR="/var/www/erp-usr"
WEB_USER="www-data"
WEB_GROUP="www-data"

cd $PROJECT_DIR

# Configurazione Git (senza sudo)
echo "Configurazione sicurezza Git..."
git config --global --add safe.directory $PROJECT_DIR

# Impostazione permessi temporanei a 777
echo "Impostazione permessi temporanei a 777 su tutti i file e cartelle..."
sudo chmod -R 777 $PROJECT_DIR


# Aggiornamento delle dipendenze
echo "Installazione dipendenze Composer..."
composer install --no-interaction --prefer-dist --optimize-autoloader

# Aggiornamento del database
echo "Esecuzione delle migrazioni database..."
php artisan migrate --force

# Pulizia e aggiornamento cache
echo "Pulizia delle cache..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo "Rigenerazione delle cache..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Riavvio della coda
echo "Riavvio delle code di lavoro..."
php artisan queue:restart


# Ripristino permessi a 775 su tutti i file e cartelle
echo "Ripristino permessi a 775 su tutti i file e cartelle..."
sudo chmod -R 775 $PROJECT_DIR

echo "Post-deploy completato con successo!"
