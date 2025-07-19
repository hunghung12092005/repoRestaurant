<template>
  <div class="container py-5">
    <div class="text-center mb-5">
      <h2 class="fw-bold text-uppercase border-bottom pb-3 d-inline-block border-3 border-primary animate__animated animate__fadeInDown">
        Lịch sử Đặt phòng
      </h2>
    </div>

    <div v-if="showCancelPopup" class="modal fade show d-block animate__animated animate__fadeIn" tabindex="-1" role="dialog" aria-labelledby="cancelModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content animate__animated animate__zoomIn">
          <div class="modal-header bg-danger text-white">
            <h5 class="modal-title" id="cancelModalLabel"><i class="bi bi-exclamation-triangle-fill me-2"></i> Xác nhận hủy đặt phòng</h5>
            <button type="button" class="btn-close btn-close-white" @click="showCancelPopup = false" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p class="text-muted mb-3">Vui lòng chọn lý do hủy đặt phòng của bạn:</p>
            <div class="mb-3">
              <label for="reason" class="form-label fw-bold">Lý do hủy:</label>
              <select v-model="selectedReason" class="form-select form-select-lg" id="reason">
                <option value="">-- Chọn lý do --</option>
                <option value="Không còn nhu cầu">Không còn nhu cầu</option>
                <option value="Lịch trình thay đổi">Lịch trình thay đổi</option>
                <option value="Giá không hợp lý">Giá không hợp lý</option>
                <option value="Khác">Khác</option>
              </select>
            </div>

            <div v-if="selectedReason === 'Khác'" class="mb-3">
              <label for="customReason" class="form-label fw-bold">Lý do khác:</label>
              <textarea v-model="customReason" class="form-control" rows="3" placeholder="Nhập lý do hủy chi tiết..." id="customReason"></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="showCancelPopup = false">Đóng</button>
            <button type="button" class="btn btn-danger" @click="confirmCancellation">Xác nhận hủy</button>
          </div>
        </div>
      </div>
    </div>

    <Loading v-if="isLoading" />

    <div v-else-if="error" class="alert alert-danger text-center shadow-sm py-4 animate__animated animate__fadeIn" role="alert">
      <i class="bi bi-exclamation-octagon-fill me-2"></i> {{ error }}
    </div>

    <div v-else-if="bookings.length === 0" class="alert alert-info text-center shadow-sm py-4 animate__animated animate__fadeIn" role="alert">
      <p class="lead mb-3"><i class="bi bi-info-circle-fill me-2"></i> Bạn chưa có lịch sử đặt phòng nào.</p>
      <p class="mb-0">Hãy khám phá các phòng của chúng tôi để bắt đầu đặt phòng ngay!</p>
      <router-link to="/rooms" class="btn btn-primary mt-3 animate__animated animate__pulse animate__infinite">
        <i class="bi bi-house-door-fill me-2"></i> Xem phòng ngay
      </router-link>
    </div>

    <div v-else class="row g-4 justify-content-center">
      <div v-for="booking in bookings" :key="booking.booking_id" class="col-12 col-md-10 col-lg-8 animate__animated animate__fadeInUp">
        
        <div v-if="booking.status === 'pending_cancel'" class="card shadow-lg border-warning border-4 rounded-4 p-4 position-relative overflow-hidden">
          <div class="ribbon-warning"><span>Đang chờ hủy</span></div>
          
          <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom border-warning-subtle">
            <h4 class="mb-0 text-warning fw-bold">
              <i class="bi bi-hourglass-split me-2"></i> Đơn hủy: #{{ booking.booking_id }}
            </h4>
          </div>

          <div v-if="cancelDetails[booking.booking_id]">
            <div class="row g-2 mb-3">
              <div class="col-12">
                <p class="mb-1 text-muted"><strong><i class="bi bi-chat-left-text-fill me-2"></i>Lý do hủy:</strong> <span class="fw-semibold text-dark">{{ cancelDetails[booking.booking_id].reason }}</span></p>
              </div>
              <div class="col-12">
                <p class="mb-1 text-muted"><strong><i class="bi bi-currency-dollar me-2"></i>Hoàn lại cho khách:</strong> <span class="text-success fw-bold fs-5">{{ formatPrice(cancelDetails[booking.booking_id].refund_amount) }}</span></p>
              </div>
              <div class="col-12">
                <p class="mb-0 text-muted"><strong><i class="bi bi-calendar-event me-2"></i>Thời gian yêu cầu:</strong> <span class="fw-semibold text-dark">{{ formatDate(cancelDetails[booking.booking_id].created_at) }}</span></p>
              </div>
            </div>
          </div>
          <div v-else class="text-muted fst-italic py-3 text-center">
            <div class="spinner-border text-warning spinner-border-sm me-2" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
            Đang tải thông tin hủy...
          </div>

          <div class="mt-4 pt-3 border-top border-warning-subtle" v-if="cancelDetails[booking.booking_id]?.refund_amount > 0">
            <h5 class="fw-bold text-primary mb-3"><i class="bi bi-bank me-2"></i> Thông tin hoàn tiền:</h5>
            <p class="text-muted small mb-3">Vui lòng nhập chính xác thông tin ngân hàng để quá trình hoàn tiền được thuận lợi.</p>

            <template v-if="cancelDetails[booking.booking_id]?.refund_bank">
              <div class="card bg-light p-3 mb-3">
                <div class="row mb-2">
                  <div class="col-md-4 text-muted">Ngân hàng:</div>
                  <div class="col-md-8 fw-semibold">{{ cancelDetails[booking.booking_id].refund_bank }}</div>
                </div>
                <div class="row mb-2">
                  <div class="col-md-4 text-muted">Số tài khoản:</div>
                  <div class="col-md-8 fw-semibold">{{ cancelDetails[booking.booking_id].refund_account_number }}</div>
                </div>
                <div class="row">
                  <div class="col-md-4 text-muted">Tên tài khoản:</div>
                  <div class="col-md-8 fw-semibold">{{ cancelDetails[booking.booking_id].refund_account_name }}</div>
                </div>
              </div>
              <div class="alert alert-success mt-3 py-2 text-center animate__animated animate__fadeIn">
                <i class="bi bi-check-circle-fill me-2"></i> Thông tin hoàn tiền đã được gửi thành công. Chúng tôi sẽ xử lý sớm nhất có thể.
              </div>
            </template>

            <template v-else>
              <div class="mb-3">
                <label class="form-label fw-bold">Chọn ngân hàng:</label>
                <select class="form-select form-select-lg" v-model="refundBank[booking.booking_id].bank">
                  <option value="" disabled>-- Chọn ngân hàng --</option>
                  <option v-for="bank in banks" :key="bank.code" :value="bank.name">
                    {{ bank.short_name }} - {{ bank.name }}
                  </option>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label fw-bold">Số tài khoản:</label>
                <input type="text" class="form-control form-control-lg" v-model="refundBank[booking.booking_id].accountNumber" placeholder="Nhập số tài khoản">
              </div>
              <div class="mb-3">
                <label class="form-label fw-bold">Tên tài khoản (Chủ tài khoản):</label>
                <input type="text" class="form-control form-control-lg" v-model="refundBank[booking.booking_id].accountName" placeholder="Nhập tên chủ tài khoản">
              </div>
              <div class="d-grid">
                <button class="btn btn-primary btn-lg mt-3" @click="submitRefundInfo(booking.booking_id)">
                  <i class="bi bi-send-fill me-2"></i> Gửi thông tin hoàn tiền
                </button>
              </div>
            </template>
          </div>
        </div>

        <div v-else class="card shadow-lg border-0 rounded-4 p-4 position-relative overflow-hidden">
          <div class="mb-4 pb-3 border-bottom">
            <h4 class="mb-1">Mã đặt phòng: <strong class="text-primary">#{{ booking.booking_id }}</strong></h4>
            <small class="text-muted">Mã đơn hàng: {{ booking.orderCode || 'Không có' }}</small>
          </div>

          <div class="row g-3 mb-4">
            <div class="col-sm-6">
              <p class="mb-0 text-muted"><i class="bi bi-calendar-check me-2"></i> <strong>Ngày nhận phòng:</strong></p>
              <p class="mb-0 fs-5 text-primary fw-semibold">{{ formatDate(booking.check_in_date) }}</p>
            </div>
            <div class="col-sm-6">
              <p class="mb-0 text-muted"><i class="bi bi-calendar-x me-2"></i> <strong>Ngày trả phòng:</strong></p>
              <p class="mb-0 fs-5 text-primary fw-semibold">{{ formatDate(booking.check_out_date) }}</p>
            </div>
            <div class="col-sm-6" v-if="booking.room_type_info">
              <p class="mb-0 text-muted"><i class="bi bi-door-open me-2"></i> <strong>Loại phòng:</strong></p>
              <p class="mb-0 fw-semibold">{{ booking.room_type_info.type_name }}</p>
            </div>
            <div class="col-sm-6" v-if="booking.room_type_info">
              <p class="mb-0 text-muted"><i class="bi bi-people me-2"></i> <strong>Sức chứa:</strong></p>
              <p class="mb-0 fw-semibold">{{ booking.room_type_info.max_occupancy }} người</p>
            </div>
            <div class="col-sm-6">
              <p class="mb-0 text-muted"><i class="bi bi-grid-fill me-2"></i> <strong>Số lượng phòng:</strong></p>
              <p class="mb-0 fw-semibold">{{ booking.total_rooms }}</p>
            </div>
            <div class="col-sm-6">
              <p class="mb-0 text-muted"><i class="bi bi-bookmark me-2"></i> <strong>Loại đặt:</strong></p>
              <p class="mb-0 fw-semibold">{{ booking.booking_type === 'online' ? 'Đặt online' : booking.booking_type }}</p>
            </div>
          </div>

          <hr class="my-4">

          <div class="row g-3 mb-4">
           
            <div class="col-sm-6">
              <p class="mb-0 text-muted"><i class="bi bi-cash-stack me-2"></i> <strong>Hình thức thanh toán:</strong></p>
              <p class="mb-0 fw-bold">{{ formatPayment(booking.payment_method) }}</p>
            </div>
            <div class="col-sm-6">
              <p class="mb-0 text-muted"><i class="bi bi-credit-card-fill me-2"></i> <strong>Trạng thái thanh toán:</strong></p>
              <p :class="['mb-0 fw-bold', booking.payment_status === 'paid' ? 'text-success' : 'text-danger']">{{ formatPaymentStatus(booking.payment_status) }}</p>
            </div>
            <div class="col-12">
               <span :class="['badge position-absolute top-0 end-0 mt-3 me-3 px-3 py-2 rounded-pill fw-bold', formatStatusClass(booking.status)]">
            {{ formatStatus(booking.status) }}
          </span>
            </div>
            <div class="col-12">
              <p class="mb-0 text-muted"><i class="bi bi-wallet-fill me-2"></i> <strong>Tổng tiền:</strong></p>
              <p class="mb-0 fs-4 text-success fw-bold">{{ formatCurrency(booking.total_price) }}</p>
            </div>
          </div>

          <div v-if="booking.note" class="mb-4 p-3 bg-light rounded-3 border border-light-subtle">
            <p class="mb-0 text-muted"><i class="bi bi-pencil-square me-2"></i> <strong>Ghi chú:</strong></p>
            <p class="mb-0 fst-italic text-dark">{{ booking.note }}</p>
          </div>

          <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
            <button v-if="canCancelBooking(booking)" class="btn btn-outline-danger btn-lg-sm py-2 px-3 animate__animated animate__pulse animate__infinite" @click="showCancelPopup = true; currentBookingId = booking.booking_id">
              <i class="bi bi-x-circle-fill me-2"></i> Hủy đặt phòng
            </button>
            <small class="text-muted fst-italic ms-auto"><i class="bi bi-arrow-clockwise me-2"></i>Cập nhật cuối: {{ formatDate(booking.updated_at) }}</small>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Base Styles */
/* Status Colors */
.bg-pending-confirmation {
  background-color: #0cd4e6 !important; /* Warning yellow */
  color: #343a40 !important; /* Dark text for contrast */
}
.bg-confirmed {
  background-color: #28a745 !important; /* Success green */
  color: #ffffff !important;
}
.bg-cancelled {
  background-color: #dc3545 !important; /* Danger red */
  color: #ffffff !important;
}
.bg-completed {
  background-color: #17a2b8 !important; /* Info blue */
  color: #ffffff !important;
}
.bg-new, .bg-pending-payment {
  background-color: #0d6efd !important; /* Primary blue for new/pending payment */
  color: #ffffff !important;
}
.container {
  max-width: 1200px;
  margin: 0 auto;
}

.border-bottom {
  border-color: rgba(0, 0, 0, 0.1) !important;
}

.text-uppercase {
  letter-spacing: 1px;
}

/* Card Enhancements */
.card {
  border: none; /* Remove default card border */
  border-radius: 1rem; /* More rounded corners */
  overflow: hidden; /* Ensures ribbon stays within bounds */
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  background-color: #ffffff; /* Ensure white background for cards */
}

.card:hover {
  transform: translateY(-8px); /* Slightly more pronounced lift on hover */
  box-shadow: 0 1rem 2rem rgba(0, 0, 0, 0.1); /* Stronger shadow on hover */
}

.card-header-custom {
  background-color: #f8f9fa;
  border-bottom: 1px solid #e9ecef;
  padding-bottom: 1.25rem;
  margin-bottom: 1.5rem;
}

/* Badges - More distinct colors and styling */
.badge {
  font-size: 0.9rem;
  padding: 0.5em 0.9em;
  border-radius: 50rem; /* Pill shape */
  min-width: 90px; /* Ensure consistent width for badges */
  text-align: center;
}

.badge.bg-pending_confirmation {
  background-color: #ffc107 !important; /* Warning yellow */
  color: #343a40 !important; /* Dark text for contrast */
}
.badge.bg-confirmed {
  background-color: #28a745 !important; /* Success green */
  color: #ffffff !important;
}
.badge.bg-cancelled {
  background-color: #dc3545 !important; /* Danger red */
  color: #ffffff !important;
}
.badge.bg-completed {
  background-color: #17a2b8 !important; /* Info blue */
  color: #ffffff !important;
}
.badge.bg-new, .badge.bg-pending_payment {
  background-color: #0d6efd !important; /* Primary blue for new/pending payment */
  color: #ffffff !important;
}

/* Ribbon for pending_cancel */
.ribbon-warning {
  position: absolute;
  top: 0;
  right: 0;
  width: 150px;
  height: 150px;
  overflow: hidden;
  z-index: 10;
}

.ribbon-warning::before,
.ribbon-warning::after {
  position: absolute;
  content: '';
  display: block;
  border: 5px solid #f0ad4e;
  border-top-color: transparent;
  border-right-color: transparent;
}

.ribbon-warning::before {
  top: 0;
  left: 0;
}

.ribbon-warning::after {
  bottom: 0;
  right: 0;
}

.ribbon-warning span {
  position: absolute;
  display: block;
  width: 225px;
  padding: 8px 0;
  background-color: #f0ad4e;
  box-shadow: 0 5px 10px rgba(0,0,0,.1);
  color: #343a40;
  font-size: 0.9rem;
  font-weight: bold;
  text-align: center;
  text-transform: uppercase;
  right: -45px;
  top: 45px;
  transform: rotate(45deg);
}


/* Modal Overlays and Animations */
.modal.show {
  background-color: rgba(0, 0, 0, 0.6); /* Darker overlay */
  animation: fadeIn 0.3s ease-out; /* Fade in animation for modal backdrop */
}

.modal-content {
  border-radius: 0.75rem; /* Rounded corners for modal */
  overflow: hidden; /* Ensures internal elements conform to border-radius */
}

.modal-header {
  border-bottom: none; /* Clean header */
  padding: 1.25rem 1.5rem;
}

.modal-title {
  font-weight: bold;
}

.modal-body {
  padding: 1.5rem;
}

.modal-footer {
  border-top: none; /* Clean footer */
  padding: 1.25rem 1.5rem;
}

/* Form Control Focus */
.form-control:focus, .form-select:focus {
  border-color: #86b7fe;
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

/* Button enhancements */
.btn {
  font-weight: 500;
  border-radius: 0.5rem;
  transition: all 0.2s ease-in-out;
}

.btn-primary {
  background-color: #0d6efd;
  border-color: #0d6efd;
}
.btn-primary:hover {
  background-color: #0b5ed7;
  border-color: #0a58ca;
}

.btn-outline-danger {
  color: #dc3545;
  border-color: #dc3545;
}
.btn-outline-danger:hover {
  background-color: #dc3545;
  color: #fff;
}

/* Utility Classes */
.fw-bold { font-weight: 700 !important; }
.fw-semibold { font-weight: 600 !important; }

/* Icons */
.bi {
  vertical-align: -0.125em; /* Align icons better with text */
}

/* Animations */
@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes zoomIn {
  from { transform: scale(0.9); opacity: 0; }
  to { transform: scale(1); opacity: 1; }
}

@keyframes pulse {
  0% { transform: scale(1); }
  50% { transform: scale(1.03); }
  100% { transform: scale(1); }
}

.animate__animated.animate__fadeInDown {
  animation-duration: 0.8s;
}
.animate__animated.animate__fadeIn, .animate__animated.animate__fadeInUp {
  animation-duration: 0.6s;
}
.animate__animated.animate__zoomIn {
  animation-duration: 0.4s;
}
.animate__animated.animate__pulse {
  animation-duration: 1.5s;
}

</style>
<script setup>
import { inject, onMounted, ref, computed } from 'vue';
import axios from 'axios';
import Loading from '../loading.vue';

const apiUrl = inject('apiUrl');
const isLoading = ref(false);
const bookings = ref([]);
const error = ref(null);
const cancelDetails = ref({});
const refundBank = ref({}); // Lưu thông tin hoàn tiền nhập từ form
//format price
const formatPrice = (value) => {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND',
    }).format(value);
};
const getHistoryBooking = async () => {
  let token = localStorage.getItem('BookingAuth') || '';
  const axiosInstance = axios.create({
    headers: { 'Authorization': `Bearer ${token}` }
  });

  try {
    isLoading.value = true;
    const res = await axiosInstance.get(`${apiUrl}/api/booking-history`);
    console.log('Lịch sử đặt phòng:', res.data);
    if (res.data?.status === 'success') {
      bookings.value = res.data.data;
      // Lấy chi tiết huỷ cho các đơn đang pending_cancel
      for (const b of res.data.data) {
        if (b.status === 'pending_cancel') {
          await getCancelBookingDetail(b.booking_id);
          refundBank.value[b.booking_id] = { bank: '', accountNumber: '' };
        }
      }
    } else {
      error.value = res.data.message || 'Không thể tải dữ liệu.';
    }
  } catch {
    error.value = 'Lỗi kết nối hoặc chưa có đơn hàng.';
  } finally {
    isLoading.value = false;
  }
};

const getCancelBookingDetail = async (bookingId) => {
  try {
    const token = localStorage.getItem('BookingAuth') || '';
    const axiosInstance = axios.create({
      headers: { 'Authorization': `Bearer ${token}` }
    });
    const res = await axiosInstance.get(`${apiUrl}/api/cancel-booking/${bookingId}`);
    if (res.data?.status === 'success') {
      cancelDetails.value[bookingId] = res.data.data;
      await loadBankList();
      console.log(`Thông tin hủy cho booking ${bookingId}:`, cancelDetails.value[bookingId]);
    }
  } catch (e) {
    console.error(`Không thể lấy thông tin hủy cho booking ${bookingId}`, e);
  }
};
const banks = ref([]);

const loadBankList = async () => {
  if (banks.value.length > 0) return; // ✅ Đã tải thì không tải lại

  try {
    const response = await axios.get('https://api.vietqr.io/v2/banks');
    if (response.data.code === '00') {
      banks.value = response.data.data;
    }
  } catch (error) {
    console.error('Lỗi tải danh sách ngân hàng:', error);
  }
};

const submitRefundInfo = async (bookingId) => {
  const info = refundBank.value[bookingId];

  if (!info.bank || !info.accountNumber || !info.accountName) {
    alert('Vui lòng nhập đầy đủ: ngân hàng, số tài khoản và tên chủ tài khoản.');
    return;
  }

  try {
    // const token = localStorage.getItem('BookingAuth') || '';
    // const axiosInstance = axios.create({
    //   headers: { Authorization: `Bearer ${token}` }
    // });

    const response = await axios.post(`${apiUrl}/api/cancel-booking/${bookingId}/bank-info`, {
      refund_bank: info.bank,
      refund_account_number: info.accountNumber,
      refund_account_name: info.accountName
    });

    if (response.data.status === 'success') {
      //alert('Cập nhật thông tin hoàn tiền thành công!');
      getCancelBookingDetail(bookingId); // Cập nhật lại thông tin hủy
    } else {
      alert('Có lỗi xảy ra: ' + response.data.message);
    }
  } catch (err) {
    console.error('Lỗi gửi thông tin hoàn tiền:', err);
    alert('Không thể gửi thông tin. Vui lòng thử lại.');
  }
};

const showCancelPopup = ref(false);
const selectedReason = ref('');
const customReason = ref('');

// Đây là lý do thật sự sẽ gửi
const cancellationReason = computed(() =>
  selectedReason.value === 'Khác' ? customReason.value : selectedReason.value
);
const currentBookingId = ref(null);

const confirmCancellation = async () => {
  if (!cancellationReason.value) {
    alert('Vui lòng chọn lý do hủy.');
    return;
  }

  try {
    const token = localStorage.getItem('BookingAuth') || '';
    const axiosInstance = axios.create({
      headers: { 'Authorization': `Bearer ${token}` }
    });

    await axiosInstance.delete(`${apiUrl}/api/booking-history/${currentBookingId.value}`, {
      data: { cancellation_reason: cancellationReason.value }
    });

    alert('Hủy thành công');
    showCancelPopup.value = false; // Đóng popup
    cancellationReason.value = ''; // Reset lý do
    getHistoryBooking(); // Cập nhật lịch sử đặt phòng
  } catch (error) {
    alert('Không thể hủy đơn. Vui lòng thử lại sau.');
    console.error('Error cancelling booking:', error);
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

const statusMap = {
  pending_confirmation: 'Đang chờ xác nhận',
  confirmed: 'Đã xác nhận',
  cancelled: 'Đã hủy',
  pending_cancel: 'Đang chờ hủy',
  completed: 'Hoàn thành',
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
