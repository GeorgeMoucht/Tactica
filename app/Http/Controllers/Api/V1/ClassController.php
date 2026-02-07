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
            'type' => $request->validated('type') ?? null,
            'active' => $request->has('active') ? filter_var($request->validated('active'), FILTER_VALIDATE_BOOLEAN) : null,
            'day_of_week' => $request->validated('day_of_week') ?? null,
            'teacher_id' => $request->validated('teacher_id') ?? null,
            'sort_by' => $request->validated('sort_by') ?? 'id',
            'sort_order' => $request->validated('sort_order') ?? 'desc',
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

        return $this->getSuccess(
            new ClassDetailResource($class),
            'Class retrieved'
        );
    }

    // PUT /api/v1/classes/{class}
    public function update(UpdateClassRequest $request, int $classId)
    {
        $class = $this->service->update($classId, $request->validated());

        return $this->getSuccess(
            new ClassDetailResource($class),
            'Class updated'
        );
    }

    // DELETE /api/v1/classes/{classId}
    public function destroy(int $classId)
    {
        $this->service->destroy($classId);

        return $this->getSuccess(null, 'Class deleted');
    }

    // PATCH /api/v1/classes/{classId}/toggle-active
    public function toggleActive(int $classId)
    {
        $class = $this->service->toggleActive($classId);

        return $this->getSuccess(
            new ClassDetailResource($class),
            'Class status toggled'
        );
    }
}
