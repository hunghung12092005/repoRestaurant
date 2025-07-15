<template>
  <div>
    <!-- Phần Admin & Staff Layout (Sử dụng cấu trúc gốc) -->
    <div v-if="$route.path.startsWith('/admin')" class="d-flex">
      
      <!-- ===== SIDEBAR CHO ADMIN (Lấy từ code gốc) ===== -->
      <div v-if="isAdmin" class="sidebar">
        <div class="header text-center p-3 border-bottom">
          <img src="https://i.postimg.cc/d3pNGXPN/7c6764b8-de90-474c-9b98-05019aef3193.png" alt="Luxuria Logo" class="rounded-circle" />
          <span class="fw-bold"><strong>Ho Xuan Huong</strong> Ecosystem</span>
        </div>
        <div class="profile text-center p-3 border-bottom">
          <p class="mb-0 text-muted">{{ userInfo.name || 'Admin' }}</p>
          <p class="text-muted">{{ userInfo.email || 'admin@seafoodie.com' }}</p>
          <div class="icons d-flex justify-content-center mt-2">
            <i class="bi bi-person mx-2" @click="goToProfile"></i>
            <i class="bi bi-pencil mx-2" @click="editProfile"></i>
            <i class="bi bi-bookmark-check mx-2"></i>
            <i class="bi bi-box-arrow-right mx-2" @click.prevent="logout"></i>
          </div>
        </div>
        <ul class="nav flex-column">
          <li class="nav-item"><router-link class="nav-link" to="/admin/dashboard"><i class="bi bi-grid"></i>
              Trang thống kê</router-link></li>
          <li class="nav-item"><router-link class="nav-link" to="/admin/occupancy"><i class="bi bi-house-door"></i>
              QL tình trạng phòng <span class="badge bg-danger">5</span></router-link></li>
          <li class="nav-item"><router-link class="nav-link" to="/admin/bookings"><i class="bi bi-book"></i>
              Quản lý đặt phòng</router-link></li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="servicesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class='bx bx-hotel'></i> Dịch vụ và tiện nghi
            </a>
            <ul class="dropdown-menu" aria-labelledby="servicesDropdown">
              <li><router-link class="dropdown-item" to="/admin/services"><i class="bi bi-list-ul me-2"></i> Quản lý dịch vụ</router-link></li>
              <li><router-link class="dropdown-item" to="/admin/amenities"><i class='bx bx-bed'></i> Quản lý tiện nghi</router-link></li>
            </ul>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="roomsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class='bx bx-bed'></i> Quản lý phòng
            </a>
            <ul class="dropdown-menu" aria-labelledby="roomsDropdown">
              <li><router-link class="dropdown-item" to="/admin/room-types"><i class="bi bi-list-ul me-2"></i> Danh mục phòng</router-link></li>
              <li><router-link class="dropdown-item" to="/admin/prices"><i class='bx bxs-dollar-circle'></i> Quản lý giá phòng</router-link></li>
              <li><router-link class="dropdown-item" to="/admin/rooms"><i class='bx bx-bed'></i> Quản lý phòng</router-link></li>
            </ul>
          </li>
          <li class="nav-item"><router-link class="nav-link" to="/admin/ChatAdmin"><i class="bi bi-people"></i>Chat Admin</router-link></li>
          <li class="nav-item"><router-link class="nav-link" to="/admin/traningAI"><i class="bi bi-people"></i>Traning AI </router-link></li>
          <li class="nav-item"><router-link class="nav-link" to="/admin/users"><i class="bi bi-people"></i>Quản lý tài khoản</router-link></li>
          <li class="nav-item"><router-link class="nav-link" to="/admin/contacts"><i class="bi bi-people"></i>Quản lý liên hệ</router-link></li>
          <li class="nav-item"><router-link class="nav-link" to="/admin/news"><i class="bi bi-newspaper"></i>Tin tức</router-link></li>
          <li class="nav-item"><router-link class="nav-link" to="/admin/news-categories"><i class="bi bi-tags"></i>Danh mục Tin tức</router-link></li>
          <li class="nav-item"><router-link class="nav-link" to="/admin/news-comments"><i class="bi bi-chat"></i>Bình luận</router-link></li>
          <li class="nav-item"><router-link class="nav-link" to="/"><i class="bi bi-box-arrow-left"></i>Thoát</router-link></li>
        </ul>
      </div>

      <!-- ===== SIDEBAR CHO STAFF (Lễ tân) - PHẦN MỚI ĐƯỢC THÊM ===== -->
      <div v-else-if="isStaff" class="sidebar">
        <div class="header text-center p-3 border-bottom">
          <img src="https://i.postimg.cc/d3pNGXPN/7c6764b8-de90-474c-9b98-05019aef3193.png" alt="Luxuria Logo" class="rounded-circle" />
          <span class="fw-bold"><strong>Ho Xuan Huong</strong> Ecosystem</span>
        </div>
        <div class="profile text-center p-3 border-bottom">
          <p class="mb-0 text-muted">{{ userInfo.name || 'Staff' }}</p>
          <p class="text-muted">{{ userInfo.email || 'staff@example.com' }}</p>
          <div class="icons d-flex justify-content-center mt-2">
            <i class="bi bi-person mx-2" @click="goToProfile"></i>
            <i class="bi bi-box-arrow-right mx-2" @click.prevent="logout"></i>
          </div>
        </div>
        <ul class="nav flex-column">
          <li class="nav-item"><router-link class="nav-link" to="/admin/dashboard"><i class="bi bi-grid"></i> Dashboard</router-link></li>
          <li class="nav-item"><router-link class="nav-link" to="/admin/occupancy"><i class="bi bi-house-door"></i> Tra cứu phòng trống</router-link></li>
          <li class="nav-item"><router-link class="nav-link" to="/admin/bookings"><i class="bi bi-book"></i> Quản lý Đặt phòng</router-link></li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="staffServicesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class='bx bx-hotel'></i> Dịch vụ & Tiện nghi
            </a>
            <ul class="dropdown-menu" aria-labelledby="staffServicesDropdown">
              <li><router-link class="dropdown-item" to="/admin/services">Tra cứu dịch vụ</router-link></li>
              <li><router-link class="dropdown-item" to="/admin/amenities">Tra cứu tiện nghi</router-link></li>
            </ul>
          </li>
          <li class="nav-item"><router-link class="nav-link" to="/"><i class="bi bi-box-arrow-left"></i>Thoát</router-link></li>
        </ul>
      </div>

      <!-- Main Content (Dùng chung, lấy từ code gốc) -->
      <div class="main-content">
        <div class="navbar-top" :class="{ 'scrolled': navbarSticky }">
          <div class="d-flex align-items-center">
            <i class="bi bi-globe"></i>
            <i class="bi bi-bell mx-3"></i>
            <span>{{ userInfo.name || 'User' }}</span>
          </div>
        </div>
        <main class="admin-main">
          <RouterView></RouterView>
        </main>
        <footer class="admin-footer text-center py-3">Trang quản trị khách sạn Hồ Xuân Hương</footer>
      </div>
    </div>

    <!-- Layout cho các route không phải admin (lấy từ code gốc) -->
    <div v-else>
        <test></test>

      <header ref="headerRef">
        <nav class="navbar navbar-expand-lg navbar-light fixed-top" :class="{ 'scrolled': navbarSticky }">
          <div class="container-fluid">
            <!-- Mobile Toggler -->
            <button class="navbar-toggler" type="button" @click="toggleMenu" aria-controls="navbarNav"
              aria-expanded="false" aria-label="Toggle navigation">
              <span class="navbar-toggler-icon"></span>
            </button>
            <!-- Collapsible Wrapper -->
            <div class="collapse navbar-collapse" id="navbarNav" ref="navbarRef" :class="{ 'show': navbarActive }">
              <!-- Left Navigation -->
              <ul class="navbar-nav navbar-nav-left">
                <li class="nav-item"><router-link class="nav-link" to="/">Trang chủ</router-link></li>
                <li class="nav-item"><router-link class="nav-link" to="/about">Giới thiệu</router-link></li>
                <li class="nav-item dropdown">
                  <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Phòng</a>
                  <ul class="dropdown-menu">
                    <li v-for="roomType in roomTypes" :key="roomType.type_id">
                      <router-link class="dropdown-item" :to="{ name: 'RoomTypeDetail', params: { id: roomType.type_id } }">{{ roomType.type_name }}</router-link>
                    </li>
                  </ul>
                </li>
                <li class="nav-item"><router-link class="nav-link" to="/news">Tin tức</router-link></li>
                <li class="nav-item"><router-link class="nav-link" to="/contact">Liên hệ</router-link></li>
              </ul>
              <!-- Right Navigation -->
              <ul class="navbar-nav navbar-nav-right align-items-center">
                <li class="nav-item"><router-link class="nav-link" to="/HistoryBooking">Lịch Sử Đặt Phòng</router-link></li>
                <!-- Logged Out State -->
                <template v-if="!isLogin">
                  <li class="nav-item">
                    <router-link class="btn btn-outline-custom" to="/login">Đăng Nhập</router-link>
                  </li>
                </template>
                <!-- Logged In State -->
                <template v-if="isLogin">
                  <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">{{ userInfo.name }}</a>
                    <ul class="dropdown-menu dropdown-menu-end">
                      <li><router-link class="dropdown-item" to="/userprofile">Thông Tin User</router-link></li>
                      <!-- CẬP NHẬT: Cho cả admin và staff thấy nút này -->
                      <li v-if="isAdmin || isStaff"><router-link class="dropdown-item" to="/admin">Vào trang quản lý</router-link></li>
                      <li><a class="dropdown-item logout-link" @click.prevent="logout">Đăng Xuất</a></li>
                    </ul>
                  </li>
                </template>
                <li class="nav-item">
                  <router-link class="btn btn-solid-custom" to="/rooms3">Đặt Phòng</router-link>
                </li>
              </ul>
            </div>
            <!-- Center Logo -->
            <a class="navbar-brand mx-auto" href="/">
              <img src="https://i.postimg.cc/d3pNGXPN/7c6764b8-de90-474c-9b98-05019aef3193.png" alt="Foodie Logo" class="logo-img" />
              <span class="d-none d-lg-inline">Hồ Xuân Hương</span>
            </a>
          </div>
        </nav>
      </header>
      <main>
        <router-view />
      </main>
      <Footer></Footer>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, provide } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axiosConfig from './axiosConfig.js';
import Footer from './components/Footer.vue';
import test from './components/booking_room/test.vue';
const route = useRoute();
const router = useRouter();
const headerRef = ref(null);
const navbarRef = ref(null);
const navbarActive = ref(false);
const navbarSticky = ref(false);
const userInfo = ref(null);
const isLogin = ref(false);
const isAdmin = ref(false);
const apiUrl = 'http://localhost:8000';//http://127.0.0.1:8000
provide('apiUrl', apiUrl);

const lastScrollPosition = ref(0);
const roomTypes = ref([]);

const fetchRoomTypes = async () => {
  try {
    const response = await axiosConfig.get(`${apiUrl}/api/room-types`);
    if (response.data.status) {
      roomTypes.value = response.data.data;
    }
  } catch (error) {
    console.error('Lỗi khi lấy danh sách loại phòng:', error);
  }
};

const toggleMenu = () => {
  navbarActive.value = !navbarActive.value;
};

const handleScroll = () => {
  if (headerRef.value || route.path.startsWith('/admin')) {
    const currentScrollPosition = window.scrollY;
    navbarSticky.value = currentScrollPosition > 50;
    lastScrollPosition.value = currentScrollPosition;
  }
};

const handleOutsideClick = (event) => {
  if (window.innerWidth <= 991 && navbarActive.value && navbarRef.value) {
    const isClickInside = navbarRef.value.contains(event.target);
    const isToggleButton = event.target.closest('.navbar-toggler');
    if (!isClickInside && !isToggleButton) {
      navbarActive.value = false;
    }
  }
};

// CẬP NHẬT: Hàm helper để set vai trò
const setUserRoles = (user) => {
    userInfo.value = user;
    isLogin.value = !!user;
    isAdmin.value = user?.role === 'admin';
    isStaff.value = user?.role === 'staff';
};

const restoreUserSession = () => {
  const storedToken = localStorage.getItem('tokenJwt');
  const storedUser = localStorage.getItem('userInfo');
  if (storedToken && storedUser) {
    try {
      setUserRoles(JSON.parse(storedUser));
    } catch (e) {
      console.error('Error parsing stored user info:', e);
      logout();
    }
  }
};

const fetchUserInfo = async () => {
  const token = localStorage.getItem('tokenJwt');
  if (!token) {
    setUserRoles(null);
    return;
  }
  try {
    const response = await axiosConfig.get(`${apiUrl}/api/protected`);
    setUserRoles(response.data.user);
  } catch (error) {
    console.error('Error fetching user info:', error.response ? error.response.data : error.message);
    if (error.response?.status === 401) {
      logout();
    }
  }
};

const handleUrlParams = () => {
  const urlParams = new URLSearchParams(window.location.search);
  const token = urlParams.get('token');
  const user = urlParams.get('user');

  if (token && user) {
    localStorage.setItem('tokenJwt', token);
    localStorage.setItem('userInfo', user);
    try {
      setUserRoles(JSON.parse(user));
      router.replace({ query: {} });
    } catch (e) {
      console.error('Error parsing user from URL:', e);
    }
  }
};

const logout = () => {
  localStorage.removeItem('tokenJwt');
  localStorage.removeItem('userInfo');
  setUserRoles(null);
  router.push('/');
};

const goToProfile = () => {
  router.push('/userprofile'); // Chuyển đến trang profile chung
};

const editProfile = () => {
  // Bạn có thể tạo một trang edit profile riêng
  router.push('/userprofile');
};

// Ghi chú: route guard đã được chuyển sang router/index.js, không cần để ở đây
// để tránh việc đăng ký lặp lại.

onMounted(() => {
  restoreUserSession();
  handleUrlParams();
  fetchUserInfo();
  fetchRoomTypes();
  window.addEventListener('scroll', handleScroll);
  document.addEventListener('click', handleOutsideClick);
});

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll);
  document.removeEventListener('click', handleOutsideClick);
});
</script>

<style scoped>
/* Lấy lại toàn bộ CSS từ file gốc của bạn */
@import url('https://fonts.googleapis.com/css2?family=Arial&display=swap');
@import url('https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css');
@import url('https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css');
@import url('https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css');

:root {
  --main-color: #16B978;
  --second-color: #081B54;
  --text-muted: #6c757d;
  --border-color: #eee;
  --badge-bg: #dc3545;
}

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: 'Arial', sans-serif;
}

body {
  background-color: #f5f7fb;
  height: 100%;
  overflow-y: auto;
}

/* START: CSS ĐÃ ĐƯỢC CHỈNH SỬA CHO LAYOUT NGƯỜI DÙNG */
.navbar {
  background-color: #ffffff;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
  height: 80px;
  padding: 0 1rem;
}
.navbar .container-fluid { position: relative; height: 100%; }
.navbar-brand {
  display: flex; align-items: center; font-weight: 600; color: #333333; transition: color 0.3s ease;
  position: absolute; left: 50%; transform: translateX(-50%); top: 50%; transform: translate(-50%, -50%);
}
.logo-img { width: 60px; height: 60px; filter: brightness(0); }
.navbar-toggler { position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); }
.navbar-collapse { height: 100%; }
.navbar-nav { gap: 0.5rem; }
.navbar-nav-left { margin-right: auto; }
.navbar-nav-right { margin-left: auto; }
.nav-link { color: #555; font-weight: 500; padding: 0.5rem 1rem; transition: color 0.2s ease-in-out; }
.nav-link:hover { color: var(--main-color); }
.btn-outline-custom {
  border: 1px solid #ccc; color: #555; padding: 8px 18px; border-radius: 4px;
  background-color: transparent; text-decoration: none; display: inline-block; transition: all 0.2s ease-in-out;
}
.btn-outline-custom:hover { background-color: #f8f9fa; color: #333; }
.btn-solid-custom {
  background-color: #0d6efd; color: white; padding: 9px 20px; border-radius: 4px; border: 1px solid #0d6efd;
  text-decoration: none; display: inline-block; font-weight: 500;
  transition: background-color 0.2s ease-in-out, border-color 0.2s ease-in-out;
}
.btn-solid-custom:hover { background-color: #0b5ed7; border-color: #0b5ed7; }
.dropdown-item {
  cursor: pointer; font-size: 0.95rem; font-weight: 500; color: #333;
  padding: 8px 15px; transition: all 0.3s ease;
}
.dropdown-item:hover { color: var(--main-color) !important; background-color: #f1f1f1; }
main { min-height: calc(100vh - 80px); width: 100%; padding-top: 80px; }
footer { background-color: var(--second-color); color: #fff; }

/* --- ADMIN LAYOUT STYLES (Không đổi) --- */
.sidebar {
  height: 100vh; background-color: #fff; box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1); padding-top: 20px;
  position: fixed; width: 250px; z-index: 1000; overflow-y: auto; scrollbar-width: none;
}
.sidebar::-webkit-scrollbar { display: none; }
.sidebar .header { text-align: center; padding: 20px; border-bottom: 1px solid var(--border-color); }
.sidebar .header img { width: 30px; margin-right: 10px; vertical-align: middle; }
.sidebar .header span { font-size: 1.5rem; vertical-align: middle; color: var(--badge-bg); }
.sidebar .profile { text-align: center; padding: 20px 0; border-bottom: 1px solid var(--border-color); }
.sidebar .profile img { width: 80px; height: 80px; border-radius: 50%; margin-bottom: 10px; }
.sidebar .profile p { margin: 0; font-size: 0.9rem; color: var(--text-muted); }
.sidebar .profile .icons { display: flex; justify-content: center; margin-top: 10px; }
.sidebar .profile .icons i {
  font-size: 1.2rem; margin: 0 5px; color: var(--text-muted); cursor: pointer; transition: color 0.3s ease;
}
.sidebar .profile .icons i:hover { color: var(--main-color); }
.sidebar .nav-link {
  color: var(--text-muted); padding: 10px 20px; display: flex; align-items: center; transition: all 0.3s ease;
}
.sidebar .nav-link:hover,
.sidebar .nav-link.router-link-active {
  color: var(--main-color); background-color: #f8f9fa;
}
.sidebar .nav-link i { margin-right: 10px; }
.sidebar .nav-link .badge { margin-left: auto; background-color: var(--badge-bg); }
.main-content {
  margin-left: 250px; padding: 20px; padding-top: 60px; min-height: 100vh;
  background-color: #f5f7fb; width: calc(100% - 250px); overflow-y: auto;
}
.navbar-top {
  padding: 10px 20px; display: flex; justify-content: flex-end; align-items: center;
  position: fixed; top: 0; left: 250px; right: 0; z-index: 900; height: 50px;
  background-color: #f5f7fb; transition: background-color 0.1s ease;
}
.navbar-top.scrolled { background-color: #ffffff; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); }
.navbar-top .d-flex i { font-size: 1.2rem; color: var(--text-muted); cursor: pointer; }
.navbar-top .d-flex span { font-weight: 500; color: var(--text-muted); }
.admin-main { padding: 20px 20px 0; min-height: calc(100vh - 50px - 60px); width: 100%; background-color: #f5f7fb; }
.admin-main>* { width: 100%; }
.admin-footer { background-color: var(--second-color); color: #000000; }

/* --- RESPONSIVE STYLES --- */
@media (max-width: 991px) {
  .navbar-brand { position: static; transform: none; margin-right: auto; }
  .navbar-toggler { position: static; transform: none; }
  .navbar .container-fluid { display: flex; justify-content: space-between; width: 100%; }
  .collapse.show .navbar-nav { flex-direction: column; align-items: flex-start !important; gap: 1rem; padding: 1rem 0; }
  .collapse.show .navbar-nav-left,
  .collapse.show .navbar-nav-right { margin: 0 !important; width: 100%; }
  .collapse.show .navbar-nav li { width: 100%; }
  .collapse.show .navbar-nav .btn { width: 100%; text-align: left; }
  .navbar-collapse {
    position: fixed; top: 80px; right: 0; height: calc(100vh - 80px); width: 250px;
    background-color: #fff; transform: translateX(100%); transition: transform 0.3s ease-in-out;
    padding: 20px; z-index: 1000; box-shadow: -2px 0 10px rgba(0, 0, 0, 0.1);
  }
  .navbar-collapse.show { transform: translateX(0); }
  .sidebar { width: 200px; transform: translateX(-100%); }
  .main-content { margin-left: 0; width: 100%; }
  .navbar-top { left: 0; width: 100%; }
}
</style>