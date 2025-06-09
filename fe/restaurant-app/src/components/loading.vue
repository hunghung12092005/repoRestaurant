<template>
<div class="spinner-overlay">
    <!-- From Uiverse.io by cohencoo --> 
<div class="custom-loader"></div>
</div>
</template>
<style scoped>

.spinner-overlay {
    position: fixed; /* Đặt lớp overlay ở vị trí cố định */
    top: 0; /* Căn lên trên cùng */
    left: 0; /* Căn sang trái */
    width: 100%; /* Chiều rộng toàn bộ màn hình */
    height: 100%; /* Chiều cao toàn bộ màn hình */
    background-color: rgba(35, 36, 32, 0.6); /* Nền mờ */
    display: flex; /* Sử dụng flex để căn giữa */
    justify-content: center; /* Căn giữa theo chiều ngang */
    align-items: center; /* Căn giữa theo chiều dọc */
    z-index: 9998; /* Đảm bảo nằm dưới spinner */
}

/* From Uiverse.io by cohencoo */ 
.custom-loader {
  width: 70px;
  height: 70px;
  background: #f3720a;
  border-radius: 50px;
  -webkit-mask: radial-gradient(circle 31px at 50% calc(100% + 13px),#000 95%,#0000) top 4px left 50%,
    radial-gradient(circle 31px,#000 95%,#0000) center,
    radial-gradient(circle 31px at 50% -13px,#000 95%,#0000) bottom 4px left 50%,
    linear-gradient(#000 0 0);
  -webkit-mask-composite: xor;
  mask-composite: exclude;
  -webkit-mask-repeat: no-repeat;
  animation: cu10 1.5s infinite;
}

@keyframes cu10 {
  0% {
    -webkit-mask-size: 0    18px,0    18px,0    18px,auto
  }

  16.67% {
    -webkit-mask-size: 100% 18px,0    18px,0    18px,auto
  }

  33.33% {
    -webkit-mask-size: 100% 18px,100% 18px,0    18px,auto
  }

  50% {
    -webkit-mask-size: 100% 18px,100% 18px,100% 18px,auto
  }

  66.67% {
    -webkit-mask-size: 0    18px,100% 18px,100% 18px,auto
  }

  83.33% {
    -webkit-mask-size: 0    18px,0    18px,100% 18px,auto
  }

  100% {
    -webkit-mask-size: 0    18px,0    18px,0    18px,auto
  }
}
</style>