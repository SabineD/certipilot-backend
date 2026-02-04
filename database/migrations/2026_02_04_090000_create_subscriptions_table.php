<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('subscriptions');

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained()->cascadeOnDelete();
            $table->enum('plan', ['starter', 'professional', 'enterprise']);
            $table->enum('status', ['trial', 'active', 'past_due', 'cancelled']);
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
        });

        DB::statement(
            "CREATE UNIQUE INDEX subscriptions_active_company_unique
            ON subscriptions ((CASE WHEN status <> 'cancelled' THEN company_id ELSE NULL END))"
        );

        $now = Carbon::now();
        $trialEndsAt = $now->copy()->addDays(14);

        $companyIds = DB::table('companies')->pluck('id');
        foreach ($companyIds as $companyId) {
            DB::table('subscriptions')->insert([
                'id' => (string) str()->uuid(),
                'company_id' => $companyId,
                'plan' => 'professional',
                'status' => 'trial',
                'trial_ends_at' => $trialEndsAt,
                'ends_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
