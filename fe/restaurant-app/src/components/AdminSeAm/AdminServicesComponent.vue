<template>
  <div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="fw-bold text-sea-green">Quản Lý Dịch Vụ</h1>
      <button class="btn btn-success shadow" @click="openAddModal">
        <i class="bi bi-plus-circle me-2"></i>Thêm Dịch Vụ
      </button>
    </div>
    <div v-if="successMessage" class="alert alert-success">{{ successMessage }}</div>
    <div v-if="errorMessage && !isLoading" class="alert alert-danger">{{ errorMessage }}</div>

    <!-- Tìm kiếm -->
    <div class="row mb-4">
      <div class="col-md-4">
        <input
          v-model="searchQuery"
          type="text"
          class="form-control"
          placeholder="Tìm tên dịch vụ..."
        />
      </div>
    </div>

    <!-- Danh sách dịch vụ -->
    <div class="table-responsive">
      <table class="table table-bordered table-hover align-middle">
        <thead>
          <tr>
            <th>#</th>
            <th>Tên Dịch Vụ</th>
            <th>Giá (VNĐ)</th>
            <th>Mô Tả</th>
            <th>Hành Động</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(service, index) in displayedServices" :key="service.service_id">
            <td>{{ (currentPage - 1) * itemsPerPage + index + 1 }}</td>
            <td>{{ service.service_name }}</td>
            <td>{{ formatPrice(service.price) }}</td>
            <td>{{ service.description || 'Không có' }}</td>
            <td>
              <button class="btn btn-primary btn-sm me-2" @click="openEditModal(service)">
                <i class="bi bi-pencil me-1"></i>Sửa
              </button>
              <button class="btn btn-danger btn-sm" @click="deleteService(service.service_id)">
                <i class="bi bi-trash me-1"></i>Xóa
              </button>
            </td>
          </tr>
          <tr v-if="displayedServices.length === 0 && !isLoading">
            <td colspan="5" class="text-center text-muted">
              Không có dịch vụ nào phù hợp
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Phân trang -->
    <div v-if="totalItems > 0" class="d-flex justify-content-between align-items-center mt-4">
      <div class="text-muted">
        Hiển thị {{ displayedServices.length }} / {{ totalItems }} dịch vụ
      </div>
      <nav>
        <ul class="pagination mb-0">
          <li class="page-item" :class="{ disabled: currentPage === 1 }">
            <button class="page-link" @click="currentPage = 1">
              <i class="bi bi-chevron-double-left"></i>
            </button>
          </li>
          <li class="page-item" :class="{ disabled: currentPage === 1 }">
            <button class="page-link" @click="currentPage--">
              <i class="bi bi-chevron-left"></i>
            </button>
          </li>
          <li
            v-for="page in pageRange"
            :key="page"
            class="page-item"
            :class="{ active: currentPage === page }"
          >
            <button class="page-link" @click="currentPage = page">{{ page }}</button>
          </li>
          <li class="page-item" :class="{ disabled: currentPage === totalPages }">
            <button class="page-link" @click="currentPage++">
              <i class="bi bi-chevron-right"></i>
            </button>
          </li>
          <li class="page-item" :class="{ disabled: currentPage === totalPages }">
            <button class="page-link" @click="currentPage = totalPages">
              <i class="bi bi-chevron-double-right"></i>
            </button>
          </li>
        </ul>
      </nav>
    </div>
    <div v-else-if="!isLoading" class="text-center text-muted">
      Không có dịch vụ nào để hiển thị.
    </div>

    <!-- Modal thêm/sửa -->
    <div
      v-if="isModalOpen"
      class="modal fade show d-block"
      tabindex="-1"
      style="background-color: rgba(0, 0, 0, 0.5)"
    >
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ currentService ? 'Cập nhật Dịch Vụ' : 'Thêm Dịch Vụ Mới' }}</h5>
            <button type="button" class="btn-close" @click="closeModal"></button>
          </div>
          <div class="modal-body">
            <div v-if="isLoading" class="text-center mb-3">
              <div class="spinner-border text-primary">
                <span class="visually-hidden">Đang tải...</span>
              </div>
            </div>
            <div v-if="modalErrorMessage" class="alert alert-warning">{{ modalErrorMessage }}</div>
            <form @submit.prevent="saveService">
              <div class="mb-3">
                <label class="form-label">Tên Dịch Vụ</label>
                <input
                  type="text"
                  v-model.trim="form.service_name"
                  @input="onInputServiceName"
                  class="form-control"
                  required
                  placeholder="Nhập tên dịch vụ"
                  :class="{ 'is-invalid': errors.service_name }"
                />
                <div v-if="errors.service_name" class="invalid-feedback">{{ errors.service_name }}</div>
              </div>
              <div class="mb-3">
                <label class="form-label">Giá (VNĐ)</label>
                <input
                  type="number"
                  v-model.number="form.price"
                  @input="onInputPrice"
                  class="form-control"
                  required
                  min="0"
                  placeholder="Nhập giá dịch vụ"
                  :class="{ 'is-invalid': errors.price }"
                />
                <div v-if="errors.price" class="invalid-feedback">{{ errors.price }}</div>
              </div>
              <div class="mb-3">
                <label class="form-label">Mô Tả</label>
                <textarea
                  v-model="form.description"
                  @input="onInputDescription"
                  class="form-control"
                  rows="3"
                  placeholder="Nhập mô tả dịch vụ"
                ></textarea>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" @click="closeModal" :disabled="isLoading">Hủy</button>
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
import { reactive, computed, watch } from "vue";
import axios from "axios";

const apiClient = axios.create({
  baseURL: "http://localhost:8000/api",
  timeout: 10000,
  headers: { "Content-Type": "application/json", "Accept": "application/json" }
});

export default {
  setup() {
    const state = reactive({
      services: [],
      searchQuery: "",
      isModalOpen: false,
      isLoading: false,
      currentService: null,
      successMessage: "",
      errorMessage: "",
      modalErrorMessage: "",
      currentPage: 1,
      itemsPerPage: 10,
      totalItems: 0,
      form: {
        service_name: "",
        price: 0,
        description: ""
      },
      errors: {
        service_name: "",
        price: ""
      }
    });

    // Format price
    const formatPrice = (price) => {
      return price ? new Intl.NumberFormat("vi-VN", { style: "currency", currency: "VND" }).format(price) : "0 ₫";
    };

    // Fetch services with pagination
    const fetchServices = async (page = 1) => {
      state.isLoading = true;
      state.errorMessage = "";
      try {
        const response = await apiClient.get(`/services?page=${page}&per_page=${state.itemsPerPage}`);
        console.log("Services API response:", JSON.stringify(response.data, null, 2));
        state.services = Array.isArray(response.data.data) ? response.data.data : [];
        state.totalItems = response.data.total || 0;
        state.currentPage = response.data.current_page || 1;
      } catch (error) {
        console.error("Fetch services error:", {
          message: error.message,
          response: error.response?.data,
          status: error.response?.status
        });
        state.errorMessage = error.response?.data?.message || "Không thể tải danh sách dịch vụ.";
        state.services = [];
        state.totalItems = 0;
      } finally {
        state.isLoading = false;
      }
    };

    fetchServices();

    // Watch for page changes
    watch(() => state.currentPage, (newPage) => {
      fetchServices(newPage);
    });

    // Filter services
    const filteredServices = computed(() => {
      if (!Array.isArray(state.services)) return [];
      return state.services.filter(service => {
        const serviceName = service.service_name?.toLowerCase() || "";
        const description = service.description?.toLowerCase() || "";
        return (
          serviceName.includes(state.searchQuery.toLowerCase()) ||
          description.includes(state.searchQuery.toLowerCase())
        );
      });
    });

    // Pagination
    const totalPages = computed(() => Math.ceil(state.totalItems / state.itemsPerPage));

    const displayedServices = computed(() => {
      return filteredServices.value;
    });

    const pageRange = computed(() => {
      const maxPages = 5;
      let start = Math.max(1, state.currentPage - Math.floor(maxPages / 2));
      let end = Math.min(totalPages.value, start + maxPages - 1);
      if (end - start + 1 < maxPages) {
        start = Math.max(1, end - maxPages + 1);
      }
      return Array.from({ length: end - start + 1 }, (_, i) => start + i);
    });

    // Modal actions
    const openAddModal = () => {
      state.form.service_name = "";
      state.form.price = 0;
      state.form.description = "";
      state.errors = {};
      state.currentService = null;
      state.isModalOpen = true;
      state.successMessage = "";
      state.modalErrorMessage = "";
      console.log("Opened add modal, form reset:", { ...state.form });
    };

    const openEditModal = (service) => {
      console.log("Opening edit modal for service:", JSON.stringify(service, null, 2));
      if (!service || typeof service !== 'object') {
        console.error("Invalid service data:", service);
        return;
      }
      state.form.service_name = String(service.service_name || "");
      state.form.price = Number(service.price) || 0;
      state.form.description = String(service.description || "");
      state.currentService = { ...service };
      state.isModalOpen = true;
      state.errors = {};
      state.successMessage = "";
      state.modalErrorMessage = "";
      console.log("Edit modal opened, form set:", { ...state.form });
    };

    const closeModal = () => {
      state.isModalOpen = false;
      state.errors = {};
      state.modalErrorMessage = "";
      state.currentService = null;
      state.form.service_name = "";
      state.form.price = 0;
      state.form.description = "";
      console.log("Closed modal, form reset:", { ...state.form });
    };

    // Handle inputs
    const onInputServiceName = (event) => {
      state.form.service_name = event.target.value;
      state.errors.service_name = "";
      state.modalErrorMessage = "";
      console.log("Service name input:", state.form.service_name);
    };

    const onInputPrice = (event) => {
      state.form.price = Number(event.target.value) || 0;
      state.errors.price = "";
      state.modalErrorMessage = "";
      console.log("Price input:", state.form.price);
    };

    const onInputDescription = (event) => {
      state.form.description = event.target.value;
      console.log("Description input:", state.form.description);
    };

    // Form validation
    const validateForm = () => {
      state.errors = {};
      let isValid = true;

      if (!state.form.service_name || state.form.service_name.trim().length === 0) {
        state.errors.service_name = "Vui lòng nhập tên dịch vụ";
        isValid = false;
      }
      if (state.form.price === null || state.form.price < 0 || isNaN(state.form.price)) {
        state.errors.price = "Giá phải là số không âm";
        isValid = false;
      }

      console.log("Validation result:", { isValid, form: { ...state.form }, errors: state.errors });
      return isValid;
    };

    // Save service
    const saveService = async () => {
      console.log("Form data before validation:", { ...state.form });
      if (!validateForm()) {
        console.log("Validation failed, stopping save.");
        state.modalErrorMessage = "Vui lòng kiểm tra thông tin nhập.";
        return;
      }

      state.isLoading = true;
      state.modalErrorMessage = "";
      const payload = {
        service_name: state.form.service_name.trim(),
        price: state.form.price,
        description: state.form.description.trim() || null
      };
      console.log("Sending POST/PUT data:", payload);
      try {
        let response;
        if (state.currentService) {
          response = await apiClient.put(`/services/${state.currentService.service_id}`, payload);
          console.log("PUT response:", JSON.stringify(response.data, null, 2));
          const index = state.services.findIndex(s => s.service_id === state.currentService.service_id);
          if (index !== -1) {
            state.services[index] = response.data.data;
          }
          state.successMessage = "Cập nhật dịch vụ thành công!";
        } else {
          response = await apiClient.post("/services", payload);
          console.log("POST response:", JSON.stringify(response.data, null, 2));
          state.services.push(response.data.data);
          state.successMessage = "Thêm dịch vụ thành công!";
          fetchServices(state.currentPage);
        }
        closeModal();
      } catch (error) {
        console.error("Save service error:", {
          message: error.message,
          response: error.response?.data,
          status: error.response?.status
        });
        state.modalErrorMessage = error.response?.data?.message || "Lưu dịch vụ thất bại.";
        if (error.response?.status === 422) {
          const backendErrors = error.response.data?.errors || {};
          state.errors = { ...backendErrors };
          state.modalErrorMessage += ": " + Object.values(backendErrors).flat().join(", ");
        }
      } finally {
        state.isLoading = false;
      }
    };

    // Delete service
    const deleteService = async (service_id) => {
      if (!confirm("Bạn có chắc chắn muốn xóa dịch vụ này?")) return;

      state.isLoading = true;
      state.errorMessage = "";
      try {
        await apiClient.delete(`/services/${service_id}`);
        state.services = state.services.filter(s => s.service_id !== service_id);
        if (displayedServices.value.length === 0 && state.currentPage > 1) {
          state.currentPage--;
        }
        state.successMessage = "Xóa dịch vụ thành công!";
        fetchServices(state.currentPage);
      } catch (error) {
        console.error("Delete service error:", error);
        state.errorMessage = error.response?.data?.message || "Xóa dịch vụ thất bại.";
      } finally {
        state.isLoading = false;
      }
    };

    return {
      services: computed(() => state.services),
      searchQuery: computed({
        get: () => state.searchQuery,
        set: (value) => { state.searchQuery = value; }
      }),
      isModalOpen: computed(() => state.isModalOpen),
      isLoading: computed(() => state.isLoading),
      currentService: computed(() => state.currentService),
      successMessage: computed(() => state.successMessage),
      errorMessage: computed(() => state.errorMessage),
      modalErrorMessage: computed(() => state.modalErrorMessage),
      currentPage: computed({
        get: () => state.currentPage,
        set: (value) => { state.currentPage = value; }
      }),
      itemsPerPage: state.itemsPerPage,
      totalItems: computed(() => state.totalItems),
      form: computed(() => state.form),
      errors: computed(() => state.errors),
      filteredServices,
      displayedServices,
      totalPages,
      pageRange,
      formatPrice,
      openAddModal,
      openEditModal,
      closeModal,
      onInputServiceName,
      onInputPrice,
      onInputDescription,
      saveService,
      deleteService
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
.table-responsive {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}
.table {
  width: max-content;
  min-width: 100%;
  border-collapse: collapse;
}
.table th,
.table td {
  white-space: nowrap;
  padding: 8px 12px;
  vertical-align: middle;
}
.table th {
  background: #78c1f1;
  text-align: center;
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