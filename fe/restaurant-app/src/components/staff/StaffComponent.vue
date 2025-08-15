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
        <div class="d-flex align-items-center gap-2 flex-grow-1">
          <input
            v-model="searchQuery"
            type="text"
            class="form-control"
            placeholder="Tìm theo tên, email, mã NV..."
            @input="handleSearch"
          />
          <select v-model="filterRole" class="form-select" @change="handleSearch">
            <option value="">Tất cả vai trò</option>
            <option value="manager">Quản lý</option>
            <option value="receptionist">Lễ tân</option>
          </select>
          <select v-model="filterDepartment" class="form-select" @change="handleSearch">
            <option value="">Tất cả phòng ban</option>
            <option value="Quản lý">Quản lý</option>
            <option value="Lễ tân">Lễ tân</option>
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
            <th>Họ và Tên</th>
            <th>Mã NV</th>
            <th>Vai Trò</th>
            <th>Phòng Ban</th>
            <th>SĐT</th>
            <th>Trạng Thái</th>
            <th class="text-center">Hành Động</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="isLoading">
            <td colspan="7" class="text-center py-5">
              <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Đang tải...</span></div>
            </td>
          </tr>
          <tr v-else-if="paginatedStaff.length === 0">
            <td colspan="7" class="text-center py-5">Không tìm thấy nhân viên phù hợp.</td>
          </tr>
          <tr v-else v-for="staff in paginatedStaff" :key="staff.id">
            <td>
              <div class="fw-bold type-name">{{ staff.name }}</div>
              <p class="description-text mb-0">{{ staff.email }}</p>
            </td>
            <td>{{ staff.staff_profile?.employee_code || 'N/A' }}</td>
            <td><span class="badge" :class="getRoleBadgeClass(staff.role)">{{ formatRole(staff.role) }}</span></td>
            <td>{{ staff.staff_profile?.department || 'N/A' }}</td>
            <td>{{ staff.staff_profile?.phone || 'N/A' }}</td>
            <td><span class="badge" :class="getStatusBadgeClass(staff.status)">{{ formatStatus(staff.status) }}</span></td>
            <td class="text-center action-buttons">
              <button class="btn btn-outline-primary btn-sm" title="Sửa" @click="openEditModal(staff)"><i class="bi bi-pencil-fill"></i></button>
              <button class="btn btn-outline-info btn-sm" title="Xem chi tiết" @click="openDetailModal(staff)"><i class="bi bi-eye-fill"></i></button>
              <button class="btn btn-outline-danger btn-sm" title="Xóa" @click="confirmDeleteStaff(staff.id)"><i class="bi bi-trash-fill"></i></button>
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
    <div class="modal fade" id="staffEditModal" tabindex="-1" aria-labelledby="staffEditModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content modal-custom">
          <div class="modal-header modal-header-custom">
            <h5 class="modal-title" id="staffEditModalLabel">{{ currentStaff ? 'Cập nhật Nhân Viên' : 'Thêm Nhân Viên Mới' }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body p-4">
            <!-- NỘI DUNG FORM ĐÃ ĐƯỢC THÊM VÀO ĐÂY -->
            <form @submit.prevent="saveStaff" novalidate>
              <h6 class="form-section-title">Thông Tin Cơ Bản</h6>
              <div class="row g-3 mb-4">
                <div class="col-md-6"><label class="form-label">Họ và Tên <span class="text-danger">*</span></label><input v-model.trim="form.name" type="text" class="form-control" :class="{ 'is-invalid': errors.name }" required><div class="invalid-feedback">{{ errors.name?.[0] }}</div></div>
                <div class="col-md-6"><label class="form-label">Email <span class="text-danger">*</span></label><input v-model.trim="form.email" type="email" class="form-control" :class="{ 'is-invalid': errors.email }" required><div class="invalid-feedback">{{ errors.email?.[0] }}</div></div>
                <div class="col-md-6"><label class="form-label">Vai Trò <span class="text-danger">*</span></label><select v-model="form.role" class="form-select" :class="{ 'is-invalid': errors.role }" @change="updatePermissions" required><option disabled value="">-- Chọn vai trò --</option><option value="manager">Quản lý</option><option value="receptionist">Lễ tân</option></select><div class="invalid-feedback">{{ errors.role?.[0] }}</div></div>
                <div class="col-md-6"><label class="form-label">Trạng Thái <span class="text-danger">*</span></label><select v-model="form.status" class="form-select" :class="{ 'is-invalid': errors.status }" required><option value="active">Hoạt động</option><option value="inactive">Nghỉ việc</option><option value="suspended">Tạm ngưng</option></select><div class="invalid-feedback">{{ errors.status?.[0] }}</div></div>
                <div class="col-12"><label class="form-label">Quyền Hạn</label><multiselect v-model="form.permissions" :options="permissionOptions" :multiple="true" track-by="value" label="label" :close-on-select="false" placeholder="Chọn quyền hạn"></multiselect></div>
              </div>
              <hr class="my-4">
              <h6 class="form-section-title">Hồ Sơ Nhân Viên</h6>
              <div class="row g-3 mb-4">
                <div class="col-md-4"><label class="form-label">Mã Nhân Viên <span class="text-danger">*</span></label><input v-model.trim="form.employee_code" type="text" class="form-control" :class="{ 'is-invalid': errors.employee_code }" required><div class="invalid-feedback">{{ errors.employee_code?.[0] }}</div></div>
                <div class="col-md-4"><label class="form-label">Số Điện Thoại</label><input v-model.trim="form.phone" type="text" class="form-control" :class="{ 'is-invalid': errors.phone }"><div class="invalid-feedback">{{ errors.phone?.[0] }}</div></div>
                <div class="col-md-4"><label class="form-label">Ngày Tuyển Dụng</label><input v-model="form.hire_date" type="date" class="form-control" :class="{ 'is-invalid': errors.hire_date }"><div class="invalid-feedback">{{ errors.hire_date?.[0] }}</div></div>
                <div class="col-12"><label class="form-label">Địa Chỉ</label><input v-model.trim="form.address" type="text" class="form-control" :class="{ 'is-invalid': errors.address }"><div class="invalid-feedback">{{ errors.address?.[0] }}</div></div>
                <div class="col-md-4"><label class="form-label">Phòng Ban</label><input v-model.trim="form.department" type="text" class="form-control" :class="{ 'is-invalid': errors.department }"><div class="invalid-feedback">{{ errors.department?.[0] }}</div></div>
                <div class="col-md-4"><label class="form-label">Chức Vụ</label><input v-model.trim="form.position" type="text" class="form-control" :class="{ 'is-invalid': errors.position }"><div class="invalid-feedback">{{ errors.position?.[0] }}</div></div>
                <div class="col-md-4"><label class="form-label">Cấp Bậc</label><select v-model="form.level" class="form-select" :class="{ 'is-invalid': errors.level }"><option value="junior">Nhân viên mới</option><option value="senior">Nhân viên lâu năm</option><option value="manager">Quản lý</option></select><div class="invalid-feedback">{{ errors.level?.[0] }}</div></div>
                <div class="col-md-4"><label class="form-label">Lương (VND)</label><input v-model.number="form.salary" type="number" class="form-control" :class="{ 'is-invalid': errors.salary }"><div class="invalid-feedback">{{ errors.salary?.[0] }}</div></div>
              </div>
              <hr class="my-4">
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
                <button type="button" class="btn btn-outline-primary mt-3" @click="form.schedules.push({ shift_date: '', start_time: '', end_time: '', task: '' })"><i class="bi bi-plus-circle me-2"></i>Thêm Ca Làm</button>
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
    <div class="modal fade" id="staffDetailModal" tabindex="-1" aria-labelledby="staffDetailModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content modal-custom">
          <div class="modal-header modal-header-custom"><h5 class="modal-title" id="staffDetailModalLabel">Chi Tiết Nhân Viên</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
          <div class="modal-body p-4">
            <!-- NỘI DUNG CHI TIẾT ĐÃ ĐƯỢC THÊM VÀO ĐÂY -->
            <div v-if="selectedStaff">
              <div class="detail-section"><h6 class="form-section-title">Thông Tin Cơ Bản</h6>
                <div class="row g-3">
                  <div class="col-md-6"><p><strong>Họ và Tên:</strong> {{ selectedStaff.name || 'N/A' }}</p></div>
                  <div class="col-md-6"><p><strong>Email:</strong> {{ selectedStaff.email || 'N/A' }}</p></div>
                  <div class="col-md-6"><p><strong>Vai Trò:</strong> {{ formatRole(selectedStaff.role) || 'N/A' }}</p></div>
                  <div class="col-md-6"><p><strong>Trạng Thái:</strong> <span :class="['badge', getStatusBadgeClass(selectedStaff.status)]">{{ formatStatus(selectedStaff.status) }}</span></p></div>
                  <div class="col-12"><p><strong>Quyền Hạn:</strong> {{ formatPermissions(selectedStaff.permissions) || 'Không có' }}</p></div>
                </div>
              </div>
              <hr class="my-4">
              <div class="detail-section"><h6 class="form-section-title">Hồ Sơ Nhân Viên</h6>
                 <div class="row g-3">
                    <div class="col-md-6"><p><strong>Mã Nhân Viên:</strong> {{ selectedStaff.staff_profile?.employee_code || 'N/A' }}</p></div>
                    <div class="col-md-6"><p><strong>Số Điện Thoại:</strong> {{ selectedStaff.staff_profile?.phone || 'N/A' }}</p></div>
                    <div class="col-md-6"><p><strong>Ngày Tuyển Dụng:</strong> {{ formatDate(selectedStaff.staff_profile?.hire_date) }}</p></div>
                    <div class="col-md-6"><p><strong>Phòng Ban:</strong> {{ selectedStaff.staff_profile?.department || 'N/A' }}</p></div>
                    <div class="col-md-6"><p><strong>Chức Vụ:</strong> {{ selectedStaff.staff_profile?.position || 'N/A' }}</p></div>
                    <div class="col-md-6"><p><strong>Cấp Bậc:</strong> {{ formatLevel(selectedStaff.staff_profile?.level) }}</p></div>
                    <div class="col-md-6"><p><strong>Lương:</strong> {{ formatSalary(selectedStaff.staff_profile?.salary) }}</p></div>
                    <div class="col-12"><p><strong>Địa Chỉ:</strong> {{ selectedStaff.staff_profile?.address || 'N/A' }}</p></div>
                 </div>
              </div>
              <hr class="my-4">
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
import { ref, computed, onMounted, watch } from 'vue';
import axios from 'axios';
import { Modal } from 'bootstrap';
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.css';

// State and Refs
const staffList = ref([]);
const currentStaff = ref(null);
const selectedStaff = ref(null);
const searchQuery = ref('');
const filterRole = ref('');
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

const permissionOptions = ref([
    { value: 'manage_bookings', label: 'Quản lý Đặt phòng & Sơ đồ phòng' },
    { value: 'manage_reports', label: 'Xem Lịch sử & Báo cáo' },
    { value: 'manage_rooms', label: 'Quản lý Phòng & Loại phòng'},
    { value: 'manage_prices', label: 'Quản lý Giá'},
    { value: 'manage_services', label: 'Quản lý Dịch vụ' },
    { value: 'manage_amenities', label: 'Quản lý Tiện nghi' },
    { value: 'manage_coupons', label: 'Quản lý Giảm giá' },
    { value: 'manage_news', label: 'Quản lý Tin tức' },
    { value: 'manage_contacts', label: 'Quản lý Liên hệ' },
    { value: 'manage_users', label: 'Quản lý Tài khoản' },
    { value: 'manage_staff', label: 'Quản lý Nhân viên' },
    { value: 'manage_ai_training', label: 'Training AI' },
    { value: 'manage_admin_chat', label: 'Chat Admin' },
]);

const resetForm = () => {
  form.value = {
    name: '', email: '', role: '', status: 'active', permissions: [],
    employee_code: '', phone: '', address: '', hire_date: new Date().toISOString().slice(0,10), 
    position: '', department: '', salary: null, level: 'junior', schedules: []
  };
  errors.value = {};
};

const fetchStaff = async () => {
  isLoading.value = true;
  try {
    const response = await apiClient.get('/staffs');
    staffList.value = response.data.data.map(staff => ({
      ...staff,
      permissions: Array.isArray(staff.permissions) ? staff.permissions : [],
      staff_profile: staff.staff_profile || {},
      staff_schedules: staff.staff_schedules || []
    }));
  } catch (error) {
    showNotification('Không thể tải danh sách nhân viên.', 'error');
  } finally {
    isLoading.value = false;
  }
};

const filteredStaff = computed(() => {
  return staffList.value.filter(staff => {
    const searchLower = searchQuery.value.toLowerCase();
    const matchesSearch = !searchLower || 
                         (staff.name?.toLowerCase() || '').includes(searchLower) ||
                         (staff.email?.toLowerCase() || '').includes(searchLower) ||
                         (staff.staff_profile?.employee_code?.toLowerCase() || '').includes(searchLower);
    const matchesRole = !filterRole.value || staff.role === filterRole.value;
    const matchesDepartment = !filterDepartment.value || staff.staff_profile?.department === filterDepartment.value;
    return matchesSearch && matchesRole && matchesDepartment;
  });
});

const totalPages = computed(() => Math.ceil(filteredStaff.value.length / itemsPerPage.value));
const paginatedStaff = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  return filteredStaff.value.slice(start, start + itemsPerPage);
});

const pageRange = computed(() => {
  const maxPages = 5;
  let start = Math.max(1, currentPage.value - Math.floor(maxPages / 2));
  let end = Math.min(totalPages.value, start + maxPages - 1);
  if (totalPages.value > maxPages && end === totalPages.value) start = end - maxPages + 1;
  return Array.from({ length: Math.min(maxPages, totalPages.value) }, (_, i) => start + i);
});


const handleSearch = () => { currentPage.value = 1; };

const openAddModal = () => {
  currentStaff.value = null;
  resetForm();
  updatePermissions();
  editModal.show();
};

const openEditModal = (staff) => {
  currentStaff.value = staff;
  form.value = {
    name: staff.name, email: staff.email, role: staff.role, status: staff.status,
    permissions: (staff.permissions || []).map(p => permissionOptions.value.find(opt => opt.value === p)).filter(Boolean),
    employee_code: staff.staff_profile?.employee_code,
    phone: staff.staff_profile?.phone,
    address: staff.staff_profile?.address,
    hire_date: staff.staff_profile?.hire_date ? staff.staff_profile.hire_date.slice(0,10) : '',
    position: staff.staff_profile?.position,
    department: staff.staff_profile?.department,
    salary: staff.staff_profile?.salary,
    level: staff.staff_profile?.level,
    schedules: (staff.staff_schedules || []).map(s => ({...s, start_time: s.start_time.slice(0,5), end_time: s.end_time.slice(0,5)})),
  };
  editModal.show();
};

const openDetailModal = (staff) => {
  selectedStaff.value = staff;
  detailModal.show();
};

const updatePermissions = () => {
  const defaultPermissions = {
    manager: ['manage_bookings', 'manage_reports', 'manage_staff', 'manage_contacts'],
    receptionist: ['manage_bookings', 'manage_contacts'],
  };
  const permissionValues = defaultPermissions[form.value.role] || [];
  form.value.permissions = permissionOptions.value.filter(opt => permissionValues.includes(opt.value));
};

const saveStaff = async () => {
  isSaving.value = true;
  errors.value = {};
  const payload = { ...form.value, permissions: form.value.permissions.map(p => p.value) };
  
  try {
    const response = currentStaff.value
      ? await apiClient.put(`/staffs/${currentStaff.value.id}`, payload)
      : await apiClient.post('/staffs', payload);
    showNotification(response.data.message, 'success');
    editModal.hide();
    fetchStaff();
  } catch (error) {
    showNotification(error.response?.data?.message || 'Có lỗi xảy ra', 'error');
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors;
    }
  } finally {
    isSaving.value = false;
  }
};

const confirmDeleteStaff = async (id) => {
  if (confirm('Bạn có chắc chắn muốn xóa nhân viên này không?')) {
    try {
      const response = await apiClient.delete(`/staffs/${id}`);
      showNotification(response.data.message, 'success');
      fetchStaff();
    } catch (error) {
      showNotification(error.response?.data?.message || 'Xóa thất bại', 'error');
    }
  }
};

const showNotification = (message, type = 'success') => {
  alertMessage.value = message;
  alertType.value = `alert-${type}`;
  showAlert.value = true;
  setTimeout(() => showAlert.value = false, 3000);
};

const formatRole = (role) => ({ manager: 'Quản lý', receptionist: 'Lễ tân' })[role] || 'N/A';
const formatLevel = (level) => ({ junior: 'Nhân viên mới', senior: 'Nhân viên lâu năm', manager: 'Quản lý' })[level] || 'Chưa có';
const formatStatus = (status) => ({ active: 'Hoạt động', inactive: 'Nghỉ việc', suspended: 'Tạm ngưng' })[status] || 'N/A';
const getRoleBadgeClass = (role) => ({ manager: 'badge-warning', receptionist: 'badge-primary' })[role] || 'badge-secondary';
const getStatusBadgeClass = (status) => ({ active: 'badge-success', inactive: 'badge-secondary', suspended: 'badge-warning' })[status] || 'badge-dark';
const formatDate = (dateString) => dateString ? new Date(dateString).toLocaleDateString('vi-VN') : 'N/A';
const formatSalary = (salary) => salary ? new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(salary) : 'N/A';
const formatPermissions = (permissions) => {
  if (!permissions || !permissions.length) return 'Không có';
  return permissions.map(p => {
    const option = permissionOptions.value.find(opt => opt.value === p);
    return option ? option.label : p;
  }).join(', ');
};

onMounted(() => {
  fetchStaff();
  const editModalEl = document.getElementById('staffEditModal');
  if (editModalEl) editModal = new Modal(editModalEl);
  const detailModalEl = document.getElementById('staffDetailModal');
  if (detailModalEl) detailModal = new Modal(detailModalEl);
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
.badge { padding: 0.4em 0.8em; font-size: 0.75rem; font-weight: 600; border-radius: 20px; letter-spacing: 0.5px; }
.badge-secondary { background-color: #f3f4f6; color: #7f8c8d; }
.badge-success { background-color: #e6f9f0; color: #2ecc71; }
.badge-warning { background-color: #fef5e7; color: #f39c12; }
.badge-primary { background-color: #e7e9fd; color: #4f46e5; }
.badge-dark { background-color: #e5e7eb; color: #1f2937; }
.action-buttons { white-space: nowrap; }
.action-buttons .btn { margin: 0 2px; }
.pagination .page-link { border: none; border-radius: 8px; margin: 0 4px; color: #7f8c8d; font-weight: 600; }
.pagination .page-item.active .page-link { background-color: #3498db; color: white; }
.modal-custom { border-radius: 16px; border: none; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); }
.modal-header-custom { background-color: #f4f7f9; border-bottom: 1px solid #e5eaee; padding: 1.5rem; }
.modal-footer-custom { background-color: #f4f7f9; border-top: 1px solid #e5eaee; padding: 1rem 1.5rem; }
.custom-alert { position: fixed; top: 20px; right: 20px; z-index: 1060; min-width: 300px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); border-radius: 8px; }
.close-btn { cursor: pointer; float: right; font-size: 1.5rem; line-height: 1; }
.form-section-title { font-size: 1.1rem; font-weight: 600; color: #3498db; margin-bottom: 1.5rem; padding-bottom: 0.5rem; border-bottom: 2px solid #e9ecef; }
.schedule-container { display: flex; flex-direction: column; gap: 0.75rem; }
.schedule-row { display: flex; gap: 0.75rem; align-items: center; }
.detail-section p { margin-bottom: 0.75rem; }
.detail-section p strong { color: #495057; display: inline-block; min-width: 150px; }
.schedule-detail-item { padding: 0.75rem; border: 1px solid #e9ecef; border-radius: 0.5rem; margin-bottom: 0.75rem; background-color: #fdfdfd; }
:deep(.multiselect__tags) { border-radius: 8px !important; border: 1px solid #e5eaee !important; font-size: 0.9rem; }
:deep(.multiselect__tag) { background: #3498db !important; }
:deep(.multiselect__input) { font-size: 0.9rem; }
:deep(.multiselect__placeholder) { color: #6c757d; }
</style>