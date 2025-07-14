<template>
  <div class="contact-us-page">
    <!-- Section 1: Hero Banner (Giữ nguyên) -->
    <section class="hero-section d-flex align-items-center justify-content-center text-center text-white">
      <div class="hero-content">
        <h1 class="display-3 fw-bold">Liên Hệ</h1>
        <p class="lead">Dù bạn có câu hỏi, cần hỗ trợ, hay chỉ đơn giản muốn chia sẻ, chúng tôi luôn ở đây lắng nghe.</p>
      </div>
    </section>

    <!-- Section 2: Contact Form & Image (Đã được cập nhật để xử lý dữ liệu) -->
    <section class="contact-form-section py-5">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-lg-6 mb-5 mb-lg-0">
            <div class="text-center text-lg-start mb-4">
                <h2 class="display-5 serif-font">Chúng tôi luôn sẵn sàng lắng nghe!</h2>
            </div>
            
            <!-- THÔNG BÁO THÀNH CÔNG/LỖI -->
            <div v-if="successMessage" class="alert alert-success mt-4">{{ successMessage }}</div>
            <div v-if="errorMessage" class="alert alert-danger mt-4">{{ errorMessage }}</div>

            <!-- FORM ĐÃ ĐƯỢC NỐI LOGIC -->
            <form @submit.prevent="submitForm" novalidate>
              <div class="mb-3">
                <label for="contactName" class="form-label">Tên của bạn</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input v-model="form.name" type="text" class="form-control" id="contactName" placeholder="Nhập tên của bạn" required>
                </div>
              </div>
              <div class="mb-3">
                <label for="contactEmail" class="form-label">Email của bạn</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input v-model="form.email" type="email" class="form-control" id="contactEmail" placeholder="Nhập email của bạn" required>
                </div>
              </div>
              <div class="mb-4">
                <label for="contactMessage" class="form-label">Lời nhắn của bạn</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-chat-left-text"></i></span>
                    <textarea v-model="form.message" class="form-control" id="contactMessage" rows="5" placeholder="Nội dung lời nhắn..." required></textarea>
                </div>
              </div>
              <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg" :disabled="isLoading">
                  <span v-if="isLoading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                  {{ isLoading ? 'Đang gửi...' : 'Gửi Tin Nhắn' }}
                </button>
              </div>
            </form>
          </div>
          <div class="col-lg-6">
            <!-- Ảnh giữ nguyên -->
            <img src="https://html.themewant.com/moonlit/assets/images/pages/contact.webp" class="img-fluid rounded shadow-sm" alt="Phòng khách sạn Hồ Xuân Hương">
          </div>
        </div>
      </div>
    </section>

    <!-- Section 3: Map & Hotel Info (Giữ nguyên) -->
    <section class="map-info-section py-5 bg-light">
      <div class="container">
        <div class="row">
          <!-- Google Map -->
          <div class="col-lg-7 mb-4 mb-lg-0">
            <div class="ratio ratio-16x9 rounded overflow-hidden shadow-sm">
              <iframe 
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d30005.15579930776!2d105.88226959082367!3d19.74902102143091!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3136f0e4737119e3%3A0x444f6f4364491752!2zU-G6p20gU8ahbiwgVGhhbmggSMOzYSwgVmnhu4d0IE5hbQ!5e0!3m2!1svi!2s!4v1678888888888!5m2!1svi!2s" 
                style="border:0;" 
                allowfullscreen="" 
                loading="lazy" 
                referrerpolicy="no-referrer-when-downgrade">
              </iframe>
            </div>
          </div>
          <!-- Hotel Info -->
          <div class="col-lg-5">
            <div class="p-4 bg-white rounded shadow-sm h-100">
              <h3 class="serif-font mb-4">Thông Tin Khách Sạn</h3>
              <ul class="list-unstyled text-muted">
                <li class="d-flex mb-3">
                  <i class="bi bi-geo-alt-fill text-primary me-3 mt-1"></i>
                  <span>Đường Hồ Xuân Hương, Phường Quảng Cư, TP. Sầm Sơn, Thanh Hóa, Việt Nam</span>
                </li>
                <li class="d-flex mb-3">
                  <i class="bi bi-telephone-fill text-primary me-3 mt-1"></i>
                  <span>(+84) 123 456 789</span>
                </li>
                 <li class="d-flex mb-3">
                  <i class="bi bi-printer-fill text-primary me-3 mt-1"></i>
                  <span>(+84) 123 456 790</span>
                </li>
                <li class="d-flex mb-3">
                  <i class="bi bi-envelope-fill text-primary me-3 mt-1"></i>
                  <span>info@hoxuanhuonghotel.com</span>
                </li>
                <li class="d-flex">
                  <i class="bi bi-clock-fill text-primary me-3 mt-1"></i>
                  <span>Giờ mở cửa: Thứ Hai – Chủ Nhật</span>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </section>

  </div>
</template>

<!-- PHẦN SCRIPT ĐÃ ĐƯỢC CẬP NHẬT -->
<script setup>
import { ref, inject } from 'vue';
import axiosConfig from '../axiosConfig.js'; // Đảm bảo đường dẫn này đúng

// Inject apiUrl đã được provide trong App.vue
const apiUrl = inject('apiUrl');

const form = ref({
  name: '',
  email: '',
  message: ''
});

const isLoading = ref(false);
const successMessage = ref('');
const errorMessage = ref('');

const submitForm = async () => {
  // Xóa thông báo cũ
  successMessage.value = '';
  errorMessage.value = '';

  // Kiểm tra dữ liệu
  if (!form.value.name || !form.value.email || !form.value.message) {
      errorMessage.value = 'Vui lòng điền đầy đủ thông tin.';
      return;
  }

  isLoading.value = true;
  try {
    const response = await axiosConfig.post(`${apiUrl}/api/contacts`, form.value);
    if (response.data.status) {
      successMessage.value = response.data.message;
      // Reset form sau khi gửi thành công
      form.value.name = '';
      form.value.email = '';
      form.value.message = '';
    }
  } catch (error) {
    if (error.response && error.response.data && error.response.data.errors) {
      const errors = error.response.data.errors;
      const firstError = Object.values(errors)[0][0];
      errorMessage.value = firstError;
    } else {
      errorMessage.value = 'Đã có lỗi xảy ra. Vui lòng thử lại sau.';
    }
    console.error('Lỗi khi gửi liên hệ:', error);
  } finally {
    isLoading.value = false;
  }
};
</script>

<!-- PHẦN STYLE GIỮ NGUYÊN -->
<style scoped>
/* Import font chữ serif đẹp cho tiêu đề */
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Roboto:wght@400&display=swap');

.serif-font {
  font-family: 'Playfair Display', serif;
}

.contact-us-page {
  font-family: 'Roboto', sans-serif;
  color: #333;
}

/* Hero Section */
.hero-section {
  position: relative;
  height: 60vh;
  /* Thay thế bằng ảnh nền phù hợp */
  background: url('https://images.unsplash.com/photo-1564501049412-61c2a3083791?q=80&w=1932') no-repeat center center;
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

/* Form Section */
.form-control {
    padding: 0.75rem 1rem;
    border-left: 0;
}
.input-group-text {
    background-color: #fff;
    border-right: 0;
}
.form-control:focus {
    box-shadow: none;
    border-color: #a07d5a;
}
.input-group:focus-within {
    border-color: #a07d5a;
    box-shadow: 0 0 0 0.25rem rgba(160, 125, 90, 0.25);
}
.input-group {
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
    transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out;
}
textarea.form-control {
    height: auto;
}

/* Custom Colors and Buttons */
.text-primary {
  color: #a07d5a !important;
}
.btn-primary {
  background-color: #a07d5a;
  border-color: #a07d5a;
  padding: 0.75rem;
}
.btn-primary:hover {
  background-color: #866849;
  border-color: #866849;
}

/* Map Info Section */
.bg-light {
    background-color: #fdfaf6 !important;
}
</style>