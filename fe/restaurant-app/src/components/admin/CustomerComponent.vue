<template>
 <div class="page-container">
    <!-- Hiển thị loading khi đang tải trang -->
    <loading v-if="isLoading"></loading>

    <!-- Nội dung trang khi đã tải xong -->
    <div v-else>
      <!-- Tiêu đề trang -->
      <div class="page-header mb-4">
        <h1 class="page-title"><i class="bi bi-people-fill me-3"></i>Quản Lý Khách Hàng</h1>
        <p class="page-subtitle">Xem thông tin và lịch sử đặt phòng của khách hàng.</p>
      </div>

      <!-- Bộ lọc và tìm kiếm -->
      <div class="card filter-card mb-4">
        <div class="card-body">
          <div class="row g-3 align-items-end">
            <div class="col-lg-6 col-md-12">
              <label for="search-input" class="form-label">Tìm kiếm khách hàng</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input
                  type="text"
                  class="form-control"
                  v-model="searchTerm"
                  placeholder="Tìm theo tên, SĐT, email..."
                  @keyup.enter="handleSearch"
                />
              </div>
            </div>
            <div class="col-lg-3 col-md-6">
              <button class="btn btn-primary w-100" @click="handleSearch">
                Tìm Kiếm
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Hiển thị thông báo lỗi -->
      <div v-if="error" class="alert alert-danger" role="alert">
        {{ error }}
      </div>

      <!-- Bảng danh sách khách hàng -->
      <div class="table-container">
        <table class="table customer-table align-middle" v-if="customers.length > 0">
          <thead>
            <tr>
              <th scope="col">Khách Hàng</th>
              <th scope="col">Email & SĐT</th>
              <th scope="col">Ngày Tham Gia</th>
              <th scope="col" class="text-center">Hành Động</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="customer in customers" :key="customer.customer_id">
              <td>
                <div class="d-flex align-items-center">
                  <div>
                    <div class="fw-bold customer-name">{{ customer.customer_name }}</div>
                    <div class="text-muted small">Mã KH: #{{ customer.customer_id }}</div>
                  </div>
                </div>
              </td>
              <td>
                <div>{{ customer.customer_email || 'Chưa có' }}</div>
                <div class="text-muted small">{{ customer.customer_phone }}</div>
              </td>
              <td>{{ formatDate(customer.created_at) }}</td>
              <td class="text-center action-buttons">
                <button @click="viewDetails(customer)" class="btn btn-outline-primary btn-sm" title="Xem chi tiết và lịch sử đặt phòng">
                  <i class="bi bi-eye-fill"></i> Xem Lịch Sử
                </button>
              </td>
            </tr>
          </tbody>
        </table>
        <div v-else class="alert alert-light text-center p-4">
          <i class="bi bi-cloud-slash fs-1"></i>
          <p class="mt-2">Không tìm thấy dữ liệu khách hàng phù hợp.</p>
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
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title"><i class="bi bi-person-vcard me-2"></i>Chi Tiết Khách Hàng: {{ selectedCustomer.customer_name }}</h5>
              <button type="button" @click="closeModal" class="btn-close" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
              <loading v-if="isDetailLoading" />
              <div v-else>
                <!-- Thông tin cơ bản -->
                <div class="info-block mb-4">
                   <h6 class="info-title">Thông tin liên hệ</h6>
                   <div class="row">
                       <div class="col-md-6 info-item">
                           <i class="bi bi-person"></i>
                           <div>
                               <label>Họ tên</label>
                               <strong>{{ selectedCustomer.customer_name }}</strong>
                           </div>
                       </div>
                       <div class="col-md-6 info-item">
                           <i class="bi bi-telephone"></i>
                           <div>
                               <label>Điện thoại</label>
                               <strong>{{ selectedCustomer.customer_phone }}</strong>
                           </div>
                       </div>
                       <div class="col-md-6 info-item">
                           <i class="bi bi-envelope"></i>
                           <div>
                               <label>Email</label>
                               <strong>{{ selectedCustomer.customer_email || 'N/A' }}</strong>
                           </div>
                       </div>
                       <div class="col-md-6 info-item">
                           <i class="bi bi-calendar-check"></i>
                           <div>
                               <label>Ngày tham gia</label>
                               <strong>{{ formatDate(selectedCustomer.created_at) }}</strong>
                           </div>
                       </div>
                       <div class="col-md-12 info-item">
                           <i class="bi bi-geo-alt"></i>
                           <div>
                               <label>Địa chỉ</label>
                               <strong>{{ selectedCustomer.address || 'N/A' }}</strong>
                           </div>
                       </div>
                   </div>
                </div>

                <!-- Lịch sử đặt phòng -->
                <h6 class="info-title">Lịch sử đặt phòng ({{ customerBookings.length }} đơn)</h6>
                <div v-if="customerBookings.length > 0" class="table-responsive">
                  <table class="table history-table">
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
                        <td class="text-center">
                          <span class="badge" :class="getBookingStatusClass(booking.status)">
                            {{ formatBookingStatus(booking.status) }}
                          </span>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
                <div v-else class="alert alert-light text-center p-4">
                  <i class="bi bi-journal-x fs-1"></i>
                  <p class="mt-2">Khách hàng này chưa có lịch sử đặt phòng.</p>
                </div>
              </div>
            </div>
            <div class="modal-footer">
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
const getInitials = (name) => {
  if (!name) return 'KH';
  const names = name.split(' ');
  const initials = names.map(n => n[0]).join('');
  return (initials.length > 2 ? initials[0] + initials[names.length - 1] : initials).toUpperCase();
};

const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  const options = { year: 'numeric', month: '2-digit', day: '2-digit' };
  return new Date(dateString).toLocaleDateString('vi-VN', options);
};

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
  return statusMap[status] || status.replace('_', ' ').toUpperCase();
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
/* --- TỔNG THỂ --- */
.page-container {
  font-family: 'Inter', sans-serif;
  background-color: #f4f7f9;
  padding: 2rem;
}
.page-header {
  border-bottom: 1px solid #e5eaee;
  padding-bottom: 1rem;
}
.page-title {
  font-size: 1.8rem;
  font-weight: 700;
  color: #2c3e50;
}
.page-subtitle {
  color: #7f8c8d;
}
.card {
  border: none;
  border-radius: 12px;
  box-shadow: 0 4px 25px rgba(0,0,0,0.06);
}
.btn-primary {
  background-color: #3498db;
  border-color: #3498db;
  font-weight: 500;
  padding: 0.6rem 1rem;
  transition: all 0.2s ease-in-out;
}
.btn-primary:hover {
  background-color: #2980b9;
  border-color: #2980b9;
  transform: translateY(-2px);
  box-shadow: 0 4px 10px rgba(52, 152, 219, 0.3);
}
.form-control:focus {
  border-color: #3498db;
  box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.15);
}

/* --- BẢNG KHÁCH HÀNG --- */
.table-container {
  background-color: #ffffff;
  border-radius: 12px;
  box-shadow: 0 4px 25px rgba(0,0,0,0.06);
  overflow: hidden;
}
.customer-table thead th {
  background-color: #f8f9fa;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: #495057;
  border-bottom: 2px solid #dee2e6;
}
.customer-table tbody tr {
  transition: background-color 0.2s ease-in-out;
}
.customer-table tbody tr:hover {
  background-color: #f9fafb;
}
.customer-avatar {
  width: 45px;
  height: 45px;
  border-radius: 50%;
  background-color: #eaf6fb;
  color: #3498db;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 1rem;
}
.customer-name {
  color: #2c3e50;
  font-weight: 600;
}

/* --- MODAL CHI TIẾT --- */
.modal-content {
  border-radius: 12px;
}
.modal-header {
  background-color: #f8f9fa;
  border-bottom: 1px solid #dee2e6;
}
.modal-title {
  font-weight: 600;
  color: #34495e;
}
.info-block {
    background-color: #f8f9fa;
    border-radius: 8px;
    padding: 1.5rem;
    border: 1px solid #e9ecef;
}
.info-title {
  font-weight: 600;
  color: #34495e;
  margin-bottom: 1.5rem;
  padding-bottom: 0.5rem;
  border-bottom: 2px solid #3498db;
  display: inline-block;
}
.info-item {
    display: flex;
    align-items: flex-start;
    margin-bottom: 1rem;
    font-size: 0.95rem;
}
.info-item i {
    font-size: 1.25rem;
    color: #3498db;
    margin-right: 1rem;
    margin-top: 0.25rem;
    width: 20px;
    text-align: center;
}
.info-item div {
    display: flex;
    flex-direction: column;
}
.info-item label {
    color: #7f8c8d;
    font-size: 0.85rem;
    margin-bottom: 0.1rem;
}
.info-item strong {
    color: #2c3e50;
    font-weight: 600;
}

/* --- BẢNG LỊCH SỬ & BADGE TRẠNG THÁI --- */
.history-table {
  font-size: 0.9rem;
}
.history-table .badge {
  font-size: 0.8rem;
  padding: 0.4em 0.8em;
  font-weight: 600;
  border-radius: 20px;
  text-transform: capitalize;
}
.badge-primary { background-color: rgba(52, 152, 219, 0.1); color: #2980b9; }
.badge-success { background-color: rgba(46, 204, 113, 0.1); color: #27ae60; }
.badge-warning { background-color: rgba(241, 196, 15, 0.1); color: #f39c12; }
.badge-danger { background-color: rgba(231, 76, 60, 0.1); color: #c0392b; }
.badge-info { background-color: rgba(26, 188, 156, 0.1); color: #16a085; }
.badge-secondary { background-color: rgba(149, 165, 166, 0.1); color: #7f8c8d; }

/* --- PHÂN TRANG --- */
.pagination .page-link {
  border-radius: 0.3rem;
  margin: 0 3px;
  color: #3498db;
  border: 1px solid #dee2e6;
  font-weight: 500;
  transition: all 0.2s ease-in-out;
}
.pagination .page-link:hover {
  background-color: #eaf6fb;
  color: #2980b9;
}
.pagination .page-item.active .page-link {
  z-index: 3;
  color: #fff;
  background-color: #3498db;
  border-color: #3498db;
  box-shadow: 0 2px 5px rgba(52, 152, 219, 0.3);
}
.pagination .page-item.disabled .page-link {
  color: #adb5bd;
}
</style>