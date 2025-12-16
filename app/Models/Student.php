<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
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
        return $this->entitlements()
            ->whereDate('starts_at', '<=', today())
            ->whereDate('ends_at', '>=', today())
            ->wherehas('product', fn ($q) => $q->where('type', 'registration'))
            ->exists();
    }
}
