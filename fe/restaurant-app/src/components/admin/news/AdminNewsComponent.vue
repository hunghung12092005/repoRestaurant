<template>
  <div class="staff-container">
    <div v-if="showAlert" :class="['alert', alertType, 'custom-alert']" role="alert">
      {{ alertMessage }}
      <span class="close-btn" @click="showAlert = false">×</span>
    </div>

    <div class="header-section mb-4">
      <h3 class="fw-bold">Quản lý Tin tức</h3>
      <div class="d-flex justify-content-between align-items-center mt-3">
        <div class="search-form position-relative">
          <input
            type="text"
            class="form-control"
            v-model="searchQuery"
            placeholder="Tìm kiếm theo tiêu đề tin tức..."
          />
          <i class="bi bi-search search-icon position-absolute"></i>
        </div>
        <button class="btn btn-primary" @click="openCreateModal">
          <i class="bi bi-plus-circle me-2"></i>Thêm Tin tức
        </button>
      </div>
    </div>

    <div class="card shadow-sm rounded-3">
      <div class="card-body p-0">
        <div class="table-responsive custom-scroll">
          <table class="table table-hover table-bordered mb-0">
            <thead class="table-light">
              <tr>
                <th scope="col"><input type="checkbox" class="form-check-input" v-model="selectAll" @change="toggleSelectAll" /></th>
                <th scope="col">Tiêu đề</th>
                <th scope="col">Danh mục</th>
                <th scope="col">Tác giả</th>
                <th scope="col">Ngày đăng</th>
                <th scope="col">Trạng thái</th>
                <th scope="col">Ghim</th>
                <th scope="col">Hành động</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="news in paginatedNews" :key="news.news_id">
                <td><input type="checkbox" class="form-check-input" v-model="selectedNews" :value="news.news_id" @change="updateSelectAll" /></td>
                <td>{{ news.title }}</td>
                <td>{{ news.category ? news.category.name : 'N/A' }}</td>
                <td>{{ news.author ? news.author.name : 'N/A' }}</td>
                <td>{{ formatDate(news.publish_date) }}</td>
                <td>{{ news.status ? 'Hoạt động' : 'Không hoạt động' }}</td>
                <td>{{ news.is_pinned ? 'Có' : 'Không' }}</td>
                <td>
                  <button class="btn btn-sm btn-outline-primary me-2" @click="openDetailModal(news)">
                    <i class="bi bi-eye"></i>
                  </button>
                  <button class="btn btn-sm btn-outline-warning me-2" @click="openEditModal(news)">
                    <i class="bi bi-pencil"></i>
                  </button>
                  <button class="btn btn-sm btn-outline-danger" @click="deleteNews(news.news_id)">
                    <i class="bi bi-trash"></i>
                  </button>
                </td>
              </tr>
              <tr v-if="!paginatedNews.length">
                <td colspan="8" class="text-center text-muted">Không tìm thấy tin tức</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="pagination-controls d-flex justify-content-center align-items-center mt-4">
      <button
        class="btn btn-outline-secondary me-2 shadow-sm rounded-pill"
        :disabled="currentPage === 1"
        @click="currentPage--"
      >
        Trước
      </button>
      <span class="mx-3 font-weight-bold">Trang {{ currentPage }} / {{ totalPages }}</span>
      <button
        class="btn btn-outline-secondary shadow-sm rounded-pill"
        :disabled="currentPage === totalPages || totalPages === 0"
        @click="currentPage++"
      >
        Tiếp
      </button>
    </div>

    <div class="modal fade" id="newsModal" tabindex="-1" aria-labelledby="newsModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-custom-wide">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="newsModalLabel">{{ isEditMode ? 'Chỉnh sửa Tin tức' : 'Thêm Tin tức' }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
          </div>
          <div class="modal-body">
            <NewsCreate v-if="!isEditMode" :categories="categories" @save="saveNews" @close="closeModal" />
            <NewsEdit v-else :news="form" :categories="categories" @save="saveNews" @close="closeModal" />
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="detailModalLabel">Chi tiết Tin tức</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
          </div>
          <div class="modal-body">
            <div class="row">
              <div class="col-md-6 mb-3">
                <strong>Tiêu đề:</strong> {{ selectedNews.title }}
              </div>
              <div class="col-md-6 mb-3">
                <strong>Danh mục:</strong> {{ selectedNews.category ? selectedNews.category.name : 'N/A' }}
              </div>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <strong>Tác giả:</strong> {{ selectedNews.author ? selectedNews.author.name : 'N/A' }}
              </div>
              <div class="col-md-6 mb-3">
                <strong>Ngày đăng:</strong> {{ formatDate(selectedNews.publish_date) }}
              </div>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <strong>Trạng thái:</strong> {{ selectedNews.status ? 'Hoạt động' : 'Không hoạt động' }}
              </div>
              <div class="col-md-6 mb-3">
                <strong>Ghim:</strong> {{ selectedNews.is_pinned ? 'Có' : 'Không' }}
              </div>
            </div>
            <div class="row">
              <div class="col-md-12 mb-3">
                <strong>Tóm tắt:</strong> {{ selectedNews.summary || 'Không có' }}
              </div>
            </div>
            <div class="row">
              <div class="col-md-12 mb-3">
                <strong>Nội dung:</strong>
                <div v-html="selectedNews.content"></div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12 mb-3">
                <strong>Thumbnail:</strong>
                <img v-if="selectedNews.thumbnail" :src="selectedNews.thumbnail" alt="Thumbnail" style="max-width: 200px;" />
                <span v-else>Không có</span>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12 mb-3">
                <strong>Tags:</strong> {{ selectedNews.tags || 'Không có' }}
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
import { ref, computed, onMounted, watch } from 'vue';
import axios from 'axios';
import { inject } from 'vue';
import { Modal } from 'bootstrap';
import NewsCreate from './NewsCreate.vue';
import NewsEdit from './NewsEdit.vue';

const apiUrl = inject('apiUrl');
const newsList = ref([]);
const categories = ref([]);
const form = ref({
  news_id: null,
  title: '',
  summary: '',
  content: '',
  thumbnail: '',
  category_id: '',
  status: 1,
  tags: '',
  is_pinned: false,
});
const isEditMode = ref(false);
const selectedNews = ref({});
const selectAll = ref(false);
const searchQuery = ref('');
const currentPage = ref(1);
const itemsPerPage = 10;
const showAlert = ref(false);
const alertMessage = ref('');
const alertType = ref('alert-success');
let newsModal = null;
let detailModal = null;

const showNotification = (message, type = 'success') => {
  alertType.value = type === 'success' ? 'alert-success' : 'alert-danger';
  alertMessage.value = message;
  showAlert.value = true;
  setTimeout(() => (showAlert.value = false), 3000);
};

const toggleSelectAll = () => {
  selectedNews.value = selectAll.value
    ? paginatedNews.value.map((news) => news.news_id)
    : [];
};

const updateSelectAll = () => {
  selectAll.value =
    paginatedNews.value.length > 0 &&
    selectedNews.value.length === paginatedNews.value.length &&
    paginatedNews.value.every((news) => selectedNews.value.includes(news.news_id));
};

const fetchNews = async () => {
  try {
    const response = await axios.get(`${apiUrl}/api/news`, {
      params: { q: searchQuery.value },
    });
    newsList.value = response.data;
    selectedNews.value = [];
    selectAll.value = false;
  } catch (error) {
    showNotification('Không thể tải danh sách tin tức.', 'error');
  }
};

const fetchCategories = async () => {
  try {
    const response = await axios.get(`${apiUrl}/api/news-categories`);
    categories.value = response.data;
  } catch (error) {
    showNotification('Không thể tải danh sách danh mục.', 'error');
  }
};

const filteredNews = computed(() => {
  if (!searchQuery.value) return newsList.value;
  const query = searchQuery.value.toLowerCase();
  return newsList.value.filter(
    (news) =>
      (news.title && news.title.toLowerCase().includes(query)) ||
      (news.summary && news.summary.toLowerCase().includes(query)) ||
      (news.content && news.content.toLowerCase().includes(query))
  );
});

const totalPages = computed(() => Math.ceil(filteredNews.value.length / itemsPerPage));

const paginatedNews = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  return filteredNews.value.slice(start, start + itemsPerPage);
});

const openCreateModal = () => {
  isEditMode.value = false;
  form.value = { news_id: null, title: '', summary: '', content: '', thumbnail: '', category_id: '', status: 1, tags: '', is_pinned: false };
  newsModal = new Modal(document.getElementById('newsModal'));
  newsModal.show();
};

const openEditModal = (news) => {
  isEditMode.value = true;
  form.value = { ...news };
  newsModal = new Modal(document.getElementById('newsModal'));
  newsModal.show();
};

const openDetailModal = (news) => {
  selectedNews.value = news;
  detailModal = new Modal(document.getElementById('detailModal'));
  detailModal.show();
};

const closeModal = () => {
  if (newsModal) newsModal.hide();
  if (detailModal) detailModal.hide();
};

const saveNews = async (newsData) => {
  try {
    if (isEditMode.value) {
      await axios.put(`${apiUrl}/api/news/${newsData.news_id}`, newsData);
      showNotification('Cập nhật tin tức thành công!');
    } else {
      await axios.post(`${apiUrl}/api/news`, newsData);
      showNotification('Thêm tin tức thành công!');
    }
    fetchNews();
    closeModal();
  } catch (error) {
    showNotification('Lưu tin tức thất bại.', 'error');
  }
};

const deleteNews = async (id) => {
  if (!confirm('Bạn có chắc muốn xóa tin tức này?')) return;
  try {
    await axios.delete(`${apiUrl}/api/news/${id}`);
    selectedNews.value = selectedNews.value.filter((newsId) => newsId !== id);
    await fetchNews();
    if (paginatedNews.value.length === 0 && currentPage.value > 1) {
      currentPage.value--;
    }
    showNotification('Xóa tin tức thành công!');
  } catch (error) {
    showNotification('Xóa tin tức thất bại.', 'error');
  }
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('vi-VN');
};

onMounted(() => {
  fetchNews();
  fetchCategories();
});

watch(searchQuery, () => {
  currentPage.value = 1;
  fetchNews();
});
</script>

<style scoped>
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
  cursor: pointer;
  font-size: 1rem;
}
.search-form .search-icon:hover {
  color: #16B978;
}
.search-form input:focus {
  border-color: #16B978;
  outline: none;
  box-shadow: 0 0 5px rgba(22, 185, 120, 0.3);
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
  overflow: hidden;
  text-overflow: ellipsis;
}
.table td:first-child,
.table td:last-child,
.table td:nth-child(5),
.table td:nth-child(6),
.table td:nth-child(7) {
  text-align: center;
  min-width: 80px;
}
.table td:nth-child(2) {
  min-width: 200px;
}
.table td:nth-child(3),
.table td:nth-child(4) {
  min-height: 150px;
}
.table-hover tbody tr:hover {
  background-color: #f1f3f5;
}
.btn-primary {
  background-color: #16B978;
  border-color: #16B978;
  transition: background-color 0.3s ease;
}
.btn-primary:hover {
  background-color: #13a567;
  border-color: #13a567;
}
.btn-outline-primary,
.btn-outline-warning,
.btn-outline-danger {
  padding: 4px 8px;
  font-size: 0.9rem;
}
.btn-outline-primary i,
.btn-outline-warning i,
.btn-outline-danger i {
  font-size: 1rem;
}
.pagination-controls .btn {
  padding: 8px 20px;
  font-size: 0.85rem;
  border-width: 1px;
  transition: all 0.3s ease;
}
.pagination-controls .btn:hover {
  background-color: #34495e;
  color: #fff;
  border-color: #34495e;
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
  -webkit-overflow-scrolling: touch;
}
.custom-scroll::-webkit-scrollbar {
  height: 10px;
}
.custom-scroll::-webkit-scrollbar-track {
  background: #f1f3f5;
  border-radius: 6px;
}
.custom-scroll::-webkit-scrollbar-thumb {
  background: #16B978;
  border-radius: 6px;
}
.custom-scroll::-webkit-scrollbar-thumb:hover {
  background: #13a567;
}
.custom-scroll {
  scrollbar-width: thin;
  scrollbar-color: #16B978 #f1f3f5;
}
table {
  min-width: 800px;
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
  line-height: 1;
  color: inherit;
  opacity: 0.7;
  transition: opacity 0.2s;
}
.custom-alert .close-btn:hover {
  opacity: 1;
}
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
@keyframes fadeOut {
  from {
    opacity: 1;
    transform: translateY(0);
  }
  to {
    opacity: 0;
    transform: translateY(-10px);
  }
}
.modal-custom-wide {
  max-width: 1000px; /* Tùy chỉnh chiều rộng lớn hơn modal-lg */
  width: 90%; /* Đảm bảo responsive */
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .staff-container {
    padding: 10px;
  }
  .table th,
  .table td {
    padding: 8px;
    font-size: 0.75rem;
  }
  .header-section .d-flex {
    flex-direction: column;
    gap: 10px;
  }
  .search-form input {
    width: 100%;
    max-width: 220px;
  }
  .btn {
    width: 100%;
  }
  .pagination-controls .btn {
    padding: 6px 15px;
    font-size: 0.75rem;
  }
  .pagination-controls span {
    font-size: 0.8rem;
  }
  .custom-alert {
    min-width: 250px;
    right: 10px;
    top: 10px;
  }
  table {
    min-width: 600px;
  }
  .modal-custom-wide {
    width: 95%; /* Giảm chiều rộng trên màn hình nhỏ */
    max-width: 95%;
  }
}
</style>