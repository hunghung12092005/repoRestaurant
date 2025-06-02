<template>
  <div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="fw-bold text-sea-green">Quản Lý Loại Bàn</h1>
      <button class="btn btn-success shadow" @click="moModalThem">
        <i class="bi bi-plus-circle me-2"></i>Thêm Loại Bàn Mới
      </button>
    </div>

    <!-- Tìm kiếm -->
    <div class="row mb-4 g-3">
      <div class="col-md-4">
        <input
          v-model="tuKhoaTim"
          type="text"
          class="form-control"
          placeholder="Tìm tên loại bàn..."
        />
      </div>
    </div>

    <!-- Danh sách loại bàn -->
    <div class="table-responsive">
      <table class="table table-bordered table-hover align-middle">
        <thead>
          <tr>
            <th>Tên Loại Bàn</th>
            <th>Mô Tả</th>
            <th>Hành Động</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="type in typeHienThi" :key="type.type_id">
            <td>{{ type.type_name }}</td>
            <td>{{ type.description }}</td>
            <td>
              <button
                class="btn btn-primary btn-sm me-2"
                @click="moModalSua(type)"
              >
                <i class="bi bi-pencil"></i> Sửa
              </button>
              <button
                class="btn btn-danger btn-sm"
                @click="xoaLoaiBan(type.type_id)"
              >
                <i class="bi bi-trash"></i> Xóa
              </button>
            </td>
          </tr>
          <tr v-if="typeHienThi.length === 0">
            <td colspan="3" class="text-center text-muted">
              Không có loại bàn nào phù hợp
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Phân trang -->
    <div class="d-flex justify-content-between align-items-center mt-4">
      <div class="text-muted">
        <span>Hiển thị {{ typeHienThi.length }} / {{ typeLoc.length }} loại bàn</span>
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
              {{ typeDangSua ? "Sửa Loại Bàn" : "Thêm Loại Bàn Mới" }}
            </h5>
            <button type="button" class="btn-close" @click="dongModal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Tên Loại Bàn</label>
              <input type="text" v-model="typeMoi.type_name" class="form-control" />
            </div>
            <div class="mb-3">
              <label class="form-label">Mô Tả</label>
              <textarea v-model="typeMoi.description" class="form-control"></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" @click="dongModal">Hủy</button>
            <button class="btn btn-success" @click="luuLoaiBan" :disabled="isLoading">
              {{ isLoading ? 'Đang lưu...' : 'Lưu' }}
            </button>
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
    const tableTypes = ref([]);
    const tuKhoaTim = ref("");
    const laModalMo = ref(false);
    const typeDangSua = ref(null);
    const typeMoi = ref({
      type_name: "",
      description: "",
    });
    const trangHienTai = ref(1);
    const soTypeMoiTrang = 10;
    const isLoading = ref(false);

    const fetchTableTypes = async () => {
      try {
        const res = await axios.get("http://localhost:8000/api/table-types");
        tableTypes.value = res.data.data || res.data;
        console.log('Danh sách loại bàn:', tableTypes.value);
      } catch (error) {
        alert("Lấy dữ liệu loại bàn thất bại: " + (error.response?.data?.message || error.message));
        console.error('Lỗi tải loại bàn:', error.response?.data || error.message);
      }
    };

    onMounted(() => {
      fetchTableTypes();
    });

    const typeLoc = computed(() => {
      return tableTypes.value.filter((t) => {
        return t.type_name
          .toLowerCase()
          .includes(tuKhoaTim.value.toLowerCase()) ||
          (t.description || '')
            .toLowerCase()
            .includes(tuKhoaTim.value.toLowerCase());
      });
    });

    const tongSoTrang = computed(() => {
      return Math.ceil(typeLoc.value.length / soTypeMoiTrang);
    });

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
      };
      typeDangSua.value = null;
      laModalMo.value = true;
    };

    const moModalSua = (t) => {
      typeMoi.value = { ...t };
      typeDangSua.value = t;
      laModalMo.value = true;
    };

    const dongModal = () => {
      laModalMo.value = false;
    };

    const luuLoaiBan = async () => {
      if (!typeMoi.value.type_name.trim()) {
        alert("Vui lòng nhập tên loại bàn");
        return;
      }
      isLoading.value = true;
      try {
        if (typeDangSua.value) {
          await axios.put(
            `http://localhost:8000/api/table-types/${typeDangSua.value.type_id}`,
            typeMoi.value
          );
          const index = tableTypes.value.findIndex(
            (t) => t.type_id === typeDangSua.value.type_id
          );
          if (index !== -1)
            tableTypes.value[index] = { ...typeMoi.value, type_id: typeDangSua.value.type_id };
        } else {
          const res = await axios.post("http://localhost:8000/api/table-types", typeMoi.value);
          tableTypes.value.push(res.data.data || res.data);
        }
        trangHienTai.value = 1;
        dongModal();
      } catch (error) {
        alert("Lưu loại bàn thất bại: " + (error.response?.data?.message || error.message));
        console.error('Lỗi lưu loại bàn:', error.response?.data || error.message);
      } finally {
        isLoading.value = false;
      }
    };

    const xoaLoaiBan = async (id) => {
      if (confirm("Bạn có chắc chắn muốn xóa loại bàn này không?")) {
        try {
          await axios.delete(`http://localhost:8000/api/table-types/${id}`);
          tableTypes.value = tableTypes.value.filter((t) => t.type_id !== id);
          if (typeHienThi.value.length === 0 && trangHienTai.value > 1) {
            trangHienTai.value--;
          }
        } catch (error) {
          alert("Xóa loại bàn thất bại: " + (error.response?.data?.message || error.message));
          console.error('Lỗi xóa loại bàn:', error.response?.data || error.message);
        }
      }
    };

    return {
      tableTypes,
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
      luuLoaiBan,
      xoaLoaiBan,
      isLoading,
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