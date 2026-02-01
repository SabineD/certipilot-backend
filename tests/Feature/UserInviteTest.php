<?php

use App\Jobs\SendUserInvitationMail;
use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\Bus;
use Laravel\Sanctum\Sanctum;

test('admin creates user and dispatches invitation job', function () {
    Bus::fake();

    $company = Company::create([
        'name' => 'CertiPilot Demo',
        'vat_number' => 'BE0123456789',
    ]);

    $admin = User::factory()->create([
        'company_id' => $company->id,
        'role' => User::ROLE_ADMIN,
    ]);

    Sanctum::actingAs($admin);

    $response = $this->postJson('/api/users', [
        'name' => 'Jan Peeters',
        'email' => 'jan.peeters@example.com',
        'role' => User::ROLE_WERFLEIDER,
    ]);

    $response->assertStatus(201)
        ->assertJsonFragment([
            'name' => 'Jan Peeters',
            'email' => 'jan.peeters@example.com',
            'role' => User::ROLE_WERFLEIDER,
        ]);

    Bus::assertDispatched(SendUserInvitationMail::class);
});
