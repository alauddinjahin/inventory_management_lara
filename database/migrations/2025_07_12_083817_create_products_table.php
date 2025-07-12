<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->text('description');
            $table->decimal('price', 10, 2);
            $table->integer('quantity');
            $table->unsignedBigInteger('category_id');
            $table->timestamps();
            
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->index(['category_id']);
            $table->index(['name']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
};