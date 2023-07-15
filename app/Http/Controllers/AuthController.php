<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
   public function login(Request $request)
   {
      try {
         $this->validate($request, [
            'username' => 'required|string',
            'password' => 'required|string',
         ]);
         $credentials = $request->only(['username', 'password']);
         if (!$token = Auth::attempt($credentials)) {
            return response()->json([
               "isSuccess" => false,
               "message" => "Unauthorized"
            ], 401);
         }

         $user = User::where('username', $request->input('username'))->first();

         return response()->json([
            "isSuccess" => true,
            'token' => $token,
            'data' => $user
         ]);
      } catch (\Throwable $e) {
         return response()->json([
            "isSuccess" => false,
            "message" => $e->getMessage()
         ], 409);
      }
   }

   public function register(Request $request)
   {

      try {

         $this->validate($request, [
            'name' => 'required|string',
            'username' => 'required|string|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required',
         ]);

         $user = new User;
         $user->name = $request->input('name');
         $user->username = $request->input('username');
         $user->email = $request->input('email');
         $user->password = Hash::make($request->input('password'));
         $user->image = "";
         $user->is_active = "1";
         $user->save();

         return response()->json([
            "isSuccess" => true,
            "message" => "User Registration Success"
         ], 201);
      } catch (\Exception $e) {

         return response()->json([
            "isSuccess" => false,
            "message" => "User Registration Failed!",
            "error" => $e->getMessage()
         ], 409);
      }
   }

   public function logout()
   {
      return response()->json(["isSuccess" => true]);
   }
}
