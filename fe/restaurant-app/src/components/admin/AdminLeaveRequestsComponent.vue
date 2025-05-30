<template>
  <div class="leave-container">
    <div v-if="showAlert" :class="['alert', alertType, 'custom-alert']" role="alert">
      {{ alertMessage }}
    </div>

    <div class="header-section mb-4">
      <h3 class="fw-bold">Quản lý Nghỉ phép</h3>
      <div class="d-flex justify-content-between align-items-center mt-3">
        <div class="search-form position-relative">
          <input
            type="text"
            class="form-control"
            v-model="searchQuery"
            placeholder="Tìm kiếm theo loại nghỉ phép hoặc trạng thái..."
            @input="filterLeaveRequests"
          />
          <i class="bi bi-search search-icon position-absolute"></i>
        </div>
        <button class="btn btn-primary" @click="openAddModal">
          <i class="bi bi-plus-circle me-2"></i>Thêm Yêu cầu Nghỉ phép
        </button>
      </div>
    </div>

    <div class="card shadow-sm rounded-3">
      <div class="card-body p-0">
        <div class="table-responsive custom-scroll">
          <table class="table table-hover table-bordered mb-0">
            <thead class="table-light">
              <tr>
                <th scope="col">Nhân viên</th>
                <th scope="col">Loại nghỉ phép</th>
                <th scope="col">Ngày bắt đầu</th>
                <th scope="col">Ngày kết thúc</th>
                <th scope="col">Trạng thái</th>
                <th scope="col">Lý do</th>
                <th scope="col">Ngày tạo</th>
                <th scope="col">Hành động</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="leave in paginatedLeaveRequests" :key="leave.leave_id">
                <td>{{ leave.employee ? leave.employee.name : 'Không có' }}</td>
                <td>{{ formatLeaveType(leave.leave_type) }}</td>
                <td>{{ formatDate(leave.start_date) }}</td>
                <td>{{ formatDate(leave.end_date) }}</td>
                <td :class="getStatusClass(leave.status)">
                  {{ formatStatus(leave.status) }}
                </td>
                <td>{{ leave.reason || 'Không có' }}</td>
                <td>{{ formatDate(leave.created_at) }}</td>
                <td>
                  <button
                    v-if="leave.status === 'Pending'"
                    class="btn btn-sm btn-outline-primary me-2"
                    @click="approveLeave(leave.leave_id)"
                    title="Duyệt"
                  >
                    <i class="bi bi-check-circle"></i>
                  </button>
                  <button
                    v-if="leave.status === 'Pending'"
                    class="btn btn-sm btn-outline-danger me-2"
                    @click="rejectLeave(leave.leave_id)"
                    title="Từ chối"
                  >
                    <i class="bi bi-x-circle"></i>
                  </button>
                  <button
                    class="btn btn-sm btn-outline-danger"
                    @click="deleteLeave(leave.leave_id)"
                    title="Xóa"
                  >
                    <i class="bi bi-trash"></i>
                  </button>
                </td>
              </tr>
              <tr v-if="!paginatedLeaveRequests.length">
                <td colspan="8" class="text-center text-muted">Không tìm thấy yêu cầu nghỉ phép</td>
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

    <div class="modal fade" id="leaveModal" tabindex="-1" aria-labelledby="leaveModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="leaveModalLabel">Thêm Yêu cầu Nghỉ phép</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="addLeaveRequest">
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="employee_id" class="form-label">Nhân viên</label>
                  <select class="form-select" v-model.number="leaveForm.employee_id" required>
                    <option value="">Chọn nhân viên</option>
                    <option v-for="employee in employees" :key="employee.employee_id" :value="employee.employee_id">
                      {{ employee.name }}
                    </option>
                  </select>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="leave_type" class="form-label">Loại nghỉ phép</label>
                  <select class="form-select" v-model="leaveForm.leave_type" required>
                    <option value="">Chọn loại</option>
                    <option value="Annual">Nghỉ phép năm</option>
                    <option value="Sick">Nghỉ ốm</option>
                    <option value="Maternity">Nghỉ thai sản</option>
                    <option value="Unpaid">Nghỉ không lương</option>
                  </select>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="start_date" class="form-label">Ngày bắt đầu</label>
                  <input type="date" class="form-control" v-model="leaveForm.start_date" required />
                </div>
                <div class="col-md-6 mb-3">
                  <label for="end_date" class="form-label">Ngày kết thúc</label>
                  <input type="date" class="form-control" v-model="leaveForm.end_date" required />
                </div>
              </div>
              <div class="row">
                <div class="col-md-12 mb-3">
                  <label for="reason" class="form-label">Lý do</label>
                  <textarea class="form-control" v-model="leaveForm.reason" rows="4"></textarea>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="submit" class="btn btn-primary">Thêm mới</button>
              </div>
            </form>
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

const leaveRequests = ref([]);
const employees = ref([]);
const searchQuery = ref('');
const currentPage = ref(1);
const itemsPerPage = 10;
const apiUrl = inject('apiUrl');
const leaveForm = ref({
  employee_id: null,
  leave_type: '',
  start_date: '',
  end_date: '',
  reason: '',
});
const showAlert = ref(false);
const alertMessage = ref('');
const alertType = ref('alert-success');

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  return date.toLocaleDateString('vi-VN', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
  });
};

// Hàm chuyển đổi trạng thái sang tiếng Việt
const formatStatus = (status) => {
  const statusMap = {
    Pending: 'Đang chờ',
    Approved: 'Đã duyệt',
    Rejected: 'Bị từ chối',
  };
  console.log('Status:', status); // Debug giá trị status
  return statusMap[status] || status || 'Không xác định';
};

// Hàm chuyển đổi loại nghỉ phép sang tiếng Việt
const formatLeaveType = (leaveType) => {
  const leaveTypeMap = {
    Annual: 'Nghỉ phép năm',
    Sick: 'Nghỉ ốm',
    Maternity: 'Nghỉ thai sản',
    Unpaid: 'Nghỉ không lương',
  };
  console.log('Leave Type:', leaveType); // Debug giá trị leave_type
  return leaveTypeMap[leaveType] || leaveType || 'Không xác định';
};

// Hàm trả về class cho trạng thái
const getStatusClass = (status) => {
  return {
    'status-pending': status === 'Pending',
    'status-approved': status === 'Approved',
    'status-rejected': status === 'Rejected',
  };
};

const showNotification = (message, type = 'success') => {
  alertType.value = type === 'success' ? 'alert-success' : 'alert-danger';
  alertMessage.value = message;
  showAlert.value = true;
  setTimeout(() => {
    showAlert.value = false;
  }, 3000);
};

onMounted(async () => {
  await fetchEmployees();
  await fetchLeaveRequests();
});

const fetchEmployees = async () => {
  try {
    const response = await axios.get(`${apiUrl}/api/employees`, {
      withCredentials: true,
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
      },
    });
    employees.value = response.data;
  } catch (error) {
    console.error('Lỗi khi tải danh sách nhân viên:', error.response ? error.response.data : error.message);
    showNotification('Không thể tải danh sách nhân viên.', 'error');
  }
};

const fetchLeaveRequests = async () => {
  try {
    const response = await axios.get(`${apiUrl}/api/leave-requests`, {
      params: { q: searchQuery.value },
      withCredentials: true,
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
      },
    });
    leaveRequests.value = response.data;
    console.log('Leave Requests:', response.data); // Debug dữ liệu API
  } catch (error) {
    console.error('Lỗi khi tải danh sách yêu cầu nghỉ phép:', error.response ? error.response.data : error.message);
    showNotification('Không thể tải danh sách yêu cầu nghỉ phép.', 'error');
  }
};

const filteredLeaveRequests = computed(() => {
  if (!searchQuery.value) return leaveRequests.value;
  const query = searchQuery.value.toLowerCase();
  return leaveRequests.value.filter((leave) => {
    const matchesType = formatLeaveType(leave.leave_type)?.toLowerCase().includes(query);
    const matchesStatus = formatStatus(leave.status)?.toLowerCase().includes(query);
    const matchesEmployee = leave.employee?.name?.toLowerCase().includes(query);
    return matchesType || matchesStatus || matchesEmployee;
  });
});

const filterLeaveRequests = debounce(() => {
  currentPage.value = 1;
  fetchLeaveRequests();
}, 300);

const totalPages = computed(() => {
  return Math.ceil(filteredLeaveRequests.value.length / itemsPerPage);
});

const paginatedLeaveRequests = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  const end = start + itemsPerPage;
  return filteredLeaveRequests.value.slice(start, end);
});

const openAddModal = () => {
  leaveForm.value = {
    employee_id: null,
    leave_type: '',
    start_date: '',
    end_date: '',
    reason: '',
  };
  const modal = new bootstrap.Modal(document.getElementById('leaveModal'));
  modal.show();
};

const addLeaveRequest = async () => {
  try {
    if (!leaveForm.value.employee_id) throw new Error('Vui lòng chọn nhân viên');
    if (!leaveForm.value.leave_type) throw new Error('Vui lòng chọn loại nghỉ phép');
    if (!leaveForm.value.start_date || !leaveForm.value.end_date) throw new Error('Vui lòng chọn ngày bắt đầu và kết thúc');

    const response = await axios.post(`${apiUrl}/api/leave-requests`, leaveForm.value, {
      withCredentials: true,
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
      },
    });

    await fetchLeaveRequests();
    showNotification('Thêm yêu cầu nghỉ phép thành công!');
    const modal = bootstrap.Modal.getInstance(document.getElementById('leaveModal'));
    modal.hide();
  } catch (error) {
    console.error('Lỗi khi thêm yêu cầu nghỉ phép:', error.response ? error.response.data : error.message);
    const errorMessage = error.response?.data?.errors
      ? Object.values(error.response.data.errors).flat().join('; ')
      : error.response?.data?.message || error.message;
    showNotification(`Thêm yêu cầu nghỉ phép thất bại: ${errorMessage}`, 'error');
  }
};

const approveLeave = async (id) => {
  try {
    await axios.put(
      `${apiUrl}/api/leave-requests/${id}`,
      { status: 'Approved' },
      {
        withCredentials: true,
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`,
        },
      }
    );
    await fetchLeaveRequests();
    showNotification('Duyệt yêu cầu nghỉ phép thành công!');
  } catch (error) {
    console.error('Lỗi khi duyệt yêu cầu nghỉ phép:', error.response ? error.response.data : error.message);
    showNotification(`Duyệt yêu cầu thất bại: ${error.response?.data?.message || error.message}`, 'error');
  }
};

const rejectLeave = async (id) => {
  try {
    await axios.put(
      `${apiUrl}/api/leave-requests/${id}`,
      { status: 'Rejected' },
      {
        withCredentials: true,
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`,
        },
      }
    );
    await fetchLeaveRequests();
    showNotification('Từ chối yêu cầu nghỉ phép thành công!');
  } catch (error) {
    console.error('Lỗi khi từ chối yêu cầu nghỉ phép:', error.response ? error.response.data : error.message);
    showNotification(`Từ chối yêu cầu thất bại: ${error.response?.data?.message || error.message}`, 'error');
  }
};

const deleteLeave = async (id) => {
  if (confirm('Bạn có chắc chắn muốn xóa yêu cầu nghỉ phép này không?')) {
    try {
      await axios.delete(`${apiUrl}/api/leave-requests/${id}`, {
        withCredentials: true,
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`,
        },
      });
      await fetchLeaveRequests();
      if (paginatedLeaveRequests.value.length === 0 && currentPage.value > 1) {
        currentPage.value--;
      }
      showNotification('Xóa yêu cầu nghỉ phép thành công!');
    } catch (error) {
      console.error('Lỗi khi xóa yêu cầu nghỉ phép:', error.response ? error.response.data : error.message);
      showNotification(`Xóa yêu cầu thất bại: ${error.response?.data?.message || error.message}`, 'error');
    }
  }
};
</script>

<style scoped>
.leave-container {
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
.table td:first-child,
.table td:last-child {
  text-align: center;
  min-width: 80px;
}
.table td:nth-child(2) {
  min-width: 150px;
}
.table td:nth-child(3),
.table td:nth-child(4),
.table td:nth-child(5) {
  min-width: 120px;
}
.table td:nth-child(6) {
  min-width: 200px;
}
.table td:nth-child(7) {
  min-width: 150px;
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
.btn-outline-danger {
  padding: 4px 8px;
  font-size: 0.9rem;
}
.btn-outline-primary i,
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
  min-width: 1100px;
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
/* Style cho trạng thái */
.table td.status-pending {
  color: #f1c40f !important; /* Vàng cho Đang chờ */
  font-weight: 600;
}
.table td.status-approved {
  color: #007bff !important; /* Xanh biển cho Đã duyệt */
  font-weight: bold;
}
.table td.status-rejected {
  color: #c0392b !important; /* Đỏ đậm cho Bị từ chối */
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
@media (max-width: 767px) {
  .leave-container {
    padding: 10px;
  }
  .table th,
  .table td {
    padding: 8px;
    font-size: 0.85rem;
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
  .pagination-controls .btn {
    padding: 6px 15px;
    font-size: 0.85rem;
  }
  .pagination-controls span {
    font-size: 0.9rem;
  }
  .custom-alert {
    min-width: 250px;
    right: 10px;
    top: 10px;
  }
  table {
    min-width: 1000px;
  }
}
</style>