<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Service::query();
            if ($request->query('per_page') === 'all') {
                $services = $query->get();
                $total = $services->count();
                $currentPage = 1;
                $data = $services;
            } else {
                $perPage = $request->query('per_page', 10);
                $page = $request->query('page', 1);
                $services = $query->paginate($perPage, ['*'], 'page', $page);
                $total = $services->total();
                $currentPage = $services->currentPage();
                $data = $services->items(); 
            }
            return response()->json([
                'status' => true,
                'data' => $data,
                'total' => $total,
                'current_page' => $currentPage,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Fetch services error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Lấy danh sách dịch vụ thất bại: ' . $e->getMessage(),
            ], 500);
        }
    }
    public function indexAllService()
    {
        $services = Service::all(); // Lấy tất cả dịch vụ
        return response()->json($services);
    }

    public function store(Request $request)
    {
        Log::info('Store service request:', $request->all());
        $validator = Validator::make($request->all(), [
            'service_name' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            Log::warning('Validation failed:', $validator->errors()->toArray());
            return response()->json(['success' => false, 'message' => 'Dữ liệu không hợp lệ', 'errors' => $validator->errors()], 422);
        }

        try {
            $data = [
                'service_name' => $request->service_name,
                'price' => $request->price,
                'description' => $request->description ?: null
            ];
            $service = Service::create($data);
            Log::info('Service created:', $service->toArray());
            return response()->json(['success' => true, 'data' => $service], 201);
        } catch (\Exception $e) {
            Log::error('Store service error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $service_id)
    {
        Log::info('Update service request:', ['service_id' => $service_id, 'data' => $request->all()]);
        $validator = Validator::make($request->all(), [
            'service_name' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            Log::warning('Validation failed:', $validator->errors()->toArray());
            return response()->json(['success' => false, 'message' => 'Dữ liệu không hợp lệ', 'errors' => $validator->errors()], 422);
        }

        try {
            $service = Service::findOrFail($service_id);
            $data = [
                'service_name' => $request->service_name,
                'price' => $request->price,
                'description' => $request->description ?: null
            ];
            $service->update($data);
            Log::info('Service updated:', $service->toArray());
            return response()->json(['success' => true, 'data' => $service], 200);
        } catch (\Exception $e) {
            Log::error('Update service error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($service_id)
    {
        Log::info('Delete service request:', ['service_id' => $service_id]);
        try {
            $service = Service::findOrFail($service_id);
            $service->delete();
            Log::info('Service deleted:', ['service_id' => $service_id]);
            return response()->json(['success' => true, 'message' => 'Xóa dịch vụ thành công'], 200);
        } catch (\Exception $e) {
            Log::error('Delete service error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }
}