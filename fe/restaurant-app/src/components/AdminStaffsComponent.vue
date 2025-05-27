<template>
  <div class="staff-container">
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
        <button class="btn btn-primary" @click="openAddModal">
          <i class="bi bi-plus-circle me-2"></i>Thêm Nhân viên Mới
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
                <th scope="col">Họ và Tên</th>
                <th scope="col">Giới tính</th>
                <th scope="col">Ngày sinh</th>
                <th scope="col">Email</th>
                <th scope="col">Số điện thoại</th>
                <th scope="col">Chức danh</th>
                <th scope="col">Phòng ban</th>
                <th scope="col">Hành động</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="employee in paginatedEmployees" :key="employee.employee_id">
                <td><input type="checkbox" class="form-check-input" /></td>
                <td>{{ employee.name }}</td>
                <td>{{ employee.gender || 'Không có' }}</td>
                <td>{{ employee.birth_date || 'Không có' }}</td>
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
                <td colspan="9" class="text-center text-muted">Không tìm thấy nhân viên</td>
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
                </div>
                <div class="col-md-6 mb-3">
                  <label for="email" class="form-label">Email</label>
                  <input type="email" class="form-control" v-model="staffForm.email" required />
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
                </div>
                <div class="col-md-6 mb-3">
                  <label for="birth_date" class="form-label">Ngày sinh</label>
                  <input type="date" class="form-control" v-model="staffForm.birth_date" />
                </div>
              </div>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="phone" class="form-label">Số điện thoại</label>
                  <input type="text" class="form-control" v-model="staffForm.phone" />
                </div>
                <div class="col-md-6 mb-3">
                  <label for="position" class="form-label">Chức danh</label>
                  <input type="text" class="form-control" v-model="staffForm.position" />
                </div>
              </div>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="department_id" class="form-label">Phòng ban</label>
                  <select class="form-select" v-model.number="staffForm.department_id">
                    <option value="">Chọn phòng ban</option>
                    <option v-for="department in departments" :key="department.department_id" :value="department.department_id">
                      {{ department.name }}
                    </option>
                  </select>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="salary" class="form-label">Lương</label>
                  <input type="number" class="form-control" v-model.number="staffForm.salary" step="0.01" />
                </div>
              </div>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="hire_date" class="form-label">Ngày tuyển dụng</label>
                  <input type="date" class="form-control" v-model="staffForm.hire_date" />
                </div>
                <div class="col-md-6 mb-3">
                  <label for="status" class="form-label">Trạng thái</label>
                  <select class="form-select" v-model="staffForm.status">
                    <option value="">Chọn trạng thái</option>
                    <option value="Active">Đang làm việc</option>
                    <option value="Inactive">Nghỉ việc</option>
                    <option value="On Leave">Nghỉ phép</option>
                  </select>
                </div>
              </div>
              <div class="row">
                <div class="col-md-12 mb-3">
                  <label for="address" class="form-label">Địa chỉ</label>
                  <textarea class="form-control" v-model="staffForm.address" rows="4"></textarea>
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
                <strong>Giới tính:</strong> {{ selectedEmployee.gender || 'Không có' }}
              </div>
              <div class="col-md-6 mb-3">
                <strong>Ngày sinh:</strong> {{ selectedEmployee.birth_date || 'Không có' }}
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
                <strong>Ngày tuyển dụng:</strong> {{ selectedEmployee.hire_date || 'Không có' }}
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
    console.log('Dữ liệu nhân viên từ API:', JSON.stringify(employees.value, null, 2));
  } catch (error) {
    console.error('Lỗi khi tải danh sách nhân viên:', error.response ? error.response.data : error.message);
    alert('Không thể tải danh sách nhân viên.');
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
    alert('Không thể tải danh sách phòng ban.');
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
  const modal = new bootstrap.Modal(document.getElementById('staffModal'));
  modal.show();
};

const openEditModal = (employee) => {
  isEditMode.value = true;
  staffForm.value = {
    ...employee,
    department_id: employee.department_id || null,
    salary: employee.salary || null
  };
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
    // Kiểm tra dữ liệu trước khi gửi
    if (!staffForm.value.name) throw new Error('Họ và tên là bắt buộc');
    if (!staffForm.value.email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(staffForm.value.email)) {
      throw new Error('Email không hợp lệ');
    }
    if (staffForm.value.department_id && !departments.value.find(d => d.department_id === staffForm.value.department_id)) {
      throw new Error('Phòng ban không hợp lệ');
    }

    console.log('Dữ liệu gửi lên:', JSON.stringify(staffForm.value, null, 2));
    
    const response = await axios.post(`${apiUrl}/api/employees`, staffForm.value, {
      withCredentials: true,
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`
      }
    });

    console.log('Phản hồi từ server:', JSON.stringify(response.data, null, 2));
    
    await fetchEmployees();
    window.dispatchEvent(new CustomEvent('refresh-departments'));
    alert('Thêm nhân viên thành công!');
    const modal = bootstrap.Modal.getInstance(document.getElementById('staffModal'));
    modal.hide();
  } catch (error) {
    console.error('Lỗi khi thêm nhân viên:', error.response ? error.response.data : error.message);
    await fetchEmployees(); // Làm mới để kiểm tra
    const errorMessage = error.response?.data?.errors
      ? Object.values(error.response.data.errors).flat().join('; ')
      : error.response?.data?.message || error.message;
    alert(`Thêm nhân viên thất bại: ${errorMessage}. Dữ liệu có thể đã được lưu, vui lòng kiểm tra bảng.`);
  }
};

const updateEmployee = async () => {
  try {
    if (!staffForm.value.name) throw new Error('Họ và tên là bắt buộc');
    if (!staffForm.value.email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(staffForm.value.email)) {
      throw new Error('Email không hợp lệ');
    }
    console.log('Dữ liệu cập nhật:', JSON.stringify(staffForm.value, null, 2));
    const response = await axios.put(`${apiUrl}/api/employees/${staffForm.value.employee_id}`, staffForm.value, {
      withCredentials: true,
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`
      }
    });
    console.log('Phản hồi từ server:', JSON.stringify(response.data, null, 2));
    await fetchEmployees();
    window.dispatchEvent(new CustomEvent('refresh-departments'));
    alert('Cập nhật nhân viên thành công!');
    const modal = bootstrap.Modal.getInstance(document.getElementById('staffModal'));
    modal.hide();
  } catch (error) {
    console.error('Lỗi khi cập nhật nhân viên:', error.response ? error.response.data : error.message);
    const errorMessage = error.response?.data?.errors
      ? Object.values(error.response.data.errors).flat().join('; ')
      : error.response?.data?.message || error.message;
    alert(`Cập nhật nhân viên thất bại: ${errorMessage}`);
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
      await fetchEmployees();
      window.dispatchEvent(new CustomEvent('refresh-departments'));
      if (paginatedEmployees.value.length === 0 && currentPage.value > 1) {
        currentPage.value--;
      }
      alert('Xóa nhân viên thành công!');
    } catch (error) {
      console.error('Lỗi khi xóa nhân viên:', error.response ? error.response.data : error.message);
      alert(`Xóa nhân viên thất bại: ${error.response?.data?.message || error.message}`);
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
.search-form input:focus {
  border-color: #16b978;
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
  background-color: #16b978;
  border-color: #16b978;
}
.btn-primary:hover {
  background-color: #13a567;
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
}
.custom-scroll {
  overflow-x: auto;
}
.custom-scroll::-webkit-scrollbar {
  height: 8px;
}
.custom-scroll::-webkit-scrollbar-track {
  background: #f1f3f5;
}
.custom-scroll::-webkit-scrollbar-thumb {
  background: #16b978;
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
  .btn-primary {
    width: 100%;
  }
}
</style>