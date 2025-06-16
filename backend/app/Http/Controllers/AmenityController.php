<?php

namespace App\Http\Controllers;

use App\Models\Amenity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class AmenityController extends Controller
{
    public function index(Request $request)
    {
        try {
            $perPage = $request->query('per_page', 10);
            $amenities = Amenity::query()->paginate($perPage);
            Log::info('Fetched amenities:', ['data' => $amenities->items()]);
            return response()->json([
                'success' => true,
                'data' => $amenities->items(),
                'current_page' => $amenities->currentPage(),
                'total' => $amenities->total(),
                'per_page' => $amenities->perPage(),
                'last_page' => $amenities->lastPage()
            ], 200);
        } catch (\Exception $e) {
            Log::error('Index amenities error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        Log::info('Store amenity request:', $request->all());
        $validator = Validator::make($request->all(), [
            'amenity_name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            Log::warning('Validation failed:', $validator->errors()->toArray());
            return response()->json(['success' => false, 'message' => 'Dữ liệu không hợp lệ', 'errors' => $validator->errors()], 422);
        }

        try {
            $data = [
                'amenity_name' => $request->amenity_name,
                'description' => $request->description ?: null
            ];
            $amenity = Amenity::create($data);
            Log::info('Amenity created:', $amenity->toArray());
            return response()->json(['success' => true, 'data' => $amenity], 201);
        } catch (\Exception $e) {
            Log::error('Store amenity error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $amenity_id)
    {
        Log::info('Update amenity request:', ['amenity_id' => $amenity_id, 'data' => $request->all()]);
        $validator = Validator::make($request->all(), [
            'amenity_name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            Log::warning('Validation failed:', $validator->errors()->toArray());
            return response()->json(['success' => false, 'message' => 'Dữ liệu không hợp lệ', 'errors' => $validator->errors()], 422);
        }

        try {
            $amenity = Amenity::findOrFail($amenity_id);
            $data = [
                'amenity_name' => $request->amenity_name,
                'description' => $request->description ?: null
            ];
            $amenity->update($data);
            Log::info('Amenity updated:', $amenity->toArray());
            return response()->json(['success' => true, 'data' => $amenity], 200);
        } catch (\Exception $e) {
            Log::error('Update amenity error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($amenity_id)
    {
        Log::info('Delete amenity request:', ['amenity_id' => $amenity_id]);
        try {
            $amenity = Amenity::findOrFail($amenity_id);
            $amenity->delete();
            Log::info('Amenity deleted:', ['amenity_id' => $amenity_id]);
            return response()->json(['success' => true, 'message' => 'Xóa tiện nghi thành công'], 200);
        } catch (\Exception $e) {
            Log::error('Delete amenity error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }
}