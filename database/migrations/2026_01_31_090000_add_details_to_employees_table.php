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
        });

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE employees CHANGE active is_active TINYINT(1) NOT NULL DEFAULT 1');
        } else {
            Schema::table('employees', function (Blueprint $table) {
                $table->renameColumn('active', 'is_active');
            });
        }

        DB::table('employees')
            ->select('id', 'name')
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
                    ]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE employees CHANGE is_active active TINYINT(1) NOT NULL DEFAULT 1');
        } else {
            Schema::table('employees', function (Blueprint $table) {
                $table->renameColumn('is_active', 'active');
            });
        }

        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'last_name', 'email']);
        });
    }
};
