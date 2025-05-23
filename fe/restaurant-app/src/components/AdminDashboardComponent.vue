<template>
  <div>
    <!-- Stats Cards -->
    <div class="row row-cols-1 row-cols-md-4 g-4" id="statsCards">
      <div class="col">
        <div class="card h-100 bg-white shadow-sm border-0 rounded-4 p-3 text-center">
          <h3 class="fw-bold text-dark" id="newBookingValue">New Booking</h3>
          <p class="text-muted mb-1">1,879</p>
          <p class="text-success mb-0" id="newBookingChange"><i class="bi bi-arrow-up"></i> +7.5%</p>
        </div>
      </div>
      <div class="col">
        <div class="card h-100 bg-white shadow-sm border-0 rounded-4 p-3 text-center">
          <h3 class="fw-bold text-dark" id="availableRoomsValue">Available Rooms</h3>
          <p class="text-muted mb-1">55</p>
          <p class="text-warning mb-0" id="availableRoomsChange"><i class="bi bi-arrow-down"></i> -5.7%</p>
        </div>
      </div>
      <div class="col">
        <div class="card h-100 bg-white shadow-sm border-0 rounded-4 p-3 text-center">
          <h3 class="fw-bold text-dark" id="revenueValue">Revenue</h3>
          <p class="text-muted mb-1">$2,287</p>
          <p class="text-success mb-0" id="revenueChange"><i class="bi bi-arrow-up"></i> +5.3%</p>
        </div>
      </div>
      <div class="col">
        <div class="card h-100 bg-white shadow-sm border-0 rounded-4 p-3 text-center">
          <h3 class="fw-bold text-dark" id="checkOutValue">Checkout</h3>
          <p class="text-muted mb-1">567</p>
          <p class="text-warning mb-0" id="checkOutChange"><i class="bi bi-arrow-down"></i> -2.4%</p>
        </div>
      </div>
    </div>

    <!-- Room Availability and Reservation -->
    <div class="row mt-4">
      <div class="col-md-5">
        <div class="card p-3 shadow-sm rounded-3 h-100 d-flex flex-column">
          <h5 class="fw-bold">Room Availability</h5>
          <div class="flex-grow-1">
            <canvas ref="roomAvailabilityChart"></canvas>
          </div>
        </div>
      </div>
      <div class="col-md-7">
        <div class="card p-3 shadow-sm rounded-3 h-100 d-flex flex-column">
          <h5 class="fw-bold">Reservation</h5>
          <div class="legend d-flex justify-content-end mb-2"></div>
          <div class="flex-grow-1">
            <canvas ref="reservationChart"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import Chart from 'chart.js/auto';
import ChartDataLabels from 'chartjs-plugin-datalabels';

Chart.register(ChartDataLabels); // Đăng ký plugin datalabels

const roomAvailabilityChart = ref(null);
const reservationChart = ref(null);
let roomChartInstance = null;
let reservationChartInstance = null;

const roomAvailabilityData = {
  labels: ['Occupied', 'Reserved', 'Available', 'Not Ready'],
  datasets: [{
    data: [125, 87, 57, 25],
    backgroundColor: ['#f4a261', '#e9c46a', '#a8d5ba', '#d3d3d3'],
    borderWidth: 1,
    borderColor: '#fff'
  }]
};

const roomAvailabilityConfig = {
  type: 'pie',
  data: roomAvailabilityData,
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        position: 'right',
        labels: {
          boxWidth: 10,
          padding: 10
        }
      },
      datalabels: {
        color: '#fff', // Màu chữ bên trong
        font: {
          weight: 'bold',
          size: 14
        },
        formatter: (value) => value, // Hiển thị giá trị số
        anchor: 'center', // Đặt nhãn ở trung tâm
        align: 'center'
      }
    }
  }
};

const reservationData = {
  labels: ['01 Jan', '02 Jan', '03 Jan', '04 Jan', '05 Jan', '06 Jan', '07 Jan', '08 Jan'],
  datasets: [{
    label: 'Booked',
    data: [44, 55, 41, 67, 13, 43, 55, 64],
    backgroundColor: '#6a0dad',
    borderRadius: 8,
    barThickness: 25,
    stack: 'Stack 0'
  }, {
    label: 'Canceled',
    data: [13, 23, 20, 18, 13, 27, 12, 9],
    backgroundColor: '#f4a261',
    borderRadius: 8,
    barThickness: 25,
    stack: 'Stack 0'
  }]
};

const reservationConfig = {
  type: 'bar',
  data: reservationData,
  options: {
    responsive: true,
    maintainAspectRatio: false,
    scales: {
      x: {
        stacked: true
      },
      y: {
        stacked: true,
        beginAtZero: true,
        max: 100,
        ticks: {
          stepSize: 20
        }
      }
    },
    plugins: {
      legend: {
        position: 'top',
        align: 'end',
        labels: {
          boxWidth: 10,
          padding: 10
        }
      }, datalabels: {
        color: '#fff', // Màu chữ bên trong
        font: {
          weight: 'bold',
          size: 14
        },
        formatter: (value) => value, // Hiển thị giá trị số
        anchor: 'center', // Đặt nhãn ở trung tâm
        align: 'center'
      }
    }
  }
};

onMounted(() => {
  const roomCtx = roomAvailabilityChart.value.getContext('2d');
  roomChartInstance = new Chart(roomCtx, roomAvailabilityConfig);

  const resCtx = reservationChart.value.getContext('2d');
  reservationChartInstance = new Chart(resCtx, reservationConfig);
});
</script>

<style scoped>
.card {
  transition: all 0.3s ease;
}

.card:hover {
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.search-bar .search-icon,
.order-search span {
  margin-right: 8px;
  font-size: 16px;
  color: #666;
}

.search-bar input,
.order-search span + * {
  border: none;
  outline: none;
  font-size: 14px;
  color: #666;
  width: 200px;
}

.search-bar input::placeholder,
.order-search span + *::placeholder {
  color: #999;
}

.user-info {
  display: flex;
  align-items: center;
}

.user-info .user-name {
  font-size: 16px;
  color: #333;
  margin-right: 10px;
}

.user-info .avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  margin-right: 10px;
}

.user-info .role-badge {
  background: #ff6200;
  color: #fff;
  font-size: 12px;
  padding: 4px 10px;
  border-radius: 12px;
  text-transform: uppercase;
}

/* Stats Section */
.stats {
  display: flex;
  gap: 20px;
  margin-bottom: 30px;
}

.stat-card {
  flex: 1;
  background: #fff;
  padding: 25px;
  border-radius: 12px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
  display: flex;
  align-items: center;
  transition: transform 0.2s ease;
}

.stat-card:hover {
  transform: translateY(-2px);
}

.stat-icon {
  width: 50px;
  height: 50px;
  background: #ff6200;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 15px;
}

.stat-icon span {
  font-size: 24px;
  color: #fff;
}

.stat-content h3 {
  font-size: 14px;
  font-weight: 500;
  color: #666;
  margin: 0 0 5px 0;
  text-transform: uppercase;
}

.stat-content p {
  font-size: 24px;
  font-weight: 600;
  color: #333;
  margin: 0;
  line-height: 1.2;
}

.stat-content .trend {
  font-size: 12px;
  padding: 2px 6px;
  border-radius: 10px;
  margin-left: 8px;
}

.stat-content .trend.up {
  background: #d4edda;
  color: #155724;
}

/* Charts Section */
.charts {
  display: flex;
  gap: 20px;
  margin-bottom: 30px;
}

.chart-card {
  flex: 1;
  background: #fff;
  padding: 20px;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.chart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
}

/* Cập nhật kích thước font cho h3 trong Stats Cards */
.row-cols-1.row-cols-md-4 .card h3 {
  font-size: 1rem; /* Giảm từ 1.25rem xuống 1rem */
}

.card p.text-muted {
  font-size: 1rem;
}

.card p.text-success,
.card p.text-warning {
  font-size: 0.9rem;
}

.legend {
  position: relative;
}

@media (max-width: 768px) {
  .row-cols-1.row-cols-md-4 .card {
    min-height: 120px;
  }

  .row-cols-1.row-cols-md-4 .card h3 {
    font-size: 0.9rem; /* Giảm thêm trên mobile nếu cần */
  }
}
</style>