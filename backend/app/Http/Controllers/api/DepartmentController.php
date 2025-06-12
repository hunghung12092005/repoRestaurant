<?php
namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Department::withCount('employees');
            if ($request->has('q') && $request->q) {
                $search = $request->q;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhere('description', 'like', '%' . $search . '%');
                });
            }
            $departments = $query->get();
            return response()->json($departments);
        } catch (\Exception $e) {
            Log::error('Failed to fetch departments', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to fetch departments'], 500);
        }
    }

    public function show($id)
    {
        try {
            $department = Department::withCount('employees')->findOrFail($id);
            return response()->json($department);
        } catch (\Exception $e) {
            Log::error('Error fetching department', ['department_id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['message' => 'Department not found'], 404);
        }
    }

    public function store(Request $request)
    {
        try {
            Log::info('Store department request', ['data' => $request->all()]);

            $validated = $request->validate([
                'name' => 'required|string|max:100|unique:departments,name',
                'description' => 'nullable|string',
            ]);

            $department = Department::create($validated);
            Log::info('Department created successfully', ['department_id' => $department->department_id]);

            return response()->json($department, 201);
        } catch (ValidationException $e) {
            Log::error('Validation error in store department', ['errors' => $e->errors()]);
            return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error in store department', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to create department'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $department = Department::findOrFail($id);
            Log::info('Update department request', ['department_id' => $id, 'data' => $request->all()]);

            $validated = $request->validate([
                'name' => 'required|string|max:100|unique:departments,name,' . $id . ',department_id',
                'description' => 'nullable|string',
            ]);

            $department->update($validated);
            Log::info('Department updated successfully', ['department_id' => $department->department_id]);

            return response()->json($department);
        } catch (ValidationException $e) {
            Log::error('Validation error in update department', ['errors' => $e->errors()]);
            return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error in update department', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to update department'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $department = Department::findOrFail($id);
            if ($department->employees()->count() > 0) {
                return response()->json(['message' => 'Cannot delete department with associated employees'], 400);
            }
            $department->delete();
            Log::info('Department deleted successfully', ['department_id' => $id]);
            return response()->json(null, 204);
        } catch (\Exception $e) {
            Log::error('Error in delete department', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to delete department'], 500);
        }
    }
}