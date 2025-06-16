<template>
  <div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="fw-bold text-sea-green">Quản Lý Phòng</h1>
      <button class="btn btn-success shadow" @click="openAddModal">
        <i class="bi bi-plus-circle me-2"></i>Thêm Phòng
      </button>
    </div>
    <div v-if="successMessage" class="alert alert-success">{{ successMessage }}</div>
    <div v-if="errorMessage && !isLoading" class="alert alert-danger">{{ errorMessage }}</div>

    <!-- Tìm kiếm và lọc -->
    <div class="row mb-4 g-3">
      <div class="col-md-4">
        <input
          v-model="searchQuery"
          type="text"
          class="form-control"
          placeholder="Tìm số phòng, tầng, hoặc loại phòng..."
        />
      </div>
      <div class="col-md-3">
        <select v-model="filterRoomType" class="form-select">
          <option value="">Tất cả loại phòng</option>
          <option v-for="type in roomTypes" :key="type.type_id" :value="type.type_id">
            {{ type.type_name }}
          </option>
        </select>
      </div>
      <div class="col-md-3">
        <select v-model="filterStatus" class="form-select">
          <option value="">Tất cả trạng thái</option>
          <option value="Trống">Trống</option>
          <option value="Đã đặt">Đã đặt</option>
          <option value="Chờ xác nhận">Chờ xác nhận</option>
          <option value="Bảo trì">Bảo trì</option>
          <option value="Đang dọn dẹp">Đang dọn dẹp</option>
        </select>
      </div>
    </div>

    <!-- Danh sách phòng -->
    <div class="table-responsive">
      <table class="table table-bordered table-hover align-middle">
        <thead>
          <tr>
            <th>#</th>
            <th>Số Phòng</th>
            <th>Loại Phòng</th>
            <th>Giá/Đêm (VNĐ)</th>
            <th>Giá/Giờ (VNĐ)</th>
            <th>Số Giường</th>
            <th>Sức Chứa</th>
            <th>Tầng</th>
            <th>Trạng Thái</th>
            <th>Bảo Trì</th>
            <th>Tiện Ích</th>
            <th>Dịch Vụ</th>
            <th>Hành Động</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(room, index) in displayedRooms" :key="room.room_id">
            <td>{{ (currentPage - 1) * itemsPerPage + index + 1 }}</td>
            <td>{{ room.room_name }}</td>
            <td>{{ room.room_type ? room.room_type.type_name : 'N/A' }}</td>
            <td>{{ formatPrice(room.price_per_night) }}</td>
            <td>{{ formatPrice(room.hourly_price) }}</td>
            <td>{{ room.bed_count !== null ? room.bed_count : '0' }}</td>
            <td>{{ room.max_occupancy !== null ? room.max_occupancy : '0' }}</td>
            <td>{{ room.floor_number }}</td>
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
                {{ room.status }}
              </span>
            </td>
            <td>
              <span
                :class="{
                  'badge bg-success': room.maintenance_status === 'Hoạt động',
                  'badge bg-warning': room.maintenance_status === 'Đang bảo trì'
                }"
              >
                {{ room.maintenance_status }}
              </span>
            </td>
            <td>{{ room.amenity_names ? room.amenity_names.join(', ') : 'Không có' }}</td>
            <td>{{ room.service_names ? room.service_names.join(', ') : 'Không có' }}</td>
            <td>
              <button class="btn btn-primary btn-sm me-2" @click="openEditModal(room)">
                <i class="bi bi-pencil me-1"></i>Sửa
              </button>
              <button class="btn btn-danger btn-sm" @click="deleteRoom(room.room_id)">
                <i class="bi bi-trash me-1"></i>Xóa
              </button>
            </td>
          </tr>
          <tr v-if="displayedRooms.length === 0 && !isLoading">
            <td colspan="13" class="text-center text-muted">
              Không có phòng nào phù hợp
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Phân trang -->
    <div v-if="totalItems > 0" class="d-flex justify-content-between align-items-center mt-4">
      <div class="text-muted">
        Hiển thị {{ displayedRooms.length }} / {{ totalItems }} phòng
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

    <!-- Modal thêm/sửa phòng -->
    <div
      v-if="isModalOpen"
      class="modal fade show d-block"
      tabindex="-1"
      style="background-color: rgba(0, 0, 0, 0.5)"
    >
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ currentRoom ? 'Cập nhật Phòng' : 'Thêm Phòng Mới' }}</h5>
            <button type="button" class="btn-close" @click="closeModal"></button>
          </div>
          <div class="modal-body">
            <div v-if="isLoading" class="text-center mb-3">
              <div class="spinner-border text-primary">
                <span class="visually-hidden">Đang tải...</span>
              </div>
            </div>
            <div v-if="modalErrorMessage" class="alert alert-warning">{{ modalErrorMessage }}</div>
            <form @submit.prevent="saveRoom">
              <div class="mb-3">
                <label class="form-label">Số Phòng</label>
                <input
                  type="text"
                  v-model.trim="form.room_name"
                  @input="onInputRoomName"
                  class="form-control"
                  required
                  placeholder="Nhập số phòng (VD: 101)"
                  :class="{ 'is-invalid': errors.room_name }"
                />
                <div v-if="errors.room_name" class="invalid-feedback">{{ errors.room_name }}</div>
              </div>
              <div class="mb-3">
                <label class="form-label">Loại Phòng</label>
                <select
                  v-model="form.type_id"
                  @change="updatePricing"
                  class="form-control"
                  required
                  :class="{ 'is-invalid': errors.type_id }"
                >
                  <option value="">-- Chọn loại phòng --</option>
                  <option v-for="type in roomTypes" :key="type.type_id" :value="type.type_id">
                    {{ type.type_name }}
                  </option>
                </select>
                <div v-if="errors.type_id" class="invalid-feedback">{{ errors.type_id }}</div>
              </div>
              <div class="mb-3">
                <label class="form-label">Tầng</label>
                <input
                  type="number"
                  v-model.number="form.floor_number"
                  @input="onInputFloorNumber"
                  class="form-control"
                  required
                  min="1"
                  placeholder="Nhập số tầng"
                  :class="{ 'is-invalid': errors.floor_number }"
                />
                <div v-if="errors.floor_number" class="invalid-feedback">{{ errors.floor_number }}</div>
              </div>
              <div class="mb-3">
                <label class="form-label">Trạng Thái</label>
                <select
                  v-model="form.status"
                  class="form-control"
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
                <label class="form-label">Tình Trạng Bảo Trì</label>
                <select
                  v-model="form.maintenance_status"
                  class="form-control"
                  required
                  :class="{ 'is-invalid': errors.maintenance_status }"
                >
                  <option value="Hoạt động">Hoạt động</option>
                  <option value="Đang bảo trì">Đang bảo trì</option>
                </select>
                <div v-if="errors.maintenance_status" class="invalid-feedback">{{ errors.maintenance_status }}</div>
              </div>
              <div class="mb-3">
                <label class="form-label">Tiện Ích</label>
                <select
                  v-model="form.amenities"
                  multiple
                  class="form-control"
                  :class="{ 'is-invalid': errors.amenities }"
                >
                  <option v-for="amenity in amenities" :key="amenity.amenity_id" :value="amenity.amenity_id">
                    {{ amenity.amenity_name }}
                  </option>
                </select>
                <div v-if="errors.amenities" class="invalid-feedback">{{ errors.amenities }}</div>
              </div>
              <div class="mb-3">
                <label class="form-label">Dịch Vụ</label>
                <select
                  v-model="form.services"
                  multiple
                  class="form-control"
                  :class="{ 'is-invalid': errors.services }"
                >
                  <option v-for="service in services" :key="service.service_id" :value="service.service_id">
                    {{ service.service_name }}
                  </option>
                </select>
                <div v-if="errors.services" class="invalid-feedback">{{ errors.services }}</div>
              </div>
              <div class="mb-3">
                <label class="form-label">Giá Theo Loại Phòng</label>
                <div class="row">
                  <div class="col">
                    <label>Giá/Đêm</label>
                    <input
                      type="text"
                      class="form-control"
                      :value="formatPrice(selectedPricing.data ? selectedPricing.data.price_per_night : 0)"
                      readonly
                    />
                  </div>
                  <div class="col">
                    <label>Giá/Giờ</label>
                    <input
                      type="text"
                      class="form-control"
                      :value="formatPrice(selectedPricing.data ? selectedPricing.data.hourly_price : 0)"
                      readonly
                    />
                  </div>
                </div>
              </div>
              <div class="mb-3">
                <label class="form-label">Mô Tả</label>
                <textarea
                  v-model="form.description"
                  @input="onInputDescription"
                  class="form-control"
                  rows="3"
                  placeholder="Nhập mô tả phòng"
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
      rooms: [],
      roomTypes: [],
      amenities: [],
      services: [],
      searchQuery: "",
      filterRoomType: "",
      filterStatus: "",
      isModalOpen: false,
      isLoading: false,
      currentRoom: null,
      successMessage: "",
      errorMessage: "",
      modalErrorMessage: "",
      currentPage: 1,
      itemsPerPage: 10,
      totalItems: 0,
      form: {
        room_name: "",
        type_id: "",
        floor_number: 1,
        status: "Trống",
        maintenance_status: "Hoạt động",
        description: "",
        amenities: [],
        services: []
      },
      errors: {
        room_name: "",
        type_id: "",
        floor_number: "",
        status: "",
        maintenance_status: "",
        amenities: "",
        services: ""
      },
      selectedPricing: { data: null }
    });

    // Format price
    const formatPrice = (price) => {
      return price ? new Intl.NumberFormat("vi-VN", { style: "currency", currency: "VND" }).format(price) : "0 ₫";
    };

    // Fetch data
    const fetchRooms = async (page = 1) => {
      state.isLoading = true;
      state.errorMessage = "";
      try {
        const response = await apiClient.get(`/rooms?page=${page}&per_page=${state.itemsPerPage}`);
        console.log("Rooms API response:", JSON.stringify(response.data, null, 2));
        state.rooms = Array.isArray(response.data.data) ? response.data.data : [];
        state.totalItems = response.data.total || 0;
        state.currentPage = response.data.current_page || 1;
      } catch (error) {
        console.error("Fetch rooms error:", {
          message: error.message,
          response: error.response?.data,
          status: error.response?.status
        });
        state.errorMessage = error.response?.data?.message || "Không thể tải danh sách phòng.";
        state.rooms = [];
        state.totalItems = 0;
      } finally {
        state.isLoading = false;
      }
    };

    const fetchRoomTypes = async () => {
      try {
        const response = await apiClient.get("/room-types");
        state.roomTypes = Array.isArray(response.data.data) ? response.data.data : [];
      } catch (error) {
        state.errorMessage = error.response?.data?.message || "Không thể tải danh sách loại phòng.";
      }
    };

    const fetchAmenities = async () => {
      try {
        const response = await apiClient.get("/amenities");
        state.amenities = Array.isArray(response.data.data) ? response.data.data : [];
      } catch (error) {
        state.errorMessage = error.response?.data?.message || "Không thể tải danh sách tiện ích.";
      }
    };

    const fetchServices = async () => {
      try {
        const response = await apiClient.get("/services");
        state.services = Array.isArray(response.data.data) ? response.data.data : [];
      } catch (error) {
        state.errorMessage = error.response?.data?.message || "Không thể tải danh sách dịch vụ.";
      }
    };

    const updatePricing = async () => {
      if (!state.form.type_id) {
        state.selectedPricing.data = null;
        return;
      }
      try {
        const response = await apiClient.get(`/pricese/current/${state.form.type_id}`);
        state.selectedPricing.data = response.data.data || null;
      } catch (error) {
        state.selectedPricing.data = null;
        state.errorMessage = error.response?.data?.message || "Không thể tải giá phòng.";
      }
    };

    // Initialize data
    fetchRooms();
    fetchRoomTypes();
    fetchAmenities();
    fetchServices();

    // Watch for page changes
    watch(() => state.currentPage, (newPage) => {
      fetchRooms(newPage);
    });

    // Watch for type_id changes
    watch(() => state.form.type_id, () => {
      updatePricing();
    });

    // Filter rooms
    const filteredRooms = computed(() => {
      if (!Array.isArray(state.rooms)) return [];
      return state.rooms.filter(room => {
        const roomName = room.room_name?.toLowerCase() || "";
        const typeName = room.room_type?.type_name?.toLowerCase() || "";
        const floorNumber = room.floor_number?.toString() || "";
        const amenityNames = room.amenity_names?.join(" ").toLowerCase() || "";
        const serviceNames = room.service_names?.join(" ").toLowerCase() || "";
        const matchesSearch =
          roomName.includes(state.searchQuery.toLowerCase()) ||
          typeName.includes(state.searchQuery.toLowerCase()) ||
          floorNumber.includes(state.searchQuery.toLowerCase()) ||
          amenityNames.includes(state.searchQuery.toLowerCase()) ||
          serviceNames.includes(state.searchQuery.toLowerCase());
        const matchesRoomType = !state.filterRoomType || room.type_id === parseInt(state.filterRoomType);
        const matchesStatus = !state.filterStatus || room.status === state.filterStatus;
        return matchesSearch && matchesRoomType && matchesStatus;
      });
    });

    // Pagination
    const totalPages = computed(() => Math.ceil(state.totalItems / state.itemsPerPage));

    const displayedRooms = computed(() => {
      return filteredRooms.value;
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
      state.form.room_name = "";
      state.form.type_id = "";
      state.form.floor_number = 1;
      state.form.status = "Trống";
      state.form.maintenance_status = "Hoạt động";
      state.form.description = "";
      state.form.amenities = [];
      state.form.services = [];
      state.errors = {};
      state.currentRoom = null;
      state.selectedPricing.data = null;
      state.isModalOpen = true;
      state.successMessage = "";
      state.modalErrorMessage = "";
      console.log("Opened add modal, form reset:", { ...state.form });
    };

    const openEditModal = (room) => {
      console.log("Opening edit modal for room:", JSON.stringify(room, null, 2));
      if (!room || typeof room !== 'object') {
        console.error("Invalid room data:", room);
        return;
      }
      state.form.room_name = String(room.room_name || "");
      state.form.type_id = String(room.type_id || "");
      state.form.floor_number = Number(room.floor_number) || 1;
      state.form.status = room.status || "Trống";
      state.form.maintenance_status = room.maintenance_status || "Hoạt động";
      state.form.description = String(room.description || "");
      state.form.amenities = room.amenities ? room.amenities.map(a => a.amenity_id) : [];
      state.form.services = room.services ? room.services.map(s => s.service_id) : [];
      state.currentRoom = { ...room };
      updatePricing();
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
      state.currentRoom = null;
      state.selectedPricing.data = null;
      state.form.room_name = "";
      state.form.type_id = "";
      state.form.floor_number = 1;
      state.form.status = "Trống";
      state.form.maintenance_status = "Hoạt động";
      state.form.description = "";
      state.form.amenities = [];
      state.form.services = [];
      console.log("Closed modal, form reset:", { ...state.form });
    };

    // Handle inputs
    const onInputRoomName = (event) => {
      state.form.room_name = event.target.value;
      state.errors.room_name = "";
      state.modalErrorMessage = "";
      console.log("Room name input:", state.form.room_name);
    };

    const onInputFloorNumber = (event) => {
      state.form.floor_number = Number(event.target.value) || 1;
      state.errors.floor_number = "";
      state.modalErrorMessage = "";
      console.log("Floor number input:", state.form.floor_number);
    };

    const onInputDescription = (event) => {
      state.form.description = event.target.value;
      console.log("Description input:", state.form.description);
    };

    // Form validation
    const validateForm = () => {
      state.errors = {};
      let isValid = true;

      if (!state.form.room_name || state.form.room_name.trim().length === 0) {
        state.errors.room_name = "Vui lòng nhập số phòng";
        isValid = false;
      }
      if (!state.form.type_id) {
        state.errors.type_id = "Vui lòng chọn loại phòng";
        isValid = false;
      }
      if (!state.form.floor_number || state.form.floor_number < 1) {
        state.errors.floor_number = "Tầng phải lớn hơn 0";
        isValid = false;
      }
      if (!["Trống", "Đã đặt", "Chờ xác nhận", "Bảo trì", "Đang dọn dẹp"].includes(state.form.status)) {
        state.errors.status = "Vui lòng chọn trạng thái hợp lệ";
        isValid = false;
      }
      if (!["Hoạt động", "Đang bảo trì"].includes(state.form.maintenance_status)) {
        state.errors.maintenance_status = "Vui lòng chọn trạng thái bảo trì hợp lệ";
        isValid = false;
      }

      console.log("Validation result:", { isValid, form: { ...state.form }, errors: state.errors });
      return isValid;
    };

    // Save room
    const saveRoom = async () => {
      console.log("Form data before validation:", { ...state.form });
      if (!validateForm()) {
        console.log("Validation failed, stopping save.");
        state.modalErrorMessage = "Vui lòng kiểm tra thông tin nhập.";
        return;
      }

      state.isLoading = true;
      state.modalErrorMessage = "";
      const payload = {
        room_name: state.form.room_name.trim(),
        type_id: state.form.type_id,
        floor_number: state.form.floor_number,
        status: state.form.status,
        maintenance_status: state.form.maintenance_status,
        description: state.form.description.trim() || null,
        amenities: state.form.amenities,
        services: state.form.services
      };
      console.log("Sending POST/PUT data:", payload);
      try {
        let response;
        if (state.currentRoom) {
          response = await apiClient.put(`/rooms/${state.currentRoom.room_id}`, payload);
          console.log("PUT response:", JSON.stringify(response.data, null, 2));
          const index = state.rooms.findIndex(r => r.room_id === state.currentRoom.room_id);
          if (index !== -1) {
            state.rooms[index] = { ...response.data.data };
          }
          state.successMessage = "Cập nhật phòng thành công!";
        } else {
          response = await apiClient.post("/rooms", payload);
          console.log("POST response:", JSON.stringify(response.data, null, 2));
          state.rooms.push(response.data.data);
          state.successMessage = "Thêm phòng thành công!";
          fetchRooms(state.currentPage);
        }
        closeModal();
      } catch (error) {
        console.error("Save room error:", {
          message: error.message,
          response: error.response?.data,
          status: error.response?.status
        });
        state.modalErrorMessage = error.response?.data?.message || "Lưu phòng thất bại.";
        if (error.response?.status === 422) {
          const backendErrors = error.response.data?.errors || {};
          state.errors = { ...backendErrors };
          state.modalErrorMessage += ": " + Object.values(backendErrors).flat().join(", ");
        }
      } finally {
        state.isLoading = false;
      }
    };

    // Delete room
    const deleteRoom = async (room_id) => {
      if (!confirm("Bạn có chắc chắn muốn xóa phòng này?")) return;

      state.isLoading = true;
      state.errorMessage = "";
      try {
        await apiClient.delete(`/rooms/${room_id}`);
        state.rooms = state.rooms.filter(r => r.room_id !== room_id);
        if (displayedRooms.value.length === 0 && state.currentPage > 1) {
          state.currentPage--;
        }
        state.successMessage = "Xóa phòng thành công!";
        fetchRooms(state.currentPage);
      } catch (error) {
        console.error("Delete room error:", error);
        state.errorMessage = error.response?.data?.message || "Xóa phòng thất bại.";
      } finally {
        state.isLoading = false;
      }
    };

    return {
      rooms: computed(() => state.rooms),
      roomTypes: computed(() => state.roomTypes),
      amenities: computed(() => state.amenities),
      services: computed(() => state.services),
      searchQuery: computed({
        get: () => state.searchQuery,
        set: (value) => { state.searchQuery = value; }
      }),
      filterRoomType: computed({
        get: () => state.filterRoomType,
        set: (value) => { state.filterRoomType = value; }
      }),
      filterStatus: computed({
        get: () => state.filterStatus,
        set: (value) => { state.filterStatus = value; }
      }),
      isModalOpen: computed(() => state.isModalOpen),
      isLoading: computed(() => state.isLoading),
      currentRoom: computed(() => state.currentRoom),
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
      selectedPricing: computed(() => state.selectedPricing),
      filteredRooms,
      displayedRooms,
      totalPages,
      pageRange,
      formatPrice,
      openAddModal,
      openEditModal,
      closeModal,
      onInputRoomName,
      onInputFloorNumber,
      onInputDescription,
      saveRoom,
      deleteRoom,
      updatePricing
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
  font-weight: 600;
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
.badge {
  font-size: 0.9rem;
  padding: 0.5em 0.75em;
}
</style>