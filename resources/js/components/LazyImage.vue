<!-- LazyImage.vue -->
<template>
    <img v-if="isVisible" :src="src" :alt="alt" />
  </template>
  
  <script setup>
  import { ref, onMounted, onUnmounted } from 'vue';
  
  const props = defineProps(['src', 'alt']);
  const isVisible = ref(false);
  const observer = ref(null);
  
  const load = (entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        isVisible.value = true;
        observer.value.unobserve(entry.target);
      }
    });
  };
  
  onMounted(() => {
    observer.value = new IntersectionObserver(load);
    const img = document.querySelector('img');
    if (img) {
      observer.value.observe(img);
    }
  });
  
  onUnmounted(() => {
    if (observer.value) {
      observer.value.disconnect();
    }
  });
  </script>