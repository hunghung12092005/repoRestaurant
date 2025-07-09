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
                            <div class="mb-3" data-bs-toggle="modal" data-bs-target="#guestSelectionModal"
                                style="cursor: pointer;">
                                <label class="form-label text-muted small mb-1">Khách & Phòng:</label>
                                <div
                                    class="card card-body p-2 shadow-sm border-0 d-flex flex-row align-items-center justify-content-between">
                                    <div class="d-flex align-items-center text-primary">
                                        <i class="bi bi-person-fill fs-5 me-2"></i>
                                        <span class="fw-bold">{{ totalAdults }}</span> người lớn
                                        <span class="mx-1 text-muted">/</span>
                                        <span class="fw-bold">{{ totalChildren }}</span> trẻ em
                                        <template v-if="totalChildren > 0">

                                        </template>
                                    </div>
                                    <!-- <div class="d-flex align-items-center text-secondary ms-3">
                                        <i class="bi bi-door-closed-fill fs-5 me-2"></i>
                                        <span class="fw-bold">{{ totalRooms }}</span> phòng
                                    </div> -->
                                </div>
                            </div>

                            <div class="modal fade" id="guestSelectionModal" tabindex="-1"
                                aria-labelledby="guestSelectionModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content rounded-4 shadow-lg">
                                        <div class="modal-header  text-white border-bottom-0 pb-3 rounded-top-4">
                                            <h5 class="modal-title fs-5" id="guestSelectionModalLabel">
                                                <i class="bi bi-person-fill me-2"></i> Chọn Khách
                                            </h5>
                                            <button type="button" class="btn-close btn-close-white"
                                                data-bs-dismiss="modal" aria-label="Đóng"></button>
                                        </div>

                                        <div class="modal-body p-4">
                                            <div v-for="(room, index) in rooms" :key="index"
                                                class="mb-4 p-3 border rounded-3 bg-light-subtle d-flex flex-column gap-3">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <!-- <h6 class="mb-0 text-primary">Phòng {{ index + 1 }}</h6> -->
                                                    <button v-if="rooms.length > 1" type="button"
                                                        class="btn btn-outline-danger btn-sm rounded-pill px-3"
                                                        @click="removeRoomFromModal(index)">
                                                        <i class="bi bi-trash me-1"></i> Xóa Phòng
                                                    </button>
                                                </div>

                                                <div class="d-flex justify-content-between align-items-center">
                                                    <label :for="'adults_' + index"
                                                        class="form-label mb-0 flex-grow-1 me-3 text-dark">Người
                                                        lớn:</label>
                                                    <div class="input-group input-group-sm flex-shrink-0"
                                                        style="width: 120px;">
                                                        <button class="btn btn-outline-secondary" type="button"
                                                            @click="decreaseAdults(index)"
                                                            :disabled="room.adults <= 1">-</button>
                                                        <input type="number" v-model="room.adults"
                                                            class="form-control text-center bg-white"
                                                            :id="'adults_' + index" readonly
                                                            aria-label="Số lượng người lớn" />
                                                        <button class="btn btn-outline-secondary" type="button"
                                                            @click="increaseAdults(index)">+</button>
                                                    </div>
                                                </div>

                                                <div class="d-flex justify-content-between align-items-center">
                                                    <label :for="'children_' + index"
                                                        class="form-label mb-0 flex-grow-1 me-3 text-dark">Trẻ
                                                        em:</label>
                                                    <div class="input-group input-group-sm flex-shrink-0"
                                                        style="width: 120px;">
                                                        <button class="btn btn-outline-secondary" type="button"
                                                            @click="decreaseChildren(index)"
                                                            :disabled="room.children <= 0">-</button>
                                                        <input type="number" v-model="room.children"
                                                            class="form-control text-center bg-white"
                                                            :id="'children_' + index" readonly
                                                            aria-label="Số lượng trẻ em" />
                                                        <button class="btn btn-outline-secondary" type="button"
                                                            @click="increaseChildren(index)">+</button>
                                                    </div>
                                                </div>
                                            </div>


                                        </div>

                                        <div class="modal-footer justify-content-between border-top-0 pt-0 pb-3">
                                            <button type="button" class="btn btn-secondary rounded-pill px-4"
                                                data-bs-dismiss="modal" @click="delSelection">Hủy</button>
                                            <button type="button" class="btn btn-primary rounded-pill px-4"
                                                @click="confirmSelection">Xác Nhận Lựa
                                                Chọn</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
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
        <section class="container my-5 py-lg-4">
            <h2 class="text-center text-dark fw-bold mb-4 display-5">
                Lựa chọn hạng phòng
            </h2>
            <div class="row g-5">
                <div class="col-lg-8">
                    <div class="row g-4">
                        <div class="col-12" v-for="hotel in hotels" :key="hotel.id">
                            <div class="card hotel-room-card mb-3 shadow-lg border-0 rounded-4 overflow-hidden">
                                <div class="row g-0 flex-md-nowrap">
                                    <div class="col-12 col-md-5"> <!-- Chiều cao 100% -->
                                        <iframe width="100%" height="100%"
                                            src="https://www.youtube.com/embed/frG7fz6umT8?si=cSyPvHyfuiOUHUnH"
                                            title="YouTube video player" frameborder="0"
                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                            referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                                    </div>

                                    <div class="col-12 col-md-7">
                                        <div class="card-body d-flex flex-column h-100 p-4">
                                            <div class="d-flex align-items-center mb-3">
                                                <span class="badge bg-primary me-2 fs-6 px-3 py-2 shadow-sm">
                                                    {{ hotel.name }} <i class="bi bi-star-fill align-middle ms-1"></i>
                                                </span>
                                                <span class="text-charcoal fs-6">Suc chua: {{ hotel.max_occupancy }}
                                                    nguoi lon -
                                                    2 child </span>
                                            </div>
                                            <div class="room-features mb-3 text-muted-dark small">
                                                <p class="mb-1"><i
                                                        class="bi bi-arrows-fullscreen me-2 text-gold"></i>Kích
                                                    thước: {{ hotel.m2 }} m²</p>
                                                <p class="mb-1"><i class="bi bi-eye me-2 text-gold"></i>View: Quang cảnh
                                                    thành
                                                    phố</p>
                                                <p class="mb-0"><i class="bi bi-aspect-ratio me-2 text-gold"></i>Giường:
                                                    {{
                                                        hotel.bed_count }} Giường</p>
                                            </div>
                                            <p class="card-text text-charcoal mb-3 flex-grow-1">{{
                                                hotel.description.substring(0, 150) }}...</p>
                                            <button
                                                class="btn btn-outline-charcoal flex-fill me-2 rounded-pill px-4 py-2  "
                                                @click="viewHotelDetails(hotel)">
                                                <i class="bi bi-info-circle me-1"></i>Chi Tiết
                                            </button>
                                            <div
                                                class="mt-auto d-flex flex-column flex-sm-row justify-content-between align-items-sm-end pt-3 border-top border-light-subtle">
                                                <div class="price-info mb-3 mb-sm-0">
                                                    <span class="d-block text-muted-subtle small">Chỉ từ</span>
                                                    <p class="fs-3 fw-bold text-gold mb-0">
                                                        {{ formatPrice(hotel.price) }}
                                                        <span class="fs-6 fw-normal text-charcoal-light">/ {{
                                                            hotel.total_days
                                                        }} Đêm / {{ hotel.so_phong }} Phòng</span>
                                                    </p>
                                                </div>
                                                <div class="buttons d-flex w-100 w-sm-auto">

                                                    <button
                                                        class="btn btn-gold flex-fill rounded-pill px-4 py-2 border border-dark"
                                                        @click="addBooking(hotel)">
                                                        <i class="bi bi-bookmark-check me-1"></i>Chọn Phòng
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card booking-sidebar-card sticky-top shadow-lg border-0 rounded-4" style="top: 100px;">
                        <div class="card-body p-4">
                            <h4 class="card-title text-center mb-4 text-gold fw-bold">Thông Tin Đặt Phòng</h4>
                            <hr class="mb-4 border-gold">
                            <h5 class="card-title text-center mb-3 text-charcoal">Phòng Đã Thêm</h5>
                            <hr class="mb-3 border-gold">
                            <div v-if="selectedRooms.length === 0" class="text-center text-muted-subtle py-3">
                                <p class="mb-0 small">Chưa có phòng nào được thêm vào.</p>
                            </div>
                            <div v-else>
                                <transition-group name="list" tag="div">
                                    <div v-for="(room, index) in selectedRooms" :key="index"
                                        class="d-flex align-items-center justify-content-between mb-3 p-3 bg-light-gold rounded-3 shadow-sm">
                                        <div>
                                            <h6 class="mb-1 text-charcoal fw-bold">{{ room.name }}</h6>
                                            <p class="mb-1 text-muted-dark small">{{ room.description.substring(0, 50)
                                            }}...</p>
                                            <p class="mb-0 text-gold fw-bold">
                                                {{ formatPrice(room.price) }} <span class="small text-charcoal-light">/
                                                    {{
                                                        room.total_days }} Đêm / {{ room.so_phong }} Phòng</span>
                                            </p>
                                        </div>
                                        <button @click="removeRoom(index)"
                                            class="btn btn-outline-danger btn-sm rounded-circle ms-3" title="Xóa phòng">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </div>
                                </transition-group>
                            </div>

                            <div class="d-flex justify-content-between mt-4 pt-3 border-top border-gold-light">
                                <strong class="text-charcoal">{{ totalAdults }} Người lớn, {{ totalChildren }} Trẻ
                                    em</strong>
                                <!-- <strong class="text-charcoal">{{ totalRooms }} Phòng</strong> -->

                            </div>
                            <div class="mt-3 text-gold fw-bold">
                                Tổng Giá(Tạm Tính): {{ formatPrice(totalPrice) }}
                            </div>
                            <div class="text-center mt-4">
                                <button @click="openPopupshowModalBooking"
                                    class="btn btn-gold w-100 py-3 rounded-pill fw-bold fs-5">
                                    Tiến Hành Thanh Toán <i class="bi bi-arrow-right ms-2"></i>
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
        <!-- Booking Modal -->
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
                                            class="text-muted small">(Bắt
                                            buộc)</span></label>
                                    <input type="text" class="form-control" id="fullName" v-model="fullName" required>
                                    <small class="form-text text-muted">Nhập họ và tên đầy đủ.</small>
                                </div>
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Số điện thoại <span
                                            class="text-muted small">(Bắt
                                            buộc)</span></label>
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
                                        chung
                                        của khách sạn</label>
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

                                    <!-- Displaying selected room details -->
                                    <div class="room-details container my-5 py-3">
                                        <h2 class="text-center text-uppercase fw-bold mb-5 position-relative text-dark">
                                            <span
                                                class="d-inline-block pb-3 border-bottom border-4 border-primary">Thông
                                                Tin
                                                Đặt Phòng</span>
                                            <span class="position-absolute start-50 translate-middle-x mt-2"
                                                style="width: 70px; height: 4px; background-color: #ffc107; border-radius: 50%;"></span>
                                        </h2>

                                        <div class="row g-4 justify-content-center">
                                            <div v-for="(room, index) in selectedRooms" :key="index" class="col-12">
                                                <div
                                                    class="card shadow-lg border-0 rounded-4 overflow-hidden animate__animated animate__fadeInUp">
                                                    <div
                                                        class="card-header bg-gradient-primary text-white py-3 px-4 d-flex justify-content-between align-items-center">
                                                        <h4 class="mb-0 fw-bold">Phòng {{ index + 1 }}: <span
                                                                class="text-white-50">{{ room.name }}</span></h4>
                                                        <span class="h4 mb-0 fw-bold text-white">{{
                                                            formatPrice(room.price)
                                                            }}</span>
                                                    </div>
                                                    <div class="card-body p-4">
                                                        <p class="fw-semibold text-secondary mb-3 border-bottom pb-2">
                                                            Các Dịch
                                                            Vụ Đã Chọn:</p>
                                                        <ul class="list-unstyled mb-4 row row-cols-1 row-cols-md-2 g-3">
                                                            <li v-for="(service, serviceIndex) in room.services"
                                                                :key="serviceIndex" class="col">
                                                                <label
                                                                    class="d-flex align-items-center bg-light p-3 rounded-3 border border-light-subtle shadow-sm service-item-hover">
                                                                    <input type="checkbox" v-model="service.selected"
                                                                        @change="updateRoomTotal(room, service.service_id)"
                                                                        class="form-check-input me-3 large-checkbox">
                                                                    <span class="flex-grow-1 text-dark fw-medium">{{
                                                                        service.service_name }}</span>
                                                                    <span class="text-muted small ms-2">{{
                                                                        formatPrice(service.price) }}</span>
                                                                </label>
                                                            </li>
                                                        </ul>
                                                        <div class="text-end pt-3 border-top">
                                                            <p class="mb-0 fw-bold text-dark">
                                                                Tổng Chi Phí Dịch Vụ Phòng: <br>
                                                                <span class="h5 text-info fw-bolder">{{
                                                                    formatPrice(room.totalServiceCost) }}</span>
                                                            </p>
                                                            <p class="mb-0 fw-bold text-dark">
                                                                Tổng Chi Phí Phòng: <br>
                                                                <span class="h5 text-info fw-bolder">{{
                                                                    formatPrice(calculateRoomTotal(room))
                                                                    }}</span>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div
                                            class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mt-5 p-4 bg-dark text-white rounded-4 shadow-lg animate__animated animate__fadeInUp">
                                            <h3 class="mb-2 mb-md-0 fw-bold text-uppercase">Tổng Cộng Thanh Toán:</h3>
                                            <p class="h1 mb-0 text-warning fw-bolder">{{
                                                formatPrice(totalCostForAllRooms) }}
                                            </p>
                                        </div>
                                    </div>
                                    <!-- <div class="total">
                                        <p>PRICE:</p>
                                        <p>{{ formatPrice(totalPrice) }} </p>
                                    </div> -->
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
                                                <span class="info-pay">{{
                                                    formatPrice(totalCostForAllRooms)
                                                    }}</span>
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
                        <button type="submit" class="btn btn-primary" @click="confirmBooking">Xác nhận Đặt
                            phòng</button>
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
                    <!-- <strong>Thông báo!</strong> {{ message }} -->
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
const phoneNumber = ref('325697601');
const fullName = ref('hunghunghung');
const orderNotes = ref('');
const createAccount = ref('true');
const paymentMethod = ref(''); // Phương thức thanh toán

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
};
const delSelection = () => {
    // Xóa tất cả các phòng đã chọn
    rooms.value = [{ adults: 1, children: 0 }]; // Reset lại danh sách phòng
    showModal.value = false; // Đóng modal
    //console.log("Đã xóa tất cả các phòng đã chọn.");
};
//addBooking
const addBooking = (hotel) => {
    // Kiểm tra xem số phòng đã chọn có nhỏ hơn số tối đa không
    if (selectedRooms.value.length < 5) {
        // Tạo dữ liệu phòng mới từ đối tượng hotel
        const roomData = {
            ...hotel, // Sao chép tất cả các thuộc tính từ đối tượng hotel
            total_days: hotel.total_days || 1, // Thiết lập mặc định cho total_days nếu không có
            services: hotel.services.map(service => ({
                ...service,
                selected: false // Thêm thuộc tính selected vào mỗi dịch vụ
            })),
            serviceChoose: [], // Khởi tạo serviceChoose là mảng rỗng
            totalServiceCost: 0 // Khởi tạo totalServiceCost là 0
        };

        // Thêm phòng vào danh sách
        selectedRooms.value.push(roomData);

        // Cập nhật tổng số phòng
        selectedRooms.totalRooms = selectedRooms.value.length;

        //console.log("selectedRooms.value:", selectedRooms.value);
        //console.log("Tổng số phòng đã chọn:", selectedRooms.totalRooms);
    } else {
        alert(`Bạn chỉ có thể chọn tối đa 5 phòng.`);
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
            images: [
                'https://img.lottehotel.com/cms/asset/2025/07/01/29403/438-2-1920-roo-LTHA.webp',
                'https://img.lottehotel.com/cms/asset/2025/06/20/29060/405-2-1920-roo-LTHA.webp',
                'link_to_image_3.jpg'
            ],
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
//lấy giá phòng
const getRoomPrices = async () => {
    selectedRooms.value = []; // Reset danh sách phòng đã chọn
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
            price: room.price +  room.totalServiceCost,
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
        // Gửi yêu cầu thanh toán đến API
        const response = await axiosWithoutHeaderPayos.post(`${apiUrl}/api/payos/checkout`, {
            //amount: 2000, // Tổng giá trị
             amount: selectedRooms.totalPrice, 
            items: payosItems // Danh sách các mặt hàng
        });

        // Xử lý phản hồi từ API
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
        confirmBooking();
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
        //confirmBooking.value = true; // Đặt trạng thái đơn hàng đã được xác nhận
        // Thực hiện hành động tiếp theo
        router.push('/thanksBooking'); // Ví dụ: về trang chủ


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
    getRoomTypes();
    getRoomPrices();
    calculateTotalDays();
});
</script>

<style scoped>
/* General Card Styling */
.booking-sidebar-card {
    position: relative;
    /* Hoặc absolute nếu cần */
    z-index: 1;
    /* Thay đổi giá trị nếu cần */
    /* Thêm margin-top nếu cần tạo khoảng cách với phần tử phía trên */
}

.hotel-room-card {
    border: none;
    /* Remove default Bootstrap border */
    border-radius: 12px;
    /* More rounded corners */
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    /* Softer, more pronounced shadow */
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    /* Smooth hover effect */
}

.hotel-room-card:hover {
    transform: translateY(-5px);
    /* Slight lift on hover */
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
    /* Enhanced shadow on hover */
}

/* Image Styling */
.hotel-room-card .room-image-wrapper {
    height: 250px;
    /* Consistent height for images */
    overflow: hidden;
    position: relative;
    /* For potential overlays */
}

.hotel-room-card .room-image-wrapper img {
    object-fit: cover;
    /* Ensures image fills space without distortion */
    width: 100%;
    height: 100%;
    transition: transform 0.5s ease;
    /* Smooth zoom effect on hover */
}

.hotel-room-card:hover .room-image-wrapper img {
    transform: scale(1.05);
    /* Gentle zoom on hover */
}

/* Card Body & Text */
.hotel-room-card .card-body {
    padding: 1.5rem;
    /* More generous padding */
    display: flex;
    flex-direction: column;
}

.hotel-room-card .card-title {
    font-size: 1.6rem;
    /* Slightly larger, more prominent title */
    color: #333;
    /* Darker, more luxurious text color */
    margin-bottom: 0.5rem;
    line-height: 1.2;
}

/* Rating Badge */
.hotel-room-card .badge {
    font-size: 0.95rem;
    /* Slightly larger badge text */
    font-weight: 600;
    padding: 0.5em 1em;
    /* More padding for a bolder look */
}

.hotel-room-card .badge .bi-star-fill {
    color: #fff;
    /* Ensure star is white on warning background */
}

/* Room Features Section */
.hotel-room-card .room-features p {
    font-size: 0.95rem;
    color: #666;
    /* Softer text color for details */
    margin-bottom: 0.3rem;
    /* Tighter line spacing */
}

.hotel-room-card .room-features i {
    color: #888;
    /* Icon color */
    width: 20px;
    /* Align icons */
    text-align: center;
}

/* Description Text */
.hotel-room-card .card-text.text-secondary {
    font-size: 0.95rem;
    line-height: 1.6;
    color: #555 !important;
    /* Ensure secondary text color is consistent */
}

/* Price Information */
.hotel-room-card .price-info {
    line-height: 1.2;
}

.hotel-room-card .price-info .text-muted.small {
    font-size: 0.85rem;
    margin-bottom: 0.2rem;
}

.hotel-room-card .price-info .fs-4 {
    font-size: 2rem !important;
    /* Larger price to stand out */
    font-weight: 700;
    /* Bolder price */
    color: #A07F5E;
    /* A sophisticated brown/gold for primary elements */
}

.hotel-room-card .price-info .fs-6 {
    color: #777 !important;
    /* More subdued for "per night" info */
}

/* Buttons */
.hotel-room-card .btn {
    font-size: 0.95rem;
    font-weight: 600;
    padding: 0.65rem 1.5rem;
    /* More substantial buttons */
    border-radius: 50px;
    /* Fully rounded buttons */
    transition: all 0.3s ease;
}

.hotel-room-card .btn-outline-dark {
    border-color: #ccc;
    color: #555;
}

.hotel-room-card .btn-outline-dark:hover {
    background-color: #f8f9fa;
    color: #333;
    border-color: #999;
}

.hotel-room-card .btn-primary {
    background-color: #A07F5E;
    /* Sophisticated primary color */
    border-color: #A07F5E;
    color: white;
}

.hotel-room-card .btn-primary:hover {
    background-color: #8C6A4C;
    /* Darker on hover */
    border-color: #8C6A4C;
}

.hotel-room-card .btn i {
    font-size: 1rem;
    /* Adjust icon size in buttons */
}

/* Horizontal line separator */
.hotel-room-card .card-body .border-top {
    border-color: #eee !important;
    /* Lighter border for separation */
}

.hotel-card {
    transition: transform 0.2s;
}

.hotel-card:hover {
    transform: scale(1.02);
}

.room-info span {
    margin-right: 15px;
}

.card-body {
    background-color: #f9f9f9;
}

.card-footer {
    background-color: #ffffff;
    border-top: 1px solid #eaeaea;
}

.card-title {
    font-weight: bold;
}

.text-danger {
    font-size: 1.2em;
}

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