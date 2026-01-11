<?php

namespace App\Services\Student;

use App\Repositories\Contracts\StudentRepository;
use App\Repositories\Contracts\StudentEntitlementRepository;

class StudentHistoryService
{
    private readonly StudentRepository $students;
    private readonly StudentEntitlementRepository $entitlements;

    public function __construct(StudentRepository $students, StudentEntitlementRepository $entitlements)
    {
        $this->students = $students;
        $this->entitlements = $entitlements;
    }

    public function getHistory(int $studentId): ?array
    {
        $student = $this->students->findWithHistory($studentId);

        if (!$student) {
            return null;
        }

        
        return [
            'student_id' => $studentId,
            'memberships' => $this->entitlements->findMembershipsByStudent($studentId),
        ];
    }
}