<template>
  <div>
    <!-- ========== PHẦN GIAO DIỆN QUẢN TRỊ (ADMIN & STAFF) ========== -->
    <div v-if="$route.path.startsWith('/admin')" class="d-flex">
      
      <!-- ===== SIDEBAR ĐỘNG CHO ADMIN & STAFF ===== -->
      <div v-if="isAdmin || isStaff" class="sidebar">
        <div class="header text-center p-3 border-bottom">
          <img src="https://i.postimg.cc/J0d3p6Ww/logo-HXH.png" alt="Luxuria Logo" class="rounded-circle" />
          <span class="fw-bold">Ho Xuan Huong Hotel</span>
        </div>
        <div class="profile text-center p-3 border-bottom">
          <p class="mb-0 text-muted">{{ userInfo.name || 'User' }}</p>
          <p class="text-muted">{{ userInfo.email || 'user@example.com' }}</p>
          <div class="icons d-flex justify-content-center mt-2">
            <i class="bi bi-person mx-2" @click="goToProfile"></i>
            <i class="bi bi-box-arrow-right mx-2" @click.prevent="logout"></i>
          </div>
        </div>
        <ul class="nav flex-column">
          <li class="nav-item"><router-link class="nav-link" to="/admin/dashboard"><i class="bi bi-grid"></i> Trang thống kê</router-link></li>
          <li class="nav-item"><router-link class="nav-link" to="/admin/occupancy"><i class="bi bi-house-door"></i> Quản lý sơ đồ phòng</router-link></li>
          <li class="nav-item"><router-link class="nav-link" to="/admin/bookings"><i class="bi bi-book"></i> Quản lý đặt phòng</router-link></li>
          <li class="nav-item"><router-link class="nav-link" to="/admin/booking-histories"><i class="bi bi-clock-history"></i> Lịch sử đặt phòng</router-link></li>
          
          <!-- Các mục này vẫn chỉ dành cho Admin để giữ cấu trúc dropdown -->
          <!-- Nếu muốn các mục này cũng theo quyền, bạn cần tạo thêm các quyền mới -->
          <template v-if="isAdmin">
            <li class="nav-item">
              <a class="nav-link collapsible-toggle" href="#" @click.prevent="roomManagementOpen = !roomManagementOpen">
                <span>
                  <i class='bx bx-bed'></i> Quản lý phòng
                </span>
                <i class="bi bi-chevron-down toggle-icon" :class="{ 'rotated': roomManagementOpen }"></i>
              </a>
              <ul class="nav-submenu" :class="{ 'open': roomManagementOpen }">
                <li><router-link class="nav-link" to="/admin/room-types"><i class="bi bi-list-ul"></i> Danh mục phòng</router-link></li>
                <li><router-link class="nav-link" to="/admin/prices"><i class='bx bxs-dollar-circle'></i> Quản lý giá phòng</router-link></li>
                <li><router-link class="nav-link" to="/admin/rooms"><i class='bx bx-bed'></i> Quản lý phòng</router-link></li>
              </ul>
            </li>
            <li class="nav-item">
            <a class="nav-link collapsible-toggle" @click.prevent="servicesAmenitiesOpen = !servicesAmenitiesOpen" href="#">
              <span>
                <i class="bi bi-sliders"></i> Dịch vụ & Tiện nghi
              </span>
              <i class="bi bi-chevron-down toggle-icon" :class="{ 'rotated': servicesAmenitiesOpen }"></i>
            </a>
            <ul class="nav-submenu" :class="{ 'open': servicesAmenitiesOpen }">
              <li><router-link class="nav-link" to="/admin/services"><i class="bi bi-list-ul"></i> Quản lý dịch vụ</router-link></li>
              <li><router-link class="nav-link" to="/admin/amenities"><i class='bx bx-bed'></i> Quản lý tiện nghi</router-link></li>
            </ul>
          </li>
          </template>

          <!-- == CÁC MỤC HIỂN THỊ THEO QUYỀN (PERMISSION-BASED) == -->
          <!-- Quản lý Tin tức -->
          <template v-if="hasPermission('manage_news')">
            <li class="nav-item">
              <a class="nav-link collapsible-toggle" href="#" @click.prevent="newsManagementOpen = !newsManagementOpen">
                <span>
                  <i class="bi bi-newspaper"></i> Quản lý Tin tức
                </span>
                <i class="bi bi-chevron-down toggle-icon" :class="{ 'rotated': newsManagementOpen }"></i>
              </a>
              <ul class="nav-submenu" :class="{ 'open': newsManagementOpen }">
                <li><router-link class="nav-link" to="/admin/news"><i class="bi bi-newspaper"></i>Tin tức</router-link></li>
                <li><router-link class="nav-link" to="/admin/news-categories"><i class="bi bi-tags"></i>Danh mục Tin tức</router-link></li>
                <li><router-link class="nav-link" to="/admin/news-comments"><i class="bi bi-chat"></i>Bình luận</router-link></li>
              </ul>
            </li>
          </template>
          <!-- Quản lý Liên hệ -->
          <li v-if="hasPermission('manage_contacts')" class="nav-item">
              <router-link class="nav-link" to="/admin/contacts"><i class="bi bi-envelope"></i>Quản lý liên hệ</router-link>
          </li>
          <!-- Quản lý coupons-->
          <li v-if="hasPermission('manage_coupons')" class="nav-item">
              <router-link class="nav-link" to="/admin/coupons"><i class="bi bi-envelope"></i>Quản lý discount</router-link>
          </li>
          <!-- Quản lý Tài khoản -->
          <li v-if="hasPermission('manage_users')" class="nav-item"><router-link class="nav-link" to="/admin/users"><i class="bi bi-people"></i>Quản lý tài khoản</router-link>
          </li>
          <!-- Training AI -->
          <li v-if="hasPermission('manage_ai_training')" class="nav-item"><router-link class="nav-link" to="/admin/traningAI"><i class="bi bi-robot"></i>Training AI </router-link>
          </li>
          <!-- Chat Admin -->
          <li v-if="hasPermission('manage_admin_chat')" class="nav-item"><router-link class="nav-link" to="/admin/ChatAdmin"><i class="bi bi-chat-dots"></i>Chat Admin</router-link>
          </li>

          <li class="nav-item"><router-link class="nav-link" to="/"><i class="bi bi-box-arrow-left"></i>Thoát trang quản trị</router-link></li>
        </ul>
      </div>

      <!-- Main Content -->
      <div class="main-content">
        <div class="navbar-top" :class="{ 'scrolled': navbarSticky }">
          <div class="d-flex align-items-center">
            <i class="bi bi-bell mx-3"></i>
            <span>{{ userInfo.name || 'User' }}</span>
          </div>
        </div>
        <main class="admin-main">
          <RouterView></RouterView>
        </main>
      </div>
    </div>

    <!-- ========== PHẦN GIAO DIỆN CÔNG KHAI (KHÁCH HÀNG) ========== -->
    <div v-else>
        <chatbot></chatbot>
      <header ref="headerRef">
        <nav class="navbar navbar-expand-lg navbar-light fixed-top" :class="{ 'scrolled': navbarSticky }">
          <div class="container-fluid">
            <button class="navbar-toggler" type="button" @click="toggleMenu" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
              <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav" ref="navbarRef" :class="{ 'show': navbarActive }">
              <ul class="navbar-nav navbar-nav-left">
                <li class="nav-item"><router-link class="nav-link" to="/">Trang chủ</router-link></li>
                <li class="nav-item"><router-link class="nav-link" to="/about">Giới thiệu</router-link></li>
                <li class="nav-item dropdown">
                  <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Các loại phòng</a>
                  <ul class="dropdown-menu">
                    <li v-for="roomType in roomTypes" :key="roomType.type_id">
                      <router-link class="dropdown-item" :to="{ name: 'RoomTypeDetail', params: { id: roomType.type_id } }">{{ roomType.type_name }}</router-link>
                    </li>
                  </ul>
                </li>
                <li class="nav-item"><router-link class="nav-link" to="/news">Tin tức</router-link></li>
                <li class="nav-item"><router-link class="nav-link" to="/contact">Liên hệ</router-link></li>
              </ul>
              <ul class="navbar-nav navbar-nav-right align-items-center">
                <li class="nav-item"><router-link class="nav-link" to="/HistoryBooking">Lịch Sử Đặt Phòng</router-link></li>
                <template v-if="!isLogin">
                  <li class="nav-item">
                    <router-link class="btn btn-outline-custom" to="/login">Đăng Nhập</router-link>
                  </li>
                </template>
                <template v-if="isLogin">
                  <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">{{ userInfo.name }}</a>
                    <ul class="dropdown-menu dropdown-menu-end">
                      <li><router-link class="dropdown-item" to="/userprofile">Thông Tin User</router-link></li>
                      <li v-if="isAdmin || isStaff"><router-link class="dropdown-item" to="/admin">Vào trang quản lý</router-link></li>
                      <li><a class="dropdown-item logout-link" @click.prevent="logout">Đăng Xuất</a></li>
                    </ul>
                  </li>
                </template>
                <li class="nav-item">
                  <router-link class="btn btn-solid-custom" to="/booking_hotel">Đặt Phòng</router-link>
                </li>
              </ul>
            </div>
            <a class="navbar-brand mx-auto" href="/">
              <img src="https://i.postimg.cc/J0d3p6Ww/logo-HXH.png" alt="Foodie Logo" class="logo-img"/>
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
import chatbot from './components/Chat.vue';

const route = useRoute();
const router = useRouter();
const headerRef = ref(null);
const navbarRef = ref(null);
const navbarActive = ref(false);
const navbarSticky = ref(false);
const userInfo = ref({});
const isLogin = ref(false);
const isAdmin = ref(false);
const isStaff = ref(false);
const roomTypes = ref([]);
const lastScrollPosition = ref(0);
const servicesAmenitiesOpen = ref(false); 
const roomManagementOpen = ref(false);
const newsManagementOpen = ref(false);

const apiUrl = 'http://127.0.0.1:8000';
provide('apiUrl', apiUrl);
const setUserRoles = (user) => {
    userInfo.value = user || {};
    isLogin.value = !!user;
    isAdmin.value = user?.role === 'admin';
    isStaff.value = user?.role === 'staff';
};

const updateAndRefreshUserInfo = (updatedUser) => {
  localStorage.setItem('userInfo', JSON.stringify(updatedUser));
  setUserRoles(updatedUser);
};
provide('updateAndRefreshUserInfo', updateAndRefreshUserInfo);

const hasPermission = (permission) => {
  if (!userInfo.value) return false;
  // Logic kiểm tra quyền mới: Áp dụng cho mọi vai trò, kể cả admin.
  // Quyền được quyết định duy nhất bởi mảng 'permissions'.
  return userInfo.value.permissions?.includes(permission) ?? false;
};

const restoreUserSession = () => {
  const storedUser = localStorage.getItem('userInfo');
  if (storedUser) {
    try {
      setUserRoles(JSON.parse(storedUser));
    } catch (e) {
      console.error('Lỗi phân tích thông tin người dùng từ localStorage:', e);
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
    const user = response.data.user;
    localStorage.setItem('userInfo', JSON.stringify(user));
    setUserRoles(user);
  } catch (error) {
    console.error('Lỗi khi lấy thông tin người dùng:', error.response ? error.response.data : error.message);
    if (error.response?.status === 401) {
      logout();
    }
  }
};

const logout = () => {
  localStorage.removeItem('tokenJwt');
  localStorage.removeItem('userInfo');
  setUserRoles(null);
  window.location.href = '/login';
};

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

const goToProfile = () => {
  router.push('/userprofile');
};

const toggleMenu = () => {
  navbarActive.value = !navbarActive.value;
};

const handleScroll = () => {
  const isClientLayout = !route.path.startsWith('/admin');
  if (isClientLayout || headerRef.value) {
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

const handleUrlParams = () => {
  const urlParams = new URLSearchParams(window.location.search);
  const token = urlParams.get('token');
  const user = urlParams.get('user');

  if (token && user) {
    localStorage.setItem('tokenJwt', token);
    localStorage.setItem('userInfo', user);
    try {
      const parsedUser = JSON.parse(user);
      setUserRoles(parsedUser);
      router.replace({ query: {} });
    } catch (e) {
      console.error('Error parsing user from URL:', e);
    }
  }
}; 

onMounted(() => {
  restoreUserSession();
  fetchUserInfo();
  handleUrlParams();
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
.logo-img { width: 60px; height: 50px;margin: 0 10px 0 10px;}
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

.dropdown-item:hover { color: var(--main-color) !important; background-color: #f1f1f1; }
main { min-height: calc(100vh - 80px); width: 100%; padding-top: 80px; }
footer { background-color: var(--second-color); color: #fff; }

.sidebar {
  height: 100vh; background-color: #fff; box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1); padding-top: 20px;
  position: fixed; width: 250px; z-index: 1000; overflow-y: auto; scrollbar-width: none;
}
.sidebar::-webkit-scrollbar { display: none; }
.sidebar .header { text-align: center; padding: 20px; border-bottom: 1px solid var(--border-color); }
.sidebar .header img { width: 30px; margin-right: 10px; vertical-align: middle; color: #080808;}
.sidebar .header span { font-size: 1.5rem; vertical-align: middle; filter: brightness(0); }
.sidebar .profile { text-align: center; padding: 20px 0; border-bottom: 1px solid var(--border-color); }
.sidebar .profile img { width: 80px; height: 80px; border-radius: 50%; margin-bottom: 10px; }
.sidebar .profile p { margin: 0; font-size: 0.9rem; color: #6c757d; }
.sidebar .profile .icons { display: flex; justify-content: center; margin-top: 10px; }
.sidebar .profile .icons i {
  font-size: 1.2rem; margin: 0 5px; color: #6c757d; cursor: pointer; transition: color 0.3s ease;
}
.sidebar .profile .icons i:hover { color: #0a75f7; }
.sidebar .nav-link {
  color: #0c0c0c; padding: 10px 20px; display: flex; align-items: center; transition: all 0.3s ease;
}
.sidebar .nav-link:hover,
.sidebar .nav-link.router-link-active {
  color: #0a75f7; background-color: #f8f9fa;
}
.sidebar .nav-link i { margin-right: 10px; }
.sidebar .nav-link .badge { margin-left: auto; background-color: #dc3545; }

.sidebar .collapsible-toggle {
  cursor: pointer;
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
}
.sidebar .collapsible-toggle .toggle-icon {
    transition: transform 0.3s ease;
    font-size: 0.8rem;
    margin-right: 0;
}
.sidebar .collapsible-toggle .toggle-icon.rotated {
    transform: rotate(180deg);
}
.nav-submenu {
  list-style: none;
  padding-left: 0;
  margin: 0;
  max-height: 0;
  overflow: hidden;
  transition: max-height 0.35s ease-in-out;
  background-color: #fdfdfd;
}
.nav-submenu.open {
  max-height: 200px;
}
.nav-submenu .nav-link {
    padding-left: 40px; 
    background-color: transparent !important; 
}
.nav-submenu .nav-link.router-link-active {
   color: #0a75f7; 
}
.nav-submenu .nav-link:hover {
   color: #0a75f7;
}

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
.navbar-top .d-flex i { font-size: 1.2rem; color: #6c757d; cursor: pointer; }
.navbar-top .d-flex span { font-weight: 500; color: #6c757d; }
.admin-main { padding: 20px 20px 0; min-height: calc(100vh - 50px - 60px); width: 100%; background-color: #f5f7fb; }
.admin-main>* { width: 100%; }

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