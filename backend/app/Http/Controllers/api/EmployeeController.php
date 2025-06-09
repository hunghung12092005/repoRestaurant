<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Department;
use Illuminate\Http\Request;

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
                  ->orWhere('position', 'like', '%' . $search . '%')
                  ->orWhereHas('department', function ($q) use ($search) {
                      $q->where('name', 'like', '%' . $search . '%');
                  });
            });
        }

        $employees = $query->get();
        return response()->json($employees);
    }

    public function show($id)
    {
        $employee = Employee::with('department')->findOrFail($id); // $id là employee_id
        return response()->json($employee);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'gender' => 'required|in:Male,Female,Other',
            'birth_date' => 'required|date',
            'email' => 'required|email|unique:employees,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'position' => 'required|string|max:100',
            'salary' => 'required|numeric|min:0|max:99999999.99',
            'department_id' => 'required|exists:departments,department_id',
            'hire_date' => 'required|date',
            'status' => 'required|in:Active,Inactive,On Leave',
        ]);

        $employee = Employee::create($validated);
        return response()->json($employee, 201);
    }

    public function update(Request $request, $id)
    {
        $employee = Employee::findOrFail($id); // $id là employee_id
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'gender' => 'required|in:Male,Female,Other',
            'birth_date' => 'required|date',
            'email' => 'required|email|unique:employees,email,' . $id . ',employee_id', // Chỉ định rõ employee_id
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'position' => 'required|string|max:100',
            'salary' => 'required|numeric|min:0|max:99999999.99',
            'department_id' => 'required|exists:departments,department_id',
            'hire_date' => 'required|date',
            'status' => 'required|in:Active,Inactive,On Leave',
        ]);

        $employee->update($validated);
        return response()->json($employee);
    }

    public function destroy($id)
    {
        $employee = Employee::findOrFail($id); // $id là employee_id
        $employee->delete();
        return response()->json(null, 204);
    }
}
