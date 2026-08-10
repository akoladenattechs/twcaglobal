<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Send scheduled newsletters every minute (the dueForSending scope checks scheduled_at)
Schedule::command('newsletter:send-scheduled')->everyMinute();

// Drain the database queue every minute (cPanel has no supervisor/queue worker).
// This processes queued jobs such as SendNewsletterCampaign. --once + --stop-when-empty
// means each scheduler tick handles whatever is queued and exits.
Schedule::command('queue:work --once --stop-when-empty')->everyMinute()->withoutOverlapping();
