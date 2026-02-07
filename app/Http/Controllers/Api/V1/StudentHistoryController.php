<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\BaseApiController;
use App\Http\Resources\Student\StudentHistoryResource;
use App\Services\Student\StudentHistoryService;

class StudentHistoryController extends BaseApiController
{
    private readonly StudentHistoryService $service;

    public function __construct(StudentHistoryService $service)
    {
        $this->service = $service;
    }

    public function index(int $studentId)
    {
        $history = $this->service->getHistory($studentId);

        return $this->getSuccess(
            new StudentHistoryResource($history),
            'Student history retrieved'
        );
    }
}