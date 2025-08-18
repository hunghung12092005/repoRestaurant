<?php

namespace App\Http\Controllers\api;

use App\Models\User;
use App\Models\StaffProfile;
use App\Models\Role; 
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $staffRoleIds = Role::whereIn('name', ['manager', 'receptionist'])->pluck('id');
        $staff = User::with(['staffProfile', 'role'])
            ->whereIn('role_id', $staffRoleIds)
            ->get();

        return response()->json(['data' => $staff], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role_id' => 'required|exists:roles,id',
            'status' => 'required|in:active,inactive,suspended',
            'employee_code' => 'required|string|unique:staff_profiles,employee_code|max:50',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'hire_date' => 'nullable|date',
            'position' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'salary' => 'nullable|numeric|min:0',
            'level' => 'nullable|in:junior,senior,manager',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Dữ liệu không hợp lệ', 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make('hxl_password'), // Mật khẩu mặc định
                'role_id' => $request->role_id,
                'status' => $request->status,
            ]);

            StaffProfile::create([
                'user_id' => $user->id,
                'employee_code' => $request->employee_code,
                'phone' => $request->phone,
                'address' => $request->address,
                'hire_date' => $request->hire_date,
                'position' => $request->position,
                'department' => $request->department,
                'salary' => $request->salary,
                'level' => $request->level,
            ]);

            DB::commit();
            return response()->json([
                'message' => 'Thêm nhân viên thành công',
                'data' => $user->load(['staffProfile', 'role'])
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Thêm nhân viên thất bại', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'role_id' => 'required|exists:roles,id', 
            'status' => 'required|in:active,inactive,suspended',
            'employee_code' => 'required|string|unique:staff_profiles,employee_code,' . ($user->staffProfile->id ?? 0) . ',id|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Dữ liệu không hợp lệ', 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'role_id' => $request->role_id,
                'status' => $request->status,
            ]);

            $user->staffProfile()->updateOrCreate(['user_id' => $user->id], $request->only([
                'employee_code', 'phone', 'address', 'hire_date', 'position', 'department', 'salary', 'level'
            ]));
            DB::commit();
            return response()->json([
                'message' => 'Cập nhật nhân viên thành công',
                'data' => $user->load(['staffProfile', 'role'])
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Cập nhật nhân viên thất bại', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $user = User::findOrFail($id);
            $user->staffProfile()->delete();
            $user->delete();
            DB::commit();
            return response()->json(['message' => 'Xóa nhân viên thành công'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Xóa nhân viên thất bại', 'error' => $e->getMessage()], 500);
        }
    }
}