<template>
  <div class="page-container">
    <!-- Tiêu đề trang -->
    <div class="page-header mb-4">
      <h1 class="page-title">Quản Lý Loại Phòng</h1>
      <p class="page-subtitle">Tạo mới, chỉnh sửa và quản lý các loại phòng của khách sạn.</p>
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
          <input v-model="tuKhoaTim" type="text" class="form-control"
            placeholder="Tìm theo tên hoặc mô tả loại phòng..." @input="currentPage = 1" />
        </div>
        <button class="btn btn-primary" @click="moModalThem">
          <i class="bi bi-plus-circle me-2"></i>Thêm Loại Phòng
        </button>
      </div>
    </div>

    <!-- Bảng danh sách -->
    <div class="table-container">
      <div v-if="isLoading" class="d-flex justify-content-center p-5">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Đang tải...</span>
        </div>
      </div>
      <div v-else>
        <table v-if="displayedTypes.length > 0" class="table booking-table align-middle">
          <thead>
            <tr>
              <th style="width: 30%;">Tên Loại Phòng</th>
              <th style="width: 20%;">Sức Chứa</th>
              <th class="text-center" style="width: 15%;">Ảnh</th>
              <th style="width: 25%;">Tiện Ích</th>
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
                  <span><i class="bi bi-people-fill me-1"></i>{{ type.max_occupancy || 0 }} Người lớn</span>
                  <span><i class="bi bi-people-fill me-1"></i>{{ type.max_occupancy_child || 0 }} Trẻ em</span>
                  <span><i class="bi bi-hdd-stack-fill me-1"></i>{{ type.bed_count || 0 }} Giường</span>
                </div>
              </td>
              <td class="text-center">
                <img v-if="getFirstImageUrl(type.images)" :src="getFirstImageUrl(type.images)" alt="Room type image"
                  class="room-image" @error="onImageError" />
                <span v-else class="badge badge-secondary">Không có ảnh</span>
              </td>
              <td>
                <div class="tags-container">
                  <span v-if="!type.amenities || !type.amenities.length" class="badge badge-secondary">Không có</span>
                  <span v-else v-for="a in type.amenities.slice(0, 3)" :key="a.amenity_id" class="badge badge-info">{{
                    a.amenity_name }}</span>
                  <span v-if="type.amenities && type.amenities.length > 3" class="badge badge-info">+{{
                    type.amenities.length - 3 }}</span>
                </div>
              </td>
              <td class="text-center action-buttons">
                <button class="btn btn-outline-primary btn-sm" @click="moModalSua(type)"
                  :disabled="type.rooms_count > 0"
                  :title="type.rooms_count > 0 ? 'Không thể sửa loại phòng đã có phòng sử dụng' : 'Xem & Sửa'">
                  <i class="bi bi-pencil-fill"></i>
                </button>
                <button class="btn btn-outline-danger btn-sm" @click="xoaLoaiPhong(type.type_id)"
                  :disabled="type.rooms_count > 0"
                  :title="type.rooms_count > 0 ? 'Không thể xóa loại phòng đã có phòng sử dụng' : 'Xóa'">
                  <i class="bi bi-trash-fill"></i>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
        <div v-else class="alert alert-light text-center m-0">Không tìm thấy dữ liệu loại phòng phù hợp.</div>
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
            <h5 class="modal-title">{{ editingType ? 'Chi Tiết Loại Phòng' : 'Thêm Loại Phòng Mới' }}</h5>
            <button type="button" class="btn-close" @click="closeModal" aria-label="Close"></button>
          </div>
          <div class="modal-body p-4">
            <div v-if="isFormLocked" class="alert alert-warning d-flex align-items-center mb-4">
              <i class="bi bi-lock-fill me-3 fs-4"></i>
              <div>
                Loại phòng này đã được sử dụng trong một lượt đặt phòng. <br>
                Để đảm bảo tính toàn vẹn dữ liệu, bạn không thể chỉnh sửa thông tin.
              </div>
            </div>
            <div v-if="modalErrorMessage" class="alert alert-danger">{{ modalErrorMessage }}</div>
            <form @submit.prevent="saveType">
              <div class="row g-3">
                <div class="col-12">
                  <label class="form-label">Tên Loại Phòng</label>
                  <input type="text" v-model="form.type_name" class="form-control" required :disabled="isFormLocked" />
                </div>
                <div class="col-md-4">
                  <label class="form-label">Số Giường</label>
                  <input type="number" v-model.number="form.bed_count" class="form-control" min="1" required
                    :disabled="isFormLocked" />
                </div>
                <div class="col-md-4">
                  <label class="form-label">Sức Chứa Người Lớn</label>
                  <input type="number" v-model.number="form.max_occupancy" class="form-control" min="1" required
                    :disabled="isFormLocked" />
                </div>
                <div class="col-md-4">
                  <label class="form-label">Sức Chứa Trẻ Em</label>
                  <input type="number" v-model.number="form.max_occupancy_child" class="form-control" min="0"
                    required :disabled="isFormLocked" />
                </div>
                <div class="col-12">
                  <label class="form-label">Mô Tả</label>
                  <textarea v-model="form.description" class="form-control" rows="3"
                    :disabled="isFormLocked"></textarea>
                </div>
                <div class="col-12">
                  <label class="form-label d-block mb-2">Ảnh Loại Phòng</label>
                  <input type="file" accept="image/*" ref="fileInput" @change="handleImageUpload"
                    style="display: none" multiple :disabled="isFormLocked" />
                  <div v-if="imagePreviews.length > 0" class="image-previews-container mb-3">
                    <div v-for="preview in imagePreviews" :key="preview.url" class="image-preview-item">
                      <img :src="preview.url" alt="Preview" class="image-preview" />
                      <button v-if="!isFormLocked" @click="removeImage(preview)" class="btn-remove-image"
                        title="Xóa ảnh">
                        <i class="bi bi-x-lg"></i>
                      </button>
                    </div>

                  </div>
                  <div v-if="!isFormLocked" class="image-uploader" @click="triggerFileInput" @dragover.prevent
                    @dragleave.prevent @drop.prevent="handleDrop">
                    <div class="uploader-instructions">
                      <i class="bi bi-cloud-arrow-up-fill uploader-icon"></i>
                      <span>Kéo & Thả ảnh vào đây hoặc <strong>nhấn để chọn ảnh</strong></span>
                    </div>
                  </div>
                  <div v-else class="alert alert-info text-center mt-3">
                    Bạn không thể thêm hoặc xóa ảnh khi loại phòng đã có lượt đặt.
                  </div>
                </div>
                <!-- Checkbox Tiện Ích -->
                <div class="col-12">
                  <label class="form-label">Tiện Ích</label>
                  <div class="checkbox-list">
                    <div class="form-check form-switch select-all-switch">
                      <input class="form-check-input" type="checkbox" role="switch" id="select-all-amenities"
                        :checked="isAllAmenitiesSelected" @change="toggleAllAmenities" />
                      <label class="form-check-label" for="select-all-amenities">Chọn Tất Cả Tiện Ích</label>
                    </div>
                    <hr class="my-2" />
                    <div class="form-check" v-for="amenity in amenities" :key="amenity.amenity_id">
                      <input class="form-check-input" type="checkbox" :value="amenity.amenity_id"
                        v-model="form.amenity_ids" :id="'amenity-' + amenity.amenity_id" />
                      <label class="form-check-label" :for="'amenity-' + amenity.amenity_id">{{ amenity.amenity_name
                      }}</label>
                    </div>
                  </div>
                </div>
              </div>
            </form>
          </div>
          <div class="modal-footer modal-footer-custom">
            <button type="button" class="btn btn-secondary" @click="closeModal">
              {{ isFormLocked ? 'Đóng' : 'Hủy' }}
            </button>
            <button type="button" class="btn btn-primary" @click="saveType" v-if="!isFormLocked" :disabled="isSaving">
              <span v-if="isSaving" class="spinner-border spinner-border-sm me-2"></span>
              Lưu Lại
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

const API_BASE_URL = 'http://127.0.0.1:8000';
const apiClient = axios.create({
  baseURL: `${API_BASE_URL}/api`,
  headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }
});

const roomTypes = ref([]);
const amenities = ref([]);
const tuKhoaTim = ref('');
const isModalOpen = ref(false);
const editingType = ref(null);
const fileInput = ref(null);
const currentPage = ref(1);
const itemsPerPage = 10;
const isLoading = ref(false);
const isSaving = ref(false);
const isFormLocked = ref(false); // Biến này sẽ quyết định các trường input trong modal có bị disable hay không
const successMessage = ref('');
const errorMessage = ref('');
const modalErrorMessage = ref('');

const form = ref({});
const existingImages = ref([]);
const newImageFiles = ref([]);

const fetchData = async () => {
  isLoading.value = true;
  errorMessage.value = '';
  try {
    const [roomTypesRes, amenitiesRes] = await Promise.all([
      apiClient.get('/room-types'),
      apiClient.get('/amenities', { params: { per_page: 'all' } }),
    ]);
    roomTypes.value = roomTypesRes.data.data || [];
    amenities.value = amenitiesRes.data.data || [];
  } catch (error) {
    handleApiError('Lấy dữ liệu thất bại', error);
  } finally {
    isLoading.value = false;
  }
};
onMounted(fetchData);

const filteredTypes = computed(() => {
  if (!tuKhoaTim.value) return roomTypes.value || [];
  const searchTerm = tuKhoaTim.value.toLowerCase();
  return (roomTypes.value || []).filter(
    (t) => (t.type_name?.toLowerCase().includes(searchTerm)) || (t.description?.toLowerCase().includes(searchTerm))
  );
});
const totalPages = computed(() => Math.ceil(filteredTypes.value.length / itemsPerPage));
const displayedTypes = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  return filteredTypes.value.slice(start, start + itemsPerPage);
});
const paginationPages = computed(() => {
  const maxPages = 7;
  if (totalPages.value <= maxPages) {
    return Array.from({ length: totalPages.value }, (_, i) => i + 1);
  }
  const middle = Math.ceil(maxPages / 2);
  let start = currentPage.value - middle + 1;
  if (start < 1) start = 1;
  let end = start + maxPages - 1;
  if (end > totalPages.value) {
    end = totalPages.value;
    start = end - maxPages + 1;
  }
  return Array.from({ length: end - start + 1 }, (_, i) => start + i);
});

const resetForm = () => {
  form.value = { bed_count: 1, max_occupancy: 1, max_occupancy_child: 0, amenity_ids: [] };
  existingImages.value = [];
  newImageFiles.value = [];
  isFormLocked.value = false;
  modalErrorMessage.value = '';
};

const moModalThem = () => {
  editingType.value = null;
  resetForm();
  isModalOpen.value = true;
};

const moModalSua = (type) => {
  editingType.value = type;
  resetForm();
  form.value = {
    ...type,
    amenity_ids: type.amenities ? type.amenities.map((a) => a.amenity_id) : [],
  };
  try {
    existingImages.value = type.images ? JSON.parse(type.images) : [];
  } catch (e) {
    existingImages.value = [];
  }
  isFormLocked.value = type.rooms_with_bookings_count > 0;
  isModalOpen.value = true;
};

const closeModal = () => { isModalOpen.value = false; };
const imagePreviews = computed(() => {
  const existing = existingImages.value.map(name => ({
    type: 'existing',
    name: name,
    url: `${API_BASE_URL}/images/room_type/${name}`
  }));
  const news = newImageFiles.value.map(file => ({
    type: 'new',
    file: file,
    url: URL.createObjectURL(file)
  }));
  return [...existing, ...news];
});

const handleFiles = (files) => {
  for (const file of files) {
    if (file && file.type.startsWith('image/')) {
      newImageFiles.value.push(file);
    }
  }
};
const handleImageUpload = (event) => { handleFiles(event.target.files); event.target.value = ''; };
const triggerFileInput = () => {
  if (isFormLocked.value) { 
    modalErrorMessage.value = 'Bạn không thể thêm ảnh khi loại phòng này đã có lượt đặt.';
    return;
  }
  fileInput.value?.click();
};
const handleDrop = (event) => { handleFiles(event.dataTransfer.files); };

const removeImage = async (imageToRemove) => {
  if (imageToRemove.type === 'new') {
    newImageFiles.value = newImageFiles.value.filter(f => f !== imageToRemove.file);
    return;
  }
  if (imageToRemove.type === 'existing') {
    if (!confirm('Bạn có chắc chắn muốn xóa vĩnh viễn ảnh này?')) return;
    isSaving.value = true;
    modalErrorMessage.value = '';
    try {
      const response = await apiClient.post(`/room-types/${editingType.value.type_id}/delete-image`, {
        image_name: imageToRemove.name
      });
      const updatedImagesJson = response.data.data.images;
      existingImages.value = updatedImagesJson ? JSON.parse(updatedImagesJson) : [];
      await fetchData();
      successMessage.value = response.data.message;
    } catch (error) {
      handleApiError('Xóa ảnh thất bại', error);
    } finally {
      isSaving.value = false;
    }
  }
};

const isAllAmenitiesSelected = computed(() => {
  if (!amenities.value || amenities.value.length === 0) return false;
  return form.value.amenity_ids?.length === amenities.value.length;
});
const toggleAllAmenities = (event) => {
  if (isFormLocked.value) return; 
  form.value.amenity_ids = event.target.checked ? amenities.value.map((a) => a.amenity_id) : [];
};

const saveType = async () => {
  if (isFormLocked.value) { 
    modalErrorMessage.value = 'Bạn không thể lưu thay đổi khi loại phòng này đã có lượt đặt.';
    return;
  }
  if (!form.value.type_name || !form.value.type_name.trim() || form.value.bed_count < 1 || form.value.max_occupancy < 1) {
    modalErrorMessage.value = 'Vui lòng điền đầy đủ thông tin bắt buộc (Tên, số giường, sức chứa).';
    return;
  }

  isSaving.value = true;
  modalErrorMessage.value = '';
  successMessage.value = '';
  const formData = new FormData();
  Object.keys(form.value).forEach(key => {
    const ignoredKeys = ['images', 'amenity_ids', 'amenities', 'rooms_count', 'rooms_with_bookings_count'];
    if (!ignoredKeys.includes(key) && form.value[key] !== null && form.value[key] !== undefined) {
      formData.append(key, form.value[key]);
    }
  });
  newImageFiles.value.forEach(file => formData.append('images[]', file));
  (form.value.amenity_ids || []).forEach(id => formData.append('amenity_ids[]', id));
  if (editingType.value) {
    existingImages.value.forEach(imageName => formData.append('existing_images[]', imageName));
    formData.append('_method', 'PUT');
  }

  try {
    const url = editingType.value
      ? `${API_BASE_URL}/api/room-types/${editingType.value.type_id}`
      : `${API_BASE_URL}/api/room-types`;
    const response = await axios.post(url, formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      }
    });
    await fetchData();
    closeModal();
    successMessage.value = response.data.message || 'Lưu loại phòng thành công!';
  } catch (error) {
    handleApiError('Lưu loại phòng thất bại', error);
  } finally {
    isSaving.value = false;
  }
};


const xoaLoaiPhong = async (id) => {
  const typeToDelete = roomTypes.value.find(type => type.type_id === id);
  if (typeToDelete && typeToDelete.rooms_count > 0) {
    errorMessage.value = 'Không thể xóa loại phòng này vì nó đang được sử dụng bởi một hoặc nhiều phòng.';
    return;
  }

  if (confirm('Bạn có chắc chắn muốn xóa loại phòng này? Hành động này không thể hoàn tác.')) {
    try {
      const response = await apiClient.delete(`/room-types/${id}`);
      await fetchData();
      if (displayedTypes.value.length === 0 && currentPage.value > 1) {
        currentPage.value--;
      }
      successMessage.value = response.data.message || 'Xóa loại phòng thành công!';
    } catch (error) {
      handleApiError('Xóa loại phòng thất bại', error);
    }
  }
};

const getFirstImageUrl = (imagesJson) => {
  if (!imagesJson) return null;
  try {
    const images = JSON.parse(imagesJson);
    if (Array.isArray(images) && images.length > 0) return `${API_BASE_URL}/images/room_type/${images[0]}`;
  } catch (e) { }
  return null;
};
const onImageError = (event) => { event.target.src = 'https://via.placeholder.com/70x50.png?text=Loi'; };
const handleApiError = (message, error) => {
  const serverMessage = error.response?.data?.message || 'Có lỗi xảy ra.';
  let errorDetails = '';
  if (error.response?.data?.errors) {
    errorDetails = Object.values(error.response.data.errors).map(e => e[0]).join(' ');
  }
  const finalMessage = `${message}: ${serverMessage} ${errorDetails}`;
  if (isModalOpen.value) {
    modalErrorMessage.value = finalMessage;
  } else {
    errorMessage.value = finalMessage;
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

.filter-card,
.table-container {
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
  overflow: hidden;
}

.booking-table {
  font-size: 0.875rem;
  border-collapse: separate;
  border-spacing: 0;
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
}

.occupancy-info {
  white-space: nowrap;
  font-size: 0.85rem;
  color: #34495e;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.occupancy-info .bi {
  color: #7f8c8d;
}

.room-image {
  width: 70px;
  height: 50px;
  object-fit: cover;
  border-radius: 8px;
}

.tags-container {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}

.badge {
  padding: 0.35em 0.7em;
  font-size: 0.7rem;
  font-weight: 600;
  border-radius: 20px;
}

.badge-info {
  background-color: #eaf6fb;
  color: #3498db;
}

.badge-secondary {
  background-color: #f3f4f6;
  color: #7f8c8d;
}

.action-buttons {
  white-space: nowrap;
}

.action-buttons .btn,
.action-buttons .badge-in-use {
  margin: 0 2px;
}

.badge-in-use {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 31px;
  height: 31px;
  background-color: #f3f4f6;
  color: #7f8c8d;
  border-radius: 0.25rem;
  font-size: 0.9rem;
  cursor: help;
  vertical-align: middle;
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
  color: #ffffff;
}

.modal-backdrop {
  background-color: rgba(0, 0, 0, 0.4);
}

.modal-custom {
  border-radius: 16px;
  border: none;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.modal-header-custom,
.modal-footer-custom {
  background-color: #f4f7f9;
  border-color: #e5eaee;
  padding: 1.5rem;
}

.checkbox-list {
  max-height: 200px;
  overflow-y: auto;
  border: 1px solid #e5eaee;
  border-radius: 8px;
  padding: 1rem;
  background-color: #ffffff;
  font-size: 0.9rem;
}

.select-all-switch .form-check-label {
  font-weight: 600;
  color: #34495e;
}

.image-previews-container {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  padding: 1rem;
  border: 1px solid #e5eaee;
  border-radius: 12px;
  background-color: #fafafa;
}

.image-preview-item {
  position: relative;
  width: 100px;
  height: 100px;
}

.image-preview {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 8px;
  border: 1px solid #e5eaee;
}

.btn-remove-image {
  position: absolute;
  top: -8px;
  right: -8px;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background-color: #dc3545;
  color: white;
  border: 2px solid white;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  padding: 0;
  font-size: 0.7rem;
  line-height: 1;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
  transition: transform 0.2s ease;
}

.btn-remove-image:hover {
  transform: scale(1.1);
}

.image-uploader {
  border: 2px dashed #d1d5db;
  border-radius: 12px;
  padding: 1.5rem;
  text-align: center;
  cursor: pointer;
  transition: all 0.2s ease;
  background-color: #f9fafb;
}

.image-uploader:hover {
  border-color: #3498db;
  background-color: #f0f8ff;
}

.uploader-instructions {
  display: flex;
  flex-direction: column;
  align-items: center;
  color: #7f8c8d;
  pointer-events: none;
}

.uploader-icon {
  font-size: 2.5rem;
  color: #bdc3c7;
  margin-bottom: 0.5rem;
}
</style>