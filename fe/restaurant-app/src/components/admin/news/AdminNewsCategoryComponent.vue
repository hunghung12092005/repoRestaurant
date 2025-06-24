<template>
  <div class="staff-container">
    <!-- Notification Alert -->
    <div v-if="showAlert" :class="['alert', alertType, 'custom-alert']" role="alert">
      {{ alertMessage }}
      <span class="close-btn" @click="showAlert = false">×</span>
    </div>

    <!-- Header Section -->
    <div class="header-section mb-4">
      <h3 class="fw-bold">Quản lý Danh mục Tin tức</h3>
      <div class="d-flex justify-content-between align-items-center mt-3">
        <div class="search-form position-relative">
          <input type="text" class="form-control" v-model="searchQuery" placeholder="Tìm kiếm theo tên danh mục..." />
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
                <th scope="col">ID</th>
                <th scope="col">Tên danh mục</th>
                <th scope="col">Mô tả</th>
                <th scope="col">Hành động</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="category in paginatedItems" :key="category.id">
                <td>{{ category.id }}</td>
                <td>{{ category.name }}</td>
                <td>{{ category.description || 'N/A' }}</td>
                <td>
                  <button class="btn btn-sm btn-outline-warning me-2" @click="openModal(true, category)">
                    <i class="bi bi-pencil-square"></i> Sửa
                  </button>
                  <button class="btn btn-sm btn-outline-danger" @click="deleteItem(category.id)">
                    <i class="bi bi-trash"></i> Xóa
                  </button>
                </td>
              </tr>
              <tr v-if="!paginatedItems.length">
                <td colspan="4" class="text-center text-muted">Không tìm thấy danh mục</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="totalPages > 1" class="pagination-controls d-flex justify-content-center align-items-center mt-4">
      <button class="btn btn-outline-secondary me-2 shadow-sm rounded-pill" :disabled="currentPage === 1" @click="currentPage--">
        Trước
      </button>
      <span class="mx-3 font-weight-bold">Trang {{ currentPage }} / {{ totalPages }}</span>
      <button class="btn btn-outline-secondary shadow-sm rounded-pill" :disabled="currentPage === totalPages" @click="currentPage++">
        Tiếp
      </button>
    </div>

    <!-- Create/Edit Modal -->
    <div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="categoryModalLabel">{{ isEditMode ? 'Chỉnh sửa Danh mục' : 'Tạo mới Danh mục' }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
          </div>
          <form @submit.prevent="handleSubmit">
            <div class="modal-body">
              <div class="mb-3">
                <label for="name" class="form-label">Tên danh mục</label>
                <input type="text" class="form-control" id="name" v-model="form.name" required>
              </div>
              <div class="mb-3">
                <label for="description" class="form-label">Mô tả</label>
                <textarea class="form-control" id="description" v-model="form.description"></textarea>
              </div>
            </div>
            <div class="modal-footer">
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
import axios from 'axios';
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
const axiosInstance = axios.create({
  baseURL: apiUrl,
  headers: {
    'Authorization': `Bearer ${localStorage.getItem('tokenJwt') || ''}`,
    'Content-Type': 'application/json',
  },
});

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
      showNotification('Xóa thất bại.', 'error');
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
/* Copy toàn bộ CSS từ component mẫu của bạn vào đây */
.staff-container { padding: 20px; }
.header-section .d-flex { gap: 15px; }
.search-form input { padding-right: 40px; }
.search-form .search-icon { top: 50%; right: 15px; transform: translateY(-50%); color: #666; }
.table th { font-weight: 600; white-space: nowrap; }
.table td { vertical-align: middle; }
.table td:last-child { text-align: center; }
.btn-outline-danger { color: #dc3545; border-color: #dc3545; }
.btn-outline-danger:hover { background-color: #dc3545; color: white; }
.pagination-controls .btn { padding: 8px 20px; font-size: 0.85rem; }
.pagination-controls .btn:disabled { opacity: 0.4; cursor: not-allowed; }
.custom-scroll::-webkit-scrollbar { height: 8px; }
.custom-scroll::-webkit-scrollbar-track { background: #f1f3f5; }
.custom-scroll::-webkit-scrollbar-thumb { background: #16B978; border-radius: 4px; }
.custom-alert { position: fixed; top: 20px; right: 20px; z-index: 1050; min-width: 300px; animation: fadeIn 0.3s, fadeOut 0.3s 2.7s forwards; }
.custom-alert.alert-success { background-color: #d4edda; color: #155724; }
.custom-alert.alert-danger { background-color: #f8d7da; color: #721c24; }
.custom-alert .close-btn { cursor: pointer; font-size: 1.2rem; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
@keyframes fadeOut { from { opacity: 1; transform: translateY(0); } to { opacity: 0; transform: translateY(-10px); } }
/* Thêm các style khác nếu cần */
</style>