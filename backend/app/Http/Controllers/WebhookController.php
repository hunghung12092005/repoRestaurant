<?php namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        // Lấy dữ liệu từ webhook
        $data = $request->all();

        // Kiểm tra signature (nếu cần)
        // $signature = $request->header('signature');
        // if (!$this->isValidSignature($data, $signature)) {
        //     return response()->json(['error' => 'Invalid signature'], 401);
        // }

        // Xử lý dữ liệu
       // Log::info('Webhook data received: ', $data);

        // Trả về phản hồi
        return response()->json(['code' => '00', 'desc' => 'success']);
    }

    // Hàm kiểm tra signature (nếu cần)
    // private function isValidSignature($data, $signature)
    // {
    //     // Logic để kiểm tra signature
    // }
}?>