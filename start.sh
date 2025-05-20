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

# Caricamento esplicito dell'ambiente (aggiunge i percorsi di npm al PATH)
if [ -f ~/.bashrc ]; then
    echo "Caricamento dell'ambiente utente..."
    source ~/.bashrc
fi

if [ -f ~/.nvm/nvm.sh ]; then
    echo "Caricamento NVM..."
    source ~/.nvm/nvm.sh
fi

# Verifica della disponibilità di npm
NPM_PATH=$(which npm || echo "")
if [ -z "$NPM_PATH" ]; then
    echo "npm non trovato nel PATH. Verifico percorsi alternativi..."
    # Controlla percorsi comuni dove npm potrebbe essere installato
    POTENTIAL_PATHS=(
        "/usr/bin/npm"
        "/usr/local/bin/npm"
        "$HOME/.nvm/versions/node/*/bin/npm"
        "/opt/node/bin/npm"
    )
    
    for path in "${POTENTIAL_PATHS[@]}"; do
        # Espandi i glob patterns se presenti
        for expanded_path in $path; do
            if [ -x "$expanded_path" ]; then
                NPM_PATH="$expanded_path"
                echo "npm trovato in: $NPM_PATH"
                break 2
            fi
        done
    done
    
    # Se non abbiamo ancora trovato npm, stampa un messaggio e prova a usare npx
    if [ -z "$NPM_PATH" ]; then
        echo "ATTENZIONE: npm non trovato. Tento di eseguire i comandi attraverso npx o di saltare la fase di build frontend."
    fi
else
    echo "npm trovato in: $NPM_PATH"
fi

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
npm ci
npm run build

# Ripristino permessi a 775 su tutti i file e cartelle
echo "Ripristino permessi a 775 su tutti i file e cartelle..."
sudo chmod -R 775 $PROJECT_DIR

echo "Post-deploy completato con successo!"
