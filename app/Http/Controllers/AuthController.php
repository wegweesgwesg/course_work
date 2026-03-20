<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email|max:254',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            if ($request->expectsJson()) {
                $user = Auth::user();
                return response()->json([
                    'success' => true,
                    'user' => [
                        'id' => $user->id,
                        'email' => $user->email,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'role' => $user->role,
                    ],
                ]);
            }

            return redirect()->intended('/');
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => 'Неверный email или пароль.'], 401);
        }

        return back()->withErrors(['email' => 'Неверный email или пароль.'])->onlyInput('email');
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:64',
            'last_name' => 'required|string|max:64',
            'email' => 'required|email|max:254|unique:users,email',
            'password' => ['required', 'string', 'min:8', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers()],
        ]);

        $validated = $validator->validateWithBag('register');

        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => 'user',
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'role' => $user->role,
                ],
            ], 201);
        }

        return redirect('/');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect('/login');
    }

    public function user(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(null);
        }
        return response()->json([
            'id' => $user->id,
            'email' => $user->email,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'role' => $user->role,
        ]);
    }
}
