<?php

namespace App\Repositories\Eloquent;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepository;

class EloquentProductRepository implements ProductRepository
{
    public function findRegistrationProduct(): ?Product
    {
        return Product::where('type', 'registration')->firstOrFail();
    }
}