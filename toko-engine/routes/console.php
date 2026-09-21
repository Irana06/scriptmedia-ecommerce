<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Stock is reserved when an order is placed, so unpaid orders have to be swept
// up or the catalogue slowly runs out of items nobody actually bought.
Schedule::command('orders:expire-unpaid')->hourly()->withoutOverlapping();
