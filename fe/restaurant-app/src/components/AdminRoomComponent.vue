<template>
  <div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="fw-bold text-sea-green">Quản Lý Phòng</h1>
      <button class="btn btn-success shadow" @click="moModalThem">
        <i class="bi bi-plus-circle me-2"></i>Thêm Phòng Mới
      </button>
    </div>

    <!-- Tìm kiếm và lọc -->
    <div class="row mb-4 g-3">
      <div class="col-md-4">
        <input
          v-model="tuKhoaTim"
          type="text"
          class="form-control"
          placeholder="Tìm tên phòng hoặc trạng thái..."
        />
      </div>
      <div class="col-md-3">
        <select v-model="locTrangThai" class="form-select">
          <option value="">Tất cả trạng thái</option>
          <option value="Trống">Trống</option>
          <option value="Đã đặt">Đã đặt</option>
          <option value="Chờ xác nhận">Chờ xác nhận</option>
          <option value="Bảo trì">Bảo trì</option>
        </select>
      </div>
    </div>

    <!-- Danh sách phòng -->
    <div class="table-responsive">
      <table class="table table-bordered table-hover align-middle">
        <thead>
          <tr>
            <th>Tên Phòng</th>
            <th>Trạng Thái</th>
            <th>Giá (mỗi đêm)</th>
            <th>Loại Phòng</th>
            <th>Sức Chứa</th>
            <th>Mô Tả</th>
            <th>Hành Động</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="phong in phongHienThi" :key="phong.room_id">
            <td>{{ phong.room_name }}</td>
            <td class="status">
              <span
                :class="{
                  badge: true,
                  'bg-success': phong.status === 'Trống',
                  'bg-danger': phong.status === 'Đã đặt',
                  'bg-warning text-dark ': phong.status === 'Chờ xác nhận',
                  'bg-warning text-dark': phong.status === 'Bảo trì'
                }"
              >
                {{ phong.status }}
              </span>
            </td>
            <td>{{ formatPrice(phong.price) }}</td>
            <td>{{ phong.room_type }}</td>
            <td>{{ phong.capacity }}</td>
            <td>{{ phong.description }}</td>
            <td>
              <button
                class="btn btn-primary btn-sm me-2"
                @click="moModalSua(phong)"
              >
                <i class="bi bi-pencil"></i> Sửa
              </button>
              <button
                class="btn btn-danger btn-sm"
                @click="xoaPhong(phong.room_id)"
              >
                <i class="bi bi-trash"></i> Xóa
              </button>
            </td>
          </tr>
          <tr v-if="phongHienThi.length === 0">
            <td colspan="7" class="text-center text-muted">
              Không có phòng nào phù hợp
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Phân trang -->
    <div class="d-flex justify-content-between align-items-center mt-4">
      <div class="text-muted">
        <span>Hiển thị {{ phongHienThi.length }} / {{ phongLoc.length }} phòng</span>
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
            v-for="trang in trangHienThi"
            :key="trang"
            class="page-item"
            :class="{ active: trangHienTai === trang }"
          >
            <button class="page-link" @click="trangHienTai = trang">{{ trang }}</button>
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

    <!-- Modal thêm/sửa -->
    <div
      v-if="laModalMo"
      class="modal fade show d-block"
      tabindex="-1"
      style="background-color: rgba(0, 0, 0, 0.5)"
    >
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              {{ phongDangSua ? "Sửa Thông Tin Phòng" : "Thêm Phòng Mới" }}
            </h5>
            <button type="button" class="btn-close" @click="dongModal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Tên Phòng</label>
              <input type="text" v-model="phongMoi.room_name" class="form-control" />
            </div>
            <div class="mb-3">
              <label class="form-label">Loại Phòng</label>
              <input type="text" v-model="phongMoi.room_type" class="form-control" />
            </div>
            <div class="mb-3">
              <label class="form-label">Sức Chứa</label>
              <input type="number" v-model.number="phongMoi.capacity" class="form-control" />
            </div>
            <div class="mb-3">
              <label class="form-label">Trạng Thái</label>
              <select v-model="phongMoi.status" class="form-select">
                <option value="Trống">Trống</option>
                <option value="Đã đặt">Đã đặt</option>
                <option value="Chờ xác nhận">Chờ xác nhận</option>
                <option value="Bảo trì">Bảo trì</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Giá (mỗi đêm)</label>
              <input
                type="number"
                min="0"
                step="1000"
                v-model.number="phongMoi.price"
                class="form-control"
              />
            </div>
            <div class="mb-3">
              <label class="form-label">Mô Tả</label>
              <textarea v-model="phongMoi.description" class="form-control"></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" @click="dongModal">Hủy</button>
            <button class="btn btn-success" @click="luuPhong">Lưu</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from "vue";
import axios from "axios";

export default {
  setup() {
    const phong = ref([]);
    const tuKhoaTim = ref("");
    const locTrangThai = ref("");
    const laModalMo = ref(false);
    const phongDangSua = ref(null);
    const phongMoi = ref({
      room_name: "",
      room_type: "",
      capacity: 0,
      status: "Trống",
      price: 0,
      description: "",
    });
    const trangHienTai = ref(1);
    const soPhongMoiTrang = 10;

    const fetchRooms = async () => {
      try {
        const res = await axios.get("http://localhost:8000/api/rooms");
        phong.value = res.data.data || res.data;
        console.log('Đã tải danh sách phòng:', phong.value.map(p => ({
          id: p.room_id,
          name: p.room_name,
          status: p.status
        })));
      } catch (error) {
        alert("Lấy dữ liệu phòng thất bại");
        console.error('Lỗi tải phòng:', error.response?.data || error.message);
      }
    };

    onMounted(() => {
      fetchRooms();
    });

    const phongLoc = computed(() => {
      return phong.value.filter((p) => {
        const khopTim = p.room_name
          .toLowerCase()
          .includes(tuKhoaTim.value.toLowerCase()) ||
          p.status.toLowerCase().includes(tuKhoaTim.value.toLowerCase());
        const khopTrangThai = !locTrangThai.value || p.status === locTrangThai.value;
        return khopTim && khopTrangThai;
      });
    });

    const tongSoTrang = computed(() => {
      return Math.ceil(phongLoc.value.length / soPhongMoiTrang);
    });

    const phongHienThi = computed(() => {
      const batDau = (trangHienTai.value - 1) * soPhongMoiTrang;
      const ketThuc = batDau + soPhongMoiTrang;
      return phongLoc.value.slice(batDau, ketThuc);
    });

    const trangHienThi = computed(() => {
      const soTrang = tongSoTrang.value;
      const trangHienTaiValue = trangHienTai.value;
      const ketQua = [];
      const maxSoTrangHienThi = 5;

      let batDau = Math.max(1, trangHienTaiValue - Math.floor(maxSoTrangHienThi / 2));
      let ketThuc = Math.min(soTrang, batDau + maxSoTrangHienThi - 1);

      if (ketThuc - batDau + 1 < maxSoTrangHienThi) {
        batDau = Math.max(1, ketThuc - maxSoTrangHienThi + 1);
      }

      for (let i = batDau; i <= ketThuc; i++) {
        ketQua.push(i);
      }
      return ketQua;
    });

    const moModalThem = () => {
      phongMoi.value = {
        room_name: "",
        room_type: "",
        capacity: 0,
        status: "Trống",
        price: 0,
        description: "",
      };
      phongDangSua.value = null;
      laModalMo.value = true;
    };

    const moModalSua = (p) => {
      phongMoi.value = { ...p };
      phongDangSua.value = p;
      laModalMo.value = true;
    };

    const dongModal = () => {
      laModalMo.value = false;
    };

    const luuPhong = async () => {
      try {
        if (phongDangSua.value) {
          await axios.put(
            `http://localhost:8000/api/rooms/${phongDangSua.value.room_id}`,
            phongMoi.value
          );
          const index = phong.value.findIndex(
            (p) => p.room_id === phongDangSua.value.room_id
          );
          if (index !== -1)
            phong.value[index] = { ...phongMoi.value, room_id: phongDangSua.value.room_id };
        } else {
          const res = await axios.post("http://localhost:8000/api/rooms", phongMoi.value);
          phong.value.push(res.data.data || res.data);
        }
        trangHienTai.value = 1;
        dongModal();
      } catch (error) {
        alert("Lưu phòng thất bại");
        console.error('Lỗi lưu phòng:', error.response?.data || error.message);
      }
    };

    const xoaPhong = async (id) => {
      if (confirm("Bạn có chắc chắn muốn xóa phòng này không?")) {
        try {
          await axios.delete(`http://localhost:8000/api/rooms/${id}`);
          phong.value = phong.value.filter((p) => p.room_id !== id);
          if (phongHienThi.value.length === 0 && trangHienTai.value > 1) {
            trangHienTai.value--;
          }
        } catch (error) {
          alert("Xóa phòng thất bại");
          console.error('Lỗi xóa phòng:', error.response?.data || error.message);
        }
      }
    };

    const formatPrice = (price) => {
      return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price || 0);
    };

    return {
      phong,
      tuKhoaTim,
      locTrangThai,
      laModalMo,
      phongDangSua,
      phongMoi,
      phongLoc,
      phongHienThi,
      trangHienTai,
      tongSoTrang,
      trangHienThi,
      moModalThem,
      moModalSua,
      dongModal,
      luuPhong,
      xoaPhong,
      formatPrice,
    };
  },
};
</script>

<style scoped>
.text-sea-green {
  background: linear-gradient(135deg, #3f8dd6, #2acabd);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  text-fill-color: transparent;
}

.table th, .status {
  text-align: center;
}

.table th,
.table td {
  vertical-align: middle;
  padding: 12px 15px;
  font-size: 15px;
  color: #444;
  border: 1px solid #dee2e6;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.table thead {
  background: linear-gradient(135deg, #198754, #146c43);
  color: white;
  font-weight: 600;
  font-size: 16px;
}

.table tbody tr:hover {
  background-color: #e6f4ea;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.badge {
  font-size: 0.9rem;
  padding: 5px 10px;
  border-radius: 12px;
  font-weight: 500;
}

.modal-content {
  border-radius: 12px;
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.modal-header {
  background: linear-gradient(135deg, #198754, #146c43);
  color: white;
  border-bottom: none;
  font-weight: 700;
  font-size: 1.25rem;
}

.modal-footer {
  border-top: none;
}

.btn-close {
  filter: invert(1);
  opacity: 0.8;
  transition: opacity 0.2s;
}

.btn-close:hover {
  opacity: 1;
}

.btn-success {
  background: linear-gradient(135deg, #1199f3, #2acabd);
  border: none;
  font-weight: 600;
  transition: transform 0.2s, box-shadow 0.2s;
}

.btn-success:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.btn-primary {
  background: linear-gradient(135deg, #0d6efd, #2b78ec);
  border: none;
  font-weight: 600;
  transition: transform 0.2s, box-shadow 0.2s;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.btn-danger {
  background: linear-gradient(135deg, #dc3545, #bb2d3b);
  border: none;
  font-weight: 600;
  transition: transform 0.2s, box-shadow 0.2s;
}

.btn-danger:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.pagination {
  gap: 8px;
}

.pagination .page-item .page-link {
  background: linear-gradient(135deg, #1199f3, #2acabd);
  color: transparent;
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  text-fill-color: transparent;
  border: none;
  border-radius: 8px;
  padding: 8px 12px;
  font-weight: 500;
  transition: all 0.3s ease;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.pagination .page-item.active .page-link {
  background: linear-gradient(135deg, #1199f3, #2acabd);
  color: white;
  -webkit-text-fill-color: white;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.pagination .page-item.disabled .page-link {
  background: #f8f9fa;
  color: #6c757d;
  -webkit-text-fill-color: #6c757d;
  cursor: not-allowed;
  box-shadow: none;
}

.pagination .page-item .page-link:hover:not(.disabled) {
  background: linear-gradient(135deg, #2acabd, #1199f3);
  color: white;
  -webkit-text-fill-color: white;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.pagination .page-item .page-link i {
  font-size: 1rem;
  background: linear-gradient(135deg, #1199f3, #2acabd);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  text-fill-color: transparent;
}

.pagination .page-item.active .page-link i {
  color: white;
  -webkit-text-fill-color: white;
}
</style>