<template>
  <div class="booking-history-container">
    <h2>Lịch sử Đặt phòng</h2>

    <div v-if="isLoading" class="loading-overlay">
      <Loading />
      <p class="loading-text">Đang tải dữ liệu lịch sử đặt phòng của bạn...</p>
    </div>

    <p v-else-if="error" class="error-message">
      {{ error }}
    </p>

    <div v-else-if="bookings.length === 0" class="no-bookings-message">
      <p>Bạn chưa có lịch sử đặt phòng nào.</p>
      <p class="suggestion-text">Hãy khám phá và đặt phòng ngay hôm nay để có một kỳ nghỉ tuyệt vời!</p>
    </div>

    <div v-else class="booking-list">
      <div v-for="booking in bookings" :key="booking.booking_id" class="booking-item">
        <div class="booking-header">
          <h3>Mã đặt phòng: #{{ booking.booking_id }}</h3>
          <span :class="['status-badge', formatStatusClass(booking.status) ]">
            {{ formatStatus(booking.status) }}
          </span>
        </div>

        <div class="booking-details">
          <p class="detail-group">
            <strong>Ngày nhận phòng:</strong> <span>{{ formatDate(booking.check_in_date) }}</span>
            <span class="detail-separator">|</span>
            <strong>Ngày trả phòng:</strong> <span>{{ formatDate(booking.check_out_date) }}</span>
          </p>

          <template v-if="booking.room_type_info">
            <p class="detail-group">
              <strong>Loại phòng:</strong> <span>{{ booking.room_type_info.type_name }}</span>
              <span class="detail-separator">|</span>
              <strong>Sức chứa:</strong> <span>{{ booking.room_type_info.max_occupancy }} người</span>
            </p>
            <p class="detail-group">
              <strong>Số lượng phòng:</strong> <span>{{ booking.total_rooms }}</span>
              <span class="detail-separator">|</span>
              <strong>Diện tích:</strong> <span>{{ booking.room_type_info.m2 }} m²</span>
            </p>
            <p v-if="booking.room_type_info.description">
              <strong>Mô tả:</strong> <span>{{ booking.room_type_info.description }}</span>
            </p>
          </template>
          <p v-else class="detail-group"><strong>Loại phòng:</strong> <span>Đang cập nhật...</span></p>

          <p class="detail-group total-price">
            <strong>Tổng tiền:</strong> <span>{{ formatCurrency(booking.total_price) }}</span>
            <span class="detail-separator">|</span>
            <strong>Phương thức:</strong> <span>{{ formatPaymentMethod(booking.payment_method) }}</span>
          </p>

          <p v-if="booking.note && booking.note !== 'Không có ghi chú'" class="detail-group">
            <strong>Ghi chú:</strong> <span>{{ booking.note }}</span>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { inject, onMounted, ref } from 'vue';
import axios from 'axios';
import Loading from '../loading.vue'; // Đảm bảo đường dẫn đúng tới component Loading của bạn

const apiUrl = inject('apiUrl');

const isLoading = ref(false);
const bookings = ref([]);
const error = ref(null);

const paymentMethodsMap = {
  'pay_qr': 'Thanh toán QR',
  'cash': 'Tiền mặt',
  'credit_card': 'Thẻ tín dụng',
  'bank_transfer': 'Chuyển khoản ngân hàng',
};

const statusMap = {
  'pending_confirmation': 'Đang chờ xác nhận',
  'pending': 'Đang chờ',
  'confirmed': 'Đã xác nhận',
  'cancelled': 'Đã hủy',
  'completed': 'Hoàn thành',
};

const getHistoryBooking = async () => {
  let token = localStorage.getItem('BookingAuth') || '';
  const axiosInstance = axios.create({
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`,
    }
  });

  try {
    isLoading.value = true;
    error.value = null;

    const response = await axiosInstance.get(`${apiUrl}/api/booking-history`);

    if (response.data && response.data.status === 'success') {
      bookings.value = response.data.data;
      console.log('Fetched booking history:', bookings.value);
    } else {
      error.value = response.data.message || 'Không thể tải dữ liệu lịch sử đặt phòng.';
      bookings.value = [];
    }
  } catch (err) {
    console.error('Error fetching booking history:', err);
    if (err.response) {
      if (err.response.status === 401) {
        error.value = 'Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.';
      } else if (err.response.data && err.response.data.message) {
        error.value = `Lỗi từ máy chủ: ${err.response.data.message}`;
      } else {
        error.value = `Lỗi kết nối máy chủ (${err.response.status}). Vui lòng thử lại.`;
      }
    } else if (err.request) {
      error.value = 'Không thể kết nối đến máy chủ. Vui lòng kiểm tra kết nối mạng của bạn.';
    } else {
      error.value = 'Đã xảy ra lỗi không xác định. Vui lòng thử lại.';
    }
    bookings.value = [];
  } finally {
    isLoading.value = false;
  }
};

const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  try {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('vi-VN', options);
  } catch (e) {
    console.error('Error formatting date:', e);
    return dateString;
  }
};

const formatCurrency = (amount) => {
  if (amount === null || amount === undefined) return '0 VNĐ';
  try {
    return parseFloat(amount).toLocaleString('vi-VN', { style: 'currency', currency: 'VND' });
  } catch (e) {
    console.error('Error formatting currency:', e);
    return `${amount} VNĐ`;
  }
};

const formatStatus = (status) => {
  return statusMap[status] || status.replace(/_/g, ' ');
};

const formatStatusClass = (status) => {
  return status.replace(/_/g, '-');
};

const formatPaymentMethod = (method) => {
  return paymentMethodsMap[method] || method.replace(/_/g, ' ');
};

onMounted(() => {
  getHistoryBooking();
});
</script>

<style lang="scss" scoped>
// Import các partials SCSS
@import '../../assets/scss/variables'; // Cập nhật đường dẫn cho phù hợp với cấu trúc dự án của bạn
@import '../../assets/scss/mixins';   // Cập nhật đường dẫn cho phù hợp
// @import '../../assets/scss/functions'; // Nếu bạn sử dụng functions

/*
|--------------------------------------------------------------------------
| Base Container Styling
|--------------------------------------------------------------------------
|
| Style cho phần bao bọc chính của component lịch sử đặt phòng.
|
*/
.booking-history-container {
  font-family: $font-family-base;
  max-width: 960px;
  margin: $spacing-xxl auto;
  padding: $spacing-xl;
 // background-color: $bg-body;
  border-radius: $radius-xl;
  box-shadow: 0 $spacing-xs $spacing-lg rgba(0, 0, 0, 0.1);
  color: $text-dark;
  overflow: hidden;

  h2 {
    text-align: center;
    color: $color-primary;
    margin-bottom: $spacing-xxl;
    font-size: px-to-rem(48px); // Ví dụ sử dụng function
    font-weight: $font-weight-extra-bold;
    letter-spacing: -1px;
    border-bottom: 3px solid darken($border-light, 5%);
    padding-bottom: $spacing-lg;
    text-transform: uppercase;
    position: relative;
    &::after {
      content: '';
      position: absolute;
      bottom: -3px;
      left: 50%;
      transform: translateX(-50%);
      width: 80px;
      height: 3px;
      background-color: $color-accent;
      border-radius: $radius-sm;
    }
  }
}

/*
|--------------------------------------------------------------------------
| Loading, Error, No Data Messages
|--------------------------------------------------------------------------
|
| Style cho các thông báo khi đang tải, có lỗi, hoặc không có dữ liệu.
|
*/
.loading-overlay,
.error-message,
.no-bookings-message {
  @include card-style($padding: $spacing-lg, $bg: $bg-light, $radius: $radius-md); // Sử dụng mixin card-style
  font-size: px-to-rem(18px);
  text-align: center;
  margin-top: $spacing-lg;
  color: $text-light; // Mặc định màu chữ
}

.loading-overlay {
  @include flex-col-center; // Sử dụng mixin mới
  min-height: 280px;

  .loading-text {
    margin-top: $spacing-md;
    font-weight: $font-weight-medium;
    color: $color-secondary;
  }
}

.error-message {
  color: $color-danger;
  background-color: lighten($color-danger, 40%);
  border: 1px solid lighten($color-danger, 30%);
}

.no-bookings-message {
  background-color: lighten($color-primary, 40%);
  border: 1px solid lighten($color-primary, 30%);

  .suggestion-text {
    margin-top: $spacing-md;
    font-size: px-to-rem(15px);
    color: darken($text-light, 10%);
    font-style: italic;
    line-height: 1.6;
  }
}

/*
|--------------------------------------------------------------------------
| Booking List & Item Styling
|--------------------------------------------------------------------------
|
| Style cho danh sách các mục đặt phòng và từng mục riêng lẻ.
|
*/
.booking-list {
  display: grid;
  gap: $spacing-xl; // Tăng khoảng cách giữa các booking item
}

.booking-item {
  @include card-style($padding: $spacing-xl, $bg: $bg-light, $radius: $radius-xl); // Tùy chỉnh padding và radius
  border: 1px solid $border-light; // Viền rõ ràng hơn

  .booking-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: $spacing-lg;
    border-bottom: 1px solid darken($border-light, 5%);
    padding-bottom: $spacing-md;

    h3 {
      margin: 0;
      color: $text-dark;
      font-size: px-to-rem(28px);
      font-weight: $font-weight-bold;
      letter-spacing: -0.5px;
    }
  }

  .status-badge {
    &.pending-confirmation, &.pending {
      @include status-badge($color-accent, $text-dark);
    }
    &.confirmed {
      @include status-badge($color-success);
    }
    &.cancelled {
      @include status-badge($color-danger);
    }
    &.completed {
      @include status-badge($color-primary);
    }
  }

  .booking-details {
    .detail-group {
      margin: $spacing-sm 0; // Khoảng cách giữa các dòng chi tiết
      font-size: px-to-rem(18px);
      line-height: 1.6;
      display: flex;
      flex-wrap: wrap;
      align-items: center;

      strong {
        @include text-emphasis($color-secondary, $font-weight-medium); // Sử dụng mixin
        min-width: 170px; // Tăng min-width cho label
        display: inline-block;
        margin-right: $spacing-sm;
      }
      span {
        color: $text-dark;
        flex-grow: 1;
      }
    }
    .detail-separator {
      margin: 0 $spacing-md;
      color: lighten($border-light, 5%);
      font-size: 0.9em;
    }

    .total-price {
      font-size: px-to-rem(20px); // Nhấn mạnh tổng tiền
      strong, span {
        color: $color-primary;
        font-weight: $font-weight-extra-bold;
      }
    }
  }
}

/*
|--------------------------------------------------------------------------
| Responsive Adjustments
|--------------------------------------------------------------------------
|
| Điều chỉnh style để hiển thị tốt trên các kích thước màn hình khác nhau.
|
*/
@include media-breakpoint-down($breakpoint-md) { // Sử dụng mixin cho responsive
  .booking-history-container {
    padding: $spacing-md;
    margin: $spacing-md auto;
  }
  h2 {
    font-size: px-to-rem(36px);
    margin-bottom: $spacing-xl;
    padding-bottom: $spacing-md;
  }
  .booking-item {
    padding: $spacing-md $spacing-lg;
  }
  .booking-header {
    flex-direction: column;
    align-items: flex-start;
    h3 {
      margin-bottom: $spacing-sm;
      font-size: px-to-rem(24px);
    }
    .status-badge {
      align-self: flex-end;
      margin-top: $spacing-xs;
    }
  }
  .booking-details {
    .detail-group {
      flex-direction: column;
      align-items: flex-start;
      margin: $spacing-xs 0;
      font-size: px-to-rem(16px);

      strong {
        min-width: unset;
        margin-right: 0;
        margin-bottom: $spacing-xxs;
      }
      span {
        padding-left: 0;
      }
    }
    .detail-separator {
      display: none;
    }
  }
}

@include media-breakpoint-down($breakpoint-sm) {
  .booking-history-container {
    padding: $spacing-sm;
  }
  h2 {
    font-size: px-to-rem(30px);
    padding-bottom: $spacing-sm;
    margin-bottom: $spacing-lg;
  }
  .booking-item {
    padding: $spacing-md;
  }
  .booking-header h3 {
    font-size: px-to-rem(20px);
  }
  .status-badge {
    padding: $spacing-xs $spacing-sm;
    font-size: 0.8em;
  }
  .booking-details .detail-group {
    font-size: px-to-rem(15px);
  }
}
</style>