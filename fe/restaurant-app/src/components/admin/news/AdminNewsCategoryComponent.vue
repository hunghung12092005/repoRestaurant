<template>
  <div class="staff-container">
    <div v-if="showAlert" :class="['alert', alertType, 'custom-alert']" role="alert">
      {{ alertMessage }}
      <span class="close-btn" @click="showAlert = false">×</span>
    </div>

    <div class="header-section mb-4">
      <h3 class="fw-bold">Quản lý Danh mục Tin tức</h3>
      <div class="d-flex justify-content-between align-items-center mt-3">
        <div class="search-form position-relative">
          <input
            type="text"
            class="form-control"
            v-model="searchQuery"
            placeholder="Tìm kiếm theo tên danh mục..."
          />
          <i class="bi bi-search search-icon position-absolute"></i>
        </div>
        <button class="btn btn-primary" @click="openCreateModal">
          <i class="bi bi-plus-circle me-2"></i>Thêm Danh mục
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
                <th scope="col">Tên danh mục</th>
                <th scope="col">Mô tả</th>
                <th scope="col">Hành động</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="category in paginatedCategories" :key="category.category_id">
                <td><input type="checkbox" class="form-check-input" v-model="selectedCategories" :value="category.category_id" @change="updateSelectAll" /></td>
                <td>{{ category.name }}</td>
                <td>{{ category.description || 'Không có' }}</td>
                <td>
                  <button class="btn btn-sm btn-outline-primary me-2" @click="openDetailModal(category)">
                    <i class="bi bi-eye"></i>
                  </button>
                  <button class="btn btn-sm btn-outline-warning me-2" @click="openEditModal(category)">
                    <i class="bi bi-pencil"></i>
                  </button>
                  <button class="btn btn-sm btn-outline-danger" @click="deleteCategory(category.category_id)">
                    <i class="bi bi-trash"></i>
                  </button>
                </td>
              </tr>
              <tr v-if="!paginatedCategories.length">
                <td colspan="4" class="text-center text-muted">Không tìm thấy danh mục</td>
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

    <div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="categoryModalLabel">{{ isEditMode ? 'Chỉnh sửa Danh mục' : 'Thêm Danh mục' }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="saveCategory">
              <div class="row">
                <div class="col-md-12 mb-3">
                  <label for="name" class="form-label">Tên danh mục</label>
                  <input type="text" class="form-control" v-model="form.name" required />
                </div>
              </div>
              <div class="row">
                <div class="col-md-12 mb-3">
                  <label for="description" class="form-label">Mô tả</label>
                  <textarea class="form-control" v-model="form.description" rows="4"></textarea>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="submit" class="btn btn-primary">{{ isEditMode ? 'Cập nhật' : 'Thêm mới' }}</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="detailModalLabel">Chi tiết Danh mục</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
          </div>
          <div class="modal-body">
            <div class="row">
              <div class="col-md-6 mb-3">
                <strong>Tên danh mục:</strong> {{ selectedCategory?.name || 'N/A' }}
              </div>
            </div>
            <div class="row">
              <div class="col-md-12 mb-3">
                <strong>Mô tả:</strong> {{ selectedCategory?.description || 'Không có' }}
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
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import axios from 'axios';
import { inject } from 'vue';
 import { Modal } from 'bootstrap';


const apiUrl = inject('apiUrl');
const categories = ref([]);
const form = ref({
  category_id: null,
  name: '',
  description: '',
});
const isEditMode = ref(false);
const selectedCategories = ref([]);
const selectAll = ref(false);
const searchQuery = ref('');
const currentPage = ref(1);
const itemsPerPage = 10;
const showAlert = ref(false);
const alertMessage = ref('');
const alertType = ref('alert-success');
const selectedCategory = ref(null); // Thêm khai báo selectedCategory
let categoryModal = null;
let detailModal = null;

const showNotification = (message, type = 'success') => {
  alertType.value = type === 'success' ? 'alert-success' : 'alert-danger';
  alertMessage.value = message;
  showAlert.value = true;
  setTimeout(() => (showAlert.value = false), 3000);
};

const toggleSelectAll = () => {
  selectedCategories.value = selectAll.value
    ? paginatedCategories.value.map((cat) => cat.category_id)
    : [];
};

const updateSelectAll = () => {
  selectAll.value =
    paginatedCategories.value.length > 0 &&
    selectedCategories.value.length === paginatedCategories.value.length &&
    paginatedCategories.value.every((cat) => selectedCategories.value.includes(cat.category_id));
};

const fetchCategories = async () => {
  try {
    const response = await axios.get(`${apiUrl}/api/news-categories`, {
      params: { q: searchQuery.value },
    });
    categories.value = response.data;
    selectedCategories.value = [];
    selectAll.value = false;
  } catch (error) {
    showNotification('Không thể tải danh sách danh mục.', 'error');
  }
};

const filteredCategories = computed(() => {
  if (!searchQuery.value) return categories.value;
  const query = searchQuery.value.toLowerCase();
  return categories.value.filter(
    (category) =>
      category.name.toLowerCase().includes(query) ||
      (category.description && category.description.toLowerCase().includes(query))
  );
});

const totalPages = computed(() => Math.ceil(filteredCategories.value.length / itemsPerPage));

const paginatedCategories = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  return filteredCategories.value.slice(start, start + itemsPerPage);
});

const openCreateModal = () => {
  isEditMode.value = false;
  form.value = { category_id: null, name: '', description: '' };
  categoryModal = new Modal(document.getElementById('categoryModal'));
  categoryModal.show();
};

const openEditModal = (category) => {
  isEditMode.value = true;
  form.value = { ...category };
  categoryModal = new Modal(document.getElementById('categoryModal'));
  categoryModal.show();
};

const openDetailModal = (category) => {
  selectedCategory.value = category;
  detailModal = new Modal(document.getElementById('detailModal'));
  detailModal.show();
};

const closeModal = () => {
  if (categoryModal) categoryModal.hide();
  if (detailModal) detailModal.hide();
};

const saveCategory = async () => {
  try {
    if (isEditMode.value) {
      await axios.put(`${apiUrl}/api/news-categories/${form.value.category_id}`, form.value);
      showNotification('Cập nhật danh mục thành công!');
    } else {
      await axios.post(`${apiUrl}/api/news-categories`, form.value);
      showNotification('Thêm danh mục thành công!');
    }
    fetchCategories();
    closeModal();
  } catch (error) {
    showNotification('Lưu danh mục thất bại.', 'error');
  }
};

const deleteCategory = async (id) => {
  if (!confirm('Bạn có chắc muốn xóa danh mục này?')) return;
  try {
    await axios.delete(`${apiUrl}/api/news-categories/${id}`);
    selectedCategories.value = selectedCategories.value.filter((catId) => catId !== id);
    await fetchCategories();
    if (paginatedCategories.value.length === 0 && currentPage.value > 1) {
      currentPage.value--;
    }
    showNotification('Xóa danh mục thành công!');
  } catch (error) {
    showNotification('Xóa danh mục thất bại.', 'error');
  }
};

onMounted(() => {
  fetchCategories();
});

watch(searchQuery, () => {
  currentPage.value = 1;
  fetchCategories();
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
.table td:last-child {
  text-align: center;
  min-width: 80px;
}
.table td:nth-child(2) {
  min-width: 200px;
}
.table td:nth-child(3) {
  min-width: 250px;
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
  min-width: 600px;
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
    min-width: 500px;
  }
}
</style>