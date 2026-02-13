<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\BaseApiController;
use App\Http\Requests\Enrollment\StoreEnrollmentRequest;
use App\Http\Requests\Enrollment\UpdateEnrollmentRequest;
use App\Http\Resources\Enrollment\EnrollmentResource;
use App\Services\Enrollment\EnrollmentService;
use Illuminate\Http\Request;

class EnrollmentController extends BaseApiController
{
    private EnrollmentService $service;

    public function __construct(EnrollmentService $service)
    {
        $this->service = $service;
    }

    // POST /students/{student}/enrollments
    public function store(StoreEnrollmentRequest $request, int $studentId)
    {
        $data = $request->validated();

        $enrollment = $this->service->enroll(
            $studentId,
            $data['class_id'],
            $data
        );

        return $this->actionSuccess(
            'Student enrolled successfully',
            new EnrollmentResource($enrollment)
        );
    }

    // GET /students/{student}/enrollments
    public function indexByStudent(int $studentId)
    {
        $enrollments = $this->service->getStudentEnrollments($studentId);

        return $this->getSuccess(
            EnrollmentResource::collection($enrollments),
            'Student enrollments retrieved'
        );
    }

    // GET /classes/{class}/enrollments
    public function indexByClass(Request $request, int $classId)
    {
        $filters = [
            'perPage' => $request->get('perPage', 20),
            'status'  => $request->get('status'),
        ];

        $paginator = $this->service->getClassEnrollments($classId, $filters);

        return $this->paginatedSuccess($paginator, 'Class enrollments retrieved', EnrollmentResource::class);
    }

    // PATCH /enrollments/{enrollment}/withdraw
    public function withdraw(int $enrollmentId)
    {
        $enrollment = $this->service->withdraw($enrollmentId);

        return $this->getSuccess(
            new EnrollmentResource($enrollment),
            'Enrollment withdrawn successfully'
        );
    }

    // PATCH /enrollments/{enrollment}/discount
    public function updateDiscount(UpdateEnrollmentRequest $request, int $enrollmentId)
    {
        $enrollment = $this->service->updateDiscount($enrollmentId, $request->validated());

        return $this->getSuccess(
            new EnrollmentResource($enrollment),
            'Enrollment discount updated successfully'
        );
    }
}
