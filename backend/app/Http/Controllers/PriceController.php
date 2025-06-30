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
    //lấy giá ra clietn dựa vào ngày
    public function getPrice(Request $request)
    {
        $checkin = $request->input('checkin');
        $checkout = $request->input('checkout');
        $bookrooms = $request->input('bookrooms');

        // Lấy tất cả các giá phù hợp với ngày checkin và checkout
        $prices = Price::where('start_date', '<=', $checkout)
            ->where('end_date', '>=', $checkin)
            ->get();

        $groupedPrices = $prices->groupBy('type_id');

        $finalPrices = [];

        foreach ($groupedPrices as $typeId => $group) {
            $priorityPriceRecord = null;
            $standardPriceRecord = null;

            foreach ($group as $price) {
                // Lưu bản ghi ưu tiên
                if ($price->priority === 1 && $price->start_date <= $checkout && $price->end_date >= $checkin) {
                    $priorityPriceRecord = $price;
                }

                // Lưu bản ghi tiêu chuẩn
                if ($price->start_date <= $checkout && $price->end_date >= $checkin) {
                    $standardPriceRecord = $price;
                }
            }

            // Tính tổng số ngày khách ở
            $totalDays = (strtotime($checkout) - strtotime($checkin)) / (60 * 60 * 24) + 0;

            // Tính tổng giá 1 ngày 1 phòng
        $pricePerNight = $priorityPriceRecord ? $priorityPriceRecord->price_per_night : ($standardPriceRecord ? $standardPriceRecord->price_per_night : 0);
            $surcharges = 0;

            // Nếu có bản ghi ưu tiên, thêm phụ phí
            if ($priorityPriceRecord) {
                $surcharges = 100000 * $bookrooms; // Phụ phí
            }
            $so_tien1phong =  ($pricePerNight * $totalDays) ;
            // Cộng phụ phí và tổng phòng vào tổng giá và nhân với số ngày khách ở
            $totalPrice =  $so_tien1phong * $bookrooms + $surcharges;

            // Thêm thông tin vào kết quả
            $result = [
                'type_id' => $typeId,
                'total_days' => $totalDays,
                'gia_tien1ngay' => $standardPriceRecord ? $standardPriceRecord->price_per_night : 0,
                'priority' => $priorityPriceRecord ? 'yes' : 'no',
                'surcharges' => $surcharges,
                'so_tien1phong' => $so_tien1phong,
                'so_phong' => $bookrooms,
                'total_price' => $totalPrice,
                'gia1h' => $standardPriceRecord ? $standardPriceRecord->hourly_price : 0
            ];

            $finalPrices[] = $result;
        }

        // Nếu không có giá nào, trả về thông báo
        if (empty($finalPrices)) {
            return response()->json(['message' => 'No price available'], 404);
        }

        return response()->json($finalPrices);
    }
}
