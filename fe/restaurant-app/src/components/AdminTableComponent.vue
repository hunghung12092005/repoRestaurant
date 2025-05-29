<template>
  <div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="fw-bold text-sea-green">Quản Lý Bàn</h1>
      <button class="btn btn-success shadow" @click="moModalThem">
        <i class="bi bi-plus-circle me-2"></i>Thêm Bàn Mới
      </button>
    </div>

    <!-- Tìm kiếm và lọc -->
    <div class="row mb-4 g-3">
      <div class="col-md-4">
        <input
          v-model="tuKhoaTim"
          type="text"
          class="form-control"
          placeholder="Tìm tên bàn hoặc trạng thái..."
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

    <!-- Danh sách bàn -->
    <div class="table-responsive">
      <table class="table table-bordered table-hover align-middle">
        <thead>
          <tr>
            <th>Tên Bàn</th>
            <th>Trạng Thái</th>
            <th>Sức Chứa</th>
            <th>Vị Trí</th>
            <th>Mô Tả</th>
            <th>Hành Động</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="ban in banHienThi" :key="ban.table_id">
            <td>{{ ban.table_name }}</td>
            <td class="status">
              <span
                :class="{
                  badge: true,
                  'bg-success': ban.status === 'Trống',
                  'bg-danger': ban.status === 'Đã đặt',
                  'bg-warning text-dark': ban.status === 'Bảo trì'
                }"
              >
                {{ ban.status }}
              </span>
            </td>
            <td>{{ ban.capacity }}</td>
            <td>{{ ban.location }}</td>
            <td>{{ ban.description }}</td>
            <td>
              <button
                class="btn btn-primary btn-sm me-2"
                @click="moModalSua(ban)"
              >
                <i class="bi bi-pencil"></i> Sửa
              </button>
              <button
                class="btn btn-danger btn-sm"
                @click="xoaBan(ban.table_id)"
              >
                <i class="bi bi-trash"></i> Xóa
              </button>
            </td>
          </tr>
          <tr v-if="banHienThi.length === 0">
            <td colspan="6" class="text-center text-muted">
              Không có bàn nào phù hợp
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Phân trang -->
    <div class="d-flex justify-content-between align-items-center mt-4">
      <div class="text-muted">
        <span>Hiển thị {{ banHienThi.length }} / {{ banLoc.length }} bàn</span>
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
              {{ banDangSua ? "Sửa Thông Tin Bàn" : "Thêm Bàn Mới" }}
            </h5>
            <button type="button" class="btn-close" @click="dongModal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Tên Bàn</label>
              <input type="text" v-model="banMoi.table_name" class="form-control" />
            </div>
            <div class="mb-3">
              <label class="form-label">Sức Chứa</label>
              <input type="number" v-model.number="banMoi.capacity" class="form-control" min="1" />
            </div>
            <div class="mb-3">
              <label class="form-label">Trạng Thái</label>
              <select v-model="banMoi.status" class="form-select">
                <option value="Trống">Trống</option>
                <option value="Đã đặt">Đã đặt</option>
                <option value="Bảo trì">Bảo trì</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Vị Trí</label>
              <input type="text" v-model="banMoi.location" class="form-control" />
            </div>
            <div class="mb-3">
              <label class="form-label">Mô Tả</label>
              <textarea v-model="banMoi.description" class="form-control"></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" @click="dongModal">Hủy</button>
            <button class="btn btn-success" @click="luuBan">Lưu</button>
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
    const ban = ref([]);
    const tuKhoaTim = ref("");
    const locTrangThai = ref("");
    const laModalMo = ref(false);
    const banDangSua = ref(null);
    const banMoi = ref({
      table_name: "",
      capacity: 1, // Mặc định là 1
      status: "Trống",
      location: "",
      description: "",
    });
    const trangHienTai = ref(1);
    const soBanMoiTrang = 5;

    // Lấy danh sách bàn từ API
    const fetchTables = async () => {
      try {
        const res = await axios.get("http://localhost:8000/api/tables", {
          timeout: 5000, // Timeout 5 giây
        });
        console.log("Phản hồi GET /api/tables:", res.data); // Debug phản hồi
        if (res.data && res.data.data) {
          ban.value = res.data.data; // Lấy mảng bàn
        } else {
          ban.value = [];
          console.warn("Không tìm thấy dữ liệu bàn trong phản hồi");
        }
        console.log("Danh sách bàn:", ban.value); // Debug dữ liệu gán
      } catch (error) {
        let errorMessage = "Lấy dữ liệu bàn thất bại";
        if (error.code === "ECONNABORTED") {
          errorMessage += ": Yêu cầu hết thời gian (timeout)";
        } else if (error.response) {
          errorMessage += `: ${error.response.status} - ${error.response.data.message || error.message}`;
        } else if (error.request) {
          errorMessage += ": Không thể kết nối đến server. Kiểm tra server Laravel hoặc CORS.";
        } else {
          errorMessage += `: ${error.message}`;
        }
        alert(errorMessage);
        console.error("Lỗi lấy dữ liệu:", error);
      }
    };

    onMounted(() => {
      fetchTables();
    });

    // Lọc bàn theo từ khóa tìm kiếm và trạng thái
    const banLoc = computed(() => {
      return ban.value.filter((b) => {
        const khopTim = b.table_name
          .toLowerCase()
          .includes(tuKhoaTim.value.toLowerCase()) ||
          b.status.toLowerCase().includes(tuKhoaTim.value.toLowerCase());
        const khopTrangThai = !locTrangThai.value || b.status === locTrangThai.value;
        return khopTim && khopTrangThai;
      });
    });

    // Tính tổng số trang
    const tongSoTrang = computed(() => {
      return Math.ceil(banLoc.value.length / soBanMoiTrang);
    });

    // Lấy danh sách bàn hiển thị trên trang hiện tại
    const banHienThi = computed(() => {
      const batDau = (trangHienTai.value - 1) * soBanMoiTrang;
      const ketThuc = batDau + soBanMoiTrang;
      return banLoc.value.slice(batDau, ketThuc);
    });

    // Tính các trang hiển thị trong phân trang
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

    // Mở modal để thêm bàn mới
    const moModalThem = () => {
      banMoi.value = {
        table_name: "",
        capacity: 1,
        status: "Trống",
        location: "",
        description: "",
      };
      banDangSua.value = null;
      laModalMo.value = true;
    };

    // Mở modal để sửa bàn
    const moModalSua = (b) => {
      banMoi.value = { ...b };
      banDangSua.value = b;
      laModalMo.value = true;
    };

    // Đóng modal
    const dongModal = () => {
      laModalMo.value = false;
    };

    // Lưu bàn (thêm hoặc sửa)
    const luuBan = async () => {
      try {
        // Kiểm tra dữ liệu đầu vào
        if (!banMoi.value.table_name.trim()) {
          throw new Error("Tên bàn không được để trống");
        }
        if (!Number.isInteger(banMoi.value.capacity) || banMoi.value.capacity <= 0) {
          throw new Error("Sức chứa phải là số nguyên dương lớn hơn 0");
        }
        if (!["Trống", "Đã đặt", "Bảo trì"].includes(banMoi.value.status)) {
          throw new Error("Trạng thái không hợp lệ");
        }

        console.log("Dữ liệu gửi đi:", banMoi.value); // Debug dữ liệu gửi

        if (banDangSua.value) {
          // Cập nhật bàn
          const res = await axios.put(
            `http://localhost:8000/api/tables/${banDangSua.value.table_id}`,
            banMoi.value
          );
          console.log("Phản hồi PUT /api/tables:", res.data); // Debug phản hồi
          const index = ban.value.findIndex(
            (b) => b.table_id === banDangSua.value.table_id
          );
          if (index !== -1) {
            ban.value[index] = res.data.data;
          }
          alert("Cập nhật bàn thành công!");
        } else {
          // Thêm bàn mới
          const res = await axios.post("http://localhost:8000/api/tables", banMoi.value);
          console.log("Phản hồi POST /api/tables:", res.data); // Debug phản hồi
          ban.value.push(res.data.data);
          alert("Thêm bàn thành công!");
        }
        trangHienTai.value = 1;
        dongModal();
      } catch (error) {
        let errorMessage = "Lưu bàn thất bại";
        if (error.code === "ECONNABORTED") {
          errorMessage += ": Yêu cầu hết thời gian (timeout)";
        } else if (error.response) {
          if (error.response.status === 422) {
            errorMessage += ": " + Object.values(error.response.data.errors).flat().join(", ");
          } else {
            errorMessage += `: ${error.response.status} - ${error.response.data.message || error.message}`;
          }
        } else if (error.request) {
          errorMessage += ": Không thể kết nối đến server. Kiểm tra server Laravel hoặc CORS.";
        } else {
          errorMessage += `: ${error.message}`;
        }
        alert(errorMessage);
        console.error("Lỗi lưu bàn:", error);
      }
    };

    // Xóa bàn
    const xoaBan = async (id) => {
      if (confirm("Bạn có chắc chắn muốn xóa bàn này không?")) {
        try {
          await axios.delete(`http://localhost:8000/api/tables/${id}`);
          ban.value = ban.value.filter((b) => b.table_id !== id);
          if (banHienThi.value.length === 0 && trangHienTai.value > 1) {
            trangHienTai.value--;
          }
          alert("Xóa bàn thành công!");
        } catch (error) {
          let errorMessage = "Xóa bàn thất bại";
          if (error.response) {
            errorMessage += `: ${error.response.status} - ${error.response.data.message || error.message}`;
          } else {
            errorMessage += `: ${error.message}`;
          }
          alert(errorMessage);
          console.error("Lỗi xóa bàn:", error);
        }
      }
    };

    return {
      ban,
      tuKhoaTim,
      locTrangThai,
      laModalMo,
      banDangSua,
      banMoi,
      banLoc,
      banHienThi,
      trangHienTai,
      tongSoTrang,
      trangHienThi,
      moModalThem,
      moModalSua,
      dongModal,
      luuBan,
      xoaBan,
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

.table th,
.status {
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
  background: linear-gradient(135deg, #1199f3, #2acabd);
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
  background: linear-gradient(135deg, #1199f3, #2acabd);
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