<template>
    <div class="auth-container">
        <h1 class="auth-title">{{ isLogin ? 'Đăng Nhập' : 'Đăng Ký' }}</h1>
        <form @submit.prevent="submitForm" class="auth-form">
            <!-- Alert Bootstrap -->
            <div id="alert" class="alert alert-dismissible fade" role="alert" style="display: none;">
                <span id="alertMessage"></span>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" v-model="email" required placeholder="Nhập email của bạn" />
            </div>
            <div class="form-group">
                <label for="password">Mật Khẩu:</label>
                <input type="password" id="password" v-model="password" required placeholder="Nhập mật khẩu" />
            </div>
            <div v-if="!isLogin" class="form-group">
                <label for="name">Tên:</label>
                <input type="text" id="name" v-model="name" required placeholder="Nhập tên của bạn" />
            </div>
            <button type="submit" class="submit-button">
                {{ isLogin ? 'Đăng Nhập' : 'Đăng Ký' }}
            </button>
            <div class="toggle-link" @click="toggleForm">
                <span v-if="isLogin" class="plain-text">Chưa có tài khoản? </span>
                <span v-if="!isLogin" class="plain-text">Đã có tài khoản? </span>
                <span class="link-text">{{ isLogin ? 'Đăng ký' : 'Đăng nhập' }}</span>
            </div>
            <button type="button" @click="loginWithGoogle" class="google-button">
                <svg class="google-icon" viewBox="0 0 24 24">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.51h5.84c-.25 1.37-.99 2.53-2.11 3.3v2.74h3.41c2-1.85 3.16-4.58 3.16-7.8z" fill="#4285F4"/>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.41-2.74c-1.01.68-2.3 1.09-3.87 1.09-2.97 0-5.49-2.01-6.39-4.71H2.36v2.96A11.97 11.97 0 0012 23z" fill="#34A853"/>
                    <path d="M5.61 14.24c-.23-.68-.36-1.41-.36-2.24s.13-1.56.36-2.24V6.8H2.36A11.97 11.97 0 000 12c0 1.86.43 3.62 1.19 5.16l4.42-2.92z" fill="#FBBC05"/>
                    <path d="M12 4.91c1.62 0 3.07.56 4.21 1.66l3.15-3.15C17.46 1.8 15 1 12 1 7.42 1 3.53 3.25 2.36 6.8l4.25 2.96c.9-2.7 3.42-4.75 6.39-4.75z" fill="#EA4335"/>
                </svg>
                Đăng nhập với Google
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
                    const response = await axios.post('http://127.0.0.1:8000/api/login', {
                        email: this.email,
                        password: this.password,
                    });
                    const token = response.data.token;
                    const user = response.data.user;
                    this.userInfo = user;
                    localStorage.setItem('token', token);
                    localStorage.setItem('userInfo', JSON.stringify(user));
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
                }
            } catch (error) {
                this.errorMessage = error.response?.data?.error || 'Đã xảy ra lỗi!';
            }
        },
        loginWithGoogle() {
            window.location.href = 'http://127.0.0.1:8000/auth/google';
        },
    },
};

function showAlert(message, type) {
    const alert = document.getElementById('alert');
    const alertMessage = document.getElementById('alertMessage');
    alertMessage.textContent = message;
    alert.className = `alert alert-${type} alert-dismissible fade show`;
    alert.style.display = 'block';
    alert.style.opacity = '0';
    alert.style.transition = 'opacity 0.5s ease-in-out';

    setTimeout(() => {
        alert.style.opacity = '1';
    }, 10);

    setTimeout(() => {
        alert.style.opacity = '0';
        setTimeout(() => {
            alert.style.display = 'none';
        }, 500);
    }, 2000);
}
</script>

<style scoped>
.auth-container {
    max-width: 450px;
    margin: 50px auto;
    padding: 30px;
    border-radius: 12px;
    background: linear-gradient(135deg, #ffffff, #f8f9fa);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
}

.auth-container:hover {
    transform: translateY(-5px);
}

.auth-title {
    font-size: 2rem;
    font-weight: 700;
    color: #2c3e50;
    text-align: center;
    margin-bottom: 20px;
}

.auth-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

label {
    font-size: 1rem;
    font-weight: 600;
    color: #34495e;
}

input {
    padding: 12px 15px;
    border: 1px solid #dfe6e9;
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.3s ease;
    background-color: #f9fbfc;
}

input:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 8px rgba(0, 123, 255, 0.3);
    background-color: #fff;
}

.submit-button, .google-button {
    padding: 12px;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.submit-button {
    background-color: #007bff;
    color: white;
}

.submit-button:hover {
    background-color: #0056b3;
    transform: translateY(-2px);
}

.google-button {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    background-color: #ffffff;
    color: #2c3e50;
    border: 1px solid #dfe6e9;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.google-button:hover {
    background-color: #f1f3f5;
    transform: translateY(-2px);
}

.google-icon {
    width: 24px;
    height: 24px;
}

.toggle-link {
    font-size: 0.9rem;
    text-align: center;
    cursor: pointer;
}

.plain-text {
    color: #000000; /* Màu đen cho văn bản thường */
}

.link-text {
    color: #007bff;
    transition: color 0.3s ease;
}

.link-text:hover {
    color: #0056b3;
    text-decoration: underline;
}

.error-message {
    color: #e74c3c;
    font-size: 0.9rem;
    text-align: center;
    margin-top: 15px;
}

.user-info {
    text-align: center;
    margin-top: 20px;
}

.alert {
    padding: 10px;
    border-radius: 8px;
    font-size: 0.9rem;
    text-align: center;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
}

.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
}

/* Responsive Design */
@media (max-width: 480px) {
    .auth-container {
        margin: 20px;
        padding: 20px;
    }

    .auth-title {
        font-size: 1.5rem;
    }

    .submit-button, .google-button {
        font-size: 0.9rem;
        padding: 10px;
    }

    .toggle-link {
        font-size: 0.85rem;
    }
}
</style>