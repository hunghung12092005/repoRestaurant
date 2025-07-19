<template>
  <div class="not-found-container">
    <div class="overlay-gradient"></div>
    <div class="content-wrapper">
      <img src="https://vcdn1-dulich.vnecdn.net/2022/07/29/hypat-1659069584-1659081355.jpg?w=460&h=0&q=100&dpr=1&fit=crop&s=3I9-DXsUwSBDQeq8oF_LgA" alt="Hotel Logo" class="hotel-logo">
      <h1>Oops! Trang này không tồn tại.</h1>
      <p>Có vẻ như bạn đã đi lạc khỏi khu nghỉ dưỡng của chúng tôi.</p>
      <p>Mã lỗi: <span class="error-code">404 - Not Found</span></p>
      <p>
        Hãy để chúng tôi hướng dẫn bạn trở lại:
        <router-link to="/" class="home-link">Về Trang Chủ</router-link>
        hoặc <a href="hxh@hotel.com" class="contact-link">Liên Hệ Hỗ Trợ</a>
      </p>
      <div class="suggestions">
        <p>Bạn có thể thử:</p>
        <ul>
          <li><router-link to="/rooms">Khám phá các loại phòng của chúng tôi</router-link></li>
          <li><router-link to="/news">Đọc tin tức và ưu đãi mới nhất</router-link></li>
          <li><router-link to="/contact">Tìm thông tin liên hệ</router-link></li>
        </ul>
      </div>
      <p class="copyright">&copy; {{ currentYear }} [HXH HOTEL]. All Rights Reserved.</p>
    </div>
  </div>
</template>

<script>
export default {
  name: 'NotFound',
  data() {
    return {
      currentYear: new Date().getFullYear()
    };
  }
}
</script>

<style scoped>
/* Google Fonts - chọn font thanh lịch, sang trọng */
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Open+Sans:wght@300;400;600&display=swap');

html, body {
  margin: 0;
  padding: 0;
  height: 100%; /* Đảm bảo body và html chiếm toàn bộ chiều cao */
  overflow: hidden; /* Ngăn cuộn trang */
}

.not-found-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh; /* Chiếm toàn bộ chiều cao viewport */
  background-image: url('https://i.imgur.com/your_hotel_background_image.jpg'); /* Thay bằng ảnh nền khách sạn của bạn */
  background-size: cover;
  background-position: center;
  color: #fff; /* Màu chữ tổng thể */
  text-align: center;
  position: relative;
  font-family: 'Open Sans', sans-serif; /* Font chữ chính */
  overflow: hidden;
}

.overlay-gradient {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.8)); /* Lớp phủ làm tối ảnh nền */
  z-index: 1;
}

.content-wrapper {
  position: relative;
  z-index: 2;
  padding: 2rem;
  max-width: 800px;
  background-color: rgba(0, 0, 0, 0.7); /* Nền mờ cho nội dung */
  border-radius: 10px;
  box-shadow: 0 0 30px rgba(0, 0, 0, 0.5);
  animation: fadeIn 1s ease-out; /* Hiệu ứng xuất hiện */
}

.hotel-logo {
  max-width: 150px;
  margin-bottom: 2rem;
  filter: drop-shadow(0 0 5px rgba(255, 255, 255, 0.3)); /* Hiệu ứng đổ bóng cho logo */
}

h1 {
  font-family: 'Playfair Display', serif; /* Font chữ tiêu đề sang trọng */
  font-size: 3.5rem;
  margin-bottom: 1rem;
  color: #FFD700; /* Màu vàng đồng hoặc màu nhấn của khách sạn */
  text-shadow: 0 0 10px rgba(255, 215, 0, 0.5);
}

p {
  font-size: 1.2rem;
  line-height: 1.6;
  margin-bottom: 1rem;
  color: rgba(255, 255, 255, 0.9);
}

.error-code {
  font-weight: bold;
  color: #e74c3c; /* Màu đỏ cho lỗi */
  background-color: rgba(255, 255, 255, 0.1);
  padding: 0.2em 0.5em;
  border-radius: 5px;
}

.home-link, .contact-link {
  color: #FFD700; /* Màu vàng đồng */
  text-decoration: none;
  font-weight: bold;
  transition: color 0.3s ease, text-shadow 0.3s ease;
}

.home-link:hover, .contact-link:hover {
  color: #ffe066; /* Sáng hơn khi hover */
  text-shadow: 0 0 8px rgba(255, 215, 0, 0.7);
}

.suggestions {
  margin-top: 2rem;
  padding-top: 1.5rem;
  border-top: 1px solid rgba(255, 255, 255, 0.2);
}

.suggestions p {
  font-size: 1.1rem;
  margin-bottom: 0.8rem;
  font-style: italic;
}

.suggestions ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.suggestions li {
  margin-bottom: 0.5rem;
}

.suggestions a {
  color: #87CEEB; /* Màu xanh nhẹ, dễ chịu */
  text-decoration: none;
  transition: color 0.3s ease;
}

.suggestions a:hover {
  color: #b0e0fe;
  text-decoration: underline;
}

.copyright {
  margin-top: 3rem;
  font-size: 0.9rem;
  color: rgba(255, 255, 255, 0.6);
}

/* Animations */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Responsive adjustments */
@media (max-width: 768px) {
  h1 {
    font-size: 2.5rem;
  }
  p {
    font-size: 1rem;
  }
  .content-wrapper {
    padding: 1.5rem;
  }
  .hotel-logo {
    max-width: 120px;
  }
}

@media (max-width: 480px) {
  h1 {
    font-size: 2rem;
  }
  p {
    font-size: 0.9rem;
  }
  .content-wrapper {
    padding: 1rem;
  }
}
</style>