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
        // SỬA ĐỔI: Tải kèm cả role và permissions của role đó
        $query = User::with('role.permissions');

        $search = $request->input('q');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('role', function ($roleQuery) use ($search) {
                      $roleQuery->where('display_name', 'like', "%{$search}%");
                  });
            });
        }
        $users = $query->get();
        return response()->json(['data' => $users], 200);
    }

    public function show($id)
    {
        // SỬA ĐỔI: Tải kèm cả role và permissions của role đó
        $user = User::with('role.permissions')->findOrFail($id);
        return response()->json(['data' => $user], 200);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'role_id' => 'required|exists:roles,id',
        ]);
        
        $user->update($validated);

        // SỬA ĐỔI: Tải lại user với thông tin role và permissions mới nhất
        $user->load('role.permissions');

        return response()->json(['message' => 'Vai trò đã được cập nhật', 'data' => $user], 200);
    }   
}