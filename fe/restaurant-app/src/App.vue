<template>
  <div>
    <!-- Kiểm tra nếu route là /admin thì hiển thị layout admin -->
    <div v-if="$route.path.startsWith('/admin')" class="d-flex">
      <!-- Sidebar -->
      <div class="sidebar">
        <div class="header text-center p-3 border-bottom">
          <img src="https://i.postimg.cc/s2Ywg6YR/logo.png" alt="Luxuria Logo" class="rounded-circle" />
          <span class="fw-bold">Sea Foodie</span>
        </div>
        <div class="profile text-center p-3 border-bottom">
          <img src="https://www.einfosoft.com/templates/admin/luxuria/source/light/assets/images/admin.jpg" alt="Profile Picture" class="rounded-circle" />
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
          <li class="nav-item "><router-link class="nav-link" to="/admin/dashboard"><i class="bi bi-grid"></i> Dashboard</router-link></li>
          <li class="nav-item"><router-link class="nav-link" to="/admin/occupancy"><i class="bi bi-house-door"></i> Occupancy <span class="badge bg-danger">5</span></router-link></li>
          <li class="nav-item"><router-link class="nav-link" to="/admin/bookings"><i class="bi bi-book"></i> Bookings</router-link></li>
          <li class="nav-item"><router-link class="nav-link" to="/admin/rooms"><i class="bi bi-building"></i> Rooms</router-link></li>
          <li class="nav-item"><router-link class="nav-link" to="/admin/staffs"><i class="bi bi-people"></i> Staffs</router-link></li>
          <li class="nav-item"><router-link class="nav-link" to="/admin/departments"><i class="bi bi-gear"></i> Departments</router-link></li>
          <li class="nav-item"><router-link class="nav-link" to="/admin/housekeeping"><i class="bi bi-house"></i> Housekeeping</router-link></li>
          <li class="nav-item"><router-link class="nav-link" to="/admin/leave-management"><i class="bi bi-calendar"></i> Leave Management</router-link></li>
          <li class="nav-item"><router-link class="nav-link" to="/"><i class="bi bi-box-arrow-left"></i> Thoát</router-link></li>
        </ul>
      </div>

      <!-- Main Content -->
      <div class="main-content">
        <!-- Top Navbar -->
        <div class="navbar-top" :class="{ 'scrolled': navbarActive }">
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
        <nav class="navbar navbar-expand-lg navbar-light fixed-top" :class="{ 'active': navbarActive }">
          <div class="container-fluid">
            <!-- Logo -->
            <a class="navbar-brand" href="/">
              <img src="https://i.postimg.cc/s2Ywg6YR/logo.png" alt="Foodie Logo" class="logo-img" />
              Sea Foodie
            </a>

            <!-- Toggle Button for Mobile -->
            <button class="navbar-toggler" type="button" @click="toggleMenu" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
              <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Navbar Links -->
            <div class="collapse navbar-collapse" id="navbarNav" ref="navbarRef" :class="{ 'show': navbarActive }">
              <ul class="navbar-nav ms-auto">
                <li class="nav-item"><router-link class="nav-link" to="/">Home</router-link></li>
                <li class="nav-item"><router-link class="nav-link" to="/about">About</router-link></li>
                <li class="nav-item"><router-link class="nav-link" to="/contact">Contact</router-link></li>
                <li class="nav-item"><router-link class="nav-link" to="/blog">Blog</router-link></li>
                <li class="nav-item dropdown">
                  <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    Menu
                  </a>
                  <ul class="dropdown-menu">
                    <li><router-link class="dropdown-item" to="/menu-list">Menu</router-link></li>
                    <li><router-link class="dropdown-item" to="/menu">menu</router-link></li>
                  </ul>
                </li>
                <li class="nav-item"><router-link class="nav-link" to="/reservation">Reservation</router-link></li>
                <li class="nav-item dropdown" v-if="isLogin">
                  <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    Xin chào, {{ userInfo.name }}!
                  </a>
                  <ul class="dropdown-menu dropdown-menu-end">
                    <li><router-link class="dropdown-item" to="/testJwt">testJwt</router-link></li>
                    <li v-if="isAdmin"><a class="dropdown-item" @click.prevent="adminPanel">Vào admin</a></li>
                    <li><a class="dropdown-item logout-link" @click.prevent="logout">Đăng Xuất</a></li>
                  </ul>
                </li>
                <li class="nav-item" v-else>
                  <router-link class="nav-link" to="/login">Đăng Nhập</router-link>
                </li>
              </ul>
            </div>
          </div>
        </nav>
      </header>

      <main>
        <RouterView></RouterView>
        <footer class="text-center py-3">đây là footer</footer>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useRoute } from 'vue-router';
import axiosConfig from './axiosConfig.js';
import { provide } from 'vue';

const route = useRoute();
const headerRef = ref(null);
const navbarRef = ref(null);
const navbarActive = ref(false);
const userInfo = ref(null);
const isLogin = ref(false);
const isAdmin = ref(false);
const apiUrl = 'http://localhost:8000';
provide('apiUrl', apiUrl);
const toggleMenu = () => {
  navbarActive.value = !navbarActive.value;
};

const handleScroll = () => {
  if (headerRef.value || route.path.startsWith('/admin')) {
    navbarActive.value = window.scrollY > 50;
  }
};

const fetchUserInfo = async () => {
  try {
    const response = await axiosConfig.get('http://127.0.0.1:8000/api/protected');
    userInfo.value = response.data.user;
    isLogin.value = true;
    if (userInfo.value.role === 'admin') {
      isAdmin.value = true;
    } else {
      isAdmin.value = false;
    }
  } catch (error) {
    console.error('Error fetching user info:', error.response ? error.response.data : error.message);
  }
};

const urlParams = new URLSearchParams(window.location.search);
const token = urlParams.get('token');
const user = urlParams.get('user');

if (token && user) {
  localStorage.setItem('token', token);
  localStorage.setItem('userInfo', user);
  userInfo.value = JSON.parse(user);
}

const goToProfile = () => {
  window.location.href = '/admin/profile';
};

const editProfile = () => {
  window.location.href = '/admin/edit-profile';
};

const logout = () => {
  localStorage.removeItem('token');
  localStorage.removeItem('userInfo');
  window.location.href = '/';
};

const adminPanel = () => {
  window.location.href = '/admin';
};

onMounted(() => {
  fetchUserInfo();
  window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll);
});
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Arial&display=swap');
@import url('https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css');
@import url('https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css');
@import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"');

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
  background-color: transparent;
}

.navbar.active {
  background-color: #081B54 !important;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.navbar-brand {
  display: flex;
  align-items: center;
  font-size: 1.2rem;
  font-weight: 600;
  color: #000 !important;
  transition: color 0.3s ease;
}

.navbar.active .navbar-brand,
.navbar.active .nav-link,
.navbar.active .dropdown-toggle {
  color: #fff !important;
}

.logo-img {
  width: 35px;
  margin-right: 10px;
  filter: brightness(0);
}

.navbar.active .logo-img {
  filter: brightness(100);
}

.nav-link {
  font-size: 1rem;
  font-weight: 500;
  color: #000;
  padding: 8px 15px !important;
  transition: all 0.3s ease;
}

.nav-link:hover {
  color: #0e45e9 !important;
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
  padding-top: 70px;
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
  overflow-y: auto; /* Enable vertical scrolling */
  scrollbar-width: none; /* Hide scrollbar for Firefox */
}

/* Hide scrollbar for WebKit browsers (Chrome, Safari, etc.) */
.sidebar::-webkit-scrollbar {
  display: none; /* Hide the scrollbar */
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

.admin-main > * {
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

  /* Admin mobile styles */
  .sidebar {
    width: 200px;
    transform: translateX(-100%);
    transition: transform 0.3s ease-in-out;
    overflow-y: auto; /* Ensure scrolling on mobile */
    scrollbar-width: none; /* Hide scrollbar for Firefox */
  }

  .sidebar::-webkit-scrollbar {
    display: none; /* Hide scrollbar for WebKit browsers */
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
</style>
