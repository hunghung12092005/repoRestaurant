<?php namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        // Nhận dữ liệu từ PayOS
        // $data = $request->all();

        // // Ghi log dữ liệu nhận được để kiểm tra
        // Log::info('Dữ liệu webhook:', $data);

        // // Kiểm tra trạng thái chuyển khoản
        // if (isset($data['status'])) {
        //     $status = $data['status'];
        //     // Xử lý trạng thái chuyển khoản
        //     if ($status == 'success') {
        //         // Xử lý khi chuyển khoản thành công
        //         Log::info('Chuyển khoản thành công:', $data);
        //     } else {
        //         // Xử lý khi chuyển khoản thất bại
        //         Log::warning('Chuyển khoản thất bại:', $data);
        //     }
        // }

        return response()->json(['message' => 'Webhook nhận thành công'], 200);
    }

    // Hàm kiểm tra signature (nếu cần)
    // private function isValidSignature($data, $signature)
    // {
    //     // Logic để kiểm tra signature
    // }
}?>