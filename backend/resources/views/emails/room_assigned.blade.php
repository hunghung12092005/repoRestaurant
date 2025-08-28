<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700&family=Lato:wght@400;700&display=swap" rel="stylesheet">
    <title>Cập nhật Xếp phòng</title>
    <style>
        body {
            font-family: 'Lato', Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f8f8;
        }
        h1, h2, h3 {
            font-family: 'Merriweather', serif;
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #f8f8f8; font-family: 'Lato', Arial, sans-serif;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td style="padding: 20px 0;">
                <table align="center" border="0" cellpadding="0" cellspacing="0" width="600" style="border-collapse: collapse; background-color: #ffffff; border-radius: 8px; overflow: hidden; border: 1px solid #e0e0e0;">
                    <tr>
                        <td align="center" style="background-color: #A98C58; padding: 30px 20px;">
                            <h1 style="color: #ffffff; font-size: 28px; margin: 0; font-family: 'Merriweather', serif; font-weight: 700;">Hồ Xuân Hương Hotel</h1>
                            <p style="color: #f6f4f4; font-size: 16px; margin: 5px 0 0; font-family: 'Lato', sans-serif;">Cập nhật về phòng của bạn</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 30px 40px; color: #333333;">
                            <h2 style="font-size: 22px; color: #A98C58; margin-top: 0; font-family: 'Merriweather', serif;">Mã đơn: HXH{{ $booking->booking_id }}</h2>
                            <p style="font-size: 16px; line-height: 1.6;">Kính gửi {{ $booking->customer->customer_name ?? 'Quý khách' }},</p>
                            <p style="font-size: 16px; line-height: 1.6;">Chúng tôi vui mừng thông báo rằng phòng cho đơn đặt của bạn đã được sắp xếp. Dưới đây là thông tin chi tiết:</p>
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 20px 0;">
                                <tr>
                                    <td style="background-color: #e8f5e9; border-left: 5px solid #2e7d32; padding: 20px;">
                                        <p style="margin: 0; font-size: 16px; line-height: 1.6;">
                                            Trạng thái: 
                                            <strong style="color: #2e7d32; font-size: 18px;">Đã xếp phòng</strong>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                            <h3 style="color: #715e3b; border-bottom: 2px solid #eeeeee; padding-bottom: 10px; margin-top: 30px; font-family: 'Merriweather', serif;">Thông tin phòng của bạn</h3>
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="color: #333333; font-size: 16px;">
                                <tr>
                                    <td style="padding: 10px 0; font-weight: bold;">Phòng</td>
                                    <td style="padding: 10px 0; text-align: right; font-weight: bold;">Loại phòng</td>
                                </tr>
                                @if (!empty($additionalInfo['rooms']))
                                    @foreach ($additionalInfo['rooms'] as $room)
                                        <tr>
                                            <td style="padding: 10px 0; border-top: 1px solid #eeeeee;">{{ $room['room_name'] }}</td>
                                            <td style="padding: 10px 0; border-top: 1px solid #eeeeee; text-align: right;">{{ $room['room_type'] }}</td>
                                        </tr>
                                    @endforeach
                                @elseif (!empty($additionalInfo['room_name']))
                                    <tr>
                                        <td style="padding: 10px 0; border-top: 1px solid #eeeeee;">{{ $additionalInfo['room_name'] }}</td>
                                        <td style="padding: 10px 0; border-top: 1px solid #eeeeee; text-align: right;">{{ $additionalInfo['room_type'] }}</td>
                                    </tr>
                                @endif
                            </table>

                            <h3 style="color: #715e3b; border-bottom: 2px solid #eeeeee; padding-bottom: 10px; margin-top: 30px; font-family: 'Merriweather', serif;">Chi tiết đặt phòng</h3>
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="color: #333333; font-size: 16px;">
                                <tr>
                                    <td style="padding: 10px 0; font-weight: bold;">Mã đặt phòng:</td>
                                    <td style="padding: 10px 0; text-align: right;">HXH{{ $booking->booking_id }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px 0; border-top: 1px solid #eeeeee; font-weight: bold;">Ngày nhận phòng:</td>
                                    <td style="padding: 10px 0; border-top: 1px solid #eeeeee; text-align: right;">{{ \Carbon\Carbon::parse($booking->check_in_date)->format('d/m/Y') }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px 0; border-top: 1px solid #eeeeee; font-weight: bold;">Ngày trả phòng:</td>
                                    <td style="padding: 10px 0; border-top: 1px solid #eeeeee; text-align: right;">{{ \Carbon\Carbon::parse($booking->check_out_date)->format('d/m/Y') }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px 0; border-top: 1px solid #eeeeee; font-weight: bold;">Số lượng phòng:</td>
                                    <td style="padding: 10px 0; border-top: 1px solid #eeeeee; text-align: right;">{{ $booking->total_rooms }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px 0; border-top: 1px solid #eeeeee; font-weight: bold;">Loại đặt phòng:</td>
                                    <td style="padding: 10px 0; border-top: 1px solid #eeeeee; text-align: right;">{{ $booking->booking_type === 'online' ? 'Trực tuyến' : ($booking->booking_type === 'offline' ? 'Tại quầy' : $booking->booking_type) }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 15px 0; border-top: 1px solid #eeeeee; font-weight: bold; font-size: 18px; color: #000">Tổng giá:</td>
                                    <td style="padding: 15px 0; border-top: 1px solid #eeeeee; text-align: right; font-weight: bold; color: rgb(228, 28, 28); font-size: 18px;">{{ number_format($booking->total_price, 0, ',', '.') }} VND</td>
                                </tr>
                            </table>

                            <p style="font-size: 16px; line-height: 1.6; margin-top: 30px;">Mọi thứ đã sẵn sàng cho kỳ nghỉ của bạn. Chúng tôi rất mong được đón tiếp bạn tại Khách sạn Hồ Xuân Hương!</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color: #f8f8f8; padding: 20px 40px; text-align: center;">
                            <p style="margin: 0; color: #888888; font-size: 12px;">© 2025 Hồ Xuân Hương Hotel. All Rights Reserved.</p>
                            <p style="margin: 5px 0 0; color: #888888; font-size: 12px;">Đây là email tự động, vui lòng không trả lời.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>