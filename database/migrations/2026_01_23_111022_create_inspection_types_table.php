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
        Schema::create('inspection_types', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('name');
            $table->string('applies_to'); // machine | employee
            $table->unsignedInteger('validity_months');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspection_types');
    }
};
