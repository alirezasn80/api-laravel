<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ApiController extends Controller

{
    public function test()
    {
        return response()->json([
            'status' => 'success',
            'message' => 'This is the first test api :)'
        ], 200);
    }


    // ثبت‌نام
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'fail',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'token' => $token,
            'user' => $user
        ], 201);
    }

    // ورود
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'fail',
                'errors' => $validator->errors()
            ], 422);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Invalid credentials'
            ], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'token' => $token,
            'user' => $user
        ]);
    }

    //get All Users
    public function getAllUsers()
    {
        $users = User::get();

        if (!$users) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Data not found!'
            ], 400);
        }

        return response()->json([
            'status' => 'success',
            'count' => count($users),
            'data' => $users
        ], 200);
    }

    // Edit Users
    public function editUser($userId, Request $request)
    {
        $user = User::find($userId);

        if (!$user) {
            return response()->json([
                'status' => 'fail',
                'message' => 'User not found!'
            ], 400);

        }

        $validator = Validator::make($request->all(), rules: [
            'name' => 'required|min:4',
            'email' => 'required|email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'fail',
                'message' => $validator->errors()
            ], 400);
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully Updated',
            'data' => $user
        ]);
    }

    public function deleteUser($userId)
    {
        $user = User::find($userId);

        if (!$user) {
            return response()->json([
                'status' => 'fail',
                'messasge' => 'User not found'
            ], 400);
        }

        $user->delete();

        return response()->json([
            'status' => 'sauccess',
            'message' => 'User Successfully deleted',
        ]);
    }
}
