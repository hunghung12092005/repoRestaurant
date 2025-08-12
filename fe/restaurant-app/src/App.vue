<template>
  <div>
    <!-- ========== PHẦN GIAO DIỆN QUẢN TRỊ (ADMIN & STAFF) ========== -->
    <div v-if="$route.path.startsWith('/admin')" class="d-flex">
      
      <!-- ===== SIDEBAR ĐỘNG CHO ADMIN & STAFF ===== -->
      <div v-if="isAdmin || isStaff" class="sidebar">
        <div class="header text-center p-3 border-bottom">
          <img src="https://i.postimg.cc/J0d3p6Ww/logo-HXH.png" alt="Luxuria Logo" class="logo" />
          <span class="fw-bold">Hồ Xuân Hương</span>
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
          <!-- Tiêu đề phân vùng: TRANG QUẢN TRỊ -->
          <li class="nav-section-title">TRANG QUẢN TRỊ</li>
          <li class="nav-item"><router-link class="nav-link" to="/admin/dashboard"><i class="bi bi-grid-1x2"></i> Trang thống kê</router-link></li>
          <li class="nav-item"><router-link class="nav-link" to="/admin/occupancy"><i class="bi bi-map"></i> Quản lý sơ đồ phòng</router-link></li>
          <li class="nav-item"><router-link class="nav-link" to="/admin/bookings"><i class="bi bi-journal-check"></i> Quản lý đặt phòng</router-link></li>
          <li class="nav-item"><router-link class="nav-link" to="/admin/booking-histories"><i class="bi bi-clock-history"></i> Lịch sử đặt phòng</router-link></li>

          <!-- Tiêu đề phân vùng: QUẢN LÝ PHÒNG -->
          <li class="nav-section-title">QUẢN LÝ PHÒNG</li>
          <li><router-link class="nav-link" to="/admin/room-types"><i class="bi bi-tags"></i> Danh mục phòng</router-link></li>
          <li><router-link class="nav-link" to="/admin/prices"><i class='bx bxs-dollar-circle'></i> Quản lý giá phòng</router-link></li>
          <li><router-link class="nav-link" to="/admin/rooms"><i class='bx bx-bed'></i> Quản lý phòng</router-link></li>

          <!-- Tiêu đề phân vùng: QUẢN LÝ DỊCH VỤ & TIỆN NGHI -->
          <li class="nav-section-title">QUẢN LÝ DỊCH VỤ & TIỆN NGHI</li>
          <template v-if="isAdmin">
            <li class="nav-item">
              <router-link class="nav-link" to="/admin/services"><i class="bi bi-box-seam"></i> Quản lý dịch vụ</router-link>
            </li>
            <li class="nav-item">
              <router-link class="nav-link" to="/admin/amenities"><i class='bi bi-gem'></i> Quản lý tiện nghi</router-link>
            </li>
          </template>

          <!-- Tiêu đề phân vùng: QUYỀN THEO PHÂN QUYỀN -->
          <li class="nav-section-title">QUYỀN THEO PHÂN QUYỀN</li>
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
                <li><router-link class="nav-link" to="/admin/news-comments"><i class="bi bi-chat-dots"></i>Bình luận</router-link></li>
              </ul>
            </li>
          </template>
          <!-- Quản lý Liên hệ -->
          <li v-if="hasPermission('manage_contacts')" class="nav-item">
            <router-link class="nav-link" to="/admin/contacts"><i class="bi bi-envelope"></i>Quản lý liên hệ</router-link>
          </li>
          <!-- Quản lý coupons -->
          <li v-if="hasPermission('manage_coupons')" class="nav-item">
            <router-link class="nav-link" to="/admin/coupons"><i class="bi bi-ticket-perforated"></i>Quản lý discount</router-link>
          </li>
          <!-- Quản lý Tài khoản -->
          <li v-if="hasPermission('manage_users')" class="nav-item"><router-link class="nav-link" to="/admin/users"><i class="bi bi-people"></i>Quản lý tài khoản</router-link></li>
          <!-- Training AI -->
          <li v-if="hasPermission('manage_ai_training')" class="nav-item"><router-link class="nav-link" to="/admin/traningAI"><i class="bi bi-robot"></i>Training AI</router-link></li>
          <!-- Chat Admin -->
          <li v-if="hasPermission('manage_admin_chat')" class="nav-item"><router-link class="nav-link" to="/admin/ChatAdmin"><i class="bi bi-chat-dots"></i>Chat Admin</router-link></li>

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
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
@import url('https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css');
@import url('https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css');
@import url('https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css');

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: 'Inter', sans-serif;
}

body {
  background-color: #f8f9fa;
  height: 100%;
  overflow-y: auto;
}

/* === Navbar Khách Hàng === */
.navbar {
  background-color: #ffffff;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  height: 80px;
  padding: 0 1.5rem;
  transition: all 0.3s ease;
}

.navbar .container-fluid {
  position: relative;
  height: 100%;
}

.navbar-brand {
  display: flex;
  align-items: center;
  font-weight: 600;
  color: #081B54;
  transition: color 0.3s ease;
  position: absolute;
  left: 50%;
  transform: translateX(-50%);
  top: 50%;
  transform: translate(-50%, -50%);
}

.logo-img {
  width: 60px;
  height: 50px;
  margin: 0 10px;
}

.navbar-toggler {
  position: absolute;
  right: 1rem;
  top: 50%;
  transform: translateY(-50%);
}

.navbar-collapse {
  height: 100%;
}

.navbar-nav {
  gap: 1rem;
}

.navbar-nav-left {
  margin-right: auto;
}

.navbar-nav-right {
  margin-left: auto;
}

.nav-link {
  color: #081B54;
  font-weight: 500;
  padding: 0.5rem 1rem;
  transition: color 0.2s ease-in-out, background-color 0.2s ease-in-out;
}

.nav-link:hover {
  color: #A98A66;
  background-color: #f1f3f5;
  border-radius: 6px;
}

.btn-outline-custom {
  border: 1px solid #e9ecef;
  color: #081B54;
  padding: 8px 18px;
  border-radius: 6px;
  background-color: transparent;
  text-decoration: none;
  display: inline-block;
  transition: all 0.3s ease;
}

.btn-outline-custom:hover {
  background-color: #A98A66;
  color: #ffffff;
  border-color: #A98A66;
}

.dropdown-item:hover {
  color: #A98A66 !important;
  background-color: #f1f3f5;
}

main {
  min-height: calc(100vh - 80px);
  width: 100%;
  padding-top: 80px;
}

footer {
  background-color: #081B54;
  color: #fff;
}

/* === Sidebar Admin === */
.sidebar {
  height: 100vh;
  background: linear-gradient(180deg, #ffffff 0%, #f8f9fa 100%);
  box-shadow: 2px 0 8px rgba(0, 0, 0, 0.1);
  padding-top: 20px;
  position: fixed;
  width: 260px;
  z-index: 1000;
  overflow-y: auto;
  scrollbar-width: none;
  transition: transform 0.3s ease;
}

.sidebar::-webkit-scrollbar {
  display: none;
}

.sidebar .header {
  text-align: center;
  padding: 1.5rem;
  border-bottom: 1px solid #e9ecef;
}

.sidebar .header .logo {
  width: 40px;
  margin: 0.5rem;
}

.sidebar .header span {
  font-size: 1.4rem;
  font-weight: 600;
  color: #081B54;
}

.sidebar .profile {
  text-align: center;
  padding: 1.5rem 0;
  border-bottom: 1px solid #e9ecef;
}

.sidebar .profile p {
  margin: 0;
  font-size: 0.95rem;
  color: #6c757d;
}

.sidebar .profile .icons {
  display: flex;
  justify-content: center;
  margin-top: 1rem;
}

.sidebar .profile .icons i {
  font-size: 1.3rem;
  margin: 0 8px;
  color: #6c757d;
  cursor: pointer;
  transition: color 0.3s ease, transform 0.2s ease;
}

.sidebar .profile .icons i:hover {
  color: #A98A66;
  transform: scale(1.2);
}

.sidebar .nav-link {
  color: #081B54;
  padding: 12px 20px;
  display: flex;
  align-items: center;
  font-weight: 500;
  transition: all 0.3s ease;
  border-left: 3px solid transparent;
}

.sidebar .nav-link:hover,
.sidebar .nav-link.router-link-active {
  color: #A98A66;
  background-color: #f1f3f5;
  border-left: 3px solid #A98A66;
}

.sidebar .nav-link i {
  margin-right: 12px;
  font-size: 1.2rem;
  width: 20px; /* Căn chỉnh icon */
  text-align: center; /* Căn chỉnh icon */
}

.sidebar .collapsible-toggle {
  cursor: pointer;
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
  padding: 12px 20px;
  color: #081B54;
  font-weight: 500;
}

.sidebar .collapsible-toggle .toggle-icon {
  transition: transform 0.3s ease;
  font-size: 0.9rem;
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
  background-color: #f8f9fa;
}

.nav-submenu.open {
  max-height: 200px;
}

.nav-submenu .nav-link {
  padding-left: 40px;
  font-size: 0.95rem;
  background-color: transparent !important;
}

.nav-submenu .nav-link:hover,
.nav-submenu .nav-link.router-link-active {
  color: #A98A66;
  background-color: #f1f3f5 !important;
}

/* === Tiêu đề phân vùng trong Sidebar === */
.nav-section-title {
  padding: 8px 20px;
  font-size: 0.9rem;
  font-weight: 400;
  color: #6c757d;
  background-color: #f8f9fa;
  text-transform: uppercase;
  opacity: 0.7;
  border-bottom: 1px solid #e9ecef;
  margin-top: 10px;
}

/* === Main Content Admin === */
.main-content {
  margin-left: 260px;
  padding: 20px;
  padding-top: 80px;
  min-height: 100vh;
  background-color: #f8f9fa;
  width: calc(100% - 260px);
  overflow-y: auto;
}

.navbar-top {
  padding: 12px 20px;
  display: flex;
  justify-content: flex-end;
  align-items: center;
  position: fixed;
  top: 0;
  left: 260px;
  right: 0;
  z-index: 900;
  height: 60px;
  background-color: #ffffff;
  transition: all 0.3s ease;
}

.navbar-top.scrolled {
  background-color: #ffffff;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.navbar-top .d-flex i {
  font-size: 1.3rem;
  color: #6c757d;
  cursor: pointer;
  transition: color 0.3s ease;
}

.navbar-top .d-flex i:hover {
  color: #A98A66;
}

.navbar-top .d-flex span {
  font-weight: 500;
  color: #081B54;
  margin-left: 10px;
}

.admin-main {
  padding: 20px;
  min-height: calc(100vh - 60px);
  width: 100%;
  background-color: transparent;
}

.admin-main > * {
  width: 100%;
  background: #ffffff;
  border-radius: 10px;
  padding: 20px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

/* === Responsive === */
@media (max-width: 991px) {
  .navbar-brand {
    position: static;
    transform: none;
    margin-right: auto;
  }

  .navbar-toggler {
    position: static;
    transform: none;
  }

  .navbar .container-fluid {
    display: flex;
    justify-content: space-between;
    width: 100%;
  }

  .collapse.show .navbar-nav {
    flex-direction: column;
    align-items: flex-start !important;
    gap: 1rem;
    padding: 1rem 0;
  }

  .collapse.show .navbar-nav-left,
  .collapse.show .navbar-nav-right {
    margin: 0 !important;
    width: 100%;
  }

  .collapse.show .navbar-nav li {
    width: 100%;
  }

  .collapse.show .navbar-nav .btn {
    width: 100%;
    text-align: left;
  }

  .navbar-collapse {
    position: fixed;
    top: 80px;
    right: 0;
    height: calc(100vh - 80px);
    width: 250px;
    background-color: #fff;
    transform: translateX(100%);
    transition: transform 0.3s ease-in-out;
    padding: 20px;
    z-index: 1000;
    box-shadow: -2px 0 10px rgba(0, 0, 0, 0.1);
  }

  .navbar-collapse.show {
    transform: translateX(0);
  }

  .sidebar {
    width: 260px;
    transform: translateX(-100%);
    transition: transform 0.3s ease;
  }

  .main-content {
    margin-left: 0;
    width: 100%;
  }

  .navbar-top {
    left: 0;
    width: 100%;
  }
}
</style>
