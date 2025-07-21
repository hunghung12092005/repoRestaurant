<template>
  <div class="position-relative">
    <!-- Loading Overlay -->
    <div v-if="isLoading" class="loading-overlay">
      <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status"></div>
      <p class="mt-3 text-muted">Đang tải dữ liệu tổng quan...</p>
    </div>

    <div v-show="!isLoading">
      <!-- 1. STATS WIDGETS (Giữ nguyên) -->
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
                  <div class="room-status-layout">
                    <div class="status-item">
                      <span class="text-danger small">Đã đặt: <b>{{ overviewData.statWidgets?.roomStatus?.occupied || 0 }}</b></span>
                      <span class="mx-1">/</span>
                      <span class="text-success small">Trống: <b>{{ overviewData.statWidgets?.roomStatus?.available || 0 }}</b></span>
                    </div>
                    <div class="total-item small">
                      Tổng: {{ overviewData.statWidgets?.roomStatus?.total || 0 }} phòng
                    </div>
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

      <!-- 2. CHARTS (Giữ nguyên) -->
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

      <!-- 3. QUICK LISTS (GIAO DIỆN MỚI) -->
      <div class="row">
        <!-- Latest News -->
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
        <!-- Latest Comments -->
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
  let interval = seconds / 31536000; if (interval > 1) return Math.floor(interval) + " năm trước";
  interval = seconds / 2592000; if (interval > 1) return Math.floor(interval) + " tháng trước";
  interval = seconds / 86400; if (interval > 1) return Math.floor(interval) + " ngày trước";
  interval = seconds / 3600; if (interval > 1) return Math.floor(interval) + " giờ trước";
  interval = seconds / 60; if (interval > 1) return Math.floor(interval) + " phút trước";
  return Math.floor(seconds) + " giây trước";
};

// --- CÁC HÀM HELPER MỚI CHO AVATAR ---
const getInitials = (name) => {
  if (!name) return '?';
  const words = name.split(' ');
  if (words.length > 1) {
    return (words[0][0] + words[words.length - 1][0]).toUpperCase();
  }
  return name.substring(0, 2).toUpperCase();
};

const getColorForName = (name) => {
  if (!name) return '#6c757d';
  const colors = ['#4e73df', '#1cc88a', '#6f42c1', '#e74a3b', '#fd7e14', '#36b9cc'];
  let hash = 0;
  for (let i = 0; i < name.length; i++) {
    hash = name.charCodeAt(i) + ((hash << 5) - hash);
  }
  return colors[Math.abs(hash % colors.length)];
};
// --- KẾT THÚC HELPER MỚI ---

const createContentActivityChart = (chartData) => {
  if (!contentActivityChartRef.value) return;
  if (contentActivityChartInstance) contentActivityChartInstance.destroy();
  const ctx = contentActivityChartRef.value.getContext('2d');
  const backgroundColors = ['rgba(78, 115, 223, 0.8)', 'rgba(28, 200, 138, 0.8)', 'rgba(246, 194, 62, 0.8)'];
  const datasets = (chartData.datasets || []).map((ds, index) => ({
      label: ds.label, data: ds.data, backgroundColor: backgroundColors[index % backgroundColors.length],
  }));
  contentActivityChartInstance = new Chart(ctx, {
    type: 'bar', data: { labels: chartData.labels, datasets: datasets },
    options: { responsive: true, maintainAspectRatio: false,
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1, callback: function(value) { if (Number.isInteger(value)) { return value; } }, } } } }
  });
};

const createBookingsByRoomTypeChart = (chartData) => {
  if (!bookingsByRoomTypeChartRef.value) return;
  if (bookingsByRoomTypeChartInstance) bookingsByRoomTypeChartInstance.destroy();
  const ctx = bookingsByRoomTypeChartRef.value.getContext('2d');
  bookingsByRoomTypeChartInstance = new Chart(ctx, {
    type: 'doughnut', data: { labels: chartData.labels, datasets: [{ data: chartData.data, backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#e13b8a', '#6f42c1'], }], },
    options: { responsive: true, maintainAspectRatio: false, cutout: '80%' }
  });
};

const fetchOverviewData = async () => {
  try {
    isLoading.value = true;
    const response = await axiosInstance.get('/api/admin/dashboard/overview');
    overviewData.value = response.data || {};
    if (overviewData.value.contentActivityChart) createContentActivityChart(overviewData.value.contentActivityChart);
    if (overviewData.value.bookingsByRoomTypeChart) createBookingsByRoomTypeChart(overviewData.value.bookingsByRoomTypeChart);
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
.text-xs { font-size: .7rem; }
.text-gray-300 { color: #dddfeb !important; }
.text-gray-800 { color: #5a5c69 !important; }
.font-weight-bold { font-weight: 700 !important; }
.chart-area, .chart-pie { position: relative; height: 20rem; }
.loading-overlay { position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(255, 255, 255, 0.9); display: flex; flex-direction: column; justify-content: center; align-items: center; z-index: 10; min-height: 50vh; }
.room-status-layout { display: flex; flex-direction: column; font-weight: 700; color: #5a5c69; }
.status-item { font-size: 1rem; line-height: 1.4; }
.total-item { margin-top: 0.1rem; }

/* === CSS MỚI CHO DANH SÁCH TIN TỨC & BÌNH LUẬN === */
.dashboard-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.dashboard-list-item {
  display: flex;
  align-items: center;
  padding: 1rem 1.25rem;
  border-bottom: 1px solid #e3e6f0;
  transition: background-color 0.2s ease-in-out;
}

.dashboard-list-item:last-child {
  border-bottom: none;
}

.dashboard-list-item:hover {
  background-color: #f8f9fc;
}

.avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-weight: 600;
  font-size: 0.9rem;
  margin-right: 1rem;
  flex-shrink: 0; /* Ngăn avatar bị co lại */
}

.list-item-content {
  flex-grow: 1;
  min-width: 0; /* Fix lỗi flexbox cho text dài */
}

.item-title {
  font-weight: 600;
  color: #5a5c69;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  margin-bottom: 0.25rem;
}

.item-user {
  font-size: 0.9rem;
  color: #5a5c69;
}

.item-comment {
  font-size: 0.9rem;
  color: #6c757d;
  border-left: 3px solid #e3e6f0;
  padding-left: 0.75rem;
  margin: 0.25rem 0;
  font-style: italic;
}

.item-meta {
  font-size: 0.75rem;
  color: #858796;
}
.item-meta a {
  color: #4e73df;
  text-decoration: none;
  font-weight: 600;
}
.item-meta a:hover {
  text-decoration: underline;
}

.empty-state {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    height: 100%;
    min-height: 200px;
    color: #858796;
}
</style>