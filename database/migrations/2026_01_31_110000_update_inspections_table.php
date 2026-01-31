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
        Schema::table('inspections', function (Blueprint $table) {
            if (!Schema::hasColumn('inspections', 'company_id')) {
                $table->foreignUuid('company_id')->nullable()->after('machine_id');
            }
            if (!Schema::hasColumn('inspections', 'inspection_type')) {
                $table->string('inspection_type')->nullable()->after('company_id');
            }
            if (!Schema::hasColumn('inspections', 'inspected_at')) {
                $table->date('inspected_at')->nullable()->after('inspection_type');
            }
            if (!Schema::hasColumn('inspections', 'valid_until')) {
                $table->date('valid_until')->nullable()->after('inspected_at');
            }
        });

        $hasOldColumns = Schema::hasColumn('inspections', 'inspection_date')
            || Schema::hasColumn('inspections', 'expiry_date')
            || Schema::hasColumn('inspections', 'inspection_type_id');

        if ($hasOldColumns) {
            $inspectionTypes = DB::table('inspection_types')->pluck('name', 'id');
            $machineCompanies = DB::table('machines')->pluck('company_id', 'id');
            $employeeCompanies = DB::table('employees')->pluck('company_id', 'id');

            DB::table('inspections')
                ->select('id', 'inspection_type_id', 'inspection_date', 'expiry_date', 'machine_id', 'employee_id')
                ->get()
                ->each(function ($inspection) use ($inspectionTypes, $machineCompanies, $employeeCompanies) {
                    $inspectionType = null;
                    if (!empty($inspection->inspection_type_id)) {
                        $inspectionType = $inspectionTypes[$inspection->inspection_type_id] ?? null;
                    }

                    $companyId = null;
                    if (!empty($inspection->machine_id)) {
                        $companyId = $machineCompanies[$inspection->machine_id] ?? null;
                    } elseif (!empty($inspection->employee_id)) {
                        $companyId = $employeeCompanies[$inspection->employee_id] ?? null;
                    }

                    DB::table('inspections')
                        ->where('id', $inspection->id)
                        ->update([
                            'inspection_type' => $inspectionType ?? 'Inspection',
                            'inspected_at' => $inspection->inspection_date ?? null,
                            'valid_until' => $inspection->expiry_date ?? null,
                            'company_id' => $companyId,
                        ]);
                });
        }

        if (Schema::hasColumn('inspections', 'inspection_type_id')) {
            Schema::table('inspections', function (Blueprint $table) {
                $table->dropForeign(['inspection_type_id']);
                $table->dropColumn('inspection_type_id');
            });
        }

        if (Schema::hasColumn('inspections', 'inspection_date') || Schema::hasColumn('inspections', 'expiry_date')) {
            Schema::table('inspections', function (Blueprint $table) {
                if (Schema::hasColumn('inspections', 'inspection_date')) {
                    $table->dropColumn('inspection_date');
                }
                if (Schema::hasColumn('inspections', 'expiry_date')) {
                    $table->dropColumn('expiry_date');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inspections', function (Blueprint $table) {
            if (Schema::hasColumn('inspections', 'inspection_date') === false) {
                $table->date('inspection_date')->nullable();
            }
            if (Schema::hasColumn('inspections', 'expiry_date') === false) {
                $table->date('expiry_date')->nullable();
            }
            if (Schema::hasColumn('inspections', 'inspection_type_id') === false) {
                $table->foreignUuid('inspection_type_id')->nullable()->constrained()->cascadeOnDelete();
            }
        });

        if (Schema::hasColumn('inspections', 'inspection_type')) {
            Schema::table('inspections', function (Blueprint $table) {
                $table->dropColumn(['inspection_type', 'inspected_at', 'valid_until']);
            });
        }

        if (Schema::hasColumn('inspections', 'company_id')) {
            Schema::table('inspections', function (Blueprint $table) {
                $table->dropForeign(['company_id']);
                $table->dropColumn('company_id');
            });
        }
    }
};
