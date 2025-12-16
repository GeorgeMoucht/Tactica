<?php

namespace App\Services\Purchase;

use App\Data\DTO\Purchase\CreatePurchaseDTO;
use App\Models\Product;
use App\Repositories\Contracts\StudentEntitlementRepository;
use App\Repositories\Contracts\StudentPurchaseRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseService
{
    private StudentPurchaseRepository $studentPurchaseRepository;
    private StudentEntitlementRepository $studentEntitlementRepository;


    public function __construct(
        StudentPurchaseRepository $studentPurchaseRepository,
        StudentEntitlementRepository $studentEntitlementRepository
    ) {
        $this->studentPurchaseRepository = $studentPurchaseRepository;
        $this->studentEntitlementRepository = $studentEntitlementRepository;
    }

    public function create(CreatePurchaseDTO $dto): void
    {
        DB::transaction(function () use ($dto) {
            $product = Product::findOrFail($dto->product_id);

            if (!$product->duration_days) {
                throw ValidationException::withMessages([
                    'product_id' => 'Product has no duration defined'
                ]);
            }

            $amount = $dto->amount ?? $product->price;

            $purchase = $this->studentPurchaseRepository->create([
                'student_id'    => $dto->student_id,
                'product_id'    => $product->id,
                'amount'        => $amount,
                'paid_at'       => now(),
            ]);

            $this->studentEntitlementRepository->create([
                'student_id'    => $dto->student_id,
                'product_id'    => $product->id,
                'student_purchase_id'    => $purchase->id,
                'starts_at'     => today(),
                'ends_at'       => today()->addDays($product->duration_days),
            ]);
        });
    }
}