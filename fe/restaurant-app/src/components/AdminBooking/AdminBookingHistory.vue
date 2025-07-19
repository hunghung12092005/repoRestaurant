<template>
  <div>
    <h2>Lịch sử đặt phòng</h2>

    <div style="margin: 10px 0;">
      <label>Chọn ngày:</label>
      <input type="date" v-model="selectedDate" @change="fetchBookings" />
      <button @click="clearFilters">Xóa lọc</button>
      <div style="margin: 10px 0;">
        <label>Tìm kiếm:</label>
        <input type="text" v-model="searchKeyword" placeholder="Nhập tên, số điện thoại hoặc CCCD" />
        <button @click="fetchBookings">Tìm</button>
      </div>
    </div>

    <div v-if="allBookings.length === 0">
      <p>Không có lịch sử đặt phòng cho ngày này.</p>
    </div>

    <table>
      <thead>
        <tr>
          <th>STT</th>
          <th>Họ tên</th>
          <th>SĐT</th>
          <th>Email</th>
          <th>Địa chỉ</th>
          <th>CCCD</th>
          <th>Phòng</th>
          <th>Loại phòng</th>
          <th>Nhận phòng</th>
          <th>Trả phòng</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(booking, index) in allBookings" :key="booking.status_id">
          <td>{{ totalItems - ((currentPage - 1) * perPage + index) }}</td>
          <td>{{ booking.customer_name }}</td>
          <td>{{ booking.customer_phone }}</td>
          <td>{{ booking.customer_email || '...' }}</td>
          <td>{{ booking.address || '...' }}</td>
          <td>{{ booking.customer_id_number || '...' }}</td>
          <td>{{ booking.room_name }}</td>
          <td>{{ booking.type_name }}</td>
          <td>{{ formatDate(booking.check_in) }}</td>
          <td>{{ formatDate(booking.check_out) }}</td>
        </tr>
      </tbody>
    </table>
  </div>
  <div v-if="totalPages > 1" style="margin-top: 16px;">
  <button
    :disabled="currentPage === 1"
    @click="() => { currentPage--; fetchBookings(); }"
  >
    Trước
  </button>

  <span style="margin: 0 10px;">Trang {{ currentPage }} / {{ totalPages }}</span>

  <button
    :disabled="currentPage === totalPages"
    @click="() => { currentPage++; fetchBookings(); }"
  >
    Sau
  </button>
</div>

</template>

<script setup>
import { ref, onMounted, inject } from 'vue';
import axios from 'axios';

const apiUrl = inject('apiUrl');
const selectedDate = ref(new Date().toISOString().substr(0, 10));
const allBookings = ref([]);
const currentPage = ref(1);
const totalPages = ref(1);
const perPage = 10;
const totalItems = ref(0);


const formatDate = (date) => {
  if (!date) return '...';
  return new Date(date).toLocaleString('vi-VN', {
    year: 'numeric', month: '2-digit', day: '2-digit',
    hour: '2-digit', minute: '2-digit'
  });
};

const clearFilters = () => {
  selectedDate.value = '';       // 👈 Gán chuỗi rỗng, không phải hôm nay
  searchKeyword.value = '';      // Gán chuỗi rỗng luôn
  fetchBookings();               // Gọi lại không có filter
  currentPage.value = 1;
fetchBookings();

};

const searchKeyword = ref('');
const fetchBookings = async () => {
  try {
    const params = {};
    if (selectedDate.value) params.date = selectedDate.value;
    if (searchKeyword.value.trim()) params.search = searchKeyword.value.trim();
    params.page = currentPage.value;
    params.per_page = perPage;

    const res = await axios.get(`${apiUrl}/api/booking-histories`, { params });
    totalItems.value = res.data.total;

    allBookings.value = res.data.data.map(b => ({
      status_id: b.status_id,
      booking_id: b.booking_id,
      customer_name: b.customer?.customer_name || 'Chưa có',
      customer_phone: b.customer?.customer_phone || '',
      customer_email: b.customer?.customer_email || '',
      address: b.customer?.address || '',
      customer_id_number: b.customer?.customer_id_number || '',
      room_name: b.room?.room_name || '',
      type_name: b.room?.type_name || '',
      check_in: b.check_in,
      check_out: b.check_out
    }));

    totalPages.value = res.data.last_page;
    currentPage.value = res.data.current_page;
  } catch (err) {
    console.error(err);
    alert("Không thể tải lịch sử đặt phòng.");
  }
};


onMounted(fetchBookings);
</script>

<style scoped>
table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 16px;
  font-family: Arial, sans-serif;
  font-size: 14px;
}

thead {
  background-color: #f0f0f0;
}

input[type="text"] {
  width: 280px;
  /* bạn có thể điều chỉnh lên 320px hoặc bao nhiêu tùy ý */
  padding: 6px 10px;
  font-size: 14px;
  border: 1px solid #ccc;
  border-radius: 4px;
}

th,
td {
  padding: 10px;
  border: 1px solid #ccc;
  text-align: left;
}

tbody tr:nth-child(even) {
  background-color: #fafafa;
}

tbody tr:hover {
  background-color: #f1f9ff;
}

button {
  padding: 6px 12px;
  background-color: #4f46e5;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 13px;
  transition: background-color 0.2s;
  margin-left: 10px;
}

button:hover {
  background-color: #3730a3;
}

input[type="date"] {
  padding: 6px 10px;
  font-size: 14px;
  border: 1px solid #ccc;
  border-radius: 4px;
}</style>
