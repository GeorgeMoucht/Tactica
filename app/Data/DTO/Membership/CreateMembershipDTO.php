<?php

namespace App\Data\DTO\Membership;

use Carbon\Carbon;

class CreateMembershipDTO
{
    public function __construct(
        public int $student_id, 
        public Carbon $starts_at,
        public Carbon $ends_at,
        public ?Carbon $paid_at = null
    ) {}
}