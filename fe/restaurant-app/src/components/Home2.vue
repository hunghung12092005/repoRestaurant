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
            Lựa chọn Bokinn là một trong những quyết định tuyệt vời nhất của chúng tôi. Họ đã chứng tỏ mình là một đối
            tác đáng tin cậy và sáng tạo.
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
              Chào mừng đến với Hồ Xuân Hương Ecosystem, nơi sự sang trọng gặp gỡ tiện nghi tại trung tâm Canada. Từ năm
              1999, chúng tôi đã cống hiến để mang đến một kỳ nghỉ đặc biệt cho quý khách, kết hợp các tiện nghi hiện
              đại với sự thanh lịch vượt thời gian. Các phòng và suite được thiết kế đẹp mắt của chúng tôi có tầm nhìn
              tuyệt đẹp và chỗ ở sang trọng, đảm bảo một nơi nghỉ ngơi thư thái dù bạn ở đây để công tác hay giải trí.
            </p>
            <a href="#" class="btn btn-custom-secondary">Tìm Hiểu Thêm</a>
          </div>
          <div class="col-lg-6">
            <div class="about-image">
              <img
                src="https://images.trvl-media.com/lodging/42000000/41830000/41826900/41826852/ff9dfc7c.jpg?impolicy=resizecrop&rw=575&rh=575&ra=fill"
                class="img-fluid rounded" alt="Nội thất khách sạn" />
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
              Các phòng của chúng tôi là sự pha trộn hài hòa giữa tiện nghi và thanh lịch, được thiết kế để mang lại một
              kỳ nghỉ đặc biệt cho mọi du khách. Mỗi phòng đều có bộ khăn trải giường cao cấp và nhiều lựa chọn gối để
              đảm bảo một giấc ngủ ngon.
            </p>
          </div>
        </div>
        <div class="row g-4">
          <div v-for="(roomType, index) in roomTypes" :key="roomType.type_id" class="col-lg-4 col-md-6">
            <router-link :to="`/room-types/${roomType.type_id}`" class="text-decoration-none">
              <div class="room-card">
                <img :src="getRoomImage(roomType, index)" class="img-fluid" :alt="roomType.type_name"
                  @error="event => handleImageError(event)" />
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
            </router-link>
          </div>
          <div v-if="loading" class="col-12 text-center py-5">
            <p>Đang tải dữ liệu phòng...</p>
          </div>
          <div v-if="!roomTypes.length && !loading" class="col-12 text-center py-5">
            <p>Hiện không có loại phòng nào để hiển thị.</p>
          </div>
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

    <!-- 6. Câu chuyện & Triết lý -->
    <section class="philosophy-section bg-white">
      <div class="container">
        <div class="row align-items-center g-5">
          <div class="col-lg-6">
            <img src="https://images.pexels.com/photos/261102/pexels-photo-261102.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1" alt="Không gian khách sạn ven biển" class="img-fluid rounded">
          </div>
          <div class="col-lg-6 text-start">
            <p class="section-subtitle">Câu Chuyện Của Chúng Tôi</p>
            <h2 class="section-title">Mang Hơi Thở Của Biển Vào Không Gian Của Bạn</h2>
            <p class="section-text">
              Chúng tôi tin rằng một kỳ nghỉ tuyệt vời là khi bạn được hoàn toàn thư giãn, lắng nghe tiếng sóng vỗ và cảm nhận làn gió biển trong lành. Khách sạn của chúng tôi được xây dựng dựa trên triết lý đó - một nơi trú ẩn yên bình, nơi bạn có thể gác lại mọi bộn bề và tái tạo năng lượng từ thiên nhiên.
            </p>
            <ul class="list-unstyled mt-4 philosophy-list">
              <li class="d-flex align-items-start mb-3">
                <i class="bi bi-tsunami philosophy-icon me-3"></i>
                <div>
                  <strong>Thiết Kế Gần Gũi</strong>
                  <p class="mb-0">Sử dụng vật liệu tự nhiên và cửa sổ lớn để tối đa hóa ánh sáng và tầm nhìn ra biển, tạo cảm giác hòa mình vào thiên nhiên.</p>
                </div>
              </li>
              <li class="d-flex align-items-start mb-3">
                <i class="bi bi-heart philosophy-icon me-3"></i>
                 <div>
                  <strong>Lòng Hiếu Khách Ấm Áp</strong>
                  <p class="mb-0">Mỗi nhân viên của chúng tôi đều là một người bạn, luôn sẵn sàng chia sẻ và giúp đỡ để bạn có một kỳ nghỉ thoải mái như ở nhà.</p>
                </div>
              </li>
              <li class="d-flex align-items-start">
                <i class="bi bi-brightness-high philosophy-icon me-3"></i>
                 <div>
                  <strong>Trải Nghiệm Thư Giãn</strong>
                  <p class="mb-0">Từ ban công riêng tư đến khu vườn yên tĩnh, mọi không gian đều được thiết kế để bạn tìm thấy sự bình yên và tĩnh lặng.</p>
                </div>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </section>
    
    <!-- 6.5. Form Liên Hệ Nhanh -->
    <section class="quick-contact-section">
        <div class="container">
            <div class="text-center">
                <p class="section-subtitle">Giữ Liên Lạc</p>
                <h2 class="section-title">Gửi Yêu Cầu Cho Chúng Tôi</h2>
                <p class="lead text-muted mt-3">Có câu hỏi hoặc muốn đặt phòng? Điền vào form bên dưới và chúng tôi sẽ liên hệ lại với bạn sớm nhất.</p>
            </div>
            <div class="row justify-content-center mt-5">
                <div class="col-lg-8">
                    <form @submit.prevent="submitContactForm" novalidate>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="contactName" class="form-label">Tên của bạn</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input v-model="contactForm.name" type="text" class="form-control" id="contactName" placeholder="Nhập tên của bạn" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="contactEmail" class="form-label">Email của bạn</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input v-model="contactForm.email" type="email" class="form-control" id="contactEmail" placeholder="Nhập email của bạn" required>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="contactPhone" class="form-label">Số điện thoại (Không bắt buộc)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                <input v-model="contactForm.phone" type="tel" class="form-control" id="contactPhone" placeholder="Nhập số điện thoại của bạn">
                            </div>
                        </div>
                        <div class="mb-4">
                            <label for="contactMessage" class="form-label">Lời nhắn của bạn</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-chat-left-text"></i></span>
                                <textarea v-model="contactForm.message" class="form-control" id="contactMessage" rows="5" placeholder="Nội dung lời nhắn..." required></textarea>
                            </div>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary btn-lg px-5" :disabled="isContactLoading">
                            <span v-if="isContactLoading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            {{ isContactLoading ? 'Đang gửi...' : 'Gửi Tin Nhắn' }}
                            </button>
                        </div>
                    </form>
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
          <div class="col-lg-3 col-md-6 col-6"><img
              src="https://images.trvl-media.com/lodging/42000000/41830000/41826900/41826852/ff9dfc7c.jpg?impolicy=resizecrop&rw=575&rh=575&ra=fill"
              class="img-fluid rounded" alt="Ảnh thư viện 1"></div>
          <div class="col-lg-3 col-md-6 col-6"><img
              src="https://www.vietnambooking.com/wp-content/uploads/2018/06/co-nhung-loai-phong-khach-san-nao-02-06-18-1.jpg"
              class="img-fluid rounded" alt="Ảnh thư viện 2"></div>
          <div class="col-lg-3 col-md-6 col-6"><img
              src="https://www.vietnambooking.com/wp-content/uploads/2018/06/co-nhung-loai-phong-khach-san-nao-02-06-18-3.jpg"
              class="img-fluid rounded" alt="Ảnh thư viện 3"></div>
          <div class="col-lg-3 col-md-6 col-6"><img
              src="https://images.trvl-media.com/lodging/42000000/41830000/41826900/41826852/ff9dfc7c.jpg?impolicy=resizecrop&rw=575&rh=575&ra=fill"
              class="img-fluid rounded" alt="Ảnh thư viện 4"></div>
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
import { ref, onMounted } from 'vue';
import axiosConfig from '../axiosConfig.js'; 

// --- DỮ LIỆU PHÒNG ---
const apiUrl = ref('http://localhost:8000');
const roomTypes = ref([]);
const loading = ref(true);

const getRoomImage = (roomType, index) => {
  if (roomType.images && roomType.images.length > 0) {
    return `${apiUrl.value}/images/room_type/${roomType.images[0]}`;
  }
  return 'https://placehold.co/575x250/EEE/31343C?text=Image+Not+Available';
};

const fetchRoomTypes = async () => {
  loading.value = true;
  try {
    const response = await axiosConfig.get(`${apiUrl.value}/api/room-types`);
    if (response.data && response.data.status === true) {
      roomTypes.value = response.data.data.map(roomType => ({
        ...roomType,
        images: roomType.images ? JSON.parse(roomType.images) : [],
      }));
    } else {
      console.error('API không trả về dữ liệu hợp lệ:', response.data);
      roomTypes.value = [];
    }
  } catch (error) {
    console.error('Lỗi khi gọi API lấy danh sách loại phòng:', error);
    roomTypes.value = [];
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchRoomTypes();
});

const handleImageError = (event) => {
  event.target.src = 'https://placehold.co/575x250/EEE/31343C?text=Image+Not+Found';
};

// --- LOGIC FORM LIÊN HỆ & TOAST ---
const contactForm = ref({
  name: '',
  email: '',
  phone: '',
  message: ''
});
const isContactLoading = ref(false);

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

const submitContactForm = async () => {
  if (!contactForm.value.name || !contactForm.value.email || !contactForm.value.message) {
      triggerToast('Vui lòng điền đầy đủ các trường bắt buộc.', 'error');
      return;
  }

  isContactLoading.value = true;
  try {
    const response = await axiosConfig.post(`${apiUrl.value}/api/contacts`, contactForm.value);
    if (response.data.status) {
      triggerToast(response.data.message, 'success');
      contactForm.value = { name: '', email: '', phone: '', message: '' }; // Reset form
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
    isContactLoading.value = false;
  }
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&display=swap');

/* GENERAL STYLES */
body, .hotel-page-container {
  font-family: 'Helvetica', 'Arial', sans-serif;
  background-color: #f8f9fa;
  overflow-x: hidden;
}
section { padding-top: 80px; padding-bottom: 80px; }
.gallery-section, .hero-section { text-align: center; }
.section-subtitle { color: #A98A66; font-family: 'Playfair Display', serif; }
.section-title, h3 { font-family: 'Playfair Display', serif; }
.section-title { font-size: 2.5rem; }
.section-text { color: #555; line-height: 1.7; }
.btn-custom-secondary { background-color: #A98A66; color: #fff; border-color: #A98A66; }
.btn-custom-secondary:hover { background-color: #937451; border-color: #937451; color: #fff; }

/* HERO SECTION */
.hero-section { min-height: 80vh; background: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), url('https://a25hotel.com/files/images/screenshot_1664443543.png') center/cover no-repeat; color: #fff; position: relative; }
.hero-content .main-heading { font-family: 'Playfair Display', serif; font-size: 4rem; font-weight: normal; }
.btn-outline-light:hover { color: #333; }

/* ABOUT SECTION */
.about-image { position: relative; }
.staff-card { position: absolute; bottom: 20px; right: -20px; padding: 1rem; border: none; border-radius: 8px; }
.staff-card .staff-icon { font-size: 2rem; }

/* ROOMS SECTION */
.room-card { position: relative; overflow: hidden; border-radius: var(--bs-border-radius); box-shadow: var(--bs-box-shadow-sm); height: 100%; transition: transform 0.3s ease; }
.room-card:hover { transform: translateY(-5px); }
.room-card img { width: 100%; height: 250px; object-fit: cover; }
.room-info { position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(to top, rgba(0, 0, 0, 0.8), transparent); color: #fff; padding: 1.5rem; display: flex; justify-content: space-between; align-items: flex-end; }
.room-info h3 { font-size: 1.4rem; color: white; font-family: 'Playfair Display', serif; }
.room-details { font-size: 0.8rem; opacity: 0.9; }
.room-rate { font-size: 1.8rem; font-weight: bold; display: flex; align-items: center; }

/* STATIC BANNER SECTION */
.static-banner-section { height: 500px; background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('https://images.trvl-media.com/lodging/42000000/41830000/41826900/41826852/ff9dfc7c.jpg?impolicy=resizecrop&rw=575&rh=575&ra=fill') center/cover no-repeat fixed; display: flex; justify-content: center; align-items: center; }
.banner-content { max-width: 800px; }

/* PHILOSOPHY SECTION */
.philosophy-section .section-text { margin-bottom: 2rem; }
.philosophy-list p { color: #555; line-height: 1.6; }
.philosophy-list strong { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 1.1rem; margin-bottom: 0.25rem; display: block; }
.philosophy-icon { font-size: 2rem; color: #A98A66; line-height: 1; margin-top: 4px; }

/* QUICK CONTACT FORM SECTION */
.quick-contact-section { background-color: #fdfaf6; }
.form-label { font-weight: 500; color: #444; }
.form-control { padding: 0.75rem 1rem; border-left: 0; }
.input-group-text { background-color: #fff; border-right: 0; color: #A98A66; }
.form-control:focus { box-shadow: none; border-color: #A98A66; }
.input-group:focus-within { border-color: #A98A66; box-shadow: 0 0 0 0.25rem rgba(169, 138, 102, 0.25); }
.input-group { border: 1px solid #ced4da; border-radius: 0.375rem; transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out; }
textarea.form-control { height: auto; }
.btn-primary { background-color: #A98A66; border-color: #A98A66; padding: 0.75rem; font-weight: bold; }
.btn-primary:hover { background-color: #937451; border-color: #937451; }

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

/* RESPONSIVE ADJUSTMENTS */
@media (max-width: 768px) {
  .main-heading { font-size: 3rem !important; }
  .section-title { font-size: 2rem; }
  .section-subtitle, .section-title, .section-text { text-align: center !important; }
  .philosophy-section .text-start { text-align: center !important; }
  .philosophy-list .d-flex { flex-direction: column; align-items: center !important; text-align: center; }
  .philosophy-icon { margin-right: 0 !important; margin-bottom: 0.5rem; }
}
</style>