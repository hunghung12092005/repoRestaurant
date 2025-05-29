<template>
  <div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="fw-bold text-sea-green">Quản Lý Booking</h1>
      <button class="btn btn-success shadow" @click="moModalThem">
        <i class="bi bi-plus-circle me-2"></i>Thêm Booking Mới
      </button>
    </div>
    <div v-if="successMessage" class="alert alert-success">
      {{ successMessage }}
    </div>

    <div class="row mb-4 g-3">
      <div class="col-md-4">
        <input
          v-model="tuKhoa"
          type="text"
          class="form-control"
          placeholder="Tìm theo tên hoặc số điện thoại"
        />
      </div>
      <div v-if="hasStatus" class="col-md-3">
        <select v-model="locTheoTrangThai" class="form-select">
          <option value="">Tất cả trạng thái</option>
          <option value="pending">Chờ xác nhận</option>
          <option value="confirmed">Đã xác nhận</option>
          <option value="cancelled">Đã hủy</option>
        </select>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-bordered table-hover align-middle">
        <thead>
          <tr>
            <th>#</th>
            <th>Tên Khách</th>
            <th>Số Điện Thoại</th>
            <th>Loại</th>
            <th>Ngày Đặt/Check-in</th>
            <th>Check-out</th>
            <th>Giờ Đặt</th>
            <th>Số Lượng</th>
            <th>Phòng</th>
            <th>Bàn</th>
            <th>Món</th>
            <th v-if="hasStatus">Trạng Thái</th>
            <th>Hành Động</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(booking, index) in bookingHienThi" :key="booking.booking_id">
            <td>{{ (trangHienTai - 1) * soBookingMoiTrang + index + 1 }}</td>
            <td>{{ booking.customer_name || '-' }}</td>
            <td>{{ booking.customer_phone || '-' }}</td>
            <td>{{ booking.type === 'room' ? 'Phòng' : 'Bàn' }}</td>
            <td>{{ formatDate(booking.check_in_date || booking.booking_date) }}</td>
            <td>{{ booking.type === 'room' ? formatDate(booking.check_out_date) : '-' }}</td>
            <td>{{ booking.booking_time || '-' }}</td>
            <td>{{ booking.quantity || '-' }}</td>
            <td>{{ booking.room ? booking.room.room_name : '-' }}</td>
            <td>{{ booking.table ? booking.table.table_name : '-' }}</td>
            <td>{{ booking.menu ? booking.menu.menu_name : '-' }}</td>
            <td v-if="hasStatus" class="status">
              <span
                :class="{
                  badge: true,
                  'bg-success': booking.status === 'confirmed',
                  'bg-warning text-dark': booking.status === 'pending',
                  'bg-danger': booking.status === 'cancelled'
                }"
              >
                {{ formatStatus(booking.status) }}
              </span>
            </td>
            <td>
              <button
                v-if="booking.status === 'pending'"
                class="btn btn-success btn-sm me-2"
                @click="confirmBooking(booking.booking_id)"
              >
                <i class="bi bi-check-circle me-1"></i>Xác nhận
              </button>
              <button class="btn btn-success btn-sm me-2" @click="moModalSua(booking)">
                <i class="bi bi-pencil me-1"></i>Sửa
              </button>
              <button class="btn btn-danger btn-sm" @click="xoaBooking(booking.booking_id)">
                <i class="bi bi-trash me-1"></i>Xóa
              </button>
            </td>
          </tr>
          <tr v-if="bookingHienThi?.length === 0">
            <td :colspan="hasStatus ? 13 : 12" class="text-center text-muted">
              Không có booking nào phù hợp
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Phân trang -->
    <div class="d-flex justify-content-between align-items-center mt-4">
      <div class="text-muted">
        <span>Hiển thị {{ bookingHienThi?.length || 0 }} / {{ bookingLoc?.length || 0 }} booking</span>
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
            v-for="tr in trangHienThi"
            :key="tr"
            class="page-item"
            :class="{ active: trangHienTai === tr }"
          >
            <button class="page-link" @click="trangHienTai = tr">{{ tr }}</button>
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

    <!-- Modal with Integrated Booking Form -->
    <div v-if="laModalMo" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5)">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ isEditing ? 'Cập nhật Booking' : 'Thêm Booking Mới' }}</h5>
            <button type="button" class="btn" @click="dongModal">X</button>
          </div>
          <div class="modal-body">
            <div class="container mt-4" style="max-width: 600px;">
              <div v-if="isLoading" class="text-center mb-3">
                <div class="spinner-border text-primary" role="status">
                  <span class="visually-hidden">Đang tải...</span>
                </div>
              </div>
              <div v-if="errorMessage" class="alert alert-danger">
                {{ errorMessage }}
              </div>
              <div v-if="!isLoading && !rooms?.length && !tables?.length" class="alert alert-warning">
                Không có phòng hoặc bàn nào được tải. Vui lòng kiểm tra kết nối hoặc thử lại.
              </div>

              <form @submit.prevent="submitBooking" novalidate>
                <div class="mb-3">
                  <label for="bookingType" class="form-label">Loại Booking</label>
                  <select
                    class="form-control"
                    v-model="form.type"
                    required
                    :class="{ 'is-invalid': errors.type }"
                    @change="onTypeChange"
                  >
                    <option value="">-- Chọn loại --</option>
                    <option value="room">Phòng</option>
                    <option value="table">Bàn</option>
                  </select>
                  <div v-if="errors.type" class="error-message">
                    {{ errors.type }}
                  </div>
                </div>
                <div class="mb-3">
                  <label for="customerName" class="form-label">Tên khách hàng</label>
                  <input
                    type="text"
                    id="customerName"
                    class="form-control"
                    v-model="form.customer_name"
                    required
                    placeholder="Nhập tên khách hàng"
                    :class="{ 'is-invalid': errors.customer_name }"
                  />
                  <div v-if="errors.customer_name" class="error-message">
                    {{ errors.customer_name }}
                  </div>
                </div>
                <div class="mb-3">
                  <label for="customerPhone" class="form-label">Số điện thoại</label>
                  <input
                    type="tel"
                    id="customerPhone"
                    class="form-control"
                    v-model="form.customer_phone"
                    required
                    placeholder="Nhập số điện thoại"
                    :class="{ 'is-invalid': errors.customer_phone }"
                  />
                  <div v-if="errors.customer_phone" class="error-message">
                    {{ errors.customer_phone }}
                  </div>
                </div>
                <div class="row mb-3">
                  <div class="col" v-if="form.type === 'room'">
                    <label for="checkInDate" class="form-label">Ngày check-in</label>
                    <input
                      type="date"
                      id="checkInDate"
                      class="form-control"
                      v-model="form.check_in_date"
                      required
                      :min="minDate"
                      :class="{ 'is-invalid': errors.check_in_date }"
                    />
                    <div v-if="errors.check_in_date" class="error-message">
                      {{ errors.check_in_date }}
                    </div>
                  </div>
                  <div class="col" v-if="form.type === 'room'">
                    <label for="checkOutDate" class="form-label">Ngày check-out</label>
                    <input
                      type="date"
                      id="checkOutDate"
                      class="form-control"
                      v-model="form.check_out_date"
                      required
                      :min="minCheckOutDate"
                      :class="{ 'is-invalid': errors.check_out_date }"
                    />
                    <div v-if="errors.check_out_date" class="error-message">
                      {{ errors.check_out_date }}
                    </div>
                  </div>
                  <div class="col" v-if="form.type === 'room'">
                    <label for="bookingTime" class="form-label">Giờ đặt</label>
                    <input
                      type="time"
                      id="bookingTime"
                      class="form-control"
                      v-model="form.booking_time"
                      required
                      :min="minTime"
                      :class="{ 'is-invalid': errors.booking_time }"                    />
                    <div v-if="errors.booking_time" class="error-message">
                      {{ errors.booking_time }}
                    </div>
                  </div>
                  <div class="col" v-if="form.type === 'table'">
                    <label for="bookingDate" class="form-label">Ngày đặt</label>
                    <input
                      type="date"
                      id="bookingDate"
                      class="form-control"
                      v-model="form.booking_date"
                      required
                      :min="minDate"
                      :class="{ 'is-invalid': errors.booking_date }"                    />
                    <div v-if="errors.booking_date" class="error-message">
                      {{ errors.booking_date }}
                    </div>
                  </div>
                  <div class="col" v-if="form.type === 'table'">
                    <label for="bookingTime" class="form-label">Giờ đặt</label>
                    <input
                      type="time"
                      id="bookingTime"
                      class="form-control"
                      v-model="form.booking_time"
                      required
                      :min="minTime"
                      :class="{ 'is-invalid': errors.booking_time }"                    />
                    <div v-if="errors.booking_time" class="error-message">
                      {{ errors.booking_time }}
                    </div>
                  </div>
                </div>
                <div class="mb-3">
                  <label for="quantity" class="form-label">Số lượng khách</label>
                  <input
                    type="number"
                    id="quantity"
                    class="form-control"
                    v-model.number="form.quantity"
                    required
                    placeholder="Nhập số lượng khách"
                    min="1"
                    :class="{ 'is-invalid': errors.quantity }"
                    @input="onQuantityChange"
                  />
                  <div v-if="errors.quantity" class="error-message">
                    {{ errors.quantity }}
                  </div>
                </div>
                <div class="mb-3" v-if="form.type === 'room'">
                  <label for="selectRoom" class="form-label">Chọn phòng</label>
                  <select
                    id="selectRoom"
                    class="form-select"
                    v-model="form.room_id"
                    :disabled="isLoading || !availableRooms?.length"
                    :class="{ 'is-invalid': errors.room_table }"
                  >
                    <option :value="null">-- {{ availableRooms?.length ? 'Chọn phòng' : 'Không có phòng phù hợp' }} --</option>
                    <option
                      v-for="room in availableRooms"
                      :key="room.room_id"
                      :value="room.room_id"
                      :disabled="!isEditing && room.status !== 'Trống'"
                    >
                      {{ room.room_name }} (Sức chứa: {{ room.capacity || 'N/A' }})
                      {{ room.status !== 'Trống' ? `(${room.status})` : '' }}
                    </option>
                  </select>
                  <div v-if="errors.room_table" class="invalid-feedback">
                    {{ errors.room_table }}
                  </div>
                </div>
                <div class="mb-3" v-if="form.type === 'table'">
                  <label for="selectTable" class="form-label">Chọn bàn</label>
                  <select
                    id="selectTable"
                    class="form-select"
                    v-model="form.table_id"
                    :disabled="isLoading || !availableTables?.length"
                    :class="{ 'is-invalid': errors.room_table }"
                  >
                    <option :value="null">-- {{ availableTables?.length ? 'Chọn bàn' : 'Không có bàn phù hợp' }} --</option>
                    <option
                      v-for="table in availableTables"
                      :key="table.table_id"
                      :value="table.table_id"
                      :disabled="!isEditing && table.status !== 'Trống'"
                    >
                      {{ table.table_name }} (Sức chứa: {{ table.capacity || 'N/A' }})
                      {{ table.status !== 'Trống' ? `(${table.status})` : '' }}
                    </option>
                  </select>
                  <div v-if="errors.room_table" class="invalid-feedback">
                    {{ errors.room_table }}
                  </div>
                </div>
                <div class="mb-3">
                  <label for="selectMenu" class="form-label">Chọn món ăn</label>
                  <select
                    id="selectMenu"
                    class="form-select"
                    v-model="form.menu_id"
                    :disabled="isLoading || !menus?.length"
                  >
                    <option :value="null">-- Chọn món --</option>
                    <option v-for="menu in menus" :key="menu.menu_id" :value="menu.menu_id">
                      {{ menu.menu_name }} ({{ formatPrice(menu.price) }})
                    </option>
                  </select>
                </div>
                <div class="mb-3">
                  <label for="note" class="form-label">Ghi chú</label>
                  <textarea
                    id="note"
                    rows="3"
                    class="form-control"
                    v-model="form.note"
                    placeholder="Nhập ghi chú nếu có"
                  ></textarea>
                </div>
              </form>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" @click="dongModal">Hủy</button>
            <button class="btn btn-success" @click="submitBooking">Lưu</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

// Retry request helper
const retryRequest = async (fn, retries = 2, delay = 1000) => {
  for (let i = 0; i < retries; i++) {
    try {
      return await fn();
    } catch (error) {
      if (i === retries - 1 || error.code !== 'ECONNABORTED') throw error;
      console.warn(`Thử lại lần ${i + 1}/${retries} sau ${delay}ms`);
      await new Promise((resolve) => setTimeout(resolve, delay));
    }
  }
};

const api = axios.create({
  baseURL: 'http://localhost:8000/api',
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

export default {
  name: 'AdminBookingComponent',
  setup() {
    const bookings = ref([]);
    const tuKhoa = ref('');
    const locTheoTrangThai = ref('');
    const laModalMo = ref(false);
    const bookingDangSua = ref(null);
    const hasStatus = ref(false);
    const successMessage = ref('');
    const trangHienTai = ref(1);
    const soBookingMoiTrang = 5;

    const form = ref({
      booking_id: null,
      type: '',
      customer_name: '',
      customer_phone: '',
      check_in_date: '',
      check_out_date: '',
      booking_date: '',
      booking_time: '',
      quantity: 1,
      room_id: null,
      table_id: null,
      menu_id: null,
      note: '',
      status: 'pending',
    });
    const rooms = ref([]);
    const tables = ref([]);
    const menus = ref([]);
    const isEditing = ref(false);
    const isLoading = ref(false);
    const errorMessage = ref('');
    const errors = ref({
      type: '',
      customer_name: '',
      customer_phone: '',
      check_in_date: '',
      check_out_date: '',
      booking_date: '',
      booking_time: '',
      quantity: '',
      room_table: '',
      menu_id: '',
      note: '',
    });

    const minDate = computed(() => {
      const today = new Date();
      return today.toISOString().split('T')[0];
    });

    const minCheckOutDate = computed(() => {
      if (!form.value.check_in_date) return minDate.value;
      const checkIn = new Date(form.value.check_in_date);
      checkIn.setDate(checkIn.getDate() + 1);
      return checkIn.toISOString().split('T')[0];
    });

    const minTime = computed(() => {
      const now = new Date();
      if (form.value.booking_date === minDate.value) {
        return now.toTimeString().slice(0, 5);
      }
      return '00:00';
    });

    const availableRooms = computed(() => {
      if (!form.value.quantity) return rooms.value;
      return rooms.value.filter(
        (room) =>
          room.capacity >= form.value.quantity ||
          (isEditing.value && room.room_id === form.value.room_id)
      );
    });

    const availableTables = computed(() => {
      if (!form.value.quantity) return tables.value;
      return tables.value.filter(
        (table) =>
          table.capacity >= form.value.quantity ||
          (isEditing.value && table.table_id === form.value.table_id)
      );
    });

    const bookingLoc = computed(() => {
      return bookings.value.filter((b) => {
        const khopTim =
          (b.customer_name?.toLowerCase() || '').includes(tuKhoa.value.toLowerCase()) ||
          (b.customer_phone || '').includes(tuKhoa.value);
        const khopTrangThai = !locTheoTrangThai.value || b.status === locTheoTrangThai.value;
        return khopTim && khopTrangThai;
      });
    });

    const tongSoTrang = computed(() => {
      return Math.ceil((bookingLoc.value?.length || 0) / soBookingMoiTrang);
    });

    const bookingHienThi = computed(() => {
      const batDau = (trangHienTai.value - 1) * soBookingMoiTrang;
      const ketThuc = batDau + soBookingMoiTrang;
      return bookingLoc.value?.slice(batDau, ketThuc) || [];
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

    const fetchBookings = async () => {
      try {
        const res = await retryRequest(() => api.get('/bookings'));
        bookings.value = Array.isArray(res.data.data)
          ? res.data.data
          : Array.isArray(res.data)
          ? res.data
          : [];
        hasStatus.value = bookings.value.length > 0 && 'status' in bookings.value[0];
      } catch (error) {
        console.error('Lỗi lấy booking:', error);
        errorMessage.value = 'Không thể tải danh sách booking. Vui lòng kiểm tra kết nối.';
      }
    };

    const fetchFormData = async () => {
      isLoading.value = true;
      errorMessage.value = '';
      try {
        const [roomsRes, tablesRes, menusRes] = await Promise.all([
          retryRequest(() => api.get('/rooms')),
          retryRequest(() => api.get('/tables')),
          retryRequest(() => api.get('/menus')),
        ]);
        rooms.value = Array.isArray(roomsRes.data.data)
          ? roomsRes.data.data
          : Array.isArray(roomsRes.data)
          ? roomsRes.data
          : [];
        tables.value = Array.isArray(tablesRes.data.data)
          ? tablesRes.data.data
          : Array.isArray(tablesRes.data)
          ? tablesRes.data
          : [];
        menus.value = Array.isArray(menusRes.data.data)
          ? menusRes.data.data
          : Array.isArray(menusRes.data)
          ? menusRes.data
          : [];
        if (!rooms.value.length && !tables.value.length) {
          errorMessage.value = 'Không có phòng hoặc bàn nào được tải từ API';
        }
      } catch (error) {
        console.error('Lỗi tải dữ liệu:', error);
        errorMessage.value = 'Không thể tải dữ liệu phòng, bàn hoặc món ăn. Vui lòng kiểm tra kết nối.';
      } finally {
        isLoading.value = false;
      }
    };

    onMounted(() => {
      fetchBookings();
      fetchFormData();
    });

    const moModalThem = () => {
      bookingDangSua.value = null;
      isEditing.value = false;
      resetForm();
      laModalMo.value = true;
      successMessage.value = '';
    };

    const moModalSua = (booking) => {
      bookingDangSua.value = { ...booking };
      loadBookingToForm(booking);
      laModalMo.value = true;
      successMessage.value = '';
    };

    const dongModal = () => {
      laModalMo.value = false;
      successMessage.value = '';
      resetForm();
    };

    const onTypeChange = () => {
      form.value.room_id = null;
      form.value.table_id = null;
      form.value.check_in_date = '';
      form.value.check_out_date = '';
      form.value.booking_date = '';
      form.value.booking_time = '';
      errors.value.room_table = '';
      errors.value.check_in_date = '';
      errors.value.check_out_date = '';
      errors.value.booking_date = '';
      errors.value.booking_time = '';
    };

    const onQuantityChange = () => {
      if (form.value.room_id) {
        const room = rooms.value.find((r) => r.room_id === form.value.room_id);
        if (room && room.capacity < form.value.quantity) {
          form.value.room_id = null;
          errors.value.room_table = 'Số lượng khách vượt quá sức chứa của phòng';
        }
      }
      if (form.value.table_id) {
        const table = tables.value.find((t) => t.table_id === form.value.table_id);
        if (table && table.capacity < form.value.quantity) {
          form.value.table_id = null;
          errors.value.room_table = 'Số lượng khách vượt quá sức chứa của bàn';
        }
      }
    };

    const validateForm = () => {
      errors.value = {
        type: '',
        customer_name: '',
        customer_phone: '',
        check_in_date: '',
        check_out_date: '',
        booking_date: '',
        booking_time: '',
        quantity: '',
        room_table: '',
        menu_id: '',
        note: '',
      };
      let isValid = true;

      if (!form.value.type) {
        errors.value.type = 'Vui lòng chọn loại booking';
        isValid = false;
      }
      if (!form.value.customer_name.trim()) {
        errors.value.customer_name = 'Vui lòng nhập tên khách hàng';
        isValid = false;
      }
      if (!form.value.customer_phone.trim()) {
        errors.value.customer_phone = 'Vui lòng nhập số điện thoại';
        isValid = false;
      } else if (!/^\d{10,11}$/.test(form.value.customer_phone.trim())) {
        errors.value.customer_phone = 'Số điện thoại phải có 10-11 số';
        isValid = false;
      }
      if (!form.value.quantity || form.value.quantity < 1) {
        errors.value.quantity = 'Số lượng khách phải lớn hơn 0';
        isValid = false;
      }

      if (form.value.type === 'room') {
        if (!form.value.check_in_date) {
          errors.value.check_in_date = 'Vui lòng chọn ngày check-in';
          isValid = false;
        }
        if (!form.value.check_out_date) {
          errors.value.check_out_date = 'Vui lòng chọn ngày check-out';
          isValid = false;
        }
        if (
          form.value.check_in_date &&
          form.value.check_out_date &&
          form.value.check_out_date <= form.value.check_in_date
        ) {
          errors.value.check_out_date = 'Ngày check-out phải sau ngày check-in';
          isValid = false;
        }
        if (!form.value.room_id) {
          errors.value.room_table = 'Vui lòng chọn phòng';
          isValid = false;
        }
        if (form.value.room_id && !isEditing.value) {
          const room = rooms.value.find((r) => r.room_id === form.value.room_id);
          if (room && room.status && room.status !== 'Trống') {
            errors.value.room_table = 'Phòng đã được đặt hoặc đang chờ xét duyệt';
            isValid = false;
          }
          if (room && room.capacity < form.value.quantity) {
            errors.value.room_table = 'Số lượng khách vượt quá sức chứa của phòng';
            isValid = false;
          }
        }
      } else if (form.value.type === 'table') {
        if (!form.value.booking_date) {
          errors.value.booking_date = 'Vui lòng chọn ngày đặt';
          isValid = false;
        }
        if (!form.value.booking_time) {
          errors.value.booking_time = 'Vui lòng chọn giờ đặt';
          isValid = false;
        }
        if (!form.value.table_id) {
          errors.value.room_table = 'Vui lòng chọn bàn';
          isValid = false;
        }
        if (form.value.table_id && !isEditing.value) {
          const table = tables.value.find((t) => t.table_id === form.value.table_id);
          if (table && table.status && table.status !== 'Trống') {
            errors.value.room_table = 'Bàn đã được đặt hoặc đang chờ xét duyệt';
            isValid = false;
          }
          if (table && table.capacity < form.value.quantity) {
            errors.value.room_table = 'Số lượng khách vượt quá sức chứa của bàn';
            isValid = false;
          }
        }
        const bookingDateTime = form.value.booking_date && form.value.booking_time
          ? new Date(`${form.value.booking_date}T${form.value.booking_time}`)
          : null;
        const currentDateTime = new Date();
        if (bookingDateTime && bookingDateTime < currentDateTime) {
          errors.value.booking_date = 'Thời gian đặt phải ở tương lai';
          isValid = false;
        }
      }

      return isValid;
    };

    const submitBooking = async () => {
      if (!validateForm()) return;

      isLoading.value = true;
      errorMessage.value = '';
      errors.value = {
        type: '',
        customer_name: '',
        customer_phone: '',
        check_in_date: '',
        check_out_date: '',
        booking_date: '',
        booking_time: '',
        quantity: '',
        room_table: '',
        menu_id: '',
        note: '',
      };

      try {
        if (form.value.type === 'room' && form.value.room_id && !isEditing.value) {
          const room = rooms.value.find((r) => r.room_id === form.value.room_id);
          if (room && room.status && room.status !== 'Trống') {
            errorMessage.value = 'Phòng đã được đặt hoặc đang chờ xét duyệt';
            isLoading.value = false;
            return;
          }
          if (room && room.capacity < form.value.quantity) {
            errorMessage.value = 'Số lượng khách vượt quá sức chứa của phòng';
            isLoading.value = false;
            return;
          }
        }
        if (form.value.type === 'table' && form.value.table_id && !isEditing.value) {
          const table = tables.value.find((t) => t.table_id === form.value.table_id);
          if (table && table.status && table.status !== 'Trống') {
            errorMessage.value = 'Bàn đã được đặt hoặc đang chờ xét duyệt';
            isLoading.value = false;
            return;
          }
          if (table && table.capacity < form.value.quantity) {
            errorMessage.value = 'Số lượng khách vượt quá sức chứa của bàn';
            isLoading.value = false;
            return;
          }
        }

        const payload = {
          type: form.value.type,
          customer_name: form.value.customer_name,
          customer_phone: form.value.customer_phone,
          check_in_date: form.value.type === 'room' ? form.value.check_in_date : null,
          check_out_date: form.value.type === 'room' ? form.value.check_out_date : null,
          booking_date: form.value.type === 'table' ? form.value.booking_date : null,
          booking_time: form.value.type === 'table' ? form.value.booking_time : null,
          quantity: form.value.quantity,
          room_id: form.value.type === 'room' ? form.value.room_id : null,
          table_id: form.value.type === 'table' ? form.value.table_id : null,
          menu_id: form.value.menu_id,
          note: form.value.note,
          status: isEditing.value ? undefined : 'pending',
        };

        console.log('Submitting booking payload:', payload);

        let bookingId;
        if (isEditing.value) {
          await retryRequest(() => api.put(`/bookings/${form.value.booking_id}`, payload));
          bookingId = form.value.booking_id;
          successMessage.value = 'Cập nhật booking thành công';
        } else {
          const response = await retryRequest(() => api.post('/bookings', payload));
          bookingId = response.data.booking_id || response.data.data?.id;
          successMessage.value = 'Thêm booking mới thành công';

          if (form.value.type === 'room' && form.value.room_id) {
            try {
              await retryRequest(() =>
                api.patch(`/rooms/${form.value.room_id}`, { status: 'Đang chờ xét duyệt' })
              );
            } catch (err) {
              console.error('Lỗi cập nhật trạng thái phòng:', err);
              errorMessage.value += ' Lỗi khi cập nhật trạng thái phòng.';
            }
          }
          if (form.value.type === 'table' && form.value.table_id) {
            try {
              await retryRequest(() =>
                api.patch(`/tables/${form.value.table_id}`, { status: 'Đang chờ xét duyệt' })
              );
            } catch (err) {
              console.error('Lỗi cập nhật trạng thái bàn:', err);
              errorMessage.value += ' Lỗi khi cập nhật trạng thái bàn.';
            }
          }
        }

        await fetchBookings();
        await fetchFormData();
        dongModal();
      } catch (error) {
        console.error('Lỗi khi lưu booking:', error);
        if (error.code === 'ECONNABORTED') {
          errorMessage.value = 'Không thể kết nối đến server. Vui lòng kiểm tra kết nối mạng hoặc thử lại.';
        } else if (error.response && error.response.status === 422) {
          const backendErrors = error.response.data.errors || {};
          errorMessage.value = 'Lưu booking thất bại. Vui lòng kiểm tra các trường nhập liệu.';
          for (const [field, messages] of Object.entries(backendErrors)) {
            if (field in errors.value) {
              errors.value[field] = messages[0];
            } else if (field === 'room_id') {
              errors.value.room_table = 'Phòng không hợp lệ: ' + messages[0];
            } else if (field === 'table_id') {
              errors.value.room_table = 'Bàn không hợp lệ: ' + messages[0];
            }
          }
        } else if (error.response && error.response.status === 500) {
          errorMessage.value = 'Lỗi hệ thống. Vui lòng kiểm tra dữ liệu hoặc thử lại sau.';
          if (error.response.data.message.includes('SQLSTATE[42S22]')) {
            if (form.value.type === 'room') {
              errors.value.room_table = 'Phòng không tồn tại hoặc cấu hình hệ thống có lỗi.';
            } else {
              errors.value.room_table = 'Bàn không tồn tại hoặc cấu hình hệ thống có lỗi.';
            }
          }
        } else {
          errorMessage.value = `Lưu booking thất bại: ${error.response?.data?.message || 'Lỗi server'}`;
        }
      } finally {
        isLoading.value = false;
      }
    };

    const confirmBooking = async (bookingId) => {
      if (!confirm('Bạn có chắc chắn muốn xác nhận booking này không?')) return;

      isLoading.value = true;
      errorMessage.value = '';
      try {
        const booking = bookings.value.find((b) => b.booking_id === bookingId);
        if (!booking) throw new Error('Không tìm thấy booking');

        await retryRequest(() => api.patch(`/bookings/${bookingId}/confirm`, { status: 'confirmed' }));

        if (booking.type === 'room' && booking.room_id) {
          try {
            await retryRequest(() => api.patch(`/rooms/${booking.room_id}`, { status: 'Đã đặt' }));
          } catch (err) {
            console.error('Lỗi cập nhật trạng thái phòng:', err);
            errorMessage.value += ' Lỗi khi cập nhật trạng thái phòng.';
          }
        }
        if (booking.type === 'table' && booking.table_id) {
          try {
            await retryRequest(() => api.patch(`/tables/${booking.table_id}`, { status: 'Đã đặt' }));
          } catch (err) {
            console.error('Lỗi cập nhật trạng thái bàn:', err);
            errorMessage.value += ' Lỗi khi cập nhật trạng thái bàn.';
          }
        }

        await fetchBookings();
        await fetchFormData();
        successMessage.value = 'Xác nhận booking thành công';
      } catch (error) {
        console.error('Lỗi xác nhận booking:', error);
        if (error.code === 'ECONNABORTED') {
          errorMessage.value = 'Không thể kết nối đến server. Vui lòng kiểm tra kết nối mạng hoặc thử lại.';
        } else if (error.response) {
          errorMessage.value = `Xác nhận booking thất bại: ${error.response.data.message || 'Lỗi server'}`;
        } else {
          errorMessage.value = 'Xác nhận booking thất bại. Vui lòng thử lại.';
        }
      } finally {
        isLoading.value = false;
      }
    };

    const resetForm = () => {
      form.value = {
        booking_id: null,
        type: '',
        customer_name: '',
        customer_phone: '',
        check_in_date: '',
        check_out_date: '',
        booking_date: '',
        booking_time: '',
        quantity: 1,
        room_id: null,
        table_id: null,
        menu_id: null,
        note: '',
        status: 'pending',
      };
      isEditing.value = false;
      errorMessage.value = '';
      errors.value = {
        type: '',
        customer_name: '',
        customer_phone: '',
        check_in_date: '',
        check_out_date: '',
        booking_date: '',
        booking_time: '',
        quantity: '',
        room_table: '',
        menu_id: '',
        note: '',
      };
    };

    const loadBookingToForm = (booking) => {
      form.value = {
        booking_id: booking.booking_id || null,
        type: booking.type || '',
        customer_name: booking.customer_name || '',
        customer_phone: booking.customer_phone || '',
        check_in_date: booking.check_in_date ? booking.check_in_date.split('T')[0] : '',
        check_out_date: booking.check_out_date ? booking.check_out_date.split('T')[0] : '',
        booking_date: booking.booking_date ? booking.booking_date.split('T')[0] : '',
        booking_time: booking.booking_time ? booking.booking_time.slice(0, 5) : '',
        quantity: booking.quantity || 1,
        room_id: booking.room_id || null,
        table_id: booking.table_id || null,
        menu_id: booking.menu_id || null,
        note: booking.note || '',
        status: booking.status || 'pending',
      };
      isEditing.value = true;
    };

    const xoaBooking = async (id) => {
      if (!confirm('Bạn có chắc chắn muốn xóa booking này không?')) return;

      try {
        const booking = bookings.value.find((b) => b.booking_id === id);
        if (!booking) throw new Error('Không tìm thấy booking');

        if (booking.type === 'room' && booking.room_id) {
          try {
            await retryRequest(() => api.patch(`/rooms/${booking.room_id}`, { status: 'Trống' }));
          } catch (err) {
            console.error('Lỗi cập nhật trạng thái phòng:', err);
            errorMessage.value += ' Lỗi khi cập nhật trạng thái phòng.';
          }
        }
        if (booking.type === 'table' && booking.table_id) {
          try {
            await retryRequest(() => api.patch(`/tables/${booking.table_id}`, { status: 'Trống' }));
          } catch (err) {
            console.error('Lỗi cập nhật trạng thái bàn:', err);
            errorMessage.value += ' Lỗi khi cập nhật trạng thái bàn.';
          }
        }

        await retryRequest(() => api.delete(`/bookings/${id}`));
        successMessage.value = 'Xóa booking thành công';
        await fetchBookings();
        await fetchFormData();
      } catch (error) {
        console.error('Lỗi xóa booking:', error);
        if (error.code === 'ECONNABORTED') {
          errorMessage.value = 'Không thể kết nối đến server. Vui lòng kiểm tra kết nối mạng hoặc thử lại.';
        } else if (error.response) {
          errorMessage.value = `Xóa booking thất bại: ${error.response.data.message || 'Lỗi server'}`;
        } else {
          errorMessage.value = 'Xóa booking thất bại. Vui lòng thử lại.';
        }
      }
    };

    const formatDate = (date) => {
      if (!date) return '-';
      try {
        return new Date(date).toLocaleDateString('vi-VN');
      } catch (error) {
        console.error('Lỗi định dạng ngày:', error);
        return '-';
      }
    };

    const formatStatus = (status) => {
      return (
        {
          pending: 'Chờ xác nhận',
          confirmed: 'Đã xác nhận',
          cancelled: 'Đã hủy',
        }[status] ||
        status ||
        '-'
      );
    };

    const formatPrice = (value) => {
      return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND',
      }).format(value || 0);
    };

    return {
      bookings,
      tuKhoa,
      locTheoTrangThai,
      laModalMo,
      bookingDangSua,
      hasStatus,
      successMessage,
      bookingLoc,
      bookingHienThi,
      trangHienTai,
      soBookingMoiTrang,
      tongSoTrang,
      trangHienThi,
      form,
      rooms,
      tables,
      menus,
      isEditing,
      isLoading,
      errorMessage,
      errors,
      minDate,
      minCheckOutDate,
      minTime,
      availableRooms,
      availableTables,
      moModalThem,
      moModalSua,
      dongModal,
      onTypeChange,
      onQuantityChange,
      submitBooking,
      confirmBooking,
      xoaBooking,
      resetForm,
      loadBookingToForm,
      formatDate,
      formatStatus,
      formatPrice,
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
  background: linear-gradient(135deg, #3f8dd6, #2acabd);
  color: white;
  font-weight: 600;
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
  background: linear-gradient(135deg, #3f8dd6, #2acabd);
  color: white;
  border-bottom: none;
  font-weight: 700;
}

.modal-footer {
  border-top: none;
}

.btn-close {
  filter: invert(1);
  opacity: 0.8;
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

.btn-secondary {
  background: linear-gradient(135deg, #6c757d, #5c636a);
  border: none;
  font-weight: 600;
  transition: transform 0.2s, box-shadow 0.2s;
}

.btn-secondary:hover {
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
  border: none;
  border-radius: 8px;
  padding: 8px 12px;
  font-weight: 500;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.pagination .page-item.active .page-link {
  background: linear-gradient(135deg, #1199f3, #2acabd);
  color: white;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.pagination .page-item.disabled .page-link {
  background: #f8f9fa;
  color: #6c757d;
  cursor: not-allowed;
  box-shadow: none;
}

.pagination .page-item .page-link:hover:not(.disabled) {
  background: linear-gradient(135deg, #2acabd, #1199f3);
  color: white;
  transform: translateY(-2px);
}

.form-control.is-invalid,
.form-select.is-invalid {
  border-color: #dc3545;
}

option:disabled {
  color: #6c757d;
  background-color: #f8f9fa;
}
</style>