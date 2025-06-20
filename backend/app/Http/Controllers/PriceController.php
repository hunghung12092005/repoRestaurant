<?php

namespace App\Http\Controllers;

use App\Models\Price;
use App\Models\RoomType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class PriceController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Price::with('room_type')->orderBy('priority', 'desc');
            $perPage = $request->query('per_page', 10);
            $page = $request->query('page', 1);

            if ($perPage === 'all') {
                $prices = $query->get();
                $total = $prices->count();
                $currentPage = 1;
            } else {
                $prices = $query->paginate($perPage, ['*'], 'page', $page);
                $total = $prices->total();
                $currentPage = $prices->currentPage();
            }

            return response()->json([
                'status' => true,
                'data' => $prices->items(),
                'total' => $total,
                'current_page' => $currentPage,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Fetch prices error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Lấy danh sách giá phòng thất bại: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        Log::info('Store price request:', $request->all());
        $validator = Validator::make($request->all(), [
            'type_id' => 'required|exists:room_types,type_id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'price_per_night' => 'required|numeric|min:0',
            'hourly_price' => 'required|numeric|min:0',
            'priority' => 'required|integer|min:0|max:10',
        ]);

        if ($validator->fails()) {
            Log::warning('Validation failed:', $validator->errors()->toArray());
            return response()->json(['success' => false, 'message' => 'Dữ liệu không hợp lệ', 'errors' => $validator->errors()], 422);
        }

        try {
            $data = $request->only(['type_id', 'start_date', 'end_date', 'description', 'price_per_night', 'hourly_price', 'priority']);
            $data['description'] = $data['description'] ?: null;

            // Kiểm tra xung đột ngày và ưu tiên
            $conflictingPrice = Price::where('type_id', $data['type_id'])
                ->where(function ($query) use ($data) {
                    $query->where(function ($q) use ($data) {
                        $q->where('start_date', '<=', $data['end_date'])
                          ->where('end_date', '>=', $data['start_date']);
                    });
                })
                ->orderBy('priority', 'desc')
                ->first();

            if ($conflictingPrice && $conflictingPrice->priority >= $data['priority']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ngày đã có giá với mức ưu tiên cao hơn hoặc bằng. Vui lòng tăng mức ưu tiên.',
                    'errors' => ['priority' => ['Mức ưu tiên phải cao hơn ' . $conflictingPrice->priority]]
                ], 422);
            }

            $price = Price::create($data);
            Log::info('Price created:', $price->toArray());
            return response()->json(['success' => true, 'data' => $price], 201);
        } catch (\Exception $e) {
            Log::error('Store price error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $price_id)
    {
        Log::info('Update price request:', ['price_id' => $price_id, 'data' => $request->all()]);
        $validator = Validator::make($request->all(), [
            'type_id' => 'required|exists:room_types,type_id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'price_per_night' => 'required|numeric|min:0',
            'hourly_price' => 'required|numeric|min:0',
            'priority' => 'required|integer|min:0|max:10',
        ]);

        if ($validator->fails()) {
            Log::warning('Validation failed:', $validator->errors()->toArray());
            return response()->json(['success' => false, 'message' => 'Dữ liệu không hợp lệ', 'errors' => $validator->errors()], 422);
        }

        try {
            $price = Price::findOrFail($price_id);
            $data = $request->only(['type_id', 'start_date', 'end_date', 'description', 'price_per_night', 'hourly_price', 'priority']);
            $data['description'] = $data['description'] ?: null;

            // Kiểm tra xung đột ngày và ưu tiên (loại trừ bản thân)
            $conflictingPrice = Price::where('type_id', $data['type_id'])
                ->where('price_id', '!=', $price_id)
                ->where(function ($query) use ($data) {
                    $query->where(function ($q) use ($data) {
                        $q->where('start_date', '<=', $data['end_date'])
                          ->where('end_date', '>=', $data['start_date']);
                    });
                })
                ->orderBy('priority', 'desc')
                ->first();

            if ($conflictingPrice && $conflictingPrice->priority >= $data['priority']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ngày đã có giá với mức ưu tiên cao hơn hoặc bằng. Vui lòng tăng mức ưu tiên.',
                    'errors' => ['priority' => ['Mức ưu tiên phải cao hơn ' . $conflictingPrice->priority]]
                ], 422);
            }

            $price->update($data);
            Log::info('Price updated:', $price->toArray());
            return response()->json(['success' => true, 'data' => $price], 200);
        } catch (\Exception $e) {
            Log::error('Update price error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($price_id)
    {
        Log::info('Delete price request:', ['price_id' => $price_id]);
        try {
            $price = Price::findOrFail($price_id);
            $price->delete();
            Log::info('Price deleted:', ['price_id' => $price_id]);
            return response()->json(['success' => true, 'message' => 'Xóa giá phòng thành công'], 200);
        } catch (\Exception $e) {
            Log::error('Delete price error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }
}