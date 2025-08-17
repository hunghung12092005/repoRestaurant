<?php
// File: app/Http/Controllers/api/RoleController.php
namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;


class RoleController extends Controller
{
    public function index() { return Role::with('permissions')->get(); }
    public function store(Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|unique:roles,name',
            'display_name' => 'required|string',
            'permission_ids' => 'nullable|array',
            'permission_ids.*' => 'exists:permissions,id',
        ]);
        $role = Role::create($validated);
        if (!empty($validated['permission_ids'])) {
            $role->permissions()->sync($validated['permission_ids']);
        }
        return $role->load('permissions');
    }
    public function update(Request $request, Role $role) {
        $validated = $request->validate([
            'name' => 'required|string|unique:roles,name,'.$role->id,
            'display_name' => 'required|string',
            'permission_ids' => 'nullable|array',
            'permission_ids.*' => 'exists:permissions,id',
        ]);

        if (in_array($role->name, ['admin', 'client'])) {
            return response()->json(['message' => 'Không thể chỉnh sửa vai trò hệ thống.'], 403);
        }

        $role->update($validated);
        $role->permissions()->sync($validated['permission_ids'] ?? []);
        return $role->load('permissions');
    }
    public function destroy(Role $role) {
        if (in_array($role->name, ['admin', 'client'])) {
            return response()->json(['message' => 'Không thể xóa vai trò hệ thống.'], 403);
        }
        $role->permissions()->detach();
        $role->delete();
        return response()->noContent();
    }
}