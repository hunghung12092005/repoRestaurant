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
        <li><router-link to="/customer">Customer</router-link></li>
        <li><router-link to="/reservation">Reservation</router-link></li>
        <li><a :href="loginUrl">Login</a></li>
      </ul>
    </header>
    
    <!-- Đặt RouterView vào phần thân -->
    <main>
      <RouterView></RouterView>
    </main>
  </div>
</template>
<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { RouterView } from 'vue-router';
const loginUrl = import.meta.env.VITE_LOGIN_URL; // Đường dẫn sẽ được lấy từ biến môi trường .env 
const headerRef = ref(null);
const navbarRef = ref(null);
const navbarActive = ref(false);

const toggleMenu = () => {
  navbarActive.value = !navbarActive.value;
};

const handleScroll = () => {
  if (headerRef.value) {
    console.log("scrollY:", window.scrollY);
    headerRef.value.classList.toggle('active', window.scrollY > 0);
  }
};


onMounted(() => {
  window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll);
});
</script>

<script>
export default {
  name: 'App'
}
</script>

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
  padding-top: 80px; /* Để tránh nội dung bị che bởi header */
  min-height: calc(100vh - 80px); /* Chiếm toàn bộ chiều cao còn lại */
  width: 100%;
}
</style>
