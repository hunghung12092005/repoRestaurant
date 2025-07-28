<template>
    <loading v-if="isLoading"></loading>
    <div>

        <!-- Hero Section -->
        <header class="hero-section d-flex align-items-center">
            <div class="container text-center">
                <h1 class="display-3 fw-bold " style="margin: 0 auto;">Hồ Xuân Hương Hotel </h1>

            </div>
        </header>
        <Popup v-if="showPopup" :isVisible="showPopup" @close="showPopup = false"></Popup>

        <!-- <div class="tooltip-container">
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
        </div> -->

        <!-- Search Box -->
        <section class="container my-5">
            <div class="search-box p-4 shadow-lg rounded-4 bg-white">
                <form @submit.prevent="getRoomPrices">
                    <div class="row g-4 align-items-end">
                        <div class="col-md-3">
                            <label for="checkIn" class="form-label text-dark fw-semibold">Ngày Nhận Phòng</label>
                            <div class="input-group input-group-lg shadow-sm rounded-pill overflow-hidden">
                                <span class="input-group-text bg-light border-0 ps-4 pe-2"><i
                                        class="bi bi-calendar-event text-primary fs-5"></i></span>
                                <input type="date" class="form-control border-0 py-2" v-model="checkin"
                                    :min="minCheckin" @change="validateDates" id="checkIn"
                                    aria-label="Ngày nhận phòng" />
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label for="checkOut" class="form-label text-dark fw-semibold">Ngày Trả Phòng</label>
                            <div class="input-group input-group-lg shadow-sm rounded-pill overflow-hidden">
                                <span class="input-group-text bg-light border-0 ps-4 pe-2"><i
                                        class="bi bi-calendar-event text-primary fs-5"></i></span>
                                <input type="date" class="form-control border-0 py-2" v-model="checkOut" :min="checkin"
                                    @change="validateDates" id="checkOut" aria-label="Ngày trả phòng" />
                            </div>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label text-dark fw-semibold">Khách & Phòng</label>
                            <div class="card card-body p-3 shadow-sm border-0 rounded-pill cursor-pointer"
                                data-bs-toggle="modal" data-bs-target="#guestSelectionModal">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center text-primary fw-bold">
                                        <i class="bi bi-person-fill fs-4 me-2"></i>
                                        <span>{{ totalAdults }} Người lớn</span>
                                        <span class="mx-2 text-muted">/</span>
                                        <span>{{ totalChildren }} Trẻ em</span>
                                    </div>
                                    <i class="bi bi-chevron-right text-muted"></i>
                                </div>
                            </div>

                            <div class="modal fade" id="guestSelectionModal" tabindex="-1"
                                aria-labelledby="guestSelectionModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
                                        <!-- Header -->
                                        <div class="modal-header bg-primary text-white border-0 py-4 px-4">
                                            <h5 class="modal-title fs-5 fw-bold d-flex align-items-center gap-2"
                                                id="guestSelectionModalLabel">
                                                <i class="bi bi-people-fill fs-4"></i> Chọn Số Lượng Khách
                                            </h5>
                                            <button type="button" class="btn-close btn-close-white"
                                                data-bs-dismiss="modal" aria-label="Đóng"></button>
                                        </div>

                                        <!-- Body -->
                                        <div class="modal-body px-4 pt-4">
                                            <div v-for="(room, index) in rooms" :key="index"
                                                class="mb-4 pb-4 border-bottom">

                                                <!-- Tiêu đề phòng -->
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <h6
                                                        class="mb-0 fw-semibold d-flex align-items-center gap-2 text-dark">
                                                        <i class="bi bi-door-closed text-primary fs-5"></i> Phòng {{
                                                            index + 1 }}
                                                    </h6>
                                                    <button v-if="rooms.length > 1" type="button"
                                                        class="btn btn-outline-danger btn-sm rounded-pill px-3"
                                                        @click="removeRoomFromModal(index)">
                                                        <i class="bi bi-trash me-1"></i> Xóa
                                                    </button>
                                                </div>

                                                <!-- Người lớn -->
                                                <div class="row align-items-center mb-3">
                                                    <div class="col-6 text-muted">Người lớn:</div>
                                                    <div class="col-6 text-end">
                                                        <div class="d-inline-flex align-items-center gap-2">
                                                            <button
                                                                class="btn btn-outline-secondary rounded-circle px-3 py-1 fs-5"
                                                                type="button" @click="decreaseAdults(index)"
                                                                :disabled="room.adults <= 1">−</button>
                                                            <span class="fw-bold fs-5"
                                                                style="width: 32px; text-align: center;">{{ room.adults
                                                                }}</span>
                                                            <button
                                                                class="btn btn-outline-secondary rounded-circle px-3 py-1 fs-5"
                                                                type="button" @click="increaseAdults(index)">+</button>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Trẻ em -->
                                                <div class="row align-items-center">
                                                    <div class="col-6 text-muted">Trẻ em:</div>
                                                    <div class="col-6 text-end">
                                                        <div class="d-inline-flex align-items-center gap-2">
                                                            <button
                                                                class="btn btn-outline-secondary rounded-circle px-3 py-1 fs-5"
                                                                type="button" @click="decreaseChildren(index)"
                                                                :disabled="room.children <= 0">−</button>
                                                            <span class="fw-bold fs-5"
                                                                style="width: 32px; text-align: center;">{{
                                                                    room.children }}</span>
                                                            <button
                                                                class="btn btn-outline-secondary rounded-circle px-3 py-1 fs-5"
                                                                type="button"
                                                                @click="increaseChildren(index)">+</button>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>

                                        <!-- Footer -->
                                        <div class="modal-footer justify-content-between border-0 px-4 pb-4 pt-0">
                                            <button type="button"
                                                class="btn btn-outline-secondary rounded-pill px-4 py-2"
                                                data-bs-dismiss="modal" @click="delSelection">
                                                <i class="bi bi-x-lg me-1"></i> Hủy
                                            </button>
                                            <button type="button" class="btn btn-primary rounded-pill px-4 py-2"
                                                data-bs-dismiss="modal" @click="confirmSelection">
                                                <i class="bi bi-check2-circle me-1"></i> Xác nhận
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>


                        </div>

                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100 py-3 rounded-pill shadow">
                                <i class="bi bi-search me-2"></i>Tìm Kiếm Phòng
                            </button>
                        </div>
                    </div>
                    <div v-if="errorMessage"
                        class="alert alert-danger d-flex align-items-center mt-3 rounded-3 shadow-sm" role="alert">
                        <i class="bi bi-exclamation-triangle-fill flex-shrink-0 me-2"></i>
                        <div>
                            {{ errorMessage }}
                        </div>
                    </div>
                </form>
            </div>
        </section>
        <!-- Toast ở góc phải trên -->
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 9999">
            <div id="roomToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header bg-primary text-white">
                    <strong class="me-auto">🎉 Thành công</strong>
                    <small>Vừa xong</small>
                    <button type="button" class="btn-close btn-close-white ms-2 mb-1" data-bs-dismiss="toast"
                        aria-label="Đóng"></button>
                </div>
                <div class="toast-body">
                    Bạn đã thêm phòng thành công!
                </div>
            </div>
        </div>
        <!-- Featured Hotels -->
        <!-- <section class="container my-5 py-lg-4">

            <div class="row g-5">
                <div class="col-lg-8">
                    <div class="row row-cols-1 g-4">
                        <div class="col" v-for="hotel in hotels" :key="hotel.id">
                            <div
                                class="card border-0 shadow rounded-4 overflow-hidden bg-white hover:shadow-lg transition-all">
                                <div class="row g-0">
                                    <div class="col-12 col-md-5">
                                        <div class="position-relative h-100" style="min-height: 280px;">
                                            <iframe class="w-100 h-100 rounded-start-4 object-fit-cover"
                                                :src="hotel.videoUrl || 'https://www.youtube.com/embed/frG7fz6umT8'"
                                                frameborder="0" allow="autoplay; encrypted-media; picture-in-picture"
                                                allowfullscreen>
                                            </iframe>

                                        </div>
                                    </div>

                                    <div class="col-12 col-md-7 d-flex flex-column p-4 bg-light">
                                        <h4 class="fw-bold text-dark mb-4">{{ hotel.name }}</h4>

                                        <span
                                            class="position-absolute top-0 end-0 m-3 badge bg-primary text-white px-3 py-2 rounded-pill shadow-sm">
                                            <i class="bi bi-door-open me-1"></i> {{ hotel.available_rooms }} Phòng
                                        </span>
                                        <div class="d-flex flex-wrap gap-4 text-muted fs-6 mb-2">
                                            <span><i class="bi bi-arrows-move"></i> {{ hotel.m2
                                            }} m²</span>
                                            <span><i class="bi bi-buildings-fill"></i> Hướng: Thành phố</span>
                                            <span><i class="bi bi-app-indicator"></i> {{ hotel.bed_count }}
                                                Giường</span>
                                            <span><i class="bi bi-people me-1 text-secondary"></i>{{ hotel.max_occupancy
                                            }} người lớn, 2 trẻ em / Phòng</span>
                                        </div>

                                        <p class="text-dark  mb-3"
                                            style="line-height: 1.5; max-height: 4.5em; overflow: hidden;">
                                            {{ hotel.description.substring(0, 160) }}...
                                        </p>

                                        <p v-if="hotel.surcharges > 0" class="text-warning  mb-2">
                                            <i class="bi bi-exclamation-triangle me-1"></i> Phụ thu: {{
                                                formatPrice(hotel.surcharges) }}
                                        </p>

                                        <div
                                            class="mt-auto pt-3 border-top border-light d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3">
                                            <div>
                                                <div class="fs-4 fw-bold text-primary">
                                                    {{ formatPrice(hotel.price) }}
                                                    <small class="text-muted fs-6">/ {{ hotel.total_days }} đêm / {{
                                                        hotel.so_phong }} phòng</small>
                                                </div>
                                            </div>

                                            <div class="d-flex gap-2 w-100 w-sm-auto">
                                                <button
                                                    class="btn btn-outline-dark rounded-pill px-2 py-2 fw-medium w-100 w-sm-auto"
                                                    @click="viewHotelDetails(hotel)">
                                                    <i class="bi bi-info-circle me-1"></i> Chi tiết
                                                </button>
                                                <button
                                                    class="btn btn-primary rounded-pill px-2 py-2 fw-medium w-100 w-sm-auto"
                                                    @click="addBooking(hotel)">
                                                    <i class="bi bi-bookmark-check me-1"></i> Thêm Phòng
                                                </button>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 d-none d-lg-block">
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
                                                    {{ room.total_days }} Đêm / {{ room.so_phong }} Phòng</span>
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
                            </div>
                            <div class="mt-3 text-gold fw-bold">
                                Tổng Giá(Tạm Tính): {{ formatPrice(totalPrice) }}
                            </div>
                            <button @click="openPopupshowModalBooking"
                                class="btn btn-gold w-100 py-2 rounded-pill fw-bold fs-5">
                                Tiến Hành Thanh Toán <i class="bi bi-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="d-lg-none fixed-bottom bg-primary border-top shadow-sm py-3" style="z-index: 999;">
                    <div class="container text-center">
                        <strong class="text-charcoal">{{ selectedRooms.length }} Phòng Đã Thêm</strong>
                        <div class="mt-2">
                            <button @click="openPopupshowModalBooking"
                                class="btn btn-gold w-100 py-2 rounded-pill fw-bold fs-5">
                                Tiến Hành Thanh Toán <i class="bi bi-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section> -->
        <!-- adjb -->

        <div class="container">
            <div class="left">
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
                                    <p class="mb-1 text-muted-dark small">{{ room.description.substring(0, 50) }}...
                                    </p>
                                    <p class="mb-0 text-gold fw-bold">
                                        {{ formatPrice(room.price) }}
                                        <span class="small text-charcoal-light">/ {{ room.total_days }} Đêm / {{
                                            room.so_phong }} Phòng</span>
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
                    </div>

                    <div class="mt-3 text-gold fw-bold">
                        Tổng Giá (Tạm Tính): {{ formatPrice(totalPrice) }}
                    </div>

                    <button @click="openPopupshowModalBooking"
                        class="btn btn-gold w-100 py-2 rounded-pill fw-bold fs-5 mt-4 btn-outline-dark">
                        Tiến Hành Thanh Toán <i class="bi bi-arrow-right ms-2"></i>
                    </button>
                </div>
            </div>

            <div class="right">
                <div class="room-card position-relative" v-for="hotel in hotels" :key="hotel.id">
                    <span
                        class="position-absolute top-0 end-0 badge bg-primary text-white m-2 px-3 py-2 rounded-pill shadow-sm">
                        <i class="bi bi-door-open me-1"></i> {{ hotel.available_rooms }} Phòng
                    </span>

                    <img :src="hotel.image" alt="Double Room" />

                    <div class="info-box d-flex flex-column">
                        <h2>
                            {{ formatPrice(hotel.price) }}
                            <small class="text-muted fs-6">/ {{ hotel.total_days }} đêm / {{ hotel.so_phong }}
                                phòng</small>
                        </h2>

                        <p v-if="hotel.surcharges > 0" class="text-warning mb-2">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            Phụ thu: {{ formatPrice(hotel.surcharges) }}
                        </p>

                        <h3>{{ hotel.name }}</h3>
                        <p>{{ hotel.description.substring(0, 160) }}...</p>

                        <div class="icons mb-3">
                            <div>
                                <img src="https://img.icons8.com/ios-filled/50/000000/bed.png" />
                                {{ hotel.bed_count }} Giường
                            </div>
                            <div>
                                <img src="https://img.icons8.com/ios-filled/50/000000/tv.png" />
                                {{ hotel.max_occupancy }} người lớn, {{ hotel.max_occupancy_child }} trẻ em / Phòng
                            </div>
                            <div>
                                <img
                                    src="https://media.istockphoto.com/id/1346028094/vi/vec-to/bi%E1%BB%83u-t%C6%B0%E1%BB%A3ng-th%C3%B4ng-tin-ph%C3%A1c-th%E1%BA%A3o-m%C3%A9t-vu%C3%B4ng.jpg?s=1024x1024&w=is&k=20&c=vPR7HFKEmPUCYAOQOxqpyNgWXfusGobjlkLiBZc0NNI=" />
                                {{ hotel.m2 }} m2
                            </div>
                        </div>

                        <div class="mt-auto d-flex gap-2">
                            <button class="btn btn-outline-dark rounded-pill px-3 py-2 w-100"
                                @click="viewHotelDetails(hotel)">
                                <i class="bi bi-info-circle me-1"></i> Chi tiết
                            </button>
                            <button class="btn btn-primary rounded-pill px-3 py-2 w-100" @click="addBooking(hotel)">
                                <i class="bi bi-bookmark-check me-1"></i> Thêm phòng
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <div class="container about-section mt-5">
            <div class="row align-items-center">
                <!-- LEFT TEXT -->
                <div class="col-lg-6 about-text">
                    <div class="subheading">Về chúng tôi</div>
                    <h1>Hãy cảm thấy như ở nhà<br>và tận hưởng trải nghiệm độc đáo</h1>
                    <p>Chúng tôi mang đến không gian thoải mái, ấm cúng và đầy đủ tiện nghi để bạn có thể thư giãn trọn
                        vẹn.</p>
                    <p>Chúng tôi tin rằng mỗi chuyến đi là một kỷ niệm. Vì vậy, mọi chi tiết đều được chăm chút tỉ mỉ để
                        mang lại trải nghiệm đáng nhớ cho bạn.</p>
                    <div class="signature">HXHHOTEL... </div>
                </div>

                <!-- RIGHT IMAGE -->
                <div class="col-lg-6">
                    <div class="image-wrapper">
                        <!-- Ảnh chính -->
                        <img src="https://www.ansonika.com/paradise/one-page-version/img/gallery/3.jpg" alt="Main"
                            class="main-image" />
                        <!-- Ảnh overlay -->
                        <img src="https://www.ansonika.com/paradise/one-page-version/img/local_amenities_1.jpg"
                            alt="Overlay" class="overlay-image" />
                    </div>
                </div>
            </div>
        </div>
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
                                    <div class="total">
                                        <p>Số khách:</p>
                                        <p>{{ totalAdults }} Người lớn - {{ totalChildren }} Trẻ em</p>
                                    </div>
                                    <div class="total" v-if="surchargeSucChua > 0">
                                        <p>Phụ thụ sức chứa:</p>
                                        <p>{{ formatPrice(surchargeSucChua) }}</p>
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
                                    <!-- Displaying selected room details -->
                                    <div class="container my-5 py-4">
                                        <!-- <h2 class="text-center fw-bold mb-4 text-dark fs-4">
                                            <span class="d-inline-block pb-2 border-bottom border-3 border-info">Thông
                                                Tin Đặt Phòng</span>
                                        </h2>

                                        <div class="row g-3 justify-content-center">
                                            <div v-for="(room, index) in selectedRooms" :key="index"
                                                class="col-12 col-md-6 col-lg-4">
                                                <div class="card shadow-sm border-0 rounded-3">
                                                    <div class="card-body p-4 bg-light">
                                                        <h6 class="mb-0 fw-bold text-primary">Phòng {{ index + 1 }}:
                                                            <span class="text-secondary fw-normal">{{ room.name
                                                            }}</span>
                                                        </h6>
                                                        <span class="fw-bold text-success fs-5">{{
                                                            formatPrice(room.price) }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div> -->

                                        <div
                                            class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mt-4 py-4 px-4 border border-info rounded-3 shadow-lg">
                                            <h6 class="mb-2 mb-md-0 fw-bold text-uppercase text-dark">Tổng Cộng Thanh
                                                Toán:  </h6>
                                            <p class="h4 mb-0 fw-bolder text-primary"> {{
                                                formatPrice(totalCostForAllRooms) }}</p>
                                        </div>
                                    </div>

                                    <div class="mb-1">
                                        <div class="radio-input">
                                            <input value="value-1" name="value-radio" id="value-1" type="radio"
                                                @click="checkAndSendOtp" />
                                            <label for="value-1">
                                                <div class="text">
                                                    <span class="circle"></span>
                                                    Thanh Toán Sau
                                                </div>
                                                <span class="info-pay">SMS OTP</span>
                                            </label>

                                            <input value="value-2" name="value-radio" id="value-2" type="radio"
                                                @click="checkAndSendOtpPayos" />
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
                        <!-- <button type="submit" class="btn btn-primary" @click="confirmBooking">Xác nhận Đặt phòng</button> -->
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
                    <input v-model="otpInputs" type="text" placeholder="Nhập OTP" required="">
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
        <!-- <div v-if="showModal" class="modal fade show mt-5"
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
         -->
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
    //console.log(hotel)
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
        selectedRooms.totalAdults = totalAdults.value;
        selectedRooms.totalChildren = totalChildren.value;
        selectedRooms.totalRooms = selectedRooms.value.length;// tong so phong
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
const surchargeSucChua = ref(0);
const openPopupshowModalBooking = () => {
    if (selectedRooms.value.length === 0) {
        alert('Vui lòng chọn ít nhất một phòng trước khi đặt.');
        return;
    }

    // Tính tổng sức chứa của các phòng đã chọn
    let totalAdultsRoom = 0;
    let totalChildrenRoom = 0;

    selectedRooms.value.forEach(room => {
        const roomCount = room.so_phong || 1;
        totalAdultsRoom += (room.max_occupancy || 0) * roomCount;
        totalChildrenRoom += (room.max_occupancy_child || 0) * roomCount;
    });

    // Người dùng chọn bao nhiêu người
    const selectedAdults = selectedRooms.totalAdults || 0;
    const selectedChildren = selectedRooms.totalChildren || 0;

    // Tính số người vượt
    const extraAdults = Math.max(0, selectedAdults - totalAdultsRoom);
    const extraChildren = Math.max(0, selectedChildren - totalChildrenRoom);

    // Tính phí phụ thu
    const feeAdult = 50000;  // 50k/người lớn vượt
    const feeChild = 30000;  // 30k/trẻ em vượt
    surchargeSucChua.value = (extraAdults * feeAdult) + (extraChildren * feeChild);

    // In ra kiểm tra
    console.log('selectedRooms:', selectedRooms.value);
    console.log('Tổng người lớn của phòng:', totalAdultsRoom);
    console.log('Tổng trẻ em của phòng:', totalChildrenRoom);
    console.log('Số người lớn khách chọn:', selectedAdults);
    console.log('Số trẻ em khách chọn:', selectedChildren);
    console.log('Phụ thu:', surchargeSucChua.value.toLocaleString(), 'VND');

    // Nếu bạn có formBooking hoặc booking_details, có thể gán:
    // formBooking.value.additional_fee = surcharge;

    showModalBooking.value = true;
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
        return total + surchargeSucChua.value + calculateRoomTotal(room); // Cộng tổng tiền từng phòng
    }, 0);
});
//lấy loại phòng
const getRoomTypes = async () => {
    isLoading.value = true;
    try {
        // Gọi đồng thời 2 API — API check-availability có truyền ngày
        const [roomTypeRes, availabilityRes] = await Promise.all([
            axios.get(`${apiUrl}/api/room-types/`),
            axios.get(`${apiUrl}/api/check-availability`, {
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
                max_occupancy_child: room.max_occupancy_child,
                images: [
                    'https://img.lottehotel.com/cms/asset/2025/07/01/29403/438-2-1920-roo-LTHA.webp',
                    room.images
                ],
                image: room.images || "https://img.lottehotel.com/cms/asset/2025/07/01/29403/438-2-1920-roo-LTHA.webp",
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
    isLoading.value = true; // Bắt đầu tải dữ liệu (spinner hiện ra)

    // --- DÒNG CODE MỚI: Giả lập độ trễ 5 giây cho mục đích test ---
    // await new Promise(resolve => setTimeout(resolve, 5000));
    // -----------------------------------------------------------------

    await getRoomTypes();

    try {
        // Dữ liệu cần gửi (phần này sẽ chạy sau độ trễ 5s)
        const requestData = {
            checkin: checkin.value,
            checkout: checkOut.value,
            bookrooms: bookrooms.value
        };

        // Gọi API lấy giá phòng thực tế
        const roomPricesResponse = await axios.post(`${apiUrl}/api/prices/prices_client`, requestData);
        const roomPrices = roomPricesResponse.data || [];
        //console.log(roomPrices);

        // Cập nhật dữ liệu phòng
        hotels.value = hotels.value.map(room => {
            const roomPrice = roomPrices.find(price => price.type_id === room.id);

            return {
                ...room,
                price_per_night: roomPrice ? roomPrice.gia_tien1ngay : 0,
                total_days: roomPrice ? roomPrice.total_days : 0,
                surcharges: roomPrice ? roomPrice.surcharges : 0,
                price: roomPrice.total_price,
                so_phong: roomPrice.so_phong,
                gia_tien1ngay: roomPrice.gia_tien1ngay,
                so_tien1phong: roomPrice.so_tien1phong,
                gia1h: roomPrice.gia1h
            };
        });
        //console.log("Bắt đầu lấy giá phòng...");
        //console.log("Giá phòng đã lấy:", hotels.value); // Kiểm tra dữ liệu phòng đã lấy

        calculateTotalDays();
    } catch (error) {
        console.error("Có lỗi xảy ra khi lấy dữ liệu:", error);
    } finally {
        isLoading.value = false; // Kết thúc tải dữ liệu (spinner ẩn đi)
    }
};
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
        adult: totalAdults.value,
        child: totalChildren.value,
        pricing_type: 'nghitly',
        payment_status: 'pending',
        status: 'pending_confirmation',
        note: orderNotes.value || 'Không có ghi chú',
    };
    console.log("Booking Details:", JSON.stringify(bookingDetails, null, 2)); // Log booking details as JSON
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
            //window.open(response.data.checkoutUrl, '_blank'); // Mở trong tab mới
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
//check de gui sms
const checkAndSendOtp = () => {
    const phone = String(phoneNumber.value || '').trim();
    const storageKey = 'sentOtpPhones';
    if (!phone) return;

    const sentPhones = JSON.parse(localStorage.getItem(storageKey)) || [];
    const isDuplicate = sentPhones.includes(phone);

    paymentMethod.value = 'thanh_toan_sau';

    if (isDuplicate) {
        // Nếu đã xác thực rồi thì thực hiện luôn
        confirmBooking();
        router.push('/thanksBooking');
    } else {
        // Gửi OTP rồi đợi xác thực mới thực hiện
        sendOtpSMS();
    }
};

const checkAndSendOtpPayos = () => {
    const phone = String(phoneNumber.value || '').trim();
    const storageKey = 'sentOtpPhones';
    if (!phone) return;

    const sentPhones = JSON.parse(localStorage.getItem(storageKey)) || [];
    const isDuplicate = sentPhones.includes(phone);

    paymentMethod.value = 'thanh_toan_qr';

    if (isDuplicate) {
        payQr();
    } else {
        sendOtpSMS();
    }
};


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

        // ✅ Xác thực OTP thành công
        isOtp.value = false;
        const phone = String(phoneNumber.value || '').trim();
        const storageKey = 'sentOtpPhones';
        const sentPhones = JSON.parse(localStorage.getItem(storageKey)) || [];

        if (!sentPhones.includes(phone)) {
            sentPhones.push(phone);
            localStorage.setItem(storageKey, JSON.stringify(sentPhones));
        }

        //  Sau xác thực thì tiếp tục hành động: bạn chọn 1 trong 2 bên dưới
        if (paymentMethod.value === 'thanh_toan_sau') {
            await confirmBooking();
            router.push('/thanksBooking');
        } else if (paymentMethod.value === 'thanh_toan_qr') {
            await payQr();
        }

    } catch (error) {
        console.error('Lỗi xác minh mã:', error.message || error);
        alert(`OTP không hợp lệ. Vui lòng thử lại.`);
    } finally {
        isLoading.value = false;
    }
};

const closeModalOtp = async () => {
    isOtp.value = false;
}
//xem chi tiết hạng phòng
// function viewHotelDetails(hotel) {

//     selectedHotel.value = hotel;
//     showModal.value = true;
// }
const viewHotelDetails = (hotel) => {
    router.push({ name: 'RoomTypeDetail', params: { id: hotel.id } });
};
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
    // getRoomTypes();
    getRoomPrices();
    calculateTotalDays();
});
</script>

<style scoped>
* {
    box-sizing: border-box;
    font-family: 'Montserrat', sans-serif;
}

body {
    margin: 0;
    background-color: #f9f7f4;
    padding: 40px;
}

.container {
    display: flex;
    gap: 40px;
    max-width: 1300px;
    margin: auto;
}

.left {
    flex: 0 0 35%;
    position: sticky;
    top: 100px;
    height: fit-content;
}

.left h5 {
    letter-spacing: 2px;
    font-size: 14px;
    color: #a28c5c;
    text-transform: uppercase;
    margin-bottom: 10px;
}

.left h2 {
    font-size: 40px;
    font-weight: 700;
    margin-bottom: 20px;
    color: #333;
}

.left p {
    font-size: 16px;
    color: #555;
    line-height: 1.6;
}

.left p strong {
    color: #000;
}



.right {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 100px;
}

.room-card {
    position: relative;
    border-radius: 14px;
    /* overflow: hidden; */
}


.room-card img {
    width: 100%;
    border-radius: 14px;
    height: auto;
    display: block;
    z-index: 9;
}

.info-box {
    background-color: white;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    padding: 40px;
    /* width: 100%; */
    max-width: 600px;
    border-radius: 10px;
    overflow: visible;
    position: absolute;
    bottom: -100px;
    /* Âm xuống */
    left: 30px;
    margin-bottom: 40px;
    z-index: 10;
}

.info-box h2 {
    color: #a28c5c;
    font-size: 24px;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 10px;
}

.info-box h3 {
    font-size: 24px;
    margin-bottom: 10px;
}

.info-box p {
    font-size: 14px;
    color: #555;
    margin-bottom: 20px;
}

.icons {
    display: flex;
    gap: 25px;
    font-size: 14px;
    align-items: center;
}

.icons div {
    display: flex;
    align-items: center;
    gap: 8px;
}

.icons img {
    width: 20px;
    height: 20px;
}

/* Optional: for smaller screens */
@media (max-width: 992px) {
    .container {
        flex-direction: column;
    }

    .left {
        position: static;
        text-align: center;
    }

    .info-box {
        position: relative;
        bottom: unset;
        left: unset;
        margin-top: 10px;
    }
}

/* about */
.about-section {
    padding: 80px 0;
}

.about-text h1 {
    font-weight: 700;
    font-size: 2.5rem;
    line-height: 1.3;
}

.about-text p {
    color: #555;
    line-height: 1.7;
}

.about-text .subheading {
    text-transform: uppercase;
    letter-spacing: 2px;
    color: #999;
    font-size: 0.85rem;
    margin-bottom: 1rem;
}

.about-text .signature {
    font-family: 'Dancing Script', cursive;
    font-size: 1.5rem;
    margin-top: 1.5rem;
    color: #333;
}

.image-wrapper {
    position: relative;
    width: 100%;
    max-width: 500px;
    margin-left: auto;
}

.main-image {
    border-radius: 12px;
    width: 100%;
    height: auto;
    display: block;
}

.overlay-image {
    position: absolute;
    bottom: -20px;
    left: -40px;
    width: 200px;
    border-radius: 12px;
    border: 5px solid #fff;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

@media (max-width: 768px) {
    .overlay-image {
        bottom: -10px;
        left: 0;
        width: 150px;
    }
}

/* General Card Styling */
.hero-section {
    background: linear-gradient(rgba(247, 253, 255, 0.2), rgba(0, 0, 0, 0.3)),
        url("https://www.ansonika.com/paradise/html-menu-1/img/rooms/2.jpg");
    background-size: cover;
    background-position: center;
    height: 600px;
    color: white;
    position: relative;
    display: flex;
    align-items: center;
    /* Canh giữa chiều dọc */
    justify-content: center;
    /* Canh giữa chiều ngang */
}

.search-box {
    background-color: rgba(255, 255, 255, 0.95);
    border-radius: 16px;
    width: 100%;
    /* max-width: 1000px; */
    padding: 30px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
    /* z-index: 2; */
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
/* model nguyen tac chung */
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



/* Show tooltip on hover */
</style>
