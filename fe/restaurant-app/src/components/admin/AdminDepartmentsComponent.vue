<template>
  <div class="staff-container">
    <!-- Thông báo -->
    <div v-if="showAlert" :class="['alert', alertType, 'custom-alert']" role="alert">
      {{ alertMessage }}
    </div>

    <div class="header-section mb-4">
      <h3 class="fw-bold">Quản lý Phòng ban</h3>
      <div class="d-flex justify-content-between align-items-center mt-3">
        <div class="search-form position-relative">
          <input
            type="text"
            class="form-control"
            v-model="searchQuery"
            placeholder="Tìm kiếm theo tên phòng ban hoặc tên người quản lý..."
            @input="filterDepartments"
          />
          <i class="bi bi-search search-icon position-absolute"></i>
        </div>
        <button class="btn btn-primary" @click="openAddModal">
          <i class="bi bi-plus-circle me-2"></i>Thêm Phòng ban Mới
        </button>
      </div>
    </div>

    <div class="card shadow-sm rounded-3">
      <div class="card-body p-0">
        <div class="table-responsive custom-scroll">
          <table class="table table-hover table-bordered mb-0">
            <thead class="table-light">
              <tr>
                <th scope="col"></th>
                <th scope="col">Tên phòng ban</th>
                <th scope="col">Mô tả</th>
                <th scope="col">Người quản lý</th>
                <th scope="col">Số điện thoại</th>
                <th scope="col">Email</th>
                <th scope="col">Hành động</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="department in paginatedDepartments" :key="department.department_id">
                <td><input type="checkbox" class="form-check-input" /></td>
                <td>{{ department.name }}</td>
                <td>{{ department.description || 'Không có' }}</td>
                <td>{{ department.manager?.name ?? 'Chưa có' }}</td>
                <td>{{ department.manager?.phone ?? 'Chưa có' }}</td>
                <td>{{ department.manager?.email ?? 'Chưa có' }}</td>
                <td>
                  <button class="btn btn-sm btn-outline-primary me-2" @click="openDetailModal(department)" title="Xem chi tiết">
                    <i class="bi bi-eye"></i>
                  </button>
                  <button class="btn btn-sm btn-outline-warning me-2" @click="openEditModal(department)" title="Sửa">
                    <i class="bi bi-pencil"></i>
                  </button>
                  <button class="btn btn-sm btn-outline-danger" @click="deleteDepartment(department.department_id)" title="Xóa">
                    <i class="bi bi-trash"></i>
                  </button>
                </td>
              </tr>
              <tr v-if="!paginatedDepartments.length">
                <td colspan="7" class="text-center text-muted">Không tìm thấy phòng ban</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Pagination Controls -->
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

    <!-- Add/Edit Modal -->
    <div class="modal fade" id="departmentModal" tabindex="-1" aria-labelledby="departmentModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="departmentModalLabel">{{ isEditMode ? 'Chỉnh sửa Phòng ban' : 'Thêm Phòng ban Mới' }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="isEditMode ? updateDepartment() : addDepartment()">
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="name" class="form-label">Tên phòng ban</label>
                  <input type="text" class="form-control" v-model="departmentForm.name" required />
                </div>
                <div class="col-md-6 mb-3">
                  <label for="manager_id" class="form-label">Người quản lý</label>
                  <select class="form-select" v-model="departmentForm.manager_id">
                    <option value="" selected>Không có</option>
                    <option v-for="employee in employees" :key="employee.employee_id" :value="employee.employee_id">
                      {{ employee.name }} ({{ employee.position }})
                    </option>
                  </select>
                </div>
              </div>
              <div class="row">
                <div class="col-md-12 mb-3">
                  <label for="description" class="form-label">Mô tả</label>
                  <textarea class="form-control" v-model="departmentForm.description" rows="4"></textarea>
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

    <!-- Detail Modal -->
    <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="detailModalLabel">Chi tiết Phòng ban</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
          </div>
          <div class="modal-body">
            <div class="row">
              <div class="col-md-6 mb-3">
                <strong>Tên phòng ban:</strong> {{ selectedDepartment.name }}
              </div>
              <div class="col-md-6 mb-3">
                <strong>Người quản lý:</strong> {{ selectedDepartment.manager?.name ?? 'Chưa có' }}
              </div>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <strong>Số điện thoại:</strong> {{ selectedDepartment.manager?.phone ?? 'Chưa có' }}
              </div>
              <div class="col-md-6 mb-3">
                <strong>Email:</strong> {{ selectedDepartment.manager?.email ?? 'Chưa có' }}
              </div>
            </div>
            <div class="row">
              <div class="col-md-12 mb-3">
                <strong>Mô tả:</strong> {{ selectedDepartment.description || 'Không có' }}
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
import { ref, computed, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import { inject } from 'vue';
import * as bootstrap from 'bootstrap';

const debounce = (func, wait) => {
  let timeout;
  return (...args) => {
    clearTimeout(timeout);
    timeout = setTimeout(() => func(...args), wait);
  };
};

const departments = ref([]);
const employees = ref([]);
const searchQuery = ref('');
const currentPage = ref(1);
const itemsPerPage = 10;
const apiUrl = inject('apiUrl');
const departmentForm = ref({
  name: '',
  description: '',
  manager_id: '',
});
const selectedDepartment = ref({});
const isEditMode = ref(false);

// Biến trạng thái cho thông báo
const showAlert = ref(false);
const alertMessage = ref('');
const alertType = ref('alert-success');

// Hàm hiển thị thông báo
const showNotification = (message, type = 'success') => {
  alertType.value = type === 'success' ? 'alert-success' : 'alert-danger';
  alertMessage.value = message;
  showAlert.value = true;
  setTimeout(() => {
    showAlert.value = false;
  }, 3000); // Ẩn sau 3 giây
};

const fetchDepartments = async () => {
  try {
    const response = await axios.get(`${apiUrl}/api/departments`, {
      params: { q: searchQuery.value },
      withCredentials: true,
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`
      }
    });
    departments.value = response.data;
    console.log('Dữ liệu phòng ban:', JSON.stringify(departments.value, null, 2));
  } catch (error) {
    console.error('Lỗi khi lấy dữ liệu phòng ban:', error.response ? error.response.data : error.message);
    showNotification('Không thể tải danh sách phòng ban.', 'error');
  }
};

const fetchEmployees = async () => {
  try {
    const response = await axios.get(`${apiUrl}/api/employees`, {
      withCredentials: true,
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`
      }
    });
    employees.value = response.data;
    console.log('Dữ liệu nhân viên:', employees.value);
  } catch (error) {
    console.error('Lỗi khi lấy dữ liệu nhân viên:', error.response ? error.response.data : error.message);
    showNotification('Không thể tải danh sách nhân viên.', 'error');
  }
};

const filteredDepartments = computed(() => {
  if (!searchQuery.value) return departments.value;
  const query = searchQuery.value.toLowerCase();
  return departments.value.filter(department => {
    const matchesName = department.name.toLowerCase().includes(query);
    const matchesDescription = department.description ? department.description.toLowerCase().includes(query) : false;
    const matchesManager = department.manager && department.manager.name
      ? department.manager.name.toLowerCase().includes(query)
      : false;
    return matchesName || matchesDescription || matchesManager;
  });
});

const filterDepartments = debounce(() => {
  currentPage.value = 1;
  fetchDepartments();
}, 300);

const totalPages = computed(() => {
  return Math.ceil(filteredDepartments.value.length / itemsPerPage);
});

const paginatedDepartments = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  const end = start + itemsPerPage;
  return filteredDepartments.value.slice(start, end);
});

const openAddModal = () => {
  isEditMode.value = false;
  departmentForm.value = {
    name: '',
    description: '',
    manager_id: '',
  };
  const modal = new bootstrap.Modal(document.getElementById('departmentModal'));
  modal.show();
};

const openEditModal = (department) => {
  isEditMode.value = true;
  departmentForm.value = { ...department, manager_id: department.manager_id || '' };
  const modal = new bootstrap.Modal(document.getElementById('departmentModal'));
  modal.show();
};

const openDetailModal = (department) => {
  selectedDepartment.value = department;
  const modal = new bootstrap.Modal(document.getElementById('detailModal'));
  modal.show();
};

const addDepartment = async () => {
  try {
    console.log('Dữ liệu gửi lên:', departmentForm.value);
    const response = await axios.post(`${apiUrl}/api/departments`, departmentForm.value, {
      withCredentials: true,
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`
      }
    });
    console.log('Phản hồi từ server:', response.data);
    await fetchDepartments();
    showNotification('Thêm phòng ban thành công!');
    const modal = bootstrap.Modal.getInstance(document.getElementById('departmentModal'));
    modal.hide();
  } catch (error) {
    console.error('Lỗi khi thêm phòng ban:', error.response ? error.response.data : error.message);
    showNotification('Thêm phòng ban thất bại.', 'error');
  }
};

const updateDepartment = async () => {
  try {
    console.log('Dữ liệu gửi lên:', departmentForm.value);
    const response = await axios.put(`${apiUrl}/api/departments/${departmentForm.value.department_id}`, departmentForm.value, {
      withCredentials: true,
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`
      }
    });
    console.log('Phản hồi từ server:', response.data);
    await fetchDepartments();
    showNotification('Cập nhật phòng ban thành công!');
    const modal = bootstrap.Modal.getInstance(document.getElementById('departmentModal'));
    modal.hide();
  } catch (error) {
    console.error('Lỗi khi cập nhật phòng ban:', error.response ? error.response.data : error.message);
    showNotification('Cập nhật phòng ban thất bại.', 'error');
  }
};

const deleteDepartment = async (id) => {
  if (confirm('Bạn có chắc chắn muốn xóa phòng ban này không?')) {
    try {
      console.log('ID xóa:', id);
      const response = await axios.delete(`${apiUrl}/api/departments/${id}`, {
        withCredentials: true,
        headers: {
          Authorization: `Bearer ${localStorage.getItem('token')}`
        }
      });
      console.log('Phản hồi từ server:', response.data);
      await fetchDepartments();
      if (paginatedDepartments.value.length === 0 && currentPage.value > 1) {
        currentPage.value--;
      }
      showNotification('Xóa phòng ban thành công!');
    } catch (error) {
      console.error('Lỗi khi xóa phòng ban:', error.response ? error.response.data : error.message);
      showNotification('Xóa phòng ban thất bại.', 'error');
    }
  }
};

// Lắng nghe sự kiện refresh-departments từ component Staffs.vue
const handleRefreshDepartments = async () => {
  await fetchDepartments();
  showNotification('Danh sách phòng ban đã được làm mới do thay đổi nhân viên.');
};

onMounted(async () => {
  await fetchEmployees();
  await fetchDepartments();
  window.addEventListener('refresh-departments', handleRefreshDepartments);
});

onUnmounted(() => {
  window.removeEventListener('refresh-departments', handleRefreshDepartments);
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
}
.table td {
  text-align: left;
  color: #666;
}
.table td:first-child,
.table td:last-child {
  text-align: center;
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
.custom-scroll {
  overflow-x: auto;
}
.custom-scroll::-webkit-scrollbar {
  height: 8px;
}
.custom-scroll::-webkit-scrollbar-track {
  background: #f1f3f5;
  border-radius: 10px;
}
.custom-scroll::-webkit-scrollbar-thumb {
  background: #16B978;
  border-radius: 10px;
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

/* CSS cho thông báo */
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

/* Hiệu ứng fade */
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
    min-width: 600px;
  }
}
</style>