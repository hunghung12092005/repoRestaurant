<template>
  <div class="page-container">
    <!-- Thông báo -->
    <div v-if="showAlert" :class="['alert', alertType, 'custom-alert']" role="alert">
      {{ alertMessage }}
      <span class="close-btn" @click="showAlert = false">×</span>
    </div>

    <!-- Tiêu đề trang -->
    <div class="page-header mb-4">
      <h1 class="page-title">Quản lý Bình luận</h1>
      <p class="page-subtitle">Duyệt, ẩn, và xóa các bình luận từ người dùng.</p>
    </div>

    <!-- Bộ lọc và tìm kiếm -->
    <div class="card filter-card mb-4">
      <div class="card-body d-flex justify-content-between align-items-center">
        <div class="col-md-5">
          <input
            v-model="searchQuery"
            type="text"
            class="form-control"
            placeholder="Tìm theo nội dung, người dùng, bài viết..."
          />
        </div>
      </div>
    </div>

    <!-- Bảng danh sách bình luận -->
    <div class="table-container">
      <table class="table booking-table align-middle">
        <thead>
          <tr>
            <th style="width: 45%;">Bình Luận</th>
            <th style="width: 25%;">Bài Viết Liên Quan</th>
            <th class="text-center" style="width: 15%;">Trạng Thái</th>
            <th class="text-center" style="width: 15%;">Hành Động</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in paginatedItems" :key="item.id">
            <td>
              <div class="comment-content">{{ item.content }}</div>
              <p class="description-text mb-0 mt-2">
                bởi <strong>{{ item.user?.name || 'Người dùng ẩn danh' }}</strong>
                vào lúc {{ formatDate(item.created_at) }}
              </p>
            </td>
            <td>
              <a href="#" @click.prevent class="related-post-link">{{ item.news?.title || 'Bài viết không tồn tại' }}</a>
            </td>
            <td class="text-center">
              <span class="badge" :class="item.is_visible ? 'badge-success' : 'badge-secondary'">
                {{ item.is_visible ? 'Hiển thị' : 'Đã ẩn' }}
              </span>
            </td>
            <td class="text-center action-buttons">
              <button 
                class="btn btn-sm" 
                :class="item.is_visible ? 'btn-outline-warning' : 'btn-outline-success'"
                :title="item.is_visible ? 'Ẩn bình luận' : 'Hiện bình luận'"
                @click="toggleVisibility(item)"
              >
                <i :class="item.is_visible ? 'bi bi-eye-slash-fill' : 'bi bi-eye-fill'"></i>
              </button>
              <button class="btn btn-sm btn-outline-danger" title="Xóa vĩnh viễn" @click="deleteItem(item.id)">
                <i class="bi bi-trash-fill"></i>
              </button>
            </td>
          </tr>
          <tr v-if="!paginatedItems.length">
            <td colspan="4" class="text-center py-5">Không tìm thấy bình luận nào.</td>
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
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { inject } from 'vue';
import axios from 'axios';

// State
const items = ref([]); // Comments
const searchQuery = ref('');
const currentPage = ref(1);
const itemsPerPage = 10;
const showAlert = ref(false);
const alertMessage = ref('');
const alertType = ref('alert-success');
const apiUrl = inject('apiUrl');

// Axios instance
const axiosInstance = axios.create({ baseURL: apiUrl, headers: { 'Authorization': `Bearer ${localStorage.getItem('tokenJwt') || ''}` } });

// Helpers
const showNotification = (message, type = 'success') => {
  alertMessage.value = message;
  alertType.value = type === 'success' ? 'alert-success' : 'alert-danger';
  showAlert.value = true;
  setTimeout(() => showAlert.value = false, 3000);
};
const formatDate = (dateString) => dateString ? new Date(dateString).toLocaleString('vi-VN') : 'N/A';

// API Calls
const fetchData = async () => {
  try {
    const response = await axiosInstance.get('/api/news-comments');
    items.value = response.data.data || response.data; // Handle both paginated and non-paginated responses
  } catch (error) {
    showNotification('Không thể tải danh sách bình luận.', 'error');
  }
};

const deleteItem = async (id) => {
  if (confirm('Bạn có chắc chắn muốn xóa bình luận này?')) {
    try {
      await axiosInstance.delete(`/api/comments/${id}`);
      showNotification('Xóa bình luận thành công!');
      items.value = items.value.filter(item => item.id !== id);
    } catch (error) {
      showNotification('Xóa thất bại.', 'error');
    }
  }
};

const toggleVisibility = async (commentItem) => {
  try {
    const response = await axiosInstance.patch(`/api/comments/${commentItem.id}/toggle-visibility`);
    const index = items.value.findIndex(item => item.id === commentItem.id);
    if (index !== -1) {
      items.value[index].is_visible = response.data.is_visible;
    }
    showNotification('Cập nhật trạng thái thành công!', 'success');
  } catch (error) {
    console.error("Lỗi khi cập nhật trạng thái bình luận:", error);
    showNotification('Cập nhật trạng thái thất bại.', 'error');
  }
};

// Computed for search and pagination
const filteredItems = computed(() => {
  if (!searchQuery.value) return items.value;
  const query = searchQuery.value.toLowerCase();
  return items.value.filter(item =>
    item.content.toLowerCase().includes(query) ||
    (item.user?.name && item.user.name.toLowerCase().includes(query)) ||
    (item.news?.title && item.news.title.toLowerCase().includes(query))
  );
});

const totalPages = computed(() => Math.ceil(filteredItems.value.length / itemsPerPage));
const paginatedItems = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  return filteredItems.value.slice(start, start + itemsPerPage);
});

// ** BỔ SUNG NHỎ ĐỂ HỖ TRỢ UI PHÂN TRANG **
const pageRange = computed(() => {
  const maxPages = 5;
  let start = Math.max(1, currentPage.value - Math.floor(maxPages / 2));
  let end = Math.min(totalPages.value, start + maxPages - 1);
  if (end - start + 1 < maxPages) {
    start = Math.max(1, end - maxPages + 1);
  }
  return Array.from({ length: end - start + 1 }, (_, i) => start + i);
});

// Lifecycle
onMounted(fetchData);
watch(searchQuery, () => { currentPage.value = 1; });
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
.booking-table { font-size: 0.875rem; border-collapse: separate; border-spacing: 0; min-width: 800px; }
.booking-table thead th { background-color: #f8f9fa; color: #7f8c8d; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e5eaee; padding: 1rem; white-space: nowrap; }
.booking-table td { padding: 1.25rem 1rem; border-bottom: 1px solid #e5eaee; }
.booking-table tbody tr:last-child td { border-bottom: none; }
.booking-table tbody tr:hover { background-color: #f9fafb; }

.comment-content {
  font-size: 0.9rem;
  white-space: normal; /* Cho phép xuống dòng */
  word-break: break-word; /* Ngắt từ nếu cần */
  max-width: 400px;
}
.description-text { font-size: 0.8rem; color: #7f8c8d; }
.related-post-link {
  font-weight: 500;
  color: #34495e;
  text-decoration: none;
}
.related-post-link:hover {
  color: #3498db;
  text-decoration: underline;
}

.badge { padding: 0.4em 0.8em; font-size: 0.75rem; font-weight: 600; border-radius: 20px; letter-spacing: 0.5px; }
.badge-secondary { background-color: #f3f4f6; color: #7f8c8d; }
.badge-success { background-color: #e6f9f0; color: #2ecc71; }

.action-buttons { white-space: nowrap; }
.action-buttons .btn { margin: 0 2px; }

.pagination .page-link { border: none; border-radius: 8px; margin: 0 4px; color: #7f8c8d; font-weight: 600; }
.pagination .page-item.active .page-link { background-color: #3498db; color: white; }

/* Alert Styles */
.custom-alert { position: fixed; top: 20px; right: 20px; z-index: 1060; min-width: 300px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); border-radius: 8px; }
.close-btn { cursor: pointer; float: right; font-size: 1.5rem; line-height: 1; }
</style>