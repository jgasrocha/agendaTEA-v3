<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{

    protected $commands = [
        \App\Console\Commands\ReverterTrocasExpiradas::class
    ];

    protected function schedule(Schedule $schedule): void
    {
        //$schedule->command('trocas:reverter')->dailyAt('03:00');
        $schedule->command('trocas:reverter')->everyFiveMinutes();
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
    }
}
