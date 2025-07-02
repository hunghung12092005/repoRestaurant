<template>
  <div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-5">
      <h1 class="fw-bold page-title">
        <i class="bi bi-door-open-fill me-3"></i>Quản Lý Loại Phòng
      </h1>
      <button class="btn btn-primary-themed shadow-lg" @click="moModalThem">
        <i class="bi bi-plus-circle me-2"></i>Thêm Loại Phòng Mới
      </button>
    </div>

    <!-- Tìm kiếm -->
    <div class="row mb-4 g-3">
      <div class="col-md-5">
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-search"></i></span>
          <input
            v-model="tuKhoaTim"
            type="text"
            class="form-control"
            placeholder="Tìm theo tên hoặc mô tả loại phòng..."
            @input="currentPage = 1"
          />
        </div>
      </div>
    </div>

    <!-- Danh sách loại phòng -->
    <div class="table-responsive">
      <table
        v-if="roomTypes.length > 0 && displayedTypes && displayedTypes.length > 0"
        class="table table-hover align-middle custom-table"
      >
        <thead class="table-header">
          <tr>
            <th>Tên Loại Phòng</th>
            <th>Mô Tả</th>
            <th>Số Giường</th>
            <th>Sức Chứa</th>
            <th>Ảnh</th>
            <th>Tiện Ích</th>
            <th>Dịch Vụ</th>
            <th>Hành Động</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="type in displayedTypes" :key="type.type_id">
            <td class="fw-medium">{{ type.type_name || 'Chưa có tên' }}</td>
            <td><p class="description-text">{{ type.description || 'N/A' }}</p></td>
            <td class="text-center">{{ type.bed_count || 0 }}</td>
            <td class="text-center">{{ type.max_occupancy || 0 }}</td>
            <td>
              <div class="image-container">
                <span v-if="!type.images || !type.images.length" class="tag tag-secondary">Không có ảnh</span>
                <div v-else class="image-list d-flex justify-content-center">
                  <div class="image-item">
                    <img
                      :src="getImageUrl(type.images)"
                      alt="Room type image"
                      class="room-image centered-image"
                      @error="onImageError"
                    />
                  </div>
                </div>
              </div>
            </td>
            <td>
              <div class="tags-container">
                <span v-if="!(type.amenities && type.amenities.length)" class="tag tag-secondary">Không có</span>
                <span v-for="a in type.amenities" :key="a.amenity_id" class="tag tag-info">
                  {{ a.amenity_name || 'Tiện ích không tên' }}
                </span>
              </div>
            </td>
            <td>
              <div class="tags-container">
                <span v-if="!(type.services && type.services.length)" class="tag tag-secondary">Không có</span>
                <span v-for="s in type.services" :key="s.service_id" class="tag tag-success">
                  {{ s.service_name || 'Dịch vụ không tên' }}
                </span>
              </div>
            </td>
            <td class="text-center action-buttons">
              <button
                class="btn btn-outline-primary btn-sm me-2"
                title="Sửa"
                @click="moModalSua(type)"
              >
                <i class="bi bi-pencil-fill"></i>
              </button>
              <button
                class="btn btn-outline-danger btn-sm"
                title="Xóa"
                @click="xoaLoaiPhong(type.type_id)"
              >
                <i class="bi bi-trash-fill"></i>
              </button>
            </td>
          </tr>
          <tr v-if="displayedTypes.length === 0">
            <td colspan="8" class="text-center text-muted py-5">
              <i class="bi bi-cloud-drizzle fs-2 mb-2 d-block"></i>
              Không tìm thấy loại phòng nào.
            </td>
          </tr>
        </tbody>
      </table>
      <div v-else class="text-center text-muted py-5">
        Không có dữ liệu loại phòng.
      </div>
    </div>

    <!-- Phân trang -->
    <div v-if="totalPages > 1" class="d-flex justify-content-between align-items-center mt-4">
      <div class="text-muted">
        Hiển thị <strong>{{ displayedTypes.length }}</strong> trên tổng số <strong>{{ filteredTypes.length }}</strong> loại phòng
      </div>
      <nav class="ms-auto">
        <ul class="pagination mb-0">
          <li class="page-item" :class="{ disabled: currentPage === 1 }">
            <button class="page-link" @click="currentPage = 1" aria-label="First">
              <i class="bi bi-chevron-double-left"></i>
            </button>
          </li>
          <li class="page-item" :class="{ disabled: currentPage === 1 }">
            <button class="page-link" @click="currentPage--" aria-label="Previous">
              <i class="bi bi-chevron-left"></i>
            </button>
          </li>
          <li
            v-for="page in paginationPages"
            :key="page"
            class="page-item"
            :class="{ active: currentPage === page }"
          >
            <button class="page-link" @click="currentPage = page">{{ page }}</button>
          </li>
          <li class="page-item" :class="{ disabled: currentPage === totalPages }">
            <button class="page-link" @click="currentPage++" aria-label="Next">
              <i class="bi bi-chevron-right"></i>
            </button>
          </li>
          <li class="page-item" :class="{ disabled: currentPage === totalPages }">
            <button class="page-link" @click="currentPage = totalPages" aria-label="Last">
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
      style="background-color: rgba(10, 37, 64, 0.6); backdrop-filter: blur(4px);"
    >
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              <i :class="editingType ? 'bi-pencil-square' : 'bi-plus-circle-dotted'" class="bi me-2"></i>
              {{ editingType ? 'Cập Nhật Loại Phòng' : 'Thêm Loại Phòng Mới' }}
            </h5>
            <button type="button" class="btn-close" @click="closeModal"></button>
          </div>
          <div class="modal-body">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Tên Loại Phòng</label>
                <input type="text" v-model="newType.type_name" class="form-control" required />
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Mô Tả</label>
                <textarea v-model="newType.description" class="form-control" rows="1"></textarea>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Số Giường</label>
                <input type="number" v-model.number="newType.bed_count" class="form-control" min="1" required />
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Sức Chứa Tối Đa</label>
                <input type="number" v-model.number="newType.max_occupancy" class="form-control" min="1" required />
              </div>
              <div class="col-md-6 mb-4">
                <label class="form-label d-block mb-3">Ảnh</label>
                <input
                  type="file"
                  accept="image/*"
                  class="form-control"
                  @change="handleImageUpload"
                />
                <div class="image-list d-flex justify-content-center mt-2">
                  <div v-if="newType.images" class="image-item">
                    <img
                      :src="isFile(newType.images) ? createObjectURL(newType.images) : getImageUrl(newType.images)"
                      alt="Room type image"
                      class="room-image centered-image"
                      @error="onImageError"
                    />
                  </div>
                </div>
              </div>
              <div class="col-md-6 mb-4">
                <label class="form-label d-block mb-3">Tiện Ích</label>
                <div class="form-check form-check-inline form-switch mb-2">
                  <input
                    class="form-check-input custom-checkbox"
                    type="checkbox"
                    role="switch"
                    :checked="newType.amenity_ids.length === amenities.length"
                    @change="toggleAllAmenities"
                    id="select-all-amenities"
                  />
                  <label class="form-check-label" for="select-all-amenities">Chọn/Bỏ chọn tất cả</label>
                </div>
                <div class="checkbox-list">
                  <div class="form-check" v-for="amenity in amenities" :key="amenity.amenity_id">
                    <input
                      class="form-check-input custom-checkbox"
                      type="checkbox"
                      :value="amenity.amenity_id"
                      v-model="newType.amenity_ids"
                      :id="'amenity-' + amenity.amenity_id"
                    />
                    <label class="form-check-label" :for="'amenity-' + amenity.amenity_id">
                      {{ amenity.amenity_name || 'Tiện ích không tên' }}
                    </label>
                  </div>
                </div>
              </div>
              <div class="col-md-6 mb-4">
                <label class="form-label d-block mb-3">Dịch Vụ</label>
                <div class="form-check form-check-inline form-switch mb-2">
                  <input
                    class="form-check-input custom-checkbox"
                    type="checkbox"
                    role="switch"
                    :checked="newType.service_ids.length === services.length"
                    @change="toggleAllServices"
                    id="select-all-services"
                  />
                  <label class="form-check-label" for="select-all-services">Chọn/Bỏ chọn tất cả</label>
                </div>
                <div class="checkbox-list">
                  <div class="form-check" v-for="service in services" :key="service.service_id">
                    <input
                      class="form-check-input custom-checkbox"
                      type="checkbox"
                      :value="service.service_id"
                      v-model="newType.service_ids"
                      :id="'service-' + service.service_id"
                    />
                    <label class="form-check-label" :for="'service-' + service.service_id">
                      {{ service.service_name || 'Dịch vụ không tên' }} ({{ (service.price || 0).toLocaleString() }} VNĐ)
                    </label>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary-themed" @click="closeModal">Hủy</button>
            <button type="button" class="btn btn-primary-themed" @click="saveType">
              <i class="bi bi-save me-2"></i>Lưu Lại
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

// Dữ liệu và trạng thái
const roomTypes = ref([]);
const amenities = ref([]);
const services = ref([]);
const tuKhoaTim = ref('');
const isModalOpen = ref(false);
const editingType = ref(null);
const newType = ref({
  type_name: '',
  description: '',
  bed_count: 1,
  max_occupancy: 1,
  images: null,
  amenity_ids: [],
  service_ids: [],
});
const currentPage = ref(1);
const itemsPerPage = 10;

// API
const API_BASE_URL = 'http://127.0.0.1:8000';

// Hàm tạo URL cho ảnh
const createObjectURL = (file) => {
  return window.URL.createObjectURL(file);
};

// Hàm kiểm tra xem image có phải là File không
const isFile = (image) => {
  return image instanceof File;
};

// Lấy dữ liệu ban đầu
const fetchData = async () => {
  try {
    const [roomTypesRes, amenitiesRes, servicesRes] = await Promise.all([
      axios.get(`${API_BASE_URL}/api/room-types`),
      axios.get(`${API_BASE_URL}/api/amenities`, { params: { per_page: 'all' } }),
      axios.get(`${API_BASE_URL}/api/services`, { params: { per_page: 'all' } }),
    ]);
    roomTypes.value = roomTypesRes.data.data || [];
    amenities.value = amenitiesRes.data.data || [];
    services.value = servicesRes.data.data || [];
  } catch (error) {
    handleApiError('Lấy dữ liệu thất bại', error);
    roomTypes.value = [];
  }
};

onMounted(fetchData);

// Xử lý Lọc và Phân trang
const filteredTypes = computed(() => {
  if (!tuKhoaTim.value) return roomTypes.value || [];
  const searchTerm = tuKhoaTim.value.toLowerCase();
  return (roomTypes.value || []).filter(t =>
    (t.type_name && t.type_name.toLowerCase().includes(searchTerm)) ||
    (t.description && t.description.toLowerCase().includes(searchTerm))
  );
});

const totalPages = computed(() => Math.ceil((filteredTypes.value || []).length / itemsPerPage));

const displayedTypes = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  const end = start + itemsPerPage;
  return (filteredTypes.value || []).slice(start, end);
});

const paginationPages = computed(() => {
  const maxPages = 5;
  const middle = Math.ceil(maxPages / 2);
  let start = Math.max(1, currentPage.value - middle + 1);
  let end = Math.min(totalPages.value || 1, start + maxPages - 1);

  if (end - start + 1 < maxPages) {
    start = Math.max(1, end - maxPages + 1);
  }

  return Array.from({ length: end - start + 1 }, (_, i) => start + i);
});

// Xử lý ảnh
const getImageUrl = (image) => {
  let img = image;
  if (typeof image === 'string') {
    try {
      const parsed = JSON.parse(image);
      img = Array.isArray(parsed) ? parsed[0] : parsed;
    } catch (e) {
      img = image; // Nếu không parse được, dùng nguyên bản
    }
  }
  return img ? `${API_BASE_URL}/images/room_type/${img}` : '';
};

const handleImageUpload = (event) => {
  const file = event.target.files[0]; // Chỉ lấy 1 ảnh
  newType.value.images = file || null;
};

const removeImage = () => {
  newType.value.images = null;
};

const xoaAnh = async (typeId, imageIndex) => {
  if (confirm('Bạn có chắc chắn muốn xóa ảnh này?')) {
    try {
      const response = await axios.delete(`${API_BASE_URL}/api/room-types/${typeId}/image`, {
        data: { image_index: imageIndex },
      });
      const index = roomTypes.value.findIndex(t => t.type_id === typeId);
      if (index !== -1) {
        roomTypes.value.splice(index, 1, response.data.data);
      }
      alert('Xóa ảnh thành công!');
    } catch (error) {
      handleApiError('Xóa ảnh thất bại', error);
    }
  }
};

// Xử lý Modal
const moModalThem = () => {
  editingType.value = null;
  newType.value = {
    type_name: '',
    description: '',
    bed_count: 1,
    max_occupancy: 1,
    images: null,
    amenity_ids: [],
    service_ids: [],
  };
  isModalOpen.value = true;
};

const moModalSua = (type) => {
  editingType.value = type;
  newType.value = {
    type_id: type.type_id,
    type_name: type.type_name,
    description: type.description,
    bed_count: type.bed_count,
    max_occupancy: type.max_occupancy,
    images: type.images ? (typeof type.images === 'string' ? JSON.parse(type.images)[0] : type.images) : null,
    amenity_ids: type.amenities ? type.amenities.map(a => a.amenity_id) : [],
    service_ids: type.services ? type.services.map(s => s.service_id) : [],
  };
  isModalOpen.value = true;
};

const closeModal = () => {
  isModalOpen.value = false;
};

// Xử lý Checkbox "Chọn tất cả"
const toggleAllAmenities = (event) => {
  newType.value.amenity_ids = event.target.checked ? amenities.value.map(a => a.amenity_id) : [];
};

const toggleAllServices = (event) => {
  newType.value.service_ids = event.target.checked ? services.value.map(s => s.service_id) : [];
};

// Xử lý CRUD
const saveType = async () => {
  if (!newType.value.type_name.trim() || newType.value.bed_count < 1 || newType.value.max_occupancy < 1) {
    alert('Vui lòng điền đầy đủ thông tin bắt buộc (Tên, số giường, sức chứa).');
    return;
  }

  try {
    const formData = new FormData();
    formData.append('type_name', newType.value.type_name);
    formData.append('description', newType.value.description || '');
    formData.append('bed_count', newType.value.bed_count);
    formData.append('max_occupancy', newType.value.max_occupancy);
    newType.value.amenity_ids.forEach(id => formData.append('amenity_ids[]', id));
    newType.value.service_ids.forEach(id => formData.append('service_ids[]', id));
    if (newType.value.images && isFile(newType.value.images)) {
      formData.append('images[0]', newType.value.images); // Chỉ gửi 1 ảnh
    }

    let response;
    if (editingType.value) {
      formData.append('_method', 'PUT');
      response = await axios.post(`${API_BASE_URL}/api/room-types/${editingType.value.type_id}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
      const index = roomTypes.value.findIndex(t => t.type_id === editingType.value.type_id);
      if (index !== -1) roomTypes.value.splice(index, 1, response.data.data);
    } else {
      response = await axios.post(`${API_BASE_URL}/api/room-types`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
      roomTypes.value.push(response.data.data);
    }
    closeModal();
    alert('Lưu loại phòng thành công!');
    currentPage.value = Math.ceil(roomTypes.value.length / itemsPerPage);
  } catch (error) {
    handleApiError('Lưu loại phòng thất bại', error);
  }
};

const xoaLoaiPhong = async (id) => {
  if (confirm('Bạn có chắc chắn muốn xóa loại phòng này? Hành động này không thể hoàn tác.')) {
    try {
      await axios.delete(`${API_BASE_URL}/api/room-types/${id}`);
      roomTypes.value = roomTypes.value.filter(t => t.type_id !== id);
      if (displayedTypes.value.length === 0 && currentPage.value > 1) currentPage.value--;
      alert('Xóa loại phòng thành công!');
    } catch (error) {
      handleApiError('Xóa loại phòng thất bại', error);
    }
  }
};

// Xử lý lỗi ảnh
const onImageError = (event) => {
  event.target.src = '/path/to/placeholder-image.jpg'; // Thay bằng đường dẫn ảnh placeholder
  console.log('Lỗi tải ảnh:', event.target.src);
};

// Hàm xử lý lỗi
const handleApiError = (message, error) => {
  const errorMessage = error.response?.data?.message || error.message;
  const errorDetails = error.response?.data?.errors
    ? Object.values(error.response.data.errors).flat().join('\n')
    : '';
  alert(`${message}:\n${errorMessage}${errorDetails ? `\n- ${errorDetails}` : ''}`);
  console.error('API Error:', error.response?.data || error);
};
</script>

<style scoped>
/* --- THEME & FONT --- */
:root {
  --c-deep-blue: #0a2540;
  --c-ocean-blue: #0077b6;
  --c-aqua: #00b4d8;
  --c-light-aqua: #90e0ef;
  --c-sand: #fdf8f0;
  --c-white: #ffffff;
  --c-gray: #6b7280;
  --c-light-gray: #f3f4f6;
  --c-success: #2a9d8f;
  --c-danger: #e76f51;
  --font-family-main: 'Poppins', sans-serif;
  --border-radius-main: 12px;
  --shadow-sm: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
  --shadow-md: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -2px rgb(0 0 0 / 0.1);
}

.image-container {
  height: 100px; /* Chiều cao cố định cho khung ảnh */
  display: flex;
  align-items: center;
  justify-content: center;
}

.image-list {
  width: 100%;
}

.image-item {
  display: flex;
  justify-content: center;
}

.room-image {
  max-width: 100%;
  max-height: 100px;
  object-fit: contain;
}

.centered-image {
  margin: 0 auto; /* Căn giữa theo chiều ngang */
}

/* Tùy chỉnh checkbox */
.custom-checkbox {
  appearance: none; /* Xóa kiểu mặc định */
  width: 1.2em;
  height: 1.2em;
  border: 2px solid var(--c-aqua); /* Viền xanh */
  border-radius: 4px;
  outline: none;
  cursor: pointer;
  position: relative;
  background-color: transparent; /* Không nền khi chưa chọn */
}

.custom-checkbox:checked {
  background-color: var(--c-aqua); /* Nền xanh khi được chọn */
}

.custom-checkbox:checked::after {
  content: '✔'; /* Dấu tích */
  color: var(--c-white); /* Màu trắng cho dấu tích */
  font-size: 0.9em;
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
}

body {
  font-family: var(--font-family-main);
  background-color: var(--c-light-gray);
}

.container {
  background: linear-gradient(180deg, var(--c-white) 0%, #f7fafd 100%);
  border-radius: 24px;
  box-shadow: 0 20px 50px rgba(10, 37, 64, 0.08);
  padding: 40px;
  max-width: 1600px;
}

/* --- HEADER & TITLE --- */
.page-title {
  color: var(--c-deep-blue);
  font-weight: 700;
  font-size: 2.25rem;
  display: flex;
  align-items: center;
}

.page-title .bi-door-open-fill {
  color: var(--c-ocean-blue);
  font-size: 2.5rem;
}

/* --- TABLE STYLES --- */
.table-responsive {
  border: none;
  border-radius: var(--border-radius-main);
  overflow-x: auto;
  box-shadow: var(--shadow-md);
}

.custom-table {
  width: 100%;
  min-width: 1200px;
  table-layout: fixed;
  border-collapse: separate;
  border-spacing: 0;
}

.table-header {
  background-color: var(--c-deep-blue);
}

.table th {
  color: var(--c-white);
  padding: 16px 20px;
  font-weight: 600;
  font-size: 14px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  border: none;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  text-align: center;
}

.table th:nth-child(1) { width: 15%; }
.table th:nth-child(2) { width: 20%; }
.table th:nth-child(3) { width: 10%; }
.table th:nth-child(4) { width: 10%; }
.table th:nth-child(5) { width: 20%; }
.table th:nth-child(6) { width: 15%; }
.table th:nth-child(7) { width: 15%; }
.table th:nth-child(8) { width: 15%; }

.table td {
  padding: 18px 20px;
  border-bottom: 1px solid #eef2f7;
  background-color: var(--c-white);
  color: #334155;
  font-size: 14px;
  vertical-align: middle;
  overflow: hidden;
  text-overflow: ellipsis;
}

.table tbody tr:last-child td {
  border-bottom: none;
}

.table tbody tr {
  transition: all 0.2s ease-in-out;
}

.table-hover > tbody > tr:hover > * {
  --bs-table-accent-bg: var(--c-sand);
  transform: scale(1.01);
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
}

.description-text {
  white-space: normal;
  word-break: break-word;
  max-width: 100%;
  margin: 0;
  color: var(--c-gray);
  font-size: 13px;
  line-height: 1.2;
}

/* --- TAGS FOR AMENITIES/SERVICES --- */
.tags-container {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  max-height: 80px;
  overflow-y: auto;
  padding-right: 5px;
}
.tag {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 500;
  line-height: 1.5;
}
.tag-info { background-color: #e0f2fe; color: #0c4a6e; }
.tag-success { background-color: #d1fae5; color: #065f46; }
.tag-secondary { background-color: #f3f4f6; color: #4b5563; }

/* --- IMAGE STYLES --- */
.image-container {
  max-height: 100px;
  overflow-y: auto;
}

.image-list {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.image-item {
  position: relative;
  width: 80px;
  height: 80px;
}

.room-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
}

.image-item .btn-danger {
  width: 24px;
  height: 24px;
  padding: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
}

/* --- BUTTONS --- */
.btn-primary-themed {
  background: linear-gradient(45deg, var(--c-ocean-blue), var(--c-aqua));
  border: none;
  padding: 12px 28px;
  font-weight: 600;
  border-radius: var(--border-radius-main);
  color: var(--c-white);
  transition: all 0.3s ease;
  box-shadow: 0 4px 15px rgba(0, 180, 216, 0.3);
}
.btn-primary-themed:hover {
  transform: translateY(-3px);
  box-shadow: 0 7px 20px rgba(0, 180, 216, 0.4);
  color: var(--c-white);
}

.btn-secondary-themed {
  background-color: #e5e7eb;
  color: #374151;
  border: none;
  padding: 10px 24px;
  font-weight: 600;
  border-radius: var(--border-radius-main);
  transition: background-color 0.2s ease;
}
.btn-secondary-themed:hover { background-color: #d1d5db; }

.action-buttons .btn {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
  margin: 0 2px;
}
.action-buttons .btn:hover { transform: scale(1.1); }

/* --- FORMS & INPUTS --- */
.form-control, .input-group-text {
  border: 1px solid #d1d5db;
  border-radius: var(--border-radius-main);
  padding: 10px 16px;
  background-color: var(--c-white);
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}
.form-control:focus {
  border-color: var(--c-ocean-blue);
  box-shadow: 0 0 0 3px rgba(0, 119, 182, 0.2);
  background-color: var(--c-white);
  outline: none;
}
.input-group-text {
  background-color: #f9fafb;
  border-right: none;
  color: var(--c-gray);
}
.input-group .form-control { border-left: none; }
.form-label {
  font-weight: 600;
  color: var(--c-deep-blue);
  margin-bottom: 8px;
}

/* --- PAGINATION --- */
.pagination .page-item .page-link {
  border-radius: 8px !important;
  margin: 0 3px;
  border: 1px solid #dee2e6;
  color: var(--c-ocean-blue);
  font-weight: 600;
  transition: all 0.2s ease;
  min-width: 40px;
  text-align: center;
}
.pagination .page-item.active .page-link {
  background-color: var(--c-ocean-blue);
  border-color: var(--c-ocean-blue);
  color: var(--c-white);
  box-shadow: 0 4px 8px rgba(0, 119, 182, 0.25);
}
.pagination .page-item:not(.active) .page-link:hover {
  background-color: #eaf6fb;
  border-color: var(--c-aqua);
}
.pagination .page-item.disabled .page-link { color: #adb5bd; }

/* --- MODAL STYLES --- */
.modal-content {
  border-radius: 20px;
  border: none;
  box-shadow: var(--shadow-md);
  background-color: #f9fafb;
}
.modal-header {
  background: linear-gradient(45deg, var(--c-deep-blue), var(--c-ocean-blue));
  color: var(--c-white);
  border-bottom: none;
  padding: 20px 24px;
  border-top-left-radius: 20px;
  border-top-right-radius: 20px;
}
.modal-title {
  font-size: 1.5rem;
  font-weight: 600;
  display: flex;
  align-items: center;
}
.modal-body { padding: 2rem; }
.modal-footer {
  background-color: #f3f4f6;
  border-top: 1px solid #e5e7eb;
  padding: 1rem 2rem;
  border-bottom-left-radius: 20px;
  border-bottom-right-radius: 20px;
}
.btn-close {
  filter: invert(1) grayscale(100%) brightness(200%);
  transition: transform 0.2s ease;
}
.btn-close:hover { transform: rotate(90deg); }

/* --- CHECKBOX STYLES --- */
.checkbox-list {
  background-color: var(--c-white);
  border: 1px solid #e5e7eb;
  border-radius: var(--border-radius-main);
  padding: 16px;
  height: 200px;
  overflow-y: auto;
}

.form-check-input {
  border-radius: 4px;
  border: 1px solid #9ca3af;
  appearance: none;
  width: 18px;
  height: 18px;
  background-color: transparent;
  position: relative;
  transition: border-color 0.2s ease, background-color 0.2s ease;
}

.form-check-input:checked {
  background-color: var(--c-ocean-blue);
  border-color: var(--c-ocean-blue);
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3e%3cpath fill='none' stroke='%23ffffff' stroke-linecap='round' stroke-linejoin='round' stroke-width='3' d='M6 10l3 3 6-6'/%3e%3c/svg%3e");
  background-repeat: no-repeat;
  background-position: center;
}

.form-check-input:focus {
  box-shadow: 0 0 0 3px rgba(0, 119, 182, 0.25);
  border-color: var(--c-ocean-blue);
}

.form-check-input:not(:checked) {
  background-image: none;
}

.form-check-label {
  font-size: 14px;
  color: #374151;
  margin-left: 8px;
}

.form-check-switch .form-check-input {
  width: 2.5em;
  height: 1.25em;
  background-color: #e5e7eb;
  border: none;
}

.form-check-switch .form-check-input:checked {
  background-color: var(--c-ocean-blue);
  background-image: none;
}

/* --- SCROLLBAR --- */
.tags-container::-webkit-scrollbar,
.checkbox-list::-webkit-scrollbar {
  width: 8px;
}
.tags-container::-webkit-scrollbar-thumb,
.checkbox-list::-webkit-scrollbar-thumb {
  background: #bdc5d1;
  border-radius: 4px;
}
.tags-container::-webkit-scrollbar-track,
.checkbox-list::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}
</style>