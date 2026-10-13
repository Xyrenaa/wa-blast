<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('blasts:dispatch-due')->everyMinute()->withoutOverlapping();
Schedule::command('queue:work --max-time=58 --sleep=1')->everyMinute()->withoutOverlapping(2)->runInBackground();

