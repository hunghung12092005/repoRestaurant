<template>
    <div class="main-s mt-5">ad</div>
</template>
<style scoped>

.main-s {
    position: relative;
    background-color: white;
    width: 100%;
    height: 100vh;
}

.main-s::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    /* background: url('https://cf.bstatic.com/xdata/images/hotel/max1280x900/295360783.jpg?k=cb6a93f5158c7887b4e8e92e1f901cf96e049f40f5168617a96420deafe6a5e8&o=&hp=1');
    background-size: cover; */
    /* Đảm bảo hình ảnh phủ toàn bộ */
    opacity: 0.5;
    /* Độ mờ của hình nền */
    z-index: -1;
    /* Đặt lớp phủ phía dưới nội dung */
}

</style>
<script setup>

</script>
<script>
</script>