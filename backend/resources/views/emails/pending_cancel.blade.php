<!DOCTYPE html>
<html>
<head>
    <title>Yêu cầu hủy đặt phòng Mã đơn: HXH{{ $booking->booking_id }}</title>
    <style>
        body { font-family: Arial, sans-serif; color: #333; margin: 0; padding: 20px; }
        h1 { color: #2c3e50; text-align: center; }
        p, ul { font-size: 16px; line-height: 1.6; }
        ul { list-style-type: none; padding: 0; }
        li { margin: 10px 0; }
        .logo-container { text-align: center; margin-bottom: 20px; }
        .logo { max-width: 150px; }
        .footer { margin-top: 20px; text-align: center; color: #666; }
    </style>
</head>
<body>
    <div class="logo-container">
        <img src="https://i.postimg.cc/D0jQGLMp/logo-HXH.png" alt="HXH Luxury Hotel" class="logo">
    </div>
    <h1>Yêu cầu hủy đặt phòng Mã đơn: HXH{{ $booking->booking_id }}</h1>
    <p>Kính gửi {{ $booking->customer->customer_name ?? 'Quý khách' }},</p>
    <p>Yêu cầu hủy đặt phòng của bạn đã được gửi và đang chờ xác nhận.</p>
    <p>Chi tiết đặt phòng:</p>
    <ul>
        <li>Mã đặt phòng: {{ $booking->booking_id }}</li>
        <li>Ngày nhận phòng: {{ \Carbon\Carbon::parse($booking->check_in_date)->format('d/m/Y') }}</li>
        <li>Ngày trả phòng: {{ \Carbon\Carbon::parse($booking->check_out_date)->format('d/m/Y') }}</li>
        <li>Số phòng: {{ $booking->total_rooms }}</li>
        <li>Loại đơn đặt phòng: {{ $booking->booking_type === 'online' ? 'Trực tuyến' : ($booking->booking_type === 'offline' ? 'Tại quầy' : $booking->booking_type) }}</li>
        <li>Tổng giá: {{ number_format($booking->total_price, 0, ',', '.') }} VND</li>
    </ul>
    @if (!empty($additionalInfo['cancellation_reason']))
        <p>Lý do hủy: {{ $additionalInfo['cancellation_reason'] }}</p>
    @endif
    @if (!empty($additionalInfo['refund_amount']))
        <p>Số tiền hoàn lại dự kiến: {{ number_format($additionalInfo['refund_amount'], 0, ',', '.') }} VND</p>
    @endif
    <p>Chúng tôi sẽ thông báo cho bạn khi yêu cầu hủy được xử lý.</p>
    <div class="footer">
        <p>Trân trọng,<br>HXH Luxury Hotel</p>
    </div>
</body>
</html>