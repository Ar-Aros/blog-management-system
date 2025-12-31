<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // Register User
    public function register(Request $request)
    {
        // 1. Validate
        $attrs = $request->validate([
            'name' => 'required|string',
            'username' => 'required|string|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|confirmed',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048' // Optional image
        ]);

        // 2. Handle Image Upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            // Save to storage/app/public/profiles
            $imagePath = $request->file('image')->store('profiles', 'public');
        }

        // 3. Create User
        $user = User::create([
            'name' => $attrs['name'],
            'username' => $attrs['username'],
            'email' => $attrs['email'],
            'password' => bcrypt($attrs['password']),
            'profile_picture' => $imagePath // Save the path (or null)
        ]);

        // 4. Create Token
        $token = $user->createToken('secret')->plainTextToken;

        return response()->json(['user' => $user, 'token' => $token], 200);
    }
    // Login User
    public function login(Request $request)
    {
        $fields = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string'
        ]);

        // Check email
        $user = User::where('email', $fields['email'])->first();

        // Check password
        if (!$user || !Hash::check($fields['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        $token = $user->createToken('myapptoken')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token
        ], 200);
    }

    // Logout
    public function logout(Request $request)
    {
        auth()->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out']);
    }
}