<?php

namespace App\Services\Membership;

use App\Data\DTO\Membership\CreateMembershipDTO;
use App\Exceptions\BusinessException;
use App\Exceptions\NotFoundException;
use App\Models\Student;
use App\Repositories\Contracts\MembershipRepository;
use App\Repositories\Contracts\ProductRepository;
use App\Repositories\Contracts\StudentRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MembershipService
{
    private MembershipRepository $memberships;
    private StudentRepository $students;
    private ProductRepository $products;

    public function __construct(MembershipRepository $memberships, StudentRepository $students, ProductRepository $products)
    {
        $this->memberships = $memberships;
        $this->students = $students;
        $this->products = $products;
    }

    public function createAnnualMembership(int $studentId, array $data): Student
    {
        $student = $this->students->findWithMembership($studentId);

        if (!$student) {
            throw new NotFoundException('Student not found.');
        }

        $product = $this->products->findRegistrationProduct();

        if ($this->memberships->hasActiveMembership($studentId)) {
            throw new BusinessException('Student already has an active membership.');
        }

        $dto = new CreateMembershipDTO(
            student_id: $studentId,
            starts_at: Carbon::parse($data['starts_at']),
            ends_at: Carbon::parse($data['ends_at']),
            paid_at: isset($data['paid_at'])
                ? Carbon::parse($data['paid_at'])
                : now()
        );

        DB::transaction(function () use ($dto, $product) {
            $this->memberships->createMembership($dto, $product);
        });

        return $this->students->findWithMembership($studentId);
    }
}