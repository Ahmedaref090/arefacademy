#!/usr/bin/env bash
# Aref Academy – final setup
php artisan migrate:fresh --seed
php artisan storage:link
npm install
npm run dev
