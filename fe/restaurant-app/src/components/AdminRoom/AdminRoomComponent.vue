<template>
  <div class="page-container">
    <!-- Tiêu đề trang -->
    <div class="page-header mb-4">
      <h1 class="page-title">Quản Lý Phòng</h1>
      <p class="page-subtitle">Tạo mới, chỉnh sửa và quản lý các phòng trong khách sạn.</p>
    </div>

    <!-- Thông báo -->
    <div v-if="successMessage" class="alert alert-success">{{ successMessage }}</div>
    <div v-if="errorMessage && !isLoading" class="alert alert-danger">{{ errorMessage }}</div>

    <!-- Bộ lọc và tìm kiếm -->
    <div class="card filter-card mb-4">
      <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3 col-12 col-lg-8">
          <input
            v-model="searchQuery"
            type="text"
            class="form-control"
            placeholder="Tìm số phòng, tầng, hoặc loại phòng..."
          />
          <select v-model="filterRoomType" class="form-select">
            <option value="">Tất cả loại phòng</option>
            <option v-for="type in roomTypes" :key="type.type_id" :value="type.type_id">
              {{ type.type_name }}
            </option>
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
            <th style="width: 20%">Phòng & Tầng</th>
            <th style="width: 25%">Loại Phòng</th>
            <th style="width: 25%">Sức Chứa</th>
            <th class="text-center" style="width: 15%">Hành Động</th>
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
                <span class="me-3">
                  <i class="bi bi-people-fill me-1"></i>{{ room.roomType.max_occupancy || 0 }} Người lớn
                </span>
                <span class="me-3">
                  <i class="bi bi-people-fill me-1"></i>{{ room.roomType.max_occupancy_child || 0 }} Trẻ em
                </span>
                <span>
                  <i class="bi bi-hdd-stack-fill me-1"></i>{{ room.roomType.bed_count || 0 }} Giường
                </span>
              </div>
              <span v-else class="badge badge-secondary">Không có thông tin</span>
            </td>
            <td class="text-center action-buttons">
              <button class="btn btn-outline-primary btn-sm" title="Sửa" @click="openEditModal(room)">
                <i class="bi bi-pencil-fill"></i>
              </button>
              <button class="btn btn-outline-danger btn-sm" title="Xóa" @click="deleteRoom(room.room_id)">
                <i class="bi bi-trash-fill"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
      <div v-if="displayedRooms.length === 0 && !isLoading" class="alert alert-light text-center m-0">
        Không tìm thấy dữ liệu phòng phù hợp.
      </div>
       <div v-if="isLoading" class="d-flex justify-content-center p-4">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Đang tải...</span>
        </div>
      </div>
    </div>

    <!-- Phân trang (HIỂN THỊ CÓ ĐIỀU KIỆN) -->
    <nav v-if="isPaginated && totalPages > 1" aria-label="Page navigation" class="d-flex justify-content-between align-items-center mt-4">
      <div class="text-muted">
        Hiển thị {{ displayedRooms.length }} / {{ totalItems }} phòng
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
    
    <!-- Thông báo tổng kết quả khi tìm kiếm -->
    <div v-else-if="!isPaginated && displayedRooms.length > 0" class="d-flex justify-content-start align-items-center mt-4">
        <div class="text-muted">
            Tìm thấy tổng cộng <strong>{{ totalItems }}</strong> phòng phù hợp.
        </div>
    </div>


    <!-- Modal thêm/sửa phòng -->
    <div v-if="isModalOpen" class="modal-backdrop fade show"></div>
    <div v-if="isModalOpen" class="modal fade show d-block" tabindex="-1">
      <!-- ... Nội dung Modal giữ nguyên ... -->
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content modal-custom">
          <div class="modal-header modal-header-custom">
            <h5 class="modal-title">{{ currentRoom ? 'Cập nhật Phòng' : 'Thêm Phòng Mới' }}</h5>
            <button type="button" class="btn-close" @click="closeModal" aria-label="Close"></button>
          </div>
          <div class="modal-body p-4">
            <div v-if="modalErrorMessage" class="alert alert-warning">{{ modalErrorMessage }}</div>
            <form @submit.prevent="saveRoom">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Số Phòng</label>
                  <input type="text" v-model.trim="form.room_name" @input="onInputRoomName" class="form-control" required placeholder="Nhập số phòng (VD: 101)" :class="{ 'is-invalid': errors.room_name }"/>
                   <div v-if="errors.room_name" class="invalid-feedback">{{ errors.room_name }}</div>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Tầng</label>
                  <input type="number" v-model.number="form.floor_number" @input="onInputFloorNumber" class="form-control" required min="1" placeholder="Nhập số tầng" :class="{ 'is-invalid': errors.floor_number }"/>
                   <div v-if="errors.floor_number" class="invalid-feedback">{{ errors.floor_number }}</div>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Loại Phòng</label>
                  <select v-model="form.type_id" class="form-select" required :class="{ 'is-invalid': errors.type_id }">
                    <option value="">-- Chọn loại phòng --</option>
                    <option v-for="type in roomTypes" :key="type.type_id" :value="type.type_id">
                      {{ type.type_name }}
                    </option>
                  </select>
                   <div v-if="errors.type_id" class="invalid-feedback">{{ errors.type_id }}</div>
                </div>
                <div class="col-12">
                  <label class="form-label">Mô Tả</label>
                  <textarea v-model="form.description" @input="onInputDescription" class="form-control" rows="3" placeholder="Nhập mô tả phòng"></textarea>
                </div>
              </div>
            </form>
          </div>
          <div class="modal-footer modal-footer-custom">
            <button type="button" class="btn btn-secondary" @click="closeModal" :disabled="isLoading">Hủy</button>
            <button type="button" class="btn btn-primary" @click="saveRoom" :disabled="isLoading">
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
import axios from 'axios';

// Cấu hình API client
const apiClient = axios.create({
  baseURL: 'http://127.0.0.1:8000/api',
  timeout: 30000,
  headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }
});

// State
const rooms = ref([]);
const roomTypes = ref([]);
const searchQuery = ref('');
const filterRoomType = ref('');
const isModalOpen = ref(false);
const isLoading = ref(false);
const currentRoom = ref(null);
const successMessage = ref('');
const errorMessage = ref('');
const modalErrorMessage = ref('');
const currentPage = ref(1);
const itemsPerPage = ref(10);
const totalItems = ref(0);
const isPaginated = ref(true); // << [THÊM MỚI] Trạng thái để kiểm soát phân trang

const form = ref({
  room_name: '',
  type_id: '',
  floor_number: 1,
  description: ''
});
const errors = ref({
  room_name: '',
  type_id: '',
  floor_number: '',
});

// Fetch data
const fetchRooms = async () => {
  isLoading.value = true;
  errorMessage.value = '';
  try {
    const params = new URLSearchParams();
    const isSearchingOrFiltering = searchQuery.value.trim() || filterRoomType.value;

    // Chỉ thêm tham số phân trang nếu KHÔNG tìm kiếm/lọc
    if (!isSearchingOrFiltering) {
      params.append('page', currentPage.value);
      params.append('per_page', itemsPerPage.value);
    }
    // Luôn thêm tham số tìm kiếm/lọc nếu có
    if (searchQuery.value.trim()) {
      params.append('search', searchQuery.value.trim());
    }
    if (filterRoomType.value) {
      params.append('type_id', filterRoomType.value);
    }

    const response = await apiClient.get(`/rooms?${params.toString()}`);

    // Xử lý dữ liệu trả về
    if (Array.isArray(response.data.data)) {
      rooms.value = response.data.data.map(room => ({
        ...room,
        roomType: room.room_type || { type_name: 'N/A' }
      }));
    } else {
      rooms.value = [];
    }

    totalItems.value = response.data.total || 0;
    // Cập nhật trạng thái phân trang từ cờ của API
    isPaginated.value = response.data.is_paginated ?? false;

    // Cập nhật trang hiện tại nếu dữ liệu có phân trang
    if (isPaginated.value) {
      currentPage.value = response.data.current_page || 1;
    }

  } catch (error) {
    console.error('Fetch rooms error:', {
      message: error.message,
      response: error.response?.data,
    });
    errorMessage.value = error.response?.data?.message || 'Không thể tải danh sách phòng.';
    rooms.value = [];
    totalItems.value = 0;
  } finally {
    isLoading.value = false;
  }
};

const fetchRoomTypes = async () => {
  try {
    const response = await apiClient.get('/room-types');
    roomTypes.value = Array.isArray(response.data.data) ? response.data.data : [];
    if (roomTypes.value.length === 0) {
      console.warn('Không tìm thấy loại phòng nào. Vui lòng thêm loại phòng trước.');
    }
  } catch (error) {
    console.error('Fetch room types error:', error);
    errorMessage.value = error.response?.data?.message || 'Không thể tải danh sách loại phòng.';
    roomTypes.value = [];
  }
};

// Initialize data
onMounted(async () => {
  await Promise.all([fetchRooms(), fetchRoomTypes()]);
});

// Watchers
watch(currentPage, (newPage, oldPage) => {
  // Chỉ gọi API khi trang thay đổi VÀ đang ở chế độ phân trang
  if (newPage !== oldPage && isPaginated.value) {
    fetchRooms();
  }
});

watch([searchQuery, filterRoomType], () => {
  // Khi tìm kiếm/lọc, luôn đặt lại về trang 1 và gọi API
  currentPage.value = 1;
  fetchRooms();
});


// Pagination
const totalPages = computed(() => {
    if (!isPaginated.value) return 1;
    return Math.ceil(totalItems.value / itemsPerPage.value);
});
const displayedRooms = computed(() => rooms.value);

const pageRange = computed(() => {
  const maxPages = 5;
  let start = Math.max(1, currentPage.value - Math.floor(maxPages / 2));
  let end = Math.min(totalPages.value, start + maxPages - 1);
  if (end - start + 1 < maxPages) {
    start = Math.max(1, end - maxPages + 1);
  }
  return Array.from({ length: end - start + 1 }, (_, i) => start + i);
});

// Modal actions
const openAddModal = () => {
  form.value = { room_name: '', type_id: '', floor_number: 1, description: '' };
  errors.value = {};
  currentRoom.value = null;
  isModalOpen.value = true;
  successMessage.value = '';
  modalErrorMessage.value = '';
};
const openEditModal = (room) => {
  if (!room || typeof room !== 'object') return;
  form.value = {
    room_name: String(room.room_name || ''),
    type_id: String(room.type_id || ''),
    floor_number: Number(room.floor_number) || 1,
    description: String(room.description || '')
  };
  currentRoom.value = { ...room };
  isModalOpen.value = true;
  errors.value = {};
  successMessage.value = '';
  modalErrorMessage.value = '';
};
const closeModal = () => {
  isModalOpen.value = false;
};

// Handle inputs
const onInputRoomName = () => {
  if(errors.value.room_name) errors.value.room_name = '';
};
const onInputFloorNumber = () => {
  if(errors.value.floor_number) errors.value.floor_number = '';
};
const onInputDescription = () => {};

// Form validation
const validateForm = () => {
  errors.value = {};
  let isValid = true;
  if (!form.value.room_name.trim()) {
    errors.value.room_name = 'Vui lòng nhập số phòng';
    isValid = false;
  }
  if (!form.value.type_id) {
    errors.value.type_id = 'Vui lòng chọn loại phòng';
    isValid = false;
  }
  if (!form.value.floor_number || form.value.floor_number < 1) {
    errors.value.floor_number = 'Tầng phải là một số nguyên lớn hơn 0';
    isValid = false;
  }
  return isValid;
};

// Save room
const saveRoom = async () => {
  if (!validateForm()) {
    modalErrorMessage.value = 'Vui lòng kiểm tra lại các trường thông tin bắt buộc.';
    return;
  }
  isLoading.value = true;
  modalErrorMessage.value = '';
  const payload = {
    room_name: form.value.room_name.trim(),
    type_id: form.value.type_id,
    floor_number: form.value.floor_number,
    description: form.value.description.trim() || null
  };
  try {
    if (currentRoom.value) {
      await apiClient.put(`/rooms/${currentRoom.value.room_id}`, payload);
      successMessage.value = 'Cập nhật phòng thành công!';
    } else {
      await apiClient.post('/rooms', payload);
      successMessage.value = 'Thêm phòng thành công!';
    }
    closeModal();
    await fetchRooms(); // Tải lại dữ liệu để cập nhật danh sách
  } catch (error) {
    console.error('Save room error:', { message: error.message, response: error.response?.data });
    modalErrorMessage.value = error.response?.data?.message || 'Lưu phòng thất bại.';
    if (error.response?.status === 422) {
      errors.value = { ...errors.value, ...error.response.data?.errors };
    }
  } finally {
    isLoading.value = false;
  }
};

// Delete room
const deleteRoom = async (room_id) => {
  if (!confirm('Bạn có chắc chắn muốn xóa phòng này không?')) return;
  isLoading.value = true;
  errorMessage.value = '';
  successMessage.value = '';
  try {
    await apiClient.delete(`/rooms/${room_id}`);
    successMessage.value = 'Xóa phòng thành công!';
    await fetchRooms(); // Tải lại dữ liệu
  } catch (error) {
    console.error('Delete room error:', error);
    errorMessage.value = error.response?.data?.message || 'Xóa phòng thất bại.';
  } finally {
    isLoading.value = false;
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
  white-space: nowrap;
  font-size: 0.85rem;
  color: #34495e;
}
.occupancy-info .bi {
  color: #7f8c8d;
}

.badge {
  padding: 0.4em 0.8em;
  font-size: 0.75rem;
  font-weight: 600;
  border-radius: 20px;
  letter-spacing: 0.5px;
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