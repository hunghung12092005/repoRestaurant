<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Service::withCount('bookings')->latest(); // Lấy cả số lần được đặt

            $perPage = $request->query('per_page', 10);
            $paginator = $query->paginate($perPage);

            return response()->json([
                'status' => true,
                'data' => $paginator->items(),
                'total' => $paginator->total(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
            ], 200);
        } catch (\Exception $e) {
            Log::error('Fetch services error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Lấy danh sách dịch vụ thất bại.'], 500);
        }
    }
    public function indexAllService()
    {
        $services = Service::all(); // Lấy tất cả dịch vụ
        return response()->json($services);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_name' => 'required|string|max:100|unique:services,service_name',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Dữ liệu không hợp lệ', 'errors' => $validator->errors()], 422);
        }

        try {
            $service = Service::create($request->all());
            return response()->json(['success' => true, 'data' => $service, 'message' => 'Thêm dịch vụ thành công!'], 201);
        } catch (\Exception $e) {
            Log::error('Store service error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Lỗi máy chủ khi thêm dịch vụ.'], 500);
        }
    }

    public function update(Request $request, $service_id)
    {
        $service = Service::withCount('bookings')->findOrFail($service_id);

        // NẾU CÓ BOOKING, KHÔNG CHO PHÉP CẬP NHẬT
        if ($service->bookings_count > 0) {
            return response()->json([
                'success' => false, 
                'message' => 'Không thể cập nhật dịch vụ này vì đã được sử dụng.'
            ], 403); // 403 Forbidden
        }

        // Nếu không có booking, cho phép cập nhật
        $validator = Validator::make($request->all(), [
            'service_name' => 'required|string|max:100|unique:services,service_name,' . $service_id . ',service_id',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Dữ liệu không hợp lệ', 'errors' => $validator->errors()], 422);
        }

        try {
            $service->update($request->all());
            return response()->json(['success' => true, 'data' => $service, 'message' => 'Cập nhật dịch vụ thành công!'], 200);
        } catch (\Exception $e) {
            Log::error('Update service error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Lỗi máy chủ khi cập nhật dịch vụ.'], 500);
        }
    }

    public function destroy($service_id)
    {
        $service = Service::withCount('bookings')->findOrFail($service_id);

        // NẾU CÓ BOOKING, KHÔNG CHO PHÉP XÓA
        if ($service->bookings_count > 0) {
            return response()->json([
                'success' => false, 
                'message' => 'Không thể xóa dịch vụ này vì đã được sử dụng.'
            ], 400); // 400 Bad Request
        }

        // Nếu không có booking, cho phép xóa
        try {
            $service->delete();
            return response()->json(['success' => true, 'message' => 'Xóa dịch vụ thành công'], 200);
        } catch (\Exception $e) {
            Log::error('Delete service error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Lỗi máy chủ khi xóa dịch vụ.'], 500);
        }
    }
}