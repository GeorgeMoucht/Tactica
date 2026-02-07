<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\BaseApiController;
use App\Http\Requests\Membership\StoreMembershipRequest;
use App\Http\Resources\Student\StudentDetailResource;
use App\Services\Membership\MembershipService;

class MembershipController extends BaseApiController
{
    private MembershipService $service;

    public function __construct(MembershipService $service)
    {
        $this->service = $service;
    }

    // POST /students/{student}/memberships
    public function store(StoreMembershipRequest $request, int $studentId)
    {
        $student = $this->service->createAnnualMembership(
            $studentId,
            $request->validated()
        );

        return $this->getSuccess(
            new StudentDetailResource($student),
            'Annual membership activated'
        );
    }
}