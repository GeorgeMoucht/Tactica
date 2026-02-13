<?php

namespace App\Services\Enrollment;

use App\Exceptions\BusinessException;
use App\Exceptions\ConflictException;
use App\Exceptions\NotFoundException;
use App\Models\ClassEnrollment;
use App\Repositories\Contracts\ClassRepository;
use App\Repositories\Contracts\EnrollmentRepository;
use App\Repositories\Contracts\MonthlyDueRepository;
use App\Repositories\Contracts\StudentRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EnrollmentService
{
    private EnrollmentRepository $enrollments;
    private StudentRepository $students;
    private ClassRepository $classes;
    private MonthlyDueRepository $monthlyDues;

    public function __construct(
        EnrollmentRepository $enrollments,
        StudentRepository $students,
        ClassRepository $classes,
        MonthlyDueRepository $monthlyDues
    ) {
        $this->enrollments = $enrollments;
        $this->students = $students;
        $this->classes = $classes;
        $this->monthlyDues = $monthlyDues;
    }

    public function enroll(int $studentId, int $classId, array $data = []): ClassEnrollment
    {
        $student = $this->students->findWithMembership($studentId);

        if (!$student) {
            throw new NotFoundException('Student not found.');
        }

        if (!$student->is_member) {
            throw new BusinessException('Student must have an active membership to enroll.');
        }

        $class = $this->classes->findDetail($classId);

        if (!$class) {
            throw new NotFoundException('Class not found.');
        }

        if ($class->type !== 'weekly') {
            throw new BusinessException('Enrollment is only allowed for weekly classes.');
        }

        if (!$class->active) {
            throw new BusinessException('Cannot enroll in an inactive class.');
        }

        if ($this->enrollments->isStudentEnrolled($studentId, $classId)) {
            throw new ConflictException('Student is already enrolled in this class.');
        }

        if ($class->capacity !== null) {
            $currentCount = $this->enrollments->countActiveEnrollments($classId);
            if ($currentCount >= $class->capacity) {
                throw new BusinessException('Class has reached maximum capacity.');
            }
        }

        return DB::transaction(function () use ($studentId, $classId, $class, $data) {
            $enrollment = $this->enrollments->create([
                'student_id'       => $studentId,
                'class_id'         => $classId,
                'enrolled_at'      => $data['enrolled_at'] ?? now()->toDateString(),
                'notes'            => $data['notes'] ?? null,
                'discount_percent' => $data['discount_percent'] ?? 0,
                'discount_amount'  => $data['discount_amount'] ?? 0,
                'discount_note'    => $data['discount_note'] ?? null,
            ]);

            // Generate monthly due for current month if it doesn't exist
            $year  = now()->year;
            $month = now()->month;

            if (!$this->monthlyDues->existsForPeriod($studentId, $classId, $year, $month)) {
                $basePrice = (float) ($class->monthly_price ?? 40.00);
                $percent   = (float) ($data['discount_percent'] ?? 0);
                $fixed     = (float) ($data['discount_amount'] ?? 0);

                $discounted = $basePrice * (1 - $percent / 100) - $fixed;
                $final      = max(0, round($discounted, 2));
                $discount   = round($basePrice - $final, 2);

                $this->monthlyDues->create([
                    'student_id'       => $studentId,
                    'class_id'         => $classId,
                    'enrollment_id'    => $enrollment->id,
                    'period_year'      => $year,
                    'period_month'     => $month,
                    'amount'           => $final,
                    'base_price'       => $basePrice,
                    'discount_applied' => $discount,
                    'price_override'   => false,
                    'status'           => 'pending',
                ]);
            }

            return $enrollment->load(['student', 'courseClass', 'courseClass.teacher']);
        });
    }

    public function withdraw(int $enrollmentId): ClassEnrollment
    {
        $enrollment = $this->enrollments->findById($enrollmentId);

        if (!$enrollment) {
            throw new NotFoundException('Enrollment not found.');
        }

        if ($enrollment->status === 'withdrawn') {
            throw new BusinessException('Enrollment is already withdrawn.');
        }

        $updated = $this->enrollments->updateStatus(
            $enrollmentId,
            'withdrawn',
            now()->toDateString()
        );

        return $updated;
    }

    public function updateDiscount(int $enrollmentId, array $data): ClassEnrollment
    {
        $enrollment = $this->enrollments->findById($enrollmentId);

        if (!$enrollment) {
            throw new NotFoundException('Enrollment not found.');
        }

        if ($enrollment->status === 'withdrawn') {
            throw new BusinessException('Cannot update discount on a withdrawn enrollment.');
        }

        $updated = $this->enrollments->update($enrollmentId, [
            'discount_percent' => $data['discount_percent'] ?? $enrollment->discount_percent,
            'discount_amount'  => $data['discount_amount'] ?? $enrollment->discount_amount,
            'discount_note'    => array_key_exists('discount_note', $data) ? $data['discount_note'] : $enrollment->discount_note,
        ]);

        return $updated;
    }

    public function getStudentEnrollments(int $studentId): Collection
    {
        $student = $this->students->findWithMembership($studentId);

        if (!$student) {
            throw new NotFoundException('Student not found.');
        }

        return $this->enrollments->findByStudent($studentId);
    }

    public function getClassEnrollments(int $classId, array $filters = []): LengthAwarePaginator
    {
        $class = $this->classes->findDetail($classId);

        if (!$class) {
            throw new NotFoundException('Class not found.');
        }

        return $this->enrollments->paginateByClass($classId, $filters);
    }
}
