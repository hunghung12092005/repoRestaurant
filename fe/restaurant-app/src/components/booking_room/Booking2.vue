<template>
    <loading v-if="isLoading"></loading>
    <div>
        <div>
            <button @click="showPopup = true">Xem Nguyên Tắc Chung</button>
            <Popup v-if="showPopup" :isVisible="showPopup" @close="showPopup = false" />
        </div>
        <!-- Hero Section -->
        <header class="hero-section d-flex align-items-center">
            <div class="container text-center">
                <h1 class="display-3 fw-bold">Booking Rooms</h1>
                <p class="lead">Hồ Xuân Hương Hotel </p>
            </div>
        </header>
        <!-- <div class="btn btn-info" @click="showPopUpMain">Xem nguyên tắc chung</div> -->
        <div class="tooltip-container">
            <div class="icon" @click="showPopUpMain">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="50" height="50">
                    <path
                        d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm0 22c-5.518 0-10-4.482-10-10s4.482-10 10-10 10 4.482 10 10-4.482 10-10 10zm-1-16h2v6h-2zm0 8h2v2h-2z">
                    </path>
                </svg>
                <span class="icon-text">Nguyên Tắc Chung</span>
            </div>
            <div class="tooltip">
                <p>Thông báo</p>
            </div>
        </div>

        <!-- Search Box -->
        <section class="container">
            <div class="search-box p-4 shadow">
                <form @submit.prevent="getRoomPrices">
                    <div class="row g-3">
                        <!-- <div class="col-md-3">
                <label for="destination" class="form-label">Destination</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                  <input type="text" class="form-control" v-model="search.destination" placeholder="Where are you going?" />
                </div>
              </div> -->
                        <div class="col-md-3">
                            <label for="checkIn" class="form-label">Check in</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                                <input type="date" class="form-control" v-model="checkin" :min="minCheckin"
                                    @change="validateDates" />
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label for="checkOut" class="form-label">Check out</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                                <input type="date" class="form-control" v-model="checkOut" :min="checkin"
                                    @change="validateDates" />
                            </div>
                        </div>
                        <div v-if="errorMessage" class="alert alert-danger d-flex align-items-center" role="alert">
                            <svg class="bi flex-shrink-0 me-2" role="img" aria-label="Danger:">
                                <use xlink:href="#exclamation-triangle-fill" />
                            </svg>
                            <div>
                                {{ errorMessage }}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label for="destination" class="form-label">Số Phòng</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                                <input type="text" class="form-control" v-model="bookrooms"
                                    placeholder="Bạn muốn book bao nhiêu rooms?" />
                            </div>
                        </div>
                        <!-- <div class="col-md-2">
                            <label for="guests" class="form-label">Số Khách</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-people"></i></span>
                                <select class="form-select">
                                    <option value="1">1 Adult</option>
                                    <option value="2">2 Adults</option>
                                    <option value="3">3 Adults</option>
                                    <option value="4">4 Adults</option>
                                    <option value="5">5+ Adults</option>
                                </select>
                            </div>
                        </div> -->
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100 py-3">
                                <i class="bi bi-search me-2"></i>Check
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </section>

        <!-- Featured Hotels -->
        <section class="container my-4">
            <h2 class="text-center mb-4">Lựa Chọn Hạng Phòng</h2>
            <div class="row g-4">
                <div class="col-md-6 col-lg-4" v-for="hotel in hotels" :key="hotel.id">
                    <div class="card hotel-card h-100">
                        <img :src="hotel.image" class="card-img-top" :alt="hotel.name"
                            style="height: 200px; object-fit: cover;" />
                        <!-- <iframe width="100%" height="255" :src="hotel.youtube_link" title="YouTube video player"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe> -->

                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <h5 class="card-title mb-0">{{ hotel.name }}</h5>
                                <span class="badge bg-primary">{{ hotel.rating }} ★</span>

                            </div>
                            <div class="room-info">
                                <span><img
                                        src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQX45rlQMvzd8yDC0V5dfIZ0agx43DvR3wKWQ&s"
                                        alt="Giường" /> {{ hotel.bed_count }} Giường </span>
                                <span><img
                                        src="https://images.icon-icons.com/3178/PNG/512/team_people_man_woman_group_icon_193939.png"
                                        alt="Người" /> {{ hotel.max_occupancy }} Người</span>
                                <span><img
                                        src="https://static.vecteezy.com/system/resources/previews/006/996/151/non_2x/m2-area-icon-vector.jpg"
                                        alt="Người" /> {{ hotel.m2 }} m2</span>

                            </div>
                            <p class="card-text">{{ hotel.description.substring(0, 85) }}...</p>
                            <!-- <p class="card-text">Phụ thu: {{ formatPrice(hotel.surcharges) }}</p> -->
                            <!-- <p class="card-text">{{ hotel.bed_count }}...</p>
                            <p class="card-text">{{ hotel.max_occupancy }}...</p> -->
                            <p class="fs-5 fw-bold text-primary">{{ formatPrice(hotel.gia1h) }} / 1 Giờ/ 1 Phòng</p>
                            <div class="d-flex align-items-center">
                                <span class="fs-5 fw-bold text-primary">{{ formatPrice(hotel.price) }}</span>


                                <span class="text-muted ms-1">/ {{ hotel.total_days }} Night / {{ hotel.so_phong }}
                                    Phòng </span>

                            </div>
                        </div>
                        <div class="card-footer bg-white">
                            <div class="d-flex justify-content-between">
                                <button class="btn btn-outline-primary" @click="viewHotelDetails(hotel)">
                                    <i class="bi bi-info-circle me-1"></i>Xem chi tiết
                                </button>
                                <button class="btn btn-primary" @click="bookHotel(hotel)">
                                    <i class="bi bi-calendar-check me-1"></i>Book Now
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- Special Deals & Offers -->
        <section class="bg-light py-5" id="deals">
            <div class="container">
                <h2 class="text-center mb-4">Special Deals & Offers</h2>
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="card text-center h-100">
                            <div class="card-body">
                                <span class="badge bg-danger mb-3">Limited Time</span>
                                <h3 class="card-title">Weekend Escape</h3>
                                <p class="card-text">
                                    Get 25% off on weekend bookings at select hotels
                                </p>
                                <p class="fs-4 text-primary fw-bold">25% OFF</p>
                                <button class="btn btn-outline-primary">View Deal</button>
                            </div>
                            <div class="card-footer text-muted">Valid until May 31, 2025</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center h-100 border-primary">
                            <div class="card-header bg-primary text-white">MOST POPULAR</div>
                            <div class="card-body">
                                <span class="badge bg-success mb-3">Best Value</span>
                                <h3 class="card-title">Stay 3, Pay 2</h3>
                                <p class="card-text">
                                    Book 3 nights and only pay for 2 at participating luxury
                                    hotels
                                </p>
                                <p class="fs-4 text-primary fw-bold">1 FREE NIGHT</p>
                                <button class="btn btn-primary">View Deal</button>
                            </div>
                            <div class="card-footer text-muted">
                                Valid until June 30, 2025
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center h-100">
                            <div class="card-body">
                                <span class="badge bg-info mb-3">Member Exclusive</span>
                                <h3 class="card-title">Early Booking Discount</h3>
                                <p class="card-text">
                                    Book 30 days in advance and save up to 15% on your stay
                                </p>
                                <p class="fs-4 text-primary fw-bold">UP TO 15% OFF</p>
                                <button class="btn btn-outline-primary">View Deal</button>
                            </div>
                            <div class="card-footer text-muted">Always available</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- Newsletter -->
        <section class="bg-primary text-white py-5">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6">
                        <h5>Để lại thông tin để bộ phận CSKH HXH hotel liên hệ hỗ trợ bạn ngay hoặc liên hệ chatbox CSKH
                            Online Hotel <router-link class="dropdown-item " to="/chat">tại đây</router-link></h5>
                    </div>
                    <div class="col-lg-6">
                        <form id="newsletterForm" class="row g-3">
                            <div class="col-md-8">
                                <input type="email" class="form-control form-control-lg"
                                    placeholder="Your email address" required />
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-light btn-lg w-100">
                                    Subscribe
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
        <!-- Why Choose Us -->
        <section class="bg-light py-5" id="about">
            <h2 class="text-center mb-5">Why Choose Ho Xuan Huong Hotel</h2>
            <div class="row g-4">
                <div class="col-md-4 text-center">
                    <div class="feature-icon mb-3">
                        <i class="bi bi-patch-check"></i>
                    </div>
                    <h3>Best Price Guarantee</h3>
                    <p>
                        Find a lower price elsewhere? We'll match it and give you an
                        additional 10% off.
                    </p>
                </div>
                <div class="col-md-4 text-center">
                    <div class="feature-icon mb-3">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h3>Secure Booking</h3>
                    <p>
                        Your payment and personal information are always protected with our
                        advanced security system.
                    </p>
                </div>
                <div class="col-md-4 text-center">
                    <div class="feature-icon mb-3">
                        <i class="bi bi-headset"></i>
                    </div>
                    <h3>24/7 Support</h3>
                    <p>
                        Our customer service team is available around the clock to assist
                        you with any questions or issues.
                    </p>
                </div>
            </div>
        </section>

        <!-- Modal for hotel details -->
        <div v-if="showModal" class="modal fade show" style="display: block;">
            <div class="modal-dialogdetail modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Hotel Details</h5>

                        <button type="button" class="btn-close" @click="closeModal" aria-label="Close">X</button>
                    </div>
                    <div class="modal-body">
                        <h4 class="hotel-name">{{ selectedHotel.name }}</h4>
                        <img :src="selectedHotel.image" class="card-img-top" :alt="selectedHotel.name" />
                        <p><strong>Giá 1 đêm/phòng:</strong> {{ formatPrice(selectedHotel.price_per_night) }}</p>
                        <p><strong>Phụ Thu (chỉ áp dụng dịp lễ, ngày đặc biệt):</strong> {{
                            formatPrice(selectedHotel.surcharges) }}</p>
                        <p><strong>Số Phòng:</strong> {{ selectedHotel.so_phong }}</p>
                        <p><strong>Số ngày ở:</strong> {{ selectedHotel.currency }}{{ selectedHotel.total_days }} Ngày
                        </p>
                        <p><strong>Tổng tiền:</strong> {{ formatPrice(selectedHotel.price) }}</p>
                        <p><strong>Mô Tả:</strong> {{ selectedHotel.description }}</p>
                        <hr />
                        <h4>Tiện Nghi:</h4>
                        <ul>
                            <li v-if="selectedHotel.amenities.length === 0">Không có tiện nghi đi kèm cho hạng phòng này
                            </li>
                            <li v-for="amenity in selectedHotel.amenities" :key="amenity.amenity_id">
                                {{ amenity.amenity_name }}: {{ amenity.description }}
                            </li>
                        </ul>
                        <h4>Dịch Vụ:</h4>
                        <ul>
                            <li v-if="selectedHotel.services.length === 0">Không có dịch vụ đi kèm cho hạng phòng này
                            </li>
                            <li v-for="service in selectedHotel.services" :key="service.service_id">
                                {{ service.service_name }}: {{ formatPrice(service.price) }}
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <!-- Popup Booking Modal -->
        <div v-show="showModalBooking" class="modal-backdrop">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title m-2" id="bookingModalLabel">Thông tin Khách hàng</h5>
                        <button type="button" class="btn-close m-4" @click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4 reservation-section m-2">

                                <div class="mb-4">
                                    <label for="fullName" class="form-label">Họ và tên <span
                                            class="text-muted small">(Bắt buộc)</span></label>
                                    <input type="text" class="form-control" id="fullName" v-model="fullName" required>
                                    <small class="form-text text-muted">Nhập họ và tên đầy đủ.</small>
                                </div>
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Số điện thoại <span
                                            class="text-muted small">(Bắt buộc)</span></label>
                                    <input type="number" class="form-control" id="phone" v-model="phoneNumber" required>
                                    <!-- <small class="form-text text-muted">Nhập số điện thoại hợp lệ.</small> -->
                                </div>
                                <div class="mb-3">
                                    <label for="orderNotes" class="form-label">Ghi chú Đặt hàng (Tùy chọn)</label>
                                    <textarea class="form-control" id="orderNotes" v-model="orderNotes"
                                        rows="3"></textarea>
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="createAccount"
                                        v-model="customer.createAccount" value="true">
                                    <label class="form-check-label" for="createAccount">Tôi hoàn toàn đồng ý với quy
                                        tắc
                                        chung của khách sạn</label>
                                </div>
                            </div>
                            <div class="col-md-5 payment-section" v-if="selectedHotelBooking">
                                <div class="receipt">
                                    <p class="shop-name">Bill Market</p>
                                    <p class="info">
                                        Ho Xuan Huong Hotel, 23/89<br />
                                        Thanh Mai, Quang Thanh<br />
                                        Date: {{ currentDateTime }}<br />
                                    </p>
                                    <table>
                                        <thead>
                                            <!-- <tr>
                                                <th>Số lượng phòng</th>
                                                <th>Thời gian</th>
                                            </tr> -->
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td></td>
                                                <td></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <div class="total">
                                        <p>Số tiền 1 phòng / 1 ngày:</p>
                                        <!-- <p>{{ formatPrice(selectedHotelBooking.so_tien1phong) }}</p> -->
                                        <p>{{ formatPrice(selectedHotelBooking.price_per_night) }}</p>
                                    </div>
                                    <div class="total">
                                        <p>Số Ngày Ở:</p>
                                        <p>{{ selectedHotelBooking.total_days }} Ngày</p>
                                    </div>

                                    <div class="total">
                                        <p>Số Phòng Book:</p>
                                        <p>{{ selectedHotelBooking.so_phong }} Phòng</p>
                                    </div>
                                    <div class="total">
                                        <p>Phụ Phí:</p>
                                        <p>{{ formatPrice(selectedHotelBooking.surcharges) }}</p>
                                    </div>
                                    <div class="service" v-if="serviceRoom.length">
                                        <p>Lựa chọn dịch vụ</p>
                                        <div v-for="service in serviceRoom" :key="service.service_id">
                                            <label>
                                                {{ service.service_name }} - Giá: {{ formatPrice(service.price) }} -
                                                Miêu tả: {{ service.description }}
                                            </label>

                                            <div class="room-selector-custom">
                                                <label>Số phòng sử dụng cho {{ service.service_name }}</label>
                                                <div class="counter-custom">
                                                    <button class="decrement-button"
                                                        @click="decrementService(service.service_id)"
                                                        :disabled="getRoomCount(service.service_id) <= 0">-</button>
                                                    <input type="number" v-model="roomCounts[service.service_id]"
                                                        min="0" :max="bookrooms" readonly />
                                                    <button class="increment-button"
                                                        @click="incrementService(service.service_id)"
                                                        :disabled="getRoomCount(service.service_id) >= bookrooms">+</button>
                                                </div>
                                            </div>

                                            <p class="total-price">Tổng giá dịch vụ cho {{ service.service_name }}:
                                                {{ formatPrice(getRoomCount(service.service_id) * service.price) }}</p>
                                        </div>

                                        <p class="total-price">Tổng giá dịch vụ: {{ formatPrice(calculateServiceTotal)
                                            }}</p>
                                    </div>

                                    <div class="total">
                                        <p>Total: <small>{{ formatPrice(selectedHotelBooking.price) }} Giá Phòng +
                                                {{ formatPrice(calculateServiceTotal) }} Giá Dịch Vụ</small></p>
                                        <p>{{ formatPrice(totalPrice) }}</p>
                                    </div>
                                    <div class="mb-1">
                                        <div class="radio-input">
                                            <input value="value-1" name="value-radio" id="value-1" type="radio"
                                                @click="sendOtpSMS" />
                                            <label for="value-1">
                                                <div class="text">
                                                    <span class="circle"></span>
                                                    Thanh Toán Sau
                                                </div>
                                                <span class="info-pay">SMS OTP</span>
                                            </label>

                                            <input value="value-2" name="value-radio" id="value-2" type="radio"
                                                @click="payQr" />
                                            <label for="value-2">
                                                <div class="text">
                                                    <span class="circle"></span>
                                                    Thanh Toán Ngay
                                                </div>
                                                <span class="info-pay" v-if="selectedHotelBooking">{{
                                                    formatPrice(totalPrice) }}</span>
                                            </label>
                                        </div>

                                    </div>
                                    <p class="thanks">Thank you for shopping with us!</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer m-4">
                        <button type="button" class="btn btn-secondary m-2" @click="closeModal">Đóng</button>
                        <button v-if="confirmBooking" type="submit" class="btn btn-primary" @click="submitBooking">Xác
                            nhận Đặt
                            phòng</button>
                    </div>
                </div>
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
                    <input v-model="otpInputs" type="number" placeholder="Nhập OTP" required="">
                </div>

                <button class="form-submit-btn" type="submit" @click="verifyCode">Xác Nhận</button>

                <p v-if="message">
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <!-- <strong>Thông báo!</strong> {{ message }} -->
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                </p>
            </div>
        </div>
        <div id="recaptcha-container"></div> <!-- ReCAPTCHA -->

        <Footer></Footer>

    </div>
</template>

<script setup>

import { ref, onMounted, computed, inject } from 'vue';
import axios from 'axios';
// import Modal from '../Model.vue';
import loading from '../loading.vue';
import Footer from '../Footer.vue';
import Popup from '../Popup.vue';
import { auth, RecaptchaVerifier, PhoneAuthProvider } from '../ShopOnline/firebase';
import { signInWithPhoneNumber, signInWithCredential } from 'firebase/auth';
const showPopup = ref(false);//popup mới vào trang
const showPopUpMain = () => {
    showPopup.value = true;//popup mới vào trang
}

const hotels = ref([]);
const isLoading = ref(false);
const formatPrice = (value) => {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND',
    }).format(value);
};
//validate lịch
const errorMessage = ref('');
const minCheckin = computed(() => {
    const today = new Date();
    return today.toISOString().split('T')[0];
});
const validateDates = () => {
    errorMessage.value = '';
    if (checkin.value && checkOut.value) {
        if (new Date(checkin.value) >= new Date(checkOut.value)) {
            errorMessage.value = 'Ngày checkout phải sau ngày checkin!';
        }
    }
};
//lấy loại phòng
const getRoomTypes = async () => {
    isLoading.value = true;
    try {
        const response = await axios.get('http://127.0.0.1:8000/api/room-types/');
        //console.log(response.data);
        hotels.value = response.data.data.map(room => ({
            id: room.type_id,
            name: room.type_name,
            description: room.description,
            bed_count: room.bed_count,
            amenities: room.amenities || [], // Lưu thông tin tiện nghi
            services: room.services || [],
            max_occupancy: room.max_occupancy,
            image: "https://i.postimg.cc/d3pNGXPN/7c6764b8-de90-474c-9b98-05019aef3193.png", // Cập nhật với URL hình ảnh thực tế
            youtube_link: room.youtube_link || "https://www.youtube.com/embed/kXaLkZPlYyo?si=Pw0ywUB6VmhsW5XC", // Liên kết video
            price: 0, // Cập nhật giá nếu có
            rating: room.rate, // Cập nhật đánh giá nếu cần
            m2: room.m2, // Cập nhật đánh giá nếu cần
        }));
        //console.log(hotels.value);
        showPopup.value = true;//popup mới vào trang

    } catch (error) {
        console.error("Có lỗi xảy ra khi lấy dữ liệu:", error);
    } finally {
        isLoading.value = false;
    }
}
const checkin = ref();
const checkOut = ref();
const bookrooms = ref();
const orderNotes = ref();
//lấy giá phòng
const getRoomPrices = async () => {
    isLoading.value = true; // Bắt đầu tải dữ liệu
    try {
        // Dữ liệu cần gửi
        const requestData = {
            checkin: checkin.value,
            checkout: checkOut.value,
            bookrooms: bookrooms.value
        };

        // Gọi API lấy giá phòng
        const roomPricesResponse = await axios.post('http://127.0.0.1:8000/api/prices/prices_client', requestData);
        const roomPrices = roomPricesResponse.data || []; // Đảm bảo roomPrices là một mảng
        //console.log("Room Prices:", roomPrices); // Kiểm tra giá phòng

        // Giả định rằng hotels.value đã được khởi tạo và chứa thông tin phòng
        hotels.value = hotels.value.map(room => {
            const roomPrice = roomPrices.find(price => price.type_id === room.id); // Tìm giá tương ứng với type_id

            return {
                ...room, // Kết hợp thông tin phòng hiện tại
                price_per_night: roomPrice ? roomPrice.gia_tien1ngay : 0, // Gán giá nếu tìm thấy, nếu không gán 0
                total_days: roomPrice ? roomPrice.total_days : 0, // Bạn có thể thêm các thông tin khác nếu cần
                surcharges: roomPrice ? roomPrice.surcharges : 0,// Thêm phụ phí nếu có
                price: roomPrice.total_price,
                so_phong: roomPrice.so_phong,
                gia_tien1ngay: roomPrice.gia_tien1ngay,
                so_tien1phong: roomPrice.so_tien1phong,
                gia1h: roomPrice.gia1h
            };
        });

        console.log("Updated Hotels:", hotels.value); // In ra mảng hotels đã được cập nhật

    } catch (error) {
        console.error("Có lỗi xảy ra khi lấy dữ liệu:", error);
    } finally {
        isLoading.value = false; // Kết thúc tải dữ liệu
    }
}
const customer = ref({
    // country: 'VietNam',
    // fullName: '',
    // address: '',
    // email: '',
    // phone: '',
    // orderNotes: '',
    createAccount: true
});

const showModal = ref(false);
const showModalBooking = ref(false);
const selectedHotel = ref(null);
const selectedHotelBooking = ref(null);

const currentDateTime = new Date().toLocaleString();
const phoneNumber = ref('');
const fullName = ref('');

const serviceRoom = ref([]);
const selectedServices = ref([]); // Mảng lưu giá các dịch vụ đã chọn
const roomCounts = ref({}); // Để lưu số phòng cho từng dịch vụ
//Xử lý khi bấm vào cái hạng phòng đó
const gia1_phong = ref();
function bookHotel(hotel) {
    selectedHotelBooking.value = hotel;

    // Kiểm tra xem services có phải là mảng không
    if (Array.isArray(selectedHotelBooking.value.services)) {
        serviceRoom.value = selectedHotelBooking.value.services;
    } else {
        console.warn('Dịch vụ không hợp lệ, khởi tạo mảng rỗng.');
        serviceRoom.value = []; // Khởi tạo mảng rỗng nếu không hợp lệ
    }

    gia1_phong.value = selectedHotelBooking.value.price;
    showModalBooking.value = true;
    selectedServices.value = []; // Reset dịch vụ đã chọn
    numberOfRooms.value = 1; // Reset số phòng sử dụng dịch vụ
}
// Tính toán tổng giá
const numberOfRooms = ref(1); // Hoặc giá trị mặc định khác
//Tính tiền dịch vụ
// Lấy giá dịch vụ từ selectedServices
const getServicePrice = (serviceId) => {
    const service = selectedServices.value[serviceId];
    return service ? service.price : 0;
};

// Tính tiền dịch vụ
// Tính tiền dịch vụ
const calculateServiceTotal = computed(() => {
    return Object.keys(roomCounts.value).reduce((sum, serviceId) => {
        const count = roomCounts.value[serviceId] || 0;
        const service = serviceRoom.value.find(s => s.service_id === Number(serviceId));
        return sum + (service ? service.price * count : 0);
    }, 0);
});

// Tính tổng tiền phòng + tiền dịch vụ
const totalPrice = computed(() => {
    return selectedHotelBooking.value.price + calculateServiceTotal.value; // Tổng tiền
});

// Hàm tăng số lượng phòng cho dịch vụ
const incrementService = (serviceId) => {
    roomCounts.value[serviceId] = (roomCounts.value[serviceId] || 0) + 1;
};

// Hàm giảm số lượng phòng cho dịch vụ
const decrementService = (serviceId) => {
    if ((roomCounts.value[serviceId] || 0) > 0) {
        roomCounts.value[serviceId]--;
    }
};

// Hàm lấy số phòng cho dịch vụ
const getRoomCount = (serviceId) => {
    return roomCounts.value[serviceId] || 0;
};
//xem chi tiết hạng phòng
function viewHotelDetails(hotel) {
    selectedHotel.value = hotel;
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
    showModalBooking.value = false; // Đóng modal sau khi xác nhận
    selectedHotel.value = null;
}
//thanh toán ngay
const confirmBooking = ref(true);//trạng thái nút đặt hàng

const paymentMethod = ref();
const payQr = async () => {
    try {
        console.log(totalPrice.value);
        paymentMethod.value = 'pay_qr';
    } catch (error) {
        console.log(error);
    }
}
//gửi otpsms
const isFormOTP = ref(true);
const isOtp = ref(false);
const otpInputs = ref();
const verificationId = ref(null);

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

        const confirmationResult = await signInWithPhoneNumber(auth, fullPhoneNumber, appVerifier);
        verificationId.value = confirmationResult.verificationId;
        //alert('Mã xác nhận đã được gửi thành công! Vui lòng kiểm tra tin nhắn.');
        isOtp.value = true;

    } catch (error) {
        console.error('Lỗi gửi mã xác nhận:', error.message || error);
        alert(`Lỗi gửi mã xác nhận: ${error.message || error}`);
    } finally {
        isLoading.value = false; // Kết thúc quá trình tải

    }
}
const verifyCode = async () => {
    isLoading.value = true;
    try {
        const credential = PhoneAuthProvider.credential(verificationId.value, otpInputs.value);
        const result = await signInWithCredential(auth, credential);
        //alert('Xác nhận thành công!');
        //closePopup(); // Đóng popup sau khi xác nhận thành công
        isOtp.value = false; //ẩn form  hiển thị otp
        paymentMethod.value = 'thanh_toan_sau';
        confirmBooking.value = true; // Đặt trạng thái đơn hàng đã được xác nhận
        // Thực hiện hành động tiếp theo
    } catch (error) {
        console.error('Lỗi xác minh mã:', error.message || error);
        alert(`Lỗi xác minh mã: ${error.message || error}`);
    } finally {
        isLoading.value = false;
    }
};
const closeModalOtp = async () => {
    isOtp.value = false;
}
const apiUrl = inject('apiUrl'); // Lấy URL API từ inject

//xử lý khi nhấn đặt phòng
const submitBooking = async () => {
    // Kiểm tra thông tin nhập
    if (!phoneNumber.value) {
        alert('Vui lòng nhập số điện thoại!');
        document.getElementById('phone').focus();
        return;
    }

    // Tính toán giá phòng
    const gia_phong = gia1_phong.value / bookrooms.value;

    // Khởi tạo bookingDetails
    const bookingDetails = {
        check_in_date: checkin.value,
        check_out_date: checkOut.value,
        total_rooms: Number(bookrooms.value), // Chuyển đổi thành số
        gia_phong: gia_phong, // Giá phòng đã tính toán
        room_type: selectedHotelBooking.value.id,
        room_services: [], // Danh sách dịch vụ cho từng phòng
        total_price: totalPrice.value,
        phone: phoneNumber.value,
        fullName: fullName.value,
        payment_method: paymentMethod.value,
        booking_type: 'online',
        note: orderNotes.value || 'Không có ghi chú',
    };

    // Xác thực và lấy token
    let token;
    //     const axiosWithoutHeader = axios.create({
    //    // baseURL: apiUrl, // Đặt base URL nếu cần
    //     headers: {} // Không thêm header nào
    // });
    try {
        const authResponse = await axios.post(`${apiUrl}/api/generate-token`, {
            name: fullName.value,
            phone: phoneNumber.value,
            address: '', // Có thể thêm địa chỉ nếu cần
        });
        token = authResponse.data.token;
        localStorage.setItem('BookingAuth', token);
        console.log(localStorage.getItem('BookingAuth'))

        //console.log('Token xác thực:', token);
    } catch (error) {
        console.error('Lỗi khi xác thực:', error);
        alert('Không thể xác thực, vui lòng kiểm tra thông tin!');
        return;
    }

    // Duyệt qua từng dịch vụ đã chọn
    for (const serviceId in roomCounts.value) {
        const serviceCount = roomCounts.value[serviceId]; // Số phòng đã chọn cho dịch vụ này
        const service = serviceRoom.value.find(s => s.service_id === Number(serviceId)); // Tìm dịch vụ tương ứng

        for (let j = 0; j < serviceCount; j++) {
            const roomService = {
                room_number: bookingDetails.room_services.length + 1, // Số phòng hiện tại
                services_id: [serviceId], // ID dịch vụ
                //services_name: [service.service_name], // Tên dịch vụ
                gia_dich_vu_1phong: Number(service.price), // Giá dịch vụ
            };
            bookingDetails.room_services.push(roomService);
        }
    }

    // Đảm bảo số phòng không có dịch vụ
    while (bookingDetails.room_services.length < bookingDetails.total_rooms) {
        bookingDetails.room_services.push({
            room_number: bookingDetails.room_services.length + 1,
            services_id: [],
            services_name: [],
            gia_dich_vu_1phong: 0,
        });
    }
    const axiosWithoutHeader = axios.create({
        // baseURL: apiUrl, // Đặt base URL nếu cần
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`, // Thêm token vào header
        }
    });
    try {
        isLoading.value = true; // Bắt đầu quá trình gửi dữ liệu
        console.log('Thông tin đặt phòng:', JSON.stringify(bookingDetails, null, 2));
        // Gửi yêu cầu đặt phòng
        const response = await axiosWithoutHeader.post(`${apiUrl}/api/booking-client`, bookingDetails);
        console.log('Đặt phòng thành công:', response.data);
    } catch (error) {
        console.error('Lỗi khi gửi thông tin đặt phòng:', error);

        const errorMessage = error.response && error.response.data && error.response.data.error
            ? error.response.data.error
            : error.message || 'Đã xảy ra lỗi không xác định';

        alert(`Lỗi khi gửi thông tin đặt phòng: ${errorMessage}`);
        return;
    } finally {
        isLoading.value = false; // Kết thúc quá trình gửi dữ liệu
    }

    console.log('Thông tin đặt phòng:', bookingDetails);
};

onMounted(() => {
    //lấy mặc định ngày 
    const today = new Date();
    const tomorrow = new Date();
    tomorrow.setDate(today.getDate() + 1);
    checkin.value = today.toISOString().split('T')[0];
    checkOut.value = tomorrow.toISOString().split('T')[0];
    bookrooms.value = 1;
    getRoomTypes();
    getRoomPrices();
});
</script>

<style scoped>
.hero-section {
    background: linear-gradient(rgba(99, 208, 248, 0.6), rgba(0, 0, 0, 0.6)),
        url("https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?ixlib=rb-4.0.3&auto=format&fit=crop&w=1470&q=80");
    background-size: cover;
    background-position: center;
    height: 500px;
    color: white;
}

/* css cái nút tăng giảm */
.room-selector-custom {
    display: flex;
    align-items: center;
    margin-left: 10px;
    background-color: #2780d3;
    /* Màu nền */
    padding: 10px;
    border-radius: 8px;
    color: white;
    /* Màu chữ */
}

.counter-custom {
    /* width: 100%; */
    display: flex;
    align-items: center;
    margin-left: 10px;
}

.decrement-button,
.increment-button {
    background-color: white;
    color: #b03030;
    /* Màu chữ nút */
    border: none;
    border-radius: 5px;
    width: 30px;
    height: 30px;
    cursor: pointer;
}

/* Media Query */
@media screen and (max-width: 400px) {

    .decrement-button,
    .increment-button {
        width: 25px;
        /* Kích thước nút nhỏ hơn trên màn hình nhỏ */
        height: 25px;
    }


}

.decrement-button:disabled,
.increment-button:disabled {
    background-color: #ccc;
    /* Màu nền nút bị vô hiệu hóa */
    cursor: not-allowed;
}

input[type="number"] {
    text-align: center;
    border: none;
    background-color: white;
    color: #b03030;
    /* Màu chữ */
    margin: 0 5px;
}

/* css cho cái dịch vụ booking */
.service {
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 8px;
    background-color: #ffffff;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    margin-top: 20px;
    transition: box-shadow 0.3s ease;
}

.service:hover {
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
}

.service p {
    font-weight: bold;
    font-size: 20px;
    color: #007bff;
    margin-bottom: 15px;
}

.service div {
    margin-bottom: 15px;
}

.service label {
    display: flex;
    align-items: center;
    margin-bottom: 10px;
    cursor: pointer;
    padding: 10px;
    border-radius: 5px;
    transition: background-color 0.3s ease;
}

.service label:hover {
    background-color: #f1f1f1;
}

.service input[type="checkbox"] {
    margin-right: 15px;
    accent-color: #007bff;
    /* Màu sắc cho checkbox */
}

.service input[type="number"] {
    width: 60px;
    margin-left: 10px;
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 4px;
    transition: border-color 0.3s ease;
}

.service input[type="number"]:focus {
    border-color: #007bff;
    outline: none;
}

.service .total-price {
    font-weight: bold;
    font-size: 18px;
    margin-top: 20px;
    color: #28a745;
    /* Màu xanh cho tổng giá */
}

/* popup detail  hotel */
.modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.8);
    /* Tối hơn để làm nổi bật modal */
    z-index: 1040;
    /* Đảm bảo nằm trên các phần tử khác */
}

.modal-dialogdetail {
    width: 100%;
    margin: 0 auto;
    max-width: 700px;
    background-color: #f8f9fa;
    /* Màu nền nhẹ nhàng */
    border-radius: 12px;
    /* Bo góc cho modal */
    box-shadow: 0 4px 30px rgba(0, 0, 0, 0.15);
    /* Đổ bóng tinh tế */
    overflow-y: auto;
    /* Cuộn dọc khi nội dung vượt quá chiều cao */
}

.modal-content {
    padding: 20px;
    /* Đệm cho nội dung */
    border: none;
    /* Bỏ đường viền */
}

.modal-header {
    border-bottom: 2px solid #4A90E2;
    /* Đường viền dưới cho header */
    display: flex;
    justify-content: space-between;
    /* Căn giữa nội dung */
    align-items: center;
    /* Căn giữa theo chiều dọc */
    background-color: #ffffff;
    /* Nền trắng cho header */
}

.modal-title {
    font-size: 1.8rem;
    /* Kích thước chữ tiêu đề lớn hơn */
    color: #333;
    /* Màu chữ tối */
    font-weight: 600;
    /* Đậm hơn */
}

.btn-close {
    background: none;
    /* Bỏ nền cho nút đóng */
    border: none;
    /* Bỏ đường viền */
    color: #999;
    /* Màu chữ cho nút đóng */
    font-size: 1.5rem;
    /* Kích thước chữ cho nút đóng */
}

.btn-close:hover {
    color: #4A90E2;
    /* Thay đổi màu khi hover */
}

.modal-body {
    font-size: 1rem;
    /* Kích thước chữ cho nội dung */
    color: #555;
    /* Màu chữ */
    line-height: 1.5;
    /* Khoảng cách giữa các dòng */
}

.hotel-name {
    color: #4A90E2;
    /* Màu sắc cho tên khách sạn */
    margin-bottom: 15px;
    /* Khoảng cách dưới cho tên khách sạn */
    font-weight: 700;
    /* Đậm hơn */
}

.modal-body img {
    width: 100%;
    /* Đảm bảo hình ảnh chiếm toàn bộ chiều rộng */
    border-radius: 8px;
    /* Bo góc cho hình ảnh */
    margin-bottom: 15px;
    /* Khoảng cách dưới cho hình ảnh */
}

.modal-body h4 {
    margin-top: 20px;
    /* Khoảng cách trên cho tiêu đề */
    color: #4A90E2;
    /* Màu sắc cho tiêu đề */
    font-weight: 600;
    /* Đậm hơn */
}

.modal-body p {
    margin: 10px 0;
    /* Khoảng cách giữa các đoạn */
}

.modal-body ul {
    list-style-type: none;
    /* Bỏ kiểu danh sách */
    padding: 0;
    /* Bỏ đệm */
}

.modal-body li {
    padding: 8px 0;
    /* Đệm cho các mục */
    border-bottom: 1px solid #ddd;
    /* Đường viền dưới cho các mục */
    transition: background-color 0.2s;
    /* Hiệu ứng chuyển đổi */
}

.modal-body li:hover {
    background-color: #f1f1f1;
    /* Hiệu ứng hover cho mục */
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

/* From Uiverse.io by escannord */
.radio-input input {
    display: none;
}

.radio-input label {
    --border-color: #a1b0d8;

    border: 1px solid var(--border-color);
    border-radius: 6px;
    min-width: 5rem;
    margin: 1rem;
    padding: 1rem;
    display: flex;
    justify-content: space-between;
    position: relative;
    align-items: center;
}

.radio-input input:checked+label {
    --border-color: #2f64d8;
    border-color: var(--border-color);
    border-width: 2px;
}

.radio-input label:hover {
    --border-color: #2f64d8;
    border-color: var(--border-color);
}

.radio-input {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-direction: column;
}

.circle {
    display: inline-block;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background-color: rgb(189, 187, 207);
    margin-right: 0.5rem;
    position: relative;
}

.radio-input input:checked+label span.circle::before {
    content: "";
    display: inline;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background-color: #2f64d8;
    width: 15px;
    height: 15px;
    border-radius: 50%;
}

.text {
    display: flex;
    align-items: center;
}

.price {
    display: flex;
    flex-direction: column;
    text-align: right;
    font-weight: bold;
}

.small {
    font-size: 10px;
    color: rgb(136, 138, 139);
    font-weight: 100;
}

.info-pay {
    position: absolute;
    display: inline-block;
    font-size: 11px;
    background-color: rgb(31, 236, 123);
    border-radius: 20px;
    padding: 1px 9px;
    top: 0;
    transform: translateY(-50%);
    right: 5px;
}

/* From Uiverse.io by Cksunandh */
/* Basic reset and styling */
.modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(19, 18, 18, 0.8);
    /* Màu nền mờ */
    z-index: 1040;
    /* Đảm bảo nằm trên các phần tử khác */
    display: flex;
    justify-content: center;
    /* Căn giữa theo chiều ngang */
    align-items: center;
    /* Căn giữa theo chiều dọc */
}

.modal-dialog {
    width: 100%;
    /* Đảm bảo modal không vượt quá chiều rộng màn hình */
    margin: 0 auto;
    max-height: 90%;
    /* Chiều cao tối đa của modal */
    background-color: rgb(252, 252, 252);
    border-radius: 10px;
    overflow-y: auto;
    /* Thêm cuộn dọc khi nội dung vượt quá chiều cao */
}

.modal-content {
    height: auto;
    /* Chiều cao tự động cho nội dung */
}

/* Tooltip container */
.tooltip-container {
    position: fixed;
    /* Đổi từ relative sang fixed */
    bottom: 20px;
    /* Khoảng cách từ đáy màn hình */
    right: 20px;
    /* Khoảng cách từ bên phải màn hình */
    display: inline-block;
    z-index: 120;
    margin: 0;
    /* Đặt margin thành 0 để không có khoảng cách không mong muốn */
}

/* Icon styling */
.icon {
    width: 150px;
    height: 50px;
    display: flex;
    justify-content: center;
    align-items: center;
    cursor: pointer;
    transition:
        transform 0.3s ease,
        filter 0.3s ease;
}

.icon-text {
    margin-top: 5px;
    /* Khoảng cách giữa biểu tượng và chữ */
    font-size: 14px;
    /* Kích thước chữ */
    color: #27c5f1;
    /* Màu chữ */
}

/* SVG Animation: Rotate and scale effect */
.icon svg {
    transition: transform 0.5s ease-in-out;
}

.icon:hover svg {
    transform: rotate(360deg) scale(1.2);
}

/* Tooltip styling */
.tooltip {
    visibility: hidden;
    width: 200px;
    background-color: #333;
    color: #fff;
    text-align: center;
    border-radius: 5px;
    padding: 10px;
    position: absolute;
    bottom: 125%;
    /* Position above the icon */
    left: 50%;
    margin-left: -100px;
    /* Center the tooltip */
    opacity: 0;
    transition:
        opacity 0.5s,
        transform 0.5s;
    transform: translateY(10px);
}

/* Tooltip Arrow */
.tooltip::after {
    content: "";
    position: absolute;
    top: 100%;
    left: 50%;
    margin-left: -5px;
    border-width: 5px;
    border-style: solid;
    border-color: #333 transparent transparent transparent;
}

/* Show tooltip on hover */
.tooltip-container:hover .tooltip {
    visibility: visible;
    opacity: 1;
    transform: translateY(0);
}

@keyframes bounce {

    0%,
    20%,
    50%,
    80%,
    100% {
        transform: translateY(0);
    }

    40% {
        transform: translateY(-30px);
    }

    60% {
        transform: translateY(-15px);
    }
}

.tooltip-container:hover .tooltip {
    visibility: visible;
    opacity: 1;
    transform: translateY(0);
    animation: bounce 0.6s ease;
}

.room-info {
    display: flex;
    justify-content: space-around;
    margin-top: 12px;
}

.room-info span {
    display: flex;
    align-items: center;
    font-size: 14px;
    color: #7d7d7d;
}

.room-info img {
    margin-right: 4px;
    width: 50px;
    /* Kích thước icon */
    height: 50px;
    /* Kích thước icon */
}

.search-box {
    background-color: rgba(255, 255, 255, 0.9);
    border-radius: 10px;
    margin-top: -50px;
}

.hotel-card {
    transition: transform 0.3s;
}

.hotel-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
}
</style>