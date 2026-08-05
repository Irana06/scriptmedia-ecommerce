<?php

use App\Console\Commands\CheckOverdueInvoices;
use App\Console\Commands\GenerateRecurringInvoices;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command(GenerateRecurringInvoices::class)
    ->dailyAt('00:10')
    ->timezone((string) config('billing.schedule_timezone'))
    ->withoutOverlapping()
    ->onOneServer();

Schedule::command(CheckOverdueInvoices::class)
    ->dailyAt('00:30')
    ->timezone((string) config('billing.schedule_timezone'))
    ->withoutOverlapping()
    ->onOneServer();
