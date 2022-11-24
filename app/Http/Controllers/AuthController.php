<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Role;
use Auth;
use Log;
use DB;

class AuthController extends Controller
{
    /**
     * Register a new user (student). If successful, create and return an API token.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request) {
        $request->validate([
            'first_name'    => 'required|string|min:1|max:127',
            'last_name'     => 'required|string|min:1|max:127',
            'email'         => 'required|string|unique:users,email|max:127',
            'password'      => 'required|string|confirmed|min:8|max:127',
            'phone'         => 'string|min:1|max:127',
            'device_name'   => 'required|string'
        ]);

        $user = Student::create([
            'first_name'    => $request->first_name,
            'last_name'     => $request->last_name,
            'email'         => $request->email,
            'password'      => Hash::make($request->password)
        ]);

        Log::debug("User $user->email registered.");

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

        // get the user with the right class type ('role model')
        $role = DB::table('users')->where('email', $request->email)->first('role');
        $user = $role
            ? Role::getRoleModel($role->role)->where('email', $request->email)->first()
            : null;
     
        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        Log::debug("User $user->email logged in.");
     
        return $user->createToken($request->device_name)->plainTextToken;
    }

    /**
     * Log out and destroy the token.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request) {
        Log::debug("User " . Auth::user()->email . " logged out.");
        Auth::user()->currentAccessToken()->delete();
    }

    /**
     * Get the currently authenticated user.
     *
     * @return \Illuminate\Http\Response
     */
    public function profile() {
        return Auth::user();
    }

    /**
     * Get the currently authenticated user's organization.
     *
     * @return \Illuminate\Http\Response
     */
    public function organization() {
        return Auth::user()->organization;
    }
}
