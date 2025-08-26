<template>
  <div class="page-container">
    <!-- Thông báo -->
    <div v-if="showAlert" :class="['alert', alertType, 'custom-alert']" role="alert">
      {{ alertMessage }}
      <span class="close-btn" @click="showAlert = false">×</span>
    </div>

    <!-- Tiêu đề trang -->
    <div class="page-header mb-4">
      <h1 class="page-title">Quản lý Danh mục Tin tức</h1>
      <p class="page-subtitle">Tạo và quản lý các danh mục cho bài viết và tin tức.</p>
    </div>

    <!-- Bộ lọc và tìm kiếm -->
    <div class="card filter-card mb-4">
      <div class="card-body d-flex justify-content-between align-items-center">
        <div class="col-md-5">
          <input
            v-model="searchQuery"
            type="text"
            class="form-control"
            placeholder="Tìm theo tên danh mục..."
          />
        </div>
        <button class="btn btn-primary" @click="openModal(false)">
          <i class="bi bi-plus-circle me-2"></i>Thêm Danh Mục
        </button>
      </div>
    </div>

    <!-- Bảng danh sách danh mục -->
    <div class="table-container">
      <table class="table booking-table align-middle">
        <thead>
          <tr>
            <th style="width: 50%;">Tên Danh Mục</th>
            <th style="width: 30%;">Số bài viết</th>
            <th class="text-center" style="width: 20%;">Hành Động</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="category in paginatedItems" :key="category.id">
            <td>
              <div class="fw-bold type-name">{{ category.name }}</div>
              <p class="description-text mb-0">{{ category.description || 'Không có mô tả' }}</p>
            </td>
            <td>
              <!-- SỬ DỤNG DỮ LIỆU 'news_count' TỪ API -->
              <span class="badge badge-info">{{ category.news_count || 0 }} bài viết</span>
            </td>
            <td class="text-center action-buttons">
              <button class="btn btn-outline-primary btn-sm" title="Sửa" @click="openModal(true, category)">
                <i class="bi bi-pencil-fill"></i>
              </button>
              <button class="btn btn-outline-danger btn-sm" title="Xóa" @click="deleteItem(category.id)">
                <i class="bi bi-trash-fill"></i>
              </button>
            </td>
          </tr>
          <tr v-if="!paginatedItems.length">
            <td colspan="3" class="text-center py-5">Không tìm thấy danh mục nào.</td>
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

    <!-- Modal Tạo/Sửa -->
    <div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-custom">
          <div class="modal-header modal-header-custom">
            <h5 class="modal-title" id="categoryModalLabel">{{ isEditMode ? 'Chỉnh sửa Danh mục' : 'Tạo mới Danh mục' }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
          </div>
          <form @submit.prevent="handleSubmit">
            <div class="modal-body p-4">
              <div class="mb-3">
                <label for="name" class="form-label">Tên danh mục</label>
                <input type="text" class="form-control" id="name" v-model="form.name" required>
              </div>
              <div class="mb-3">
                <label for="description" class="form-label">Mô tả</label>
                <textarea class="form-control" id="description" rows="3" v-model="form.description"></textarea>
              </div>
            </div>
            <div class="modal-footer modal-footer-custom">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
              <button type="submit" class="btn btn-primary">{{ isEditMode ? 'Lưu thay đổi' : 'Tạo mới' }}</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { inject } from 'vue';
import axiosInstance from '../../../axiosConfig.js';
import { Modal } from 'bootstrap';

// State
const items = ref([]);
const form = ref({});
const isEditMode = ref(false);
const searchQuery = ref('');
const currentPage = ref(1);
const itemsPerPage = 10;
const showAlert = ref(false);
const alertMessage = ref('');
const alertType = ref('alert-success');
let itemModal = null;
const apiUrl = inject('apiUrl');

// Axios instance with auth
// const axiosInstance = axios.create({
//   baseURL: apiUrl,
//   headers: {
//     'Authorization': `Bearer ${localStorage.getItem('tokenJwt') || ''}`,
//     'Content-Type': 'application/json',
//   },
// });

// Helper Functions
const showNotification = (message, type = 'success') => {
  alertType.value = type === 'success' ? 'alert-success' : 'alert-danger';
  alertMessage.value = message;
  showAlert.value = true;
  setTimeout(() => showAlert.value = false, 3000);
};

const resetForm = () => {
    form.value = { id: null, name: '', description: '' };
};

// API Calls
const fetchData = async () => {
  try {
    const response = await axiosInstance.get('/api/news-categories');
    items.value = response.data;
  } catch (error) {
    showNotification('Không thể tải danh sách danh mục.', 'error');
  }
};

// Computed properties for search and pagination
const filteredItems = computed(() => {
  if (!searchQuery.value) return items.value;
  const query = searchQuery.value.toLowerCase();
  return items.value.filter(item => item.name.toLowerCase().includes(query));
});

const totalPages = computed(() => Math.ceil(filteredItems.value.length / itemsPerPage));

const paginatedItems = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  return filteredItems.value.slice(start, start + itemsPerPage);
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

// Modal and Form Handling
const openModal = (editMode = false, item = null) => {
  isEditMode.value = editMode;
  if (editMode && item) {
    form.value = { ...item };
  } else {
    resetForm();
  }
  itemModal.show();
};

const handleSubmit = async () => {
  try {
    if (isEditMode.value) {
      await axiosInstance.put(`/api/news-categories/${form.value.id}`, form.value);
      showNotification('Cập nhật danh mục thành công!');
    } else {
      await axiosInstance.post(`/api/news-categories`, form.value);
      showNotification('Tạo danh mục mới thành công!');
    }
    itemModal.hide();
    fetchData();
  } catch (error) {
    showNotification('Thao tác thất bại. ' + (error.response?.data?.message || ''), 'error');
  }
};

const deleteItem = async (id) => {
  if (confirm('Bạn có chắc chắn muốn xóa danh mục này?')) {
    try {
      await axiosInstance.delete(`/api/news-categories/${id}`);
      showNotification('Xóa danh mục thành công!');
      fetchData();
    } catch (error) {
      showNotification('Xóa thất bại. ' + (error.response?.data?.message || 'Danh mục có thể đang được sử dụng.'), 'error');
    }
  }
};

// Lifecycle Hooks
onMounted(() => {
  fetchData();
  itemModal = new Modal(document.getElementById('categoryModal'));
});

watch(searchQuery, () => {
  currentPage.value = 1;
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
.booking-table { font-size: 0.875rem; border-collapse: separate; border-spacing: 0; min-width: 600px; }
.booking-table thead th { background-color: #f8f9fa; color: #7f8c8d; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e5eaee; padding: 1rem; white-space: nowrap; }
.booking-table td { padding: 1.25rem 1rem; border-bottom: 1px solid #e5eaee; }
.booking-table tbody tr:last-child td { border-bottom: none; }
.booking-table tbody tr:hover { background-color: #f9fafb; }
.type-name { font-size: 1rem; font-weight: 600; }
.description-text { font-size: 0.8rem; color: #7f8c8d; }

.badge { padding: 0.4em 0.8em; font-size: 0.75rem; font-weight: 600; border-radius: 20px; letter-spacing: 0.5px; }
.badge-info { background-color: #eaf6fb; color: #3498db; }

.action-buttons { white-space: nowrap; }
.action-buttons .btn { margin: 0 2px; }

.pagination .page-link { border: none; border-radius: 8px; margin: 0 4px; color: #7f8c8d; font-weight: 600; }
.pagination .page-item.active .page-link { background-color: #3498db; color: white; }

/* Modal Styles */
.modal-custom { border-radius: 16px; border: none; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); }
.modal-header-custom { background-color: #f4f7f9; border-bottom: 1px solid #e5eaee; padding: 1.5rem; }
.modal-footer-custom { background-color: #f4f7f9; border-top: 1px solid #e5eaee; padding: 1rem 1.5rem; }

/* Alert Styles */
.custom-alert { position: fixed; top: 20px; right: 20px; z-index: 1060; min-width: 300px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); border-radius: 8px; }
.close-btn { cursor: pointer; float: right; font-size: 1.5rem; line-height: 1; }
</style>