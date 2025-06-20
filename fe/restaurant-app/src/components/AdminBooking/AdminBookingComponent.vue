<template>
 <Loading v-if="isLoading"/>
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
      <div class="col-md-3">
        <select v-model="locTheoLoai" class="form-select">
          <option value="">Tất cả loại</option>
          <option value="room">Phòng</option>
          <option value="table">Bàn</option>
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
            <th>Tổng Chi Phí</th>
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
            <td>{{ calculateTotalCost(booking) }}</td>
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
            <td colspan="15" class="text-center text-muted">Không có booking nào phù hợp</td>
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
      <div class="modal-dialog modal-xl">
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
              <div class="row mb-3">
                <div class="col-md-4">
                  <label for="bookingType" class="form-label">Loại Booking</label>
                  <select
                    id="bookingType"
                    class="form-select"
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
                <div class="col-md-4">
                  <label for="customerName" class="form-label">Tên Khách Hàng</label>
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
                <div class="col-md-4">
                  <label for="customerPhone" class="form-label">Số Điện Thoại</label>
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
              </div>
              <div class="row mb-3" v-if="form.booking_type === 'room'">
                <div class="col-md-4">
                  <label for="checkInDate" class="form-label">Ngày Check-in</label>
                  <input
                    type="date"
                    id="checkInDate"
                    class="form-control"
                    v-model="form.check_in_date"
                    required
                    :min="minDate"
                    :class="{ 'is-invalid': errors.check_in_date }"
                    @change="checkAvailability"
                  />
                  <div v-if="errors.check_in_date" class="invalid-feedback">{{ errors.check_in_date }}</div>
                </div>
                <div class="col-md-4">
                  <label for="checkOutDate" class="form-label">Ngày Check-out</label>
                  <input
                    type="date"
                    id="checkOutDate"
                    class="form-control"
                    v-model="form.check_out_date"
                    required
                    :min="form.check_in_date || minDate"
                    :class="{ 'is-invalid': errors.check_out_date }"
                    @change="checkAvailability"
                  />
                  <div v-if="errors.check_out_date" class="invalid-feedback">{{ errors.check_out_date }}</div>
                </div>
                <div class="col-md-4">
                  <label for="selectRoom" class="form-label">Chọn Phòng</label>
                  <select
                    id="selectRoom"
                    class="form-select"
                    v-model="form.room_id"
                    :disabled="isLoading || !availableRooms.length"
                    :class="{ 'is-invalid': errors.room_id }"
                    @change="calculatePricing"
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
              </div>
              <div class="row mb-3" v-if="form.booking_type === 'table'">
                <div class="col-md-4">
                  <label for="selectTable" class="form-label">Chọn Bàn</label>
                  <select
                    id="selectTable"
                    class="form-select"
                    v-model="form.table_id"
                    :disabled="isLoading || !availableTables.length"
                    :class="{ 'is-invalid': errors.table_id }"
                    @change="checkAvailability"
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
              </div>
              <div class="row mb-3">
                <div class="col-md-4">
                  <label for="bookingDate" class="form-label">Ngày Đặt</label>
                  <input
                    type="date"
                    id="bookingDate"
                    class="form-control"
                    v-model="form.booking_date"
                    required
                    :min="minDate"
                    :class="{ 'is-invalid': errors.booking_date }"
                    @change="checkAvailability"
                  />
                  <div v-if="errors.booking_date" class="invalid-feedback">{{ errors.booking_date }}</div>
                </div>
                <div class="col-md-4">
                  <label for="bookingTime" class="form-label">Giờ Đặt</label>
                  <input
                    type="time"
                    id="bookingTime"
                    class="form-control"
                    v-model="form.booking_time"
                    required
                    :min="minTime"
                    :class="{ 'is-invalid': errors.booking_time }"
                    @change="checkAvailability"
                  />
                  <div v-if="errors.booking_time" class="invalid-feedback">{{ errors.booking_time }}</div>
                </div>
                <div class="col-md-4">
                  <label for="quantity" class="form-label">Số Lượng Khách</label>
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
              </div>
              <div class="mb-3">
                <label for="selectMenu" class="form-label">Chọn Món Ăn</label>
                <select
                  id="selectMenu"
                  class="form-select"
                  v-model="form.menu_id"
                  :disabled="isLoading || !menus.length"
                  @change="calculateTotal"
                >
                  <option :value="null">-- Chọn món (tùy chọn) --</option>
                  <option v-for="menu in menus" :key="menu.menu_id" :value="menu.menu_id">
                    {{ menu.menu_name }} ({{ formatPrice(menu.price) }})
                  </option>
                </select>
              </div>
              <div class="mb-3" v-if="form.booking_type === 'room' && pricing.season">
                <label class="form-label">Thông Tin Giá Phòng</label>
                <div class="card bg-light p-3">
                  <div class="row">
                    <div class="col-md-3">
                      <p><strong>Giờ:</strong> {{ formatPrice(pricing.hourly) }} / giờ</p>
                    </div>
                    <div class="col-md-3">
                      <p><strong>Đêm:</strong> {{ formatPrice(pricing.nightly) }} / đêm</p>
                    </div>
                    <div class="col-md-3">
                      <p><strong>Ngày:</strong> {{ formatPrice(pricing.daily) }} / ngày</p>
                    </div>
                    <div class="col-md-3">
                      <p><strong>Giảm giá:</strong> {{ pricing.discount ? pricing.discount + '%' : '-' }}</p>
                    </div>
                  </div>
                  <p><strong>Mùa:</strong> {{ pricing.season.season_name }} ({{ formatDate(pricing.season.start_date) }} - {{ formatDate(pricing.season.end_date) }})</p>
                </div>
              </div>
              <div class="mb-3">
                <label for="note" class="form-label">Ghi Chú</label>
                <textarea
                  id="note"
                  rows="4"
                  class="form-control"
                  v-model="form.note"
                  placeholder="Nhập ghi chú nếu có"
                ></textarea>
              </div>
              <div class="mb-3" v-if="totalCost > 0">
                <label class="form-label">Tổng Chi Phí Dự Kiến</label>
                <div class="alert alert-info">
                  <strong>{{ formatPrice(totalCost) }}</strong>
                </div>
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
<<<<<<< HEAD
import Loading from '../loading.vue';
=======
// import Loading from './loading.vue';
>>>>>>> beea7485f86dda5fc71cf67082462a6c0940c4eb
// Hàm retry cho các yêu cầu API
const retryRequest = async (fn, retries = 3, delay = 1000) => {
  for (let i = 0; i < retries; i++) {
    try {
      return await fn();
    } catch (error) {
      if (i === retries - 1 || error.code !== 'ECONNABORTED') throw error;
      await new Promise(resolve => setTimeout(resolve, delay));
    }
  }
};

// Cấu hình Axios
const api = axios.create({
  baseURL: 'http://127.0.0.1:8000/api',
  timeout: 15000,
  headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }
});

export default {
  name: 'AdminBookingComponent',
  setup() {
    // State
    const bookings = ref([]);
    const rooms = ref([]);
    const tables = ref([]);
    const menus = ref([]);
    const seasons = ref([]);
    const tuKhoa = ref('');
    const locTheoTrangThai = ref('');
    const locTheoLoai = ref('');
    const laModalMo = ref(false);
    const isEditing = ref(false);
    const isLoading = ref(false);
    const successMessage = ref('');
    const errorMessage = ref('');
    const trangHienTai = ref(1);
    const soBookingMoiTrang = 10;
    const totalCost = ref(0);

    // Form data
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

    // Pricing
    const pricing = ref({
      hourly: null,
      nightly: null,
      daily: null,
      season: null,
      discount: null
    });

    // Errors
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

    // Computed properties
    const minDate = computed(() => {
      const today = new Date();
      return today.toISOString().split('T')[0];
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
      const result = bookings.value.filter(b => {
        const khopTim =
          (b.customer_name?.toLowerCase() || '').includes(tuKhoa.value.toLowerCase()) ||
          (b.customer_phone || '').includes(tuKhoa.value);
        const khopTrangThai = !locTheoTrangThai.value || b.status === locTheoTrangThai.value;
        const khopLoai = !locTheoLoai.value || b.booking_type === locTheoLoai.value;
        return khopTim && khopTrangThai && khopLoai;
      });
      console.log('Booking lọc:', result.map(b => ({
        id: b.booking_id,
        type: b.booking_type,
        status: b.status
      })));
      return result;
    });

    const tongSoTrang = computed(() => Math.ceil(bookingLoc.value.length / soBookingMoiTrang));

    const bookingHienThi = computed(() => {
      const batDau = (trangHienTai.value - 1) * soBookingMoiTrang;
      const ketThuc = batDau + soBookingMoiTrang;
      const result = bookingLoc.value.slice(batDau, ketThuc);
      console.log('Booking hiển thị:', result.map(b => ({
        id: b.booking_id,
        type: b.booking_type,
        status: b.status
      })));
      return result;
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

    // Methods
    const fetchData = async () => {
      isLoading.value = true;
      errorMessage.value = '';
      try {
        const [bookingsRes, roomsRes, tablesRes, menusRes, seasonsRes] = await Promise.all([
          retryRequest(() => api.get('/bookings', { params: { _t: new Date().getTime() } })),
          retryRequest(() => api.get('/rooms', { params: { _t: new Date().getTime() } })),
          retryRequest(() => api.get('/tables', { params: { _t: new Date().getTime() } })),
          retryRequest(() => api.get('/menus', { params: { _t: new Date().getTime() } })),
          retryRequest(() => api.get('/seasons', { params: { _t: new Date().getTime() } }))
        ]);
        bookings.value = Array.isArray(bookingsRes.data.data) ? bookingsRes.data.data : Array.isArray(bookingsRes.data) ? bookingsRes.data : [];
        rooms.value = Array.isArray(roomsRes.data.data) ? roomsRes.data.data : Array.isArray(roomsRes.data) ? roomsRes.data : [];
        tables.value = Array.isArray(tablesRes.data.data) ? tablesRes.data.data : Array.isArray(tablesRes.data) ? tablesRes.data : [];
        menus.value = Array.isArray(menusRes.data.data) ? menusRes.data.data : Array.isArray(menusRes.data) ? menusRes.data : [];
        seasons.value = Array.isArray(seasonsRes.data.data) ? seasonsRes.data.data : Array.isArray(seasonsRes.data) ? seasonsRes.data : [];
        console.log('Dữ liệu đã tải:', {
          bookings: bookings.value.length,
          rooms: rooms.value.map(r => ({ id: r.room_id, name: r.room_name, status: r.status, daily_rate: r.season?.daily_rate })),
          tables: tables.value.map(t => ({ id: t.table_id, name: t.table_name, status: t.status })),
          menus: menus.value.map(m => ({ id: m.menu_id, name: m.menu_name, price: m.price })),
          seasons: seasons.value.length
        });
        if (!bookings.value.length) {
          errorMessage.value = 'Không có booking nào trong hệ thống. Vui lòng thêm booking mới.';
        }
      } catch (error) {
        console.error('Lỗi tải dữ liệu:', error.response?.data || error.message);
        errorMessage.value = error.response?.data?.message || 'Không thể tải dữ liệu. Vui lòng kiểm tra kết nối backend.';
      } finally {
        isLoading.value = false;
      }
    };

    const updateRoomStatus = async (roomId, status) => {
      try {
        await retryRequest(() => api.patch(`/rooms/${roomId}`, { status }));
        console.log(`Cập nhật trạng thái phòng ${roomId} thành ${status}`);
      } catch (error) {
        console.error('Lỗi cập nhật trạng thái phòng:', error.response?.data || error.message);
        errorMessage.value = errorMessage.value || 'Cảnh báo: Không thể cập nhật trạng thái phòng.';
      }
    };

    const updateTableStatus = async (tableId, status) => {
      try {
        await retryRequest(() => api.patch(`/tables/${tableId}`, { status }));
        console.log(`Cập nhật trạng thái bàn ${tableId} thành ${status}`);
      } catch (error) {
        console.error('Lỗi cập nhật trạng thái bàn:', error.response?.data || error.message);
        errorMessage.value = errorMessage.value || 'Cảnh báo: Không thể cập nhật trạng thái bàn.';
      }
    };

    const checkAvailability = async () => {
      if (!form.value.booking_date || !form.value.booking_time || (!form.value.room_id && !form.value.table_id)) return;

      isLoading.value = true;
      errors.value.room_id = '';
      errors.value.table_id = '';
      errorMessage.value = '';
      try {
        const payload = {
          booking_date: form.value.booking_date,
          booking_time: form.value.booking_time + ':00',
          check_in_date: form.value.check_in_date,
          check_out_date: form.value.check_out_date,
          room_id: form.value.room_id,
          table_id: form.value.table_id,
          booking_id: isEditing.value ? form.value.booking_id : null
        };
        const res = await retryRequest(() => api.post('/bookings/check-availability', payload));
        if (!res.data.available) {
          if (form.value.room_id) {
            errors.value.room_id = res.data.message || 'Phòng không khả dụng trong khoảng thời gian này';
            form.value.room_id = null;
          } else if (form.value.table_id) {
            errors.value.table_id = res.data.message || 'Bàn không khả dụng trong khoảng thời gian này';
            form.value.table_id = null;
          }
        }
      } catch (error) {
        console.error('Lỗi kiểm tra lịch:', error.response?.data || error.message);
        errorMessage.value = error.response?.data?.message || 'Không thể kiểm tra lịch trống';
      } finally {
        isLoading.value = false;
      }
    };

    const calculatePricing = () => {
      pricing.value = { hourly: null, nightly: null, daily: null, season: null, discount: null };
      totalCost.value = 0;
      if (form.value.booking_type !== 'room' || !form.value.room_id || !form.value.check_in_date || !form.value.check_out_date) return;

      const room = rooms.value.find(r => r.room_id === form.value.room_id);
      if (!room || !room.season) {
        console.error('Không tìm thấy phòng hoặc thông tin mùa:', form.value.room_id, room);
        errorMessage.value = 'Không thể lấy thông tin giá phòng';
        return;
      }

      pricing.value = {
        hourly: Number(room.season.hourly_rate) || 0,
        nightly: Number(room.season.nightly_rate) || 0,
        daily: Number(room.season.daily_rate) || 0,
        season: room.season,
        discount: Number(room.season.discount) || 0
      };

      console.log('Thông tin giá:', {
        hourly: pricing.value.hourly,
        nightly: pricing.value.nightly,
        daily: pricing.value.daily,
        discount: pricing.value.discount,
        season: room.season.season_name
      });

      // Kiểm tra giá trị hợp lệ
      if (pricing.value.daily < 0 || pricing.value.daily > 10000000) {
        console.error('Giá ngày không hợp lệ:', pricing.value.daily);
        errorMessage.value = 'Giá phòng không hợp lý, vui lòng kiểm tra dữ liệu';
        pricing.value.daily = 0;
      }
      if (pricing.value.discount < 0 || pricing.value.discount > 100) {
        console.error('Giảm giá không hợp lệ:', pricing.value.discount);
        errorMessage.value = 'Giảm giá không hợp lý, vui lòng kiểm tra dữ liệu';
        pricing.value.discount = 0;
      }

      calculateTotal();
    };

    const calculateTotal = () => {
      totalCost.value = 0;
      if (form.value.booking_type === 'room' && form.value.check_in_date && form.value.check_out_date && pricing.value.daily) {
        const checkIn = new Date(form.value.check_in_date);
        const checkOut = new Date(form.value.check_out_date);
        if (isNaN(checkIn.getTime()) || isNaN(checkOut.getTime())) {
          console.error('Ngày check-in hoặc check-out không hợp lệ:', form.value.check_in_date, form.value.check_out_date);
          errorMessage.value = 'Ngày check-in hoặc check-out không hợp lệ';
          return;
        }
        const days = Math.ceil((checkOut - checkIn) / (1000 * 60 * 60 * 24));
        if (days <= 0 || days > 365) {
          console.error('Số ngày không hợp lệ:', days);
          errorMessage.value = 'Khoảng thời gian đặt phòng không hợp lý';
          return;
        }
        console.log('Tính chi phí phòng:', {
          checkIn: form.value.check_in_date,
          checkOut: form.value.check_out_date,
          days: days,
          dailyRate: pricing.value.daily,
          discount: pricing.value.discount
        });
        let cost = days * pricing.value.daily;
        if (pricing.value.discount) {
          cost *= (100 - pricing.value.discount) / 100;
        }
        totalCost.value += cost;
      }
      if (form.value.menu_id) {
        const menu = menus.value.find(m => m.menu_id === form.value.menu_id);
        if (menu) {
          const menuPrice = Number(menu.price) || 0;
          if (menuPrice < 0 || menuPrice > 10000000) {
            console.error('Giá thực đơn không hợp lệ:', menu.price);
            errorMessage.value = 'Giá thực đơn không hợp lý, vui lòng kiểm tra dữ liệu';
          } else {
            console.log('Tính chi phí thực đơn:', { menu: menu.menu_name, price: menuPrice });
            totalCost.value += menuPrice;
          }
        } else {
          console.error('Không tìm thấy thực đơn:', form.value.menu_id);
        }
      }
      console.log('Tổng chi phí dự kiến:', totalCost.value);
    };

    const calculateTotalCost = (booking) => {
      let cost = 0;
      if (booking.booking_type === 'room' && booking.check_in_date && booking.check_out_date && booking.room?.season) {
        const checkIn = new Date(booking.check_in_date);
        const checkOut = new Date(booking.check_out_date);
        if (isNaN(checkIn.getTime()) || isNaN(checkOut.getTime())) {
          console.error('Ngày check-in hoặc check-out không hợp lệ:', booking.check_in_date, booking.check_out_date);
          return '-';
        }
        const days = Math.ceil((checkOut - checkIn) / (1000 * 60 * 60 * 24));
        if (days <= 0 || days > 365) {
          console.error('Số ngày không hợp lệ:', days);
          return '-';
        }
        const dailyRate = Number(booking.room.season.daily_rate) || 0;
        if (dailyRate < 0 || dailyRate > 10000000) {
          console.error('Giá ngày không hợp lệ:', dailyRate);
          return '-';
        }
        const discount = Number(booking.room.season.discount) || 0;
        if (discount < 0 || discount > 100) {
          console.error('Giảm giá không hợp lệ:', discount);
          return '-';
        }
        console.log('Tính chi phí phòng:', {
          checkIn: booking.check_in_date,
          checkOut: booking.check_out_date,
          days: days,
          dailyRate: dailyRate,
          discount: discount
        });
        let roomCost = days * dailyRate;
        if (discount) {
          roomCost *= (100 - discount) / 100;
        }
        cost += roomCost;
      }
      if (booking.menu) {
        const menuPrice = Number(booking.menu.price) || 0;
        if (menuPrice < 0 || menuPrice > 10000000) {
          console.error('Giá thực đơn không hợp lệ:', menuPrice);
          return '-';
        }
        console.log('Tính chi phí thực đơn:', { menu: booking.menu.menu_name, price: menuPrice });
        cost += menuPrice;
      }
      console.log('Tổng chi phí cho booking:', cost);
      return formatPrice(cost);
    };

    const moModalThem = () => {
      console.log('Mở modal thêm booking');
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
        check_in_date: booking.check_in_date ? booking.check_in_date.split('T')[0] : '',
        check_out_date: booking.check_out_date ? booking.check_out_date.split('T')[0] : '',
        booking_date: booking.booking_date ? booking.booking_date.split('T')[0] : now.toISOString().split('T')[0],
        booking_time: booking.booking_time ? booking.booking_time.slice(0, 5) : now.toTimeString().slice(0, 5),
        quantity: booking.quantity,
        room_id: booking.room_id,
        table_id: booking.table_id,
        menu_id: booking.menu_id,
        note: booking.note || '',
        status: booking.status
      };
      isEditing.value = true;
      laModalMo.value = true;
      successMessage.value = '';
      calculatePricing();
      calculateTotal();
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
        check_in_date: '',
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
      pricing.value = { hourly: null, nightly: null, daily: null, season: null, discount: null };
      totalCost.value = 0;
    };

    const onTypeChange = () => {
      form.value.room_id = null;
      form.value.table_id = null;
      form.value.check_in_date = form.value.booking_type === 'room' ? minDate.value : '';
      form.value.check_out_date = form.value.booking_type === 'room' ? minDate.value : '';
      errors.value = {};
      calculatePricing();
      calculateTotal();
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
      checkAvailability();
      calculateTotal();
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
        if (form.value.check_in_date && form.value.check_out_date && form.value.check_out_date < form.value.check_in_date) {
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
          status: form.value.status
        };

        if (isEditing.value) {
          await retryRequest(() => api.put(`/bookings/${form.value.booking_id}`, payload));
          // Cập nhật trạng thái phòng/bàn nếu trạng thái booking thay đổi
          if (form.value.booking_type === 'room' && form.value.room_id) {
            const room = rooms.value.find(r => r.room_id === form.value.room_id);
            if (form.value.status === 'confirmed' && room && room.status !== 'Đã đặt') {
              await updateRoomStatus(form.value.room_id, 'Đã đặt');
            } else if (form.value.status === 'cancelled' && room && room.status !== 'Trống') {
              await updateRoomStatus(form.value.room_id, 'Trống');
            }
          } else if (form.value.booking_type === 'table' && form.value.table_id) {
            const table = tables.value.find(t => t.table_id === form.value.table_id);
            if (form.value.status === 'confirmed' && table && table.status !== 'Đã đặt') {
              await updateTableStatus(form.value.table_id, 'Đã đặt');
            } else if (form.value.status === 'cancelled' && table && table.status !== 'Trống') {
              await updateTableStatus(form.value.table_id, 'Trống');
            }
          }
          successMessage.value = 'Cập nhật booking thành công';
        } else {
          await retryRequest(() => api.post('/bookings', payload));
          // Cập nhật trạng thái phòng/bàn cho booking mới
          if (form.value.booking_type === 'room' && form.value.room_id && form.value.status === 'pending') {
            await updateRoomStatus(form.value.room_id, 'Chờ xác nhận');
          } else if (form.value.booking_type === 'table' && form.value.table_id && form.value.status === 'pending') {
            await updateTableStatus(form.value.table_id, 'Chờ xác nhận');
          }
          successMessage.value = 'Thêm booking mới thành công';
        }

        await fetchData();
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
        
        await retryRequest(() => api.patch(`/bookings/${bookingId}/confirm`, { status: 'confirmed' }));
        
        // Cập nhật trạng thái phòng/bàn
        if (booking.booking_type === 'room' && booking.room_id) {
          await updateRoomStatus(booking.room_id, 'Đã đặt');
        } else if (booking.booking_type === 'table' && booking.table_id) {
          await updateTableStatus(booking.table_id, 'Đã đặt');
        }
        
        await fetchData();
        successMessage.value = 'Xác nhận booking thành công';
        
        // Kiểm tra trạng thái sau khi cập nhật
        const updatedBooking = bookings.value.find(b => b.booking_id === bookingId);
        if (updatedBooking.booking_type === 'room' && updatedBooking.room_id) {
          const room = rooms.value.find(r => r.room_id === updatedBooking.room_id);
          if (room && room.status !== 'Đã đặt') {
            errorMessage.value = 'Cảnh báo: Trạng thái phòng chưa được cập nhật thành Đã đặt';
          }
        } else if (updatedBooking.booking_type === 'table' && updatedBooking.table_id) {
          const table = tables.value.find(t => t.table_id === updatedBooking.table_id);
          if (table && table.status !== 'Đã đặt') {
            errorMessage.value = 'Cảnh báo: Trạng thái bàn chưa được cập nhật thành Đã đặt';
          }
        }
      } catch (error) {
        console.error('Lỗi khi xác nhận booking:', error.response?.data || error.message);
        errorMessage.value = error.response?.data?.message || 'Xác nhận booking thất bại';
        if (error.response?.status === 404) {
          errorMessage.value = 'Booking không tồn tại hoặc đã bị xóa.';
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
        
        await retryRequest(() => api.patch(`/bookings/${bookingId}/confirm`, { status: 'cancelled' }));
        
        // Cập nhật trạng thái phòng/bàn
        if (booking.booking_type === 'room' && booking.room_id) {
          await updateRoomStatus(booking.room_id, 'Trống');
        } else if (booking.booking_type === 'table' && booking.table_id) {
          await updateTableStatus(booking.table_id, 'Trống');
        }
        
        await fetchData();
        successMessage.value = 'Hủy booking thành công';
        
        // Kiểm tra trạng thái sau khi cập nhật
        const updatedBooking = bookings.value.find(b => b.booking_id === bookingId);
        if (updatedBooking.booking_type === 'room' && updatedBooking.room_id) {
          const room = rooms.value.find(r => r.room_id === updatedBooking.room_id);
          if (room && room.status !== 'Trống') {
            errorMessage.value = 'Cảnh báo: Trạng thái phòng chưa được cập nhật thành Trống';
          }
        } else if (updatedBooking.booking_type === 'table' && updatedBooking.table_id) {
          const table = tables.value.find(t => t.table_id === updatedBooking.table_id);
          if (table && table.status !== 'Trống') {
            errorMessage.value = 'Cảnh báo: Trạng thái bàn chưa được cập nhật thành Trống';
          }
        }
      } catch (error) {
        console.error('Lỗi khi hủy booking:', error.response?.data || error.message);
        errorMessage.value = error.response?.data?.message || 'Hủy booking thất bại';
        if (error.response?.status === 404) {
          errorMessage.value = 'Booking không tồn tại hoặc đã bị xóa.';
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
        
        // Cập nhật trạng thái phòng/bàn
        if (booking.booking_type === 'room' && booking.room_id) {
          await updateRoomStatus(booking.room_id, 'Trống');
        } else if (booking.booking_type === 'table' && booking.table_id) {
          await updateTableStatus(booking.table_id, 'Trống');
        }
        
        await fetchData();
        successMessage.value = 'Xóa booking thành công';
        
        // Kiểm tra trạng thái sau khi xóa
        if (booking.booking_type === 'room' && booking.room_id) {
          const room = rooms.value.find(r => r.room_id === booking.room_id);
          if (room && room.status !== 'Trống') {
            errorMessage.value = 'Cảnh báo: Trạng thái phòng chưa được cập nhật thành Trống';
          }
        } else if (booking.booking_type === 'table' && booking.table_id) {
          const table = tables.value.find(t => t.table_id === booking.table_id);
          if (table && table.status !== 'Trống') {
            errorMessage.value = 'Cảnh báo: Trạng thái bàn chưa được cập nhật thành Trống';
          }
        }
      } catch (error) {
        console.error('Lỗi khi xóa booking:', error.response?.data || error.message);
        errorMessage.value = error.response?.data?.message || 'Xóa booking thất bại';
        if (error.response?.status === 404) {
          errorMessage.value = 'Booking không tồn tại hoặc đã bị xóa.';
        }
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
      } catch {
        return '-';
      }
    };

    const formatStatus = (status) => {
      return { pending: 'Chờ xác nhận', confirmed: 'Đã xác nhận', cancelled: 'Đã hủy' }[status] || '-';
    };

    const formatPrice = (value) => {
      const numValue = Number(value);
      if (isNaN(numValue)) {
        console.error('Giá trị không hợp lệ để định dạng:', value);
        return '-';
      }
      return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(numValue);
    };

    // Khởi chạy fetchData khi component được mount
    onMounted(() => {
      fetchData();
    });

    return {
      bookings,
      rooms,
      tables,
      menus,
      seasons,
      tuKhoa,
      locTheoTrangThai,
      locTheoLoai,
      laModalMo,
      isEditing,
      isLoading,
      successMessage,
      errorMessage,
      trangHienTai,
      soBookingMoiTrang,
      form,
      pricing,
      totalCost,
      errors,
      minDate,
      minTime,
      availableRooms,
      availableTables,
      bookingLoc,
      bookingHienThi,
      tongSoTrang,
      trangHienThi,
      fetchData,
      checkAvailability,
      calculatePricing,
      calculateTotal,
      calculateTotalCost,
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
th{
  text-align: center;
  background-color: #1199f3;
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
  box-shadow: 0 4px 8px rgba(0, 123, 255, 0.2);
}
.btn-warning {
  background: linear-gradient(135deg, #ffc107, #e0a800);
  color: white;
}
.btn-warning:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(255, 193, 0, 0.2);
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
.card.bg-light {
  border: none;
  border-radius: 8px;
}
.table th,
.table td {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 150px;
  padding: 8px 12px;
}
.table td:last-child,
.table th:last-child {
  white-space: nowrap;
  max-width: none;
  min-width: 200px;
}
.table td .btn {
  margin-right: 8px;
  padding: 4px 8px;
  font-size: 0.875rem;
}
.table-responsive {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}
</style>