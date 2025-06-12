<template>
  <div class="staff-container">
    <div v-if="showAlert" :class="['alert', alertType, 'custom-alert']" role="alert">
      {{ alertMessage }}
      <span class="close-btn" @click="showAlert = false">×</span>
    </div>

    <div class="header-section mb-4">
      <h3 class="fw-bold">Quản lý Bình luận Tin tức</h3>
      <div class="d-flex justify-content-between align-items-center mt-3">
        <div class="search-form position-relative">
          <input
            type="text"
            class="form-control"
            v-model="searchQuery"
            placeholder="Tìm kiếm theo nội dung bình luận..."
          />
          <i class="bi bi-search search-icon position-absolute"></i>
        </div>
        <button class="btn btn-primary" @click="openCreateModal" disabled>
          <i class="bi bi-plus-circle me-2"></i>Thêm Bình luận (Tạm khóa)
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
                <th scope="col">Người dùng</th>
                <th scope="col">Nội dung</th>
                <th scope="col">Tin tức</th>
                <th scope="col">Hành động</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="comment in paginatedComments" :key="comment.comment_id">
                <td><input type="checkbox" class="form-check-input" v-model="selectedComments" :value="comment.comment_id" @change="updateSelectAll" /></td>
                <td>{{ comment.user?.name || 'N/A' }}</td>
                <td>{{ comment.comment_text || 'Không có' }}</td>
                <td>{{ comment.news?.title || 'N/A' }}</td>
                <td>
                  <button class="btn btn-sm btn-outline-primary me-2" @click="openDetailModal(comment)">
                    <i class="bi bi-eye"></i>
                  </button>
                  <button class="btn btn-sm btn-outline-danger" @click="deleteComment(comment.comment_id)">
                    <i class="bi bi-trash"></i>
                  </button>
                </td>
              </tr>
              <tr v-if="!paginatedComments.length">
                <td colspan="5" class="text-center text-muted">Không tìm thấy bình luận</td>
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

    <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="detailModalLabel">Chi tiết Bình luận</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
          </div>
          <div class="modal-body">
            <div class="row">
              <div class="col-md-6 mb-3">
                <strong>Người dùng:</strong> {{ selectedComment?.user?.name || 'N/A' }}
              </div>
              <div class="col-md-6 mb-3">
                <strong>Tin tức:</strong> {{ selectedComment?.news?.title || 'N/A' }}
              </div>
            </div>
            <div class="row">
              <div class="col-md-12 mb-3">
                <strong>Nội dung:</strong> {{ selectedComment?.comment_text || 'Không có' }}
              </div>
            </div>
            <div class="row">
              <div class="col-md-12 mb-3">
                <strong>Ngày tạo:</strong> {{ selectedComment?.comment_date || 'N/A' }}
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
const comments = ref([]);
const selectedComments = ref([]);
const selectAll = ref(false);
const searchQuery = ref('');
const currentPage = ref(1);
const itemsPerPage = 10;
const showAlert = ref(false);
const alertMessage = ref('');
const alertType = ref('alert-success');
const selectedComment = ref(null);
let detailModal = null;

const showNotification = (message, type = 'success') => {
  alertType.value = type === 'success' ? 'alert-success' : 'alert-danger';
  alertMessage.value = message;
  showAlert.value = true;
  setTimeout(() => (showAlert.value = false), 3000);
};

const toggleSelectAll = () => {
  selectedComments.value = selectAll.value
    ? paginatedComments.value.map((com) => com.comment_id)
    : [];
};

const updateSelectAll = () => {
  selectAll.value =
    paginatedComments.value.length > 0 &&
    selectedComments.value.length === paginatedComments.value.length &&
    paginatedComments.value.every((com) => selectedComments.value.includes(com.comment_id));
};

const fetchComments = async () => {
  try {
    const response = await axios.get(`${apiUrl}/api/news-comments`, {
      params: { q: searchQuery.value },
    });
    comments.value = response.data;
    selectedComments.value = [];
    selectAll.value = false;
  } catch (error) {
    showNotification('Không thể tải danh sách bình luận.', 'error');
  }
};

const filteredComments = computed(() => {
  if (!searchQuery.value) return comments.value;
  const query = searchQuery.value.toLowerCase();
  return comments.value.filter(
    (comment) =>
      (comment.comment_text && comment.comment_text.toLowerCase().includes(query)) ||
      (comment.user?.name && comment.user.name.toLowerCase().includes(query)) ||
      (comment.news?.title && comment.news.title.toLowerCase().includes(query))
  );
});

const totalPages = computed(() => Math.ceil(filteredComments.value.length / itemsPerPage));

const paginatedComments = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  return filteredComments.value.slice(start, start + itemsPerPage);
});

const openCreateModal = () => {
  showNotification('Chức năng thêm bình luận chưa được hỗ trợ.', 'error');
};

const openDetailModal = (comment) => {
  selectedComment.value = comment;
  detailModal = new Modal(document.getElementById('detailModal'));
  detailModal.show();
};

const closeModal = () => {
  if (detailModal) detailModal.hide();
};

const deleteComment = async (id) => {
  if (!confirm('Bạn có chắc muốn xóa bình luận này?')) return;
  try {
    await axios.delete(`${apiUrl}/api/comments/${id}`);
    selectedComments.value = selectedComments.value.filter((comId) => comId !== id);
    await fetchComments();
    if (paginatedComments.value.length === 0 && currentPage.value > 1) {
      currentPage.value--;
    }
    showNotification('Xóa bình luận thành công!');
  } catch (error) {
    showNotification('Xóa bình luận thất bại.', 'error');
  }
};

onMounted(() => {
  fetchComments();
});

watch(searchQuery, () => {
  currentPage.value = 1;
  fetchComments();
});
</script>

<style scoped>
/* Giữ nguyên style như bạn đã cung cấp */
</style>