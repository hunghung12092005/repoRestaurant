<template>
  <main class="profile-card-enhanced">
    <div class="card-bg-decoration"></div>
    <button class="close-button" @click="closeProfile">
      <i class="fas fa-times"></i>
    </button>

    <div class="profile-header-enhanced">
      <div class="profile-avatar-enhanced">
        <img v-if="userInfo.avatar" :src="userInfo.avatar" alt="User Avatar" />
        <i v-else class="fas fa-user-circle"></i>
        <span class="avatar-badge" v-if="userInfo.isPro"><i class="fas fa-check-circle"></i></span>
      </div>
      <h1 class="user-name-enhanced">{{ userInfo.name }}</h1>
      <p class="user-role-enhanced">{{ userInfo.role }}</p>
    </div>

    <div class="profile-details-enhanced">
      <div class="detail-item-enhanced">
        <i class="fas fa-id-badge detail-icon-enhanced"></i>
        <span class="detail-label-enhanced">ID</span>
        <span class="detail-value-enhanced">{{ userInfo.id }}</span>
      </div>
      <div class="detail-item-enhanced">
        <i class="fas fa-envelope detail-icon-enhanced"></i>
        <span class="detail-label-enhanced">Email</span>
        <span class="detail-value-enhanced">{{ userInfo.email }}</span>
      </div>
      <div class="detail-item-enhanced bio-section">
        <i class="fas fa-info-circle detail-icon-enhanced"></i>
        <span class="detail-label-enhanced">Giới thiệu</span>
        <p class="detail-value-enhanced bio-text-enhanced">
          Chào bạn, 🌼 🌱 Rất vui được ở đây! 🌿 Luôn sẵn sàng kết nối và chia sẻ.
        </p>
      </div>
    </div>

    <div class="profile-actions-enhanced">
      <button class="action-button-enhanced primary-action" @click="showChangePasswordModal = true">
        <i class="fas fa-key"></i> Thay Mật Khẩu
      </button>
      <button class="action-button-enhanced secondary-action" @click="showUpdateProfileModal = true">
        <i class="fas fa-edit"></i> Chỉnh Sửa Hồ Sơ
      </button>
    </div>

    <div v-if="showChangePasswordModal" class="fancy-modal-overlay">
      <div class="fancy-modal-content">
        <button class="fancy-close-button" @click="closePasswordModal">
          <i class="fas fa-times"></i>
        </button>
        <h2 class="fancy-modal-title">Đổi Mật Khẩu</h2>

        <form @submit.prevent="submitChangePassword" class="fancy-form">
          <div class="fancy-form-group">
            <label for="current_password_fancy">Mật khẩu hiện tại</label>
            <input
              type="password"
              id="current_password_fancy"
              v-model="passwordForm.current_password"
              required
              autocomplete="current-password"
            />
          </div>

          <div class="fancy-form-group">
            <label for="new_password_fancy">Mật khẩu mới</label>
            <input
              type="password"
              id="new_password_fancy"
              v-model="passwordForm.new_password"
              required
              minlength="6"
              autocomplete="new-password"
            />
            <p v-if="passwordErrors.new_password" class="fancy-error-message">{{ passwordErrors.new_password }}</p>
          </div>

          <div class="fancy-form-group">
            <label for="new_password_confirmation_fancy">Xác nhận mật khẩu mới</label>
            <input
              type="password"
              id="new_password_confirmation_fancy"
              v-model="passwordForm.new_password_confirmation"
              required
              autocomplete="new-password"
            />
            <p v-if="passwordErrors.new_password_confirmation" class="fancy-error-message">{{ passwordErrors.new_password_confirmation }}</p>
          </div>

          <div v-if="passwordMessage" :class="['fancy-response-message', isPasswordError ? 'error' : 'success']">
            {{ passwordMessage }}
          </div>

          <button type="submit" :disabled="isLoadingPasswordChange" class="fancy-submit-button">
            <span v-if="isLoadingPasswordChange"><i class="fas fa-spinner fa-spin"></i> Đang gửi...</span>
            <span v-else><i class="fas fa-check-circle"></i> Xác nhận đổi</span>
          </button>
        </form>
      </div>
    </div>

    <div v-if="showUpdateProfileModal" class="fancy-modal-overlay">
      <div class="fancy-modal-content">
        <button class="fancy-close-button" @click="closeUpdateProfileModal">
          <i class="fas fa-times"></i>
        </button>
        <h2 class="fancy-modal-title">Cập Nhật Hồ Sơ</h2>

        <form @submit.prevent="submitUpdateProfile" class="fancy-form">
          <div class="fancy-form-group">
            <label for="profile_name">Tên của bạn</label>
            <input
              type="text"
              id="profile_name"
              v-model="profileForm.name"
              required
            />
          </div>

          <div class="fancy-form-group">
            <label for="profile_email">Email</label>
            <input
              type="email"
              id="profile_email"
              v-model="profileForm.email"
              required
            />
            <p v-if="profileErrors.email" class="fancy-error-message">{{ profileErrors.email }}</p>
          </div>

          <div v-if="profileMessage" :class="['fancy-response-message', isProfileError ? 'error' : 'success']">
            {{ profileMessage }}
          </div>

          <button type="submit" :disabled="isLoadingProfileUpdate" class="fancy-submit-button">
            <span v-if="isLoadingProfileUpdate"><i class="fas fa-spinner fa-spin"></i> Đang cập nhật...</span>
            <span v-else><i class="fas fa-save"></i> Lưu thay đổi</span>
          </button>
        </form>
      </div>
    </div>
  </main>
</template>

<style scoped>
/* --- GIỮ NGUYÊN CSS IMPORT --- */
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
@import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css');

/* --- PROFILE CARD CHÍNH: ĐẸP VÀ CHỈNH CHU HƠN --- */
.profile-card-enhanced {
  font-family: 'Poppins', sans-serif;
  background-color: #ffffff;
  border-radius: 16px; /* Bo góc mềm mại */
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); /* Bóng đổ sâu hơn, chuyên nghiệp */
  max-width: 420px;
  margin: 60px auto;
  padding: 30px;
  text-align: center;
  position: relative;
  overflow: hidden;
  animation: fadeInScaleUp 0.6s ease-out forwards; /* Hiệu ứng mượt mà */
}

.card-bg-decoration {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 120px;
  /* //background: linear-gradient(135deg, #10d6ec, #8BC34A); Gradient xanh lá cây tươi mới */
  border-top-left-radius: 16px;
  border-top-right-radius: 16px;
  z-index: 0;
}

.profile-header-enhanced {
  position: relative;
  z-index: 1;
  margin-top: 20px;
  padding-bottom: 25px;
  border-bottom: 1px solid #e0e0e0; /* Đường kẻ tinh tế */
  margin-bottom: 25px;
}

.profile-avatar-enhanced {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  background-color: #f8f8f8; /* Nền avatar sáng */
  margin: -60px auto 15px;
  display: flex;
  justify-content: center;
  align-items: center;
  font-size: 50px;
  color: #a0a0a0;
  border: 5px solid #ffffff; /* Viền trắng dày hơn */
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15); /* Bóng đổ nổi bật hơn */
  overflow: hidden;
  position: relative; /* Quan trọng cho badge */
  transition: transform 0.3s ease-out, box-shadow 0.3s ease-out; /* Hiệu ứng mượt mà */
}

.profile-avatar-enhanced:hover {
  transform: translateY(-5px) scale(1.05);
  box-shadow: 0 12px 25px rgba(0, 0, 0, 0.2);
}

.profile-avatar-enhanced img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

/* BADGE CHO AVATAR */
.avatar-badge {
  position: absolute;
  bottom: 0px; /* Điều chỉnh vị trí */
  right: 0px;  /* Điều chỉnh vị trí */
  background-color: #FFC107; /* Màu vàng Gold cho badge */
  color: #fff;
  border-radius: 50%;
  width: 30px; /* Kích thước badge */
  height: 30px; /* Kích thước badge */
  display: flex;
  justify-content: center;
  align-items: center;
  font-size: 1rem;
  border: 3px solid #ffffff; /* Viền trắng cho badge */
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2); /* Bóng đổ cho badge */
  transition: transform 0.2s ease-out;
}

.avatar-badge:hover {
  transform: scale(1.1);
}

.user-name-enhanced {
  font-size: 1.8rem; /* Tên lớn hơn */
  font-weight: 700; /* Đậm hơn */
  color: #2c3e50; /* Màu chữ tối hơn, chuyên nghiệp */
  margin-bottom: 5px;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.05); /* Bóng chữ nhẹ */
}

.user-role-enhanced {
  font-size: 1rem; /* Kích thước role */
  color: #7f8c8d; /* Màu xám dịu */
  font-weight: 500;
  margin-bottom: 0;
}

.profile-details-enhanced {
  text-align: left;
  margin-bottom: 30px;
  padding: 0 10px; /* Thêm padding ngang */
}

.detail-item-enhanced {
  display: flex;
  align-items: center; /* Căn giữa theo chiều dọc */
  margin-bottom: 20px;
  font-size: 0.95rem; /* Kích thước chi tiết */
  color: #555;
  line-height: 1.5;
}

.detail-icon-enhanced {
  font-size: 1.2rem; /* Icon lớn hơn */
  color: #4CAF50; /* Màu icon theo gradient */
  margin-right: 15px; /* Khoảng cách icon */
  width: 25px; /* Chiều rộng icon cố định */
  text-align: center;
}

.detail-label-enhanced {
  font-weight: 600; /* Đậm hơn */
  color: #333;
  min-width: 80px; /* Chiều rộng tối thiểu cho label */
  flex-shrink: 0;
}

.detail-value-enhanced {
  flex: 1;
  color: #666;
}

.bio-section {
  align-items: flex-start;
}

.bio-text-enhanced {
  line-height: 1.6;
  margin-top: 0;
  font-size: 0.9rem; /* Giới thiệu nhỏ hơn một chút */
  color: #777;
}

.profile-actions-enhanced {
  display: flex;
  flex-direction: column;
  gap: 15px; /* Khoảng cách giữa các nút */
}

.action-button-enhanced {
  padding: 14px 25px; /* Padding lớn hơn */
  border: none;
  border-radius: 10px; /* Bo góc mềm mại */
  font-size: 1rem; /* Kích thước chữ nút */
  font-weight: 600; /* Chữ nút đậm hơn */
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px; /* Khoảng cách icon và chữ */
  transition: all 0.3s ease;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); /* Bóng đổ cho nút */
}

.action-button-enhanced.primary-action {
  background: linear-gradient(45deg, #4CAF50, #66BB6A); /* Gradient xanh lá cho nút chính */
  color: #fff;
  box-shadow: 0 6px 18px rgba(76, 175, 80, 0.3);
}

.action-button-enhanced.primary-action:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 22px rgba(76, 175, 80, 0.4);
  filter: brightness(1.1); /* Sáng hơn khi hover */
}

.action-button-enhanced.secondary-action {
  background-color: #f5f7f9; /* Nền xám nhạt */
  color: #555;
  border: 1px solid #e0e6ec; /* Border tinh tế */
}

.action-button-enhanced.secondary-action:hover {
  background-color: #e2e8ee;
  transform: translateY(-3px);
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.15);
}

.close-button {
  position: absolute;
  top: 15px;
  right: 15px;
  background: none;
  border: none;
  font-size: 1.8rem; /* Icon lớn hơn */
  color: #f0f0f0; /* Màu trắng xám */
  cursor: pointer;
  z-index: 2;
  transition: transform 0.3s ease, color 0.3s ease;
}

.close-button:hover {
  color: #fff; /* Trắng tinh khi hover */
  transform: rotate(90deg) scale(1.1);
}

/* Animations cho card chính */
@keyframes fadeInScaleUp {
  from {
    opacity: 0;
    transform: scale(0.9) translateY(20px);
  }
  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

/* Responsive adjustments */
@media (max-width: 500px) {
  .profile-card-enhanced {
    margin: 30px 15px;
    padding: 25px;
  }
  .card-bg-decoration {
    height: 100px;
  }
  .profile-avatar-enhanced {
    margin-top: -50px;
    width: 90px;
    height: 90px;
    font-size: 45px;
  }
  .avatar-badge {
    width: 25px;
    height: 25px;
    font-size: 0.9rem;
    bottom: -2px;
    right: -2px;
  }
  .user-name-enhanced {
    font-size: 1.6rem;
  }
  .user-role-enhanced {
    font-size: 0.9rem;
  }
  .detail-item-enhanced {
    flex-direction: column;
    align-items: flex-start;
    margin-bottom: 15px;
  }
  .detail-label-enhanced {
    margin-bottom: 5px;
    min-width: unset;
  }
  .detail-icon-enhanced {
    margin-right: 8px;
  }
  .detail-label-enhanced, .detail-value-enhanced {
    width: 100%;
  }
  .action-button-enhanced {
    font-size: 0.9rem;
    padding: 12px 18px;
  }
}

/* --- STYLE MỚI CHO POPUP (Đổi mật khẩu & Cập nhật hồ sơ): ĐẸP VÀ CHỈNH CHU HƠN --- */
.fancy-modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.6); /* Nền tối hơn, làm nổi bật popup */
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000; /* Cao hơn các thành phần khác */
  animation: fadeIn 0.3s ease-out forwards;
}

.fancy-modal-content {
  background-color: #fff;
  padding: 30px;
  border-radius: 12px; /* Bo góc mềm mại */
  box-shadow: 0 15px 45px rgba(0, 0, 0, 0.25); /* Bóng đổ sâu, cao cấp */
  width: 90%;
  max-width: 450px; /* Chiều rộng rộng hơn một chút */
  position: relative;
  animation: slideIn 0.3s cubic-bezier(0.25, 0.8, 0.25, 1) forwards; /* Hiệu ứng slide in mượt mà */
}

.fancy-close-button {
  position: absolute;
  top: 15px;
  right: 15px;
  background: none;
  border: none;
  font-size: 1.5rem; /* Icon đóng lớn hơn */
  color: #999; /* Màu xám dịu */
  cursor: pointer;
  transition: color 0.2s ease, transform 0.2s ease;
}

.fancy-close-button:hover {
  color: #333; /* Tối hơn khi hover */
  transform: rotate(90deg);
}

.fancy-modal-title {
  font-size: 1.8rem; /* Tiêu đề lớn và rõ ràng */
  font-weight: 700;
  color: #34495e; /* Màu chữ tối hơn */
  margin-bottom: 25px;
  text-align: center;
  position: relative;
  padding-bottom: 10px;
}

.fancy-modal-title::after {
  content: '';
  position: absolute;
  left: 50%;
  transform: translateX(-50%);
  bottom: 0;
  width: 60px;
  height: 3px;
  background-color: #4CAF50; /* Đường gạch chân màu xanh lá */
  border-radius: 2px;
}

.fancy-form .fancy-form-group {
  margin-bottom: 20px;
  text-align: left;
}

.fancy-form label {
  display: block;
  font-size: 0.95rem; /* Label rõ ràng */
  color: #555;
  margin-bottom: 8px;
  font-weight: 600; /* Đậm hơn */
}

.fancy-form input[type="password"],
.fancy-form input[type="text"],
.fancy-form input[type="email"] {
  width: calc(100% - 20px); /* Kích thước input với padding */
  padding: 12px 10px; /* Padding lớn hơn */
  border: 1px solid #ccc;
  border-radius: 8px; /* Bo góc mềm mại */
  font-size: 1rem;
  color: #333;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.fancy-form input[type="password"]:focus,
.fancy-form input[type="text"]:focus,
.fancy-form input[type="email"]:focus {
  border-color: #4CAF50; /* Màu border khi focus */
  outline: none;
  box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.2); /* Box-shadow tinh tế */
}

.fancy-error-message {
  color: #e74c3c; /* Màu đỏ nổi bật cho lỗi */
  font-size: 0.85rem;
  margin-top: 5px;
  font-weight: 500;
}

.fancy-response-message {
  padding: 12px;
  border-radius: 8px;
  margin-top: 25px; /* Khoảng cách trên */
  margin-bottom: 20px; /* Khoảng cách dưới */
  font-size: 0.95rem;
  text-align: center;
  font-weight: 500;
  border-left: 5px solid; /* Border bên trái cho hiệu ứng nổi bật */
}

.fancy-response-message.success {
  background-color: #e8f5e9; /* Nền xanh lá nhạt */
  color: #2e7d32; /* Chữ xanh lá đậm */
  border-color: #4CAF50; /* Border xanh lá */
}

.fancy-response-message.error {
  background-color: #ffebee; /* Nền đỏ nhạt */
  color: #c62828; /* Chữ đỏ đậm */
  border-color: #e74c3c; /* Border đỏ */
}

.fancy-submit-button {
  width: 100%;
  padding: 14px 20px;
  background: linear-gradient(45deg, #4CAF50, #66BB6A); /* Gradient xanh lá */
  color: #fff;
  border: none;
  border-radius: 8px;
  font-size: 1.1rem; /* Nút lớn hơn */
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  transition: all 0.3s ease;
  box-shadow: 0 5px 15px rgba(76, 175, 80, 0.3);
}

.fancy-submit-button:hover:not(:disabled) {
  background: linear-gradient(45deg, #66BB6A, #4CAF50); /* Đổi gradient khi hover */
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(76, 175, 80, 0.4);
}

.fancy-submit-button:disabled {
  background-color: #a5d6a7; /* Màu nhạt hơn khi disabled */
  cursor: not-allowed;
  opacity: 0.8;
  box-shadow: none;
}

/* Animations cho popup */
@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes slideIn {
  from { transform: translateY(-30px) scale(0.9); opacity: 0; }
  to { transform: translateY(0) scale(1); opacity: 1; }
}
</style>

<script setup>
import { ref, onMounted, inject, reactive } from 'vue';
import axiosConfig from '../../axiosConfig';
import axios from 'axios'; // Đảm bảo đã import axios
import { useRoute } from 'vue-router';
var router = useRoute();//su dung router để điều hướng

const userInfo = ref({
  id: 'USERID001',
  name: 'Tên Người Dùng',
  role: 'Chức Vụ',
  email: 'email@example.com',
  avatar: null
});
const apiUrl = inject('apiUrl');

// --- Logic cho Popup Thay đổi Mật khẩu Đơn giản ---
const showChangePasswordModal = ref(false);
const passwordForm = reactive({
  current_password: '',
  new_password: '',
  new_password_confirmation: ''
});
const passwordErrors = reactive({
  new_password: '',
  new_password_confirmation: ''
});
const passwordMessage = ref('');
const isPasswordError = ref(false);
const isLoadingPasswordChange = ref(false);

const closePasswordModal = () => {
  showChangePasswordModal.value = false;
  // Reset form và thông báo khi đóng modal
  passwordForm.current_password = '';
  passwordForm.new_password = '';
  passwordForm.new_password_confirmation = '';
  passwordErrors.new_password = '';
  passwordErrors.new_password_confirmation = '';
  passwordMessage.value = '';
  isPasswordError.value = false;
};

const validatePasswordForm = () => {
  let isValid = true;
  passwordErrors.new_password = '';
  passwordErrors.new_password_confirmation = '';

  if (passwordForm.new_password.length < 6) {
    passwordErrors.new_password = 'Mật khẩu mới phải có ít nhất 6 ký tự.';
    isValid = false;
  }
  if (passwordForm.new_password !== passwordForm.new_password_confirmation) {
    passwordErrors.new_password_confirmation = 'Xác nhận mật khẩu mới không khớp.';
    isValid = false;
  }
  return isValid;
};

const submitChangePassword = async () => {
  if (!validatePasswordForm()) {
    return;
  }

  isLoadingPasswordChange.value = true;
  passwordMessage.value = '';
  isPasswordError.value = false;

  try {
    const token = localStorage.getItem('tokenJwt');

    if (!token) {
      throw new Error('Không tìm thấy token. Vui lòng đăng nhập lại.');
    }

    const axiosInstance = axios.create({
      baseURL: apiUrl,
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`,
      }
    });

    const response = await axiosInstance.post(`${apiUrl}/api/change-password`, {
      current_password: passwordForm.current_password,
      new_password: passwordForm.new_password,
      new_password_confirmation: passwordForm.new_password_confirmation,
    });

    passwordMessage.value = response.data.message || 'Mật khẩu đã được thay đổi thành công!';
    isPasswordError.value = false;
    // closePasswordModal(); // Uncomment nếu muốn tự đóng sau thành công
  } catch (error) {
    isPasswordError.value = true;
    if (error.response) {
      if (error.response.status === 400 && error.response.data.message === 'Current password is incorrect') {
        passwordMessage.value = 'Mật khẩu hiện tại không chính xác.';
      } else if (error.response.status === 422 && error.response.data.errors) {
        let errorMessages = [];
        for (const key in error.response.data.errors) {
          errorMessages.push(error.response.data.errors[key][0]);
        }
        passwordMessage.value = errorMessages.join(' ');
      } else {
        passwordMessage.value = error.response.data.message || 'Đã xảy ra lỗi khi thay đổi mật khẩu.';
      }
    } else {
      passwordMessage.value = error.message || 'Lỗi kết nối hoặc lỗi không xác định.';
    }
  } finally {
    isLoadingPasswordChange.value = false;
  }
};
// --- Kết thúc Logic cho Popup Thay đổi Mật khẩu Đơn giản ---


// --- Logic cho Popup Cập nhật Thông tin cá nhân (Name, Email) ---
const showUpdateProfileModal = ref(false);
const profileForm = reactive({
  name: '',
  email: ''
});
const profileErrors = reactive({
  email: ''
});
const profileMessage = ref('');
const isProfileError = ref(false);
const isLoadingProfileUpdate = ref(false);

const closeUpdateProfileModal = () => {
  showUpdateProfileModal.value = false;
  // Reset form và thông báo khi đóng modal
  profileForm.name = userInfo.value.name; // Đặt lại về giá trị hiện tại của user
  profileForm.email = userInfo.value.email;
  profileErrors.email = '';
  profileMessage.value = '';
  isProfileError.value = false;
};

const validateProfileForm = () => {
  let isValid = true;
  profileErrors.email = '';

  if (!profileForm.name.trim()) {
    profileErrors.name = 'Tên không được để trống.';
    isValid = false;
  }

  // Regex đơn giản cho email
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(profileForm.email)) {
    profileErrors.email = 'Email không hợp lệ.';
    isValid = false;
  }
  return isValid;
};

const submitUpdateProfile = async () => {
  if (!validateProfileForm()) {
    return;
  }

  isLoadingProfileUpdate.value = true;
  profileMessage.value = '';
  isProfileError.value = false;

  try {
    const token = localStorage.getItem('tokenJwt');

    if (!token) {
      throw new Error('Không tìm thấy token. Vui lòng đăng nhập lại.');
    }

    const axiosInstance = axios.create({
      baseURL: apiUrl,
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`,
      }
    });

    const response = await axiosInstance.post(`${apiUrl}/api/update-password`, { // Thay đổi endpoint nếu cần
      name: profileForm.name,
      email: profileForm.email,
    });

    profileMessage.value = response.data.message || 'Cập nhật hồ sơ thành công!';
    isProfileError.value = false;
    // Cập nhật thông tin userInfo ở giao diện sau khi thành công
    userInfo.value.name = profileForm.name;
    userInfo.value.email = profileForm.email;
    // closeUpdateProfileModal(); // Uncomment nếu muốn tự đóng sau thành công
  } catch (error) {
    isProfileError.value = true;
    if (error.response) {
      if (error.response.status === 422 && error.response.data.errors) {
        let errorMessages = [];
        for (const key in error.response.data.errors) {
          errorMessages.push(error.response.data.errors[key][0]);
        }
        profileMessage.value = errorMessages.join(' ');
      } else {
        profileMessage.value = error.response.data.message || 'Đã xảy ra lỗi khi cập nhật hồ sơ.';
      }
    } else {
      profileMessage.value = error.message || 'Lỗi kết nối hoặc lỗi không xác định.';
    }
  } finally {
    isLoadingProfileUpdate.value = false;
  }
};
// --- Kết thúc Logic cho Popup Cập nhật Thông tin cá nhân ---


const fetchUserInfo = () => {
    axiosConfig.get(`${apiUrl}/api/protected`)
        .then(response => {
            userInfo.value = {
                ...response.data.user,
                avatar: response.data.user.avatar || 'https://jbagy.me/wp-content/uploads/2025/03/anh-avatar-zalo-cute-4.jpg'
            };
            // Khi lấy được thông tin user, gán vào form update profile
            profileForm.name = userInfo.value.name;
            profileForm.email = userInfo.value.email;
            console.log(userInfo.value);
        })
        .catch(error => {
            console.error('Error fetching user info:', error.response ? error.response.data : error.message);
            userInfo.value = {
              id: 'USERID001',
              name: 'John Doe',
              role: 'Phát triển viên',
              email: 'john.doe@example.com',
              avatar: 'https://jbagy.me/wp-content/uploads/2025/03/anh-avatar-zalo-cute-4.jpg'
            };
            // Gán placeholder vào form update profile
            profileForm.name = userInfo.value.name;
            profileForm.email = userInfo.value.email;
        });
};
const closeProfile = () => {
  router.push('home2'); // Chuyển hướng về trang chính
  //window.location.href = '/'; // Ví dụ: chuyển đến trang chính
  // Thêm logic để đóng hoặc ẩn component này
};

onMounted(fetchUserInfo);
</script>

