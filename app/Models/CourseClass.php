<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseClass extends Model
{
    use HasFactory;

    protected $table='classes';

    protected $fillable = [
        'title',
        'description',
        'type',
        'active',
        'day_of_week',
        'starts_time',
        'ends_time',
        'capacity',
        'monthly_price',
        'teacher_id',
    ];

    protected $casts = [
        'day_of_week'   => 'integer',
        'capacity'      => 'integer',
        'active'        => 'boolean',
        'monthly_price' => 'decimal:2',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(ClassSession::class, 'class_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(ClassEnrollment::class, 'class_id');
    }

    public function activeEnrollments(): HasMany
    {
        return $this->hasMany(ClassEnrollment::class, 'class_id')->where('status', 'active');
    }

    public function enrolledStudents(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'class_enrollments', 'class_id', 'student_id')
            ->withPivot(['status', 'enrolled_at', 'withdrawn_at', 'notes'])
            ->withTimestamps();
    }

    public function getCurrentEnrollmentCountAttribute(): int
    {
        return $this->activeEnrollments()->count();
    }

    public function getHasCapacityAttribute(): bool
    {
        if ($this->capacity === null) {
            return true;
        }

        return $this->current_enrollment_count < $this->capacity;
    }
}