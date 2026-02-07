<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\BaseApiController;
use App\Http\Requests\Guardian\IndexGuardianRequest;
use App\Http\Requests\Guardian\StoreGuardianRequest;
use App\Http\Requests\Guardian\UpdateGuardianRequest;
use App\Http\Resources\Guardian\GuardianDetailResource;
use App\Services\Guardian\GuardianService;

class GuardianController extends BaseApiController
{
    public function __construct(
        private GuardianService $service
    ) {}

    // POST /api/v1/guardians
    public function store(StoreGuardianRequest $request)
    {
        $guardian = $this->service->create($request->validated());

        return $this->actionSuccess('Guardian created', [
            'guardian_id' => $guardian->id
        ]);
    }

    // GET /api/v1/guardians
    public function index(IndexGuardianRequest $request)
    {
        $filters = [
            'query'     => $request->validated('query') ?? null,
            'perPage'   => (int)($request->validated('pageSize') ?? 10),
        ];

        $paginator = $this->service->list($filters);

        return $this->paginatedSuccess($paginator, 'Guardians retrieved');
    }

    // GET /api/v1/guardians/{id}
    public function show(int $guardianId)
    {
        $guardian = $this->service->detail($guardianId);

        return $this->getSuccess(
            new GuardianDetailResource($guardian),
            'Guardian retrieved'
        );
    }

    // PUT /api/v1/guardians/{id}
    public function update(UpdateGuardianRequest $request, int $guardianId)
    {
        $guardian = $this->service->update($guardianId, $request->validated());

        return $this->getSuccess(
            new GuardianDetailResource($guardian),
            'Guardian updated'
        );
    }
}