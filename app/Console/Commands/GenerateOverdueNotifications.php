<?php

namespace App\Console\Commands;

use App\Jobs\CheckOverdueTasks;
use Illuminate\Console\Command;

class GenerateOverdueNotifications extends Command
{
    protected $signature = 'tasks:generate-overdue-notifications';
    protected $description = 'Generate overdue task notifications for users';

    public function handle(): int
    {
        CheckOverdueTasks::dispatch();

        $this->info('Overdue notification generation job dispatched.');

        return 0;
    }
}
