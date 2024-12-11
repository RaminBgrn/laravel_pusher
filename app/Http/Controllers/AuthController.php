<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use GuzzleHttp\Psr7\Response;
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

    private function isValidCredential(LoginRequest $request)
    {
        $data = $request->validate();
        $user = User::where('email', $data['email'])->first();
        if ($user === null) {
            return [
                'success' => false,
                'message' => 'Invalid Credential'
            ];
        }

        if (!Hash::check($data['password'], $user->password)) {
            return [
                'success' => false,
                'message' => 'Password is not matched'
            ];
        }
        return [
            'success' => true,
            'user' => $user
        ];
    }

    public function login(LoginRequest $request)
    {
        $isValid = $this->isValidCredential($request);
        if (!$isValid['success']) {
            return $this->error($isValid['message'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $user = $isValid['user'];
        $token = $user->createToken(User::USER_TOKEN);
        return $this->success(
            [
                'user' => $user,
                'token' => $token->plainTextToken,
            ],
            "Login successfully"
        );
    }

    public function loginWithToken()
    {
        return $this->success(auth()->user(), 'Login successfully');
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return $this->success(null, 'Logout successfully');
    }
}
