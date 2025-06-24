<?php

namespace App\Http\Controllers\api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class UsersController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        // Lấy tham số tìm kiếm từ query string
        $search = $request->input('q');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('role', 'like', "%{$search}%");
            });
        }

        // Lấy danh sách người dùng
        $users = $query->get();

        return response()->json(['data' => $users], 200);
    }

    public function show($id)
    {
        $user = User::findOrFail($id);
        return response()->json(['data' => $user], 200);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'role' => 'required|in:admin,staff,client', // Chỉ cho phép chỉnh sửa vai trò
        ]);

        $user->update([
            'role' => $validated['role'],
        ]);

        return response()->json(['message' => 'Vai trò đã được cập nhật', 'data' => $user], 200);
    }
}