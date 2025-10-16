# Linux (run from project root, example: /var/www/emely)
cd /path/to/project || exit
# if you added composer packages or new classes
composer install --no-dev --optimize-autoloader
# regenerate autoload (safe if you only added classes)
composer dump-autoload -o

# Laravel caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Rebuild optimized caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# If you added database migrations (only run if needed)
# php artisan migrate --force

# Restart queue workers (if using queues)
php artisan queue:restart

# Restart PHP-FPM / web server (example commands, may vary)
# sudo systemctl restart php7.4-fpm
# sudo systemctl restart nginx

# Ensure storage & cache permissions (adjust user/group)
# sudo chown -R www-data:www-data storage bootstrap/cache
# sudo chmod -R 775 storage bootstrap/cache

# Windows (using XAMPP) - run in project folder via cmd or PowerShell
# cd C:\xampp\htdocs\Emely
# composer dump-autoload -o
# php artisan config:clear
# php artisan route:clear
# php artisan view:clear
# php artisan config:cache
# php artisan route:cache
# php artisan view:cache

# Check logs for errors
php artisan tail --lines=50 || tail -n 200 storage/logs/laravel.log
