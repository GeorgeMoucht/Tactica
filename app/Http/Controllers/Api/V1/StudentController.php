<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\BaseApiController;
use App\Http\Requests\Student\IndexStudentRequest;
use App\Http\Requests\Student\StoreStudentRequest;
use App\Http\Requests\Student\UpdateStudentRequest;
use App\Http\Resources\Student\StudentDetailResource;
use App\Services\Student\StudentService;

class StudentController extends BaseApiController
{
    public function __construct(
        private StudentService $service
    ) {}

    // POST /api/v1/students
    public function store(StoreStudentRequest $request)
    {
        [$studentId, $guardianIds] = $this->service->createWithGuardians(
            $request->validated()
        );

        return $this->actionSuccess('Student created', [
            'student_id'    => $studentId,
            'guardian_ids'  => $guardianIds
        ]);
    }

    // Get /api/v1/students
    public function index(IndexStudentRequest $request)
    {
        $filters = [
            'query'         => $request->validated('query') ?? null,
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

        return $this->getSuccess(
            new StudentDetailResource($student),
            'Student updated'
        );
    }
}