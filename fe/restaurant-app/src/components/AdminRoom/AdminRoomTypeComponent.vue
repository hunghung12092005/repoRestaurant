<template>
  <div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="fw-bold text-sea-green">Quản Lý Loại Phòng</h1>
      <button class="btn btn-success shadow" @click="moModalThem">
        <i class="bi bi-plus-circle me-2"></i>Thêm Loại Phòng Mới
      </button>
    </div>

    <!-- Tìm kiếm -->
    <div class="row mb-4 g-3">
      <div class="col-md-4">
        <input
          v-model="tuKhoaTim"
          type="text"
          class="form-control"
          placeholder="Tìm tên loại phòng..."
        />
      </div>
    </div>

    <!-- Danh sách loại phòng -->
    <div class="table-responsive">
      <table class="table table-bordered table-hover align-middle">
        <thead>
          <tr>
            <th>Tên Loại Phòng</th>
            <th>Mô Tả</th>
            <th>Số Giường</th>
            <th>Sức Chứa</th>
            <th>Hành Động</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="type in typeHienThi" :key="type.id">
            <td>{{ type.type_name }}</td>
            <td>{{ type.description || 'Không có mô tả' }}</td>
            <td>{{ type.bed_count }}</td>
            <td>{{ type.max_occupancy }}</td>
            <td>
              <button
                class="btn btn-primary btn-sm me-2"
                @click="moModalSua(type)"
              >
                <i class="bi bi-pencil"></i> Sửa
              </button>
              <button
                class="btn btn-danger btn-sm"
                @click="xoaLoaiPhong(type.id)"
              >
                <i class="bi bi-trash"></i> Xóa
              </button>
            </td>
          </tr>
          <tr v-if="typeHienThi.length === 0">
            <td colspan="5" class="text-center text-muted">
              Không có loại phòng nào phù hợp
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Phân trang -->
    <div class="d-flex justify-content-between align-items-center mt-4">
      <div class="text-muted">
        <span>Hiển thị {{ typeHienThi.length }} / {{ typeLoc.length }} loại phòng</span>
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
              {{ typeDangSua ? "Sửa Loại Phòng" : "Thêm Loại Phòng Mới" }}
            </h5>
            <button type="button" class="btn-close" @click="dongModal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Tên Loại Phòng</label>
              <input type="text" v-model="typeMoi.type_name" class="form-control" required />
            </div>
            <div class="mb-3">
              <label class="form-label">Mô Tả</label>
              <textarea v-model="typeMoi.description" class="form-control"></textarea>
            </div>
            <div class="mb-3">
              <label class="form-label">Số Giường</label>
              <input type="number" v-model="typeMoi.bed_count" class="form-control" min="1" required />
            </div>
            <div class="mb-3">
              <label class="form-label">Sức Chứa Tối Đa</label>
              <input type="number" v-model="typeMoi.max_occupancy" class="form-control" min="1" required />
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" @click="dongModal">Hủy</button>
            <button class="btn btn-success" @click="luuLoaiPhong">Lưu</button>
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
    const roomTypes = ref([]);
    const tuKhoaTim = ref("");
    const laModalMo = ref(false);
    const typeDangSua = ref(null);
    const typeMoi = ref({
      type_name: "",
      description: "",
      bed_count: 1,
      max_occupancy: 1,
    });
    const trangHienTai = ref(1);
    const soTypeMoiTrang = 10;

    const fetchRoomTypes = async () => {
      try {
        const res = await axios.get("http://localhost:8000/api/room-types");
        roomTypes.value = res.data.data || [];
      } catch (error) {
        alert("Lấy dữ liệu loại phòng thất bại: " + (error.response?.data?.message || error.message));
        console.error('Lỗi:', error.response?.data || error);
      }
    };

    onMounted(() => {
      fetchRoomTypes();
    });

    const typeLoc = computed(() => {
      return roomTypes.value.filter((t) => {
        return (
          t.type_name.toLowerCase().includes(tuKhoaTim.value.toLowerCase()) ||
          (t.description || '').toLowerCase().includes(tuKhoaTim.value.toLowerCase())
        );
      });
    });

    const tongSoTrang = computed(() => Math.ceil(typeLoc.value.length / soTypeMoiTrang));

    const typeHienThi = computed(() => {
      const batDau = (trangHienTai.value - 1) * soTypeMoiTrang;
      const ketThuc = batDau + soTypeMoiTrang;
      return typeLoc.value.slice(batDau, ketThuc);
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
      typeMoi.value = {
        type_name: "",
        description: "",
        bed_count: 1,
        max_occupancy: 1,
      };
      typeDangSua.value = null;
      laModalMo.value = true;
    };

    const moModalSua = (type) => {
      typeMoi.value = { ...type };
      typeDangSua.value = type;
      laModalMo.value = true;
    };

    const dongModal = () => {
      laModalMo.value = false;
    };

    const luuLoaiPhong = async () => {
      if (
        !typeMoi.value.type_name.trim() ||
        isNaN(typeMoi.value.bed_count) || typeMoi.value.bed_count < 1 ||
        isNaN(typeMoi.value.max_occupancy) || typeMoi.value.max_occupancy < 1
      ) {
        alert("Vui lòng nhập đầy đủ và đúng định dạng dữ liệu!");
        return;
      }

      try {
        let response;
        if (typeDangSua.value) {
          response = await axios.put(
            `http://localhost:8000/api/room-types/${typeDangSua.value.id}`,
            typeMoi.value
          );
          const index = roomTypes.value.findIndex(
            (t) => t.id === typeDangSua.value.id
          );
          if (index !== -1) {
            roomTypes.value[index] = { ...typeMoi.value, id: typeDangSua.value.id };
          }
        } else {
          response = await axios.post("http://localhost:8000/api/room-types", typeMoi.value);
          roomTypes.value.push(response.data.data || response.data);
        }
        trangHienTai.value = 1;
        dongModal();
        alert("Lưu loại phòng thành công!");
      } catch (error) {
        const errorMessage = error.response?.data?.message || error.message;
        const errorDetails = error.response?.data?.errors
          ? Object.values(error.response.data.errors).join(", ")
          : "";
        alert(`Lưu loại phòng thất bại: ${errorMessage}${errorDetails ? " - " + errorDetails : ""}`);
        console.error("Lỗi:", error.response?.data || error);
      }
    };

    const xoaLoaiPhong = async (id) => {
      if (confirm("Bạn có chắc chắn muốn xóa loại phòng này?")) {
        try {
          await axios.delete(`http://localhost:8000/api/room-types/${id}`);
          roomTypes.value = roomTypes.value.filter((t) => t.id !== id);
          if (typeHienThi.value.length === 0 && trangHienTai.value > 1) {
            trangHienTai.value--;
          }
          alert("Xóa loại phòng thành công!");
        } catch (error) {
          const errorMessage = error.response?.data?.message || error.message;
          alert(`Xóa loại phòng thất bại: ${errorMessage}`);
          console.error('Lỗi:', error.response?.data || error);
        }
      }
    };

    return {
      roomTypes,
      tuKhoaTim,
      laModalMo,
      typeDangSua,
      typeMoi,
      typeLoc,
      typeHienThi,
      trangHienTai,
      tongSoTrang,
      trangHienThi,
      moModalThem,
      moModalSua,
      dongModal,
      luuLoaiPhong,
      xoaLoaiPhong,
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

.table th {
  background-color: #78c1f1;
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
  background: linear-gradient(135deg, #3f8dd6, #2acabd);
  color: white;
  font-weight: 600;
  font-size: 16px;
}

.table tbody tr:hover {
  background-color: #e6f4ea;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.modal-content {
  border-radius: 12px;
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.modal-header {
  background: linear-gradient(135deg, #3f8dd6, #2acabd);
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