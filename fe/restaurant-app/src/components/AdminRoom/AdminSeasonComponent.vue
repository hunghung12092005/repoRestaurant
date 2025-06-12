<template>
  <div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="fw-bold text-sea-green">Quản Lý Mùa và Giá</h1>
      <button class="btn btn-success shadow" @click="moModalThem">
        <i class="bi bi-plus-circle me-2"></i>Thêm Mùa
      </button>
    </div>
    <div v-if="successMessage" class="alert alert-success">{{ successMessage }}</div>
    <div v-if="errorMessage" class="alert alert-danger">{{ errorMessage }}</div>

    <div class="table-responsive">
      <table class="table table-bordered table-hover align-middle">
        <thead>
          <tr>
            <th>#</th>
            <th>Tên Mùa</th>
            <th>Ngày Bắt Đầu</th>
            <th>Ngày Kết Thúc</th>
            <th>Giá/Giờ</th>
            <th>Giá/Đêm</th>
            <th>Giá/Ngày</th>
            <th>Giảm Giá (%)</th>
            <th>Mô Tả</th>
            <th>Hành Động</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(season, index) in seasons" :key="season.season_id">
            <td>{{ index + 1 }}</td>
            <td>{{ season.season_name || '-' }}</td>
            <td>{{ formatDate(season.start_date) }}</td>
            <td>{{ formatDate(season.end_date) }}</td>
            <td>{{ formatPrice(season.hourly_rate) }}</td>
            <td>{{ formatPrice(season.nightly_rate) }}</td>
            <td>{{ formatPrice(season.daily_rate) }}</td>
            <td>{{ season.discount ? season.discount + '%' : '-' }}</td>
            <td>{{ season.description || '-' }}</td>
            <td>
              <button class="btn btn-primary btn-sm me-2" @click="moModalSua(season)">
                <i class="bi bi-pencil me-1"></i>Sửa
              </button>
              <button class="btn btn-danger btn-sm" @click="xoaSeason(season.season_id)">
                <i class="bi bi-trash me-1"></i>Xóa
              </button>
            </td>
          </tr>
          <tr v-if="seasons.length === 0">
            <td colspan="10" class="text-center text-muted">Không có mùa nào</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="laModalMo" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5)">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ isEditing ? 'Cập nhật Mùa' : 'Thêm Mùa' }}</h5>
            <button type="button" class="btn-close" @click="dongModal"></button>
          </div>
          <div class="modal-body">
            <div v-if="isLoading" class="text-center mb-3">
              <div class="spinner-border text-primary">
                <span class="visually-hidden">Đang tải...</span>
              </div>
            </div>
            <div v-if="errorMessage" class="alert alert-warning">
              {{ errorMessage }}
            </div>
            <form @submit.prevent="submitSeason">
              <div class="mb-3">
                <label for="seasonName" class="form-label">Tên Mùa</label>
                <input
                  type="text"
                  id="seasonName"
                  class="form-control"
                  v-model="form.season_name"
                  required
                  placeholder="Nhập tên mùa"
                  :class="{ 'is-invalid': errors.season_name }"
                />
                <div v-if="errors.season_name" class="invalid-feedback">{{ errors.season_name }}</div>
              </div>
              <div class="row mb-3">
                <div class="col">
                  <label for="startDate" class="form-label">Ngày Bắt Đầu</label>
                  <input
                    type="date"
                    id="startDate"
                    class="form-control"
                    v-model="form.start_date"
                    required
                    :class="{ 'is-invalid': errors.start_date }"
                  />
                  <div v-if="errors.start_date" class="invalid-feedback">{{ errors.start_date }}</div>
                </div>
                <div class="col">
                  <label for="endDate" class="form-label">Ngày Kết Thúc</label>
                  <input
                    type="date"
                    id="endDate"
                    class="form-control"
                    v-model="form.end_date"
                    required
                    :class="{ 'is-invalid': errors.end_date }"
                  />
                  <div v-if="errors.end_date" class="invalid-feedback">{{ errors.end_date }}</div>
                </div>
              </div>
              <div class="row mb-3">
                <div class="col">
                  <label for="hourlyRate" class="form-label">Giá/Giờ</label>
                  <input
                    type="number"
                    id="hourlyRate"
                    class="form-control"
                    v-model.number="form.hourly_rate"
                    required
                    min="0"
                    step="0.01"
                    placeholder="Nhập giá/giờ"
                    :class="{ 'is-invalid': errors.hourly_rate }"
                  />
                  <div v-if="errors.hourly_rate" class="invalid-feedback">{{ errors.hourly_rate }}</div>
                </div>
                <div class="col">
                  <label for="nightlyRate" class="form-label">Giá/Đêm</label>
                  <input
                    type="number"
                    id="nightlyRate"
                    class="form-control"
                    v-model.number="form.nightly_rate"
                    required
                    min="0"
                    step="0.01"
                    placeholder="Nhập giá/đêm"
                    :class="{ 'is-invalid': errors.nightly_rate }"
                  />
                  <div v-if="errors.nightly_rate" class="invalid-feedback">{{ errors.nightly_rate }}</div>
                </div>
                <div class="col">
                  <label for="dailyRate" class="form-label">Giá/Ngày</label>
                  <input
                    type="number"
                    id="dailyRate"
                    class="form-control"
                    v-model.number="form.daily_rate"
                    required
                    min="0"
                    step="0.01"
                    placeholder="Nhập giá/ngày"
                    :class="{ 'is-invalid': errors.daily_rate }"
                  />
                  <div v-if="errors.daily_rate" class="invalid-feedback">{{ errors.daily_rate }}</div>
                </div>
              </div>
              <div class="mb-3">
                <label for="discount" class="form-label">Giảm Giá (%)</label>
                <input
                  type="number"
                  id="discount"
                  class="form-control"
                  v-model.number="form.discount"
                  min="0"
                  max="100"
                  step="0.01"
                  placeholder="Nhập % giảm giá (nếu có)"
                  :class="{ 'is-invalid': errors.discount }"
                />
                <div v-if="errors.discount" class="invalid-feedback">{{ errors.discount }}</div>
              </div>
              <div class="mb-3">
                <label for="description" class="form-label">Mô Tả</label>
                <textarea
                  id="description"
                  rows="3"
                  class="form-control"
                  v-model="form.description"
                  placeholder="Nhập mô tả mùa"
                ></textarea>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" @click="dongModal">Hủy</button>
                <button type="submit" class="btn btn-success" :disabled="isLoading">Lưu</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const retryRequest = async (fn, retries = 2, delay = 1000) => {
  for (let i = 0; i < retries; i++) {
    try {
      return await fn();
    } catch (error) {
      if (i === retries - 1 || error.code !== 'ECONNABORTED') throw error;
      await new Promise(resolve => setTimeout(resolve, delay));
    }
  }
};

const api = axios.create({
  baseURL: 'http://localhost:8000/api',
  timeout: 10000,
  headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }
});

export default {
  name: 'SeasonManagementComponent',
  setup() {
    const seasons = ref([]);
    const laModalMo = ref(false);
    const isEditing = ref(false);
    const isLoading = ref(false);
    const successMessage = ref('');
    const errorMessage = ref('');

    const form = ref({
      season_id: null,
      season_name: '',
      start_date: '',
      end_date: '',
      hourly_rate: 0,
      nightly_rate: 0,
      daily_rate: 0,
      discount: null,
      description: ''
    });

    const errors = ref({
      season_name: '',
      start_date: '',
      end_date: '',
      hourly_rate: '',
      nightly_rate: '',
      daily_rate: '',
      discount: ''
    });

    const fetchSeasons = async () => {
      isLoading.value = true;
      errorMessage.value = '';
      try {
        const response = await retryRequest(() => api.get('/seasons', { params: { _t: new Date().getTime() } }));
        seasons.value = Array.isArray(response.data.data) ? response.data.data : [];
        console.log('Dữ liệu mùa:', seasons.value);
        if (!seasons.value.length) {
          errorMessage.value = 'Không có dữ liệu mùa. Vui lòng thêm mùa mới.';
        }
      } catch (error) {
        console.error('Lỗi tải dữ liệu mùa:', error.response?.data || error.message);
        errorMessage.value = error.response?.data?.message || 'Không thể tải dữ liệu mùa.';
      } finally {
        isLoading.value = false;
      }
    };

    onMounted(() => {
      fetchSeasons();
    });

    const moModalThem = () => {
      resetForm();
      isEditing.value = false;
      laModalMo.value = true;
      successMessage.value = '';
    };

    const moModalSua = (season) => {
      form.value = {
        season_id: season.season_id,
        season_name: season.season_name,
        start_date: season.start_date,
        end_date: season.end_date,
        hourly_rate: season.hourly_rate,
        nightly_rate: season.nightly_rate,
        daily_rate: season.daily_rate,
        discount: season.discount,
        description: season.description || ''
      };
      isEditing.value = true;
      laModalMo.value = true;
      successMessage.value = '';
    };

    const dongModal = () => {
      laModalMo.value = false;
      resetForm();
      successMessage.value = '';
      errorMessage.value = '';
    };

    const resetForm = () => {
      form.value = {
        season_id: null,
        season_name: '',
        start_date: '',
        end_date: '',
        hourly_rate: 0,
        nightly_rate: 0,
        daily_rate: 0,
        discount: null,
        description: ''
      };
      errors.value = {};
    };

    const validateForm = () => {
      errors.value = {};
      let isValid = true;

      if (!form.value.season_name?.trim()) {
        errors.value.season_name = 'Vui lòng nhập tên mùa';
        isValid = false;
      }
      if (!form.value.start_date) {
        errors.value.start_date = 'Vui lòng chọn ngày bắt đầu';
        isValid = false;
      }
      if (!form.value.end_date) {
        errors.value.end_date = 'Vui lòng chọn ngày kết thúc';
        isValid = false;
      } else if (new Date(form.value.end_date) < new Date(form.value.start_date)) {
        errors.value.end_date = 'Ngày kết thúc phải sau ngày bắt đầu';
        isValid = false;
      }
      if (form.value.hourly_rate === null || form.value.hourly_rate < 0) {
        errors.value.hourly_rate = 'Giá/giờ phải lớn hơn hoặc bằng 0';
        isValid = false;
      }
      if (form.value.nightly_rate === null || form.value.nightly_rate < 0) {
        errors.value.nightly_rate = 'Giá/đêm phải lớn hơn hoặc bằng 0';
        isValid = false;
      }
      if (form.value.daily_rate === null || form.value.daily_rate < 0) {
        errors.value.daily_rate = 'Giá/ngày phải lớn hơn hoặc bằng 0';
        isValid = false;
      }
      if (form.value.discount !== null && (form.value.discount < 0 || form.value.discount > 100)) {
        errors.value.discount = 'Giảm giá phải từ 0 đến 100%';
        isValid = false;
      }

      return isValid;
    };

    const submitSeason = async () => {
      if (!validateForm()) return;

      isLoading.value = true;
      errorMessage.value = '';

      try {
        if (isEditing.value) {
          await retryRequest(() => api.put(`/seasons/${form.value.season_id}`, form.value));
          successMessage.value = 'Cập nhật mùa thành công';
        } else {
          await retryRequest(() => api.post('/seasons', form.value));
          successMessage.value = 'Thêm mùa mới thành công';
        }

        await fetchSeasons();
        dongModal();
      } catch (error) {
        console.error('Lỗi khi lưu mùa:', error.response?.data || error.message);
        errorMessage.value = error.response?.data?.message || 'Lưu mùa thất bại';
        if (error.response?.status === 422) {
          const backendErrors = error.response.data.errors || {};
          errorMessage.value += ': ' + Object.values(backendErrors).flat().join(', ');
        }
      } finally {
        isLoading.value = false;
      }
    };

    const xoaSeason = async (seasonId) => {
      if (!confirm('Bạn có chắc chắn muốn xóa mùa này không?')) return;

      isLoading.value = true;
      errorMessage.value = '';
      try {
        await retryRequest(() => api.delete(`/seasons/${seasonId}`));
        await fetchSeasons();
        successMessage.value = 'Xóa mùa thành công';
      } catch (error) {
        console.error('Lỗi khi xóa mùa:', error.response?.data || error.message);
        errorMessage.value = error.response?.data?.message || 'Xóa mùa thất bại';
      } finally {
        isLoading.value = false;
      }
    };

    const formatPrice = (value) => {
      return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value || 0);
    };

    const formatDate = (date) => {
      if (!date) return '-';
      try {
        const d = new Date(date);
        return d.toLocaleDateString('vi-VN', {
          day: '2-digit',
          month: '2-digit',
          year: 'numeric'
        });
      } catch {
        return '-';
      }
    };

    return {
      seasons,
      laModalMo,
      isEditing,
      isLoading,
      successMessage,
      errorMessage,
      form,
      errors,
      moModalThem,
      moModalSua,
      dongModal,
      resetForm,
      validateForm,
      submitSeason,
      xoaSeason,
      formatPrice,
      formatDate
    };
  }
};
</script>

<style scoped>
.text-sea-green {
  background: linear-gradient(135deg, #3f8dd6, #2acabd);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}
.table thead {
  background: linear-gradient(135deg, #3f8dd6, #2acabd);
  color: white;
}
.table tbody tr:hover {
  background-color: #e6f4ea;
}
.modal-header {
  background: linear-gradient(135deg, #3f8dd6, #2acabd);
  color: white;
}
.btn-success {
  background: linear-gradient(135deg, #1199f3, #2acabd);
  border: none;
}
.btn-success:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}
.btn-danger {
  background: linear-gradient(135deg, #dc3545, #bb2d3b);
  border: none;
}
.btn-danger:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}
.btn-primary {
  background: linear-gradient(135deg, #0d6efd, #0b5ed7);
  border: none;
}
.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}
</style>