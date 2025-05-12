<template>
  <div class="container">
    <h1 class="my-4 text-center">Welcome to My App</h1>

    <nav class="navbar navbar-light bg-light mb-4">
      <ul class="nav">
        <li class="nav-item">
          <router-link to="/" exact class="nav-link">Home</router-link>
        </li>
        <li class="nav-item">
          <router-link to="/about" class="nav-link">About</router-link>
        </li>
        <li class="nav-item">
          <router-link to="/products" class="nav-link">Products</router-link>
        </li>
        <li class="nav-item">
          <router-link to="/contact" class="nav-link">Contact</router-link>
        </li>
      </ul>
    </nav>

    <router-view></router-view>
  </div>
</template>

<script>
export default {
  name: 'App'
}
</script>

<style scoped>
.nav-link {
  color: #007bff;
  transition: all 0.3s ease;
}
.nav-link:hover {
  text-decoration: underline;
}
</style>
