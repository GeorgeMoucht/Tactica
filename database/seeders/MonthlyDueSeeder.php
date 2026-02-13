<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MonthlyDue;
use App\Models\ClassEnrollment;
use App\Models\Student;
use Carbon\Carbon;

class MonthlyDueSeeder extends Seeder
{
    /**
     * Default monthly amount for class dues.
     */
    private const DEFAULT_AMOUNT = 45.00;

    public function run(): void
    {
        $enrollments = ClassEnrollment::with(['student', 'courseClass'])
            ->where('status', 'active')
            ->get();

        if ($enrollments->isEmpty()) {
            $this->command?->warn('MonthlyDueSeeder: No active enrollments found. Run ClassEnrollmentSeeder first.');
            return;
        }

        $dueCount = 0;
        $today = Carbon::today();

        foreach ($enrollments as $enrollment) {
            $dueCount += $this->createDuesForEnrollment($enrollment, $today);
        }

        // Add some extra scenarios for testing
        $dueCount += $this->createWaivedDues();

        $this->command?->info("MonthlyDueSeeder: Created {$dueCount} monthly due records.");
    }

    /**
     * Create monthly dues for an enrollment from enrolled_at until current month.
     */
    private function createDuesForEnrollment(ClassEnrollment $enrollment, Carbon $today): int
    {
        $startDate = Carbon::parse($enrollment->enrolled_at)->startOfMonth();
        $endDate = $today->copy()->startOfMonth();
        $count = 0;

        // Generate dues from enrollment start to current month
        $current = $startDate->copy();

        while ($current <= $endDate) {
            $year = $current->year;
            $month = $current->month;

            // Check if due already exists
            $exists = MonthlyDue::where('student_id', $enrollment->student_id)
                ->where('class_id', $enrollment->class_id)
                ->where('period_year', $year)
                ->where('period_month', $month)
                ->exists();

            if (!$exists) {
                $status = $this->determineStatus($current, $today, $enrollment->student_id);
                $paidAt = $status === 'paid' ? $this->generatePaidAt($current) : null;

                MonthlyDue::create([
                    'student_id'    => $enrollment->student_id,
                    'class_id'      => $enrollment->class_id,
                    'enrollment_id' => $enrollment->id,
                    'period_year'   => $year,
                    'period_month'  => $month,
                    'amount'        => self::DEFAULT_AMOUNT,
                    'status'        => $status,
                    'paid_at'       => $paidAt,
                    'notes'         => $status === 'paid' ? null : null,
                ]);
                $count++;
            }

            $current->addMonth();
        }

        return $count;
    }

    /**
     * Determine the status of a due based on logic:
     * - Past months (more than 1 month ago): 80% paid, 20% pending
     * - Last month: 50% paid, 50% pending
     * - Current month: pending
     */
    private function determineStatus(Carbon $dueMonth, Carbon $today, int $studentId): string
    {
        $monthsAgo = $dueMonth->diffInMonths($today);

        // Current month is always pending
        if ($monthsAgo === 0) {
            return 'pending';
        }

        // Use student_id as seed for consistent results
        $rand = ($studentId + $dueMonth->month + $dueMonth->year) % 10;

        // Last month: 50% paid
        if ($monthsAgo === 1) {
            return $rand < 5 ? 'paid' : 'pending';
        }

        // Older months: 80% paid
        return $rand < 8 ? 'paid' : 'pending';
    }

    /**
     * Generate a realistic paid_at date within the due month.
     */
    private function generatePaidAt(Carbon $dueMonth): Carbon
    {
        // Payment usually happens between 1st and 15th of the month
        $day = rand(1, 15);
        return $dueMonth->copy()->setDay($day)->setTime(rand(9, 18), rand(0, 59));
    }

    /**
     * Create some waived dues for demonstration.
     */
    private function createWaivedDues(): int
    {
        $count = 0;

        // Find a student with pending dues and waive one
        $pendingDue = MonthlyDue::where('status', 'pending')
            ->with('student')
            ->first();

        if ($pendingDue) {
            // Create a waived due for the same student (different period)
            $year = Carbon::today()->subMonths(3)->year;
            $month = Carbon::today()->subMonths(3)->month;

            $exists = MonthlyDue::where('student_id', $pendingDue->student_id)
                ->where('class_id', $pendingDue->class_id)
                ->where('period_year', $year)
                ->where('period_month', $month)
                ->exists();

            if (!$exists) {
                MonthlyDue::create([
                    'student_id'    => $pendingDue->student_id,
                    'class_id'      => $pendingDue->class_id,
                    'enrollment_id' => $pendingDue->enrollment_id,
                    'period_year'   => $year,
                    'period_month'  => $month,
                    'amount'        => self::DEFAULT_AMOUNT,
                    'status'        => 'waived',
                    'paid_at'       => null,
                    'notes'         => 'Scholarship - waived by administration',
                ]);
                $count++;
            }
        }

        return $count;
    }
}
