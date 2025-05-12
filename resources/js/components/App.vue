<template>
  <div>
    <nav class="navbar navbar-light py-2 d-flex justify-content-between align-items-center">
      <!-- Logo + tên -->
      <div class="d-flex align-items-center">
        <!-- <img src="/logo.png" alt="Logo" class="logo-img mr-2" /> -->
        <span class="h5 font-weight-bold">Sea Foodie</span>
      </div>

      <!-- Menu điều hướng -->
      <ul class="nav">
        <li class="nav-item">
          <router-link to="/" exact class="nav-link font-weight-bold">Home</router-link>
        </li>
        <li class="nav-item">
          <router-link to="/about" class="nav-link font-weight-bold">About</router-link>
        </li>
        <li class="nav-item">
          <router-link to="/products" class="nav-link font-weight-bold">Shop</router-link>
        </li>
        <li class="nav-item">
          <router-link to="/customer" class="nav-link font-weight-bold">Customer</router-link>
        </li>
        <li class="nav-item">
          <router-link to="/contact" class="nav-link font-weight-bold">Contact</router-link>
        </li>
      </ul>
    </nav>

    <!-- Hiển thị component -->
    <router-view />
  </div>
</template>

<script>
export default {
  name: 'App'
}
</script>

<style scoped>
.logo-img {
  width: 40px;
  height: 40px;
}
.nav-link {
  color: #000;
  transition: color 0.3s;
}
.nav-link:hover {
  color: #007bff;
  text-decoration: none;
}
.router-link-exact-active {
  color: #007bff;
}
</style>
