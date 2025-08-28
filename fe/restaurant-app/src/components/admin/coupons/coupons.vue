<template>
  <div class="page-container">
    <!-- Thông báo -->
    <div v-if="showAlert" :class="['alert', alertType, 'custom-alert']" role="alert">
      {{ alertMessage }}
      <span class="close-btn" @click="showAlert = false">×</span>
    </div>

    <!-- Tiêu đề trang -->
    <div class="page-header mb-4">
      <h1 class="page-title">Quản lý Mã Giảm Giá</h1>
      <p class="page-subtitle">Tạo, chỉnh sửa và quản lý các mã giảm giá cho khách hàng.</p>
    </div>

    <!-- Nút thêm mới -->
    <!-- MODIFIED SECTION START -->
    <div class="text-end mb-4">
      <button class="btn btn-primary" @click="openCreateModal">
        <i class="bi bi-plus-circle me-2"></i>Thêm Mã Mới
      </button>
    </div>
    <!-- MODIFIED SECTION END -->

    <!-- Bảng danh sách mã giảm giá -->
    <div class="table-container">
      <table class="table booking-table align-middle">
        <thead>
          <tr>
            <th style="width: 5%;">#</th>
            <th style="width: 15%;">Mã Giảm Giá</th>
            <th style="width: 20%;">Mô Tả</th>
            <th class="text-center" style="width: 15%;">Giá trị giảm</th>
            <th class="text-center" style="width: 10%;">Sử dụng</th>
            <th class="text-center" style="width: 15%;">Hạn sử dụng</th>
            <th class="text-center" style="width: 10%;">Trạng Thái</th>
            <th class="text-center" style="width: 10%;">Hành Động</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="!discountCodes.length">
            <td colspan="8" class="text-center py-5">Không có mã giảm giá nào.</td>
          </tr>
          <tr v-for="(code, index) in discountCodes" :key="code.id">
            <td>{{ index + 1 }}</td>
            <td>
              <div class="fw-bold type-name">{{ code.code }}</div>
            </td>
            <td>
              <p class="description-text mb-0">{{ code.description || '—' }}</p>
            </td>
            <td class="text-center fw-bold text-success">{{ formatCurrency(code.discount_amount) }}</td>
            <td class="text-center">{{ code.used_count }} / {{ code.usage_limit }}</td>
            <td class="text-center" :class="{ 'text-danger': isExpired(code.expires_at) }">
              {{ formatDate(code.expires_at) }}
            </td>
            <td class="text-center">
              <span class="badge" :class="getStatusInfo(code).class">
                {{ getStatusInfo(code).text }}
              </span>
            </td>
            <td class="text-center action-buttons">
              <button class="btn btn-outline-primary btn-sm"
                :disabled="code.used_count > 0"
                :title="code.used_count > 0 ? 'Không thể sửa mã đã sử dụng' : 'Chỉnh sửa'"
                @click="openEditModal(code)">
                <i class="bi bi-pencil-fill"></i>
              </button>
              <button class="btn btn-outline-danger btn-sm"
                :disabled="code.used_count > 0"
                :title="code.used_count > 0 ? 'Không thể xóa mã đã sử dụng' : 'Xóa'"
                @click="deleteCode(code.id, code.used_count)">
                <i class="bi bi-trash-fill"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal Thêm/Sửa -->
    <div class="modal fade" id="discountCodeModal" tabindex="-1" aria-labelledby="discountCodeModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content modal-custom">
          <div class="modal-header modal-header-custom">
            <h5 class="modal-title" id="discountCodeModalLabel">{{ form.id ? 'Chỉnh sửa Mã Giảm Giá' : 'Tạo mới Mã Giảm Giá' }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
          </div>
          <form @submit.prevent="saveCode">
            <div class="modal-body p-4">
              <div class="row g-3">
                <div class="col-md-6">
                  <label for="code" class="form-label">Mã giảm giá <span class="text-danger">*</span></label>
                  <input v-model="form.code" id="code" type="text" class="form-control" required :disabled="isFormDisabled" />
                </div>
                <div class="col-md-6">
                  <label for="discount_amount" class="form-label">Số tiền giảm (VNĐ) <span class="text-danger">*</span></label>
                  <input v-model.number="form.discount_amount" id="discount_amount" type="number" class="form-control" required :disabled="isFormDisabled" />
                </div>
                 <div class="col-md-12">
                  <label for="description" class="form-label">Mô tả</label>
                  <input v-model="form.description" id="description" type="text" class="form-control" :disabled="isFormDisabled" />
                </div>
                <div class="col-md-6">
                  <label for="usage_limit" class="form-label">Giới hạn lượt dùng <span class="text-danger">*</span></label>
                  <input v-model.number="form.usage_limit" id="usage_limit" type="number" min="0" class="form-control" required :disabled="isFormDisabled" />
                </div>
                <div class="col-md-6">
                  <label for="expires_at" class="form-label">Ngày hết hạn</label>
                  <input v-model="form.expires_at" id="expires_at" type="date" class="form-control" :disabled="isFormDisabled" />
                </div>
                <div class="col-12 mt-4">
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="activeCheck" v-model="form.is_active" :disabled="isFormDisabled">
                    <label class="form-check-label" for="activeCheck">Kích hoạt mã</label>
                  </div>
                </div>
              </div>
            </div>
            <div class="modal-footer modal-footer-custom">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
              <button type="submit" class="btn btn-primary" :disabled="isFormDisabled">
                {{ form.id ? 'Lưu thay đổi' : 'Tạo mới' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, inject, computed } from 'vue';
import axios from 'axios';
import { Modal } from 'bootstrap';

const discountCodes = ref([]);
const apiUrl = inject('apiUrl');
const initialFormState = {
  id: null, code: '', description: '', discount_amount: null,
  usage_limit: 1, used_count: 0, is_active: true, expires_at: null,
};
const form = ref({ ...initialFormState });
let discountCodeModal;

// State cho thông báo
const showAlert = ref(false);
const alertMessage = ref('');
const alertType = ref('alert-success');

const isFormDisabled = computed(() => form.value.id && form.value.used_count > 0);

const showNotification = (message, type = 'success') => {
    alertType.value = type === 'success' ? 'alert-success' : 'alert-danger';
    alertMessage.value = message;
    showAlert.value = true;
    setTimeout(() => { showAlert.value = false; }, 3000);
};

onMounted(() => {
  fetchDiscountCodes();
  discountCodeModal = new Modal(document.getElementById('discountCodeModal'));
});

const fetchDiscountCodes = async () => {
  try {
    const res = await axios.get(`${apiUrl}/api/discount-codes`);
    discountCodes.value = res.data;
  } catch (error) {
    showNotification("Lỗi khi tải mã giảm giá.", 'error');
  }
};

const openCreateModal = () => {
  resetForm();
  discountCodeModal.show();
};

const openEditModal = (code) => {
  if (code.used_count > 0) {
    showNotification("Không thể chỉnh sửa mã giảm giá đã được sử dụng.", 'error');
    return;
  }
  form.value = { ...code, is_active: !!code.is_active };
  if (form.value.expires_at) {
    form.value.expires_at = new Date(form.value.expires_at).toISOString().split('T')[0];
  }
  discountCodeModal.show();
};

const saveCode = async () => {
  if (isFormDisabled.value) return;
  try {
    if (form.value.id) {
      await axios.put(`${apiUrl}/api/discount-codes/${form.value.id}`, form.value);
      showNotification("Cập nhật mã giảm giá thành công!");
    } else {
      await axios.post(`${apiUrl}/api/discount-codes`, form.value);
      showNotification("Thêm mã giảm giá mới thành công!");
    }
    await fetchDiscountCodes();
    discountCodeModal.hide();
  } catch (error) {
    const message = error.response?.data?.message || "Thao tác thất bại. Vui lòng thử lại.";
    showNotification(message, 'error');
  }
};

const deleteCode = async (id, usedCount) => {
  if (usedCount > 0) {
    showNotification("Không thể xóa mã giảm giá đã được sử dụng.", 'error');
    return;
  }
  if (confirm("Bạn có chắc chắn muốn xóa mã giảm giá này không?")) {
    try {
      await axios.delete(`${apiUrl}/api/discount-codes/${id}`);
      showNotification("Xóa mã giảm giá thành công!");
      await fetchDiscountCodes();
    } catch (error) {
      const message = error.response?.data?.message || "Xóa mã thất bại.";
      showNotification(message, 'error');
    }
  }
};

const resetForm = () => {
  form.value = { ...initialFormState };
};

const formatDate = (dateStr) => {
  if (!dateStr) return 'Vô hạn';
  return new Date(dateStr).toLocaleDateString('vi-VN');
};

const isExpired = (dateStr) => {
  if (!dateStr) return false;
  return new Date(dateStr) < new Date().setHours(0, 0, 0, 0);
};

const formatCurrency = (num) => {
  if (typeof num !== 'number') return num;
  return num.toLocaleString('vi-VN') + ' đ';
};

const getStatusInfo = (code) => {
  if (code.used_count >= code.usage_limit) {
    return { text: 'Hết lượt', class: 'badge-warning' };
  }
  if (isExpired(code.expires_at)) {
    return { text: 'Hết hạn', class: 'badge-danger' };
  }
  if (code.is_active) {
    return { text: 'Hoạt động', class: 'badge-success' };
  }
  return { text: 'Tạm ẩn', class: 'badge-secondary' };
};
</script>

<style scoped>
/* Copy toàn bộ style từ component mẫu */
@import url('https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700&display=swap');
@import url('https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css');

.page-container {
  font-family: 'Be Vietnam Pro', sans-serif;
  background-color: #f4f7f9;
  padding: 2rem;
  color: #34495e;
}
.page-header { border-bottom: 1px solid #e5eaee; padding-bottom: 1rem; }
.page-title { font-size: 2rem; font-weight: 700; }
.page-subtitle { font-size: 1rem; color: #7f8c8d; }
.form-control, .form-select { border-radius: 8px; border: 1px solid #e5eaee; transition: all 0.2s ease-in-out; font-size: 0.9rem; }
.form-control:focus, .form-select:focus { border-color: #3498db; box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.15); }

.table-container { background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); overflow-x: auto; }
.booking-table { font-size: 0.875rem; border-collapse: separate; border-spacing: 0; min-width: 900px; }
.booking-table thead th { background-color: #f8f9fa; color: #7f8c8d; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e5eaee; padding: 1rem; white-space: nowrap; }
.booking-table td { padding: 1rem; border-bottom: 1px solid #e5eaee; vertical-align: middle; }
.booking-table tbody tr:last-child td { border-bottom: none; }
.booking-table tbody tr:hover { background-color: #f9fafb; }
.type-name { font-size: 1rem; font-weight: 600; }
.description-text { font-size: 0.8rem; color: #7f8c8d; }

.badge { padding: 0.4em 0.8em; font-size: 0.75rem; font-weight: 600; border-radius: 20px; letter-spacing: 0.5px; }
.badge-secondary { background-color: #f3f4f6; color: #7f8c8d; }
.badge-success { background-color: #e6f9f0; color: #2ecc71; }
.badge-danger { background-color: #fce8e6; color: #e74c3c; }
.badge-warning { background-color: #fef5e7; color: #f39c12; }

.action-buttons { white-space: nowrap; }
.action-buttons .btn { margin: 0 2px; }

.modal-custom { border-radius: 16px; border: none; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); }
.modal-header-custom { background-color: #f4f7f9; border-bottom: 1px solid #e5eaee; padding: 1.5rem; }
.modal-footer-custom { background-color: #f4f7f9; border-top: 1px solid #e5eaee; padding: 1rem 1.5rem; }

.custom-alert { position: fixed; top: 20px; right: 20px; z-index: 1060; min-width: 300px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); border-radius: 8px; }
.close-btn { cursor: pointer; float: right; font-size: 1.5rem; line-height: 1; }
</style>