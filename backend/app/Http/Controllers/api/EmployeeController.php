<?php
namespace App\Http\Controllers\api;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::with('department');
        if ($request->has('q') && $request->q) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhere('position', 'like', '%' . $search . '%');
            });
        }
        $employees = $query->get();
        return response()->json($employees);
    }

    public function store(Request $request)
    {
        try {
            // Log dữ liệu đầu vào
            Log::info('Store employee request', ['data' => $request->all()]);

            $validated = $request->validate([
                'name' => 'required|string|max:100',
                'gender' => 'nullable|in:Male,Female,Other',
                'birth_date' => 'nullable|date',
                'email' => 'required|email|unique:employees,email',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:255',
                'position' => 'nullable|string|max:100',
                'salary' => 'nullable|numeric',
                'department_id' => 'nullable|exists:departments,department_id',
                'hire_date' => 'nullable|date',
                'status' => 'nullable|in:Active,Inactive,On Leave',
            ]);

            Log::info('Validated employee data', $validated);

            // Tạo nhân viên
            $employee = Employee::create($validated);

            // Gán manager_id nếu chức danh chứa "trưởng"
            if (isset($validated['position']) && 
                stripos(mb_strtolower($validated['position'], 'UTF-8'), 'trưởng') !== false && 
                $employee->department_id) {
                $department = Department::find($employee->department_id);
                if ($department) {
                    $department->update(['manager_id' => $employee->employee_id]);
                    Log::info('Assigned manager for department', [
                        'department_id' => $department->department_id,
                        'manager_id' => $employee->employee_id
                    ]);
                } else {
                    Log::warning('Department not found', ['department_id' => $employee->department_id]);
                }
            }

            Log::info('Employee created successfully', ['employee_id' => $employee->employee_id]);

            return response()->json($employee->load('department'), 201);
        } catch (ValidationException $e) {
            Log::error('Validation error in store employee', [
                'errors' => $e->errors(),
                'request' => $request->all()
            ]);
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (QueryException $e) {
            Log::error('Database error in store employee', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);
            return response()->json(['message' => 'Database error occurred'], 500);
        } catch (\Exception $e) {
            Log::error('Unexpected error in store employee', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);
            return response()->json(['message' => 'An unexpected error occurred'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $employee = Employee::findOrFail($id);
            Log::info('Update employee request', ['employee_id' => $id, 'data' => $request->all()]);

            $validated = $request->validate([
                'name' => 'required|string|max:100',
                'gender' => 'nullable|in:Male,Female,Other',
                'birth_date' => 'nullable|date',
                'email' => 'required|email|unique:employees,email,' . $id . ',employee_id',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:255',
                'position' => 'nullable|string|max:100',
                'salary' => 'nullable|numeric',
                'department_id' => 'nullable|exists:departments,department_id',
                'hire_date' => 'nullable|date',
                'status' => 'nullable|in:Active,Inactive,On Leave',
            ]);

            $employee->update($validated);

            // Gán hoặc xóa manager_id
            if (isset($validated['position']) && 
                stripos(mb_strtolower($validated['position'], 'UTF-8'), 'trưởng') !== false && 
                $employee->department_id) {
                $department = Department::find($employee->department_id);
                if ($department) {
                    $department->update(['manager_id' => $employee->employee_id]);
                    Log::info('Updated manager_id', [
                        'department_id' => $department->department_id,
                        'manager_id' => $employee->employee_id
                    ]);
                }
            } elseif ($employee->department_id) {
                $department = Department::where('manager_id', $employee->employee_id)->first();
                if ($department) {
                    $department->update(['manager_id' => null]);
                    Log::info('Removed manager_id', ['department_id' => $department->department_id]);
                }
            }

            return response()->json($employee->load('department'));
        } catch (ValidationException $e) {
            Log::error('Validation error in update employee', ['errors' => $e->errors()]);
            return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error in update employee', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to update employee'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $employee = Employee::findOrFail($id);
            $department = Department::where('manager_id', $id)->first();
            if ($department) {
                $department->update(['manager_id' => null]);
            }
            $employee->delete();
            return response()->json(['message' => 'Employee deleted successfully']);
        } catch (\Exception $e) {
            Log::error('Error in delete employee', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to delete employee'], 500);
        }
    }
}