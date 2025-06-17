<template>
    <div>
        <!-- Hero Section -->
        <header class="hero-section d-flex align-items-center">
            <div class="container text-center">
                <h1 class="display-3 fw-bold">Find Your Perfect Stay</h1>
                <p class="lead">Discover premium hotels at unbeatable prices worldwide</p>
            </div>
        </header>

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
        <section class="container my-5">
            <h2 class="text-center mb-4">Lựa Chọn Hạng Phòng</h2>
            <div class="row g-4">
                <div class="col-md-6 col-lg-4" v-for="hotel in hotels" :key="hotel.id">
                    <div class="card hotel-card h-100">
                        <!-- <img :src="hotel.image" class="card-img-top" :alt="hotel.name"
                            style="height: 200px; object-fit: cover;" /> -->
                        <iframe width="100%" height="255"
                            src="https://www.youtube.com/embed/1IbCt-nY09Q?si=VNRifz2UegA4rH-R"
                            title="YouTube video player" frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <h5 class="card-title mb-0">{{ hotel.name }}</h5>
                                <span class="badge bg-primary">{{ hotel.rating }} ★</span>
                            </div>
                            <p class="text-muted"><i class="bi bi-geo-alt-fill"></i> {{ hotel.location }}</p>
                            <p class="card-text">{{ hotel.description.substring(0, 100) }}...</p>
                            <div class="d-flex align-items-center">
                                <span class="fs-5 fw-bold text-primary">{{ hotel.currency }}{{ hotel.price }}</span>
                                <span class="text-muted ms-1">/ night</span>
                            </div>
                        </div>
                        <div class="card-footer bg-white">
                            <div class="d-flex justify-content-between">
                                <button class="btn btn-outline-primary" @click="viewHotelDetails(hotel)">
                                    <i class="bi bi-info-circle me-1"></i>View Details
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
                        <h5>Để lại thông tin để bộ phận CSKH HXH hotel liên hệ hỗ trợ bạn ngay hoặc liên hệ chatbox CSKH Online Hotel <router-link class="dropdown-item " to="/chat">tại đây</router-link></h5>   
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
                        <h5>Amenities:</h5>
                        <ul>
                            <li v-for="amenity in selectedHotel.amenities" :key="amenity">{{ amenity }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <Footer></Footer>
</template>

<script setup>
import { ref, onMounted } from 'vue';
// import Modal from '../Model.vue';
import Footer from '../Footer.vue';
const hotels = ref([
    {
        id: 1,
        name: "Grand Royal Hotel",
        location: "New York, USA",
        price: 299,
        currency: "$",
        rating: 4.8,
        description: "Experience luxury at its finest in the heart of Manhattan. The Grand Royal Hotel offers stunning views of the city skyline.",
        image: "https://images.unsplash.com/photo-1618773928121-c32242e63f39?ixlib=rb-4.0.3&auto=format&fit=crop&w=1470&q=80"
    },
    {
        id: 2,
        name: "Seaside Resort & Spa",
        location: "Cancun, Mexico",
        price: 189,
        currency: "$",
        rating: 4.6,
        description: "Nestled on the pristine beaches of Cancun, the Seaside Resort & Spa offers an all-inclusive experience.",
        image: "https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-4.0.3&auto=format&fit=crop&w=1470&q=80"
    },
    {
        id: 3,
        name: "Alpine Lodge",
        location: "Interlaken, Switzerland",
        price: 249,
        currency: "$",
        rating: 4.9,
        description: "Surrounded by the majestic Swiss Alps, Alpine Lodge offers a cozy retreat with breathtaking mountain views.",
        image: "https://images.unsplash.com/photo-1613490493576-7fde63acd811?ixlib=rb-4.0.3&auto=format&fit=crop&w=1471&q=80"
    }
]);

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
    // Load featured hotels on component mount
});
</script>

<style scoped>
.hero-section {
    background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)),
        url("https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?ixlib=rb-4.0.3&auto=format&fit=crop&w=1470&q=80");
    background-size: cover;
    background-position: center;
    height: 500px;
    color: white;
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

.footer {
    background-color: #343a40;
    color: white;
}
</style>