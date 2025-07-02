<template>
  <div class="container mt-5">
    <h1 class="text-3xl font-weight-bold text-primary mb-4">Quản lý Booking Khách Sạn</h1>
    
    <!-- Thông báo lỗi nếu có -->
    <div v-if="errorMessage" class="alert alert-danger" role="alert">
      {{ errorMessage }}
    </div>

    <!-- Bảng danh sách booking -->
    <div class="table-responsive shadow-sm rounded">
      <table class="table table-striped table-hover">
        <thead class="thead-dark">
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
          <tr v-for="booking in bookings" :key="booking.booking_id" class="table-active">
            <td>{{ booking.booking_id }}</td>
            <td>{{ booking.customer?.customer_name || 'N/A' }}</td>
            <td>{{ booking.customer?.customer_phone || 'N/A' }}</td>
            <td>{{ booking.booking_type || 'N/A' }}</td>
            <td>{{ formatDate(booking.check_in_date) }}</td>
            <td>{{ formatDate(booking.check_out_date) }}</td>
            <td>{{ booking.total_rooms || '0' }}</td>
            <td>{{ formatPrice(booking.total_price) }}</td>
            <td>
              <span :class="{
                'badge': true,
                'badge-warning': booking.status === 'pending_confirmation',
                'badge-success': booking.status === 'booked',
                'badge-danger': booking.status === 'cancelled',
                'badge-info': booking.status === 'available'
              }">
                {{ formatStatus(booking.status) }}
              </span>
            </td>
            <td>
              <button
                v-if="booking.status === 'pending_confirmation'"
                @click="openDetailModal(booking)"
                class="btn btn-primary btn-sm"
              >
                Xem chi tiết
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal chi tiết booking -->
    <div v-if="showModal" class="modal fade show" style="display: block;" tabindex="-1" role="dialog">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title text-primary">Chi tiết Booking #{{ selectedBooking.booking_id }}</h5>
            <button type="button" @click="closeModal" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">×</span>
            </button>
          </div>
          <div class="modal-body">
            <!-- Loading state -->
            <div v-if="loading" class="text-center">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
            </div>
            <!-- Thông tin chung -->
            <div v-else-if="selectedBooking.booking_id" class="row mb-4">
              <div class="col-md-6">
                <p><strong class="text-dark">Khách hàng:</strong> {{ selectedBooking.customer?.customer_name || 'N/A' }}</p>
                <p><strong class="text-dark">Số điện thoại:</strong> {{ selectedBooking.customer?.customer_phone || 'N/A' }}</p>
                <p><strong class="text-dark">Email:</strong> {{ selectedBooking.customer?.customer_email || 'Không có' }}</p>
                <p><strong class="text-dark">Loại Booking:</strong> {{ selectedBooking.booking_type || 'N/A' }}</p>
                <p><strong class="text-dark">Loại giá:</strong> {{ selectedBooking.pricing_type || 'N/A' }}</p>
              </div>
              <div class="col-md-6">
                <p><strong class="text-dark">Check-in:</strong> {{ formatDate(selectedBooking.check_in_date) }}</p>
                <p><strong class="text-dark">Check-out:</strong> {{ formatDate(selectedBooking.check_out_date) }}</p>
                <p><strong class="text-dark">Tổng phòng:</strong> {{ selectedBooking.total_rooms || '0' }}</p>
                <p><strong class="text-dark">Tổng giá:</strong> {{ formatPrice(selectedBooking.total_price) }}</p>
                <p><strong class="text-dark">Phí bổ sung:</strong> {{ formatPrice(selectedBooking.additional_fee || 0) }}</p>
                <p><strong class="text-dark">Trạng thái thanh toán:</strong> {{ selectedBooking.payment_status || 'N/A' }}</p>
                <p><strong class="text-dark">Ghi chú:</strong> {{ selectedBooking.note || 'Không có' }}</p>
              </div>
            </div>

            <!-- Danh sách chi tiết phòng -->
            <h4 v-if="!loading && selectedBooking.booking_id" class="text-primary mb-3">Chi tiết phòng</h4>
            <div v-if="!loading && selectedBooking.booking_id" class="table-responsive mb-4">
              <table class="table table-striped">
                <thead class="thead-dark">
                  <tr>
                    <th>ID</th>
                    <th>Loại phòng ban đầu</th>
                    <th>Phòng đã xếp</th>
                    <th>Tổng giá</th>
                    <th>Ghi chú</th>
                    <th>Xếp phòng</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="detail in validBookingDetails" :key="detail.booking_detail_id || 'no-id'">
                    <td>{{ detail.booking_detail_id || 'N/A' }}</td>
                    <td>{{ detail.initial_room_type || 'Chưa xác định' }}</td>
                    <td>{{ detail.room && detail.room.room_name ? detail.room.room_name : 'Chưa xếp' }}</td>
                    <td>{{ formatPrice(detail.total_price) || '0' }}</td>
                    <td>{{ detail.note || 'Không có' }}</td>
                    <td>
                      <select
                        v-if="detail && !detail.room_id && availableRooms.length > 0"
                        v-model="detail.room_id"
                        @change="assignRoom(detail)"
                        class="form-control form-control-sm"
                      >
                        <option value="">Chọn phòng</option>
                        <option
                          v-for="room in availableRooms"
                          :key="room.id || 'no-id'"
                          :value="room.id"
                          v-if="room.id && !bookedRooms.includes(room.id)"
                        >
                          {{ room.name }} ({{ room.room_type?.type_name || 'N/A' }})
                        </option>
                      </select>
                      <span v-else-if="detail.room_id">Đã xếp: {{ detail.room?.room_name || 'Phòng không xác định' }}</span>
                      <span v-else>Không có phòng trống để xếp</span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Danh sách dịch vụ -->
            <h4 v-if="!loading && selectedBooking.booking_id && bookingServices.length > 0" class="text-primary mb-3">Dịch vụ bổ sung</h4>
            <div v-if="!loading && selectedBooking.booking_id && bookingServices.length > 0" class="table-responsive mb-4">
              <table class="table table-striped">
                <thead class="thead-dark">
                  <tr>
                    <th>ID</th>
                    <th>Dịch vụ ID</th>
                    <th>Giá dịch vụ</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="service in bookingServices" :key="service.booking_service_id || 'no-id'">
                    <td>{{ service.booking_service_id || 'N/A' }}</td>
                    <td>{{ service.service_id || 'N/A' }}</td>
                    <td>{{ formatPrice(service.service_price) || '0' }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
            <p v-else-if="!loading && selectedBooking.booking_id" class="text-muted">Không có dịch vụ bổ sung.</p>
          </div>
          <div v-if="!loading && selectedBooking.booking_id" class="modal-footer">
            <button @click="confirmBooking" class="btn btn-success">Xác nhận Booking</button>
            <button @click="closeModal" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
          </div>
        </div>
      </div>
    </div>
    <!-- Overlay để mô phỏng modal -->
    <div v-if="showModal" class="modal-backdrop fade show"></div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';

// State
const bookings = ref([]);
const showModal = ref(false);
const selectedBooking = ref({});
const bookingDetails = ref([]);
const bookingServices = ref([]);
const availableRooms = ref([]);
const bookedRooms = ref([]);
const errorMessage = ref('');
const loading = ref(false);

// Computed properties
const validBookingDetails = computed(() => {
  return bookingDetails.value.filter(detail => detail && detail.booking_detail_id !== undefined && detail.booking_detail_id !== null);
});

// Methods
const fetchBookings = async () => {
  try {
    const response = await axios.get('/api/bookings?status=pending_confirmation');
    bookings.value = Array.isArray(response.data) ? response.data : [];
    console.log('Số lượng booking nhận được từ API:', bookings.value.length);
    console.log('Bookings received:', JSON.stringify(bookings.value, null, 2));
    errorMessage.value = '';
  } catch (error) {
    console.error('Lỗi khi lấy danh sách booking:', error);
    errorMessage.value = `Lỗi khi lấy danh sách booking: ${error.response?.status || 'Unknown'} - ${error.response?.data?.error || error.message}`;
  }
};

const openDetailModal = async (booking) => {
  selectedBooking.value = booking;
  loading.value = true;
  try {
    console.log('Opening detail modal for booking:', JSON.stringify(booking, null, 2));
    if (!booking.type_id) {
      errorMessage.value = 'Booking không có type_id hợp lệ, loại phòng sẽ hiển thị mặc định. Vui lòng kiểm tra dữ liệu.';
      bookingDetails.value = [];
      availableRooms.value = [];
    } else {
      // Lấy chi tiết booking
      const detailsResponse = await axios.get(`/api/booking-details/${booking.booking_id}`);
      bookingDetails.value = Array.isArray(detailsResponse.data)
        ? detailsResponse.data.map(d => ({
            ...d,
            room: d.room && d.room.room_id !== null
              ? d.room
              : { room_id: null, room_name: 'Chưa xếp', type_id: booking.type_id },
            initial_room_type: d.initial_room_type || (booking.roomType?.type_name || 'Chưa xác định')
          }))
        : [];
      console.log('Booking details received:', JSON.stringify(bookingDetails.value, null, 2));

      // Lấy dịch vụ booking
      const servicesResponse = await axios.get(`/api/booking-services/${booking.booking_id}`);
      bookingServices.value = Array.isArray(servicesResponse.data) ? servicesResponse.data : [];
      console.log('Booking services received:', JSON.stringify(bookingServices.value, null, 2));

      // Lấy danh sách phòng trống
      const roomsResponse = await axios.get('/api/available-rooms', {
        params: {
          check_in: booking.check_in_date,
          check_out: booking.check_out_date,
          type_id: booking.type_id,
        },
      });
      availableRooms.value = Array.isArray(roomsResponse.data)
        ? roomsResponse.data.map(room => ({
            id: room.id || null,
            name: room.name || 'Không xác định',
            room_type: room.room_type || { type_name: 'N/A' }
          }))
        : [];
    }
    console.log('Available rooms received:', JSON.stringify(availableRooms.value, null, 2));

    bookedRooms.value = bookingDetails.value
      .filter(d => d?.room_id)
      .map(d => d.room_id)
      .filter(id => id !== null);

    errorMessage.value = availableRooms.value.length === 0 && booking.type_id
      ? 'Không có phòng trống phù hợp với loại phòng và thời gian này.'
      : '';
  } catch (error) {
    console.error('Lỗi khi lấy chi tiết booking:', error);
    errorMessage.value = `Lỗi khi lấy chi tiết booking: ${error.response?.status || 'Unknown'} - ${error.response?.data?.error || error.message}`;
  } finally {
    loading.value = false;
    showModal.value = true;
  }
};

const assignRoom = async (detail) => {
  if (!detail || !detail.booking_detail_id || !detail.room_id) {
    errorMessage.value = 'Chi tiết booking hoặc phòng không hợp lệ';
    return;
  }
  try {
    await axios.post(`/api/assign-room/${detail.booking_detail_id}`, {
      room_id: detail.room_id,
    });
    alert('Xếp phòng thành công!');
    await openDetailModal(selectedBooking.value);
    errorMessage.value = '';
  } catch (error) {
    console.error('Lỗi khi xếp phòng:', error);
    errorMessage.value = `Lỗi khi xếp phòng: ${error.response?.status || 'Unknown'} - ${error.response?.data?.error || error.message}`;
  }
};

const confirmBooking = async () => {
  try {
    await axios.patch(`/api/bookings/${selectedBooking.value.booking_id}`, {
      status: 'booked',
    });
    alert('Xác nhận booking thành công!');
    showModal.value = false;
    await fetchBookings();
    errorMessage.value = '';
  } catch (error) {
    console.error('Lỗi khi xác nhận booking:', error);
    errorMessage.value = `Lỗi khi xác nhận booking: ${error.response?.status || 'Unknown'} - ${error.response?.data?.error || error.message}`;
  }
};

const closeModal = () => {
  showModal.value = false;
  selectedBooking.value = {};
  bookingDetails.value = [];
  bookingServices.value = [];
  availableRooms.value = [];
  bookedRooms.value = [];
  errorMessage.value = '';
};

const formatDate = (date) => {
  return date ? new Date(date).toLocaleDateString('vi-VN') : 'N/A';
};

const formatPrice = (price) => {
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price || 0);
};

const formatStatus = (status) => {
  const statusMap = {
    pending_confirmation: 'Chờ xác nhận',
    booked: 'Đã đặt',
    available: 'Còn trống',
    cancelled: 'Đã hủy'
  };
  return statusMap[status] || status;
};

// Lifecycle
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
.badge-danger { background-color: #dc3545; color: #fff; }
.badge-info { background-color: #17a2b8; color: #fff; }
</style>