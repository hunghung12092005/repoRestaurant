<template>
  <div class="booking-page">
    <div class="header">
      <h1 class="title">Lịch sử đặt phòng</h1>
      <div class="header-link">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-clock-history"
          viewBox="0 0 16 16">
          <path
            d="M8.515 1.019A7 7 0 0 0 8 1V0a8 8 0 0 1 8 8c0 4.418-3.582 8-8 8s-8-3.582-8-8C0 3.582 3.582 0 8 0a7 7 0 0 1 .515 1.019zM8 2a6 6 0 1 0 0 12A6 6 0 0 0 8 2zm.5 2v4.5l3.5 2.1.75-1.23-3-1.8V4h-.75z" />
        </svg>
        <span>Lịch sử đặt phòng</span>
      </div>
    </div>

    <div class="filter-container">
      <div class="filter-group">
        <label for="filter-date">Ngày:</label>
        <input type="date" id="filter-date" v-model="selectedDate" @change="fetchBookings" />
      </div>
      <button @click="clearFilters" class="clear-btn">Xóa lọc</button>
    </div>

    <div>
      <div class="booking-grid">
        <div v-for="booking in allBookings" :key="booking.status_id" class="booking-card">
          <p class="booking-label">Booking ID: {{ booking.booking_id }}</p>
          <h2 class="booking-customer">{{ booking.customer_name }}</h2>
          <p class="booking-room">Phòng: {{ booking.room_name }}</p>
          <p class="booking-dates">Nhận: {{ formatDate(booking.check_in) }}</p>
          <p class="booking-dates">Trả: {{ formatDate(booking.check_out) }}</p>
          <p class="booking-total">Tổng tiền: {{ formatPrice(booking.total_paid) }}</p>
          <div class="booking-actions">
            <button class="action-link" @click.prevent="showBookingDetails(booking.status_id)">Chi tiết</button>
          </div>
        </div>
      </div>
      <div v-if="allBookings.length === 0" class="no-results">
        Không có lịch sử booking nào khớp với bộ lọc hiện tại.
      </div>
    </div>

    <!-- Modal Chi tiết Booking -->
    <div v-if="showDetailModal" class="modal-overlay">
      <div class="modal-content">
        <h2>Chi tiết Lịch sử Booking</h2>
        <p><strong>Status ID:</strong> {{ bookingDetail.status_id }}</p>
        <p><strong>Booking ID:</strong> {{ bookingDetail.booking_id }}</p>
        <p><strong>Khách hàng:</strong> {{ bookingDetail.customer_name || 'Chưa có' }}</p>
        <p><strong>SĐT:</strong> {{ bookingDetail.customer_phone || '...' }}</p>
        <p><strong>Email:</strong> {{ bookingDetail.customer_email || '...' }}</p>
        <p><strong>Địa chỉ:</strong> {{ bookingDetail.address || '...' }}</p>
        <p><strong>Phòng:</strong> {{ bookingDetail.room_name }} - {{ bookingDetail.type_name }} (Tầng {{
          bookingDetail.floor_number }})</p>
        <p><strong>Nhận phòng:</strong> {{ formatDate(bookingDetail.check_in) }}</p>
        <p><strong>Trả phòng:</strong> {{ formatDate(bookingDetail.check_out) }}</p>
        <p><strong>Giá phòng:</strong> {{ formatPrice(bookingDetail.room_price) }}</p>
        <p><strong>Giá dịch vụ:</strong> {{ formatPrice(bookingDetail.service_price) }}</p>
        <p><strong>Tổng tiền:</strong> {{ formatPrice(bookingDetail.total_paid) }}</p>
        <p v-if="bookingDetail.surcharge"><strong>Phí phụ thu:</strong> {{ formatPrice(bookingDetail.surcharge) }}
          <span v-if="bookingDetail.surcharge_reason"> (Lý do: {{ bookingDetail.surcharge_reason }})</span></p>
        <div class="form-actions">
          <button @click="showDetailModal = false">Đóng</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { inject } from 'vue';

const apiUrl = inject('apiUrl');
const selectedDate = ref(new Date().toISOString().substr(0, 10));
const allBookings = ref([]);
const showDetailModal = ref(false);
const bookingDetail = ref({});

const fetchBookings = async () => {
  try {
    const params = { 
      date: selectedDate.value
    };
    const res = await axios.get(`${apiUrl}/api/booking-histories`, { params });
    allBookings.value = res.data.map(booking => ({
      status_id: booking.status_id,
      booking_id: booking.booking_id,
      customer_name: booking.customer?.customer_name || 'Chưa có',
      room_name: booking.room?.room_name,
      check_in: booking.check_in,
      check_out: booking.check_out,
      room_price: Number(booking.room_price) || 0,
      service_price: Number(booking.service_price) || 0,
      total_paid: Number(booking.total_paid) || 0,
    }));
  } catch (e) {
    console.error("Lỗi load lịch sử booking:", e);
    alert("Không thể tải danh sách lịch sử booking.");
  }
};

const formatDate = (date) => {
  if (!date) return '...';
  return new Date(date).toLocaleString('vi-VN', {
    year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit'
  });
};

const formatPrice = (price) => price.toLocaleString('vi-VN') + ' VND';

const clearFilters = () => {
  selectedDate.value = new Date().toISOString().substr(0, 10);
  fetchBookings();
};

const showBookingDetails = async (status_id) => {
  try {
    const res = await axios.get(`${apiUrl}/api/booking-histories/${status_id}`, {
      params: { date: selectedDate.value }
    });
    bookingDetail.value = {
      status_id: res.data.status_id,
      booking_id: res.data.booking_id,
      customer_name: res.data.customer?.customer_name,
      customer_phone: res.data.customer?.customer_phone,
      customer_email: res.data.customer?.customer_email,
      address: res.data.customer?.address,
      room_name: res.data.room?.room_name,
      type_name: res.data.room?.type_name,
      floor_number: res.data.room?.floor_number,
      check_in: res.data.check_in,
      check_out: res.data.check_out,
      room_price: Number(res.data.room_price) || 0,
      service_price: Number(res.data.service_price) || 0,
      total_paid: Number(res.data.total_paid) || 0,
      surcharge: Number(res.data.surcharge) || 0,
      surcharge_reason: res.data.surcharge_reason || ''
    };
    showDetailModal.value = true;
  } catch (e) {
    console.error("Lỗi load chi tiết booking:", e);
    alert("Không thể tải chi tiết booking.");
  }
};
onMounted(fetchBookings);
</script>

<style scoped>
.booking-page {
  font-family: 'monospace', sans-serif;
  padding: 24px;
  color: #333;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.title {
  font-size: 28px;
  font-weight: 600;
}

.header-link {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #888;
}

.filter-container {
  background-color: #ffffff;
  padding: 20px;
  border-radius: 8px;
  display: flex;
  align-items: flex-end;
  gap: 24px;
  margin-bottom: 24px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.filter-group {
  position: relative;
  margin-top: 10px;
}

.filter-group label {
  position: absolute;
  top: -8px;
  left: 10px;
  font-size: 12px;
  color: #888;
  background-color: #ffffff;
  padding: 0 4px;
  z-index: 1;
}

.filter-group input[type="date"] {
  padding: 10px 12px;
  border: 1px solid #adb5bd;
  border-radius: 6px;
  min-width: 180px;
  background-color: #fff;
  font-size: 14px;
  -webkit-appearance: none;
  -moz-appearance: none;
  appearance: none;
  transition: border-color 0.2s, box-shadow 0.2s;
  outline: none;
}

.filter-group input[type="date"]:focus {
  border-color: #6c5ffc;
  box-shadow: 0 0 0 3px rgba(108, 95, 252, 0.2);
}

.clear-btn {
  background-color: #6c5ffc;
  color: white;
  border: none;
  padding: 10px 24px;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
  height: 42px;
}

.no-results {
  text-align: center;
  padding: 50px;
  font-size: 18px;
  color: #888;
}

.booking-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
}

.booking-card {
  background-color: #ffffff;
  border: 1px solid #000000;
  border-radius: 12px;
  padding: 16px;
  position: relative;
  transition: box-shadow 0.3s, transform 0.3s;
}

.booking-card:hover {
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
  transform: translateY(-5px);
}

.booking-label {
  font-size: 13px;
  color: #888;
  margin-bottom: 0;
}

.booking-customer {
  font-size: 24px;
  font-weight: 700;
  margin: -5px 0 12px 0;
}

.booking-room,
.booking-dates,
.booking-total {
  font-size: 14px;
  color: #333;
  margin: 4px 0;
}

.booking-actions {
  margin-top: 12px;
  display: flex;
  gap: 12px;
}

.action-link {
  color: #6c5ffc;
  text-decoration: none;
  font-weight: 600;
  font-size: 14px;
  background: none;
  border: none;
  cursor: pointer;
}

.action-link:hover {
  text-decoration: underline;
}

.modal-overlay {
  z-index: 1000;
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
}

.modal-content {
  background: white;
  padding: 24px;
  border-radius: 8px;
  width: 400px;
}

.modal-content p {
  font-size: 14px;
  margin: 8px 0;
}

.form-actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
}

button {
  padding: 6px 14px;
  background-color: #4f46e5;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease-in-out;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
}

button:hover {
  background-color: #4338ca;
  transform: translateY(-1px);
}

button:active {
  transform: scale(0.97);
}

button:disabled {
  background-color: #ccc;
  cursor: not-allowed;
  box-shadow: none;
}
</style>