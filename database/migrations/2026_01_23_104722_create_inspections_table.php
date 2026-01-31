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

            $table->foreignUuid('machine_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->foreignUuid('employee_id')
                  ->nullable()
                  ->constrained()
                  ->cascadeOnDelete();

            $table->foreignUuid('company_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->string('inspection_type');
            $table->date('inspected_at');
            $table->date('valid_until');

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
