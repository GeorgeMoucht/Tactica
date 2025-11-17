<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Guardian extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
        'preferred_contact',
        'notes',
        'newsletter_consent'
    ];

    protected $casts = [
        'address' => 'array',
        'newsletter_consent' => 'boolean',
    ];

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class)
            ->withPivot('relation')
            ->withTimestamps();
    }

    public function getNameAttribute(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }
}
