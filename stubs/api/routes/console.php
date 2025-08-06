<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Prune expired Sanctum tokens (every minute)
Schedule::command('sanctum:prune-expired --hours=0')->everyMinute();

// Prune used or expired verification codes (every minute)
Schedule::command('sneeze:prune-stale')->everyMinute();
