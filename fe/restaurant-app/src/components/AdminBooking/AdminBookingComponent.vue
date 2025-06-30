<template>
  <div class="container mt-5">
    <h1 class="text-3xl font-weight-bold text-primary mb-4">Quản lý Booking Khách Sạn</h1>
    
    <div v-if="errorMessage" class="alert alert-danger" role="alert">
      {{ errorMessage }}
    </div>

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
            <th scope="col">Phòng dịch vụ</th>
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
            <td>{{ booking.booking_type }}</td>
            <td>{{ formatDate(booking.check_in_date) }}</td>
            <td>{{ formatDate(booking.check_out_date) }}</td>
            <td>{{ booking.total_rooms }}</td>
            <td>{{ booking.so_phong_dich_vu || 0 }}</td>
            <td>{{ formatPrice(booking.total_price) }}</td>
            <td>
              <span :class="{
                'badge badge-warning': booking.status === 'pending_confirmation',
                'badge badge-success': booking.status === 'booked',
                'badge badge-danger': booking.status === 'cancelled',
                'badge badge-info': booking.status === 'available'
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
            <div class="row mb-4">
              <div class="col-md-6">
                <p><strong class="text-dark">Khách hàng:</strong> {{ selectedBooking.customer?.customer_name || 'N/A' }}</p>
                <p><strong class="text-dark">Số điện thoại:</strong> {{ selectedBooking.customer?.customer_phone || 'N/A' }}</p>
                <p><strong class="text-dark">Email:</strong> {{ selectedBooking.customer?.customer_email || 'Không có' }}</p>
                <p><strong class="text-dark">Loại Booking:</strong> {{ selectedBooking.booking_type }}</p>
                <p><strong class="text-dark">Loại giá:</strong> {{ selectedBooking.pricing_type }}</p>
                <p><strong class="text-dark">Loại phòng:</strong> {{ roomTypeName }}</p>
                <p><strong class="text-dark">Giá phòng:</strong> {{ formatPrice(roomPrice) }}</p>
              </div>
              <div class="col-md-6">
                <p><strong class="text-dark">Check-in:</strong> {{ formatDate(selectedBooking.check_in_date) }}</p>
                <p><strong class="text-dark">Check-out:</strong> {{ formatDate(selectedBooking.check_out_date) }}</p>
                <p><strong class="text-dark">Tổng phòng:</strong> {{ selectedBooking.total_rooms }}</p>
                <p><strong class="text-dark">Phòng dịch vụ:</strong> {{ selectedBooking.so_phong_dich_vu || 0 }}</p>
                <p><strong class="text-dark">Tổng giá:</strong> {{ formatPrice(selectedBooking.total_price) }}</p>
                <p><strong class="text-dark">Phí bổ sung:</strong> {{ formatPrice(selectedBooking.additional_fee || 0) }}</p>
                <p><strong class="text-dark">Trạng thái thanh toán:</strong> {{ selectedBooking.payment_status }}</p>
                <p><strong class="text-dark">Ghi chú:</strong> {{ selectedBooking.note || 'Không có' }}</p>
              </div>
            </div>

            <h4 class="text-primary mb-3">Dịch vụ bổ sung</h4>
            <div class="table-responsive mb-4">
              <table class="table table-striped" v-if="bookingServices.length > 0">
                <thead class="thead-dark">
                  <tr>
                    <th>ID</th>
                    <th>Tên dịch vụ</th>
                    <th>Giá dịch vụ</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="service in bookingServices" :key="service.service_id">
                    <td>{{ service.service_id }}</td>
                    <td>{{ service.service_name || 'Chưa xác định' }}</td>
                    <td>{{ formatPrice(service.service_price) }}</td>
                  </tr>
                </tbody>
              </table>
              <p v-else class="text-muted">Không có dịch vụ bổ sung.</p>
            </div>

            <h4 class="text-primary mb-3">Chi tiết phòng</h4>
            <div class="table-responsive mb-4">
              <table class="table table-striped">
                <thead class="thead-dark">
                  <tr>
                    <th>ID</th>
                    <th>Loại phòng ban đầu</th>
                    <th>Phòng đã xếp</th>
                    <th>Giá phòng</th>
                    <th>Ghi chú</th>
                    <th>Xếp phòng</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="detail in bookingDetails" :key="detail.booking_detail_id">
                    <td>{{ detail.booking_detail_id }}</td>
                    <td>{{ detail.initial_room_type }}</td>
                    <td>{{ detail.room?.room_name || 'Chưa xếp' }}</td>
                    <td>{{ formatPrice(detail.gia_phong) }}</td>
                    <td>{{ detail.note || 'Không có' }}</td>
                    <td>
                      <select
                        v-model="detail.room_id"
                        @change="assignRoom(detail)"
                        class="form-control form-control-sm"
                        :disabled="detail.room_id !== null"
                      >
                        <option value="">Chọn phòng</option>
                        <option
                          v-for="room in filteredRooms"
                          :key="room.id"
                          :value="room.id"
                          :disabled="assignedRoomIds.includes(room.id)"
                        >
                          {{ room.name }} ({{ room.room_type?.type_name || 'N/A' }} - {{ formatPrice(room.price) }})
                        </option>
                      </select>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <div class="modal-footer">
            <button @click="confirmBooking" class="btn btn-success">Xác nhận Booking</button>
            <button @click="closeModal" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
          </div>
        </div>
      </div>
    </div>
    <div v-if="showModal" class="modal-backdrop fade show"></div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import axios from 'axios';

// State
const bookings = ref([]);
const showModal = ref(false);
const selectedBooking = ref({});
const bookingDetails = ref([]);
const bookingServices = ref([]);
const availableRooms = ref([]);
const assignedRoomIds = ref([]);
const errorMessage = ref('');
const roomTypes = ref({});
const roomTypeName = ref('Chưa xác định');
const roomPrice = ref(0);

// Computed
const filteredRooms = computed(() => {
  return availableRooms.value.filter(room =>
    selectedBooking.value.type_id && room.type_id === selectedBooking.value.type_id
  );
});

// Methods
const fetchBookings = async () => {
  try {
    const response = await axios.get('/api/bookings?status=pending_confirmation');
    bookings.value = response.data;
    console.log('Số lượng booking nhận được từ API:', bookings.value.length);
    errorMessage.value = '';
  } catch (error) {
    console.error('Lỗi khi lấy danh sách booking:', error);
    errorMessage.value = `Lỗi khi lấy danh sách booking: ${error.response?.status || 'Unknown'} - ${error.response?.data?.error || error.message}`;
  }
};

const getRoomTypeName = async (typeId) => {
  if (!roomTypes.value[typeId]) {
    try {
      const response = await axios.get(`/api/room-types/${typeId}`, {
        params: {
          check_in: selectedBooking.value.check_in_date || new Date().toISOString().split('T')[0],
        },
      });
      if (response.data && response.data.type_name) {
        roomTypes.value[typeId] = response.data.type_name;
        roomPrice.value = response.data.price.price_per_night || 0;
      } else {
        throw new Error('Không có dữ liệu loại phòng');
      }
    } catch (error) {
      console.error('Lỗi khi lấy loại phòng:', error);
      roomTypes.value[typeId] = `Lỗi: ${error.response?.status || 'Không xác định'}`;
      roomPrice.value = 0;
    }
  }
  roomTypeName.value = roomTypes.value[typeId] || 'Chưa xác định';
  console.log('roomTypeName updated to:', roomTypeName.value, 'for typeId:', typeId);
  return roomTypes.value[typeId] || 'Chưa xác định';
};

const openDetailModal = async (booking) => {
  selectedBooking.value = booking;
  try {
    const detailsResponse = await axios.get(`/api/booking-details/${booking.booking_id}`);
    bookingDetails.value = detailsResponse.data;

    const servicesResponse = await axios.get(`/api/booking-services/${booking.booking_id}`);
    bookingServices.value = servicesResponse.data.map(service => ({
      ...service,
      service_name: service.service?.name || 'Chưa xác định',
      service_price: service.price || 0,
    }));

    const roomsResponse = await axios.get('/api/available-rooms', {
      params: {
        check_in: booking.check_in_date,
        check_out: booking.check_out_date,
        type_id: booking.type_id,
      },
    });
    availableRooms.value = roomsResponse.data;

    assignedRoomIds.value = bookingDetails.value
      .filter(detail => detail.room_id)
      .map(detail => detail.room_id);

    if (booking.type_id) {
      await getRoomTypeName(booking.type_id);
      for (const detail of bookingDetails.value) {
        detail.initial_room_type = roomTypeName.value;
        detail.gia_phong = roomPrice.value;
      }
    } else {
      bookingDetails.value.forEach(detail => {
        detail.initial_room_type = 'Chưa xác định loại phòng';
        detail.gia_phong = 0;
      });
    }

    errorMessage.value = '';
    showModal.value = true;
  } catch (error) {
    console.error('Lỗi khi lấy chi tiết booking:', error);
    errorMessage.value = `Lỗi khi lấy chi tiết booking: ${error.response?.status || 'Unknown'} - ${error.response?.data?.error || error.message}`;
  }
};

const assignRoom = async (detail) => {
  try {
    await axios.post(`/api/assign-room/${detail.booking_detail_id}`, {
      room_id: detail.room_id,
    });
    assignedRoomIds.value.push(detail.room_id);
    detail.room = availableRooms.value.find(r => r.id === detail.room_id);
    alert('Xếp phòng thành công!');
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
    fetchBookings();
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
  assignedRoomIds.value = [];
  errorMessage.value = '';
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('vi-VN');
};

const formatPrice = (price) => {
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price);
};

const formatStatus = (status) => {
  const statusMap = {
    pending_confirmation: 'Chờ xác nhận',
    booked: 'Đã đặt',
    available: 'Còn trống',
    cancelled: 'Đã hủy',
    occupied: 'Đã đặt'
  };
  return statusMap[status] || status;
};

// Lifecycle
onMounted(() => {
  fetchBookings();
});
</script>

<style scoped>
/* CSS tùy chỉnh cho modal overlay */
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

/* Đảm bảo modal hiển thị đúng */
.modal-dialog {
  transform: translate(0, 0) !important;
}

/* Responsive table */
@media (max-width: 768px) {
  .table-responsive {
    overflow-x: auto;
  }
  .btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
  }
}

/* Style cho option disabled */
option:disabled {
  color: #ccc;
  background-color: #f9f9f9;
}
</style>