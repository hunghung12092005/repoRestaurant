<?php

namespace App\Http\Controllers;

use App\Models\Price;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class PriceController extends Controller
{
    public function index(Request $request)
    {
        try {
            $perPage = $request->query('per_page', 10);
            $page = $request->query('page', 1);
            $cacheKey = 'prices_page_' . $page . '_per_' . $perPage;
            $prices = Cache::remember($cacheKey, 60, function () use ($perPage) {
                return Price::with('roomType')->paginate($perPage);
            });
            return response()->json([
                'success' => true,
                'data' => $prices->items(),
                'current_page' => $prices->currentPage(),
                'total' => $prices->total(),
                'per_page' => $prices->perPage(),
                'last_page' => $prices->lastPage()
            ], 200);
        } catch (\Exception $e) {
            Log::error('Index prices error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }
    //lấy giá phòng dựa vào ngày khách nhập
    public function getPrice(Request $request)
    {
        $checkin = $request->input('checkin');
        $checkout = $request->input('checkout');

        // Lấy tất cả các giá phù hợp với ngày checkin và checkout
        $prices = Price::where('start_date', '<=', $checkout)
            ->where('end_date', '>=', $checkin)
            ->get();

        $finalPrices = [];

        foreach ($prices as $price) {
            // Nếu có giá ưu tiên, lấy giá ưu tiên
            if ($price->priority !== 0) {
                $finalPrices[] = [
                    'price' => $price->priority,
                    'type' => 'priority',
                    'details' => $price
                ];
            } else {
                // Nếu không có giá ưu tiên, lấy giá mỗi đêm
                $finalPrices[] = [
                    'price' => $price->price_per_night,
                    'type' => 'standard',
                    'details' => $price
                ];
            }
        }

        return response()->json(['prices' => $finalPrices]);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type_id' => 'required|exists:room_types,type_id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'price_per_night' => 'required|numeric|min:0',
            'hourly_price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Dữ liệu không hợp lệ', 'errors' => $validator->errors()], 422);
        }

        try {
            $pricing = Price::create($request->all());
            $pricing->load('roomType');
            Cache::flush(); // Xóa cache để đồng bộ
            return response()->json(['success' => true, 'data' => $pricing], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'type_id' => 'required|exists:room_types,type_id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'price_per_night' => 'required|numeric|min:0',
            'hourly_price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Dữ liệu không hợp lệ', 'errors' => $validator->errors()], 422);
        }

        try {
            $pricing = Price::findOrFail($id);
            $pricing->update($request->all());
            $pricing->load('roomType');
            Cache::flush(); // Xóa cache để đồng bộ
            return response()->json(['success' => true, 'data' => $pricing], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $pricing = Price::findOrFail($id);
            $pricing->delete();
            Cache::flush(); // Xóa cache để đồng bộ
            return response()->json(['success' => true, 'message' => 'Xóa thành công'], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }
}
