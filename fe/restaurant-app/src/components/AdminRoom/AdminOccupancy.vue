<template>
  <isLoading v-if="isisLoading"></isLoading>
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
          <div class="col-lg-3 col-md-6">
            <label for="filter-date" class="form-label">Xem theo ngày</label>
            <input type="date" id="filter-date" class="form-control" v-model="selectedDate" @change="fetchRooms" />
          </div>
          <div class="col-lg-2 col-md-6">
            <label for="status" class="form-label">Trạng thái</label>
            <select id="status" class="form-select" v-model="selectedStatus">
              <option>Tất cả</option>
              <option>Còn trống</option>
              <option>Đã đặt</option>
            </select>
          </div>
          <div class="col-lg-3 col-md-6">
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
              <span class="badge" :class="room.status === 'Đã đặt' ? 'badge-booked' : 'badge-available'">
                {{ room.status }}
              </span>
            </div>
            <div class="card-body">
              <p class="room-type">{{ room.type }}</p>
              <p class="room-bedsize">{{ room.bedSize }}</p>
            </div>
            <div class="card-footer">
              <!-- [THAY ĐỔI] Bố cục nút bấm mới -->
              <div v-if="room.status === 'Đã đặt'" class="action-grid">
                <div class="action-row">
                  <button class="btn btn-sm btn-outline-primary" @click.prevent="showGuestDetails(room)">Chi tiết</button>
                  <button class="btn btn-sm btn-outline-warning" @click.prevent="showExtendForm(room.room_id)">Gia hạn</button>
                </div>
                <button class="btn btn-sm btn-outline-danger w-100 mt-2" @click.prevent="checkoutRoom(room.room_id)">Trả phòng</button>
              </div>
              <button v-else class="btn btn-sm btn-outline-primary w-100" @click.prevent="showAddGuest(room.room_id)">
                <i class="bi bi-person-plus-fill me-1"></i> Thêm khách
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div v-else class="alert alert-light text-center">
      Không có phòng nào khớp với bộ lọc hiện tại.
    </div>
    
    <!-- ======================= MODALS ======================= -->
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
              <div class="col-md-6"><label class="form-label">Ngày giờ nhận phòng</label><input type="datetime-local" v-model="formData.check_in_date" required class="form-control" /></div>
              <div class="col-md-6"><label class="form-label">Ngày giờ trả phòng</label><input type="datetime-local" v-model="formData.check_out_date" required class="form-control" /></div>
              <div v-if="totalPricePreview" class="col-12 mt-3"><label class="form-label">Tổng tiền ước tính:</label><div class="fw-bold fs-5 text-success">{{ Number(totalPricePreview).toLocaleString('vi-VN') + ' VND' }}</div></div>
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
                <div class="col-md-6"><h6 class="info-title">Thông tin phòng</h6><ul class="info-list"><li><span>Phòng:</span><strong>{{ guestInfo.room.room_name }} (Tầng {{ guestInfo.room.floor_number }})</strong></li><li><span>Loại phòng:</span><strong>{{ guestInfo.room.type_name }}</strong></li><li><span>Trạng thái phòng:</span><strong>{{ guestInfo.room?.status }}</strong></li></ul></div>
                 <div class="col-md-6"><h6 class="info-title">Thông tin khách hàng</h6><ul class="info-list"><li><span>Họ tên:</span><strong>{{ guestInfo.customer?.customer_name || 'N/A' }}</strong></li><li><span>SĐT:</span><strong>{{ guestInfo.customer?.customer_phone || 'N/A' }}</strong></li><li><span>Email:</span><strong>{{ guestInfo.customer?.customer_email || 'N/A' }}</strong></li><li><span>Địa chỉ:</span><strong>{{ guestInfo.customer?.address || 'N/A' }}</strong></li></ul></div>
                 <div class="col-12"><h6 class="info-title">Chi tiết lưu trú</h6><ul class="info-list"><li><span>Nhận phòng:</span><strong>{{ guestInfo.booking?.check_in_date || 'N/A' }}</strong></li><li><span>Trả phòng dự kiến:</span><strong>{{ guestInfo.booking?.check_out_date || 'N/A' }}</strong></li><li><span>Trả phòng thực tế:</span><strong>{{ getActualCheckout(guestInfo.booking) }}</strong></li><li><span>Tổng tiền:</span><strong class="text-success fs-6">{{ guestInfo.booking?.total_price ? Number(guestInfo.booking.total_price).toLocaleString('vi-VN') + ' VND' : 'N/A' }}</strong></li></ul></div>
            </div>
          </div>
          <div class="modal-footer modal-footer-custom"><button type="button" class="btn btn-secondary" @click="showGuestModal = false">Đóng</button><button type="button" class="btn btn-primary" @click="editCustomerInfo(guestInfo.customer)">Sửa thông tin</button></div>
        </div>
      </div>
    </div>
    
    <!-- [THÊM LẠI] Modal Gia hạn -->
    <div v-if="showExtendModal" class="modal-backdrop fade show"></div>
    <div v-if="showExtendModal" class="modal fade show d-block" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <form @submit.prevent="submitExtendForm" class="modal-content modal-custom">
          <div class="modal-header modal-header-custom"><h5 class="modal-title">Gia hạn thuê phòng</h5><button type="button" class="btn-close" @click="showExtendModal = false"></button></div>
          <div class="modal-body p-4">
            <div class="mb-3">
              <label class="form-label">Ngày giờ trả mới:</label>
              <input type="datetime-local" v-model="extendForm.check_out_date" required class="form-control" />
            </div>
          </div>
          <div class="modal-footer modal-footer-custom">
            <button type="button" class="btn btn-secondary" @click="showExtendModal = false">Hủy</button>
            <button type="submit" class="btn btn-primary">Xác nhận</button>
          </div>
        </form>
      </div>
    </div>

    <!-- [THÊM LẠI] Modal Dịch vụ -->
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
          <div class="modal-footer modal-footer-custom">
            <button type="button" class="btn btn-secondary" @click="showServiceModal = false">Hủy</button>
            <button type="button" class="btn btn-danger" @click="confirmPayment">Xác nhận trả phòng</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
// --- TOÀN BỘ SCRIPT CỦA BẠN GIỮ NGUYÊN ---
import { ref, computed, onMounted, watch } from 'vue';
import axios from 'axios';
import { inject } from 'vue';
import isLoading from '../loading.vue'

const apiUrl = inject('apiUrl');
const allRooms = ref([]);
const isisLoading = ref(false);
const selectedStatus = ref('Tất cả');
const selectedRoomType = ref('Tất cả');
const selectedFloor = ref('Tất cả');
const selectedDate = ref(new Date().toISOString().substr(0, 10));
const showForm = ref(false);
const totalPricePreview = ref(null);
const formData = ref({
  customer_name: '', customer_phone: '', customer_email: '', address: '',
  customer_id_number: '', room_id: null, check_in_date: '',
  check_out_date: '', pricing_type: 'nightly'
});
const showEditForm = ref(false);
const editFormData = ref({
  customer_id: null, customer_name: '', customer_phone: '',
  customer_email: '', address: '',
});
const showServiceModal = ref(false);
const allServices = ref([]);
const currentRoomId = ref(null);
const showGuestModal = ref(false);
const guestInfo = ref({});
const showExtendModal = ref(false);
const extendForm = ref({ room_id: null, check_out_date: '' });
const additionalFee = ref(0);
const surchargeReason = ref('');
const apiKey = 'XXjjI5g9j7gk4NcZE9Dh9PPLCrvrR6zJ';
const imageFile = ref(null);

const serviceTotal = computed(() => allServices.value.reduce((sum, s) => sum + s.price * s.quantity, 0));
const increaseQty = (index) => { allServices.value[index].quantity++; };
const decreaseQty = (index) => { if (allServices.value[index].quantity > 0) allServices.value[index].quantity--; };
const formatPrice = (price) => price.toLocaleString('vi-VN') + ' VND';

const editCustomerInfo = (customer) => {
  if (!customer) return;
  editFormData.value = { ...customer };
  showEditForm.value = true;
};

const submitEditForm = async () => {
  try {
    const res = await axios.post(`${apiUrl}/api/customers/${editFormData.value.customer_id}/update-name`, { ...editFormData.value });
    alert(res.data.message || 'Cập nhật thành công.');
    guestInfo.value.customer = { ...editFormData.value };
    showEditForm.value = false;
  } catch (e) {
    alert("Không thể cập nhật.");
    console.error(e);
  }
};

const onFileChange = (e) => {
  const file = e.target.files[0];
  if (file) imageFile.value = file;
};

const uploadImage = async () => {
  if (!imageFile.value) return alert('Chọn ảnh CCCD trước!');
  isisLoading.value = true;
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
    isisLoading.value = false;
  }
};

const checkoutRoom = async (room_id) => {
  currentRoomId.value = room_id;
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
    const services = allServices.value.filter(s => Number(s.quantity) > 0).map(s => ({ service_id: Number(s.service_id), quantity: Number(s.quantity) }));
    const response = await axios.post(`${apiUrl}/api/rooms/${currentRoomId.value}/checkout`, {
      services, date: selectedDate.value, additional_fee: additionalFee.value, surcharge_reason: surchargeReason.value
    });
    const data = response.data;
    alert(`${data.message}\n\n💳 Đã thanh toán trước: ${Number(data.paid_total).toLocaleString('vi-VN')} VND\n🛏️ Tiền phòng: ${Number(data.room_total).toLocaleString('vi-VN')} VND\n🧾 Dịch vụ: ${Number(data.service_total).toLocaleString('vi-VN')} VND\n➕ Phí phụ thu: ${Number(data.additional_fee).toLocaleString('vi-VN')} VND${data.surcharge_reason ? ` (Lý do: ${data.surcharge_reason})` : ''}\n💰 Tổng phải trả: ${Number(data.actual_total).toLocaleString('vi-VN')} VND\n\n${data.note || ''}`);
    showServiceModal.value = false;
    await fetchRooms();
  } catch (error) {
    console.error("Lỗi thanh toán:", error);
    const errorMessage = error.response?.data?.message || "Không thể thanh toán phòng.";
    alert(errorMessage);
  }
};

const mapApiStatusToVietnamese = (s) => s === 'available' ? 'Còn trống' : s === 'occupied' ? 'Đã đặt' : 'Không xác định';
const mapBedCountToString = (c) => c === 1 ? 'Giường đơn' : c === 2 ? 'Giường đôi' : `${c} giường`;

const fetchRooms = async () => {
  isisLoading.value = true;
  try {
    const res = await axios.get(`${apiUrl}/api/occupancy/by-date`, { params: { date: selectedDate.value } });
    allRooms.value = res.data.map(r => ({
      room_id: r.room_id, number: r.room_name, floor: r.floor_number,
      status: mapApiStatusToVietnamese(r.status), type: r.type_name,
      bedSize: mapBedCountToString(r.bed_count),
    }));
  } catch (e) {
    console.error("Lỗi load phòng:", e);
  } finally {
    isisLoading.value = false;
  }
};

const showAddGuest = (room_id) => {
  formData.value = {
    customer_name: '', customer_phone: '', customer_email: '', address: '',
    customer_id_number: '', room_id, check_in_date: '',
    check_out_date: '', pricing_type: 'nightly'
  };
  showForm.value = true;
  calculateTotalPricePreview();
};

const submitCustomerForm = async () => {
  if (!window.confirm("Xác nhận lưu khách hàng?")) return;
  try {
    const res = await axios.post(`${apiUrl}/api/rooms/${formData.value.room_id}/add-guest`, formData.value);
    alert(`${res.data.message}\nTổng tiền: ${res.data.total_price}`);
    showForm.value = false;
    await fetchRooms();
  } catch (e) {
    console.error("Lỗi gửi dữ liệu:", e);
    alert("Không thể lưu thông tin khách.");
  }
};

const calculateTotalPricePreview = async () => {
  if (!formData.value.room_id) return;
  try {
    const res = await axios.post(`${apiUrl}/api/rooms/preview-price`, { ...formData.value, is_extend: false });
    totalPricePreview.value = res.data.total_price;
  } catch (e) {
    totalPricePreview.value = 'Không tính được';
  }
};

const roomTypes = computed(() => ['Tất cả', ...[...new Set(allRooms.value.map(r => r.type))].sort()]);
const floors = computed(() => ['Tất cả', ...[...new Set(allRooms.value.map(r => r.floor))].sort((a, b) => a - b).map(f => `Tầng ${f}`)]);
const filteredRooms = computed(() => allRooms.value.filter(r => (selectedStatus.value === 'Tất cả' || r.status === selectedStatus.value) && (selectedRoomType.value === 'Tất cả' || r.type === selectedRoomType.value) && (selectedFloor.value === 'Tất cả' || `Tầng ${r.floor}` === selectedFloor.value)));
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
};

const showGuestDetails = async (room) => {
  try {
    const res = await axios.get(`${apiUrl}/api/rooms/${room.room_id}/customer`, { params: { date: selectedDate.value } });
    guestInfo.value = res.data;
    showGuestModal.value = true;
  } catch (e) {
    alert("Không tìm thấy thông tin khách.");
    console.error(e);
  }
};

const showExtendForm = (room_id) => {
  extendForm.value = { room_id, check_out_date: '' };
  showExtendModal.value = true;
};

const submitExtendForm = async () => {
  try {
    const res = await axios.post(`${apiUrl}/api/rooms/${extendForm.value.room_id}/extend`, { ...extendForm.value });
    alert(res.data.message + '\nTổng tiền mới: ' + res.data.total_price);
    showExtendModal.value = false;
    await fetchRooms();
  } catch (e) {
    console.error("Lỗi gia hạn:", e);
    alert("Không thể gia hạn.");
  }
};

const getActualCheckout = (booking) => {
  if (!booking || !booking.actual_check_out_time) return 'Chưa trả';
  return booking.actual_check_out_time;
};

onMounted(fetchRooms);
watch(() => [formData.value.check_in_date, formData.value.check_out_date, formData.value.pricing_type], calculateTotalPricePreview);
watch(() => formData.value.room_id, () => { if (formData.value.check_in_date && formData.value.check_out_date) calculateTotalPricePreview(); });
</script>

<style scoped>
/* Copied styles from other components for consistency */
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

/* Grid Styles */
.floor-section { margin-bottom: 2.5rem; }
.floor-header { font-size: 1.75rem; font-weight: 600; color: #2c3e50; padding-bottom: 0.75rem; border-bottom: 2px solid #e5eaee; margin-bottom: 1.5rem; }

/* Bố cục 5 cột */
.room-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 1.5rem; }
@media (max-width: 1200px) { .room-grid { grid-template-columns: repeat(4, 1fr); } }
@media (max-width: 992px) { .room-grid { grid-template-columns: repeat(3, 1fr); } }
@media (max-width: 768px) { .room-grid { grid-template-columns: repeat(2, 1fr); } }

.room-card {
  background-color: #ffffff;
  border-radius: 12px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
  border: 1px solid #e5eaee;
  transition: all 0.2s ease-in-out;
  display: flex; flex-direction: column;
}
.room-card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08); }

/* Bảng màu "Blue" */
.room-card.booked { border-left: 4px solid #f39c12; }
.room-card:not(.booked) { border-left: 4px solid #3498db; }
.badge-available { background-color: #eaf6fb; color: #3498db; }
.badge-booked { background-color: #fef5e7; color: #f39c12; }

.room-card .card-header { display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 1rem; border-bottom: 1px solid #f0f2f5; }
.room-card .room-number { font-size: 1.5rem; font-weight: 700; margin: 0; color: #2c3e50; }
.room-card .badge { padding: 0.4em 0.8em; font-size: 0.7rem; font-weight: 600; border-radius: 20px; }
.room-card .card-body { padding: 1rem; flex-grow: 1; }
.room-card .room-type { font-weight: 600; margin-bottom: 0.25rem; }
.room-card .room-bedsize { font-size: 0.85rem; color: #7f8c8d; margin-bottom: 0; }
.room-card .card-footer { background-color: #fafbfc; padding: 0.75rem 1rem; border-top: 1px solid #f0f2f5; }

/* Bố cục nút bấm mới */
.action-grid .action-row { display: flex; gap: 0.5rem; }
.action-grid .action-row .btn { flex-grow: 1; }

/* Modal Styles */
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

/* Nút bấm theo chủ đề blue */
.btn-primary { background-color: #3498db; border-color: #3498db; }
.btn-primary:hover { background-color: #2980b9; border-color: #2980b9; }
.btn-outline-primary { color: #3498db; border-color: #3498db; }
.btn-outline-primary:hover { background-color: #3498db; color: white; }
.btn-outline-warning { color: #f39c12; border-color: #f39c12; }
.btn-outline-warning:hover { background-color: #f39c12; color: white; }

/* Modal Dịch vụ */
.service-item { display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0; }
.qty-controls { display: flex; align-items: center; gap: 0.5rem; }
.qty-controls input { width: 50px; }
</style>