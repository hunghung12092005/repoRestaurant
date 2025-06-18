<template>
  <div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="fw-bold text-sea-green">Quản Lý Tiện Nghi</h1>
      <button class="btn btn-success shadow" @click="openAddModal">
        <i class="bi bi-plus-circle me-2"></i>Thêm Tiện Nghi
      </button>
    </div>
    <div v-if="successMessage" class="alert alert-success">{{ successMessage }}</div>
    <div v-if="errorMessage" class="alert alert-danger">{{ errorMessage }}</div>

    <!-- Tìm kiếm -->
    <div class="row mb-4">
      <div class="col-md-4">
        <input
          v-model="searchQuery"
          type="text"
          class="form-control"
          placeholder="Tìm tên tiện nghi..."
        />
      </div>
    </div>

    <!-- Danh sách tiện nghi -->
    <div class="table-responsive">
      <table class="table table-bordered table-hover align-middle">
        <thead>
          <tr>
            <th>STT</th>
            <th>Tên Tiện Nghi</th>
            <th>Mô Tả</th>
            <th>Hành Động</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(amenity, index) in displayedAmenities" :key="amenity.amenity_id">
            <td>{{ (currentPage - 1) * itemsPerPage + index + 1 }}</td>
            <td>{{ amenity.amenity_name }}</td>
            <td>{{ amenity.description || 'Không có' }}</td>
            <td>
              <button class="btn btn-primary btn-sm me-2" @click="openEditModal(amenity)">
                <i class="bi bi-pencil me-1"></i>Sửa
              </button>
              <button class="btn btn-danger btn-sm" @click="deleteAmenity(amenity.amenity_id)">
                <i class="bi bi-trash me-1"></i>Xóa
              </button>
            </td>
          </tr>
          <tr v-if="displayedAmenities.length === 0">
            <td colspan="4" class="text-center text-muted">
              Không có tiện nghi nào để hiển thị
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Phân trang -->
    <div v-if="totalItems > 0" class="d-flex justify-content-between align-items-center mt-4">
      <div class="text-muted">
        Hiển thị {{ displayedAmenities.length }} / {{ totalItems }} tiện nghi
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

    <!-- Modal thêm/sửa -->
    <div
      v-if="isModalOpen"
      class="modal fade show d-block"
      tabindex="-1"
      style="background-color: rgba(0, 0, 0, 0.5)"
    >
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ currentAmenity ? 'Cập nhật Tiện Nghi' : 'Thêm Tiện Nghi Mới' }}</h5>
            <button type="button" class="btn-close" @click="closeModal"></button>
          </div>
          <div class="modal-body">
            <div v-if="modalErrorMessage" class="alert alert-warning">{{ modalErrorMessage }}</div>
            <form @submit.prevent="saveAmenity">
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
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" @click="closeModal">Hủy</button>
                <button type="submit" class="btn btn-success">Lưu</button>
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

const apiClient = axios.create({
  baseURL: 'http://127.0.0.1:8000/api',
  timeout: 30000,
  headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }
});

// State
const amenities = ref([]);
const searchQuery = ref('');
const isModalOpen = ref(false);
const currentAmenity = ref(null);
const successMessage = ref('');
const errorMessage = ref('');
const modalErrorMessage = ref('');
const currentPage = ref(1);
const itemsPerPage = 10; // 10 tiện ích mỗi trang
const totalItems = ref(0);
const form = ref({
  amenity_name: '',
  description: ''
});
const errors = ref({
  amenity_name: ''
});

// Fetch amenities
const fetchAmenities = async () => {
  errorMessage.value = '';
  try {
    const response = await apiClient.get('/amenities', {
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

// Filter and paginate amenities
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

// Watch page changes
watch(currentPage, () => {
  if (currentPage.value < 1) currentPage.value = 1;
  if (currentPage.value > totalPages.value) currentPage.value = totalPages.value;
});

// Modal actions
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

// Handle input
const onInputAmenityName = () => {
  errors.value.amenity_name = '';
  modalErrorMessage.value = '';
};

const onInputDescription = () => {
  modalErrorMessage.value = '';
};

// Form validation
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

// Save amenity
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
      response = await apiClient.put(`/amenities/${currentAmenity.value.amenity_id}`, payload);
      console.log('PUT response:', JSON.stringify(response.data, null, 2));
      const index = amenities.value.findIndex((s) => s.amenity_id === currentAmenity.value.amenity_id);
      if (index !== -1) {
        amenities.value[index] = response.data.data;
      }
      successMessage.value = 'Cập nhật tiện nghi thành công!';
    } else {
      response = await apiClient.post('/amenities', payload);
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

// Delete amenity
const deleteAmenity = async (amenity_id) => {
  if (!confirm('Bạn có chắc chắn muốn xóa tiện nghi này?')) return;

  errorMessage.value = '';
  try {
    await apiClient.delete(`/amenities/${amenity_id}`);
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
.text-sea-green {
  background: linear-gradient(135deg, #3f8dd6, #2acabd);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}
.table-responsive {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}
.table {
  width: max-content;
  min-width: 100%;
  border-collapse: collapse;
}
.table th,
.table td {
  white-space: nowrap;
  padding: 8px 12px;
  vertical-align: middle;
}
.table th {
  background: #78c1f1;
  text-align: center;
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
.btn-danger {
  background: linear-gradient(135deg, #dc3545, #bb2d3b);
  border: none;
}
.btn-danger:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}
.btn-primary {
  background: linear-gradient(135deg, #0d6efd, #0b5ed7);
  border: none;
}
.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}
.pagination .page-item .page-link {
  background: linear-gradient(135deg, #1199f3, #2acabd);
  color: transparent;
  -webkit-background-clip: text;
  border-radius: 8px;
}
.pagination .page-item.active .page-link {
  background: linear-gradient(135deg, #1199f3, #2acabd);
  color: white;
}
</style>