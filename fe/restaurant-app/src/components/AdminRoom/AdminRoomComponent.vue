<template>
  <div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="fw-bold text-sea-green">Quản Lý Phòng</h1>
      <button class="btn btn-success shadow" @click="moModalThem">
        <i class="bi bi-plus-circle me-2"></i>Thêm Phòng
      </button>
    </div>
    <div v-if="successMessage" class="alert alert-success">{{ successMessage }}</div>
    <div v-if="errorMessage" class="alert alert-danger">{{ errorMessage }}</div>

    <div class="row mb-4 g-3">
      <div class="col-md-4">
        <input
          v-model="tuKhoa"
          type="text"
          class="form-control"
          placeholder="Tìm theo tên phòng"
        />
      </div>
      <div class="col-md-3">
        <select v-model="locTheoMua" class="form-select">
          <option value="">Tất cả mùa</option>
          <option v-for="season in seasons" :key="season.season_id" :value="season.season_id">
            {{ season.season_name }}
          </option>
        </select>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-bordered table-hover align-middle">
        <thead>
          <tr>
            <th>#</th>
            <th>Phòng</th>
            <th>Loại Phòng</th>
            <th>Mùa</th>
            <th>Giá/Giờ</th>
            <th>Giá/Đêm</th>
            <th>Giá/Ngày</th>
            <th>Giảm Giá (%)</th>
            <th>Trạng Thái</th>
            <th>Hành Động</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(room, index) in roomHienThi" :key="room.room_id">
            <td>{{ (trangHienTai - 1) * soRoomMoiTrang + index + 1 }}</td>
            <td>{{ room.room_name || '-' }}</td>
            <td>{{ room.room_type ? room.room_type.type_name : '-' }}</td>
            <td>{{ room.season ? room.season.season_name : '-' }}</td>
            <td>{{ formatPrice(room.season ? room.season.hourly_rate : 0) }}</td>
            <td>{{ formatPrice(room.season ? room.season.nightly_rate : 0) }}</td>
            <td>{{ formatPrice(room.season ? room.season.daily_rate : 0) }}</td>
            <td>{{ room.season && room.season.discount ? room.season.discount + '%' : '-' }}</td>
            <td>
              <span
                :class="{
                  'badge bg-success': room.status === 'Trống',
                  'badge bg-danger': room.status === 'Đã đặt',
                  'badge bg-warning': room.status === 'Chờ xác nhận',
                  'badge bg-info': room.status === 'Bảo trì',
                  'badge bg-secondary': room.status === 'Đang dọn dẹp'
                }"
              >
                {{ room.status || '-' }}
              </span>
            </td>
            <td>
              <button class="btn btn-primary btn-sm me-2" @click="moModalSua(room)">
                <i class="bi bi-pencil me-1"></i>Sửa
              </button>
              <button class="btn btn-danger btn-sm" @click="xoaRoom(room.room_id)">
                <i class="bi bi-trash me-1"></i>Xóa
              </button>
            </td>
          </tr>
          <tr v-if="roomHienThi.length === 0">
            <td colspan="10" class="text-center text-muted">Không có phòng nào phù hợp</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-4">
      <div class="text-muted">
        Hiển thị {{ roomHienThi.length }} / {{ roomLoc.length }} phòng
      </div>
      <nav>
        <ul class="pagination mb-0">
          <li class="page-item" :class="{ disabled: trangHienTai === 1 }">
            <button class="page-link" @click="trangHienTai = 1">
              <i class="bi bi-chevron-double-left"></i>
            </button>
          </li>
          <li class="page-item" :class="{ disabled: trangHienTai === 1 }">
            <button class="page-link" @click="trangHienTai--">
              <i class="bi bi-chevron-left"></i>
            </button>
          </li>
          <li
            v-for="tr in trangHienThi"
            :key="tr"
            class="page-item"
            :class="{ active: trangHienTai === tr }"
          >
            <button class="page-link" @click="trangHienTai = tr">{{ tr }}</button>
          </li>
          <li class="page-item" :class="{ disabled: trangHienTai === tongSoTrang }">
            <button class="page-link" @click="trangHienTai++">
              <i class="bi bi-chevron-right"></i>
            </button>
          </li>
          <li class="page-item" :class="{ disabled: trangHienTai === tongSoTrang }">
            <button class="page-link" @click="trangHienTai = tongSoTrang">
              <i class="bi bi-chevron-double-right"></i>
            </button>
          </li>
        </ul>
      </nav>
    </div>

    <div v-if="laModalMo" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5)">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ isEditing ? 'Cập nhật Phòng' : 'Thêm Phòng' }}</h5>
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
            <form @submit.prevent="submitRoom">
              <div class="mb-3">
                <label for="roomName" class="form-label">Tên Phòng</label>
                <input
                  type="text"
                  id="roomName"
                  class="form-control"
                  v-model="form.room_name"
                  required
                  placeholder="Nhập tên phòng"
                  :class="{ 'is-invalid': errors.room_name }"
                />
                <div v-if="errors.room_name" class="invalid-feedback">{{ errors.room_name }}</div>
              </div>
              <div class="mb-3">
                <label for="typeId" class="form-label">Loại Phòng</label>
                <select
                  id="typeId"
                  class="form-select"
                  v-model="form.type_id"
                  required
                  :class="{ 'is-invalid': errors.type_id }"
                >
                  <option :value="null">-- Chọn loại phòng --</option>
                  <option v-for="type in roomTypes" :key="type.type_id" :value="type.type_id">
                    {{ type.type_name }}
                  </option>
                </select>
                <div v-if="errors.type_id" class="invalid-feedback">{{ errors.type_id }}</div>
              </div>
              <div class="mb-3">
                <label for="seasonId" class="form-label">Mùa</label>
                <select
                  id="seasonId"
                  class="form-select"
                  v-model="form.season_id"
                  required
                  @change="updatePricing"
                  :class="{ 'is-invalid': errors.season_id }"
                >
                  <option :value="null">-- Chọn mùa --</option>
                  <option v-for="season in seasons" :key="season.season_id" :value="season.season_id">
                    {{ season.season_name }} ({{ formatDate(season.start_date) }} - {{ formatDate(season.end_date) }})
                  </option>
                </select>
                <div v-if="errors.season_id" class="invalid-feedback">{{ errors.season_id }}</div>
              </div>
              <div class="mb-3">
                <label for="capacity" class="form-label">Sức Chứa</label>
                <input
                  type="number"
                  id="capacity"
                  class="form-control"
                  v-model.number="form.capacity"
                  required
                  min="1"
                  placeholder="Nhập sức chứa"
                  :class="{ 'is-invalid': errors.capacity }"
                />
                <div v-if="errors.capacity" class="invalid-feedback">{{ errors.capacity }}</div>
              </div>
              <div class="mb-3">
                <label for="status" class="form-label">Trạng Thái</label>
                <select
                  id="status"
                  class="form-select"
                  v-model="form.status"
                  required
                  :class="{ 'is-invalid': errors.status }"
                >
                  <option value="Trống">Trống</option>
                  <option value="Đã đặt">Đã đặt</option>
                  <option value="Chờ xác nhận">Chờ xác nhận</option>
                  <option value="Bảo trì">Bảo trì</option>
                  <option value="Đang dọn dẹp">Đang dọn dẹp</option>
                </select>
                <div v-if="errors.status" class="invalid-feedback">{{ errors.status }}</div>
              </div>
              <div class="mb-3">
                <label class="form-label">Giá Theo Mùa</label>
                <div class="row">
                  <div class="col">
                    <label>Giá/Giờ</label>
                    <input
                      type="text"
                      class="form-control"
                      :value="formatPrice(selectedSeason ? selectedSeason.hourly_rate : 0)"
                      readonly
                    />
                  </div>
                  <div class="col">
                    <label>Giá/Đêm</label>
                    <input
                      type="text"
                      class="form-control"
                      :value="formatPrice(selectedSeason ? selectedSeason.nightly_rate : 0)"
                      readonly
                    />
                  </div>
                  <div class="col">
                    <label>Giá/Ngày</label>
                    <input
                      type="text"
                      class="form-control"
                      :value="formatPrice(selectedSeason ? selectedSeason.daily_rate : 0)"
                      readonly
                    />
                  </div>
                  <div class="col">
                    <label>Giảm Giá (%)</label>
                    <input
                      type="text"
                      class="form-control"
                      :value="selectedSeason && selectedSeason.discount ? selectedSeason.discount + '%' : '-'"
                      readonly
                    />
                  </div>
                </div>
              </div>
              <div class="mb-3">
                <label for="amenities" class="form-label">Tiện Ích</label>
                <textarea
                  id="amenities"
                  rows="3"
                  class="form-control"
                  v-model="form.amenities"
                  required
                  placeholder='Nhập tiện ích (JSON, ví dụ: ["Wi-Fi", "Máy lạnh"])'
                  :class="{ 'is-invalid': errors.amenities }"
                ></textarea>
                <div v-if="errors.amenities" class="invalid-feedback">{{ errors.amenities }}</div>
              </div>
              <div class="mb-3">
                <label for="description" class="form-label">Mô Tả</label>
                <textarea
                  id="description"
                  rows="3"
                  class="form-control"
                  v-model="form.description"
                  placeholder="Nhập mô tả phòng"
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
import { ref, computed, onMounted } from 'vue';
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
  name: 'RoomPricingComponent',
  setup() {
    const rooms = ref([]);
    const roomTypes = ref([]);
    const seasons = ref([]);
    const tuKhoa = ref('');
    const locTheoMua = ref('');
    const laModalMo = ref(false);
    const isEditing = ref(false);
    const isLoading = ref(false);
    const successMessage = ref('');
    const errorMessage = ref('');
    const trangHienTai = ref(1);
    const soRoomMoiTrang = 10;

    const form = ref({
      room_id: null,
      room_name: '',
      type_id: null,
      season_id: null,
      capacity: 1,
      status: 'Trống',
      amenities: '[]',
      description: ''
    });

    const selectedSeason = ref(null);

    const errors = ref({
      room_name: '',
      type_id: '',
      season_id: '',
      capacity: '',
      status: '',
      amenities: ''
    });

    const roomLoc = computed(() => {
      return rooms.value.filter(r => {
        const khopTim = (r.room_name?.toLowerCase() || '').includes(tuKhoa.value.toLowerCase());
        const khopMua = !locTheoMua.value || r.season_id === parseInt(locTheoMua.value);
        return khopTim && khopMua;
      });
    });

    const tongSoTrang = computed(() => Math.ceil(roomLoc.value.length / soRoomMoiTrang));

    const roomHienThi = computed(() => {
      const batDau = (trangHienTai.value - 1) * soRoomMoiTrang;
      const ketThuc = batDau + soRoomMoiTrang;
      return roomLoc.value.slice(batDau, ketThuc);
    });

    const trangHienThi = computed(() => {
      const soTrang = tongSoTrang.value;
      const maxSoTrangHienThi = 5;
      let batDau = Math.max(1, trangHienTai.value - Math.floor(maxSoTrangHienThi / 2));
      let ketThuc = Math.min(soTrang, batDau + maxSoTrangHienThi - 1);
      if (ketThuc - batDau + 1 < maxSoTrangHienThi) {
        batDau = Math.max(1, ketThuc - maxSoTrangHienThi + 1);
      }
      return Array.from({ length: ketThuc - batDau + 1 }, (_, i) => batDau + i);
    });

    const fetchData = async () => {
      isLoading.value = true;
      errorMessage.value = '';
      try {
        const [roomsRes, typesRes, seasonsRes] = await Promise.all([
          retryRequest(() => api.get('/rooms', { params: { _t: new Date().getTime() } })),
          retryRequest(() => api.get('/room-types', { params: { _t: new Date().getTime() } })),
          retryRequest(() => api.get('/seasons', { params: { _t: new Date().getTime() } }))
        ]);

        rooms.value = Array.isArray(roomsRes.data.data) ? roomsRes.data.data : [];
        roomTypes.value = Array.isArray(typesRes.data.data) ? typesRes.data.data : [];
        seasons.value = Array.isArray(seasonsRes.data.data) ? seasonsRes.data.data : [];

        console.log('Dữ liệu API:', {
          rooms: rooms.value,
          roomTypes: roomTypes.value,
          seasons: seasons.value
        });

        if (!rooms.value.length && !roomTypes.value.length && !seasons.value.length) {
          errorMessage.value = 'Không có dữ liệu phòng, loại phòng hoặc mùa. Vui lòng kiểm tra cơ sở dữ liệu.';
        }
      } catch (error) {
        console.error('Lỗi tải dữ liệu:', error.response?.data || error.message);
        errorMessage.value = error.response?.data?.message || 'Không thể tải dữ liệu phòng, loại phòng hoặc mùa.';
      } finally {
        isLoading.value = false;
      }
    };

    onMounted(() => {
      fetchData();
    });

    const updatePricing = () => {
      selectedSeason.value = seasons.value.find(s => s.season_id === form.value.season_id) || null;
    };

    const moModalThem = () => {
      resetForm();
      isEditing.value = false;
      laModalMo.value = true;
      successMessage.value = '';
    };

    const moModalSua = (room) => {
      form.value = {
        room_id: room.room_id,
        room_name: room.room_name,
        type_id: room.type_id,
        season_id: room.season_id,
        capacity: room.capacity,
        status: room.status || 'Trống',
        amenities: JSON.stringify(room.amenities || []),
        description: room.description || ''
      };
      updatePricing();
      isEditing.value = true;
      laModalMo.value = true;
      successMessage.value = '';
    };

    const dongModal = () => {
      laModalMo.value = false;
      resetForm();
      selectedSeason.value = null;
      successMessage.value = '';
      errorMessage.value = '';
    };

    const resetForm = () => {
      form.value = {
        room_id: null,
        room_name: '',
        type_id: null,
        season_id: null,
        capacity: 1,
        status: 'Trống',
        amenities: '[]',
        description: ''
      };
      errors.value = {};
    };

    const validateForm = () => {
      errors.value = {};
      let isValid = true;

      if (!form.value.room_name?.trim()) {
        errors.value.room_name = 'Vui lòng nhập tên phòng';
        isValid = false;
      }
      if (!form.value.type_id) {
        errors.value.type_id = 'Vui lòng chọn loại phòng';
        isValid = false;
      }
      if (!form.value.season_id) {
        errors.value.season_id = 'Vui lòng chọn mùa';
        isValid = false;
      }
      if (!form.value.capacity || form.value.capacity < 1) {
        errors.value.capacity = 'Sức chứa phải lớn hơn 0';
        isValid = false;
      }
      if (!form.value.status || !['Trống', 'Đã đặt', 'Chờ xác nhận', 'Bảo trì', 'Đang dọn dẹp'].includes(form.value.status)) {
        errors.value.status = 'Vui lòng chọn trạng thái hợp lệ';
        isValid = false;
      }
      try {
        JSON.parse(form.value.amenities);
      } catch {
        errors.value.amenities = 'Tiện ích phải là JSON hợp lệ';
        isValid = false;
      }

      return isValid;
    };

    const submitRoom = async () => {
      if (!validateForm()) return;

      isLoading.value = true;
      errorMessage.value = '';

      try {
        const payload = { ...form.value, amenities: JSON.parse(form.value.amenities) };
        if (isEditing.value) {
          await retryRequest(() => api.put(`/rooms/${form.value.room_id}`, payload));
          successMessage.value = 'Cập nhật phòng thành công';
        } else {
          await retryRequest(() => api.post('/rooms', payload));
          successMessage.value = 'Thêm phòng mới thành công';
        }

        await fetchData();
        dongModal();
      } catch (error) {
        console.error('Lỗi khi lưu phòng:', error.response?.data || error.message);
        errorMessage.value = error.response?.data?.message || 'Lưu phòng thất bại';
        if (error.response?.status === 422) {
          const backendErrors = error.response.data.errors || {};
          errorMessage.value += ': ' + Object.values(backendErrors).flat().join(', ');
        }
      } finally {
        isLoading.value = false;
      }
    };

    const xoaRoom = async (roomId) => {
      if (!confirm('Bạn có chắc chắn muốn xóa phòng này không?')) return;

      isLoading.value = true;
      errorMessage.value = '';
      try {
        await retryRequest(() => api.delete(`/rooms/${roomId}`));
        await fetchData();
        successMessage.value = 'Xóa phòng thành công';
      } catch (error) {
        console.error('Lỗi khi xóa phòng:', error.response?.data || error.message);
        errorMessage.value = error.response?.data?.message || 'Xóa phòng thất bại';
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
      rooms,
      roomTypes,
      seasons,
      tuKhoa,
      locTheoMua,
      laModalMo,
      isEditing,
      isLoading,
      successMessage,
      errorMessage,
      trangHienTai,
      soRoomMoiTrang,
      form,
      selectedSeason,
      errors,
      roomLoc,
      roomHienThi,
      tongSoTrang,
      trangHienThi,
      moModalThem,
      moModalSua,
      dongModal,
      resetForm,
      validateForm,
      submitRoom,
      xoaRoom,
      updatePricing,
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
.pagination .page-item .page-link {
  background: linear-gradient(135deg, #1199f3, #2acabd);
  color: transparent;
  -webkit-background-clip: text;
  border-radius: 8px;
}
.pagination .page-item.active .page-link {
  background: linear-gradient(135deg, #1199f3, #2acabd);
  color: white;
}
</style>