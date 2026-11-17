<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wishlist extends Model
{
    use HasFactory;

    // Isko fillable mein daalna zaroori hai error se bachne ke liye
    protected $fillable = ['user_id', 'product_id'];

    // Product ke sath relation
    public function product() {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
