<template>
  <div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="fw-bold text-sea-green">Quản Lý Tiện Nghi</h1>
      <button class="btn btn-success shadow" @click="openAddModal">
        <i class="bi bi-plus-circle me-2"></i>Thêm Tiện Nghi
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
          placeholder="Tìm tên tiện nghi..."
        />
      </div>
    </div>

    <!-- Danh sách tiện nghi -->
    <div class="table-responsive">
      <table class="table table-bordered table-hover align-middle">
        <thead>
          <tr>
            <th>#</th>
            <th>Tên Tiện Nghi</th>
            <th>Mô Tả</th>
            <th>Hành Động</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(amenity, index) in displayedAmenities" :key="amenity.amenity_id">
            <td>{{ (currentPage - 1) * itemsPerPage + index + 1 }}</td>
            <td>{{ amenity.amenity_name }}</td>
            <td>{{ amenity.description || 'Không có' }}</td>
            <td>
              <button class="btn btn-primary btn-sm me-2" @click="openEditModal(amenity)">
                <i class="bi bi-pencil me-1"></i>Sửa
              </button>
              <button class="btn btn-danger btn-sm" @click="deleteAmenity(amenity.amenity_id)">
                <i class="bi bi-trash me-1"></i>Xóa
              </button>
            </td>
          </tr>
          <tr v-if="displayedAmenities.length === 0 && !isLoading">
            <td colspan="4" class="text-center text-muted">
              Không có tiện nghi nào để hiển thị
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Phân trang -->
    <div v-if="totalItems > 0" class="d-flex justify-content-between align-items-center mt-4">
      <div class="text-muted">
        Hiển thị {{ displayedAmenities.length }} / {{ totalItems }} tiện nghi
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
            <h5 class="modal-title">{{ currentAmenity ? 'Cập nhật Tiện Nghi' : 'Thêm Tiện Nghi Mới' }}</h5>
            <button type="button" class="btn-close" @click="closeModal"></button>
          </div>
          <div class="modal-body">
            <div v-if="isLoading" class="text-center mb-3">
              <div class="spinner-border text-primary">
                <span class="visually-hidden">Đang tải...</span>
              </div>
            </div>
            <div v-if="modalErrorMessage" class="alert alert-warning">{{ modalErrorMessage }}</div>
            <form @submit.prevent="saveAmenity">
              <div class="mb-3">
                <label class="form-label">Tên Tiện Nghi</label>
                <input
                  type="text"
                  v-model.trim="form.amenity_name"
                  class="form-control"
                  required
                  placeholder="Nhập tên tiện nghi"
                  @input="onInputAmenityName"
                  :class="{ 'is-invalid': errors.amenity_name }"
                />
                <div v-if="errors.amenity_name" class="invalid-feedback">{{ errors.amenity_name }}</div>
              </div>
              <div class="mb-3">
                <label class="form-label">Mô Tả</label>
                <textarea
                  v-model="form.description"
                  class="form-control"
                  rows="3"
                  placeholder="Nhập mô tả tiện nghi"
                  @input="onInputDescription"
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
      amenities: [],
      searchQuery: "",
      isModalOpen: false,
      isLoading: false,
      currentAmenity: null,
      successMessage: "",
      errorMessage: "",
      modalErrorMessage: "",
      currentPage: 1,
      itemsPerPage: 10,
      totalItems: 0,
      form: {
        amenity_name: "",
        description: ""
      },
      errors: {
        amenity_name: ""
      }
    });

    // Fetch amenities with pagination
    const fetchAmenities = async (page = 1) => {
      state.isLoading = true;
      state.errorMessage = "";
      try {
        const response = await apiClient.get(`/amenities?page=${page}&per_page=${state.itemsPerPage}`);
        console.log("Amenities API response:", JSON.stringify(response.data, null, 2));
        state.amenities = Array.isArray(response.data.data) ? response.data.data : [];
        state.totalItems = response.data.total || 0;
        state.currentPage = response.data.current_page || 1;
      } catch (error) {
        console.error("Fetch amenities error:", {
          message: error.message,
          response: error.response?.data,
          status: error.response?.status
        });
        state.errorMessage = error.response?.data?.message || "Không thể tải danh sách tiện nghi.";
        state.amenities = [];
        state.totalItems = 0;
      } finally {
        state.isLoading = false;
      }
    };

    fetchAmenities();

    // Watch for page changes
    watch(() => state.currentPage, (newPage) => {
      fetchAmenities(newPage);
    });

    // Filter amenities
    const filteredAmenities = computed(() => {
      if (!Array.isArray(state.amenities)) return [];
      return state.amenities.filter(amenity => {
        const amenityName = amenity.amenity_name?.toLowerCase() || "";
        const description = amenity.description?.toLowerCase() || "";
        return (
          amenityName.includes(state.searchQuery.toLowerCase()) ||
          description.includes(state.searchQuery.toLowerCase())
        );
      });
    });

    // Pagination
    const totalPages = computed(() => Math.ceil(state.totalItems / state.itemsPerPage));

    const displayedAmenities = computed(() => {
      return filteredAmenities.value;
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
      state.form.amenity_name = "";
      state.form.description = "";
      state.errors = {};
      state.currentAmenity = null;
      state.isModalOpen = true;
      state.successMessage = "";
      state.modalErrorMessage = "";
      console.log("Opened add modal, form reset:", { ...state.form });
    };

    const openEditModal = (amenity) => {
      console.log("Opening edit modal for amenity:", JSON.stringify(amenity, null, 2));
      if (!amenity || typeof amenity !== 'object') {
        console.error("Invalid amenity data:", amenity);
        return;
      }
      state.form.amenity_name = String(amenity.amenity_name || "");
      state.form.description = String(amenity.description || "");
      state.currentAmenity = { ...amenity };
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
      state.currentAmenity = null;
      state.form.amenity_name = "";
      state.form.description = "";
      console.log("Closed modal, form reset:", { ...state.form });
    };

    // Handle input for amenity_name
    const onInputAmenityName = (event) => {
      state.form.amenity_name = event.target.value;
      state.errors.amenity_name = "";
      state.modalErrorMessage = "";
      console.log("Amenity name input:", state.form.amenity_name);
    };

    // Handle input for description
    const onInputDescription = (event) => {
      state.form.description = event.target.value;
      console.log("Description input:", state.form.description);
    };

    // Form validation
    const validateForm = () => {
      state.errors = {};
      let isValid = true;

      if (!state.form.amenity_name || state.form.amenity_name.trim().length === 0) {
        state.errors.amenity_name = "Vui lòng nhập tên tiện nghi";
        isValid = false;
      }

      console.log("Validation result:", {
        isValid,
        form: { ...state.form },
        errors: state.errors
      });
      return isValid;
    };

    // Save amenity
    const saveAmenity = async () => {
      console.log("Form data before validation:", { ...state.form });
      if (!validateForm()) {
        console.log("Validation failed, stopping save.");
        state.modalErrorMessage = "Vui lòng kiểm tra thông tin nhập.";
        return;
      }

      state.isLoading = true;
      state.modalErrorMessage = "";
      const payload = {
        amenity_name: state.form.amenity_name.trim(),
        description: state.form.description.trim() || null
      };
      console.log("Sending POST/PUT data:", payload);
      try {
        let response;
        if (state.currentAmenity) {
          response = await apiClient.put(`/amenities/${state.currentAmenity.amenity_id}`, payload);
          console.log("PUT response:", JSON.stringify(response.data, null, 2));
          const index = state.amenities.findIndex(s => s.amenity_id === state.currentAmenity.amenity_id);
          if (index !== -1) {
            state.amenities[index] = response.data.data;
          }
          state.successMessage = "Cập nhật tiện nghi thành công!";
        } else {
          response = await apiClient.post("/amenities", payload);
          console.log("POST response:", JSON.stringify(response.data, null, 2));
          state.amenities.push(response.data.data);
          state.successMessage = "Thêm tiện nghi thành công!";
          fetchAmenities(state.currentPage);
        }
        closeModal();
      } catch (error) {
        console.error("Save amenity error:", {
          message: error.message,
          response: error.response?.data,
          status: error.response?.status
        });
        const errorMsg = error.response?.data?.message || "Lưu tiện nghi thất bại.";
        state.modalErrorMessage = errorMsg;
        if (error.response?.status === 422) {
          const backendErrors = error.response.data?.errors || {};
          state.errors = { ...backendErrors };
          state.modalErrorMessage += ": " + Object.values(backendErrors).flat().join(", ");
        }
      } finally {
        state.isLoading = false;
      }
    };

    // Delete amenity
    const deleteAmenity = async (amenity_id) => {
      if (!confirm("Bạn có chắc chắn muốn xóa tiện nghi này?")) return;

      state.isLoading = true;
      state.errorMessage = "";
      try {
        await apiClient.delete(`/amenities/${amenity_id}`);
        state.amenities = state.amenities.filter(s => s.amenity_id !== amenity_id);
        if (displayedAmenities.value.length === 0 && state.currentPage > 1) {
          state.currentPage--;
        }
        state.successMessage = "Xóa tiện nghi thành công!";
        fetchAmenities(state.currentPage);
      } catch (error) {
        console.error("Delete amenity error:", error);
        state.errorMessage = error.response?.data?.message || "Xóa tiện nghi thất bại.";
      } finally {
        state.isLoading = false;
      }
    };

    return {
      amenities: computed(() => state.amenities),
      searchQuery: computed({
        get() {
          return state.searchQuery;
        },
        set(value) {
          state.searchQuery = value;
        }
      }),
      isModalOpen: computed(() => state.isModalOpen),
      isLoading: computed(() => state.isLoading),
      currentAmenity: computed(() => state.currentAmenity),
      successMessage: computed(() => state.successMessage),
      errorMessage: computed(() => state.errorMessage),
      modalErrorMessage: computed(() => state.modalErrorMessage),
      currentPage: computed({
        get() {
          return state.currentPage;
        },
        set(value) {
          state.currentPage = value;
        }
      }),
      itemsPerPage: state.itemsPerPage,
      totalItems: computed(() => state.totalItems),
      form: computed(() => state.form),
      errors: computed(() => state.errors),
      filteredAmenities,
      displayedAmenities,
      totalPages,
      pageRange,
      openAddModal,
      openEditModal,
      closeModal,
      onInputAmenityName,
      onInputDescription,
      saveAmenity,
      deleteAmenity
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