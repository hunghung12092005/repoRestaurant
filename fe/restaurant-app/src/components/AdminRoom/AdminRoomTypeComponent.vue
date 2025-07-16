<template>
  <div class="page-container">
    <!-- Tiêu đề trang -->
    <div class="page-header mb-4">
      <h1 class="page-title">Quản Lý Loại Phòng</h1>
      <p class="page-subtitle">Tạo mới, chỉnh sửa và quản lý các loại phòng của khách sạn.</p>
    </div>

    <!-- Bộ lọc và tìm kiếm -->
    <div class="card filter-card mb-4">
      <div class="card-body d-flex justify-content-between align-items-center">
        <div class="col-md-5">
            <input
              v-model="tuKhoaTim"
              type="text"
              class="form-control"
              placeholder="Tìm theo tên hoặc mô tả loại phòng..."
              @input="currentPage = 1"
            />
        </div>
        <button class="btn btn-primary" @click="moModalThem">
          <i class="bi bi-plus-circle me-2"></i>Thêm Loại Phòng
        </button>
      </div>
    </div>

    <!-- Bảng danh sách -->
    <div class="table-container">
      <table v-if="displayedTypes.length > 0" class="table booking-table align-middle">
        <thead>
          <tr>
            <th style="width: 25%;">Tên Loại Phòng</th>
            <th style="width: 15%;">Sức Chứa</th>
            <th class="text-center" style="width: 12%;">Ảnh</th>
            <th style="width: 19%;">Tiện Ích</th>
            <th style="width: 19%;">Dịch Vụ</th>
            <th class="text-center" style="width: 10%;">Hành Động</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="type in displayedTypes" :key="type.type_id">
            <td>
              <div class="fw-bold type-name">{{ type.type_name || 'Chưa có tên' }}</div>
              <p class="description-text mb-0">{{ type.description || 'Không có mô tả' }}</p>
            </td>
            <td>
              <div class="occupancy-info">
                <span class="me-3">
                  <i class="bi bi-people-fill me-1"></i>{{ type.max_occupancy || 0 }} Người
                </span>
                <span>
                  <i class="bi bi-hdd-stack-fill me-1"></i>{{ type.bed_count || 0 }} Giường
                </span>
              </div>
            </td>
            <td class="text-center">
              <img
                v-if="getImageUrl(type.images)"
                :src="getImageUrl(type.images)"
                alt="Room type image"
                class="room-image"
                @error="onImageError"
              />
              <span v-else class="badge badge-secondary">Không có ảnh</span>
            </td>
            <td>
              <div class="tags-container">
                <span v-if="!type.amenities || !type.amenities.length" class="badge badge-secondary">Không có</span>
                <span v-else v-for="a in type.amenities.slice(0, 3)" :key="a.amenity_id" class="badge badge-info">
                  {{ a.amenity_name }}
                </span>
                <span v-if="type.amenities && type.amenities.length > 3" class="badge badge-info">
                  +{{ type.amenities.length - 3 }}
                </span>
              </div>
            </td>
            <td>
              <div class="tags-container">
                <span v-if="!type.services || !type.services.length" class="badge badge-secondary">Không có</span>
                <span v-else v-for="s in type.services.slice(0, 3)" :key="s.service_id" class="badge badge-success">
                  {{ s.service_name }}
                </span>
                 <span v-if="type.services && type.services.length > 3" class="badge badge-success">
                  +{{ type.services.length - 3 }}
                </span>
              </div>
            </td>
            <td class="text-center action-buttons">
              <button class="btn btn-outline-primary btn-sm" title="Sửa" @click="moModalSua(type)">
                <i class="bi bi-pencil-fill"></i>
              </button>
              <button class="btn btn-outline-danger btn-sm" title="Xóa" @click="xoaLoaiPhong(type.type_id)">
                <i class="bi bi-trash-fill"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
      <div v-else class="alert alert-light text-center">
        Không tìm thấy dữ liệu loại phòng phù hợp.
      </div>
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
            <li v-for="page in paginationPages" :key="page" class="page-item" :class="{ active: page === currentPage }">
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
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content modal-custom">
          <div class="modal-header modal-header-custom">
            <h5 class="modal-title">{{ editingType ? 'Cập Nhật Loại Phòng' : 'Thêm Loại Phòng Mới' }}</h5>
            <button type="button" class="btn-close" @click="closeModal" aria-label="Close"></button>
          </div>
          <div class="modal-body p-4">
            <div class="row g-3">
              <div class="col-12">
                <label class="form-label">Tên Loại Phòng</label>
                <input type="text" v-model="newType.type_name" class="form-control" required />
              </div>
              <div class="col-md-6">
                <label class="form-label">Số Giường</label>
                <input type="number" v-model.number="newType.bed_count" class="form-control" min="1" required />
              </div>
              <div class="col-md-6">
                <label class="form-label">Sức Chứa Tối Đa</label>
                <input type="number" v-model.number="newType.max_occupancy" class="form-control" min="1" required />
              </div>
              <div class="col-12">
                <label class="form-label">Mô Tả</label>
                <textarea v-model="newType.description" class="form-control" rows="3"></textarea>
              </div>

              <!-- Giao diện upload ảnh -->
              <div class="col-12">
                 <label class="form-label d-block mb-2">Ảnh Đại Diện</label>
                 <input
                    type="file"
                    accept="image/*"
                    ref="fileInput"
                    @change="handleImageUpload"
                    style="display: none"
                  />
                  <div 
                    class="image-uploader" 
                    @click="triggerFileInput"
                    @dragover.prevent="dragActive = true"
                    @dragleave.prevent="dragActive = false"
                    @drop.prevent="handleDrop"
                    :class="{ 'drag-active': dragActive }"
                  >
                    <div v-if="!previewImage" class="uploader-instructions">
                      <i class="bi bi-cloud-arrow-up-fill uploader-icon"></i>
                      <span>Kéo & Thả ảnh vào đây</span>
                      <span class="text-muted small">hoặc <strong>nhấn để chọn file</strong></span>
                    </div>
                    <div v-else class="image-preview-container">
                      <img :src="previewImage" class="image-preview" alt="Preview"/>
                      <div class="change-image-overlay">
                        <i class="bi bi-arrow-repeat fs-3"></i>
                        <span>Đổi ảnh</span>
                      </div>
                    </div>
                  </div>
              </div>

              <!-- SỬA Ở ĐÂY: Thêm checkbox "Chọn tất cả" -->
              <div class="col-md-6">
                <label class="form-label">Tiện Ích</label>
                <div class="checkbox-list">
                    <div class="form-check form-switch select-all-switch">
                      <input class="form-check-input" type="checkbox" role="switch" id="select-all-amenities" :checked="isAllAmenitiesSelected" @change="toggleAllAmenities">
                      <label class="form-check-label" for="select-all-amenities">Chọn Tất Cả Tiện Ích</label>
                    </div>
                    <hr class="my-2">
                    <div class="form-check" v-for="amenity in amenities" :key="amenity.amenity_id">
                        <input class="form-check-input" type="checkbox" :value="amenity.amenity_id" v-model="newType.amenity_ids" :id="'amenity-' + amenity.amenity_id"/>
                        <label class="form-check-label" :for="'amenity-' + amenity.amenity_id">{{ amenity.amenity_name }}</label>
                    </div>
                </div>
              </div>
              <div class="col-md-6">
                <label class="form-label">Dịch Vụ</label>
                <div class="checkbox-list">
                    <div class="form-check form-switch select-all-switch">
                      <input class="form-check-input" type="checkbox" role="switch" id="select-all-services" :checked="isAllServicesSelected" @change="toggleAllServices">
                      <label class="form-check-label" for="select-all-services">Chọn Tất Cả Dịch Vụ</label>
                    </div>
                    <hr class="my-2">
                    <div class="form-check" v-for="service in services" :key="service.service_id">
                        <input class="form-check-input" type="checkbox" :value="service.service_id" v-model="newType.service_ids" :id="'service-' + service.service_id"/>
                        <label class="form-check-label" :for="'service-' + service.service_id">{{ service.service_name }}</label>
                    </div>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer modal-footer-custom">
            <button type="button" class="btn btn-secondary" @click="closeModal">Hủy</button>
            <button type="button" class="btn btn-primary" @click="saveType">Lưu Lại</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
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
const itemsPerPage = 5;
const fileInput = ref(null);
const dragActive = ref(false);
const previewImage = ref(null);

// API
const API_BASE_URL = 'http://127.0.0.1:8000';

// Hàm kiểm tra xem image có phải là File không
const isFile = (image) => image instanceof File;

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
  return filteredTypes.value.slice(start, start + itemsPerPage);
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

// SỬA Ở ĐÂY: Thêm computed property để quản lý trạng thái "Chọn tất cả"
const isAllAmenitiesSelected = computed(() => {
  if (!amenities.value || amenities.value.length === 0) return false;
  return newType.value.amenity_ids.length === amenities.value.length;
});

const isAllServicesSelected = computed(() => {
  if (!services.value || services.value.length === 0) return false;
  return newType.value.service_ids.length === services.value.length;
});


// Xử lý ảnh
const getImageUrl = (image) => {
  if (!image) return null;
  let img = image;
  if (typeof image === 'string') {
    try {
      const parsed = JSON.parse(image);
      img = Array.isArray(parsed) ? parsed[0] : parsed;
    } catch (e) { img = image; }
  } else if (Array.isArray(image)) {
    img = image[0];
  }
  return img ? `${API_BASE_URL}/images/room_type/${img}` : null;
};

const handleFile = (file) => {
    if (file && file.type.startsWith('image/')) {
        newType.value.images = file;
        previewImage.value = URL.createObjectURL(file);
    } else {
        alert('Vui lòng chỉ chọn file hình ảnh.');
    }
}

const handleImageUpload = (event) => {
  const file = event.target.files[0];
  handleFile(file);
};

const triggerFileInput = () => {
    if (fileInput.value) fileInput.value.click();
}

const handleDrop = (event) => {
    dragActive.value = false;
    const file = event.dataTransfer.files[0];
    handleFile(file);
}

// Xử lý Modal
const moModalThem = () => {
  editingType.value = null;
  newType.value = {
    type_name: '', description: '', bed_count: 1, max_occupancy: 1,
    images: null, amenity_ids: [], service_ids: [],
  };
  previewImage.value = null;
  isModalOpen.value = true;
};

const moModalSua = (type) => {
  editingType.value = type;
  newType.value = {
    type_id: type.type_id, type_name: type.type_name, description: type.description,
    bed_count: type.bed_count, max_occupancy: type.max_occupancy, images: null,
    amenity_ids: type.amenities ? type.amenities.map(a => a.amenity_id) : [],
    service_ids: type.services ? type.services.map(s => s.service_id) : [],
  };
  previewImage.value = getImageUrl(type.images);
  isModalOpen.value = true;
};

const closeModal = () => {
  isModalOpen.value = false;
  dragActive.value = false;
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
  const formData = new FormData();
  Object.keys(newType.value).forEach(key => {
    if (key === 'amenity_ids' || key === 'service_ids') {
        newType.value[key].forEach(id => formData.append(`${key}[]`, id));
    } else if (newType.value[key] !== null) {
        formData.append(key, newType.value[key]);
    }
});

  try {
    if (editingType.value) {
        formData.append('_method', 'PUT');
        await axios.post(`${API_BASE_URL}/api/room-types/${editingType.value.type_id}`, formData);
    } else {
        await axios.post(`${API_BASE_URL}/api/room-types`, formData);
    }
    await fetchData();
    closeModal();
    alert('Lưu loại phòng thành công!');
} catch (error) {
    console.error('Lỗi chi tiết:', error);
    handleApiError('Lưu loại phòng thất bại', error);
}
};

const xoaLoaiPhong = async (id) => {
  if (confirm('Bạn có chắc chắn muốn xóa loại phòng này? Hành động này không thể hoàn tác.')) {
    try {
      await axios.delete(`${API_BASE_URL}/api/room-types/${id}`);
      await fetchData();
      if (displayedTypes.value.length === 0 && currentPage.value > 1) currentPage.value--;
      alert('Xóa loại phòng thành công!');
    } catch (error) {
      handleApiError('Xóa loại phòng thất bại', error);
    }
  }
};

const onImageError = (event) => { event.target.src = 'https://via.placeholder.com/80x60.png?text=Lỗi'; };
const handleApiError = (message, error) => {
  const errorMessage = error.response?.data?.message || error.message;
  const errorDetails = error.response?.data?.errors ? Object.values(error.response.data.errors).flat().join('\n') : '';
  alert(`${message}:\n${errorMessage}${errorDetails ? `\n- ${errorDetails}` : ''}`);
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700&display=swap');
@import url("https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css");

.page-container { font-family: 'Be Vietnam Pro', sans-serif; background-color: #f4f7f9; padding: 2rem; color: #34495e; }
.page-header { border-bottom: 1px solid #e5eaee; padding-bottom: 1rem; }
.page-title { font-size: 2rem; font-weight: 700; }
.page-subtitle { font-size: 1rem; color: #7f8c8d; }
.filter-card { background-color: #ffffff; border: none; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
.form-control, .form-select { border-radius: 8px; border: 1px solid #e5eaee; transition: all 0.2s ease-in-out; font-size: 0.9rem; }
.form-control:focus, .form-select:focus { border-color: #3498db; box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.15); }

.table-container { background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); overflow: hidden; }
.booking-table { font-size: 0.875rem; border-collapse: separate; border-spacing: 0; }
.booking-table thead th { background-color: #f8f9fa; color: #7f8c8d; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e5eaee; padding: 1rem; white-space: nowrap; }
.booking-table td { padding: 1.25rem 1rem; border-bottom: 1px solid #e5eaee; }
.booking-table tbody tr:last-child td { border-bottom: none; }
.booking-table tbody tr:hover { background-color: #f9fafb; }
.type-name { font-size: 1rem; }
.description-text { font-size: 0.8rem; color: #7f8c8d; max-width: 250px; }
.occupancy-info { white-space: nowrap; font-size: 0.85rem; color: #34495e; }
.occupancy-info .bi { color: #7f8c8d; }
.room-image { width: 70px; height: 50px; object-fit: cover; border-radius: 8px; }

.image-uploader { border: 2px dashed #d1d5db; border-radius: 12px; padding: 1rem; text-align: center; cursor: pointer; transition: all 0.2s ease; background-color: #f9fafb; min-height: 150px; display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden; }
.image-uploader:hover, .image-uploader.drag-active { border-color: #3498db; background-color: #f0f8ff; }
.uploader-instructions { display: flex; flex-direction: column; align-items: center; color: #7f8c8d; pointer-events: none; }
.uploader-icon { font-size: 2.5rem; color: #bdc3c7; margin-bottom: 0.5rem; }
.image-preview-container { width: 100%; height: 100%; position: absolute; top: 0; left: 0; display: flex; align-items: center; justify-content: center; padding: 0.5rem; }
.image-preview { max-height: 100%; max-width: 100%; object-fit: contain; border-radius: 8px; }
.change-image-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); color: white; display: flex; flex-direction: column; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.2s ease; pointer-events: none; border-radius: 10px; }
.image-preview-container:hover .change-image-overlay { opacity: 1; }

.tags-container { display: flex; flex-wrap: wrap; gap: 6px; }
.badge { padding: 0.35em 0.7em; font-size: 0.7rem; font-weight: 600; border-radius: 20px; }
.badge-info { background-color: #eaf6fb; color: #3498db; }
.badge-success { background-color: #eafaf1; color: #27ae60; }
.badge-secondary { background-color: #f3f4f6; color: #7f8c8d; }

.action-buttons { white-space: nowrap; }
.action-buttons .btn { margin: 0 2px; }

.pagination .page-link { border: none; border-radius: 8px; margin: 0 4px; color: #7f8c8d; font-weight: 600; }
.pagination .page-item.active .page-link { background-color: #3498db; color: white; }

.modal-backdrop { background-color: rgba(0,0,0,0.4); }
.modal-custom { border-radius: 16px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
.modal-header-custom { background-color: #f4f7f9; border-bottom: 1px solid #e5eaee; padding: 1.5rem; }
.modal-footer-custom { background-color: #f4f7f9; border-top: 1px solid #e5eaee; padding: 1rem 1.5rem; }

.checkbox-list { max-height: 200px; overflow-y: auto; border: 1px solid #e5eaee; border-radius: 8px; padding: 1rem; background-color: #ffffff; font-size: 0.9rem; }
/* SỬA Ở ĐÂY: CSS cho switch "Chọn tất cả" */
.select-all-switch {
  padding-bottom: 0.5rem;
}
.select-all-switch .form-check-label {
  font-weight: 600;
  color: #34495e;
}
</style>