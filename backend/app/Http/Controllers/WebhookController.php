<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\BookingHotel;

class WebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        $payload = $request->all();

        // Lấy signature và checksum_key
        $receivedSignature = $payload['signature'] ?? null;
        $checksumKey = env('PAYOS_CHECKSUM_KEY'); // từ .env

        // Dữ liệu cần kiểm tra chữ ký
        $data = $payload['data'] ?? [];

        // Kiểm tra chữ ký
        if (!$this->isValidSignature($data, $receivedSignature, $checksumKey)) {
            Log::warning('❌ Webhook sai chữ ký', [
                'received' => $receivedSignature,
                'data' => $data
            ]);
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        Log::info('✅ Webhook hợp lệ từ PayOS', $data);

        // Tìm đơn theo orderCode
        $orderCode = $data['orderCode'] ?? null;
        $code = $data['code'] ?? null;

        if ($orderCode && $code) {
            $booking = BookingHotel::where('orderCode', $orderCode)->first();

            if ($booking) {
                $newStatus = $code === '00' ? 'completed' : 'failed';
                $booking->payment_status = $newStatus;
                $booking->save();

                Log::info("💰 Đơn hàng [$orderCode] cập nhật trạng thái [$newStatus]");

                // Nếu thanh toán thành công thì cập nhật booking_hotel_detail
                if ($newStatus === 'completed') {
                    $details = \App\Models\BookingHotelDetail::where('booking_id', $booking->booking_id)->get();

                    foreach ($details as $detail) {
                        $detail->update([
                            'thanh_toan_truoc' => $detail->gia_phong // copy từ gia_phong sang thanh_toan_truoc
                        ]);
                    }

                    Log::info("📌 Đã cập nhật cột thanh_toan_truoc cho booking_id = {$booking->booking_id}");
                }
            } else {
                Log::warning("⚠️ Không tìm thấy đơn hàng với orderCode [$orderCode]");
            }
        } else {
            Log::warning("⚠️ Dữ liệu webhook thiếu orderCode hoặc code");
        }

        return response()->json(['message' => 'Webhook xử lý thành công'], 200);
    }

    private function isValidSignature($transaction, $signature, $checksum_key)
    {
        ksort($transaction);
        $transaction_str_arr = [];

        foreach ($transaction as $key => $value) {
            if (in_array($value, ["undefined", "null"]) || is_null($value)) {
                $value = "";
            }

            if (is_array($value)) {
                ksort($value);
                $value = json_encode($value, JSON_UNESCAPED_UNICODE);
            }

            $transaction_str_arr[] = $key . '=' . $value;
        }

        $transaction_str = implode('&', $transaction_str_arr);
        $generated = hash_hmac('sha256', $transaction_str, $checksum_key);

        return $signature === $generated;
    }
}
