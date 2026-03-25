<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\BaseApiController;
use App\Services\Dashboard\DashboardService;

class DashboardController extends BaseApiController
{
    public function __construct(
        private readonly DashboardService $service
    ) {}

    /**
     * GET /dashboard/widgets
     *
     * Return all dashboard widget data in a single response.
     */
    public function widgets()
    {
        return $this->getSuccess(
            $this->service->widgets(),
            'Dashboard widgets retrieved'
        );
    }

    /**
     * GET /dashboard/financials
     */
    public function financials()
    {
        return $this->getSuccess(
            $this->service->financials(),
            'Dashboard financials retrieved'
        );
    }
}
