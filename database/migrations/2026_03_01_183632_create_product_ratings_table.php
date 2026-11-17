<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_ratings', function (Blueprint $table) {
            $table->id();
            // Foreign key relation product_id ke sath
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('username');
            $table->string('email');
            $table->text('comment');
            $table->double('rating', 3, 2);
            $table->integer('status')->default(0); // Default 0 (Block)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_ratings');
    }
};
