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
            // permissions phải là một mảng và có thể null
            'permissions' => 'nullable|array',
            // Mỗi item trong mảng permissions phải là một trong các giá trị hợp lệ
            'permissions.*' => [
                'string',
                Rule::in(['manage_news', 'manage_contacts']), // <-- DANH SÁCH QUYỀN HỢP LỆ
            ],
        ]);
        
        // Nếu vai trò là admin, permissions sẽ bị xóa để đảm bảo admin luôn có mọi quyền
        $permissionsToUpdate = $validated['permissions'] ?? [];
        if ($validated['role'] === 'admin') {
            $permissionsToUpdate = []; // Admin không cần lưu permissions cụ thể
        }

        $user->update([
            'role' => $validated['role'],
            'permissions' => $permissionsToUpdate,
        ]);

        return response()->json(['message' => 'Vai trò và quyền đã được cập nhật', 'data' => $user], 200);
    }
}