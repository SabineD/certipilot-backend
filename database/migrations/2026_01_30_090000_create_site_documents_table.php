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
        Schema::create('site_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('site_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('name');
            $table->enum('type', ['safety_plan', 'permit', 'toolbox', 'other']);
            $table->enum('status', ['valid', 'expired', 'expiring_soon']);
            $table->timestamp('uploaded_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_documents');
    }
};
