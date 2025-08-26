<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\RoomType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class RoomTypeController extends Controller
{
    /**
     * Lấy danh sách loại phòng.
     */
     public function index()
    {
        try {
            $roomTypes = RoomType::with(['amenities'])
                ->withCount('rooms')
                ->withCount(['rooms as rooms_with_bookings_count' => function ($query) {
                    $query->has('bookings');
                }])
                ->latest()
                ->get();
            return response()->json(['status' => true, 'data' => $roomTypes], 200);
        } catch (\Exception $e) {
            Log::error('Lỗi lấy danh sách loại phòng: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Lấy danh sách thất bại'], 500);
        }
    }

    /**
     * Lấy chi tiết một loại phòng.
     */
    public function show($type_id, Request $request)
    {
        try {
            $checkInDate = $request->query('check_in', Carbon::now()->toDateString());
            $roomType = RoomType::with(['amenities', 'rooms'])->findOrFail($type_id);

            // Giả sử bạn có bảng `prices`
            $price = DB::table('prices')
                ->where('type_id', $type_id)
                ->where('start_date', '<=', $checkInDate)
                ->where('end_date', '>=', $checkInDate)
                ->orderBy('priority', 'desc')
                ->first();

            $priceData = $price ? [
                'price_per_night' => $price->price_per_night,
                'hourly_price' => $price->hourly_price,
            ] : ['price_per_night' => 0, 'hourly_price' => 0];

            return response()->json([
                'status' => true,
                'data' => [
                    'type_id' => $roomType->type_id,
                    'type_name' => $roomType->type_name,
                    'description' => $roomType->description,
                    'bed_count' => $roomType->bed_count,
                    'max_occupancy' => $roomType->max_occupancy,
                    'max_occupancy_child' => $roomType->max_occupancy_child,
                    'images' => $roomType->images,
                    'amenities' => $roomType->amenities,
                    'price' => $priceData,
                    'rate' => $roomType->rate,
                    'm2' => $roomType->m2
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy loại phòng (type_id: ' . $type_id . '): ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Không tìm thấy loại phòng'], 404);
        }
    }

    /**
     * Lưu một loại phòng mới.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type_name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'bed_count' => 'required|integer|min:1',
            'max_occupancy' => 'required|integer|min:1',
            'max_occupancy_child' => 'required|integer|min:0',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048',
            'amenity_ids' => 'nullable|array',
            'amenity_ids.*' => 'exists:amenities,amenity_id'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => 'Dữ liệu không hợp lệ', 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $imageNames = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                    $image->move(public_path('images/room_type'), $imageName);
                    $imageNames[] = $imageName;
                }
            }

            $roomType = RoomType::create([
                'type_name' => $request->type_name,
                'description' => $request->description,
                'bed_count' => $request->bed_count,
                'max_occupancy' => $request->max_occupancy,
                'max_occupancy_child' => $request->max_occupancy_child,
                'images' => !empty($imageNames) ? json_encode($imageNames) : null,
            ]);

            if ($request->has('amenity_ids')) {
                $roomType->amenities()->sync($request->amenity_ids);
            }
            DB::commit();
            return response()->json(['status' => true, 'data' => $roomType, 'message' => 'Thêm loại phòng thành công'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi thêm loại phòng: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Thêm loại phòng thất bại'], 500);
        }
    }

    /**
     * Cập nhật loại phòng.
     */
    public function update(Request $request, $type_id)
    {
        $roomType = RoomType::withCount(['rooms as rooms_with_bookings_count' => function ($query) {
            $query->has('bookings');
        }])->findOrFail($type_id);

        if ($roomType->rooms_with_bookings_count > 0) {
            return response()->json([
                'status' => false,
                'message' => 'Không thể cập nhật loại phòng này vì đã có lịch sử đặt phòng liên quan.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'type_name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'bed_count' => 'required|integer|min:1',
            'max_occupancy' => 'required|integer|min:1',
            'max_occupancy_child' => 'required|integer|min:0',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048',
            'existing_images' => 'nullable|array',
            'amenity_ids' => 'nullable|array',
            'amenity_ids.*' => 'exists:amenities,amenity_id'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => 'Dữ liệu không hợp lệ', 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $oldImages = $roomType->images ? json_decode($roomType->images, true) : [];
            $keptImages = $request->input('existing_images', []);
            $imagesToDelete = array_diff($oldImages, $keptImages);
            foreach ($imagesToDelete as $imageFile) {
                $filePath = public_path('images/room_type/' . $imageFile);
                if (File::exists($filePath)) File::delete($filePath);
            }

            $newImageNames = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                    $image->move(public_path('images/room_type'), $imageName);
                    $newImageNames[] = $imageName;
                }
            }

            $finalImageNames = array_merge($keptImages, $newImageNames);

            $roomType->update([
                'type_name' => $request->type_name,
                'description' => $request->description,
                'bed_count' => $request->bed_count,
                'max_occupancy' => $request->max_occupancy,
                'max_occupancy_child' => $request->max_occupancy_child,
                'images' => !empty($finalImageNames) ? json_encode($finalImageNames) : null,
            ]);

            $roomType->amenities()->sync($request->input('amenity_ids', []));
            DB::commit();
            return response()->json(['status' => true, 'data' => $roomType, 'message' => 'Cập nhật loại phòng thành công'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi cập nhật loại phòng: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Cập nhật loại phòng thất bại'], 500);
        }
    }

    /**
     * Xóa loại phòng.
     */
    public function destroy($type_id)
    {
        $roomType = RoomType::withCount('rooms')->findOrFail($type_id);
        
        if ($roomType->rooms_count > 0) {
            return response()->json(['status' => false, 'message' => 'Không thể xóa loại phòng này vì đang có phòng trực thuộc.'], 400);
        }

        DB::beginTransaction();
        try {
            if ($roomType->images) {
                $images = json_decode($roomType->images, true);
                foreach ($images as $image) {
                    $filePath = public_path('images/room_type/' . $image);
                    if (File::exists($filePath)) File::delete($filePath);
                }
            }
            $roomType->amenities()->detach();
            $roomType->delete();
            DB::commit();
            return response()->json(['status' => true, 'message' => 'Xóa loại phòng thành công'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi xóa loại phòng: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Xóa loại phòng thất bại'], 500);
        }
    }

    /**
     * Xóa ảnh khỏi loại phòng.
     */
    public function deleteImage(Request $request, $type_id)
    {
        $validator = Validator::make($request->all(), [
            'image_name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => 'Dữ liệu không hợp lệ', 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $roomType = RoomType::findOrFail($type_id);
            $imagePaths = $roomType->images ? json_decode($roomType->images, true) : [];
            $imageName = $request->image_name;
            $key = array_search($imageName, $imagePaths);

            if ($key === false) {
                DB::rollBack();
                return response()->json(['status' => false, 'message' => 'Ảnh không tồn tại trong danh sách'], 404);
            }
            $filePath = public_path('images/room_type/' . $imageName);
            if (File::exists($filePath)) {
                File::delete($filePath);
            }
            unset($imagePaths[$key]);
            $roomType->images = !empty($imagePaths) ? json_encode(array_values($imagePaths)) : null;
            $roomType->save();

            DB::commit();
            $roomType->load('amenities');
            return response()->json(['status' => true, 'data' => $roomType, 'message' => 'Xóa ảnh thành công'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi xóa ảnh: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Xóa ảnh thất bại'], 500);
        }
    }
}