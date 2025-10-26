<?php

namespace App\Http\Controllers\Api\V1;

use App\Data\DTO\Registration\CreateRegistrationDTO;
use App\Http\Controllers\BaseApiController;
use App\Http\Requests\Registration\IndexRegistrationRequest;
use App\Http\Requests\Registration\StoreRegistrationRequest;
use App\Http\Resources\Registration\RegistrationListResource;
use App\Repositories\Contracts\GuardianRepository;
use App\Services\Registration\RegistrationService;

class RegistrationController extends BaseApiController
{
    public function __construct(
        private RegistrationService $service,
        private GuardianRepository $guardians
    ) {}

    public function store(StoreRegistrationRequest $request)
    {
        $dto = CreateRegistrationDTO::fromArray($request->validated());
        [$guardianId, $studentIds] = $this->service->create($dto);

        return $this->getSuccess([
            'guardian_id' => $guardianId,
            'student_ids' => $studentIds,
        ], 'Registration created');
    }
}