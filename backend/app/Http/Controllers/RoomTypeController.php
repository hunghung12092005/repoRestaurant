<?php

namespace App\Http\Controllers;

use App\Models\RoomType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RoomTypeController extends Controller
{
    public function index()
    {
        try {
            $roomTypes = RoomType::with(['amenities', 'services'])->get();
            return response()->json([
                'status' => true,
                'data' => $roomTypes,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Lấy danh sách loại phòng thất bại: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type_name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'bed_count' => 'required|integer|min:1',
            'max_occupancy' => 'required|integer|min:1',
            'amenity_ids' => 'nullable|array',
            'amenity_ids.*' => 'exists:amenities,amenity_id',
            'service_ids' => 'nullable|array',
            'service_ids.*' => 'exists:services,service_id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $roomType = RoomType::create([
                'type_name' => $request->type_name,
                'description' => $request->description,
                'bed_count' => $request->bed_count,
                'max_occupancy' => $request->max_occupancy,
            ]);

            // Đồng bộ tiện ích
            if ($request->has('amenity_ids')) {
                $roomType->amenities()->sync($request->amenity_ids);
            }

            // Đồng bộ dịch vụ
            if ($request->has('service_ids')) {
                $roomType->services()->sync($request->service_ids);
            }

            DB::commit();

            // Tải lại dữ liệu với mối quan hệ
            $roomType->load(['amenities', 'services']);

            return response()->json([
                'status' => true,
                'data' => $roomType,
                'message' => 'Thêm loại phòng thành công',
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Thêm loại phòng thất bại: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $type_id)
    {
        $roomType = RoomType::find($type_id);
        if (!$roomType) {
            return response()->json([
                'status' => false,
                'message' => 'Loại phòng không tồn tại',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'type_name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'bed_count' => 'required|integer|min:1',
            'max_occupancy' => 'required|integer|min:1',
            'amenity_ids' => 'nullable|array',
            'amenity_ids.*' => 'exists:amenities,amenity_id',
            'service_ids' => 'nullable|array',
            'service_ids.*' => 'exists:services,service_id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $roomType->update([
                'type_name' => $request->type_name,
                'description' => $request->description,
                'bed_count' => $request->bed_count,
                'max_occupancy' => $request->max_occupancy,
            ]);

            // Đồng bộ tiện ích
            $roomType->amenities()->sync($request->amenity_ids ?? []);

            // Đồng bộ dịch vụ
            $roomType->services()->sync($request->service_ids ?? []);

            DB::commit();

            // Tải lại dữ liệu với mối quan hệ
            $roomType->load(['amenities', 'services']);

            return response()->json([
                'status' => true,
                'data' => $roomType,
                'message' => 'Cập nhật loại phòng thành công',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Cập nhật loại phòng thất bại: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($type_id)
    {
        $roomType = RoomType::find($type_id);
        if (!$roomType) {
            return response()->json([
                'status' => false,
                'message' => 'Loại phòng không tồn tại',
            ], 404);
        }

        try {
            // Kiểm tra xem loại phòng có phòng nào đang sử dụng không
            if ($roomType->rooms()->count() > 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'Không thể xóa loại phòng vì có phòng đang sử dụng nó',
                ], 400);
            }

            DB::beginTransaction();
            $roomType->delete();
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Xóa loại phòng thành công',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Xóa loại phòng thất bại: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show($type_id, Request $request)
    {
        try {
            $checkInDate = $request ? $request->query('check_in', Carbon::now()->toDateString()) : Carbon::now()->toDateString();
            $roomType = RoomType::with(['amenities', 'services', 'rooms', 'prices'])
                ->findOrFail($type_id);

            // Lấy giá phù hợp với ngày check-in
            $price = $roomType->prices()
                ->where('start_date', '<=', $checkInDate)
                ->where('end_date', '>=', $checkInDate)
                ->orderBy('priority', 'desc')
                ->first();

            $priceData = $price ? [
                'price_per_night' => $price->price_per_night,
                'hourly_price' => $price->hourly_price,
                'start_date' => $price->start_date,
                'end_date' => $price->end_date,
            ] : ['price_per_night' => 0, 'hourly_price' => 0];

            Log::info('RoomType show - type_id: ' . $type_id . ', checkInDate: ' . $checkInDate . ', price: ' . json_encode($priceData));

            return response()->json([
                'type_id' => $roomType->type_id,
                'type_name' => $roomType->type_name,
                'description' => $roomType->description,
                'bed_count' => $roomType->bed_count,
                'max_occupancy' => $roomType->max_occupancy,
                'amenities' => $roomType->amenities,
                'services' => $roomType->services,
                'price' => $priceData,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy loại phòng (type_id: ' . $type_id . '): ' . $e->getMessage());
            return response()->json(['error' => 'Không tìm thấy loại phòng: ' . $e->getMessage()], 404);
        }
    }
}
