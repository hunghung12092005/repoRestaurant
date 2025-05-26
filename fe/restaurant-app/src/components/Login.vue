<template>
    <div class="auth-container">
        <h1>{{ isLogin ? 'Đăng Nhập' : 'Đăng Ký' }}</h1>
        <form @submit.prevent="submitForm" class="auth-form">
            <!-- Alert Bootstrap -->
            <div id="alert" class="alert alert-dismissible fade" role="alert" style="display: none;">
                <span id="alertMessage"></span>

            </div>
            <div class="form-group">
                <label>Email:</label>
                <input type="email" v-model="email" required />
            </div>
            <div class="form-group">
                <label>Mật Khẩu:</label>
                <input type="password" v-model="password" required />
            </div>
            <div v-if="!isLogin" class="form-group">
                <label>Tên:</label>
                <input type="text" v-model="name" required />
            </div>
            <button type="submit" class="submit-button">
                {{ isLogin ? 'Đăng Nhập' : 'Đăng Ký' }}
            </button>
            <button type="button" @click="toggleForm" class="toggle-button">
                Chuyển sang {{ isLogin ? 'Đăng Ký' : 'Đăng Nhập' }}
            </button>
            <button type="button" @click="loginWithGoogle" class="google-button">
                Đăng Nhập với Google
            </button>
        </form>
        <div v-if="errorMessage" class="error-message">{{ errorMessage }}</div>
        <div v-if="userInfo" class="user-info">
            <h2>Xin chào, {{ userInfo.name }}!</h2>
        </div>
    </div>

</template>

<script>
import axios from 'axios';

export default {
    data() {
        return {
            isLogin: true,
            email: '',
            password: '',
            name: '',
            errorMessage: '',
            userInfo: null,
        };
    },
    methods: {
        toggleForm() {
            this.isLogin = !this.isLogin;
            this.errorMessage = '';
        },
        async submitForm() {
            try {
                if (this.isLogin) {
                    // console.log('loginnnnnnn');
                    const response = await axios.post('http://127.0.0.1:8000/api/login', {
                        email: this.email,
                        password: this.password,
                    });
                    const token = response.data.token;
                    const user = response.data.user;
                    this.userInfo = user;
                    localStorage.setItem('token', token);
                    localStorage.setItem('userInfo', JSON.stringify(user));
                    // Chuyển hướng và tải lại trang
                    window.location.href = '/';
                } else {
                    const response = await axios.post('http://127.0.0.1:8000/api/register', {
                        name: this.name,
                        email: this.email,
                        password: this.password,
                    });
                    const token = response.data.token;
                    localStorage.setItem('token', token);
                    showAlert(response.data.message, 'success');
                    //this.$router.push('/login');
                }
            } catch (error) {
                this.errorMessage = error.response.data.error || 'Đã xảy ra lỗi!';
            }
        },
        loginWithGoogle() {
            window.location.href = 'http://127.0.0.1:8000/auth/google'; // Thay đổi URL theo route của bạn
        },

    },
};
// Hàm hiển thị alert
function showAlert(message, type) {
    const alert = document.getElementById('alert');
    const alertMessage = document.getElementById('alertMessage');
    alertMessage.textContent = message;
    alert.className = `alert alert-${type} alert-dismissible fade show`;
    alert.style.display = 'block';

    // Ẩn thông báo sau 2 giây
    setTimeout(() => {
        alert.style.display = 'none';
    }, 2000); // 2000 milliseconds = 2 seconds
}

</script>


<style scoped>
.auth-container {
    max-width: 400px;
    margin: auto;
    padding: 20px;
    border: 1px solid #ccc;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    background-color: #fff;
}

.auth-form {
    display: flex;
    flex-direction: column;
}

.form-group {
    margin-bottom: 15px;
}

label {
    margin-bottom: 5px;
    font-weight: bold;
}

input {
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
}

.submit-button,
.toggle-button {
    padding: 10px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    background-color: #007bff;
    color: white;
    font-size: 16px;
}

.submit-button:hover,
.toggle-button:hover {
    background-color: #0056b3;
}

.error-message {
    color: red;
    margin-top: 10px;
}
</style>
