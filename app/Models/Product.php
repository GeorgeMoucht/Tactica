<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'name',
        'type',             // registration | subscription | workshop
        'price',
        'billing_period',   // year | month | one_time
        'duration_days',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function purchases(): HasMany
    {
        return $this->hasMany(StudentPurchase::class);
    }

    public function entitlements(): HasMany
    {
        return $this->hasMany(StudentEntitlement::class);
    }
}