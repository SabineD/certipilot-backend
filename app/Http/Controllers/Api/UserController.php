<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Jobs\SendUserInvitationMail;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Lijst van gebruikers (admin)
     */
    public function index(Request $request)
    {
        $company = $request->user()->company;

        $users = User::where('company_id', $company->id)
            ->orderBy('created_at', 'desc')
            ->get(['id', 'name', 'email', 'role', 'created_at', 'is_active']);

        return response()->json($users);
    }

    /**
     * Gebruiker aanmaken (admin)
     */
    public function store(StoreUserRequest $request)
    {
        $company = $request->user()->company;
        $data = $request->validated();

        $user = new User();
        $user->company_id = $company->id;
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->role = $data['role'];
        $user->password = null;
        $user->is_active = true;
        $user->save();

        SendUserInvitationMail::dispatch($user->id);

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'is_active' => (bool) $user->is_active,
        ], 201);
    }

    /**
     * Gebruiker bijwerken (admin)
     */
    public function update(UpdateUserRequest $request, string $id)
    {
        $company = $request->user()->company;

        $user = User::where('company_id', $company->id)
            ->where('id', $id)
            ->first();

        if (! $user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $data = $request->validated();

        if (
            $user->id === $request->user()->id &&
            ! in_array($data['role'], [User::ROLE_ADMIN, User::ROLE_ZAAKVOERDER], true)
        ) {
            return response()->json([
                'message' => 'Je kan je eigen admin-rol niet verwijderen.',
                'errors' => [
                    'role' => ['Je kan je eigen admin-rol niet verwijderen.'],
                ],
            ], 422);
        }

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->role = $data['role'];
        if (array_key_exists('is_active', $data)) {
            $user->is_active = (bool) $data['is_active'];
        }
        $user->save();

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'is_active' => (bool) $user->is_active,
        ]);
    }
}
