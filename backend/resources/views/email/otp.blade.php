<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Mã Xác Thực Đặt Lại Mật Khẩu</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .container {
            background-color: #f4f4f4;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
        }
        .otp-code {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
            letter-spacing: 5px;
            margin: 20px 0;
            padding: 10px;
            background-color: #fff;
            border: 2px dashed #007bff;
            border-radius: 5px;
        }
        .note {
            color: #6c757d;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Đặt Lại Mật Khẩu</h2>
        <p>Chúng tôi nhận được yêu cầu đặt lại mật khẩu từ tài khoản của bạn.</p>
        
        <div class="otp-code">
            {{ $otp }}
        </div>
        
        <p>Mã xác thực này sẽ hết hạn sau 15 phút.</p>
        
        <p class="note">
            Nếu bạn không yêu cầu đặt lại mật khẩu, vui lòng bỏ qua email này.
        </p>
    </div>
</body>
</html>