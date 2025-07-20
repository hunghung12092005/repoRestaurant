<template>
  <div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="fw-bold text-sea-green">Quản Lý Giá Phòng</h1>
      <button class="btn btn-success shadow" @click="openAddModal">
        <i class="bi bi-plus-circle me-2"></i>Thêm Giá Phòng Mới
      </button>
    </div>

    <!-- Tìm kiếm -->
    <div class="row mb-4 g-3">
      <div class="col-md-4">
        <input
          v-model="searchKeyword"
          type="text"
          class="form-control"
          placeholder="Tìm tên phòng hoặc loại phòng..."
          @input="logSearchKeyword"
        />
      </div>
    </div>

    <!-- Danh sách giá phòng -->
    <div class="table-responsive">
      <table class="table table-bordered table-hover align-middle">
        <thead>
          <tr>
            <th>STT</th>
            <th>Loại Phòng</th>
            <th>Ngày Bắt Đầu</th>
            <th>Ngày Kết Thúc</th>
            <th>Giá Đêm (VNĐ)</th>
            <th>Giá Giờ (VNĐ)</th>
            <th>Ưu Tiên</th>
            <th>Hành Động</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="isLoading">
            <td colspan="8" class="text-center">Đang tải dữ liệu...</td>
          </tr>
          <tr v-else-if="displayedPricings.length === 0">
            <td colspan="8" class="text-center text-muted">
              Không có giá phòng nào phù hợp
            </td>
          </tr>
          <tr v-else v-for="(pricing, index) in displayedPricings" :key="pricing.price_id">
            <td>{{ (currentPage - 1) * itemsPerPage + index + 1 }}</td>
            <td>{{ pricing.room_type?.type_name || 'Không xác định' }}</td>
            <td>{{ formatDate(pricing.start_date) }}</td>
            <td>{{ formatDate(pricing.end_date) }}</td>
            <td>{{ formatPrice(pricing.price_per_night) }}</td>
            <td>{{ formatPrice(pricing.hourly_price) }}</td>
            <td>{{ pricing.priority }}</td>
            <td>
              <button
                class="btn btn-sm"
                :class="pricing.is_active ? 'btn-warning' : 'btn-success'"
                @click="toggleActivation(pricing)"
              >
                <i :class="pricing.is_active ? 'bi bi-pause-circle' : 'bi bi-play-circle'"></i>
                {{ pricing.is_active ? 'Hủy kích hoạt' : 'Kích hoạt' }}
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Phân trang -->
    <div v-if="totalItems > 0" class="d-flex justify-content-between align-items-center mt-4">
      <div class="text-muted">
        Hiển thị {{ displayedPricings.length }} / {{ totalItems }} giá phòng
      </div>
      <nav>
        <ul class="pagination mb-0">
          <li class="page-item" :class="{ disabled: currentPage === 1 }">
            <button class="page-link" @click="currentPage = 1">
              <i class="bi bi-chevron-double-left"></i>
            </button>
          </li>
          <li class="page-item" :class="{ disabled: currentPage === 1 }">
            <button class="page-link" @click="currentPage--">
              <i class="bi bi-chevron-left"></i>
            </button>
          </li>
          <li
            v-for="page in pageRange"
            :key="page"
            class="page-item"
            :class="{ active: currentPage === page }"
          >
            <button class="page-link" @click="currentPage = page">{{ page }}</button>
          </li>
          <li class="page-item" :class="{ disabled: currentPage === totalPages }">
            <button class="page-link" @click="currentPage++">
              <i class="bi bi-chevron-right"></i>
            </button>
          </li>
          <li class="page-item" :class="{ disabled: currentPage === totalPages }">
            <button class="page-link" @click="currentPage = totalPages">
              <i class="bi bi-chevron-double-right"></i>
            </button>
          </li>
        </ul>
      </nav>
    </div>

    <!-- Modal thêm -->
    <div
      v-if="isModalOpen"
      class="modal fade show d-block"
      tabindex="-1"
      style="background-color: rgba(0, 0, 0, 0.5)"
    >
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Thêm Giá Phòng Mới</h5>
            <button type="button" class="btn-close" @click="closeModal"></button>
          </div>
          <div class="modal-body">
            <div v-if="isLoading" class="text-center mb-3">
              <div class="spinner-border text-primary">
                <span class="visually-hidden">Đang tải...</span>
              </div>
            </div>
            <div v-if="modalErrorMessage" class="alert alert-warning">{{ modalErrorMessage }}</div>
            <form @submit.prevent="savePricing">
              <div class="mb-3">
                <label class="form-label">Ngày Bắt Đầu</label>
                <input
                  type="date"
                  v-model="newPricing.start_date"
                  class="form-control"
                  :min="today"
                  required
                  :class="{ 'is-invalid': errors.start_date }"
                />
                <div v-if="errors.start_date" class="invalid-feedback">{{ errors.start_date }}</div>
              </div>
              <div class="mb-3">
                <label class="form-label">Ngày Kết Thúc</label>
                <input
                  type="date"
                  v-model="newPricing.end_date"
                  class="form-control"
                  :min="newPricing.start_date || today"
                  required
                  :class="{ 'is-invalid': errors.end_date }"
                />
                <div v-if="errors.end_date" class="invalid-feedback">{{ errors.end_date }}</div>
              </div>
              <div class="mb-3">
                <label class="form-label">Mô Tả</label>
                <textarea
                  v-model="newPricing.description"
                  class="form-control"
                  :class="{ 'is-invalid': errors.description }"
                ></textarea>
                <div v-if="errors.description" class="invalid-feedback">{{ errors.description }}</div>
              </div>
              <div class="mb-3">
                <label class="form-label">Loại Phòng</label>
                <select
                  v-model="newPricing.type_id"
                  class="form-control"
                  required
                  :class="{ 'is-invalid': errors.type_id }"
                >
                  <option value="">-- Chọn loại phòng --</option>
                  <option v-for="type in roomTypes" :key="type.type_id" :value="type.type_id">
                    {{ type.type_name }}
                  </option>
                </select>
                <div v-if="errors.type_id" class="invalid-feedback">{{ errors.type_id }}</div>
              </div>
              <div class="mb-3">
                <label class="form-label">Giá Đêm (VNĐ)</label>
                <input
                  type="number"
                  v-model.number="newPricing.price_per_night"
                  class="form-control"
                  min="0"
                  required
                  :class="{ 'is-invalid': errors.price_per_night }"
                />
                <div v-if="errors.price_per_night" class="invalid-feedback">{{ errors.price_per_night }}</div>
              </div>
              <div class="mb-3">
                <label class="form-label">Giá Giờ (VNĐ)</label>
                <input
                  type="number"
                  v-model.number="newPricing.hourly_price"
                  class="form-control"
                  min="0"
                  required
                  :class="{ 'is-invalid': errors.hourly_price }"
                />
                <div v-if="errors.hourly_price" class="invalid-feedback">{{ errors.hourly_price }}</div>
              </div>
              <div class="mb-3">
                <label class="form-label">Ưu Tiên (0-10, càng cao càng ưu tiên)</label>
                <input
                  type="number"
                  v-model.number="newPricing.priority"
                  class="form-control"
                  min="0"
                  max="10"
                  required
                  :class="{ 'is-invalid': errors.priority }"
                />
                <div v-if="errors.priority" class="invalid-feedback">{{ errors.priority }}</div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" @click="closeModal" :disabled="isLoading">Hủy</button>
                <button type="submit" class="btn btn-success" :disabled="isLoading">Lưu</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import axios from 'axios';

// Cấu hình API client
const apiClient = axios.create({
  baseURL: 'http://127.0.0.1:8000/api',
  timeout: 60000,
  headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }
});

// State
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
  priority: 0,
  is_active: true // Sử dụng boolean
});
const errors = ref({
  start_date: '',
  end_date: '',
  description: '',
  type_id: '',
  price_per_night: '',
  hourly_price: '',
  priority: ''
});
const modalErrorMessage = ref('');
const currentPage = ref(1);
const itemsPerPage = ref(10);
const totalItems = ref(0);

// Ngày hiện tại
const today = computed(() => new Date().toISOString().split('T')[0]);

// Format giá
const formatPrice = (price) => {
  return price ? new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price) : '0 ₫';
};

// Format ngày
const formatDate = (date) => {
  return date ? new Date(date).toLocaleDateString('vi-VN') : '';
};

// Log từ khóa tìm kiếm
const logSearchKeyword = () => {
  console.log('Từ khóa tìm kiếm:', searchKeyword.value);
};

// Lấy danh sách giá
const fetchPricings = async (page = 1) => {
  isLoading.value = true;
  modalErrorMessage.value = '';
  try {
    const response = await apiClient.get(`/prices?page=${page}&per_page=${itemsPerPage.value}`);
    seasonalPricings.value = Array.isArray(response.data.data) ? response.data.data.map(item => ({
      ...item,
      is_active: Boolean(item.is_active) // Chuyển thành boolean
    })) : [];
    console.log('Updated seasonalPricings:', seasonalPricings.value);
    totalItems.value = response.data.total || 0;
    currentPage.value = response.data.current_page || 1;
  } catch (error) {
    console.error('Lỗi khi lấy danh sách giá:', error);
    modalErrorMessage.value = error.response?.data?.message || 'Không thể tải danh sách giá phòng.';
    seasonalPricings.value = [];
    totalItems.value = 0;
  } finally {
    isLoading.value = false;
  }
};

// Lấy danh sách loại phòng
const fetchRoomTypes = async () => {
  try {
    const response = await apiClient.get('/room-types');
    roomTypes.value = Array.isArray(response.data.data) ? [...response.data.data] : [];
  } catch (error) {
    console.error('Lỗi khi lấy danh sách loại phòng:', error);
    modalErrorMessage.value = error.response?.data?.message || 'Không thể tải danh sách loại phòng.';
  }
};

// Khởi tạo dữ liệu
onMounted(async () => {
  isLoading.value = true;
  try {
    await Promise.all([fetchPricings(), fetchRoomTypes()]);
  } catch (error) {
    console.error('Lỗi khi khởi tạo dữ liệu:', error);
    modalErrorMessage.value = 'Khởi tạo dữ liệu thất bại.';
  } finally {
    isLoading.value = false;
  }
});

// Theo dõi thay đổi trang
watch(currentPage, (newPage) => {
  fetchPricings(newPage);
});

// Lọc giá
const filteredPricings = computed(() => {
  if (!Array.isArray(seasonalPricings.value)) return [];
  if (!searchKeyword.value.trim()) return seasonalPricings.value;
  return seasonalPricings.value.filter(pricing => {
    const typeName = pricing.room_type?.type_name?.toLowerCase() || '';
    return typeName.includes(searchKeyword.value.toLowerCase().trim());
  });
});

// Danh sách giá hiển thị
const displayedPricings = computed(() => filteredPricings.value);

// Tính tổng số trang
const totalPages = computed(() => Math.ceil(totalItems.value / itemsPerPage.value));

// Tính phạm vi trang
const pageRange = computed(() => {
  const maxPages = 5;
  let start = Math.max(1, currentPage.value - Math.floor(maxPages / 2));
  let end = Math.min(totalPages.value, start + maxPages - 1);
  if (end - start + 1 < maxPages) {
    start = Math.max(1, end - maxPages + 1);
  }
  return Array.from({ length: end - start + 1 }, (_, i) => start + i);
});

// Mở modal thêm
const openAddModal = () => {
  newPricing.value = {
    type_id: '',
    start_date: '',
    end_date: '',
    description: '',
    price_per_night: 0,
    hourly_price: 0,
    priority: 0,
    is_active: true
  };
  errors.value = {};
  isModalOpen.value = true;
  modalErrorMessage.value = '';
};

// Đóng modal
const closeModal = () => {
  isModalOpen.value = false;
  errors.value = {};
  modalErrorMessage.value = '';
  newPricing.value = {
    type_id: '',
    start_date: '',
    end_date: '',
    description: '',
    price_per_night: 0,
    hourly_price: 0,
    priority: 0,
    is_active: true
  };
};

// Kiểm tra form
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
  if (isNaN(newPricing.value.priority) || newPricing.value.priority < 0 || newPricing.value.priority > 10) {
    errors.value.priority = 'Ưu tiên phải từ 0 đến 10';
    isValid = false;
  }

  return isValid;
};

// Lưu giá
const savePricing = async () => {
  if (!validateForm()) {
    modalErrorMessage.value = 'Vui lòng kiểm tra thông tin nhập.';
    return;
  }

  isLoading.value = true;
  modalErrorMessage.value = '';
  const payload = {
    type_id: newPricing.value.type_id,
    start_date: newPricing.value.start_date,
    end_date: newPricing.value.end_date,
    description: newPricing.value.description.trim() || null,
    price_per_night: newPricing.value.price_per_night,
    hourly_price: newPricing.value.hourly_price,
    priority: newPricing.value.priority,
    is_active: newPricing.value.is_active
  };
  try {
    const response = await apiClient.post('/prices', payload);
    await fetchPricings(currentPage.value);
    closeModal();
    modalErrorMessage.value = 'Lưu giá phòng thành công!';
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

// Kích hoạt/Hủy kích hoạt giá
const toggleActivation = async (pricing) => {
  if (!confirm(`Bạn có chắc chắn muốn ${pricing.is_active ? 'hủy kích hoạt' : 'kích hoạt'} giá phòng này?`)) return;

  isLoading.value = true;
  modalErrorMessage.value = '';
  try {
    console.log('Sending PUT request for price_id:', pricing.price_id, 'with is_active:', !pricing.is_active);
    const response = await apiClient.put(`/prices/${pricing.price_id}`, {
      is_active: !pricing.is_active // Gửi boolean
    });
    console.log('API response:', response.data);
    await fetchPricings(currentPage.value); // Làm mới danh sách
    modalErrorMessage.value = `${pricing.is_active ? 'Hủy kích hoạt' : 'Kích hoạt'} giá phòng thành công!`;
  } catch (error) {
    console.error('Lỗi khi thay đổi trạng thái:', error.response?.data || error);
    modalErrorMessage.value = error.response?.data?.message || 'Thay đổi trạng thái giá phòng thất bại.';
  } finally {
    isLoading.value = false;
  }
};
</script>

<style scoped>
th {
  background-color: #78c1f1;
  text-align: center;
}

.text-sea-green {
  background: linear-gradient(135deg, #3f8dd6, #2acabd);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}
.table thead {
  background: linear-gradient(135deg, #3f8dd6, #2acabd);
  color: white;
}
.table tbody tr:hover {
  background-color: #e6f4ea;
}
.modal-header {
  background: linear-gradient(135deg, #3f8dd6, #2acabd);
  color: white;
}
.btn-success {
  background: linear-gradient(135deg, #1199f3, #2acabd);
  border: none;
}
.btn-success:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}
.btn-warning {
  background: linear-gradient(135deg, #ffc107, #e0a800);
  border: none;
}
.btn-warning:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}
</style>