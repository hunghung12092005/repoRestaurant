<template>
  <div>
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
              <li class="nav-item">
                <router-link class="nav-link" to="/">Home</router-link>
              </li>
              <li class="nav-item">
                <router-link class="nav-link" to="/about">About</router-link>
              </li>
              <li class="nav-item">
                <router-link class="nav-link" to="/contact">Contact</router-link>
              </li>
              <li class="nav-item">
                <router-link class="nav-link" to="/blog">Blog</router-link>
              </li>
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                  Menu
                </a>
                <ul class="dropdown-menu">
                  <li><router-link class="dropdown-item" to="/menu-list">Menu</router-link></li>
                  <li><router-link class="dropdown-item" to="/menu">menu</router-link></li>
                </ul>
              </li>
              <li class="nav-item">
                <router-link class="nav-link" to="/reservation">Reservation</router-link>
              </li>
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
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import axiosConfig from './axiosConfig.js';

const headerRef = ref(null);
const navbarRef = ref(null);
const navbarActive = ref(false);
const userInfo = ref(null);
const isLogin = ref(false);
const isAdmin = ref(false);

const handleScroll = () => {
  if (headerRef.value) {
    navbarActive.value = window.scrollY > 0; // Cập nhật trực tiếp navbarActive
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

onMounted(() => {
  fetchUserInfo();
  window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll);
});

const logout = () => {
  localStorage.removeItem('token');
  localStorage.removeItem('userInfo');
  window.location.href = '/';
};

const adminPanel = () => {
  window.location.href = '/admin';
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Turret+Road:wght@400;500;700;800&display=swap');
@import url('https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css');

:root {
  --main-color: #16B978;
  --second-color: #081B54;
}

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: 'Poppins', sans-serif;
}

.navbar {
  transition: background-color 0.3s ease, color 0.3s ease;
  padding: 10px 15px;
  background-color: transparent; /* Nền trong suốt khi chưa cuộn */
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
  color: #fff !important; /* Đổi màu chữ thành trắng khi cuộn */
}

.logo-img {
  width: 35px;
  margin-right: 10px;
  filter: brightness(0); /* Đổi logo thành màu đen khi chưa cuộn */
}

.navbar.active .logo-img {
  filter: brightness(100); /* Đổi logo thành màu trắng khi cuộn */
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

/* Ensure mobile menu looks good */
@media (max-width: 991px) {
  .navbar-nav {
    background-color: #fff;
    padding: 10px;
    border-radius: 5px;
  }

  .navbar.active .navbar-nav {
    background-color: var(--second-color);
  }

  .navbar.active .nav-link,
  .navbar.active .dropdown-toggle {
    color: #fff !important;
  }

  .nav-link:hover,
  .dropdown-item:hover {
    background-color: var(--main-color);
    color: #fff !important;
  }
}
</style>