<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\BaseApiController;
use App\Http\Requests\Payment\GenerateMonthlyDuesRequest;
use App\Http\Requests\Payment\PayDueRequest;
use App\Http\Requests\Payment\StoreMonthlyDueRequest;
use App\Http\Requests\Payment\WaiveDueRequest;
use App\Http\Resources\Payment\MonthlyDueResource;
use App\Services\Payment\MonthlyDueService;
use Illuminate\Http\Request;

class MonthlyDueController extends BaseApiController
{
    private MonthlyDueService $service;

    public function __construct(MonthlyDueService $service)
    {
        $this->service = $service;
    }

    /**
     * GET /students/{student}/monthly-dues
     *
     * List all monthly dues for a student with optional filters.
     */
    public function index(Request $request, int $studentId)
    {
        $filters = [
            'perPage' => $request->get('perPage', 15),
            'status'  => $request->get('status'),
            'year'    => $request->get('year'),
            'month'   => $request->get('month'),
        ];

        $paginator = $this->service->paginateDuesForStudent($studentId, $filters);

        return $this->paginatedSuccess(
            $paginator,
            'Monthly dues retrieved',
            MonthlyDueResource::class
        );
    }

    /**
     * POST /students/{student}/monthly-dues
     *
     * Manually create a monthly due for a student.
     */
    public function store(StoreMonthlyDueRequest $request, int $studentId)
    {
        $data = $request->validated();

        $due = $this->service->createDue($studentId, $data);

        return $this->actionSuccess(
            'Monthly due created successfully',
            new MonthlyDueResource($due->load('courseClass'))
        );
    }

    /**
     * PATCH /monthly-dues/{due}/pay
     *
     * Mark a due as paid.
     */
    public function pay(PayDueRequest $request, int $dueId)
    {
        $purchaseId = $request->validated()['student_purchase_id'] ?? null;

        $due = $this->service->markAsPaid($dueId, $purchaseId);

        return $this->getSuccess(
            new MonthlyDueResource($due->load('courseClass')),
            'Due marked as paid'
        );
    }

    /**
     * PATCH /monthly-dues/{due}/waive
     *
     * Waive a due (forgive the debt).
     */
    public function waive(WaiveDueRequest $request, int $dueId)
    {
        $notes = $request->validated()['notes'] ?? null;

        $due = $this->service->waive($dueId, $notes);

        return $this->getSuccess(
            new MonthlyDueResource($due->load('courseClass')),
            'Due waived successfully'
        );
    }

    /**
     * POST /monthly-dues/generate
     *
     * Batch generate monthly dues for all active enrollments.
     */
    public function generate(GenerateMonthlyDuesRequest $request)
    {
        $data = $request->validated();

        $stats = $this->service->batchGenerate(
            $data['year'],
            $data['month'],
            $data['amount'] ?? null
        );

        return $this->actionSuccess(
            'Monthly dues generated successfully',
            $stats
        );
    }
}
