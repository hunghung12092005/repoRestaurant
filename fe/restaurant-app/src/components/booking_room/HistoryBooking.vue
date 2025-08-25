<template>

  <!-- <div v-if="showPopUpSMS" class="popup-overlay">
    <div class="form-container">
      <div class="logo-container">
        Xác thực Số Điện Thoại
        <button type="button" class="btn-close m-4" @click="closeModalOtp"></button>
      </div>
      <div class="form-group">
        <label for="email">Nhập SĐT</label>
        <input v-model="phoneNumber" type="number" placeholder="Nhập OTP" required="">
      </div>

      <button class="form-submit-btn" type="submit" @click="checkAndSendOtp">Xác Nhận</button>

      <p v-if="message">
      <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      </p>
    </div>
  </div>
  <div v-if="isOtp" class="popup-overlay">
    <div class="form-container">
      <div class="logo-container">
        Xác thực SMS
        <button type="button" class="btn-close m-4" @click="closeModalOtp"></button>
      </div>
      <div class="form-group">
        <label for="email">Mã OTP</label>
        <input v-model="otpInputs" type="text" placeholder="Nhập OTP" required="">
      </div>

      <button class="form-submit-btn" type="submit" @click="verifyCode">Xác Nhận</button>

      <p v-if="message">
      <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      </p>
    </div>
  </div> -->
  <div id="recaptcha-container"></div>
  <!-- popup Evaluate -->
  <div v-if="popupEvaluate" class="modal fade show d-block" tabindex="-1" role="dialog"
    style="z-index: 9999; background: rgba(0,0,0,0.5);">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 rounded-3 shadow-lg">
        <!-- Modal Header -->
        <div class="modal-header bg-primary text-white border-0 rounded-top-3">
          <h5 class="modal-title fw-bold">Đánh giá trải nghiệm của bạn tại HXH</h5>
          <button type="button" class="btn-close btn-close-white" @click="popupEvaluate = false"></button>
        </div>

        <!-- Modal Body - Nội dung chính của form -->
        <div class="modal-body p-4 p-sm-5">
          <!-- Đánh giá Dịch vụ khách sạn -->
          <div class="mb-4">
            <label for="hotel-service-range" class="form-label fw-bold text-dark">Dịch vụ khách sạn</label>
            <input type="range" min="1" max="10" v-model="form.hotel_service" id="hotel-service-range"
              class="form-range custom-range">
            <div class="d-flex justify-content-between text-muted">
              <span>Rất tệ</span>
              <span><strong>{{ form.hotel_service }}/10</strong></span>
              <span>Rất tốt</span>
            </div>
          </div>

          <!-- Đánh giá Nhân viên -->
          <div class="mb-4">
            <label for="staff-range" class="form-label fw-bold text-dark">Nhân viên</label>
            <input type="range" min="1" max="10" v-model="form.staff" id="staff-range" class="form-range custom-range">
            <div class="d-flex justify-content-between text-muted">
              <span>Rất tệ</span>
              <span><strong>{{ form.staff }}/10</strong></span>
              <span>Rất tốt</span>
            </div>
          </div>

          <!-- Đánh giá Phòng -->
          <div class="mb-4">
            <label for="room-range" class="form-label fw-bold text-dark">Phòng</label>
            <input type="range" min="1" max="10" v-model="form.room" id="room-range" class="form-range custom-range">
            <div class="d-flex justify-content-between text-muted">
              <span>Rất tệ</span>
              <span><strong>{{ form.room }}/10</strong></span>
              <span>Rất tốt</span>
            </div>
          </div>

          <!-- Đánh giá sao tổng thể -->
          <div class="mb-4 text-center">
            <label class="form-label fw-bold text-dark">Đánh giá tổng thể</label>
            <div class="rating-stars" @mouseleave="hover = 0">
              <!-- Vòng lặp để tạo 5 nút sao -->
              <button v-for="n in 5" :key="n" type="button" class="star-btn" @mouseover="hover = n"
                @click="form.stars = n">
                <span :class="(hover || form.stars) >= n ? 'star filled' : 'star'">★</span>
              </button>
            </div>
            <small v-if="form.stars" class="text-muted mt-2 d-block">Bạn chọn {{ form.stars }} sao</small>
          </div>

          <!-- Nhận xét -->
          <div class="mb-4">
            <label for="comment-textarea" class="form-label fw-bold text-dark">Nhận xét</label>
            <textarea v-model.trim="form.comment" id="comment-textarea" rows="3" class="form-control"
              placeholder="Chia sẻ trải nghiệm của bạn..."></textarea>
          </div>
        </div>

        <!-- Modal Footer -->
        <div class="modal-footer border-0 p-4">
          <button class="btn btn-outline-secondary" @click="popupEvaluate = false">Đóng</button>
          <button class="btn btn-solid-custom" @click="sendEvaluate()">Gửi đánh giá</button>
        </div>
      </div>
    </div>
  </div>
  <!-- Bắt đầu của component chi tiết-->
  <div class="modal fade" :class="{ show: popupDetail }" style="display: block; background-color: rgba(0,0,0,0.5);"
    v-if="popupDetail" tabindex="-1" role="dialog" aria-labelledby="orderDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content rounded-4 shadow">
        <div class="modal-header border-bottom-0">
          <h5 class="modal-title fw-bold" id="orderDetailModalLabel">Chi tiết đơn hàng</h5>
          <button type="button" class="btn-close" aria-label="Close" @click="popupDetail = false"></button>
        </div>
        <div class="modal-body py-0">
          <!-- Trạng thái tải dữ liệu -->
          <div v-if="isLoading" class="d-flex flex-column align-items-center justify-content-center">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Đang tải dữ liệu...</span>
            </div>
            <p class="mt-3 text-muted">Đang tải dữ liệu...</p>
          </div>
          <!-- Trạng thái lỗi -->
          <div v-else-if="error" class="alert alert-danger text-center rounded-3 my-4" role="alert">
            {{ error }}
          </div>
          <!-- Nội dung chi tiết đơn hàng -->
          <div v-else>
            <div v-for="room in bookingDetails" :key="room.booking_detail_id"
              class="card mb-4 rounded-3 shadow-sm border-0">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <h6 class="card-title mb-0 fs-5">
                    Phòng: <strong class="text-primary">{{ room.room_id ?? 'Chưa có phòng' }}</strong>
                  </h6>
                  <!-- <span class="badge" :class="{'bg-success': room.trang_thai === 'Hoàn thành', 'bg-warning text-dark': room.trang_thai !== 'Hoàn thành'}">
                  {{ room.trang_thai ?? 'Chưa rõ' }}
                </span> -->
                </div>
                <p class="card-subtitle text-muted mb-3">
                  Tổng tiền phòng: <span class="fw-bold text-success">{{ formatCurrency(room.total_price) }}</span>
                </p>
                <hr class="my-3">
                <div v-if="room.services && room.services.length">
                  <p class="mb-2 fw-bold text-dark">Dịch vụ đi kèm:</p>
                  <ul class="list-group list-group-flush border-top border-bottom">
                    <li v-for="service in room.services" :key="service.booking_service_id"
                      class="list-group-item d-flex justify-content-between align-items-center px-0">
                      <span>{{ service.service_info.service_name }}</span>
                      <span class="text-dark">x{{ service.quantity }} - <span class="fw-bold">{{
                        formatCurrency(service.total) }}</span></span>
                    </li>
                  </ul>
                </div>
                <div v-else class="text-muted fst-italic text-center py-2">Không có dịch vụ đi kèm.</div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer border-top-0">
          <button type="button" class="btn btn-secondary rounded-pill px-4" @click="popupDetail = false">Đóng</button>
        </div>

      </div>
    </div>
  </div>

  <div class="history-wrapper">
    <div class="history-container">
      <header class="page-header animate__animated animate__fadeInDown">
        <h2>LỊCH SỬ ĐẶT PHÒNG</h2>
        <p>Tra cứu và quản lý tất cả các đặt phòng đã thực hiện.</p>
        <!-- <div class="phone-form">
          <input type="tel" placeholder="Nhập số điện thoại..." class="phone-input" v-model="phoneNumber">
          <button @click="checkAndSendOtp" class="btn btn-solid-custom">Gửi</button>
        </div> -->

        <!-- <div class="btn btn-solid-custom" @click="showPopUpSMS = true">Nhập Số Điện Thoại</div> -->
      </header>

      <!-- MODAL HỦY PHÒNG -->
      <div v-if="showCancelPopup" class="modal-backdrop animate__animated animate__fadeIn">
        <div class="modal-content animate__animated animate__zoomIn" style="animation-duration: 0.4s;">
          <div class="modal-header">
            <h3><i class="bi bi-shield-fill-exclamation"></i> Xác Nhận Hủy</h3>
            <button @click="showCancelPopup = false" class="close-button" aria-label="Đóng">&times;</button>
          </div>
          <div class="modal-body">
            <p>Bạn có chắc chắn muốn gửi yêu cầu hủy cho đơn đặt phòng này không?</p>
            <div class="form-group">
              <label for="reason">Lý do hủy (bắt buộc):</label>
              <select v-model="selectedReason" id="reason" class="form-control">
                <option value="" disabled>-- Vui lòng chọn lý do --</option>
                <option value="Không còn nhu cầu">Không còn nhu cầu</option>
                <option value="Lịch trình thay đổi">Lịch trình thay đổi</option>
                <option value="Giá không hợp lý">Giá không hợp lý</option>
                <option value="Khác">Khác</option>
              </select>
            </div>
            <div v-if="selectedReason === 'Khác'" class="form-group animate__animated animate__fadeIn">
              <label for="customReason">Vui lòng mô tả chi tiết:</label>
              <textarea v-model="customReason" id="customReason" class="form-control" rows="3"
                placeholder="..."></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" @click="showCancelPopup = false">Quay lại</button>
            <button class="btn btn-danger" @click="confirmCancellation"
              :disabled="!selectedReason || (selectedReason === 'Khác' && !customReason)">
              <i class="bi bi-trash3-fill me-2"></i> Gửi Yêu Cầu Hủy
            </button>
          </div>
        </div>
      </div>

      <!-- CÁC TRẠNG THÁI KHÁC -->
      <main>
        <Loading v-if="isLoading" />

        <div v-else-if="error" class="info-state animate__animated animate__fadeIn">
          <div class="icon-wrapper error"><i class="bi bi-cloud-slash-fill"></i></div>
          <h2>Không thể tải dữ liệu</h2>
          <p>{{ error }}</p>
          <button class="btn btn-primary" @click="getHistoryBooking">Thử Lại</button>
        </div>

        <div v-else-if="bookings.length === 0" class="info-state animate__animated animate__fadeIn">
          <div class="icon-wrapper empty"><i class="bi bi-briefcase-fill"></i></div>
          <h2>Trống ! </h2>
          <p>Hãy bắt đầu khám phá và đặt phòng ngay hôm nay!</p>
          <p>Hoặc tìm đơn phòng theo số điện thoại!</p>
          <router-link to="/booking_hotel" class="btn btn-primary btn-lg">
            <i class="bi bi-search me-2"></i> Khám Phá Phòng
          </router-link>
        </div>

        <!-- DANH SÁCH ĐẶT PHÒNG -->
        <div v-else class="booking-list">
          <div v-for="booking in bookings" :key="booking.booking_id"
            class="booking-card animate__animated animate__fadeInUp" :class="'status-border-' + booking.status">

            <!-- CARD ĐANG CHỜ HỦY -->
            <template v-if="booking.status === 'pending_cancel'">
              <div class="card-header">
                <h4 class="pending-cancel-title"><i class="bi bi-arrow-clockwise"></i> Yêu Cầu Hủy HXH {{ booking.booking_id }}</h4>
                <span class="status-badge" :class="'status-' + booking.status">{{ formatStatus(booking.status) }}</span>
              </div>
              <div class="card-body">
                <div v-if="cancelDetails[booking.booking_id]" class="cancellation-details">
                  <div><i class="bi bi-card-text"></i><strong>Lý do:</strong><span>{{
                    cancelDetails[booking.booking_id].reason }}</span></div>
                  <div><i class="bi bi-calendar-week"></i><strong>Ngày yêu cầu:</strong><span>{{
                    formatDate(cancelDetails[booking.booking_id].created_at) }}</span></div>
                  <div class="refund-info"><i class="bi bi-box-arrow-in-left"></i><strong>Tiền hoàn dự
                      kiến:</strong><span class="price">{{ formatPrice(cancelDetails[booking.booking_id].refund_amount)
                      }}</span></div>
                </div>
                <div v-else class="text-center py-4">
                  <div class="spinner-border text-secondary spinner-border-sm" role="status"></div>
                  <p class="mt-2 text-muted small">Đang tải chi tiết...</p>
                </div>
                <div class="refund-form-container" v-if="cancelDetails[booking.booking_id]?.refund_amount > 0">
                  <h5 class="refund-form-title"><i class="bi bi-bank"></i> Thông Tin Nhận Tiền</h5>
                  <template v-if="cancelDetails[booking.booking_id]?.refund_bank">
                    <div class="refund-display">
                      <p><strong>Ngân hàng:</strong> {{ cancelDetails[booking.booking_id].refund_bank }}</p>
                      <p><strong>Số tài khoản:</strong> {{ cancelDetails[booking.booking_id].refund_account_number }}
                      </p>
                      <p><strong>Chủ tài khoản:</strong> {{ cancelDetails[booking.booking_id].refund_account_name }}</p>
                      <div class="alert-success"><i class="bi bi-check-circle-fill me-2"></i>Đã nhận thông tin. Chúng
                        tôi sẽ xử lý sớm.</div>
                    </div>
                  </template>
                  <template v-else>
                    <div class="form-group">
                      <label>Ngân hàng</label>
                      <select class="form-control" v-model="refundBank[booking.booking_id].bank">
                        <option value="" disabled>-- Chọn ngân hàng --</option>
                        <option v-for="bank in banks" :key="bank.code" :value="bank.name">{{ bank.short_name }} - {{
                          bank.name }}</option>
                      </select>
                    </div>
                    <div class="form-group">
                      <label>Số tài khoản</label>
                      <input type="text" class="form-control" v-model="refundBank[booking.booking_id].accountNumber"
                        placeholder="Nhập số tài khoản">
                    </div>
                    <div class="form-group">
                      <label>Tên chủ tài khoản (Viết hoa không dấu)</label>
                      <input type="text" class="form-control" v-model="refundBank[booking.booking_id].accountName"
                        placeholder="NGUYEN VAN A">
                    </div>
                    <button class="btn btn-primary w-100 mt-2" @click="submitRefundInfo(booking.booking_id)">
                      <i class="bi bi-send-fill me-2"></i> Gửi
                    </button>
                  </template>
                </div>
              </div>
            </template>

            <!-- CARD THÔNG THƯỜNG -->
            <template v-else>
              <div class="card-main-content">
                <div class="card-header">
                  <h3>{{ booking.room_type_info ? booking.room_type_info.type_name : 'Thông tin phòng' }}</h3>
                  <span class="status-badge" :class="'status-' + booking.status">{{ formatStatus(booking.status)
                    }}</span>
                </div>
                <div class="card-body">
                  <div class="info-grid">
                    <div class="info-item">
                      <label>Mã Đặt Phòng</label>
                      <span>HXH{{ booking.booking_id }}</span>
                    </div>
                    <div class="info-item">
                      <label>Số Lượng</label>
                      <span>{{ booking.total_rooms }} phòng</span>
                    </div>
                    <div class="info-item">
                      <label>Check-in</label>
                      <span>{{ formatDate(booking.check_in_date) }}</span>
                    </div>
                    <div class="info-item">
                      <label>Check-out</label>
                      <span>{{ formatDate(booking.check_out_date) }}</span>
                    </div>
                    <div class="info-item">
                      <span><button class="btn btn-outline-dark" @click="viewDetailOrder(booking.booking_id)">Xem chi
                          tiết</button></span>
                    </div>
                    <!-- <div class="info-item">
                      <span><button class="btn btn-outline-dark" @click="popUpEvaluate(booking.booking_id)">Đánh giá
                        </button></span>
                    </div> -->
                    <div class="info-item" v-if="booking.status === 'completed'">
                      <span><button class="btn btn-outline-dark" @click="popUpEvaluate(booking.booking_id)">Đánh giá
                        </button></span>
                    </div>
                  </div>
                  <div v-if="booking.note" class="note">
                    <strong>Ghi chú:</strong> {{ booking.note }}
                  </div>
                </div>
              </div>
              <div class="card-details-panel">
                <div class="price-section">
                  <label>Tổng chi phí</label>
                  <span class="price">{{ formatCurrency(booking.total_price) }}</span>
                </div>
                <div class="payment-details">
                  <div class="payment-item">
                    <i class="bi bi-credit-card"></i>
                    <span>{{ formatPayment(booking.payment_method) }}</span>
                  </div>
                  <div class="payment-item" v-if="booking.payment_method === 'thanh_toan_qr'">
                    <i
                      :class="booking.payment_status === 'paid' ? 'bi-check-circle-fill text-success' : 'bi-hourglass-split text-warning'"></i>
                    <span>{{ formatPaymentStatus(booking.payment_status) }}</span>
                    <div class="payment-item" v-if="booking.payment_status != 'completed'">
                      <button @click="gotoLinkPayos(booking.orderCode)" class="btn">Tiếp tục thanh toán </button>
                    </div>
                  </div>


                </div>
                <div class="card-actions">
                  <button v-if="canCancelBooking(booking)" class="btn btn-light-danger"
                    @click="showCancelPopup = true; currentBookingId = booking.booking_id">
                    <i class="bi bi-x-lg"></i> Hủy
                  </button>
                  <span class="update-time">Lần cuối cập nhật: {{ formatDate(booking.updated_at) }}</span>
                </div>
              </div>
            </template>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { inject, onMounted, ref, computed } from 'vue';
import axios from 'axios';
import Loading from '../loading.vue';
import { auth, RecaptchaVerifier, PhoneAuthProvider } from '../ShopOnline/firebase';
import { signInWithPhoneNumber, signInWithCredential } from 'firebase/auth';

const apiUrl = inject('apiUrl');
const isLoading = ref(false);
const bookings = ref([]);
const error = ref(null);
const cancelDetails = ref({});
const refundBank = ref({}); // Lưu thông tin hoàn tiền nhập từ form
//format price
const formatPrice = (value) => {
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND',
  }).format(value);
};
const curPhone = ref('');
const showPopUpSMS = ref(false); // Hiển thị popup yêu cầu đăng nhập
const getHistoryBooking = async () => {
  console.log(authSms.value);
  let token = localStorage.getItem('BookingAuth');
  // if (token === null || token === '') {
  //   showPopUpSMS.value = true;
  // }
  const axiosInstance = axios.create({
    headers: { 'Authorization': `Bearer ${token}` }
  });
  try {
    isLoading.value = true;
    // if (authSms.value === true) {
    //   try {
    //     isLoading.value = true;
    //     const rawPhone = localStorage.getItem('BookingAuthPhone');
    //     const phoneWithZero = rawPhone.startsWith('0') ? rawPhone : '0' + rawPhone;
    //     curPhone.value = phoneWithZero;
    //     // console.log('Phones gửi lên:', [rawPhone, phoneWithZero]);

    //     const res = await axios.post(`${apiUrl}/api/booking-historyPhone`, {
    //       phones: [rawPhone, phoneWithZero] // gửi mảng phone
    //     });

    //     //console.log('Lịch sử đặt phòng phone:', res.data);
    //     if (res.data?.status === 'success') {
    //       bookings.value = res.data.data;
    //       // Lấy chi tiết huỷ cho các đơn đang pending_cancel
    //       for (const b of res.data.data) {
    //         if (b.status === 'pending_cancel') {
    //           await getCancelBookingDetail(b.booking_id);
    //           refundBank.value[b.booking_id] = { bank: '', accountNumber: '' };
    //         }
    //       }
    //       console.log('Lịch sử đặt phòng đã tải thành công addjab:', bookings.value);
    //     } else {
    //       error.value = res.data.message || 'Không thể tải dữ liệu.';
    //     }
    //     return;
    //   } catch (err) {
    //     console.error('Lỗi lấy lịch sử đặt phòng:', err);
    //   } finally {
    //     isLoading.value = false;
    //   }
    // }
    const res = await axiosInstance.get(`${apiUrl}/api/booking-history`);
    // console.log('Lịch sử đặt phòng:', res.data);
    if (res.data?.status === 'success') {
      bookings.value = res.data.data;
      // Lấy chi tiết huỷ cho các đơn đang pending_cancel
      for (const b of res.data.data) {
        if (b.status === 'pending_cancel') {
          await getCancelBookingDetail(b.booking_id);
          refundBank.value[b.booking_id] = { bank: '', accountNumber: '' };
        }
      }
      console.log('Lịch sử đặt phòng đã tải thành công 123:', bookings.value);

    } else {
      error.value = res.data.message || 'Không thể tải dữ liệu.';
    }
  } catch {
    error.value = 'Chưa có đơn hàng.';
  } finally {
    isLoading.value = false;
  }
};
const popupDetail = ref(false);
const bookingDetails = ref('');
const viewDetailOrder = async (bookingID) => {
  const bookingIDs = bookingID;
  try {
    isLoading.value = true;
    const res = await axios.get(`${apiUrl}/api/booking-history/${bookingIDs}`);
    bookingDetails.value = res.data.data;
    console.log('Chi tiết đơn hàng:', bookingDetails.value);
    popupDetail.value = true;
  }
  catch {
    error.value = 'Không thể tải dữ liệu.';
  }
  finally {
    isLoading.value = false;
  }
};
const gotoLinkPayos = async (orderCode) => {
  try {
    console.log('Đang lấy thông tin thanh toán cho orderCode:', orderCode);
    const axiosInstancePayos = axios.create({
      baseURL: 'https://api-merchant.payos.vn/v2',
      headers: {
        'x-client-id': '078daf0e-baed-45f9-b2f9-20b79c89668e',
        'x-api-key': '8841f63c-c976-4b89-bf56-b769b035292b'
      }
    });
    // Gọi API PayOS
    const res = await axiosInstancePayos.get(`https://api-merchant.payos.vn/v2/payment-requests/${orderCode}`);

    if (res.data && res.data.code === '00') {
      const id = res.data.data.id; // lấy data.id
      const payLink = `https://pay.payos.vn/web/${id}/`; // tạo link thanh toán
      console.log('Link thanh toán PayOS:', payLink);

      // Chuyển hướng trình duyệt
      window.location.href = payLink;
    } else {
      console.error('Lỗi lấy dữ liệu từ PayOS:', res.data);
    }

  } catch (error) {
    console.error('Lỗi khi gọi API PayOS:', error);
  }
}
const popupEvaluate = ref(false);
const hover = ref(0)

const form = ref({
  booking_id: null,
  customerPhone: null,
  hotel_service: 5,
  staff: 5,
  room: 5,
  stars: 0,
  comment: ''
})
const idBooking = ref(null)

const popUpEvaluate = async (bookingId) => {
  popupDetail.value = false
  idBooking.value = bookingId   // gán giá trị cho ref
  popupEvaluate.value = true
}

const sendEvaluate = async () => {
  if (form.value.stars === 0) {
    alert('Vui lòng chọn đánh giá sao trước khi gửi!');
  }
  try {
    isLoading.value = true;
    const customerPhone = "0" + localStorage.getItem('BookingAuthPhone')
    console.log('Đang gửi đánh giá cho booking', idBooking.value)
    console.log('Số điện thoại khách:', customerPhone)
    const data = {
      booking_id: idBooking.value,
      customerPhone: customerPhone,
      hotel_service: form.value.hotel_service,
      staff: form.value.staff,
      room: form.value.room,
      stars: form.value.stars,
      comment: form.value.comment
    }
    console.log('Dữ liệu đánh giá:', data);
    const res = await axios.post(`${apiUrl}/api/customer-reviews`, data);
    console.log('Đánh giá thành công:', res.data);
    alert('Cảm ơn bạn đã đánh giá! Chúng tôi sẽ cải thiện dịch vụ tốt hơn.');
    form.value = {
      booking_id: null,
      customerPhone: null,
      hotel_service: 5,
      staff: 5,
      room: 5,
      stars: 0,
      comment: ''
    };
    popupEvaluate.value = false; // Đóng popup sau khi gửi đánh giá}
  } catch (error) {
    console.error('Lỗi gửi đánh giá:', error);
    alert('Đã có lỗi xảy ra khi gửi đánh giá. Vui lòng thử lại sau.');
  } finally {
    isLoading.value = false;
  }
}

const phoneNumber = ref('');
const otpInputs = ref();
const isOtp = ref(false); // Biến để kiểm soát hiển thị OTP
const verificationId = ref(null);
const message = ref(''); // Biến để hiển thị thông báo lỗi
const checkAndSendOtp = async () => {
  if (!phoneNumber.value) {
    alert('Vui lòng nhập số điện thoại!');
    // Tập trung vào input số điện thoại
    document.getElementById('phone').focus(); // Đảm bảo ID đúng với input của bạn
    resetRadio();
    return; // Ngừng thực hiện hàm nếu không có số điện thoại
  }
  const phone = String(phoneNumber.value || '').trim();
  const storageKey = 'sentOtpPhones';
  if (!phone) return;

  const sentPhones = JSON.parse(localStorage.getItem(storageKey)) || [];
  const isDuplicate = sentPhones.includes(phone);

  if (isDuplicate) {
    console.log('Số điện thoại đã được xác thực trước đó, không cần gửi OTP nữa.');
    authSms.value = true; // Đánh dấu đã xác thực
    const currentPhone = localStorage.getItem('BookingAuthPhone');
    if (!currentPhone || currentPhone !== phoneNumber.value) {
      localStorage.setItem('BookingAuthPhone', phoneNumber.value);
    }
    getHistoryBooking();
    showPopUpSMS.value = false; // Đóng popup nếu đã xác thực
    // Nếu đã xác thực rồi thì thực hiện luôn        
  } else {
    console.log('Số điện thoại chưa được xác thực trước đó,  cần gửi OTP nữa.');
    // Gửi OTP rồi đợi xác thực mới thực hiện
    sendOtpSMS();
  }
};
const authSms = ref(false);
const sendOtpSMS = async () => {
  isLoading.value = true; // Bắt đầu quá trình tải
  try {
    // Kiểm tra xem số điện thoại đã được nhập chưa
    if (!phoneNumber.value) {
      alert('Vui lòng nhập số điện thoại!');
      // Tập trung vào input số điện thoại
      document.getElementById('phone').focus(); // Đảm bảo ID đúng với input của bạn
      return; // Ngừng thực hiện hàm nếu không có số điện thoại
    }
    // Kiểm tra xem auth có được khởi tạo đúng cách
    if (!auth) {
      throw new Error('auth chưa được khởi tạo. Kiểm tra cấu hình Firebase.');
    }

    // Khởi tạo RecaptchaVerifier
    const appVerifier = new RecaptchaVerifier(auth, 'recaptcha-container', {
      size: 'invisible',
      callback: (response) => {
        //console.log('ReCAPTCHA đã được xác minh:', response);
      },
      'expired-callback': () => {
        //console.warn('ReCAPTCHA đã hết hạn.');
      },
    });
    // Thêm +84 vào trước số điện thoại
    const fullPhoneNumber = `+84${phoneNumber.value}`;
    // console.log(fullPhoneNumber)
    // return;
    const confirmationResult = await signInWithPhoneNumber(auth, fullPhoneNumber, appVerifier);
    verificationId.value = confirmationResult.verificationId;
    //alert('Mã xác nhận đã được gửi thành công! Vui lòng kiểm tra tin nhắn.');
    isOtp.value = true;
    showPopUpSMS.value = false; // Đóng popup nếu đã xác thực

  } catch (error) {
    console.error('Lỗi gửi mã xác nhận:', error.message || error);
    alert(`Lỗi gửi mã xác nhận: SDT không hợp lệ hoặc đã được đăng ký trước đó. Vui lòng thử lại.`);
    //location.reload();
  } finally {
    isLoading.value = false; // Kết thúc quá trình tải
  }
}
const verifyCode = async () => {
  isLoading.value = true;
  try {
    const credential = PhoneAuthProvider.credential(verificationId.value, otpInputs.value);
    const result = await signInWithCredential(auth, credential);

    //  Xác thực OTP thành công
    isOtp.value = false;
    const phone = String(phoneNumber.value || '').trim();
    const storageKey = 'sentOtpPhones';
    const sentPhones = JSON.parse(localStorage.getItem(storageKey)) || [];

    if (!sentPhones.includes(phone)) {
      sentPhones.push(phone);
      localStorage.setItem(storageKey, JSON.stringify(sentPhones));
    }
    authSms.value = true; // Đánh dấu đã xác thực
    const currentPhone = localStorage.getItem('BookingAuthPhone');
    if (!currentPhone || currentPhone !== phoneNumber.value) {
      localStorage.setItem('BookingAuthPhone', phoneNumber.value);
    }
    await getHistoryBooking();
    closeModalOtp();
    //  Sau xác thực thì tiếp tục hành động: bạn chọn 1 trong 2 bên dưới
    // if (paymentMethod.value === 'thanh_toan_sau') {
    //     await confirmBooking();
    //     if (returnout.value != true) {
    //         router.push('/thanksBooking');
    //     }
    // } else if (paymentMethod.value === 'thanh_toan_qr') {
    //     await payQr();
    // }

  } catch (error) {
    console.error('Lỗi xác minh mã:', error.message || error);
    alert(`OTP không hợp lệ. Vui lòng thử lại.`);
  } finally {
    isLoading.value = false;
  }
};
const closeModalOtp = async () => {
  showPopUpSMS.value = false;
  isOtp.value = false;
}
const getCancelBookingDetail = async (bookingId) => {
  try {
    isLoading.value = true;
    const token = localStorage.getItem('BookingAuth') || '';
    const axiosInstance = axios.create({
      headers: { 'Authorization': `Bearer ${token}` }
    });
    const res = await axiosInstance.get(`${apiUrl}/api/cancel-booking/${bookingId}`);
    if (res.data?.status === 'success') {
      cancelDetails.value[bookingId] = res.data.data;
      await loadBankList();
      //console.log(`Thông tin hủy cho booking ${bookingId}:`, cancelDetails.value[bookingId]);
    }
  } catch (e) {
    console.error(`Không thể lấy thông tin hủy cho booking ${bookingId}`, e);
  } finally {
    isLoading.value = false;
  }
};
const banks = ref([]);

const loadBankList = async () => {
  if (banks.value.length > 0) return; // ✅ Đã tải thì không tải lại

  try {
    isLoading.value = true;
    const response = await axios.get('https://api.vietqr.io/v2/banks');
    if (response.data.code === '00') {
      banks.value = response.data.data;
    }
  } catch (error) {
    console.error('Lỗi tải danh sách ngân hàng:', error);
  } finally {
    isLoading.value = false;
  }
};

const submitRefundInfo = async (bookingId) => {
  const info = refundBank.value[bookingId];

  if (!info.bank || !info.accountNumber || !info.accountName) {
    alert('Vui lòng nhập đầy đủ: ngân hàng, số tài khoản và tên chủ tài khoản.');
    return;
  }

  try {
    // const token = localStorage.getItem('BookingAuth') || '';
    // const axiosInstance = axios.create({
    //   headers: { Authorization: `Bearer ${token}` }
    // });
    isLoading.value = true;
    const response = await axios.post(`${apiUrl}/api/cancel-booking/${bookingId}/bank-info`, {
      refund_bank: info.bank,
      refund_account_number: info.accountNumber,
      refund_account_name: info.accountName
    });

    if (response.data.status === 'success') {
      //alert('Cập nhật thông tin hoàn tiền thành công!');
      getCancelBookingDetail(bookingId); // Cập nhật lại thông tin hủy
    } else {
      alert('Có lỗi xảy ra: ' + response.data.message);
    }
  } catch (err) {
    console.error('Lỗi gửi thông tin hoàn tiền:', err);
    alert('Không thể gửi thông tin. Vui lòng thử lại.');
  } finally {
    isLoading.value = false;
  }
};

const showCancelPopup = ref(false);
const selectedReason = ref('');
const customReason = ref('');

// Đây là lý do thật sự sẽ gửi
const cancellationReason = computed(() =>
  selectedReason.value === 'Khác' ? customReason.value : selectedReason.value
);
const currentBookingId = ref(null);

const confirmCancellation = async () => {
  if (!cancellationReason.value) {
    alert('Vui lòng chọn lý do hủy.');
    return;
  }

  try {
    isLoading.value = true;
    const token = localStorage.getItem('BookingAuth') || '';
    const axiosInstance = axios.create({
      headers: { 'Authorization': `Bearer ${token}` }
    });

    await axiosInstance.delete(`${apiUrl}/api/booking-history/${currentBookingId.value}`, {
      data: { cancellation_reason: cancellationReason.value }
    });

    alert('Đã gửi yêu cầu hủy đặt phòng thành công! Chúng tôi sẽ xử lý trong thời gian sớm nhất.');
    showCancelPopup.value = false; // Đóng popup
    cancellationReason.value = ''; // Reset lý do
    getHistoryBooking(); // Cập nhật lịch sử đặt phòng
  } catch (error) {
    alert('Không thể hủy đơn. Vui lòng thử lại sau.');
    console.error('Error cancelling booking:', error);
  } finally {
    isLoading.value = false;
  }
};

const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  return new Date(dateString).toLocaleDateString('vi-VN', {
    year: 'numeric', month: '2-digit', day: '2-digit',
  });
};

const formatCurrency = (amount) => {
  return (Number(amount) || 0).toLocaleString('vi-VN', {
    style: 'currency',
    currency: 'VND'
  });
};

const statusMap = {
  pending_confirmation: 'Đang chờ xác nhận',
  confirmed_not_assigned: 'Đã xác nhận',
  confirmed: 'Đã nhận phòng',
  cancelled: 'Đã hủy',
  pending_cancel: 'Đang chờ hủy',
  completed: 'Hoàn thành',
};

const formatStatus = (status) => statusMap[status] || status;
const formatStatusClass = (status) => `bg-${status.replace(/_/g, '-')}`;

const formatPayment = (method) => {
  const map = {
    thanh_toan_qr: 'QR Code',
    thanh_toan_ngay: 'Thanh toán trực tiếp tại khách sạn',
    thanh_toan_sau: 'Thanh toán trực tiếp tại khách sạn',
  };
  return map[method] || 'Không rõ';
};

const formatPaymentStatus = (status) => {
  const map = {
    pending: 'Đang xử lý',
    failed: 'Đang xử lý',
    completed: 'Đã thanh toán',
  };
  return map[status] || 'Không rõ';
};

const canCancelBooking = (booking) => {
  //console.log('Checking if booking can be cancelled:', booking);
  if (booking.status === 'cancelled') return false;
  const checkInDate = new Date(booking.check_in_date);
  const now = new Date();
  checkInDate.setDate(checkInDate.getDate() - 1);
  return now < checkInDate;
};

onMounted(getHistoryBooking);
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap');

/* nút sđt */
.phone-form {
  display: flex;
  align-items: center;
  justify-content: center;
  max-width: 400px;
  margin: 20px auto;
  background: #fff;
  border: 1px solid #ddd;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
}

.phone-input {
  flex: 1;
  padding: 12px 15px;
  border: none;
  outline: none;
  font-size: 16px;
}

.send-btn {
  background-color: rgb(87, 196, 240);
  /* background: linear-gradient(135deg, #66a2d6, #00fe37); */
  color: white;
  border: none;
  padding: 12px 20px;
  cursor: pointer;
  font-size: 16px;
  font-weight: 500;
  transition: 0.3s;
}

.send-btn:hover {
  background: linear-gradient(135deg, #43e97b, #38f9d7);
}


/* Custom styles cho hệ thống sao */
.star-btn {
  background: transparent;
  border: 0;
  padding: 0;
  cursor: pointer;
}

.star {
  font-size: 2.5rem;
  color: #ced4da;
  transition: color 0.2s;
}

.star.filled {
  color: #ffc107;
}

/* Ẩn hiện modal theo Bootstrap */
.modal.show {
  display: block;
}

/* --- GENERAL & LAYOUT --- */
.history-wrapper {
  background-color: #f4f7f9;
  font-family: 'Nunito', sans-serif;
  min-height: 100vh;
  padding: 2rem 1rem;
}

.history-container {
  max-width: 950px;
  margin: 0 auto;
}

.page-header {
  text-align: center;
  margin-bottom: 3rem;
}

.page-header h1 {
  font-size: 2.8rem;
  font-weight: 800;
  color: #2d3748;
}

.page-header p {
  font-size: 1.1rem;
  color: #718096;
  margin-top: 0.5rem;
}

/* --- INFO STATE (EMPTY/ERROR) --- */
.info-state {
  text-align: center;
  padding: 3rem 1rem;
}

.icon-wrapper {
  width: 90px;
  height: 90px;
  border-radius: 50%;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 1.5rem;
}

.icon-wrapper.empty {
  background-color: #e6fffa;
  color: #38b2ac;
}

.icon-wrapper.error {
  background-color: #fed7d7;
  color: #e53e3e;
}

.icon-wrapper i {
  font-size: 2.5rem;
}

.info-state h2 {
  font-size: 1.8rem;
  font-weight: 700;
  color: #2d3748;
}

.info-state p {
  color: #718096;
  max-width: 400px;
  margin: 0.5rem auto 1.5rem;
}

/* --- BOOKING LIST & CARD --- */
.booking-list {
  display: grid;
  gap: 1.5rem;
}

.booking-card {
  background-color: #fff;
  border-radius: 12px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  display: flex;
  transition: all 0.3s ease-in-out;
  border-left: 5px solid;
}

.booking-card:hover {
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
  transform: translateY(-4px);
}

.card-main-content {
  flex-grow: 1;
}

.card-header {
  padding: 1rem 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #edf2f7;
}

.card-header h3 {
  font-size: 1.25rem;
  font-weight: 800;
  color: #2d3748;
  margin: 0;
}

.card-body {
  padding: 1.5rem;
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 1.5rem;
}

.info-item {
  display: flex;
  flex-direction: column;
}

.info-item label {
  font-size: 0.8rem;
  font-weight: 600;
  color: #a0aec0;
  text-transform: uppercase;
}

.info-item span {
  font-size: 1rem;
  font-weight: 700;
  color: #4a5568;
}

.note {
  margin-top: 1.5rem;
  padding: 1rem;
  background-color: #f7fafc;
  border-radius: 8px;
  font-size: 0.9rem;
  color: #4a5568;
  border-left: 3px solid #cbd5e0;
}

.note strong {
  color: #2d3748;
}

/* Status Borders */
.status-border-pending_confirmation,
.status-border-confirmed_not_assigned {
  border-color: #4299e1;
}

.status-border-confirmed,
.status-border-completed {
  border-color: #48bb78;
}

.status-border-cancelled {
  border-color: #f56565;
}

.status-border-pending_cancel {
  border-color: #ed8936;
}

/* Status Badges */
.status-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 99px;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
}

.status-pending_confirmation,
.status-confirmed_not_assigned {
  background-color: #bee3f8;
  color: #2b6cb0;
}

.status-confirmed,
.status-completed {
  background-color: #c6f6d5;
  color: #2f855a;
}

.status-cancelled {
  background-color: #fed7d7;
  color: #9b2c2c;
}

.status-pending_cancel {
  background-color: #feebc8;
  color: #9c4221;
}

/* --- CARD DETAILS PANEL (RIGHT) --- */
.card-details-panel {
  width: 280px;
  flex-shrink: 0;
  background-color: #f7fafc;
  border-left: 1px solid #edf2f7;
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
}

.price-section {
  text-align: center;
}

.price-section label {
  color: #718096;
  font-size: 0.9rem;
}

.price-section .price {
  display: block;
  font-size: 2rem;
  font-weight: 800;
  color: #2d3748;
}

.payment-details {
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 1px solid #e2e8f0;
}

.payment-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  color: #4a5568;
  font-size: 0.9rem;
}

.payment-item:not(:last-child) {
  margin-bottom: 0.75rem;
}

.payment-item i {
  font-size: 1.2rem;
  color: #a0aec0;
}

.payment-item i.text-success {
  color: #48bb78;
}

.payment-item i.text-warning {
  color: #ed8936;
}

.card-actions {
  margin-top: auto;
  padding-top: 1rem;
  text-align: center;
}

.update-time {
  display: block;
  font-size: 0.75rem;
  color: #a0aec0;
  margin-top: 0.5rem;
}

/* --- PENDING CANCEL CARD --- */
.pending-cancel-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: #b7791f;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.cancellation-details {
  display: grid;
  gap: 0.75rem;
  font-size: 0.95rem;
  color: #4a5568;
}

.cancellation-details div {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.cancellation-details i {
  color: #a0aec0;
}

.cancellation-details .refund-info {
  background-color: #f7fafc;
  padding: 0.75rem;
  border-radius: 8px;
  margin-top: 0.5rem;
}

.cancellation-details .refund-info .price {
  font-weight: 700;
  color: #2f855a;
}

.refund-form-container {
  margin-top: 1.5rem;
  padding: 1.5rem;
  border-radius: 8px;
  background-color: #f7fafc;
  border: 1px solid #e2e8f0;
}

.refund-form-title {
  text-align: center;
  font-weight: 700;
  color: #2d3748;
  margin-bottom: 1rem;
}

.refund-display {
  font-size: 0.9rem;
}

.refund-display p {
  margin-bottom: 0.5rem;
}

.alert-success {
  margin-top: 1rem;
  padding: 0.75rem;
  background-color: #c6f6d5;
  color: #2f855a;
  border-radius: 6px;
  text-align: center;
  font-weight: 600;
  font-size: 0.9rem;
}

/* --- MODAL --- */
.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(45, 55, 72, 0.75);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background-color: #fff;
  border-radius: 12px;
  width: 90%;
  max-width: 500px;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}

.modal-header {
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #e2e8f0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h3 {
  font-size: 1.2rem;
  color: #2d3748;
  margin: 0;
}

.modal-body {
  padding: 1.5rem;
}

.modal-body p {
  color: #718096;
}

.modal-footer {
  padding: 1rem 1.5rem;
  background-color: #f7fafc;
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  border-top: 1px solid #e2e8f0;
  border-radius: 0 0 12px 12px;
}

.close-button {
  background: none;
  border: none;
  font-size: 1.8rem;
  color: #a0aec0;
  cursor: pointer;
  transition: color 0.2s;
}

.close-button:hover {
  color: #2d3748;
}

/* --- FORMS & BUTTONS --- */
.form-group {
  margin-bottom: 1rem;
}

.form-group label {
  display: block;
  font-size: 0.875rem;
  font-weight: 600;
  color: #4a5568;
  margin-bottom: 0.5rem;
}

.form-control {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 1px solid #cbd5e0;
  border-radius: 8px;
  font-size: 1rem;
  font-family: 'Nunito', sans-serif;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.form-control:focus {
  outline: none;
  border-color: #4299e1;
  box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.5);
}

.btn {
  padding: 0.6rem 1.2rem;
  border-radius: 8px;
  font-weight: 700;
  border: none;
  cursor: pointer;
  transition: all 0.2s;
  font-size: 0.9rem;
  display: inline-flex;
  align-items: center;
}

.btn-primary {
  background-color: #4299e1;
  color: #fff;
}

.btn-primary:hover {
  background-color: #2b6cb0;
}

.btn-secondary {
  background-color: #e2e8f0;
  color: #4a5568;
}

.btn-secondary:hover {
  background-color: #cbd5e0;
}

.btn-danger {
  background-color: #e53e3e;
  color: #fff;
}

.btn-danger:hover {
  background-color: #c53030;
}

.btn:disabled {
  background-color: #e2e8f0;
  color: #a0aec0;
  cursor: not-allowed;
}

.btn-light-danger {
  background-color: #fed7d7;
  color: #c53030;
  padding: 0.5rem 1rem;
  font-size: 0.8rem;
}

.btn-light-danger:hover {
  background-color: #feb2b2;
}

.w-100 {
  width: 100%;
  justify-content: center;
}

/* --- RESPONSIVE --- */
@media (max-width: 900px) {
  .booking-card {
    flex-direction: column;
  }

  .card-details-panel {
    width: 100%;
    border-left: none;
    border-top: 1px solid #edf2f7;
  }
}

/* form otp */
.popup-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  /* Nền đen mờ */
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 2000;
  /* Đảm bảo popup nằm trên các phần tử khác */
}

.form-container {
  margin: 0;
  /* Bỏ margin */
  max-width: 400px;
  background-color: #fff;
  padding: 32px 24px;
  font-size: 14px;
  font-family: inherit;
  color: #212121;
  display: flex;
  flex-direction: column;
  gap: 20px;
  box-sizing: border-box;
  border-radius: 10px;
  box-shadow: 0px 0px 3px rgba(0, 0, 0, 0.084), 0px 2px 3px rgba(0, 0, 0, 0.168);
}

.form-container button:active {
  scale: 0.95;
}


.form-container .logo-container {
  text-align: center;
  font-weight: 600;
  font-size: 18px;
}

.form-container .form {
  display: flex;
  flex-direction: column;
}

.form-container .form-group {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.form-container .form-group label {
  display: block;
  margin-bottom: 5px;
}

.form-container .form-group input {
  width: 100%;
  padding: 12px 16px;
  border-radius: 6px;
  font-family: inherit;
  border: 1px solid #ccc;
}

.form-container .form-group input::placeholder {
  opacity: 0.5;
}

.form-container .form-group input:focus {
  outline: none;
  border-color: #1778f2;
}

.form-container .form-submit-btn {
  display: flex;
  justify-content: center;
  align-items: center;
  font-family: inherit;
  color: #fff;
  background-color: #212121;
  border: none;
  width: 100%;
  padding: 12px 16px;
  font-size: inherit;
  gap: 8px;
  margin: 12px 0;
  cursor: pointer;
  border-radius: 6px;
  box-shadow: 0px 0px 3px rgba(0, 0, 0, 0.084), 0px 2px 3px rgba(0, 0, 0, 0.168);
}

.form-container .form-submit-btn:hover {
  background-color: #313131;
}

.form-container .link {
  color: #1778f2;
  text-decoration: none;
}

.form-container .signup-link {
  align-self: center;
  font-weight: 500;
}

.form-container .signup-link .link {
  font-weight: 400;
}

.form-container .link:hover {
  text-decoration: underline;
}
</style>