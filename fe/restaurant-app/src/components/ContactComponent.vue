<template>
  <div class="contact-us-page">
    <!-- Section 1: Hero Banner -->
    <section class="hero-section d-flex align-items-center justify-content-center text-center text-white">
      <div class="hero-content">
        <h1 class="display-3 fw-bold">Liên Hệ</h1>
        <p class="lead">Dù bạn có câu hỏi, cần hỗ trợ, hay chỉ đơn giản muốn chia sẻ, chúng tôi luôn ở đây lắng nghe.</p>
      </div>
    </section>

    <!-- Section 2: Contact Form & Image -->
    <section class="contact-form-section py-5">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-lg-6 mb-5 mb-lg-0">
            <div class="text-center text-lg-start mb-4">
                <h2 class="display-5 serif-font">Chúng tôi luôn sẵn sàng lắng nghe!</h2>
            </div>
            
            <!-- ĐÃ XÓA CÁC THÔNG BÁO ALERT TĨNH Ở ĐÂY -->

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
              
              <div class="mb-3">
                <label for="contactPhone" class="form-label">Số điện thoại (Không bắt buộc)</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                    <input v-model="form.phone" type="tel" class="form-control" id="contactPhone" placeholder="Nhập số điện thoại của bạn">
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
            <img src="https://html.themewant.com/moonlit/assets/images/pages/contact.webp" class="img-fluid rounded shadow-sm" alt="Phòng khách sạn Hồ Xuân Hương">
          </div>
        </div>
      </div>
    </section>

    <!-- Section 3: Map & Hotel Info -->
    <section class="map-info-section py-5 bg-light">
      <div class="container">
        <div class="row">
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

    <!-- KHU VỰC HIỂN THỊ THÔNG BÁO TOAST TÙY CHỈNH -->
    <transition-group name="toast" tag="div" class="toast-container">
      <div v-for="toast in toasts" :key="toast.id" :class="['toast-notification', `toast--${toast.type}`]">
        <i :class="['toast-icon', toast.icon]"></i>
        <div class="toast-content">
          <p class="toast-message">{{ toast.message }}</p>
          <div class="toast-progress"></div>
        </div>
      </div>
    </transition-group>

  </div>
</template>

<script setup>
import { ref, inject } from 'vue';
import axiosConfig from '../axiosConfig.js';

const apiUrl = inject('apiUrl');

const form = ref({
  name: '',
  email: '',
  phone: '',
  message: ''
});

const isLoading = ref(false);

// --- HỆ THỐNG THÔNG BÁO TOAST TÙY CHỈNH ---
const toasts = ref([]);
let toastIdCounter = 0;

const triggerToast = (message, type = 'success') => {
  const id = toastIdCounter++;
  const toastDetails = {
    id,
    message,
    type,
    icon: type === 'success' ? 'bi bi-check-circle-fill' : 'bi bi-exclamation-triangle-fill'
  };
  toasts.value.push(toastDetails);

  setTimeout(() => {
    removeToast(id);
  }, 4000);
};

const removeToast = (id) => {
  const index = toasts.value.findIndex(toast => toast.id === id);
  if (index !== -1) {
    toasts.value.splice(index, 1);
  }
};
// --- KẾT THÚC HỆ THỐNG TOAST ---

const submitForm = async () => {
  if (!form.value.name || !form.value.email || !form.value.message) {
      triggerToast('Vui lòng điền đầy đủ các trường bắt buộc.', 'error');
      return;
  }

  isLoading.value = true;
  try {
    const response = await axiosConfig.post(`${apiUrl}/api/contacts`, form.value);
    if (response.data.status) {
      triggerToast(response.data.message, 'success');
      // Reset form sau khi gửi thành công
      form.value = { name: '', email: '', phone: '', message: '' };
    }
  } catch (error) {
    if (error.response?.data?.errors) {
      const firstError = Object.values(error.response.data.errors)[0][0];
      triggerToast(firstError, 'error');
    } else {
      triggerToast('Đã có lỗi xảy ra. Vui lòng thử lại sau.', 'error');
    }
    console.error('Lỗi khi gửi liên hệ:', error);
  } finally {
    isLoading.value = false;
  }
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Roboto:wght@400&display=swap');

/* STYLES CỦA TRANG LIÊN HỆ */
.serif-font { font-family: 'Playfair Display', serif; }
.contact-us-page { font-family: 'Roboto', sans-serif; color: #333; }
.hero-section {
  position: relative;
  height: 60vh;
  background: url('https://images.unsplash.com/photo-1564501049412-61c2a3083791?q=80&w=1932') no-repeat center center;
  background-size: cover;
}
.hero-section::before {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(0, 0, 0, 0.5);
}
.hero-content { position: relative; z-index: 1; }
.form-control { padding: 0.75rem 1rem; border-left: 0; }
.input-group-text { background-color: #fff; border-right: 0; }
.form-control:focus { box-shadow: none; border-color: #a07d5a; }
.input-group:focus-within {
  border-color: #a07d5a;
  box-shadow: 0 0 0 0.25rem rgba(160, 125, 90, 0.25);
}
.input-group {
  border: 1px solid #ced4da;
  border-radius: 0.375rem;
  transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out;
}
textarea.form-control { height: auto; }
.text-primary { color: #a07d5a !important; }
.btn-primary {
  background-color: #a07d5a;
  border-color: #a07d5a;
  padding: 0.75rem;
}
.btn-primary:hover { background-color: #866849; border-color: #866849; }
.bg-light { background-color: #fdfaf6 !important; }

/* CUSTOM TOAST NOTIFICATION STYLES */
.toast-container { position: fixed; top: 20px; right: 20px; z-index: 9999; width: 350px; }
.toast-notification { display: flex; align-items: flex-start; background: #fff; border-radius: 8px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); padding: 16px; margin-bottom: 1rem; overflow: hidden; position: relative; border-left: 5px solid; }
.toast--success { border-color: #4CAF50; }
.toast--error { border-color: #F44336; }
.toast-icon { font-size: 1.5rem; margin-right: 12px; }
.toast--success .toast-icon { color: #4CAF50; }
.toast--error .toast-icon { color: #F44336; }
.toast-content { flex-grow: 1; }
.toast-message { margin: 0; font-weight: 500; color: #333; }
.toast-progress { position: absolute; bottom: 0; left: 0; height: 4px; width: 100%; animation: progress-bar-animation 4s linear forwards; }
.toast--success .toast-progress { background-color: #4CAF50; }
.toast--error .toast-progress { background-color: #F44336; }
@keyframes progress-bar-animation { from { width: 100%; } to { width: 0%; } }
.toast-enter-active, .toast-leave-active { transition: all 0.5s cubic-bezier(0.68, -0.55, 0.27, 1.55); }
.toast-enter-from, .toast-leave-to { opacity: 0; transform: translateX(100%); }
</style>