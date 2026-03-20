<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function index()
    {
        return view('admin.users');
    }

    public function users(Request $request)
    {
        $query = User::query();

        if ($search = $request->input('search')) {
            $query->where('email', 'like', '%' . $search . '%');
        }

        $users = $query->orderBy('email')->get()->map(function ($user) {
            return [
                'id' => $user->id,
                'email' => $user->email,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'role' => $user->role,
            ];
        });

        return response()->json($users);
    }

    public function updateRole(Request $request, int $id)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['error' => 'Только администратор может менять роли.'], 403);
        }

        $validated = $request->validate([
            'role' => 'required|string|in:user,content_manager,warehouse_manager,admin',
        ]);

        $user = User::findOrFail($id);
        $user->role = $validated['role'];
        $user->save();

        return response()->json(['success' => true, 'user' => [
            'id' => $user->id,
            'email' => $user->email,
            'role' => $user->role,
        ]]);
    }

    public function destroyUser(int $id)
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json(['error' => 'Только администратор может удалять пользователей.'], 403);
        }

        $currentUser = Auth::user();
        $user = User::findOrFail($id);

        if ($currentUser->id === $user->id) {
            Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
        }

        $user->delete();

        return response()->json(['success' => true, 'self_deleted' => $currentUser->id === (int) $id]);
    }
}
