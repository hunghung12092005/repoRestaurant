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
                <form @submit.prevent="searchHotels">
                    <div class="row g-3">
                        <!-- <div class="col-md-3">
                <label for="destination" class="form-label">Destination</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                  <input type="text" class="form-control" v-model="search.destination" placeholder="Where are you going?" />
                </div>
              </div> -->
                        <div class="col-md-2">
                            <label for="checkIn" class="form-label">Check in</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                                <input type="date" class="form-control" v-model="search.checkIn" />
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label for="checkOut" class="form-label">Check out</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                                <input type="date" class="form-control" v-model="search.checkOut" />
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label for="destination" class="form-label">Số Phòng</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                                <input type="text" class="form-control" v-model="search.destination"
                                    placeholder="Bạn muốn book bao nhiêu rooms?" />
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label for="guests" class="form-label">Số Khách</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-people"></i></span>
                                <select class="form-select" v-model="search.guests">
                                    <option value="1">1 Adult</option>
                                    <option value="2">2 Adults</option>
                                    <option value="3">3 Adults</option>
                                    <option value="4">4 Adults</option>
                                    <option value="5">5+ Adults</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100 py-3">
                                <i class="bi bi-search me-2"></i>Search Hotels
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
                            <p class="card-text">{{ hotel.description.substring(0, 100) }}...</p>
                            <!-- <p class="card-text">{{ hotel.bed_count }}...</p>
                            <p class="card-text">{{ hotel.max_occupancy }}...</p> -->

                            <div class="d-flex align-items-center">
                                <span class="fs-5 fw-bold text-primary">{{ hotel.currency }}{{ hotel.price }}</span>

                                <span class="text-muted ms-1">/ night</span>

                            </div>
                        </div>
                        <div class="card-footer bg-white">
                            <div class="d-flex justify-content-between">
                                <button class="btn btn-outline-primary" @click="viewHotelDetails(hotel)">
                                    <i class="bi bi-info-circle me-1"></i>Xem chi tiết
                                </button>
                                <button class="btn btn-primary" @click="bookHotel(hotel.id)">
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
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Hotel Details</h5>
                        <button type="button" class="btn-close" @click="closeModal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <h4>{{ selectedHotel.name }}</h4>
                        <img :src="selectedHotel.image" class="card-img-top" :alt="selectedHotel.name"
                            style="height: 200px; object-fit: cover;" />
                        <p><strong>Location:</strong> {{ selectedHotel.location }}</p>
                        <p><strong>Price per night:</strong> {{ selectedHotel.currency }}{{ selectedHotel.price }}</p>
                        <p><strong>Description:</strong> {{ selectedHotel.description }}</p>
                        <hr />
                        <h4>Tiện nghi:</h4>
                        <li v-if="selectedHotel.amenities.length === 0">Không có tiện nghi đi kèm cho hạng phòng này
                        </li>
                        <ul>
                            <li v-for="amenity in selectedHotel.amenities" :key="amenity.amenity_id">
                                {{ amenity.amenity_name }}: {{ amenity.description }}
                            </li>
                        </ul>
                        <h4>Dịch vụ:</h4>
                        <li v-if="selectedHotel.services.length === 0">Không có dịch vụ đi kèm cho hạng phòng này</li>

                        <ul>
                            <li v-for="service in selectedHotel.services" :key="service.service_id">
                                {{ service.service_name }}: {{ service.price }}
                            </li>
                        </ul>
                        <!-- <h5>Amenities:</h5>
                        <ul>
                            <li v-for="amenity in selectedHotel.amenities" :key="amenity">{{ amenity }}</li>
                        </ul> -->
                    </div>
                </div>
            </div>
        </div>
        <Footer></Footer>

    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
// import Modal from '../Model.vue';
import loading from '../loading.vue';
import Footer from '../Footer.vue';
import Popup from '../Popup.vue';

const showPopup = ref(false);//popup mới vào trang
const showPopUpMain = () => {
    showPopup.value = true;//popup mới vào trang
}
const hotels = ref([]);
const isLoading = ref(false);
const getRoomTypes = async () => {
    isLoading.value = true;
    try {
        const response = await axios.get('http://127.0.0.1:8000/api/room-types/');
        console.log(response.data.data);

        hotels.value = response.data.data.map(room => ({
            id: room.type_id,
            name: room.type_name,
            description: room.description,
            bed_count: room.bed_count,
            amenities: room.amenities || [], // Lưu thông tin tiện nghi
            services: room.services || [],
            max_occupancy: room.max_occupancy,
            image: "https://i.postimg.cc/d3pNGXPN/7c6764b8-de90-474c-9b98-05019aef3193.png", // Cập nhật với URL hình ảnh thực tế
            youtube_link: room.youtube_link || "", // Liên kết video
            price: 0, // Cập nhật giá nếu có
            currency: "$", // Cập nhật loại tiền tệ nếu cần
            rating: room.rate, // Cập nhật đánh giá nếu cần
            m2: room.m2, // Cập nhật đánh giá nếu cần
        }));
        console.log(hotels.value);
        showPopup.value = true;//popup mới vào trang

    } catch (error) {
        console.error("Có lỗi xảy ra khi lấy dữ liệu:", error);
    } finally {
        isLoading.value = false;
    }
}

const search = ref({
    destination: '',
    checkIn: '',
    checkOut: '',
    guests: 2,
});

const showModal = ref(false);
const selectedHotel = ref(null);

function searchHotels() {
    // Logic to search hotels
}
function bookHotel(hotel) {
    selectedHotel.value = hotel;
    showModal.value = true;
}
function viewHotelDetails(hotel) {
    selectedHotel.value = hotel;
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
    selectedHotel.value = null;
}

onMounted(() => {
    getRoomTypes();
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

/* From Uiverse.io by Cksunandh */
/* Basic reset and styling */

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
    margin-top: 5px; /* Khoảng cách giữa biểu tượng và chữ */
    font-size: 14px; /* Kích thước chữ */
    color: #27c5f1; /* Màu chữ */
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