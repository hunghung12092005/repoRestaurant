<template>
  <div class="hotel-page-container">
    <!-- 1. Banner chính -->
    <section class="hero-section d-flex align-items-center text-center">
      <div class="container">
          <div class="hero-content">
            <p class="sub-heading">Chào Mừng Đến Với Khách Sạn Của Chúng Tôi</p>
            <h1 class="main-heading">Trải Nghiệm Khách Sạn Sang Trọng</h1>
            <h1 class="main-heading">& Tiện Nghi & Thanh Lịch</h1>
            <p class="description">
              Lựa chọn Bokinn là một trong những quyết định tuyệt vời nhất của chúng tôi. Họ đã chứng tỏ mình là một đối tác đáng tin cậy và sáng tạo.
            </p>
            <a href="#" class="btn btn-outline-light px-4 py-2">Khám Phá Phòng</a>
          </div>
      </div>
    </section>

    <!-- 2. Giới thiệu -->
    <section class="about-section bg-white">
      <div class="container">
        <div class="row align-items-center g-5">
          <div class="col-lg-6">
            <p class="section-subtitle text-start">Về Chúng Tôi</p>
            <h2 class="section-title text-start">Chào Mừng Tới Khách Sạn Hồ Xuân Hương</h2>
            <p class="section-text text-start">
              Chào mừng đến với Hồ Xuân Hương Ecosystem, nơi sự sang trọng gặp gỡ tiện nghi tại trung tâm Canada. Từ năm 1999, chúng tôi đã cống hiến để mang đến một kỳ nghỉ đặc biệt cho quý khách, kết hợp các tiện nghi hiện đại với sự thanh lịch vượt thời gian. Các phòng và suite được thiết kế đẹp mắt của chúng tôi có tầm nhìn tuyệt đẹp và chỗ ở sang trọng, đảm bảo một nơi nghỉ ngơi thư thái dù bạn ở đây để công tác hay giải trí.
            </p>
            <a href="#" class="btn btn-custom-secondary">Tìm Hiểu Thêm</a>
          </div>
          <div class="col-lg-6">
            <div class="about-image">
              <img src="https://images.trvl-media.com/lodging/42000000/41830000/41826900/41826852/ff9dfc7c.jpg?impolicy=resizecrop&rw=575&rh=575&ra=fill" class="img-fluid rounded" alt="Nội thất khách sạn" />
              <div class="staff-card card shadow">
                <div class="d-flex align-items-center">
                  <span class="staff-icon me-3">👥</span>
                  <p class="mb-0"><strong>50+</strong><br />Nhân Viên Kinh Nghiệm</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- 3. Tiện nghi khách sạn -->
    <section class="facilities-section">
      <div class="container">
        <p class="section-subtitle">Tiện Nghi</p>
        <h2 class="section-title">Tiện Nghi Khách Sạn</h2>
        <div class="row g-4 mt-4">
          <div class="col-md-6 col-lg-3">
            <div class="card facility-card h-100 text-center p-4">
                <div class="facility-icon">🏨</div>
                <h3>Phòng & Suite</h3>
                <p class="mb-0">Đa dạng các loại phòng, từ tiêu chuẩn đến suite sang trọng, được trang bị đầy đủ tiện nghi thiết yếu.</p>
            </div>
          </div>
          <div class="col-md-6 col-lg-3">
            <div class="card facility-card h-100 text-center p-4">
                <div class="facility-icon">🛡️</div>
                <h3>An Ninh 24/7</h3>
                <p class="mb-0">Nhân viên an ninh tại chỗ và hệ thống giám sát tốt nhất, đảm bảo an toàn tuyệt đối cho quý khách.</p>
            </div>
          </div>
          <div class="col-md-6 col-lg-3">
            <div class="card facility-card h-100 text-center p-4">
                <div class="facility-icon">🏋️</div>
                <h3>Trung Tâm Thể Hình</h3>
                <p class="mb-0">Trang bị máy tập và tạ hiện đại. Cung cấp các dịch vụ massage, chăm sóc da mặt và trị liệu khác.</p>
            </div>
          </div>
          <div class="col-md-6 col-lg-3">
            <div class="card facility-card h-100 text-center p-4">
                <div class="facility-icon">🏊</div>
                <h3>Hồ Bơi</h3>
                <p class="mb-0">Hồ bơi trong nhà và ngoài trời để giải trí hoặc tập luyện. Cung cấp dịch vụ massage và trị liệu.</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- 4. Các loại phòng (KẾT NỐI DỮ LIỆU ĐỘNG) -->
    <section class="rooms-section bg-white">
      <div class="container">
        <div class="row align-items-center mb-5">
          <div class="col-lg-5">
            <p class="section-subtitle text-start">Phòng</p>
            <h2 class="section-title text-start">Các Loại Phòng Của Chúng Tôi</h2>
          </div>
          <div class="col-lg-7">
            <p class="section-text text-start rooms-description">
              Các phòng của chúng tôi là sự pha trộn hài hòa giữa tiện nghi và thanh lịch, được thiết kế để mang lại một kỳ nghỉ đặc biệt cho mọi du khách. Mỗi phòng đều có bộ khăn trải giường cao cấp và nhiều lựa chọn gối để đảm bảo một giấc ngủ ngon.
            </p>
          </div>
        </div>
        
        <div class="row g-4">
          <!-- Sử dụng v-for để lặp qua danh sách các loại phòng -->
          <div v-for="(roomType, index) in roomTypes" :key="roomType.type_id" class="col-lg-4 col-md-6">
            <div class="room-card">
              <img :src="getRoomImage(index)" class="img-fluid" :alt="roomType.type_name" />
              <div class="room-info">
                <div>
                  <h3>{{ roomType.type_name }}</h3>
                  <div class="room-details">
                    <span>📏 {{ roomType.m2 }} m²</span>
                    <span>🛏️ {{ roomType.bed_count }} Giường</span>
                    <span>👤 {{ roomType.max_occupancy }} Người</span>
                  </div>
                </div>
                <div class="room-rate">
                  <span>{{ roomType.rate }}</span>
                  <span style="color: #f5b942; margin-left: 4px;">★</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Hiển thị thông báo khi đang tải hoặc không có dữ liệu -->
          <div v-if="loading" class="col-12 text-center py-5">
            <p>Đang tải dữ liệu phòng...</p>
          </div>
          <div v-if="!roomTypes.length && !loading" class="col-12 text-center py-5">
            <p>Hiện không có loại phòng nào để hiển thị.</p>
          </div>
        </div>
        
        <div class="pagination-dots text-center mt-5">
          <span class="dot active"></span><span class="dot"></span><span class="dot"></span><span class="dot"></span>
        </div>
      </div>
    </section>

    <!-- 5. Banner tĩnh -->
    <section class="static-banner-section">
        <div class="banner-content text-center text-white">
            <h2 class="display-4 fw-bold">Không Gian Đẳng Cấp</h2>
            <p class="lead">Tận hưởng sự thư giãn và dịch vụ hoàn hảo tại khách sạn của chúng tôi.</p>
            <a href="#" class="btn btn-outline-light btn-lg mt-3">Xem Dịch Vụ</a>
        </div>
    </section>

    <!-- 6. Ưu đãi đặc biệt -->
    <section class="offers-section bg-white">
      <div class="container">
        <div class="row align-items-center mb-5">
            <div class="col-lg-5">
                <p class="section-subtitle text-start">Ưu Đãi Đặc Biệt</p>
                <h2 class="section-title text-start">Ưu Đãi Đặc Biệt</h2>
            </div>
            <div class="col-lg-7">
              <p class="section-text text-start offers-description">
                  Trải nghiệm sự sang trọng và thư giãn tột đỉnh với các gói ưu đãi độc quyền của chúng tôi! Đặt phòng ngay hôm nay để nhận những quyền lợi đặc biệt.
              </p>
            </div>
        </div>
        <div class="row g-4">
          <div class="col-lg-6">
            <div class="offer-card d-flex flex-column flex-md-row">
              <img src="https://images.trvl-media.com/lodging/42000000/41830000/41826900/41826852/ff9dfc7c.jpg?impolicy=resizecrop&rw=575&rh=575&ra=fill" class="offer-image" alt="Gói Gia Đình">
              <div class="offer-content d-flex flex-column p-4">
                <h3>Gói Vui Vẻ Gia Đình</h3>
                <ul class="list-unstyled my-3">
                  <li class="d-flex align-items-start mb-2"><span class="me-2" style="color: #A98A66;">✓</span><span>Lưu trú tại phòng Suite Gia đình rộng rãi</span></li>
                  <li class="d-flex align-items-start mb-2"><span class="me-2" style="color: #A98A66;">✓</span><span>Miễn phí bữa ăn cho trẻ em dưới 12 tuổi</span></li>
                  <li class="d-flex align-items-start mb-2"><span class="me-2" style="color: #A98A66;">✓</span><span>Vé vào cửa công viên giải trí miễn phí</span></li>
                  <li class="d-flex align-items-start mb-2"><span class="me-2" style="color: #A98A66;">✓</span><span>Hoạt động gia đình hàng ngày</span></li>
                </ul>
                <a href="#" class="book-now-link mt-auto">Đặt Ngay</a>
              </div>
            </div>
          </div>
          <div class="col-lg-6">
            <div class="offer-card d-flex flex-column flex-md-row">
              <img src="https://images.trvl-media.com/lodging/42000000/41830000/41826900/41826852/ff9dfc7c.jpg?impolicy=resizecrop&rw=575&rh=575&ra=fill" class="offer-image" alt="Gói Spa">
              <div class="offer-content d-flex flex-column p-4">
                <h3>Gói Thư Giãn Spa</h3>
                <ul class="list-unstyled my-3">
                    <li class="d-flex align-items-start mb-2"><span class="me-2" style="color: #A98A66;">✓</span><span>Hai đêm nghỉ tại phòng premium</span></li>
                    <li class="d-flex align-items-start mb-2"><span class="me-2" style="color: #A98A66;">✓</span><span>Tín dụng Spa trị giá 100$</span></li>
                    <li class="d-flex align-items-start mb-2"><span class="me-2" style="color: #A98A66;">✓</span><span>Bữa sáng và bữa trưa lành mạnh</span></li>
                    <li class="d-flex align-items-start mb-2"><span class="me-2" style="color: #A98A66;">✓</span><span>Sử dụng tất cả các tiện ích spa</span></li>
                </ul>
                <a href="#" class="book-now-link mt-auto">Đặt Ngay</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- 7. Thư viện ảnh -->
    <section class="gallery-section">
      <div class="container">
        <p class="section-subtitle">Thư Viện Ảnh</p>
        <h2 class="section-title">Thư Viện Ảnh Của Chúng Tôi</h2>
        <div class="row g-3 mt-4">
          <div class="col-lg-3 col-md-6 col-6"><img src="https://images.trvl-media.com/lodging/42000000/41830000/41826900/41826852/ff9dfc7c.jpg?impolicy=resizecrop&rw=575&rh=575&ra=fill" class="img-fluid rounded" alt="Ảnh thư viện 1"></div>
          <div class="col-lg-3 col-md-6 col-6"><img src="https://images.trvl-media.com/lodging/42000000/41830000/41826900/41826852/ff9dfc7c.jpg?impolicy=resizecrop&rw=575&rh=575&ra=fill" class="img-fluid rounded" alt="Ảnh thư viện 2"></div>
          <div class="col-lg-3 col-md-6 col-6"><img src="https://images.trvl-media.com/lodging/42000000/41830000/41826900/41826852/ff9dfc7c.jpg?impolicy=resizecrop&rw=575&rh=575&ra=fill" class="img-fluid rounded" alt="Ảnh thư viện 3"></div>
          <div class="col-lg-3 col-md-6 col-6"><img src="https://images.trvl-media.com/lodging/42000000/41830000/41826900/41826852/ff9dfc7c.jpg?impolicy=resizecrop&rw=575&rh=575&ra=fill" class="img-fluid rounded" alt="Ảnh thư viện 4"></div>
        </div>
      </div>
    </section>

    <!-- 8. Đăng ký nhận tin -->
    <section class="newsletter-section bg-white">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-lg-7 text-center">
            <h2 class="section-title">Đăng Ký Nhận Tin</h2>
            <form class="d-flex gap-2 mt-4">
                <input type="email" class="form-control form-control-lg" placeholder="Nhập email của bạn">
                <button type="submit" class="btn btn-custom-secondary btn-lg flex-shrink-0">Đăng Ký</button>
            </form>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const roomTypes = ref([]);
const loading = ref(true);

const staticImages = [
  'https://images.trvl-media.com/lodging/42000000/41830000/41826900/41826852/ff9dfc7c.jpg?impolicy=resizecrop&rw=575&rh=575&ra=fill',
  
];

const getRoomImage = (index) => {
  return staticImages[index % staticImages.length];
};

const fetchRoomTypes = async () => {
  // !!! QUAN TRỌNG: Thay thế URL này bằng URL API thực tế của bạn
  const apiUrl = 'http://127.0.0.1:8000/api/room-types';
  loading.value = true;
  
  try {
    const response = await axios.get(apiUrl);
    if (response.data && response.data.status === true) {
      roomTypes.value = response.data.data;
    } else {
      console.error('API không trả về dữ liệu hợp lệ:', response.data);
      roomTypes.value = []; // Xóa dữ liệu cũ nếu API lỗi
    }
  } catch (error) {
    console.error('Lỗi khi gọi API lấy danh sách loại phòng:', error);
    roomTypes.value = []; // Xóa dữ liệu cũ nếu API lỗi
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchRoomTypes();
});
</script>

<style scoped>
/* GENERAL & CUSTOM FONT/COLOR STYLES */
body, .hotel-page-container {
  font-family: 'Helvetica', 'Arial', sans-serif;
  background-color: #f8f9fa;
  overflow-x: hidden;
}

section {
  padding-top: 80px;
  padding-bottom: 80px;
}

.section-subtitle {
  color: #A98A66;
  font-family: 'Georgia', 'Times New Roman', serif;
}

.section-title, h3 {
  font-family: 'Georgia', 'Times New Roman', serif;
}

.section-title {
  font-size: 2.5rem;
}

.section-text {
  color: #555;
  line-height: 1.7;
}

/* CUSTOM BUTTON */
.btn-custom-secondary {
  background-color: #A98A66;
  color: #fff;
  border-color: #A98A66;
}
.btn-custom-secondary:hover {
  background-color: #937451;
  border-color: #937451;
  color: #fff;
}
.btn-outline-light:hover {
    color: #333;
}

/* 1. HERO SECTION */
.hero-section {
  min-height: 80vh;
  background-image: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), url('https://a25hotel.com/files/images/screenshot_1664443543.png');
  background-size: cover;
  background-position: center;
  color: #fff;
  position: relative;
  padding-bottom: 80px;
}

.hero-content .main-heading {
  font-family: 'Georgia', 'Times New Roman', serif;
  font-size: 4rem;
  font-weight: normal;
}

/* 2. ABOUT SECTION */
.about-section {
  padding-top: 80px;
}
.about-image { position: relative; }
.staff-card {
  position: absolute;
  bottom: 20px;
  right: -20px;
  padding: 1rem;
  border: none;
  border-radius: 8px;
}
.staff-card .staff-icon { font-size: 2rem; }

/* 3. FACILITIES SECTION */
.facilities-section { background-color: #f1f1f1; }
.facility-card {
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  border: none;
  background-color: #fff;
  border-radius: 8px;
}
.facility-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 .5rem 1rem rgba(0,0,0,.15) !important;
}
.facility-card .facility-icon {
  font-size: 3rem;
  color: #A98A66;
  margin-bottom: 1rem;
}

/* 4. ROOM CARD */
.room-card {
  position: relative;
  overflow: hidden;
  border-radius: var(--bs-border-radius);
  box-shadow: var(--bs-box-shadow-sm);
  height: 100%;
}
.room-card img {
  width: 100%;
  height: 250px;
  object-fit: cover;
}
.room-info {
  position: absolute; bottom: 0; left: 0; right: 0;
  background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
  color: #fff; padding: 1.5rem; display: flex;
  justify-content: space-between; align-items: flex-end;
}
.room-info h3 { font-size: 1.4rem; color: white;}
.room-details { font-size: 0.8rem; opacity: 0.9; }
.room-rate { 
  font-size: 1.8rem; 
  font-weight: bold;
  display: flex;
  align-items: center;
}
.pagination-dots .dot {
  display: inline-block; width: 10px; height: 10px; border-radius: 50%;
  background-color: #ccc; margin: 0 5px; cursor: pointer;
}
.pagination-dots .dot.active { background-color: #A98A66; }

/* 5. STATIC BANNER SECTION */
.static-banner-section {
    height: 500px;
    background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('https://images.trvl-media.com/lodging/42000000/41830000/41826900/41826852/ff9dfc7c.jpg?impolicy=resizecrop&rw=575&rh=575&ra=fill');
    background-size: cover;
    background-position: center;
    background-attachment: fixed; /* Parallax effect */
    display: flex;
    justify-content: center;
    align-items: center;
}
.banner-content {
    max-width: 800px;
}

/* 6. OFFER CARD */
.offer-card {
    background: #f9f9f9;
    border-radius: var(--bs-border-radius);
    overflow: hidden;
    box-shadow: var(--bs-box-shadow-sm);
}
.offer-image { object-fit: cover; }
@media (min-width: 768px) { .offer-image { width: 40%; } }
@media (max-width: 767px) { .offer-image { width: 100%; height: 200px; } }
.book-now-link {
    color: #A98A66; text-decoration: none; font-weight: bold;
    border-bottom: 2px solid transparent; transition: border-color 0.3s;
    align-self: flex-start;
}
.book-now-link:hover { border-color: #A98A66; }

/* RESPONSIVE ADJUSTMENTS */
@media (max-width: 992px) {
    .about-section {
        padding-top: 80px;
    }
}
@media (max-width: 768px) {
    .main-heading { font-size: 3rem !important; }
    .section-title { font-size: 2rem; }
    .section-subtitle, .section-title, .section-text { text-align: center !important; }
}
</style>