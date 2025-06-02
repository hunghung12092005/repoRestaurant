<template>
  <div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="fw-bold text-sea-green">Quản Lý Món Ăn</h1>
      <button class="btn btn-success shadow" @click="moModalThem">
        <i class="bi bi-plus-circle me-2"></i>Thêm Món Ăn Mới
      </button>
    </div>

    <!-- Tìm kiếm -->
    <div class="row mb-4 g-3">
      <div class="col-md-4">
        <input
          v-model="tuKhoaTim"
          type="text"
          class="form-control"
          placeholder="Tìm tên món ăn, danh mục..."
        />
      </div>
    </div>

    <!-- Danh sách món ăn -->
    <div class="table-responsive">
      <table class="table table-bordered table-hover align-middle">
        <thead>
          <tr>
            <th>Tên Món Ăn</th>
            <th>Danh Mục</th>
            <th>Giá</th>
            <th>Mô Tả</th>
            <th>Hành Động</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="menu in menuHienThi" :key="menu.menu_id">
            <td>{{ menu.menu_name }}</td>
            <td>{{ menu.category_name || 'Không xác định' }}</td>
            <td>{{ formatPrice(menu.price) }}</td>
            <td>{{ menu.description || 'Không có' }}</td>
            <td>
              <button
                class="btn btn-primary btn-sm me-2"
                @click="moModalSua(menu)"
              >
                <i class="bi bi-pencil"></i> Sửa
              </button>
              <button
                class="btn btn-danger btn-sm"
                @click="xoaMonAn(menu.menu_id)"
              >
                <i class="bi bi-trash"></i> Xóa
              </button>
            </td>
          </tr>
          <tr v-if="menuHienThi.length === 0">
            <td colspan="5" class="text-center text-muted">
              Không có món ăn nào phù hợp
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Phân trang -->
    <div class="d-flex justify-content-between align-items-center mt-4">
      <div class="text-muted">
        <span>Hiển thị {{ menuHienThi.length }} / {{ menuLoc.length }} món ăn</span>
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
              {{ menuDangSua ? "Sửa Món Ăn" : "Thêm Món Ăn Mới" }}
            </h5>
            <button type="button" class="btn-close" @click="dongModal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Tên Món Ăn</label>
              <input type="text" v-model="menuMoi.menu_name" class="form-control" />
            </div>
            <div class="mb-3">
              <label class="form-label">Danh Mục</label>
              <select v-model="menuMoi.category_id" class="form-select">
                <option value="0">Chọn danh mục</option>
                <option
                  v-for="category in categories"
                  :key="category.category_id"
                  :value="category.category_id"
                >
                  {{ category.category_name }}
                </option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Giá</label>
              <input
                type="number"
                v-model.number="menuMoi.price"
                class="form-control"
                min="0"
                step="0.01"
              />
            </div>
            <div class="mb-3">
              <label class="form-label">Mô Tả</label>
              <textarea v-model="menuMoi.description" class="form-control"></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" @click="dongModal">Hủy</button>
            <button class="btn btn-success" @click="luuMonAn" :disabled="isLoading">
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
  name: "AdminMenuComponent",
  setup() {
    const menus = ref([]);
    const categories = ref([]);
    const tuKhoaTim = ref("");
    const laModalMo = ref(false);
    const menuDangSua = ref(null);
    const menuMoi = ref({
      menu_name: "",
      category_id: 0,
      price: 0,
      description: "",
    });
    const trangHienTai = ref(1);
    const soMenuMoiTrang = 10;
    const isLoading = ref(false);

    // Lấy danh sách món ăn từ API
    const fetchMenus = async () => {
      try {
        const res = await axios.get("http://localhost:8000/api/menus", {
          timeout: 5000,
        });
        console.log("Phản hồi GET /api/menus:", res.data);
        menus.value = res.data.data || [];
        if (!res.data.data) {
          console.warn("Không tìm thấy dữ liệu món ăn trong phản hồi");
        }
      } catch (error) {
        let errorMessage = "Lấy dữ liệu món ăn thất bại";
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

    // Lấy danh sách danh mục món ăn từ API
    const fetchCategories = async () => {
      try {
        const res = await axios.get("http://localhost:8000/api/menu-categories", {
          timeout: 5000,
        });
        console.log("Phản hồi GET /api/menu-categories:", res.data);
        categories.value = res.data.data || [];
        if (!res.data.data) {
          console.warn("Không tìm thấy dữ liệu danh mục trong phản hồi");
        }
      } catch (error) {
        let errorMessage = "Lấy dữ liệu danh mục món ăn thất bại";
        if (error.code === "ECONNABORTED") {
          errorMessage += ": Yêu cầu hết thời gian (timeout)";
        } else if (error.response) {
          errorMessage += `: ${error.response.status} - ${error.response.data.message || error.message}`;
        } else if (error.request) {
          errorMessage += ": Không thể kết nối đến server.";
        } else {
          errorMessage += `: ${error.message}`;
        }
        alert(errorMessage);
        console.error("Lỗi lấy dữ liệu danh mục:", error);
      }
    };

    onMounted(() => {
      fetchMenus();
      fetchCategories();
    });

    // Định dạng giá tiền
    const formatPrice = (price) => {
      return new Intl.NumberFormat("vi-VN", {
        style: "currency",
        currency: "VND",
      }).format(price);
    };

    // Lọc món ăn theo từ khóa tìm kiếm
    const menuLoc = computed(() => {
      return menus.value.filter((m) => {
        const khopTim =
          m.menu_name.toLowerCase().includes(tuKhoaTim.value.toLowerCase()) ||
          (m.category_name || "")
            .toLowerCase()
            .includes(tuKhoaTim.value.toLowerCase());
        return khopTim;
      });
    });

    // Tính tổng số trang
    const tongSoTrang = computed(() => {
      return Math.ceil(menuLoc.value.length / soMenuMoiTrang);
    });

    // Lấy danh sách món ăn hiển thị trên trang hiện tại
    const menuHienThi = computed(() => {
      const batDau = (trangHienTai.value - 1) * soMenuMoiTrang;
      const ketThuc = batDau + soMenuMoiTrang;
      return menuLoc.value.slice(batDau, ketThuc);
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

    // Mở modal để thêm món ăn mới
    const moModalThem = () => {
      menuMoi.value = {
        menu_name: "",
        category_id: 0,
        price: 0,
        description: "",
      };
      menuDangSua.value = null;
      laModalMo.value = true;
    };

    // Mở modal để sửa món ăn
    const moModalSua = (m) => {
      menuMoi.value = {
        menu_name: m.menu_name,
        category_id: m.category_id,
        price: m.price,
        description: m.description || "",
      };
      menuDangSua.value = m;
      laModalMo.value = true;
    };

    // Đóng modal
    const dongModal = () => {
      laModalMo.value = false;
    };

    // Lưu món ăn (thêm hoặc sửa)
    const luuMonAn = async () => {
      // Kiểm tra dữ liệu đầu vào
      if (!menuMoi.value.menu_name.trim()) {
        alert("Tên món ăn không được để trống");
        return;
      }
      if (menuMoi.value.category_id === 0) {
        alert("Vui lòng chọn danh mục");
        return;
      }
      if (!Number.isFinite(menuMoi.value.price) || menuMoi.value.price <= 0) {
        alert("Giá phải là số lớn hơn 0");
        return;
      }

      isLoading.value = true;
      try {
        console.log("Dữ liệu gửi đi:", menuMoi.value);
        if (menuDangSua.value) {
          // Cập nhật món ăn
          const res = await axios.put(
            `http://localhost:8000/api/menus/${menuDangSua.value.menu_id}`,
            menuMoi.value
          );
          console.log("Phản hồi PUT /api/menus:", res.data);
          const index = menus.value.findIndex(
            (m) => m.menu_id === menuDangSua.value.menu_id
          );
          if (index !== -1) {
            menus.value[index] = res.data.data;
          }
          alert("Cập nhật món ăn thành công!");
        } else {
          // Thêm món ăn mới
          const res = await axios.post("http://localhost:8000/api/menus", menuMoi.value);
          console.log("Phản hồi POST /api/menus:", res.data);
          menus.value.push(res.data.data);
          alert("Thêm món ăn thành công!");
        }
        trangHienTai.value = 1;
        dongModal();
      } catch (error) {
        let errorMessage = "Lưu món ăn thất bại";
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
        console.error("Lỗi lưu món ăn:", error);
      } finally {
        isLoading.value = false;
      }
    };

    // Xóa món ăn
    const xoaMonAn = async (id) => {
      if (confirm("Bạn có chắc chắn muốn xóa món ăn này không?")) {
        try {
          await axios.delete(`http://localhost:8000/api/menus/${id}`);
          menus.value = menus.value.filter((m) => m.menu_id !== id);
          if (menuHienThi.value.length === 0 && trangHienTai.value > 1) {
            trangHienTai.value--;
          }
          alert("Xóa món ăn thành công!");
        } catch (error) {
          let errorMessage = "Xóa món ăn thất bại";
          if (error.response) {
            errorMessage += `: ${error.response.status} - ${error.response.data.message || error.message}`;
          } else {
            errorMessage += `: ${error.message}`;
          }
          alert(errorMessage);
          console.error("Lỗi xóa món ăn:", error);
        }
      }
    };

    return {
      menus,
      categories,
      tuKhoaTim,
      laModalMo,
      menuDangSua,
      menuMoi,
      menuLoc,
      menuHienThi,
      trangHienTai,
      tongSoTrang,
      trangHienThi,
      moModalThem,
      moModalSua,
      dongModal,
      luuMonAn,
      xoaMonAn,
      formatPrice,
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