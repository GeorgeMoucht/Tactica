<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentEntitlement extends Model
{
    protected $fillable = [
        'student_id',
        'product_id',
        'student_purchase_id',
        'starts_at',
        'ends_at'
    ];

    protected $casts = [
        'starts_at' => 'date',
        'ends_at'   => 'date'
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(StudentPurchase::class, 'student_purchase_id');
    }
}