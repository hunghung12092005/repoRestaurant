<template>
  <loading v-if="isLoading"></loading>
  <div class="main-login-container">
    <div class="login-image-wrapper">
      <img src="https://studiochupanhdep.com/Upload/Newsimages/phong-khach-san-tt-studio.jpg" alt="Hotel View" class="side-image">
    </div>
    <div class="login-form-wrapper">
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
          <input type="text" class="input" v-model="email" placeholder="Enter your Email" required>
        </div>
        <div class="flex-column" v-if="!isLogin">
          <label>Name </label>
        </div>
        <div v-if="qrCode">
          <h5 v-if="!isLogin">Mã QR của bạn (lưu vào nơi an toàn khi đăng nhập gửi mã qr lên đăng nhập)</h5>
          <img v-if="!isLogin" :src="qrCode" alt="QR Code" />
          <button v-if="!isLogin" class="btn btn-info m-2" @click="downloadQRCode">Tải về mã QR</button>
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
          <input type="password" class="input" v-model="password" placeholder="Enter your Password" required>
          <svg viewBox="0 0 576 512" height="1em" xmlns="http://www.w3.org/2000/svg">
            <path
              d="M288 32c-80.8 0-145.5 36.8-192.6 80.6C48.6 156 17.3 208 2.5 243.7c-3.3 7.9-3.3 16.7 0 24.6C17.3 304 48.6 356 95.4 399.4C142.5 443.2 207.2 480 288 480s145.5-36.8 192.6-80.6c46.8-43.5 78.1-95.4 93-131.1c3.3-7.9 3.3-16.7 0-24.6c-14.9-35.7-46.2-87.7-93-131.1C433.5 68.8 368.8 32 288 32zM144 256a144 144 0 1 1 288 0 144 144 0 1 1 -288 0zm144-64c0 35.3-28.7 64-64 64c-7.1 0-13.9-1.2-20.3-3.3c-5.5-1.8-11.9 1.6-11.7 7.4c.3 6.9 1.3 13.8 3.2 20.7c13.7 51.2 66.4 81.6 117.6 67.9s81.6-66.4 67.9-117.6c-11.1-41.5-47.8-69.4-88.6-71.1c-5.8-.2-9.2 6.1-7.4 11.7c2.1 6.4 3.3 13.2 3.3 20.3z">
            </path>
          </svg>
        </div>
        <div class="cf-turnstile" data-sitekey="0x4AAAAAABhcDWU29f0qZu4n"></div>

        <div class="flex-row">
          <!-- <div>
            <input type="checkbox">
            <label>Remember me </label>
          </div> -->
          <span class="span"><router-link to="/forgotPass">Forgot password?</router-link></span>
        </div>
        <div id="alert"></div>
        <button class="btn-main" type="submit">
          {{ isLogin ? 'Đăng Nhập' : 'Đăng Ký' }}
         
        </button>
        <div v-if="alertMessage" class="alert alert-success alert-dismissible fade show" role="alert">
          {{ alertMessage }}
        </div>
        <p class="p">{{ isLogin ? 'Chưa có tài khoản ?' : 'Tài khoản sẵn sàng' }} <span @click="toggleForm"
            class="span">Chuyển sang
            {{ isLogin ? 'Đăng Ký' : 'Đăng Nhập' }}</span>
        </p>

        <p class="p line">Or With</p>

        <div class="flex-row">
          <button type="button" class="btnLogin google" @click="loginWithGoogle">
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
    
  </div>
</template>

<style scoped>
/* Main container for the two columns */
.main-login-container {
  display: flex;
  min-height: 100vh; /* Takes full viewport height */
  align-items: center; /* Vertically centers content */
  justify-content: center; /* Horizontally centers content */
  background-color: #f0f2f5; /* A light background color */
}

/* Wrapper for the login form (4 parts) */
.login-form-wrapper {
  flex: 4; /* Occupies 4 parts of the flex container */
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 20px;
}

/* The actual form styling */
.form {
  display: flex;
  flex-direction: column;
  gap: 10px;
  padding: 30px;
  max-width: 450px; /* Limit form width */
  width: 100%; /* Make it responsive within its wrapper */
  border-radius: 20px;
  background-color: white; /* Give the form a solid background */
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Add a subtle shadow */
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
}

/* Wrapper for the image (8 parts) */
.login-image-wrapper {
  flex: 8; /* Occupies 8 parts of the flex container */
  background-color: #ccc; /* Placeholder background */
  display: flex;
  justify-content: center;
  align-items: center;
  overflow: hidden; /* Ensure image doesn't overflow */
}

.side-image {
  width: 100%; /* Make the image responsive */
  height: 100%;
  flex: 2; /* Takes 2/3 of the space, adjust as needed (e.g., flex: 8 for 8/12) */
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #e0e0e0; /* Placeholder background */
}

/* Original main-login styles, adapted or removed as needed */
/* The original .main-login with the background image will likely be replaced by .main-login-container and .login-image-wrapper */
.main-login {
  display: none; /* Hide the original if you're using the new structure */
}


/* Responsive adjustments */
@media (max-width: 768px) {
  .main-login-container {
    flex-direction: column; /* Stack columns on smaller screens */
  }

  .login-form-wrapper,
  .login-image-wrapper {
    flex: none; /* Remove flex sizing */
    width: 100%; /* Take full width */
  }

  .login-image-wrapper {
    height: 250px; /* Give the image a fixed height on mobile */
  }
}

/* Existing styles from your template, ensure they are compatible or update as needed */
::placeholder {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
}

.form button {
    align-self: flex-end;
}

.flex-column > label {
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

.flex-row > div > label {
    font-size: 14px;
    color: black;
    font-weight: 400;
}

.span {
    font-size: 14px;
    margin-left: 5px;
    color: #007bff; /* Changed to a more visible blue for links */
    font-weight: 500;
    cursor: pointer;
}
.span a {
  color: #007bff; /* Ensure router-link also has the desired color */
  text-decoration: none;
}

.btn-main {
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
  display: flex; /* Make it a flex container to center content */
  justify-content: center; /* Center horizontally */
  align-items: center; /* Center vertically */
  position: relative; /* For arrow positioning */
  overflow: hidden; /* Hide overflowing arrow */
}

.btn-main:hover {
  background-color: #252727;
}

.arrow-wrapper {
  position: absolute;
  right: 15px;
  transition: transform 0.3s ease-in-out;
}

.btn-main:hover .arrow-wrapper {
  transform: translateX(5px); /* Move arrow slightly on hover */
}

.arrow {
  width: 0;
  height: 0;
  border-left: 8px solid transparent;
  border-right: 8px solid transparent;
  border-top: 8px solid white; /* Upward pointing arrow */
  transform: rotate(90deg); /* Rotate to point right */
}


.p {
    text-align: center;
    color: black;
    font-size: 14px;
    margin: 5px 0;
}

.p.line {
  position: relative;
  text-align: center;
  margin: 20px 0;
}

.p.line::before,
.p.line::after {
  content: "";
  position: absolute;
  top: 50%;
  width: 40%; /* Adjust as needed */
  height: 1px;
  background: #ccc;
}

.p.line::before {
  left: 0;
}

.p.line::after {
  right: 0;
}


.btnLogin {
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
}

.btnLogin:hover {
    border: 1px solid #2d79f3;
}

.alert {
  margin-bottom: 15px;
  padding: 10px;
  border-radius: 5px;
}

.alert-warning {
  background-color: #fff3cd;
  border-color: #ffeeba;
  color: #856404;
}

.alert-success {
  background-color: #d4edda;
  border-color: #c3e6cb;
  color: #155724;
}

/* Styles for the loading component */
/* Assuming 'loading' is a separate component you have */
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
import axios from 'axios';
import { inject, ref, onMounted, onBeforeUnmount } from 'vue';
const apiUrl = inject('apiUrl');
import loading from './loading.vue';
const isLoading = ref(false);
const isLogin = ref(true);
const email = ref('');
const password = ref('');
const name = ref('');
const errorMessage = ref('');
const alertMessage = ref('');
const userInfo = ref(null);
const qrCode = ref(null); // Biến để lưu mã QR
const toggleForm = () => {
    isLogin.value = !isLogin.value;
    errorMessage.value = '';
};
const submitForm = async () => {
    isLoading.value = true;
    // Lấy giá trị Turnstile response
    const turnstileResponse = document.querySelector('input[name="cf-turnstile-response"]').value; console.log("Turnstile response:", turnstileResponse); // In giá trị ra console
    if (!turnstileResponse) {
        showError("Please complete the Turnstile challenge."); // Hiển thị thông báo nếu Turnstile chưa hoàn thành
        isLoading.value = false;
        return;
    }
    try {
        if (isLogin.value) {
            const response = await axios.post(`${apiUrl}/api/login`, {
                email: email.value,
                password: password.value,
                turnstileResponse, // Gửi Turnstile response cho login
            });
            const token = response.data.token;
            const user = response.data.user;
            userInfo.value = user;
            localStorage.setItem('tokenJwt', token);
            localStorage.setItem('userInfo', JSON.stringify(user));
            window.location.href = 'http://127.0.0.1:5173/'; 
        } else {
            const response = await axios.post(`${apiUrl}/api/register`, {
                name: name.value,
                email: email.value,
                password: password.value,
                turnstileResponse, // Gửi Turnstile response cho register
            });
            const token = response.data.token;
            //qrCode.value = response.data.qr_code;
            localStorage.setItem('tokenJwt', token);
            //console.log(response.data.message);
            showAlert(response.data.message);
            // this.$router.push('/login');
        }
    } catch (error) {
        if (error.response && error.response.data) {
            if (error.response.data.errors && error.response.data.errors.password) {
                showError(error.response.data.errors.password[0]);
            } else {
                showError(error.response.data.message || 'Sai pass!');
            }
        } else {
            showError('Đã xảy ra lỗi không xác định!');
        }
    } finally {
        isLoading.value = false;
    }
};
//tải về mã QR
const downloadQRCode = () => {
    const link = document.createElement('a');
    link.href = qrCode.value; // Đặt địa chỉ URL của mã QR
    link.download = 'qr_code.png'; // Tên file khi tải về
    document.body.appendChild(link);
    link.click(); // Kích hoạt tải về
    document.body.removeChild(link); // Xóa liên kết
};
//qr login
import jsQR from 'jsqr'; // Thư viện để đọc mã QR
//gửi mã qr từ máy tính lên server
const errorMessageQr = ref('');
const onFileChange = (event) => {
    const file = event.target.files[0]; // Lấy tệp hình ảnh đầu tiên từ input
    if (file) {
        const reader = new FileReader(); // Tạo một FileReader để đọc tệp
        reader.onload = (e) => {
            const img = new Image(); // Tạo một đối tượng hình ảnh mới
            img.src = e.target.result; // Đặt nguồn hình ảnh là dữ liệu đã đọc từ tệp
            img.onload = () => {
                // Khi hình ảnh đã được tải xong
                const canvas = document.createElement('canvas'); // Tạo một canvas để vẽ hình ảnh
                const context = canvas.getContext('2d'); // Lấy ngữ cảnh 2D của canvas
                canvas.width = img.width; // Đặt chiều rộng của canvas bằng chiều rộng của hình ảnh
                canvas.height = img.height; // Đặt chiều cao của canvas bằng chiều cao của hình ảnh
                context.drawImage(img, 0, 0); // Vẽ hình ảnh lên canvas

                // Lấy dữ liệu pixel từ canvas
                const imageData = context.getImageData(0, 0, img.width, img.height);
                // Sử dụng thư viện jsQR để tìm mã QR trong dữ liệu pixel
                const code = jsQR(imageData.data, img.width, img.height);
                if (code) {
                    loginWithQR(code.data); // Gọi hàm đăng nhập và truyền token
                } else {
                    errorMessageQr.value = 'Không tìm thấy mã QR trong hình ảnh.'; // Thông báo lỗi nếu không có mã QR
                }
            };
        };
        reader.readAsDataURL(file); // Đọc tệp hình ảnh dưới dạng URL dữ liệu
    }
};
//quét mã qr bằng camera
import { Html5Qrcode } from "html5-qrcode";
const html5QrCode = ref(null);
const startQRCodeScanner = () => {
    errorMessageQr.value = ''; // Reset thông báo lỗi
    const readerElement = document.getElementById("reader");
    readerElement.style.display = "block"; // Hiển thị video từ camera

    html5QrCode.value = new Html5Qrcode("reader");

    const config = {
        fps: 10,
        qrbox: 250,
    };

    html5QrCode.value.start(
        { facingMode: "environment" }, // Sử dụng camera phía sau
        config,
        (decodedText) => {
            loginWithQR(decodedText); // Gọi hàm đăng nhập
            stopQRCodeScanner(); // Dừng quét sau khi quét thành công
        },
        (errorMessage) => {
            // Xử lý lỗi nếu cần
        }
    ).catch(err => {
        console.error("Error starting QR code scanner:", err);
    });
};
const loginWithQR = async (fullUrl) => {
    isLoading.value = true;
    const url = new URL(fullUrl);
    const token = url.searchParams.get('token');
    if (token) {
        try {
            console.log('Token:', token);
            const response = await axios.post(`${apiUrl}/api/qr-login`, { token });
            console.log('Đăng nhập thành công:', response.data);
            console.log('User Info:', response.data.user);
            console.log('token:', response.data.token);
            localStorage.setItem('tokenJwt', response.data.token);
            localStorage.setItem('userInfo', JSON.stringify(response.data.user));
            window.location.href = 'http://127.0.0.1:5173/';
            // Thực hiện các hành động sau khi đăng nhập thành công
        } catch (error) {
            console.error('Đăng nhập không thành công:', error.response.data);
            errorMessageQr.value = error.response?.data?.error || 'Đăng nhập không thành công.';
        } finally {
            isLoading.value = false;
        }
    } else {
        errorMessageQr.value = 'Vui lòng tải lên hình ảnh có mã QR trước.';
    }
};
const stopQRCodeScanner = () => {
    if (html5QrCode.value) {
        html5QrCode.value.stop();
        document.getElementById("reader").style.display = "none"; // Ẩn video
    }
};
onMounted(() => {
    const script = document.createElement('script');
    script.src = 'https://challenges.cloudflare.com/turnstile/v0/api.js';
    script.async = true;
    script.onload = () => {
        console.log("Turnstile script loaded successfully."); // Kiểm tra xem script đã tải
        // Kiểm tra xem widget có tồn tại không
        const turnstileWidget = document.querySelector('.cf-turnstile');
        //console.log("Turnstile widget:", turnstileWidget); // In ra widget
    };
    document.body.appendChild(script);
});
onBeforeUnmount(() => {
    stopQRCodeScanner(); // Dừng quét khi component bị hủy
});
// Hàm đăng nhập với Google
const loginWithGoogle = () => {
    window.location.href = `http://127.0.0.1:8000/auth/google`;
};

function showError(message) {
    errorMessage.value = message;
    // Ẩn thông báo sau 2 giây
    setTimeout(() => {
        errorMessage.value = ''; // Đặt lại thông báo
    }, 5000);
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
    }, 5000);
}

</script>