<template>
  <div class="page-container">
    <!-- Tiêu đề trang -->
    <div class="page-header mb-4">
      <h1 class="page-title">Quản Lý Dịch Vụ</h1>
      <p class="page-subtitle">Tạo và quản lý các dịch vụ cộng thêm của khách sạn.</p>
    </div>

    <!-- Thông báo -->
    <div v-if="successMessage" class="alert alert-success alert-dismissible fade show" role="alert">
      {{ successMessage }}
      <button type="button" class="btn-close" @click="successMessage = ''" aria-label="Close"></button>
    </div>
    <div v-if="errorMessage && !isLoading" class="alert alert-danger">{{ errorMessage }}</div>

    <!-- Bộ lọc và tìm kiếm -->
    <div class="card filter-card mb-4">
      <div class="card-body d-flex justify-content-between align-items-center">
        <div class="col-md-5">
          <input v-model="searchQuery" type="text" class="form-control"
            placeholder="Tìm theo tên hoặc mô tả dịch vụ..." />
        </div>
        <button class="btn btn-primary" @click="openAddModal">
          <i class="bi bi-plus-circle me-2"></i>Thêm Dịch Vụ
        </button>
      </div>
    </div>

    <!-- Bảng danh sách dịch vụ -->
    <div class="table-container">
      <div v-if="isLoading" class="d-flex justify-content-center p-5">
        <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Đang tải...</span></div>
      </div>
      <div v-else>
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
                <button class="btn btn-outline-primary btn-sm" title="Xem & Sửa" @click="openEditModal(service)">
                  <i class="bi bi-pencil-fill"></i> Sửa
                </button>

                <!-- SỬA LỖI: LOGIC HIỂN THỊ NÚT XÓA / KHÓA MỚI -->
                <button v-if="service.bookings_count === 0" class="btn btn-outline-danger btn-sm" title="Xóa"
                  @click="deleteService(service.service_id)">
                  <i class="bi bi-trash-fill"></i> Xóa
                </button>
                <span v-else class="badge-in-use" title="Không thể xóa vì dịch vụ này đã được sử dụng.">
                  <i class="bi bi-lock-fill"></i>
                </span>

              </td>
            </tr>
            <tr v-if="displayedServices.length === 0">
              <td colspan="3" class="text-center py-5">Không tìm thấy dịch vụ nào.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Phân trang -->
    <nav v-if="totalPages > 1" aria-label="Page navigation" class="mt-4">
      <ul class="pagination justify-content-center">
        <li class="page-item" :class="{ disabled: currentPage === 1 }"><a class="page-link" href="#"
            @click.prevent="changePage(1)">««</a></li>
        <li class="page-item" :class="{ disabled: currentPage === 1 }"><a class="page-link" href="#"
            @click.prevent="changePage(currentPage - 1)">«</a></li>
        <li v-for="page in pageRange" :key="page" class="page-item" :class="{ active: page === currentPage }"><a
            class="page-link" href="#" @click.prevent="changePage(page)">{{ page }}</a></li>
        <li class="page-item" :class="{ disabled: currentPage === totalPages }"><a class="page-link" href="#"
            @click.prevent="changePage(currentPage + 1)">»</a></li>
        <li class="page-item" :class="{ disabled: currentPage === totalPages }"><a class="page-link" href="#"
            @click.prevent="changePage(totalPages)">»»</a></li>
      </ul>
    </nav>

    <!-- Modal thêm/sửa -->
    <div v-if="isModalOpen" class="modal-backdrop fade show"></div>
    <div v-if="isModalOpen" class="modal fade show d-block" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-custom">
          <div class="modal-header modal-header-custom">
            <h5 class="modal-title">{{ currentService ? 'Chi Tiết Dịch Vụ' : 'Thêm Dịch Vụ Mới' }}</h5>
            <button type="button" class="btn-close" @click="closeModal"></button>
          </div>
          <form @submit.prevent="saveService">
            <div class="modal-body p-4">
              <div v-if="isFormLocked" class="alert alert-warning d-flex align-items-center mb-4">
                <i class="bi bi-lock-fill me-3 fs-4"></i>
                <div>
                  Dịch vụ này đã được sử dụng. <br>
                  Để đảm bảo tính toàn vẹn dữ liệu, bạn không thể chỉnh sửa thông tin.
                </div>
              </div>
              <div v-if="modalErrorMessage" class="alert alert-danger">{{ modalErrorMessage }}</div>

              <fieldset :disabled="isFormLocked">
                <div class="mb-3">
                  <label class="form-label">Tên Dịch Vụ</label>
                  <input type="text" v-model.trim="form.service_name" class="form-control" required
                    :class="{ 'is-invalid': errors.service_name }" />
                  <div v-if="errors.service_name" class="invalid-feedback">{{ errors.service_name[0] }}</div>
                </div>
                <div class="mb-3">
                  <label class="form-label">Giá (VNĐ)</label>
                  <input type="number" v-model.number="form.price" class="form-control" required min="0"
                    :class="{ 'is-invalid': errors.price }" />
                  <div v-if="errors.price" class="invalid-feedback">{{ errors.price[0] }}</div>
                </div>
                <div class="mb-3">
                  <label class="form-label">Mô Tả</label>
                  <textarea v-model="form.description" class="form-control" rows="3"></textarea>
                </div>
              </fieldset>
            </div>
            <div class="modal-footer modal-footer-custom">
              <button type="button" class="btn btn-secondary" @click="closeModal">{{ isFormLocked ? 'Đóng' : 'Hủy'
                }}</button>
              <button type="submit" class="btn btn-primary" v-if="!isFormLocked" :disabled="isSaving">
                <span v-if="isSaving" class="spinner-border spinner-border-sm me-2"></span>
                Lưu
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import axiosInstance from '../../axiosConfig.js';

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
const lastPage = ref(1);
const isLoading = ref(false);
const isSaving = ref(false);
const isFormLocked = ref(false);

const form = ref({ service_name: '', price: 0, description: '' });
const errors = ref({});

const formatPrice = (price) => {
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price || 0);
};

const fetchServices = async (page = 1) => {
  isLoading.value = true;
  errorMessage.value = '';
  try {
    const response = await axiosInstance.get(`/api/services?page=${page}&per_page=${itemsPerPage}`);
    console.log('Services API response:', JSON.stringify(response.data, null, 2));
    services.value = Array.isArray(response.data.data) ? response.data.data : (response.data.data?.data || []);
    totalItems.value = response.data.total || 0;
    currentPage.value = response.data.current_page || 1;
    lastPage.value = response.data.last_page || 1;
  } catch (error) {
    handleApiError('Tải danh sách dịch vụ thất bại', error, 'page');
  } finally {
    isLoading.value = false;
  }
};

onMounted(fetchServices);

watch(currentPage, (newPage) => {
  if (!searchQuery.value.trim()) {
    fetchServices(newPage);
  }
});

const filteredServices = computed(() => {
  if (!Array.isArray(services.value)) return [];
  if (!searchQuery.value) return []; // Trả về mảng rỗng nếu không tìm kiếm
  return services.value.filter(service =>
    (service.service_name?.toLowerCase() || '').includes(searchQuery.value.toLowerCase()) ||
    (service.description?.toLowerCase() || '').includes(searchQuery.value.toLowerCase())
  );
});

const displayedServices = computed(() => {
  // Nếu có từ khóa tìm kiếm, hiển thị kết quả lọc
  if (searchQuery.value.trim()) {
    return filteredServices.value;
  }
  // Nếu không, hiển thị danh sách từ API (đã được phân trang)
  return services.value;
});

const totalPages = computed(() => lastPage.value);
const pageRange = computed(() => {
  const maxPages = 5;
  let start = Math.max(1, currentPage.value - Math.floor(maxPages / 2));
  let end = Math.min(totalPages.value, start + maxPages - 1);
  if (end - start + 1 < maxPages) start = Math.max(1, end - maxPages + 1);
  return Array.from({ length: end - start + 1 }, (_, i) => start + i);
});

const resetForm = () => {
  form.value = { service_name: '', price: 0, description: '' };
  errors.value = {};
  currentService.value = null;
  isFormLocked.value = false;
  modalErrorMessage.value = '';
};

const openAddModal = () => {
  resetForm();
  isModalOpen.value = true;
};

const openEditModal = (service) => {
  resetForm();
  currentService.value = service;
  form.value = { ...service };
  isFormLocked.value = service.bookings_count > 0;
  isModalOpen.value = true;
};

const closeModal = () => { isModalOpen.value = false; };

const saveService = async () => {
  isSaving.value = true;
  modalErrorMessage.value = '';
  errors.value = {};
  successMessage.value = '';

  try {
    let response;
    if (currentService.value) {
      response = await axiosInstance.put(`/api/services/${currentService.value.service_id}`, payload);
      console.log('PUT response:', JSON.stringify(response.data, null, 2));
      const index = services.value.findIndex(s => s.service_id === currentService.value.service_id);
      if (index !== -1) {
        services.value[index] = response.data.data;
      }
      successMessage.value = 'Cập nhật dịch vụ thành công!';
    } else {
      response = await axiosInstance.post('/api/services', payload);
      console.log('POST response:', JSON.stringify(response.data, null, 2));
      services.value.push(response.data.data);
      successMessage.value = 'Thêm dịch vụ thành công!';
      await fetchServices(currentPage.value);
    }
    successMessage.value = response.data.message;
    closeModal();
    await fetchServices(currentService.value ? currentPage.value : 1);
  } catch (error) {
    handleApiError('Lưu dịch vụ thất bại', error, 'modal');
  } finally {
    isSaving.value = false;
  }
};

const deleteService = async (service_id) => {
  if (!confirm('Bạn có chắc chắn muốn xóa dịch vụ này?')) return;
  successMessage.value = '';
  errorMessage.value = '';
  try {
    await axiosInstance.delete(`/api/services/${service_id}`);
    services.value = services.value.filter(s => s.service_id !== service_id);
    if (displayedServices.value.length === 0 && currentPage.value > 1) {
      currentPage.value--;
    }
    await fetchServices(currentPage.value);
  } catch (error) {
    handleApiError('Xóa dịch vụ thất bại', error, 'page');
  }
};

const changePage = (page) => {
  if (page >= 1 && page <= totalPages.value && page !== currentPage.value) {
    currentPage.value = page;
  }
};

const handleApiError = (message, error, context = 'page') => {
  const serverMessage = error.response?.data?.message || 'Có lỗi xảy ra, vui lòng thử lại.';
  let errorDetails = '';
  if (error.response?.status === 422) {
    errors.value = error.response.data.errors;
    errorDetails = Object.values(errors.value).flat().join(' ');
  }
  const finalMessage = `${message}. ${serverMessage} ${errorDetails}`;

  if (context === 'modal') {
    modalErrorMessage.value = finalMessage;
  } else {
    errorMessage.value = finalMessage;
  }
};
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

.form-control {
  border-radius: 8px;
  border: 1px solid #e5eaee;
  transition: all 0.2s ease-in-out;
  font-size: 0.9rem;
}

.form-control:focus {
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
  min-width: 700px;
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
  font-weight: 600;
}

.description-text {
  font-size: 0.8rem;
  color: #7f8c8d;
}

.price-tag {
  font-weight: 600;
  color: #27ae60;
  font-size: 0.9rem;
}

.action-buttons {
  white-space: nowrap;
}

.action-buttons .btn,
.action-buttons .badge-in-use {
  margin: 0 5px;
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

/* STYLE MỚI CHO HUY HIỆU KHÓA */
.badge-in-use {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 70px;
  /* Bằng kích thước nút small */
  height: 31px;
  background-color: #f3f4f6;
  color: #7f8c8d;
  border-radius: 0.25rem;
  font-size: 0.9rem;
  cursor: help;
  vertical-align: middle;
}
</style>