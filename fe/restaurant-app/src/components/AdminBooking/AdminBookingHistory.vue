<template>
  <div class="page-container">
    <div class="page-header mb-4">
      <h1 class="page-title">Lịch Sử Sử Dụng Phòng</h1>
      <p class="page-subtitle">Tra cứu và xem lại thông tin các đơn đặt phòng của khách hàng.</p>
    </div>

    <!-- Bộ lọc và tìm kiếm -->
    <div class="card filter-card mb-5">
      <div class="card-body">
        <div class="row g-3 align-items-end">
          <div class="col-lg-4 col-md-12">
            <label for="search-input" class="form-label">Tìm kiếm khách hàng</label>
            <input
              id="search-input"
              v-model="filters.searchKeyword"
              type="text"
              class="form-control"
              placeholder="Nhập tên, SĐT hoặc CCCD..."
              @keyup.enter="applyFiltersAndFetch"
              :disabled="isLoading"
            />
          </div>
          <div class="col-lg-3 col-md-5">
            <label for="filter-date" class="form-label">Lọc theo ngày</label>
            <input
              id="filter-date"
              type="date"
              v-model="filters.selectedDate"
              class="form-control"
              :disabled="isLoading"
            />
          </div>
          <div class="col-lg-3 col-md-4">
            <button @click="applyFiltersAndFetch" class="btn btn-primary w-100" :disabled="isLoading">
              <span v-if="isLoading" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
              <i v-else class="bi bi-search me-2"></i>
              Tìm Kiếm
            </button>
          </div>
          <div class="col-lg-2 col-md-3">
            <button @click="clearFilters" class="btn btn-outline-secondary w-100" :disabled="isLoading">
              <i class="bi bi-arrow-clockwise me-2"></i>
              Xóa lọc
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Vùng hiển thị kết quả -->
    <div class="results-container">
      <div v-if="isLoading" class="loading-state">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-3">Đang tải dữ liệu, vui lòng chờ...</p>
      </div>

      <div v-else-if="fetchError" class="error-state">
        <i class="bi bi-exclamation-triangle-fill error-icon"></i>
        <h4 class="mt-3">Rất tiếc, đã có lỗi xảy ra</h4>
        <p class="text-muted">{{ fetchError }}</p>
        <button @click="fetchBookings(1)" class="btn btn-primary mt-3">
          <i class="bi bi-arrow-repeat me-2"></i>Thử lại
        </button>
      </div>

      <div v-else-if="allBookings.length === 0" class="empty-state">
        <i class="bi bi-journal-x empty-icon"></i>
        <h4 class="mt-3">Không tìm thấy lịch sử</h4>
        <p class="text-muted">Không có lịch sử đặt phòng nào phù hợp với tiêu chí tìm kiếm của bạn.</p>
      </div>

      <div v-else class="booking-list">
        <div
          class="booking-list-item"
          v-for="booking in allBookings"
          :key="booking.booking_id"
          :class="{ 'is-cancelled': booking.status === 'cancelled' }"
        >
          <div class="row align-items-center">
            <div class="col-lg-3 col-md-12">
              <div class="fw-bold text-dark">{{ booking.customer_name }}</div>
              <div class="text-muted small">{{ booking.customer_phone }}</div>
            </div>
            <div class="col-lg-3 col-md-6">
              <div class="fw-medium">Mã Đơn: HXH{{ booking.booking_id }}</div>
              <div class="text-muted small">
                Ngày đặt: {{ formatDate(booking.check_in_date, true) }} | {{ booking.used_rooms.length }} phòng
              </div>
            </div>
            <div class="col-lg-2 col-md-6 mt-2 mt-lg-0">
              <span class="badge" :class="statusClass(booking.status)">{{ booking.status_display }}</span>
            </div>
            <div class="col-lg-2 col-md-6 mt-2 mt-lg-0">
              <div class="fw-bold text-primary fs-6">{{ formatCurrency(booking.total_price) }}</div>
            </div>
            <div class="col-lg-2 col-12 mt-3 mt-lg-0 text-lg-end">
              <button class="btn btn-sm btn-outline-primary" @click="showDetails(booking)">
                <i class="bi bi-eye me-1"></i>Xem Chi Tiết
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Phân trang -->
    <nav v-if="pagination.totalPages > 1 && !isLoading" aria-label="Page navigation" class="mt-5">
      <ul class="pagination justify-content-center">
        <li class="page-item" :class="{ disabled: pagination.currentPage === 1 }">
          <a class="page-link" href="#" @click.prevent="changePage(1)">««</a>
        </li>
        <li class="page-item" :class="{ disabled: pagination.currentPage === 1 }">
          <a class="page-link" href="#" @click.prevent="changePage(pagination.currentPage - 1)">«</a>
        </li>
        <li
          v-for="page in pageRange"
          :key="page"
          class="page-item"
          :class="{ active: page === pagination.currentPage }"
        >
          <a class="page-link" href="#" @click.prevent="changePage(page)">{{ page }}</a>
        </li>
        <li class="page-item" :class="{ disabled: pagination.currentPage === pagination.totalPages }">
          <a class="page-link" href="#" @click.prevent="changePage(pagination.currentPage + 1)">»</a>
        </li>
        <li class="page-item" :class="{ disabled: pagination.currentPage === pagination.totalPages }">
          <a class="page-link" href="#" @click.prevent="changePage(pagination.totalPages)">»»</a>
        </li>
      </ul>
    </nav>
  </div>

  <!-- Modal Chi Tiết -->
  <div v-if="selectedBooking" class="modal-backdrop" @click="closeDetails">
    <div class="modal-dialog-scrollable modal-lg modal-content" @click.stop>
      <div class="modal-header">
        <h5 class="modal-title">Chi Tiết Đơn Đặt Phòng - HXH{{ selectedBooking.booking_id }}</h5>
        <button type="button" class="btn-close" @click="closeDetails" aria-label="Đóng"></button>
      </div>
      <div class="modal-body">
        <!-- Thông tin chung -->
        <div class="row g-4">
          <div class="col-lg-6">
            <div class="detail-section">
              <h6 class="section-title"><i class="bi bi-person-circle"></i> Thông Tin Khách Hàng</h6>
              <ul class="info-list">
                <li><span>Họ tên:</span> <strong>{{ selectedBooking.customer_name }}</strong></li>
                <li><span>Số điện thoại:</span> <strong>{{ selectedBooking.customer_phone }}</strong></li>
                <li><span>Email:</span> {{ selectedBooking.customer_email || 'Chưa cung cấp' }}</li>
                <li><span>CCCD/Passport:</span> {{ selectedBooking.customer_id_number || 'Chưa cung cấp' }}</li>
              </ul>
            </div>
          </div>
          <div class="col-lg-6">
            <div class="detail-section">
              <h6 class="section-title"><i class="bi bi-wallet2"></i> Thông Tin Thanh Toán</h6>
              <ul class="info-list">
                <li><span>Trạng thái đơn:</span> <span class="badge" :class="statusClass(selectedBooking.status)">{{ selectedBooking.status_display }}</span></li>
                <li><span>Thanh toán:</span> <span class="badge" :class="paymentStatusClass(selectedBooking.payment_status)">{{ selectedBooking.payment_status_display }}</span></li>
                <li><span>Phương thức:</span> <strong>{{ selectedBooking.payment_method_display }}</strong></li>
                <li class="total"><span>Tổng Tiền Đơn:</span> <strong>{{ formatCurrency(selectedBooking.total_price) }}</strong></li>
              </ul>
            </div>
          </div>
        </div>

        <!-- Chi tiết các phòng -->
        <div class="detail-section mt-4">
          <h6 class="section-title">
            <i class="bi bi-door-open-fill"></i> Chi Tiết Các Phòng Đã Sử Dụng ({{ selectedBooking.used_rooms.length }})
          </h6>
          <div class="room-list-container">
            <div v-for="room in selectedBooking.used_rooms" :key="room.status_id" class="room-card">
              <div class="room-card-header">
                <div class="room-name">{{ room.room_name }} <small>({{ room.type_name }})</small></div>
                <div class="room-total">{{ formatCurrency(room.total_paid) }}</div>
              </div>
              <div class="room-card-body">
                <div class="body-section">
                  <p class="section-label"><i class="bi bi-clock-history"></i> Thời gian</p>
                  <div class="info-line1"><span>Nhận phòng:</span> <br><span>{{ formatDate(room.check_in) }}</span></div>
                  <div class="info-line1"><span>Trả phòng:</span> <br><span>{{ formatDate(room.check_out) }}</span></div>
                </div>
                <div class="body-section">
                  <p class="section-label"><i class="bi bi-cash-coin"></i> Chi phí</p>
                  <div class="info-line"><span>Tiền phòng:</span> <span>{{ formatCurrency(room.room_price) }}</span></div>
                  <div class="info-line"><span>Tiền dịch vụ:</span> <span>{{ formatCurrency(room.service_price) }}</span></div>
                  <div class="info-line"><span>Phụ phí:</span> <span>{{ formatCurrency(room.surcharge) }}</span></div>
                  <div v-if="room.surcharge > 0" class="surcharge-reason">{{ room.surcharge_reason }}</div>
                </div>
              </div>
              <div v-if="room.used_services && room.used_services.length > 0" class="room-card-footer">
                <p class="section-label"><i class="bi bi-card-checklist"></i> Dịch vụ đã dùng</p>
                <ul>
                  <li v-for="service in room.used_services" :key="service.service_name">
                    <span>{{ service.service_name }} (x{{ service.quantity }})</span>
                    <span>{{ formatCurrency(service.total) }}</span>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" @click="closeDetails">Đóng</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, inject, computed } from 'vue';
import axios from 'axios';

const apiUrl = inject('apiUrl');
const allBookings = ref([]);
const isLoading = ref(true);
const fetchError = ref(null);
const selectedBooking = ref(null);

const getTodayDateString = () => {
  const today = new Date();
  const year = today.getFullYear();
  const month = String(today.getMonth() + 1).padStart(2, '0');
  const day = String(today.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
};

const filters = reactive({
  searchKeyword: '',
  selectedDate: getTodayDateString(),
});

const pagination = reactive({
  currentPage: 1,
  totalPages: 1,
  perPage: 10,
});

const statusDisplayMap = {
  'pending_confirmation': 'Chờ Xác Nhận',
  'confirmed': 'Đã Xác Nhận',
  'cancelled': 'Đã Hủy',
  'completed': 'Hoàn Thành',
  'pending_cancel': 'Chờ Hủy',
  'checked_in': 'Đã Nhận Phòng',
  'checked_out': 'Đã Trả Phòng',
};
const paymentMethodMap = {
  'cash': 'Tiền Mặt',
  'bank_transfer': 'Chuyển Khoản',
  'credit_card': 'Thẻ Tín Dụng',
  'momo': 'Ví Momo',
  'at_hotel': 'Tại Khách Sạn',
  'thanh_toan_ngay': 'Thanh Toán Ngay',
  'thanh_toan_qr': 'Thanh Toán QR',
  'thanh_toan_sau': 'Thanh Toán Sau',
};
const paymentStatusMap = {
  'pending': 'Chưa Thanh Toán',
  'completed': 'Đã Thanh Toán',
  'unpaid': 'Chưa Thanh Toán',
  'partially_paid': 'Thanh Toán Một Phần',
  'refunded': 'Đã Hoàn Tiền',
  'failed': 'Thanh Toán Thất Bại',
  'cancelled': 'Đã Hủy',
};

const formatDate = (dateString, dateOnly = false) => {
  if (!dateString) return 'N/A';
  const options = dateOnly
    ? { year: 'numeric', month: '2-digit', day: '2-digit' }
    : { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' };
  return new Date(dateString).toLocaleString('vi-VN', options);
};

const formatCurrency = (amount) => {
  if (amount === null || amount === undefined) return '0 VNĐ';
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
};

const statusClass = (status) => ({
  'bg-success-light text-success': ['completed', 'checked_out'].includes(status),
  'bg-warning-light text-warning': ['pending_confirmation', 'pending_cancel', 'checked_in'].includes(status),
  'bg-danger-light text-danger': status === 'cancelled',
  'bg-info-light text-info': status === 'confirmed',
  'bg-secondary-light text-secondary': !['completed', 'checked_out', 'pending_confirmation', 'pending_cancel', 'cancelled', 'checked_in', 'confirmed'].includes(status),
});

const paymentStatusClass = (status) => ({
  'bg-success-light text-success': status === 'completed',
  'bg-danger-light text-danger': ['pending', 'unpaid', 'failed'].includes(status),
  'bg-warning-light text-warning': status === 'partially_paid',
  'bg-info-light text-info': status === 'refunded',
  'bg-secondary-light text-secondary': status === 'cancelled',
});

const transformBookingData = (bookingItem) => ({
  ...bookingItem,
  customer_name: bookingItem.customer?.customer_name || 'Khách vãng lai',
  customer_phone: bookingItem.customer?.customer_phone || 'N/A',
  customer_email: bookingItem.customer?.customer_email,
  customer_id_number: bookingItem.customer?.customer_id_number,
  payment_method_display: paymentMethodMap[bookingItem.payment_method] || bookingItem.payment_method || 'N/A',
  payment_status_display: paymentStatusMap[bookingItem.payment_status] || bookingItem.payment_status || 'N/A',
  status_display: statusDisplayMap[bookingItem.status] || bookingItem.status || 'N/A',
});

const showDetails = (booking) => {
  selectedBooking.value = booking;
};

const closeDetails = () => {
  selectedBooking.value = null;
};

const clearFilters = () => {
  filters.searchKeyword = '';
  filters.selectedDate = '';
  fetchBookings(1);
};

const applyFiltersAndFetch = () => {
  fetchBookings(1);
};

const fetchBookings = async (page = 1) => {
  isLoading.value = true;
  fetchError.value = null;
  pagination.currentPage = page;
  try {
    const params = {
      page: pagination.currentPage,
      per_page: pagination.perPage,
    };
    if (filters.selectedDate) {
      params.date = filters.selectedDate;
    }
    if (filters.searchKeyword.trim()) {
      params.search = filters.searchKeyword.trim();
    }
    
    const response = await axios.get(`${apiUrl}/api/booking-histories`, { params });
    allBookings.value = response.data.data.map(transformBookingData);
    pagination.totalPages = response.data.last_page;

  } catch (err) {
    console.error("Lỗi khi tải lịch sử đặt phòng:", err);
    fetchError.value = "Không thể kết nối đến máy chủ hoặc đã có lỗi xảy ra.";
  } finally {
    isLoading.value = false;
  }
};

const pageRange = computed(() => {
  const maxPagesToShow = 5;
  const half = Math.floor(maxPagesToShow / 2);
  let start = Math.max(1, pagination.currentPage - half);
  let end = Math.min(pagination.totalPages, start + maxPagesToShow - 1);
  if (end - start + 1 < maxPagesToShow) {
    start = Math.max(1, end - maxPagesToShow + 1);
  }
  return Array.from({ length: end - start + 1 }, (_, i) => start + i);
});

const changePage = (page) => {
  if (page >= 1 && page <= pagination.totalPages && page !== pagination.currentPage) {
    fetchBookings(page);
  }
};

onMounted(() => {
  fetchBookings();
});
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap');
@import url('https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css');

.page-container {
  font-family: 'Be Vietnam Pro', sans-serif;
  background-color: #f8fafc;
  padding: 2rem;
}
.page-header .page-title {
  font-size: 2rem;
  font-weight: 700;
  color: #1a202c;
}
.page-header .page-subtitle {
  font-size: 1rem;
  color: #718096;
}
.filter-card {
  border-radius: 12px;
}
.btn {
  border-radius: 8px;
  font-weight: 600;
}

.booking-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}
.booking-list-item {
  background-color: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 1.25rem 1.5rem;
  transition: all 0.2s ease-in-out;
  box-shadow: 0 1px 2px rgba(0,0,0,0.04);
  border-left: 4px solid #3182ce;
}
.booking-list-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.06);
}
.booking-list-item.is-cancelled {
  border-left-color: #e53e3e;
}

.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-color: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1050;
  padding: 1rem;
}
.modal-content {
  background-color: #fff;
  border-radius: 12px;
  box-shadow: 0 10px 25px rgba(0,0,0,0.1);
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  width: 100%;
  max-width: 1000px;
}
.modal-header {
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid #e2e8f0;
  flex-shrink: 0;
}
.modal-body {
  background-color: #f9fafb;
  padding: 2rem;
  overflow-y: auto;
  flex-grow: 1;
}
.modal-footer {
  padding: 1rem 1.5rem;
  border-top: 1px solid #e2e8f0;
  background-color: #f8fafb;
  flex-shrink: 0;
}

.detail-section {
  margin-bottom: 1.5rem;
}
.section-title {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1a202c;
  margin-bottom: 1rem;
  padding-bottom: 0.5rem;
  display: flex;
  align-items: center;
  gap: 0.6rem;
  border-bottom: 1px solid #e2e8f0;
}
.info-list {
  list-style: none;
  padding: 0;
  margin: 0;
}
.info-list li {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.6rem 0.25rem;
  border-bottom: 1px dashed #e9ecef;
}
.info-list li:last-child {
  border-bottom: none;
}
.info-list li span:first-child {
  color: #718096;
}
.info-list li.total {
  font-size: 1.15rem;
  font-weight: 600;
}
.info-list li.total strong {
  color: #3182ce;
}

.room-list-container {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
  gap: 1.5rem;
}
.room-card {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.05);
  overflow: hidden;
  display: flex;
  flex-direction: column;
}
.room-card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.25rem;
  background-color: #f8fafc;
}
.room-name {
  font-size: 1.1rem;
  font-weight: 600;
  color: #2d3748;
}
.room-name small {
  font-weight: 400;
  color: #718096;
}
.room-total {
  font-size: 1.2rem;
  font-weight: 700;
  color: #2d3748;
}

.room-card-body {
  padding: 1.25rem;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem 1.5rem;
  flex-grow: 1;
}
.section-label {
  font-weight: 600;
  margin-bottom: 0.75rem;
  color: #4a5568;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.info-line {
  display: flex;
  justify-content: space-between;
  font-size: 0.95rem;
  margin-bottom: 0.5rem;
}
.info-line span:first-child {
  color: #718096;
}
.surcharge-reason {
  font-size: 0.85rem;
  font-style: italic;
  color: #718096;
  text-align: right;
  margin-top: 0.5rem;
}
.room-card-footer {
  padding: 1rem 1.25rem;
  background-color: #fdfdff;
  border-top: 1px dashed #e2e8f0;
}
.room-card-footer ul {
  list-style: none;
  padding: 0;
  margin: 0;
}
.room-card-footer li {
  display: flex;
  justify-content: space-between;
  font-size: 0.9rem;
  padding: 0.25rem 0;
}

.badge {
  font-weight: 600;
  padding: 0.4em 0.8em;
  border-radius: 20px;
  font-size: 0.8rem;
  letter-spacing: 0.5px;
}
.bg-success-light { background-color: rgba(72, 187, 120, 0.15); }
.text-success { color: #2f855a !important; }
.bg-warning-light { background-color: rgba(237, 162, 57, 0.15); }
.text-warning { color: #975a16 !important; }
.bg-danger-light { background-color: rgba(245, 101, 101, 0.15); }
.text-danger { color: #c53030 !important; }
.bg-info-light { background-color: rgba(99, 179, 237, 0.15); }
.text-info { color: #2b6cb0 !important; }
.bg-secondary-light { background-color: rgba(108, 117, 125, 0.1); }
.text-secondary { color: #5a6268 !important; }

.pagination .page-link {
  border: none;
  border-radius: 8px;
  margin: 0 4px;
  color: #4a5568;
  font-weight: 600;
  padding: 0.6rem 1rem;
}
.pagination .page-item.active .page-link {
  background-color: #3182ce;
  color: white;
  box-shadow: 0 4px 6px -1px rgba(49,130,206, 0.3);
}

.empty-state {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  text-align: center;
  background-color: #ffffff;
  border: 1px solid #e2e8f0; 
  border-radius: 12px; 
  padding: 4rem 2rem; 
  margin-top: 1.5rem; 
  color: #718096;
}

.empty-state .empty-icon {
  font-size: 4.5rem; 
  color: #cbd5e0; 
  margin-bottom: 1.5rem;
}

.empty-state h4 {
  font-size: 1.5rem; 
  font-weight: 600;
  color: #2d3748; 
  margin-bottom: 0.5rem;
}

.empty-state p {
  color: #718096;
  max-width: 450px; 
  line-height: 1.6; 
}
</style>