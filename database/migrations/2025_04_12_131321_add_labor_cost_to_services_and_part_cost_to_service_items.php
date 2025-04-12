<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->decimal('labor_cost', 10, 2)->nullable()->after('mileage');
        });

        Schema::table('service_items', function (Blueprint $table) {
            $table->decimal('part_cost', 10, 2)->nullable()->after('notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('labor_cost');
        });

        Schema::table('service_items', function (Blueprint $table) {
            $table->dropColumn('part_cost');
        });
    }
};
