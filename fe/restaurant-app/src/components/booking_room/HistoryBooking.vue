<template>
  <div class="container py-5">
    <div class="text-center mb-5">
      <h2 class="fw-bold text-uppercase border-bottom pb-2 d-inline-block border-3 border-primary">
        Lịch sử Đặt phòng
      </h2>
    </div>

    <Loading v-if="isLoading" />

    <div v-else-if="error" class="alert alert-danger text-center">{{ error }}</div>

    <div v-else-if="bookings.length === 0" class="alert alert-info text-center">
      <p class="mb-1">Bạn chưa có lịch sử đặt phòng nào.</p>
    </div>

    <div v-else class="row g-4 justify-content-center">
      <div v-for="booking in bookings" :key="booking.booking_id" class="col-12 col-md-10 col-lg-8">
        <div class="card shadow-sm border-0 rounded-4 p-4">
          <!-- Header -->
          <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
            <div>
              <h5 class="mb-0">Mã đặt phòng: <strong>#{{ booking.booking_id }}</strong></h5>
              <small class="text-muted">Mã đơn hàng: {{ booking.orderCode || 'Không có' }}</small>
            </div>
            <span :class="['badge px-3 py-2', formatStatusClass(booking.status)]">
              {{ formatStatus(booking.status) }}
            </span>
          </div>

          <!-- Thông tin chính -->
          <div class="mb-2 row">
            <div class="col-6"><strong>Nhận phòng:</strong> {{ formatDate(booking.check_in_date) }}</div>
            <div class="col-6"><strong>Trả phòng:</strong> {{ formatDate(booking.check_out_date) }}</div>
          </div>

          <div class="mb-2 row" v-if="booking.room_type_info">
            <div class="col-6"><strong>Loại phòng:</strong> {{ booking.room_type_info.type_name }}</div>
            <div class="col-6"><strong>Sức chứa:</strong> {{ booking.room_type_info.max_occupancy }} người</div>
          </div>

          <div class="mb-2 row">
            <div class="col-6"><strong>Số lượng phòng:</strong> {{ booking.total_rooms }}</div>
            <div class="col-6"><strong>Loại đặt:</strong> {{ booking.booking_type === 'online' ? 'Đặt online' : booking.booking_type }}</div>
          </div>

          <div class="mb-2 row">
            <div class="col-6"><strong>Hình thức thanh toán:</strong> {{ formatPayment(booking.payment_method) }}</div>
            <div class="col-6"><strong>Trạng thái thanh toán:</strong> {{ formatPaymentStatus(booking.payment_status) }}</div>
          </div>

          <div class="mb-2 row">
            <div class="col-6"><strong>Phụ phí:</strong> {{ formatCurrency(booking.additional_fee) }}</div>
            <div class="col-6"><strong>Tổng tiền:</strong> {{ formatCurrency(booking.total_price) }}</div>
          </div>

          <div v-if="booking.note" class="mb-2">
            <strong>Ghi chú:</strong> <span class="fst-italic">{{ booking.note }}</span>
          </div>

          <!-- Hành động -->
          <div class="mb-3" v-if="canCancelBooking(booking)">
            <button class="btn btn-outline-danger btn-sm" @click="cancelBooking(booking.booking_id)">
              Hủy đặt phòng
            </button>
          </div>

          <small class="text-muted fst-italic">Cập nhật: {{ formatDate(booking.updated_at) }}</small>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { inject, onMounted, ref } from 'vue';
import axios from 'axios';
import Loading from '../loading.vue';

const apiUrl = inject('apiUrl');
const isLoading = ref(false);
const bookings = ref([]);
const error = ref(null);

const statusMap = {
  pending_confirmation: 'Đang chờ xác nhận',
  confirmed: 'Đã xác nhận',
  cancelled: 'Đã hủy',
  completed: 'Hoàn thành',
};

const getHistoryBooking = async () => {
  let token = localStorage.getItem('BookingAuth') || '';
  const axiosInstance = axios.create({
    headers: { 'Authorization': `Bearer ${token}` }
  });

  try {
    isLoading.value = true;
    const res = await axiosInstance.get(`${apiUrl}/api/booking-history`);
    if (res.data?.status === 'success') {
      bookings.value = res.data.data;
    } else {
      error.value = res.data.message || 'Không thể tải dữ liệu.';
    }
  } catch {
    error.value = 'Lỗi kết nối hoặc chưa có đơn hàng.';
  } finally {
    isLoading.value = false;
  }
};

const cancelBooking = async (bookingId) => {
  if (!confirm('Bạn có chắc muốn hủy đơn này?')) return;
  try {
    const token = localStorage.getItem('BookingAuth') || '';
    const axiosInstanceDel = axios.create({
      headers: { 'Authorization': `Bearer ${token}` }
    });
    await axiosInstanceDel.delete(`${apiUrl}/api/booking-history/${bookingId}`);
    alert('Hủy thành công');
    getHistoryBooking();
  } catch {
    alert('Không thể hủy đơn. Vui lòng thử lại sau.');
  }
};

const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  return new Date(dateString).toLocaleDateString('vi-VN', {
    year: 'numeric', month: '2-digit', day: '2-digit',
  });
};

const formatCurrency = (amount) => {
  return (Number(amount) || 0).toLocaleString('vi-VN', {
    style: 'currency',
    currency: 'VND'
  });
};

const formatStatus = (status) => statusMap[status] || status;
const formatStatusClass = (status) => `bg-${status.replace(/_/g, '-')}`;

const formatPayment = (method) => {
  const map = {
    thanh_toan_qr: 'QR Code',
    thanh_toan_tien_mat: 'Tiền mặt',
    thanh_toan_the: 'Thẻ',
  };
  return map[method] || 'Không rõ';
};

const formatPaymentStatus = (status) => {
  const map = {
    pending: 'Chưa thanh toán',
    paid: 'Đã thanh toán',
  };
  return map[status] || 'Không rõ';
};

const canCancelBooking = (booking) => {
  if (booking.status !== 'pending_confirmation') return false;
  const checkInDate = new Date(booking.check_in_date);
  const now = new Date();
  checkInDate.setDate(checkInDate.getDate() - 1);
  return now < checkInDate;
};

onMounted(getHistoryBooking);
</script>

<style scoped>
.bg-confirmed {
  background-color: #0d6efd;
  color: #fff;
}
.bg-cancelled {
  background-color: #dc3545;
  color: #fff;
}
.bg-completed {
  background-color: #198754;
  color: #fff;
}
.bg-pending-confirmation {
  background-color: #ffc107;
  color: #000;
}
.card {
  transition: box-shadow 0.3s;
}
.card:hover {
  box-shadow: 0 0 12px rgba(0, 0, 0, 0.12);
}
</style>
