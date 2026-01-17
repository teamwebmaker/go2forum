<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// If you want an otp audit trail (who/when verified), enable cleanup and retention.
// Schedule::command('otp:cleanup')->daily();
