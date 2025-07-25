<template>
  <div>
    <!-- Loading State -->
    <div v-if="loading" class="loading-container">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
    </div>

    <!-- Content State -->
    <div v-else-if="roomType" class="page-content">
      <!-- [THAY ĐỔI] Header Section - Chứa thông tin chữ, chia 2 cột -->
      <header class="page-header">
        <div class="container">
          <div class="row align-items-center gy-3">
            <!-- Cột trái: Tiêu đề và mô tả -->
            <div class="col-lg-8 col-md-7">
              <h1 class="header-title">{{ roomType.type_name }}</h1>
              <p class="header-subtitle">
                Trải nghiệm không gian nghỉ dưỡng đẳng cấp và tiện nghi vượt trội bên bờ biển.
              </p>
            </div>
            <!-- Cột phải: Nút Đặt phòng -->
            <div class="col-lg-4 col-md-5 text-md-end text-center">
              <router-link to="/rooms3" class="btn btn-primary btn-lg rounded-pill px-5 py-3 shadow">
                <i class="bi bi-calendar-check me-2"></i> Đặt Phòng Ngay
              </router-link>
            </div>
          </div>
        </div>
      </header>

      <!-- Featured Image Section - Chứa ảnh chính -->
      <section class="featured-image-section">
        <div class="container">
          <img :src="featuredImage" :alt="roomType.type_name" class="featured-image">
        </div>
      </section>

      <div class="container py-5">
        <!-- Main Details Section -->
        <main class="main-content">
          <div class="row g-4 g-lg-5">
            <!-- Left Column: Description & Key Info -->
            <div class="col-lg-7">
              <div class="detail-card">
                <h2 class="section-title">Tổng Quan Về Hạng Phòng</h2>
                <p class="text-muted fs-5 mb-5">{{ roomType.description }}</p>

                <div class="row g-3 text-center">
                  <div class="col-sm-4">
                    <div class="info-card">
                      <i class="bi bi-people-fill info-icon"></i>
                      <div class="info-label">Sức chứa tối đa</div>
                      <div class="info-value">{{ roomType.max_occupancy }} người</div>
                    </div>
                  </div>
                  <div class="col-sm-4">
                    <div class="info-card">
                      <i class="bi bi-aspect-ratio-fill info-icon"></i>
                      <div class="info-label">Diện tích</div>
                      <div class="info-value">{{ roomType.area || 'N/A' }} m²</div>
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

        <!-- Image Gallery Section -->
        <section class="gallery-section mt-5 pt-4" v-if="roomType.images && roomType.images.length > 1">
          <h2 class="section-title text-center mb-5">Thư Viện Ảnh</h2>
          <div class="image-grid">
            <div 
              v-for="(image, index) in roomType.images.slice(1)" 
              :key="index" 
              class="grid-item"
            >
              <img :src="`${apiUrl}/images/room_type/${image}`" :alt="`Ảnh phòng ${index + 2}`" class="img-fluid">
            </div>
          </div>
        </section>

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
    <div v-else class="loading-container">
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
// Script không thay đổi, bạn có thể giữ nguyên
import { ref, onMounted, inject, watch, computed } from 'vue';
import { useRoute } from 'vue-router';
import axiosConfig from '../axiosConfig.js';

const route = useRoute();
const roomType = ref(null);
const loading = ref(true);
const apiUrl = inject('apiUrl');

const featuredImage = computed(() => {
  if (roomType.value && roomType.value.images && roomType.value.images.length > 0) {
    return `${apiUrl}/images/room_type/${roomType.value.images[0]}`;
  }
  return 'https://via.placeholder.com/1200x600?text=Premium+Room';
});

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
        area: response.data.area || response.data.m2 
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
/* Bạn chỉ cần thay thế phần CSS này */
@import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap');
@import url("https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css");

/* --- Theme & General --- */
.page-content {
  font-family: 'Nunito', sans-serif;
  background-color: #F9F7F3;
  color: #343a40;
}
.loading-container {
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  background-color: #F9F7F3;
}

/* --- [CẬP NHẬT] Page Header Section --- */
.page-header {
  background-color: #ffffff;
  padding: 4rem 0;
  border-bottom: 1px solid #e9ecef;
}
.header-title {
  font-size: 3.5rem;
  font-weight: 800;
  color: #0A2463; /* Xanh navy đậm */
  margin-bottom: 0.5rem; /* Giảm margin bottom */
}
.header-subtitle {
  font-size: 1.25rem;
  color: #6c757d;
  margin-bottom: 0; /* Bỏ margin bottom */
}


/* --- Featured Image Section --- */
.featured-image-section {
  padding-top: 2rem;
  padding-bottom: 2rem;
  background-color: #F9F7F3; /* Đảm bảo nền nhất quán */
}
.featured-image {
  width: 100%;
  height: auto;
  max-height: 600px;
  object-fit: cover;
  border-radius: 1rem;
  box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
}

/* --- Main Content & Cards --- */
.detail-card {
  background-color: #fff;
  padding: 2.5rem;
  border-radius: 15px;
  box-shadow: 0 8px 25px rgba(0,0,0,0.05);
  border: 1px solid #e9ecef;
  height: 100%;
}
.section-title {
  font-weight: 800;
  color: #0A2463;
  margin-bottom: 1.5rem;
  padding-bottom: 0.75rem;
  border-bottom: 3px solid #3D99A5;
  display: inline-block;
}
.section-title.text-center {
  display: block;
  width: fit-content;
  margin-left: auto;
  margin-right: auto;
}

/* --- Info Cards --- */
.info-card {
  background-color: #f8f9fa;
  border-radius: 12px;
  padding: 1.5rem 1rem;
  border: 1px solid #e9ecef;
  transition: all 0.3s ease;
}
.info-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 5px 15px rgba(0,0,0,0.07);
}
.info-icon {
  font-size: 2.5rem;
  color: #3D99A5;
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
  color: #0A2463;
}

/* --- Feature List (Amenities) --- */
.feature-list {
  columns: 2;
  -webkit-columns: 2;
  -moz-columns: 2;
  column-gap: 2rem;
}
.feature-list li {
  padding: 0.5rem 0;
  font-size: 1rem;
  display: flex;
  align-items: center;
  break-inside: avoid;
}
.feature-list li i {
  color: #3D99A5;
  margin-right: 0.75rem;
  font-size: 1.2rem;
}

/* --- Image Gallery Grid --- */
.image-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1rem;
}
.grid-item {
  overflow: hidden;
  border-radius: 12px;
  box-shadow: 0 5px 15px rgba(0,0,0,0.1);
  aspect-ratio: 4 / 3;
}
.grid-item img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.4s ease;
}
.grid-item:hover img {
  transform: scale(1.05);
}

/* --- CTA & Buttons --- */
.cta-section {
  border-top: 1px solid #e9ecef;
}
.btn-primary {
  background-color: #3D99A5;
  border-color: #3D99A5;
  font-weight: 700;
  transition: all 0.3s ease;
}
.btn-primary:hover {
  background-color: #2f7a85;
  border-color: #2f7a85;
  transform: translateY(-3px);
  box-shadow: 0 4px 15px rgba(61, 153, 165, 0.4);
}
</style>