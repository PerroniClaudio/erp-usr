git config --global --add safe.directory /var/www/erp-usr
sudo chmod -R 777 storage bootstrap/cache vendor ./database/database.sqlite

php artisan migrate --force
composer install --no-interaction --prefer-dist --optimize-autoloader

php artisan config:cache
php artisan route:cache
php artisan view:cache

php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

php artisan queue:restart

sudo chown -R www-data:www-data .
sudo chmod -R 775 storage bootstrap/cache vendor ./database/database.sqlite