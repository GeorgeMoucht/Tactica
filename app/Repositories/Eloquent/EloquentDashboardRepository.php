<?php

namespace App\Repositories\Eloquent;

use App\Models\ClassEnrollment;
use App\Models\ClassSession;
use App\Models\CourseClass;
use App\Models\Expense;
use App\Models\MonthlyDue;
use App\Models\Student;
use App\Models\SessionAttendance;
use App\Repositories\Contracts\DashboardRepository;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EloquentDashboardRepository implements DashboardRepository
{
    public function outstandingAmount(): float
    {
        return (float) MonthlyDue::pending()->sum('amount');
    }

    public function outstandingStudentsCount(): int
    {
        return (int) MonthlyDue::pending()->distinct('student_id')->count('student_id');
    }

    public function activeClassesWithEnrolled(string $type): Collection
    {
        return CourseClass::where('active', true)
            ->where('type', $type)
            ->withCount(['activeEnrollments as enrolled'])
            ->get();
    }

    public function activeWorkshopsWithEnrolled(): Collection
    {
        $today = Carbon::today();

        return CourseClass::where('active', true)
            ->where('type', 'workshop')
            ->whereHas('sessions', fn ($q) => $q->where('date', '>=', $today))
            ->withCount(['activeEnrollments as enrolled'])
            ->get();
    }

    public function recentStudents(int $limit = 5): Collection
    {
        return Student::with('guardians')
            ->latest()
            ->take($limit)
            ->get();
    }

    public function monthlyRevenue(int $months = 6): Collection
    {
        $from = Carbon::now()->subMonths($months - 1)->startOfMonth();

        return MonthlyDue::select(
                'period_year',
                'period_month',
                DB::raw("SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END) as paid"),
                DB::raw("SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as pending")
            )
            ->where(function ($q) use ($from) {
                $q->where('period_year', '>', $from->year)
                  ->orWhere(function ($q2) use ($from) {
                      $q2->where('period_year', $from->year)
                         ->where('period_month', '>=', $from->month);
                  });
            })
            ->groupBy('period_year', 'period_month')
            ->orderBy('period_year', 'desc')
            ->orderBy('period_month', 'desc')
            ->get();
    }

    public function currentMonthExpenses(): array
    {
        $now = Carbon::now();

        $result = Expense::whereYear('date', $now->year)
            ->whereMonth('date', $now->month)
            ->selectRaw("COALESCE(SUM(amount), 0) as total")
            ->selectRaw("COALESCE(SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END), 0) as paid")
            ->selectRaw("COALESCE(SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END), 0) as pending_amount")
            ->selectRaw("COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_count")
            ->first();

        return [
            'total'         => (float) $result->total,
            'paid'          => (float) $result->paid,
            'pending_amount' => (float) $result->pending_amount,
            'pending_count' => (int) $result->pending_count,
        ];
    }

    public function expensesByCategory(int $year, int $month): Collection
    {
        return Expense::join('expense_categories', 'expenses.expense_category_id', '=', 'expense_categories.id')
            ->whereYear('expenses.date', $year)
            ->whereMonth('expenses.date', $month)
            ->select('expense_categories.name as category', DB::raw('SUM(expenses.amount) as amount'))
            ->groupBy('expense_categories.id', 'expense_categories.name')
            ->orderByDesc('amount')
            ->get();
    }

    public function activeLearnerCount(): int
    {
        return (int) ClassEnrollment::where('status', 'active')
            ->distinct('student_id')
            ->count('student_id');
    }

    public function todayAttendanceSummary(): array
    {
        $todaySessions = ClassSession::where('date', Carbon::today())
            ->whereHas('courseClass', fn ($q) => $q->where('active', true))
            ->pluck('id');

        $present = (int) SessionAttendance::whereIn('session_id', $todaySessions)
            ->where('status', 'present')
            ->count();

        $enrolled = (int) ClassEnrollment::where('status', 'active')
            ->whereHas('courseClass', fn ($q) => $q->where('active', true)
                ->whereHas('sessions', fn ($s) => $s->where('date', Carbon::today()))
            )
            ->count();

        return [
            'present'  => $present,
            'enrolled' => $enrolled,
        ];
    }

    public function todaySessionCount(): int
    {
        return (int) ClassSession::where('date', Carbon::today())
            ->whereHas('courseClass', fn ($q) => $q->where('active', true))
            ->count();
    }

    public function enrollmentsThisMonth(): int
    {
        $now = Carbon::now();

        return (int) ClassEnrollment::whereYear('enrolled_at', $now->year)
            ->whereMonth('enrolled_at', $now->month)
            ->count();
    }
}
