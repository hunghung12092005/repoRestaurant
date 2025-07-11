<template>
  <div class="position-relative">
    <!-- Loading overlay: Chỉ hiển thị khi đang tải dữ liệu -->
    <div v-if="loading" class="loading-overlay">
      <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
    </div>

    <!-- Main Content: Được render với dữ liệu mặc định ban đầu, nhưng bị che bởi overlay -->
    <div :class="{ 'content-loading': loading }">
      <!-- Stats Cards -->
      <div class="row row-cols-1 row-cols-md-4 g-4" id="statsCards">
        <div class="col">
          <div class="card h-100 bg-white shadow-sm border-0 rounded-4 p-3 text-center">
            <h3 class="fw-bold text-dark">New Booking</h3>
            <!-- Dữ liệu được truy cập an toàn nhờ giá trị khởi tạo của statsCards -->
            <p class="text-muted mb-1 fs-4 fw-bold">{{ statsCards.newBooking.value }}</p>
            <p class="mb-0" :class="statsCards.newBooking.change >= 0 ? 'text-success' : 'text-danger'">
              <i class="bi" :class="statsCards.newBooking.change >= 0 ? 'bi-arrow-up' : 'bi-arrow-down'"></i>
              {{ Math.abs(statsCards.newBooking.change) }}%
            </p>
          </div>
        </div>
        <div class="col">
          <div class="card h-100 bg-white shadow-sm border-0 rounded-4 p-3 text-center">
            <h3 class="fw-bold text-dark">Available Rooms</h3>
            <p class="text-muted mb-1 fs-4 fw-bold">{{ statsCards.availableRooms.value }}</p>
            <p class="text-info mb-0">
              {{ statsCards.availableRooms.change }}% of total
            </p>
          </div>
        </div>
        <div class="col">
          <div class="card h-100 bg-white shadow-sm border-0 rounded-4 p-3 text-center">
            <h3 class="fw-bold text-dark">Revenue</h3>
            <p class="text-muted mb-1 fs-4 fw-bold">${{ statsCards.revenue.value.toLocaleString() }}</p>
            <p class="mb-0" :class="statsCards.revenue.change >= 0 ? 'text-success' : 'text-danger'">
              <i class="bi" :class="statsCards.revenue.change >= 0 ? 'bi-arrow-up' : 'bi-arrow-down'"></i>
              {{ Math.abs(statsCards.revenue.change) }}%
            </p>
          </div>
        </div>
        <div class="col">
          <div class="card h-100 bg-white shadow-sm border-0 rounded-4 p-3 text-center">
            <h3 class="fw-bold text-dark">Checkout</h3>
            <p class="text-muted mb-1 fs-4 fw-bold">{{ statsCards.checkOut.value }}</p>
            <p class="mb-0" :class="statsCards.checkOut.change >= 0 ? 'text-success' : 'text-danger'">
              <i class="bi" :class="statsCards.checkOut.change >= 0 ? 'bi-arrow-up' : 'bi-arrow-down'"></i>
              {{ Math.abs(statsCards.checkOut.change) }}%
            </p>
          </div>
        </div>
      </div>

      <!-- Room Availability and Reservation -->
      <div class="row mt-4">
        <div class="col-lg-5">
          <div class="card p-3 shadow-sm rounded-3 h-100 d-flex flex-column">
            <h5 class="fw-bold">Room Availability</h5>
            <div class="flex-grow-1" style="min-height: 300px;">
              <canvas ref="roomAvailabilityChart"></canvas>
            </div>
          </div>
        </div>
        <div class="col-lg-7">
          <div class="card p-3 shadow-sm rounded-3 h-100 d-flex flex-column">
            <h5 class="fw-bold">Reservation Statistics (Last 8 Days)</h5>
            <div class="flex-grow-1" style="min-height: 300px;">
              <canvas ref="reservationChart"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, onBeforeUnmount, ref } from 'vue';
import Chart from 'chart.js/auto';
import ChartDataLabels from 'chartjs-plugin-datalabels';
import axios from 'axios'; // Đảm bảo đường dẫn này đúng

Chart.register(ChartDataLabels);

const roomAvailabilityChart = ref(null);
const reservationChart = ref(null);
let roomChartInstance = null;
let reservationChartInstance = null;

// Khởi tạo trạng thái loading là true
const loading = ref(true);

// DỮ LIỆU MẶC ĐỊNH: Rất quan trọng để tránh lỗi render ban đầu
const statsCards = ref({
  newBooking: { value: 0, change: 0 },
  availableRooms: { value: 0, change: 0 },
  revenue: { value: 0, change: 0 },
  checkOut: { value: 0, change: 0 }
});

const createRoomAvailabilityChart = (data) => {
  if (roomChartInstance) {
    roomChartInstance.destroy();
  }
  const ctx = roomAvailabilityChart.value.getContext('2d');
  roomChartInstance = new Chart(ctx, {
    type: 'pie',
    data: {
      labels: data.labels,
      datasets: [{
        data: data.data,
        backgroundColor: ['#f4a261', '#e9c46a', '#a8d5ba', '#d3d3d3'],
        borderWidth: 2,
        borderColor: '#fff'
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'right',
          labels: { boxWidth: 12, padding: 15, font: { size: 14 } }
        },
        datalabels: {
          color: '#fff',
          font: { weight: 'bold', size: 14 },
          formatter: (value) => value > 0 ? value : '',
          anchor: 'center',
          align: 'center'
        }
      }
    }
  });
};

const createReservationChart = (data) => {
  if (reservationChartInstance) {
    reservationChartInstance.destroy();
  }
  const ctx = reservationChart.value.getContext('2d');
  reservationChartInstance = new Chart(ctx, {
    type: 'bar',
    data: data,
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        x: { stacked: true, grid: { display: false } },
        y: { stacked: true, beginAtZero: true, ticks: { stepSize: 20 } }
      },
      plugins: {
        legend: {
          position: 'top',
          align: 'end',
          labels: { boxWidth: 10, padding: 10 }
        },
        datalabels: {
          color: '#fff',
          font: { weight: 'bold', size: 12 },
          formatter: (value) => value > 0 ? value : '',
          anchor: 'center',
          align: 'center'
        }
      }
    }
  });
};

const fetchDashboardData = async () => {
  // Không cần set loading = true ở đây nữa, vì nó đã được khởi tạo là true
  try {
    const response = await axios.get('/admin/dashboard/stats');
    const data = response.data;
    
    // **FIX LỖI QUAN TRỌNG**: Kiểm tra xem dữ liệu có tồn tại trước khi gán
    if (data && data.statsCards) {
      statsCards.value = data.statsCards;
    }
    
    // Tạo lại biểu đồ với dữ liệu mới
    if (data && data.roomAvailability) {
      createRoomAvailabilityChart(data.roomAvailability);
    }
    if (data && data.reservationChart) {
      createReservationChart(data.reservationChart);
    }

  } catch (error) {
    console.error("Error fetching dashboard data:", error);
    // Hiển thị thông báo lỗi cho người dùng (ví dụ: dùng thư viện toast)
    alert('Failed to load dashboard data. Please check the console for more details.');
  } finally {
    // Luôn tắt loading spinner sau khi API hoàn thành (dù thành công hay thất bại)
    loading.value = false;
  }
};

onMounted(() => {
  fetchDashboardData();
});

onBeforeUnmount(() => {
    if(roomChartInstance) roomChartInstance.destroy();
    if(reservationChartInstance) reservationChartInstance.destroy();
});
</script>

<style scoped>
.card {
  transition: all 0.3s ease;
}

.card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1) !important;
}

.row-cols-1.row-cols-md-4 .card h3 {
  font-size: 1.1rem;
}

/* Thêm style cho loading overlay */
.loading-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(255, 255, 255, 0.8);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 10;
  border-radius: 0.5rem;
  backdrop-filter: blur(2px);
}

.content-loading {
  /* Làm mờ nội dung phía sau khi đang tải */
  filter: blur(4px);
  transition: filter 0.3s ease-in-out;
}
</style>