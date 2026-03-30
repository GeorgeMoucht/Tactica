<?php

namespace App\Services\Dashboard;

use App\Models\CourseClass;
use App\Models\Student;
use App\Repositories\Contracts\DashboardRepository;
use Carbon\Carbon;

class DashboardService
{
    public function __construct(
        private readonly DashboardRepository $repo
    ) {}

    public function widgets(): array
    {
        return [
            'stats'                => $this->stats(),
            'outstanding'          => $this->outstanding(),
            'class_capacity'       => $this->classCapacity(),
            'workshop_capacity'    => $this->workshopCapacity(),
            'recent_registrations' => $this->recentRegistrations(),
        ];
    }

    private function stats(): array
    {
        return [
            'active_learners'         => $this->repo->activeLearnerCount(),
            'today_attendance'        => $this->repo->todayAttendanceSummary(),
            'session_today'           => $this->repo->todaySessionCount(),
            'entrollments_this_month' => $this->repo->enrollmentsThisMonth(),
        ];
    }

    private function outstanding(): array
    {
        return [
            'total_amount'   => $this->repo->outstandingAmount(),
            'students_count' => $this->repo->outstandingStudentsCount(),
        ];
    }

    private function classCapacity(): array
    {
        return $this->repo->activeClassesWithEnrolled('weekly')
            ->map(fn (CourseClass $c) => [
                'id'         => $c->id,
                'title'      => $c->title,
                'capacity'   => $c->capacity ?? 0,
                'enrolled'   => $c->enrolled,
                'percentage' => $c->capacity
                    ? (int) round(($c->enrolled / $c->capacity) * 100)
                    : 0,
            ])
            ->toArray();
    }

    private function workshopCapacity(): array
    {
        return $this->repo->activeWorkshopsWithEnrolled()
            ->map(fn (CourseClass $c) => [
                'id'         => $c->id,
                'title'      => $c->title,
                'capacity'   => $c->capacity ?? 0,
                'enrolled'   => $c->enrolled,
                'percentage' => $c->capacity
                    ? (int) round(($c->enrolled / $c->capacity) * 100)
                    : 0,
            ])
            ->toArray();
    }

    private function recentRegistrations(): array
    {
        return $this->repo->recentStudents(5)
            ->map(fn (Student $s) => [
                'id'              => $s->id,
                'created_at'      => $s->created_at->toDateString(),
                'guardian_id'     => $s->guardians->first()?->id,
                'guardian_name'   => $s->guardians->first()?->name,
                'students'        => $this->guardianStudents($s),
            ])
            ->toArray();
    }

    private function guardianStudents(Student $student): array
    {
        $guardian = $student->guardians->first();

        if (! $guardian) {
            return [['id' => $student->id, 'name' => $student->name]];
        }

        return $guardian->students->map(fn (Student $s) => [
            'id'   => $s->id,
            'name' => $s->name,
        ])->toArray();
    }

    public function financials(): array
    {
        $now = Carbon::now();

        $monthLabels = [
            1 => 'Ιαν', 2 => 'Φεβ', 3 => 'Μαρ', 4 => 'Απρ',
            5 => 'Μάι', 6 => 'Ιουν', 7 => 'Ιουλ', 8 => 'Αυγ',
            9 => 'Σεπ', 10 => 'Οκτ', 11 => 'Νοε', 12 => 'Δεκ',
        ];

        $monthlyRevenue = $this->repo->monthlyRevenue(6)
            ->map(fn ($row) => [
                'year'        => (int) $row->period_year,
                'month'       => (int) $row->period_month,
                'month_label' => $monthLabels[$row->period_month] ?? '',
                'paid'        => (float) $row->paid,
                'pending'     => (float) $row->pending,
                'total'       => round((float) $row->paid + (float) $row->pending, 2),
            ])
            ->values()
            ->toArray();

        $currentMonthRevenue = collect($monthlyRevenue)
            ->first(fn ($r) => $r['year'] === $now->year && $r['month'] === $now->month);

        $revenueThisMonth = $currentMonthRevenue['paid'] ?? 0.0;

        $expenses = $this->repo->currentMonthExpenses();
        $byCategory = $this->repo->expensesByCategory($now->year, $now->month)
            ->map(fn ($row) => [
                'category' => $row->category,
                'amount'   => (float) $row->amount,
            ])
            ->toArray();

        return [
            'overview' => [
                'revenue_this_month'  => $revenueThisMonth,
                'expenses_this_month' => $expenses['total'],
                'balance'             => round($revenueThisMonth - $expenses['total'], 2),
                'outstanding_total'   => $this->repo->outstandingAmount(),
                'outstanding_count'   => $this->repo->outstandingStudentsCount(),
            ],
            'monthly_revenue'  => $monthlyRevenue,
            'expenses_summary' => [
                'total'          => $expenses['total'],
                'paid'           => $expenses['paid'],
                'pending_amount' => $expenses['pending_amount'],
                'pending_count'  => $expenses['pending_count'],
                'by_category'    => $byCategory,
            ],
        ];
    }
}
