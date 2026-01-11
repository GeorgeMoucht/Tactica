<?php

namespace App\Repositories\Eloquent;

use App\Models\StudentEntitlement;
use App\Repositories\Contracts\StudentEntitlementRepository;
use Illuminate\Support\Collection;

class EloquentStudentEntitlementRepository implements StudentEntitlementRepository
{
    public function create(array $data): StudentEntitlement
    {
        return StudentEntitlement::create($data);
    }

    public function findMembershipsByStudent(int $studentId): Collection
    {
        return StudentEntitlement::query()
            ->with('product')
            ->where('student_id', $studentId)
            ->whereHas('product', fn ($q) =>
                $q->where('type', 'registration')
            )
            ->orderByDesc('starts_at')
            ->get();
    }
}
