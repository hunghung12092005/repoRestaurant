<?php

namespace App\Mail;

use App\Models\BookingHotel;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingStatusUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;
    public $status;
    public $additionalInfo;

    /**
     * Create a new message instance.
     *
     * @param BookingHotel $booking
     * @param string $status
     * @param array $additionalInfo
     * @return void
     */

    public function __construct(BookingHotel $booking, string $status, array $additionalInfo = [])
    {
        $this->booking = $booking;
        $this->status = $status;
        $this->additionalInfo = $additionalInfo;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = '';
        $template = '';

        switch ($this->status) {
            case 'confirmed_not_assigned':
                $subject = 'Xác Nhận Đặt Phòng Mã Đơn: HXH' . $this->booking->booking_id;
                $template = 'emails.booking_confirmed';
                break;
            case 'confirmed':
                $subject = 'Hoàn Tất Xác Nhận Đặt Phòng Mã Đơn: HXH' . $this->booking->booking_id;
                $template = 'emails.booking_completed';
                break;
            case 'cancelled':
                $subject = 'Hủy Đặt Phòng Mã Đơn: HXH' . $this->booking->booking_id;
                $template = 'emails.booking_cancelled';
                break;
            case 'room_assigned':
                $subject = 'Cập Nhật Xếp Phòng Cho Đặt Phòng Mã Đơn: HXH' . $this->booking->booking_id;
                $template = 'emails.room_assigned';
                break;
            default:
                $subject = 'Cập Nhật Trạng Thái Đặt Phòng Mã Đơn: HXH' . $this->booking->booking_id;
                $template = 'emails.booking_status_updated';
        }

        return $this->subject($subject)
                    ->view($template)
                    ->with([
                        'booking' => $this->booking,
                        'status' => $this->status,
                        'additionalInfo' => $this->additionalInfo,
                    ]);
    }
}