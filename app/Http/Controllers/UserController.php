<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        return response()->json($users);
    }

    public function find($id)
    {
        $user = User::find($id);
        return response()->json($user);
    }

    public function update(Request $request, $id)
    {
        try {
            $user = User::find($id);
            if ($user) {
                if ($request->has("name")) $user->name = $request->input("name");
                if ($request->has("email")) $user->email = $request->input("email");
                if ($request->has("username")) $user->username = $request->input("username");
                $user->save();

                return response()->json([
                    "isSuccess" => true,
                    "message" => "User Successfuly Updated",
                    "user" => $user
                ]);
            } else {
                return response()->json([
                    "isSuccess" => false,
                    "message" => "User not found"
                ], 401);
            }
        } catch (\Throwable $e) {
            return response()->json([
                "isSuccess" => false,
                "message" => $e->getMessage()
            ], 409);
        }
    }

    public function changePassword(Request $request, $id)
    {
        try {
            $this->validate($request, [
                'old_password' => 'required|string',
                'new_password' => 'required|string',
            ]);

            $user = User::find($id);
            if ($user) {

                $oldPassword = $request->input("old_password");
                $newPassword = $request->input("new_password");

                if (Hash::check($oldPassword, $user->password)) {
                    $user->password = Hash::make($newPassword);
                    $user->save();

                    return response()->json([
                        "isSuccess" => true,
                        "message" => "User Successfuly Updated",
                        "user" => $user
                    ]);
                } else {
                    return response()->json([
                        "isSuccess" => false,
                        "message" => "Password Not Match"
                    ], 401);
                }
            } else {
                return response()->json([
                    "isSuccess" => false,
                    "message" => "User not found"
                ], 401);
            }
        } catch (\Throwable $e) {
            return response()->json([
                "isSuccess" => false,
                "message" => $e->getMessage()
            ], 409);
        }
    }

    public function delete($id)
    {
        $user = User::find($id);
        $user->delete();

        return response()->json([
            "isSuccess" => true,
            "message" => "User Successfuly Deleted"
        ]);
    }
}
