<template>
  <div class="about-us-page">
    <!-- Section 1: Hero Banner -->
    <section class="hero-section d-flex align-items-center justify-content-center text-center text-white">
      <div class="hero-content">
        <h1 class="display-3 fw-bold">Giới Thiệu</h1>
        <p class="lead">Chào mừng đến với Hồ Xuân Hương, nơi tiện nghi và thư giãn bên bờ biển Sầm Sơn.</p>
      </div>
    </section>

    <!-- Section 2: Welcome -->
    <section class="welcome-section py-5">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-lg-6 mb-4 mb-lg-0">
            <div class="image-stack">
              <!-- Thay thế bằng ảnh thực tế của biển Sầm Sơn -->
              <img src="https://treviettours.com.vn/wp-content/uploads/2020/06/getlstd-property-photo.jpg" alt="Bãi biển Sầm Sơn" class="img-fluid rounded shadow-lg image-back">
              <!-- Thay thế bằng ảnh thực tế của khách sạn -->
              <img src="https://images.unsplash.com/photo-1566073771259-6a8506099945?q=80&w=2070" alt="Khách sạn Hồ Xuân Hương" class="img-fluid rounded shadow-lg image-front">
            </div>
          </div>
          <div class="col-lg-6">
            <h5 class="text-primary fw-bold">VỀ CHÚNG TÔI</h5>
            <h2 class="display-5 mb-4 serif-font">Chào Mừng Đến Với Khách Sạn Hồ Xuân Hương</h2>
            <p class="text-muted">Tọa lạc tại vị trí đắc địa ven biển Sầm Sơn, khách sạn 3 sao Hồ Xuân Hương tự hào mang đến cho quý khách một kỳ nghỉ dưỡng đặc biệt. Chúng tôi kết hợp hoàn hảo giữa tiện nghi hiện đại và vẻ đẹp thanh lịch. Các phòng được thiết kế tinh xảo, tầm nhìn hướng biển tuyệt đẹp và nội thất ấm cúng, đảm bảo một không gian nghỉ ngơi thư thái dù bạn đang đi công tác hay du lịch.</p>
            <div class="signature mt-4">Hồ Xuân Hương</div>
          </div>
        </div>
      </div>
    </section>

    <!-- Section 3: Facilities -->
    <section class="facilities-section py-5 bg-light">
      <div class="container">
        <div class="text-center mb-5">
          <h5 class="text-primary fw-bold">TRẢI NGHIỆM</h5>
          <h2 class="display-5 serif-font">Tiện Ích Khách Sạn</h2>
        </div>
        <div class="row align-items-center">
          <div class="col-lg-7">
            <div class="row">
              <div v-for="facility in facilities" :key="facility.title" class="col-md-6 mb-4">
                <div class="d-flex">
                  <div class="facility-icon me-3">
                    <i :class="facility.icon" class="fs-2 text-primary"></i>
                  </div>
                  <div>
                    <h4 class="h5">{{ facility.title }}</h4>
                    <p class="text-muted">{{ facility.description }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-5 d-none d-lg-block">
            <!-- Thay thế bằng ảnh thực tế của phòng khách sạn -->
            <img src="https://images.unsplash.com/photo-1611892440504-42a792e24d32?q=80&w=2070" class="img-fluid rounded shadow-sm" alt="Phòng khách sạn">
          </div>
        </div>
      </div>
    </section>

    <!-- Section 4: Team -->
    <section class="team-section py-5">
      <div class="container">
        <div class="text-center mb-5">
          <h5 class="text-primary fw-bold">ĐỘI NGŨ</h5>
          <h2 class="display-sf serif-font">Gặp Gỡ Đội Ngũ Của Chúng Tôi</h2>
        </div>
        <!-- Sử dụng row-cols để tạo 5 cột trên màn hình lớn -->
        <div class="row row-cols-1 row-cols-md-3 row-cols-lg-5 g-4">
          <div v-for="member in team" :key="member.name" class="col">
            <div class="card h-100 border-0 shadow-sm text-center team-card">
              <!-- Sử dụng ảnh chân dung thực tế -->
              <img :src="member.image" class="card-img-top" :alt="member.name">
              <div class="card-body">
                <h5 class="card-title">{{ member.name }}</h5>
                <p class="card-text text-muted">{{ member.role }}</p>
                <div class="social-icons">
                  <a href="#" class="text-secondary mx-1"><i class="bi bi-facebook"></i></a>
                  <a href="#" class="text-secondary mx-1"><i class="bi bi-linkedin"></i></a>
                  <a href="#" class="text-secondary mx-1"><i class="bi bi-twitter-x"></i></a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

  </div>
</template>

<script setup>
import { ref } from 'vue';

const facilities = ref([
  {
    title: 'Phòng và Suites',
    description: 'Nhiều loại phòng đa dạng, từ tiêu chuẩn đến cao cấp, được trang bị đầy đủ tiện nghi.',
    icon: 'bi bi-door-closed-fill'
  },
  {
    title: 'An Ninh 24/7',
    description: 'Đội ngũ an ninh chuyên nghiệp và hệ thống giám sát hiện đại, đảm bảo an toàn tuyệt đối.',
    icon: 'bi bi-shield-lock-fill'
  },
  {
    title: 'Dịch Vụ Tour Du Lịch',
    description: 'Hỗ trợ đặt tour tham quan các địa danh nổi tiếng tại Sầm Sơn và khu vực lân cận.',
    icon: 'bi bi-map-fill'
  },
  {
    title: 'Bể Bơi Ngoài Trời',
    description: 'Bể bơi với tầm nhìn ra biển, lý tưởng để thư giãn và giải trí dưới ánh nắng.',
    icon: 'bi bi-water'
  }
]);

// Cập nhật danh sách đội ngũ lên 5 người
const team = ref([
  {
    name: 'Nguyễn Văn Anh',
    role: 'Tổng Quản Lý',
    image: 'https://i.pravatar.cc/300?u=manager'
  },
  {
    name: 'Trần Thị Bích',
    role: 'Trợ Lý Quản Lý',
    image: 'https://i.pravatar.cc/300?u=assistant'
  },
  {
    name: 'Lê Minh Hải',
    role: 'Trưởng Phòng Lễ Tân',
    image: 'https://i.pravatar.cc/300?u=reception'
  },
  {
    name: 'Phạm Thu Hà',
    role: 'Quản Lý Chất Lượng',
    image: 'https://i.pravatar.cc/300?u=quality'
  },
  {
    name: 'Hoàng Thị Lan',
    role: 'Trưởng Bếp',
    image: 'https://i.pravatar.cc/300?u=chef'
  }
]);
</script>

<style scoped>
/* Import font chữ serif đẹp cho tiêu đề */
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Roboto:wght@400&display=swap');

.serif-font {
  font-family: 'Playfair Display', serif;
}

.about-us-page {
  font-family: 'Roboto', sans-serif;
  color: #333;
}

/* Hero Section */
.hero-section {
  position: relative;
  height: 60vh;
  background: url('https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?q=80&w=2070') no-repeat center center;
  background-size: cover;
}

.hero-section::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
}

.hero-content {
  position: relative;
  z-index: 1;
}

/* Welcome Section */
.image-stack {
  position: relative;
  min-height: 450px;
}
.image-stack .image-back {
  position: absolute;
  top: 0;
  left: 0;
  width: 80%;
  z-index: 1;
}
.image-stack .image-front {
  position: absolute;
  bottom: 0;
  right: 0;
  width: 70%;
  border: 8px solid white;
  z-index: 2;
}
.signature {
  font-family: 'Playfair Display', cursive;
  font-size: 2.5rem;
  color: #a07d5a;
}
.text-primary {
  color: #a07d5a !important;
}

/* Facilities Section */
.facilities-section {
  background-color: #fdfaf6 !important;
}

.facility-icon {
  flex-shrink: 0;
}

/* Team Section */
.team-card {
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.team-card:hover {
  transform: translateY(-10px);
  box-shadow: 0 1rem 3rem rgba(0,0,0,.175)!important;
}
.social-icons a {
  transition: color 0.3s ease;
}
.social-icons a:hover {
  color: #a07d5a !important;
}
</style>