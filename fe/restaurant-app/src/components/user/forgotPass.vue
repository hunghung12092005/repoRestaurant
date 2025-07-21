<template>
  <loading v-if="isLoading"></loading>

  <div class="main-forgot-password-layout">
    <div class="forgot-password-image-panel">
      <img src="https://images.unsplash.com/photo-1517840901100-8179e982acb7?q=80&w=2940&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" alt="Hotel Lobby" class="side-image" />
    </div>

    <div class="forgot-password-form-panel">
      <div v-if="!otpSent" class="form-container">
        <div class="logo-container">Quên Mật Khẩu</div>

        <form @submit.prevent="sendOtp">
          <div class="form-group">
            <label for="email">Email</label>
            <input
              v-model="email"
              type="email"
              id="email"
              name="email"
              placeholder="Nhập địa chỉ email của bạn"
              required
            />
          </div>

          <button class="form-submit-btn" type="submit">Gửi mã OTP</button>
        </form>

        <p class="signup-link">
          Bạn đã có tài khoản?
          <router-link to="/login" class="text-black">Đăng nhập ngay</router-link>
        </p>

        <div v-if="message" :class="['alert', 'alert-warning', 'fade', 'show', { 'd-none': !message }]" role="alert">
          <strong>Thông báo!</strong> {{ message }}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" @click="message = ''"></button>
        </div>
      </div>

      <div v-if="otpSent && !otpVerified" class="form-container">
        <div class="logo-container">Xác Thực OTP</div>

        <form @submit.prevent="verifyOtp">
          <div class="form-group">
            <label for="otp">Mã OTP</label>
            <input
              v-model="otp"
              type="text"
              id="otp"
              placeholder="Nhập mã OTP bạn đã nhận"
              required
            />
          </div>

          <button class="form-submit-btn" type="submit">Xác thực</button>
        </form>

        <div v-if="message" :class="['alert', 'alert-warning', 'fade', 'show', { 'd-none': !message }]" role="alert">
          <strong>Thông báo!</strong> {{ message }}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" @click="message = ''"></button>
        </div>
      </div>

      <div v-if="otpVerified" class="form-container">
        <div class="logo-container">Đặt Lại Mật Khẩu</div>

        <form @submit.prevent="resetPassword">
          <div class="form-group">
            <label for="newPassword">Mật khẩu mới</label>
            <input
              v-model="newPassword"
              type="password"
              id="newPassword"
              placeholder="Nhập mật khẩu mới"
              required
            />
          </div>

          <div class="form-group">
            <label for="confirmPassword">Xác nhận mật khẩu</label>
            <input
              v-model="confirmPassword"
              type="password"
              id="confirmPassword"
              placeholder="Xác nhận mật khẩu mới"
              required
            />
          </div>

          <button class="form-submit-btn" type="submit">Đặt lại mật khẩu</button>
        </form>

        <div v-if="message" :class="['alert', 'alert-warning', 'fade', 'show', { 'd-none': !message }]" role="alert">
          <strong>Thông báo!</strong> {{ message }}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" @click="message = ''"></button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.main-forgot-password-layout {
  display: flex;
  min-height: 100vh;
  background-color: #f0f2f5; /* Light background for the overall page */
  overflow: hidden; /* Prevent content overflow */
}

.forgot-password-image-panel {
  flex: 2; /* Takes 2/3 of the space, adjust as needed (e.g., flex: 8 for 8/12) */
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #e0e0e0; /* Placeholder background */
}

.forgot-password-form-panel {
  flex: 1; /* Takes 1/3 of the space (e.g., flex: 4 for 4/12) */
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px; /* Add some padding around the form */
}

.side-image {
  width: 100%;
  height: 100%;
  object-fit: cover; /* Ensures the image covers the area without distortion */
  display: block;
}

/* Responsive adjustments */
@media (max-width: 992px) {
  .main-forgot-password-layout {
    flex-direction: column; /* Stack vertically on smaller screens */
  }

  .forgot-password-image-panel {
    flex: none; /* Remove flex sizing */
    width: 100%;
    height: 250px; /* Give the image a fixed height on smaller screens */
  }

  .forgot-password-form-panel {
    flex: none;
    width: 100%;
    padding: 15px; /* Adjust padding for smaller screens */
  }

  .form-container {
    margin: 20px auto; /* Adjust margin for mobile */
    max-width: 95%; /* Make form more responsive */
  }
}

/* --- Original Uiverse.io Styles (with minor adjustments) --- */
.form-container {
    /* Removed margin: 100px auto; as it's handled by flexbox centering */
    max-width: 400px; /* Set a max-width for the form itself */
    background-color: #fff;
    padding: 32px 24px;
    font-size: 14px;
    font-family: inherit;
    color: #212121;
    display: flex;
    flex-direction: column;
    gap: 20px;
    box-sizing: border-box;
    border-radius: 10px;
    box-shadow: 0px 0px 3px rgba(0, 0, 0, 0.084), 0px 2px 3px rgba(0, 0, 0, 0.168);
    width: 100%; /* Ensure it takes full width within its flex container */
}

.form-container button:active {
    scale: 0.95;
}

.form-container .logo-container {
    text-align: center;
    font-weight: 600;
    font-size: 24px; /* Slightly larger heading for better visual hierarchy */
    color: #333;
    margin-bottom: 10px;
}

/* Added form tag for better structure and accessibility */
.form-container form {
  display: flex;
  flex-direction: column;
  gap: 20px; /* Match the gap of the parent container for consistent spacing */
}

.form-container .form-group {
    display: flex;
    flex-direction: column;
    gap: 8px; /* Slightly increased gap for better spacing */
}

.form-container .form-group label {
    display: block;
    margin-bottom: 0;
    font-weight: 500;
    color: #555;
}

.form-container .form-group input {
    width: 100%;
    padding: 12px 16px;
    border-radius: 6px;
    font-family: inherit;
    border: 1px solid #ccc;
    box-sizing: border-box;
}

.form-container .form-group input::placeholder {
    opacity: 0.7; /* Make placeholder text a bit more visible */
    color: #888;
}

.form-container .form-group input:focus {
    outline: none;
    border-color: #007bff; /* Primary blue for focus border */
    box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.25); /* Subtle focus shadow */
}

.form-container .form-submit-btn {
    display: flex;
    justify-content: center;
    align-items: center;
    font-family: inherit;
    color: #fff;
    background-color: #007bff; /* Primary blue for action button */
    border: none;
    width: 100%;
    padding: 12px 16px;
    font-size: 16px; /* Slightly larger font */
    gap: 8px;
    margin-top: 12px; /* Adjusted margin */
    cursor: pointer;
    border-radius: 6px;
    box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
    transition: background-color 0.2s ease, transform 0.2s ease;
}

.form-container .form-submit-btn:hover {
    background-color: #0056b3; /* Darker blue on hover */
    transform: translateY(-1px);
}

.form-container .signup-link {
    align-self: center;
    font-weight: 400;
    color: #777;
    margin-top: 10px; /* Add some spacing above the link */
}

.form-container .signup-link .text-black {
    color: #007bff; /* Consistent link color */
    text-decoration: none;
    font-weight: 500;
    transition: color 0.2s ease;
}

.form-container .signup-link .text-black:hover {
    text-decoration: underline;
    color: #0056b3;
}

/* Alert styles - improved visibility and spacing */
.alert {
    padding: 12px 20px;
    margin-top: 15px; /* Add some space above the alert */
    border-radius: 8px;
    font-size: 14px;
    display: flex; /* Use flex for alignment of text and close button */
    align-items: center;
    justify-content: space-between;
    word-break: break-word; /* Prevent long words from overflowing */
}

.alert-warning {
    background-color: #fff3cd;
    border: 1px solid #ffeeba;
    color: #856404;
}

.alert-success { /* Add this if you intend to use success alerts */
    background-color: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
}

.btn-close {
    background: none;
    border: none;
    font-size: 18px;
    cursor: pointer;
    color: inherit; /* Inherit color from alert */
    margin-left: 15px;
    opacity: 0.7;
    transition: opacity 0.2s ease;
}

.btn-close:hover {
    opacity: 1;
}

/* Ensure the alert doesn't show when message is empty for better control */
.d-none {
    display: none !important;
}

/* Styles for loading component */
loading {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(255, 255, 255, 0.8);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}
</style>

<script setup>
import { inject, ref } from 'vue';
import axios from 'axios';
import loading from '../loading.vue';
const isLoading = ref(false);
const email = ref('');
const otp = ref('');
const newPassword = ref('');
const confirmPassword = ref('');
const message = ref('');
const otpSent = ref(false);
const otpVerified = ref(false);
const apiUrl = inject('apiUrl')
const sendOtp = async () => {
    isLoading.value = true;
    try {
        const response = await axios.post(`${apiUrl}/api/send-otp`, { email: email.value });
        message.value = response.data.message;
        otpSent.value = true;
    } catch (error) {
        message.value = error.response.data.message;
    } finally {
        isLoading.value = false;
    }
};

const verifyOtp = async () => {
    isLoading.value = true;
    try {
        const response = await axios.post(`${apiUrl}/api/verify-otp`, { otp: otp.value, email: email.value });
        message.value = response.data.message;
        otpVerified.value = response.data.success;
    } catch (error) {
        message.value = error.response.data.message;
    } finally {
        isLoading.value = false;
    }
};

const resetPassword = async () => {
    isLoading.value = true;
    try {
        const response = await axios.post(`${apiUrl}/api/reset-password`, {
            email: email.value,
            new_password: newPassword.value,
            new_password_confirmation: confirmPassword.value,
        });
        message.value = response.data.message;
        window.location.href = 'http://127.0.0.1:5173/login';

    } catch (error) {
        message.value = error.response.data.message;
    } finally {
        isLoading.value = false;
    }
};
</script>