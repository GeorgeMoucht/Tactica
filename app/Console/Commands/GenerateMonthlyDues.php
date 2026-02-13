<?php

namespace App\Console\Commands;

use App\Services\Payment\MonthlyDueService;
use Illuminate\Console\Command;

class GenerateMonthlyDues extends Command
{
    protected $signature = 'dues:generate
        {--year= : The year to generate dues for (default: current year)}
        {--month= : The month to generate dues for (default: current month)}
        {--amount= : The amount per due (default: 40.00)}';

    protected $description = 'Generate monthly dues for all active enrollments';

    public function handle(MonthlyDueService $service): int
    {
        $year   = (int) ($this->option('year') ?: now()->year);
        $month  = (int) ($this->option('month') ?: now()->month);
        $amount = $this->option('amount') ? (float) $this->option('amount') : null;

        $this->info("Generating monthly dues for {$month}/{$year}...");

        try {
            $stats = $service->batchGenerate($year, $month, $amount);

            $this->info("Students processed: {$stats['students_processed']}");
            $this->info("Dues created:      {$stats['dues_created']}");
            $this->info("Dues skipped:      {$stats['dues_skipped']}");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            report($e);
            $this->error("Failed to generate dues: {$e->getMessage()}");

            return self::FAILURE;
        }
    }
}
