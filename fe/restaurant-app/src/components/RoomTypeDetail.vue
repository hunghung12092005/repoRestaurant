<template>
  <div>
    <!-- Loading State -->
    <div v-if="loading" class="loading-container d-flex justify-content-center align-items-center">
      <div class="spinner-border text-primary" role="status" style="width: 4rem; height: 4rem;">
        <span class="visually-hidden">Loading...</span>
      </div>
    </div>

    <!-- Content State -->
    <div v-else-if="roomType" class="page-content">
      <div class="container py-5">
        <!-- Header: Title and Back Button -->
        <div class="row align-items-center mb-4">
          <div class="col-md-8">
            <h1 class="display-4 fw-bold room-title">{{ roomType.type_name }}</h1>
            <p class="fs-5 text-muted room-subtitle">Trải nghiệm không gian nghỉ dưỡng đẳng cấp và tiện nghi vượt trội.</p>
          </div>
          <div class="col-md-4 text-md-end">
            <router-link to="/rooms3" class="btn btn-primary btn-lg rounded-pill px-4 py-2 shadow-sm">
                <i class="bi bi-calendar-check me-2"></i> Đặt Phòng Ngay
            </router-link>
          </div>
        </div>

        <!-- Carousel -->
        <section class="carousel-section mb-5">
          <!-- SỬA Ở ĐÂY: Xóa 2 nút <button> Previous và Next -->
          <div id="hotelImageCarousel" class="carousel slide shadow-lg" data-bs-ride="carousel">
            <div class="carousel-indicators">
              <button 
                v-for="(image, index) in roomType.images" 
                :key="index"
                type="button" 
                data-bs-target="#hotelImageCarousel" 
                :data-bs-slide-to="index" 
                :class="{ 'active': index === 0 }" 
                :aria-current="index === 0 ? 'true' : 'false'"
                :aria-label="`Slide ${index + 1}`"
              ></button>
            </div>
            <div class="carousel-inner">
              <div 
                v-if="roomType.images && roomType.images.length > 0"
                v-for="(image, index) in roomType.images" 
                :key="index" 
                class="carousel-item" 
                :class="{ 'active': index === 0 }"
              >
                <img :src="`${apiUrl}/images/room_type/${image}`" class="d-block w-100" :alt="`Room Image ${index + 1}`">
              </div>
              <div v-else class="carousel-item active">
                <img src="https://via.placeholder.com/1200x700?text=No+Image+Available" class="d-block w-100" alt="No Image">
              </div>
            </div>
          </div>
        </section>

        <!-- Main Details Section -->
        <main class="main-content">
          <div class="row g-4 g-lg-5">
            <!-- Left Column: Description & Key Info -->
            <div class="col-lg-7">
              <div class="detail-card">
                <h2 class="section-title">Tổng Quan Về Hạng Phòng</h2>
                <p class="text-muted fs-5">{{ roomType.description }}</p>
                
                <div class="row g-3 mt-4 text-center">
                    <div class="col-sm-4">
                        <div class="info-card">
                            <i class="bi bi-people-fill info-icon"></i>
                            <div class="info-label">Sức chứa</div>
                            <div class="info-value">{{ roomType.max_occupancy }} người</div>
                        </div>
                    </div>
                    <!-- SỬA Ở ĐÂY: Sửa roomType.m2 thành roomType.area -->
                    <div class="col-sm-4">
                        <div class="info-card">
                            <i class="bi bi-aspect-ratio-fill info-icon"></i>
                            <div class="info-label">Diện tích</div>
                            <div class="info-value">{{ roomType.m2 || 'N/A' }} m²</div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="info-card">
                           <i class="bi bi-hdd-stack-fill info-icon"></i>
                            <div class="info-label">Số giường</div>
                            <div class="info-value">{{ roomType.bed_count }} giường</div>
                        </div>
                    </div>
                </div>
              </div>
            </div>

            <!-- SỬA Ở ĐÂY: Bỏ cột dịch vụ, mở rộng cột tiện nghi -->
            <!-- Right Column: Amenities -->
            <div class="col-lg-5">
              <div class="detail-card">
                <h2 class="section-title">Tiện Nghi Trong Phòng</h2>
                <ul v-if="roomType.amenities && roomType.amenities.length > 0" class="feature-list list-unstyled">
                    <li v-for="amenity in roomType.amenities" :key="amenity.amenity_id">
                        <i class="bi bi-check-circle-fill"></i> {{ amenity.amenity_name }}
                    </li>
                </ul>
                <p v-else class="text-muted small">Chưa có thông tin tiện nghi.</p>
              </div>
            </div>
          </div>
        </main>

        <!-- Call to Action Section -->
        <section class="cta-section text-center mt-5 pt-5">
          <h2 class="mb-3">Sẵn sàng cho một kỳ nghỉ đáng nhớ?</h2>
          <p class="lead text-muted mb-4">Đặt phòng ngay hôm nay để nhận được những ưu đãi tốt nhất từ chúng tôi.</p>
          <router-link to="/rooms3" class="btn btn-primary btn-lg rounded-pill px-5 py-3 shadow-lg">
            <i class="bi bi-calendar-check me-2"></i> Đặt Phòng Ngay
          </router-link>
        </section>
      </div>
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
// PHẦN SCRIPT GIỮ NGUYÊN
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
    const response = await axiosConfig.get(`${apiUrl}/api/room-types/${typeId}`);
    if (response.data) {
      roomType.value = {
        ...response.data,
        images: response.data.images ? JSON.parse(response.data.images) : [],
      };
    } else {
      console.error(`Không tìm thấy loại phòng với ID: ${typeId}`);
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
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700;800&display=swap');
@import url("https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css");

/* --- General & Theme --- */
.page-content {
  font-family: 'Montserrat', sans-serif;
  background-color: #f8f9fa;
  color: #343a40;
}
.loading-container { min-height: 100vh; }
.room-title { color: #081B54; }
.room-subtitle { color: #6c757d; }

/* --- Carousel --- */
.carousel-section {
  border-radius: 20px;
  overflow: hidden;
}
.carousel-item {
  height: 65vh;
  min-height: 450px;
  max-height: 600px;
}
.carousel-item img {
  height: 100%;
  object-fit: cover;
}

/* --- Main Content Cards --- */
.detail-card {
  background-color: #fff;
  padding: 2.5rem;
  border-radius: 15px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.05);
  height: 100%;
}
.section-title {
  font-weight: 700;
  color: #081B54;
  margin-bottom: 1.5rem;
  padding-bottom: 0.75rem;
  border-bottom: 3px solid #16B978;
  display: inline-block;
}

/* --- Key Info Cards --- */
.info-card {
  background-color: #f8f9fa;
  border-radius: 12px;
  padding: 1.5rem 1rem;
  transition: all 0.3s ease;
  border: 1px solid #e9ecef;
}
.info-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 20px rgba(0,0,0,0.08);
  border-color: #16B978;
}
.info-icon {
  font-size: 2.5rem;
  color: #16B978;
  margin-bottom: 0.75rem;
  display: block;
}
.info-label {
  font-size: 0.9rem;
  color: #6c757d;
  margin-bottom: 0.25rem;
}
.info-value {
  font-size: 1.1rem;
  font-weight: 700;
  color: #081B54;
}

/* --- Feature List (Amenities/Services) --- */
.feature-list {
  padding-left: 0;
  /* SỬA Ở ĐÂY: Thêm columns để tự chia cột */
  columns: 2;
  -webkit-columns: 2;
  -moz-columns: 2;
  column-gap: 2rem;
}
.feature-list li {
  padding: 0.5rem 0;
  font-size: 1rem;
  color: #495057;
  display: flex;
  align-items: center;
  /* Ngăn ngắt cột giữa chừng của 1 item */
  -webkit-column-break-inside: avoid;
  page-break-inside: avoid;
  break-inside: avoid;
}
.feature-list li i {
  color: #16B978;
  margin-right: 0.75rem;
  font-size: 1.2rem;
}

/* --- Call to Action --- */
.cta-section {
  border-top: 1px solid #e9ecef;
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