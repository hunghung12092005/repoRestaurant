<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
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
            Log::error('Lỗi lấy danh sách loại phòng: ' . $e->getMessage());
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
            'max_occupancy_child' => 'required|integer|min:1',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'amenity_ids' => 'nullable|array',
            'amenity_ids.*' => 'exists:amenities,amenity_id'
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

            $imagePath = null;
            if ($request->hasFile('images')) {
                $image = $request->file('images')[0]; // Chỉ lấy ảnh đầu tiên
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('images/room_type'), $imageName);
                $imagePath = $imageName;
            }

            $roomType = RoomType::create([
                'type_name' => $request->type_name,
                'description' => $request->description,
                'bed_count' => $request->bed_count,
                'max_occupancy' => $request->max_occupancy,
                'max_occupancy_child' => $request->max_occupancy_child,
                'images' => $imagePath ? json_encode([$imagePath]) : null,
            ]);

            if ($request->has('amenity_ids')) {
                $roomType->amenities()->sync($request->amenity_ids);
            }
            if ($request->has('service_ids')) {
                $roomType->services()->sync($request->service_ids);
            }

            DB::commit();

            $roomType->load(['amenities', 'services']);
            return response()->json([
                'status' => true,
                'data' => $roomType,
                'message' => 'Thêm loại phòng thành công',
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi thêm loại phòng: ' . $e->getMessage());
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
            'max_occupancy_child' => 'required|integer|min:1',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'amenity_ids' => 'nullable|array',
            'amenity_ids.*' => 'exists:amenities,amenity_id'
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

            $existingImages = $roomType->images ? json_decode($roomType->images, true) : [];
            if ($request->hasFile('images')) {
                // Xóa ảnh cũ nếu có ảnh mới
                if (!empty($existingImages)) {
                    foreach ($existingImages as $oldImage) {
                        $filePath = public_path('images/room_type/' . $oldImage);
                        if (file_exists($filePath)) {
                            unlink($filePath);
                        }
                    }
                }
                // Lưu chỉ 1 ảnh mới
                $image = $request->file('images')[0];
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('images/room_type'), $imageName);
                $newImages = [$imageName];
            } else {
                $newImages = $existingImages; // Giữ ảnh cũ nếu không có ảnh mới
            }

            $roomType->update([
                'type_name' => $request->type_name,
                'description' => $request->description,
                'bed_count' => $request->bed_count,
                'max_occupancy' => $request->max_occupancy,
                'max_occupancy_child' => $request->max_occupancy_child,
                'images' => !empty($newImages) ? json_encode($newImages) : null,
            ]);

            $roomType->amenities()->sync($request->amenity_ids ?? []);
            $roomType->services()->sync($request->service_ids ?? []);

            DB::commit();

            $roomType->load(['amenities', 'services']);
            return response()->json([
                'status' => true,
                'data' => $roomType,
                'message' => 'Cập nhật loại phòng thành công',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi cập nhật loại phòng: ' . $e->getMessage());
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
            if ($roomType->images) {
                $images = json_decode($roomType->images, true);
                foreach ($images as $image) {
                    $filePath = public_path('images/room_type/' . $image);
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                }
            }

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
            Log::error('Lỗi xóa loại phòng: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Xóa loại phòng thất bại: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function deleteImage(Request $request, $type_id)
    {
        $validator = Validator::make($request->all(), [
            'image_index' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors(),
            ], 422);
        }

        $roomType = RoomType::find($type_id);
        if (!$roomType) {
            return response()->json([
                'status' => false,
                'message' => 'Loại phòng không tồn tại',
            ], 404);
        }

        try {
            DB::beginTransaction();
            $imagePaths = $roomType->images ? json_decode($roomType->images, true) : [];
            $imageIndex = $request->image_index;

            if (!isset($imagePaths[$imageIndex])) {
                return response()->json([
                    'status' => false,
                    'message' => 'Ảnh không tồn tại',
                ], 404);
            }

            $filePath = public_path('images/room_type/' . $imagePaths[$imageIndex]);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            unset($imagePaths[$imageIndex]);
            $imagePaths = array_values($imagePaths);
            $roomType->update(['images' => !empty($imagePaths) ? json_encode($imagePaths) : null]);

            DB::commit();

            $roomType->load(['amenities', 'services']);
            return response()->json([
                'status' => true,
                'data' => $roomType,
                'message' => 'Xóa ảnh thành công',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi xóa ảnh: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Xóa ảnh thất bại: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show($type_id, Request $request)
    {
        try {
            $checkInDate = $request->query('check_in', Carbon::now()->toDateString());
            $roomType = RoomType::with(['amenities', 'services', 'rooms', 'prices'])
                ->findOrFail($type_id);

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
                'max_occupancy_child' => $roomType->max_occupancy_child,
                'images' => $roomType->images ?? [],
                'amenities' => $roomType->amenities,
                'price' => $priceData,
                'rate' => $roomType->rate,   
                'm2' => $roomType->m2
            ], 200);
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy loại phòng (type_id: ' . $type_id . '): ' . $e->getMessage());
            return response()->json(['error' => 'Không tìm thấy loại phòng: ' . $e->getMessage()], 404);
        }
    }
}