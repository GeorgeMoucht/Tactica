<?php

use Illuminate\Support\Facades\Schedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('passport:purge --expired --revoked')->hourly();

// php artisan passport:purge --expired --revoked

// Or in a new tab of cmd run this
    // php artisan schedule:work
