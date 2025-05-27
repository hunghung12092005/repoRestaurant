<?php
namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Department::with('manager');

        if ($request->has('q') && $request->q) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%')
                  ->orWhereHas('manager', function ($q) use ($search) {
                      $q->where('name', 'like', '%' . $search . '%');
                  });
            });
        }

        $departments = $query->get();
        return response()->json($departments);
    }

    public function show($id)
    {
        $department = Department::with('manager')->findOrFail($id);
        return response()->json($department);
    }

    public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:100',
        'description' => 'nullable|string',
        'manager_id' => 'nullable|exists:employees,employee_id',
    ]);

    // Nếu không có manager_id, tìm nhân viên có chức danh chứa "trưởng" trong phòng ban
    if (empty($validated['manager_id']) && !empty($request->department_id)) {
        $manager = Employee::where('department_id', $request->department_id)
            ->where('position', 'like', '%trưởng%')
            ->first();
        if ($manager) {
            $validated['manager_id'] = $manager->employee_id;
        }
    }

    $department = Department::create($validated);
    return response()->json($department->load('manager'), 201);
}

public function update(Request $request, $id)
{
    $department = Department::findOrFail($id);
    $validated = $request->validate([
        'name' => 'required|string|max:100',
        'description' => 'nullable|string',
        'manager_id' => 'nullable|exists:employees,employee_id',
    ]);

    // Tương tự, tự động gán manager_id nếu không có
    if (empty($validated['manager_id']) && !empty($department->department_id)) {
        $manager = Employee::where('department_id', $department->department_id)
            ->where('position', 'like', '%trưởng%')
            ->first();
        if ($manager) {
            $validated['manager_id'] = $manager->employee_id;
        }
    }

    $department->update($validated);
    return response()->json($department->load('manager'));
}

    public function destroy($id)
    {
        $department = Department::findOrFail($id);
        $department->delete();
        return response()->json(null, 204);
    }
}