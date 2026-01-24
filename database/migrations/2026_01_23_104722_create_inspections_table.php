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
        Schema::create('inspections', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('inspection_type_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->foreignUuid('machine_id')
                  ->nullable()
                  ->constrained()
                  ->cascadeOnDelete();

            $table->foreignUuid('employee_id')
                  ->nullable()
                  ->constrained()
                  ->cascadeOnDelete();

            $table->date('inspection_date');
            $table->date('expiry_date');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspections');
    }
};
