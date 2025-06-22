<template>
    <loading v-if="isLoading"></loading>
    <div class="mt-5">
        <div v-if="!otpSent">
            <div class="form-container">
                <div class="logo-container">
                    Forgot Password
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input v-model="email" type="text" id="email" name="email" placeholder="Enter your email"
                        required="">
                </div>

                <button class="form-submit-btn" type="submit" @click="sendOtp">Send Email</button>

                <p class="signup-link">
                    Don't have an account? <router-link to="/login" class="text-black">Login Now</router-link> </p>
            </div>
            <!-- <input v-model="email" type="email" placeholder="Nhập email của bạn" />
            <button @click="sendOtp">Gửi mã OTP</button> -->
            <p v-if="message">
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <strong>Thông báo!</strong> {{ message }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            </p>
        </div>

        <div v-if="otpSent && !otpVerified">
            <div class="form-container">
                <div class="logo-container">
                    Xác Thực OTP
                </div>

                <div class="form-group">
                    <label for="email">OTP</label>
                    <input v-model="otp" type="text"  placeholder="Nhập OTP"
                        required="">
                </div>

                <button class="form-submit-btn" type="submit" @click="verifyOtp">Xác thực</button>

                
            </div>
            <!-- <input v-model="otp" type="text" placeholder="Nhập mã OTP" />
            <button @click="verifyOtp">Xác thực</button> -->
            <p v-if="message">
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <strong>Thông báo!</strong> {{ message }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            </p>
        </div>

        <div v-if="otpVerified">
            <div class="form-container">
                <div class="logo-container">
                    Đặt lại mật khẩu
                </div>

                <div class="form-group">
                    <label for="email">Password</label>
                    <input v-model="newPassword" type="password"  placeholder="Nhập newPassword"
                        required="">
                    <input v-model="confirmPassword" type="password"  placeholder="Nhập confirmPassword"
                        required="">
                </div>

                <button class="form-submit-btn" type="submit" @click="resetPassword">Đặt lại mật khẩu</button>

                
            </div>
            <!-- <h2>Đặt lại mật khẩu</h2>
            <input v-model="newPassword" type="password" placeholder="Mật khẩu mới" />
            <input v-model="confirmPassword" type="password" placeholder="Xác nhận mật khẩu" />
            <button @click="resetPassword">Đặt lại mật khẩu</button> -->
            <p v-if="message">
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <strong>Thông báo!</strong> {{ message }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            </p>
        </div>
    </div>
</template>

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

<style scoped>
/* From Uiverse.io by gharsh11032000 */
.form-container {
    margin: 100px auto;
    max-width: 400px;
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
}

.form-container button:active {
    scale: 0.95;
}

.form-container .logo-container {
    text-align: center;
    font-weight: 600;
    font-size: 18px;
}

.form-container .form {
    display: flex;
    flex-direction: column;
}

.form-container .form-group {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.form-container .form-group label {
    display: block;
    margin-bottom: 5px;
}

.form-container .form-group input {
    width: 100%;
    padding: 12px 16px;
    border-radius: 6px;
    font-family: inherit;
    border: 1px solid #ccc;
}

.form-container .form-group input::placeholder {
    opacity: 0.5;
}

.form-container .form-group input:focus {
    outline: none;
    border-color: #1778f2;
}

.form-container .form-submit-btn {
    display: flex;
    justify-content: center;
    align-items: center;
    font-family: inherit;
    color: #fff;
    background-color: #212121;
    border: none;
    width: 100%;
    padding: 12px 16px;
    font-size: inherit;
    gap: 8px;
    margin: 12px 0;
    cursor: pointer;
    border-radius: 6px;
    box-shadow: 0px 0px 3px rgba(0, 0, 0, 0.084), 0px 2px 3px rgba(0, 0, 0, 0.168);
}

.form-container .form-submit-btn:hover {
    background-color: #313131;
}

.form-container .link {
    color: #1778f2;
    text-decoration: none;
}

.form-container .signup-link {
    align-self: center;
    font-weight: 500;
}

.form-container .signup-link .link {
    font-weight: 400;
}

.form-container .link:hover {
    text-decoration: underline;
}


/* Thêm CSS nếu cần */
</style>