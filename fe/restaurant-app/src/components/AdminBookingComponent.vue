<template>
  <div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="fw-bold text-sea-green">Quản Lý Booking</h1>
      <button class="btn btn-success shadow" @click="moModalThem">
        <i class="bi bi-plus-circle me-2"></i>Thêm Booking Mới
      </button>
    </div>
    <div v-if="successMessage" class="alert alert-success">{{ successMessage }}</div>
    <div v-if="errorMessage" class="alert alert-danger">{{ errorMessage }}</div>

    <div class="row mb-4 g-3">
      <div class="col-md-4">
        <input
          v-model="tuKhoa"
          type="text"
          class="form-control"
          placeholder="Tìm theo tên hoặc số điện thoại"
        />
      </div>
      <div class="col-md-3">
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
            <th>Check-in</th>
            <th>Check-out</th>
            <th>Ngày Đặt</th>
            <th>Giờ Đặt</th>
            <th>Số Lượng</th>
            <th>Phòng</th>
            <th>Bàn</th>
            <th>Món</th>
            <th>Trạng Thái</th>
            <th>Hành Động</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(booking, index) in bookingHienThi" :key="booking.booking_id">
            <td>{{ (trangHienTai - 1) * soBookingMoiTrang + index + 1 }}</td>
            <td>{{ booking.customer_name || '-' }}</td>
            <td>{{ booking.customer_phone || '-' }}</td>
            <td>{{ booking.booking_type === 'room' ? 'Phòng' : 'Bàn' }}</td>
            <td>{{ formatDate(booking.check_in_date) }}</td>
            <td>{{ formatDate(booking.check_out_date) }}</td>
            <td>{{ formatDate(booking.booking_date) }}</td>
            <td>{{ booking.booking_time ? booking.booking_time.slice(0, 5) : '-' }}</td>
            <td>{{ booking.quantity || '-' }}</td>
            <td>{{ booking.room ? booking.room.room_name : '-' }}</td>
            <td>{{ booking.table ? booking.table.table_name : '-' }}</td>
            <td>{{ booking.menu ? booking.menu.menu_name : '-' }}</td>
            <td>
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
              <button
                v-if="booking.status === 'confirmed'"
                class="btn btn-warning btn-sm me-2"
                @click="cancelBooking(booking.booking_id)"
              >
                <i class="bi bi-x-circle me-1"></i>Hủy
              </button>
              <button class="btn btn-primary btn-sm me-2" @click="moModalSua(booking)">
                <i class="bi bi-pencil me-1"></i>Sửa
              </button>
              <button class="btn btn-danger btn-sm" @click="xoaBooking(booking.booking_id)">
                <i class="bi bi-trash me-1"></i>Xóa
              </button>
            </td>
          </tr>
          <tr v-if="bookingHienThi.length === 0">
            <td colspan="14" class="text-center text-muted">Không có booking nào phù hợp</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-4">
      <div class="text-muted">
        Hiển thị {{ bookingHienThi.length }} / {{ bookingLoc.length }} booking
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

    <div v-if="laModalMo" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5)">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ isEditing ? 'Cập nhật Booking' : 'Thêm Booking' }}</h5>
            <button type="button" class="btn-close" @click="dongModal"></button>
          </div>
          <div class="modal-body">
            <div v-if="isLoading" class="text-center mb-3">
              <div class="spinner-border text-primary">
                <span class="visually-hidden">Đang tải...</span>
              </div>
            </div>
            <div v-if="errorMessage" class="alert alert-warning">
              {{ errorMessage }}
            </div>
            <form @submit.prevent="submitBooking">
              <div class="mb-3">
                <label for="bookingType" class="form-label">Loại Booking</label>
                <select
                  id="bookingType"
                  class="form-control"
                  v-model="form.booking_type"
                  required
                  :class="{ 'is-invalid': errors.booking_type }"
                  @change="onTypeChange"
                >
                  <option value="">-- Chọn loại --</option>
                  <option value="room">Phòng</option>
                  <option value="table">Bàn</option>
                </select>
                <div v-if="errors.booking_type" class="invalid-feedback">{{ errors.booking_type }}</div>
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
                <div v-if="errors.customer_name" class="invalid-feedback">{{ errors.customer_name }}</div>
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
                <div v-if="errors.customer_phone" class="invalid-feedback">{{ errors.customer_phone }}</div>
              </div>
              <div class="row mb-3" v-if="form.booking_type === 'room'">
                <div class="col">
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
                  <div v-if="errors.check_in_date" class="invalid-feedback">{{ errors.check_in_date }}</div>
                </div>
                <div class="col">
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
                  <div v-if="errors.check_out_date" class="invalid-feedback">{{ errors.check_out_date }}</div>
                </div>
              </div>
              <div class="row mb-3" v-if="form.booking_type === 'room' || form.booking_type === 'table'">
                <div class="col">
                  <label for="bookingDate" class="form-label">Ngày đặt</label>
                  <input
                    type="date"
                    id="bookingDate"
                    class="form-control"
                    v-model="form.booking_date"
                    required
                    :min="minDate"
                    :class="{ 'is-invalid': errors.booking_date }"
                  />
                  <div v-if="errors.booking_date" class="invalid-feedback">{{ errors.booking_date }}</div>
                </div>
                <div class="col">
                  <label for="bookingTime" class="form-label">Giờ đặt</label>
                  <input
                    type="time"
                    id="bookingTime"
                    class="form-control"
                    v-model="form.booking_time"
                    required
                    :min="minTime"
                    :class="{ 'is-invalid': errors.booking_time }"
                  />
                  <div v-if="errors.booking_time" class="invalid-feedback">{{ errors.booking_time }}</div>
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
                  min="1"
                  placeholder="Nhập số lượng khách"
                  :class="{ 'is-invalid': errors.quantity }"
                  @input="onQuantityChange"
                />
                <div v-if="errors.quantity" class="invalid-feedback">{{ errors.quantity }}</div>
              </div>
              <div class="mb-3" v-if="form.booking_type === 'room'">
                <label for="selectRoom" class="form-label">Chọn phòng</label>
                <select
                  id="selectRoom"
                  class="form-select"
                  v-model="form.room_id"
                  :disabled="isLoading || !availableRooms.length"
                  :class="{ 'is-invalid': errors.room_id }"
                >
                  <option :value="null">-- {{ availableRooms.length ? 'Chọn phòng' : 'Không có phòng' }} --</option>
                  <option
                    v-for="room in availableRooms"
                    :key="room.room_id"
                    :value="room.room_id"
                    :disabled="!isEditing && room.status !== 'Trống'"
                  >
                    {{ room.room_name }} (Sức chứa: {{ room.capacity }}) {{ room.status !== 'Trống' ? `(${room.status})` : '' }}
                  </option>
                </select>
                <div v-if="errors.room_id" class="invalid-feedback">{{ errors.room_id }}</div>
              </div>
              <div class="mb-3" v-if="form.booking_type === 'table'">
                <label for="selectTable" class="form-label">Chọn bàn</label>
                <select
                  id="selectTable"
                  class="form-select"
                  v-model="form.table_id"
                  :disabled="isLoading || !availableTables.length"
                  :class="{ 'is-invalid': errors.table_id }"
                >
                  <option :value="null">-- {{ availableTables.length ? 'Chọn bàn' : 'Không có bàn' }} --</option>
                  <option
                    v-for="table in availableTables"
                    :key="table.table_id"
                    :value="table.table_id"
                    :disabled="!isEditing && table.status !== 'Trống'"
                  >
                    {{ table.table_name }} (Sức chứa: {{ table.capacity }}) {{ table.status !== 'Trống' ? `(${table.status})` : '' }}
                  </option>
                </select>
                <div v-if="errors.table_id" class="invalid-feedback">{{ errors.table_id }}</div>
              </div>
              <div class="mb-3">
                <label for="selectMenu" class="form-label">Chọn món ăn</label>
                <select
                  id="selectMenu"
                  class="form-select"
                  v-model="form.menu_id"
                  :disabled="isLoading || !menus.length"
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
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" @click="dongModal">Hủy</button>
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
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const retryRequest = async (fn, retries = 2, delay = 1000) => {
  for (let i = 0; i < retries; i++) {
    try {
      return await fn();
    } catch (error) {
      if (i === retries - 1 || error.code !== 'ECONNABORTED') throw error;
      await new Promise(resolve => setTimeout(resolve, delay));
    }
  }
};

const api = axios.create({
  baseURL: 'http://localhost:8000/api',
  timeout: 10000,
  headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }
});

export default {
  name: 'AdminBookingComponent',
  setup() {
    const bookings = ref([]);
    const tuKhoa = ref('');
    const locTheoTrangThai = ref('');
    const laModalMo = ref(false);
    const isEditing = ref(false);
    const isLoading = ref(false);
    const successMessage = ref('');
    const errorMessage = ref('');
    const trangHienTai = ref(1);
    const soBookingMoiTrang = 10;

    const form = ref({
      booking_id: null,
      booking_type: '',
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
      status: 'pending'
    });

    const errors = ref({
      booking_type: '',
      customer_name: '',
      customer_phone: '',
      check_in_date: '',
      check_out_date: '',
      booking_date: '',
      booking_time: '',
      quantity: '',
      room_id: '',
      table_id: ''
    });

    const rooms = ref([]);
    const tables = ref([]);
    const menus = ref([]);

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
        room => room.capacity >= form.value.quantity || (isEditing.value && room.room_id === form.value.room_id)
      );
    });

    const availableTables = computed(() => {
      if (!form.value.quantity) return tables.value;
      return tables.value.filter(
        table => table.capacity >= form.value.quantity || (isEditing.value && table.table_id === form.value.table_id)
      );
    });

    const bookingLoc = computed(() => {
      return bookings.value.filter(b => {
        const khopTim =
          (b.customer_name?.toLowerCase() || '').includes(tuKhoa.value.toLowerCase()) ||
          (b.customer_phone || '').includes(tuKhoa.value);
        const khopTrangThai = !locTheoTrangThai.value || b.status === locTheoTrangThai.value;
        return khopTim && khopTrangThai;
      });
    });

    const tongSoTrang = computed(() => Math.ceil(bookingLoc.value.length / soBookingMoiTrang));

    const bookingHienThi = computed(() => {
      const batDau = (trangHienTai.value - 1) * soBookingMoiTrang;
      const ketThuc = batDau + soBookingMoiTrang;
      return bookingLoc.value.slice(batDau, ketThuc);
    });

    const trangHienThi = computed(() => {
      const soTrang = tongSoTrang.value;
      const maxSoTrangHienThi = 5;
      let batDau = Math.max(1, trangHienTai.value - Math.floor(maxSoTrangHienThi / 2));
      let ketThuc = Math.min(soTrang, batDau + maxSoTrangHienThi - 1);
      if (ketThuc - batDau + 1 < maxSoTrangHienThi) {
        batDau = Math.max(1, ketThuc - maxSoTrangHienThi + 1);
      }
      return Array.from({ length: ketThuc - batDau + 1 }, (_, i) => batDau + i);
    });

    const fetchBookings = async () => {
      try {
        const res = await retryRequest(() => api.get('/bookings', { params: { _t: new Date().getTime() } }));
        bookings.value = Array.isArray(res.data.data) ? res.data.data : Array.isArray(res.data) ? res.data : [];
        console.log('Đã tải bookings:', bookings.value.map(b => ({
          id: b.booking_id,
          status: b.status
        })));
      } catch (error) {
        console.error('Lỗi tải bookings:', error.response?.data || error.message);
        errorMessage.value = 'Không thể tải danh sách booking.';
      }
    };

    const fetchFormData = async () => {
      isLoading.value = true;
      try {
        const [roomsRes, tablesRes, menusRes] = await Promise.all([
          retryRequest(() => api.get('/rooms', { params: { _t: new Date().getTime() } })),
          retryRequest(() => api.get('/tables', { params: { _t: new Date().getTime() } })),
          retryRequest(() => api.get('/menus', { params: { _t: new Date().getTime() } }))
        ]);
        rooms.value = Array.isArray(roomsRes.data.data) ? roomsRes.data.data : roomsRes.data;
        tables.value = Array.isArray(tablesRes.data.data) ? tablesRes.data.data : tablesRes.data;
        menus.value = Array.isArray(menusRes.data.data) ? menusRes.data.data : menusRes.data;
        console.log('Đã tải formData:', {
          rooms: rooms.value.map(r => ({
            id: r.room_id,
            name: r.room_name,
            status: r.status
          })),
          tables: tables.value.map(t => ({
            id: t.table_id,
            name: t.table_name,
            status: t.status
          })),
          menus: menus.value.length
        });
      } catch (error) {
        console.error('Lỗi tải formData:', error.response?.data || error.message);
        errorMessage.value = 'Không thể tải dữ liệu phòng, bàn hoặc món ăn.';
      } finally {
        isLoading.value = false;
      }
    };

    onMounted(() => {
      fetchBookings();
      fetchFormData();
    });

    const moModalThem = () => {
      resetForm();
      isEditing.value = false;
      laModalMo.value = true;
      successMessage.value = '';
    };

    const moModalSua = (booking) => {
      const now = new Date();
      form.value = {
        booking_id: booking.booking_id,
        booking_type: booking.booking_type,
        customer_name: booking.customer_name,
        customer_phone: booking.customer_phone,
        check_in_date: booking.check_in_date ? booking.check_in_date.split('T')[0] : now.toISOString().split('T')[0],
        check_out_date: booking.check_out_date ? booking.check_out_date.split('T')[0] : '',
        booking_date: booking.booking_date ? booking.booking_date.split('T')[0] : now.toISOString().split('T')[0],
        booking_time: booking.booking_time ? booking.booking_time.slice(0, 5) : now.toTimeString().slice(0, 5),
        quantity: booking.quantity,
        room_id: booking.room_id,
        table_id: booking.table_id,
        menu_id: booking.menu_id,
        note: booking.note,
        status: booking.status
      };
      isEditing.value = true;
      laModalMo.value = true;
      successMessage.value = '';
    };

    const dongModal = () => {
      laModalMo.value = false;
      resetForm();
      successMessage.value = '';
      errorMessage.value = '';
    };

    const resetForm = () => {
      const now = new Date();
      form.value = {
        booking_id: null,
        booking_type: '',
        customer_name: '',
        customer_phone: '',
        check_in_date: now.toISOString().split('T')[0],
        check_out_date: '',
        booking_date: now.toISOString().split('T')[0],
        booking_time: now.toTimeString().slice(0, 5),
        quantity: 1,
        room_id: null,
        table_id: null,
        menu_id: null,
        note: '',
        status: 'pending'
      };
      errors.value = {};
    };

    const onTypeChange = () => {
      form.value.room_id = null;
      form.value.table_id = null;
      form.value.check_in_date = form.value.booking_type === 'room' ? minDate.value : '';
      form.value.check_out_date = '';
      errors.value = {};
    };

    const onQuantityChange = () => {
      if (form.value.room_id) {
        const room = rooms.value.find(r => r.room_id === form.value.room_id);
        if (room && room.capacity < form.value.quantity) {
          form.value.room_id = null;
          errors.value.room_id = 'Số lượng khách vượt quá sức chứa của phòng';
        }
      }
      if (form.value.table_id) {
        const table = tables.value.find(t => t.table_id === form.value.table_id);
        if (table && table.capacity < form.value.quantity) {
          form.value.table_id = null;
          errors.value.table_id = 'Số lượng khách vượt quá sức chứa của bàn';
        }
      }
    };

    const validateForm = () => {
      errors.value = {};
      let isValid = true;

      if (!form.value.booking_type) {
        errors.value.booking_type = 'Vui lòng chọn loại booking';
        isValid = false;
      }
      if (!form.value.customer_name?.trim()) {
        errors.value.customer_name = 'Vui lòng nhập tên khách hàng';
        isValid = false;
      }
      if (!form.value.customer_phone?.trim()) {
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
      if (!form.value.booking_date) {
        errors.value.booking_date = 'Vui lòng chọn ngày đặt';
        isValid = false;
      }
      if (!form.value.booking_time) {
        errors.value.booking_time = 'Vui lòng chọn giờ đặt';
        isValid = false;
      }
      const bookingDateTime = form.value.booking_date && form.value.booking_time
        ? new Date(`${form.value.booking_date}T${form.value.booking_time}`)
        : null;
      const now = new Date();
      now.setSeconds(0, 0);
      if (bookingDateTime && bookingDateTime < now) {
        errors.value.booking_date = 'Thời gian đặt phải từ hiện tại trở đi';
        isValid = false;
      }

      if (form.value.booking_type === 'room') {
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
          errors.value.room_id = 'Vui lòng chọn phòng';
          isValid = false;
        }
        if (form.value.room_id && !isEditing.value) {
          const room = rooms.value.find(r => r.room_id === form.value.room_id);
          if (room && room.status !== 'Trống') {
            errors.value.room_id = 'Phòng đã được đặt hoặc đang chờ';
            isValid = false;
          }
          if (room && room.capacity < form.value.quantity) {
            errors.value.room_id = 'Số lượng khách vượt quá sức chứa';
            isValid = false;
          }
        }
      } else if (form.value.booking_type === 'table') {
        if (!form.value.table_id) {
          errors.value.table_id = 'Vui lòng chọn bàn';
          isValid = false;
        }
        if (form.value.table_id && !isEditing.value) {
          const table = tables.value.find(t => t.table_id === form.value.table_id);
          if (table && table.status !== 'Trống') {
            errors.value.table_id = 'Bàn đã được đặt hoặc đang chờ';
            isValid = false;
          }
          if (table && table.capacity < form.value.quantity) {
            errors.value.table_id = 'Số lượng khách vượt quá sức chứa';
            isValid = false;
          }
        }
      }

      return isValid;
    };

    const submitBooking = async () => {
      if (!validateForm()) return;

      isLoading.value = true;
      errorMessage.value = '';

      try {
        const payload = {
          booking_type: form.value.booking_type,
          customer_name: form.value.customer_name,
          customer_phone: form.value.customer_phone,
          check_in_date: form.value.booking_type === 'room' ? form.value.check_in_date : null,
          check_out_date: form.value.booking_type === 'room' ? form.value.check_out_date : null,
          booking_date: form.value.booking_date,
          booking_time: form.value.booking_time ? `${form.value.booking_time}:00` : null,
          quantity: form.value.quantity,
          room_id: form.value.booking_type === 'room' ? form.value.room_id : null,
          table_id: form.value.booking_type === 'table' ? form.value.table_id : null,
          menu_id: form.value.menu_id,
          note: form.value.note,
          status: isEditing.value ? form.value.status : 'pending'
        };

        if (isEditing.value) {
          await retryRequest(() => api.put(`/bookings/${form.value.booking_id}`, payload));
          successMessage.value = 'Cập nhật booking thành công';
        } else {
          await retryRequest(() => api.post('/bookings', payload));
          successMessage.value = 'Thêm booking mới thành công';
        }

        await fetchBookings();
        await fetchFormData();
        dongModal();
      } catch (error) {
        console.error('Lỗi khi lưu booking:', error.response?.data || error.message);
        errorMessage.value = error.response?.data?.message || 'Lưu booking thất bại';
        if (error.response?.status === 422) {
          const backendErrors = error.response.data.errors || {};
          errorMessage.value += ': ' + Object.values(backendErrors).flat().join(', ');
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
        const booking = bookings.value.find(b => b.booking_id === bookingId);
        if (!booking) throw new Error('Không tìm thấy booking');
        console.log('Trước khi xác nhận:', {
          bookingId,
          bookingType: booking.booking_type,
          roomId: booking.room_id,
          tableId: booking.table_id
        });

        const patchResponse = await retryRequest(() => api.patch(`/bookings/${bookingId}/confirm`, { status: 'confirmed' }));
        console.log('Response từ PATCH /bookings/confirm:', patchResponse.data);

        if (patchResponse.data.data?.status !== 'confirmed') {
          throw new Error('Server không cập nhật trạng thái booking thành confirmed');
        }

        booking.status = 'confirmed';
        console.log('Đã cập nhật trạng thái cục bộ booking sang confirmed');

        await new Promise(resolve => setTimeout(resolve, 1000));
        await fetchBookings();
        await fetchFormData();

        if (booking.booking_type === 'room' && booking.room_id) {
          const room = rooms.value.find(r => r.room_id === booking.room_id);
          console.log('Trạng thái phòng sau xác nhận:', {
            roomId: booking.room_id,
            roomName: room?.room_name,
            status: room?.status ?? 'Không tìm thấy'
          });
          if (room?.status !== 'Đã đặt') {
            errorMessage.value = 'Cảnh báo: Trạng thái phòng chưa được cập nhật thành Đã đặt';
          }
        } else if (booking.booking_type === 'table' && booking.table_id) {
          const table = tables.value.find(t => t.table_id === booking.table_id);
          console.log('Trạng thái bàn sau xác nhận:', {
            tableId: booking.table_id,
            tableName: table?.table_name,
            status: table?.status ?? 'Không tìm thấy'
          });
          if (table?.status !== 'Đã đặt') {
            errorMessage.value = 'Cảnh báo: Trạng thái bàn chưa được cập nhật thành Đã đặt';
          }
        }

        successMessage.value = 'Xác nhận booking thành công';
      } catch (error) {
        console.error('Lỗi khi xác nhận booking:', error.response?.data || error.message);
        errorMessage.value = error.response?.data?.message || 'Xác nhận booking thất bại';
        if (error.response?.status === 422) {
          const errors = error.response.data.errors || {};
          errorMessage.value += ': ' + Object.values(errors).flat().join(', ');
        }
      } finally {
        isLoading.value = false;
      }
    };

    const cancelBooking = async (bookingId) => {
      if (!confirm('Bạn có chắc muốn hủy booking này không?')) return;

      isLoading.value = true;
      errorMessage.value = '';
      try {
        const booking = bookings.value.find(b => b.booking_id === bookingId);
        if (!booking) throw new Error('Không tìm thấy booking');
        console.log('Trước khi hủy:', {
          bookingId,
          bookingType: booking.booking_type,
          bookingStatus: booking.status,
          roomId: booking.room_id,
          tableId: booking.table_id
        });

        // Gửi yêu cầu hủy booking
        const patchResponse = await retryRequest(() => api.patch(`/bookings/${bookingId}/confirm`, { status: 'cancelled' }));
        console.log('Response từ PATCH /bookings (cancel):', patchResponse.data);

        if (patchResponse.data.data?.status !== 'cancelled') {
          throw new Error('Server không cập nhật trạng thái thành cancelled');
        }

        // Cập nhật trạng thái cục bộ
        booking.status = 'cancelled';
        console.log('Đã cập nhật trạng thái cục bộ booking sang cancelled');

        // Trì hoãn để server xử lý cập nhật room/table
        await new Promise(resolve => setTimeout(resolve, 1000));
        await fetchBookings();
        await fetchFormData();

        // Kiểm tra trạng thái room/table sau khi hủy
        if (booking.booking_type === 'room' && booking.room_id) {
          const room = rooms.value.find(r => r.room_id === booking.room_id);
          console.log('Trạng thái phòng sau hủy:', {
            roomId: booking.room_id,
            roomName: room?.room_name,
            status: room?.status ?? 'Không tìm thấy'
          });
          if (room?.status !== 'Trống') {
            errorMessage.value = 'Cảnh báo: Trạng thái phòng chưa được cập nhật thành Trống';
          }
        } else if (booking.booking_type === 'table' && booking.table_id) {
          const table = tables.value.find(t => t.table_id === booking.table_id);
          console.log('Trạng thái bàn sau hủy:', {
            tableId: booking.table_id,
            tableName: table?.table_name,
            status: table?.status ?? 'Không tìm thấy'
          });
          if (table?.status !== 'Trống') {
            errorMessage.value = 'Cảnh báo: Trạng thái bàn chưa được cập nhật thành Trống';
          }
        }

        successMessage.value = 'Hủy booking thành công';
      } catch (error) {
        console.error('Lỗi khi hủy booking:', error.response?.data || error.message);
        errorMessage.value = error.response?.data?.message || 'Hủy booking thất bại';
        if (error.response?.status === 422) {
          const backendErrors = error.response.data.errors || {};
          errorMessage.value += ': ' + Object.values(backendErrors).flat().join(', ');
        }
      } finally {
        isLoading.value = false;
      }
    };

    const xoaBooking = async (bookingId) => {
      if (!confirm('Bạn có chắc chắn muốn xóa booking này không?')) return;

      isLoading.value = true;
      errorMessage.value = '';
      try {
        const booking = bookings.value.find(b => b.booking_id === bookingId);
        if (!booking) throw new Error('Không tìm thấy booking');

        if (booking.status === 'confirmed') {
          if (!confirm('Booking này đã được xác nhận. Bạn có muốn hủy trước khi xóa không?')) return;
          await cancelBooking(bookingId);
        }

        await retryRequest(() => api.delete(`/bookings/${bookingId}`));
        await fetchBookings();
        await fetchFormData();
        successMessage.value = 'Xóa booking thành công';
      } catch (error) {
        console.error('Lỗi khi xóa booking:', error.response?.data || error.message);
        errorMessage.value = error.response?.data?.message || 'Xóa booking thất bại';
      } finally {
        isLoading.value = false;
      }
    };

    const formatDate = (date) => {
      if (!date) return '-';
      try {
        const normalizedDate = date.includes('T') ? date.split('T')[0] : date;
        const d = new Date(normalizedDate);
        if (isNaN(d.getTime())) return date;
        return d.toLocaleDateString('vi-VN', {
          day: '2-digit',
          month: '2-digit',
          year: 'numeric'
        });
      } catch (error) {
        return '-';
      }
    };

    const formatStatus = (status) => {
      return { pending: 'Chờ xác nhận', confirmed: 'Đã xác nhận', cancelled: 'Đã hủy' }[status] || '-';
    };

    const formatPrice = (value) => {
      return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value || 0);
    };

    return {
      bookings,
      tuKhoa,
      locTheoTrangThai,
      laModalMo,
      isEditing,
      isLoading,
      successMessage,
      errorMessage,
      trangHienTai,
      soBookingMoiTrang,
      form,
      errors,
      rooms,
      tables,
      menus,
      minDate,
      minCheckOutDate,
      minTime,
      availableRooms,
      availableTables,
      bookingLoc,
      bookingHienThi,
      tongSoTrang,
      trangHienThi,
      moModalThem,
      moModalSua,
      dongModal,
      resetForm,
      onTypeChange,
      onQuantityChange,
      validateForm,
      submitBooking,
      confirmBooking,
      cancelBooking,
      xoaBooking,
      formatDate,
      formatStatus,
      formatPrice
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
.btn-warning {
  background: linear-gradient(135deg, #ffc107, #e0a800);
  border: none;
}
.btn-warning:hover {
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