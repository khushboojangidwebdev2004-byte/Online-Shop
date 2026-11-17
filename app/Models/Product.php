<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    // Relationship: Ek product ki multiple images ho sakti hain
    public function product_images() {
        return $this->hasMany(ProductImage::class);
    }
    // Naya relation add karein
    public function product_ratings() {
        return $this->hasMany(ProductRating::class)->where('status', 1);
    }
}
