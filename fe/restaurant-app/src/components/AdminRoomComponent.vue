<template>
  <div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="fw-bold text-success">Quản Lý Phòng</h1>
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
          <tr v-for="phong in phongLoc" :key="phong.room_id">
            <td>{{ phong.room_name }}</td>
            <td class="status">
              <span
                :class="{
                  badge: true,
                  'bg-success': phong.status === 'Trống',
                  'bg-danger': phong.status === 'Đã đặt',
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
          <tr v-if="phongLoc.length === 0">
            <td colspan="7" class="text-center text-muted">
              Không có phòng nào phù hợp
            </td>
          </tr>
        </tbody>
      </table>
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

    const fetchRooms = async () => {
      try {
        const res = await axios.get("http://localhost:8000/api/rooms");
        phong.value = res.data;
      } catch (error) {
        alert("Lấy dữ liệu phòng thất bại");
        console.error(error);
      }
    };

    onMounted(() => {
      fetchRooms();
    });

    const phongLoc = computed(() => {
      return phong.value.filter((p) => {
        const khopTim = p.room_name
          .toLowerCase()
          .includes(tuKhoaTim.value.toLowerCase());
        const khopTrangThai = !locTrangThai.value || p.status === locTrangThai.value;
        return khopTim && khopTrangThai;
      });
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
          // Sửa phòng
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
          // Thêm phòng mới
          const res = await axios.post("http://localhost:8000/api/rooms", phongMoi.value);
          phong.value.push(res.data);
        }
        dongModal();
      } catch (error) {
        alert("Lưu phòng thất bại");
        console.error(error);
      }
    };

    const xoaPhong = async (id) => {
      if (confirm("Bạn có chắc chắn muốn xóa phòng này không?")) {
        try {
          await axios.delete(`http://localhost:8000/api/rooms/${id}`);
          phong.value = phong.value.filter((p) => p.room_id !== id);
        } catch (error) {
          alert("Xóa phòng thất bại");
          console.error(error);
        }
      }
    };

    const formatPrice = (price) => {
      return price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".") + " VNĐ";
    };

    return {
      phong,
      tuKhoaTim,
      locTrangThai,
      laModalMo,
      phongDangSua,
      phongMoi,
      phongLoc,
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
.table th, .status {
  text-align: center;
}

.table th,
.table td {
  vertical-align: middle;
  /* text-align: center; */
  padding: 12px 15px;
  font-size: 15px;
  color: #444;
  border: 1px solid #dee2e6;
  
  white-space: nowrap; 
  overflow: hidden;     
  text-overflow: ellipsis;
}

.table thead {
  background-color: #198754;
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
  background-color: #198754;
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
  background-color: #198754;
  border-color: #198754;
  font-weight: 600;
  transition: background-color 0.3s;
}

.btn-success:hover {
  background-color: #146c43;
  border-color: #146c43;
}

.btn-primary {
  background-color: #0d6efd;
  border-color: #0d6efd;
  font-weight: 600;
}

.btn-primary:hover {
  background-color: #0a58ca;
  border-color: #0a58ca;
}

.btn-danger {
  background-color: #dc3545;
  border-color: #dc3545;
  font-weight: 600;
}

.btn-danger:hover {
  background-color: #bb2d3b;
  border-color: #bb2d3b;
}
</style>
