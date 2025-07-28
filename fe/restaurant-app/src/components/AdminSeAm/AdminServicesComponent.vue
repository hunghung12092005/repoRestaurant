<template>
  <div class="page-container">
    <!-- Tiêu đề trang -->
    <div class="page-header mb-4">
      <h1 class="page-title">Quản Lý Dịch Vụ</h1>
      <p class="page-subtitle">Tạo và quản lý các dịch vụ cộng thêm của khách sạn.</p>
    </div>

    <!-- Thông báo -->
    <div v-if="successMessage" class="alert alert-success">{{ successMessage }}</div>
    <div v-if="errorMessage" class="alert alert-danger">{{ errorMessage }}</div>

    <!-- Bộ lọc và tìm kiếm -->
    <div class="card filter-card mb-4">
      <div class="card-body d-flex justify-content-between align-items-center">
        <div class="col-md-5">
          <input
            v-model="searchQuery"
            type="text"
            class="form-control"
            placeholder="Tìm theo tên hoặc mô tả dịch vụ..."
          />
        </div>
        <button class="btn btn-primary" @click="openAddModal">
          <i class="bi bi-plus-circle me-2"></i>Thêm Dịch Vụ
        </button>
      </div>
    </div>

    <!-- Bảng danh sách dịch vụ -->
    <div class="table-container">
      <table class="table booking-table align-middle">
        <thead>
          <tr>
            <th style="width: 50%;">Dịch Vụ & Mô Tả</th>
            <th style="width: 20%;">Giá</th>
            <th class="text-center" style="width: 30%;">Hành Động</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="service in displayedServices" :key="service.service_id">
            <td>
              <div class="fw-bold type-name">{{ service.service_name }}</div>
              <p class="description-text mb-0">{{ service.description || 'Không có mô tả' }}</p>
            </td>
            <td>
              <span class="price-tag">{{ formatPrice(service.price) }}</span>
            </td>
            <td class="text-center action-buttons">
              <button class="btn btn-outline-primary btn-sm" title="Sửa" @click="openEditModal(service)">
                <i class="bi bi-pencil-fill"></i> Sửa
              </button>
              <button class="btn btn-outline-danger btn-sm" title="Xóa" @click="deleteService(service.service_id)">
                <i class="bi bi-trash-fill"></i> Xóa
              </button>
            </td>
          </tr>
          <tr v-if="displayedServices.length === 0">
            <td colspan="3" class="text-center py-5">Không tìm thấy dịch vụ nào.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Phân trang -->
    <nav v-if="totalPages > 1" aria-label="Page navigation" class="mt-4">
      <ul class="pagination justify-content-center">
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

    <!-- Modal thêm/sửa -->
    <div v-if="isModalOpen" class="modal-backdrop fade show"></div>
    <div v-if="isModalOpen" class="modal fade show d-block" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-custom">
          <div class="modal-header modal-header-custom">
            <h5 class="modal-title">{{ currentService ? 'Cập nhật Dịch Vụ' : 'Thêm Dịch Vụ Mới' }}</h5>
            <button type="button" class="btn-close" @click="closeModal"></button>
          </div>
          <form @submit.prevent="saveService">
            <div class="modal-body p-4">
              <div v-if="modalErrorMessage" class="alert alert-warning small p-2">{{ modalErrorMessage }}</div>
              <div class="mb-3">
                <label class="form-label">Tên Dịch Vụ</label>
                <input
                  type="text"
                  v-model.trim="form.service_name"
                  @input="onInputServiceName"
                  class="form-control"
                  required
                  placeholder="Nhập tên dịch vụ"
                  :class="{ 'is-invalid': errors.service_name }"
                />
                <div v-if="errors.service_name" class="invalid-feedback">{{ errors.service_name }}</div>
              </div>
              <div class="mb-3">
                <label class="form-label">Giá (VNĐ)</label>
                <input
                  type="number"
                  v-model.number="form.price"
                  @input="onInputPrice"
                  class="form-control"
                  required
                  min="0"
                  placeholder="Nhập giá dịch vụ"
                  :class="{ 'is-invalid': errors.price }"
                />
                <div v-if="errors.price" class="invalid-feedback">{{ errors.price }}</div>
              </div>
              <div class="mb-3">
                <label class="form-label">Mô Tả</label>
                <textarea
                  v-model="form.description"
                  @input="onInputDescription"
                  class="form-control"
                  rows="3"
                  placeholder="Nhập mô tả dịch vụ"
                ></textarea>
              </div>
            </div>
            <div class="modal-footer modal-footer-custom">
              <button type="button" class="btn btn-secondary" @click="closeModal">Hủy</button>
              <button type="submit" class="btn btn-primary">Lưu</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
// --- TOÀN BỘ SCRIPT CỦA BẠN GIỮ NGUYÊN ---
import { ref, computed, watch, onMounted } from 'vue';
import axios from 'axios';

const apiClient = axios.create({
  baseURL: 'http://127.0.0.1:8000',
  timeout: 30000,
  headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }
});

const services = ref([]);
const searchQuery = ref('');
const isModalOpen = ref(false);
const currentService = ref(null);
const successMessage = ref('');
const errorMessage = ref('');
const modalErrorMessage = ref('');
const currentPage = ref(1);
const itemsPerPage = 10;
const totalItems = ref(0);
const form = ref({
  service_name: '',
  price: 0,
  description: ''
});
const errors = ref({
  service_name: '',
  price: ''
});

const formatPrice = (price) => {
  return price ? new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price) : '0 ₫';
};

const fetchServices = async (page = 1) => {
  errorMessage.value = '';
  try {
    const response = await apiClient.get(`/services?page=${page}&per_page=${itemsPerPage}`);
    console.log('Services API response:', JSON.stringify(response.data, null, 2));
    services.value = Array.isArray(response.data.data) ? response.data.data : (response.data.data?.data || []);
    totalItems.value = response.data.total || 0;
    currentPage.value = response.data.current_page || 1;
  } catch (error) {
    console.error('Fetch services error:', {
      message: error.message,
      response: error.response?.data,
      status: error.response?.status
    });
    errorMessage.value = error.response?.data?.message || 'Không thể tải danh sách dịch vụ. Vui lòng kiểm tra kết nối server.';
    services.value = [];
    totalItems.value = 0;
  }
};

onMounted(() => {
    fetchServices();
});

watch(currentPage, (newPage) => {
  fetchServices(newPage);
});

const filteredServices = computed(() => {
  if (!Array.isArray(services.value)) return [];
  return services.value.filter(service => {
    const serviceName = service.service_name?.toLowerCase() || '';
    const description = service.description?.toLowerCase() || '';
    return (
      serviceName.includes(searchQuery.value.toLowerCase()) ||
      description.includes(searchQuery.value.toLowerCase())
    );
  });
});

const totalPages = computed(() => Math.ceil(totalItems.value / itemsPerPage));

const displayedServices = computed(() => {
  return filteredServices.value;
});

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
  form.value = { service_name: '', price: 0, description: '' };
  errors.value = {};
  currentService.value = null;
  isModalOpen.value = true;
  successMessage.value = '';
  modalErrorMessage.value = '';
  console.log('Opened add modal, form reset:', { ...form.value });
};

const openEditModal = (service) => {
  console.log('Opening edit modal for service:', JSON.stringify(service, null, 2));
  if (!service || typeof service !== 'object') {
    console.error('Invalid service data:', service);
    errorMessage.value = 'Dữ liệu dịch vụ không hợp lệ.';
    return;
  }
  form.value = {
    service_name: String(service.service_name || ''),
    price: Number(service.price) || 0,
    description: String(service.description || '')
  };
  currentService.value = { ...service };
  isModalOpen.value = true;
  errors.value = {};
  successMessage.value = '';
  modalErrorMessage.value = '';
  console.log('Edit modal opened, form set:', { ...form.value });
};

const closeModal = () => {
  isModalOpen.value = false;
  errors.value = {};
  modalErrorMessage.value = '';
  currentService.value = null;
  form.value = { service_name: '', price: 0, description: '' };
  console.log('Closed modal, form reset:', { ...form.value });
};

const onInputServiceName = (event) => {
  form.value.service_name = event.target.value;
  errors.value.service_name = '';
  modalErrorMessage.value = '';
  console.log('Service name input:', form.value.service_name);
};

const onInputPrice = (event) => {
  form.value.price = Number(event.target.value) || 0;
  errors.value.price = '';
  modalErrorMessage.value = '';
  console.log('Price input:', form.value.price);
};

const onInputDescription = (event) => {
  form.value.description = event.target.value;
  console.log('Description input:', form.value.description);
};

const validateForm = () => {
  errors.value = {};
  let isValid = true;

  if (!form.value.service_name || form.value.service_name.trim().length === 0) {
    errors.value.service_name = 'Vui lòng nhập tên dịch vụ';
    isValid = false;
  }
  if (form.value.price === null || form.value.price < 0 || isNaN(form.value.price)) {
    errors.value.price = 'Giá phải là số không âm';
    isValid = false;
  }

  console.log('Validation result:', { isValid, form: { ...form.value }, errors: errors.value });
  return isValid;
};

const saveService = async () => {
  console.log('Form data before validation:', { ...form.value });
  if (!validateForm()) {
    console.log('Validation failed, stopping save.');
    modalErrorMessage.value = 'Vui lòng kiểm tra thông tin nhập.';
    return;
  }
  modalErrorMessage.value = '';
  const payload = {
    service_name: form.value.service_name.trim(),
    price: form.value.price,
    description: form.value.description.trim() || null
  };
  console.log('Sending POST/PUT data:', payload);
  try {
    let response;
    if (currentService.value) {
      response = await apiClient.put(`/services/${currentService.value.service_id}`, payload);
      console.log('PUT response:', JSON.stringify(response.data, null, 2));
      const index = services.value.findIndex(s => s.service_id === currentService.value.service_id);
      if (index !== -1) {
        services.value[index] = response.data.data;
      }
      successMessage.value = 'Cập nhật dịch vụ thành công!';
    } else {
      response = await apiClient.post('/services', payload);
      console.log('POST response:', JSON.stringify(response.data, null, 2));
      services.value.push(response.data.data);
      successMessage.value = 'Thêm dịch vụ thành công!';
      await fetchServices(currentPage.value);
    }
    closeModal();
  } catch (error) {
    console.error('Save service error:', {
      message: error.message,
      response: error.response?.data,
      status: error.response?.status
    });
    const errorMsg = error.response?.data?.message || 'Lưu dịch vụ thất bại. Vui lòng thử lại.';
    modalErrorMessage.value = errorMsg;
    if (error.response?.status === 422) {
      const backendErrors = error.response.data?.errors || {};
      errors.value = { ...backendErrors };
      modalErrorMessage.value += ': ' + Object.values(backendErrors).flat().join(', ');
    } else if (error.code === 'ECONNABORTED') {
      modalErrorMessage.value = 'Yêu cầu timeout. Vui lòng kiểm tra kết nối server.';
    }
  }
};

const deleteService = async (service_id) => {
  if (!confirm('Bạn có chắc chắn muốn xóa dịch vụ này?')) return;
  errorMessage.value = '';
  try {
    await apiClient.delete(`/services/${service_id}`);
    services.value = services.value.filter(s => s.service_id !== service_id);
    if (displayedServices.value.length === 0 && currentPage.value > 1) {
      currentPage.value--;
    }
    successMessage.value = 'Xóa dịch vụ thành công!';
    await fetchServices(currentPage.value);
  } catch (error) {
    console.error('Delete service error:', {
      message: error.message,
      response: error.response?.data,
      status: error.response?.status
    });
    errorMessage.value = error.response?.data?.message || 'Xóa dịch vụ thất bại.';
  }
};
</script>

<style scoped>
/* Copied styles from other components for consistency */
@import url('https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700&display=swap');
@import url('https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css');

.page-container {
  font-family: 'Be Vietnam Pro', sans-serif;
  background-color: #f4f7f9;
  padding: 2rem;
  color: #34495e;
}
.page-header { border-bottom: 1px solid #e5eaee; padding-bottom: 1rem; }
.page-title { font-size: 2rem; font-weight: 700; }
.page-subtitle { font-size: 1rem; color: #7f8c8d; }
.filter-card { background-color: #ffffff; border: none; border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); }
.form-control, .form-select { border-radius: 8px; border: 1px solid #e5eaee; transition: all 0.2s ease-in-out; font-size: 0.9rem; }
.form-control:focus, .form-select:focus { border-color: #3498db; box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.15); }

.table-container { background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); overflow-x: auto; }
.booking-table { font-size: 0.875rem; border-collapse: separate; border-spacing: 0; min-width: 700px; }
.booking-table thead th { background-color: #f8f9fa; color: #7f8c8d; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e5eaee; padding: 1rem; white-space: nowrap; }
.booking-table td { padding: 1.25rem 1rem; border-bottom: 1px solid #e5eaee; }
.booking-table tbody tr:last-child td { border-bottom: none; }
.booking-table tbody tr:hover { background-color: #f9fafb; }
.type-name { font-size: 1rem; font-weight: 600; }
.description-text { font-size: 0.8rem; color: #7f8c8d; }
.price-tag {
  font-weight: 600;
  color: #27ae60;
  font-size: 0.9rem;
}

.action-buttons { white-space: nowrap; }
.action-buttons .btn { margin: 0 5px; }

.pagination .page-link { border: none; border-radius: 8px; margin: 0 4px; color: #7f8c8d; font-weight: 600; }
.pagination .page-item.active .page-link { background-color: #3498db; color: white; }

/* Modal Styles */
.modal-backdrop { background-color: rgba(0, 0, 0, 0.4); }
.modal-custom { border-radius: 16px; border: none; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); }
.modal-header-custom { background-color: #f4f7f9; border-bottom: 1px solid #e5eaee; padding: 1.5rem; }
.modal-footer-custom { background-color: #f4f7f9; border-top: 1px solid #e5eaee; padding: 1rem 1.5rem; }
</style>