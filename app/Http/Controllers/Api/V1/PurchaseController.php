<?php

namespace App\Http\Controllers\Api\V1;

use App\Data\DTO\Purchase\CreatePurchaseDTO;
use App\Http\Controllers\BaseApiController;
use App\Http\Requests\Purchase\StorePurchaseRequest;
use App\Services\Purchase\PurchaseService;

class PurchaseController extends BaseApiController
{
    private PurchaseService $purchaseService;

    public function __construct(PurchaseService $purchaseService)
    {
        $this->purchaseService = $purchaseService;
    }

    public function store(StorePurchaseRequest $request, int $student)
    {
        $dto = CreatePurchaseDTO::fromArray(
            $request->validated(),
            $student
        );

        $this->purchaseService->create($dto);

        return $this->actionSuccess('Purchase completed');
    }
}