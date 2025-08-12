<template>
  <div class="page-container">
    <!-- Tiêu đề trang -->
    <div class="page-header mb-4">
      <h1 class="page-title">Lịch Sử Đặt Phòng</h1>
      <p class="page-subtitle">Tra cứu và xem lại thông tin các lượt lưu trú đã hoàn thành của khách hàng.</p>
    </div>

    <!-- Bộ lọc và tìm kiếm -->
    <div class="card filter-card mb-5">
      <div class="card-body">
        <div class="row g-3 align-items-end">
          <div class="col-lg-4 col-md-12">
            <label for="search-input" class="form-label">Tìm kiếm khách hàng</label>
            <input 
              id="search-input"
              v-model="searchKeyword" 
              type="text" 
              class="form-control" 
              placeholder="Nhập tên, SĐT hoặc CCCD..."
              @keyup.enter="fetchBookings"
            >
          </div>
          <div class="col-lg-3 col-md-5">
            <label for="filter-date" class="form-label">Lọc theo ngày trả phòng</label>
            <input 
              id="filter-date"
              type="date" 
              v-model="selectedDate" 
              class="form-control"
            />
          </div>
          <div class="col-lg-3 col-md-4">
            <button @click="fetchBookings" class="btn btn-primary w-100" :disabled="isLoading">
              <i class="bi bi-search me-2"></i>Tìm Kiếm
            </button>
          </div>
          <div class="col-lg-2 col-md-3">
            <button @click="clearFilters" class="btn btn-outline-secondary w-100">
              <i class="bi bi-arrow-clockwise me-2"></i>Xóa lọc
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Vùng hiển thị kết quả -->
    <div class="results-container">
      <!-- Trạng thái đang tải -->
      <div v-if="isLoading" class="loading-state">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-3">Đang tải dữ liệu, vui lòng chờ...</p>
      </div>

      <!-- Không có kết quả -->
      <div v-if="!isLoading && allBookings.length === 0" class="empty-state">
        <i class="bi bi-journal-x empty-icon"></i>
        <h4 class="mt-3">Không tìm thấy lịch sử</h4>
        <p class="text-muted">Không có lịch sử đặt phòng nào phù hợp với tiêu chí tìm kiếm của bạn.</p>
      </div>

      <!-- Danh sách thẻ đặt phòng -->
      <div v-if="!isLoading && allBookings.length > 0" class="row g-4">
        <div class="col-12" v-for="(booking, index) in allBookings" :key="booking.status_id">
          <div class="booking-card">
            <div class="card-header d-flex justify-content-between align-items-center">
              <div>
                <span class="fw-bold fs-5 me-3">Phòng {{ booking.room_name }}</span>
                <span class="badge" :class="statusClass(booking.status)">{{ booking.status_display }}</span>
              </div>
              <span class="text-muted fw-semibold">Mã Booking: {{ booking.booking_id }}</span>
            </div>
            <div class="card-body">
              <div class="row g-4">
                <!-- Cột thông tin khách hàng -->
                <div class="col-lg-4">
                  <h6 class="section-title"><i class="bi bi-person-circle me-2"></i>Thông Tin Khách Hàng</h6>
                  <p class="info-item main-info">{{ booking.customer_name }}</p>
                  <p class="info-item"><i class="bi bi-telephone me-2"></i>{{ booking.customer_phone }}</p>
                  <p class="info-item"><i class="bi bi-envelope me-2"></i>{{ booking.customer_email || 'Chưa cung cấp' }}</p>
                  <p class="info-item"><i class="bi bi-person-badge me-2"></i>CCCD: {{ booking.customer_id_number || 'Chưa cung cấp' }}</p>
                  <p class="info-item"><i class="bi bi-person-badge me-2"></i>Ghi chu: {{ booking.surcharge_reason || '' }}</p>
                </div>
                
                <!-- Cột thời gian lưu trú -->
                <div class="col-lg-4">
                  <h6 class="section-title"><i class="bi bi-calendar-range me-2"></i>Thời Gian Lưu Trú</h6>
                  <div class="timeline">
                    <div class="timeline-item check-in">
                      <div class="timeline-icon"><i class="bi bi-box-arrow-in-right"></i></div>
                      <div class="timeline-content">
                        <strong>Nhận phòng:</strong>
                        <span>{{ formatDate(booking.check_in) }}</span>
                      </div>
                    </div>
                    <div class="timeline-item check-out">
                      <div class="timeline-icon"><i class="bi bi-box-arrow-left"></i></div>
                      <div class="timeline-content">
                        <strong>Trả phòng:</strong>
                        <span>{{ formatDate(booking.check_out) }}</span>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Cột thanh toán -->
                <div class="col-lg-4">
                  <h6 class="section-title"><i class="bi bi-wallet2 me-2"></i>Thông Tin Thanh Toán</h6>
                   <div class="payment-details">
                      <div class="payment-row"><span>Phương thức:</span> <strong class="text-dark">{{ booking.payment_method_display }}</strong></div>
                      <div class="payment-row"><span>Trạng thái:</span> <strong class="text-dark">{{ booking.payment_status_display }}</strong></div>
                      <hr class="my-2">
                      <div class="payment-row"><span>Tiền phòng:</span> <span>{{ formatCurrency(booking.room_price) }}</span></div>
                      <div class="payment-row"><span>Tiền dịch vụ:</span> <span>{{ formatCurrency(booking.service_price) }}</span></div>
                      <div class="payment-row"><span>Phụ phí:</span> <span>{{ formatCurrency(booking.surcharge) }}</span></div>
                      <hr class="my-2">
                      <div class="payment-row total"><span>Tổng cộng:</span> <strong>{{ formatCurrency(booking.total_paid) }}</strong></div>
                   </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Phân trang -->
    <nav v-if="totalPages > 1 && !isLoading" aria-label="Page navigation" class="mt-5">
      <ul class="pagination justify-content-center">
        <li class="page-item" :class="{ disabled: currentPage === 1 }">
          <a class="page-link" href="#" @click.prevent="chuyenTrang(1)">««</a>
        </li>
        <li class="page-item" :class="{ disabled: currentPage === 1 }">
          <a class="page-link" href="#" @click.prevent="chuyenTrang(currentPage - 1)">«</a>
        </li>
        <li v-for="page in pageRange" :key="page" class="page-item" :class="{ active: page === currentPage }">
          <a class="page-link" href="#" @click.prevent="chuyenTrang(page)">{{ page }}</a>
        </li>
        <li class="page-item" :class="{ disabled: currentPage === totalPages }">
          <a class="page-link" href="#" @click.prevent="chuyenTrang(currentPage + 1)">»</a>
        </li>
        <li class="page-item" :class="{ disabled: currentPage === totalPages }">
          <a class="page-link" href="#" @click.prevent="chuyenTrang(totalPages)">»»</a>
        </li>
      </ul>
    </nav>
  </div>
</template>

<script setup>
import { ref, onMounted, inject, computed } from 'vue';
import axios from 'axios';

const apiUrl = inject('apiUrl');
const selectedDate = ref('');
const allBookings = ref([]);
const currentPage = ref(1);
const totalPages = ref(1);
const perPage = 10;
const searchKeyword = ref('');
const isLoading = ref(true);

const formatDate = (date) => {
  if (!date) return 'N/A';
  return new Date(date).toLocaleString('vi-VN', {
    year: 'numeric', month: '2-digit', day: '2-digit',
    hour: '2-digit', minute: '2-digit'
  });
};

const formatCurrency = (amount) => {
  if (amount === null || amount === undefined) return '0 VNĐ';
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
};

const statusDisplayMap = {
  'pending_confirmation': 'Chờ Xác Nhận',
  'confirmed': 'Đã Xác Nhận',
  'cancelled': 'Đã Hủy',
  'completed': 'Hoàn Thành',
  'pending_cancel': 'Chờ Hủy'
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

const statusClass = (status) => {
  return {
    'bg-success-light text-success': status === 'completed',
    'bg-warning-light text-warning': status === 'pending_confirmation',
    'bg-danger-light text-danger': status === 'cancelled' || status === 'pending_cancel',
    'bg-info-light text-info': status === 'confirmed'
  };
};

const clearFilters = () => {
  selectedDate.value = '';
  searchKeyword.value = '';
  currentPage.value = 1;
  fetchBookings();
};

const fetchBookings = async () => {
  isLoading.value = true;
  try {
    const params = {
      page: currentPage.value,
      per_page: perPage,
      status: 'completed'
    };
    if (selectedDate.value) params.date = selectedDate.value;
    if (searchKeyword.value.trim()) params.search = searchKeyword.value.trim();
    
    const res = await axios.get(`${apiUrl}/api/booking-histories`, { params });

    allBookings.value = res.data.data.map(b => {
      const paymentMethodRaw = b.booking?.payment_method;
      const paymentStatusRaw = b.booking?.payment_status;
      const statusRaw = b.booking?.status;

      return {
        status_id: b.status_id,
        booking_id: b.booking_id,
        customer_name: b.customer?.customer_name || 'Khách vãng lai',
        customer_phone: b.customer?.customer_phone || 'N/A',
        customer_email: b.customer?.customer_email,
        customer_id_number: b.customer?.customer_id_number,
        room_name: b.room?.room_name || 'N/A',
        check_in: b.check_in,
        check_out: b.check_out,
        room_price: b.room_price,
        service_price: b.service_price,
        surcharge: b.surcharge,
        total_paid: b.total_paid,
        surcharge_reason: b.surcharge_reason || 'N/A',
        payment_method_display: paymentMethodMap[paymentMethodRaw] || paymentMethodRaw || 'N/A',
        payment_status_display: paymentStatusMap[paymentStatusRaw] || paymentStatusRaw || 'N/A',
        status_display: statusDisplayMap[statusRaw] || statusRaw || 'N/A',
        
        status: statusRaw || 'N/A',
      }
    });

    totalPages.value = res.data.last_page;
    currentPage.value = res.data.current_page;
  } catch (err) {
    console.error("Lỗi khi tải lịch sử đặt phòng:", err);
    alert("Có lỗi xảy ra, không thể tải được lịch sử đặt phòng.");
  } finally {
    isLoading.value = false;
  }
};

const pageRange = computed(() => {
  const maxPages = 5;
  const halfPages = Math.floor(maxPages / 2);
  let start = Math.max(1, currentPage.value - halfPages);
  let end = Math.min(totalPages.value, start + maxPages - 1);
  if (end - start + 1 < maxPages) {
    start = Math.max(1, end - maxPages + 1);
  }
  return Array.from({ length: end - start + 1 }, (_, i) => start + i);
});

const chuyenTrang = (page) => {
  if (page >= 1 && page <= totalPages.value && page !== currentPage.value) {
    currentPage.value = page;
    fetchBookings();
  }
};

onMounted(fetchBookings);
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap');
@import url('https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css');

.page-container {
  font-family: 'Be Vietnam Pro', sans-serif;
  background-color: #f8fafc;
  padding: 2rem;
  color: #4a5568;
  max-width: 1600px;
  margin: 0 auto;
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
  background-color: #ffffff;
  border-radius: 12px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  border: none;
  padding: 1rem;
}

.form-label {
  font-weight: 600;
  font-size: 0.9rem;
  margin-bottom: 0.5rem;
}
.form-control {
  border-radius: 8px;
  border-color: #e2e8f0;
  padding: 0.6rem 1rem;
}
.form-control:focus {
  border-color: #3182ce;
  box-shadow: 0 0 0 3px rgba(49, 130, 206, 0.2);
}

.btn {
  border-radius: 8px;
  font-weight: 600;
  padding: 0.6rem 1rem;
  transition: all 0.2s ease-in-out;
}
.btn-primary {
  background-color: #3182ce;
  border-color: #3182ce;
}
.btn-primary:hover, .btn-primary:focus {
  background-color: #2b6cb0;
  border-color: #2b6cb0;
}
.btn-outline-secondary {
  border-color: #e2e8f0;
}
.btn-outline-secondary:hover {
  background-color: #edf2f7;
  color: #4a5568;
}

/* Loading and Empty States */
.loading-state, .empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem;
  text-align: center;
  background-color: #fff;
  border-radius: 12px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  color: #718096;
}
.empty-icon {
  font-size: 4rem;
  color: #a0aec0;
}

/* Booking Card */
.booking-card {
  background-color: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  transition: all 0.2s ease-in-out;
}
.booking-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05);
}
.booking-card .card-header {
  background-color: #f7fafc;
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #e2e8f0;
  border-top-left-radius: 12px;
  border-top-right-radius: 12px;
}
.booking-card .card-body {
  padding: 1.5rem;
}
.section-title {
  font-size: 1rem;
  font-weight: 700;
  color: #1a202c;
  margin-bottom: 1.25rem;
  display: flex;
  align-items: center;
}
.info-item {
  display: flex;
  align-items: center;
  font-size: 0.95rem;
  color: #4a5568;
  margin-bottom: 0.75rem;
}
.info-item .bi {
  color: #718096;
  margin-right: 0.75rem;
  font-size: 1.1rem;
}
.info-item.main-info {
  font-weight: 600;
  font-size: 1.1rem;
  color: #1a202c;
}

/* Timeline for Check-in/out */
.timeline {
  position: relative;
  padding-left: 25px;
}
.timeline-item {
  position: relative;
  margin-bottom: 1.5rem;
}
.timeline-item:last-child {
  margin-bottom: 0;
}
.timeline-icon {
  position: absolute;
  left: -27px;
  top: 0;
  width: 28px;
  height: 28px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
  color: #fff;
  border: 2px solid #fff;
}
.timeline-item.check-in .timeline-icon { background-color: #48bb78; }
.timeline-item.check-out .timeline-icon { background-color: #f56565; }
.timeline-content {
  display: flex;
  flex-direction: column;
}
.timeline-content strong {
  font-weight: 600;
  color: #4a5568;
}
.timeline-content span {
  font-size: 0.95rem;
}

/* Payment Details */
.payment-details .payment-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.5rem;
  font-size: 0.95rem;
}
.payment-details .payment-row.total {
  font-size: 1.1rem;
  color: #3182ce;
}
.payment-details .payment-row span:first-child { color: #718096; }

/* Status Badges */
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

/* Pagination */
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
.pagination .page-item.disabled .page-link {
  color: #a0aec0;
}
</style>