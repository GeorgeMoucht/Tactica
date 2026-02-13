<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\BaseApiController;
use App\Http\Resources\Payment\PaymentSummaryResource;
use App\Services\Payment\MonthlyDueService;

class PaymentSummaryController extends BaseApiController
{
    private MonthlyDueService $service;

    public function __construct(MonthlyDueService $service)
    {
        $this->service = $service;
    }

    /**
     * GET /students/{student}/payment-summary
     *
     * Get a summary of payments and outstanding balance for a student.
     */
    public function show(int $studentId)
    {
        $summary = $this->service->getPaymentSummary($studentId);

        return $this->getSuccess(
            new PaymentSummaryResource($summary),
            'Payment summary retrieved'
        );
    }
}
