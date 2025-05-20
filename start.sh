#!/bin/bash
set -e  # Interrompe l'esecuzione se un comando fallisce

echo "Iniziando lo script di post-deploy per Laravel 12..."

# Impostazione dell'ambiente di lavoro
PROJECT_DIR="/var/www/erp-usr"
WEB_USER="www-data"
WEB_GROUP="www-data"

cd $PROJECT_DIR

# Configurazione Git e permessi iniziali
echo "Configurazione sicurezza Git..."
git config --global --add safe.directory $PROJECT_DIR

# Preparazione per installazione pacchetti
echo "Impostazione permessi temporanei per l'installazione..."
chmod -R 775 storage bootstrap/cache

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

# Compilazione degli asset
echo "Compilazione degli asset frontend..."
npm ci  # Usa npm ci invece di npm install per maggiore consistenza
npm run build

# Impostazione dei permessi finali
echo "Impostazione permessi finali..."
chown -R $WEB_USER:$WEB_GROUP .
find . -type d -not -path "./node_modules/*" -not -path "./vendor/*" -exec chmod 755 {} \;
find . -type f -not -path "./node_modules/*" -not -path "./vendor/*" -exec chmod 644 {} \;

# Permessi speciali per directory che richiedono scrittura
chmod -R 775 storage bootstrap/cache
chmod -R 775 vendor

echo "Post-deploy completato con successo!"
