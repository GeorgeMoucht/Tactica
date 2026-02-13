<?php

namespace App\Services\Payment;

use App\Exceptions\BusinessException;
use App\Exceptions\NotFoundException;
use App\Models\ClassEnrollment;
use App\Models\MonthlyDue;
use App\Repositories\Contracts\EnrollmentRepository;
use App\Repositories\Contracts\MonthlyDueRepository;
use App\Repositories\Contracts\StudentRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MonthlyDueService
{
    private MonthlyDueRepository $dues;
    private StudentRepository $students;
    private EnrollmentRepository $enrollments;

    public function __construct(
        MonthlyDueRepository $dues,
        StudentRepository $students,
        EnrollmentRepository $enrollments
    ) {
        $this->dues = $dues;
        $this->students = $students;
        $this->enrollments = $enrollments;
    }

    /**
     * Generate monthly dues for a student's active enrollments.
     *
     * @return Collection<MonthlyDue> Created dues
     */
    public function generateForStudent(int $studentId, int $year, int $month, ?float $amount = null): Collection
    {
        $student = $this->students->findWithMembership($studentId);

        if (!$student) {
            throw new NotFoundException('Student not found.');
        }

        $activeEnrollments = $this->enrollments->findByStudent($studentId)
            ->where('status', 'active');

        $createdDues = collect();

        foreach ($activeEnrollments as $enrollment) {
            // Skip if due already exists for this period
            if ($this->dues->existsForPeriod($studentId, $enrollment->class_id, $year, $month)) {
                continue;
            }

            $pricing = $this->resolveDueAmount($enrollment, $amount);

            $due = $this->dues->create([
                'student_id'       => $studentId,
                'class_id'         => $enrollment->class_id,
                'enrollment_id'    => $enrollment->id,
                'period_year'      => $year,
                'period_month'     => $month,
                'amount'           => $pricing['amount'],
                'base_price'       => $pricing['base_price'],
                'discount_applied' => $pricing['discount_applied'],
                'price_override'   => $pricing['price_override'],
                'status'           => 'pending',
            ]);

            $createdDues->push($due);
        }

        return $createdDues;
    }

    /**
     * Create a single monthly due manually.
     */
    public function createDue(int $studentId, array $data): MonthlyDue
    {
        $student = $this->students->findWithMembership($studentId);

        if (!$student) {
            throw new NotFoundException('Student not found.');
        }

        // Check if due already exists
        if ($this->dues->existsForPeriod(
            $studentId,
            $data['class_id'],
            $data['period_year'],
            $data['period_month']
        )) {
            throw new BusinessException('Monthly due already exists for this period.');
        }

        return $this->dues->create([
            'student_id'    => $studentId,
            'class_id'      => $data['class_id'],
            'enrollment_id' => $data['enrollment_id'] ?? null,
            'period_year'   => $data['period_year'],
            'period_month'  => $data['period_month'],
            'amount'        => $data['amount'],
            'status'        => 'pending',
            'notes'         => $data['notes'] ?? null,
        ]);
    }

    /**
     * Mark a due as paid.
     */
    public function markAsPaid(int $dueId, ?int $purchaseId = null): MonthlyDue
    {
        $due = $this->dues->findById($dueId);

        if (!$due) {
            throw new NotFoundException('Monthly due not found.');
        }

        if ($due->status === 'paid') {
            throw new BusinessException('Due is already marked as paid.');
        }

        if ($due->status === 'cancelled') {
            throw new BusinessException('Cannot pay a cancelled due.');
        }

        $updated = $this->dues->update($dueId, [
            'status'              => 'paid',
            'paid_at'             => now(),
            'student_purchase_id' => $purchaseId,
        ]);

        return $updated;
    }

    /**
     * Waive a due (forgive the debt).
     */
    public function waive(int $dueId, ?string $notes = null): MonthlyDue
    {
        $due = $this->dues->findById($dueId);

        if (!$due) {
            throw new NotFoundException('Monthly due not found.');
        }

        if ($due->status === 'paid') {
            throw new BusinessException('Cannot waive a paid due.');
        }

        if ($due->status === 'waived') {
            throw new BusinessException('Due is already waived.');
        }

        $updateData = ['status' => 'waived'];

        if ($notes) {
            $updateData['notes'] = $notes;
        }

        return $this->dues->update($dueId, $updateData);
    }

    /**
     * Get the outstanding balance for a student.
     */
    public function getOutstandingBalance(int $studentId): float
    {
        return $this->dues->sumPendingByStudent($studentId);
    }

    /**
     * Get all dues for a student.
     */
    public function getDuesForStudent(int $studentId, array $filters = []): Collection
    {
        $student = $this->students->findWithMembership($studentId);

        if (!$student) {
            throw new NotFoundException('Student not found.');
        }

        return $this->dues->findByStudent($studentId, $filters);
    }

    /**
     * Get paginated dues for a student.
     */
    public function paginateDuesForStudent(int $studentId, array $filters = []): LengthAwarePaginator
    {
        $student = $this->students->findWithMembership($studentId);

        if (!$student) {
            throw new NotFoundException('Student not found.');
        }

        return $this->dues->paginateByStudent($studentId, $filters);
    }

    /**
     * Get payment summary for a student.
     */
    public function getPaymentSummary(int $studentId): array
    {
        $student = $this->students->findWithMembership($studentId);

        if (!$student) {
            throw new NotFoundException('Student not found.');
        }

        $totalPaid = $this->dues->sumPaidByStudent($studentId);
        $totalOutstanding = $this->dues->sumPendingByStudent($studentId);
        $pendingDues = $this->dues->getPendingByStudent($studentId);

        return [
            'student_id'       => $studentId,
            'total_paid'       => $totalPaid,
            'total_outstanding' => $totalOutstanding,
            'outstanding_dues' => $pendingDues,
        ];
    }

    /**
     * Batch generate dues for all active enrollments for a given month.
     */
    public function batchGenerate(int $year, int $month, ?float $amount = null): array
    {
        $stats = [
            'students_processed' => 0,
            'dues_created'       => 0,
            'dues_skipped'       => 0,
        ];

        // Get all active enrollments
        $activeEnrollments = \App\Models\ClassEnrollment::with(['student', 'courseClass'])
            ->where('status', 'active')
            ->get()
            ->groupBy('student_id');

        foreach ($activeEnrollments as $studentId => $enrollments) {
            $stats['students_processed']++;

            foreach ($enrollments as $enrollment) {
                // Skip if due already exists
                if ($this->dues->existsForPeriod($studentId, $enrollment->class_id, $year, $month)) {
                    $stats['dues_skipped']++;
                    continue;
                }

                $pricing = $this->resolveDueAmount($enrollment, $amount);

                $this->dues->create([
                    'student_id'       => $studentId,
                    'class_id'         => $enrollment->class_id,
                    'enrollment_id'    => $enrollment->id,
                    'period_year'      => $year,
                    'period_month'     => $month,
                    'amount'           => $pricing['amount'],
                    'base_price'       => $pricing['base_price'],
                    'discount_applied' => $pricing['discount_applied'],
                    'price_override'   => $pricing['price_override'],
                    'status'           => 'pending',
                ]);

                $stats['dues_created']++;
            }
        }

        return $stats;
    }

    private function resolveDueAmount(ClassEnrollment $enrollment, ?float $overrideAmount): array
    {
        if ($overrideAmount !== null) {
            return [
                'amount'           => $overrideAmount,
                'base_price'       => null,
                'discount_applied' => null,
                'price_override'   => true,
            ];
        }

        $basePrice = (float) ($enrollment->courseClass->monthly_price ?? 40.00);
        $percent   = (float) ($enrollment->discount_percent ?? 0);
        $fixed     = (float) ($enrollment->discount_amount ?? 0);

        $discounted = $basePrice * (1 - $percent / 100) - $fixed;
        $final      = max(0, round($discounted, 2));
        $discount   = round($basePrice - $final, 2);

        return [
            'amount'           => $final,
            'base_price'       => $basePrice,
            'discount_applied' => $discount,
            'price_override'   => false,
        ];
    }
}
