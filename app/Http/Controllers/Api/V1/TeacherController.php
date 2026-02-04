<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\BaseApiController;
use App\Http\Resources\User\TeacherListResource;
use App\Repositories\Contracts\UserRepository;
use App\Services\User\TeacherService;

class TeacherController extends BaseApiController
{
    private readonly TeacherService $service;

    public function __construct(TeacherService $service)
    {
        $this->service = $service;
    }

    // GET /api/v1/teachers
    public function index()
    {
        $teachers = $this->service->list();

        return $this->getSuccess(
            TeacherListResource::collection($teachers),
            'Teachers retrieved'
        );
    }
}