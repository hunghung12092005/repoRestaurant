<!-- src/components/Footer.vue -->
<template>
  <footer class="main-footer">
    <!-- 1. Phần Bản đồ -->
    <section class="map-section">
      <iframe
        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15024.18021434927!2d105.89201174249156!3d19.70997184288673!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31365027b432b0c3%3A0x66f44923f6d76412!2zU-G6p20gU8ahbiwgVGhhbmggSMOzYSwgVmnhu4d0IE5hbQ!5e0!3m2!1svi!2s!4v1689844123456!5m2!1svi!2s"
        width="100%"
        height="400"
        style="border:0;"
        allowfullscreen=""
        loading="lazy"
        referrerpolicy="no-referrer-when-downgrade">
      </iframe>
    </section>

    <!-- 2. Phần Nội dung chính -->
    <div class="footer-content container">
      <!-- Bố cục được cập nhật thành 3 cột -->
      <div class="row g-5">
        <!-- Cột 1: Giới thiệu -->
        <div class="col-lg-4 col-md-6">
          <div class="footer-about">
            <h4 class="footer-title">Hồ Xuân Hương Hotel</h4>
            <p>
              Nơi sự sang trọng giao thoa cùng nét thơ mộng của biển Sầm Sơn, mang đến cho quý khách những trải nghiệm nghỉ dưỡng đẳng cấp và khó quên.
            </p>
          </div>
        </div>

        <!-- Cột 2: Liên kết nhanh -->
        <div class="col-lg-4 col-md-6">
          <h5 class="footer-title">Liên kết nhanh</h5>
          <ul class="footer-links list-unstyled">
            <li><a href="/">Trang chủ</a></li>
            <li><a href="/about">Giới thiệu</a></li>
            <li><a href="/booking_hotel">Đặt phòng</a></li>
            <li><a href="/news">Tin tức</a></li>
            <li><a href="/contact">Liên hệ</a></li>
          </ul>
        </div>

        <!-- Cột 3: Thông tin liên hệ -->
        <div class="col-lg-4 col-md-12">
          <h5 class="footer-title">Liên hệ</h5>
          <ul class="footer-contact list-unstyled">
            <li>
              <span class="icon">📍</span>
              <span>Số 123, Đường Hồ Xuân Hương, Bãi biển Sầm Sơn, Thanh Hóa</span>
            </li>
            <li>
              <span class="icon">📞</span>
              <span>(+84) 237 3123 456</span>
            </li>
            <li>
              <span class="icon">📧</span>
              <span>info@hoxuanhuonghotel.com</span>
            </li>
          </ul>
        </div>
      </div>
    </div>

    <!-- 3. Phần Bản quyền -->
    <div class="footer-bottom">
      <div class="container text-center">
        <p class="mb-0">© 2025 Hồ Xuân Hương Hotel. All Rights Reserved.</p>
      </div>
    </div>
  </footer>
</template>

<script setup>
</script>

<style scoped>
/* CSS được cập nhật, font-family sẽ được định nghĩa ở trang chính */
.main-footer {
  background-color: #111827;
  color: #a9b3c1;
  font-size: 0.95rem;
}

.map-section {
  line-height: 0;
}

.footer-content {
  padding-top: 60px;
  padding-bottom: 60px;
}

.footer-title {
  color: #fff;
  /* Font sẽ được kế thừa từ file chính */
  font-family: 'Playfair Display', serif; 
  margin-bottom: 20px;
  font-size: 1.25rem;
  position: relative;
  padding-bottom: 10px;
}

.footer-title::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  width: 40px;
  height: 2px;
  background-color: #A98A66;
}

.footer-about p {
  line-height: 1.7;
}

.footer-links li, .footer-contact li {
  margin-bottom: 12px;
}

.footer-links a {
  color: #a9b3c1;
  text-decoration: none;
  transition: all 0.3s ease;
}

.footer-links a:hover {
  color: #fff;
  padding-left: 5px;
}

.footer-contact li {
  display: flex;
  align-items: flex-start;
}

.footer-contact .icon {
  color: #A98A66;
  margin-right: 12px;
  font-size: 1.1rem;
  flex-shrink: 0;
  width: 20px;
  text-align: center;
}

.footer-bottom {
  background-color: #000;
  padding: 20px 0;
  font-size: 0.85rem;
}

@media (max-width: 768px) {
  .footer-title::after {
    left: 50%;
    transform: translateX(-50%);
  }
  .footer-content .col-lg-4 {
    text-align: center;
  }
  .footer-contact li {
    justify-content: center;
  }
}
</style>