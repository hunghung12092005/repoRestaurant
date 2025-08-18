<?php

namespace App\Http\Controllers\api;

use App\Models\User;
use App\Models\StaffProfile;
use App\Models\StaffSchedule;
use App\Models\Role; // <-- THÊM DÒNG NÀY
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
          $staffRoleIds = Role::whereNotIn('name', ['admin', 'client'])->pluck('id');

        // Luôn tải kèm các quan hệ cần thiết
        $staff = User::with(['staffProfile', 'staffSchedules', 'role.permissions'])
            ->whereIn('role_id', $staffRoleIds) // Lọc theo danh sách ID nhân viên
            ->get();

        return response()->json(['data' => $staff], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role_id' => 'required|exists:roles,id', // <-- SỬA ĐỔI: Dùng role_id
            'status' => 'required|in:active,inactive,suspended',
            // Profile validation
            'employee_code' => 'required|string|unique:staff_profiles,employee_code|max:50',
            // ... (các validation khác của profile và schedule giữ nguyên)
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'hire_date' => 'nullable|date',
            'position' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'salary' => 'nullable|numeric|min:0',
            'level' => 'nullable|in:junior,senior,manager',
            'schedules' => 'array',
            'schedules.*.shift_date' => 'required|date',
            'schedules.*.start_time' => 'required|date_format:H:i:s',
            'schedules.*.end_time' => 'required|date_format:H:i:s|after:schedules.*.start_time',
            'schedules.*.task' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Dữ liệu không hợp lệ', 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make('password123'), // Mật khẩu mặc định
                'role_id' => $request->role_id, // <-- SỬA ĐỔI
                'status' => $request->status,
            ]);

            StaffProfile::create([
                'user_id' => $user->id,
                'employee_code' => $request->employee_code,
                // ... (các trường profile khác)
                'phone' => $request->phone,
                'address' => $request->address,
                'hire_date' => $request->hire_date,
                'position' => $request->position,
                'department' => $request->department,
                'salary' => $request->salary,
                'level' => $request->level,
            ]);

            if ($request->schedules) {
                foreach ($request->schedules as $schedule) {
                    $user->staffSchedules()->create($schedule);
                }
            }

            DB::commit();
            return response()->json([
                'message' => 'Thêm nhân viên thành công',
                'data' => $user->load(['staffProfile', 'staffSchedules', 'role'])
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
            'role_id' => 'required|exists:roles,id', // <-- SỬA ĐỔI
            'status' => 'required|in:active,inactive,suspended',
            'employee_code' => 'required|string|unique:staff_profiles,employee_code,' . ($user->staffProfile->id ?? 0) . ',id|max:50',
            // ... (các validation khác giữ nguyên)
            'schedules' => 'array',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Dữ liệu không hợp lệ', 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'role_id' => $request->role_id, // <-- SỬA ĐỔI
                'status' => $request->status,
            ]);

            $user->staffProfile()->updateOrCreate(['user_id' => $user->id], $request->only([
                'employee_code', 'phone', 'address', 'hire_date', 'position', 'department', 'salary', 'level'
            ]));

            // Logic cập nhật schedule có thể giữ nguyên
            $newScheduleIds = array_filter(array_column($request->schedules ?? [], 'id'));
            StaffSchedule::where('user_id', $user->id)->whereNotIn('id', $newScheduleIds)->delete();
            if ($request->schedules) {
                foreach ($request->schedules as $schedule) {
                    StaffSchedule::updateOrCreate(
                        ['id' => $schedule['id'] ?? null, 'user_id' => $user->id],
                        $schedule
                    );
                }
            }

            DB::commit();
            return response()->json([
                'message' => 'Cập nhật nhân viên thành công',
                'data' => $user->load(['staffProfile', 'staffSchedules', 'role'])
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
            $user->staffSchedules()->delete();
            $user->delete();
            DB::commit();
            return response()->json(['message' => 'Xóa nhân viên thành công'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Xóa nhân viên thất bại', 'error' => $e->getMessage()], 500);
        }
    }
}