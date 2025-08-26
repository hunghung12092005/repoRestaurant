<template>
  <div class="page-container">
    <!-- Thông báo lỗi -->
    <div v-if="error" class="alert alert-danger custom-alert" role="alert">
      {{ error }}
      <span class="close-btn" @click="error = ''">×</span>
    </div>

    <!-- Tiêu đề trang -->
    <div class="page-header mb-4">
      <h1 class="page-title">Quản lý Đánh giá</h1>
      <p class="page-subtitle">Xem và quản lý các đánh giá của khách hàng về dịch vụ.</p>
    </div>

    <!-- Bảng danh sách đánh giá -->
    <div class="table-container">
      <table class="table booking-table align-middle">
        <thead>
          <tr>
            <th style="width: 5%;">#</th>
            <th style="width: 10%;">Mã Booking</th>
            <th class="text-center" style="width: 10%;">Phòng</th>
            <th class="text-center" style="width: 10%;">Dịch vụ</th>
            <th class="text-center" style="width: 10%;">Nhân viên</th>
            <th class="text-center" style="width: 15%;">Đánh giá chung</th>
            <th style="width: 30%;">Nhận xét</th>
            <th style="width: 10%;">Ngày tạo</th>
          </tr>
        </thead>
        <tbody>
          <!-- Loading State -->
          <tr v-if="isLoading">
            <td colspan="8" class="text-center py-5">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Đang tải...</span>
              </div>
              <p class="mt-2 mb-0">Đang tải dữ liệu...</p>
            </td>
          </tr>

          <!-- Empty State -->
          <tr v-else-if="!reviews.length">
            <td colspan="8" class="text-center py-5">Chưa có đánh giá nào.</td>
          </tr>

          <!-- Data Rows -->
          <tr v-for="(review, index) in reviews" :key="review.id">
            <td>{{ (pagination.current_page - 1) * pagination.per_page + index + 1 }}</td>
            <td>
              <a href="#" @click.prevent="showBookingDetail(review.booking_id)" class="fw-bold text-primary text-decoration-none">
                HXH{{ review.booking_id }}
              </a>
            </td>
            <td class="text-center">{{ review.room_rating }}/10</td>
            <td class="text-center">{{ review.service_rating }}/10</td>
            <td class="text-center">{{ review.staff_rating }}/10</td>
            <td class="text-center">
              <div class="d-flex align-items-center justify-content-center gap-1 star-rating">
                <span v-for="n in 5" :key="n" :class="n <= review.star_rating ? 'rated' : ''">★</span>
              </div>
            </td>
            <td>
              <p class="description-text mb-0 fst-italic">{{ review.comment || 'Không có nhận xét' }}</p>
            </td>
            <td>
              {{ new Date(review.created_at).toLocaleDateString("vi-VN") }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Phân trang -->
    <nav v-if="pagination.last_page > 1 && !isLoading" aria-label="Page navigation" class="mt-4">
      <ul class="pagination justify-content-center">
        <li class="page-item" :class="{ disabled: pagination.current_page === 1 }">
          <a class="page-link" href="#" @click.prevent="changePage(1)">««</a>
        </li>
        <li class="page-item" :class="{ disabled: pagination.current_page === 1 }">
          <a class="page-link" href="#" @click.prevent="changePage(pagination.current_page - 1)">«</a>
        </li>
        <li v-for="page in pageRange" :key="page" class="page-item" :class="{ active: page === pagination.current_page }">
          <a class="page-link" href="#" @click.prevent="changePage(page)">{{ page }}</a>
        </li>
        <li class="page-item" :class="{ disabled: pagination.current_page === pagination.last_page }">
          <a class="page-link" href="#" @click.prevent="changePage(pagination.current_page + 1)">»</a>
        </li>
        <li class="page-item" :class="{ disabled: pagination.current_page === pagination.last_page }">
          <a class="page-link" href="#" @click.prevent="changePage(pagination.last_page)">»»</a>
        </li>
      </ul>
    </nav>

    <!-- Modal Chi tiết Booking -->
    <div class="modal fade" id="bookingDetailModal" tabindex="-1" aria-labelledby="bookingDetailModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content modal-custom">
          <div class="modal-header modal-header-custom">
            <h5 class="modal-title" id="bookingDetailModalLabel">Chi tiết Booking</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
          </div>
          <div class="modal-body p-4">
            <div v-if="!bookingDetail" class="text-center p-5">
               <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Đang tải...</span>
              </div>
            </div>
            <div v-else>
              <h5 class="mb-3">Mã Booking: <span class="fw-bold text-primary">HXH{{ bookingDetail.booking_id }}</span></h5>
              
              <div class="border rounded p-3 bg-light mb-3">
                <h6 class="fw-bold mb-3">Thông tin khách hàng</h6>
                <div class="row">
                  <div class="col-md-6 mb-2"><strong>Họ tên:</strong> {{ bookingDetail.customer?.customer_name }}</div>
                  <div class="col-md-6 mb-2"><strong>Email:</strong> {{ bookingDetail.customer?.customer_email }}</div>
                  <div class="col-md-6 mb-2"><strong>Điện thoại:</strong> {{ bookingDetail.customer?.customer_phone }}</div>
                  <div class="col-md-6 mb-2"><strong>Địa chỉ:</strong> {{ bookingDetail.customer?.address }}</div>
                </div>
              </div>
              
              <div class="border rounded p-3">
                <h6 class="fw-bold mb-3">Thông tin đặt phòng</h6>
                <p><strong>Ngày đặt:</strong> {{ new Date(bookingDetail.created_at).toLocaleString('vi-VN') }}</p>
                <div class="row">
                  <!-- CHANGED HERE -->
                  <div class="col-md-6 mb-2"><strong>Check-in:</strong> {{ formatDate(bookingDetail.check_in_date) }} {{ bookingDetail.check_in_time }}</div>
                  <div class="col-md-6 mb-2"><strong>Check-out:</strong> {{ formatDate(bookingDetail.check_out_date) }} {{ bookingDetail.check_out_time }}</div>
                  <!-- END CHANGE -->
                  <div class="col-md-6 mb-2"><strong>Người lớn:</strong> {{ bookingDetail.adult }}</div>
                  <div class="col-md-6 mb-2"><strong>Trẻ em:</strong> {{ bookingDetail.child }}</div>
                   <div class="col-md-6 mb-2"><strong>Số phòng:</strong> {{ bookingDetail.total_rooms }}</div>
                   <div class="col-md-6 mb-2">
                     <strong>Trạng thái:</strong>
                      <!-- CHANGED HERE -->
                      <span class="badge" :class="getBadgeClass(bookingDetail.status)">
                        {{ translateStatus(bookingDetail.status) }}
                      </span>
                      <!-- END CHANGE -->
                  </div>
                </div>
                <hr>
                <p class="mb-2"><strong>Tổng tiền:</strong>
                  <span class="text-danger fw-bold fs-5">
                    {{ Number(bookingDetail.total_price).toLocaleString('vi-VN') }} VND
                  </span>
                </p>
                 <p class="mb-0"><strong>Ghi chú:</strong> {{ bookingDetail.note || 'Không có' }}</p>
              </div>

            </div>
          </div>
          <div class="modal-footer modal-footer-custom">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, inject, computed } from "vue";
import axios from "axios";
import { Modal } from 'bootstrap';

const apiUrl = inject("apiUrl");

const reviews = ref([]);
const pagination = ref({ current_page: 1, last_page: 1, per_page: 10 });
const isLoading = ref(false);
const error = ref("");
const bookingDetail = ref(null);

let detailModalInstance = null;

// --- NEW HELPER FUNCTIONS ---
/**
 * Dịch trạng thái booking sang tiếng Việt
 * @param {string} status Trạng thái tiếng Anh
 * @returns {string} Trạng thái tiếng Việt
 */
const translateStatus = (status) => {
  const statusMap = {
    'completed': 'Đã hoàn thành',
    'pending': 'Đang chờ xử lý',
    'confirmed': 'Đã xác nhận',
    'cancelled': 'Đã hủy'
  };
  return statusMap[status] || status;
};

/**
 * Lấy class CSS cho badge dựa vào trạng thái
 * @param {string} status Trạng thái booking
 * @returns {string} Tên class CSS
 */
const getBadgeClass = (status) => {
  switch (status) {
    case 'completed':
      return 'badge-success';
    case 'pending':
      return 'badge-info'; // Sử dụng badge màu xanh dương cho pending
    case 'cancelled':
      return 'badge-danger'; // Sử dụng badge màu đỏ cho cancelled
    default:
      return 'badge-secondary';
  }
};

/**
 * Định dạng chuỗi ngày YYYY-MM-DD sang DD/MM/YYYY
 * @param {string} dateString Chuỗi ngày
 * @returns {string} Ngày đã định dạng hoặc chuỗi gốc nếu lỗi
 */
const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    try {
        const [year, month, day] = dateString.split('-');
        return `${day}/${month}/${year}`;
    } catch (e) {
        return dateString; // Trả về ngày gốc nếu không thể định dạng
    }
};
// --- END NEW HELPER FUNCTIONS ---


const fetchReviews = async (page = 1) => {
  try {
    isLoading.value = true;
    error.value = "";
    const res = await axios.get(`${apiUrl}/api/customer-reviewsget?page=${page}`);
    reviews.value = res.data.data;
    pagination.value = {
      current_page: res.data.current_page,
      last_page: res.data.last_page,
      per_page: res.data.per_page,
    };
  } catch (err) {
    console.error("Fetch reviews error:", err);
    error.value = "Không thể tải dữ liệu đánh giá!";
  } finally {
    isLoading.value = false;
  }
};

const showBookingDetail = async (bookingId) => {
  bookingDetail.value = null; 
  detailModalInstance.show(); 
  try {
    const res = await axios.get(`${apiUrl}/api/booking-detailreview/${bookingId}`);
    if (res.data?.status === 'success') {
      bookingDetail.value = res.data.data;
    } else {
      error.value = 'Không thể tải thông tin chi tiết booking.';
      detailModalInstance.hide();
    }
  } catch (err) {
    console.error('Lỗi khi lấy chi tiết booking:', err);
    error.value = 'Có lỗi xảy ra khi tải chi tiết booking.';
    detailModalInstance.hide();
  }
};

const changePage = (page) => {
  if (page < 1 || page > pagination.value.last_page) return;
  fetchReviews(page);
};

const pageRange = computed(() => {
  const maxPages = 5;
  const currentPage = pagination.value.current_page;
  const totalPages = pagination.value.last_page;

  let start = Math.max(1, currentPage - Math.floor(maxPages / 2));
  let end = Math.min(totalPages, start + maxPages - 1);

  if (end - start + 1 < maxPages) {
    start = Math.max(1, end - maxPages + 1);
  }
  
  return Array.from({ length: end - start + 1 }, (_, i) => start + i);
});

onMounted(() => {
  fetchReviews();
  const detailModalElement = document.getElementById('bookingDetailModal');
  if (detailModalElement) {
    detailModalInstance = new Modal(detailModalElement);
  }
});
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700&display=swap');
@import url('https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css');

.page-container {
  font-family: 'Be Vietnam Pro', sans-serif;
  background-color: #f4f7f9;
  padding: 2rem;
  color: #34495e;
}

.page-header {
  border-bottom: 1px solid #e5eaee;
  padding-bottom: 1rem;
}

.page-title {
  font-size: 2rem;
  font-weight: 700;
}

.page-subtitle {
  font-size: 1rem;
  color: #7f8c8d;
}

.table-container {
  background-color: #ffffff;
  border-radius: 12px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
  overflow-x: auto;
}

.booking-table {
  font-size: 0.875rem;
  border-collapse: separate;
  border-spacing: 0;
  min-width: 900px;
}

.booking-table thead th {
  background-color: #f8f9fa;
  color: #7f8c8d;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  border-bottom: 2px solid #e5eaee;
  padding: 1rem;
  white-space: nowrap;
}

.booking-table td {
  padding: 1rem;
  border-bottom: 1px solid #e5eaee;
  vertical-align: middle;
}

.booking-table tbody tr:last-child td {
  border-bottom: none;
}

.booking-table tbody tr:hover {
  background-color: #f9fafb;
}

.description-text {
  font-size: 0.8rem;
  color: #7f8c8d;
}

.star-rating span {
  font-size: 1.25rem;
  color: #e0e0e0;
}
.star-rating span.rated {
  color: #f39c12;
}

.badge {
  padding: 0.4em 0.8em;
  font-size: 0.75rem;
  font-weight: 600;
  border-radius: 20px;
  letter-spacing: 0.5px;
}

.badge-secondary {
  background-color: #f3f4f6;
  color: #7f8c8d;
}

.badge-success {
  background-color: #e6f9f0;
  color: #2ecc71;
}

/* Thêm class cho các trạng thái mới */
.badge-info { 
  background-color: #eaf6fb; 
  color: #3498db; 
}
.badge-danger {
  background-color: #fce8e6;
  color: #e74c3c;
}

.pagination .page-link {
  border: none;
  border-radius: 8px;
  margin: 0 4px;
  color: #7f8c8d;
  font-weight: 600;
}

.pagination .page-item.active .page-link {
  background-color: #3498db;
  color: white;
}

.modal-custom {
  border-radius: 16px;
  border: none;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.modal-header-custom {
  background-color: #f4f7f9;
  border-bottom: 1px solid #e5eaee;
  padding: 1.5rem;
}

.modal-footer-custom {
  background-color: #f4f7f9;
  border-top: 1px solid #e5eaee;
  padding: 1rem 1.5rem;
}

.custom-alert {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 1060;
  min-width: 300px;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
  border-radius: 8px;
}

.close-btn {
  cursor: pointer;
  float: right;
  font-size: 1.5rem;
  line-height: 1;
}
</style>