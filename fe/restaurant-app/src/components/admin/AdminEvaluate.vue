<template>
  <!-- Modal / Popup -->
  <!-- Modal -->
  <div v-if="showPopup" class="modal fade show d-block custom-backdrop" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered ">
      <div class="modal-content">

        <!-- Header -->
        <div class="modal-header">
          <h5 class="modal-title">Chi tiết Booking</h5>
          <button type="button" class="btn-close" @click="showPopup = false"></button>
        </div>

        <!-- Body -->
        <div class="modal-body">
          <p><strong>Mã Booking:</strong> {{ bookingDetail?.booking_id }}</p>
          <div class="border rounded p-3 bg-light mb-3">
            <h6 class="fw-bold mb-2">Thông tin khách hàng</h6>
            <p><strong>Họ tên:</strong> {{ bookingDetail?.customer?.customer_name }}</p>
            <p><strong>Email:</strong> {{ bookingDetail?.customer?.customer_email }}</p>
            <p><strong>Số điện thoại:</strong> {{ bookingDetail?.customer?.customer_phone }}</p>
            <p><strong>Địa chỉ:</strong> {{ bookingDetail?.customer?.address }}</p>
          </div>
          <p><strong>Ngày đặt:</strong> {{ new Date(bookingDetail?.created_at).toLocaleString('vi-VN') }}</p>

          <div class="row">
            <div class="col-6"><strong>Check-in:</strong> {{ bookingDetail?.check_in_date }} {{
              bookingDetail?.check_in_time }}</div>
            <div class="col-6"><strong>Check-out:</strong> {{ bookingDetail?.check_out_date }} {{
              bookingDetail?.check_out_time }}</div>
            <div class="col-6"><strong>Người lớn:</strong> {{ bookingDetail?.adult }}</div>
            <div class="col-6"><strong>Trẻ em:</strong> {{ bookingDetail?.child }}</div>
          </div>

          <p><strong>Số phòng:</strong> {{ bookingDetail?.total_rooms }}</p>
          <p><strong>Tổng tiền:</strong>
            <span class="text-danger fw-bold">
              {{ Number(bookingDetail?.total_price).toLocaleString('vi-VN') }} VND
            </span>
          </p>

          <p>
            <strong>Trạng thái:</strong>
            <span class="badge" :class="bookingDetail?.status === 'completed' ? 'bg-success' : 'bg-warning'">
              {{ bookingDetail?.status }}
            </span>
          </p>

          <p><strong>Ghi chú:</strong> {{ bookingDetail?.note || 'Không có' }}</p>
        </div>

        <!-- Footer -->
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="showPopup = false">Đóng</button>
          <!-- <button type="button" class="btn btn-primary">Xuất hoá đơn</button> -->
        </div>

      </div>
    </div>
  </div>

  <!-- backdrop mờ -->
  <!-- Backdrop -->
  <div v-if="showPopup" class="modal-backdrop fade show custom-backdrop"></div>


  <div class="p-6">
    <h2 class="text-2xl font-bold mb-6 text-gray-800 flex items-center gap-2">
      Quản lý đánh giá khách hàng
    </h2>

    <!-- Loading -->
    <div v-if="isLoading" class="flex justify-center py-6">
      <span class="animate-spin border-4 border-gray-300 border-t-blue-500 rounded-full w-10 h-10 inline-block"></span>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="text-center text-red-500 py-4 font-medium">
      {{ error }}
    </div>

    <!-- Table -->
    <div v-else class="bg-white rounded-2xl shadow overflow-hidden">
      <table class="min-w-full text-sm text-gray-700 border-collapse">
        <thead class="bg-gradient-to-r from-gray-100 to-gray-200 text-gray-700 text-left">
          <tr>
            <th class="px-4 py-3">#</th>
            <th class="px-4 py-3">Booking ID</th>
            <th class="px-4 py-3">Phòng</th>
            <th class="px-4 py-3">Dịch vụ</th>
            <th class="px-4 py-3">Nhân viên</th>
            <th class="px-4 py-3">Đánh giá</th>
            <th class="px-4 py-3">Nhận xét</th>
            <th class="px-4 py-3">Ngày tạo</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="!reviews || reviews.length === 0">
            <td colspan="8" class="text-center py-6 text-gray-400 italic">
              Chưa có đánh giá nào
            </td>
          </tr>

          <tr v-for="(review, index) in reviews" :key="review.id" class="border-t hover:bg-gray-50 transition-colors"
            :class="index % 2 === 0 ? 'bg-gray-50/40' : 'bg-white'">
            <td class="px-4 py-3 font-medium text-gray-600">
              {{ (pagination.current_page - 1) * 10 + index + 1 }}
            </td>
            <td @click="showBookingDetail(review.booking_id)" class="px-4 py-3 font-medium text-primary cursor-pointer">
              {{ review.booking_id }}
            </td>

            <!-- Customer -->
            <!-- <td class="px-4 py-3">
              <div class="flex flex-col">
               
                <span class="text-xs text-gray-500">
                  {{ review.customer?.customer_name || 'N/A' }} - {{ review.customer?.customer_phone || '-' }}
                </span>
                
              </div>
            </td> -->

            <!-- Room & Service rating -->
            <td class="px-4 py-3">{{ review.room_rating }}/10</td>
            <td class="px-4 py-3">{{ review.service_rating }}/10</td>
            <td class="px-4 py-3">{{ review.staff_rating }}/10</td>

            <!-- Star rating -->
            <td class="px-4 py-3">
              <div class="flex items-center gap-1">
                <span v-for="n in 5" :key="n" class="text-lg"
                  :class="n <= review.star_rating ? 'text-yellow-400' : 'text-gray-300'">
                  ★
                </span>
              </div>
            </td>

            <!-- Comment -->
            <td class="px-4 py-3 italic text-gray-600">
              {{ review.comment ?? 'Trống' }}
            </td>

            <!-- Created at -->
            <td class="px-4 py-3 text-gray-500">
              {{ new Date(review.created_at).toLocaleDateString("vi-VN") }}
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div v-if="pagination.last_page > 1" class="flex justify-center items-center gap-3 py-4 border-t bg-gray-50">
        <button class="px-3 py-1 rounded-lg bg-gray-200 hover:bg-gray-300 disabled:opacity-50"
          :disabled="pagination.current_page === 1" @click="changePage(pagination.current_page - 1)">
          ⬅ Trước
        </button>
        <span class="text-sm font-medium text-gray-600">
          Trang {{ pagination.current_page }} / {{ pagination.last_page }}
        </span>
        <button class="px-3 py-1 rounded-lg bg-gray-200 hover:bg-gray-300 disabled:opacity-50"
          :disabled="pagination.current_page === pagination.last_page" @click="changePage(pagination.current_page + 1)">
          Tiếp ➡
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, inject } from "vue";
import axios from "axios";

const apiUrl = inject("apiUrl");

const reviews = ref([]);
const pagination = ref({});
const isLoading = ref(false);
const error = ref("");

const fetchReviews = async (page = 1) => {
  try {
    isLoading.value = true;
    error.value = "";
    const res = await axios.get(`${apiUrl}/api/customer-reviewsget?page=${page}`);
    reviews.value = res.data.data; // reviews với customer
    pagination.value = res.data;
  } catch (err) {
    console.error("Fetch reviews error:", err);
    error.value = "Không thể tải dữ liệu!";
  } finally {
    isLoading.value = false;
  }
};
const bookingDetail = ref(null);
const showPopup = ref(false);

const showBookingDetail = async (bookingId) => {
  try {
    const res = await axios.get(`${apiUrl}/api/booking-detailreview/${bookingId}`);
    console.log('Chi tiết booking:', res.data);

    if (res.data?.status === 'success') {
      bookingDetail.value = res.data.data;
      showPopup.value = true;
    } else {
      alert('Không thể tải thông tin chi tiết.');
    }
  } catch (err) {
    console.error('Lỗi khi lấy chi tiết booking:', err);
    alert('Có lỗi xảy ra khi tải chi tiết booking.');
  }
};

const changePage = (page) => {
  fetchReviews(page);
};

onMounted(() => {
  fetchReviews();
});
</script>
<style scoped>
.custom-backdrop {
  background-color: rgba(0, 0, 0, 0.25) !important;
  /* mờ nhẹ hơn */
}
</style>