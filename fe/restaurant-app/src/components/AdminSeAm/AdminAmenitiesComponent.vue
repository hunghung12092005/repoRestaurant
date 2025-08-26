<template>
  <div class="page-container">
    <!-- Tiêu đề trang -->
    <div class="page-header mb-4">
      <h1 class="page-title">Quản Lý Tiện Nghi</h1>
      <p class="page-subtitle">Tạo và quản lý các tiện nghi cho các loại phòng.</p>
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
            placeholder="Tìm theo tên hoặc mô tả tiện nghi..."
          />
        </div>
        <button class="btn btn-primary" @click="openAddModal">
          <i class="bi bi-plus-circle me-2"></i>Thêm Tiện Nghi
        </button>
      </div>
    </div>

    <!-- Bảng danh sách tiện nghi -->
    <div class="table-container">
      <table class="table booking-table align-middle">
        <thead>
          <tr>
            <th style="width: 70%;">Tiện Nghi & Mô tả</th>
            <th class="text-center" style="width: 30%;">Hành Động</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="amenity in displayedAmenities" :key="amenity.amenity_id">
            <td>
              <div class="fw-bold type-name">{{ amenity.amenity_name }}</div>
              <p class="description-text mb-0">{{ amenity.description || 'Không có mô tả' }}</p>
            </td>
            <td class="text-center action-buttons">
              <button class="btn btn-outline-primary btn-sm" title="Sửa" @click="openEditModal(amenity)">
                <i class="bi bi-pencil-fill"></i> Sửa
              </button>
              <button class="btn btn-outline-danger btn-sm" title="Xóa" @click="deleteAmenity(amenity.amenity_id)">
                <i class="bi bi-trash-fill"></i> Xóa
              </button>
            </td>
          </tr>
          <tr v-if="displayedAmenities.length === 0">
            <td colspan="2" class="text-center py-5">Không tìm thấy tiện nghi nào.</td>
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
            <h5 class="modal-title">{{ currentAmenity ? 'Cập nhật Tiện Nghi' : 'Thêm Tiện Nghi Mới' }}</h5>
            <button type="button" class="btn-close" @click="closeModal"></button>
          </div>
          <form @submit.prevent="saveAmenity">
            <div class="modal-body p-4">
                <div v-if="modalErrorMessage" class="alert alert-warning small p-2">{{ modalErrorMessage }}</div>
                <div class="mb-3">
                  <label class="form-label">Tên Tiện Nghi</label>
                  <input
                    type="text"
                    v-model.trim="form.amenity_name"
                    class="form-control"
                    required
                    placeholder="Nhập tên tiện nghi"
                    @input="onInputAmenityName"
                    :class="{ 'is-invalid': errors.amenity_name }"
                  />
                  <div v-if="errors.amenity_name" class="invalid-feedback">{{ errors.amenity_name }}</div>
                </div>
                <div class="mb-3">
                  <label class="form-label">Mô Tả</label>
                  <textarea
                    v-model="form.description"
                    class="form-control"
                    rows="3"
                    placeholder="Nhập mô tả tiện nghi"
                    @input="onInputDescription"
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
import axiosInstance from '../../axiosConfig.js';


const amenities = ref([]);
const searchQuery = ref('');
const isModalOpen = ref(false);
const currentAmenity = ref(null);
const successMessage = ref('');
const errorMessage = ref('');
const modalErrorMessage = ref('');
const currentPage = ref(1);
const itemsPerPage = 10;
const totalItems = ref(0);
const form = ref({
  amenity_name: '',
  description: ''
});
const errors = ref({
  amenity_name: ''
});

const fetchAmenities = async () => {
  errorMessage.value = '';
  try {
    const response = await axiosInstance.get('/api/amenities', {
      params: { per_page: 'all' }
    });
    console.log('Fetch amenities response:', JSON.stringify(response.data, null, 2));
    amenities.value = Array.isArray(response.data.data) ? response.data.data : [];
    totalItems.value = response.data.total || amenities.value.length;
  } catch (error) {
    console.error('Fetch amenities error:', {
      message: error.message,
      response: error.response?.data,
      status: error.response?.status
    });
    errorMessage.value = error.response?.data?.message || 'Không thể tải danh sách tiện nghi. Vui lòng kiểm tra kết nối server.';
    amenities.value = [];
    totalItems.value = 0;
  }
};

onMounted(fetchAmenities);

const filteredAmenities = computed(() => {
  if (!Array.isArray(amenities.value)) return [];
  return amenities.value.filter((amenity) => {
    const amenityName = amenity.amenity_name?.toLowerCase() || '';
    const description = amenity.description?.toLowerCase() || '';
    return (
      amenityName.includes(searchQuery.value.toLowerCase()) ||
      description.includes(searchQuery.value.toLowerCase())
    );
  });
});

const totalPages = computed(() => Math.ceil(filteredAmenities.value.length / itemsPerPage));

const displayedAmenities = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  const end = start + itemsPerPage;
  return filteredAmenities.value.slice(start, end);
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

watch(currentPage, () => {
  if (currentPage.value < 1) currentPage.value = 1;
  if (currentPage.value > totalPages.value) currentPage.value = totalPages.value;
});

const openAddModal = () => {
  form.value = { amenity_name: '', description: '' };
  errors.value = {};
  currentAmenity.value = null;
  isModalOpen.value = true;
  successMessage.value = '';
  modalErrorMessage.value = '';
  console.log('Opened add modal, form reset:', { ...form.value });
};

const openEditModal = (amenity) => {
  console.log('Opening edit modal for amenity:', JSON.stringify(amenity, null, 2));
  if (!amenity || typeof amenity !== 'object') {
    console.error('Invalid amenity data:', amenity);
    errorMessage.value = 'Dữ liệu tiện nghi không hợp lệ.';
    return;
  }
  form.value = {
    amenity_name: String(amenity.amenity_name || ''),
    description: String(amenity.description || '')
  };
  currentAmenity.value = { ...amenity };
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
  currentAmenity.value = null;
  form.value = { amenity_name: '', description: '' };
  console.log('Closed modal, form reset:', { ...form.value });
};

const onInputAmenityName = () => {
  errors.value.amenity_name = '';
  modalErrorMessage.value = '';
};

const onInputDescription = () => {
  modalErrorMessage.value = '';
};

const validateForm = () => {
  errors.value = {};
  let isValid = true;
  if (!form.value.amenity_name || form.value.amenity_name.trim().length === 0) {
    errors.value.amenity_name = 'Vui lòng nhập tên tiện nghi';
    isValid = false;
  }
  console.log('Validation result:', {
    isValid,
    form: { ...form.value },
    errors: errors.value
  });
  return isValid;
};

const saveAmenity = async () => {
  console.log('Form data before validation:', { ...form.value });
  if (!validateForm()) {
    console.log('Validation failed, stopping save.');
    modalErrorMessage.value = 'Vui lòng kiểm tra thông tin nhập.';
    return;
  }
  modalErrorMessage.value = '';
  const payload = {
    amenity_name: form.value.amenity_name.trim(),
    description: form.value.description.trim() || null
  };
  console.log('Sending POST/PUT data:', payload);
  try {
    let response;
    if (currentAmenity.value) {
      response = await axiosInstance.put(`/api/amenities/${currentAmenity.value.amenity_id}`, payload);
      console.log('PUT response:', JSON.stringify(response.data, null, 2));
      const index = amenities.value.findIndex((s) => s.amenity_id === currentAmenity.value.amenity_id);
      if (index !== -1) {
        amenities.value[index] = response.data.data;
      }
      successMessage.value = 'Cập nhật tiện nghi thành công!';
    } else {
      response = await axiosInstance.post('/api/amenities', payload);
      console.log('POST response:', JSON.stringify(response.data, null, 2));
      amenities.value.push(response.data.data);
      totalItems.value++;
      successMessage.value = 'Thêm tiện nghi thành công!';
    }
    closeModal();
    await fetchAmenities();
  } catch (error) {
    console.error('Save amenity error:', {
      message: error.message,
      response: error.response?.data,
      status: error.response?.status
    });
    const errorMsg = error.response?.data?.message || 'Lưu tiện nghi thất bại. Vui lòng thử lại.';
    modalErrorMessage.value = errorMsg;
    if (error.response?.status === 422) {
      const backendErrors = error.response.data?.errors || {};
      errors.value = {
        amenity_name: backendErrors.amenity_name ? backendErrors.amenity_name[0] : '',
        description: backendErrors.description ? backendErrors.description[0] : ''
      };
      modalErrorMessage.value += ': ' + Object.values(backendErrors).flat().join(', ');
    } else if (error.code === 'ECONNABORTED') {
      modalErrorMessage.value = 'Yêu cầu timeout. Vui lòng kiểm tra kết nối server.';
    }
  }
};

const deleteAmenity = async (amenity_id) => {
  if (!confirm('Bạn có chắc chắn muốn xóa tiện nghi này?')) return;
  errorMessage.value = '';
  try {
    await axiosInstance.delete(`/api/amenities/${amenity_id}`);
    amenities.value = amenities.value.filter((s) => s.amenity_id !== amenity_id);
    totalItems.value--;
    if (displayedAmenities.value.length === 0 && currentPage.value > 1) {
      currentPage.value--;
    }
    successMessage.value = 'Xóa tiện nghi thành công!';
  } catch (error) {
    console.error('Delete amenity error:', {
      message: error.message,
      response: error.response?.data,
      status: error.response?.status
    });
    errorMessage.value = error.response?.data?.message || 'Xóa tiện nghi thất bại.';
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
.booking-table { font-size: 0.875rem; border-collapse: separate; border-spacing: 0; min-width: 600px; }
.booking-table thead th { background-color: #f8f9fa; color: #7f8c8d; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e5eaee; padding: 1rem; white-space: nowrap; }
.booking-table td { padding: 1.25rem 1rem; border-bottom: 1px solid #e5eaee; }
.booking-table tbody tr:last-child td { border-bottom: none; }
.booking-table tbody tr:hover { background-color: #f9fafb; }
.type-name { font-size: 1rem; font-weight: 600; }
.description-text { font-size: 0.8rem; color: #7f8c8d; }

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