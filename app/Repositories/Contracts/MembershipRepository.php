<?php

namespace App\Repositories\Contracts;

use App\Data\DTO\Membership\CreateMembershipDTO;
use App\Models\Product;

interface MembershipRepository
{
    public function createMembership(
        CreateMembershipDTO $dto,
        Product $product
    ): void;

    public function hasActiveMembership(int $studentId): bool;
}