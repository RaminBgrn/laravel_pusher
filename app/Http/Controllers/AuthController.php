<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $data = $request->validate();
        $data['password'] = Hash::make($data['password']);
        $data['username'] = strstr($data['email'], "@", true);
        $user = User::create($data);
        $token = $user->createToken(User::USER_TOKEN);

        return $this->success(
            [
                'user' => $user,
                'token' => $token->plainTextToken
            ],
            "User has been register successfully"
        );
    }

    public function login() {}

    public function loginWithToken() {}

    public function logout() {}
}
