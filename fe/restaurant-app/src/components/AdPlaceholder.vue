<template>
  <div class="ad-placeholder">
    <ins class="adsbygoogle"
         style="display:block"
         data-ad-client="ca-pub-2849216366032057" 
         :data-ad-slot="adSlot"
         data-ad-format="auto"
         data-full-width-responsive="true"></ins>
  </div>
</template>

<script setup>
// This component doesn't need any script logic.
</script>

<style scoped>
.ad-placeholder {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  min-height: 250px; /* Chiều cao phổ biến cho quảng cáo sidebar */
  background-color: #e9ecef; /* Màu nền nhẹ nhàng */
  border: 2px dashed #ced4da; /* Viền đứt để thể hiện là placeholder */
  border-radius: 8px;
  color: #6c757d; /* Màu chữ mờ */
  text-align: center;
  padding: 20px;
  width: 100%;
}

.placeholder-icon {
  font-size: 2.5rem;
  margin-bottom: 0.5rem;
}

.placeholder-text {
  font-weight: 600;
  font-size: 1rem;
}

.placeholder-subtext {
  font-size: 0.8rem;
  margin-top: 4px;
}
</style>