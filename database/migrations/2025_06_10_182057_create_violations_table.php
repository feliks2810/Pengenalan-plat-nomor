<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('violations', function (Blueprint $table) {
            $table->id(); // Primary key otomatis
            $table->string('description')->nullable();
            $table->string('plateNumber')->nullable();
            $table->float('plateConfidence')->nullable();
            $table->string('violationType')->nullable();
            $table->float('helmConfidence')->nullable();
            $table->string('imageFile')->nullable();
            $table->string('image_path')->nullable();
            $table->timestamp('timestamp');
            $table->timestamps(); // created_at dan updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('violations');
    }
};