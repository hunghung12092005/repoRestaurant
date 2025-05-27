<template>
  <div class="container-fluid mt-5">
    <h2 class="mb-4">Quản lý Booking</h2>
    <div v-if="successMessage" class="alert alert-success">
      {{ successMessage }}
    </div>

    <div class="card mb-3">
      <div class="card-body">
        <div class="row">
          <div class="col-md-6 mb-3">
            <input
              v-model="tuKhoa"
              type="text"
              class="form-control"
              placeholder="Tìm theo tên hoặc số điện thoại"
            />
          </div>
          <div v-if="hasStatus" class="col-md-3 mb-3">
            <select v-model="locTheoTrangThai" class="form-select">
              <option value="">Tất cả trạng thái</option>
              <option value="pending">Chờ xác nhận</option>
              <option value="confirmed">Đã xác nhận</option>
              <option value="cancelled">Đã hủy</option>
            </select>
          </div>
          <div class="col-md-3 mb-3 text-end">
            <button class="btn btn-primary" @click="moModalThem">Thêm Booking mới</button>
          </div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordered">
            <thead>
              <tr>
                <th>#</th>
                <th>Tên khách</th>
                <th>Số điện thoại</th>
                <th>Ngày đặt</th>
                <th>Giờ đặt</th>
                <th>Số lượng</th>
                <th>Phòng</th>
                <th>Bàn</th>
                <th>Món</th>
                <th v-if="hasStatus">Trạng thái</th>
                <th>Hành động</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(booking, index) in bookingLoc" :key="booking.id">
                <td>{{ index + 1 }}</td>
                <td>{{ booking.customer_name }}</td>
                <td>{{ booking.customer_phone }}</td>
                <td>{{ formatDate(booking.booking_date) }}</td>
                <td>{{ booking.booking_time }}</td>
                <td>{{ booking.quantity }}</td>
                <td>{{ booking.room ? booking.room.room_name : '-' }}</td>
                <td>{{ booking.table ? booking.table.table_name : '-' }}</td>
                <td>{{ booking.menu ? booking.menu.menu_name : '-' }}</td>
                <td v-if="hasStatus">{{ formatStatus(booking.status) }}</td>
                <td>
                  <button
                    v-if="booking.status === 'pending'"
                    class="btn btn-success btn-sm me-2"
                    @click="confirmBooking(booking.booking_id)"
                  >
                    Xác nhận
                  </button>
                  <button class="btn btn-warning btn-sm me-2" @click="moModalSua(booking)">
                    Sửa
                  </button>
                  <button class="btn btn-danger btn-sm" @click="xoaBooking(booking.booking_id)">
                    Xóa
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Modal with Integrated Booking Form -->
    <div v-if="laModalMo" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0, 0, 0, 0.5)">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ isEditing ? 'Cập nhật Booking' : 'Thêm Booking Mới' }}</h5>
            <button type="button" class="btn-close" @click="dongModal"></button>
          </div>
          <div class="modal-body">
            <!-- Integrated Booking Form -->
            <div class="container mt-4" style="max-width: 600px;">
              <!-- Loading spinner -->
              <div v-if="isLoading" class="text-center mb-3">
                <div class="spinner-border text-primary" role="status">
                  <span class="visually-hidden">Đang tải...</span>
                </div>
              </div>

              <!-- Error message -->
              <div v-if="errorMessage" class="alert alert-danger" role="alert">
                {{ errorMessage }}
              </div>

              <!-- No rooms available message -->
              <div v-if="!isLoading && !rooms.length" class="alert alert-warning">
                Không có phòng nào được tải. Vui lòng kiểm tra kết nối hoặc thử lại.
              </div>

              <form @submit.prevent="submitBooking" novalidate>
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
                  <div v-if="errors.customer_name" class="invalid-feedback">
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
                  <div v-if="errors.customer_phone" class="invalid-feedback">
                    {{ errors.customer_phone }}
                  </div>
                </div>

                <div class="row mb-3">
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
                    <div v-if="errors.booking_date" class="invalid-feedback">
                      {{ errors.booking_date }}
                    </div>
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
                    <div v-if="errors.booking_time" class="invalid-feedback">
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
                  />
                  <div v-if="errors.quantity" class="invalid-feedback">
                    {{ errors.quantity }}
                  </div>
                </div>

                <div class="mb-3">
                  <label for="selectRoom" class="form-label">Chọn phòng</label>
                  <select
                    id="selectRoom"
                    class="form-select"
                    v-model="form.room_id"
                    :disabled="isLoading || !rooms.length"
                    :class="{ 'is-invalid': errors.room_id }"
                  >
                    <option :value="null">-- {{ rooms.length ? 'Chọn phòng' : 'Không có phòng' }} --</option>
                    <option
                      v-for="room in rooms"
                      :key="room.room_id"
                      :value="room.room_id"
                      :disabled="!isEditing && room.status !== 'Trống'"
                    >
                      {{ room.room_name }} (Sức chứa: {{ room.capacity || 'N/A' }})
                      {{ room.status !== 'Trống' ? `(${room.status})` : '' }}
                    </option>
                  </select>
                  <div v-if="errors.room_id" class="invalid-feedback">
                    {{ errors.room_id }}
                  </div>
                </div>

                <div class="mb-3">
                  <label for="selectTable" class="form-label">Chọn bàn (tùy chọn)</label>
                  <select
                    id="selectTable"
                    class="form-select"
                    v-model="form.table_id"
                    :disabled="isLoading || !tables.length"
                    :class="{ 'is-invalid': errors.table_id }"
                  >
                    <option :value="null">-- {{ tables.length ? 'Chọn bàn' : 'Không có bàn' }} --</option>
                    <option
                      v-for="table in tables"
                      :key="table.table_id"
                      :value="table.table_id"
                      :disabled="!isEditing && table.status !== 'Trống'"
                    >
                      {{ table.table_name }} (Sức chứa: {{ table.capacity || 'N/A' }})
                      {{ table.status !== 'Trống' ? `(${table.status})` : '' }}
                    </option>
                  </select>
                  <div v-if="errors.table_id" class="invalid-feedback">
                    {{ errors.table_id }}
                  </div>
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
              </form>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" @click="dongModal">Hủy</button>
            <button class="btn btn-primary" @click="submitBooking">Lưu</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const api = axios.create({
  baseURL: 'http://localhost:8000/api',
  timeout: 5000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

export default {
  name: 'AdminBookingComponent',
  setup() {
    // Main component state
    const bookings = ref([]);
    const tuKhoa = ref('');
    const locTheoTrangThai = ref('');
    const laModalMo = ref(false);
    const bookingDangSua = ref(null);
    const hasStatus = ref(false);
    const successMessage = ref('');

    // Form component state
    const form = ref({
      booking_id: null,
      customer_name: '',
      customer_phone: '',
      booking_date: '',
      booking_time: '',
      quantity: 1,
      room_id: null,
      table_id: null,
      menu_id: null,
      note: '',
      status: 'pending', // Mặc định khi tạo mới
    });
    const rooms = ref([]);
    const tables = ref([]);
    const menus = ref([]);
    const isEditing = ref(false);
    const isLoading = ref(false);
    const errorMessage = ref('');
    const errors = ref({
      customer_name: '',
      customer_phone: '',
      booking_date: '',
      booking_time: '',
      quantity: '',
      room_id: '',
      table_id: '',
    });

    // Tính toán ngày và giờ tối thiểu (hiện tại)
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

    const fetchBookings = async () => {
      try {
        const res = await api.get('/bookings');
        console.log('Bookings fetched:', res.data);
        bookings.value = res.data.data || res.data;
        hasStatus.value = bookings.value.length > 0 && 'status' in bookings.value[0];
      } catch (error) {
        console.error('Lỗi lấy booking:', error);
        alert('Lấy dữ liệu booking thất bại');
      }
    };

    const fetchFormData = async () => {
      isLoading.value = true;
      errorMessage.value = '';
      try {
        const [roomsRes, tablesRes, menusRes] = await Promise.all([
          api.get('/rooms').catch(err => {
            console.error('Lỗi gọi API /rooms:', err.message, err.response?.status, err.response?.data);
            throw err;
          }),
          api.get('/tables').catch(err => {
            console.error('Lỗi gọi API /tables:', err.message, err.response?.status, err.response?.data);
            throw err;
          }),
          api.get('/menus').catch(err => {
            console.error('Lỗi gọi API /menus:', err.message, err.response?.status, err.response?.data);
            throw err;
          }),
        ]);
        console.log('Raw rooms response:', roomsRes);
        console.log('Processed rooms:', roomsRes.data.data || roomsRes.data);
        console.log('Raw tables response:', tablesRes);
        console.log('Processed tables:', tablesRes.data.data || tablesRes.data);
        console.log('Raw menus response:', menusRes);
        console.log('Processed menus:', menusRes.data.data || menusRes.data);

        rooms.value = Array.isArray(roomsRes.data.data) ? roomsRes.data.data : Array.isArray(roomsRes.data) ? roomsRes.data : [];
        tables.value = Array.isArray(tablesRes.data.data) ? tablesRes.data.data : Array.isArray(tablesRes.data) ? tablesRes.data : [];
        menus.value = Array.isArray(menusRes.data.data) ? menusRes.data.data : Array.isArray(menusRes.data) ? menusRes.data : [];

        // Debug trạng thái phòng và bàn
        rooms.value.forEach(room => {
          console.log(`Phòng ${room.room_name}: status = ${room.status}`);
        });
        tables.value.forEach(table => {
          console.log(`Bàn ${table.table_name}: status = ${table.status}`);
        });

        if (!rooms.value.length) {
          errorMessage.value = 'Không có phòng nào được tải từ API. Vui lòng kiểm tra kết nối hoặc cơ sở dữ liệu.';
        }
      } catch (error) {
        console.error('Lỗi tải dữ liệu:', error);
        errorMessage.value = 'Không thể tải dữ liệu phòng, bàn, món ăn. Vui lòng kiểm tra kết nối hoặc thử lại!';
      } finally {
        isLoading.value = false;
      }
    };

    onMounted(() => {
      console.log('AdminBookingComponent mounted');
      fetchBookings();
      fetchFormData().then(() => {
        console.log('Rooms after fetch:', rooms.value);
        console.log('Tables after fetch:', tables.value);
      });
    });

    const bookingLoc = computed(() => {
      return bookings.value.filter((b) => {
        const khopTim =
          b.customer_name.toLowerCase().includes(tuKhoa.value.toLowerCase()) ||
          b.customer_phone.includes(tuKhoa.value);
        const khopTrangThai = !locTheoTrangThai.value || b.status === locTheoTrangThai.value;
        return khopTim && khopTrangThai;
      });
    });

    const moModalThem = () => {
      bookingDangSua.value = null;
      isEditing.value = false;
      resetForm();
      laModalMo.value = true;
      successMessage.value = '';
    };

    const moModalSua = (booking) => {
      console.log('Booking to edit:', booking);
      bookingDangSua.value = { ...booking };
      loadBookingToEdit(booking);
      laModalMo.value = true;
      successMessage.value = '';
    };

    const dongModal = () => {
      laModalMo.value = false;
      successMessage.value = '';
      resetForm();
    };

    const validateForm = () => {
      errors.value = {
        customer_name: '',
        customer_phone: '',
        booking_date: '',
        booking_time: '',
        quantity: '',
        room_id: '',
        table_id: '',
      };
      let isValid = true;

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
      if (!form.value.booking_date) {
        errors.value.booking_date = 'Vui lòng chọn ngày đặt';
        isValid = false;
      }
      if (!form.value.booking_time) {
        errors.value.booking_time = 'Vui lòng chọn giờ đặt';
        isValid = false;
      }
      if (!form.value.quantity || form.value.quantity < 1) {
        errors.value.quantity = 'Số lượng khách phải lớn hơn 0';
        isValid = false;
      }
      if (!form.value.room_id) {
        errors.value.room_id = 'Vui lòng chọn phòng';
        isValid = false;
      }

      // Kiểm tra thời gian không ở quá khứ
      const bookingDateTime = new Date(`${form.value.booking_date}T${form.value.booking_time}`);
      const currentDateTime = new Date();
      if (bookingDateTime < currentDateTime) {
        errors.value.booking_date = 'Thời gian đặt phải ở tương lai';
        isValid = false;
      }

      // Kiểm tra phòng trống (trừ khi đang chỉnh sửa)
      if (form.value.room_id && !isEditing.value) {
        const selectedRoom = rooms.value.find(room => room.room_id === form.value.room_id);
        if (selectedRoom && selectedRoom.status && selectedRoom.status !== 'Trống') {
          errors.value.room_id = 'Phòng đã được đặt hoặc đang chờ xét duyệt, vui lòng chọn phòng khác';
          isValid = false;
        }
      }

      // Kiểm tra bàn trống nếu được chọn (trừ khi đang chỉnh sửa)
      if (form.value.table_id && !isEditing.value) {
        const selectedTable = tables.value.find(table => table.table_id === form.value.table_id);
        if (selectedTable && selectedTable.status && selectedTable.status !== 'Trống') {
          errors.value.table_id = 'Bàn đã được đặt hoặc đang chờ xét duyệt, vui lòng chọn bàn khác';
          isValid = false;
        }
      }

      return isValid;
    };

    const submitBooking = async () => {
      if (!validateForm()) {
        return false;
      }

      isLoading.value = true;
      errorMessage.value = '';
      try {
        // Kiểm tra trạng thái phòng trước khi gửi API (trừ khi đang chỉnh sửa)
        if (form.value.room_id && !isEditing.value) {
          const room = rooms.value.find(r => r.room_id === form.value.room_id);
          if (room && room.status && room.status !== 'Trống') {
            errorMessage.value = 'Phòng đã được đặt hoặc đang chờ xét duyệt, vui lòng chọn phòng khác';
            isLoading.value = false;
            return false;
          }
        }
        // Kiểm tra trạng thái bàn nếu được chọn (trừ khi đang chỉnh sửa)
        if (form.value.table_id && !isEditing.value) {
          const table = tables.value.find(t => t.table_id === form.value.table_id);
          if (table && table.status && table.status !== 'Trống') {
            errorMessage.value = 'Bàn đã được đặt hoặc đang chờ xét duyệt, vui lòng chọn bàn khác';
            isLoading.value = false;
            return false;
          }
        }

        const payload = {
          customer_name: form.value.customer_name,
          customer_phone: form.value.customer_phone,
          booking_date: form.value.booking_date,
          booking_time: form.value.booking_time,
          quantity: form.value.quantity,
          room_id: form.value.room_id,
          table_id: form.value.table_id,
          menu_id: form.value.menu_id,
          note: form.value.note,
          status: isEditing.value ? undefined : 'pending', // Chỉ gửi status khi tạo mới
        };

        let bookingId;
        if (isEditing.value) {
          await api.put(`/bookings/${form.value.booking_id}`, payload);
          bookingId = form.value.booking_id;
          successMessage.value = 'Cập nhật booking thành công';
        } else {
          const response = await api.post('/bookings', payload);
          bookingId = response.data.booking_id || response.data.id;
          successMessage.value = 'Thêm booking mới thành công';

          // Cập nhật trạng thái phòng sang Đang chờ xét duyệt
          if (form.value.room_id) {
            try {
              await api.patch(`/rooms/${form.value.room_id}`, { status: 'Đang chờ xét duyệt' });
              console.log(`Cập nhật trạng thái phòng ${form.value.room_id} thành Đang chờ xét duyệt`);
            } catch (err) {
              console.error('Lỗi cập nhật trạng thái phòng:', err);
              errorMessage.value += ' Lỗi khi cập nhật trạng thái phòng.';
            }
          }

          // Cập nhật trạng thái bàn sang Đang chờ xét duyệt (nếu có)
          if (form.value.table_id) {
            try {
              await api.patch(`/tables/${form.value.table_id}`, { status: 'Đang chờ xét duyệt' });
              console.log(`Cập nhật trạng thái bàn ${form.value.table_id} thành Đang chờ xét duyệt`);
            } catch (err) {
              console.error('Lỗi cập nhật trạng thái bàn:', err);
              errorMessage.value += ' Lỗi khi cập nhật trạng thái bàn.';
            }
          }
        }

        await fetchBookings();
        await fetchFormData();
        dongModal();
        return true;
      } catch (error) {
        console.error('Lỗi khi lưu booking:', error);
        errorMessage.value = 'Lưu booking thất bại. Vui lòng thử lại.';
        return false;
      } finally {
        isLoading.value = false;
      }
    };

    const confirmBooking = async (bookingId) => {
      if (!confirm('Bạn có chắc chắn muốn xác nhận booking này không?')) return;

      isLoading.value = true;
      errorMessage.value = '';
      try {
        // Lấy booking để biết room_id và table_id
        const booking = bookings.value.find(b => b.booking_id === bookingId);
        if (!booking) {
          throw new Error('Không tìm thấy booking');
        }

        // Gọi API xác nhận booking
        await api.patch(`/bookings/${bookingId}/confirm`, { status: 'confirmed' });
        console.log(`Xác nhận booking ${bookingId} thành công`);

        // Cập nhật trạng thái phòng sang Đã đặt
        if (booking.room_id) {
          try {
            await api.patch(`/rooms/${booking.room_id}`, { status: 'Đã đặt' });
            console.log(`Cập nhật trạng thái phòng ${booking.room_id} thành Đã đặt`);
          } catch (err) {
            console.error('Lỗi cập nhật trạng thái phòng:', err);
            errorMessage.value += ' Lỗi khi cập nhật trạng thái phòng.';
          }
        }

        // Cập nhật trạng thái bàn sang Đã đặt (nếu có)
        if (booking.table_id) {
          try {
            await api.patch(`/tables/${booking.table_id}`, { status: 'Đã đặt' });
            console.log(`Cập nhật trạng thái bàn ${booking.table_id} thành Đã đặt`);
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
        errorMessage.value = 'Xác nhận booking thất bại. Vui lòng thử lại.';
      } finally {
        isLoading.value = false;
      }
    };

    const resetForm = () => {
      form.value = {
        booking_id: null,
        customer_name: '',
        customer_phone: '',
        booking_date: '',
        booking_time: '',
        quantity: 1,
        room_id: null,
        table_id: '',
        menu_id: null,
        note: '',
        status: 'pending',
      };
      isEditing.value = false;
      errorMessage.value = '';
      errors.value = {
        customer_name: '',
        customer_phone: '',
        booking_date: '',
        booking_time: '',
        quantity: '',
        room_id: '',
        table_id: '',
      };
    };

    const loadBookingToEdit = (booking) => {
      form.value = {
        booking_id: booking.booking_id,
        customer_name: booking.customer_name,
        customer_phone: booking.customer_phone,
        booking_date: booking.booking_date.split('T')[0],
        booking_time: booking.booking_time.slice(0, 5),
        quantity: booking.quantity,
        room_id: booking.room_id,
        table_id: booking.table_id,
        menu_id: booking.menu_id,
        note: booking.note,
        status: booking.status || 'pending'
      };
      isEditing.value = true;
    };

    const xoaBooking = async (id) => {
      if (confirm('Bạn có chắc chắn muốn xóa booking này không?')) {
        try {
          // Lấy booking để biết room_id và table_id
          const booking = bookings.value.find(b => b.booking_id === id);
          if (booking) {
            // Khôi phục trạng thái phòng về Trống
            if (booking.room_id) {
              await api.patch(`/rooms/${booking.room_id}`, { status: 'Trống' });
              console.log(`Khôi phục trạng thái phòng ${booking.room_id} thành Trống`);
            }
            // Khôi phục trạng thái bàn về Trống (nếu có)
            if (booking.table_id) {
              await api.patch(`/tables/${booking.table_id}`, { status: 'Trống' });
              console.log(`Khôi phục trạng thái bàn ${booking.table_id} thành Trống`);
            }
          }

          await api.delete(`/bookings/${id}`);
          successMessage.value = 'Xóa booking thành công';
          await fetchBookings();
          await fetchFormData();
        } catch (error) {
          console.error('Lỗi xóa booking:', error);
          errorMessage.value = 'Xóa booking thất bại. Vui lòng thử lại!';
        }
      }
    };

    const formatDate = (date) => {
      try {
        return new Date(date).toLocaleDateString('vi-VN');
      } catch (error) {
        console.error('Lỗi định dạng ngày:', error);
        return '';
      }
    };

    const formatStatus = (status) => {
      return {
        pending: 'Chờ xác nhận',
        confirmed: 'Đã xác nhận',
        cancelled: 'Đã hủy',
      }[status] || status;
    };

    const formatPrice = (value) => {
      return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
      }).format(value || 0);
    }

    return {
      bookings,
      tuKhoa,
      locTheoTrangThai,
      laModalMo,
      bookingDangSua,
      hasStatus,
      successMessage,
      bookingLoc,
      moModalThem,
      moModalSua,
      dongModal,
      xoaBooking,
      confirmBooking,
      formatDate,
      formatStatus,
      form,
      rooms,
      tables,
      menus,
      isEditing,
      isLoading,
      errorMessage,
      errors,
      submitBooking,
      resetForm,
      formatPrice,
      minDate,
      minTime,
    };
},
};
</script>

<style scoped>
.modal.fade-show {
  display: block;
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