<?php

use App\Http\Controllers\SitemapController;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Rebuild public/sitemap.xml every night so a static file (fast for crawlers)
// always matches the latest published content. Requires a cron running
// `php artisan schedule:work`, or a system cron line for schedule:run.
Artisan::command('sitemap:generate', function () {
    SitemapController::writeToPublic();
    $this->info('Sitemap regenerated.');
})->purpose('Regenerate the public sitemap.xml')->dailyAt('03:00');
