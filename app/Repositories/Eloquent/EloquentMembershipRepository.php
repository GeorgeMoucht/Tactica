<?php

namespace App\Repositories\Eloquent;

use App\Data\DTO\Membership\CreateMembershipDTO;
use App\Models\StudentEntitlement;
use App\Models\StudentPurchase;
use App\Models\Product;
use App\Repositories\Contracts\MembershipRepository;

class EloquentMembershipRepository implements MembershipRepository
{
    public function createMembership(CreateMembershipDTO $dto, Product $product): void
    {
        $purchase = StudentPurchase::create([
            'student_id' => $dto->student_id,
            'product_id' => $product->id,
            'amount'     => $product->price,
            'paid_at'    => $dto->paid_at
        ]);

        StudentEntitlement::create([
            'student_id'            => $dto->student_id,
            'product_id'            => $product->id,
            'student_purchase_id'   => $purchase->id,
            'starts_at'             => $dto->starts_at,
            'ends_at'               => $dto->ends_at
        ]);
    }

    public function hasActiveMembership(int $studentId): bool
    {
        return StudentEntitlement::query()
            ->where('student_id', $studentId)
            ->whereDate('starts_at', '<=', today())
            ->whereDate('ends_at', '>=', today())
            ->whereHas('product', fn ($q) => $q->where('type', 'registration'))
            ->exists();
    }
}