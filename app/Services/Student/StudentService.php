<?php

namespace App\Services\Student;

use App\Models\Student;
use App\Repositories\Contracts\StudentRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class StudentService
{
    public function __construct(private StudentRepository $students)
    {
        
    }

    /**
     * Returns a paginator of student list rows.
     * Structure per row: id, name, email, phone, level, created_at
     * 
     * @param array{q?:string|null, perPage?:int|null} $filters
     */
    public function list(array $filters = []): LengthAwarePaginator
    {
        $p = $this->students->paginateForList($filters);

        $mapped = $p->getCollection()->map(function (Student $s) {
            return [
                'id'            => $s->id,
                'name'          => trim($s->first_name.' '.$s->last_name),
                'email'         => $s->email,
                'phone'         => $s->phone,
                'level'         => $s->level,
                'created_at'    => $s->created_at?->toIso8601String(),
            ];
        });

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $mapped,
            $p->total(),
            $p->perPage(),
            $p->currentPage(),
            [
                'path'  => request()->url(),
                'query' => request()->query()
            ]
            );
    }

    /** 
     * Student detail with guardians (or null if not found)
     */
    public function detail(int $id): ?Student
    {
        return $this->students->findWithGuardians($id);
    }

    public function update(int $id, array $data): ?Student
    {
        return $this->students->update($id, $data);
    }
}