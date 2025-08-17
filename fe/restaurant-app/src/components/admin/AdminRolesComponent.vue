<!-- components/admin/AdminRolesComponent.vue -->
<template>
  <div class="page-container">
    <!-- Tiêu đề trang và nút Thêm mới -->
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
      <div>
        <h1 class="page-title">Quản lý Vai trò</h1>
        <p class="page-subtitle">Tạo và gán các quyền hạn cho từng vai trò trong hệ thống.</p>
      </div>
      <button class="btn btn-primary" @click="openModal()"><i class="bi bi-plus-circle me-2"></i> Thêm Vai trò mới</button>
    </div>

    <!-- Bảng danh sách vai trò -->
    <div class="table-container">
       <table class="table booking-table align-middle">
        <thead>
          <tr>
            <th style="width: 25%;">Vai trò</th>
            <th style="width: 60%;">Quyền Hạn Được Gán</th>
            <th class="text-center" style="width: 15%;">Hành Động</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="role in roles" :key="role.id">
            <td>
                <div class="fw-bold type-name">{{ role.display_name }}</div>
                <p class="description-text mb-0"><code>{{ role.name }}</code></p>
            </td>
            <td>
              <div class="tags-container">
                  <span v-if="role.name === 'admin'" class="badge badge-success"><i class="bi bi-star-fill me-1"></i>Toàn quyền hệ thống</span>
                  <span v-else-if="!role.permissions.length" class="badge badge-secondary">Không có quyền</span>
                  <span v-else v-for="p in role.permissions" :key="p.id" class="badge badge-info">{{ p.display_name }}</span>
              </div>
            </td>
            <td class="text-center action-buttons">
              <button class="btn btn-outline-primary btn-sm" @click="openModal(role)" :disabled="isSystemRole(role.name)" title="Sửa">
                <i class="bi bi-pencil-fill"></i>
              </button>
              <button class="btn btn-outline-danger btn-sm" @click="deleteRole(role.id)" :disabled="isSystemRole(role.name)" title="Xóa">
                <i class="bi bi-trash-fill"></i>
              </button>
            </td>
          </tr>
          <tr v-if="!roles.length">
            <td colspan="3" class="text-center py-5">Chưa có vai trò nào.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal Thêm/Sửa Vai Trò -->
    <div class="modal fade" id="roleModal" tabindex="-1">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content modal-custom">
          <div class="modal-header modal-header-custom">
            <h5 class="modal-title">{{ form.id ? 'Chỉnh sửa Vai Trò' : 'Tạo Vai Trò Mới' }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body p-4">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Tên hiển thị</label>
                    <input type="text" class="form-control" v-model="form.display_name" placeholder="Ví dụ: Quản lý Kho">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Tên định danh (name)</label>
                    <input type="text" class="form-control" v-model="form.name" placeholder="Ví dụ: warehouse_manager">
                    <small class="form-text text-muted">Viết liền, không dấu, không trùng lặp.</small>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Chọn các quyền cho vai trò này</label>
                <div class="checkbox-list">
                    <div class="form-check form-switch" v-for="p in allPermissions" :key="p.id">
                        <input class="form-check-input" type="checkbox" role="switch" :value="p.id" v-model="form.permission_ids" :id="'p-'+p.id">
                        <label class="form-check-label" :for="'p-'+p.id">{{ p.display_name }}</label>
                    </div>
                </div>
            </div>
          </div>
          <div class="modal-footer modal-footer-custom">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
            <button type="button" @click="saveRole" class="btn btn-primary">Lưu Thay Đổi</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, inject } from 'vue';
import axios from 'axios';
import { Modal } from 'bootstrap';

const apiUrl = inject('apiUrl');
const roles = ref([]);
const allPermissions = ref([]);
const form = ref({});
let modal = null;
const axiosConfig = axios.create({ baseURL: apiUrl, headers: { 'Authorization': `Bearer ${localStorage.getItem('tokenJwt')}` } });

const fetchRoles = async () => { roles.value = (await axiosConfig.get('/api/roles')).data; };
const fetchAllPermissions = async () => { allPermissions.value = (await axiosConfig.get('/api/permissions')).data; };

const isSystemRole = (name) => ['admin', 'client'].includes(name);

const openModal = (role = null) => {
    if (role) {
        form.value = { 
            ...role, 
            permission_ids: role.permissions.map(p => p.id) // Lấy mảng ID từ object permissions
        };
    } else {
        form.value = { name: '', display_name: '', permission_ids: [] };
    }
    modal.show();
};

const saveRole = async () => {
    try {
        if (form.value.id) {
            await axiosConfig.put(`/api/roles/${form.value.id}`, form.value);
        } else {
            await axiosConfig.post('/api/roles', form.value);
        }
        await fetchRoles();
        modal.hide();
    } catch (e) { alert('Lỗi: ' + e.response?.data?.message); }
};

const deleteRole = async (id) => {
    if (confirm('Bạn chắc chắn muốn xóa vai trò này?')) {
        await axiosConfig.delete(`/api/roles/${id}`);
        await fetchRoles();
    }
};

onMounted(async () => {
    await Promise.all([fetchRoles(), fetchAllPermissions()]);
    modal = new Modal(document.getElementById('roleModal'));
});
</script>

<style scoped>
/* Dùng chung CSS cho các trang quản trị */
@import url('https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700&display=swap');
.page-container { font-family: 'Be Vietnam Pro', sans-serif; background-color: #f4f7f9; padding: 2rem; color: #34495e; }
.page-header { border-bottom: 1px solid #e5eaee; padding-bottom: 1rem; }
.page-title { font-size: 2rem; font-weight: 700; }
.page-subtitle { font-size: 1rem; color: #7f8c8d; }
.table-container { background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); overflow-x: auto; }
.booking-table { font-size: 0.875rem; border-collapse: separate; border-spacing: 0; min-width: 800px; }
.booking-table thead th { background-color: #f8f9fa; color: #7f8c8d; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e5eaee; padding: 1rem; white-space: nowrap; }
.booking-table td { padding: 1.25rem 1rem; border-bottom: 1px solid #e5eaee; }
.booking-table tbody tr:last-child td { border-bottom: none; }
.booking-table tbody tr:hover { background-color: #f9fafb; }
.type-name { font-size: 1rem; }
.description-text { font-size: 0.8rem; color: #7f8c8d; }
.action-buttons { white-space: nowrap; }
.action-buttons .btn { margin: 0 2px; }
.modal-custom { border-radius: 16px; border: none; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); }
.modal-header-custom { background-color: #f4f7f9; border-bottom: 1px solid #e5eaee; padding: 1.5rem; }
.modal-footer-custom { background-color: #f4f7f9; border-top: 1px solid #e5eaee; padding: 1rem 1.5rem; }
.form-control, .form-select { border-radius: 8px; border: 1px solid #e5eaee; transition: all 0.2s ease-in-out; font-size: 0.9rem; }
.form-control:focus, .form-select:focus { border-color: #3498db; box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.15); }
.checkbox-list { max-height: 300px; overflow-y: auto; border: 1px solid #e5eaee; border-radius: 8px; padding: 1rem; background-color: #ffffff; }
.tags-container { display: flex; flex-wrap: wrap; gap: 6px; }
.badge { padding: 0.4em 0.8em; font-size: 0.75rem; font-weight: 600; border-radius: 20px; letter-spacing: 0.5px; }
.badge-info { background-color: #eaf6fb; color: #3498db; }
.badge-secondary { background-color: #f3f4f6; color: #7f8c8d; }
.badge-success { background-color: #e6f9f0; color: #2ecc71; }
</style>