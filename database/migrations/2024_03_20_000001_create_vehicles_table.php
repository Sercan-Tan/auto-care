<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('plate_no')->unique();
            $table->string('brand');
            $table->string('model');
            $table->integer('year');
            $table->string('chassis_no')->unique();
            $table->string('owner_name');
            $table->string('owner_phone');
            $table->string('owner_email')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};