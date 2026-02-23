<?php

namespace App\Console\Commands;

use App\Models\ClassSession;
use App\Models\CourseClass;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Console\Command;

class GenerateWeeklySessions extends Command
{
    protected $signature = 'sessions:generate
        {--from= : Start date (Y-m-d), default: Monday of current week}
        {--to= : End date (Y-m-d), default: Sunday of current week}';

    protected $description = 'Generate class session records for active weekly classes within a date range';

    public function handle(): int
    {
        $from = $this->option('from')
            ? Carbon::parse($this->option('from'))
            : Carbon::today()->startOfWeek(Carbon::MONDAY);

        $to = $this->option('to')
            ? Carbon::parse($this->option('to'))
            : Carbon::today()->endOfWeek(Carbon::SUNDAY);

        $this->info("Generating sessions from {$from->toDateString()} to {$to->toDateString()}...");

        $classes = CourseClass::where('type', 'weekly')
            ->where('active', true)
            ->get();

        $created = 0;
        $skipped = 0;

        foreach ($classes as $class) {
            $period = CarbonPeriod::create($from, $to);

            foreach ($period as $date) {
                if ($date->dayOfWeekIso !== $class->day_of_week) {
                    continue;
                }

                $wasRecentlyCreated = ClassSession::firstOrCreate(
                    ['class_id' => $class->id, 'date' => $date->copy()->startOfDay()],
                    [
                        'starts_time' => $class->starts_time,
                        'ends_time'   => $class->ends_time,
                    ]
                )->wasRecentlyCreated;

                if ($wasRecentlyCreated) {
                    $created++;
                } else {
                    $skipped++;
                }
            }
        }

        $this->info("Sessions created: {$created}");
        $this->info("Sessions skipped: {$skipped}");

        return self::SUCCESS;
    }
}
