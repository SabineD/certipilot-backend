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
        Schema::create('certificates', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('machine_id')
                  ->nullable()
                  ->constrained()
                  ->cascadeOnDelete();

            $table->foreignUuid('employee_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->foreignUuid('company_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->string('certificate_type');
            $table->date('issued_at');
            $table->date('valid_until');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
