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
        Schema::table('certificates', function (Blueprint $table) {
            if (!Schema::hasColumn('certificates', 'company_id')) {
                $table->foreignUuid('company_id')->nullable()->after('employee_id');
            }
            if (!Schema::hasColumn('certificates', 'certificate_type')) {
                $table->string('certificate_type')->nullable()->after('company_id');
            }
            if (!Schema::hasColumn('certificates', 'issued_at')) {
                $table->date('issued_at')->nullable()->after('certificate_type');
            }
            if (!Schema::hasColumn('certificates', 'valid_until')) {
                $table->date('valid_until')->nullable()->after('issued_at');
            }
        });

        $hasOldColumns = Schema::hasColumn('certificates', 'certificate_type_id')
            || Schema::hasColumn('certificates', 'issued_date')
            || Schema::hasColumn('certificates', 'expiry_date');

        if ($hasOldColumns) {
            $certificateTypes = DB::table('certificate_types')->pluck('name', 'id');
            $machineCompanies = DB::table('machines')->pluck('company_id', 'id');
            $employeeCompanies = DB::table('employees')->pluck('company_id', 'id');

            DB::table('certificates')
                ->select('id', 'certificate_type_id', 'issued_date', 'expiry_date', 'machine_id', 'employee_id')
                ->get()
                ->each(function ($certificate) use ($certificateTypes, $machineCompanies, $employeeCompanies) {
                    $certificateType = null;
                    if (!empty($certificate->certificate_type_id)) {
                        $certificateType = $certificateTypes[$certificate->certificate_type_id] ?? null;
                    }

                    $companyId = null;
                    if (!empty($certificate->employee_id)) {
                        $companyId = $employeeCompanies[$certificate->employee_id] ?? null;
                    } elseif (!empty($certificate->machine_id)) {
                        $companyId = $machineCompanies[$certificate->machine_id] ?? null;
                    }

                    DB::table('certificates')
                        ->where('id', $certificate->id)
                        ->update([
                            'certificate_type' => $certificateType ?? 'Certificate',
                            'issued_at' => $certificate->issued_date ?? null,
                            'valid_until' => $certificate->expiry_date ?? null,
                            'company_id' => $companyId,
                        ]);
                });
        }

        if (Schema::hasColumn('certificates', 'certificate_type_id')) {
            Schema::table('certificates', function (Blueprint $table) {
                $table->dropForeign(['certificate_type_id']);
                $table->dropColumn('certificate_type_id');
            });
        }

        if (Schema::hasColumn('certificates', 'issued_date') || Schema::hasColumn('certificates', 'expiry_date')) {
            Schema::table('certificates', function (Blueprint $table) {
                if (Schema::hasColumn('certificates', 'issued_date')) {
                    $table->dropColumn('issued_date');
                }
                if (Schema::hasColumn('certificates', 'expiry_date')) {
                    $table->dropColumn('expiry_date');
                }
            });
        }

        if (Schema::hasColumn('certificates', 'file_url')) {
            Schema::table('certificates', function (Blueprint $table) {
                $table->dropColumn('file_url');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            if (Schema::hasColumn('certificates', 'issued_date') === false) {
                $table->date('issued_date')->nullable();
            }
            if (Schema::hasColumn('certificates', 'expiry_date') === false) {
                $table->date('expiry_date')->nullable();
            }
            if (Schema::hasColumn('certificates', 'certificate_type_id') === false) {
                $table->foreignUuid('certificate_type_id')->nullable()->constrained()->cascadeOnDelete();
            }
            if (Schema::hasColumn('certificates', 'file_url') === false) {
                $table->string('file_url')->nullable();
            }
        });

        if (Schema::hasColumn('certificates', 'certificate_type')) {
            Schema::table('certificates', function (Blueprint $table) {
                $table->dropColumn(['certificate_type', 'issued_at', 'valid_until']);
            });
        }

        if (Schema::hasColumn('certificates', 'company_id')) {
            Schema::table('certificates', function (Blueprint $table) {
                $table->dropForeign(['company_id']);
                $table->dropColumn('company_id');
            });
        }
    }
};
