<template>
  <div class="position-relative">
    <!-- Loading Overlay -->
    <div v-if="isLoading" class="loading-overlay">
      <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status"></div>
      <p class="mt-3 text-muted">Đang tải dữ liệu tổng quan...</p>
    </div>

    <div v-show="!isLoading">
      <!-- 1. STATS WIDGETS (5 WIDGETS) -->
      <div class="row row-cols-1 row-cols-md-3 row-cols-xl-5 g-4">
        <!-- Revenue This Month -->
        <div class="col">
          <div class="card h-100 border-left-success shadow-sm py-2">
            <div class="card-body">
              <div class="row no-gutters align-items-center">
                <div class="col mr-2">
                  <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Doanh thu (Tháng)</div>
                  <div class="stat-number">{{ formatCurrency(overviewData.statWidgets?.revenueThisMonth?.value) }}</div>
                </div>
                <div class="col-auto"><i class="fas fa-dollar-sign fa-2x text-gray-300"></i></div>
              </div>
            </div>
          </div>
        </div>
        <!-- Bookings This Month -->
        <div class="col">
          <div class="card h-100 border-left-primary shadow-sm py-2">
            <div class="card-body">
              <div class="row no-gutters align-items-center">
                <div class="col mr-2">
                  <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Lượt đặt (Tháng)</div>
                  <div class="stat-number">{{ overviewData.statWidgets?.bookingsThisMonth?.value || 0 }} lượt</div>
                </div>
                <div class="col-auto"><i class="fas fa-calendar-check fa-2x text-gray-300"></i></div>
              </div>
            </div>
          </div>
        </div>
        <!-- Cancellations This Month -->
        <div class="col">
          <div class="card h-100 border-left-danger shadow-sm py-2">
            <div class="card-body">
              <div class="row no-gutters align-items-center">
                <div class="col mr-2">
                  <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Lượt hủy (Tháng)</div>
                  <div class="stat-number">{{ overviewData.statWidgets?.cancellationsThisMonth?.value || 0 }} lượt</div>
                </div>
                <div class="col-auto"><i class="fas fa-ban fa-2x text-gray-300"></i></div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Room Status (Monthly) - GIAO DIỆN MỚI -->
        <div class="col">
          <div class="card h-100 border-left-info shadow-sm py-2">
            <div class="card-body">
              <div class="row no-gutters align-items-center">
                <div class="col mr-2">
                  <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Tình trạng phòng (Tháng)</div>
                  <div class="room-status-vertical">
                    <div class="status-line text-danger">
                      <span>Đã đặt:</span>
                      <b>{{ overviewData.statWidgets?.roomStatusMonthly?.occupied || 0 }}</b>
                    </div>
                    <div class="status-line text-success">
                      <span>Trống:</span>
                      <b>{{ overviewData.statWidgets?.roomStatusMonthly?.available || 0 }}</b>
                    </div>
                     <div class="status-line total">
                      <span>Tổng:</span>
                      <b>{{ overviewData.statWidgets?.roomStatusMonthly?.total || 0 }}</b>
                    </div>
                  </div>
                </div>
                <div class="col-auto"><i class="fas fa-bed fa-2x text-gray-300"></i></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Total Room Types -->
        <div class="col">
          <div class="card h-100 border-left-warning shadow-sm py-2">
            <div class="card-body">
              <div class="row no-gutters align-items-center">
                <div class="col mr-2">
                  <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Tổng loại phòng</div>
                  <div class="stat-number">{{ overviewData.statWidgets?.totalRoomTypes?.value || 0 }} loại</div>
                </div>
                <div class="col-auto"><i class="fas fa-hotel fa-2x text-gray-300"></i></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 2. CHARTS -->
      <div class="row mt-4">
        <div class="col-lg-12">
           <div class="card shadow-sm mb-4">
            <div class="card-header py-3">
              <h6 class="m-0 font-weight-bold text-primary">Doanh thu theo tháng (6 tháng qua)</h6>
            </div>
            <div class="card-body">
              <div class="chart-area">
                <canvas ref="revenueChartRef"></canvas>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-lg-7">
          <div class="card shadow-sm mb-4">
            <div class="card-header py-3">
              <h6 class="m-0 font-weight-bold text-primary">Hoạt động hệ thống (6 tháng qua)</h6>
            </div>
            <div class="card-body">
              <div class="chart-area">
                <canvas ref="contentActivityChartRef"></canvas>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-5">
          <div class="card shadow-sm mb-4">
            <div class="card-header py-3">
              <h6 class="m-0 font-weight-bold text-primary">Phòng đã đặt theo loại (Hôm nay)</h6>
            </div>
            <div class="card-body">
              <div class="chart-pie pt-4">
                <canvas ref="bookingsByRoomTypeChartRef"></canvas>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 3. QUICK LISTS -->
      <div class="row">
         <div class="col-lg-6 mb-4">
          <div class="card shadow-sm h-100">
            <div class="card-header py-3">
              <h6 class="m-0 font-weight-bold text-primary">Tin tức mới nhất</h6>
            </div>
            <div class="card-body p-0">
               <ul v-if="overviewData.latestNews && overviewData.latestNews.length > 0" class="dashboard-list">
                <li v-for="news in overviewData.latestNews" :key="news.id" class="dashboard-list-item">
                  <div class="avatar" :style="{ backgroundColor: getColorForName(news.author?.name || 'A') }">
                    {{ getInitials(news.author?.name || 'Admin') }}
                  </div>
                  <div class="list-item-content">
                    <div class="item-title">{{ news.title }}</div>
                    <div class="item-meta">
                      Đăng bởi <b>{{ news.author?.name || 'N/A' }}</b> - {{ formatDate(news.publish_date) }}
                    </div>
                  </div>
                </li>
              </ul>
              <div v-else class="empty-state">
                  <i class="fas fa-newspaper fa-2x text-gray-300 mb-2"></i>
                  <span>Không có tin tức mới.</span>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-6 mb-4">
          <div class="card shadow-sm h-100">
            <div class="card-header py-3">
              <h6 class="m-0 font-weight-bold text-primary">Bình luận mới nhất</h6>
            </div>
            <div class="card-body p-0">
               <ul v-if="overviewData.latestComments && overviewData.latestComments.length > 0" class="dashboard-list">
                <li v-for="comment in overviewData.latestComments" :key="comment.id" class="dashboard-list-item">
                   <div class="avatar" :style="{ backgroundColor: getColorForName(comment.user?.name || 'U') }">
                    {{ getInitials(comment.user?.name || 'User') }}
                  </div>
                  <div class="list-item-content">
                    <div class="item-user"><b>{{ comment.user?.name || 'N/A' }}</b> đã bình luận:</div>
                    <blockquote class="item-comment">"{{ comment.content.substring(0, 80) }}..."</blockquote>
                    <div class="item-meta">
                      {{ formatTimeAgo(comment.created_at) }} trong bài 
                      <a href="#">{{ comment.news?.title || 'Tin tức' }}</a>
                    </div>
                  </div>
                </li>
              </ul>
              <div v-else class="empty-state">
                  <i class="fas fa-comments fa-2x text-gray-300 mb-2"></i>
                  <span>Chưa có bình luận nào.</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
// ... (PHẦN SCRIPT GIỮ NGUYÊN NHƯ CŨ) ...
import { ref, onMounted, onBeforeUnmount, inject } from 'vue';
import axios from 'axios';
import Chart from 'chart.js/auto';

const isLoading = ref(true);
const overviewData = ref({});
const revenueChartRef = ref(null);
const contentActivityChartRef = ref(null);
const bookingsByRoomTypeChartRef = ref(null);
let revenueChartInstance = null;
let contentActivityChartInstance = null;
let bookingsByRoomTypeChartInstance = null;

const apiUrl = inject('apiUrl');
const axiosInstance = axios.create({ baseURL: apiUrl });
axiosInstance.interceptors.request.use(config => {
  const token = localStorage.getItem('tokenJwt');
  if (token) config.headers.Authorization = `Bearer ${token}`;
  return config;
});

const formatCurrency = (value) => value == null ? '0 ₫' : new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
const formatDate = (dateString) => dateString ? new Date(dateString).toLocaleDateString('vi-VN') : 'N/A';
const formatTimeAgo = (dateString) => {
  if (!dateString) return 'N/A';
  const seconds = Math.floor((new Date() - new Date(dateString)) / 1000);
  let interval = seconds / 31536000; if (interval > 1) return Math.floor(interval) + " năm trước";
  interval = seconds / 2592000; if (interval > 1) return Math.floor(interval) + " tháng trước";
  interval = seconds / 86400; if (interval > 1) return Math.floor(interval) + " ngày trước";
  interval = seconds / 3600; if (interval > 1) return Math.floor(interval) + " giờ trước";
  interval = seconds / 60; if (interval > 1) return Math.floor(interval) + " phút trước";
  return Math.floor(seconds) + " giây trước";
};
const getInitials = (name) => {
  if (!name) return '?';
  const words = name.split(' ');
  return (words.length > 1) ? (words[0][0] + words[words.length - 1][0]).toUpperCase() : name.substring(0, 2).toUpperCase();
};
const getColorForName = (name) => {
  if (!name) return '#6c757d';
  const colors = ['#4e73df', '#1cc88a', '#6f42c1', '#e74a3b', '#fd7e14', '#36b9cc'];
  let hash = 0;
  for (let i = 0; i < name.length; i++) hash = name.charCodeAt(i) + ((hash << 5) - hash);
  return colors[Math.abs(hash % colors.length)];
};

const createRevenueChart = (chartData) => {
  if (!revenueChartRef.value || !chartData) return;
  if (revenueChartInstance) revenueChartInstance.destroy();
  const ctx = revenueChartRef.value.getContext('2d');
  revenueChartInstance = new Chart(ctx, {
    type: 'line', data: { labels: chartData.labels, datasets: [{
        label: chartData.datasets[0].label, data: chartData.datasets[0].data,
        backgroundColor: 'rgba(28, 200, 138, 0.1)', borderColor: 'rgba(28, 200, 138, 1)',
        fill: true, tension: 0.3 }] },
    options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { callback: (value) => formatCurrency(value) } } } }
  });
};

const createContentActivityChart = (chartData) => {
  if (!contentActivityChartRef.value || !chartData) return;
  if (contentActivityChartInstance) contentActivityChartInstance.destroy();
  const ctx = contentActivityChartRef.value.getContext('2d');
  const backgroundColors = ['#4e73df', '#f6c23e', '#1cc88a'];
  const datasets = (chartData.datasets || []).map((ds, index) => ({
      label: ds.label, data: ds.data, backgroundColor: backgroundColors[index % backgroundColors.length],
  }));
  contentActivityChartInstance = new Chart(ctx, {
    type: 'bar', data: { labels: chartData.labels, datasets: datasets },
    options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { stepSize: 1, callback: function(value) { if (Number.isInteger(value)) return value; } } } } }
  });
};

const createBookingsByRoomTypeChart = (chartData) => {
  if (!bookingsByRoomTypeChartRef.value || !chartData) return;
  if (bookingsByRoomTypeChartInstance) bookingsByRoomTypeChartInstance.destroy();
  const ctx = bookingsByRoomTypeChartRef.value.getContext('2d');
  bookingsByRoomTypeChartInstance = new Chart(ctx, {
    type: 'doughnut', data: { labels: chartData.labels, datasets: [{ data: chartData.data, backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#e13b8a', '#6f42c1'] }] },
    options: { responsive: true, maintainAspectRatio: false, cutout: '80%' }
  });
};

const fetchOverviewData = async () => {
  try {
    isLoading.value = true;
    const response = await axiosInstance.get('/api/admin/dashboard/overview');
    overviewData.value = response.data || {};
    createRevenueChart(overviewData.value.revenueChart);
    createContentActivityChart(overviewData.value.contentActivityChart);
    createBookingsByRoomTypeChart(overviewData.value.bookingsByRoomTypeChart);
  } catch (error) {
    console.error("Lỗi khi lấy dữ liệu tổng quan:", error.response || error);
    overviewData.value = {};
    alert('Không thể tải dữ liệu tổng quan. Chi tiết: ' + (error.response?.data?.message || error.message));
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => fetchOverviewData());
onBeforeUnmount(() => {
  if (revenueChartInstance) revenueChartInstance.destroy();
  if (contentActivityChartInstance) contentActivityChartInstance.destroy();
  if (bookingsByRoomTypeChartInstance) bookingsByRoomTypeChartInstance.destroy();
});
</script>

<style scoped>
/* CSS cũ giữ nguyên */
.card.border-left-primary { border-left: .25rem solid #4e73df !important; }
.text-primary { color: #4e73df !important; }
.card.border-left-success { border-left: .25rem solid #1cc88a !important; }
.text-success { color: #1cc88a !important; }
.card.border-left-info { border-left: .25rem solid #36b9cc !important; }
.text-info { color: #36b9cc !important; }
.card.border-left-warning { border-left: .25rem solid #f6c23e !important; }
.text-warning { color: #f6c23e !important; }
.card.border-left-danger { border-left: .25rem solid #e74a3b !important; }
.text-danger { color: #e74a3b !important; }
.text-xs { font-size: .7rem; }
.text-gray-300 { color: #dddfeb !important; }
.text-gray-800 { color: #5a5c69 !important; }
.font-weight-bold { font-weight: 700 !important; }
.chart-area, .chart-pie { position: relative; height: 20rem; }
.loading-overlay { position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(255, 255, 255, 0.9); display: flex; flex-direction: column; justify-content: center; align-items: center; z-index: 10; min-height: 50vh; }
.dashboard-list { list-style: none; padding: 0; margin: 0; }
.dashboard-list-item { display: flex; align-items: center; padding: 1rem 1.25rem; border-bottom: 1px solid #e3e6f0; transition: background-color 0.2s ease-in-out; }
.dashboard-list-item:last-child { border-bottom: none; }
.dashboard-list-item:hover { background-color: #f8f9fc; }
.avatar { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 600; font-size: 0.9rem; margin-right: 1rem; flex-shrink: 0; }
.list-item-content { flex-grow: 1; min-width: 0; }
.item-title { font-weight: 600; color: #5a5c69; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 0.25rem; }
.item-user { font-size: 0.9rem; color: #5a5c69; }
.item-comment { font-size: 0.9rem; color: #6c757d; border-left: 3px solid #e3e6f0; padding-left: 0.75rem; margin: 0.25rem 0; font-style: italic; }
.item-meta { font-size: 0.75rem; color: #858796; }
.item-meta a { color: #4e73df; text-decoration: none; font-weight: 600; }
.item-meta a:hover { text-decoration: underline; }
.empty-state { display: flex; flex-direction: column; justify-content: center; align-items: center; height: 100%; min-height: 200px; color: #858796; }

/* === CSS MỚI: ĐIỀU CHỈNH LẠI FONT & BỐ CỤC WIDGET === */
.stat-number {
  font-size: 1.25rem;
  font-weight: 700;
  color: #5a5c69;
  line-height: 1.2;
}
.room-status-vertical {
  /* Container for vertical status display */
}
.status-line {
  display: flex;
  justify-content: space-between;
  font-size: 0.9rem;
  font-weight: 500;
  line-height: 1.5;
}
.status-line.total {
  font-size: 0.8rem;
  color: #858796;
  border-top: 1px solid #e3e6f0;
  margin-top: 0.2rem;
  padding-top: 0.2rem;
}
.status-line b {
  font-weight: 700;
}
</style>