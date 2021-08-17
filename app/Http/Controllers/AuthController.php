<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
class AuthController extends Controller
{
    /**
     * Logs the user in
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse Returns a token or 401
     */
    public function login(Request $request): \Illuminate\Http\JsonResponse {
        $data = [
            'email' => $request->email,
            'password' => $request->password
        ];

        if (auth()->attempt($data)) {
            $token = auth()->user()->createToken('todo-app')->accessToken;
            return response()->json(['token' => $token], 200);
        } else {
            return response()->json(['error' => 'Unauthorised'], 401);
        }
    }

}
