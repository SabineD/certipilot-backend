<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('first_name')->default('')->after('site_id');
            $table->string('last_name')->default('')->after('first_name');
            $table->string('email')->nullable()->after('job_title');
            $table->boolean('is_active')->default(true)->after('email');
        });

        DB::table('employees')
            ->select('id', 'name', 'active')
            ->get()
            ->each(function ($employee) {
                $firstName = '';
                $lastName = '';

                if ($employee->name !== null) {
                    $name = trim($employee->name);
                    if ($name !== '') {
                        $parts = preg_split('/\s+/', $name, 2);
                        $firstName = $parts[0] ?? '';
                        $lastName = $parts[1] ?? '';
                    }
                }

                DB::table('employees')
                    ->where('id', $employee->id)
                    ->update([
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'is_active' => $employee->active ?? true,
                    ]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'last_name', 'email', 'is_active']);
        });
    }
};
