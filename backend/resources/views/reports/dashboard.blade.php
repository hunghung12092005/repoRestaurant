<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Báo cáo tổng quan</title>
    <style>
        /* Đảm bảo font hỗ trợ tiếng Việt */
        body { 
            font-family: 'DejaVu Sans', sans-serif; 
            font-size: 12px;
            color: #333;
        }
        .container {
            width: 100%;
            margin: 0 auto;
            padding: 10px;
        }
        .header {
            text-align: center;
            margin-bottom: 25px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0;
            font-size: 14px;
        }
        .section {
            margin-bottom: 25px;
        }
        .section-title {
            font-size: 18px;
            font-weight: bold;
            border-bottom: 2px solid #4e73df;
            padding-bottom: 5px;
            margin-bottom: 15px;
            color: #4e73df;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .kpi-grid {
            width: 100%;
            display: table;
            margin-bottom: 20px;
        }
        .kpi-item {
            display: table-cell;
            width: 25%;
            text-align: center;
        }
        .kpi-label {
            font-size: 13px;
            color: #555;
            margin-bottom: 5px;
        }
        .kpi-value {
            font-size: 20px;
            font-weight: bold;
            color: #000;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>BÁO CÁO TỔNG QUAN HỆ THỐNG</h1>
            <!-- === THAY ĐỔI CÁCH HIỂN THỊ NGÀY THÁNG TẠI ĐÂY === -->
            <p>
                Báo cáo dữ liệu từ ngày {{ \Carbon\Carbon::parse($data['filterRange']['start'])->format('d/m/Y') }}
                đến ngày {{ \Carbon\Carbon::parse($data['filterRange']['end'])->format('d/m/Y') }}
            </p>
        </div>

        <div class="section">
            <div class="section-title">Các chỉ số chính (KPIs)</div>
            <div class="kpi-grid">
                <div class="kpi-item">
                    <div class="kpi-label">Tổng Doanh Thu</div>
                    <div class="kpi-value">{{ number_format($data['kpis']['totalRevenue'] ?? 0, 0, ',', '.') }} ₫</div>
                </div>
                <div class="kpi-item">
                    <div class="kpi-label">Tỷ Lệ Lấp Đầy</div>
                    <div class="kpi-value">{{ number_format($data['kpis']['occupancyRate'] ?? 0, 1) }}%</div>
                </div>
                <div class="kpi-item">
                    <div class="kpi-label">Giá TB (ADR)</div>
                    <div class="kpi-value">{{ number_format($data['kpis']['adr'] ?? 0, 0, ',', '.') }} ₫</div>
                </div>
                <div class="kpi-item">
                    <div class="kpi-label">Đánh giá TB</div>
                    <div class="kpi-value">{{ number_format($data['reviewStats']['averageRating'] ?? 0, 1) }} / 5</div>
                </div>
            </div>
        </div>
        
        <div class="section">
            <div class="section-title">Thống Kê Chi Tiết</div>
            <table>
                <tr>
                    <td style="width: 50%;">Lượt đặt mới</td>
                    <td>{{ $data['kpis']['bookingsCount'] ?? 0 }}</td>
                </tr>
                <tr>
                    <td>Lượt hủy</td>
                    <td>{{ $data['kpis']['cancellationsCount'] ?? 0 }}</td>
                </tr>
                <tr>
                    <td>Tổng số phòng</td>
                    <td>{{ $data['kpis']['totalRooms'] ?? 0 }}</td>
                </tr>
                 <tr>
                    <td>Tổng loại phòng</td>
                    <td>{{ $data['kpis']['totalRoomTypes'] ?? 0 }}</td>
                </tr>
                <tr>
                    <td>Tổng lượt đánh giá</td>
                    <td>{{ $data['reviewStats']['totalReviews'] ?? 0 }}</td>
                </tr>
            </table>
        </div>

        <div class="section">
            <div class="section-title">Doanh Thu Theo Loại Phòng</div>
            <table>
                <thead>
                    <tr>
                        <th>Loại Phòng</th>
                        <th>Doanh Thu</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data['revenueByRoomTypeChart']['labels'] as $index => $label)
                    <tr>
                        <td>{{ $label }}</td>
                        <td>{{ number_format($data['revenueByRoomTypeChart']['data'][$index], 0, ',', '.') }} ₫</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="2" style="text-align: center;">Không có dữ liệu.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="section">
            <div class="section-title">Phân Bố Đánh Giá</div>
            <table>
                <thead>
                    <tr>
                        <th>Mức Đánh Giá</th>
                        <th>Số Lượt</th>
                    </tr>
                </thead>
                <tbody>
                     @forelse($data['reviewStats']['distributionChart']['labels'] as $index => $label)
                    <tr>
                        <td>{{ $label }}</td>
                        <td>{{ $data['reviewStats']['distributionChart']['data'][$index] }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="2" style="text-align: center;">Không có dữ liệu.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</body>
</html>