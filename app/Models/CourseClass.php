<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseClass extends Model
{
    protected $table='classes';

    protected $fillable = [
        'title',
        'description',
        'day_of_week',
        'starts_time',
        'ends_time',
        'capacity',
        'teacher_id',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
        'capacity'    => 'integer'
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}