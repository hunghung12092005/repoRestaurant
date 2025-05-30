<template>
  <div class="staff-container">
    <!-- Thông báo -->
    <div v-if="showAlert" :class="['alert', alertType, 'custom-alert']" role="alert">
      {{ alertMessage }}
    </div>

    <div class="header-section mb-4">
      <h3 class="fw-bold">Quản lý Nhân viên</h3>
      <div class="d-flex justify-content-between align-items-center mt-3">
        <div class="search-form position-relative">
          <input
            type="text"
            class="form-control"
            v-model="searchQuery"
            placeholder="Tìm kiếm theo tên, email hoặc chức danh..."
            @input="filterEmployees"
          />
          <i class="bi bi-search search-icon position-absolute"></i>
        </div>
        <div class="d-flex align-items-center gap-2">
          <button v-if="selectedEmployees.length > 0" class="btn btn-danger" @click="deleteSelectedEmployees" title="Xóa nhân viên đã chọn">
            <i class="bi bi-trash me-2"></i>Xóa
          </button>
          <button class="btn btn-primary" @click="openAddModal">
            <i class="bi bi-plus-circle me-2"></i>Thêm Nhân viên Mới
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
                <th scope="col">
                  <input
                    type="checkbox"
                    class="form-check-input"
                    v-model="selectAll"
                    @change="toggleSelectAll"
                  />
                </th>
                <th scope="col">Họ và Tên</th>
                <th scope="col">Giới tính</th>
                <th scope="col">Email</th>
                <th scope="col">Số điện thoại</th>
                <th scope="col">Chức danh</th>
                <th scope="col">Phòng ban</th>
                <th scope="col">Hành động</th>
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
                <td>{{ employee.name }}</td>
                <td>{{ formatGender(employee.gender) }}</td>
                <td>{{ employee.email }}</td>
                <td>{{ employee.phone || 'Không có' }}</td>
                <td>{{ employee.position || 'Không có' }}</td>
                <td>{{ employee.department ? employee.department.name : 'Không có' }}</td>
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
                <td colspan="8" class="text-center text-muted">Không tìm thấy nhân viên</td>
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

    <div class="modal fade" id="staffModal" tabindex="-1" aria-labelledby="staffModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="staffModalLabel">{{ isEditMode ? 'Chỉnh sửa Nhân viên' : 'Thêm Nhân viên Mới' }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="isEditMode ? updateEmployee() : addEmployee()">
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="name" class="form-label">Họ và Tên</label>
                  <input type="text" class="form-control" v-model="staffForm.name" required />
                  <div v-if="errors.name" class="text-danger">{{ errors.name.join('; ') }}</div>
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
                  <select class="form-select" v-model="staffForm.gender">
                    <option value="">Chọn giới tính</option>
                    <option value="Male">Nam</option>
                    <option value="Female">Nữ</option>
                    <option value="Other">Khác</option>
                  </select>
                  <div v-if="errors.gender" class="text-danger">{{ errors.gender.join('; ') }}</div>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="birth_date" class="form-label">Ngày sinh</label>
                  <input type="date" class="form-control" v-model="staffForm.birth_date" />
                  <div v-if="errors.birth_date" class="text-danger">{{ errors.birth_date.join('; ') }}</div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="phone" class="form-label">Số điện thoại</label>
                  <input type="text" class="form-control" v-model="staffForm.phone" />
                  <div v-if="errors.phone" class="text-danger">{{ errors.phone.join('; ') }}</div>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="position" class="form-label">Chức danh</label>
                  <input type="text" class="form-control" v-model="staffForm.position" />
                  <div v-if="errors.position" class="text-danger">{{ errors.position.join('; ') }}</div>
                </div>
              </div>
              <div class="row">
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
                <div class="col-md-6 mb-3">
                  <label for="salary" class="form-label">Lương</label>
                  <input type="number" class="form-control" v-model.number="staffForm.salary" step="0.01" />
                  <div v-if="errors.salary" class="text-danger">{{ errors.salary.join('; ') }}</div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="hire_date" class="form-label">Ngày tuyển dụng</label>
                  <input type="date" class="form-control" v-model="staffForm.hire_date" />
                  <div v-if="errors.hire_date" class="text-danger">{{ errors.hire_date.join('; ') }}</div>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="status" class="form-label">Trạng thái</label>
                  <select class="form-select" v-model="staffForm.status">
                    <option value="">Chọn trạng thái</option>
                    <option value="Active">Đang làm việc</option>
                    <option value="Inactive">Nghỉ việc</option>
                    <option value="On Leave">Nghỉ phép</option>
                  </select>
                  <div v-if="errors.status" class="text-danger">{{ errors.status.join('; ') }}</div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-12 mb-3">
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
                <strong>Họ và Tên:</strong> {{ selectedEmployee.name }}
              </div>
              <div class="col-md-6 mb-3">
                <strong>Email:</strong> {{ selectedEmployee.email }}
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
                <strong>Chức danh:</strong> {{ selectedEmployee.position || 'Không có' }}
              </div>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <strong>Phòng ban:</strong> {{ selectedEmployee.department ? selectedEmployee.department.name : 'Không có' }}
              </div>
              <div class="col-md-6 mb-3">
                <strong>Lương:</strong> {{ selectedEmployee.salary ? selectedEmployee.salary.toLocaleString() : 'Không có' }}
              </div>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <strong>Ngày tuyển dụng:</strong> {{ formatDate(selectedEmployee.hire_date) || 'Không có' }}
              </div>
              <div class="col-md-6 mb-3">
                <strong>Trạng thái:</strong> {{ selectedEmployee.status || 'Không có' }}
              </div>
            </div>
            <div class="row">
              <div class="col-md-12 mb-3">
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
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import { inject } from 'vue';
import * as bootstrap from 'bootstrap';

const debounce = (func, wait) => {
  let timeout = null;
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
const staffForm = ref({
  name: '',
  gender: '',
  email: '',
  phone: '',
  address: '',
  position: '',
  department_id: null,
  salary: null,
  hire_date: '',
  status: ''
});
const selectedEmployee = ref({});
const isEditMode = ref(false);
const selectedEmployees = ref([]);
const selectAll = ref(false);
const errors = ref({});

// Biến trạng thái cho thông báo
const showAlert = ref(false);
const alertMessage = ref('');
const alertType = ref('alert-success');

// Hàm định dạng ngày thành ngày/tháng/năm
const formatDate = (dateStr) => {
  if (!dateStr) return '';
  
  // Giả sử dateStr có định dạng dd/mm/yyyy (ví dụ: "31/12/2023")
  const regex = /^(\d{2})\/(\d{2})\/(\d{4})$/;
  if (regex.test(dateStr)) {
    const [, day, month, year] = dateStr.match(regex);
    const standardizedDate = `${year}-${month}-${day}`;
    const date = new Date(standardizedDate);
    if (!isNaN(date.getTime())) {
      return date.toLocaleDateString('vi-VN', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
      });
    }
  }
  
  return dateStr || 'Không có';
};

// Hàm chuyển đổi định dạng ngày từ YYYY-MM-DD sang dd/mm/yyyy cho API
const formatDateForApi = (dateStr) => {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  if (!isNaN(date.getTime())) {
    return date.toLocaleDateString('vi-VN', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric'
    });
  }
  return dateStr;
};

// Hàm chuyển đổi định dạng ngày từ dd/mm/yyyy sang YYYY-MM-DD cho input type="date"
const formatDateForInput = (dateStr) => {
  if (!dateStr) return '';
  const regex = /^(\d{2})\/(\d{2})\/(\d{4})$/;
  if (regex.test(dateStr)) {
    const [, day, month, year] = dateStr.match(regex);
    return `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`;
  }
  return dateStr;
};

// Hàm chuyển đổi giới tính sang tiếng Việt
const formatGender = (gender) => {
  if (!gender) return '';
  const genderMap = {
    'Male': 'Nam',
    'Female': 'Nữ',
    'Other': 'Khác'
  };
  return genderMap[gender] || gender;
};

// Hàm hiển thị thông báo
const showNotification = (message, type = 'success') => {
  alertType.value = type === 'success' ? 'alert-success' : 'alert-danger';
  alertMessage.value = message;
  showAlert.value = true;
  setTimeout(() => {
    showAlert.value = false;
  }, 3000);
};

// Hàm chọn/tắt chọn tất cả
const toggleSelectAll = () => {
  if (selectAll.value) {
    selectedEmployees.value = paginatedEmployees.value.map(emp => emp.employee_id);
  } else {
    selectedEmployees.value = [];
  }
};

// Hàm cập nhật trạng thái checkbox "Chọn tất cả"
const updateSelectAll = () => {
  selectAll.value = paginatedEmployees.value.length > 0 &&
    selectedEmployees.value.length === paginatedEmployees.value.length &&
    paginatedEmployees.value.every(emp => selectedEmployees.value.includes(emp.employee_id));
};

// Hàm xóa các nhân viên được chọn
const deleteSelectedEmployees = async () => {
  if (selectedEmployees.value.length === 0) return;
  
  if (confirm(`Bạn có chắc chắn muốn xóa ${selectedEmployees.value.length} nhân viên đã chọn không?`)) {
    try {
      await Promise.all(selectedEmployees.value.map(id =>
        axios.delete(`${apiUrl}/api/employees/${id}`, {
          withCredentials: true,
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`
          }
        })
      ));
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
      console.error('Lỗi khi xóa nhân viên:', error.response ? error.response.data : error.message);
      showNotification(`Xóa nhân viên thất bại: ${error.response?.data?.message || error.message}`, 'error');
    }
  }
};

onMounted(async () => {
  await fetchDepartments();
  await fetchEmployees();
});

const fetchEmployees = async () => {
  try {
    const response = await axios.get(`${apiUrl}/api/employees`, {
      params: { q: searchQuery.value },
      withCredentials: true,
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`
      }
    });
    employees.value = response.data;
    selectedEmployees.value = [];
    selectAll.value = false;
    console.log('Dữ liệu nhân viên từ API:', JSON.stringify(employees.value, null, 2));
  } catch (error) {
    console.error('Lỗi khi tải danh sách nhân viên:', error.response ? error.response.data : error.message);
    showNotification('Không thể tải danh sách nhân viên.', 'error');
  }
};

const fetchDepartments = async () => {
  try {
    const response = await axios.get(`${apiUrl}/api/departments`, {
      withCredentials: true,
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`
      }
    });
    departments.value = response.data;
    console.log('Dữ liệu phòng ban:', JSON.stringify(departments.value, null, 2));
  } catch (error) {
    console.error('Lỗi khi tải danh sách phòng ban:', error.response ? error.response.data : error.message);
    showNotification('Không thể tải danh sách phòng ban.', 'error');
  }
};

const filteredEmployees = computed(() => {
  if (!searchQuery.value) return employees.value;
  const query = searchQuery.value.toLowerCase();
  return employees.value.filter(employee => {
    const matchesName = employee.name?.toLowerCase().includes(query);
    const matchesEmail = employee.email?.toLowerCase().includes(query);
    const matchesPosition = employee.position?.toLowerCase().includes(query);
    return matchesName || matchesEmail || matchesPosition;
  });
});

const filterEmployees = debounce(() => {
  currentPage.value = 1;
  fetchEmployees();
}, 300);

const totalPages = computed(() => {
  return Math.ceil(filteredEmployees.value.length / itemsPerPage);
});

const paginatedEmployees = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  const end = start + itemsPerPage;
  return filteredEmployees.value.slice(start, end);
});

const openAddModal = () => {
  isEditMode.value = false;
  staffForm.value = {
    name: '',
    gender: '',
    email: '',
    phone: '',
    address: '',
    position: '',
    department_id: null,
    salary: null,
    hire_date: '',
    status: ''
  };
  errors.value = {}; // Reset lỗi
  const modal = new bootstrap.Modal(document.getElementById('staffModal'));
  modal.show();
};

const openEditModal = (employee) => {
  isEditMode.value = true;
  staffForm.value = {
    employee_id: employee.employee_id,
    name: employee.name || '',
    gender: employee.gender || '',
    email: employee.email || '',
    phone: employee.phone || '',
    address: employee.address || '',
    position: employee.position || '',
    department_id: employee.department_id || null,
    salary: employee.salary || null,
    birth_date: employee.birth_date ? formatDateForInput(employee.birth_date) : '',
    hire_date: employee.hire_date ? formatDateForInput(employee.hire_date) : '',
    status: employee.status || ''
  };
  errors.value = {}; // Reset lỗi
  console.log('Dữ liệu gán vào form:', JSON.stringify(staffForm.value, null, 2));
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
    errors.value = {}; // Reset lỗi
    // Kiểm tra dữ liệu trước khi gửi
    if (!staffForm.value.name) throw new Error('Họ và tên là bắt buộc');
    if (!staffForm.value.email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(staffForm.value.email)) {
      throw new Error('Email không hợp lệ');
    }
    if (staffForm.value.department_id && !departments.value.find(d => d.department_id === staffForm.value.department_id)) {
      throw new Error('Phòng ban không hợp lệ');
    }

    // Tạo object với định dạng ngày tháng đúng
    const formattedForm = { ...staffForm.value };
    if (formattedForm.birth_date) {
      formattedForm.birth_date = formatDateForApi(formattedForm.birth_date);
    }
    if (formattedForm.hire_date) {
      formattedForm.hire_date = formatDateForApi(formattedForm.hire_date);
    }

    console.log('Dữ liệu gửi lên:', JSON.stringify(formattedForm, null, 2));
    
    // Sử dụng formattedForm thay vì staffForm.value
    const response = await axios.post(`${apiUrl}/api/employees`, formattedForm, {
      withCredentials: true,
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`
      }
    });

    console.log('Phản hồi từ server:', JSON.stringify(response.data, null, 2));
    
    await fetchEmployees();
    await fetchDepartments();
    window.dispatchEvent(new CustomEvent('refresh-departments'));
    showNotification('Thêm nhân viên thành công!');
    const modal = bootstrap.Modal.getInstance(document.getElementById('staffModal'));
    modal.hide();
  } catch (error) {
    console.error('Lỗi khi thêm nhân viên:', error.response ? error.response.data : error.message);
    let errorMessage = 'Thêm nhân viên thất bại.';
    if (error.response?.status === 422 && error.response.data.errors) {
      errors.value = error.response.data.errors; // Lưu lỗi vào biến errors
      errorMessage = Object.values(error.response.data.errors).flat().join('; ');
    } else {
      errorMessage = error.response?.data?.message || error.message;
    }
    showNotification(`Thêm nhân viên thất bại: ${errorMessage}`, 'error');
  }
};

const updateEmployee = async () => {
  try {
    errors.value = {}; // Reset lỗi
    if (!staffForm.value.name) throw new Error('Họ và tên là bắt buộc');
    if (!staffForm.value.email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(staffForm.value.email)) {
      throw new Error('Email không hợp lệ');
    }

    const formattedForm = { ...staffForm.value };
    if (formattedForm.birth_date) {
      formattedForm.birth_date = formatDateForApi(formattedForm.birth_date);
    }
    if (formattedForm.hire_date) {
      formattedForm.hire_date = formatDateForApi(formattedForm.hire_date);
    }

    console.log('Dữ liệu cập nhật:', JSON.stringify(formattedForm, null, 2));
    
    const response = await axios.put(`${apiUrl}/api/employees/${staffForm.value.employee_id}`, formattedForm, {
      withCredentials: true,
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`
      }
    });

    console.log('Phản hồi từ server:', JSON.stringify(response.data, null, 2));
    
    await fetchEmployees();
    await fetchDepartments();
    window.dispatchEvent(new CustomEvent('refresh-departments'));
    showNotification('Cập nhật nhân viên thành công!');
    const modal = bootstrap.Modal.getInstance(document.getElementById('staffModal'));
    modal.hide();
  } catch (error) {
    console.error('Lỗi khi cập nhật nhân viên:', error.response ? error.response.data : error.message);
    let errorMessage = 'Cập nhật nhân viên thất bại.';
    if (error.response?.status === 422 && error.response.data.errors) {
      errors.value = error.response.data.errors; // Lưu lỗi vào biến errors
      errorMessage = Object.values(error.response.data.errors).flat().join('; ');
    } else {
      errorMessage = error.response?.data?.message || error.message;
    }
    showNotification(`Cập nhật nhân viên thất bại: ${errorMessage}`, 'error');
  }
};

const deleteEmployee = async (id) => {
  if (confirm('Bạn có chắc chắn muốn xóa nhân viên này không?')) {
    try {
      await axios.delete(`${apiUrl}/api/employees/${id}`, {
        withCredentials: true,
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`
        }
      });
      selectedEmployees.value = selectedEmployees.value.filter(empId => empId !== id);
      await fetchEmployees();
      await fetchDepartments();
      window.dispatchEvent(new CustomEvent('refresh-departments'));
      if (paginatedEmployees.value.length === 0 && currentPage.value > 1) {
        currentPage.value--;
      }
      showNotification('Xóa nhân viên thành công!');
    } catch (error) {
      console.error('Lỗi khi xóa nhân viên:', error.response ? error.response.data : error.message);
      showNotification(`Xóa nhân viên thất bại: ${error.response?.data?.message || error.message}`, 'error');
    }
  }
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
  text-align: center;
  min-width: 50px;
}
.table td:last-child {
  text-align: center;
  min-width: 80px;
}
.table td:nth-child(2) {
  min-width: 180px;
}
.table td:nth-child(3) {
  min-width: 80px;
}
.table td:nth-child(4) {
  min-width: 150px;
}
.table td:nth-child(5) {
  min-width: 110px;
}
.table td:nth-child(6) {
  min-width: 140px;
}
.table td:nth-child(7) {
  min-width: 140px;
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
.btn-danger {
  background-color: #dc3545;
  border-color: #dc3545;
}
.btn-danger:hover {
  background-color: #c82333;
  border-color: #c82333;
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
.table-responsive.custom-scroll table {
  width: 100%;
  min-width: 780px;
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
  .btn-primary, .btn-danger {
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
  .table-responsive.custom-scroll table {
    min-width: 580px;
  }
}
</style>
