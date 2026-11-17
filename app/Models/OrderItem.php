<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderItem extends Model
{
    use HasFactory;

    // YAHAN FIX KIYA HAI: Product relation add karna
    public function product() {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
