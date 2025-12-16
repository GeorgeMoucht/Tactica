<?php

namespace App\Data\DTO\Purchase;

readonly class CreatePurchaseDTO
{
    public function __construct(
        public int $student_id,
        public int $product_id,
        public ?float $amount = null,
    ) {}

    public static function fromArray(array $data, int $studentId): self
    {
        return new self(
            student_id: $studentId, 
            product_id: $data['product_id'],
            amount: $data['amount'] ?? null
        );
    }
}