<template>
  <div class="staff-container">
    <div v-if="showAlert" :class="['alert', alertType, 'custom-alert']" role="alert">
      {{ alertMessage }}
      <span class="close-btn" @click="showAlert = false">×</span>
    </div>

    <div class="header-section mb-4">
      <h3 class="fw-bold">Quản lý Nhân viên</h3>
      <div class="d-flex justify-content-between align-items-center mt-3">
        <div class="search-form position-relative">
          <input
            type="text"
            class="form-control"
            v-model="searchQuery"
            placeholder="Tìm kiếm theo tên, email..."
          />
          <i class="bi bi-search search-icon position-absolute"></i>
        </div>
        <div class="d-flex align-items-center gap-2">
          <button v-if="selectedEmployees.length > 0" class="btn btn-danger" @click="deleteSelectedEmployees">
            <i class="bi bi-trash me-2"></i>Xóa
          </button>
          <button class="btn btn-primary" @click="openAddModal">
            <i class="bi bi-plus-circle me-2"></i>Thêm Nhân viên
          </button>
        </div>
      </div>
    </div>

    <div class="card shadow-sm rounded-3">
      <div class="card-body p-0">
        <div class="table-responsive custom-scroll">
          <table class="table table-hover table-bordered mb-0">
            <thead class="table-light">
              <tr>
                <th>
                  <input type="checkbox" class="form-check-input" v-model="selectAll" @change="toggleSelectAll" />
                </th>
                <th>Họ và Tên</th>
                <th>Giới tính</th>
                <th>Email</th>
                <th>Số điện thoại</th>
                <th>Phòng ban</th>
                <th>Hành động</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="employee in paginatedEmployees" :key="employee.employee_id">
                <td>
                  <input
                    type="checkbox"
                    class="form-check-input"
                    v-model="selectedEmployees"
                    :value="employee.employee_id"
                    @change="updateSelectAll"
                  />
                </td>
                <td>{{ employee.full_name }}</td>
                <td>{{ formatGender(employee.gender) }}</td>
                <td>{{ employee.email }}</td>
                <td>{{ employee.phone || 'Không có' }}</td>
                <td>{{ employee.department ? employee.department.name : 'Không có' }}</td>
                <td>
                  <button class="btn btn-sm btn-outline-primary me-2" @click="openDetailModal(employee)">
                    <i class="bi bi-eye"></i>
                  </button>
                  <button class="btn btn-sm btn-outline-warning me-2" @click="openEditModal(employee)">
                    <i class="bi bi-pencil"></i>
                  </button>
                  <button class="btn btn-sm btn-outline-danger" @click="deleteEmployee(employee.employee_id)">
                    <i class="bi bi-trash"></i>
                  </button>
                </td>
              </tr>
              <tr v-if="!paginatedEmployees.length">
                <td colspan="5" class="text-center text-muted">Không tìm thấy nhân viên</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="pagination-controls d-flex justify-content-center align-items-center mt-4">
      <button class="btn btn-outline-secondary me-2" :disabled="currentPage === 1" @click="currentPage--">
        Trước
      </button>
      <span>Trang {{ currentPage }} / {{ totalPages }}</span>
      <button
        class="btn btn-outline-secondary ms-2"
        :disabled="currentPage === totalPages || totalPages === 0"
        @click="currentPage++"
      >
        Tiếp
      </button>
    </div>

    <!-- Modal for Add/Edit Employee -->
    <div class="modal fade" id="staffModal" tabindex="-1" aria-labelledby="staffModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="staffModalLabel">{{ isEditMode ? 'Chỉnh sửa Nhân viên' : 'Thêm Nhân viên' }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="isEditMode ? updateEmployee() : addEmployee()">
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="full_name" class="form-label">Họ và Tên</label>
                  <input type="text" class="form-control" v-model="staffForm.full_name" required />
                  <div v-if="errors.full_name" class="text-danger">{{ errors.full_name.join('; ') }}</div>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="email" class="form-label">Email</label>
                  <input type="email" class="form-control" v-model="staffForm.email" required />
                  <div v-if="errors.email" class="text-danger">{{ errors.email.join('; ') }}</div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="gender" class="form-label">Giới tính</label>
                  <select class="form-select" v-model="staffForm.gender" required>
                    <option value="Male">Nam</option>
                    <option value="Female">Nữ</option>
                  </select>
                  <div v-if="errors.gender" class="text-danger">{{ errors.gender.join('; ') }}</div>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="birth_date" class="form-label">Ngày sinh</label>
                  <input type="date" class="form-control" v-model="staffForm.birth_date" required />
                  <div v-if="errors.birth_date" class="text-danger">{{ errors.birth_date.join('; ') }}</div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="phone" class="form-label">Số điện thoại</label>
                  <input type="text" class="form-control" v-model="staffForm.phone" required />
                  <div v-if="errors.phone" class="text-danger">{{ errors.phone.join('; ') }}</div>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="department_id" class="form-label">Phòng ban</label>
                  <select class="form-select" v-model.number="staffForm.department_id">
                    <option value="">Chọn phòng ban</option>
                    <option v-for="department in departments" :key="department.department_id" :value="department.department_id">
                      {{ department.name }} ({{ department.employees_count }} nhân viên)
                    </option>
                  </select>
                  <div v-if="errors.department_id" class="text-danger">{{ errors.department_id.join('; ') }}</div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="start_date" class="form-label">Ngày tuyển dụng</label>
                  <input type="date" class="form-control" v-model="staffForm.start_date" required />
                  <div v-if="errors.start_date" class="text-danger">{{ errors.start_date.join('; ') }}</div>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="address" class="form-label">Địa chỉ</label>
                  <textarea class="form-control" v-model="staffForm.address" rows="4"></textarea>
                  <div v-if="errors.address" class="text-danger">{{ errors.address.join('; ') }}</div>
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
            <h5 class="modal-title" id="detailModalLabel">Chi tiết nhân viên</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="row">
              <div class="col-md-6 mb-3">
                <strong>Họ và Tên:</strong> {{ selectedEmployee.full_name || 'Không có' }}
              </div>
              <div class="col-md-6 mb-3">
                <strong>Email:</strong> {{ selectedEmployee.email || 'Không có' }}
              </div>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <strong>Giới tính:</strong> {{ formatGender(selectedEmployee.gender) || 'Không có' }}
              </div>
              <div class="col-md-6 mb-3">
                <strong>Ngày sinh:</strong> {{ formatDate(selectedEmployee.birth_date) || 'Không có' }}
              </div>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <strong>Số điện thoại:</strong> {{ selectedEmployee.phone || 'Không có' }}
              </div>
              <div class="col-md-6 mb-3">
                <strong>Phòng ban:</strong> {{ selectedEmployee.department ? selectedEmployee.department.name : 'Không có' }}
              </div>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <strong>Ngày tuyển dụng:</strong> {{ formatDate(selectedEmployee.start_date) || 'Không có' }}
              </div>
              <div class="col-md-6 mb-3">
                <strong>Địa chỉ:</strong> {{ selectedEmployee.address || 'Không có' }}
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
import * as bootstrap from 'bootstrap';

const employees = ref([]);
const departments = ref([]);
const searchQuery = ref('');
const currentPage = ref(1);
const itemsPerPage = 10;
const apiUrl = inject('apiUrl');
const staffForm = ref({
  employee_id: null,
  full_name: '',
  gender: '',
  email: '',
  phone: '',
  address: '',
  department_id: null,
  birth_date: '',
  start_date: '',
});
const selectedEmployee = ref({});
const isEditMode = ref(false);
const selectedEmployees = ref([]);
const selectAll = ref(false);
const errors = ref({});
const showAlert = ref(false);
const alertMessage = ref('');
const alertType = ref('alert-success');

const formatDate = (dateStr) => {
  if (!dateStr) return 'Không có';
  const date = new Date(dateStr);
  return date.toLocaleDateString('vi-VN', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
  });
};

const formatGender = (gender) => {
  const genderMap = { Male: 'Nam', Female: 'Nữ' };
  return genderMap[gender] || 'Không có';
};

const showNotification = (message, type = 'success') => {
  alertType.value = type === 'success' ? 'alert-success' : 'alert-danger';
  alertMessage.value = message;
  showAlert.value = true;
  setTimeout(() => (showAlert.value = false), 3000);
};

const toggleSelectAll = () => {
  selectedEmployees.value = selectAll.value
    ? paginatedEmployees.value.map((emp) => emp.employee_id)
    : [];
};

const updateSelectAll = () => {
  selectAll.value =
    paginatedEmployees.value.length > 0 &&
    selectedEmployees.value.length === paginatedEmployees.value.length &&
    paginatedEmployees.value.every((emp) => selectedEmployees.value.includes(emp.employee_id));
};

const fetchEmployees = async () => {
  try {
    const response = await axios.get(`${apiUrl}/api/employees`, {
      params: { q: searchQuery.value },
      withCredentials: true,
      headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
    });
    employees.value = response.data;
    selectedEmployees.value = [];
    selectAll.value = false;
  } catch (error) {
    showNotification('Không thể tải danh sách nhân viên.', 'error');
  }
};

const fetchDepartments = async () => {
  try {
    const response = await axios.get(`${apiUrl}/api/departments`, {
      withCredentials: true,
      headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
    });
    departments.value = response.data;
  } catch (error) {
    showNotification('Không thể tải danh sách phòng ban.', 'error');
  }
};

const filteredEmployees = computed(() => {
  if (!searchQuery.value) return employees.value;
  const query = searchQuery.value.toLowerCase();
  return employees.value.filter(
    (employee) =>
      (employee.full_name?.toLowerCase().includes(query) ||
      employee.email?.toLowerCase().includes(query))
  );
});

const totalPages = computed(() => Math.ceil(filteredEmployees.value.length / itemsPerPage));

const paginatedEmployees = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  return filteredEmployees.value.slice(start, start + itemsPerPage);
});

const openAddModal = () => {
  isEditMode.value = false;
  staffForm.value = {
    employee_id: null,
    full_name: '',
    gender: '',
    email: '',
    phone: '',
    address: '',
    department_id: null,
    birth_date: '',
    start_date: '',
  };
  errors.value = {};
  const modal = new bootstrap.Modal(document.getElementById('staffModal'));
  modal.show();
};

const openEditModal = (employee) => {
  isEditMode.value = true;
  staffForm.value = {
    employee_id: employee.employee_id,
    full_name: employee.full_name || '',
    gender: employee.gender || '',
    email: employee.email || '',
    phone: employee.phone || '',
    address: employee.address || '',
    department_id: employee.department_id || null,
    birth_date: employee.birth_date || '',
    start_date: employee.start_date || '',
  };
  errors.value = {};
  const modal = new bootstrap.Modal(document.getElementById('staffModal'));
  modal.show();
};

const openDetailModal = (employee) => {
  selectedEmployee.value = employee;
  const modal = new bootstrap.Modal(document.getElementById('detailModal'));
  modal.show();
};

const addEmployee = async () => {
  try {
    errors.value = {};
    const response = await axios.post(`${apiUrl}/api/employees`, staffForm.value, {
      withCredentials: true,
      headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
    });
    await fetchEmployees();
    await fetchDepartments();
    window.dispatchEvent(new CustomEvent('refresh-departments'));
    showNotification('Thêm nhân viên thành công!');
    bootstrap.Modal.getInstance(document.getElementById('staffModal')).hide();
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors;
      showNotification('Thêm nhân viên thất bại: Vui lòng kiểm tra dữ liệu.', 'error');
    } else {
      showNotification('Thêm nhân viên thất bại.', 'error');
    }
  }
};

const updateEmployee = async () => {
  try {
    errors.value = {};
    const response = await axios.put(`${apiUrl}/api/employees/${staffForm.value.employee_id}`, staffForm.value, {
      withCredentials: true,
      headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
    });
    await fetchEmployees();
    await fetchDepartments();
    window.dispatchEvent(new CustomEvent('refresh-departments'));
    showNotification('Cập nhật nhân viên thành công!');
    bootstrap.Modal.getInstance(document.getElementById('staffModal')).hide();
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors;
      showNotification('Cập nhật nhân viên thất bại: Vui lòng kiểm tra dữ liệu.', 'error');
    } else {
      showNotification('Cập nhật nhân viên thất bại.', 'error');
    }
  }
};

const deleteEmployee = async (id) => {
  if (!confirm('Bạn có chắc chắn muốn xóa nhân viên này không?')) return;
  try {
    await axios.delete(`${apiUrl}/api/employees/${id}`, {
      withCredentials: true,
      headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
    });
    selectedEmployees.value = selectedEmployees.value.filter((empId) => empId !== id);
    await fetchEmployees();
    await fetchDepartments();
    window.dispatchEvent(new CustomEvent('refresh-departments'));
    if (paginatedEmployees.value.length === 0 && currentPage.value > 1) {
      currentPage.value--;
    }
    showNotification('Xóa nhân viên thành công!');
  } catch (error) {
    showNotification('Xóa nhân viên thất bại.', 'error');
  }
};

const deleteSelectedEmployees = async () => {
  if (!selectedEmployees.value.length || !confirm(`Bạn có chắc muốn xóa ${selectedEmployees.value.length} nhân viên?`)) return;
  try {
    await Promise.all(
      selectedEmployees.value.map((id) =>
        axios.delete(`${apiUrl}/api/employees/${id}`, {
          withCredentials: true,
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
        })
      )
    );
    selectedEmployees.value = [];
    selectAll.value = false;
    await fetchEmployees();
    await fetchDepartments();
    window.dispatchEvent(new CustomEvent('refresh-departments'));
    if (paginatedEmployees.value.length === 0 && currentPage.value > 1) {
      currentPage.value--;
    }
    showNotification('Xóa nhân viên thành công!');
  } catch (error) {
    showNotification('Xóa nhân viên thất bại.', 'error');
  }
};

onMounted(async () => {
  await fetchDepartments();
  await fetchEmployees();
});

watch(searchQuery, () => {
  currentPage.value = 1;
  fetchEmployees();
});
</script>

<style scoped>
.staff-container {
  padding: 20px;
}
.header-section .d-flex {
  gap: 15px;
}
.search-form {
  position: relative;
  width: 100%;
  max-width: 300px;
}
.search-form input {
  padding-right: 40px;
  width: 100%;
  box-sizing: border-box;
}
.search-form .search-icon {
  position: absolute;
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
  line-height: 1.5;
  vertical-align: middle;
}
.table td {
  color: #666;
  white-space: nowrap;
  padding: 10px 15px;
  overflow: hidden;
  text-overflow: ellipsis;
  line-height: 1.5;
  vertical-align: middle;
}
.table td:last-child {
  text-align: center;
  min-width: 120px; /* Đảm bảo đủ chỗ cho các nút hành động */
}
.table th:first-child,
.table td:first-child {
  display: flex;
  justify-content: center;
  align-items: center;
  min-width: 30px;
  padding: 10px;
  height: 40px;
}
.table th .form-check-input,
.table td .form-check-input {
  width: 18px;
  height: 18px;
  margin: 0;
  transform: scale(1.2);
  cursor: pointer;
}
.table th:first-child:hover,
.table td:first-child:hover {
  background-color: transparent;
}
.table-hover tbody tr:hover td:first-child {
  background-color: transparent;
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
  opacity: 0.5;
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
  height: 8px;
}
.custom-scroll::-webkit-scrollbar-track {
  background: #f1f3f5;
  border-radius: 4px;
}
.custom-scroll::-webkit-scrollbar-thumb {
  background: #16B978;
  border-radius: 4px;
}
.custom-scroll::-webkit-scrollbar-thumb:hover {
  background: #13a567;
}
.custom-scroll {
  scrollbar-width: thin;
  scrollbar-color: #16B978 #f1f3f5;
}
table {
  width: 100%;
  min-width: 600px;
}
.custom-alert {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 1050;
  min-width: 300px;
  padding: 15px 20px;
  border-radius: 8px;
  box-shadow: 0 4px 8px rgba(0, 0,0, 0.1);
  animation: fadeIn 0.3s ease-in, fadeOut 0.5s ease-out 2s forwards;
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
  font-size: 20px;
  line-height: 1;
  color: inherit;
  opacity: 0.7;
  transition: opacity 0.3s;
}
.custom-alert .close-btn:hover {
  opacity: 1;
}
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-20px);
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
  transform: translateY(-20px);
}
}
@media (max-width: 768px) {
  .staff-container {
    padding: 10px;
  }
  .table th,
  .table td {
    padding: 8px;
    font-size: 14px;
  }
  .header-section .d-flex {
    gap: 10px;
    flex-wrap: row wrap;
  }
  .search-form {
    width: 100%;
    max-width: 100%;
  }
  .search-form input {
    width: 100%;
  }
  .btn {
    width: auto;
    padding: 6px 12px;
    font-size: 14px;
  }
  .pagination-controls .btn {
    padding: 6px 12px;
    font-size: 14px;
  }
}
  .pagination-controls span {
    font-size: 14px;
  }
  .custom-alert {
    min-width: 250px;
    right: 10px;
    top: 10px;
  }
  table {
    min-width: 500px;
  }
  .table th .form-check-input,
  .table td .form-check-input {
    width: 16px;
    height: 16px;
    transform: scale(1.1); /* Nhỏ hơn trên mobile */
  }

</style>