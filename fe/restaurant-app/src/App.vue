<template>
  <div>
    <div v-if="$route.path.startsWith('/admin')" class="d-flex">
      <div class="sidebar">
        <div class="header text-center p-3 border-bottom">
          <img src="https://i.postimg.cc/d3pNGXPN/7c6764b8-de90-474c-9b98-05019aef3193.png" alt="Luxuria Logo" class="rounded-circle" />
          <span class="fw-bold"><strong>Ho Xuan Huong</strong> Ecosystem</span>
        </div>
        <div class="profile text-center p-3 border-bottom">
          <img src="https://www.pngkey.com/png/full/263-2635979_admin-abuse.png" alt="Profile Picture"
            class="rounded-circle" />
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
              Dashboard</router-link></li>
          <li class="nav-item"><router-link class="nav-link" to="/admin/occupancy"><i class="bi bi-house-door"></i>
              Occupancy <span class="badge bg-danger">5</span></router-link></li>
          <li class="nav-item"><router-link class="nav-link" to="/admin/bookings"><i class="bi bi-book"></i>
              Bookings</router-link></li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="roomsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class='bx  bx-bed'></i> Dịch vụ và tiện nghi
            </a>
            <ul class="dropdown-menu" aria-labelledby="roomsDropdown">
              <li>
                <router-link class="dropdown-item" to="/admin/services">
                  <i class="bi bi-list-ul me-2"></i> Quản lý dịch vụ
                </router-link>
              </li>
              <li>
                <router-link class="dropdown-item" to="/admin/amenities">
                  <i class='bx  bx-bed'></i> Quản lý tiện nghi
                </router-link>
              </li>
            </ul>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="roomsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class='bx  bx-bed'></i> Quản lý phòng
            </a>
            <ul class="dropdown-menu" aria-labelledby="roomsDropdown">
              <li>
                <router-link class="dropdown-item" to="/admin/room-types">
                  <i class="bi bi-list-ul me-2"></i> Danh mục phòng
                </router-link>
              </li>
              <li>
                <router-link class="dropdown-item" to="/admin/prices">
                  <i class='bx  bx-bed'></i> Quản lý giá phòng
                </router-link>
              </li>
              <li>
                <router-link class="dropdown-item" to="/admin/rooms">
                  <i class='bx  bx-bed'></i> Quản lý phòng
                </router-link>
              </li>
            </ul>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="tablesDropdown" role="button" data-bs-toggle="dropdown"
              aria-expanded="false">
              <i class="bi bi-table"></i> Quản lý bàn
            </a>
            <ul class="dropdown-menu" aria-labelledby="tablesDropdown">
              <li>
                <router-link class="dropdown-item" to="/admin/table-types">
                  <i class="bi bi-list-ul me-2"></i> Danh mục bàn
                </router-link>
              </li>
              <li>
                <router-link class="dropdown-item" to="/admin/tables">
                  <i class="bi bi-table me-2"></i> Quản lý bàn
                </router-link>
              </li>
            </ul>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="menuDropdown" role="button" data-bs-toggle="dropdown"
              aria-expanded="false">
              <i class="bi bi-menu"></i> Quản lý thực đơn
            </a>
            <ul class="dropdown-menu" aria-labelledby="menuDropdown">
              <li>
                <router-link class="dropdown-item" to="/admin/menu-categories">
                  <i class="bi bi-list-ul me-2"></i> Danh mục thực đơn
                </router-link>
              </li>
              <li>
                <router-link class="dropdown-item" to="/admin/menus">
                  <i class="bi bi-menu me-2"></i> Quản lý thực đơn
                </router-link>
              </li>
            </ul>
          </li>
          <li class="nav-item"><router-link class="nav-link" to="/admin/staffs"><i class="bi bi-people"></i>
              Staffs</router-link></li>
          <li class="nav-item"><router-link class="nav-link" to="/admin/departments"><i class="bi bi-gear"></i>
              Departments</router-link></li>
          <li class="nav-item"><router-link class="nav-link" to="/admin/housekeeping"><i class="bi bi-house"></i>
              Housekeeping</router-link></li>
          <li class="nav-item"><router-link class="nav-link" to="/admin/leave-management"><i class="bi bi-calendar"></i>
              Leave Management</router-link></li>
          <li class="nav-item"><router-link class="nav-link" to="/"><i class="bi bi-box-arrow-left"></i>
              Thoát</router-link></li>
        </ul>
      </div>

      <!-- Main Content -->
      <div class="main-content">
        <!-- Top Navbar -->
        <div class="navbar-top" :class="{ 'scrolled': navbarSticky }">
          <div class="d-flex align-items-center">
            <i class="bi bi-globe"></i>
            <i class="bi bi-bell mx-3"></i>
            <span>{{ userInfo.name || 'Admin' }}</span>
          </div>
        </div>

        <!-- Dynamic Content -->
        <main class="admin-main">
          <RouterView></RouterView>
        </main>
        <footer class="admin-footer text-center py-3">Admin Footer - Sea Foodie</footer>
      </div>
    </div>

    <!-- Layout cho các route không phải admin -->
    <div v-else>
      <header ref="headerRef">
        <nav class="navbar navbar-expand-lg navbar-light fixed-top" :class="{ 'active': navbarSticky }">
          <div class="container-fluid">
            <!-- Logo -->
            <a class="navbar-brand" href="/">
              <img src="https://i.postimg.cc/d3pNGXPN/7c6764b8-de90-474c-9b98-05019aef3193.png" alt="Foodie Logo" class="logo-img" />
              Ho Xuan Huong Eco
            </a>

            <!-- Toggle Button for Mobile -->
            <button class="navbar-toggler" type="button" @click="toggleMenu" aria-controls="navbarNav"
              aria-expanded="false" aria-label="Toggle navigation">
              <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Navbar Links -->
            <div class="collapse navbar-collapse" id="navbarNav" ref="navbarRef" :class="{ 'show': navbarActive }">
              <ul class="navbar-nav ms-auto">
                <li class="nav-item"><router-link class="nav-link sisf-m-subtitle" to="/">Home</router-link></li>


                <li class="nav-item"><router-link class="nav-link sisf-m-subtitle" to="/blog">Blog</router-link></li>
                <li class="nav-item dropdown">
                  <a class="nav-link dropdown-toggle sisf-m-subtitle" href="#" role="button" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    Menu
                  </a>
                  <ul class="dropdown-menu">
                    <li><router-link class="dropdown-item " to="/menu-list">Menu Đặt Món</router-link></li>
                    <li><router-link class="dropdown-item " to="/menu">menu</router-link></li>
                    <li><router-link class="dropdown-item " to="/product-detail">Chi Tiết Online</router-link></li>
                    <li><router-link class="dropdown-item " to="/CategoryShopOnline">Menu ShopOnline</router-link></li>
                  </ul>
                </li>
                <li class="nav-item "><router-link class="nav-link sisf-m-subtitle" to="/rooms">Đặt
                    Phòng</router-link></li>
                <li class="nav-item "><router-link class="nav-link sisf-m-subtitle" to="/reservation">Đặt
                    Bàn</router-link></li>
                <li class="nav-item dropdown " v-if="isLogin">
                  <a class="nav-link dropdown-toggle sisf-m-subtitle" href="#" role="button" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    Xin chào, {{ userInfo.name }}!
                  </a>
                  <ul class="dropdown-menu dropdown-menu-end">
                    <li><router-link class="dropdown-item" to="/testJwt">testJwt</router-link></li>
                    <li><router-link class="dropdown-item" to="/userprofile">Thông Tin User</router-link></li>
                    <li v-if="isAdmin"><router-link class="dropdown-item" to="/admin">Vào admin</router-link></li>
                    <li><a class="dropdown-item logout-link" @click.prevent="logout">Đăng Xuất</a></li>
                  </ul>
                </li>
                <li class="nav-item" v-else>
                  <router-link class="nav-link sisf-m-subtitle" to="/login">Đăng Nhập</router-link>
                </li>
              </ul>
            </div>
          </div>
        </nav>
      </header>

      <main>
        <router-view />
      </main>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axiosConfig from './axiosConfig.js';
import { provide } from 'vue';

const route = useRoute();
const router = useRouter();
const headerRef = ref(null);
const navbarRef = ref(null);
const navbarActive = ref(false);
const navbarSticky = ref(false);
const userInfo = ref(null);
const isLogin = ref(false);
const isAdmin = ref(false);
const apiUrl = 'http://localhost:8000';
provide('apiUrl', apiUrl);

// Biến để theo dõi vị trí cuộn trước đó
const lastScrollPosition = ref(0);

const toggleMenu = () => {
  navbarActive.value = !navbarActive.value;
};

const handleScroll = () => {
  if (headerRef.value || route.path.startsWith('/admin')) {
    const currentScrollPosition = window.scrollY;

    // Áp dụng hiệu ứng sticky cho navbar trên mọi kích thước màn hình
    navbarSticky.value = currentScrollPosition > 50;

    lastScrollPosition.value = currentScrollPosition;
  }
};

// Đóng menu khi nhấn bên ngoài
const handleOutsideClick = (event) => {
  if (window.innerWidth <= 991 && navbarActive.value && navbarRef.value) {
    const isClickInside = navbarRef.value.contains(event.target);
    const isToggleButton = event.target.closest('.navbar-toggler');
    if (!isClickInside && !isToggleButton) {
      navbarActive.value = false;
    }
  }
};

const restoreUserSession = () => {
  const storedToken = localStorage.getItem('tokenJwt');
  const storedUser = localStorage.getItem('userInfo');
  if (storedToken && storedUser) {
    try {
      userInfo.value = JSON.parse(storedUser);
      isLogin.value = true;
      isAdmin.value = userInfo.value.role === 'admin';
    } catch (e) {
      console.error('Error parsing stored user info:', e);
      logout();
    }
  }
};

const fetchUserInfo = async () => {
  const token = localStorage.getItem('tokenJwt');
  if (!token) {
    isLogin.value = false;
    isAdmin.value = false;
    return;
  }

  try {
    const response = await axiosConfig.get(`${apiUrl}/api/protected`);
    userInfo.value = response.data.user;
    isLogin.value = true;
    isAdmin.value = userInfo.value.role === 'admin';
  } catch (error) {
    console.error('Error fetching user info:', error.response ? error.response.data : error.message);
    if (error.response?.status === 401) {
      logout(); // Xóa token và đăng xuất nếu token không hợp lệ
    }
  }
};

const handleUrlParams = () => {
  const urlParams = new URLSearchParams(window.location.search);
  const token = urlParams.get('token');
  const user = urlParams.get('user');

  if (token && user) {
    localStorage.setItem('tokenJwt', token);
    try {
      const parsedUser = JSON.parse(user);
      localStorage.setItem('userInfo', user);
      userInfo.value = parsedUser;
      isLogin.value = true;
      isAdmin.value = parsedUser.role === 'admin';
      // Xóa query params khỏi URL
      router.replace({ query: {} });
    } catch (e) {
      console.error('Error parsing user from URL:', e);
    }
  }
};

const logout = () => {
  localStorage.removeItem('tokenJwt');
  localStorage.removeItem('userInfo');
  userInfo.value = null;
  isLogin.value = false;
  isAdmin.value = false;
  router.push('/');
};

const goToProfile = () => {
  router.push('/admin/profile');
};

const editProfile = () => {
  router.push('/admin/edit-profile');
};

// Navigation guard để bảo vệ route admin
router.beforeEach((to, from, next) => {
  const isAuthenticated = !!localStorage.getItem('tokenJwt');
  const isAdminUser = userInfo.value?.role === 'admin';

  if (to.path.startsWith('/admin') && (!isAuthenticated || !isAdminUser)) {
    next('/'); // Chuyển về trang chủ nếu không đăng nhập hoặc không phải admin
  } else {
    next();
  }
});

onMounted(() => {
  restoreUserSession(); // Khôi phục phiên từ localStorage
  handleUrlParams(); // Xử lý token/user từ URL nếu có
  fetchUserInfo(); // Lấy thông tin người dùng từ API
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

/* Styles cho layout thông thường */
.navbar {
  transition: background-color 0.3s ease, color 0.3s ease;
  padding: 10px 15px;
  /* background: url('https://i.pinimg.com/1200x/61/e5/99/61e599a4b5a497cf75d3e61f030574cc.jpg'); */
  background-size: cover;
  background-color: rgb(56, 80, 124);
}

.navbar-brand {
  display: flex;
  align-items: center;
  font-size: 1rem;
  font-weight: 600;
  color: white;
  transition: color 0.3s ease;
}

.sisf-m-subtitle {
  font-family: 'Roboto', sans-serif;
  font-size: 1rem;
  line-height: 1.2;
  font-weight: 600;
  padding: 0 20px !important;
  /* color:rgba(43, 212, 231, 0.7); */
  color: white;
}

.navbar.active .navbar-brand,
.navbar.active .nav-link,
.navbar.active .dropdown-toggle {
  color: black;
}

.navbar.active .navbar-toggler-icon {
  filter: brightness(0) invert(1);
  /* Đổi màu biểu tượng toggle thành trắng khi active */
}

.navbar.active .navbar-toggler-icon {
  filter: brightness(0) invert(1);
}

.logo-img {
  width: 35px;
  margin-right: 10px;
  filter: brightness(0);
}

.navbar.active .logo-img {
  filter: brightness(100);
}

/* .nav-link {
  font-size: 1rem;
  font-weight: 500;
  color: rgb(14, 192, 224);
  padding: 8px 15px !important;
  transition: all 0.3s ease;
} */


.nav-link:hover {
  color: black;
  border-radius: 4px;
}

.dropdown-item {
  cursor: pointer;
  font-size: 1rem;
  font-weight: 500;
  color: #000;
  padding: 8px 15px;
  transition: all 0.3s ease;
}

.dropdown-item:hover {
  color: #0e45e9 !important;
}

.logout-link {
  cursor: pointer;
  color: #000 !important;
}

.logout-link:hover {
  color: #0e45e9 !important;
}

main {
  /* padding-top: 70px; */
  /* margin-bottom: 20px; */
  min-height: calc(100vh - 70px);
  width: 100%;
}

footer {
  background-color: var(--second-color);
  color: #fff;
}

/* Admin Layout Styles */
body {
  background-color: #f5f7fb;
  height: 100%;
  overflow-y: auto;
}

.sidebar {
  height: 100vh;
  background-color: #fff;
  box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
  padding-top: 20px;
  position: fixed;
  width: 250px;
  z-index: 1000;
  overflow-y: auto;
  scrollbar-width: none;
}

.sidebar::-webkit-scrollbar {
  display: none;
}

.sidebar .header {
  text-align: center;
  padding: 20px;
  border-bottom: 1px solid var(--border-color);
}

.sidebar .header img {
  width: 30px;
  margin-right: 10px;
  vertical-align: middle;
}

.sidebar .header span {
  font-size: 1.5rem;
  vertical-align: middle;
  color: var(--badge-bg);
}

.sidebar .profile {
  text-align: center;
  padding: 20px 0;
  border-bottom: 1px solid var(--border-color);
}

.sidebar .profile img {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  margin-bottom: 10px;
}

.sidebar .profile p {
  margin: 0;
  font-size: 0.9rem;
  color: var(--text-muted);
}

.sidebar .profile .icons {
  display: flex;
  justify-content: center;
  margin-top: 10px;
}

.sidebar .profile .icons i {
  font-size: 1.2rem;
  margin: 0 5px;
  color: var(--text-muted);
  cursor: pointer;
  transition: color 0.3s ease;
}

.sidebar .profile .icons i:hover {
  color: var(--main-color);
}

.sidebar .nav-link {
  color: var(--text-muted);
  padding: 10px 20px;
  display: flex;
  align-items: center;
  transition: all 0.3s ease;
}

.sidebar .nav-link:hover,
.sidebar .nav-link.router-link-active {
  color: var(--main-color);
  background-color: #f8f9fa;
}

.sidebar .nav-link i {
  margin-right: 10px;
}

.sidebar .nav-link .badge {
  margin-left: auto;
  background-color: var(--badge-bg);
}

.main-content {
  margin-left: 250px;
  padding: 20px;
  padding-top: 60px;
  min-height: 100vh;
  background-color: #f5f7fb;
  width: calc(100% - 250px);
  overflow-y: auto;
}

.navbar-top {
  padding: 10px 20px;
  display: flex;
  justify-content: flex-end;
  align-items: center;
  position: fixed;
  top: 0;
  left: 250px;
  right: 0;
  z-index: 900;
  height: 50px;
  background-color: #f5f7fb;
  transition: background-color 0.1s ease;
}

.navbar-top.scrolled {
  background-color: #ffffff;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.navbar-top .d-flex i {
  font-size: 1.2rem;
  color: var(--text-muted);
  cursor: pointer;
}

.navbar-top .d-flex span {
  font-weight: 500;
  color: var(--text-muted);
}

.admin-main {
  padding: 20px 20px 0;
  min-height: calc(100vh - 50px - 60px);
  width: 100%;
  background-color: #f5f7fb;
}

.admin-main>* {
  width: 100%;
}

.admin-footer {
  background-color: var(--second-color);
  color: #000000;
}

/* Mobile menu styles */
@media (max-width: 991px) {
  .navbar-collapse {
    position: fixed;
    top: 0;
    right: 0;
    height: 100vh;
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

  .navbar-nav {
    flex-direction: column;
    background-color: transparent;
    padding: 10px;
  }

  .navbar.active .navbar-nav {
    background-color: transparent;
  }

  .nav-link,
  .dropdown-toggle {
    color: #000 !important;
    padding: 15px !important;
  }

  .navbar.active .nav-link,
  .navbar.active .dropdown-toggle {
    color: #000 !important;
    /* Màu đen cho mobile khi active */
  }

  .navbar.active .navbar-toggler-icon {
    filter: brightness(0) invert(1);
    /* Đổi màu biểu tượng toggle thành trắng khi active trên mobile */
  }

  .navbar.active .navbar-toggler-icon {
    filter: brightness(0) invert(1);
  }

  .nav-link:hover,
  .dropdown-item:hover {
    background-color: var(--main-color);
    color: #fff !important;
  }

  .dropdown-menu {
    background-color: #f8f9fa;
    margin-left: 10px;
  }

  .sidebar {
    width: 200px;
    transform: translateX(-100%);
    transition: transform 0.3s ease-in-out;
    overflow-y: auto;
    scrollbar-width: none;
  }

  .sidebar::-webkit-scrollbar {
    display: none;
  }

  .sidebar.show {
    transform: translateX(0);
  }

  .main-content {
    margin-left: 0;
    width: 100%;
  }

  .navbar-top {
    left: 0;
    width: 100%;
  }

  .admin-main {
    padding-top: 70px;
  }
}

@media (min-width: 992px) {

  .navbar.active .navbar-brand,
  .navbar.active .nav-link,
  .navbar.active .dropdown-toggle {
    color: #fff !important;
  }
}
</style>
