#!/usr/bin/zsh

echo '------ Starting deploy tasks  ------'

composer install --prefer-dist --no-interaction --no-progress --ansi
cp .env.example .env
php artisan key:generate

yarn install && yarn build

touch /var/www/app/.data/database.sqlite

php artisan migrate --seed --force
php artisan config:cache
php artisan view:cache
php artisan route:cache
php artisan icons:cache

echo '------ Deploy completed ------'
