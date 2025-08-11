<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Contact;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactReplyMail;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Notification;
use App\Events\NewNotification;
use Illuminate\Support\Str;

class ContactController extends Controller
{
    /**
     * Lấy danh sách tất cả liên hệ (cho admin).
     */
    public function index()
    {
        // Sắp xếp theo ngày tạo mới nhất
        $contacts = Contact::orderBy('created_at', 'desc')->get();
        return response()->json([
            'status' => true,
            'data' => $contacts
        ]);
    }

    /**
     * Lưu một liên hệ mới từ form public.
     */

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|regex:/^([0-9\s\-\+\(\)]*)$/|min:10|max:15',
            'message' => 'required|string',
        ], [
            'phone.regex' => 'Số điện thoại không hợp lệ.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $contact = Contact::create($request->all());

        // ===== BẮT ĐẦU PHẦN THÊM MỚI =====
        // Gửi thông báo real-time tới admin/staff
        $adminsAndStaff = User::whereIn('role', ['admin', 'staff'])->get();

        foreach ($adminsAndStaff as $user) {
            // 1. Tạo bản ghi Notification trong database
            $notification = new Notification(); // Sử dụng \App\Models\Notification đầy đủ hoặc use ở trên
            $notification->id = Str::uuid();
            $notification->type = 'NEW_CONTACT';
            $notification->notifiable_type = User::class;
            $notification->notifiable_id = $user->id;
            $notification->subject_type = Contact::class;
            $notification->subject_id = $contact->id;
            $notification->data = [
                'message' => "Bạn có một liên hệ mới từ {$contact->name}.",
                'link' => '/admin/contacts'
            ];
            $notification->save();

            // 2. Phát sóng sự kiện tới frontend
            broadcast(new NewNotification($notification))->toOthers();
        }
        // ===== KẾT THÚC PHẦN THÊM MỚI =====


        return response()->json([
            'status' => true,
            'message' => 'Cảm ơn bạn đã liên hệ! Chúng tôi sẽ phản hồi sớm nhất có thể.',
            'data' => $contact
        ], 201);
    }

    /**
     * Xóa một liên hệ (cho admin).
     */
    public function destroy(Contact $contact)
    {
        $contact->delete();

        return response()->json([
            'status' => true,
            'message' => 'Đã xóa liên hệ thành công.'
        ], 200);
    }

    /**
     * Gửi email phản hồi cho một liên hệ.
     */
    public function reply(Request $request, Contact $contact)
    {
        $validator = Validator::make($request->all(), [
            'reply_message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $replyMessage = $request->input('reply_message');

        try {
            // Gửi email đến người dùng
            Mail::to($contact->email)->send(new ContactReplyMail($contact, $replyMessage));

            // Cập nhật trạng thái của liên hệ
            $contact->status = 'replied';
            $contact->save();

            return response()->json([
                'status' => true,
                'message' => 'Đã gửi phản hồi thành công!'
            ]);
        } catch (\Exception $e) {
            // Ghi lại lỗi nếu có
            Log::error('Lỗi gửi email: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Gửi email thất bại. Vui lòng kiểm tra lại cấu hình.'
            ], 500);
        }
    }

    /**
     * Lấy các liên hệ mới hơn một ID cụ thể.
     * Dùng cho việc polling từ phía admin.
     */
    public function fetchNew(Request $request)
    {
        $lastId = $request->query('lastContactId', 0);

        $newContacts = Contact::where('id', '>', $lastId)
            ->orderBy('id', 'asc') // Sắp xếp theo ID tăng dần để frontend dễ xử lý
            ->get();

        return response()->json([
            'status' => true,
            'data' => $newContacts
        ]);
    }
}
