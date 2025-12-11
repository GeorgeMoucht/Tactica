<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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

        'is_member',
        'registration_date',
    ];

    protected $casts = [
        'address'               => 'array',
        'interests'             => 'array',
        'birthdate'             => 'date:Y-m-d',
        'consent_media'         => 'boolean',

        'is_member'             => 'boolean',
        'registration_date'     => 'date',
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
}
