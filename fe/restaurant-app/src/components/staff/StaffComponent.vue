<template>
  <div class="page-container">
    <!-- Page Header -->
    <div class="page-header mb-4">
      <h1 class="page-title">Quản Lý Nhân Viên</h1>
      <p class="page-subtitle">Tạo mới, chỉnh sửa và quản lý nhân viên trong khách sạn của bạn.</p>
    </div>

    <!-- Notifications -->
    <div v-if="successMessage" class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="bi bi-check-circle-fill me-2"></i>
      {{ successMessage }}
      <button type="button" class="btn-close" @click="successMessage = ''" aria-label="Close"></button>
    </div>
    <div v-if="errorMessage && !isLoading" class="alert alert-danger alert-dismissible fade show" role="alert">
      <i class="bi bi-exclamation-triangle-fill me-2"></i>
      {{ errorMessage }}
      <button type="button" class="btn-close" @click="errorMessage = ''" aria-label="Close"></button>
    </div>

    <!-- Filters and Search -->
    <div class="card filter-card mb-4">
      <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div class="d-flex align-items-center gap-2 flex-grow-1">
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input
              v-model="searchQuery"
              type="text"
              class="form-control"
              placeholder="Tìm theo tên, email, mã NV..."
              @input="currentPage = 1"
            />
          </div>
          <select v-model="filterRole" class="form-select" @change="currentPage = 1">
            <option value="">Tất cả vai trò</option>
            <option value="manager">Quản lý</option>
            <option value="receptionist">Lễ tân</option>
            <option value="housekeeping">Buồng phòng</option>
            <option value="technician">Kỹ thuật</option>
          </select>
          <select v-model="filterDepartment" class="form-select" @change="currentPage = 1">
            <option value="">Tất cả phòng ban</option>
            <option value="Quản lý">Quản lý</option>
            <option value="Lễ tân">Lễ tân</option>
            <option value="Buồng phòng">Buồng phòng</option>
            <option value="Kỹ thuật">Kỹ thuật</option>
          </select>
          <select v-model="filterLevel" class="form-select" @change="currentPage = 1">
            <option value="">Tất cả cấp bậc</option>
            <option value="junior">Nhân viên mới</option>
            <option value="senior">Nhân viên lâu năm</option>
            <option value="manager">Quản lý</option>
          </select>
        </div>
        <button class="btn btn-primary" @click="openAddModal">
          <i class="bi bi-plus-circle-fill me-2"></i>Thêm Nhân Viên
        </button>
      </div>
    </div>

    <!-- Staff Table -->
    <div class="card table-container">
      <div class="card-body p-0">
        <table v-if="displayedStaff.length > 0" class="table staff-table align-middle">
          <thead>
            <tr>
              <th>STT</th>
              <th>Họ và Tên</th>
              <th>Mã NV</th>
              <th>Vai Trò</th>
              <th>Chức Vụ</th>
              <th>SĐT</th>
              <th>Ngày Tuyển</th>
              <th>Trạng Thái</th>
              <th class="text-center">Hành Động</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(staff, index) in displayedStaff" :key="staff.id">
              <td><span style="text-align: center;">{{ (currentPage - 1) * itemsPerPage + index + 1 }}</span></td>
              <td>
                <div class="fw-bold type-name">{{ staff.name || 'Chưa có' }}</div>
                <p class="description-text mb-0">{{ staff.email || 'Không có' }}</p>
              </td>
              <td>{{ staff.staff_profile?.employee_code || 'Không có' }}</td>
              <td>{{ formatRole(staff.role) || 'Không xác định' }}</td>
              <td>{{ staff.staff_profile?.position || 'Không có' }}</td>
              <td>{{ staff.staff_profile?.phone || 'Không có' }}</td>
              <td>{{ formatDate(staff.staff_profile?.hire_date) }}</td>
              <td>
                <span :class="['badge', `status-${staff.status}`]">
                  {{ formatStatus(staff.status) }}
                </span>
              </td>
              <td class="text-center action-buttons">
                <button class="btn btn-icon btn-outline-primary" title="Sửa" @click="openEditModal(staff)">
                  <i class="bi bi-pencil-fill"></i>
                </button>
                <button class="btn btn-icon btn-outline-info" title="Xem chi tiết" @click="openDetailModal(staff)">
                  <i class="bi bi-eye-fill"></i>
                </button>
                <button class="btn btn-icon btn-outline-danger" title="Xóa" @click="deleteStaff(staff.id)">
                  <i class="bi bi-trash-fill"></i>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
        <div v-if="displayedStaff.length === 0 && !isLoading" class="text-center p-5">
          <i class="bi bi-people-fill fs-1 text-muted"></i>
          <p class="mt-3">Không tìm thấy nhân viên phù hợp.</p>
        </div>
        <div v-if="isLoading" class="d-flex justify-content-center p-5">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Đang tải...</span>
          </div>
        </div>
      </div>
       <!-- Pagination -->
      <div v-if="totalPages > 1" class="card-footer d-flex justify-content-between align-items-center">
        <div class="text-muted small">
          Hiển thị <strong>{{ displayedStaff.length }}</strong> trên tổng số <strong>{{ totalItems }}</strong> nhân viên
        </div>
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-end mb-0">
                <li class="page-item" :class="{ disabled: currentPage === 1 }">
                    <a class="page-link" href="#" @click.prevent="currentPage = 1" aria-label="First">
                        <i class="bi bi-chevron-bar-left"></i>
                    </a>
                </li>
                <li class="page-item" :class="{ disabled: currentPage === 1 }">
                    <a class="page-link" href="#" @click.prevent="currentPage--" aria-label="Previous">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                </li>
                <li v-for="page in pageRange" :key="page" class="page-item" :class="{ active: page === currentPage }">
                    <a class="page-link" href="#" @click.prevent="currentPage = page">{{ page }}</a>
                </li>
                <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                    <a class="page-link" href="#" @click.prevent="currentPage++" aria-label="Next">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
                <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                    <a class="page-link" href="#" @click.prevent="currentPage = totalPages" aria-label="Last">
                        <i class="bi bi-chevron-bar-right"></i>
                    </a>
                </li>
            </ul>
        </nav>
      </div>
    </div>

    <!-- Modal for Add/Edit Staff -->
    <div v-if="isModalOpen" class="modal-backdrop fade show"></div>
    <div v-if="isModalOpen" class="modal fade show d-block" tabindex="-1" @click.self="closeModal">
      <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content modal-custom">
          <div class="modal-header modal-header-custom">
            <h5 class="modal-title">
              <i :class="currentStaff ? 'bi bi-pencil-square' : 'bi bi-person-plus-fill'"></i>
              {{ currentStaff ? 'Cập nhật Nhân Viên' : 'Thêm Nhân Viên Mới' }}
            </h5>
            <button type="button" class="btn-close btn-close-white" @click="closeModal" aria-label="Close"></button>
          </div>
          <div class="modal-body p-4">
            <div v-if="modalErrorMessage" class="alert alert-warning">{{ modalErrorMessage }}</div>
            <form @submit.prevent="confirmSaveStaff" ref="staffForm" novalidate>
              
              <!-- Section: Thông tin cơ bản -->
              <h6 class="form-section-title">Thông Tin Cơ Bản</h6>
              <div class="row g-3 mb-4">
                <div class="col-md-6">
                  <label class="form-label">Họ và Tên <span class="text-danger">*</span></label>
                  <input v-model.trim="form.name" type="text" class="form-control" required placeholder="Ví dụ: Nguyễn Văn A" :class="{ 'is-invalid': errors.name }" ref="nameInput"/>
                  <div v-if="errors.name" class="invalid-feedback">{{ errors.name }}</div>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Email <span class="text-danger">*</span></label>
                  <input v-model.trim="form.email" type="email" class="form-control" required placeholder="Ví dụ: example@domain.com" :class="{ 'is-invalid': errors.email }"/>
                  <div v-if="errors.email" class="invalid-feedback">{{ errors.email }}</div>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Vai Trò <span class="text-danger">*</span></label>
                  <select v-model="form.role" class="form-select" required :class="{ 'is-invalid': errors.role }" @change="updatePermissions">
                    <option disabled value="">-- Chọn vai trò --</option>
                    <option value="manager">Quản lý</option>
                    <option value="receptionist">Lễ tân</option>
                    <option value="housekeeping">Buồng phòng</option>
                    <option value="technician">Kỹ thuật</option>
                  </select>
                  <div v-if="errors.role" class="invalid-feedback">{{ errors.role }}</div>
                </div>
                 <div class="col-md-6">
                  <label class="form-label">Trạng Thái <span class="text-danger">*</span></label>
                  <select v-model="form.status" class="form-select" required :class="{ 'is-invalid': errors.status }">
                    <option disabled value="">-- Chọn trạng thái --</option>
                    <option value="active">Hoạt động</option>
                    <option value="inactive">Nghỉ việc</option>
                    <option value="suspended">Tạm ngưng</option>
                  </select>
                  <div v-if="errors.status" class="invalid-feedback">{{ errors.status }}</div>
                </div>
                <div class="col-12">
                  <label class="form-label">Quyền Hạn</label>
                  <multiselect v-model="form.permissions" :options="permissionOptions" :multiple="true" track-by="value" label="label" :close-on-select="false" placeholder="Chọn quyền hạn" :class="{ 'is-invalid': errors.permissions }"/>
                  <div v-if="errors.permissions" class="invalid-feedback d-block">{{ errors.permissions }}</div>
                </div>
              </div>
              <hr class="my-4">

              <!-- Section: Hồ sơ nhân viên -->
              <h6 class="form-section-title">Hồ Sơ Nhân Viên</h6>
              <div class="row g-3 mb-4">
                  <div class="col-md-4">
                    <label class="form-label">Mã Nhân Viên <span class="text-danger">*</span></label>
                    <input v-model.trim="form.employee_code" type="text" class="form-control" required placeholder="Ví dụ: NV001" :class="{ 'is-invalid': errors.employee_code }"/>
                    <div v-if="errors.employee_code" class="invalid-feedback">{{ errors.employee_code }}</div>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Số Điện Thoại</label>
                    <input v-model.trim="form.phone" type="text" class="form-control" placeholder="Ví dụ: 0901234567" :class="{ 'is-invalid': errors.phone }"/>
                    <div v-if="errors.phone" class="invalid-feedback">{{ errors.phone }}</div>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Ngày Tuyển Dụng</label>
                    <input v-model="form.hire_date" type="date" class="form-control" :class="{ 'is-invalid': errors.hire_date }"/>
                    <div v-if="errors.hire_date" class="invalid-feedback">{{ errors.hire_date }}</div>
                  </div>
                  <div class="col-12">
                    <label class="form-label">Địa Chỉ</label>
                    <input v-model.trim="form.address" type="text" class="form-control" placeholder="Ví dụ: 123 Đường ABC, Quận 1, TP. HCM" :class="{ 'is-invalid': errors.address }"/>
                    <div v-if="errors.address" class="invalid-feedback">{{ errors.address }}</div>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Phòng Ban</label>
                    <input v-model.trim="form.department" type="text" class="form-control" placeholder="Ví dụ: Bộ phận Lễ tân" :class="{ 'is-invalid': errors.department }"/>
                    <div v-if="errors.department" class="invalid-feedback">{{ errors.department }}</div>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Chức Vụ</label>
                    <input v-model.trim="form.position" type="text" class="form-control" placeholder="Ví dụ: Trưởng phòng" :class="{ 'is-invalid': errors.position }"/>
                    <div v-if="errors.position" class="invalid-feedback">{{ errors.position }}</div>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Cấp Bậc</label>
                    <select v-model="form.level" class="form-select" :class="{ 'is-invalid': errors.level }">
                      <option disabled value="">-- Chọn cấp bậc --</option>
                      <option value="junior">Nhân viên mới</option>
                      <option value="senior">Nhân viên lâu năm</option>
                      <option value="manager">Quản lý</option>
                    </select>
                    <div v-if="errors.level" class="invalid-feedback">{{ errors.level }}</div>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Lương (VND)</label>
                    <input v-model.number="form.salary" type="number" class="form-control" placeholder="Ví dụ: 10000000" :class="{ 'is-invalid': errors.salary }"/>
                    <div v-if="errors.salary" class="invalid-feedback">{{ errors.salary }}</div>
                  </div>
              </div>
              <hr class="my-4">
              
              <!-- Section: Lịch làm việc -->
              <h6 class="form-section-title">Lịch Làm Việc</h6>
              <div class="schedule-container">
                  <div v-if="!form.schedules.length" class="text-center p-3 bg-light rounded">
                      Chưa có lịch làm việc. Nhấn "Thêm Ca Làm" để bắt đầu.
                  </div>
                  <div v-for="(schedule, index) in form.schedules" :key="index" class="schedule-row">
                      <input v-model="schedule.shift_date" type="date" class="form-control" :class="{ 'is-invalid': errors[`schedules.${index}.shift_date`] }"/>
                      <input v-model="schedule.start_time" type="time" class="form-control" :class="{ 'is-invalid': errors[`schedules.${index}.start_time`] }"/>
                      <input v-model="schedule.end_time" type="time" class="form-control" :class="{ 'is-invalid': errors[`schedules.${index}.end_time`] }"/>
                      <input v-model="schedule.task" type="text" class="form-control flex-grow-1" placeholder="Nhiệm vụ" :class="{ 'is-invalid': errors[`schedules.${index}.task`] }"/>
                      <button type="button" class="btn btn-outline-danger btn-sm" @click="removeScheduleRow(index)"><i class="bi bi-trash-fill"></i></button>
                  </div>
                  <button type="button" class="btn btn-outline-primary mt-3" @click="addScheduleRow">
                    <i class="bi bi-plus-circle me-2"></i>Thêm Ca Làm
                  </button>
              </div>

            </form>
          </div>
          <div class="modal-footer modal-footer-custom">
            <button type="button" class="btn btn-secondary" @click="closeModal" :disabled="isLoading">Hủy</button>
            <button type="button" class="btn btn-primary" @click="confirmSaveStaff" :disabled="isLoading">
              <span v-if="isLoading" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
              {{ isLoading ? 'Đang lưu...' : 'Lưu Lại' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal for Staff Details -->
    <div v-if="isDetailModalOpen" class="modal-backdrop fade show"></div>
    <div v-if="isDetailModalOpen" class="modal fade show d-block" tabindex="-1" @click.self="closeDetailModal">
      <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content modal-custom">
          <div class="modal-header modal-header-custom">
            <h5 class="modal-title"><i class="bi bi-person-lines-fill me-2"></i>Chi Tiết Nhân Viên</h5>
            <button type="button" class="btn-close btn-close-white" @click="closeDetailModal" aria-label="Close"></button>
          </div>
          <div class="modal-body p-4">
            <div v-if="selectedStaff">
              <div class="detail-section">
                <h6 class="form-section-title">Thông Tin Cơ Bản</h6>
                <div class="row">
                  <div class="col-md-6"><p><strong>Họ và Tên:</strong> {{ selectedStaff.name || 'N/A' }}</p></div>
                  <div class="col-md-6"><p><strong>Email:</strong> {{ selectedStaff.email || 'N/A' }}</p></div>
                  <div class="col-md-6"><p><strong>Vai Trò:</strong> {{ formatRole(selectedStaff.role) || 'N/A' }}</p></div>
                  <div class="col-md-6"><p><strong>Trạng Thái:</strong> <span :class="['badge', `status-${selectedStaff.status}`]">{{ formatStatus(selectedStaff.status) }}</span></p></div>
                  <div class="col-12"><p><strong>Quyền Hạn:</strong> {{ formatPermissions(selectedStaff.permissions) || 'Không có' }}</p></div>
                </div>
              </div>
              <hr class="my-4">
              <div class="detail-section">
                 <h6 class="form-section-title">Hồ Sơ Nhân Viên</h6>
                 <div class="row">
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
              <div class="detail-section">
                <h6 class="form-section-title">Lịch Làm Việc</h6>
                <div v-if="selectedStaff.staff_schedules && selectedStaff.staff_schedules.length > 0">
                  <div class="schedule-detail-item" v-for="schedule in selectedStaff.staff_schedules" :key="schedule.id">
                      <div>
                        <span class="fw-bold"><i class="bi bi-calendar-check me-2"></i>{{ formatDate(schedule.shift_date) }}</span>
                        <span class="text-muted mx-2">|</span>
                        <span><i class="bi bi-clock me-1"></i>{{ formatTime(schedule.start_time) }} - {{ formatTime(schedule.end_time) }}</span>
                      </div>
                      <div class="text-muted ps-4 mt-1"><em>Nhiệm vụ: {{ schedule.task }}</em></div>
                  </div>
                </div>
                <div v-else class="text-center p-3 bg-light rounded">Chưa có lịch làm việc.</div>
              </div>
            </div>
            <div v-else class="text-center p-3 bg-light rounded">Không có dữ liệu nhân viên.</div>
          </div>
          <div class="modal-footer modal-footer-custom">
            <button type="button" class="btn btn-secondary" @click="closeDetailModal">Đóng</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, nextTick } from 'vue';
import axios from 'axios';
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.css';

// API Client Configuration
const apiClient = axios.create({
  baseURL: 'http://127.0.0.1:8000/api',
  timeout: 30000,
  headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }
});

// State
const staffList = ref([]);
const searchQuery = ref('');
const filterRole = ref('');
const filterDepartment = ref('');
const filterLevel = ref('');
const isModalOpen = ref(false);
const isDetailModalOpen = ref(false);
const selectedStaff = ref(null);
const isLoading = ref(false);
const currentStaff = ref(null);
const successMessage = ref('');
const errorMessage = ref('');
const modalErrorMessage = ref('');
const currentPage = ref(1);
const itemsPerPage = ref(10);
const totalItems = ref(0);
const form = ref({
  name: '',
  email: '',
  role: '',
  status: 'active',
  permissions: [],
  employee_code: '',
  phone: '',
  address: '',
  hire_date: '',
  position: '',
  department: '',
  salary: null,
  level: '',
  schedules: []
});
const errors = ref({});
const nameInput = ref(null);

// Permission Options
const permissionOptions = [
  { value: 'manage_bookings', label: 'Quản lý đặt phòng' },
  { value: 'manage_staff', label: 'Quản lý nhân viên' },
  { value: 'manage_reports', label: 'Quản lý báo cáo' },
  { value: 'manage_cleaning', label: 'Quản lý dọn dẹp' },
  { value: 'manage_maintenance', label: 'Quản lý bảo trì' }
];

// Fetch Staff
const fetchStaff = async (page = 1) => {
  isLoading.value = true;
  errorMessage.value = '';
  try {
    const response = await apiClient.get(`/staffs?page=${page}&per_page=${itemsPerPage.value}`);
    staffList.value = Array.isArray(response.data.data) ? response.data.data.map(staff => ({
      ...staff,
      staff_profile: staff.staff_profile || {},
      staff_schedules: staff.staff_schedules || [],
      permissions: typeof staff.permissions === 'string' ? JSON.parse(staff.permissions || '[]') : (staff.permissions || [])
    })) : [];
    totalItems.value = response.data.total || staffList.value.length;
    currentPage.value = response.data.current_page || 1;
  } catch (error) {
    console.error('Fetch staff error:', error.response?.data || error.message);
    errorMessage.value = error.response?.data?.message || 'Không thể tải danh sách nhân viên.';
    staffList.value = [];
    totalItems.value = 0;
  } finally {
    isLoading.value = false;
  }
};

// Initialize Data
onMounted(() => {
  fetchStaff();
});

// Watch for Page Changes
watch(currentPage, (newPage) => {
  fetchStaff(newPage);
});

// Filter Staff
const filteredStaff = computed(() => {
  if (!Array.isArray(staffList.value)) return [];
  return staffList.value.filter(staff => {
    const name = staff.name?.toLowerCase() || '';
    const email = staff.email?.toLowerCase() || '';
    const position = staff.staff_profile?.position?.toLowerCase() || '';
    const employee_code = staff.staff_profile?.employee_code?.toLowerCase() || '';
    const matchesSearch = name.includes(searchQuery.value.toLowerCase()) ||
                         email.includes(searchQuery.value.toLowerCase()) ||
                         position.includes(searchQuery.value.toLowerCase()) ||
                         employee_code.includes(searchQuery.value.toLowerCase());
    const matchesRole = !filterRole.value || staff.role === filterRole.value;
    const matchesDepartment = !filterDepartment.value || staff.staff_profile?.department === filterDepartment.value;
    const matchesLevel = !filterLevel.value || staff.staff_profile?.level === filterLevel.value;
    return matchesSearch && matchesRole && matchesDepartment && matchesLevel;
  });
});

// Pagination
const totalPages = computed(() => Math.ceil(totalItems.value / itemsPerPage.value));
const displayedStaff = computed(() => filteredStaff.value);
const pageRange = computed(() => {
  const maxPages = 5;
  let start = Math.max(1, currentPage.value - Math.floor(maxPages / 2));
  let end = Math.min(totalPages.value, start + maxPages - 1);
  if (end - start + 1 < maxPages) {
    start = Math.max(1, end - maxPages + 1);
  }
  return Array.from({ length: end - start + 1 }, (_, i) => start + i);
});

// Modal Actions
const openAddModal = () => {
  form.value = {
    name: '',
    email: '',
    role: '',
    status: 'active',
    permissions: [],
    employee_code: '',
    phone: '',
    address: '',
    hire_date: '',
    position: '',
    department: '',
    salary: null,
    level: '',
    schedules: []
  };
  errors.value = {};
  currentStaff.value = null;
  isModalOpen.value = true;
  successMessage.value = '';
  modalErrorMessage.value = '';
  nextTick(() => {
    nameInput.value?.focus();
  });
};

const openEditModal = (staff) => {
  if (!staff || typeof staff !== 'object') {
    console.error('Invalid staff data:', staff);
    errorMessage.value = 'Dữ liệu nhân viên không hợp lệ.';
    return;
  }
  form.value = {
    name: String(staff.name || ''),
    email: String(staff.email || ''),
    role: String(staff.role || ''),
    status: String(staff.status || 'active'),
    permissions: (Array.isArray(staff.permissions) ? staff.permissions : []).map(p => 
      permissionOptions.find(opt => opt.value === p) || { value: p, label: p }
    ).filter(Boolean),
    employee_code: String(staff.staff_profile?.employee_code || ''),
    phone: String(staff.staff_profile?.phone || ''),
    address: String(staff.staff_profile?.address || ''),
    hire_date: staff.staff_profile?.hire_date ? String(staff.staff_profile.hire_date).split('T')[0] : '',
    position: String(staff.staff_profile?.position || ''),
    department: String(staff.staff_profile?.department || ''),
    salary: Number(staff.staff_profile?.salary) || null,
    level: String(staff.staff_profile?.level || ''),
    schedules: (staff.staff_schedules || []).map(schedule => ({
      id: schedule.id,
      shift_date: schedule.shift_date ? String(schedule.shift_date).split('T')[0] : '',
      start_time: schedule.start_time ? schedule.start_time.slice(0, 5) : '',
      end_time: schedule.end_time ? schedule.end_time.slice(0, 5) : '',
      task: schedule.task || ''
    }))
  };
  currentStaff.value = { ...staff };
  isModalOpen.value = true;
  errors.value = {};
  successMessage.value = '';
  modalErrorMessage.value = '';
  nextTick(() => {
    nameInput.value?.focus();
  });
};

const closeModal = () => {
  isModalOpen.value = false;
  errors.value = {};
  modalErrorMessage.value = '';
  currentStaff.value = null;
};

// Modal Chi Tiết
const openDetailModal = (staff) => {
  if (!staff || typeof staff !== 'object') {
    console.error('Invalid staff data for detail modal:', staff);
    errorMessage.value = 'Không thể hiển thị chi tiết nhân viên.';
    return;
  }
  selectedStaff.value = {
    ...staff,
    permissions: typeof staff.permissions === 'string' ? JSON.parse(staff.permissions || '[]') : (staff.permissions || []),
    staff_profile: staff.staff_profile || {},
    staff_schedules: staff.staff_schedules || []
  };
  isDetailModalOpen.value = true;
};

const closeDetailModal = () => {
  isDetailModalOpen.value = false;
  selectedStaff.value = null;
};

// Schedule Management
const addScheduleRow = () => {
  form.value.schedules.push({
    shift_date: '',
    start_time: '',
    end_time: '',
    task: ''
  });
};

const removeScheduleRow = (index) => {
  form.value.schedules.splice(index, 1);
};

// Update Permissions Based on Role
const updatePermissions = () => {
  const rolePermissions = {
    manager: ['manage_bookings', 'manage_staff', 'manage_reports'],
    receptionist: ['manage_bookings'],
    housekeeping: ['manage_cleaning'],
    technician: ['manage_maintenance']
  };
  const permissionValues = rolePermissions[form.value.role] || [];
  form.value.permissions = permissionOptions.filter(opt => permissionValues.includes(opt.value));
};

// Format Time to H:i
const formatTime = (time) => {
  if (!time) return 'N/A';
  const [hours, minutes] = time.split(':').slice(0, 2);
  return `${hours.padStart(2, '0')}:${minutes.padStart(2, '0')}`;
};

// Form Validation
const validateForm = () => {
  errors.value = {};
  let isValid = true;

  // Validate basic info
  if (!form.value.name || form.value.name.trim().length === 0) {
    errors.value.name = 'Vui lòng nhập họ và tên';
    isValid = false;
  }
  if (!form.value.email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.value.email)) {
    errors.value.email = 'Vui lòng nhập email hợp lệ';
    isValid = false;
  }
  if (!form.value.role) {
    errors.value.role = 'Vui lòng chọn vai trò';
    isValid = false;
  }
  if (!form.value.status) {
    errors.value.status = 'Vui lòng chọn trạng thái';
    isValid = false;
  }
  if (!form.value.employee_code || form.value.employee_code.trim().length === 0) {
    errors.value.employee_code = 'Vui lòng nhập mã nhân viên';
    isValid = false;
  }
  if (form.value.phone && !/^\d{10,11}$/.test(form.value.phone)) {
    errors.value.phone = 'Số điện thoại phải có 10-11 chữ số';
    isValid = false;
  }
  if (form.value.salary && form.value.salary < 0) {
    errors.value.salary = 'Lương phải lớn hơn hoặc bằng 0';
    isValid = false;
  }
  if (form.value.hire_date && !/^\d{4}-\d{2}-\d{2}$/.test(form.value.hire_date)) {
    errors.value.hire_date = 'Ngày tuyển dụng không hợp lệ';
    isValid = false;
  }

  // Validate schedules only if status is active
  if (form.value.status === 'active') {
    form.value.schedules.forEach((schedule, index) => {
      if (!schedule.shift_date || !/^\d{4}-\d{2}-\d{2}$/.test(schedule.shift_date)) {
        errors.value[`schedules.${index}.shift_date`] = 'Vui lòng nhập ngày ca hợp lệ';
        isValid = false;
      }
      if (!schedule.start_time || !/^[0-2][0-9]:[0-5][0-9]$/.test(schedule.start_time)) {
        errors.value[`schedules.${index}.start_time`] = 'Giờ bắt đầu phải có định dạng HH:mm (ví dụ: 09:00)';
        isValid = false;
      }
      if (!schedule.end_time || !/^[0-2][0-9]:[0-5][0-9]$/.test(schedule.end_time)) {
        errors.value[`schedules.${index}.end_time`] = 'Giờ kết thúc phải có định dạng HH:mm (ví dụ: 17:00)';
        isValid = false;
      }
      if (schedule.start_time && schedule.end_time && schedule.start_time >= schedule.end_time) {
        errors.value[`schedules.${index}.end_time`] = 'Giờ kết thúc phải sau giờ bắt đầu';
        isValid = false;
      }
      if (!schedule.task || schedule.task.trim().length === 0) {
        errors.value[`schedules.${index}.task`] = 'Vui lòng nhập nhiệm vụ';
        isValid = false;
      }
    });
  }

  return isValid;
};

// Save Staff
const confirmSaveStaff = async () => {
  if (!confirm(currentStaff.value ? 'Bạn có chắc chắn muốn cập nhật nhân viên này?' : 'Bạn có chắc chắn muốn thêm nhân viên này?')) return;
  if (!validateForm()) {
    modalErrorMessage.value = 'Vui lòng kiểm tra lại các trường thông tin bắt buộc.';
    return;
  }
  isLoading.value = true;
  modalErrorMessage.value = '';
  const permissionValues = form.value.permissions.map(p => p.value);
  const payload = {
    ...form.value,
    permissions: permissionValues,
    salary: Number(form.value.salary) || null,
    schedules: form.value.status === 'active' ? form.value.schedules.map(schedule => ({
      id: schedule.id || null,
      shift_date: schedule.shift_date || null,
      start_time: schedule.start_time ? schedule.start_time.slice(0, 5) : null,
      end_time: schedule.end_time ? schedule.end_time.slice(0, 5) : null,
      task: schedule.task || null
    })).filter(schedule => schedule.shift_date && schedule.start_time && schedule.end_time && schedule.task) : []
  };

  try {
    let response;
    if (currentStaff.value) {
      response = await apiClient.put(`/staffs/${currentStaff.value.id}`, payload);
      const index = staffList.value.findIndex(s => s.id === currentStaff.value.id);
      if (index !== -1) {
        staffList.value[index] = {
          ...response.data.data,
          staff_profile: response.data.data.staff_profile || {},
          staff_schedules: response.data.data.staff_schedules || [],
          permissions: typeof response.data.data.permissions === 'string' ? 
            JSON.parse(response.data.data.permissions || '[]') : 
            (response.data.data.permissions || [])
        };
      }
    } else {
      response = await apiClient.post('/staffs', payload);
      staffList.value.push({
        ...response.data.data,
        staff_profile: response.data.data.staff_profile || {},
        staff_schedules: response.data.data.staff_schedules || [],
        permissions: typeof response.data.data.permissions === 'string' ? 
          JSON.parse(response.data.data.permissions || '[]') : 
          (response.data.data.permissions || [])
      });
      totalItems.value++;
    }
    successMessage.value = response.data.message || (currentStaff.value ? 'Cập nhật nhân viên thành công!' : 'Thêm nhân viên thành công!');
    closeModal();
    fetchStaff(currentPage.value);
    setTimeout(() => { successMessage.value = '' }, 4000);
  } catch (error) {
    console.error('Save staff error:', error.response?.data || error.message);
    modalErrorMessage.value = error.response?.data?.message || 'Lưu nhân viên thất bại.';
    if (error.response?.status === 422) {
      errors.value = error.response.data?.errors || {};
      const errorMessages = Object.values(errors.value).flat().join(', ');
      modalErrorMessage.value = `Dữ liệu không hợp lệ: ${errorMessages}`;
    }
  } finally {
    isLoading.value = false;
  }
};

// Delete Staff
const deleteStaff = async (staff_id) => {
  if (!confirm('Bạn có chắc chắn muốn xóa nhân viên này? Thao tác này không thể hoàn tác.')) return;
  isLoading.value = true;
  errorMessage.value = '';
  try {
    await apiClient.delete(`/staffs/${staff_id}`);
    staffList.value = staffList.value.filter(s => s.id !== staff_id);
    totalItems.value = Math.max(0, totalItems.value - 1);
    if (displayedStaff.value.length === 0 && currentPage.value > 1) {
      currentPage.value--;
    }
    successMessage.value = 'Xóa nhân viên thành công!';
    setTimeout(() => { successMessage.value = '' }, 3000);
  } catch (error) {
    console.error('Delete staff error:', error.response?.data || error.message);
    errorMessage.value = error.response?.data?.message || 'Xóa nhân viên thất bại.';
  } finally {
    isLoading.value = false;
  }
};

// Utility Functions
const formatRole = (role) => {
  const roles = { manager: 'Quản lý', receptionist: 'Lễ tân', housekeeping: 'Buồng phòng', technician: 'Kỹ thuật' };
  return roles[role] || role || 'N/A';
};

const formatLevel = (level) => {
  const levels = { junior: 'Nhân viên mới', senior: 'Nhân viên lâu năm', manager: 'Quản lý' };
  return levels[level] || 'Chưa có';
};

const formatStatus = (status) => {
  const statuses = { active: 'Hoạt động', inactive: 'Nghỉ việc', suspended: 'Tạm ngưng' };
  return statuses[status] || status || 'N/A';
};

const formatSalary = (salary) => {
  return salary ? new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(salary) : 'Chưa có';
};

const formatDate = (date) => {
  if (!date) return 'Chưa có';
  const options = { day: '2-digit', month: '2-digit', year: 'numeric' };
  return new Date(date).toLocaleDateString('vi-VN', options);
};

const formatPermissions = (permissions) => {
  if (!permissions || !Array.isArray(permissions) || permissions.length === 0) return 'Không có';
  const permissionNames = {
    manage_bookings: 'QL đặt phòng',
    manage_staff: 'QL nhân viên',
    manage_reports: 'QL báo cáo',
    manage_cleaning: 'QL dọn dẹp',
    manage_maintenance: 'QL bảo trì'
  };
  return permissions.map(p => permissionNames[p] || p).join(', ');
};
</script>

<style scoped>
/* Giữ nguyên style bạn cung cấp */
.page-container {
  background-color: #f8f9fa;
  padding: 2rem;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.page-title {
  font-weight: 700;
  color: #343a40;
}

.page-subtitle {
  color: #6c757d;
  font-size: 1.1rem;
}

/* Card Styling */
.card {
  border: none;
  border-radius: 0.75rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
  transition: all 0.3s ease-in-out;
}

.card:hover {
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
  transform: translateY(-2px);
}

.card-footer {
    background-color: #fdfdfd;
    border-top: 1px solid #e9ecef;
    border-bottom-left-radius: 0.75rem;
    border-bottom-right-radius: 0.75rem;
}

/* Filter Card */
.filter-card .form-control,
.filter-card .form-select {
  border-radius: 0.5rem;
}

.filter-card .input-group-text {
    background-color: #e9ecef;
    border-color: #ced4da;
}

/* Table Styling */
.table-container {
  overflow: hidden; /* To make border-radius work with the table */
}

.staff-table {
  margin-bottom: 0;
}

.staff-table thead th {
  background-color: #f8f9fa;
  color: #495057;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-size: 0.8rem;
  border-bottom: 2px solid #dee2e6;
  padding: 1rem;
}

.staff-table tbody tr {
  transition: background-color 0.2s ease;
}

.staff-table tbody tr:hover {
  background-color: #f1f3f5;
}

.staff-table tbody td {
  padding: 1rem;
  vertical-align: middle;
}

.type-name {
  color: #212529;
  font-weight: 600 !important;
}

.description-text {
  font-size: 0.85rem;
  color: #6c757d;
}

/* Status Badges */
.badge {
  padding: 0.5em 0.75em;
  font-size: 0.75rem;
  font-weight: 600;
  border-radius: 50px;
}
.status-active { background-color: rgba(40, 167, 69, 0.1); color: #1a936f; }
.status-inactive { background-color: rgba(108, 117, 125, 0.1); color: #6c757d; }
.status-suspended { background-color: rgba(255, 193, 7, 0.1); color: #ff9800; }

/* Action Buttons */
.action-buttons {
  gap: 0.5rem;
  justify-content: center;
}

.btn-icon {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
  margin: 2px;
}

/* Pagination */
.pagination .page-item .page-link {
    border-radius: 0.3rem;
    margin: 0 2px;
    border: none;
    color: #0d6efd;
    transition: all 0.2s ease;
}
.pagination .page-item.active .page-link {
    background-color: #0d6efd;
    color: white;
    box-shadow: 0 2px 8px rgba(13, 110, 253, 0.3);
}
.pagination .page-item.disabled .page-link {
    color: #6c757d;
    background-color: #e9ecef;
}
.pagination .page-item .page-link:hover {
    background-color: #e9ecef;
}
.pagination .page-item.active .page-link:hover {
    background-color: #0b5ed7;
}

/* Modal Styling */
.modal-custom {
  border: none;
  border-radius: 0.75rem;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.modal-header-custom {
  background: linear-gradient(to right, #007bff, #0056b3);
  color: white;
  border-top-left-radius: 0.75rem;
  border-top-right-radius: 0.75rem;
  padding: 1.25rem;
}
.modal-header-custom .modal-title {
  font-weight: 600;
}
.modal-footer-custom {
  background-color: #f8f9fa;
  border-bottom-left-radius: 0.75rem;
  border-bottom-right-radius: 0.75rem;
  padding: 1rem;
}

.modal-body {
  background-color: #ffffff;
}

.form-section-title {
  font-size: 1.1rem;
  font-weight: 600;
  color: #007bff;
  margin-bottom: 1.5rem;
  padding-bottom: 0.5rem;
  border-bottom: 2px solid #e9ecef;
}

.form-control, .form-select {
    transition: all 0.2s ease-in-out;
}
.form-control:focus, .form-select:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.25rem rgba(0, 123, 255, 0.25);
}

.schedule-container {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}
.schedule-row {
  display: flex;
  gap: 0.75rem;
  align-items: center;
}

/* Vue Multiselect Override */
:deep(.multiselect__tags) {
    border-radius: 0.375rem;
    border: 1px solid #ced4da;
}
:deep(.multiselect__tag) {
    background: #0d6efd;
}
:deep(.multiselect__tag-icon):after {
    color: #fff;
}
:deep(.multiselect__tag-icon):focus,
:deep(.multiselect__tag-icon):hover {
    background: #0b5ed7;
}

/* Detail Modal */
.detail-section p {
  margin-bottom: 0.75rem;
}
.detail-section p strong {
  color: #495057;
  display: inline-block;
  width: 150px; /* Align text */
}
.schedule-detail-item {
  padding: 0.75rem;
  border: 1px solid #e9ecef;
  border-radius: 0.5rem;
  margin-bottom: 0.75rem;
  background-color: #fdfdfd;
}
</style>