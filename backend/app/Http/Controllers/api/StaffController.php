<?php

namespace App\Http\Controllers\api;

use App\Models\User;
use App\Models\StaffProfile;
use App\Models\StaffSchedule;
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
        $perPage = $request->query('per_page', 10);
        // SỬA ĐỔI: Chỉ lấy các vai trò nhân viên còn lại
        $staff = User::with(['staffProfile', 'staffSchedules'])
            ->whereIn('role', ['manager', 'receptionist'])
            ->paginate($perPage);
        return response()->json([
            'data' => $staff->items(),
            'total' => $staff->total(),
            'current_page' => $staff->currentPage(),
            'per_page' => $staff->perPage()
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            // SỬA ĐỔI: Cập nhật danh sách vai trò hợp lệ
            'role' => 'required|in:manager,receptionist',
            'status' => 'required|in:active,inactive,suspended',
            'permissions' => 'array',
            // SỬA ĐỔI: Cập nhật danh sách quyền hạn hợp lệ
            'permissions.*' => Rule::in([
                'manage_bookings', 'manage_reports', 'manage_rooms', 'manage_prices', 
                'manage_services', 'manage_amenities', 'manage_coupons', 'manage_news', 
                'manage_contacts', 'manage_users', 'manage_staff', 'manage_ai_training', 'manage_admin_chat'
            ]),
            'employee_code' => 'nullable|string|unique:staff_profiles,employee_code|max:50',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'hire_date' => 'nullable|date',
            'position' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'salary' => 'nullable|numeric|min:0',
            'level' => 'nullable|in:junior,senior,manager',
            'schedules' => 'array',
            'schedules.*.id' => 'nullable|exists:staff_schedules,id',
            'schedules.*.shift_date' => 'required|date',
            'schedules.*.start_time' => 'required|date_format:H:i',
            'schedules.*.end_time' => 'required|date_format:H:i|after:schedules.*.start_time',
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
                'password' => Hash::make('password123'),
                'role' => $request->role,
                'status' => $request->status,
                'permissions' => $request->permissions ?? $this->getPermissions($request->role),
            ]);

            StaffProfile::create([
                'user_id' => $user->id,
                'employee_code' => $request->employee_code ?? 'NV' . str_pad($user->id, 3, '0', STR_PAD_LEFT),
                'phone' => $request->phone,
                'address' => $request->address,
                'hire_date' => $request->hire_date,
                'position' => $request->position,
                'department' => $request->department,
                'salary' => $request->salary,
                'level' => $request->level,
            ]);

            if ($request->schedules && $request->status === 'active') {
                foreach ($request->schedules as $schedule) {
                    StaffSchedule::create([
                        'user_id' => $user->id,
                        'shift_date' => $schedule['shift_date'],
                        'start_time' => $schedule['start_time'],
                        'end_time' => $schedule['end_time'],
                        'task' => $schedule['task'],
                    ]);
                }
            }

            DB::commit();
            return response()->json([
                'message' => 'Thêm nhân viên thành công',
                'data' => $user->load(['staffProfile', 'staffSchedules'])
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
            'role' => 'required|in:manager,receptionist',
            'status' => 'required|in:active,inactive,suspended',
            'permissions' => 'array',
            'permissions.*' => Rule::in([
                'manage_bookings', 'manage_reports', 'manage_rooms', 'manage_prices', 
                'manage_services', 'manage_amenities', 'manage_coupons', 'manage_news', 
                'manage_contacts', 'manage_users', 'manage_staff', 'manage_ai_training', 'manage_admin_chat'
            ]),
            'employee_code' => 'nullable|string|unique:staff_profiles,employee_code,' . ($user->staffProfile->id ?? 0) . '|max:50',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'hire_date' => 'nullable|date',
            'position' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'salary' => 'nullable|numeric|min:0',
            'level' => 'nullable|in:junior,senior,manager',
            'schedules' => 'array',
            'schedules.*.id' => 'nullable|exists:staff_schedules,id',
            'schedules.*.shift_date' => 'required|date',
            'schedules.*.start_time' => 'required|date_format:H:i',
            'schedules.*.end_time' => 'required|date_format:H:i|after:schedules.*.start_time',
            'schedules.*.task' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Dữ liệu không hợp lệ', 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'role' => $request->role,
                'status' => $request->status,
                'permissions' => $request->permissions ?? $this->getPermissions($request->role),
            ]);

            $user->staffProfile()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'employee_code' => $request->employee_code,
                    'phone' => $request->phone,
                    'address' => $request->address,
                    'hire_date' => $request->hire_date,
                    'position' => $request->position,
                    'department' => $request->department,
                    'salary' => $request->salary,
                    'level' => $request->level,
                ]
            );

            if ($request->status !== 'active') {
                StaffSchedule::where('user_id', $user->id)
                    ->where('shift_date', '>=', now()->toDateString())
                    ->delete();
            } else {
                $newScheduleIds = array_filter(array_column($request->schedules ?? [], 'id'), fn($id) => !is_null($id));
                StaffSchedule::where('user_id', $user->id)->whereNotIn('id', $newScheduleIds)->delete();
                if ($request->schedules) {
                    foreach ($request->schedules as $schedule) {
                        StaffSchedule::updateOrCreate(
                            ['id' => $schedule['id'] ?? null, 'user_id' => $user->id],
                            [
                                'shift_date' => $schedule['shift_date'],
                                'start_time' => $schedule['start_time'],
                                'end_time' => $schedule['end_time'],
                                'task' => $schedule['task'],
                            ]
                        );
                    }
                }
            }

            DB::commit();
            return response()->json([
                'message' => 'Cập nhật nhân viên thành công',
                'data' => $user->load(['staffProfile', 'staffSchedules'])
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

    // SỬA ĐỔI: Cập nhật quyền mặc định cho các vai trò
    private function getPermissions($role)
    {
        $permissions = [
            'manager' => [
                'manage_bookings', 
                'manage_reports', 
                'manage_staff',
                'manage_contacts',
            ],
            'receptionist' => ['manage_bookings', 'manage_contacts'],
        ];
        return $permissions[$role] ?? [];
    }
}