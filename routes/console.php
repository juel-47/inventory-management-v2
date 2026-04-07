<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// setup corn live server
// * * * * * cd /home/USERNAME/your-project && /usr/local/bin/php artisan queue:work database --queue=mail-notifications,default --stop-when-empty --tries=3 --max-time=50 --sleep=1 --timeout=120 >> /dev/null 2>&1

// * * * * * cd /home/USERNAME/your-project && /usr/local/bin/php artisan queue:work database --queue=mail-notifications,default --stop-when-empty --tries=3 --max-time=50 --sleep=1 --timeout=120 >> /dev/null 2>&1

// * * * * * cd /home/USERNAME/your-project && /usr/local/bin/php artisan schedule:run >> /dev/null 2>&1

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('backup:clean')->dailyAt('02:30');
Schedule::command('backup:run')->dailyAt('03:00');
