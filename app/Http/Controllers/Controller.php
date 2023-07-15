<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class Controller extends BaseController
{
    protected function respondWithToken($token)
    {
        return response()->json([
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60
        ], 200);
    }

    protected function getPayloadJWT($payload)
    {
        $token = JWTAuth::getToken();
        return JWTAuth::getPayload($token)->get($payload);
    }
}
