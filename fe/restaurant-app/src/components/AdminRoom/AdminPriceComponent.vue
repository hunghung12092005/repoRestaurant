<template>
  <div class="page-container">
    <div class="page-header mb-4">
      <h1 class="page-title">Quản Lý Giá Phòng</h1>
      <p class="page-subtitle">Tạo và quản lý các bảng giá theo mùa hoặc theo sự kiện.</p>
    </div>

    <div class="card filter-card mb-4">
      <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div class="col-md-5">
          <input
            v-model="searchKeyword"
            type="text"
            class="form-control"
            placeholder="Tìm theo tên loại phòng..."
            @input="logSearchKeyword"
          />
        </div>
        <button class="btn btn-primary" @click="openAddModal">
          <i class="bi bi-plus-circle me-2"></i>Thêm Bảng Giá
        </button>
      </div>
    </div>

    <div class="table-container">
      <table v-if="displayedPricings.length > 0" class="table booking-table align-middle">
        <thead>
          <tr>
            <th style="width: 25%">Loại Phòng & Mô tả</th>
            <th style="width: 20%">Ngày Áp Dụng</th>
            <th style="width: 20%">Giá (Đêm / Giờ)</th>
            <th class="text-center" style="width: 10%">Ưu tiên</th>
            <th class="text-center" style="width: 10%">Trạng Thái</th>
            <th class="text-center" style="width: 15%">Hành Động</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="isLoading">
            <td colspan="6" class="text-center p-5">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Đang tải...</span>
              </div>
            </td>
          </tr>
          <tr v-else v-for="pricing in displayedPricings" :key="pricing.price_id">
            <td>
              <div class="fw-bold type-name">{{ pricing.room_type?.type_name || 'Không xác định' }}</div>
              <p class="description-text mb-0">{{ pricing.description || 'Không có mô tả' }}</p>
            </td>
            <td>
              <div class="occupancy-info">
                <div>
                  <i class="bi bi-calendar-check-fill me-2 text-success"></i>{{ formatDate(pricing.start_date) }}
                </div>
                <div>
                  <i class="bi bi-calendar-x-fill me-2 text-danger"></i>{{ formatDate(pricing.end_date) }}
                </div>
              </div>
            </td>
            <td>
              <div class="occupancy-info">
                  <div><strong>Đêm:</strong> {{ formatPrice(pricing.price_per_night) }}</div>
                  <div><strong>Giờ:</strong> {{ formatPrice(pricing.hourly_price) }}</div>
              </div>
            </td>
            <td class="text-center">
              <span class="badge" :class="pricing.priority ? 'badge-success' : 'badge-secondary'">{{ pricing.priority ? 'Ưu Tiên' : 'Không' }}</span>
            </td>
            <td class="text-center">
              <span class="badge" :class="getStatusBadge(pricing.is_active)">
                {{ formatStatus(pricing.is_active) }}
              </span>
            </td>
            <td class="text-center action-buttons">
              <button
                class="btn btn-sm"
                :class="pricing.is_active ? 'btn-outline-warning' : 'btn-outline-success'"
                :title="pricing.is_active ? 'Hủy kích hoạt' : 'Kích hoạt'"
                @click="toggleActivation(pricing)"
              >
                <i :class="pricing.is_active ? 'bi bi-toggle-off' : 'bi bi-toggle-on'"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
      <div v-else-if="!isLoading" class="alert alert-light text-center m-0">
        Không có dữ liệu giá phòng phù hợp.
      </div>
    </div>

    <nav v-if="totalPages > 1" aria-label="Page navigation" class="d-flex justify-content-between align-items-center mt-4">
        <div class="text-muted">
            Hiển thị {{ displayedPricings.length }} trên tổng số {{ totalItems }} mục
        </div>
        <ul class="pagination justify-content-center mb-0">
            <li class="page-item" :class="{ disabled: currentPage === 1 }">
            <a class="page-link" href="#" @click.prevent="currentPage = 1">««</a>
            </li>
            <li class="page-item" :class="{ disabled: currentPage === 1 }">
            <a class="page-link" href="#" @click.prevent="currentPage--">«</a>
            </li>
            <li v-for="page in pageRange" :key="page" class="page-item" :class="{ active: page === currentPage }">
            <a class="page-link" href="#" @click.prevent="currentPage = page">{{ page }}</a>
            </li>
            <li class="page-item" :class="{ disabled: currentPage === totalPages }">
            <a class="page-link" href="#" @click.prevent="currentPage++">»</a>
            </li>
            <li class="page-item" :class="{ disabled: currentPage === totalPages }">
            <a class="page-link" href="#" @click.prevent="currentPage = totalPages">»»</a>
            </li>
        </ul>
    </nav>

    <div v-if="isModalOpen" class="modal-backdrop fade show"></div>
    <div v-if="isModalOpen" class="modal fade show d-block" tabindex="-1">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content modal-custom">
          <div class="modal-header modal-header-custom">
            <h5 class="modal-title">Thêm Bảng Giá Mới</h5>
            <button type="button" class="btn-close" @click="closeModal" aria-label="Close"></button>
          </div>
          <div class="modal-body p-4">
             <div v-if="modalErrorMessage" class="alert alert-warning">{{ modalErrorMessage }}</div>
            <form @submit.prevent="savePricing">
              <div class="row g-3">
                 <div class="col-12">
                  <label class="form-label">Loại Phòng</label>
                  <select v-model="newPricing.type_id" class="form-select" required :class="{ 'is-invalid': errors.type_id }">
                    <option value="">-- Chọn loại phòng --</option>
                    <option v-for="type in roomTypes" :key="type.type_id" :value="type.type_id">
                      {{ type.type_name }}
                    </option>
                  </select>
                  <div v-if="errors.type_id" class="invalid-feedback">{{ errors.type_id }}</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Giá mỗi đêm (VNĐ)</label>
                    <input type="number" v-model.number="newPricing.price_per_night" class="form-control" min="0" required :class="{ 'is-invalid': errors.price_per_night }"/>
                    <div v-if="errors.price_per_night" class="invalid-feedback">{{ errors.price_per_night }}</div>
                </div>
                 <div class="col-md-6">
                    <label class="form-label">Giá mỗi giờ (VNĐ)</label>
                    <input type="number" v-model.number="newPricing.hourly_price" class="form-control" min="0" required :class="{ 'is-invalid': errors.hourly_price }"/>
                    <div v-if="errors.hourly_price" class="invalid-feedback">{{ errors.hourly_price }}</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Ngày bắt đầu</label>
                    <input type="date" v-model="newPricing.start_date" class="form-control" :min="today" required :class="{ 'is-invalid': errors.start_date }"/>
                    <div v-if="errors.start_date" class="invalid-feedback">{{ errors.start_date }}</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Ngày kết thúc</label>
                    <input type="date" v-model="newPricing.end_date" class="form-control" :min="newPricing.start_date || today" required :class="{ 'is-invalid': errors.end_date }"/>
                    <div v-if="errors.end_date" class="invalid-feedback">{{ errors.end_date }}</div>
                </div>
                <div class="col-12">
                  <div class="form-check">
                    <input type="checkbox" v-model="newPricing.is_priority" class="form-check-input" id="is_priority" />
                    <label class="form-check-label" for="is_priority">Áp dụng ưu tiên</label>
                  </div>
                </div>
                 <div class="col-12">
                    <label class="form-label">Mô tả</label>
                    <textarea v-model="newPricing.description" class="form-control" rows="2" :class="{ 'is-invalid': errors.description }"></textarea>
                    <div v-if="errors.description" class="invalid-feedback">{{ errors.description }}</div>
                </div>
              </div>
            </form>
          </div>
          <div class="modal-footer modal-footer-custom">
            <button type="button" class="btn btn-secondary" @click="closeModal" :disabled="isLoading">Hủy</button>
            <button type="button" class="btn btn-primary" @click="savePricing" :disabled="isLoading">
                <span v-if="isLoading" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                Lưu Lại
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import axiosInstance from '../../axiosConfig.js';

const seasonalPricings = ref([]);
const roomTypes = ref([]);
const searchKeyword = ref('');
const isModalOpen = ref(false);
const isLoading = ref(false);
const newPricing = ref({
  type_id: '',
  start_date: '',
  end_date: '',
  description: '',
  price_per_night: 0,
  hourly_price: 0,
  is_priority: false, 
  is_active: true
});
const errors = ref({
  start_date: '',
  end_date: '',
  description: '',
  type_id: '',
  price_per_night: '',
  hourly_price: ''
});
const modalErrorMessage = ref('');
const currentPage = ref(1);
const itemsPerPage = ref(10);
const totalItems = ref(0);

const today = computed(() => new Date().toISOString().split('T')[0]);

const formatPrice = (price) => {
  return price ? new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price) : '0 ₫';
};

const formatDate = (date) => {
  return date ? new Date(date).toLocaleDateString('vi-VN') : 'N/A';
};

const logSearchKeyword = () => {
  console.log('Từ khóa tìm kiếm:', searchKeyword.value);
};

const fetchPricings = async (page = 1) => {
  isLoading.value = true;
  try {
    const response = await axiosInstance.get(`/api/prices?page=${page}&per_page=${itemsPerPage.value}`);
    seasonalPricings.value = Array.isArray(response.data.data) ? response.data.data.map(item => ({
      ...item,
      is_active: Boolean(item.is_active) // Chuyển thành boolean
    })) : [];
    console.log('Updated seasonalPricings:', seasonalPricings.value);
    totalItems.value = response.data.total || 0;
    currentPage.value = response.data.current_page || 1;
  } catch (error) {
    console.error('Lỗi khi lấy danh sách giá:', error);
    seasonalPricings.value = [];
    totalItems.value = 0;
  } finally {
    isLoading.value = false;
  }
};

const fetchRoomTypes = async () => {
  try {
    const response = await axiosInstance.get('/api/room-types', { params: { per_page: 'all' } });
    roomTypes.value = Array.isArray(response.data.data) ? [...response.data.data] : [];
  } catch (error) {
    console.error('Lỗi khi lấy danh sách loại phòng:', error);
  }
};

onMounted(async () => {
  isLoading.value = true;
  try {
    await Promise.all([fetchPricings(), fetchRoomTypes()]);
  } catch (error) {
    console.error('Lỗi khi khởi tạo dữ liệu:', error);
  } finally {
    isLoading.value = false;
  }
});

watch(currentPage, (newPage) => {
  fetchPricings(newPage);
});

const filteredPricings = computed(() => {
  if (!Array.isArray(seasonalPricings.value)) return [];
  if (!searchKeyword.value.trim()) return seasonalPricings.value;
  return seasonalPricings.value.filter(pricing => {
    const typeName = pricing.room_type?.type_name?.toLowerCase() || '';
    return typeName.includes(searchKeyword.value.toLowerCase().trim());
  });
});


const displayedPricings = computed(() => filteredPricings.value);
const totalPages = computed(() => Math.ceil(totalItems.value / itemsPerPage.value));

const pageRange = computed(() => {
  const maxPages = 5;
  let start = Math.max(1, currentPage.value - Math.floor(maxPages / 2));
  let end = Math.min(totalPages.value, start + maxPages - 1);
  if (end - start + 1 < maxPages) {
    start = Math.max(1, end - maxPages + 1);
  }
  return Array.from({ length: end - start + 1 }, (_, i) => start + i);
});

const openAddModal = () => {
  newPricing.value = {
    type_id: '',
    start_date: '',
    end_date: '',
    description: '',
    price_per_night: 0,
    hourly_price: 0,
    is_priority: false,
    is_active: true
  };
  errors.value = {};
  isModalOpen.value = true;
  modalErrorMessage.value = '';
};

const closeModal = () => {
  isModalOpen.value = false;
  errors.value = {};
  modalErrorMessage.value = '';
};

const validateForm = () => {
  errors.value = {};
  let isValid = true;
  if (!newPricing.value.start_date) {
    errors.value.start_date = 'Vui lòng chọn ngày bắt đầu';
    isValid = false;
  }
  if (!newPricing.value.end_date) {
    errors.value.end_date = 'Vui lòng chọn ngày kết thúc';
    isValid = false;
  }
  if (new Date(newPricing.value.start_date) > new Date(newPricing.value.end_date)) {
    errors.value.end_date = 'Ngày kết thúc phải sau hoặc bằng ngày bắt đầu';
    isValid = false;
  }
  if (!newPricing.value.type_id) {
    errors.value.type_id = 'Vui lòng chọn loại phòng';
    isValid = false;
  }
  if (isNaN(newPricing.value.price_per_night) || newPricing.value.price_per_night < 0) {
    errors.value.price_per_night = 'Giá đêm phải là số không âm';
    isValid = false;
  }
  if (isNaN(newPricing.value.hourly_price) || newPricing.value.hourly_price < 0) {
    errors.value.hourly_price = 'Giá giờ phải là số không âm';
    isValid = false;
  }
  return isValid;
};

const savePricing = async () => {
  if (!validateForm()) {
    modalErrorMessage.value = 'Vui lòng kiểm tra thông tin nhập.';
    return;
  }

  isLoading.value = true;
  modalErrorMessage.value = '';
  const payload = { 
    ...newPricing.value,
    priority: newPricing.value.is_priority ? 1 : 0 
  };
  delete payload.is_priority; 
  try {
    await axiosInstance.post('/api/prices', payload);
    await fetchPricings(currentPage.value);
    closeModal();
    alert('Lưu giá phòng thành công!');
  } catch (error) {
    console.error('Lỗi khi lưu giá:', error);
    modalErrorMessage.value = error.response?.data?.message || 'Lưu giá phòng thất bại.';
    if (error.response?.status === 422) {
      errors.value = { ...error.response.data?.errors };
      modalErrorMessage.value += ': ' + Object.values(error.response.data?.errors).flat().join(', ');
    }
  } finally {
    isLoading.value = false;
  }
};

const toggleActivation = async (pricing) => {
  if (!confirm(`Bạn có chắc chắn muốn ${pricing.is_active ? 'hủy kích hoạt' : 'kích hoạt'} giá phòng này?`)) return;

  isLoading.value = true;
  try {
    await axiosInstance.put(`/api/prices/${pricing.price_id}`, {
      is_active: !pricing.is_active
    });
    await fetchPricings(currentPage.value);
    alert(`${pricing.is_active ? 'Hủy kích hoạt' : 'Kích hoạt'} giá phòng thành công!`);
  } catch (error) {
    console.error('Lỗi khi thay đổi trạng thái:', error.response?.data || error);
    alert(error.response?.data?.message || 'Thay đổi trạng thái giá phòng thất bại.');
  } finally {
    isLoading.value = false;
  }
};

const formatStatus = (isActive) => {
    return isActive ? 'Kích hoạt' : 'Hủy kích hoạt';
}

const getStatusBadge = (isActive) => {
    return isActive ? 'badge-success' : 'badge-secondary';
}
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
.filter-card {
  background-color: #ffffff;
  border: none;
  border-radius: 12px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
}
.form-control,
.form-select {
  border-radius: 8px;
  border: 1px solid #e5eaee;
  transition: all 0.2s ease-in-out;
  font-size: 0.9rem;
}
.form-control:focus,
.form-select:focus {
  border-color: #3498db;
  box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.15);
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
  padding: 1.25rem 1rem;
  border-bottom: 1px solid #e5eaee;
}
.booking-table tbody tr:last-child td {
  border-bottom: none;
}
.booking-table tbody tr:hover {
  background-color: #f9fafb;
}
.type-name {
  font-size: 1rem;
}
.description-text {
  font-size: 0.8rem;
  color: #7f8c8d;
  max-width: 250px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.occupancy-info {
  font-size: 0.85rem;
  color: #34495e;
}

.badge {
  padding: 0.4em 0.8em;
  font-size: 0.75rem;
  font-weight: 600;
  border-radius: 20px;
  letter-spacing: 0.5px;
}
.badge-secondary { background-color: #f3f4f6; color: #7f8c8d; }
.badge-success { background-color: #e6f9f0; color: #2ecc71; }
.badge-priority { background-color: #e8eaf6; color: #3f51b5; }


.action-buttons {
  white-space: nowrap;
}
.action-buttons .btn {
  margin: 0 2px;
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

.modal-backdrop {
  background-color: rgba(0, 0, 0, 0.4);
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
.badge-success { 
  background-color: #e6f9f0; 
  color: #2ecc71; 
} 
.badge-secondary { 
  background-color: #f3f4f6; 
  color: #7f8c8d; 
  }
</style>