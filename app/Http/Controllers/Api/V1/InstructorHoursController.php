<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\BaseApiController;
use App\Services\Instructor\InstructorHoursService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class InstructorHoursController extends BaseApiController
{
    public function __construct(
        private readonly InstructorHoursService $service
    ) {}

    /**
     * GET /hours/instructors
     *
     * Query params: from, to, week (ISO week e.g. "2026-W11")
     */
    public function index(Request $request)
    {
        if ($week = $request->query('week')) {
            $from = Carbon::parse($week)->startOfWeek();
            $to   = $from->copy()->endOfWeek();
        } elseif ($request->query('from') && $request->query('to')) {
            $from = Carbon::parse($request->query('from'))->startOfDay();
            $to   = Carbon::parse($request->query('to'))->endOfDay();
        } else {
            $from = Carbon::now()->startOfWeek();
            $to   = Carbon::now()->endOfWeek();
        }

        $data = $this->service->getHoursByPeriod($from, $to);

        return $this->getSuccess($data, 'Instructor hours retrieved');
    }
}
