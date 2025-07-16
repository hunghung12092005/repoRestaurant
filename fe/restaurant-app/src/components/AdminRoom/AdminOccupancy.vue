<template>
  <isLoading v-if="isisLoading"></isLoading>
  <div class="occupancy-page">
    <div class="header">
      <h1 class="title">Quản lý Phòng</h1>
      <div class="header-link">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-house-door-fill"
          viewBox="0 0 16 16">
          <path
            d="M6.5 14.5v-3.505c0-.245.25-.495.5-.495h2c.25 0 .5.25.5.495v3.505A.5.5 0 0 0 10 15h2a.5.5 0 0 0 .5-.5V8.207l.646.647a.5.5 0 0 0 .708-.708L8.354 1.146a.5.5 0 0 0-.708 0l-6 6a.5.5 0 0 0 .708.708L2.5 8.207V14.5a.5.5 0 0 0 .5.5h2a.5.5 0 0 0 .5-.5z" />
        </svg>
        <span>Quản lý phòng</span>
      </div>
    </div>

    <div class="filter-container">
      <div class="filter-group">
        <label for="status">Trạng thái:</label>
        <select id="status" v-model="selectedStatus">
          <option>Tất cả</option>
          <option>Còn trống</option>
          <option>Đã đặt</option>
        </select>
      </div>
      <div class="filter-group">
        <label for="room-type">Loại phòng:</label>
        <select id="room-type" v-model="selectedRoomType">
          <option v-for="type in roomTypes" :key="type" :value="type">{{ type }}</option>
        </select>
      </div>
      <div class="filter-group">
        <label for="floor">Tầng:</label>
        <select id="floor" v-model="selectedFloor">
          <option v-for="floor in floors" :key="floor" :value="floor">{{ floor }}</option>
        </select>
        </div>
          <div class="filter-group">
      <label for="filter-date">Ngày:</label>
      <input type="date" id="filter-date" v-model="selectedDate" @change="fetchRooms" />
    </div>

        <button @click="clearFilters" class="clear-btn">Xóa lọc</button>
      </div>


    <div>
      <div v-for="floorGroup in groupedAndSortedRooms" :key="floorGroup.floor" class="floor-section">

        <div class="floor-header-container">
          <h2 class="floor-header-text">Tầng {{ floorGroup.floor }}</h2>
          <div class="floor-header-line"></div>
        </div>

        <div class="room-grid">
          <div v-for="room in floorGroup.rooms" :key="room.room_id" class="room-card">
            <div :class="['status-badge', room.status === 'Đã đặt' ? 'booked' : 'available']">
              {{ room.status }}
            </div>

            <p class="room-label">Phòng</p>
            <h2 class="room-number">{{ room.number }}</h2>

            <h3 class="room-type">{{ room.type }}</h3>
            <p class="bed-size">{{ room.bedSize }}</p>

            <div v-if="room.status === 'Đã đặt'">
              <button class="action-link" @click.prevent="showGuestDetails(room)">
                Chi tiết
              </button>
              <br>
              <a href="#" class="action-link" @click.prevent="checkoutRoom(room.room_id)"><button>Thanh
                  toán</button></a>
              <br>
              <a href="#" class="action-link" @click.prevent="showExtendForm(room.room_id)"><button>Gia hạn</button></a>

            </div>
            <a v-else href="#" class="action-link" @click.prevent="showAddGuest(room.room_id)">
              <button>Thêm khách</button>
            </a>

          </div>
        </div>
      </div>
      <div v-if="filteredRooms.length === 0" class="no-results">
        Không có phòng nào khớp với bộ lọc hiện tại.
      </div>
    </div>
  </div>
  <!-- Modal Form Thêm Khách -->
  <div v-if="showForm" class="modal-overlay">
    <div class="modal-content">
      <h2>Đăng ký khách hàng</h2>
      <form @submit.prevent="submitCustomerForm">
        <div class="form-group">
          <label>Ảnh CCCD</label>
          <input type="file" @change="onFileChange" accept="image/*" />
          <button type="button" @click="uploadImage">Tải Ảnh CCCD</button>
        </div>

        <div class="form-group">

          <label>Họ tên</label>
          <input v-model="formData.customer_name" required />
        </div>

          <div class="form-group">
            <label>Số điện thoại</label>
            <input v-model="formData.customer_phone" required />
          </div>
          <div class="form-group">
    <label>Số CCCD</label>
    <input v-model="formData.customer_id_number" required />
  </div>

          <div class="form-group">
            <label>Email</label>
            <input v-model="formData.customer_email" type="email" required />
          </div>

        <div class="form-group">
          <label>Địa chỉ</label>
          <input v-model="formData.address" />
        </div>
        <input type="hidden" v-model="formData.pricing_type" />

        <div class="form-group">
          <label>Ngày giờ nhận phòng</label>
          <input type="datetime-local" v-model="formData.check_in_date" required />
        </div>
        <div class="form-group">
          <label>Ngày giờ trả phòng</label>
          <input type="datetime-local" v-model="formData.check_out_date" required />
        </div>
        <div class="form-actions">
          <div v-if="totalPricePreview" class="form-group">
            <label>Tổng tiền ước tính: </label>
            <div style="font-weight: bold; color: #2c3e50;">
              {{ Number(totalPricePreview).toLocaleString('vi-VN') + ' VND' }}
            </div>
          </div>

          <button type="submit">Lưu</button>
          <button type="button" @click="showForm = false">Hủy</button>
        </div>
      </form>
    </div>
  </div>
  <div v-if="showGuestModal" class="modal-overlay">
    <div class="modal-content">
      <h2>Chi tiết phòng & khách</h2>

      <!-- Chi tiết Phòng -->
      <p v-if="guestInfo.room"><strong>Phòng:</strong> {{ guestInfo.room.room_name }} - {{ guestInfo.room.type_name }}
        (Tầng {{ guestInfo.room.floor_number }})</p>
      <p v-else>Đang tải thông tin phòng...</p>

      <p><strong>Trạng thái:</strong> {{ guestInfo.room?.status }}</p>

      <hr />

      <!-- Chi tiết Khách -->
      <p v-if="guestInfo.room"><strong>Khách hàng:</strong> {{ guestInfo.customer?.customer_name || 'Chưa có' }}</p>
      <p v-if="guestInfo.room"><strong>SĐT:</strong> {{ guestInfo.customer?.customer_phone || '...' }}</p>
      <p v-if="guestInfo.room"><strong>Email:</strong> {{ guestInfo.customer?.customer_email || '...' }}</p>
      <p v-if="guestInfo.room"><strong>Địa chỉ:</strong> {{ guestInfo.customer?.address || '...' }}</p>

      <hr />

      <!-- Thông tin đặt phòng -->
      <p v-if="guestInfo.room"><strong>Nhận phòng:</strong> {{ guestInfo.booking?.check_in_date || '...' }}</p>
      <p v-if="guestInfo.room"><strong>Trả phòng dự kiến:</strong> {{ guestInfo.booking?.check_out_date || '...' }}</p>
      <p v-if="guestInfo.room"><strong>Trả phòng thực tế:</strong> {{ getActualCheckout(guestInfo.booking) }} </p>
      <p v-if="guestInfo.room"><strong>Loại giá:</strong> {{ guestInfo.booking?.pricing_type || '...' }}</p>
      <p v-if="guestInfo.room"><strong>Trạng thái:</strong> {{ guestInfo.booking?.status || '...' }}</p>
      <p v-if="guestInfo.room"><strong>Tổng tiền:</strong> {{ guestInfo.booking?.total_price ?
        Number(guestInfo.booking.total_price).toLocaleString('vi-VN') + ' VND' : '...' }}</p>

      <!-- Nút sửa -->
<div class="form-actions">
  <button @click="editCustomerInfo(guestInfo.customer)">Sửa thông tin</button>
  <button @click="showGuestModal = false">Đóng</button>
</div>
    </div>


  </div>
  <div v-if="showExtendModal" class="modal-overlay">
    <div class="modal-content">
      <h2>Gia hạn thuê phòng</h2>
      <form @submit.prevent="submitExtendForm">
        <div class="form-group">
          <label>Ngày giờ trả mới:</label>
          <input type="datetime-local" v-model="extendForm.check_out_date" required />
        </div>
        <div class="form-actions">
          <button type="submit">Xác nhận</button>
          <button type="button" @click="showExtendModal = false">Hủy</button>
        </div>
      </form>
    </div>
  </div>
  <!-- Modal Chọn Dịch Vụ Khi Thanh Toán -->
  <div v-if="showServiceModal" class="modal-overlay">
    <div class="modal-content">
      <h2>Chọn dịch vụ sử dụng</h2>

      <div v-if="allServices.length === 0">Đang tải dịch vụ...</div>

      <div v-else>
        <div v-for="(service, index) in allServices" :key="service.service_id" class="service-item">
  <div>{{ service.service_name }} - {{ formatPrice(service.price) }}</div>
  <div class="qty-controls">
    <button @click="decreaseQty(index)">-</button>
    <input type="number" v-model.number="service.quantity" min="0" />
    <button @click="increaseQty(index)">+</button>
  </div>
</div>

      </div>

      <p style="margin-top: 10px;">
        <strong>Tổng dịch vụ: </strong>
        <span style="color: #e74c3c; font-weight: bold;">
          {{ serviceTotal.toLocaleString('vi-VN') }} VND
        </span>
      </p>

      <div class="form-actions">
        <button @click="confirmPayment">Xác nhận thanh toán</button>
        <button @click="showServiceModal = false">Hủy</button>
      </div>
    </div>
  </div>

<!-- Modal sửa thông tin -->
<div v-if="showEditForm" class="modal-overlay">
  <div class="modal-content">
    <h2>Sửa thông tin khách</h2>
    <form @submit.prevent="submitEditForm">
      <div class="form-group">
        <label>Họ tên:</label>
        <input v-model="editFormData.customer_name" required />
      </div>
      <div class="form-group">
        <label>SĐT:</label>
        <input v-model="editFormData.customer_phone" />
      </div>
      <div class="form-group">
        <label>Email:</label>
        <input v-model="editFormData.customer_email" />
      </div>
      <div class="form-group">
        <label>Địa chỉ:</label>
        <input v-model="editFormData.address" />
      </div>
      <div class="form-actions">
        <button type="submit">Lưu</button>
        <button type="button" @click="showEditForm = false">Hủy</button>
      </div>
    </form>
  </div>
</div>

</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';
import { inject } from 'vue';
import isLoading from '../loading.vue'
const apiUrl = inject('apiUrl');
const allRooms = ref([]);
const isisLoading = ref(false);
// Trạng thái lọc
const selectedStatus = ref('Tất cả');
const selectedRoomType = ref('Tất cả');
const selectedFloor = ref('Tất cả');
const selectedDate = ref(new Date().toISOString().substr(0, 10)); // Ngày mặc định: hôm nay

// Form thêm khách
const showForm = ref(false);
const totalPricePreview = ref(null);
const formData = ref({
  customer_name: '',
  customer_phone: '',
  customer_email: '',
  address: '',
  customer_id_number: '',
  room_id: null,
  check_in_date: '',
  check_out_date: '',
  pricing_type: 'nightly'
});

const showEditForm = ref(false);
const editFormData = ref({
  customer_id: null,
  customer_name: '',
  customer_phone: '',
  customer_email: '',
  address: '',
});

const editCustomerInfo = (customer) => {
  if (!customer) return;
  editFormData.value = {
    customer_id: customer.customer_id,
    customer_name: customer.customer_name,
    customer_phone: customer.customer_phone,
    customer_email: customer.customer_email,
    address: customer.address,
  };
  showEditForm.value = true;
};

const submitEditForm = async () => {
  try {
    const res = await axios.post(`${apiUrl}/api/customers/${editFormData.value.customer_id}/update-name`, {
      customer_name: editFormData.value.customer_name,
      customer_phone: editFormData.value.customer_phone,
      customer_email: editFormData.value.customer_email,
      address: editFormData.value.address,
    });

    alert(res.data.message || 'Cập nhật thành công.');

    // ✅ Cập nhật dữ liệu hiển thị
    guestInfo.value.customer.customer_name = editFormData.value.customer_name;
    guestInfo.value.customer.customer_phone = editFormData.value.customer_phone;
    guestInfo.value.customer.customer_email = editFormData.value.customer_email;
    guestInfo.value.customer.address = editFormData.value.address;

    showEditForm.value = false;
  } catch (e) {
    alert("Không thể cập nhật.");
    console.error(e);
  }
};

// CCCD
const imageFile = ref(null);
const onFileChange = (e) => {
  const file = e.target.files[0];
  if (file) imageFile.value = file;
};
const apiKey = 'XXjjI5g9j7gk4NcZE9Dh9PPLCrvrR6zJ';
const uploadImage = async () => {
  if (!imageFile.value) return alert('Chọn ảnh CCCD trước!');
  isisLoading.value = true;
  try {
    const formDataSend = new FormData();
    formDataSend.append('image', imageFile.value);
    const res = await fetch('https://api.fpt.ai/vision/idr/vnm/', {
      method: 'POST',
      headers: { api_key: apiKey },
      body: formDataSend
    });
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

// Danh sách dịch vụ
const showServiceModal = ref(false);
const allServices = ref([]);
const currentRoomId = ref(null);

const serviceTotal = computed(() =>
  allServices.value.reduce((sum, s) => sum + s.price * s.quantity, 0)
);

const increaseQty = (index) => {
  allServices.value[index].quantity++
};

const decreaseQty = (index) => {
  if (allServices.value[index].quantity > 0) {
    allServices.value[index].quantity--
  }
};

const formatPrice = (price) => price.toLocaleString('vi-VN') + ' VND';


// Thanh toán - mở modal chọn dịch vụ
const checkoutRoom = async (room_id) => {
  currentRoomId.value = room_id;
  try {
    const res = await axios.get(`${apiUrl}/api/services/indexAllService`);
    allServices.value = res.data;
    allServices.value = res.data.map(service => ({
  ...service,
  price: Number(service.price) || 0, // 👈 ép kiểu giá
  quantity: 0
}));    showServiceModal.value = true;
  } catch (error) {
    console.error("Không thể tải dịch vụ:", error);
    alert("Không thể tải danh sách dịch vụ.");
  }
};

// Xác nhận thanh toán và gửi kèm dịch vụ
const confirmPayment = async () => {
  if (!window.confirm("Xác nhận thanh toán và trả phòng?")) return;
  try {
    const services = allServices.value
      .filter(s => Number(s.quantity) > 0)
      .map(s => ({
        service_id: Number(s.service_id),
        quantity: Number(s.quantity)
      }));

    console.log("Dịch vụ sẽ gửi đi:", services); // Thêm dòng này để kiểm tra

    const response = await axios.post(`${apiUrl}/api/rooms/${currentRoomId.value}/checkout`, {
      services: services
    });

    const data = response.data;
    console.log("Thanh toán thành công:", data);
    alert(
      `${data.message}\n\n` +
      `💳 Đã thanh toán trước: ${Number(data.paid_total).toLocaleString('vi-VN')} VND\n` +
      `🛏️ Tiền phòng: ${Number(data.room_total).toLocaleString('vi-VN')} VND\n` +
      `🧾 Dịch vụ: ${Number(data.service_total).toLocaleString('vi-VN')} VND\n` +
      `💰 Tổng phải trả: ${Number(data.actual_total).toLocaleString('vi-VN')} VND\n\n` +
      (data.note || '')
    );

    showServiceModal.value = false;
    await fetchRooms();
  } catch (error) {
    console.error("Lỗi thanh toán:", error);
    alert("Không thể thanh toán phòng.");
  }
};


// Xử lý dữ liệu phòng
const mapApiStatusToVietnamese = (s) => s === 'available' ? 'Còn trống' : s === 'occupied' ? 'Đã đặt' : 'Không xác định';
const mapBedCountToString = (c) => c === 1 ? 'Giường đơn' : c === 2 ? 'Giường đôi' : `${c} giường`;
const fetchRooms = async () => {
  isisLoading.value = true;
  try {
    const res = await axios.get(`${apiUrl}/api/occupancy/by-date`, {
  params: { date: selectedDate.value }
});

    allRooms.value = res.data.map(r => ({
      room_id: r.room_id,
      number: r.room_name,
      floor: r.floor_number,
      status: mapApiStatusToVietnamese(r.status),
      type: r.type_name,
      bedSize: mapBedCountToString(r.bed_count),
    }));
  } catch (e) {
    console.error("Lỗi load phòng:", e);
  } finally {
    isisLoading.value = false;
  }
};

// Mở form thêm khách
const showAddGuest = (room_id) => {
  formData.value = {
    customer_name: '',
    customer_phone: '',
    customer_email: '',
    address: '',
    customer_id_number: '',
    room_id,
    check_in_date: '',
    check_out_date: '',
    pricing_type: 'nightly'
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
    const res = await axios.post(`${apiUrl}/api/rooms/preview-price`, {
      room_id: formData.value.room_id,
      check_in_date: formData.value.check_in_date,
      check_out_date: formData.value.check_out_date,
      pricing_type: formData.value.pricing_type,
      is_extend: false
    });
    totalPricePreview.value = res.data.total_price;
  } catch (e) {
    totalPricePreview.value = 'Không tính được';
  }
};

// Bộ lọc
const roomTypes = computed(() => ['Tất cả', ...[...new Set(allRooms.value.map(r => r.type))].sort()]);
const floors = computed(() => ['Tất cả', ...[...new Set(allRooms.value.map(r => r.floor))].sort((a, b) => a - b).map(f => `Tầng ${f}`)]);
const filteredRooms = computed(() =>
  allRooms.value.filter(r =>
    (selectedStatus.value === 'Tất cả' || r.status === selectedStatus.value) &&
    (selectedRoomType.value === 'Tất cả' || r.type === selectedRoomType.value) &&
    (selectedFloor.value === 'Tất cả' || `Tầng ${r.floor}` === selectedFloor.value)
  )
);
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

// Chi tiết khách
const showGuestModal = ref(false);
const guestInfo = ref({});
const showGuestDetails = async (room) => {
  try {
    const res = await axios.get(`${apiUrl}/api/rooms/${room.room_id}/customer`);
    guestInfo.value = res.data;
    showGuestModal.value = true;
  } catch (e) {
    alert("Không tìm thấy thông tin khách.");
    console.error(e);
  }
};

// Gia hạn
const showExtendModal = ref(false);
const extendForm = ref({ room_id: null, check_out_date: '' });
const showExtendForm = (room_id) => {
  extendForm.value = { room_id, check_out_date: '' };
  showExtendModal.value = true;
};
const submitExtendForm = async () => {
  try {
    const res = await axios.post(`${apiUrl}/api/rooms/${extendForm.value.room_id}/extend`, {
      check_out_date: extendForm.value.check_out_date
    });
    alert(res.data.message + '\nTổng tiền mới: ' + res.data.total_price);
    showExtendModal.value = false;
    await fetchRooms();
  } catch (e) {
    console.error("Lỗi gia hạn:", e);
    alert("Không thể gia hạn.");
  }
};

// Mounted + Watch
onMounted(fetchRooms);
watch(() => [
  formData.value.check_in_date,
  formData.value.check_out_date,
  formData.value.pricing_type
], calculateTotalPricePreview);
watch(() => formData.value.room_id, () => {
  if (formData.value.check_in_date && formData.value.check_out_date) {
    calculateTotalPricePreview();
  }
  
});
const getActualCheckout = (booking) => {
  if (!booking || !booking.actual_check_out_time) return 'Chưa trả';
  return booking.actual_check_out_time;
};
</script>

<style scoped>

/* CSS giữ nguyên như phiên bản trước */
.occupancy-page {
  font-family: 'Be Vietnam Pro', sans-serif;
  /* background-color: #f0f2f5; */
  padding: 24px;
  color: #333;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.title {
  font-size: 28px;
  font-weight: 600;
}

.header-link {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #888;
}

.filter-container {
  background-color: #ffffff;
  padding: 20px;
  border-radius: 8px;
  display: flex;
  align-items: flex-end;
  gap: 24px;
  margin-bottom: 24px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.filter-group {
  position: relative;
  margin-top: 10px;
}

.filter-group label {
  position: absolute;
  top: -8px;
  left: 10px;
  font-size: 12px;
  color: #888;
  background-color: #ffffff;
  padding: 0 4px;
  z-index: 1;
}

.filter-group select {
  padding: 10px 12px;
  border: 1px solid #adb5bd;
  border-radius: 6px;
  min-width: 180px;
  background-color: #fff;
  font-size: 14px;
  -webkit-appearance: none;
  -moz-appearance: none;
  appearance: none;
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
  background-repeat: no-repeat;
  background-position: right 12px center;
  background-size: 16px 12px;
  transition: border-color 0.2s, box-shadow 0.2s;
  outline: none;
}

.filter-group select:focus {
  border-color: #6c5ffc;
  box-shadow: 0 0 0 3px rgba(108, 95, 252, 0.2);
}

.clear-btn {
  background-color: #6c5ffc;
  color: white;
  border: none;
  padding: 10px 24px;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
  height: 42px;
}

.isLoading-state,
.no-results {
  text-align: center;
  padding: 50px;
  font-size: 18px;
  color: #888;
}

.floor-section:not(:last-child) {
  margin-bottom: 40px;
}

.floor-header-container {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-bottom: 20px;
}

.floor-header-text {
  font-size: 33px;
  font-weight: 600;
  color: #333;
  white-space: nowrap;
}

.floor-header-line {
  flex-grow: 1;
  height: 3px;
  background-color: #020202;
}

.room-grid {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 16px;
}

.room-card {
  background-color: #ffffff;
  border: 1px solid #000000;
  border-radius: 12px;
  padding: 16px;
  position: relative;
  transition: box-shadow 0.3s, transform 0.3s;
}

.room-card:hover {
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
  transform: translateY(-5px);
}

.status-badge {
  position: absolute;
  top: 0;
  right: 0;
  padding: 3px 10px;
  border-radius: 0 12px;
  font-size: 12px;
  font-weight: 500;
  color: #ffffff;
}

.status-badge.booked {
  background-color: #FFECDC;
  color: #FD8220;
  font-weight: bold;
}

.status-badge.available {
  background-color: #DDEDE6;
  color: #66B6AA;
  font-weight: bold;
}

.room-label {
  font-size: 13px;
  color: #888;
  margin-bottom: 0;
}

.room-number {
  font-size: 36px;
  font-weight: 700;
  margin: -5px 0 12px 0;
}

.room-type {
  font-size: 16px;
  font-weight: 600;
  margin-bottom: 4px;
}

.bed-size {
  font-size: 13px;
  color: #888;
  margin: 2px 0 20px 0;
}

.action-link {
  color: #6c5ffc;
  text-decoration: none;
  font-weight: 600;
  font-size: 14px;
}

.action-link:hover {
  text-decoration: underline;
}

.modal-overlay {
  z-index: 1000;
  position: fixed;
  z-index: 1000;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
}

.modal-content {
  background: white;
  padding: 24px;
  border-radius: 8px;
  width: 400px;
}

.form-group {
  margin-bottom: 16px;
}

.form-group input {
  width: 100%;
  padding: 8px;
  border: 1px solid #ccc;
  border-radius: 4px;
}

.form-actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
}

button {
  padding: 6px 14px;
  background-color: #4f46e5;
  /* tím dịu */
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease-in-out;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
}

button:hover {
  background-color: #4338ca;
  /* tím đậm hơn */
  transform: translateY(-1px);
}

button:active {
  transform: scale(0.97);
}

button:disabled {
  background-color: #ccc;
  cursor: not-allowed;
  box-shadow: none;
}

button.secondary {
  background-color: #f3f4f6;
  /* xám sáng */
  color: #333;
}
.service-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 0;
  border-bottom: 1px solid #eee;
  font-size: 14px;
}

.service-item:last-child {
  border-bottom: none;
}

.qty-controls {
  display: flex;
  align-items: center;
  gap: 8px;
}

.qty-controls input {
  width: 50px;
  text-align: center;
  padding: 4px 6px;
  border: 1px solid #ccc;
  border-radius: 4px;
}
.qty-controls input {
  width: 50px;
  text-align: center;
  padding: 6px 8px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  background-color: #f9fafb;
  font-weight: bold;
}

button.secondary:hover {
  background-color: #e5e7eb;
  /* xám hover */
}
</style>