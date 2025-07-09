<template>
  <!-- ... HTML của bạn giữ nguyên ... -->
  <div class="staff-container">
    <!-- Notification Alert -->
    <div v-if="showAlert" :class="['alert', alertType, 'custom-alert']" role="alert">
      {{ alertMessage }}
      <span class="close-btn" @click="showAlert = false">×</span>
    </div>

    <!-- Header Section -->
    <div class="header-section mb-4">
      <h3 class="fw-bold">Quản lý Tin tức</h3>
      <div class="d-flex justify-content-between align-items-center mt-3">
        <div class="search-form position-relative">
          <input type="text" class="form-control" v-model="searchQuery"
            @input="handleSearch"
            placeholder="Tìm kiếm theo tiêu đề..." />
          <i class="bi bi-search search-icon position-absolute"></i>
        </div>
        <button class="btn btn-primary shadow-sm" @click="openModal(false)">
          <i class="bi bi-plus-circle"></i> Thêm mới
        </button>
      </div>
    </div>

    <!-- Table Section -->
    <div class="card shadow-sm rounded-3">
      <div class="card-body p-0">
        <div class="table-responsive custom-scroll">
          <table class="table table-hover table-bordered mb-0">
            <thead class="table-light">
              <tr>
                <th>Ảnh</th>
                <th>Tiêu đề</th>
                <th>Danh mục</th>
                <th>Trạng thái</th>
                <th>Ngày đăng</th>
                <th style="width: 180px;">Hành động</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in items" :key="item.id">
                <td>
                  <img :src="getImageUrl(item.thumbnail)" alt="thumbnail"
                    style="width: 80px; height: 50px; object-fit: cover; border-radius: 4px;">
                </td>
                <td>{{ item.title }}</td>
                <td>{{ item.category?.name || 'N/A' }}</td>
                <td>
                  <span :class="['badge', item.status ? 'bg-success' : 'bg-secondary']">
                    {{ item.status ? 'Hiển thị' : 'Ẩn' }}
                  </span>
                </td>
                <td>{{ formatDate(item.publish_date) }}</td>
                <td>
                  <button class="btn btn-sm btn-outline-info me-2" @click="openDetailModal(item)" title="Xem chi tiết">
                    <i class="bi bi-eye"></i>
                  </button>
                  <button class="btn btn-sm btn-outline-warning me-2" @click="openModal(true, item)" title="Sửa">
                    <i class="bi bi-pencil-square"></i>
                  </button>
                  <button class="btn btn-sm btn-outline-danger" @click="deleteItem(item.id)" title="Xóa">
                    <i class="bi bi-trash"></i>
                  </button>
                </td>
              </tr>
              <tr v-if="!items.length">
                <td colspan="6" class="text-center text-muted">Không tìm thấy tin tức</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="totalPages > 1" class="pagination-controls d-flex justify-content-center align-items-center mt-4">
      <button class="btn btn-outline-secondary me-2 shadow-sm rounded-pill" :disabled="currentPage === 1" @click="fetchData(currentPage - 1)">
        Trước
      </button>
      <span class="mx-3 font-weight-bold">Trang {{ currentPage }} / {{ totalPages }}</span>
      <button class="btn btn-outline-secondary shadow-sm rounded-pill" :disabled="currentPage === totalPages" @click="fetchData(currentPage + 1)">
        Tiếp
      </button>
    </div>

    <!-- Create/Edit Modal -->
    <div class="modal fade" id="newsModal" tabindex="-1" aria-labelledby="newsModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-xl">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="newsModalLabel">{{ isEditMode ? 'Chỉnh sửa Tin tức' : 'Tạo mới Tin tức' }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
          </div>
          <form @submit.prevent="handleSubmit">
            <div class="modal-body">
              <div class="row">
                <div class="col-md-8">
                  <div class="mb-3">
                    <label for="title" class="form-label">Tiêu đề</label>
                    <input type="text" class="form-control" id="title" v-model="form.title" required>
                  </div>
                  <div class="mb-3">
                    <label for="summary" class="form-label">Tóm tắt</label>
                    <textarea class="form-control" id="summary" rows="3" v-model="form.summary"></textarea>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Nội dung</label>
                    <div v-if="isModalVisible">
                      <QuillEditor
                          theme="snow"
                          toolbar="full"
                          contentType="html"
                          v-model:content="form.content"
                      />
                    </div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="mb-3">
                    <label for="category_id" class="form-label">Danh mục</label>
                    <select class="form-select" id="category_id" v-model="form.category_id" required>
                      <option :value="null" disabled>-- Chọn danh mục --</option>
                      <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                    </select>
                  </div>
                  <div class="mb-3">
                    <label for="thumbnailFile" class="form-label">Ảnh đại diện</label>
                    <input type="file" class="form-control" id="thumbnailFile" @change="handleFileChange" accept="image/png,image/jpeg,image/jpg,image/gif">
                    <div v-if="thumbnailError" class="text-danger mt-1">{{ thumbnailError }}</div>
                    <img v-if="thumbnailPreview" :src="thumbnailPreview" class="mt-2" style="max-width: 100%; border-radius: 4px;"/>
                  </div>
                  <div class="mb-3">
                    <label for="tags" class="form-label">Tags (cách nhau bởi dấu phẩy)</label>
                    <input type="text" class="form-control" id="tags" v-model="form.tags">
                  </div>
                  <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" role="switch" id="statusSwitch" v-model="form.status" :true-value="1" :false-value="0">
                    <label class="form-check-label" for="statusSwitch">Hiển thị</label>
                  </div>
                  <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" role="switch" id="pinnedSwitch" v-model="form.is_pinned">
                    <label class="form-check-label" for="pinnedSwitch">Ghim tin nổi bật</label>
                  </div>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
              <button type="submit" class="btn btn-primary" :disabled="isSubmitting">
                {{ isEditMode ? 'Lưu thay đổi' : 'Tạo mới' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
    
    <!-- Detail Modal -->
    <div class="modal fade" id="detailNewsModal" tabindex="-1" aria-labelledby="detailNewsModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="detailNewsModalLabel">Chi tiết Tin tức</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
          </div>
          <div class="modal-body news-detail-modal-body">
            <div v-if="selectedNewsDetail">
              <h2 class="mb-3">{{ selectedNewsDetail.title }}</h2>
              <div class="metadata mb-4 text-muted">
                  <span><i class="bi bi-person-fill"></i> {{ selectedNewsDetail.author ? selectedNewsDetail.author.name : 'Không xác định' }}</span>
                  <span class="mx-2">|</span>
                  <span><i class="bi bi-folder-fill"></i> {{ selectedNewsDetail.category?.name || 'N/A' }}</span>
                  <span class="mx-2">|</span>
                  <span><i class="bi bi-calendar-event-fill"></i> {{ formatDate(selectedNewsDetail.publish_date) }}</span>
                  <span class="mx-2">|</span>
                  <span><i class="bi bi-eye-fill"></i> {{ selectedNewsDetail.views.toLocaleString('vi-VN') }} lượt xem</span>
              </div>
              <hr>
              <div class="content-wrapper" v-html="selectedNewsDetail.content"></div>
            </div>
            <div v-else class="text-center py-5">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Đang tải...</span>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, inject } from 'vue';
import axios from 'axios';
import { Modal } from 'bootstrap';
import { QuillEditor } from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.snow.css';

// State Variables
const items = ref([]);
const categories = ref([]);
const form = ref({
    title: '',
    summary: '',
    content: '',
    category_id: null,
    tags: '',
    status: 1,
    is_pinned: false
});
const isEditMode = ref(false);
const thumbnailFile = ref(null);
const thumbnailPreview = ref('');
const thumbnailError = ref('');
const searchQuery = ref('');
const currentPage = ref(1);
const totalPages = ref(1);
const showAlert = ref(false);
const alertMessage = ref('');
const alertType = ref('alert-success');
const isModalVisible = ref(false);
const selectedNewsDetail = ref(null);
const isSubmitting = ref(false);

// Modal Instances
let editModalInstance = null;
let detailModalInstance = null;

const apiUrl = inject('apiUrl');

// Axios Instance
const axiosInstance = axios.create({
    baseURL: apiUrl,
    headers: {
        'Accept': 'application/json',
    }
});

// Thêm hàm lấy thông tin người dùng từ localStorage
const getLoggedInUser = () => {
    const user = localStorage.getItem('userInfo');
    if (user) {
        try {
            return JSON.parse(user);
        } catch (e) {
            console.error("Lỗi khi parse thông tin người dùng từ localStorage", e);
            return null;
        }
    }
    return null;
};


// Helper Functions
const showNotification = (message, type = 'success') => {
    alertType.value = type === 'success' ? 'alert-success' : 'alert-danger';
    alertMessage.value = message;
    showAlert.value = true;
    setTimeout(() => { showAlert.value = false; }, 3000);
};

const formatDate = (dateString) => {
    return dateString ? new Date(dateString).toLocaleDateString('vi-VN') : 'N/A';
};

const getImageUrl = (filename) => {
    if (!filename) {
        return 'https://via.placeholder.com/80x50';
    }
    return `${apiUrl}/images/news_thumbnails/${filename.split('/').pop()}`;
};

const resetForm = () => {
    form.value = {
        title: '',
        summary: '',
        content: '',
        category_id: null,
        tags: '',
        status: 1,
        is_pinned: false
    };
    thumbnailFile.value = null;
    thumbnailPreview.value = '';
    thumbnailError.value = '';
    const fileInput = document.querySelector('#newsModal input[type="file"]');
    if (fileInput) fileInput.value = '';
};

// Validate File Type
const validateFile = (file) => {
    if (!file) return true;
    const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
    if (!validTypes.includes(file.type)) {
        thumbnailError.value = 'File phải là ảnh (jpeg, png, jpg, gif).';
        return false;
    }
    if (file.size > 2 * 1024 * 1024) { // 2MB limit
        thumbnailError.value = 'File không được lớn hơn 2MB.';
        return false;
    }
    thumbnailError.value = '';
    return true;
};

// API Functions
const fetchData = async (page = 1) => {
    try {
        const response = await axiosInstance.get('/api/news', {
            params: { 
                page: page, 
                q: searchQuery.value,
                from_admin: true // [THAY ĐỔI] Gửi tham số để backend biết đây là admin
            }
        });
        items.value = response.data.data;
        currentPage.value = response.data.current_page;
        totalPages.value = response.data.last_page;
    } catch (error) {
        showNotification('Không thể tải danh sách tin tức.', 'error');
    }
};

const fetchCategories = async () => {
    try {
        const response = await axiosInstance.get('/api/news-categories');
        categories.value = response.data;
    } catch (error) {
        showNotification('Không thể tải danh mục.', 'error');
    }
};

// Event Handlers
let searchTimeout = null;
const handleSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        fetchData(1);
    }, 300);
};

const openModal = (editMode = false, item = null) => {
    isEditMode.value = editMode;
    if (editMode && item) {
        form.value = {
            ...item,
            content: item.content ?? '',
            status: item.status ? 1 : 0,
            is_pinned: !!item.is_pinned
        };
        thumbnailPreview.value = getImageUrl(item.thumbnail);
        thumbnailFile.value = null;
    } else {
        resetForm();
    }
    editModalInstance.show();
};

const openDetailModal = async (item) => {
    selectedNewsDetail.value = null;
    detailModalInstance.show();
    try {
        const response = await axiosInstance.get(`/api/news/${item.id}`, {
            params: {
                from_admin: true // Gửi tham số để backend biết đây là admin
            }
        });
        selectedNewsDetail.value = response.data;
    } catch (error)
    {
        showNotification('Không thể tải chi tiết tin tức.', 'error');
        detailModalInstance.hide();
    }
};

const handleFileChange = (event) => {
    const file = event.target.files[0];
    if (file && validateFile(file)) {
        thumbnailFile.value = file;
        thumbnailPreview.value = URL.createObjectURL(file);
    } else {
        thumbnailFile.value = null;
        if (!file) thumbnailPreview.value = '';
    }
};

const handleSubmit = async () => {
    if (thumbnailFile.value && !validateFile(thumbnailFile.value)) {
        return;
    }

    isSubmitting.value = true;
    const formData = new FormData();
    
    formData.append('title', form.value.title);
    formData.append('content', form.value.content || '');
    formData.append('category_id', form.value.category_id);
    formData.append('status', form.value.status ? '1' : '0');
    formData.append('is_pinned', form.value.is_pinned ? '1' : '0');
    
    if (form.value.summary) formData.append('summary', form.value.summary);
    if (form.value.tags) formData.append('tags', form.value.tags);
    
    if (thumbnailFile.value) {
        formData.append('thumbnail', thumbnailFile.value);
    }
    
    try {
        if (isEditMode.value) {
            formData.append('_method', 'PUT');
            await axiosInstance.post(`/api/news/${form.value.id}`, formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });
        } else {
            const user = getLoggedInUser();
            if (!user || !user.id) {
                showNotification('Không tìm thấy thông tin người dùng. Vui lòng đăng nhập lại.', 'error');
                isSubmitting.value = false;
                return;
            }
            formData.append('author_id', user.id);

            await axiosInstance.post('/api/news', formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });
        }

        showNotification(`Thao tác thành công!`);
        editModalInstance.hide();
        fetchData(isEditMode.value ? currentPage.value : 1);
    } catch (error) {
        const errorMessage = error.response?.data?.errors
            ? Object.values(error.response.data.errors).flat().join('<br/>')
            : 'Đã có lỗi xảy ra.';
        showNotification('Thao tác thất bại: <br/>' + errorMessage, 'error');
    } finally {
        isSubmitting.value = false;
    }
};

const deleteItem = async (id) => {
    if (confirm('Bạn có chắc chắn muốn xóa tin tức này?')) {
        try {
            await axiosInstance.delete(`/api/news/${id}`);
            showNotification('Xóa tin tức thành công!');
            if (items.value.length === 1 && currentPage.value > 1) {
                fetchData(currentPage.value - 1);
            } else {
                fetchData(currentPage.value);
            }
        } catch (error) {
            showNotification('Xóa thất bại.', 'error');
        }
    }
};

// Lifecycle Hook
onMounted(() => {
    fetchData();
    fetchCategories();
    
    const editModalElement = document.getElementById('newsModal');
    if (editModalElement) {
        editModalInstance = new Modal(editModalElement);
        editModalElement.addEventListener('shown.bs.modal', () => { isModalVisible.value = true; });
        editModalElement.addEventListener('hidden.bs.modal', () => { isModalVisible.value = false; });
    }

    const detailModalElement = document.getElementById('detailNewsModal');
    if (detailModalElement) {
        detailModalInstance = new Modal(detailModalElement);
    }
});
</script>

<style scoped>
/* ... CSS của bạn giữ nguyên ... */
.staff-container {
  padding: 20px;
}
.header-section .d-flex {
  gap: 15px;
}
.search-form input {
  padding-right: 40px;
}
.search-form .search-icon {
  top: 50%;
  right: 15px;
  transform: translateY(-50%);
  color: #666;
}
.table th {
  background-color: #f8f9fa;
  font-weight: 600;
  color: #333;
  white-space: nowrap;
  padding: 10px 15px;
}
.table td {
  color: #666;
  white-space: nowrap;
  padding: 10px 15px;
  vertical-align: middle;
}
.table td:last-child {
  text-align: center;
  min-width: 180px;
}
.table-hover tbody tr:hover {
  background-color: #f1f3f5;
}
.btn-outline-primary, .btn-outline-warning, .btn-outline-danger, .btn-outline-info {
  padding: 4px 8px;
  font-size: 0.9rem;
}
.pagination-controls .btn {
  padding: 8px 20px;
  font-size: 0.85rem;
  border-width: 1px;
}
.pagination-controls .btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}
.pagination-controls span {
  font-size: 0.9rem;
  color: #2c3e50;
  font-weight: 600;
}
.table-responsive.custom-scroll {
  overflow-x: auto;
}
.custom-scroll::-webkit-scrollbar {
  height: 8px;
}
.custom-scroll::-webkit-scrollbar-track {
  background: #f1f3f5;
  border-radius: 4px;
}
.custom-scroll::-webkit-scrollbar-thumb {
  background: #16B978;
  border-radius: 4px;
}
.custom-alert {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 1050;
  min-width: 300px;
  padding: 15px;
  border-radius: 8px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  animation: fadeIn 0.3s ease-in, fadeOut 0.3s ease-out 2.7s forwards;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.custom-alert.alert-success {
  background-color: #d4edda;
  border-color: #c3e6cb;
  color: #155724;
}
.custom-alert.alert-danger {
  background-color: #f8d7da;
  border-color: #f5c6cb;
  color: #721c24;
}
.custom-alert .close-btn {
  cursor: pointer;
  font-size: 1.2rem;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: translateY(0); }
}
@keyframes fadeOut {
  from { opacity: 1; transform: translateY(0); }
  to { opacity: 0; transform: translateY(-10px); }
}
:deep(.ql-editor) {
  height: 400px;
  overflow-y: auto;
}
.news-detail-modal-body .metadata {
    font-size: 0.9rem;
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    align-items: center;
}
.news-detail-modal-body .content-wrapper {
    line-height: 1.7;
    word-wrap: break-word;
}
.news-detail-modal-body .content-wrapper :deep(p) {
    margin-bottom: 1rem;
}
.news-detail-modal-body .content-wrapper :deep(h1),
.news-detail-modal-body .content-wrapper :deep(h2),
.news-detail-modal-body .content-wrapper :deep(h3) {
    margin-top: 1.5rem;
    margin-bottom: 1rem;
    font-weight: 600;
}
.news-detail-modal-body .content-wrapper :deep(img) {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    margin: 1rem 0;
}
.news-detail-modal-body .content-wrapper :deep(blockquote) {
    border-left: 4px solid #ccc;
    padding-left: 1rem;
    margin-left: 0;
    font-style: italic;
    color: #6c757d;
}
</style>