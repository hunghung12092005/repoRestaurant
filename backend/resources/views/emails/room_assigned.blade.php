<!DOCTYPE html>
<html>
<head>
    <title>Cập nhật xếp phòng cho đặt phòng Mã đơn: {{ $booking->booking_id }}</title>
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
    <h1>Cập nhật xếp phòng cho đặt phòng Mã đơn: {{ $booking->booking_id }}</h1>
    <p>Kính gửi {{ $booking->customer->customer_name ?? 'Quý khách' }},</p>
    <p>Đặt phòng của bạn đã được gán phòng.</p>
    <p>Chi tiết đặt phòng:</p>
    <ul>
        <li>Mã đặt phòng: {{ $booking->booking_id }}</li>
        <li>Ngày nhận phòng: {{ \Carbon\Carbon::parse($booking->check_in_date)->format('d/m/Y') }}</li>
        <li>Ngày trả phòng: {{ \Carbon\Carbon::parse($booking->check_out_date)->format('d/m/Y') }}</li>
        <li>Số phòng: {{ $booking->total_rooms }}</li>
        <li>Loại đơn đặt phòng: {{ $booking->booking_type === 'online' ? 'Trực tuyến' : ($booking->booking_type === 'offline' ? 'Tại quầy' : $booking->booking_type) }}</li>
        <li>Tổng giá: {{ number_format($booking->total_price, 0, ',', '.') }} VND</li>
    </ul>
    @if (!empty($additionalInfo['rooms']))
        <p>Phòng đã gán:</p>
        <ul>
            @foreach ($additionalInfo['rooms'] as $room)
                <li>{{ $room['room_name'] }} (Loại: {{ $room['room_type'] }})</li>
            @endforeach
        </ul>
    @elseif (!empty($additionalInfo['room_name']))
        <p>Phòng đã gán: {{ $additionalInfo['room_name'] }} (Loại: {{ $additionalInfo['room_type'] }})</p>
    @endif
    <p>Chúng tôi rất mong được đón tiếp bạn tại HXH Luxury Hotel!</p>
    <div class="footer">
        <p>Trân trọng,<br>HXH Luxury Hotel</p>
    </div>
</body>
</html>