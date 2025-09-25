<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call('sync-resources')->daily();
// Schedule::command('queue:work --queue=default --timeout=100 --stop-when-empty')->everyMinute();

/*
    Crontab entry:
    * * * * * cd /var/www/llm-txt && php artisan schedule:run >> /dev/null 2>&1
*/