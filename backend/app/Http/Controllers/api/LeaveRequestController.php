<?php
namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;

class LeaveRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = LeaveRequest::with('employee');
        if ($request->has('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        $leaveRequests = $query->orderBy('created_at', 'desc')->get();
        return response()->json($leaveRequests);
    }

    public function store(Request $request)
    {
        try {
            Log::info('Store leave request', ['data' => $request->all()]);

            $validated = $request->validate([
                'employee_id' => 'required|exists:employees,employee_id',
                'leave_type' => 'required|string|in:Annual,Sick,Maternity,Unpaid',
                'start_date' => 'required|date|after_or_equal:today',
                'end_date' => 'required|date|after_or_equal:start_date',
                'reason' => 'nullable|string|max:500',
            ]);

            $leaveRequest = LeaveRequest::create($validated);
            Log::info('Leave request created', ['leave_id' => $leaveRequest->leave_id]);

            return response()->json($leaveRequest->load('employee'), 201);
        } catch (ValidationException $e) {
            Log::error('Validation error in store leave request', ['errors' => $e->errors()]);
            return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (QueryException $e) {
            Log::error('Database error in store leave request', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Database error occurred'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $leaveRequest = LeaveRequest::findOrFail($id);
            Log::info('Update leave request', ['leave_id' => $id, 'data' => $request->all()]);

            $validated = $request->validate([
                'status' => 'required|in:Pending,Approved,Rejected',
            ]);

            $leaveRequest->update($validated);
            return response()->json($leaveRequest->load('employee'));
        } catch (ValidationException $e) {
            Log::error('Validation error in update leave request', ['errors' => $e->errors()]);
            return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (QueryException $e) {
            Log::error('Database error in update leave request', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Database error occurred'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $leaveRequest = LeaveRequest::findOrFail($id);
            $leaveRequest->delete();
            Log::info('Leave request deleted', ['leave_id' => $id]);
            return response()->json(['message' => 'Leave request deleted successfully']);
        } catch (\Exception $e) {
            Log::error('Error in delete leave request', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to delete leave request'], 500);
        }
    }
}