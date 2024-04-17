#!/usr/bin/zsh

echo '------ Starting deploy tasks  ------'

cp .env.example .env
touch /var/www/app/.data/database.sqlite
php artisan migrate --seed --force

php artisan key:generate
php artisan config:cache
php artisan view:cache
php artisan route:cache
php artisan icons:cache

echo '------ Deploy completed ------'
