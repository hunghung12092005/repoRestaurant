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
             <div class="row" v-if="selectedUser?.permissions?.length > 0">
                 <div class="col-12">
                     <strong>Quyền:</strong>
                     <ul>
                         <li v-for="permission in selectedUser.permissions" :key="permission">{{ permission }}</li>
                     </ul>
                 </div>
             </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
          </div>
        </div>
      </div>
    </div>

    <!-- --- MODIFIED MODAL --- -->
    <div class="modal fade" id="editRoleModal" tabindex="-1" aria-labelledby="editRoleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="editRoleModalLabel">Chỉnh sửa Vai trò & Quyền</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="saveRole">
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="role" class="form-label fw-bold">Vai trò</label>
                  <select class="form-control" v-model="form.role" required>
                    <option value="admin">Admin (Tất cả quyền)</option>
                    <option value="staff">Staff</option>
                    <option value="client">Client</option>
                  </select>
                </div>

                <div class="col-md-6 mb-3">
                  <label class="form-label fw-bold">Quyền cụ thể</label>
                  <div class="list-group">
                    <label class="list-group-item" v-for="permission in availablePermissions" :key="permission.value">
                      <input
                        class="form-check-input me-2"
                        type="checkbox"
                        :value="permission.value"
                        v-model="form.permissions"
                        :disabled="form.role !== 'staff'"
                      >
                      {{ permission.label }}
                    </label>
                  </div>
                   <small v-if="form.role !== 'staff'" class="form-text text-muted mt-1 d-block">Quyền chỉ có thể gán cho vai trò Staff.</small>
                </div>
              </div>
              <div class="modal-footer mt-3">
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
import { inject } from 'vue';
import axios from 'axios';
import { Modal } from 'bootstrap';

const users = ref([]);
const form = ref({
  id: null,
  role: 'client',
  permissions: [], // --- ADDED ---
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

// --- ADDED ---
const availablePermissions = ref([
    { value: 'manage_news', label: 'Quản lý Tin tức' },
    { value: 'manage_contacts', label: 'Quản lý Liên hệ' },
]);

const axiosConfig = axios.create({
  baseURL: apiUrl,
  headers: {
    'Authorization': `Bearer ${localStorage.getItem('tokenJwt') || ''}`,
    'Content-Type': 'application/json',
    'Accept': 'application/json',
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
    // Tìm kiếm phía server
    const response = await axiosConfig.get('/api/users', {
      params: { q: searchQuery.value },
    });
    users.value = (response.data.data || response.data).map(user => ({
        ...user,
        permissions: user.permissions || []
    }));
  } catch (error) {
    showNotification('Không thể tải danh sách người dùng.', 'error');
  }
};

const filteredUsers = computed(() => {
    // Bây giờ users đã được lọc từ server, không cần lọc lại ở client
    return users.value;
});

const totalPages = computed(() => Math.ceil(filteredUsers.value.length / itemsPerPage) || 1);

const paginatedUsers = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  return filteredUsers.value.slice(start, start + itemsPerPage);
});

const openDetailModal = (user) => {
  selectedUser.value = user;
  if (!detailModal) detailModal = new Modal(document.getElementById('detailModal'));
  detailModal.show();
};

const openEditRoleModal = (user) => {
  form.value = { 
      id: user.id, 
      role: user.role, 
      permissions: user.permissions || [] 
  };
  if (!editRoleModal) editRoleModal = new Modal(document.getElementById('editRoleModal'));
  editRoleModal.show();
};

const closeModal = () => {
  if (detailModal) detailModal.hide();
  if (editRoleModal) editRoleModal.hide();
};

const saveRole = async () => {
  try {
    let permissionsToSend = form.value.permissions;
    // Nếu vai trò không phải staff, gửi một mảng rỗng
    if (form.value.role !== 'staff') {
        permissionsToSend = [];
    }

    await axiosConfig.put(`/api/users/${form.value.id}`, { 
        role: form.value.role,
        permissions: permissionsToSend
    });
    showNotification('Cập nhật vai trò và quyền thành công!');
    await fetchUsers(); // Tải lại danh sách người dùng
    closeModal();
  } catch (error) {
    const errorMessage = error.response?.data?.message || 'Cập nhật thất bại.';
    showNotification(errorMessage, 'error');
  }
};

const getRoleClass = (role) => {
  switch (role) {
    case 'admin': return 'role-admin';
    case 'staff': return 'role-staff';
    case 'client': return 'role-client';
    default: return '';
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
  fetchUsers(); // Gọi API mỗi khi search query thay đổi
});

</script>

<style scoped>
.staff-container { padding: 20px; }
.header-section .d-flex { gap: 15px; }
.search-form input { padding-right: 40px; }
.search-form .search-icon { top: 50%; right: 15px; transform: translateY(-50%); color: #666; cursor: pointer; font-size: 1rem; }
.search-form .search-icon:hover { color: #16B978; }
.search-form input:focus { border-color: #16B978; outline: none; box-shadow: 0 0 5px rgba(22, 185, 120, 0.3); }
.table th { background-color: #f8f9fa; font-weight: 600; color: #333; white-space: nowrap; padding: 10px 15px; }
.table td { color: #666; white-space: nowrap; padding: 10px 15px; overflow: hidden; text-overflow: ellipsis; }
.table-hover tbody tr:hover { background-color: #f1f3f5; }
.pagination-controls .btn { padding: 8px 20px; font-size: 0.85rem; border-width: 1px; }
.custom-alert { position: fixed; top: 20px; right: 20px; z-index: 1050; min-width: 300px; }
.role-admin { color: #2ecc71 !important; font-weight: bold; }
.role-staff { color: #f1c40f !important; font-weight: bold; }
.role-client { color: #3498db !important; font-weight: bold; }
.list-group-item { cursor: pointer; }
.list-group-item:has(input:disabled) { background-color: #f8f9fa; cursor: not-allowed; color: #6c757d; }
</style>