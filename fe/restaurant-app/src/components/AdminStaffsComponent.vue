<template>
  <div class="staff-container">
    <!-- Header Section -->
    <div class="header-section mb-4">
      <h3 class="fw-bold">Quản lý Nhân viên</h3>
      <div class="d-flex justify-content-between align-items-center mt-3">
        <!-- Search Form -->
        <div class="search-form position-relative">
          <input
            type="text"
            class="form-control"
            v-model="searchQuery"
            placeholder="Tìm kiếm theo tên, email, chức danh hoặc phòng ban..."
            @input="filterStaffs"
          />
          <i class="bi bi-search search-icon position-absolute"></i>
        </div>
        <!-- Add New Staff Button -->
        <button class="btn btn-primary" @click="openAddModal">
          <i class="bi bi-plus-circle me-2"></i>Thêm Nhân viên Mới
        </button>
      </div>
    </div>

    <!-- Staff Table -->
    <div class="card shadow-sm rounded-3">
      <div class="card-body p-0">
        <div class="table-responsive custom-scroll">
          <table class="table table-hover table-bordered mb-0">
            <thead class="table-light">
              <tr>
                <th scope="col"></th>
                <th scope="col">Họ và tên</th>
                <th scope="col">Chức danh</th>
                <th scope="col">Số điện thoại</th>
                <th scope="col">Email</th>
                <th scope="col">Ngày gia nhập</th>
                <th scope="col">Địa chỉ</th>
                <th scope="col">Phòng ban</th>
                <th scope="col">Trạng thái</th>
                <th scope="col">Hành động</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="employee in paginatedEmployees" :key="employee.employee_id">
                <td><input type="checkbox" class="form-check-input" /></td>
                <td>{{ employee.name }}</td>
                <td>{{ employee.position }}</td>
                <td>{{ employee.phone }}</td>
                <td>{{ employee.email }}</td>
                <td>{{ formatDate(employee.hire_date) }}</td>
                <td>{{ employee.address }}</td>
                <td>{{ employee.department ? employee.department.name : 'Chưa có' }}</td>
                <td>{{ employee.status === 'Active' ? 'Hoạt động' : employee.status === 'Inactive' ? 'Ngưng hoạt động' : 'Nghỉ phép' }}</td>
                <td>
                  <button class="btn btn-sm btn-outline-primary me-2" @click="openDetailModal(employee)" title="Xem chi tiết">
                    <i class="bi bi-eye"></i>
                  </button>
                  <button class="btn btn-sm btn-outline-warning me-2" @click="openEditModal(employee)" title="Sửa">
                    <i class="bi bi-pencil"></i>
                  </button>
                  <button class="btn btn-sm btn-outline-danger" @click="deleteEmployee(employee.employee_id)" title="Xóa">
                    <i class="bi bi-trash"></i>
                  </button>
                </td>
              </tr>
              <tr v-if="!paginatedEmployees.length">
                <td colspan="10" class="text-center text-muted">Không tìm thấy nhân viên</td>
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
    <div class="modal fade" id="employeeModal" tabindex="-1" aria-labelledby="employeeModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="employeeModalLabel">{{ isEditMode ? 'Chỉnh sửa Nhân viên' : 'Thêm Nhân viên Mới' }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="isEditMode ? updateEmployee() : addEmployee()">
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="name" class="form-label">Họ và tên</label>
                  <input type="text" class="form-control" v-model="employeeForm.name" required />
                </div>
                <div class="col-md-6 mb-3">
                  <label for="email" class="form-label">Email</label>
                  <input type="email" class="form-control" v-model="employeeForm.email" required />
                </div>
              </div>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="phone" class="form-label">Số điện thoại</label>
                  <input type="text" class="form-control" v-model="employeeForm.phone" />
                </div>
                <div class="col-md-6 mb-3">
                  <label for="position" class="form-label">Chức danh</label>
                  <input type="text" class="form-control" v-model="employeeForm.position" required />
                </div>
              </div>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="hire_date" class="form-label">Ngày gia nhập</label>
                  <input type="date" class="form-control" v-model="employeeForm.hire_date" required />
                </div>
                <div class="col-md-6 mb-3">
                  <label for="birth_date" class="form-label">Ngày sinh</label>
                  <input type="date" class="form-control" v-model="employeeForm.birth_date" required />
                </div>
              </div>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="address" class="form-label">Địa chỉ</label>
                  <input type="text" class="form-control" v-model="employeeForm.address" />
                </div>
                <div class="col-md-6 mb-3">
                  <label for="salary" class="form-label">Lương</label>
                  <input type="number" class="form-control" v-model="employeeForm.salary" required />
                </div>
              </div>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="department_id" class="form-label">Phòng ban</label>
                  <select class="form-select" v-model="employeeForm.department_id" required>
                    <option value="" disabled>Chọn phòng ban</option>
                    <option v-for="dept in departments" :key="dept.department_id" :value="dept.department_id">
                      {{ dept.name }}
                    </option>
                  </select>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="status" class="form-label">Trạng thái</label>
                  <select class="form-select" v-model="employeeForm.status" required>
                    <option value="Active">Hoạt động</option>
                    <option value="Inactive">Ngưng hoạt động</option>
                    <option value="On Leave">Nghỉ phép</option>
                  </select>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="gender" class="form-label">Giới tính</label>
                  <select class="form-select" v-model="employeeForm.gender" required>
                    <option value="Male">Nam</option>
                    <option value="Female">Nữ</option>
                    <option value="Other">Khác</option>
                  </select>
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
            <h5 class="modal-title" id="detailModalLabel">Chi tiết Nhân viên</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
          </div>
          <div class="modal-body">
            <div class="row">
              <div class="col-md-6 mb-3">
                <strong>Họ và tên:</strong> {{ selectedEmployee.name }}
              </div>
              <div class="col-md-6 mb-3">
                <strong>Email:</strong> {{ selectedEmployee.email }}
              </div>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <strong>Số điện thoại:</strong> {{ selectedEmployee.phone || 'Không có' }}
              </div>
              <div class="col-md-6 mb-3">
                <strong>Chức danh:</strong> {{ selectedEmployee.position }}
              </div>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <strong>Ngày gia nhập:</strong> {{ formatDate(selectedEmployee.hire_date) }}
              </div>
              <div class="col-md-6 mb-3">
                <strong>Ngày sinh:</strong> {{ formatDate(selectedEmployee.birth_date) }}
              </div>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <strong>Địa chỉ:</strong> {{ selectedEmployee.address || 'Không có' }}
              </div>
              <div class="col-md-6 mb-3">
                <strong>Lương:</strong> {{ formatCurrency(selectedEmployee.salary) }}
              </div>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <strong>Phòng ban:</strong> {{ selectedEmployee.department ? selectedEmployee.department.name : 'Chưa có' }}
              </div>
              <div class="col-md-6 mb-3">
                <strong>Trạng thái:</strong> {{ selectedEmployee.status === 'Active' ? 'Hoạt động' : selectedEmployee.status === 'Inactive' ? 'Ngưng hoạt động' : 'Nghỉ phép' }}
              </div>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <strong>Giới tính:</strong> {{ selectedEmployee.gender === 'Male' ? 'Nam' : selectedEmployee.gender === 'Female' ? 'Nữ' : 'Khác' }}
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
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import { inject } from 'vue';
import * as bootstrap from 'bootstrap';

// Simple debounce function to prevent excessive API calls
const debounce = (func, wait) => {
  let timeout;
  return (...args) => {
    clearTimeout(timeout);
    timeout = setTimeout(() => func(...args), wait);
  };
};

const employees = ref([]);
const departments = ref([]);
const searchQuery = ref('');
const currentPage = ref(1);
const itemsPerPage = 10;
const apiUrl = inject('apiUrl');
const employeeForm = ref({
  name: '',
  email: '',
  phone: '',
  position: '',
  hire_date: '',
  birth_date: '',
  address: '',
  salary: '',
  department_id: '',
  status: 'Active',
  gender: 'Male',
});
const selectedEmployee = ref({});
const isEditMode = ref(false);

// Debug API URL
console.log('API URL:', apiUrl);

// Fetch employees and departments on mount
onMounted(async () => {
  await fetchEmployees();
  await fetchDepartments();
});

const fetchEmployees = async () => {
  try {
    const response = await axios.get(`${apiUrl}/api/employees`, {
      params: { q: searchQuery.value }
    });
    employees.value = response.data;
    console.log('Dữ liệu nhân viên từ API:', employees.value);
  } catch (error) {
    console.error('Lỗi khi lấy dữ liệu nhân viên:', error);
    alert('Không thể tải danh sách nhân viên. Vui lòng kiểm tra API.');
  }
};

const fetchDepartments = async () => {
  try {
    const response = await axios.get(`${apiUrl}/api/departments`);
    departments.value = response.data;
    console.log('Danh sách phòng ban:', departments.value);
  } catch (error) {
    console.error('Lỗi khi lấy dữ liệu phòng ban:', error);
    alert('Không thể tải danh sách phòng ban. Vui lòng kiểm tra API.');
  }
};

// Filter employees based on single search query (fallback for frontend filtering)
const filteredEmployees = computed(() => {
  if (!searchQuery.value) return employees.value;
  const query = searchQuery.value.toLowerCase();
  return employees.value.filter(employee => {
    const matchesName = employee.name.toLowerCase().includes(query);
    const matchesEmail = employee.email.toLowerCase().includes(query);
    const matchesPosition = employee.position.toLowerCase().includes(query);
    const matchesDepartment = employee.department && employee.department.name
      ? employee.department.name.toLowerCase().includes(query)
      : false;
    return matchesName || matchesEmail || matchesPosition || matchesDepartment;
  });
});

// Reset to page 1 and fetch employees when search changes
const filterStaffs = debounce(() => {
  currentPage.value = 1;
  fetchEmployees();
}, 300);

// Pagination logic
const totalPages = computed(() => {
  return Math.ceil(filteredEmployees.value.length / itemsPerPage);
});

const paginatedEmployees = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  const end = start + itemsPerPage;
  return filteredEmployees.value.slice(start, end);
});

// Modal functions
const openAddModal = () => {
  isEditMode.value = false;
  employeeForm.value = {
    name: '',
    email: '',
    phone: '',
    position: '',
    hire_date: '',
    birth_date: '',
    address: '',
    salary: '',
    department_id: '',
    status: 'Active',
    gender: 'Male',
  };
  const modal = new bootstrap.Modal(document.getElementById('employeeModal'));
  modal.show();
};

const openEditModal = (employee) => {
  isEditMode.value = true;
  employeeForm.value = { ...employee };
  const modal = new bootstrap.Modal(document.getElementById('employeeModal'));
  modal.show();
};

const openDetailModal = (employee) => {
  selectedEmployee.value = employee;
  const modal = new bootstrap.Modal(document.getElementById('detailModal'));
  modal.show();
};

// CRUD operations
const addEmployee = async () => {
  try {
    console.log('Dữ liệu gửi lên:', employeeForm.value);
    const response = await axios.post(`${apiUrl}/api/employees`, employeeForm.value);
    console.log('Phản hồi từ server:', response.data);
    await fetchEmployees();
    alert('Thêm nhân viên thành công!');
    const modal = bootstrap.Modal.getInstance(document.getElementById('employeeModal'));
    modal.hide();
  } catch (error) {
    console.error('Lỗi khi thêm nhân viên:', error.response ? error.response.data : error.message);
    alert('Thêm nhân viên thất bại.');
  }
};

const updateEmployee = async () => {
  try {
    console.log('Dữ liệu gửi lên:', employeeForm.value);
    const response = await axios.put(`${apiUrl}/api/employees/${employeeForm.value.employee_id}`, employeeForm.value);
    console.log('Phản hồi từ server:', response.data);
    await fetchEmployees();
    alert('Cập nhật nhân viên thành công!');
    const modal = bootstrap.Modal.getInstance(document.getElementById('employeeModal'));
    modal.hide();
  } catch (error) {
    console.error('Lỗi khi cập nhật nhân viên:', error.response ? error.response.data : error.message);
    alert('Cập nhật nhân viên thất bại.');
  }
};

const deleteEmployee = async (id) => {
  if (confirm('Bạn có chắc chắn muốn xóa nhân viên này không?')) {
    try {
      console.log('ID xóa:', id);
      const response = await axios.delete(`${apiUrl}/api/employees/${id}`);
      console.log('Phản hồi từ server:', response.data);
      await fetchEmployees();
      if (paginatedEmployees.value.length === 0 && currentPage.value > 1) {
        currentPage.value--;
      }
      alert('Xóa nhân viên thành công!');
    } catch (error) {
      console.error('Lỗi khi xóa nhân viên:', error.response ? error.response.data : error.message);
      alert('Xóa nhân viên thất bại.');
    }
  }
};

// Format functions
const formatDate = (date) => {
  return date ? new Date(date).toLocaleDateString('vi-VN') : '';
};

const formatCurrency = (amount) => {
  return amount ? new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount) : '0 VND';
};
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

.bi-telephone-fill { color: #28a745; }
.bi-envelope-fill { color: #dc3545; }
.bi-geo-alt-fill { color: #007bff; }

.bi-telephone-fill:hover,
.bi-envelope-fill:hover,
.bi-geo-alt-fill:hover {
  opacity: 0.8;
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
  min-width: 1200px;
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

  table {
    min-width: 800px;
  }
}
</style>