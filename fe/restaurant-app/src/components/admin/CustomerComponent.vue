<template>
 <div class="page-container">
    <!-- Hiển thị loading khi đang tải trang -->
    <loading v-if="isLoading"></loading>

    <!-- Nội dung trang khi đã tải xong -->
    <div v-else>
      <!-- Tiêu đề trang -->
      <div class="page-header mb-4">
        <h1 class="page-title">Quản Lý Khách Hàng</h1>
        <p class="page-subtitle">Xem thông tin và lịch sử đặt phòng của khách hàng.</p>
      </div>

      <!-- Bộ lọc và tìm kiếm -->
      <div class="card filter-card mb-4">
        <div class="card-body d-flex justify-content-between align-items-center">
          <div class="col-md-5">
            <input
              type="text"
              class="form-control"
              v-model="searchTerm"
              placeholder="Tìm theo tên, SĐT, email, CCCD..."
              @keyup.enter="handleSearch"
            />
          </div>
          <button class="btn btn-primary" @click="handleSearch">
            <i class="bi bi-search me-2"></i>Tìm Kiếm
          </button>
        </div>
      </div>

      <!-- Hiển thị thông báo lỗi -->
      <div v-if="error" class="alert alert-danger" role="alert">
        {{ error }}
      </div>

      <!-- Bảng danh sách khách hàng -->
      <div class="table-container">
        <table class="table booking-table align-middle" v-if="customers.length > 0">
          <thead>
            <tr>
              <th style="width: 25%;">Khách Hàng</th>
              <th style="width: 25%;">Email & SĐT</th>
              <th style="width: 35%;">Địa chỉ & CCCD</th>
              <th style="width: 15%;" class="text-center">Hành Động</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="customer in customers" :key="customer.customer_id">
              <td>
                <div class="fw-bold type-name">{{ customer.customer_name }}</div>
                <p class="description-text mb-0">Mã KH: #{{ customer.customer_id }}</p>
              </td>
              <td>
                <div class="fw-bold">{{ customer.customer_email || 'Chưa có' }}</div>
                <p class="description-text mb-0">{{ customer.customer_phone }}</p>
              </td>
              <td>
                 <div class="fw-bold">{{ customer.address || 'Chưa có địa chỉ' }}</div>
                 <p class="description-text mb-0">CCCD: {{ customer.customer_id_number || 'N/A' }}</p>
              </td>
              <td class="text-center action-buttons">
                <button @click="viewDetails(customer)" class="btn btn-outline-primary btn-sm" title="Xem chi tiết và lịch sử đặt phòng">
                  <i class="bi bi-eye-fill me-1"></i> Xem Lịch Sử
                </button>
              </td>
            </tr>
          </tbody>
        </table>
        <div v-else class="alert alert-light text-center p-4">
          Không tìm thấy dữ liệu khách hàng phù hợp.
        </div>
      </div>

      <!-- Phân trang -->
      <nav aria-label="Page navigation" class="mt-4" v-if="pagination.last_page > 1">
        <ul class="pagination justify-content-center">
           <li class="page-item" :class="{ disabled: pagination.current_page === 1 }">
            <a class="page-link" href="#" @click.prevent="changePage(1)">««</a>
          </li>
          <li class="page-item" :class="{ disabled: pagination.current_page === 1 }">
            <a class="page-link" href="#" @click.prevent="changePage(pagination.current_page - 1)">«</a>
          </li>
          <!-- Logic phân trang có thể cần điều chỉnh để hiển thị ... nếu có quá nhiều trang -->
          <li class="page-item" v-for="page in pagination.last_page" :key="page" :class="{ active: page === pagination.current_page }">
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

      <!-- Modal chi tiết khách hàng -->
      <div v-if="isModalVisible" class="modal-backdrop fade show"></div>
      <div v-if="isModalVisible" class="modal fade show d-block" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
          <div class="modal-content modal-custom">
            <div class="modal-header modal-header-custom">
              <h5 class="modal-title">Chi Tiết Khách Hàng: {{ selectedCustomer.customer_name }}</h5>
              <button type="button" @click="closeModal" class="btn-close" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
              <loading v-if="isDetailLoading" />
              <div v-else>
                <!-- Thông tin cơ bản -->
                <h6 class="form-section-title">Thông Tin Cá Nhân & Liên Hệ</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-6"><p><strong>Họ và tên:</strong> {{ selectedCustomer.customer_name }}</p></div>
                    <div class="col-md-6"><p><strong>Điện thoại:</strong> {{ selectedCustomer.customer_phone }}</p></div>
                    <div class="col-md-6"><p><strong>Email:</strong> {{ selectedCustomer.customer_email || 'N/A' }}</p></div>
                    <div class="col-md-6"><p><strong>CCCD/CMND:</strong> {{ selectedCustomer.customer_id_number || 'N/A' }}</p></div>
                    <div class="col-12"><p><strong>Địa chỉ:</strong> {{ selectedCustomer.address || 'N/A' }}</p></div>
                </div>

                <!-- Lịch sử đặt phòng -->
                <h6 class="form-section-title">Lịch sử đặt phòng ({{ customerBookings.length }} đơn)</h6>
                <div v-if="customerBookings.length > 0" class="table-responsive">
                  <table class="table booking-table history-table">
                    <thead>
                      <tr>
                        <th>Mã ĐP</th>
                        <th>Ngày Nhận - Trả Phòng</th>
                        <th class="text-end">Tổng Tiền</th>
                        <th class="text-center">Trạng Thái</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="booking in customerBookings" :key="booking.booking_id">
                        <td>#{{ booking.booking_id }}</td>
                        <td>
                          <div>Nhận: {{ formatDateTime(booking.check_in_date, booking.check_in_time) }}</div>
                          <div>Trả: {{ formatDateTime(booking.check_out_date, booking.check_out_time) }}</div>
                        </td>
                        <td class="text-end fw-bold">{{ formatCurrency(booking.total_price) }}</td>
                        <td class="text-center tags-container justify-content-center">
                          <span class="badge" :class="getBookingStatusClass(booking.status)">
                            {{ formatBookingStatus(booking.status) }}
                          </span>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
                <div v-else class="alert alert-light text-center p-4">
                  Khách hàng này chưa có lịch sử đặt phòng.
                </div>
              </div>
            </div>
            <div class="modal-footer modal-footer-custom">
              <button @click="closeModal" class="btn btn-secondary">Đóng</button>
            </div>
          </div>
        </div>
      </div>
    </div>
 </div>
</template>

<script setup>
import { ref, onMounted, inject } from 'vue';
import axiosConfig from '../../axiosConfig.js';
import loading from '../../components/loading.vue';

const apiUrl = inject('apiUrl');
const customers = ref([]);
const isLoading = ref(true);
const error = ref('');
const searchTerm = ref('');

const pagination = ref({
  current_page: 1,
  last_page: 1,
  total: 0,
});

const isModalVisible = ref(false);
const isDetailLoading = ref(false);
const selectedCustomer = ref(null);
const customerBookings = ref([]);

const fetchCustomers = async (page = 1) => {
  isLoading.value = true;
  error.value = '';
  try {
    const response = await axiosConfig.get(`${apiUrl}/api/customers`, {
      params: {
        page: page,
        search: searchTerm.value,
      },
    });
    customers.value = response.data.data;
    pagination.value = {
      current_page: response.data.current_page,
      last_page: response.data.last_page,
      total: response.data.total,
    };
  } catch (err) {
    console.error('Lỗi khi tải danh sách khách hàng:', err);
    error.value = 'Không thể tải được dữ liệu khách hàng. Vui lòng thử lại.';
  } finally {
    isLoading.value = false;
  }
};

const viewDetails = async (customer) => {
  selectedCustomer.value = customer;
  isModalVisible.value = true;
  isDetailLoading.value = true;
  customerBookings.value = [];
  try {
    const response = await axiosConfig.get(`${apiUrl}/api/customers/${customer.customer_id}/bookings`);
    customerBookings.value = response.data.data;
  } catch (err) {
    console.error('Lỗi khi tải lịch sử đặt phòng:', err);
  } finally {
    isDetailLoading.value = false;
  }
};

const closeModal = () => {
  isModalVisible.value = false;
  selectedCustomer.value = null;
};

const handleSearch = () => {
  fetchCustomers(1);
};

const changePage = (page) => {
  if (page > 0 && page <= pagination.value.last_page) {
    fetchCustomers(page);
  }
};

// --- CÁC HÀM HELPER ---
const formatDateTime = (date, time) => {
  if (!date) return 'N/A';
  const dateTimeString = time ? `${date}T${time}` : date;
  return new Date(dateTimeString).toLocaleString('vi-VN', {
    day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit'
  });
};

const formatCurrency = (value) => {
    if (value === null || value === undefined) return '0 ₫';
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
};

const formatBookingStatus = (status) => {
  const statusMap = {
    pending_confirmation: 'Chờ xác nhận',
    confirmed_not_assigned: 'Chưa xếp phòng',
    confirmed: 'Đã xác nhận',
    pending_cancel: 'Chờ hủy',
    cancelled: 'Đã hủy',
    completed: 'Hoàn thành',
  };
  return statusMap[status] || status.replace(/_/g, ' ');
};

const getBookingStatusClass = (status) => {
  const classMap = {
    pending_confirmation: 'badge-warning',
    confirmed_not_assigned: 'badge-info',
    confirmed: 'badge-primary',
    cancelled: 'badge-danger',
    completed: 'badge-success',
  };
  return classMap[status] || 'badge-secondary';
};

onMounted(() => {
  fetchCustomers();
});
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700&display=swap');
@import url('https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css');

.page-container { font-family: 'Be Vietnam Pro', sans-serif; background-color: #f4f7f9; padding: 2rem; color: #34495e; }
.page-header { border-bottom: 1px solid #e5eaee; padding-bottom: 1rem; }
.page-title { font-size: 2rem; font-weight: 700; }
.page-subtitle { font-size: 1rem; color: #7f8c8d; }
.card { background-color: #ffffff; border: none; border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); }

.form-control, .form-select { border-radius: 8px; border: 1px solid #e5eaee; transition: all 0.2s ease-in-out; font-size: 0.9rem; }
.form-control:focus, .form-select:focus { border-color: #3498db; box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.15); }

.table-container { background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); overflow: hidden; }
.booking-table { font-size: 0.875rem; border-collapse: separate; border-spacing: 0; }
.booking-table thead th { background-color: #f8f9fa; color: #7f8c8d; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e5eaee; padding: 1rem; white-space: nowrap; }
.booking-table td { padding: 1.25rem 1rem; border-bottom: 1px solid #e5eaee; }
.booking-table tbody tr:last-child td { border-bottom: none; }
.booking-table tbody tr:hover { background-color: #f9fafb; }
.type-name { font-size: 1rem; color: #34495e; }
.description-text { font-size: 0.8rem; color: #7f8c8d; }

.tags-container { flex-wrap: wrap; gap: 6px; }
.badge { padding: 0.4em 0.8em; font-size: 0.75rem; font-weight: 600; border-radius: 20px; text-transform: capitalize; }
.badge-secondary { background-color: #f3f4f6; color: #7f8c8d; }
.badge-primary { background-color: #eaf6fb; color: #3498db; }
.badge-success { background-color: #e8f9f0; color: #2ecc71; }
.badge-warning { background-color: #fff8e1; color: #f39c12; }
.badge-danger { background-color: #feeeed; color: #e74c3c; }
.badge-info { background-color: #e8f8f5; color: #1abc9c; }

.action-buttons { white-space: nowrap; }
.action-buttons .btn { margin: 0 2px; }
.pagination .page-link { border: none; border-radius: 8px; margin: 0 4px; color: #7f8c8d; font-weight: 600; }
.pagination .page-item.active .page-link { background-color: #3498db; color: #ffffff; }

.modal-custom { border-radius: 16px; border: none; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); }
.modal-header-custom, .modal-footer-custom { background-color: #f4f7f9; border-color: #e5eaee; padding: 1.5rem; }
.modal-header-custom .btn-close { filter: brightness(0) invert(0.7); }
.modal-body { background-color: #ffffff; }
.modal-body p { margin-bottom: 0.75rem; }
.modal-body p strong { color: #34495e; display: inline-block; min-width: 100px; }

.form-section-title { font-size: 1rem; font-weight: 600; color: #34495e; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 1px solid #e5eaee; }
.history-table { font-size: 0.9rem; }
.history-table td, .history-table th { padding: 0.75rem; }
</style>