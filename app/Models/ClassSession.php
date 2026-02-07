<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassSession extends Model
{
    protected $table = 'class_sessions';

    protected $fillable = [
        'class_id',
        'date',
        'starts_time',
        'ends_time',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function courseClass(): BelongsTo
    {
        return $this->belongsTo(CourseClass::class, 'class_id');
    }
}
