<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\BaseApiController;
use App\Http\Requests\Student\IndexStudentRequest;
use App\Http\Requests\Student\UpdateStudentRequest;
use App\Http\Resources\Student\StudentDetailResource;
use App\Services\Student\StudentService;

class StudentController extends BaseApiController
{
    public function __construct(
        private StudentService $service
    ) {}

    // Get /api/v1/students
    public function index(IndexStudentRequest $request)
    {
        $filters = [
            'q'         => $request->validated('q') ?? null,
            'perPage'   => (int)($request->validated('pageSize') ?? 10),
        ];

        $paginator = $this->service->list($filters);

        return $this->paginatedSuccess(
            $paginator,
            'Students retrieved'
        );
    }

    // GET /api/v1/students/{id}
    public function show(int $studentId)
    {
        $student = $this->service->detail($studentId);

        if (!$student) {
            return $this->getError('Student not found', 404);
        }

        return $this->getSuccess(
            new StudentDetailResource($student),
            'Student retrieved'
        );
    }


    public function update(UpdateStudentRequest $request, int $studentId)
    {
        $student = $this->service->update(
            $studentId,
            $request->validated()
        );

        if (!$student) {
            return $this->getError('Student not found', 404);
        }

        return $this->getSuccess(
            new StudentDetailResource($student),
            'Student updated'
        );
    }
}