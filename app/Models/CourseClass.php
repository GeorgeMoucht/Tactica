<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseClass extends Model
{
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
        'teacher_id',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
        'capacity'    => 'integer',
        'active'      => 'boolean',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(ClassSession::class, 'class_id');
    }
}