<template>
  <div class="staff-container">
    <!-- Notification Alert -->
    <div v-if="showAlert" :class="['alert', alertType, 'custom-alert']" role="alert">
      {{ alertMessage }}
      <span class="close-btn" @click="showAlert = false">×</span>
    </div>

    <!-- Header Section -->
    <div class="header-section mb-4">
      <h3 class="fw-bold">Quản lý Bình luận</h3>
      <div class="d-flex justify-content-between align-items-center mt-3">
        <div class="search-form position-relative">
          <input type="text" class="form-control" v-model="searchQuery"
            placeholder="Tìm theo nội dung, người dùng..." />
          <i class="bi bi-search search-icon position-absolute"></i>
        </div>
      </div>
    </div>

    <!-- Table Section -->
    <div class="card shadow-sm rounded-3">
      <div class="card-body p-0">
        <div class="table-responsive custom-scroll">
          <table class="table table-hover table-bordered mb-0">
            <thead class="table-light">
              <tr>
                <th scope="col">Nội dung</th>
                <th scope="col">Người bình luận</th>
                <th scope="col">Bài viết</th>
                <th scope="col">Trạng thái</th>
                <th scope="col">Ngày tạo</th>
                <th scope="col">Hành động</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in paginatedItems" :key="item.id">
                <td style="white-space: normal; min-width: 300px;">{{ item.content }}</td>
                <td>{{ item.user?.name || 'N/A' }}</td>
                <td>{{ item.news?.title || 'N/A' }}</td>
                <!-- [THAY ĐỔI] Hiển thị trạng thái bằng badge -->
                <td>
                   <span class="badge" :class="item.is_visible ? 'bg-success' : 'bg-secondary'">
                    {{ item.is_visible ? 'Hiển thị' : 'Đã ẩn' }}
                  </span>
                </td>
                <td>{{ formatDate(item.created_at) }}</td>
                <!-- [THAY ĐỔI] Thêm nút Ẩn/Hiện -->
                <td class="d-flex justify-content-center gap-2">
                  <button 
                    class="btn btn-sm" 
                    :class="item.is_visible ? 'btn-outline-warning' : 'btn-outline-success'"
                    @click="toggleVisibility(item)"
                    >
                    <i :class="item.is_visible ? 'bi-eye-slash' : 'bi-eye'"></i>
                    {{ item.is_visible ? 'Ẩn' : 'Hiện' }}
                  </button>

                  <button class="btn btn-sm btn-outline-danger" @click="deleteItem(item.id)">
                    <i class="bi bi-trash"></i> Xóa
                  </button>
                </td>
              </tr>
              <tr v-if="!paginatedItems.length">
                <!-- Cập nhật colspan -->
                <td colspan="6" class="text-center text-muted">Không tìm thấy bình luận</td>
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
const formatDate = (dateString) => dateString ? new Date(dateString).toLocaleDateString('vi-VN') : 'N/A';

// API Calls
const fetchData = async () => {
  try {
    const response = await axiosInstance.get('/api/news-comments');
    items.value = response.data.data;
  } catch (error) {
    showNotification('Không thể tải danh sách bình luận.', 'error');
  }
};

const deleteItem = async (id) => {
  if (confirm('Bạn có chắc chắn muốn xóa bình luận này?')) {
    try {
      await axiosInstance.delete(`/api/comments/${id}`);
      showNotification('Xóa bình luận thành công!');
      // Xóa khỏi danh sách thay vì fetch lại toàn bộ
      items.value = items.value.filter(item => item.id !== id);
    } catch (error) {
      showNotification('Xóa thất bại.', 'error');
    }
  }
};

/**
 * [HÀM MỚI] Thay đổi trạng thái hiển thị của bình luận
 * @param {object} commentItem - Đối tượng bình luận cần thay đổi
 */
const toggleVisibility = async (commentItem) => {
  try {
    // Gọi API để thay đổi trạng thái ở backend
    const response = await axiosInstance.patch(`/api/comments/${commentItem.id}/toggle-visibility`);
    
    // Cập nhật trạng thái của item trong danh sách `items`
    const index = items.value.findIndex(item => item.id === commentItem.id);
    if (index !== -1) {
      // Dùng dữ liệu trả về từ server để đảm bảo đồng bộ
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
    (item.user?.name && item.user.name.toLowerCase().includes(query))
  );
});

const totalPages = computed(() => Math.ceil(filteredItems.value.length / itemsPerPage));
const paginatedItems = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  return filteredItems.value.slice(start, start + itemsPerPage);
});


// Lifecycle
onMounted(fetchData);
watch(searchQuery, () => { currentPage.value = 1; });
</script>

<style scoped>
/* Copy toàn bộ CSS từ component mẫu của bạn vào đây */
/* Thêm style cho badge nếu cần */
.badge {
  padding: 0.5em 0.75em;
  font-size: 0.8rem;
  font-weight: 600;
}
.btn-outline-warning {
  color: #ffc107;
  border-color: #ffc107;
}
.btn-outline-warning:hover {
  background-color: #ffc107;
  color: #000;
}
.btn-outline-success {
  color: #198754;
  border-color: #198754;
}
.btn-outline-success:hover {
  background-color: #198754;
  color: #fff;
}
.staff-container { padding: 20px; }
.header-section .d-flex { gap: 15px; }
.search-form input { padding-right: 40px; }
.search-form .search-icon { top: 50%; right: 15px; transform: translateY(-50%); color: #666; }
.table th { font-weight: 600; white-space: nowrap; vertical-align: middle;}
.table td { vertical-align: middle; }
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
</style>