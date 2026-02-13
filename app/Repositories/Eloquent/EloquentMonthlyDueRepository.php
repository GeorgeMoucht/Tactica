<?php

namespace App\Repositories\Eloquent;

use App\Models\MonthlyDue;
use App\Repositories\Contracts\MonthlyDueRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class EloquentMonthlyDueRepository implements MonthlyDueRepository
{
    public function findById(int $id): ?MonthlyDue
    {
        return MonthlyDue::with(['student', 'courseClass', 'enrollment'])->find($id);
    }

    public function create(array $data): MonthlyDue
    {
        return MonthlyDue::create($data);
    }

    public function update(int $id, array $data): ?MonthlyDue
    {
        $due = MonthlyDue::find($id);

        if (!$due) {
            return null;
        }

        $due->update($data);

        return $due->fresh(['student', 'courseClass']);
    }

    public function findByStudent(int $studentId, array $filters = []): Collection
    {
        return $this->buildStudentQuery($studentId, $filters)->get();
    }

    public function paginateByStudent(int $studentId, array $filters = []): LengthAwarePaginator
    {
        $perPage = (int) ($filters['perPage'] ?? 15);

        return $this->buildStudentQuery($studentId, $filters)->paginate($perPage);
    }

    public function existsForPeriod(int $studentId, int $classId, int $year, int $month): bool
    {
        return MonthlyDue::where('student_id', $studentId)
            ->where('class_id', $classId)
            ->where('period_year', $year)
            ->where('period_month', $month)
            ->exists();
    }

    public function getPendingByStudent(int $studentId): Collection
    {
        return MonthlyDue::with(['courseClass'])
            ->where('student_id', $studentId)
            ->where('status', 'pending')
            ->orderBy('period_year')
            ->orderBy('period_month')
            ->get();
    }

    public function sumPaidByStudent(int $studentId): float
    {
        return (float) MonthlyDue::where('student_id', $studentId)
            ->where('status', 'paid')
            ->sum('amount');
    }

    public function sumPendingByStudent(int $studentId): float
    {
        return (float) MonthlyDue::where('student_id', $studentId)
            ->where('status', 'pending')
            ->sum('amount');
    }

    private function buildStudentQuery(int $studentId, array $filters = [])
    {
        $status = $filters['status'] ?? null;
        $year   = $filters['year'] ?? null;
        $month  = $filters['month'] ?? null;

        return MonthlyDue::with(['courseClass'])
            ->where('student_id', $studentId)
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($year, fn ($q) => $q->where('period_year', $year))
            ->when($month, fn ($q) => $q->where('period_month', $month))
            ->orderByDesc('period_year')
            ->orderByDesc('period_month');
    }
}
