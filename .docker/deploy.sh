#!/usr/bin/zsh

echo '------ Starting deploy tasks  ------'

cp .env.example .env
composer install --prefer-dist --no-interaction --no-progress --ansi
php artisan key:generate

yarn install
yarn build

touch /app/.data/database.sqlite

php artisan migrate --seed --force

php artisan storage:link
php artisan optimize
php artisan icons:cache

echo '------ Deploy completed ------'
