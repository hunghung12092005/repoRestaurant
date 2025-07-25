<template>
  <div class="page-container">
    <!-- Thông báo -->
    <div v-if="showAlert" :class="['alert', alertType, 'custom-alert']" role="alert">
      {{ alertMessage }}
      <span class="close-btn" @click="showAlert = false">×</span>
    </div>

    <!-- Tiêu đề trang -->
    <div class="page-header mb-4">
      <h1 class="page-title">Quản lý Tin tức</h1>
      <p class="page-subtitle">Tạo mới, chỉnh sửa và quản lý các bài viết, tin tức trên website.</p>
    </div>

    <!-- Bộ lọc và tìm kiếm -->
    <div class="card filter-card mb-4">
      <div class="card-body d-flex justify-content-between align-items-center">
        <div class="col-md-5">
          <input
            v-model="searchQuery"
            type="text"
            class="form-control"
            @input="handleSearch"
            placeholder="Tìm theo tiêu đề tin tức..."
          />
        </div>
        <button class="btn btn-primary" @click="openModal(false)">
          <i class="bi bi-plus-circle me-2"></i>Thêm Tin Tức
        </button>
      </div>
    </div>

    <!-- Bảng danh sách tin tức -->
    <div class="table-container">
      <table class="table booking-table align-middle">
        <thead>
          <tr>
            <th style="width: 10%;">Ảnh</th>
            <th style="width: 40%;">Tiêu Đề</th>
            <th style="width: 20%;">Danh Mục & Tác Giả</th>
            <th class="text-center" style="width: 15%;">Trạng Thái</th>
            <th class="text-center" style="width: 15%;">Hành Động</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in items" :key="item.id">
            <td>
              <img :src="getImageUrl(item.thumbnail)" alt="thumbnail" class="room-image" />
            </td>
            <td>
              <div class="fw-bold type-name">{{ item.title }}</div>
              <p class="description-text mb-0">{{ item.summary || 'Không có tóm tắt' }}</p>
            </td>
            <td>
                <div class="fw-bold">{{ item.category?.name || 'N/A' }}</div>
                <p class="description-text mb-0 fst-italic">bởi {{ item.author?.name || 'Không rõ' }}</p>
            </td>
            <td class="text-center">
              <span class="d-block mb-1 badge" :class="item.status ? 'badge-success' : 'badge-secondary'">
                {{ item.status ? 'Hiển thị' : 'Ẩn' }}
              </span>
               <span v-if="item.is_pinned" class="badge badge-info">
                <i class="bi bi-pin-angle-fill me-1"></i>Ghim
              </span>
            </td>
            <td class="text-center action-buttons">
              <button class="btn btn-outline-info btn-sm" title="Xem chi tiết" @click="openDetailModal(item)">
                <i class="bi bi-eye-fill"></i>
              </button>
              <button class="btn btn-outline-primary btn-sm" title="Sửa" @click="openModal(true, item)">
                <i class="bi bi-pencil-fill"></i>
              </button>
              <button class="btn btn-outline-danger btn-sm" title="Xóa" @click="deleteItem(item.id)">
                <i class="bi bi-trash-fill"></i>
              </button>
            </td>
          </tr>
          <tr v-if="!items.length">
            <td colspan="5" class="text-center py-5">Không tìm thấy tin tức nào.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Phân trang -->
    <nav v-if="totalPages > 1" aria-label="Page navigation" class="mt-4">
      <ul class="pagination justify-content-center">
        <li class="page-item" :class="{ disabled: currentPage === 1 }">
          <a class="page-link" href="#" @click.prevent="fetchData(1)">««</a>
        </li>
        <li class="page-item" :class="{ disabled: currentPage === 1 }">
          <a class="page-link" href="#" @click.prevent="fetchData(currentPage - 1)">«</a>
        </li>
        <li v-for="page in pageRange" :key="page" class="page-item" :class="{ active: page === currentPage }">
          <a class="page-link" href="#" @click.prevent="fetchData(page)">{{ page }}</a>
        </li>
        <li class="page-item" :class="{ disabled: currentPage === totalPages }">
          <a class="page-link" href="#" @click.prevent="fetchData(currentPage + 1)">»</a>
        </li>
        <li class="page-item" :class="{ disabled: currentPage === totalPages }">
          <a class="page-link" href="#" @click.prevent="fetchData(totalPages)">»»</a>
        </li>
      </ul>
    </nav>

    <!-- Modal Tạo/Sửa (GIỮ NGUYÊN ID VÀ CLASS FADE) -->
    <div class="modal fade" id="newsModal" tabindex="-1" aria-labelledby="newsModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content modal-custom">
          <div class="modal-header modal-header-custom">
            <h5 class="modal-title" id="newsModalLabel">{{ isEditMode ? 'Chỉnh sửa Tin tức' : 'Tạo mới Tin tức' }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
          </div>
          <form @submit.prevent="handleSubmit">
            <div class="modal-body p-4">
              <div class="row g-4">
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
                    <div v-if="isModalVisible" class="editor-container">
                      <QuillEditor theme="snow" toolbar="full" contentType="html" v-model:content="form.content"/>
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
                    <label for="tags" class="form-label">Tags (cách nhau bởi dấu phẩy)</label>
                    <input type="text" class="form-control" id="tags" v-model="form.tags">
                  </div>
                  <div class="mb-3">
                    <label for="thumbnailFile" class="form-label">Ảnh đại diện</label>
                    <input type="file" class="form-control" id="thumbnailFile" @change="handleFileChange" accept="image/png,image/jpeg,image/jpg,image/gif">
                    <div v-if="thumbnailError" class="text-danger mt-1 small">{{ thumbnailError }}</div>
                    <img v-if="thumbnailPreview" :src="thumbnailPreview" class="mt-2 img-fluid rounded border"/>
                  </div>
                  <div class="border rounded p-3">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" role="switch" id="statusSwitch" v-model="form.status" :true-value="1" :false-value="0">
                        <label class="form-check-label" for="statusSwitch">Hiển thị bài viết</label>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="pinnedSwitch" v-model="form.is_pinned">
                        <label class="form-check-label" for="pinnedSwitch">Ghim làm tin nổi bật</label>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="modal-footer modal-footer-custom">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
              <button type="submit" class="btn btn-primary" :disabled="isSubmitting">
                <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                {{ isEditMode ? 'Lưu thay đổi' : 'Tạo mới' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
    
    <!-- Modal Chi tiết -->
    <div class="modal fade" id="detailNewsModal" tabindex="-1" aria-labelledby="detailNewsModalLabel" aria-hidden="true">
        <!-- ... Nội dung modal chi tiết không thay đổi ... -->
    </div>
  </div>
</template>

<script setup>
// ... TOÀN BỘ SCRIPT CỦA BẠN GIỮ NGUYÊN ...
import { ref, onMounted, inject, computed } from 'vue';
import axios from 'axios';
import { Modal } from 'bootstrap';
import { QuillEditor } from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.snow.css';

const items = ref([]);
const categories = ref([]);
const form = ref({
    title: '', summary: '', content: '', category_id: null,
    tags: '', status: 1, is_pinned: false
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

let editModalInstance = null;
let detailModalInstance = null;

const apiUrl = inject('apiUrl');

const axiosInstance = axios.create({
    baseURL: apiUrl,
    headers: { 'Accept': 'application/json' }
});

const getLoggedInUser = () => {
    const user = localStorage.getItem('userInfo');
    if (user) {
        try { return JSON.parse(user); }
        catch (e) { return null; }
    }
    return null;
};

const pageRange = computed(() => {
  const maxPages = 5;
  let start = Math.max(1, currentPage.value - Math.floor(maxPages / 2));
  let end = Math.min(totalPages.value, start + maxPages - 1);
  if (end - start + 1 < maxPages) {
    start = Math.max(1, end - maxPages + 1);
  }
  return Array.from({ length: end - start + 1 }, (_, i) => start + i);
});

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
    if (!filename) return 'https://via.placeholder.com/80x50.png?text=No+Image';
    return `${apiUrl}/images/news_thumbnails/${filename.split('/').pop()}`;
};

const resetForm = () => {
    form.value = {
        title: '', summary: '', content: '', category_id: null,
        tags: '', status: 1, is_pinned: false
    };
    thumbnailFile.value = null;
    thumbnailPreview.value = '';
    thumbnailError.value = '';
    const fileInput = document.querySelector('#newsModal input[type="file"]');
    if (fileInput) fileInput.value = '';
};

const validateFile = (file) => {
    if (!file) return true;
    const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
    if (!validTypes.includes(file.type)) {
        thumbnailError.value = 'File phải là ảnh (jpeg, png, jpg, gif).';
        return false;
    }
    if (file.size > 2 * 1024 * 1024) {
        thumbnailError.value = 'File không được lớn hơn 2MB.';
        return false;
    }
    thumbnailError.value = '';
    return true;
};

const fetchData = async (page = 1) => {
    try {
        const response = await axiosInstance.get('/api/news', {
            params: { page: page, q: searchQuery.value, from_admin: true }
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

let searchTimeout = null;
const handleSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => { fetchData(1); }, 300);
};

const openModal = (editMode = false, item = null) => {
    isEditMode.value = editMode;
    if (editMode && item) {
        form.value = { ...item, content: item.content ?? '', status: item.status ? 1 : 0, is_pinned: !!item.is_pinned };
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
        const response = await axiosInstance.get(`/api/news/${item.id}`, { params: { from_admin: true } });
        selectedNewsDetail.value = response.data;
    } catch (error) {
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
    if (thumbnailFile.value && !validateFile(thumbnailFile.value)) return;
    isSubmitting.value = true;
    const formData = new FormData();
    formData.append('title', form.value.title);
    formData.append('content', form.value.content || '');
    formData.append('category_id', form.value.category_id);
    formData.append('status', form.value.status ? '1' : '0');
    formData.append('is_pinned', form.value.is_pinned ? '1' : '0');
    if (form.value.summary) formData.append('summary', form.value.summary);
    if (form.value.tags) formData.append('tags', form.value.tags);
    if (thumbnailFile.value) formData.append('thumbnail', thumbnailFile.value);
    try {
        if (isEditMode.value) {
            formData.append('_method', 'PUT');
            await axiosInstance.post(`/api/news/${form.value.id}`, formData, { headers: { 'Content-Type': 'multipart/form-data' } });
        } else {
            const user = getLoggedInUser();
            if (!user || !user.id) {
                showNotification('Không tìm thấy thông tin người dùng. Vui lòng đăng nhập lại.', 'error');
                isSubmitting.value = false;
                return;
            }
            formData.append('author_id', user.id);
            await axiosInstance.post('/api/news', formData, { headers: { 'Content-Type': 'multipart/form-data' } });
        }
        showNotification(`Thao tác thành công!`);
        editModalInstance.hide();
        fetchData(isEditMode.value ? currentPage.value : 1);
    } catch (error) {
        const errorMessage = error.response?.data?.errors ? Object.values(error.response.data.errors).flat().join('<br/>') : 'Đã có lỗi xảy ra.';
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
.booking-table { font-size: 0.875rem; border-collapse: separate; border-spacing: 0; min-width: 900px; }
.booking-table thead th { background-color: #f8f9fa; color: #7f8c8d; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e5eaee; padding: 1rem; white-space: nowrap; }
.booking-table td { padding: 1rem; border-bottom: 1px solid #e5eaee; vertical-align: middle; }
.booking-table tbody tr:last-child td { border-bottom: none; }
.booking-table tbody tr:hover { background-color: #f9fafb; }
.type-name { font-size: 1rem; font-weight: 600; }
.description-text { font-size: 0.8rem; color: #7f8c8d; }
.room-image { width: 70px; height: 50px; object-fit: cover; border-radius: 8px; }

.badge { padding: 0.4em 0.8em; font-size: 0.75rem; font-weight: 600; border-radius: 20px; letter-spacing: 0.5px; }
.badge-info { background-color: #eaf6fb; color: #3498db; }
.badge-secondary { background-color: #f3f4f6; color: #7f8c8d; }
.badge-success { background-color: #e6f9f0; color: #2ecc71; }

.action-buttons { white-space: nowrap; }
.action-buttons .btn { margin: 0 2px; }

.pagination .page-link { border: none; border-radius: 8px; margin: 0 4px; color: #7f8c8d; font-weight: 600; }
.pagination .page-item.active .page-link { background-color: #3498db; color: white; }

.modal-custom { border-radius: 16px; border: none; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); }
.modal-header-custom { background-color: #f4f7f9; border-bottom: 1px solid #e5eaee; padding: 1.5rem; }
.modal-footer-custom { background-color: #f4f7f9; border-top: 1px solid #e5eaee; padding: 1rem 1.5rem; }

.custom-alert { position: fixed; top: 20px; right: 20px; z-index: 1060; min-width: 300px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); border-radius: 8px; }
.close-btn { cursor: pointer; float: right; font-size: 1.5rem; line-height: 1; }

.editor-container { border: 1px solid #e5eaee; border-radius: 8px; overflow: hidden; }
:deep(.ql-toolbar.ql-snow) { border-bottom: 1px solid #e5eaee; }
/* === [THAY ĐỔI CHÍNH Ở ĐÂY] === */
:deep(.ql-editor) {
  height: 400px;      /* Đặt chiều cao cố định */
  overflow-y: auto;   /* Thêm thanh cuộn khi nội dung tràn */
  font-size: 1rem;
}

.news-detail-modal-body .metadata { font-size: 0.9rem; display: flex; flex-wrap: wrap; gap: 5px; align-items: center; }
.news-detail-modal-body .content-wrapper { line-height: 1.7; word-wrap: break-word; }
.news-detail-modal-body .content-wrapper :deep(p) { margin-bottom: 1rem; }
.news-detail-modal-body .content-wrapper :deep(h1),
.news-detail-modal-body .content-wrapper :deep(h2),
.news-detail-modal-body .content-wrapper :deep(h3) { margin-top: 1.5rem; margin-bottom: 1rem; font-weight: 600; }
.news-detail-modal-body .content-wrapper :deep(img) { max-width: 100%; height: auto; border-radius: 8px; margin: 1rem 0; }
.news-detail-modal-body .content-wrapper :deep(blockquote) { border-left: 4px solid #ccc; padding-left: 1rem; margin-left: 0; font-style: italic; color: #6c757d; }
</style>