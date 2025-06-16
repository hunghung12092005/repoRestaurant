<template>
  <div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="fw-bold text-sea-green">Quản Lý Giá Phòng</h1>
      <button class="btn btn-success shadow" @click="openAddModal">
        <i class="bi bi-plus-circle me-2"></i>Thêm Giá Phòng Mới
      </button>
    </div>

    <!-- Tìm kiếm -->
    <div class="row mb-4 g-3">
      <div class="col-md-4">
        <input
          v-model="state.searchKeyword"
          type="text"
          class="form-control"
          placeholder="Tìm tên phòng hoặc loại phòng..."
          @input="logSearchKeyword"
        />
      </div>
    </div>

    <!-- Danh sách giá phòng -->
    <div class="table-responsive">
      <table class="table table-bordered table-hover align-middle">
        <thead>
          <tr>
            <th>Loại Phòng</th>
            <th>Ngày Bắt Đầu</th>
            <th>Ngày Kết Thúc</th>
            <th>Giá Đêm (VNĐ)</th>
            <th>Giá Giờ (VNĐ)</th>
            <th>Hành Động</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="state.isLoading">
            <td colspan="6" class="text-center">Đang tải dữ liệu...</td>
          </tr>
          <tr v-else-if="!state.displayedPricings || state.displayedPricings.length === 0">
            <td colspan="6" class="text-center text-muted">
              Không có giá phòng nào phù hợp
            </td>
          </tr>
          <tr v-else v-for="pricing in state.displayedPricings" :key="pricing.price_id">
            <td>{{ pricing.room_type?.type_name || 'Không xác định' }}</td>
            <td>{{ formatDate(pricing.start_date) }}</td>
            <td>{{ formatDate(pricing.end_date) }}</td>
            <td>{{ formatPrice(pricing.price_per_night) }}</td>
            <td>{{ formatPrice(pricing.hourly_price) }}</td>
            <td>
              <button
                class="btn btn-primary btn-sm me-2"
                @click="openEditModal(pricing)"
              >
                <i class="bi bi-pencil"></i> Sửa
              </button>
              <button
                class="btn btn-danger btn-sm"
                @click="deletePricing(pricing.price_id)"
              >
                <i class="bi bi-trash"></i> Xóa
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Phân trang -->
    <div v-if="state.totalItems > 0" class="d-flex justify-content-between align-items-center mt-4">
      <div class="text-muted">
        Hiển thị {{ state.displayedPricings?.length || 0 }} / {{ state.totalItems }} giá phòng
      </div>
      <nav>
        <ul class="pagination mb-0">
          <li class="page-item" :class="{ disabled: state.currentPage === 1 }">
            <button class="page-link" @click="state.currentPage = 1">
              <i class="bi bi-chevron-double-left"></i>
            </button>
          </li>
          <li class="page-item" :class="{ disabled: state.currentPage === 1 }">
            <button class="page-link" @click="state.currentPage--">
              <i class="bi bi-chevron-left"></i>
            </button>
          </li>
          <li
            v-for="page in pageRange"
            :key="page"
            class="page-item"
            :class="{ active: state.currentPage === page }"
          >
            <button class="page-link" @click="state.currentPage = page">{{ page }}</button>
          </li>
          <li class="page-item" :class="{ disabled: state.currentPage === state.totalPages }">
            <button class="page-link" @click="state.currentPage++">
              <i class="bi bi-chevron-right"></i>
            </button>
          </li>
          <li class="page-item" :class="{ disabled: state.currentPage === state.totalPages }">
            <button class="page-link" @click="state.currentPage = state.totalPages">
              <i class="bi bi-chevron-double-right"></i>
            </button>
          </li>
        </ul>
      </nav>
    </div>

    <!-- Modal thêm/sửa -->
    <div
      v-if="state.isModalOpen"
      class="modal fade show d-block"
      tabindex="-1"
      style="background-color: rgba(0, 0, 0, 0.5)"
    >
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              {{ state.currentPricing ? "Sửa Giá Phòng" : "Thêm Giá Phòng Mới" }}
            </h5>
            <button type="button" class="btn-close" @click="closeModal"></button>
          </div>
          <div class="modal-body">
            <div v-if="state.isLoading" class="text-center mb-3">
              <div class="spinner-border text-primary">
                <span class="visually-hidden">Đang tải...</span>
              </div>
            </div>
            <div v-if="state.modalErrorMessage" class="alert alert-warning">{{ state.modalErrorMessage }}</div>
            <form @submit.prevent="savePricing">
              <div class="mb-3">
                <label class="form-label">Ngày Bắt Đầu</label>
                <input
                  type="date"
                  v-model="state.newPricing.start_date"
                  class="form-control"
                  :min="today"
                  required
                  :class="{ 'is-invalid': state.errors.start_date }"
                />
                <div v-if="state.errors.start_date" class="invalid-feedback">{{ state.errors.start_date }}</div>
              </div>
              <div class="mb-3">
                <label class="form-label">Ngày Kết Thúc</label>
                <input
                  type="date"
                  v-model="state.newPricing.end_date"
                  class="form-control"
                  :min="state.newPricing.start_date || today"
                  required
                  :class="{ 'is-invalid': state.errors.end_date }"
                />
                <div v-if="state.errors.end_date" class="invalid-feedback">{{ state.errors.end_date }}</div>
              </div>
              <div class="mb-3">
                <label class="form-label">Mô Tả</label>
                <textarea
                  v-model="state.newPricing.description"
                  class="form-control"
                  :class="{ 'is-invalid': state.errors.description }"
                ></textarea>
                <div v-if="state.errors.description" class="invalid-feedback">{{ state.errors.description }}</div>
              </div>
              <div class="mb-3">
                <label class="form-label">Loại Phòng</label>
                <select
                  v-model="state.newPricing.type_id"
                  class="form-control"
                  required
                  :class="{ 'is-invalid': state.errors.type_id }"
                >
                  <option value="">-- Chọn loại phòng --</option>
                  <option v-for="type in state.roomTypes" :key="type.type_id" :value="type.type_id">
                    {{ type.type_name }}
                  </option>
                </select>
                <div v-if="state.errors.type_id" class="invalid-feedback">{{ state.errors.type_id }}</div>
              </div>
              <div class="mb-3">
                <label class="form-label">Giá Đêm (VNĐ)</label>
                <input
                  type="number"
                  v-model.number="state.newPricing.price_per_night"
                  class="form-control"
                  min="0"
                  required
                  :class="{ 'is-invalid': state.errors.price_per_night }"
                />
                <div v-if="state.errors.price_per_night" class="invalid-feedback">{{ state.errors.price_per_night }}</div>
              </div>
              <div class="mb-3">
                <label class="form-label">Giá Giờ (VNĐ)</label>
                <input
                  type="number"
                  v-model.number="state.newPricing.hourly_price"
                  class="form-control"
                  min="0"
                  required
                  :class="{ 'is-invalid': state.errors.hourly_price }"
                />
                <div v-if="state.errors.hourly_price" class="invalid-feedback">{{ state.errors.hourly_price }}</div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" @click="closeModal" :disabled="state.isLoading">Hủy</button>
                <button type="submit" class="btn btn-success" :disabled="state.isLoading">Lưu</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { reactive, computed, watch, onMounted } from "vue";
import axios from "axios";

const apiClient = axios.create({
  baseURL: "http://localhost:8000/api",
  timeout: 60000,
  headers: { "Content-Type": "application/json", "Accept": "application/json" }
});

export default {
  setup() {
    const state = reactive({
      seasonalPricings: [],
      roomTypes: [],
      searchKeyword: "",
      isModalOpen: false,
      isLoading: false,
      currentPricing: null,
      newPricing: {
        type_id: "",
        start_date: "",
        end_date: "",
        description: "",
        price_per_night: 0,
        hourly_price: 0
      },
      errors: {
        start_date: "",
        end_date: "",
        description: "",
        type_id: "",
        price_per_night: "",
        hourly_price: ""
      },
      modalErrorMessage: "",
      currentPage: 1,
      itemsPerPage: 10,
      totalItems: 0
    });

    // Ngày hiện tại
    const today = new Date().toISOString().split('T')[0];

    // Format giá
    const formatPrice = (price) => {
      return price ? new Intl.NumberFormat("vi-VN", { style: "currency", currency: "VND" }).format(price) : "0 ₫";
    };

    // Format ngày
    const formatDate = (date) => {
      return date ? new Date(date).toLocaleDateString("vi-VN") : "";
    };

    // Log từ khóa tìm kiếm
    const logSearchKeyword = () => {
      console.log("Từ khóa tìm kiếm:", state.searchKeyword);
    };

    // Lấy danh sách giá
    const fetchPricings = async (page = 1) => {
      state.isLoading = true;
      state.modalErrorMessage = "";
      try {
        const response = await apiClient.get(`/pricese?page=${page}&per_page=${state.itemsPerPage}`);
        console.log("Dữ liệu API trả về:", JSON.stringify(response.data, null, 2));
        state.seasonalPricings = Array.isArray(response.data.data) ? [...response.data.data] : [];
        state.totalItems = response.data.total || 0;
        state.currentPage = response.data.current_page || 1;
        console.log("Danh sách giá sau khi cập nhật:", state.seasonalPricings);
      } catch (error) {
        console.error("Lỗi khi lấy danh sách giá:", {
          message: error.message,
          response: error.response?.data,
          status: error.response?.status
        });
        state.modalErrorMessage = error.response?.data?.message || "Không thể tải danh sách giá phòng.";
        state.seasonalPricings = [];
        state.totalItems = 0;
      } finally {
        state.isLoading = false;
      }
    };

    // Lấy danh sách loại phòng
    const fetchRoomTypes = async () => {
      try {
        const response = await apiClient.get("/room-types");
        state.roomTypes = Array.isArray(response.data.data) ? [...response.data.data] : [];
        console.log("Danh sách loại phòng:", state.roomTypes);
      } catch (error) {
        console.error("Lỗi khi lấy danh sách loại phòng:", error);
        state.modalErrorMessage = error.response?.data?.message || "Không thể tải danh sách loại phòng.";
      }
    };

    // Khởi tạo dữ liệu
    onMounted(async () => {
      state.isLoading = true;
      try {
        await Promise.all([fetchPricings(), fetchRoomTypes()]);
      } catch (error) {
        console.error("Lỗi khi khởi tạo dữ liệu:", error);
        state.modalErrorMessage = "Khởi tạo dữ liệu thất bại.";
      } finally {
        state.isLoading = false;
      }
    });

    // Theo dõi thay đổi trang
    watch(() => state.currentPage, (newPage) => {
      fetchPricings(newPage);
    });

    // Lọc giá
    const filteredPricings = computed(() => {
      console.log("Tính toán filteredPricings, từ khóa:", state.searchKeyword, "dữ liệu:", state.seasonalPricings);
      if (!Array.isArray(state.seasonalPricings)) return [];
      if (!state.searchKeyword.trim()) return state.seasonalPricings;
      return state.seasonalPricings.filter(pricing => {
        const typeName = pricing.room_type?.type_name?.toLowerCase() || "";
        return typeName.includes(state.searchKeyword.toLowerCase().trim());
      });
    });

    // Danh sách giá hiển thị
    const displayedPricings = computed(() => {
      const result = filteredPricings.value || [];
      console.log("Danh sách giá hiển thị:", result);
      return result;
    });

    // Tính tổng số trang
    const totalPages = computed(() => Math.ceil(state.totalItems / state.itemsPerPage));

    // Tính phạm vi trang
    const pageRange = computed(() => {
      const maxPages = 5;
      let start = Math.max(1, state.currentPage - Math.floor(maxPages / 2));
      let end = Math.min(totalPages.value, start + maxPages - 1);
      if (end - start + 1 < maxPages) {
        start = Math.max(1, end - maxPages + 1);
      }
      return Array.from({ length: end - start + 1 }, (_, i) => start + i);
    });

    // Mở modal thêm
    const openAddModal = () => {
      state.newPricing = {
        type_id: "",
        start_date: "",
        end_date: "",
        description: "",
        price_per_night: 0,
        hourly_price: 0
      };
      state.errors = {};
      state.currentPricing = null;
      state.isModalOpen = true;
      state.modalErrorMessage = "";
      console.log("Mở modal thêm, reset form:", { ...state.newPricing });
    };

    // Mở modal sửa
    const openEditModal = (pricing) => {
      console.log("Mở modal sửa:", JSON.stringify(pricing, null, 2));
      if (!pricing || typeof pricing !== 'object') {
        state.modalErrorMessage = "Dữ liệu giá không hợp lệ.";
        return;
      }
      state.newPricing = {
        type_id: String(pricing.type_id || ""),
        start_date: pricing.start_date || "",
        end_date: pricing.end_date || "",
        description: String(pricing.description || ""),
        price_per_night: Number(pricing.price_per_night) || 0,
        hourly_price: Number(pricing.hourly_price) || 0
      };
      state.currentPricing = { ...pricing };
      state.isModalOpen = true;
      state.errors = {};
      state.modalErrorMessage = "";
      console.log("Form sửa:", { ...state.newPricing });
    };

    // Đóng modal
    const closeModal = () => {
      state.isModalOpen = false;
      state.errors = {};
      state.modalErrorMessage = "";
      state.currentPricing = null;
      state.newPricing = {
        type_id: "",
        start_date: "",
        end_date: "",
        description: "",
        price_per_night: 0,
        hourly_price: 0
      };
      console.log("Đóng modal, reset form:", { ...state.newPricing });
    };

    // Kiểm tra form
    const validateForm = () => {
      state.errors = {};
      let isValid = true;

      if (!state.newPricing.start_date) {
        state.errors.start_date = "Vui lòng chọn ngày bắt đầu";
        isValid = false;
      }
      if (!state.newPricing.end_date) {
        state.errors.end_date = "Vui lòng chọn ngày kết thúc";
        isValid = false;
      }
      if (state.newPricing.start_date && state.newPricing.end_date && 
          new Date(state.newPricing.start_date) >= new Date(state.newPricing.end_date)) {
        state.errors.end_date = "Ngày kết thúc phải sau ngày bắt đầu";
        isValid = false;
      }
      if (!state.newPricing.type_id) {
        state.errors.type_id = "Vui lòng chọn loại phòng";
        isValid = false;
      }
      if (isNaN(state.newPricing.price_per_night) || state.newPricing.price_per_night < 0) {
        state.errors.price_per_night = "Giá đêm phải là số không âm";
        isValid = false;
      }
      if (isNaN(state.newPricing.hourly_price) || state.newPricing.hourly_price < 0) {
        state.errors.hourly_price = "Giá giờ phải là số không âm";
        isValid = false;
      }

      console.log("Kết quả kiểm tra form:", { isValid, form: { ...state.newPricing }, errors: state.errors });
      return isValid;
    };

    // Lưu giá
    const savePricing = async () => {
      console.log("Dữ liệu form trước khi kiểm tra:", { ...state.newPricing });
      if (!validateForm()) {
        state.modalErrorMessage = "Vui lòng kiểm tra thông tin nhập.";
        return;
      }

      state.isLoading = true;
      state.modalErrorMessage = "";
      const payload = {
        type_id: state.newPricing.type_id,
        start_date: state.newPricing.start_date,
        end_date: state.newPricing.end_date,
        description: state.newPricing.description.trim() || null,
        price_per_night: state.newPricing.price_per_night,
        hourly_price: state.newPricing.hourly_price
      };
      console.log("Gửi dữ liệu:", payload);
      try {
        let response;
        if (state.currentPricing) {
          response = await apiClient.put(`/pricese/${state.currentPricing.price_id}`, payload);
          console.log("Kết quả cập nhật:", JSON.stringify(response.data, null, 2));
          const index = state.seasonalPricings.findIndex(p => p.price_id === state.currentPricing.price_id);
          if (index !== -1) {
            state.seasonalPricings[index] = { ...response.data.data };
          }
        } else {
          response = await apiClient.post("/pricese", payload);
          console.log("Kết quả thêm mới:", JSON.stringify(response.data, null, 2));
          await fetchPricings(state.currentPage);
        }
        closeModal();
        state.modalErrorMessage = "Lưu giá phòng thành công!";
      } catch (error) {
        console.error("Lỗi khi lưu giá:", {
          message: error.message,
          response: error.response?.data,
          status: error.response?.status
        });
        state.modalErrorMessage = error.response?.data?.message || "Lưu giá phòng thất bại.";
        if (error.response?.status === 422) {
          state.errors = { ...error.response.data?.errors };
          state.modalErrorMessage += ": " + Object.values(error.response.data?.errors).flat().join(", ");
        }
      } finally {
        state.isLoading = false;
      }
    };

    // Xóa giá
    const deletePricing = async (id) => {
      if (!confirm("Bạn có chắc chắn muốn xóa giá phòng này?")) return;

      state.isLoading = true;
      state.modalErrorMessage = "";
      try {
        await apiClient.delete(`/pricese/${id}`);
        state.seasonalPricings = state.seasonalPricings.filter(p => p.price_id !== id);
        state.totalItems = Math.max(0, state.totalItems - 1);
        if (state.displayedPricings.length === 0 && state.currentPage > 1) {
          state.currentPage--;
        }
        state.modalErrorMessage = "Xóa giá phòng thành công!";
        await fetchPricings(state.currentPage);
      } catch (error) {
        console.error("Lỗi khi xóa giá:", error);
        state.modalErrorMessage = error.response?.data?.message || "Xóa giá phòng thất bại.";
      } finally {
        state.isLoading = false;
      }
    };

    return {
      state,
      today,
      formatPrice,
      formatDate,
      pageRange,
      displayedPricings,
      openAddModal,
      openEditModal,
      closeModal,
      savePricing,
      deletePricing,
      logSearchKeyword
    };
  }
};
</script>

<style scoped>
th {
  background-color: #78c1f1;
  text-align: center;
}

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