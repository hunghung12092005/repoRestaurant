<template>
  <loading v-if="isLoading"></loading>
  <div class="page-container">
    <!-- Tiêu đề trang -->
    <div class="page-header mb-4">
      <h1 class="page-title">Sơ Đồ Phòng</h1>
      <p class="page-subtitle">Quản lý trạng thái và thao tác với các phòng trong khách sạn.</p>
    </div>
    <!-- Bộ lọc -->
    <div class="card filter-card mb-4">
      <div class="card-body">
        <div class="row g-3 align-items-end">
          <div class="col-lg-2 col-md-6">
            <label for="filter-date" class="form-label">Xem theo ngày</label>
            <input type="date" id="filter-date" class="form-control" v-model="selectedDate" @change="fetchRooms" />
          </div>
          <div class="col-lg-2 col-md-6">
            <label for="filter-time" class="form-label">Thời gian</label>
            <input type="time" id="filter-time" class="form-control" v-model="selectedTime" @change="fetchRooms" />
          </div>
          <div class="col-lg-2 col-md-6">
            <label for="status" class="form-label">Trạng thái</label>
            <select id="status" class="form-select" v-model="selectedStatus">
              <option>Tất cả</option>
              <option>Còn trống</option>
              <option>Đã đặt</option>
            </select>
          </div>
          <div class="col-lg-2 col-md-6">
            <label for="room-type" class="form-label">Loại phòng</label>
            <select id="room-type" class="form-select" v-model="selectedRoomType">
              <option v-for="type in roomTypes" :key="type" :value="type">{{ type }}</option>
            </select>
          </div>
          <div class="col-lg-2 col-md-6">
            <label for="floor" class="form-label">Tầng</label>
            <select id="floor" class="form-select" v-model="selectedFloor">
              <option v-for="floor in floors" :key="floor" :value="floor">{{ floor }}</option>
            </select>
          </div>
          <div class="col-lg-2 col-md-12 text-end">
  <button @click="openFutureBookings" class="btn btn-outline-danger w-100">
    <i class="bi bi-calendar-x me-2"></i>Hủy phòng trước
  </button>
</div>

          <div class="col-lg-2 col-md-12 text-end">
            <button @click="clearFilters" class="btn btn-outline-secondary w-100">
              <i class="bi bi-arrow-clockwise me-2"></i>Xóa lọc
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Sơ đồ phòng -->
    <div v-if="filteredRooms.length > 0">
      <div v-for="floorGroup in groupedAndSortedRooms" :key="floorGroup.floor" class="floor-section">
        <h2 class="floor-header">Tầng {{ floorGroup.floor }}</h2>
        <div class="room-grid">
          <div v-for="room in floorGroup.rooms" :key="room.room_id" class="room-card" :class="{ 'booked': room.status === 'Đã đặt' }">
            <div class="card-header">
              <h5 class="room-number">{{ room.number }}</h5>
              <span class="badge" :class="room.status === 'Đã đặt' ? 'badge-booked' : 'badge-available'">{{ room.status }}</span>
              <span v-if="room.payment_status === 'completed'" class="badge badge-paid">Đã thanh toán</span>
            </div>
            <div class="card-body">
              <p class="room-type">{{ room.type }}</p>
              <p class="room-bedsize">{{ room.bedSize }}</p>
              <p class="room-capacity">
                <i class="bi bi-people-fill"></i> {{ room.max_occupancy }} Người lớn & {{ room.max_occupancy_child }} Trẻ em
              </p>
            </div>
            <div class="card-footer">
              <div v-if="room.status === 'Đã đặt'" class="action-grid">
                <div class="action-row">
                  <button class="btn btn-sm btn-outline-primary" @click.prevent="showGuestDetails(room)">Chi tiết</button>
                </div>
                <button class="btn btn-sm btn-outline-danger w-100 mt-2" @click.prevent="checkoutRoom(room)" :disabled="room.payment_status === 'completed'">Trả phòng</button>
              </div>
              <button v-else class="btn btn-sm btn-outline-primary w-100" @click.prevent="showAddGuest(room.room_id)"><i class="bi bi-person-plus-fill me-1"></i> Thêm khách</button>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div v-else class="alert alert-light text-center">Không có phòng nào khớp với bộ lọc hiện tại.</div>

    <!-- Modal Thêm Khách -->
    <div v-if="showForm" class="modal-backdrop fade show"></div>
    <div v-if="showForm" class="modal fade show d-block" tabindex="-1">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <form @submit.prevent="submitCustomerForm" class="modal-content modal-custom">
          <div class="modal-header modal-header-custom"><h5 class="modal-title">Đăng ký khách hàng</h5><button type="button" class="btn-close" @click="showForm = false"></button></div>
          <div class="modal-body p-4">
            <div class="row g-3">
              <div class="col-12"><label class="form-label">Ảnh CCCD</label><div class="input-group"><input type="file" @change="onFileChange" accept="image/*" class="form-control" /><button type="button" class="btn btn-outline-secondary" @click="uploadImage">Quét CCCD</button></div></div>
              <div class="col-md-6"><label class="form-label">Họ tên</label><input v-model="formData.customer_name" required class="form-control" /></div>
              <div class="col-md-6"><label class="form-label">Số điện thoại</label><input v-model="formData.customer_phone" required class="form-control" /></div>
              <div class="col-md-6"><label class="form-label">Số CCCD</label><input v-model="formData.customer_id_number" required class="form-control" /></div>
              <div class="col-md-6"><label class="form-label">Email</label><input v-model="formData.customer_email" type="email" required class="form-control" /></div>
              <div class="col-12"><label class="form-label">Địa chỉ</label><input v-model="formData.address" class="form-control" /></div>
              <div class="col-md-6"><label class="form-label">Ngày nhận phòng</label><input type="date" v-model="formData.check_in_date" required class="form-control" /></div>
              <div class="col-md-6"><label class="form-label">Giờ nhận phòng</label><input type="time" v-model="formData.check_in_time" class="form-control" placeholder="14:00" /></div>
              <div class="col-md-6"><label class="form-label">Ngày trả phòng</label><input type="date" v-model="formData.check_out_date" required class="form-control" /></div>
              <div class="col-md-6"><label class="form-label">Giờ trả phòng</label><input type="time" v-model="formData.check_out_time" class="form-control" placeholder="12:00" /></div>
              <div class="col-12 mt-3">
                <label class="form-label">Tổng tiền ước tính:</label>
                <div v-if="totalPricePreview && isFinite(totalPricePreview)" class="fw-bold fs-5 text-success">
                  {{ Number(totalPricePreview).toLocaleString('vi-VN') + ' VND' }}
                </div>
                <div v-else class="text-danger">
                  {{ pricePreviewError || 'Không thể tính giá. Vui lòng kiểm tra thời gian đặt phòng.' }}
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer modal-footer-custom"><button type="button" class="btn btn-secondary" @click="showForm = false">Hủy</button><button type="submit" class="btn btn-primary">Lưu</button></div>
        </form>
      </div>
    </div>

    <!-- Modal Chi Tiết Khách -->
    <div v-if="showGuestModal" class="modal-backdrop fade show"></div>
    <div v-if="showGuestModal" class="modal fade show d-block" tabindex="-1">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content modal-custom" v-if="guestInfo.room">
          <div class="modal-header modal-header-custom"><h5 class="modal-title">Chi tiết phòng & khách</h5><button type="button" class="btn-close" @click="showGuestModal = false"></button></div>
          <div class="modal-body p-4">
            <div class="row g-4">
              <div class="col-md-6"><h6 class="info-title">Thông tin phòng</h6><ul class="info-list"><li><span>Phòng:</span><strong>{{ guestInfo.room.room_name }} (Tầng {{ guestInfo.room.floor_number }})</strong></li><li><span>Loại phòng:</span><strong>{{ guestInfo.room.type_name }}</strong></li></ul></div>
              <div class="col-md-6"><h6 class="info-title">Thông tin khách hàng</h6><ul class="info-list"><li><span>Họ tên:</span><strong>{{ guestInfo.customer?.customer_name || 'N/A' }}</strong></li><li><span>SĐT:</span><strong>{{ guestInfo.customer?.customer_phone || 'N/A' }}</strong></li><li><span>Email:</span><strong>{{ guestInfo.customer?.customer_email || 'N/A' }}</strong></li><li><span>Địa chỉ:</span><strong>{{ guestInfo.customer?.address || 'N/A' }}</strong></li></ul></div>
              <div class="col-12"><h6 class="info-title">Chi tiết lưu trú</h6><ul class="info-list"><li><span>Nhận phòng:</span><strong>{{ formatDateTime(guestInfo.booking?.check_in_date, guestInfo.booking?.check_in_time) || 'N/A' }}</strong></li><li><span>Trả phòng dự kiến:</span><strong>{{ formatDateTime(guestInfo.booking?.check_out_date, guestInfo.booking?.check_out_time) || 'N/A' }}</strong></li><li><span>Trả phòng thực tế:</span><strong>{{ getActualCheckout(guestInfo.booking) }}</strong></li><li><span>Tổng tiền:</span><strong class="text-success fs-6">{{ guestInfo.booking?.total_price ? Number(guestInfo.booking.total_price).toLocaleString('vi-VN') + ' VND' : 'N/A' }}</strong></li></ul></div>
            </div>
          </div>
          <div class="modal-footer modal-footer-custom"><button type="button" class="btn btn-secondary" @click="showGuestModal = false">Đóng</button><button type="button" class="btn btn-primary" @click="editCustomerInfo(guestInfo.customer)">Sửa thông tin</button></div>
        </div>
      </div>
    </div>

    <!-- Modal Gia hạn -->
    <div v-if="showExtendModal" class="modal-backdrop fade show"></div>
    <div v-if="showExtendModal" class="modal fade show d-block" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <form @submit.prevent="submitExtendForm" class="modal-content modal-custom">
          <div class="modal-header modal-header-custom"><h5 class="modal-title">Gia hạn thuê phòng</h5><button type="button" class="btn-close" @click="showExtendModal = false"></button></div>
          <div class="modal-body p-4"><div class="mb-3"><label class="form-label">Ngày giờ trả mới:</label><input type="datetime-local" v-model="extendForm.check_out_date" required class="form-control" /></div></div>
          <div class="modal-footer modal-footer-custom"><button type="button" class="btn btn-secondary" @click="showExtendModal = false">Hủy</button><button type="submit" class="btn btn-primary">Xác nhận</button></div>
        </form>
      </div>
    </div>

    <!-- Modal Dịch vụ -->
    <div v-if="showServiceModal" class="modal-backdrop fade show"></div>
    <div v-if="showServiceModal" class="modal fade show d-block" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-custom">
          <div class="modal-header modal-header-custom"><h5 class="modal-title">Chọn dịch vụ sử dụng</h5><button type="button" class="btn-close" @click="showServiceModal = false"></button></div>
          <div class="modal-body p-4">
            <div v-if="allServices.length === 0">Đang tải dịch vụ...</div>
            <div v-else>
              <div v-for="(service, index) in allServices" :key="service.service_id" class="service-item">
                <div>{{ service.service_name }} - {{ formatPrice(service.price) }}</div>
                <div class="qty-controls">
                  <button @click="decreaseQty(index)" class="btn btn-sm btn-outline-secondary">-</button>
                  <input type="number" v-model.number="service.quantity" min="0" class="form-control form-control-sm text-center" />
                  <button @click="increaseQty(index)" class="btn btn-sm btn-outline-secondary">+</button>
                </div>
              </div>
              <hr/>
              <div class="mb-2"><label>Tổng tiền dịch vụ:</label><span class="fw-bold text-danger ms-2">{{ serviceTotal.toLocaleString('vi-VN') }} VND</span></div>
              <div class="mb-2"><label class="form-label">Phí phụ thu (VND):</label><input type="number" v-model.number="additionalFee" min="0" class="form-control form-control-sm" /></div>
              <div class="mb-2"><label class="form-label">Lý do phụ thu:</label><input type="text" v-model="surchargeReason" class="form-control form-control-sm" /></div>
            </div>
          </div>
          <div class="modal-footer modal-footer-custom"><button type="button" class="btn btn-secondary" @click="showServiceModal = false">Hủy</button><button type="button" class="btn btn-danger" @click="confirmPayment">Xác nhận trả phòng</button></div>
        </div>
      </div>
    </div>

    <!-- Modal Sửa Thông Tin Khách -->
    <div v-if="showEditForm" class="modal-backdrop fade show"></div>
    <div v-if="showEditForm" class="modal fade show d-block" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <form @submit.prevent="submitEditForm" class="modal-content modal-custom">
          <div class="modal-header modal-header-custom"><h5 class="modal-title">Sửa thông tin khách hàng</h5><button type="button" class="btn-close" @click="showEditForm = false"></button></div>
          <div class="modal-body p-4">
            <div class="mb-3">
  <label class="form-label">Ngày nhận phòng:</label>
  <input type="date" v-model="editFormData.check_in_date" class="form-control" />
</div>
<div class="mb-3">
  <label class="form-label">Giờ nhận phòng:</label>
  <input type="time" v-model="editFormData.check_in_time" class="form-control" />
</div>
<div class="mb-3">
  <label class="form-label">Ngày trả phòng:</label>
  <input type="date" v-model="editFormData.check_out_date" class="form-control" />
</div>
<div class="mb-3">
  <label class="form-label">Giờ trả phòng:</label>
  <input type="time" v-model="editFormData.check_out_time" class="form-control" />
</div>

            <div class="mb-3"><label class="form-label">Họ tên:</label><input v-model="editFormData.customer_name" required class="form-control" /></div>
            <div class="mb-3"><label class="form-label">SĐT:</label><input v-model="editFormData.customer_phone" class="form-control" /></div>
            <div class="mb-3"><label class="form-label">Email:</label><input v-model="editFormData.customer_email" class="form-control" /></div>
            <div class="mb-3"><label class="form-label">Địa chỉ:</label><input v-model="editFormData.address" class="form-control" /></div>
          </div>
          <div class="modal-footer modal-footer-custom">
            <button type="button" class="btn btn-secondary" @click="showEditForm = false">Hủy</button>
            <button type="submit" class="btn btn-primary">Lưu</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <div v-if="futureBookingModal" class="modal-backdrop fade show"></div>
  <div v-if="futureBookingModal" class="modal fade show d-block" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content modal-custom">
        <div class="modal-header modal-header-custom">
          <h5 class="modal-title">Danh sách hủy phòng trong tương lai</h5>
          <button type="button" class="btn-close" @click="futureBookingModal = false"></button>
        </div>
        <div class="modal-body p-4">
          <table class="table table-bordered">
            <thead>
              <tr>
                <th>Phòng</th>
                <th>Khách</th>
                <th>Nhận phòng</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="booking in futureBookings" :key="booking.id">
                <td>{{ booking.room_name }}</td>
                <td>{{ booking.customer_name }}</td>
                <td>{{ booking.check_in_time }}</td>
                <td>
                  <button class="btn btn-sm btn-danger" @click="openCancelNowModal(booking)">Hủy</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal nhập lý do hủy -->
  <div v-if="cancelNowModal" class="modal-backdrop fade show"></div>
  <div v-if="cancelNowModal" class="modal fade show d-block" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content modal-custom">
        <div class="modal-header modal-header-custom">
          <h5 class="modal-title">Xác nhận hủy phòng</h5>
          <button type="button" class="btn-close" @click="cancelNowModal = false"></button>
        </div>
        <div class="modal-body p-4">
          <textarea v-model="cancelNowReason" class="form-control" placeholder="Lý do hủy (tuý chọn)" rows="3"></textarea>
        </div>
        <div class="modal-footer modal-footer-custom">
          <button class="btn btn-secondary" @click="cancelNowModal = false">Hủy</button>
          <button class="btn btn-danger" @click="confirmCancelNow">Xác nhận</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import axios from 'axios';
import { inject } from 'vue';
import loading from '../loading.vue';

const apiUrl = inject('apiUrl');
const allRooms = ref([]);
const isLoading = ref(false);
const selectedStatus = ref('Tất cả');
const selectedRoomType = ref('Tất cả');
const selectedFloor = ref('Tất cả');
const selectedDate = ref(new Date().toISOString().substr(0, 10));
const selectedTime = ref(new Date().toLocaleTimeString('it-IT', { hour: '2-digit', minute: '2-digit' }));
const showForm = ref(false);
const totalPricePreview = ref(null);
const pricePreviewError = ref('');
const formData = ref({
  customer_name: '',
  customer_phone: '',
  customer_email: '',
  address: '',
  customer_id_number: '',
  room_id: null,
  check_in_date: '',
  check_in_time: '11:00',
  check_out_date: '',
  check_out_time: '12:00',
  pricing_type: 'hourly'
});
const showEditForm = ref(false);
const editFormData = ref({
  customer_id: null,
  customer_name: '',
  customer_phone: '',
  customer_email: '',
  address: '',
  check_in_date: '',
  check_in_time: '',
  check_out_date: '',
  check_out_time: ''
});

const showServiceModal = ref(false);
const allServices = ref([]);
const currentBookingDetailId = ref(null);
const showGuestModal = ref(false);
const guestInfo = ref({});
const showExtendModal = ref(false);
const extendForm = ref({ booking_detail_id: null, check_out_date: '' });
const additionalFee = ref(0);
const surchargeReason = ref('');
const apiKey = 'UIOieiMUjnqR7UCrJrtF30wQaw8Jc4ys';
const imageFile = ref(null);

const serviceTotal = computed(() => allServices.value.reduce((sum, s) => sum + s.price * s.quantity, 0));
const increaseQty = (index) => { allServices.value[index].quantity++; };
const decreaseQty = (index) => { if (allServices.value[index].quantity > 0) allServices.value[index].quantity--; };
const formatPrice = (price) => price.toLocaleString('vi-VN') + ' VND';

const futureBookingModal = ref(false);
const cancelNowModal = ref(false);
const cancelNowBookingId = ref(null);
const cancelNowReason = ref('');
const futureBookings = ref([]);

const openFutureBookings = async () => {
  try {
    const res = await axios.get(`${apiUrl}/api/occupancy/future-bookings`);
    futureBookings.value = res.data;
    futureBookingModal.value = true;
  } catch (err) {
    alert('Lỗi khi tải danh sách hủy phòng trước.');
  }
};

const openCancelNowModal = (booking) => {
  cancelNowBookingId.value = booking.id;
  cancelNowReason.value = '';
  cancelNowModal.value = true;
};

const confirmCancelNow = async () => {
  try {
    await axios.post(`${apiUrl}/api/occupancy/cancel-now`, {
      detail_id: cancelNowBookingId.value,
      reason: cancelNowReason.value
    });
    cancelNowModal.value = false;
    futureBookingModal.value = false;
    alert('Hủy phòng thành công!');
    await fetchRooms();
  } catch (err) {
    alert(err.response?.data?.message || 'Lỗi khi hủy phòng!');
  }
};

const formatDateTime = (date, time) => {
  if (!date || !time) return 'N/A';
  return `${date} ${time}`;
};

const editCustomerInfo = (customer) => {
  if (!customer || !guestInfo.value.booking) return;
  const booking = guestInfo.value.booking;

  editFormData.value = {
    customer_id: customer.customer_id,
    customer_name: customer.customer_name || '',
    customer_phone: customer.customer_phone || '',
    customer_email: customer.customer_email || '',
    address: customer.address || '',
    check_in_date: booking.check_in_date || '',
    check_in_time: booking.check_in_time || '',
    check_out_date: booking.check_out_date || '',
    check_out_time: booking.check_out_time || '',
  };

  showGuestModal.value = false;
  showEditForm.value = true;
};


const submitEditForm = async () => {
  try {
const isValidTime = (val) => typeof val === 'string' && /^\d{2}:\d{2}$/.test(val);

const checkInTime = isValidTime(editFormData.value.check_in_time) ? editFormData.value.check_in_time : '14:00';
const checkOutTime = isValidTime(editFormData.value.check_out_time) ? editFormData.value.check_out_time : '12:00';



    const res = await axios.post(`${apiUrl}/api/bookings/${guestInfo.value.booking.booking_id}/update-time`, {
      check_in_date: editFormData.value.check_in_date,
      check_in_time: checkInTime,
      check_out_date: editFormData.value.check_out_date,
      check_out_time: checkOutTime,

      customer_name: editFormData.value.customer_name,
      customer_phone: editFormData.value.customer_phone,
      customer_email: editFormData.value.customer_email,
      address: editFormData.value.address,
    });

    alert('Cập nhật thành công!\n' + res.data.message);
    showEditForm.value = false;
    await fetchRooms();
  } catch (e) {
    console.error("Lỗi cập nhật:", e);
    alert(e.response?.data?.message || "Không thể cập nhật thông tin.");
  }
};




const onFileChange = (e) => {
  const file = e.target.files[0];
  if (file) imageFile.value = file;
};

const uploadImage = async () => {
  if (!imageFile.value) return alert('Chọn ảnh CCCD trước!');
  isLoading.value = true;
  try {
    const formDataSend = new FormData();
    formDataSend.append('image', imageFile.value);
    const res = await fetch('https://api.fpt.ai/vision/idr/vnm/', { method: 'POST', headers: { api_key: apiKey }, body: formDataSend });
    const data = await res.json();
    if (data && data.data && data.data.length > 0) {
      const d = data.data[0];
      formData.value.customer_name = d.name || '';
      formData.value.address = d.home || '';
      formData.value.customer_id_number = d.id || '';
      alert('Lấy thông tin từ CCCD thành công!');
    } else alert('Không nhận diện được CCCD!');
  } catch (e) {
    console.error('Lỗi gửi CCCD:', e);
    alert('Đã xảy ra lỗi khi gửi ảnh.');
  } finally {
    isLoading.value = false;
  }
};

const checkoutRoom = async (room) => {
  if (!room.booking_detail_id) {
    alert('Không tìm thấy thông tin đặt phòng cho phòng này.');
    return;
  }
  currentBookingDetailId.value = room.booking_detail_id;
  try {
    const res = await axios.get(`${apiUrl}/api/services/indexAllService`);
    allServices.value = res.data.map(service => ({ ...service, price: Number(service.price) || 0, quantity: 0 }));
    additionalFee.value = 0;
    surchargeReason.value = '';
    showServiceModal.value = true;
  } catch (error) {
    console.error("Không thể tải dịch vụ:", error);
    alert("Không thể tải danh sách dịch vụ.");
  }
};

const confirmPayment = async () => {
  if (!window.confirm("Xác nhận thanh toán và trả phòng?")) return;
  try {
    const services = allServices.value
      .filter(s => Number(s.quantity) > 0)
      .map(s => ({ service_id: Number(s.service_id), quantity: Number(s.quantity) }));

    const response = await axios.post(`${apiUrl}/api/booking-details/${currentBookingDetailId.value}/checkout`, {
      services,
      additional_fee: additionalFee.value,
      surcharge_reason: surchargeReason.value
    });
    const data = response.data;

    const alertMessage = [
      data.message,
      `\n--------------------------------`,
      `🛏️ Tiền phòng: ${Number(data.room_total || 0).toLocaleString('vi-VN')} VND`,
      `   ➡️ Cách tính: ${data.calculation_note || 'N/A'}`,
      `🧾 Tiền dịch vụ: ${Number(data.service_total || 0).toLocaleString('vi-VN')} VND`,
      `➕ Phí phụ thu: ${Number(data.additional_fee || 0).toLocaleString('vi-VN')} VND` + (data.surcharge_reason ? ` (Lý do: ${data.surcharge_reason})` : ''),
      `--------------------------------`,
      `💳 TỔNG THANH TOÁN: ${Number(data.actual_total || 0).toLocaleString('vi-VN')} VND`,
      data.booking_completed ? `📋 TỔNG TIỀN BOOKING: ${Number(data.booking_total || 0).toLocaleString('vi-VN')} VND` : '',
      `\n📝 Ghi chú: ${data.note || 'Không có'}`
    ].filter(line => line).join('\n');

    alert(alertMessage);

    showServiceModal.value = false;
    await fetchRooms();
  } catch (error) {
    console.error("Lỗi thanh toán:", error);
    const errorMessage = error.response?.data?.message || "Không thể thanh toán phòng.";
    alert(errorMessage);
  }
};

const mapApiStatusToVietnamese = (s) => {
  if (s === 'available') return 'Còn trống';
  if (s === 'occupied' || s === 'pending' || s === 'confirmed' || s === 'cancellation_requested') return 'Đã đặt';
  return 'Không xác định';
};

const mapBedCountToString = (c) => c === 1 ? 'Giường đơn' : c === 2 ? 'Giường đôi' : `${c} giường`;

const fetchRooms = async () => {
  isLoading.value = true;
  try {
    const res = await axios.get(`${apiUrl}/api/occupancy/by-date`, {
      params: {
        date: selectedDate.value,
        time: selectedTime.value || undefined
      }
    });
    allRooms.value = res.data.map(r => ({
      room_id: r.room_id,
      number: r.room_name,
      floor: r.floor_number,
      status: mapApiStatusToVietnamese(r.status),
      type: r.type_name,
      bedSize: mapBedCountToString(r.bed_count),
      max_occupancy: r.max_occupancy || 0,
      max_occupancy_child: r.max_occupancy_child || 0,
      booking_detail_id: r.booking_detail_id || null,
      payment_status: r.payment_status || 'pending'
    }));
  } catch (e) {
    console.error("Lỗi load phòng:", e);
    alert("Không thể tải thông tin phòng. Vui lòng thử lại.");
  } finally {
    isLoading.value = false;
  }
};

const showAddGuest = (room_id) => {
  const now = new Date();
  const checkInDate = now.toISOString().slice(0, 10);
  const checkInTime = now.toTimeString().slice(0, 5); // 'HH:mm'

  const out = new Date(now.getTime() + 2 * 60 * 60 * 1000);
  const checkOutTime = out.toTimeString().slice(0, 5);
  const checkOutDate = out.toISOString().slice(0, 10);

  formData.value = {
    customer_name: '',
    customer_phone: '',
    customer_email: '',
    address: '',
    customer_id_number: '',
    room_id,
    check_in_date: checkInDate,
    check_in_time: checkInTime,
    check_out_date: checkOutDate,
    check_out_time: checkOutTime,
    pricing_type: 'hourly'
  };

  showForm.value = true;
  pricePreviewError.value = '';
  calculateTotalPricePreview();
};

const submitCustomerForm = async () => {
  if (!window.confirm("Xác nhận lưu khách hàng?")) return;
  try {
    const res = await axios.post(`${apiUrl}/api/rooms/${formData.value.room_id}/add-guest`, {
      ...formData.value,
      check_in_time: formData.value.check_in_time || '14:00',
      check_out_time: formData.value.check_out_time || '12:00'
    });
    alert(`${res.data.message}\nTổng tiền: ${Number(res.data.total_price).toLocaleString('vi-VN')} VND`);
    showForm.value = false;
    await fetchRooms();
  } catch (e) {
    console.error("Lỗi gửi dữ liệu:", e);
    const errorMessage = e.response?.data?.message || "Không thể lưu thông tin khách.";
    alert(errorMessage);
  }
};

const calculateTotalPricePreview = async () => {
  if (!formData.value.room_id || !formData.value.check_in_date || !formData.value.check_out_date) {
    totalPricePreview.value = null;
    pricePreviewError.value = 'Vui lòng nhập đầy đủ thông tin phòng và thời gian.';
    return;
  }
  try {
    const res = await axios.post(`${apiUrl}/api/rooms/preview-price`, {
      ...formData.value,
      check_in_time: formData.value.check_in_time || '14:00',
      check_out_time: formData.value.check_out_time || '12:00',
      is_extend: false
    });
    if (res.data.total_price && isFinite(res.data.total_price)) {
      totalPricePreview.value = res.data.total_price;
      pricePreviewError.value = '';
    } else {
      totalPricePreview.value = null;
      pricePreviewError.value = 'API trả về giá không hợp lệ.';
      console.error('API trả về total_price không hợp lệ:', res.data);
    }
  } catch (e) {
    totalPricePreview.value = null;
    pricePreviewError.value = e.response?.data?.message || 'Lỗi tính giá phòng. Vui lòng kiểm tra thời gian đặt phòng.';
    console.error('Lỗi tính giá:', e.response?.data?.message || e.message);
    if (e.response?.status === 422) {
      alert('Thời gian đặt phòng không hợp lệ: ' + (e.response.data.message || 'Vui lòng kiểm tra lại.'));
    }
  }
};

const showGuestDetails = async (room) => {
  try {
    const res = await axios.get(`${apiUrl}/api/rooms/${room.room_id}/customer`, {
      params: {
        date: selectedDate.value,
        time: selectedTime.value,
      },
    });
    guestInfo.value = res.data;
    showGuestModal.value = true;
  } catch (e) {
    if (e.response?.status === 404) {
      alert("Không tìm thấy thông tin khách tại thời điểm này.");
    } else {
      alert("Lỗi khi lấy thông tin khách: " + (e.response?.data?.message || 'Lỗi server.'));
      console.error('Lỗi showGuestDetails:', e);
    }
  }
};


const submitExtendForm = async () => {
  try {
    const res = await axios.post(`${apiUrl}/api/booking-details/${extendForm.value.booking_detail_id}/extend`, { ...extendForm.value });
    alert(res.data.message + '\nTổng tiền mới: ' + res.data.total_price);
    showExtendModal.value = false;
    await fetchRooms();
  } catch (e) {
    console.error("Lỗi gia hạn:", e);
    const errorMessage = e.response?.data?.message || "Không thể gia hạn.";
    alert(errorMessage);
  }
};

const getActualCheckout = (booking) => {
  if (!booking || !booking.actual_check_out_time) return 'Chưa trả';
  return booking.actual_check_out_time;
};

const roomTypes = computed(() => ['Tất cả', ...[...new Set(allRooms.value.map(r => r.type))].sort()]);
const floors = computed(() => ['Tất cả', ...[...new Set(allRooms.value.map(r => r.floor))].sort((a, b) => a - b).map(f => `Tầng ${f}`)]);
const filteredRooms = computed(() => allRooms.value.filter(r => 
  (selectedStatus.value === 'Tất cả' || r.status === selectedStatus.value) && 
  (selectedRoomType.value === 'Tất cả' || r.type === selectedRoomType.value) && 
  (selectedFloor.value === 'Tất cả' || `Tầng ${r.floor}` === selectedFloor.value)
));
const groupedAndSortedRooms = computed(() => {
  const groups = {};
  for (const room of filteredRooms.value) {
    if (!groups[room.floor]) groups[room.floor] = [];
    groups[room.floor].push(room);
  }
  return Object.keys(groups).sort((a, b) => a - b).map(f => ({ floor: f, rooms: groups[f] }));
});

const clearFilters = () => {
  selectedStatus.value = 'Tất cả';
  selectedRoomType.value = 'Tất cả';
  selectedFloor.value = 'Tất cả';
  selectedTime.value = new Date().toLocaleTimeString('it-IT', { hour: '2-digit', minute: '2-digit' });
  fetchRooms();
};

onMounted(fetchRooms);
watch(() => [formData.value.check_in_date, formData.value.check_in_time, formData.value.check_out_date, formData.value.check_out_time, formData.value.pricing_type, formData.value.room_id], calculateTotalPricePreview, { deep: true });
watch(() => [selectedDate, selectedTime], fetchRooms);
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700&display=swap');
@import url('https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css');

.page-container {
  font-family: 'Be Vietnam Pro', sans-serif;
  background-color: #f4f7f9;
  padding: 2rem;
  color: #34495e;
}
.page-header { border-bottom: 1px solid #e5eaee; padding-bottom: 1rem; }
.page-title { font-size: 2rem; font-weight: 700; }
.page-subtitle { font-size: 1rem; color: #7f8c8d; }

.filter-card { background-color: #ffffff; border: none; border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); }
.form-label { font-weight: 500; margin-bottom: 0.5rem; font-size: 0.875rem; }
.form-control, .form-select { border-radius: 8px; border: 1px solid #e5eaee; transition: all 0.2s ease-in-out; }
.form-control:focus, .form-select:focus { border-color: #3498db; box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.15); }
.form-control[type="time"] { width: 100%; }

.floor-section { margin-bottom: 2.5rem; }
.floor-header { font-size: 1.75rem; font-weight: 600; color: #2c3e50; padding-bottom: 0.75rem; border-bottom: 2px solid #e5eaee; margin-bottom: 1.5rem; }
.room-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 1.5rem; }
@media (max-width: 1200px) { .room-grid { grid-template-columns: repeat(4, 1fr); } }
@media (max-width: 992px) { .room-grid { grid-template-columns: repeat(3, 1fr); } }
@media (max-width: 768px) { .room-grid { grid-template-columns: repeat(2, 1fr); } }

.room-capacity {
  font-size: 0.8rem;
  color: #7f8c8d;
  margin-bottom: 0;
  display: flex;
  align-items: center;
  gap: 0.4rem;
}

.room-capacity .bi {
  color: #3498db;
}

.room-card {
  background-color: #ffffff;
  border-radius: 12px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
  border: 1px solid #e5eaee;
  transition: all 0.2s ease-in-out;
  display: flex; flex-direction: column;
}
.room-card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08); }

.room-card.booked { border-left: 4px solid #f39c12; }
.room-card:not(.booked) { border-left: 4px solid #3498db; }
.badge-available { background-color: #eaf6fb; color: #3498db; }
.badge-booked { background-color: #fef5e7; color: #f39c12; }
.badge-paid { background-color: #d4edda; color: #155724; margin-left: 0.5rem; }

.room-card .card-header { display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 1rem; border-bottom: 1px solid #f0f2f5; }
.room-card .room-number { font-size: 1.5rem; font-weight: 700; margin: 0; color: #2c3e50; }
.room-card .badge { padding: 0.4em 0.8em; font-size: 0.7rem; font-weight: 600; border-radius: 20px; }
.room-card .card-body { padding: 1rem; flex-grow: 1; }
.room-card .room-type { font-weight: 600; margin-bottom: 0.25rem; }
.room-card .room-bedsize { font-size: 0.85rem; color: #7f8c8d; margin-bottom: 0; }
.room-card .card-footer { background-color: #fafbfc; padding: 0.75rem 1rem; border-top: 1px solid #f0f2f5; }

.action-grid .action-row { display: flex; gap: 0.5rem; }
.action-grid .action-row .btn { flex-grow: 1; }

.modal-backdrop { background-color: rgba(0, 0, 0, 0.4); }
.modal-custom { border-radius: 16px; border: none; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); }
.modal-header-custom { background-color: #f4f7f9; border-bottom: 1px solid #e5eaee; padding: 1.5rem; }
.modal-footer-custom { background-color: #f4f7f9; border-top: 1px solid #e5eaee; padding: 1rem 1.5rem; }

.info-title { font-weight: 600; color: #34495e; margin-bottom: 1rem; font-size: 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid #3498db; display: inline-block; }
.info-list { list-style: none; padding: 0; margin: 0; }
.info-list li { display: flex; justify-content: space-between; align-items: baseline; gap: 1rem; padding: 0.6rem 0.5rem; border-bottom: 1px solid #e5eaee; font-size: 0.9rem; }
.info-list li:last-child { border-bottom: none; }
.info-list li span { color: #7f8c8d; flex-shrink: 0; }
.info-list li strong { color: #34495e; text-align: right; word-break: break-word; }

.btn-primary { background-color: #3498db; border-color: #3498db; }
.btn-primary:hover { background-color: #2980b9; border-color: #2980b9; }
.btn-outline-primary { color: #3498db; border-color: #3498db; }
.btn-outline-primary:hover { background-color: #3498db; color: white; }
.btn-outline-warning { color: #f39c12; border-color: #f39c12; }
.btn-outline-warning:hover { background-color: #f39c12; color: white; }

.service-item { display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0; }
.qty-controls { display: flex; align-items: center; gap: 0.5rem; }
.qty-controls input { width: 50px; }
</style>