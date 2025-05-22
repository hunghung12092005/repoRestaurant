<template>
  <div>
    <header ref="headerRef">
      <a href="/" class="logo">
        <img src="https://i.postimg.cc/s2Ywg6YR/logo.png" alt="Foodie Logo" />
        Sea Foodie
      </a>
      <div class="bx bx-menu" id="menu-icon" @click="toggleMenu"></div>

      <ul class="navbar" :class="{ active: navbarActive }" ref="navbarRef">
        <li><router-link to="/">Home</router-link></li>
        <li><router-link to="/about">About</router-link></li>
        <li><router-link to="/contact">Contact</router-link></li>
        <li><router-link to="/blog">Blog</router-link></li>
        <li><router-link to="/menu-list">Menu</router-link></li>
        <li><router-link to="/testJwt">testJwt</router-link></li>
        <li><router-link to="/menu">menu</router-link></li>
        <li><router-link to="/reservation">Reservation</router-link></li>
        <li v-if="isAdmin">
          <a @click.prevent="adminPanel">Vào admin</a>
        </li>
        <li v-if="isLogin">
          <span>Xin chào, {{ userInfo.name }}!</span>
          <a @click.prevent="logout">Đăng Xuất</a>
        </li>

        <li v-else>
          <router-link to="/login">Đăng Nhập</router-link>
        </li>
      </ul>
    </header>

    <main>
      <RouterView></RouterView>
      <footer>đây là footer </footer>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import axiosConfig from '../axiosConfig'; // Import axiosConfig

const headerRef = ref(null);
const navbarRef = ref(null);
const navbarActive = ref(false);
const userInfo = ref(null);
const isLogin = ref(false);
const isAdmin = ref(false); // Biến để kiểm tra quyền admin

const toggleMenu = () => {
  navbarActive.value = !navbarActive.value;
};

const handleScroll = () => {
  if (headerRef.value) {
    headerRef.value.classList.toggle('active', window.scrollY > 0);
  }
};

// Hàm lấy thông tin người dùng
const fetchUserInfo = async () => {
  try {
    const response = await axiosConfig.get('/api/protected');
    userInfo.value = response.data.user; // Lưu thông tin người dùng
    isLogin.value = true; // Đánh dấu là đã đăng nhập
   // console.log(1);
    // Kiểm tra vai trò
    if (userInfo.value.role === 'admin') {
      isAdmin.value = true; // Đánh dấu là admin
    } else {
      isAdmin.value = false; // Không phải admin
    }

  } catch (error) {
    console.error('Error fetching user info:', error.response ? error.response.data : error.message);
  }
};

// Xử lý token và thông tin người dùng từ query params
const urlParams = new URLSearchParams(window.location.search);
const token = urlParams.get('token');
const user = urlParams.get('user');

if (token && user) {
  localStorage.setItem('token', token);
  localStorage.setItem('userInfo', user);
  userInfo.value = JSON.parse(user); // Lưu thông tin người dùng
  // Chuyển hướng về trang chính
  // window.location.href = '/'; // Sửa lại đường dẫn nếu cần
}

// Lifecycle hooks
onMounted(() => {
  fetchUserInfo(); // Gọi hàm lấy thông tin người dùng khi component được mount
  window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll);
});

// Hàm logout
const logout = () => {
  localStorage.removeItem('token'); // Xóa token
  localStorage.removeItem('userInfo'); // Xóa thông tin người dùng
  window.location.href = '/'; // Chuyển hướng
};

// Hàm adminPanel
const adminPanel = () => {
  window.location.href = '/admin'; // Chuyển hướng
};
</script>

<style scoped>
/* Các style của bạn tại đây */
</style>



<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Turret+Road:wght@400;500;700;800&display=swap');

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  scroll-padding-top: 2rem;
  list-style: none;
  text-decoration: none;
  scroll-behavior: smooth;
  font-family: 'Poppins', sans-serif;
}

:root {
  --main-color: #16B978;
  --second-color: #081B54;
}

section {
  padding: 50px 10%;
}

img {
  width: 100%;
}

*::selection {
  color: #1e85e6;
  background: var(--main-color);
}

header {
  position: fixed;
  width: 100%;
  top: 0;
  right: 0;
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 15px 10%;
  transition: .2s;
}

:global(header.active) {
  background-color: #081B54 !important;
  box-shadow: 0 0 4px rgba(14, 55, 54, 0.15);
}

:global(header.active .logo),
:global(header.active .navbar a) {
  color: #ffffff !important;
}

.logo {
  display: flex;
  align-items: center;
  font-size: 1.1rem;
  font-weight: 600;
  color: #000000;
}

.logo img {
  width: 35px;
  margin-right: 10px;
}

.navbar {
  display: flex;
}

.navbar a {
  font-size: 1rem;
  padding: 10px 20px;
  color: #000000;
  font-weight: 500;
}

.navbar a:hover {
  background: var(--main-color);
  color: #078ff7;
  border-radius: 4px;
}

#menu-icon {
  font-size: 24px;
  cursor: pointer;
  z-index: 10000;
  display: none;
}

/* Styles cho phần thân */
main {
  padding-top: 80px;
  /* Để tránh nội dung bị che bởi header */
  min-height: calc(100vh - 80px);
  /* Chiếm toàn bộ chiều cao còn lại */
  width: 100%;
}


</style>
