<template>
  <div class="container py-5">
    <div class="text-center mb-5">
      <h2 class="fw-bold text-uppercase border-bottom pb-2 d-inline-block border-3 border-primary">
        Lịch sử Đặt phòng
      </h2>
    </div>

    <Loading v-if="isLoading" />

    <div v-else-if="error" class="alert alert-danger text-center">
      {{ error }}
    </div>

    <div v-else-if="bookings.length === 0" class="alert alert-info text-center">
      <p class="mb-1">Bạn chưa có lịch sử đặt phòng nào.</p>
      <small class="text-muted fst-italic">Hãy khám phá và đặt phòng ngay hôm nay để có một kỳ nghỉ tuyệt vời!</small>
    </div>

    <div v-else class="row g-4 justify-content-center">
      <div
        v-for="booking in bookings"
        :key="booking.booking_id"
        class="col-12 col-md-10 col-lg-8"
      >
        <div class="card shadow-sm border-0 rounded-4 p-4">
          <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
            <h5 class="mb-0">Mã đặt phòng: #{{ booking.booking_id }}</h5>
            <span :class="['badge px-3 py-2', formatStatusClass(booking.status)]">
              {{ formatStatus(booking.status) }}
            </span>
          </div>

          <div class="mb-2">
            <strong>Ngày nhận phòng:</strong>
            <span class="ms-1">{{ formatDate(booking.check_in_date) }}</span>
            <span class="mx-2">|</span>
            <strong>Ngày trả phòng:</strong>
            <span class="ms-1">{{ formatDate(booking.check_out_date) }}</span>
          </div>

          <div v-if="booking.room_type_info">
            <div class="mb-2">
              <strong>Loại phòng:</strong>
              <span class="ms-1">{{ booking.room_type_info.type_name }}</span>
              <span class="mx-2">|</span>
              <strong>Sức chứa:</strong>
              <span class="ms-1">{{ booking.room_type_info.max_occupancy }} người</span>
            </div>
            <div class="mb-2">
              <strong>Số lượng phòng:</strong>
              <span class="ms-1">{{ booking.total_rooms }}</span>
              <span class="mx-2">|</span>
              <strong>Diện tích:</strong>
              <span class="ms-1">{{ booking.room_type_info.m2 }} m²</span>
            </div>
            <div class="mb-2" v-if="booking.room_type_info.description">
              <strong>Mô tả:</strong>
              <span class="ms-1">{{ booking.room_type_info.description }}</span>
            </div>
          </div>
          <div v-else class="mb-2">
            <strong>Loại phòng:</strong> <span>Đang cập nhật...</span>
          </div>

          <div class="mb-2">
            <strong>Tổng tiền:</strong>
            <span class="ms-1">{{ formatCurrency(booking.total_price) }}</span>
            <span class="mx-2">|</span>
            <strong>Phương thức:</strong>
            <span class="ms-1">{{ formatPaymentMethod(booking.payment_method) }}</span>
          </div>

          <div v-if="booking.note && booking.note !== 'Không có ghi chú'">
            <strong>Ghi chú:</strong>
            <span class="ms-1">{{ booking.note }}</span>
          </div>
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

const paymentMethodsMap = {
  'thanh toan qr': 'Thanh toán QR',
  'cash': 'Tiền mặt',
  'credit_card': 'Thẻ tín dụng',
  'bank_transfer': 'Chuyển khoản ngân hàng',
};

const statusMap = {
  'pending_confirmation': 'Đang chờ xác nhận',
  'pending': 'Đang chờ',
  'confirmed': 'Đã xác nhận',
  'cancelled': 'Đã hủy',
  'completed': 'Hoàn thành',
};

const getHistoryBooking = async () => {
  let token = localStorage.getItem('BookingAuth') || '';
  const axiosInstance = axios.create({
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`,
    }
  });

  try {
    isLoading.value = true;
    error.value = null;

    const response = await axiosInstance.get(`${apiUrl}/api/booking-history`);

    if (response.data && response.data.status === 'success') {
      bookings.value = response.data.data;
    } else {
      error.value = response.data.message || 'Không thể tải dữ liệu lịch sử đặt phòng.';
      bookings.value = [];
    }
  } catch (err) {
    console.error('Error fetching booking history:', err);
    if (err.response) {
      if (err.response.status === 401) {
        error.value = 'Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.';
      } else if (err.response.data && err.response.data.message) {
        error.value = 'Bạn chưa có đơn hàng nào.';
      } else {
        error.value = 'Bạn chưa có đơn hàng nào.';
      }
    } else if (err.request) {
      error.value = 'Không thể kết nối đến máy chủ. Vui lòng kiểm tra kết nối mạng của bạn.';
    } else {
      error.value = 'Bạn chưa có đơn hàng nào.';
    }
    bookings.value = [];
  } finally {
    isLoading.value = false;
  }
};

const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  try {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('vi-VN', options);
  } catch (e) {
    console.error('Error formatting date:', e);
    return dateString;
  }
};

const formatCurrency = (amount) => {
  if (amount === null || amount === undefined) return '0 VNĐ';
  try {
    return parseFloat(amount).toLocaleString('vi-VN', { style: 'currency', currency: 'VND' });
  } catch (e) {
    console.error('Error formatting currency:', e);
    return `${amount} VNĐ`;
  }
};

const formatStatus = (status) => {
  return statusMap[status] || status.replace(/_/g, ' ');
};

const formatStatusClass = (status) => {
  return `bg-${status.replace(/_/g, '-')}`;
};

const formatPaymentMethod = (method) => {
  return paymentMethodsMap[method] || method.replace(/_/g, ' ');
};

onMounted(() => {
  getHistoryBooking();
});
</script>

<style scoped>
.bg-pending-confirmation, .bg-pending {
  background-color: #ffc107;
  color: #212529;
}
.bg-confirmed {
  background-color: #0d6efd;
}
.bg-cancelled {
  background-color: #dc3545;
}
.bg-completed {
  background-color: #198754;
}
</style>
