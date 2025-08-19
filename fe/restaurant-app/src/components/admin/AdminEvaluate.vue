<template>
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
            <th class="px-4 py-3">Khách hàng</th>
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

          <tr
            v-for="(review, index) in reviews"
            :key="review.id"
            class="border-t hover:bg-gray-50 transition-colors"
            :class="index % 2 === 0 ? 'bg-gray-50/40' : 'bg-white'"
          >
            <td class="px-4 py-3 font-medium text-gray-600">
              {{ (pagination.current_page - 1) * 10 + index + 1 }}
            </td>
            <td class="px-4 py-3">{{ review.booking_id }}</td>

            <!-- Customer -->
            <td class="px-4 py-3">
              <div class="flex flex-col">
               
                <span class="text-xs text-gray-500">
                  {{ review.customer?.customer_name || 'N/A' }} - {{ review.customer?.customer_phone || '-' }}
                </span>
                
              </div>
            </td>

            <!-- Room & Service rating -->
            <td class="px-4 py-3">{{ review.room_rating }}/10</td>
            <td class="px-4 py-3">{{ review.service_rating }}/10</td>
            <td class="px-4 py-3">{{ review.staff_rating }}/10</td>

            <!-- Star rating -->
            <td class="px-4 py-3">
              <div class="flex items-center gap-1">
                <span
                  v-for="n in 5"
                  :key="n"
                  class="text-lg"
                  :class="n <= review.star_rating ? 'text-yellow-400' : 'text-gray-300'"
                >
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
      <div
        v-if="pagination.last_page > 1"
        class="flex justify-center items-center gap-3 py-4 border-t bg-gray-50"
      >
        <button
          class="px-3 py-1 rounded-lg bg-gray-200 hover:bg-gray-300 disabled:opacity-50"
          :disabled="pagination.current_page === 1"
          @click="changePage(pagination.current_page - 1)"
        >
          ⬅ Trước
        </button>
        <span class="text-sm font-medium text-gray-600">
          Trang {{ pagination.current_page }} / {{ pagination.last_page }}
        </span>
        <button
          class="px-3 py-1 rounded-lg bg-gray-200 hover:bg-gray-300 disabled:opacity-50"
          :disabled="pagination.current_page === pagination.last_page"
          @click="changePage(pagination.current_page + 1)"
        >
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

const changePage = (page) => {
  fetchReviews(page);
};

onMounted(() => {
  fetchReviews();
});
</script>
