<?php
namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Employee::with('department');
            if ($request->has('q') && $request->q) {
                $search = $request->q;
                $query->where(function ($q) use ($search) {
                    $q->where('full_name', 'like', '%' . $search . '%')
                      ->orWhere('email', 'like', '%' . $search . '%');
                });
            }
            $employees = $query->get();
            return response()->json($employees);
        } catch (\Exception $e) {
            Log::error('Failed to fetch employees', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);
            return response()->json(['message' => 'Failed to fetch employees'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            Log::info('Store employee request', ['data' => $request->all()]);

            $validated = $request->validate([
                'full_name' => 'required|string|max:100',
                'gender' => 'required|in:Male,Female',
                'birth_date' => 'required|date_format:Y-m-d',
                'email' => 'required|email|unique:employees,email',
                'phone' => 'required|string|max:15|regex:/^[0-9]{10,15}$/',
                'address' => 'nullable|string|max:255',
                'department_id' => 'nullable|exists:departments,department_id',
                'start_date' => 'required|date_format:Y-m-d',
            ]);

            $employee = Employee::create($validated);
            Log::info('Employee created successfully', [
                'employee_id' => $employee->employee_id,
                'email' => $validated['email']
            ]);

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
                'full_name' => 'required|string|max:100',
                'gender' => 'required|in:Male,Female',
                'birth_date' => 'required|date_format:Y-m-d',
                'email' => 'required|email|unique:employees,email,' . $id . ',employee_id',
                'phone' => 'required|string|max:15|regex:/^[0-9]{10,15}$/',
                'address' => 'nullable|string|max:255',
                'department_id' => 'nullable|exists:departments,department_id',
                'start_date' => 'required|date_format:Y-m-d',
            ]);

            $employee->update($validated);
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
            $employee->delete();
            Log::info('Employee deleted successfully', ['employee_id' => $id]);
            return response()->json(['message' => 'Employee deleted successfully'], 204);
        } catch (\Exception $e) {
            Log::error('Error in delete employee', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to delete employee'], 500);
        }
    }

}