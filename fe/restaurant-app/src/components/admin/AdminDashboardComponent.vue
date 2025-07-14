<template>
  <div class="position-relative">
    <!-- Loading Overlay -->
    <div v-if="isLoading" class="loading-overlay">
      <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status"></div>
      <p class="mt-3 text-muted">Đang tải dữ liệu tổng quan...</p>
    </div>

    <div v-show="!isLoading">
      <!-- 1. STATS WIDGETS -->
      <div class="row">
        <!-- News Widget -->
        <div class="col-lg-3 col-md-6 mb-4">
          <div class="card h-100 border-left-primary shadow-sm py-2">
            <div class="card-body">
              <div class="row no-gutters align-items-center">
                <div class="col mr-2">
                  <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Tin tức</div>
                  <div class="h5 mb-0 font-weight-bold text-gray-800">{{ overviewData.statWidgets?.news?.value || 0 }} bài viết</div>
                </div>
                <div class="col-auto">
                  <i class="fas fa-newspaper fa-2x text-gray-300"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- Comments Widget -->
        <div class="col-lg-3 col-md-6 mb-4">
          <div class="card h-100 border-left-success shadow-sm py-2">
            <div class="card-body">
              <div class="row no-gutters align-items-center">
                <div class="col mr-2">
                  <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Bình luận</div>
                  <div class="h5 mb-0 font-weight-bold text-gray-800">{{ overviewData.statWidgets?.comments?.value || 0 }} bình luận</div>
                </div>
                <div class="col-auto">
                  <i class="fas fa-comments fa-2x text-gray-300"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- Room Types Widget -->
        <div class="col-lg-3 col-md-6 mb-4">
          <div class="card h-100 border-left-info shadow-sm py-2">
            <div class="card-body">
              <div class="row no-gutters align-items-center">
                <div class="col mr-2">
                  <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Loại phòng</div>
                  <div class="h5 mb-0 font-weight-bold text-gray-800">{{ overviewData.statWidgets?.roomTypes?.value || 0 }} loại</div>
                </div>
                <div class="col-auto">
                  <i class="fas fa-hotel fa-2x text-gray-300"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- Room Status Widget -->
        <div class="col-lg-3 col-md-6 mb-4">
          <div class="card h-100 border-left-warning shadow-sm py-2">
            <div class="card-body">
              <div class="row no-gutters align-items-center">
                <div class="col mr-2">
                  <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Tình trạng phòng</div>
                  <div class="mb-0 font-weight-bold text-gray-800">
                    <span class="text-danger">Đã đặt: {{ overviewData.statWidgets?.roomStatus?.occupied || 0 }}</span>
                    /
                    <span class="text-success">Trống: {{ overviewData.statWidgets?.roomStatus?.available || 0 }}</span>
                    <div class="small">Tổng: {{ overviewData.statWidgets?.roomStatus?.total || 0 }} phòng</div>
                  </div>
                </div>
                <div class="col-auto">
                  <i class="fas fa-bed fa-2x text-gray-300"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 2. CHARTS -->
      <div class="row">
        <div class="col-lg-7">
          <div class="card shadow-sm mb-4">
            <div class="card-header py-3">
              <h6 class="m-0 font-weight-bold text-primary">Hoạt động nội dung (6 tháng qua)</h6>
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
              <h6 class="m-0 font-weight-bold text-primary">Phòng đã đặt theo loại</h6>
            </div>
            <div class="card-body">
              <div class="chart-pie pt-4">
                <canvas ref="bookingsByRoomTypeChartRef"></canvas>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 3. QUICK TABLES -->
      <div class="row">
        <!-- Latest News -->
        <div class="col-lg-6 mb-4">
          <div class="card shadow-sm h-100">
            <div class="card-header py-3">
              <h6 class="m-0 font-weight-bold text-primary">Tin tức mới nhất</h6>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-hover table-sm mb-0">
                  <thead>
                    <tr>
                      <th>Tiêu đề</th>
                      <th>Ngày đăng</th>
                      <th>Tác giả</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="news in overviewData.latestNews" :key="news.id">
                      <td>{{ news.title.substring(0, 30) }}...</td>
                      <td>{{ formatDate(news.publish_date) }}</td>
                      <td>{{ news.author?.name || 'N/A' }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
        <!-- Latest Comments -->
        <div class="col-lg-6 mb-4">
          <div class="card shadow-sm h-100">
            <div class="card-header py-3">
              <h6 class="m-0 font-weight-bold text-primary">Bình luận mới nhất</h6>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-hover table-sm mb-0">
                  <thead>
                    <tr>
                      <th>Người dùng</th>
                      <th>Bình luận</th>
                      <th>Thời gian</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="comment in overviewData.latestComments" :key="comment.id">
                      <td>{{ comment.user?.name || 'N/A' }}</td>
                      <td>{{ comment.content.substring(0, 30) }}...</td>
                      <td>{{ formatTimeAgo(comment.created_at) }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, inject } from 'vue';
import axios from 'axios';
import Chart from 'chart.js/auto';

// State Variables
const isLoading = ref(true);
const overviewData = ref({});
const contentActivityChartRef = ref(null);
const bookingsByRoomTypeChartRef = ref(null);
let contentActivityChartInstance = null;
let bookingsByRoomTypeChartInstance = null;

const apiUrl = inject('apiUrl');

// Axios Instance with interceptor for auth
const axiosInstance = axios.create({ baseURL: apiUrl });
axiosInstance.interceptors.request.use(config => {
  const token = localStorage.getItem('tokenJwt');
  if (token) config.headers.Authorization = `Bearer ${token}`;
  return config;
});

// Helper Functions
const formatDate = (dateString) => dateString ? new Date(dateString).toLocaleDateString('vi-VN') : 'N/A';
const formatTimeAgo = (dateString) => {
  if (!dateString) return 'N/A';
  const seconds = Math.floor((new Date() - new Date(dateString)) / 1000);
  let interval = seconds / 31536000;
  if (interval > 1) return Math.floor(interval) + " năm trước";
  interval = seconds / 2592000;
  if (interval > 1) return Math.floor(interval) + " tháng trước";
  interval = seconds / 86400;
  if (interval > 1) return Math.floor(interval) + " ngày trước";
  interval = seconds / 3600;
  if (interval > 1) return Math.floor(interval) + " giờ trước";
  interval = seconds / 60;
  if (interval > 1) return Math.floor(interval) + " phút trước";
  return Math.floor(seconds) + " giây trước";
};

// Chart Creation Functions
const createContentActivityChart = (chartData) => {
  if (!contentActivityChartRef.value) return;
  if (contentActivityChartInstance) contentActivityChartInstance.destroy();
  const ctx = contentActivityChartRef.value.getContext('2d');
  contentActivityChartInstance = new Chart(ctx, {
    type: 'line',
    data: {
      labels: chartData.labels,
      datasets: [{
        label: chartData.datasets[0].label,
        data: chartData.datasets[0].data,
        borderColor: 'rgba(78, 115, 223, 1)',
        backgroundColor: 'rgba(78, 115, 223, 0.05)',
        fill: true,
        tension: 0.3
      }, {
        label: chartData.datasets[1].label,
        data: chartData.datasets[1].data,
        borderColor: 'rgba(28, 200, 138, 1)',
        backgroundColor: 'rgba(28, 200, 138, 0.05)',
        fill: true,
        tension: 0.3
      }]
    },
    options: { responsive: true, maintainAspectRatio: false }
  });
};

const createBookingsByRoomTypeChart = (chartData) => {
  if (!bookingsByRoomTypeChartRef.value) return;
  if (bookingsByRoomTypeChartInstance) bookingsByRoomTypeChartInstance.destroy();
  const ctx = bookingsByRoomTypeChartRef.value.getContext('2d');
  bookingsByRoomTypeChartInstance = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: chartData.labels,
      datasets: [{
        data: chartData.data,
        backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'],
      }],
    },
    options: { responsive: true, maintainAspectRatio: false, cutout: '80%' }
  });
};

// API Call
const fetchOverviewData = async () => {
  try {
    const response = await axiosInstance.get('/api/admin/dashboard/overview');
    overviewData.value = response.data || {};
    console.log('Fetched data:', overviewData.value);

    // Khởi tạo biểu đồ contentActivityChart
    if (contentActivityChartRef.value) {
      if (contentActivityChartInstance) contentActivityChartInstance.destroy();
      const ctx = contentActivityChartRef.value.getContext('2d');
      const chartData = overviewData.value.contentActivityChart || { labels: [], datasets: [{ label: 'Tin tức mới', data: [] }, { label: 'Bình luận mới', data: [] }] };
      contentActivityChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
          labels: chartData.labels || [],
          datasets: chartData.datasets || [{ label: 'Tin tức mới', data: [], borderColor: 'rgba(78, 115, 223, 1)', backgroundColor: 'rgba(78, 115, 223, 0.05)', fill: true, tension: 0.3 }, { label: 'Bình luận mới', data: [], borderColor: 'rgba(28, 200, 138, 1)', backgroundColor: 'rgba(28, 200, 138, 0.05)', fill: true, tension: 0.3 }]
        },
        options: { responsive: true, maintainAspectRatio: false }
      });
    }

    // Khởi tạo biểu đồ bookingsByRoomTypeChart
    if (bookingsByRoomTypeChartRef.value) {
      if (bookingsByRoomTypeChartInstance) bookingsByRoomTypeChartInstance.destroy();
      const ctx = bookingsByRoomTypeChartRef.value.getContext('2d');
      const chartData = overviewData.value.bookingsByRoomTypeChart || { labels: [], data: [] };
      bookingsByRoomTypeChartInstance = new Chart(ctx, {
        type: 'doughnut',
        data: {
          labels: chartData.labels || [],
          datasets: [{ data: chartData.data || [], backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'] }]
        },
        options: { responsive: true, maintainAspectRatio: false, cutout: '80%' }
      });
    }
  } catch (error) {
    console.error("Lỗi khi lấy dữ liệu tổng quan:", error.response || error);
    overviewData.value = {};
    alert('Không thể tải dữ liệu tổng quan. Vui lòng kiểm tra kết nối hoặc liên hệ admin. Chi tiết: ' + (error.response?.statusText || 'Không xác định'));
  } finally {
    isLoading.value = false;
  }
};

// Lifecycle Hooks
onMounted(() => {
  fetchOverviewData();
});

onBeforeUnmount(() => {
  if (contentActivityChartInstance) contentActivityChartInstance.destroy();
  if (bookingsByRoomTypeChartInstance) bookingsByRoomTypeChartInstance.destroy();
});
</script>

<style scoped>
/* Giữ nguyên CSS như trước */
.card.border-left-primary { border-left: .25rem solid #4e73df !important; }
.text-primary { color: #4e73df !important; }

.card.border-left-success { border-left: .25rem solid #1cc88a !important; }
.text-success { color: #1cc88a !important; }

.card.border-left-info { border-left: .25rem solid #36b9cc !important; }
.text-info { color: #36b9cc !important; }

.card.border-left-warning { border-left: .25rem solid #f6c23e !important; }
.text-warning { color: #f6c23e !important; }

.text-xs { font-size: .7rem; }
.text-gray-300 { color: #dddfeb !important; }
.text-gray-800 { color: #5a5c69 !important; }
.font-weight-bold { font-weight: 700 !important; }

.chart-area, .chart-pie {
  position: relative;
  height: 20rem;
}

.loading-overlay {
  position: absolute;
  top: 0; left: 0; right: 0; bottom: 0;
  background-color: rgba(255, 255, 255, 0.9);
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  z-index: 10;
}
</style>