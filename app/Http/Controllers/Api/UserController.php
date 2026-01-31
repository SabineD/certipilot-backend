<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use App\Mail\UserInviteMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

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
            ->get(['id', 'name', 'email', 'role', 'created_at']);

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
        $user->save();

        $token = Password::broker()->createToken($user);
        Mail::to($user->email)->send(new UserInviteMail($user, $token));

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
        ], 201);
    }
}
