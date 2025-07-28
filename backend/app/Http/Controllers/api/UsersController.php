<?php
// File: app/Http/Controllers/api/UsersController.php

namespace App\Http\Controllers\api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UsersController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        $search = $request->input('q');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('role', 'like', "%{$search}%");
            });
        }
        $users = $query->get();
        return response()->json(['data' => $users], 200);
    }

    public function show($id)
    {
        $user = User::findOrFail($id);
        return response()->json(['data' => $user], 200);
    }

    // --- MODIFIED ---
     public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'role' => 'required|in:admin,staff,client',
            'permissions' => 'nullable|array',
            'permissions.*' => [
                'string',
                Rule::in([
                    'manage_news', 
                    'manage_contacts',
                    'manage_users',
                    'manage_ai_training',
                    'manage_admin_chat'
                ]),
            ],
        ]);
        
        $permissionsToUpdate = $validated['permissions'] ?? [];

        $user->update([
            'role' => $validated['role'],
            'permissions' => $permissionsToUpdate,
        ]);

        return response()->json(['message' => 'Vai trò và quyền đã được cập nhật', 'data' => $user], 200);
    }
}