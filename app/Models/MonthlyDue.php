<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonthlyDue extends Model
{
    protected $fillable = [
        'student_id',
        'class_id',
        'enrollment_id',
        'period_year',
        'period_month',
        'amount',
        'base_price',
        'discount_applied',
        'price_override',
        'status',
        'student_purchase_id',
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'period_year'      => 'integer',
        'period_month'     => 'integer',
        'amount'           => 'decimal:2',
        'base_price'       => 'decimal:2',
        'discount_applied' => 'decimal:2',
        'price_override'   => 'boolean',
        'paid_at'          => 'datetime',
    ];

    protected $appends = ['period_label'];

    // ──────────────────────────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────────────────────────

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function courseClass(): BelongsTo
    {
        return $this->belongsTo(CourseClass::class, 'class_id');
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(ClassEnrollment::class, 'enrollment_id');
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(StudentPurchase::class, 'student_purchase_id');
    }

    // ──────────────────────────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────────────────────────

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', 'paid');
    }

    public function scopeForStudent(Builder $query, int $studentId): Builder
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeForPeriod(Builder $query, int $year, int $month): Builder
    {
        return $query->where('period_year', $year)->where('period_month', $month);
    }

    // ──────────────────────────────────────────────────────────────────
    // Accessors
    // ──────────────────────────────────────────────────────────────────

    public function getPeriodLabelAttribute(): string
    {
        $months = [
            1  => 'Ιαν',
            2  => 'Φεβ',
            3  => 'Μαρ',
            4  => 'Απρ',
            5  => 'Μάι',
            6  => 'Ιουν',
            7  => 'Ιουλ',
            8  => 'Αυγ',
            9  => 'Σεπ',
            10 => 'Οκτ',
            11 => 'Νοε',
            12 => 'Δεκ',
        ];

        $monthLabel = $months[$this->period_month] ?? '';

        return "{$monthLabel} {$this->period_year}";
    }
}
