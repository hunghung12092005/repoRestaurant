<template>
<<<<<<< HEAD
    <!-- <div class="auth-container">
        <h1>{{ isLogin ? 'Đăng Nhập' : 'Đăng Ký' }}</h1>
=======
    <div class="auth-container">
        <h1 class="auth-title">{{ isLogin ? 'Đăng Nhập' : 'Đăng Ký' }}</h1>
>>>>>>> bde4c42f2a8e7fa66d8337d8e58e747b46edfe1a
        <form @submit.prevent="submitForm" class="auth-form">
            <!-- Alert Bootstrap -->
    <!-- <div id="alert" class="alert alert-dismissible fade" role="alert" style="display: none;">
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
        <div v-if="errorMessage" class="alert alert-warning alert-dismissible fade show" role="alert">
            <strong>{{ errorMessage }}!</strong> Nhập lại.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <div v-if="errorMessage" class="alert alert-danger" role="alert">
            {{ errorMessage }}
        </div>
        <div v-if="userInfo" class="user-info">
            <h2>Xin chào, {{ userInfo.name }}!</h2>
<<<<<<< HEAD
        </div> -->
    <div class="main-login">
        <form @submit.prevent="submitForm" class="form">
            <div v-if="errorMessage" class="alert alert-warning alert-dismissible fade show" role="alert">
                {{ errorMessage }}
            </div>
            <div class="flex-column">
                <label>Email </label>
            </div>
            <div class="inputForm">
                <svg height="20" viewBox="0 0 32 32" width="20" xmlns="http://www.w3.org/2000/svg">
                    <g id="Layer_3" data-name="Layer 3">
                        <path
                            d="m30.853 13.87a15 15 0 0 0 -29.729 4.082 15.1 15.1 0 0 0 12.876 12.918 15.6 15.6 0 0 0 2.016.13 14.85 14.85 0 0 0 7.715-2.145 1 1 0 1 0 -1.031-1.711 13.007 13.007 0 1 1 5.458-6.529 2.149 2.149 0 0 1 -4.158-.759v-10.856a1 1 0 0 0 -2 0v1.726a8 8 0 1 0 .2 10.325 4.135 4.135 0 0 0 7.83.274 15.2 15.2 0 0 0 .823-7.455zm-14.853 8.13a6 6 0 1 1 6-6 6.006 6.006 0 0 1 -6 6z">
                        </path>
                    </g>
                </svg>
                <input type="text" class="input" v-model="email" placeholder="Enter your Email">
            </div>
            <div class="flex-column" v-if="!isLogin">
                <label>Name </label>
            </div>
            <div class="inputForm" v-if="!isLogin">
                <svg height="20" viewBox="-64 0 512 512" width="20" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="m336 512h-288c-26.453125 0-48-21.523438-48-48v-224c0-26.476562 21.546875-48 48-48h288c26.453125 0 48 21.523438 48 48v224c0 26.476562-21.546875 48-48 48zm-288-288c-8.8125 0-16 7.167969-16 16v224c0 8.832031 7.1875 16 16 16h288c8.8125 0 16-7.167969 16-16v-224c0-8.832031-7.1875-16-16-16zm0 0">
                    </path>
                    <path
                        d="m304 224c-8.832031 0-16-7.167969-16-16v-80c0-52.929688-43.070312-96-96-96s-96 43.070312-96 96v80c0 8.832031-7.167969 16-16 16s-16-7.167969-16-16v-80c0-70.59375 57.40625-128 128-128s128 57.40625 128 128v80c0 8.832031-7.167969 16-16 16zm0 0">
                    </path>
                </svg>
                <input type="text" class="input" placeholder="Name" v-model="name" required>

            </div>
            <div class="flex-column">
                <label>Password </label>
            </div>
            <div class="inputForm">
                <svg height="20" viewBox="-64 0 512 512" width="20" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="m336 512h-288c-26.453125 0-48-21.523438-48-48v-224c0-26.476562 21.546875-48 48-48h288c26.453125 0 48 21.523438 48 48v224c0 26.476562-21.546875 48-48 48zm-288-288c-8.8125 0-16 7.167969-16 16v224c0 8.832031 7.1875 16 16 16h288c8.8125 0 16-7.167969 16-16v-224c0-8.832031-7.1875-16-16-16zm0 0">
                    </path>
                    <path
                        d="m304 224c-8.832031 0-16-7.167969-16-16v-80c0-52.929688-43.070312-96-96-96s-96 43.070312-96 96v80c0 8.832031-7.167969 16-16 16s-16-7.167969-16-16v-80c0-70.59375 57.40625-128 128-128s128 57.40625 128 128v80c0 8.832031-7.167969 16-16 16zm0 0">
                    </path>
                </svg>
                <input type="password" class="input" v-model="password" placeholder="Enter your Password">
                <svg viewBox="0 0 576 512" height="1em" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M288 32c-80.8 0-145.5 36.8-192.6 80.6C48.6 156 17.3 208 2.5 243.7c-3.3 7.9-3.3 16.7 0 24.6C17.3 304 48.6 356 95.4 399.4C142.5 443.2 207.2 480 288 480s145.5-36.8 192.6-80.6c46.8-43.5 78.1-95.4 93-131.1c3.3-7.9 3.3-16.7 0-24.6c-14.9-35.7-46.2-87.7-93-131.1C433.5 68.8 368.8 32 288 32zM144 256a144 144 0 1 1 288 0 144 144 0 1 1 -288 0zm144-64c0 35.3-28.7 64-64 64c-7.1 0-13.9-1.2-20.3-3.3c-5.5-1.8-11.9 1.6-11.7 7.4c.3 6.9 1.3 13.8 3.2 20.7c13.7 51.2 66.4 81.6 117.6 67.9s81.6-66.4 67.9-117.6c-11.1-41.5-47.8-69.4-88.6-71.1c-5.8-.2-9.2 6.1-7.4 11.7c2.1 6.4 3.3 13.2 3.3 20.3z">
                    </path>
                </svg>
            </div>
            <div class="flex-row">
                <div>
                    <input type="checkbox">
                    <label>Remember me </label>
                </div>
                <span class="span">Forgot password?</span>
            </div>
            <div id="alert"></div>
            <button class="btn-main" type="submit">
                {{ isLogin ? 'Đăng Nhập' : 'Đăng Ký' }}
                <div class="arrow-wrapper">
                    <div class="arrow"></div>
                </div>
            </button>
            <!-- <button type="submit" class="button-submit">{{ isLogin ? 'Đăng Nhập' : 'Đăng Ký' }}</button> -->
            <div v-if="alertMessage" class="alert alert-success alert-dismissible fade show" role="alert">
                {{ alertMessage }}
            </div>
            <p class="p">{{ isLogin ? 'Chưa có tài khoản ?' : 'Tài khoản sẵn sàng' }} <span @click="toggleForm"
                    class="span">Chuyển sang
                    {{ isLogin ? 'Đăng Ký' : 'Đăng Nhập' }}</span>

            </p>

            <p class="p line">Or With</p>

            <div class="flex-row">
                <button type="button" class="btn google" @click="loginWithGoogle">
                    <svg version="1.1" width="20" id="Layer_1" xmlns="http://www.w3.org/2000/svg"
                        xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 512 512"
                        style="enable-background:new 0 0 512 512;" xml:space="preserve">
                        <path style="fill:#FBBB00;" d="M113.47,309.408L95.648,375.94l-65.139,1.378C11.042,341.211,0,299.9,0,256
	c0-42.451,10.324-82.483,28.624-117.732h0.014l57.992,10.632l25.404,57.644c-5.317,15.501-8.215,32.141-8.215,49.456
	C103.821,274.792,107.225,292.797,113.47,309.408z"></path>
                        <path style="fill:#518EF8;" d="M507.527,208.176C510.467,223.662,512,239.655,512,256c0,18.328-1.927,36.206-5.598,53.451
	c-12.462,58.683-45.025,109.925-90.134,146.187l-0.014-0.014l-73.044-3.727l-10.338-64.535
	c29.932-17.554,53.324-45.025,65.646-77.911h-136.89V208.176h138.887L507.527,208.176L507.527,208.176z"></path>
                        <path style="fill:#28B446;" d="M416.253,455.624l0.014,0.014C372.396,490.901,316.666,512,256,512
	c-97.491,0-182.252-54.491-225.491-134.681l82.961-67.91c21.619,57.698,77.278,98.771,142.53,98.771
	c28.047,0,54.323-7.582,76.87-20.818L416.253,455.624z"></path>
                        <path style="fill:#F14336;" d="M419.404,58.936l-82.933,67.896c-23.335-14.586-50.919-23.012-80.471-23.012
	c-66.729,0-123.429,42.957-143.965,102.724l-83.397-68.276h-0.014C71.23,56.123,157.06,0,256,0
	C318.115,0,375.068,22.126,419.404,58.936z"></path>

                    </svg>

                    Google

                </button>
            </div>
        </form>
    </div>
    
    <Footer></Footer>
=======
        </div>
    </div>
>>>>>>> bde4c42f2a8e7fa66d8337d8e58e747b46edfe1a
</template>

<script setup>
import axios from 'axios';
import { inject, ref } from 'vue';
const apiUrl = inject('apiUrl');
import Footer from './Footer.vue';
const isLogin = ref(true);
const email = ref('');
const password = ref('');
const name = ref('');
const errorMessage = ref('');
const alertMessage = ref('');
const userInfo = ref(null);
const toggleForm = () => {
    isLogin.value = !isLogin.value;
    errorMessage.value = '';
};
const submitForm = async () => {
    try {
        if (isLogin.value) {
            const response = await axios.post(`${apiUrl}/api/login`, {
                email: email.value,
                password: password.value,
            });
            const token = response.data.token;
            const user = response.data.user;
            userInfo.value = user; // Sửa this.userInfo thành userInfo.value
            localStorage.setItem('tokenJwt', token);
            localStorage.setItem('userInfo', JSON.stringify(user));
            window.location.href = '/';
        } else {
            const response = await axios.post(`${apiUrl}/api/register`, {
                name: name.value, // Sửa this.name thành name.value
                email: email.value,
                password: password.value,
            });
            const token = response.data.token;
            localStorage.setItem('tokenJwt', token);
            console.log(response.data.message);
            showAlert(response.data.message);
            // this.$router.push('/login');
        }
    } catch (error) {
        if (error.response && error.response.data) {
            // Kiểm tra xem có lỗi trong password không
            if (error.response.data.errors && error.response.data.errors.password) {
                showError(error.response.data.errors.password[0]); // Lấy thông báo lỗi đầu tiên
            } else {
                showError(error.response.data.message || 'Sai pass!');
            }
        } else {
            //errorMessage.value = 'Đã xảy ra lỗi không xác định!';
        }
    }
};
const loginWithGoogle = () => {
    window.location.href = 'http://127.0.0.1:8000/auth/google';
};

function showError(message) {
    errorMessage.value = message;

    // Ẩn thông báo sau 2 giây
    setTimeout(() => {
        errorMessage.value = ''; // Đặt lại thông báo
    }, 2000);
}

// Hàm hiển thị alert
function showAlert(message) {
    alertMessage.value = message;

    // Ẩn thông báo sau 2 giây
    setTimeout(() => {
        alertMessage.value = ''; // Đặt lại thông báo
    }, 2000);

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
<<<<<<< HEAD
<style scoped>
.main-login {
    position: relative;
    background-color: rgba(15, 141, 172, 0.2);
=======

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
>>>>>>> bde4c42f2a8e7fa66d8337d8e58e747b46edfe1a
}

.main-login::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('https://cf.bstatic.com/xdata/images/hotel/max1280x900/295360783.jpg?k=cb6a93f5158c7887b4e8e92e1f901cf96e049f40f5168617a96420deafe6a5e8&o=&hp=1');
    background-size: cover; /* Đảm bảo hình ảnh phủ toàn bộ */
    opacity: 0.5; /* Độ mờ của hình nền */
    z-index: -1; /* Đặt lớp phủ phía dưới nội dung */
}

.form {
    display: flex;
    flex-direction: column;
<<<<<<< HEAD
    gap: 10px;
    padding: 30px;
    width: 450px;
    margin: 0 auto;
    border-radius: 20px;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
}

::placeholder {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
}

.form button {
    align-self: flex-end;

}

.flex-column>label {
    color: #151717;
    font-weight: 600;
}

.inputForm {
    border: 1.5px solid pink;
    background-color: white;
    border-radius: 10px;
    height: 50px;
    display: flex;
    align-items: center;
    padding-left: 10px;
    transition: 0.2s ease-in-out;
}

.input {
    margin-left: 10px;
    border-radius: 10px;
    border: none;
    background-color: white;

    width: 85%;
    height: 100%;
}

.input:focus {
    outline: none;
}

.inputForm:focus-within {
    border: 1.5px solid #2d79f3;
}

.flex-row {
    display: flex;
    flex-direction: row;
    align-items: center;
    gap: 10px;
    justify-content: space-between;
}

.flex-row>div>label {
    font-size: 14px;
    color: black;
    font-weight: 400;
}

.span {
    font-size: 14px;
    margin-left: 5px;
    color: white;
    font-weight: 500;
    cursor: pointer;
}

.button-submit {
    margin: 20px 0 10px 0;
    background-color: #151717;
    border: none;
    color: white;
    font-size: 15px;
    font-weight: 500;
    border-radius: 10px;
    height: 50px;
    width: 100%;
    cursor: pointer;
}

.button-submit:hover {
    background-color: #252727;
}

.p {
    text-align: center;
    color: black;
    font-size: 14px;
    margin: 5px 0;
}

.btn {
    margin-top: 10px;
    width: 100%;
    height: 50px;
    border-radius: 10px;
    display: flex;
    justify-content: center;
    align-items: center;
    font-weight: 500;
    gap: 10px;
    border: 1px solid #ededef;
    background-color: white;
    cursor: pointer;
    transition: 0.2s ease-in-out;
=======
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
>>>>>>> bde4c42f2a8e7fa66d8337d8e58e747b46edfe1a
}

.btn:hover {
    border: 1px solid #2d79f3;
    ;
}
</style>
