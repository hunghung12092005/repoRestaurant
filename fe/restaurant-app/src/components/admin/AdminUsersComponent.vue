<template>
  <div class="staff-container">
    <div v-if="showAlert" :class="['alert', alertType, 'custom-alert']" role="alert">
      {{ alertMessage }}
      <span class="close-btn" @click="showAlert = false">×</span>
    </div>

    <div class="header-section mb-4">
      <h3 class="fw-bold">Quản lý Tài Khoản Người Dùng</h3>
      <div class="d-flex justify-content-between align-items-center mt-3">
        <div class="search-form position-relative">
          <input
            type="text"
            class="form-control"
            v-model="searchQuery"
            placeholder="Tìm kiếm theo tên hoặc email..."
            @input="fetchUsers"
          />
          <i class="bi bi-search search-icon position-absolute"></i>
        </div>
      </div>
    </div>

    <div class="card shadow-sm rounded-3">
      <div class="card-body p-0">
        <div class="table-responsive custom-scroll">
          <table class="table table-hover table-bordered mb-0">
            <thead class="table-light">
              <tr>
                <th scope="col">Tên</th>
                <th scope="col">Email</th>
                <th scope="col">Vai trò</th>
                <th scope="col">Ngày tạo</th>
                <th scope="col">Hành động</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="user in paginatedUsers" :key="user.id">
                <td>{{ user.name }}</td>
                <td>{{ user.email }}</td>
                <td :class="getRoleClass(user.role)">{{ user.role }}</td>
                <td>{{ formatDate(user.created_at) }}</td>
                <td>
                  <button class="btn btn-sm btn-outline-primary me-2" @click="openDetailModal(user)">
                    <i class="bi bi-eye"></i>
                  </button>
                  <button class="btn btn-sm btn-outline-warning me-2" @click="openEditRoleModal(user)">
                    <i class="bi bi-person-check"></i>
                  </button>
                </td>
              </tr>
              <tr v-if="!paginatedUsers.length">
                <td colspan="5" class="text-center text-muted">Không tìm thấy người dùng</td>
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
            <h5 class="modal-title" id="detailModalLabel">Chi tiết Tài Khoản</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
          </div>
          <div class="modal-body">
            <div class="row">
              <div class="col-md-6 mb-3"><strong>Tên:</strong> {{ selectedUser?.name || 'N/A' }}</div>
              <div class="col-md-6 mb-3"><strong>Email:</strong> {{ selectedUser?.email || 'N/A' }}</div>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3"><strong>Vai trò:</strong> <span :class="getRoleClass(selectedUser?.role)">{{ selectedUser?.role || 'N/A' }}</span></div>
              <div class="col-md-6 mb-3"><strong>Ngày tạo:</strong> {{ formatDate(selectedUser?.created_at) }}</div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="editRoleModal" tabindex="-1" aria-labelledby="editRoleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="editRoleModalLabel">Chỉnh sửa Vai trò</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="saveRole">
              <div class="row">
                <div class="col-md-12 mb-3">
                  <label for="role" class="form-label">Vai trò</label>
                  <select class="form-control" v-model="form.role" required>
                    <option value="admin">Admin</option>
                    <option value="staff">Staff</option>
                    <option value="client">Client</option>
                  </select>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="submit" class="btn btn-primary">Cập nhật</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import { inject } from 'vue';
import axios from 'axios';
import { Modal } from 'bootstrap';

const router = useRouter();
const users = ref([]);
const form = ref({
  id: null,
  role: 'user',
});
const searchQuery = ref('');
const currentPage = ref(1);
const itemsPerPage = 10;
const showAlert = ref(false);
const alertMessage = ref('');
const alertType = ref('alert-success');
const selectedUser = ref(null);
let detailModal = null;
let editRoleModal = null;
const apiUrl = inject('apiUrl', 'http://localhost:8000');

const axiosConfig = axios.create({
  baseURL: apiUrl,
  headers: {
    'Authorization': `Bearer ${localStorage.getItem('tokenJwt') || ''}`,
    'Content-Type': 'application/json',
  },
});

const showNotification = (message, type = 'success') => {
  alertType.value = type === 'success' ? 'alert-success' : 'alert-danger';
  alertMessage.value = message;
  showAlert.value = true;
  setTimeout(() => (showAlert.value = false), 3000);
};

const fetchUsers = async () => {
  try {
    const response = await axiosConfig.get('/api/users', {
      params: { q: searchQuery.value },
    });
    users.value = response.data.data || response.data;
  } catch (error) {
    showNotification('Không thể tải danh sách người dùng.', 'error');
  }
};

const filteredUsers = computed(() => {
  if (!searchQuery.value) return users.value;
  const query = searchQuery.value.toLowerCase();
  return users.value.filter(
    (user) =>
      user.name.toLowerCase().includes(query) ||
      user.email.toLowerCase().includes(query)
  );
});

const totalPages = computed(() => Math.ceil(filteredUsers.value.length / itemsPerPage));

const paginatedUsers = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  return filteredUsers.value.slice(start, start + itemsPerPage);
});

const openDetailModal = (user) => {
  selectedUser.value = user;
  detailModal = new Modal(document.getElementById('detailModal'));
  detailModal.show();
};

const openEditRoleModal = (user) => {
  form.value = { id: user.id, role: user.role };
  editRoleModal = new Modal(document.getElementById('editRoleModal'));
  editRoleModal.show();
};

const closeModal = () => {
  if (detailModal) detailModal.hide();
  if (editRoleModal) editRoleModal.hide();
};

const saveRole = async () => {
  try {
    await axiosConfig.put(`/api/users/${form.value.id}`, { role: form.value.role });
    showNotification('Cập nhật vai trò thành công!');
    fetchUsers();
    closeModal();
  } catch (error) {
    showNotification('Cập nhật vai trò thất bại.', 'error');
  }
};

const getRoleClass = (role) => {
  switch (role) {
    case 'admin':
      return 'role-admin';
    case 'staff':
      return 'role-staff';
    case 'client':
      return 'role-client';
    default:
      return '';
  }
};

const formatDate = (dateString) => {
  return dateString ? new Date(dateString).toLocaleDateString('vi-VN') : 'N/A';
};

onMounted(() => {
  fetchUsers();
});

watch(searchQuery, () => {
  currentPage.value = 1;
  fetchUsers();
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
.table td:first-child {
  min-width: 150px;
}
.table td:nth-child(2) {
  min-width: 200px;
}
.table td:last-child {
  text-align: center;
  min-width: 180px;
}
.table-hover tbody tr:hover {
  background-color: #f1f3f5;
}
.btn-outline-primary,
.btn-outline-warning {
  padding: 4px 8px;
  font-size: 0.9rem;
}
.btn-outline-primary i,
.btn-outline-warning i {
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
.role-admin {
  color: #2ecc71 !important;
  font-weight: bold;
}
.role-staff {
  color: #f1c40f !important;
  font-weight: bold;
}
.role-client {
  color: #3498db !important;
  font-weight: bold;
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