<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\BaseApiController;
use App\Http\Requests\Attendance\AttendanceHistoryRequest;
use App\Http\Requests\Attendance\AttendanceSummaryRequest;
use App\Http\Requests\Attendance\StoreAttendanceRequest;
use App\Http\Resources\Attendance\AttendanceHistorySessionResource;
use App\Http\Resources\Attendance\AttendanceRosterResource;
use App\Http\Resources\Attendance\AttendanceSummaryResource;
use App\Http\Resources\Attendance\TodaySessionResource;
use App\Services\Attendance\AttendanceService;

class AttendanceController extends BaseApiController
{
    private AttendanceService $service;

    public function __construct(AttendanceService $service)
    {
        $this->service = $service;
    }

    // GET /dashboard/today-sessions
    public function todaySessions()
    {
        $sessions = $this->service->getTodaySessions();

        return $this->getSuccess(
            TodaySessionResource::collection($sessions),
            'Today\'s sessions retrieved'
        );
    }

    // GET /sessions/{sessionId}/attendance
    public function roster(int $sessionId)
    {
        $roster = $this->service->getSessionRoster($sessionId);

        return $this->getSuccess(
            new AttendanceRosterResource($roster),
            'Session roster retrieved'
        );
    }

    // POST /sessions/{sessionId}/attendance
    public function store(StoreAttendanceRequest $request, int $sessionId)
    {
        $this->service->markAttendance(
            $sessionId,
            $request->validated('attendances'),
            $request->validated('conducted_by'),
            $request->user()->id
        );

        return $this->actionSuccess('Attendance recorded successfully');
    }

    // GET /classes/{classId}/attendance-history
    public function history(AttendanceHistoryRequest $request, int $classId)
    {
        $paginator = $this->service->getClassAttendanceHistory($classId, $request->validated());

        return $this->paginatedSuccess($paginator, 'Attendance history retrieved', AttendanceHistorySessionResource::class);
    }

    // GET /classes/{classId}/attendance-summary
    public function summary(AttendanceSummaryRequest $request, int $classId)
    {
        $summary = $this->service->getClassAttendanceSummary(
            $classId, $request->validated('from'), $request->validated('to')
        );

        return $this->getSuccess(new AttendanceSummaryResource($summary), 'Attendance summary retrieved');
    }
}
