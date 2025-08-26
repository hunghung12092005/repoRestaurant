<template>
  <div class="dashboard-container">
    <div v-if="isLoading" class="loading-overlay">
      <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status"></div>
      <p class="mt-3 text-muted">Đang tải dữ liệu tổng quan...</p>
    </div>

    <div v-show="!isLoading" class="fade-in">
      <!-- 0. BỘ LỌC VÀ TIÊU ĐỀ -->
      <div class="dashboard-header">
        <div>
          <h1 class="h3 mb-0 text-gray-800">Tổng quan hệ thống</h1>
          <p class="mb-0 text-muted" v-if="displayMonth">
            Hiển thị dữ liệu cho <span class="fw-bold text-primary">{{ displayMonth }}</span>
          </p>
        </div>
        <div class="filter-group">
          <label for="month-picker" class="filter-label">Chọn tháng:</label>
          <!-- GIAO DIỆN CHỌN THÁNG -->
          <div class="custom-month-picker" @click="openMonthPicker">
            <span class="form-control form-control-sm custom-month-display">
              {{ formattedPickerDisplay }}
            </span>
            <i class="fas fa-calendar-alt month-picker-icon"></i>
            <input type="month" ref="monthPickerInput" id="month-picker" v-model="filterMonth" class="hidden-month-input">
          </div>
          <!-- Nút "Tra cứu" có thể được giữ lại hoặc xóa đi vì giờ đã tự động -->
          <button class="btn btn-sm btn-primary btn-filter" @click="handleFilter" title="Dữ liệu được tự động tra cứu khi bạn đổi tháng">
            <i class="fas fa-search me-1"></i> Tra cứu
          </button>
          <button class="btn btn-sm btn-danger btn-export" @click="handleExportPdf" :disabled="isExporting">
            <i :class="isExporting ? 'fas fa-spinner fa-spin' : 'fas fa-file-pdf'"></i>
            <span v-if="!isExporting"> Xuất PDF</span>
          </button>
          <button class="btn btn-sm btn-outline-secondary btn-reset" @click="handleReset" title="Về tháng hiện tại">
            <i class="fas fa-sync-alt"></i>
          </button>
        </div>
      </div>
      
      <!-- 1. KPI WIDGETS (4 THẺ) -->
      <div class="row g-4">
        <div class="col-xl-3 col-md-6">
          <div class="kpi-card h-100 card-revenue">
            <div class="kpi-icon"><i class="fas fa-wallet"></i></div>
            <div class="kpi-content"><div class="kpi-label">Tổng Doanh Thu</div><div class="kpi-value">{{ formatCurrency(overviewData.kpis?.totalRevenue) }}</div></div>
          </div>
        </div>
        <div class="col-xl-3 col-md-6">
          <div class="kpi-card h-100 card-occupancy">
            <div class="kpi-icon"><i class="fas fa-chart-pie"></i></div>
            <div class="kpi-content"><div class="kpi-label">Tỷ Lệ Lấp Đầy</div><div class="kpi-value">{{ formatRating(overviewData.kpis?.occupancyRate) }}%</div></div>
          </div>
        </div>
        <div class="col-xl-3 col-md-6">
          <div class="kpi-card h-100 card-rating">
            <div class="kpi-icon"><i class="fas fa-star"></i></div>
            <div class="kpi-content">
                <div class="kpi-label">Đánh giá TB</div>
                <div class="kpi-value">{{ formatRating(overviewData.reviewStats?.averageRating) }}</div>
            </div>
          </div>
        </div>
        <div class="col-xl-3 col-md-6">
          <div class="kpi-card h-100 card-adr">
            <div class="kpi-icon"><i class="fas fa-dollar-sign"></i></div>
            <div class="kpi-content"><div class="kpi-label">Giá TB (ADR)</div><div class="kpi-value">{{ formatCurrency(overviewData.kpis?.adr) }}</div></div>
          </div>
        </div>
      </div>

      <!-- 2. MAIN CHART -->
      <div class="row mt-4">
        <div class="col-12">
           <div class="card shadow-sm">
            <div class="card-header py-3">
              <h6 class="m-0 font-weight-bold text-primary">Xu Hướng Kinh Doanh (12 Tháng Qua)</h6>
            </div>
            <div class="card-body">
              <div class="chart-container" style="height: 350px;">
                <canvas ref="revenueTrendChartRef"></canvas>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- 3. SUPPORTING CHARTS & STATS -->
      <div class="row mt-4 g-4">
        <div class="col-lg-6">
          <div class="card shadow-sm h-100">
            <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Doanh Thu Theo Loại Phòng</h6></div>
            <div class="card-body"><div class="chart-container" style="height: 100%; min-height: 300px;"><canvas ref="revenueByRoomTypeChartRef"></canvas></div></div>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="card shadow-sm h-100">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Phân Bố Đánh Giá ({{ overviewData.reviewStats?.totalReviews || 0 }} lượt)</h6>
            </div>
            <div class="card-body"><div class="chart-container" style="height: 100%; min-height: 300px;"><canvas ref="reviewChartRef"></canvas></div></div>
          </div>
        </div>
      </div>
      <div class="row mt-4 g-4">
         <div class="col-lg-6">
           <div class="card shadow-sm h-100">
            <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Thống Kê Phụ</h6></div>
            <div class="card-body d-flex flex-column justify-content-center">
              <div class="row g-3">
                <div class="col-12"><div class="stat-card-mini border-left-primary"><div class="stat-card-mini-icon bg-primary"><i class="fas fa-calendar-plus"></i></div><div class="stat-card-mini-content"><div class="stat-card-mini-label">Lượt đặt mới</div><div class="stat-card-mini-value">{{ overviewData.kpis?.bookingsCount || 0 }}</div></div></div></div>
                <div class="col-12"><div class="stat-card-mini border-left-danger"><div class="stat-card-mini-icon bg-danger"><i class="fas fa-calendar-times"></i></div><div class="stat-card-mini-content"><div class="stat-card-mini-label">Lượt hủy</div><div class="stat-card-mini-value">{{ overviewData.kpis?.cancellationsCount || 0 }}</div></div></div></div>
                <div class="col-12"><div class="stat-card-mini border-left-info"><div class="stat-card-mini-icon bg-info"><i class="fas fa-bed"></i></div><div class="stat-card-mini-content"><div class="stat-card-mini-label">Tổng số phòng</div><div class="stat-card-mini-value">{{ overviewData.kpis?.totalRooms || 0 }}</div></div></div></div>
                <div class="col-12"><div class="stat-card-mini border-left-warning"><div class="stat-card-mini-icon bg-warning"><i class="fas fa-hotel"></i></div><div class="stat-card-mini-content"><div class="stat-card-mini-label">Tổng loại phòng</div><div class="stat-card-mini-value">{{ overviewData.kpis?.totalRoomTypes || 0 }}</div></div></div></div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Hoạt Động Hôm Nay</h6></div>
                <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                    <ul v-if="overviewData.todaysActivity && (overviewData.todaysActivity.checkIns.length > 0 || overviewData.todaysActivity.checkOuts.length > 0)" class="activity-list">
                        <li v-for="booking in overviewData.todaysActivity.checkIns" :key="'in-'+booking.booking_id"><span class="activity-icon check-in"><i class="fas fa-arrow-down"></i></span><div class="activity-content"><strong>{{ booking.customer?.customer_name }}</strong> đã check-in<small class="text-muted d-block">Lúc {{ booking.check_in_time }}</small></div></li>
                        <li v-for="booking in overviewData.todaysActivity.checkOuts" :key="'out-'+booking.booking_id"><span class="activity-icon check-out"><i class="fas fa-arrow-up"></i></span><div class="activity-content"><strong>{{ booking.customer?.customer_name }}</strong> đã check-out<small class="text-muted d-block">Lúc {{ booking.check_out_time }}</small></div></li>
                    </ul>
                    <div v-else class="empty-state"><i class="fas fa-calendar-check fa-2x text-gray-300 mb-2"></i><span>Không có hoạt động.</span></div>
                </div>
            </div>
        </div>
      </div>
      <div class="row mt-4 g-4">
        <div class="col-lg-6">
          <div class="card shadow-sm h-100">
            <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Tin tức mới nhất</h6></div>
            <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
               <ul v-if="overviewData.latestNews && overviewData.latestNews.length > 0" class="dashboard-list">
                <li v-for="news in overviewData.latestNews" :key="news.id" class="dashboard-list-item"><div class="list-item-content"><div class="item-title">{{ news.title }}</div><div class="item-meta">Bởi <b>{{ news.author?.name || 'N/A' }}</b> - {{ formatDate(news.publish_date) }}</div></div></li>
              </ul>
              <div v-else class="empty-state"><i class="fas fa-newspaper fa-2x text-gray-300 mb-2"></i><span>Không có tin tức mới.</span></div>
            </div>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="card shadow-sm h-100">
            <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Bình luận mới nhất</h6></div>
            <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
               <ul v-if="overviewData.latestComments && overviewData.latestComments.length > 0" class="dashboard-list">
                <li v-for="comment in overviewData.latestComments" :key="comment.id" class="dashboard-list-item"><div class="list-item-content"><div class="item-user"><b>{{ comment.user?.name || 'N/A' }}</b>: <i>"{{ comment.content.substring(0, 80) }}..."</i></div><div class="item-meta">{{ formatTimeAgo(comment.created_at) }}</div></div></li>
              </ul>
              <div v-else class="empty-state"><i class="fas fa-comments fa-2x text-gray-300 mb-2"></i><span>Chưa có bình luận nào.</span></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, inject, computed, watch } from 'vue'; // Thêm 'watch'
import axios from 'axios';
import Chart from 'chart.js/auto';

// State
const isLoading = ref(true);
const isExporting = ref(false);
const overviewData = ref({});
const filterMonth = ref('');
const monthPickerInput = ref(null);

// Chart Refs & Instances
const revenueTrendChartRef = ref(null);
const revenueByRoomTypeChartRef = ref(null);
const reviewChartRef = ref(null);
let revenueTrendChartInstance = null;
let revenueByRoomTypeChartInstance = null;
let reviewChartInstance = null;

// API setup
const apiUrl = inject('apiUrl');
const axiosInstance = axios.create({ baseURL: apiUrl });
axiosInstance.interceptors.request.use(config => {
  const token = localStorage.getItem('tokenJwt');
  if (token) config.headers.Authorization = `Bearer ${token}`;
  return config;
});

// Computed Properties
const displayMonth = computed(() => {
  if (!filterMonth.value) return '';
  const [year, month] = filterMonth.value.split('-');
  return `Tháng ${month}/${year}`;
});
const formattedPickerDisplay = computed(() => {
  if (!filterMonth.value) return 'Chọn tháng...';
  const [year, month] = filterMonth.value.split('-');
  return `Tháng ${month} năm ${year}`;
});

// === THÊM WATCH ĐỂ TỰ ĐỘNG TRA CỨU ===
watch(filterMonth, (newMonth, oldMonth) => {
  // Điều kiện này ngăn việc gọi API 2 lần khi trang vừa tải
  // Chỉ gọi khi người dùng thực sự thay đổi tháng
  if (oldMonth && newMonth !== oldMonth) {
    fetchOverviewData();
  }
});

// Helper Functions
const formatCurrency = (value) => value == null ? '0 ₫' : new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
const formatDate = (dateString) => dateString ? new Date(dateString).toLocaleDateString('vi-VN', {day: '2-digit', month: '2-digit', year: 'numeric'}) : 'N/A';
const formatTimeAgo = (dateString) => {
  if (!dateString) return 'N/A';
  const seconds = Math.floor((new Date() - new Date(dateString)) / 1000);
  let interval = seconds / 31536000; if (interval > 1) return Math.floor(interval) + " năm trước";
  interval = seconds / 2592000; if (interval > 1) return Math.floor(interval) + " tháng trước";
  interval = seconds / 86400; if (interval > 1) return Math.floor(interval) + " ngày trước";
  interval = seconds / 3600; if (interval > 1) return Math.floor(interval) + " giờ trước";
  interval = seconds / 60; if (interval > 1) return Math.floor(interval) + " phút trước";
  return "vài giây trước";
};
const toYYYYMM = (date) => {
    const year = date.getFullYear();
    const month = (date.getMonth() + 1).toString().padStart(2, '0');
    return `${year}-${month}`;
};
const formatRating = (rating) => {
    const num = parseFloat(rating);
    if (!isNaN(num)) {
        return num.toFixed(1);
    }
    return 'N/A';
};

// Chart Creation Functions
const createOrUpdateChart = (instance, ref, config) => {
    if (!ref.value) return null;
    if (instance) {
        instance.data = config.data;
        instance.options = config.options;
        instance.update();
        return instance;
    }
    return new Chart(ref.value.getContext('2d'), config);
};
const createRevenueTrendChart = (chartData) => {
  if (!chartData) return;
  revenueTrendChartInstance = createOrUpdateChart(revenueTrendChartInstance, revenueTrendChartRef, { type: 'bar', data: { labels: chartData.labels, datasets: [ { label: 'Doanh thu', data: chartData.revenueData, backgroundColor: 'rgba(78, 115, 223, 0.8)', yAxisID: 'yRevenue', order: 2 }, { label: 'Tỷ lệ lấp đầy (%)', data: chartData.occupancyData, borderColor: 'rgba(28, 200, 138, 1)', backgroundColor: 'rgba(28, 200, 138, 0.1)', type: 'line', fill: true, tension: 0.3, yAxisID: 'yOccupancy', order: 1 } ] }, options: { responsive: true, maintainAspectRatio: false, scales: { yRevenue: { type: 'linear', position: 'left', beginAtZero: true, title: { display: true, text: 'Doanh thu (VND)' }, ticks: { callback: (value) => new Intl.NumberFormat('vi-VN').format(value) } }, yOccupancy: { type: 'linear', position: 'right', min: 0, max: 100, grid: { drawOnChartArea: false }, title: { display: true, text: 'Tỷ lệ lấp đầy (%)' }, ticks: { callback: (value) => value + '%' } } } } });
};
const createRevenueByRoomTypeChart = (chartData) => {
  if (!chartData) return;
  revenueByRoomTypeChartInstance = createOrUpdateChart(revenueByRoomTypeChartInstance, revenueByRoomTypeChartRef, { type: 'bar', data: { labels: chartData.labels, datasets: [{ label: 'Doanh thu', data: chartData.data, backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c2e', '#e74a3b', '#6f42c1', '#fd7e14'], }] }, options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { ticks: { callback: (value) => formatCurrency(value) } } } } });
};
const createReviewChart = (chartData) => {
  if (!chartData) return;
  reviewChartInstance = createOrUpdateChart(reviewChartInstance, reviewChartRef, { type: 'bar', data: { labels: chartData.labels, datasets: [{ label: 'Số lượt', data: chartData.data, backgroundColor: ['#28a745', '#a3d16b', '#ffc107', '#fd7e14', '#dc3545'], borderColor: '#fff', borderWidth: 2, borderRadius: 5 }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1, callback: function(value) {if (Number.isInteger(value)) {return value;}} } } } } });
};

// Data Fetching
const fetchOverviewData = async () => {
  if (!filterMonth.value) { alert("Vui lòng chọn tháng để tra cứu."); return; }
  isLoading.value = true;
  try {
    const [year, month] = filterMonth.value.split('-');
    const startDate = new Date(year, month - 1, 1);
    const endDate = new Date(year, month, 0);
    const params = {
        startDate: startDate.toISOString().split('T')[0],
        endDate: endDate.toISOString().split('T')[0],
    };
    const response = await axiosInstance.get('/api/admin/dashboard/overview', { params });
    overviewData.value = response.data || {};
    createRevenueTrendChart(overviewData.value.revenueAndOccupancyTrend);
    createRevenueByRoomTypeChart(overviewData.value.revenueByRoomTypeChart);
    createReviewChart(overviewData.value.reviewStats.distributionChart);
  } catch (error) {
    console.error("Lỗi khi lấy dữ liệu tổng quan:", error.response || error);
    overviewData.value = {};
    alert('Không thể tải dữ liệu tổng quan: ' + (error.response?.data?.message || error.message));
  } finally {
    isLoading.value = false;
  }
};

// Event Handlers
const handleFilter = () => { fetchOverviewData(); };
const handleReset = () => {
    const currentMonth = toYYYYMM(new Date());
    // Nếu tháng hiện tại khác với tháng đang chọn thì gán lại (sẽ tự động trigger watch)
    // Nếu giống thì gọi fetch trực tiếp
    if (filterMonth.value !== currentMonth) {
        filterMonth.value = currentMonth;
    } else {
        fetchOverviewData();
    }
};

const handleExportPdf = async () => {
  if (!filterMonth.value) {
    alert("Vui lòng chọn tháng để xuất báo cáo.");
    return;
  }
  isExporting.value = true;
  try {
    const [year, month] = filterMonth.value.split('-');
    const startDate = new Date(year, month - 1, 1);
    const endDate = new Date(year, month, 0);
    const params = {
      startDate: startDate.toISOString().split('T')[0],
      endDate: endDate.toISOString().split('T')[0],
    };
    const response = await axiosInstance.get('/api/admin/dashboard/export-pdf', {
      params,
      responseType: 'blob',
    });
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    const fileName = `bao-cao-thang-${month}-${year}.pdf`;
    link.setAttribute('download', fileName);
    document.body.appendChild(link);
    link.click();
    link.parentNode.removeChild(link);
    window.URL.revokeObjectURL(url);
  } catch (error) {
    console.error("Lỗi khi xuất PDF:", error);
    alert('Không thể xuất báo cáo PDF. Vui lòng thử lại.');
  } finally {
    isExporting.value = false;
  }
};

const openMonthPicker = () => {
  monthPickerInput.value?.showPicker();
};

// Lifecycle Hooks
onMounted(() => {
    filterMonth.value = toYYYYMM(new Date());
    fetchOverviewData(); // Gọi lần đầu khi component được mount
});
onBeforeUnmount(() => {
  if (revenueTrendChartInstance) revenueTrendChartInstance.destroy();
  if (revenueByRoomTypeChartInstance) revenueByRoomTypeChartInstance.destroy();
  if (reviewChartInstance) reviewChartInstance.destroy();
});
</script>

<style scoped>
/* GENERAL STYLES */
.dashboard-container { background-color: #f8f9fc; }
.text-gray-800 { color: #5a5c69 !important; }
.fade-in { animation: fadeIn 0.5s ease-in-out; }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
.loading-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(255, 255, 255, 0.9); display: flex; flex-direction: column; justify-content: center; align-items: center; z-index: 1050; }
.chart-container { position: relative; width: 100%; }
.text-primary { color: #4e73df !important; }
.bg-primary { background-color: #4e73df !important; }
.bg-danger { background-color: #e74a3b !important; }
.bg-info { background-color: #36b9cc !important; }
.bg-warning { background-color: #f6c23e !important; }
.fw-bold { font-weight: 700 !important; }
.me-1 { margin-right: 0.25rem !important; }

/* HEADER & FILTER GROUP */
.dashboard-header {
  display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;
  gap: 1rem; margin-bottom: 2rem; padding: 1rem; background-color: #fff;
  border-radius: 0.5rem; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
}
.filter-group {
  display: flex; align-items: center; gap: 0.75rem; background-color: #f8f9fc;
  padding: 0.5rem 1rem; border-radius: 0.375rem;
}
.filter-label { margin-bottom: 0; font-size: 0.875rem; font-weight: 500; color: #5a5c69; }
.btn-filter, .btn-reset, .btn-export { transition: all 0.2s ease; }
.btn-filter:hover, .btn-export:hover { transform: scale(1.05); }
.btn-export {
  width: 110px;
  text-align: center;
}

/* CUSTOM MONTH PICKER STYLES */
.custom-month-picker {
  position: relative;
  width: 185px; 
  cursor: pointer;
}
.custom-month-display {
  display: block;
  width: 100%;
  user-select: none;
  padding-right: 30px;
  text-align: left;
  white-space: nowrap; 
  overflow: hidden; 
  text-overflow: ellipsis; 
}
.month-picker-icon {
  position: absolute;
  top: 50%;
  right: 12px;
  transform: translateY(-50%);
  color: #6c757d;
  pointer-events: none;
}
.hidden-month-input {
  position: absolute;
  opacity: 0;
  pointer-events: none;
  width: 1px;
  height: 1px;
}

/* KPI CARDS */
.kpi-card {
  display: flex; align-items: center; padding: 20px; border-radius: 8px; color: white;
  position: relative; overflow: hidden; border: none;
  transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}
.kpi-card:hover { transform: translateY(-5px); box-shadow: 0 0.5rem 1rem rgba(0,0,0,.15); }
.kpi-card::before { content: ''; position: absolute; top: -50px; right: -50px; width: 150px; height: 150px; background-color: rgba(255,255,255,0.1); border-radius: 50%; opacity: 0.5; }
.kpi-card.card-revenue { background: linear-gradient(45deg, #4e73df, #224abe); }
.kpi-card.card-occupancy { background: linear-gradient(45deg, #1cc88a, #13855c); }
.kpi-card.card-rating { background: linear-gradient(45deg, #ffc107, #ff9800); }
.kpi-card.card-adr { background: linear-gradient(45deg, #6f42c1, #5a2b9d); }
.kpi-icon { font-size: 2rem; margin-right: 20px; opacity: 0.7; }
.kpi-label { font-size: 0.9rem; text-transform: uppercase; font-weight: 500; margin-bottom: 5px; }
.kpi-value { font-size: 1.75rem; font-weight: 700; }

/* MINI STAT CARDS */
.stat-card-mini {
  background-color: #fff; border-radius: 0.5rem; padding: 1rem; display: flex; align-items: center;
  box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
  transition: transform 0.2s ease-in-out; height: 100%;
}
.stat-card-mini:hover { transform: translateY(-3px); }
.border-left-primary { border-left: 0.25rem solid #4e73df !important; }
.border-left-danger { border-left: 0.25rem solid #e74a3b !important; }
.border-left-info { border-left: 0.25rem solid #36b9cc !important; }
.border-left-warning { border-left: 0.25rem solid #f6c23e !important; }
.stat-card-mini-icon { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1.2rem; margin-right: 1rem; }
.stat-card-mini-content { display: flex; flex-direction: column; }
.stat-card-mini-label { font-size: 0.8rem; color: #858796; text-transform: uppercase; font-weight: 700; }
.stat-card-mini-value { font-size: 1.25rem; font-weight: 700; color: #5a5c69; }

/* LISTS */
.empty-state { display: flex; flex-direction: column; justify-content: center; align-items: center; height: 100%; min-height: 150px; color: #858796; text-align: center; padding: 1rem; }
.dashboard-list { list-style: none; padding: 0; margin: 0; }
.dashboard-list-item { padding: 0.75rem 1.25rem; border-bottom: 1px solid #e3e6f0; }
.dashboard-list-item:last-child { border-bottom: none; }
.item-title { font-weight: 600; color: #5a5c69; margin-bottom: 0.2rem; }
.item-user { font-size: 0.9rem; color: #5a5c69; }
.item-meta { font-size: 0.75rem; color: #858796; }

/* ACTIVITY LIST */
.activity-list { list-style: none; padding: 0.5rem 0; margin: 0; }
.activity-list li { display: flex; align-items: center; padding: 0.75rem 1.25rem; }
.activity-icon { width: 35px; height: 35px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; color: #fff; margin-right: 1rem; flex-shrink: 0; }
.activity-icon.check-in { background-color: #1cc88a; }
.activity-icon.check-out { background-color: #e74a3b; }
.activity-content { font-size: 0.9rem; }
.activity-content small { font-size: 0.8rem; }
</style>