<template>
  <div class="page-container">
    <div class="page-header mb-4">
      <h1 class="page-title">Nhật ký Hoạt động (Audit Log)</h1>
      <p class="page-subtitle">Theo dõi tất cả các thay đổi quan trọng trong hệ thống.</p>
    </div>

    <!-- Bộ lọc -->
    <div class="card filter-card mb-4">
      <div class="card-body d-flex gap-2 flex-wrap">
        <input v-model="filters.q" type="text" class="form-control" placeholder="Tìm theo tên người dùng, đối tượng..." @input="debouncedFetchLogs" style="flex: 1; min-width: 250px;">
        <select v-model="filters.event" class="form-select" @change="fetchLogs(1)" style="width: auto;">
          <option value="">Tất cả hành động</option>
          <option value="created">Tạo mới</option>
          <option value="updated">Cập nhật</option>
          <option value="deleted">Xóa</option>
        </select>
        <input v-model="filters.date" type="date" class="form-control" @change="fetchLogs(1)" style="width: auto;">
      </div>
    </div>

    <!-- Bảng Log -->
    <div class="table-container">
      <table class="table booking-table align-middle">
        <thead>
          <tr>
            <th style="width: 25%;">Người Dùng</th>
            <th style="width: 15%;">Hành động</th>
            <th style="width: 30%;">Đối tượng</th>
            <th style="width: 20%;">Thời gian</th>
            <th class="text-center" style="width: 10%;">Chi tiết</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="isLoading"><td colspan="5" class="text-center py-5"><div class="spinner-border text-primary"></div></td></tr>
          <tr v-else-if="logs.length === 0"><td colspan="5" class="text-center py-5">Không có dữ liệu.</td></tr>
          <tr v-else v-for="log in logs" :key="log.id">
            <td>
              <!-- SỬA ĐỔI: Hiển thị tên và vai trò -->
              <div v-if="log.user" class="fw-bold">{{ log.user.name }}</div>
              <div v-if="log.user" class="description-text">{{ log.user.role?.display_name || 'Không rõ vai trò' }}</div>
              <div v-else class="fw-bold text-muted">Hệ thống / Vô danh</div>
            </td>
            <td><span class="badge" :class="getEventBadge(log.event)">{{ formatEvent(log.event) }}</span></td>
            <td>
              <!-- SỬA ĐỔI: Hiển thị tên đối tượng -->
              <div class="fw-bold">{{ log.auditable_name }}</div>
              <div class="description-text">{{ formatAuditable(log.auditable_type) }} #{{ log.auditable_id }}</div>
            </td>
            <td>{{ formatDateTime(log.created_at) }}</td>
            <td class="text-center">
              <button class="btn btn-outline-info btn-sm" @click="openDetailModal(log)"><i class="bi bi-eye-fill"></i></button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Phân trang -->
    <nav v-if="pagination.last_page > 1" class="mt-4 d-flex justify-content-center">
      <!-- ... Code phân trang của bạn ... -->
    </nav>
    
        <!-- SỬA ĐỔI HOÀN TOÀN: Modal Chi tiết Log (Theo giao diện mới) -->
    <div class="modal fade" id="logDetailModal" tabindex="-1">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content modal-custom" v-if="selectedLog">
          <div class="modal-header modal-header-custom">
            <h5 class="modal-title">Chi tiết thay đổi</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body p-4">
            <!-- Phần tóm tắt thông tin -->
            <div class="log-summary mb-4">
              <p><strong>Người thực hiện:</strong> {{ selectedLog.user?.name || 'Hệ thống' }}</p>
              <p><strong>Hành động:</strong> {{ formatEvent(selectedLog.event) }}</p>
              <p><strong>Đối tượng:</strong> {{ selectedLog.auditable_name }} ({{ formatAuditable(selectedLog.auditable_type) }} #{{ selectedLog.auditable_id }})</p>
              <p><strong>Thời gian:</strong> {{ formatDateTime(selectedLog.created_at) }}</p>
            </div>
            <hr>

            

          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue';
import axiosConfig from '../../axiosConfig.js';
import { Modal } from 'bootstrap';
import { format } from 'date-fns';
import { vi } from 'date-fns/locale';

const logs = ref([]);
const pagination = ref({});
const isLoading = ref(true);
const selectedLog = ref(null);
let detailModal = null;
const filters = reactive({ q: '', event: '', date: '' });
let debounceTimer = null;



const fetchLogs = async (page = 1) => {
  isLoading.value = true;
  try {
    const params = { page, ...filters };
    const response = await axiosConfig.get('/api/audit-logs', { params });
    logs.value = response.data.data;
    pagination.value = response.data;
  } catch (error) {
    console.error("Lỗi khi tải audit logs:", error);
  } finally {
    isLoading.value = false;
  }
};

const debouncedFetchLogs = () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => { fetchLogs(1); }, 500);
};

const openDetailModal = (log) => {
  selectedLog.value = log;
  detailModal.show();
};

const formatEvent = (event) => ({ created: 'Tạo mới', updated: 'Cập nhật', deleted: 'Xóa' })[event] || event;
const getEventBadge = (event) => ({ created: 'badge-success', updated: 'badge-warning', deleted: 'badge-danger' })[event] || 'badge-secondary';
const formatAuditable = (type) => (type || '').split('\\').pop();
const formatDateTime = (date) => format(new Date(date), 'HH:mm:ss, EEEE, dd/MM/yyyy', { locale: vi });

onMounted(() => {
  fetchLogs();
  detailModal = new Modal(document.getElementById('logDetailModal'));
});
</script>

<style scoped>
/* Dùng lại CSS từ các trang quản trị khác */
.page-container { font-family: 'Be Vietnam Pro', sans-serif; background-color: #f4f7f9; padding: 2rem; }
.page-header { border-bottom: 1px solid #e5eaee; padding-bottom: 1rem; }
.page-title { font-size: 2rem; font-weight: 700; }
.page-subtitle { font-size: 1rem; color: #7f8c8d; }
.filter-card { background-color: #ffffff; border: none; border-radius: 12px; }
.table-container { background-color: #ffffff; border-radius: 12px; }
.booking-table { font-size: 0.875rem; }
.booking-table td, .booking-table th { padding: 1rem; }
.description-text { font-size: 0.8rem; color: #7f8c8d; }
.modal-custom { border-radius: 16px; border: none; }
.modal-header-custom { background-color: #f4f7f9; border-bottom: 1px solid #e5eaee; }
.badge { padding: 0.4em 0.8em; font-size: 0.75rem; font-weight: 600; border-radius: 20px; }
.badge-success { background-color: #e8f9f0; color: #2ecc71; }
.badge-warning { background-color: #fff8e1; color: #f39c12; }
.badge-danger { background-color: #feeeed; color: #e74c3c; }
.log-summary p { margin-bottom: 0.5rem; }
.comparison-table { font-size: 0.85rem; }
.comparison-table pre { margin: 0; background-color: #f8f9fa; padding: 5px; border-radius: 4px; white-space: pre-wrap; word-break: break-all; }
.comparison-table .highlight { background-color: #fff3cd; }
</style>