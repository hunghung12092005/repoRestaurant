<template>
  <div>
    <!-- Loading State -->
    <div v-if="loading" class="loading-container d-flex justify-content-center align-items-center">
      <div class="spinner-border text-primary" role="status" style="width: 4rem; height: 4rem;">
        <span class="visually-hidden">Loading...</span>
      </div>
    </div>

    <!-- Content State -->
    <div v-else-if="roomType">
      <!-- Hero Section with Full-width Carousel -->
      <section class="hero-section position-relative">
        <div id="hotelImageCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000">
          <div class="carousel-inner">
            <div class="carousel-item active">
              <img src="https://pistachiohotel.com/UploadFile/Gallery/Overview/a3.jpg" class="d-block w-100" alt="Hotel Room View 1">
            </div>
            <div class="carousel-item">
              <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRMFteLbo7EdtFP32wDKvTJxML1CEt9pCdo4ByeKxCZnFX9cBf0ifdq6eCRQZBW_3feWRI&usqp=CAU" class="d-block w-100" alt="Hotel Room View 2">
            </div>
            <div class="carousel-item">
              <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR7roFS7C9CH67wV7w3WdxLZ8CtW8nuvA2tf3kNJzn6YR5Xczj8AktzixNewUwV_SASOz8&usqp=CAU" class="d-block w-100" alt="Hotel Room View 3">
            </div>
          </div>
          <button class="carousel-control-prev" type="button" data-bs-target="#hotelImageCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
          </button>
          <button class="carousel-control-next" type="button" data-bs-target="#hotelImageCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
          </button>
        </div>
        <div class="overlay-content d-flex align-items-center justify-content-center text-center">
          <div>
            <h1 class="text-white display-3 fw-bolder">{{ roomType.type_name }}</h1>
            <p class="text-white fs-4 lead">Trải nghiệm không gian nghỉ dưỡng đẳng cấp</p>
          </div>
        </div>
      </section>

      <!-- Main Details Section -->
      <main class="container py-5">
        <div class="row g-5">
          <!-- Description -->
          <div class="col-12">
            <h2 class="section-title">Tổng Quan Về Hạng Phòng</h2>
            <p class="text-muted fs-5">{{ roomType.description }}</p>
          </div>
        </div>
        
        <hr class="my-5">

        <div class="row g-5 align-items-center">
            <!-- Key Info -->
            <div class="col-lg-4">
                <ul class="info-list list-unstyled">
                    <li><i class="bi bi-people-fill"></i> Sức chứa: <strong>{{ roomType.max_occupancy }} người</strong></li>
                    <li><i class="bi bi-aspect-ratio-fill"></i> Diện tích: <strong>{{ roomType.m2 }} m²</strong></li>
                    <li><i class="bi bi-hdd-stack-fill"></i> Số giường: <strong>{{ roomType.bed_count }} giường</strong></li>
                </ul>
            </div>
            <!-- Amenities -->
            <div class="col-lg-4">
                <h3 class="subsection-title"><i class="bi bi-gem me-2"></i>Tiện nghi</h3>
                <ul v-if="roomType.amenities && roomType.amenities.length > 0" class="feature-list list-unstyled">
                    <li v-for="amenity in roomType.amenities" :key="amenity.amenity_id">
                        <i class="bi bi-check-circle-fill text-success"></i> {{ amenity.amenity_name }}
                    </li>
                </ul>
                <p v-else class="text-muted">Không có thông tin tiện nghi.</p>
            </div>
            <!-- Services -->
            <div class="col-lg-4">
                <h3 class="subsection-title"><i class="bi bi-patch-check-fill me-2"></i>Dịch vụ</h3>
                <ul v-if="roomType.services && roomType.services.length > 0" class="feature-list list-unstyled">
                    <li v-for="service in roomType.services" :key="service.service_id">
                        <i class="bi bi-check-circle-fill text-success"></i> {{ service.service_name }}
                    </li>
                </ul>
                <p v-else class="text-muted">Không có thông tin dịch vụ.</p>
            </div>
        </div>

        <!-- Call to Action Section -->
        <section class="text-center mt-5 pt-5 border-top">
          <h2 class="mb-4">Sẵn sàng cho kỳ nghỉ của bạn?</h2>
          <router-link to="/rooms3" class="btn btn-primary btn-lg rounded-pill px-5 py-3 shadow-lg">
            <i class="bi bi-calendar-check me-2"></i> Đặt Phòng Ngay
          </router-link>
        </section>
      </main>
    </div>

    <!-- Error State -->
    <div v-else class="loading-container d-flex justify-content-center align-items-center">
        <div class="alert alert-danger text-center">
            <h4 class="alert-heading">Lỗi!</h4>
            <p>Không tìm thấy thông tin cho loại phòng này. Vui lòng thử lại.</p>
            <hr>
            <router-link to="/" class="btn btn-danger">Về trang chủ</router-link>
        </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, inject, watch } from 'vue';
import { useRoute } from 'vue-router';
import axiosConfig from '../axiosConfig.js';

const route = useRoute();
const roomType = ref(null);
const loading = ref(true);
const apiUrl = inject('apiUrl');

const fetchRoomTypeDetails = async () => {
  loading.value = true;
  roomType.value = null;
  const typeId = route.params.id;
  
  if (!typeId) {
    loading.value = false;
    return;
  }

  try {
    const response = await axiosConfig.get(`${apiUrl}/api/room-types`);
    if (response.data.status && response.data.data) {
      const foundRoomType = response.data.data.find(rt => rt.type_id == typeId);
      if(foundRoomType) {
        roomType.value = foundRoomType;
      } else {
        console.error(`Không tìm thấy loại phòng với ID: ${typeId}`);
      }
    }
  } catch (error) {
    console.error(`Lỗi khi lấy chi tiết loại phòng ${typeId}:`, error);
    roomType.value = null;
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchRoomTypeDetails();
});

watch(() => route.params.id, (newId) => {
  if (newId) {
    fetchRoomTypeDetails();
  }
});
</script>

<style scoped>
.loading-container {
  min-height: calc(100vh - 80px); /* Full height minus navbar */
  padding-top: 80px;
}

/* --- Hero Section --- */
.hero-section {
  height: 65vh;
  min-height: 450px;
  max-height: 700px;
  color: #fff;
}

.hero-section .carousel,
.hero-section .carousel-inner,
.hero-section .carousel-item {
  height: 100%;
}

.hero-section .carousel-item img {
  height: 100%;
  object-fit: cover;
  filter: brightness(0.6);
}

.overlay-content {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.3);
}

/* --- Main Content Section --- */
.section-title {
  font-weight: 700;
  color: #081B54;
  margin-bottom: 1.5rem;
  padding-bottom: 0.75rem;
  border-bottom: 3px solid #16B978;
  display: inline-block;
}

.subsection-title {
    font-weight: 600;
    color: #333;
    margin-bottom: 1rem;
}

.info-list li {
    font-size: 1.1rem;
    padding: 0.8rem 0;
    border-bottom: 1px solid #e9ecef;
}

.info-list li i {
    color: #16B978;
    margin-right: 1rem;
    font-size: 1.5rem;
    vertical-align: middle;
}

.feature-list li {
    padding: 0.5rem 0;
    font-size: 1rem;
}
.feature-list li i {
    margin-right: 0.75rem;
}

.btn-primary {
    background-color: #16B978;
    border-color: #16B978;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    background-color: #13a86c;
    border-color: #13a86c;
    transform: translateY(-3px);
    box-shadow: 0 4px 15px rgba(22, 185, 120, 0.4);
}
</style>