<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentPurchase extends Model
{
    protected $fillable = [
        'student_id',
        'product_id',
        'amount',
        'paid_at'
    ];

    protected $casts = [
        'amount'    => 'decimal:2',
        'paid_at'   => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}