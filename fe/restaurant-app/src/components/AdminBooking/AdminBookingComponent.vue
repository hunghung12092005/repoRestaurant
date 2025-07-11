<template>
  <div class="container mt-5">
    <!-- Tiêu đề trang -->
    <h1 class="text-heading-md font-semibold text-primary mb-4">Quản lý Booking Khách Sạn</h1>

    <!-- Hiển thị thông báo lỗi nếu có -->
    <div v-if="errorMessage" class="alert alert-danger" role="alert">
      {{ errorMessage }}
    </div>

    <!-- Bảng danh sách booking -->
    <div class="table-responsive shadow-sm rounded">
      <table class="table table-striped table-hover" v-if="bookings.length > 0">
        <thead class="bg-gray-50">
          <tr>
            <th scope="col">ID</th>
            <th scope="col">Khách hàng</th>
            <th scope="col">Số điện thoại</th>
            <th scope="col">Loại Booking</th>
            <th scope="col">Check-in</th>
            <th scope="col">Check-out</th>
            <th scope="col">Tổng phòng</th>
            <th scope="col">Tổng giá</th>
            <th scope="col">Trạng thái</th>
            <th scope="col">Hành động</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="booking in bookings" :key="booking.booking_id" class="table-row">
            <td>{{ booking.booking_id }}</td>
            <td>{{ booking.customer?.customer_name || 'N/A' }}</td>
            <td>{{ booking.customer?.customer_phone || 'N/A' }}</td>
            <td>{{ booking.booking_type || 'N/A' }}</td>
            <td>{{ formatDate(booking.check_in_date) }}</td>
            <td>{{ formatDate(booking.check_out_date) }}</td>
            <td>{{ booking.total_rooms || '0' }}</td>
            <td>{{ formatPrice(booking.total_price) }}</td>
            <td>
              <span :class="getStatusClass(booking.status)">
                {{ formatStatus(booking.status) }}
              </span>
            </td>
            <td>
              <!-- Nút xem chi tiết cho tất cả trạng thái -->
              <button
                @click="openDetailModal(booking)"
                class="btn btn-primary btn-sm"
              >
                Xem chi tiết
              </button>
            </td>
          </tr>
        </tbody>
      </table>
      <div v-else class="alert alert-info text-center" role="alert">
        Chưa có booking
      </div>
    </div>

    <!-- Modal chi tiết booking -->
    <div v-if="showModal" class="modal fade show" style="display: block;" tabindex="-1" role="dialog">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title text-primary">Chi tiết Booking #{{ selectedBooking.booking_id }}</h5>
            <button type="button" @click="closeModal" class="btn-close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true"></span>
            </button>
          </div>
          <div class="modal-body">
            <!-- Hiển thị loading khi đang tải dữ liệu -->
            <div v-if="loading" class="text-center">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
            </div>
            <!-- Hiển thị thông tin chi tiết booking -->
            <div v-else-if="selectedBooking.booking_id" class="row mb-4">
              <div class="col-md-6">
                <p><strong class="text-dark">Khách hàng:</strong> {{ selectedBooking.customer?.customer_name || 'N/A' }}</p>
                <p><strong class="text-dark">Số điện thoại:</strong> {{ selectedBooking.customer?.customer_phone || 'N/A' }}</p>
                <p><strong class="text-dark">Email:</strong> {{ selectedBooking.customer?.customer_email || 'Không có' }}</p>
                <p><strong class="text-dark">Loại Booking:</strong> {{ selectedBooking.booking_type || 'N/A' }}</p>
              </div>
              <div class="col-md-6">
                <p><strong class="text-dark">Check-in:</strong> {{ formatDate(selectedBooking.check_in_date) }}</p>
                <p><strong class="text-dark">Check-out:</strong> {{ formatDate(selectedBooking.check_out_date) }}</p>
                <p><strong class="text-dark">Tổng phòng:</strong> {{ selectedBooking.total_rooms || '0' }}</p>
                <p><strong class="text-dark">Tổng giá:</strong> {{ formatPrice(selectedBooking.total_price) }}</p>
                <p><strong class="text-dark">Ghi chú:</strong> {{ selectedBooking.note || 'Không có' }}</p>
                <p><strong class="text-dark">Trạng thái:</strong>
                  <span :class="getStatusClass(selectedBooking.status)">
                    {{ formatStatus(selectedBooking.status) }}
                  </span>
                </p>
              </div>
            </div>

            <!-- Bảng chi tiết phòng -->
            <h4 v-if="!loading && selectedBooking.booking_id" class="text-primary mb-3">Chi tiết phòng</h4>
            <div v-if="!loading && selectedBooking.booking_id" class="table-responsive mb-4">
              <table class="table table-striped">
                <thead class="thead-dark">
                  <tr>
                    <th>ID</th>
                    <th>Loại phòng đã đặt</th>
                    <th>Phòng đã xếp</th>
                    <th>Tổng giá</th>
                    <th>Xếp phòng</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="detail in validBookingDetails" :key="detail.booking_detail_id">
                    <td>{{ detail.booking_detail_id }}</td>
                    <td>{{ detail.type_name || 'Loại phòng không xác định' }}</td>
                    <td>{{ detail.room?.room_name || 'Chưa xếp' }}</td>
                    <td>{{ formatPrice(detail.total_price) || '0' }}</td>
                    <td>
                      <!-- Dropdown chọn phòng nếu chưa gán và booking là pending_confirmation -->
                      <select
                        v-if="!detail.room_id && availableRooms[detail.booking_detail_id]?.length > 0 && selectedBooking.status === 'pending_confirmation'"
                        v-model="detail.room_id"
                        @change="assignRoom(detail)"
                        class="form-control form-control-sm"
                        :disabled="detail.room_id"
                      >
                        <option value="">Chọn phòng</option>
                        <option
                          v-for="room in availableRooms[detail.booking_detail_id]"
                          :key="room.room_id"
                          :value="room.room_id"
                          :disabled="room.status === 'occupied'"
                        >
                          {{ room.room_name }} ({{ room.type_name || 'N/A' }})
                        </option>
                      </select>
                      <span v-else-if="detail.room_id">Đã xếp: {{ detail.room?.room_name || 'Phòng không xác định' }}</span>
                      <span v-else-if="!detail.room_id && (selectedBooking.status === 'confirmed' || selectedBooking.status === 'completed')">Chưa xếp (Booking {{ formatStatus(selectedBooking.status) }})</span>
                      <span v-else>Không có phòng trống</span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div v-if="!loading && selectedBooking.booking_id && !validBookingDetails.length" class="alert alert-warning text-center">
              Không có chi tiết phòng để hiển thị.
            </div>
          </div>
          <!-- Nút xác nhận và đóng modal -->
          <div v-if="!loading && selectedBooking.booking_id" class="modal-footer">
            <button
              v-if="selectedBooking.status === 'pending_confirmation'"
              @click="confirmBooking"
              class="btn btn-success"
              :disabled="hasUnassignedRooms"
            >
              Xác nhận Booking
            </button>
            <button @click="closeModal" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
          </div>
        </div>
      </div>
    </div>
    <div v-if="showModal" class="modal-backdrop fade show"></div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';

// Trạng thái (state)
const bookings = ref([]); // Danh sách booking
const showModal = ref(false); // Hiển thị modal
const selectedBooking = ref({}); // Booking được chọn
const bookingDetails = ref([]); // Chi tiết booking
const availableRooms = ref({}); // Danh sách phòng trống
const errorMessage = ref(''); // Thông báo lỗi
const loading = ref(false); // Trạng thái tải dữ liệu

// Tính toán danh sách chi tiết booking hợp lệ
const validBookingDetails = computed(() => {
  return bookingDetails.value.filter(detail => detail && detail.booking_detail_id);
});

// Kiểm tra xem có phòng nào chưa được gán hay không
const hasUnassignedRooms = computed(() => {
  return bookingDetails.value.some(detail => !detail.room_id);
});

// Lấy danh sách booking từ API
const fetchBookings = async () => {
  try {
    // Lấy booking pending_confirmation, confirmed và completed
    const response = await axios.get('/api/bookings?status[]=pending_confirmation&status[]=confirmed&status[]=completed');
    bookings.value = Array.isArray(response.data) ? response.data : [];
    errorMessage.value = '';
    console.log('Bookings fetched:', bookings.value);
  } catch (error) {
    console.error('Lỗi khi lấy danh sách booking:', error);
    errorMessage.value = `Lỗi khi lấy danh sách booking: ${error.response?.data?.error || error.message}`;
  }
};

// Mở modal chi tiết booking
const openDetailModal = async (booking) => {
  selectedBooking.value = booking;
  loading.value = true;
  try {
    // Lấy chi tiết booking từ API
    const detailsResponse = await axios.get(`/api/booking-details/${booking.booking_id}`);
    bookingDetails.value = Array.isArray(detailsResponse.data)
      ? detailsResponse.data.map(detail => ({
          ...detail,
          room: detail.room || { room_id: null, room_name: 'Chưa xếp', status: 'N/A' },
          type_name: detail.roomType?.type_name || 'Loại phòng không xác định', // Lấy tên loại phòng từ roomType
          room_type: detail.room_type // Đảm bảo room_type là type_id (số nguyên)
        }))
      : [];
    console.log('Booking details fetched:', bookingDetails.value);

    // Lấy danh sách phòng trống cho booking pending_confirmation
    if (booking.status === 'pending_confirmation') {
      await Promise.all(
        bookingDetails.value.map(async (detail) => {
          if (!detail.room_id && detail.room_type) {
            try {
              // Chuyển room_type thành số nguyên
              const roomTypeId = Number(detail.room_type);
              console.log('room_type:', detail.room_type, 'Converted to:', roomTypeId); // Debug
              if (isNaN(roomTypeId) || roomTypeId <= 0) {
                throw new Error(`room_type không hợp lệ: ${detail.room_type}`);
              }
              // Gửi yêu cầu lấy phòng trống
              const roomsResponse = await axios.post('/api/available-rooms', {
                room_type: roomTypeId
              }, {
                headers: { 'Content-Type': 'application/json' }
              });
              availableRooms.value[detail.booking_detail_id] = Array.isArray(roomsResponse.data)
                ? roomsResponse.data
                : [];
              console.log(`Available rooms for booking_detail_id ${detail.booking_detail_id}:`, availableRooms.value[detail.booking_detail_id]);
            } catch (error) {
              console.error(`Lỗi khi lấy phòng trống cho room_type ${detail.room_type}:`, error.response?.data || error.message);
              availableRooms.value[detail.booking_detail_id] = [];
              errorMessage.value = `Lỗi khi lấy phòng trống: ${error.response?.data?.message || error.message}`;
            }
          }
        })
      );
    }

    // Kiểm tra nếu không có phòng trống
    errorMessage.value = Object.values(availableRooms.value).every(arr => arr.length === 0) && booking.status === 'pending_confirmation'
      ? 'Không có phòng trống phù hợp với loại phòng.'
      : '';
  } catch (error) {
    console.error('Lỗi khi lấy chi tiết booking:', error);
    errorMessage.value = `Lỗi khi lấy chi tiết booking: ${error.response?.data?.error || error.message}`;
  } finally {
    loading.value = false;
    showModal.value = true;
  }
};

// Gán phòng cho chi tiết booking
const assignRoom = async (detail) => {
  if (!detail || !detail.booking_detail_id || !detail.room_id) {
    errorMessage.value = 'Chi tiết booking hoặc phòng không hợp lệ';
    return;
  }
  try {
    await axios.post(`/api/assign-room/${detail.booking_detail_id}`, {
      room_id: detail.room_id
    });
    alert('Xếp phòng thành công!');
    // Kiểm tra nếu tất cả phòng đã được gán, tự động xác nhận booking
    await openDetailModal(selectedBooking.value); // Tải lại chi tiết booking
    if (!hasUnassignedRooms.value) {
      await confirmBooking(); // Tự động xác nhận nếu không còn phòng chưa gán
    }
    errorMessage.value = '';
  } catch (error) {
    console.error('Lỗi khi xếp phòng:', error);
    errorMessage.value = `Lỗi khi xếp phòng: ${error.response?.data?.error || error.message}`;
  }
};

// Xác nhận booking
const confirmBooking = async () => {
  try {
    await axios.patch(`/api/bookings/${selectedBooking.value.booking_id}`, {});
    alert('Xác nhận booking thành công!');
    showModal.value = false;
    await fetchBookings(); // Tải lại danh sách booking
    errorMessage.value = '';
  } catch (error) {
    console.error('Lỗi khi xác nhận booking:', error);
    errorMessage.value = `Lỗi khi xác nhận booking: ${error.response?.data?.error || error.message}`;
  }
};

// Đóng modal
const closeModal = () => {
  showModal.value = false;
  selectedBooking.value = {};
  bookingDetails.value = [];
  availableRooms.value = {};
  errorMessage.value = '';
};

// Định dạng ngày
const formatDate = (date) => {
  return date ? new Date(date).toLocaleDateString('vi-VN') : 'N/A';
};

// Định dạng giá tiền
const formatPrice = (price) => {
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price || 0);
};

// Định dạng trạng thái
const formatStatus = (status) => {
  const statusMap = {
    pending_confirmation: 'Chờ xác nhận',
    confirmed: 'Đã xác nhận',
    completed: 'Hoàn thành',
    cancelled: 'Đã hủy'
  };
  return statusMap[status] || status;
};

// Lấy lớp CSS cho trạng thái
const getStatusClass = (status) => {
  return {
    'badge': true,
    'badge-warning': status === 'pending_confirmation',
    'badge-success': status === 'confirmed',
    'badge-info': status === 'completed',
    'badge-danger': status === 'cancelled'
  };
};

// Gọi fetchBookings khi component được mount
onMounted(() => {
  fetchBookings();
});
</script>

<style scoped>
.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  z-index: 1040;
}

.modal.fade.show {
  z-index: 1050;
}

.modal-dialog {
  transform: translate(0, 0) !important;
}

@media (max-width: 768px) {
  .table-responsive {
    overflow-x: auto;
  }
  .btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
  }
}

.badge {
  display: inline-block;
  padding: 0.25em 0.4em;
  font-size: 75%;
  font-weight: 700;
  line-height: 1;
  text-align: center;
  white-space: nowrap;
  vertical-align: baseline;
  border-radius: 0.25rem;
}
.badge-warning { background-color: #ffc107; color: #212529; }
.badge-success { background-color: #28a745; color: #fff; }
.badge-info { background-color: #17a2b8; color: #fff; }
.badge-danger { background-color: #dc3545; color: #fff; }
</style>