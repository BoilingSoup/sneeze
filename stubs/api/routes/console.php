<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Check every minute and delete any expired sanctum auth tokens from database.
Schedule::command('sanctum:prune-expired --hours=0')->everyMinute();
