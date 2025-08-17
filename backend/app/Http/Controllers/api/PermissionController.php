<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function index() { return Permission::all(); }
    public function store(Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|unique:permissions,name',
            'display_name' => 'required|string',
            'description' => 'nullable|string',
        ]);
        return Permission::create($validated);
    }
    public function update(Request $request, Permission $permission) {
        $validated = $request->validate([
            'name' => 'required|string|unique:permissions,name,'.$permission->id,
            'display_name' => 'required|string',
            'description' => 'nullable|string',
        ]);
        $permission->update($validated);
        return $permission;
    }
    public function destroy(Permission $permission) {
        $permission->delete();
        return response()->noContent();
    }
}
?>