<template>
  <div class="page-container">
    <!-- Tiêu đề trang -->
    <div class="page-header mb-4">
      <h1 class="page-title">Quản Lý Phòng</h1>
      <p class="page-subtitle">Tạo mới, chỉnh sửa và quản lý các phòng trong khách sạn.</p>
    </div>

    <!-- Thông báo -->
    <div v-if="successMessage" class="alert alert-success alert-dismissible fade show" role="alert">
      {{ successMessage }}
      <button type="button" class="btn-close" @click="successMessage = ''" aria-label="Close"></button>
    </div>
    <div v-if="errorMessage && !isLoading" class="alert alert-danger">{{ errorMessage }}</div>

    <!-- Bộ lọc và tìm kiếm -->
    <div class="card filter-card mb-4">
      <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3 col-12 col-lg-8">
          <input v-model="searchQuery" type="text" class="form-control" placeholder="Tìm số phòng, tầng..." />
          <select v-model="filterRoomType" class="form-select">
            <option value="">Tất cả loại phòng</option>
            <option v-for="type in roomTypes" :key="type.type_id" :value="type.type_id">{{ type.type_name }}</option>
          </select>
        </div>
        <button class="btn btn-primary ms-auto" @click="openAddModal">
          <i class="bi bi-plus-circle me-2"></i>Thêm Phòng
        </button>
      </div>
    </div>

    <!-- Bảng danh sách phòng -->
    <div class="table-container">
      <table v-if="displayedRooms.length > 0" class="table booking-table align-middle">
        <thead>
          <tr>
            <th style="width: 25%">Phòng & Tầng</th>
            <th style="width: 35%">Loại Phòng</th>
            <th style="width: 30%">Sức Chứa</th>
            <th class="text-center" style="width: 10%">Hành Động</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="room in displayedRooms" :key="room.room_id">
            <td>
              <div class="fw-bold type-name">{{ room.room_name || 'Chưa có' }}</div>
              <p class="description-text mb-0">Tầng {{ room.floor_number || 'N/A' }}</p>
            </td>
            <td>
              <div class="fw-bold">{{ room.roomType?.type_name || 'Không xác định' }}</div>
              <p class="description-text mb-0">{{ room.roomType?.description || 'Không có mô tả' }}</p>
            </td>
            <td>
              <div v-if="room.roomType" class="occupancy-info">
                <div><i class="bi bi-person-fill me-2"></i> Người lớn: <strong>{{ room.roomType.max_occupancy || 0
                    }}</strong></div>
                <div><i class="bi bi-person-standing me-2"></i> Trẻ em: <strong>{{ room.roomType.max_occupancy_child ||
                    0 }}</strong></div>
                <div><i class="bi bi-ev-front-fill me-2"></i> Số giường: <strong>{{ room.roomType.bed_count || 0
                    }}</strong></div>
              </div>
            </td>
            <td class="text-center action-buttons">
              <button class="btn btn-outline-primary btn-sm" @click="openEditModal(room)"
                :disabled="room.bookings_count > 0"
                :title="room.bookings_count > 0 ? 'Không thể sửa phòng đã có lịch sử đặt' : 'Sửa thông tin phòng'">
                <i class="bi bi-pencil-fill"></i>
              </button>
              <button class="btn btn-outline-danger btn-sm" @click="deleteRoom(room)"
                :disabled="room.bookings_count > 0"
                :title="room.bookings_count > 0 ? 'Không thể xóa phòng đã có lịch sử đặt' : 'Xóa phòng'">
                <i class="bi bi-trash-fill"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-if="!isLoading && displayedRooms.length === 0" class="alert alert-light text-center m-0">Không tìm thấy dữ
        liệu phòng phù hợp.</div>
      <div v-if="isLoading" class="d-flex justify-content-center p-4">
        <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Đang tải...</span></div>
      </div>
    </div>

    <!-- Phân trang -->
    <nav v-if="isPaginated && totalPages > 1" class="d-flex justify-content-between align-items-center mt-4">
      <div class="text-muted">
        Hiển thị {{ displayedRooms.length }} trên tổng số {{ totalItems }} phòng
      </div>
      <ul class="pagination justify-content-center mb-0">
        <li class="page-item" :class="{ disabled: currentPage === 1 }">
          <a class="page-link" href="#" @click.prevent="changePage(1)">««</a>
        </li>
        <li class="page-item" :class="{ disabled: currentPage === 1 }">
          <a class="page-link" href="#" @click.prevent="changePage(currentPage - 1)">«</a>
        </li>
        <li v-for="page in pageRange" :key="page" class="page-item" :class="{ active: page === currentPage }">
          <a class="page-link" href="#" @click.prevent="changePage(page)">{{ page }}</a>
        </li>
        <li class="page-item" :class="{ disabled: currentPage === totalPages }">
          <a class="page-link" href="#" @click.prevent="changePage(currentPage + 1)">»</a>
        </li>
        <li class="page-item" :class="{ disabled: currentPage === totalPages }">
          <a class="page-link" href="#" @click.prevent="changePage(totalPages)">»»</a>
        </li>
      </ul>
    </nav>

    <!-- Thông báo tổng kết quả khi tìm kiếm -->
    <div v-else-if="!isPaginated && displayedRooms.length > 0"
      class="d-flex justify-content-start align-items-center mt-4">
      <div class="text-muted">
        Tìm thấy tổng cộng <strong>{{ totalItems }}</strong> phòng phù hợp.
      </div>
    </div>

    <!-- Modal thêm/sửa phòng -->
    <div v-if="isModalOpen" class="modal-backdrop fade show"></div>
    <div v-if="isModalOpen" class="modal fade show d-block" tabindex="-1">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content modal-custom">
          <div class="modal-header modal-header-custom">
            <h5 class="modal-title">{{ currentRoom ? 'Cập nhật Phòng' : 'Thêm Phòng Mới' }}</h5>
            <button type="button" class="btn-close" @click="closeModal" aria-label="Close"></button>
          </div>
          <div class="modal-body p-4">
            <div v-if="modalErrorMessage" class="alert alert-danger">{{ modalErrorMessage }}</div>
            <form @submit.prevent="saveRoom">
              <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Số Phòng</label><input type="text"
                    v-model.trim="form.room_name" class="form-control" required
                    :class="{ 'is-invalid': errors.room_name }" />
                  <div v-if="errors.room_name" class="invalid-feedback">{{ errors.room_name[0] }}</div>
                </div>
                <div class="col-md-6"><label class="form-label">Tầng</label><input type="number"
                    v-model.number="form.floor_number" class="form-control" required min="1"
                    :class="{ 'is-invalid': errors.floor_number }" />
                  <div v-if="errors.floor_number" class="invalid-feedback">{{ errors.floor_number[0] }}</div>
                </div>
                <div class="col-md-12">
                  <label class="form-label">Loại Phòng</label>
                  <select v-model="form.type_id" class="form-select" required :class="{ 'is-invalid': errors.type_id }">
                    <option disabled value="">-- Chọn loại phòng --</option>
                    <option v-for="type in roomTypes" :key="type.type_id" :value="type.type_id">{{ type.type_name }}
                    </option>
                  </select>
                  <div v-if="errors.type_id" class="invalid-feedback">{{ errors.type_id[0] }}</div>
                </div>
                <div class="col-12"><label class="form-label">Mô Tả</label><textarea v-model="form.description"
                    class="form-control" rows="3"></textarea></div>
              </div>
            </form>
          </div>
          <div class="modal-footer modal-footer-custom">
            <button type="button" class="btn btn-secondary" @click="closeModal" :disabled="isSaving">Hủy</button>
            <button type="button" class="btn btn-primary" @click="saveRoom" :disabled="isSaving">
              <span v-if="isSaving" class="spinner-border spinner-border-sm me-2"></span>Lưu Lại
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

const rooms = ref([]);
const roomTypes = ref([]);
const searchQuery = ref('');
const filterRoomType = ref('');
const isModalOpen = ref(false);
const isLoading = ref(false);
const isSaving = ref(false);
const currentRoom = ref(null);
const successMessage = ref('');
const errorMessage = ref('');
const modalErrorMessage = ref('');
const currentPage = ref(1);
const itemsPerPage = ref(10);
const totalItems = ref(0);
const lastPage = ref(1);
const isPaginated = ref(true);
const form = ref({
  room_name: '', type_id: '', floor_number: 1, description: ''
});
const errors = ref({});

const fetchRooms = async () => {
  isLoading.value = true;
  errorMessage.value = '';
  try {
    const params = new URLSearchParams();
    params.append('page', currentPage.value);
    params.append('per_page', itemsPerPage.value);

    if (searchQuery.value.trim()) params.append('search', searchQuery.value.trim());
    if (filterRoomType.value) params.append('type_id', filterRoomType.value);

    const response = await axiosInstance.get(`/api/rooms?${params.toString()}`);

    rooms.value = (response.data.data || []).map(room => ({
      ...room,
      roomType: room.room_type || null
    }));

    totalItems.value = response.data.total || 0;
    isPaginated.value = response.data.is_paginated;

    if (isPaginated.value) {
      currentPage.value = response.data.current_page || 1;
      lastPage.value = response.data.last_page || 1;
    }

  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Không thể tải danh sách phòng.';
  } finally {
    isLoading.value = false;
  }
};

const fetchRoomTypes = async () => {
  try {
    const response = await axiosInstance.get('/api/room-types');
    roomTypes.value = Array.isArray(response.data.data) ? response.data.data : [];
    if (roomTypes.value.length === 0) {
      console.warn('Không tìm thấy loại phòng nào. Vui lòng thêm loại phòng trước.');
    }
  } catch (error) {
    console.error('Không thể tải danh sách loại phòng:', error);
  }
};

onMounted(() => { Promise.all([fetchRooms(), fetchRoomTypes()]); });
watch([searchQuery, filterRoomType], () => {
  currentPage.value = 1;
  fetchRooms();
});

const totalPages = computed(() => lastPage.value);
const displayedRooms = computed(() => rooms.value);
const pageRange = computed(() => {
  const maxPages = 5;
  let start = Math.max(1, currentPage.value - Math.floor(maxPages / 2));
  let end = Math.min(totalPages.value, start + maxPages - 1);
  if (end - start + 1 < maxPages) start = Math.max(1, end - maxPages + 1);
  return Array.from({ length: end - start + 1 }, (_, i) => start + i);
});

const resetForm = () => {
  form.value = { room_name: '', type_id: '', floor_number: 1, description: '' };
  errors.value = {};
  currentRoom.value = null;
  modalErrorMessage.value = '';
};

const openAddModal = () => {
  resetForm();
  isModalOpen.value = true;
};

const openEditModal = (room) => {
  if (room.bookings_count > 0) return;
  resetForm();
  currentRoom.value = room;
  form.value = {
    room_name: room.room_name,
    type_id: room.type_id,
    floor_number: room.floor_number,
    description: room.description || '',
  };
  isModalOpen.value = true;
};

const closeModal = () => { isModalOpen.value = false; };

const saveRoom = async () => {
  isSaving.value = true;
  modalErrorMessage.value = '';
  errors.value = {};
  successMessage.value = '';
  try {
    let response;
    if (currentRoom.value) {
      await axiosInstance.put(`/api/rooms/${currentRoom.value.room_id}`, payload);
      successMessage.value = 'Cập nhật phòng thành công!';
    } else {
      await axiosInstance.post('/api/rooms', payload);
      successMessage.value = 'Thêm phòng thành công!';
    }
    successMessage.value = response.data.message;
    closeModal();
    await fetchRooms();
  } catch (error) {
    modalErrorMessage.value = error.response?.data?.message || 'Lưu phòng thất bại.';
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors;
    }
  } finally {
    isSaving.value = false;
  }
};

const deleteRoom = async (room) => {
  if (room.bookings_count > 0) return;
  if (!confirm(`Bạn có chắc chắn muốn xóa vĩnh viễn phòng "${room.room_name}" không?`)) return;

  isSaving.value = true;
  successMessage.value = '';
  errorMessage.value = '';
  try {
    await axiosInstance.delete(`/api/rooms/${room_id}`);
    successMessage.value = 'Xóa phòng thành công!';
    await fetchRooms(); 
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Xóa phòng thất bại.';
  } finally {
    isSaving.value = false;
  }
};

const changePage = (page) => {
  if (page >= 1 && page <= totalPages.value && page !== currentPage.value) {
    currentPage.value = page;
    fetchRooms();
  }
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700&display=swap');
@import url('https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css');

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

.occupancy-info i {
  color: #7f8c8d;
}

.occupancy-info div {
  margin-bottom: 0.25rem;
}

.occupancy-info strong {
  color: #34495e;
}

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
</style>