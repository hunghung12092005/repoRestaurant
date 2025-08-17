<template>
  <div class="page-container">
    <!-- Thông báo -->
    <div v-if="showAlert" :class="['alert', alertType, 'custom-alert']" role="alert">
      {{ alertMessage }}
      <span class="close-btn" @click="showAlert = false">×</span>
    </div>

    <!-- Tiêu đề trang -->
    <div class="page-header mb-4">
      <h1 class="page-title">Quản Lý Nhân Viên</h1>
      <p class="page-subtitle">Tạo mới, chỉnh sửa và quản lý nhân viên trong khách sạn của bạn.</p>
    </div>

    <!-- Bộ lọc và tìm kiếm -->
    <div class="card filter-card mb-4">
      <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div class="d-flex align-items-center gap-2 flex-grow-1" style="min-width: 300px;">
          <input
            v-model="searchQuery"
            type="text"
            class="form-control"
            placeholder="Tìm theo tên, email, mã NV..."
          />
          <select v-model="filterRoleId" class="form-select">
            <option value="">Tất cả vai trò</option>
            <option v-for="role in staffRoles" :key="role.id" :value="role.id">{{ role.display_name }}</option>
          </select>
          <select v-model="filterDepartment" class="form-select">
            <option value="">Tất cả phòng ban</option>
            <option v-for="dept in departments" :key="dept" :value="dept">{{ dept }}</option>
          </select>
        </div>
        <button class="btn btn-primary" @click="openAddModal">
          <i class="bi bi-plus-circle me-2"></i>Thêm Nhân Viên
        </button>
      </div>
    </div>

    <!-- Bảng danh sách nhân viên -->
    <div class="table-container">
      <table class="table booking-table align-middle">
        <thead>
          <tr>
            <th style="width: 25%;">Họ và Tên</th>
            <th style="width: 10%;">Mã NV</th>
            <th style="width: 15%;">Vai Trò</th>
            <th style="width: 15%;">Phòng Ban</th>
            <th style="width: 15%;">SĐT</th>
            <th style="width: 10%;">Trạng Thái</th>
            <th class="text-center" style="width: 10%;">Hành Động</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="isLoading">
            <td colspan="7" class="text-center py-5">
              <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Đang tải...</span></div>
            </td>
          </tr>
          <tr v-else-if="paginatedStaff.length === 0">
             <td colspan="7">
                <div class="alert alert-light text-center mb-0">Không tìm thấy nhân viên phù hợp.</div>
            </td>
          </tr>
          <tr v-else v-for="staff in paginatedStaff" :key="staff.id">
            <td>
              <div class="fw-bold type-name">{{ staff.name }}</div>
              <p class="description-text mb-0">{{ staff.email }}</p>
            </td>
            <td>{{ staff.staff_profile?.employee_code || 'N/A' }}</td>
            <td>
              <span v-if="staff.role" class="badge" :class="getRoleBadgeClass(staff.role.name)">
                {{ staff.role.display_name }}
              </span>
            </td>
            <td>{{ staff.staff_profile?.department || 'N/A' }}</td>
            <td>{{ staff.staff_profile?.phone || 'N/A' }}</td>
            <td><span class="badge" :class="getStatusBadgeClass(staff.status)">{{ formatStatus(staff.status) }}</span></td>
            <td class="text-center action-buttons">
              <button class="btn btn-outline-primary btn-sm" title="Sửa" @click="openEditModal(staff)"><i class="bi bi-pencil-fill"></i></button>
              <button class="btn btn-outline-info btn-sm" title="Xem chi tiết" @click="openDetailModal(staff)"><i class="bi bi-eye-fill"></i></button>
              <button class="btn btn-outline-danger btn-sm" title="Xóa" @click="confirmDeleteStaff(staff)"><i class="bi bi-trash-fill"></i></button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Phân trang -->
    <nav v-if="totalPages > 1" aria-label="Page navigation" class="mt-4">
      <ul class="pagination justify-content-center">
        <li class="page-item" :class="{ disabled: currentPage === 1 }"><a class="page-link" href="#" @click.prevent="currentPage = 1">««</a></li>
        <li class="page-item" :class="{ disabled: currentPage === 1 }"><a class="page-link" href="#" @click.prevent="currentPage--">«</a></li>
        <li v-for="page in pageRange" :key="page" class="page-item" :class="{ active: page === currentPage }"><a class="page-link" href="#" @click.prevent="currentPage = page">{{ page }}</a></li>
        <li class="page-item" :class="{ disabled: currentPage === totalPages }"><a class="page-link" href="#" @click.prevent="currentPage++">»</a></li>
        <li class="page-item" :class="{ disabled: currentPage === totalPages }"><a class="page-link" href="#" @click.prevent="currentPage = totalPages">»»</a></li>
      </ul>
    </nav>

    <!-- ======== MODAL THÊM/SỬA NHÂN VIÊN ======== -->
    <div class="modal fade" id="staffEditModal" tabindex="-1">
      <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content modal-custom">
          <div class="modal-header modal-header-custom">
            <h5 class="modal-title">{{ currentStaffId ? 'Cập nhật Nhân Viên' : 'Thêm Nhân Viên Mới' }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body p-4">
            <form @submit.prevent="saveStaff" novalidate>
               <h6 class="form-section-title">Thông Tin Cơ Bản</h6>
              <div class="row g-3 mb-4">
                <div class="col-md-6"><label class="form-label">Họ và Tên <span class="text-danger">*</span></label><input v-model.trim="form.name" type="text" class="form-control" :class="{ 'is-invalid': errors.name }"><div class="invalid-feedback">{{ errors.name?.[0] }}</div></div>
                <div class="col-md-6"><label class="form-label">Email <span class="text-danger">*</span></label><input v-model.trim="form.email" type="email" class="form-control" :class="{ 'is-invalid': errors.email }"><div class="invalid-feedback">{{ errors.email?.[0] }}</div></div>
                <div class="col-md-6"><label class="form-label">Vai Trò <span class="text-danger">*</span></label>
                  <select v-model="form.role_id" class="form-select" :class="{ 'is-invalid': errors.role_id }">
                    <option disabled value="">-- Chọn vai trò --</option>
                    <option v-for="role in staffRoles" :key="role.id" :value="role.id">{{ role.display_name }}</option>
                  </select>
                  <div class="invalid-feedback">{{ errors.role_id?.[0] }}</div>
                </div>
                <div class="col-md-6"><label class="form-label">Trạng Thái <span class="text-danger">*</span></label><select v-model="form.status" class="form-select" :class="{ 'is-invalid': errors.status }"><option value="active">Hoạt động</option><option value="inactive">Nghỉ việc</option><option value="suspended">Tạm ngưng</option></select><div class="invalid-feedback">{{ errors.status?.[0] }}</div></div>
              </div>
              
              <h6 class="form-section-title">Hồ Sơ Nhân Viên</h6>
              <div class="row g-3 mb-4">
                <div class="col-md-4"><label class="form-label">Mã Nhân Viên <span class="text-danger">*</span></label><input v-model.trim="form.employee_code" type="text" class="form-control" :class="{ 'is-invalid': errors.employee_code }"><div class="invalid-feedback">{{ errors.employee_code?.[0] }}</div></div>
                <div class="col-md-4"><label class="form-label">Số Điện Thoại</label><input v-model.trim="form.phone" type="text" class="form-control" :class="{ 'is-invalid': errors.phone }"></div>
                <div class="col-md-4"><label class="form-label">Ngày Tuyển Dụng</label><input v-model="form.hire_date" type="date" class="form-control" :class="{ 'is-invalid': errors.hire_date }"></div>
                <div class="col-12"><label class="form-label">Địa Chỉ</label><input v-model.trim="form.address" type="text" class="form-control" :class="{ 'is-invalid': errors.address }"></div>
                <div class="col-md-4"><label class="form-label">Phòng Ban</label><input v-model.trim="form.department" type="text" class="form-control" :class="{ 'is-invalid': errors.department }"></div>
                <div class="col-md-4"><label class="form-label">Chức Vụ</label><input v-model.trim="form.position" type="text" class="form-control" :class="{ 'is-invalid': errors.position }"></div>
                <div class="col-md-4"><label class="form-label">Cấp Bậc</label><select v-model="form.level" class="form-select" :class="{ 'is-invalid': errors.level }"><option value="junior">Nhân viên mới</option><option value="senior">Nhân viên lâu năm</option><option value="manager">Quản lý</option></select></div>
                <div class="col-md-4"><label class="form-label">Lương (VND)</label><input v-model.number="form.salary" type="number" class="form-control" :class="{ 'is-invalid': errors.salary }"></div>
              </div>
              
               <h6 class="form-section-title">Lịch Làm Việc</h6>
              <div class="schedule-container">
                <div v-if="!form.schedules || !form.schedules.length" class="text-center p-3 bg-light rounded">Chưa có lịch làm việc. Nhấn "Thêm Ca Làm" để bắt đầu.</div>
                <div v-for="(schedule, index) in form.schedules" :key="index" class="schedule-row">
                  <input v-model="schedule.shift_date" type="date" class="form-control" :class="{ 'is-invalid': errors[`schedules.${index}.shift_date`] }"/>
                  <input v-model="schedule.start_time" type="time" class="form-control" :class="{ 'is-invalid': errors[`schedules.${index}.start_time`] }"/>
                  <input v-model="schedule.end_time" type="time" class="form-control" :class="{ 'is-invalid': errors[`schedules.${index}.end_time`] }"/>
                  <input v-model="schedule.task" type="text" class="form-control flex-grow-1" placeholder="Nhiệm vụ"/>
                  <button type="button" class="btn btn-outline-danger btn-sm" @click="form.schedules.splice(index, 1)"><i class="bi bi-trash-fill"></i></button>
                </div>
                <button type="button" class="btn btn-outline-primary mt-3" @click="form.schedules.push({ shift_date: '', start_time: '08:00:00', end_time: '17:00:00', task: '' })"><i class="bi bi-plus-circle me-2"></i>Thêm Ca Làm</button>
              </div>
            </form>
          </div>
          <div class="modal-footer modal-footer-custom">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
            <button type="button" class="btn btn-primary" @click="saveStaff" :disabled="isSaving">
              <span v-if="isSaving" class="spinner-border spinner-border-sm me-2"></span>{{ isSaving ? 'Đang lưu...' : 'Lưu Lại' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ======== MODAL CHI TIẾT NHÂN VIÊN ======== -->
    <div class="modal fade" id="staffDetailModal" tabindex="-1">
      <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content modal-custom">
          <div class="modal-header modal-header-custom"><h5 class="modal-title">Chi Tiết Nhân Viên</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
          <div class="modal-body p-4">
            <div v-if="selectedStaff">
              <div class="detail-section"><h6 class="form-section-title">Thông Tin Cơ Bản</h6>
                <div class="row g-3">
                  <div class="col-md-6"><p><strong>Họ và Tên:</strong> {{ selectedStaff.name }}</p></div>
                  <div class="col-md-6"><p><strong>Email:</strong> {{ selectedStaff.email }}</p></div>
                  <div class="col-md-6"><p><strong>Vai Trò:</strong> {{ selectedStaff.role?.display_name }}</p></div>
                  <div class="col-md-6"><p><strong>Trạng Thái:</strong> <span :class="['badge', getStatusBadgeClass(selectedStaff.status)]">{{ formatStatus(selectedStaff.status) }}</span></p></div>
                </div>
              </div>
              <hr class="my-2">
              <div class="detail-section"><h6 class="form-section-title">Hồ Sơ Nhân Viên</h6>
                 <div class="row g-3">
                    <div class="col-md-6"><p><strong>Mã Nhân Viên:</strong> {{ selectedStaff.staff_profile?.employee_code }}</p></div>
                    <div class="col-md-6"><p><strong>Số Điện Thoại:</strong> {{ selectedStaff.staff_profile?.phone }}</p></div>
                    <div class="col-md-6"><p><strong>Ngày Tuyển Dụng:</strong> {{ formatDate(selectedStaff.staff_profile?.hire_date) }}</p></div>
                    <div class="col-md-6"><p><strong>Phòng Ban:</strong> {{ selectedStaff.staff_profile?.department }}</p></div>
                    <div class="col-md-6"><p><strong>Chức Vụ:</strong> {{ selectedStaff.staff_profile?.position }}</p></div>
                    <div class="col-md-6"><p><strong>Cấp Bậc:</strong> {{ formatLevel(selectedStaff.staff_profile?.level) }}</p></div>
                    <div class="col-md-6"><p><strong>Lương:</strong> {{ formatSalary(selectedStaff.staff_profile?.salary) }}</p></div>
                    <div class="col-12"><p><strong>Địa Chỉ:</strong> {{ selectedStaff.staff_profile?.address }}</p></div>
                 </div>
              </div>
              <hr class="my-2">
              <div class="detail-section"><h6 class="form-section-title">Lịch Làm Việc</h6>
                <div v-if="selectedStaff.staff_schedules && selectedStaff.staff_schedules.length > 0">
                  <div class="schedule-detail-item" v-for="schedule in selectedStaff.staff_schedules" :key="schedule.id">
                      <div><span class="fw-bold"><i class="bi bi-calendar-check me-2"></i>{{ formatDate(schedule.shift_date) }}</span><span class="text-muted mx-2">|</span><span><i class="bi bi-clock me-1"></i>{{ schedule.start_time.slice(0,5) }} - {{ schedule.end_time.slice(0,5) }}</span></div>
                      <div class="text-muted ps-4 mt-1"><em>Nhiệm vụ: {{ schedule.task }}</em></div>
                  </div>
                </div>
                <div v-else class="text-center p-3 bg-light rounded">Chưa có lịch làm việc.</div>
              </div>
            </div>
          </div>
          <div class="modal-footer modal-footer-custom"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import { Modal } from 'bootstrap';

// --- STATE & REFS ---
const staffList = ref([]);
const staffRoles = ref([]); // <-- Mới: Dùng để lưu các vai trò nhân viên (Manager, Receptionist)
const currentStaffId = ref(null);
const selectedStaff = ref(null);
const searchQuery = ref('');
const filterRoleId = ref(''); // <-- Sửa: Lọc theo role_id
const filterDepartment = ref('');
const currentPage = ref(1);
const itemsPerPage = 10;
const isLoading = ref(true);
const isSaving = ref(false);
const showAlert = ref(false);
const alertMessage = ref('');
const alertType = ref('alert-success');
const errors = ref({});
const form = ref({});
let editModal = null;
let detailModal = null;

const apiClient = axios.create({
  baseURL: 'http://127.0.0.1:8000/api',
  headers: { 'Authorization': `Bearer ${localStorage.getItem('tokenJwt') || ''}` }
});

// --- FORM & MODAL LOGIC ---
const resetForm = () => {
  form.value = {
    name: '', email: '', role_id: '', status: 'active',
    employee_code: '', phone: '', address: '', hire_date: new Date().toISOString().slice(0,10), 
    position: '', department: '', salary: null, level: 'junior', schedules: []
  };
  errors.value = {};
};

const openAddModal = () => {
  currentStaffId.value = null;
  resetForm();
  editModal.show();
};

const openEditModal = (staff) => {
  currentStaffId.value = staff.id;
  // Trải phẳng dữ liệu từ các object lồng nhau vào form
  form.value = {
    name: staff.name,
    email: staff.email,
    role_id: staff.role_id,
    status: staff.status,
    ...(staff.staff_profile || {}), // Trải các thuộc tính từ profile
    schedules: (staff.staff_schedules || []).map(s => ({
        ...s,
        start_time: s.start_time ? s.start_time.substring(0, 8) : '', // Format H:i:s
        end_time: s.end_time ? s.end_time.substring(0, 8) : ''
    }))
  };
  errors.value = {};
  editModal.show();
};

const openDetailModal = (staff) => {
  selectedStaff.value = staff;
  detailModal.show();
};


// --- API CALLS ---
const fetchStaff = async () => {
  isLoading.value = true;
  try {
    const response = await apiClient.get('/staffs');
    staffList.value = response.data.data;
  } catch (error) {
    showNotification('Không thể tải danh sách nhân viên.', 'error');
  } finally {
    isLoading.value = false;
  }
};

const fetchStaffRoles = async () => {
    try {
        const response = await apiClient.get('/roles');
        // Lọc bỏ vai trò 'admin' và 'client' khỏi danh sách chọn
        staffRoles.value = response.data.filter(role => !['admin', 'client'].includes(role.name));
    } catch (error) { showNotification('Không thể tải danh sách vai trò.', 'error'); }
}

const saveStaff = async () => {
  isSaving.value = true;
  errors.value = {};
  
  try {
    const response = currentStaffId.value
      ? await apiClient.put(`/staffs/${currentStaffId.value}`, form.value)
      : await apiClient.post('/staffs', form.value);
    showNotification(response.data.message, 'success');
    editModal.hide();
    await fetchStaff(); // Tải lại danh sách
  } catch (error) {
    showNotification(error.response?.data?.message || 'Có lỗi xảy ra', 'error');
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors;
    }
  } finally {
    isSaving.value = false;
  }
};

const confirmDeleteStaff = async (staff) => {
  if (confirm(`Bạn có chắc chắn muốn xóa nhân viên "${staff.name}" không?`)) {
    try {
      const response = await apiClient.delete(`/staffs/${staff.id}`);
      showNotification(response.data.message, 'success');
      await fetchStaff();
    } catch (error) {
      showNotification(error.response?.data?.message || 'Xóa thất bại', 'error');
    }
  }
};


// --- COMPUTED PROPERTIES ---
const filteredStaff = computed(() => {
  return staffList.value.filter(staff => {
    const searchLower = searchQuery.value.toLowerCase();
    const matchesSearch = !searchLower || 
                         (staff.name?.toLowerCase() || '').includes(searchLower) ||
                         (staff.email?.toLowerCase() || '').includes(searchLower) ||
                         (staff.staff_profile?.employee_code?.toLowerCase() || '').includes(searchLower);
    const matchesRole = !filterRoleId.value || staff.role_id == filterRoleId.value;
    const matchesDepartment = !filterDepartment.value || staff.staff_profile?.department === filterDepartment.value;
    return matchesSearch && matchesRole && matchesDepartment;
  });
});

const departments = computed(() => {
    const depts = staffList.value.map(s => s.staff_profile?.department).filter(Boolean);
    return [...new Set(depts)];
});

const totalPages = computed(() => Math.ceil(filteredStaff.value.length / itemsPerPage));
const paginatedStaff = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  return filteredStaff.value.slice(start, start + itemsPerPage);
});

const pageRange = computed(() => {
    // Logic phân trang giữ nguyên
    const range = [];
    for (let i = 1; i <= totalPages.value; i++) {
        range.push(i);
    }
    return range;
});


// --- FORMATTING & UTILITY FUNCTIONS ---
const showNotification = (message, type = 'success') => {
  alertMessage.value = message;
  alertType.value = `alert-${type === 'error' ? 'danger' : 'success'}`;
  showAlert.value = true;
  setTimeout(() => showAlert.value = false, 4000);
};

const formatStatus = (status) => ({ active: 'Hoạt động', inactive: 'Nghỉ việc', suspended: 'Tạm ngưng' })[status] || 'N/A';
const formatLevel = (level) => ({ junior: 'Nhân viên mới', senior: 'Nhân viên lâu năm', manager: 'Quản lý' })[level] || 'Chưa có';
const getRoleBadgeClass = (roleName) => ({ manager: 'badge-warning', receptionist: 'badge-primary' })[roleName] || 'badge-secondary';
const getStatusBadgeClass = (status) => ({ active: 'badge-success', inactive: 'badge-secondary', suspended: 'badge-danger' })[status] || 'badge-dark';
const formatDate = (dateString) => dateString ? new Date(dateString).toLocaleDateString('vi-VN') : 'N/A';
const formatSalary = (salary) => salary ? new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(salary) : 'N/A';


// --- LIFECYCLE HOOK ---
onMounted(async () => {
  await Promise.all([fetchStaff(), fetchStaffRoles()]);
  editModal = new Modal(document.getElementById('staffEditModal'));
  detailModal = new Modal(document.getElementById('staffDetailModal'));
});
</script>

<style scoped>
/* Giữ nguyên CSS của bạn, nó đã rất đẹp */
@import url('https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700&display=swap');
@import url('https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css');

.page-container { font-family: 'Be Vietnam Pro', sans-serif; background-color: #f4f7f9; padding: 2rem; color: #34495e; }
.page-header { border-bottom: 1px solid #e5eaee; padding-bottom: 1rem; }
.page-title { font-size: 2rem; font-weight: 700; }
.page-subtitle { font-size: 1rem; color: #7f8c8d; }
.card { background-color: #ffffff; border: none; border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); }
.form-control, .form-select { border-radius: 8px; border: 1px solid #e5eaee; transition: all 0.2s ease-in-out; font-size: 0.9rem; }
.form-control:focus, .form-select:focus { border-color: #3498db; box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.15); }
.table-container { background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); overflow: hidden; }
.booking-table { font-size: 0.875rem; border-collapse: separate; border-spacing: 0; }
.booking-table thead th { background-color: #f8f9fa; color: #7f8c8d; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e5eaee; padding: 1rem; white-space: nowrap; }
.booking-table td { padding: 1.25rem 1rem; border-bottom: 1px solid #e5eaee; }
.booking-table tbody tr:last-child td { border-bottom: none; }
.booking-table tbody tr:hover { background-color: #f9fafb; }
.type-name { font-size: 1rem; color: #34495e; }
.description-text { font-size: 0.8rem; color: #7f8c8d; max-width: 250px; }
.badge { padding: 0.4em 0.8em; font-size: 0.75rem; font-weight: 600; border-radius: 20px; text-transform: capitalize; }
.badge-secondary { background-color: #f3f4f6; color: #7f8c8d; }
.badge-primary { background-color: #eaf6fb; color: #3498db; }
.badge-success { background-color: #e8f9f0; color: #2ecc71; }
.badge-warning { background-color: #fff8e1; color: #f39c12; }
.badge-danger { background-color: #feeeed; color: #e74c3c; }
.action-buttons { white-space: nowrap; }
.action-buttons .btn { margin: 0 2px; }
.pagination .page-link { border: none; border-radius: 8px; margin: 0 4px; color: #7f8c8d; font-weight: 600; }
.pagination .page-item.active .page-link { background-color: #3498db; color: #ffffff; }
.modal-custom { border-radius: 16px; border: none; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); }
.modal-header-custom, .modal-footer-custom { background-color: #f4f7f9; border-color: #e5eaee; padding: 1.5rem; }
.modal-header-custom .btn-close { filter: brightness(0) invert(0.7); }
.modal-body { background-color: #ffffff; }
.form-section-title { font-size: 1rem; font-weight: 600; color: #34495e; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 1px solid #e5eaee; }
.schedule-container { display: flex; flex-direction: column; gap: 0.75rem; }
.schedule-row { display: flex; gap: 0.75rem; align-items: center; }
.detail-section p { margin-bottom: 0.75rem; font-size: 0.9rem; }
.detail-section p strong { color: #34495e; display: inline-block; width: 150px; }
.schedule-detail-item { padding: 0.75rem; border: 1px solid #e9ecef; border-radius: 8px; margin-bottom: 0.75rem; background-color: #f8f9fa; }
.custom-alert { position: fixed; top: 80px; right: 20px; z-index: 1056; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); display: flex; align-items: center; padding: 1rem 1.5rem; }
.close-btn { margin-left: 1.5rem; font-size: 1.5rem; line-height: 1; cursor: pointer; font-weight: bold; }
</style>