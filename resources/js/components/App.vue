<template>
    <div>
      <h1>Welcome to My App</h1>
      <nav>
        <router-link to="/">Home</router-link>
        <router-link to="/about">About</router-link>
        <router-link to="/products">Products</router-link>
      </nav>
      <router-view></router-view> <!-- Nơi hiển thị các component -->
    </div>
  </template>
  
  <script>
  export default {
    name: 'App'
  }
  </script>
  
  <style scoped>
  nav {
      margin-bottom: 20px;
  }
  </style>