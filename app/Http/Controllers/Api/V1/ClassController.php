<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\BaseApiController;
use App\Http\Requests\Class\IndexClassRequest;
use App\Http\Requests\Class\StoreClassRequest;
use App\Http\Requests\Class\UpdateClassRequest;
use App\Http\Resources\Class\ClassDetailResource;
use App\Http\Resources\Class\ClassListResource;
use App\Services\Class\ClassService;

class ClassController extends BaseApiController
{
    private readonly ClassService $service;

    public function __construct(ClassService $service)
    {
        $this->service = $service;
    }

    // GET /api/v1/classes
    public function index (IndexClassRequest $request)
    {
        $filters = [
            'query' => $request->validated('query') ?? null,
            'perPage' => (int)($request->validated('pageSize') ?? 10),
        ];

        $paginator = $this->service->list($filters);

        return $this->paginatedSuccess($paginator, 'Classes retrieved');
    }

    // POST \api\v1\classes
    public function store(StoreClassRequest $request)
    {
        $class = $this->service->create($request->validated());

        return $this->actionSuccess(
            'Class created',
            new ClassDetailResource($class)
        );
    }

    // GET /api/v1/classes/{class}
    public function show(int $classId)
    {
        $class = $this->service->detail($classId);

        if (!$class) {
            return $this->getError('Class not found', 404);
        }

        return $this->getSuccess(
            new ClassDetailResource($class),
            'Class retrieved'
        );
    }

    // PUT /api/v1/classes/{class}
    public function update(UpdateClassRequest $request, int $classId)
    {
        $class = $this->service->update($classId, $request->validated());

        if (!$class) {
            return $this->getError('Class not found', 404);
        }

        return $this->getSuccess(
            new ClassDetailResource($class),
            'Class updated'
        );
    }
}