<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface DashboardRepository
{
    public function outstandingAmount(): float;

    public function outstandingStudentsCount(): int;

    /**
     * Active classes of a given type with enrolled count.
     *
     * @return Collection<int, \App\Models\CourseClass>
     */
    public function activeClassesWithEnrolled(string $type): Collection;

    /**
     * Active workshops that have future sessions, with enrolled count.
     *
     * @return Collection<int, \App\Models\CourseClass>
     */
    public function activeWorkshopsWithEnrolled(): Collection;

    /**
     * Most recent students with their guardians.
     *
     * @return Collection<int, \App\Models\Student>
     */
    public function recentStudents(int $limit = 5): Collection;

    /**
     * Monthly revenue breakdown (paid + pending) grouped by year/month.
     *
     * @return Collection<int, array{year: int, month: int, paid: float, pending: float}>
     */
    public function monthlyRevenue(int $months = 6): Collection;

    /**
     * Current-month expense totals.
     *
     * @return array{total: float, paid: float, pending_amount: float, pending_count: int}
     */
    public function currentMonthExpenses(): array;

    /**
     * Expenses grouped by category for a given year/month.
     *
     * @return Collection<int, array{category: string, amount: float}>
     */
    public function expensesByCategory(int $year, int $month): Collection;

    /**
     * Count of students with at least one active enrollment.
     */
    public function activeLearnerCount(): int;

    /**
     * Today's attendance: present count and total enrolled across today's sessions.
     *
     * @return array{present: int, enrolled: int}
     */
    public function todayAttendanceSummary(): array;

    /**
     * Count of class sessions scheduled for today.
     */
    public function todaySessionCount(): int;

    /**
     * Count of enrollments created this month.
     */
    public function enrollmentsThisMonth(): int;
}
