<template>
    <loading v-if="isLoading"></loading>
    <div class="bg-light">

        <!-- Hero Section -->
        <header class="hero-section d-flex align-items-center position-relative">
            <div class="position-absolute top-0 start-0 w-100 h-100"
                style="background: linear-gradient(to top, rgba(0, 0, 0, 0.6) 20%, rgba(0, 0, 0, 0.2) 100%);"></div>
            <div class="container text-center position-relative">
                <h1 class="display-3 fw-bold text-white">Booking Rooms</h1>
                <p class="lead text-white-50">Hồ Xuân Hương Hotel - Trải nghiệm đẳng cấp, dịch vụ tận tâm</p>
            </div>
        </header>

        <Popup v-if="showPopup" :isVisible="showPopup" @close="showPopup = false"></Popup>

        <!-- Search Box -->
        <section class="container" style="margin-top: -80px; position: relative; z-index: 10;">
            <div class="search-box p-4 p-lg-5 shadow-lg rounded-4 bg-white">
                <form @submit.prevent="getRoomPrices">
                    <div class="row g-3 g-lg-4 align-items-end">
                        <!-- Check-in -->
                        <div class="col-md-6 col-lg-3">
                            <label for="checkIn" class="form-label fw-semibold mb-2">Ngày Nhận Phòng</label>
                            <div class="input-group shadow-sm rounded-pill overflow-hidden">
                                <span class="input-group-text bg-white border-0 ps-4 text-primary"><i
                                        class="bi bi-calendar-check"></i></span>
                                <input type="date" class="form-control border-0 py-2" v-model="checkin"
                                    :min="minCheckin" @change="validateDates" id="checkIn" />
                            </div>
                        </div>
                        <!-- Check-out -->
                        <div class="col-md-6 col-lg-3">
                            <label for="checkOut" class="form-label fw-semibold mb-2">Ngày Trả Phòng</label>
                            <div class="input-group shadow-sm rounded-pill overflow-hidden">
                                <span class="input-group-text bg-white border-0 ps-4 text-primary"><i
                                        class="bi bi-calendar-x"></i></span>
                                <input type="date" class="form-control border-0 py-2" v-model="checkOut" :min="checkin"
                                    @change="validateDates" id="checkOut" />
                            </div>
                        </div>
                        <!-- Guests & Rooms -->
                        <div class="col-md-6 col-lg-3">
                            <label class="form-label fw-semibold mb-2">Khách & Phòng</label>
                            <div class="input-group shadow-sm rounded-pill overflow-hidden bg-white cursor-pointer"
                                data-bs-toggle="modal" data-bs-target="#guestSelectionModal">
                                <span class="input-group-text bg-white border-0 ps-4 text-primary"><i
                                        class="bi bi-people-fill"></i></span>
                                <div
                                    class="form-control border-0 py-2 d-flex justify-content-between align-items-center">
                                    <span>{{ totalAdults }} người lớn, {{ totalChildren }} trẻ em</span>
                                    <i class="bi bi-chevron-down text-muted small"></i>
                                </div>
                            </div>
                        </div>
                        <!-- Search Button -->
                        <div class="col-md-6 col-lg-3">
                            <button type="submit" class="btn btn-primary w-100 py-2 rounded-pill shadow-sm" style="height: 46px;">
                                <i class="bi bi-search me-2"></i>Tìm Kiếm
                            </button>
                        </div>
                    </div>
                    <div v-if="errorMessage"
                        class="alert alert-danger d-flex align-items-center mt-4 rounded-3 shadow-sm mb-0"
                        role="alert">
                        <i class="bi bi-exclamation-triangle-fill flex-shrink-0 me-2"></i>
                        <div>{{ errorMessage }}</div>
                    </div>
                </form>
            </div>
        </section>

        <!-- Guest Selection Modal -->
        <div class="modal fade" id="guestSelectionModal" tabindex="-1" aria-labelledby="guestSelectionModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered my-auto">
                <div class="modal-content rounded-4 shadow-lg border-0">
                    <div class="modal-header bg-primary text-white border-0 rounded-top-4">
                        <h5 class="modal-title fw-bold" id="guestSelectionModalLabel"><i
                                class="bi bi-person-fill-gear me-2"></i>Chọn Số Lượng Khách</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Đóng"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div v-for="(room, index) in rooms" :key="index" class="mb-4 pb-4 border-bottom last-child-no-border">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0 fw-semibold text-primary">Phòng {{ index + 1 }}</h6>
                                <button v-if="rooms.length > 1" type="button"
                                    class="btn btn-outline-danger btn-sm rounded-pill px-3"
                                    @click="removeRoomFromModal(index)">
                                    <i class="bi bi-trash me-1"></i> Xóa
                                </button>
                            </div>
                            <!-- Adults -->
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <label class="form-label mb-0">Người lớn</label>
                                <div class="d-flex align-items-center gap-3">
                                    <button class="btn btn-outline-secondary rounded-circle p-0" type="button"
                                        @click="decreaseAdults(index)" :disabled="room.adults <= 1"
                                        style="width: 32px; height: 32px;">−</button>
                                    <span class="fw-bold fs-5"
                                        style="min-width: 30px; text-align: center;">{{ room.adults }}</span>
                                    <button class="btn btn-outline-secondary rounded-circle p-0" type="button"
                                        @click="increaseAdults(index)"
                                        style="width: 32px; height: 32px;">+</button>
                                </div>
                            </div>
                            <!-- Children -->
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="form-label mb-0">Trẻ em</label>
                                <div class="d-flex align-items-center gap-3">
                                    <button class="btn btn-outline-secondary rounded-circle p-0" type="button"
                                        @click="decreaseChildren(index)" :disabled="room.children <= 0"
                                        style="width: 32px; height: 32px;">−</button>
                                    <span class="fw-bold fs-5"
                                        style="min-width: 30px; text-align: center;">{{ room.children }}</span>
                                    <button class="btn btn-outline-secondary rounded-circle p-0" type="button"
                                        @click="increaseChildren(index)"
                                        style="width: 32px; height: 32px;">+</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 d-flex justify-content-end">
                        <button type="button" class="btn btn-primary rounded-pill px-4 py-2" data-bs-dismiss="modal"
                            @click="confirmSelection">
                            <i class="bi bi-check2-circle me-1"></i> Xác Nhận
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Toast Notification -->
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 9999">
            <div id="roomToast" class="toast align-items-center text-bg-success border-0" role="alert"
                aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body fw-semibold">
                        <i class="bi bi-check-circle-fill me-2"></i>Thêm phòng vào giỏ hàng thành công!
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                        aria-label="Đóng"></button>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <section class="container my-5 py-lg-4">
            <div class="row g-5">
                <!-- Room List -->
                <div class="col-lg-8">
                    <div class="row g-4">
                        <div v-if="hotels.length === 0 && !isLoading" class="col-12">
                            <div class="text-center p-5 bg-white rounded-4 shadow-sm">
                                <i class="bi bi-search-heart fs-1 text-primary mb-3"></i>
                                <h4 class="fw-bold">Không tìm thấy phòng phù hợp</h4>
                                <p class="text-muted">Vui lòng thử chọn ngày khác hoặc thay đổi số lượng khách.</p>
                            </div>
                        </div>
                        <div class="col-12" v-for="hotel in hotels" :key="hotel.id">
                            <div class="card room-card border-0 shadow-sm rounded-4 overflow-hidden">
                                <div class="row g-0">
                                    <div class="col-md-5">
                                        <div class="room-card-image h-100"
                                            :style="{ backgroundImage: 'url(' + (hotel.image || 'https://pistachiohotel.com/UploadFile/Gallery/Overview/a3.jpg') + ')' }">
                                            <span
                                                class="badge bg-danger position-absolute top-0 start-0 m-3 fs-6 rounded-pill px-3 shadow-sm">
                                                <i class="bi bi-door-open-fill me-1"></i> Còn {{ hotel.available_rooms }} phòng
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-md-7 d-flex flex-column">
                                        <div class="card-body p-4">
                                            <h4 class="card-title fw-bold mb-3">{{ hotel.name }}</h4>
                                            <div class="d-flex flex-wrap gap-3 text-muted small mb-3">
                                                <span><i class="bi bi-arrows-fullscreen me-1"></i> {{ hotel.m2 }} m²</span>
                                                <span><i class="bi bi-aspect-ratio me-1"></i> Hướng phố</span>
                                                <span><i class="bi bi-person-arms-up me-1"></i> {{ hotel.max_occupancy
                                                    }} khách</span>
                                            </div>
                                            <p class="card-text text-secondary mb-3 description">
                                                {{ hotel.description }}
                                            </p>
                                            <p v-if="hotel.surcharges > 0" class="text-danger small mb-3">
                                                <i class="bi bi-exclamation-triangle-fill me-1"></i> Phụ thu ngày lễ: {{
                                                    formatPrice(hotel.surcharges) }}
                                            </p>
                                        </div>
                                        <div
                                            class="card-footer bg-white mt-auto p-4 border-top d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3">
                                            <div class="text-center text-sm-start">
                                                <div class="fs-4 fw-bolder text-primary">
                                                    {{ formatPrice(hotel.price) }}
                                                </div>
                                                <small class="text-muted">/ {{ hotel.total_days }} đêm / {{
                                                    hotel.so_phong }} phòng</small>
                                            </div>
                                            <div class="d-flex gap-2 w-100 w-sm-auto">
                                                <button
                                                    class="btn btn-outline-secondary rounded-pill flex-fill"
                                                    @click="viewHotelDetails(hotel)">
                                                    <i class="bi bi-info-circle"></i> Chi tiết
                                                </button>
                                                <button class="btn btn-primary rounded-pill flex-fill"
                                                    @click="addBooking(hotel)">
                                                    <i class="bi bi-plus-lg"></i> Thêm
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Booking Sidebar (Desktop) -->
                <div class="col-lg-4 d-none d-lg-block">
                    <div class="booking-sidebar sticky-top shadow-sm rounded-4 border-0" style="top: 100px;">
                        <div class="card-body p-4">
                            <h4 class="card-title text-center mb-4 fw-bold text-primary">Đơn Hàng Của Bạn</h4>
                            <hr class="mb-4">
                            <div v-if="selectedRooms.length === 0" class="text-center text-muted py-5">
                                <i class="bi bi-cart3 fs-1 mb-3"></i>
                                <p class="mb-0">Giỏ hàng của bạn đang trống</p>
                            </div>
                            <div v-else class="booking-sidebar-items">
                                <transition-group name="list" tag="div">
                                    <div v-for="(room, index) in selectedRooms" :key="index"
                                        class="selected-room-item d-flex align-items-start p-3 mb-3 rounded-3">
                                        <div class="flex-grow-1 me-3">
                                            <h6 class="mb-1 fw-bold">{{ room.name }}</h6>
                                            <p class="mb-0 text-primary fw-semibold">
                                                {{ formatPrice(room.price) }} <span class="small text-muted fw-normal">/ {{
                                                    room.total_days }} Đêm</span>
                                            </p>
                                        </div>
                                        <button @click="removeRoom(index)"
                                            class="btn btn-sm btn-outline-danger rounded-circle p-0"
                                            style="width: 28px; height: 28px;" title="Xóa phòng">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </div>
                                </transition-group>
                            </div>

                            <div v-if="selectedRooms.length > 0">
                                <hr class="my-4">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Khách:</span>
                                    <strong class="text-dark">{{ totalAdults }} người lớn, {{ totalChildren }} trẻ em</strong>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <strong class="text-dark fs-5">Tổng cộng:</strong>
                                    <strong class="text-primary fs-4">{{ formatPrice(totalPrice) }}</strong>
                                </div>
                                <button @click="openPopupshowModalBooking" class="btn btn-primary btn-lg w-100 mt-4 rounded-pill">
                                    Tiến Hành Đặt Phòng <i class="bi bi-arrow-right ms-2"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Fixed Footer for Mobile -->
                <div v-if="selectedRooms.length > 0"
                    class="d-lg-none fixed-bottom bg-white border-top shadow-lg py-2 px-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-bold">{{ selectedRooms.length }} loại phòng</div>
                            <div class="text-primary fw-bolder fs-5">{{ formatPrice(totalPrice) }}</div>
                        </div>
                        <button @click="openPopupshowModalBooking" class="btn btn-primary rounded-pill px-4 py-2">
                            Đặt phòng <i class="bi bi-arrow-right ms-1"></i>
                        </button>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Booking Modal -->
        <div v-show="showModalBooking" class="modal-backdrop" @click="closeModal">
            <div class="modal-dialog modal-lg" @click.stop>
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
                                </div>
                                <div class="mb-3">
                                    <label for="orderNotes" class="form-label">Ghi chú Đặt hàng (Tùy chọn)</label>
                                    <textarea class="form-control" id="orderNotes" v-model="orderNotes"
                                        rows="3"></textarea>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="createAccount"
                                        v-model="createAccount" value="true">
                                    <label class="form-check-label" for="createAccount">Tôi hoàn toàn đồng ý với quy tắc
                                        chung của khách sạn</label>
                                </div>
                            </div>
                            <div class="col-md-5 payment-section">
                                <div class="receipt">
                                    <p class="shop-name">Bill Market</p>
                                    <p class="info">
                                        Ho Xuan Huong Hotel, 23/89<br />
                                        Thanh Mai, Quang Thanh<br />
                                        Date: {{ currentDateTime }}<br />
                                    </p>
                                    <div class="total">
                                        <p>Số Ngày Ở:</p>
                                        <p>{{ totalday }} Ngày</p>
                                    </div>
                                    <div class="total">
                                        <p>Số Phòng Book:</p>
                                        <p>{{ selectedRooms.length }} Phòng</p>
                                    </div>
                                    <div v-for="(room, index) in selectedRooms" :key="index">
                                        <div class="total">
                                            <p>Phòng {{ index + 1 }} :
                                                <span class="text-secondary fw-normal">{{ room.name
                                                }}</span>
                                            </p>
                                            <p>{{
                                                formatPrice(room.price) }}</p>
                                        </div>
                                    </div>
                                    <div class="container my-5 py-4">
                                        <div
                                            class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mt-4 py-4 px-4 border border-info rounded-3 shadow-lg">
                                            <h6 class="mb-2 mb-md-0 fw-bold text-uppercase text-dark">Tổng Cộng Thanh
                                                Toán:</h6>
                                            <p class="h4 mb-0 fw-bolder text-primary">{{
                                                formatPrice(totalCostForAllRooms) }}</p>
                                        </div>
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
                                                <span class="info-pay">{{ formatPrice(totalCostForAllRooms) }}</span>
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
                    </div>
                </div>
            </div>
        </div>
        <!-- popup smsotp -->
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
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                </p>
            </div>
        </div>
        <div id="recaptcha-container"></div> <!-- ReCAPTCHA -->
        
        <!-- Modal for hotel details -->
        <div v-if="showModal" class="modal fade show mt-5"
            style="display: block; background-color: rgba(0, 0, 0, 0.7);">
            <div class="modal-dialog modal-dialog-centered modal-xl">
                <div class="modal-content shadow-lg border-0 rounded-4">
                    <div class="modal-header bg-dark text-white border-0 pb-0 pt-4 px-4 rounded-top-4">
                        <h5 class="modal-title fw-bold fs-3 text-uppercase">Chi Tiết Hạng Phòng</h5>
                        <button type="button" class="btn-close btn-close-white" @click="closeModal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-0">
                        <div class="position-relative">
                            <div id="hotelImageCarousel" class="carousel slide" data-bs-ride="carousel"
                                data-bs-interval="5000">
                                <div class="carousel-inner rounded-top-4 rounded-md-top-0">
                                    <div class="carousel-item active">
                                        <img src="https://pistachiohotel.com/UploadFile/Gallery/Overview/a3.jpg"
                                            class="d-block w-100 object-fit-cover rounded-top-4" alt="Phòng Khách Sạn 1"
                                            style="min-height: 450px; max-height: 600px;">
                                    </div>
                                    <div class="carousel-item">
                                        <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRMFteLbo7EdtFP32wDKvTJxML1CEt9pCdo4ByeKxCZnFX9cBf0ifdq6eCRQZBW_3feWRI&usqp=CAU"
                                            class="d-block w-100 object-fit-cover rounded-top-4" alt="Phòng Khách Sạn 2"
                                            style="min-height: 450px; max-height: 600px;">
                                    </div>
                                    <div class="carousel-item">
                                        <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR7roFS7C9CH67wV7w3WdxLZ8CtW8nuvA2tf3kNJzn6YR5Xczj8AktzixNewUwV_SASOz8&usqp=CAU"
                                            class="d-block w-100 object-fit-cover rounded-top-4" alt="Phòng Khách Sạn 3"
                                            style="min-height: 450px; max-height: 600px;">
                                    </div>

                                </div>
                                <button class="carousel-control-prev" type="button" data-bs-target="#hotelImageCarousel"
                                    data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Previous</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#hotelImageCarousel"
                                    data-bs-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Next</span>
                                </button>
                                <div class="position-absolute w-100 h-100 d-flex align-items-end p-4"
                                    style="background: linear-gradient(to top, rgba(0, 0, 0, 0.75) 0%, rgba(0, 0, 0, 0) 100%); top: 0; left: 0;">
                                    <h4 class="text-white fw-bolder mb-0 display-4">{{ selectedHotel.name }}</h4>
                                </div>
                            </div>
                        </div>

                        <div class="p-5 bg-light rounded-bottom-4">
                            <div class="row">
                                <div class="col-lg-6 mb-4 mb-lg-0">
                                    <h5 class="text-dark fw-bold mb-4 border-bottom pb-2">Thông Tin Chung</h5>
                                    <p class="mb-2 fs-6"><strong class="text-secondary">Giá 1 đêm/phòng:</strong> <span
                                            class="fw-bold text-warning">{{ formatPrice(selectedHotel.price_per_night)
                                            }}</span>
                                    </p>
                                    <p class="mb-2 fs-6"><strong class="text-secondary">Phụ Thu (dịp lễ/đặc
                                            biệt):</strong>
                                        <span class="text-danger fw-bold">{{ formatPrice(selectedHotel.surcharges)
                                        }}</span>
                                    </p>
                                    <p class="mb-2 fs-6"><strong class="text-secondary">Số Phòng:</strong> <span
                                            class="fw-bold text-dark">{{ selectedHotel.so_phong }}</span></p>
                                    <p class="mb-2 fs-6"><strong class="text-secondary">Số đêm:</strong> <span
                                            class="fw-bold text-dark">{{ selectedHotel.total_days }}</span></p>
                                    <p class="mb-4 pt-3"><strong class="text-dark fs-5">Tổng tiền:</strong> <span
                                            class="fw-bolder text-warning fs-3">{{ formatPrice(selectedHotel.price)
                                            }}</span>
                                    </p>

                                    <h5 class="text-dark fw-bold mb-3 border-bottom pb-2">Mô Tả Hạng Phòng</h5>
                                    <p class="text-muted mb-0">{{ selectedHotel.description }}</p>
                                </div>
                                <div class="col-lg-6">
                                    <h5 class="text-dark fw-bold mb-3 border-bottom pb-2">Tiện Nghi Đi Kèm:</h5>
                                    <ul class="list-unstyled mb-4 small">
                                        <li v-if="selectedHotel.amenities.length === 0" class="text-muted fst-italic">
                                            Không có
                                            tiện nghi đi kèm cho hạng phòng này</li>
                                        <li v-for="amenity in selectedHotel.amenities" :key="amenity.amenity_id"
                                            class="mb-2 d-flex align-items-start">
                                            <i class="bi bi-check-lg me-2 text-success"></i> <span
                                                class="flex-grow-1">{{
                                                    amenity.amenity_name }}: <span class="text-muted">{{ amenity.description
                                                }}</span></span>
                                        </li>
                                    </ul>
                                    <h5 class="text-dark fw-bold mb-3 border-bottom pb-2">Dịch Vụ Đặc Biệt:</h5>
                                    <ul class="list-unstyled small">
                                        <li v-if="selectedHotel.services.length === 0" class="text-muted fst-italic">
                                            Không có
                                            dịch vụ đi kèm cho hạng phòng này</li>
                                        <li v-for="service in selectedHotel.services" :key="service.service_id"
                                            class="mb-2 d-flex align-items-start">
                                            <i class="bi bi-star-fill me-2 text-info"></i> <span class="flex-grow-1">{{
                                                service.service_name }}: <span class="fw-bold text-dark">{{
                                                    formatPrice(service.price) }}</span></span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0 d-flex justify-content-center p-4 rounded-bottom-4">
                        <button type="button" class="btn btn-outline-dark rounded-pill px-5 py-2 fw-medium"
                            @click="closeModal">Đóng</button>
                        <button type="button" class="btn btn-warning text-dark rounded-pill px-5 py-2 fw-bold ms-3">Đặt
                            Ngay
                            Hạng Phòng Này</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<script setup>
// PHẦN SCRIPT GIỮ NGUYÊN HOÀN TOÀN
import { ref, onMounted, computed, inject } from 'vue';
import axios from 'axios';
// import Modal from '../Model.vue';
import loading from '../loading.vue';
import Footer from '../Footer.vue';
import Popup from '../Popup.vue';
import { auth, RecaptchaVerifier, PhoneAuthProvider } from '../ShopOnline/firebase';
import { signInWithPhoneNumber, signInWithCredential } from 'firebase/auth';
import { useRouter } from 'vue-router'; // Dòng này cực kỳ quan trọng!

// Khởi tạo router instance
const apiUrl = inject('apiUrl'); // Lấy apiUrl từ inject
var router = useRouter();//su dung router để điều hướng
const selectedRooms = ref([]); // Danh sách các phòng đã chọn
const showPopup = ref(false);//popup mới vào trang
const hotels = ref([]);
const isLoading = ref(false);
const rooms = ref([{ adults: 1, children: 0 }]); // Mỗi phòng có số người lớn và trẻ emconst MAX_ROOMS = 5; // Giới hạn số lượng phòng tối đa
const MAX_ADULTS_PER_ROOM = 15; // Giới hạn số người lớn tối đa trong mỗi phòng
const MAX_CHILD_PER_ROOM = 10; // Giới hạn số tre tối đa trong mỗi phòng
const MAX_ROOMS = 5; // Giới hạn số lượng phòng tối đa
const totalAdults = computed(() => rooms.value.reduce((sum, room) => sum + room.adults, 0));
// Tính tổng số trẻ em
const totalChildren = computed(() => {
    return rooms.value.reduce((sum, room) => sum + room.children, 0);
});
// Tính tổng số price
const totalPrice = computed(() => {
    return selectedRooms.value.reduce((total, room) => total + room.price, 0);
});

//const totalRooms = computed(() => rooms.value.length);
const checkin = ref();
const checkOut = ref();
const bookrooms = ref();
const totalday = ref(0);
// Tính số ngày giữa checkin và checkout
const calculateTotalDays = () => {
    if (checkin.value && checkOut.value) {
        const checkinDate = new Date(checkin.value);
        const checkoutDate = new Date(checkOut.value);

        // Tính số ngày
        totalday.value = (checkoutDate - checkinDate) / (1000 * 60 * 60 * 24);
        // console.log(totalday.value);
        // Chia cho số mili giây trong một ngày
    } else {
        totalday.value = 0; // Nếu không có ngày, gán giá trị mặc định
    }
};

const showModal = ref(false);
const showModalBooking = ref(false);
const selectedHotel = ref(null);
const selectedHotelBooking = ref(null);

const currentDateTime = new Date().toLocaleString();
const phoneNumber = ref('');
const fullName = ref('');
const orderNotes = ref('');
const createAccount = ref('true');
const paymentMethod = ref(''); // Phương thức thanh toán
const orderCode = ref(''); // Mã đơn hàng
//sms
const isFormOTP = ref(true);
const isOtp = ref(false);
const otpInputs = ref();

const showPopUpMain = () => {
    showPopup.value = true;//popup mới vào trang
}
//format price
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
// cho phép người dùng chọn số lượng khách
const increaseAdults = (index) => {
    if (rooms.value[index].adults < MAX_ADULTS_PER_ROOM) {
        rooms.value[index].adults++;
    }
};

const decreaseAdults = (index) => {
    if (rooms.value[index].adults > 1) { // Đảm bảo ít nhất có 1 người lớn
        rooms.value[index].adults--;
    }
};
// Hàm tăng số lượng trẻ em cho phòng
const increaseChildren = (index) => {
    if (rooms.value[index].children < MAX_CHILD_PER_ROOM) {
        rooms.value[index].children++;
    }
    //rooms.value[index].children++;
};

// Hàm giảm số lượng trẻ em cho phòng
const decreaseChildren = (index) => {
    if (rooms.value[index].children > 0) {
        rooms.value[index].children--;
    }
};
const confirmSelection = () => {
    // Kiểm tra xem có ít nhất một phòng được chọn không
    if (rooms.value.length > 0) {
        getRoomPrices();
    } else {
        alert('Vui lòng thêm ít nhất một phòng trước khi xác nhận.');
    }

    showModal.value = false; // Đóng modal
};
const delSelection = () => {
    // Xóa tất cả các phòng đã chọn
    rooms.value = [{ adults: 1, children: 0 }]; // Reset lại danh sách phòng
    //showModal.value = false; // Đóng modal
    //console.log("Đã xóa tất cả các phòng đã chọn.");
};
//addBooking

const addBooking = (hotel) => {
    const maxRooms = hotel.available_rooms || 0;
    const currentRooms = selectedRooms.value.length;

    //console.log(`📦 Đã chọn: ${currentRooms} / Tối đa: ${maxRooms} phòng`);

    if (currentRooms < maxRooms) {
        // toast.success("Đã thêm phòng thành công!");


        const roomData = {
            ...hotel,
            total_days: hotel.total_days || 1,
            services: (hotel.services || []).map(service => ({
                ...service,
                selected: false
            })),
            serviceChoose: [],
            totalServiceCost: 0
        };

        selectedRooms.value.push(roomData);
        selectedRooms.totalRooms = selectedRooms.value.length;
        // Hiển thị toast luôn
        const toastEl = document.getElementById('roomToast');
        if (toastEl) {
            new bootstrap.Toast(toastEl, { delay: 1000 }).show();
        }

        //console.log("Thêm phòng:", roomData);
        //console.log("Tổng số phòng đã chọn:", selectedRooms.totalRooms);
    } else {
        alert(`❗ Bạn chỉ có thể chọn tối đa ${maxRooms} phòng.`);
    }
};

const removeRoom = (index) => {
    selectedRooms.value.splice(index, 1); // Xóa phòng tại chỉ số index
    selectedRooms.totalRooms = selectedRooms.value.length; // Giảm tổng số phòng};
    // console.log(1);

    // Hàm xử lý chọn khách
}
const removeRoomFromModal = (index) => {
    rooms.value.splice(index, 1); // Xóa phòng tại chỉ số index từ danh sách rooms
};
//hien thi popup chon dich vu
const openPopupshowModalBooking = () => {
    if (selectedRooms.value.length === 0) {
        alert('Vui lòng chọn ít nhất một phòng trước khi đặt.');
        return;
    }
    showModalBooking.value = true;
    //console.log(selectedRooms);
    // console.log("totalRooms:", selectedRooms.totalRooms);//lay cai nay for booking_details  
};
const updateRoomTotal = (room, selectedServiceId) => {
    // Kiểm tra và thêm hoặc xóa service id trong serviceChoose
    if (selectedServiceId) {
        const index = room.serviceChoose.indexOf(selectedServiceId);
        if (index === -1) {
            // Nếu chưa có, thêm vào
            room.serviceChoose.push(selectedServiceId);
        } else {
            // Nếu đã có, xóa ra
            room.serviceChoose.splice(index, 1);
        }
    }
    // Tính tổng chi phí dịch vụ
    room.totalServiceCost = room.services.reduce((total, service) => {
        return total + (service.selected ? parseFloat(service.price) : 0);
    }, 0);
    //console.log("Tổng số phòng đã chọn:", selectedRooms.totalRooms);
    //console.log("Cập nhật tổng chi phí dịch vụ cho phòng:", room.name, "Tổng chi phí:", room.totalServiceCost);
    //console.log("Dịch vụ đã chọn:", room.serviceChoose);
    //console.log(selectedRooms.value);
};
//tinh tien dich vu 1 phong
const calculateRoomTotal = (room) => {
    const roomPrice = parseFloat(room.price) || 0; // Giá phòng
    const serviceCost = room.totalServiceCost || 0; // Chi phí dịch vụ
    return roomPrice + serviceCost; // Tổng tiền cho phòng
};
// tinh tien dich vu all room
const totalCostForAllRooms = computed(() => {
    return selectedRooms.value.reduce((total, room) => {
        return total + calculateRoomTotal(room); // Cộng tổng tiền từng phòng
    }, 0);
});
//lấy loại phòng
const getRoomTypes = async () => {
    isLoading.value = true;
    try {
        // Gọi đồng thời 2 API — API check-availability có truyền ngày
        const [roomTypeRes, availabilityRes] = await Promise.all([
            axios.get('http://127.0.0.1:8000/api/room-types/'),
            axios.get('http://127.0.0.1:8000/api/check-availability', {
                params: {
                    check_in_date: checkin.value,
                    check_out_date: checkOut.value
                }
            })
        ]);

        const roomTypes = roomTypeRes.data.data;
        const availabilityData = availabilityRes.data;

        // Tạo map để tra nhanh số phòng trống
        const availabilityMap = {};
        availabilityData.forEach(item => {
            const roomTypeId = parseInt(item.room_type.toString().trim());
            availabilityMap[roomTypeId] = item.available_rooms;
        });

        // Map room types + gán số phòng trống tương ứng
        hotels.value = roomTypes.map(room => {
            const typeId = parseInt(room.type_id);
            return {
                id: typeId,
                name: room.type_name,
                description: room.description,
                bed_count: room.bed_count,
                amenities: room.amenities || [],
                services: room.services || [],
                max_occupancy: room.max_occupancy,
                images: [
                    'https://img.lottehotel.com/cms/asset/2025/07/01/29403/438-2-1920-roo-LTHA.webp',
                    'https://img.lottehotel.com/cms/asset/2025/06/20/29060/405-2-1920-roo-LTHA.webp',
                    'link_to_image_3.jpg'
                ],
                image: "https://i.postimg.cc/d3pNGXPN/7c6764b8-de90-474c-9b98-05019aef3193.png",
                youtube_link: room.youtube_link || "https://www.youtube.com/embed/kXaLkZPlYyo?si=Pw0ywUB6VmhsW5XC",
                price: 0,
                rating: room.rate,
                m2: room.m2,
                available_rooms: availabilityMap[typeId] || 0
            };
        });
        //console.log("Hotels:", hotels.value); // Kiểm tra dữ liệu phòng đã lấy
        // showPopup.value = true;

    } catch (error) {
        console.error("Có lỗi xảy ra khi lấy dữ liệu:", error);
    } finally {
        isLoading.value = false;
    }
};


//lấy giá phòng
const getRoomPrices = async () => {
    selectedRooms.value = []; // Reset danh sách phòng đã chọn
    isLoading.value = true; // Bắt đầu tải dữ liệu
    getRoomTypes(); // Gọi hàm lấy loại phòng
    try {
        // Dữ liệu cần gửi
        const requestData = {
            checkin: checkin.value,
            checkout: checkOut.value,
            bookrooms: bookrooms.value
        };

        // Gọi API lấy giá phòng
        const roomPricesResponse = await axios.post(`${apiUrl}/api/prices/prices_client`, requestData);
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
        calculateTotalDays();//tinh lai ngay
        //totalday.value = hotels[0].total_days; 
        //console.log("Updated Hotels:", hotels.value); // Kiểm tra thông tin phòng đã cập nhật
    } catch (error) {
        console.error("Có lỗi xảy ra khi lấy dữ liệu:", error);
    } finally {
        isLoading.value = false; // Kết thúc tải dữ liệu
    }
}
//boooking

// Xử lý booking
const confirmBooking = async () => {
    // Kiểm tra thông tin bắt buộc
    if (!fullName.value || !phoneNumber.value) {
        alert('Vui lòng nhập đầy đủ họ tên và số điện thoại.');
        return;
    }
    // Kiểm tra xem có ít nhất một phòng đã chọn không
    if (selectedRooms.value.length === 0) {
        alert('Vui lòng chọn ít nhất một phòng trước khi đặt.');
        return;
    }
    selectedRooms.totalPrice = totalCostForAllRooms.value; //gia tong
    // console.log("totalPrice:", selectedRooms.totalPrice); // Log the updated totalPrice
    // console.log("selectedRooms:", selectedRooms.value); // Log the selected rooms
    const roomDetails = computed(() => {
        return selectedRooms.value.map(room => ({
            id: room.id,
            price: room.price,
            serviceChoose: room.serviceChoose,
            totalServiceCost: room.totalServiceCost
        }));
    });// tao mang moi  
    // Khởi tạo bookingDetails
    const bookingDetails = {
        check_in_date: checkin.value,
        check_out_date: checkOut.value,
        total_rooms: selectedRooms.totalRooms,
        total_price: selectedRooms.totalPrice,
        roomDetails: roomDetails.value,
        payment_method: paymentMethod.value,
        orderCode: orderCode.value || '', // Mã đơn hàng nếu có
        booking_type: 'online',
        pricing_type: 'nghitly',
        payment_status: 'pending',
        status: 'pending_confirmation',
        note: orderNotes.value || 'Không có ghi chú',
    };
    //console.log("Booking Details:", JSON.stringify(bookingDetails, null, 2)); // Log booking details as JSON
    //return;
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
        // console.log(localStorage.getItem('BookingAuth'))

        //console.log('Token xác thực:', token);
    } catch (error) {
        console.error('Lỗi khi xác thực:', error);
        alert('Không thể xác thực, vui lòng kiểm tra thông tin!');
        return;
    }
    //dat phong
    const axiosWithoutHeader = axios.create({
        // baseURL: apiUrl, // Đặt base URL nếu cần
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`, // Thêm token vào header
        }
    });
    try {
        isLoading.value = true; // Bắt đầu quá trình gửi dữ liệu
        //console.log('Thông tin đặt phòng:', JSON.stringify(bookingDetails, null, 2));
        // Gửi yêu cầu đặt phòng
        const response = await axiosWithoutHeader.post(`${apiUrl}/api/booking-client`, bookingDetails);
        //console.log('Đặt phòng thành công:', response.data);

    } catch (error) {
        console.error('Lỗi khi gửi thông tin đặt phòng:', error);

        const errorMessage = error.response && error.response.data && error.response.data.error
            ? error.response.data.error
            : error.message || 'Đã xảy ra lỗi không xác định';

        alert(`Lỗi khi gửi thông tin đặt phòng: ${errorMessage}`);
        return;
    } finally {
        isLoading.value = false; // Kết thúc quá trình gửi dữ liệu
        // router.push('/thanksBooking'); // Ví dụ: về trang chủ
    }

    // Gọi hàm gửi OTP SMS
    //await payQr();
};
//payQr
const payQr = async () => {
    isLoading.value = true; // Bắt đầu quá trình tải
    try {
        // Kiểm tra xem có ít nhất một phòng đã chọn không
        selectedRooms.totalPrice = totalCostForAllRooms.value;
        // console.log("selectedRooms.value:", selectedRooms.totalPrice); // lấy giá này 
        //console.log("Booking Details:", selectedRooms.value, null, 2); // Log booking details as JSON

        const payosItems = selectedRooms.value.map((room, index) => ({
            name: `Phòng ${index + 1}`, // Tạo tên phòng dựa trên chỉ số
            price: room.price + room.totalServiceCost,
            // totalServiceCost: room.totalServiceCost,
            quantity: 1 // Đảm bảo quantity là số dương
        }));
        // console.log("roomDetails.value:", payosItems); // Log the room details

        if (selectedRooms.value.length === 0) {
            alert('Vui lòng chọn ít nhất một phòng trước khi đặt.');
            return;
        }

        // Kiểm tra thông tin bắt buộc
        if (!fullName.value || !phoneNumber.value) {
            alert('Vui lòng nhập đầy đủ họ tên và số điện thoại.');
            return;
        }

        // Gọi API thanh toán QR
        paymentMethod.value = 'thanh_toan_qr'; // Đặt phương thức thanh toán là QR
        const axiosWithoutHeaderPayos = axios.create({
            headers: {
                'Content-Type': 'application/json',
                'Authorization': ``,
            }
        });
        //await confirmBooking();
        // Gửi yêu cầu thanh toán đến API
        const response = await axiosWithoutHeaderPayos.post(`${apiUrl}/api/payos/checkout`, {
            //amount: 2000, // Tổng giá trị
            amount: selectedRooms.totalPrice,
            items: payosItems // Danh sách các mặt hàng
        });
        //console.log('Phản hồi từ API thanh toán:', response.data.orderCode);
        orderCode.value = response.data.orderCode;
        //Xử lý phản hồi từ API
        if (response.data && response.data.checkoutUrl) {
            // Chuyển hướng đến link thanh toán
            await confirmBooking();
            window.location.href = response.data.checkoutUrl;
        } else {
            alert('Đã xảy ra lỗi trong quá trình thanh toán.');
        }

    } catch (error) {
        console.error('Lỗi thanh toán:', error.message || error);
        alert(`Lỗi thanh toán: ${error.message || error}`);
    } finally {
        isLoading.value = false; // Kết thúc quá trình tải
    }
}
//sms
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
        alert(`Lỗi gửi mã xác nhận: SDT không hợp lệ hoặc đã được đăng ký trước đó. Vui lòng thử lại.`);
        location.reload();
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
        //confirmBooking.value = true; // Đặt trạng thái đơn hàng đã được xác nhận
        // Thực hiện hành động tiếp theo
        await confirmBooking();
        router.push('/thanksBooking');
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
onMounted(() => {
    //lấy mặc định ngày 
    const today = new Date();
    const tomorrow = new Date();
    tomorrow.setDate(today.getDate() + 1);
    checkin.value = today.toISOString().split('T')[0];
    checkOut.value = tomorrow.toISOString().split('T')[0];
    bookrooms.value = 1;
    showPopup.value = true;
    getRoomPrices();
    calculateTotalDays();
});
</script>

<style scoped>
/* General Style */
.cursor-pointer {
    cursor: pointer;
}

/* Hero Section */
.hero-section {
    background-image: url("https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?ixlib=rb-4.0.3&auto=format&fit=crop&w=1470&q=80");
    background-size: cover;
    background-position: center;
    height: 450px;
    color: white;
}

/* Room Card */
.room-card {
    transition: all 0.3s ease-in-out;
}

.room-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.1) !important;
}

.room-card-image {
    background-size: cover;
    background-position: center;
    min-height: 280px;
    position: relative;
}

.room-card .description {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.6;
    height: calc(1.6em * 3);
}

/* Booking Sidebar */
.booking-sidebar {
    background-color: var(--bs-light);
    border: 1px solid var(--bs-border-color-translucent);
    z-index: 900;
}

.booking-sidebar-items {
    max-height: 40vh;
    overflow-y: auto;
    padding-right: 10px;
    margin-right: -10px;
}

.selected-room-item {
    background-color: var(--bs-white);
    border: 1px solid var(--bs-border-color-translucent);
    transition: background-color 0.2s;
}

.list-enter-active,
.list-leave-active {
    transition: all 0.5s ease;
}

.list-enter-from,
.list-leave-to {
    opacity: 0;
    transform: translateX(30px);
}

/* Guest Selection Modal */
.last-child-no-border:last-child {
    border-bottom: none !important;
    padding-bottom: 0 !important;
    margin-bottom: 0 !important;
}

/* form otp */
.popup-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 2000;
}

.form-container {
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

/* Payment Radio */
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

/* Modal Backdrop */
.modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(19, 18, 18, 0.8);
    z-index: 1040;
    display: flex;
    justify-content: center;
    align-items: center;
}

.modal-dialog {
    width: 100%;
    margin: 0 auto;
    max-height: 90%;
    border-radius: 10px;
    overflow-y: auto;
}
</style>