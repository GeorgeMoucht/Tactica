<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class ClassEnrollment extends Model
{
    use HasFactory;

    protected $table = 'class_enrollments';

    protected $fillable = [
        'student_id',
        'class_id',
        'status',
        'enrolled_at',
        'withdrawn_at',
        'notes',
        'discount_percent',
        'discount_amount',
        'discount_note',
    ];

    protected $casts = [
        'enrolled_at'      => 'date',
        'withdrawn_at'     => 'date',
        'discount_percent' => 'decimal:2',
        'discount_amount'  => 'decimal:2',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function courseClass(): BelongsTo
    {
        return $this->belongsTo(CourseClass::class, 'class_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeForClass(Builder $query, int $classId): Builder
    {
        return $query->where('class_id', $classId);
    }

    public function scopeForStudent(Builder $query, int $studentId): Builder
    {
        return $query->where('student_id', $studentId);
    }

    public function getEffectivePriceAttribute(): float
    {
        $basePrice = (float) ($this->courseClass->monthly_price ?? 40.00);
        $percent   = (float) ($this->discount_percent ?? 0);
        $fixed     = (float) ($this->discount_amount ?? 0);

        return max(0, $basePrice * (1 - $percent / 100) - $fixed);
    }
}
