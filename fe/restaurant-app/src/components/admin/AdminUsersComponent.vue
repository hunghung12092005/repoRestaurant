<template>
  <div class="page-container">
    <!-- Thông báo -->
    <div v-if="showAlert" :class="['alert', alertType, 'custom-alert']" role="alert">
      {{ alertMessage }}
      <span class="close-btn" @click="showAlert = false">×</span>
    </div>

    <!-- Tiêu đề trang -->
    <div class="page-header mb-4">
      <h1 class="page-title">Quản lý Tài Khoản</h1>
      <p class="page-subtitle">Xem, phân quyền và quản lý tài khoản người dùng trong hệ thống.</p>
    </div>

    <!-- Bộ lọc và tìm kiếm -->
    <div class="card filter-card mb-4">
      <div class="card-body d-flex justify-content-between align-items-center">
        <div class="col-md-5">
          <input
            v-model="searchQuery"
            type="text"
            class="form-control"
            placeholder="Tìm theo tên hoặc email người dùng..."
          />
        </div>
      </div>
    </div>

    <!-- Bảng danh sách người dùng -->
    <div class="table-container">
      <table class="table booking-table align-middle">
        <thead>
          <tr>
            <th style="width: 35%;">Người Dùng</th>
            <th style="width: 20%;">Vai Trò</th>
            <th style="width: 25%;">Quyền Hạn</th>
            <th class="text-center" style="width: 20%;">Hành Động</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in paginatedUsers" :key="user.id">
            <td>
              <div class="fw-bold type-name">{{ user.name }}</div>
              <p class="description-text mb-0">{{ user.email }}</p>
              <p class="description-text mb-0 mt-1 fst-italic">Ngày tạo: {{ formatDate(user.created_at) }}</p>
            </td>
            <td>
              <span class="badge" :class="getRoleBadgeClass(user.role)">{{ user.role }}</span>
            </td>
            <td>
               <div class="tags-container">
                <span v-if="!user.permissions || user.permissions.length === 0" class="badge badge-secondary">Không có quyền</span>
                <span v-else v-for="permission in user.permissions.slice(0, 2)" :key="permission" class="badge badge-info">
                  {{ getPermissionLabel(permission) }}
                </span>
                <span v-if="user.permissions && user.permissions.length > 2" class="badge badge-info">
                  +{{ user.permissions.length - 2 }}
                </span>
              </div>
            </td>
            <td class="text-center action-buttons">
              <button class="btn btn-outline-info btn-sm" title="Xem chi tiết" @click="openDetailModal(user)">
                <i class="bi bi-eye-fill"></i>
              </button>
              <button class="btn btn-outline-primary btn-sm" title="Sửa vai trò & quyền" @click="openEditRoleModal(user)">
                <i class="bi bi-shield-lock-fill"></i>
              </button>
            </td>
          </tr>
          <tr v-if="!paginatedUsers.length">
            <td colspan="4" class="text-center py-5">Không tìm thấy người dùng phù hợp.</td>
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
        <li class="page-item" :class="{ disabled: currentPage === totalPages || !totalPages }">
          <a class="page-link" href="#" @click.prevent="currentPage++">»</a>
        </li>
        <li class="page-item" :class="{ disabled: currentPage === totalPages || !totalPages }">
          <a class="page-link" href="#" @click.prevent="currentPage = totalPages">»»</a>
        </li>
      </ul>
    </nav>


    <!-- Modal Chi Tiết -->
    <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content modal-custom">
          <div class="modal-header modal-header-custom">
            <h5 class="modal-title" id="detailModalLabel">Chi tiết Tài Khoản</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
          </div>
          <div class="modal-body p-4">
            <div class="row g-3" v-if="selectedUser">
                <div class="col-12">
                    <p class="mb-1 text-muted">Họ và Tên</p>
                    <h6 class="fw-bold">{{ selectedUser.name || 'N/A' }}</h6>
                </div>
                <div class="col-12">
                    <p class="mb-1 text-muted">Email</p>
                    <h6 class="fw-bold">{{ selectedUser.email || 'N/A' }}</h6>
                </div>
                 <div class="col-md-6">
                    <p class="mb-1 text-muted">Vai trò</p>
                    <h6><span class="badge fs-6" :class="getRoleBadgeClass(selectedUser.role)">{{ selectedUser.role || 'N/A' }}</span></h6>
                </div>
                 <div class="col-md-6">
                    <p class="mb-1 text-muted">Ngày tham gia</p>
                    <h6 class="fw-bold">{{ formatDate(selectedUser.created_at) }}</h6>
                </div>
                 <div class="col-12">
                    <p class="mb-1 text-muted">Quyền cụ thể</p>
                    <ul class="list-group list-group-flush" v-if="selectedUser.permissions && selectedUser.permissions.length > 0">
                         <li class="list-group-item px-0" v-for="permission in selectedUser.permissions" :key="permission">
                            <i class="bi bi-check-circle-fill text-success me-2"></i>{{ getPermissionLabel(permission) }}
                        </li>
                    </ul>
                    <p v-else class="fst-italic text-muted">Không có quyền cụ thể nào được gán.</p>
                 </div>
            </div>
          </div>
          <div class="modal-footer modal-footer-custom">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
          </div>
        </div>
      </div>
    </div>


    <!-- Modal Sửa Vai Trò -->
    <div class="modal fade" id="editRoleModal" tabindex="-1" aria-labelledby="editRoleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content modal-custom">
          <div class="modal-header modal-header-custom">
            <h5 class="modal-title" id="editRoleModalLabel">Chỉnh sửa Vai trò & Quyền</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
          </div>
          <div class="modal-body p-4">
            <form @submit.prevent="saveRole">
              <div class="row g-3">
                <div class="col-12 mb-2">
                  <label for="role" class="form-label">Vai trò</label>
                  <select class="form-select" v-model="form.role" required>
                    <option value="admin">Admin</option>
                    <option value="staff">Staff</option>
                    <option value="client">Client</option>
                  </select>
                </div>

                <div class="col-12">
                  <label class="form-label">Quyền cụ thể</label>
                  <div class="checkbox-list" :class="{ 'disabled-list': form.role === 'client' }">
                    <div class="form-check form-switch" v-for="permission in availablePermissions" :key="permission.value">
                       <input
                        class="form-check-input"
                        type="checkbox"
                        role="switch"
                        :value="permission.value"
                        v-model="form.permissions"
                        :disabled="form.role === 'client'"
                        :id="'perm-' + permission.value"
                      >
                      <label class="form-check-label" :for="'perm-' + permission.value">{{ permission.label }}</label>
                    </div>
                  </div>
                   <small v-if="form.role === 'client'" class="form-text text-muted mt-2 d-block">Quyền chỉ có thể gán cho vai trò "Admin" hoặc "Staff".</small>
                </div>
              </div>
            </form>
          </div>
           <div class="modal-footer modal-footer-custom">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="button" @click="saveRole" class="btn btn-primary">Lưu Thay Đổi</button>
            </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch, inject } from 'vue';
import axios from 'axios';
import { Modal } from 'bootstrap';

const apiUrl = inject('apiUrl', 'http://localhost:8000');
const updateAndRefreshUserInfo = inject('updateAndRefreshUserInfo');

const users = ref([]);
const form = ref({ id: null, role: 'client', permissions: [] });
const searchQuery = ref('');
const currentPage = ref(1);
const itemsPerPage = 10;
const showAlert = ref(false);
const alertMessage = ref('');
const alertType = ref('alert-success');
const selectedUser = ref(null);
let detailModal = null;
let editRoleModal = null;

const availablePermissions = ref([
    { value: 'manage_news', label: 'Quản lý Tin tức' },
    { value: 'manage_contacts', label: 'Quản lý Liên hệ' },
    { value: 'manage_users', label: 'Quản lý Tài khoản' },
    { value: 'manage_ai_training', label: 'Training AI' },
    { value: 'manage_admin_chat', label: 'Chat Admin' },
]);

const axiosConfig = axios.create({
  baseURL: apiUrl,
  headers: {
    'Authorization': `Bearer ${localStorage.getItem('tokenJwt') || ''}`,
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// === FIXED: Logic mở modal đã được sửa lại ===
const openEditRoleModal = (user) => {
  // Logic đúng: Luôn lấy quyền từ đối tượng người dùng được truyền vào.
  // Không có logic `if (user.role === 'admin')` nào ở đây nữa.
  // Điều này đảm bảo trạng thái hiển thị trong modal luôn khớp với CSDL.
  form.value = {
      id: user.id,
      role: user.role,
      // Tạo một bản sao của mảng để tránh các vấn đề về tham chiếu trong Vue
      permissions: user.permissions ? [...user.permissions] : [] 
  };

  if (!editRoleModal) {
      editRoleModal = new Modal(document.getElementById('editRoleModal'));
  }
  editRoleModal.show();
};

const saveRole = async () => {
  try {
    let permissionsToSend = form.value.permissions;
    if (form.value.role === 'client') {
        permissionsToSend = [];
    }
    
    const response = await axiosConfig.put(`/api/users/${form.value.id}`, {
        role: form.value.role,
        permissions: permissionsToSend
    });
    
    const updatedUser = response.data.data;
    const loggedInUser = JSON.parse(localStorage.getItem('userInfo') || '{}');

    // Cập nhật trạng thái tức thì nếu người dùng tự sửa quyền của chính mình
    if (loggedInUser && loggedInUser.id === updatedUser.id) {
      if (updateAndRefreshUserInfo) {
        updateAndRefreshUserInfo(updatedUser);
      }
    }

    showNotification('Cập nhật vai trò và quyền thành công!');
    await fetchUsers(); // Tải lại danh sách để cập nhật bảng
    closeModal('edit');
    
  } catch (error) {
    const errorMessage = error.response?.data?.message || 'Cập nhật thất bại.';
    showNotification(errorMessage, 'error');
  }
};

// Watcher này chỉ còn tác dụng khi chuyển vai trò thành 'client' trong form
watch(() => form.value.role, (newRole, oldRole) => {
  if (newRole === 'client' && oldRole !== 'client') {
    form.value.permissions = [];
  }
});


// Các hàm còn lại không thay đổi
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
    users.value = (response.data.data || response.data).map(user => ({
        ...user,
        permissions: user.permissions || []
    }));
  } catch (error) {
    showNotification('Không thể tải danh sách người dùng.', 'error');
  }
};

const closeModal = (modalType) => {
    if (modalType === 'edit' && editRoleModal) {
        const modalInstance = Modal.getInstance(document.getElementById('editRoleModal'));
        if (modalInstance) modalInstance.hide();
    }
    if (modalType === 'detail' && detailModal) {
        const modalInstance = Modal.getInstance(document.getElementById('detailModal'));
        if (modalInstance) modalInstance.hide();
    }
};

const filteredUsers = computed(() => users.value);

const totalPages = computed(() => Math.ceil(filteredUsers.value.length / itemsPerPage) || 1);

const paginatedUsers = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  return filteredUsers.value.slice(start, start + itemsPerPage);
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

const openDetailModal = (user) => {
  selectedUser.value = user;
  if (!detailModal) detailModal = new Modal(document.getElementById('detailModal'));
  detailModal.show();
};

const getRoleBadgeClass = (role) => {
  switch (role) {
    case 'admin': return 'badge-danger';
    case 'staff': return 'badge-warning';
    case 'client': return 'badge-success';
    default: return 'badge-secondary';
  }
};

const getPermissionLabel = (permissionValue) => {
    const permission = availablePermissions.value.find(p => p.value === permissionValue);
    return permission ? permission.label : permissionValue;
}

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
@import url('https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700&display=swap');
@import url('https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css');
.page-container { font-family: 'Be Vietnam Pro', sans-serif; background-color: #f4f7f9; padding: 2rem; color: #34495e; }
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
.type-name { font-size: 1rem; }
.description-text { font-size: 0.8rem; color: #7f8c8d; }
.tags-container { display: flex; flex-wrap: wrap; gap: 6px; }
.badge { padding: 0.4em 0.8em; font-size: 0.75rem; font-weight: 600; border-radius: 20px; letter-spacing: 0.5px; }
.badge-info { background-color: #eaf6fb; color: #3498db; }
.badge-secondary { background-color: #f3f4f6; color: #7f8c8d; }
.badge-success { background-color: #e6f9f0; color: #2ecc71; }
.badge-danger { background-color: #fce8e6; color: #e74c3c; }
.badge-warning { background-color: #fef5e7; color: #f39c12; }
.action-buttons { white-space: nowrap; }
.action-buttons .btn { margin: 0 2px; }
.pagination .page-link { border: none; border-radius: 8px; margin: 0 4px; color: #7f8c8d; font-weight: 600; }
.pagination .page-item.active .page-link { background-color: #3498db; color: white; }
.modal-custom { border-radius: 16px; border: none; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); }
.modal-header-custom { background-color: #f4f7f9; border-bottom: 1px solid #e5eaee; padding: 1.5rem; }
.modal-footer-custom { background-color: #f4f7f9; border-top: 1px solid #e5eaee; padding: 1rem 1.5rem; }
.custom-alert { position: fixed; top: 20px; right: 20px; z-index: 1060; min-width: 300px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); border-radius: 8px; }
.close-btn { cursor: pointer; float: right; font-size: 1.5rem; line-height: 1; }
.checkbox-list { max-height: 250px; overflow-y: auto; border: 1px solid #e5eaee; border-radius: 8px; padding: 1rem; background-color: #ffffff; }
.checkbox-list.disabled-list { background-color: #f8f9fa; cursor: not-allowed; }
.checkbox-list.disabled-list .form-check-label { color: #6c757d; }
</style>