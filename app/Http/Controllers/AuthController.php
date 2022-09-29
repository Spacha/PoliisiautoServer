<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Try logging in. If successful, create and return an API token.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request) {
        $request->validate([
            'name'          => 'required|string|min:1|max:127',
            'email'         => 'required|string|unique:users,email|max:127',
            'password'      => 'required|string|confirmed|min:8|max:127',
            'device_name'   => 'required|string'
        ]);

        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password)
        ]);

        return $user->createToken($request->device_name)->plainTextToken;
    }

    /**
     * Try logging in. If successful, create and return an API token.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request) {
        $request->validate([
            'email'         => 'required|email',
            'password'      => 'required|string',
            'device_name'   => 'required|string'
        ]);
     
        $user = User::where('email', $request->email)->first();
     
        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }
     
        return $user->createToken($request->device_name)->plainTextToken;
    }

    /**
     * Log out and destroy the token.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request) {
        // ...
    }
}
