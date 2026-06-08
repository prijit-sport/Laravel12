<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Register commands for the application.
     *
     * @var array<int, class-string>
     */
    protected $commands = [
        \App\Console\Commands\PopulateUsStocksCommand::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        // no scheduled tasks by default
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}

