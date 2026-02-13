<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Student extends Model
{
    use HasFactory;

    protected $appends = ['is_member'];

    protected $fillable = [
        'first_name',
        'last_name',
        'birthdate',
        'email',
        'phone',
        'preferred_contact',
        'contact_notes',
        'address',
        'level',
        'interests',
        'notes',
        'medical_note',
        'consent_media',
    ];

    protected $casts = [
        'address'               => 'array',
        'interests'             => 'array',
        'birthdate'             => 'date:Y-m-d',
        'consent_media'         => 'boolean',
    ];

    public function guardians(): BelongsToMany
    {
        return $this->belongsToMany(Guardian::class)
            ->withPivot('relation')
            ->withTimestamps();
    }

    public function getNameAttribute(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(StudentPurchase::class);
    }

    public function entitlements(): HasMany
    {
        return $this->hasMany(StudentEntitlement::class);
    }

    public function getIsMemberAttribute(): bool
    {
        if (!$this->relationLoaded('entitlements')) {
            $this->load('entitlements.product');
        }

        return $this->entitlements->contains(fn ($e) =>
            $e->starts_at <= today() &&
            $e->ends_at >= today() &&
            $e->product?->type === 'registration'
        );
        // return $this->entitlements()
        //     ->whereDate('starts_at', '<=', today())
        //     ->whereDate('ends_at', '>=', today())
        //     ->wherehas('product', fn ($q) => $q->where('type', 'registration'))
        //     ->exists();
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(ClassEnrollment::class);
    }

    public function activeEnrollments(): HasMany
    {
        return $this->hasMany(ClassEnrollment::class)->where('status', 'active');
    }

    public function enrolledClasses(): BelongsToMany
    {
        return $this->belongsToMany(CourseClass::class, 'class_enrollments', 'student_id', 'class_id')
            ->withPivot(['status', 'enrolled_at', 'withdrawn_at', 'notes'])
            ->withTimestamps();
    }

    public function monthlyDues(): HasMany
    {
        return $this->hasMany(MonthlyDue::class);
    }
}
