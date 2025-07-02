<template>
  <div class="confirmation-page mt-5">
    <div class="background-elements">
      <img src="https://motortrip.vn/wp-content/uploads/2022/03/khach-san-14.jpg" alt="Hotel Background Pattern" class="background-map-pattern">
      <!-- <img src="https://motortrip.vn/wp-content/uploads/2022/03/khach-san-14.jpg" alt="Hotel Aerial View" class="floating-aerial-view-img"> -->
    </div>

    <header class="page-header">
      <h1 class="header-main-title">XÁC NHẬN ĐẶT PHÒNG</h1>
      <h2 class="header-sub-title">THÀNH CÔNG</h2>
    </header>

    <section class="main-content-card">
      <div class="text-block">
        <p>Chúc mừng bạn! Yêu cầu đặt phòng của bạn đã được gửi thành công. Chúng tôi đã nhận được thông tin và sẽ xử lý sớm nhất.</p>
        <p>Vui lòng kiểm tra email hoặc mục "Lịch sử đặt phòng" để xem chi tiết và theo dõi trạng thái đơn đặt của bạn.</p>
        <p>Xin chân thành cảm ơn bạn đã lựa chọn dịch vụ của chúng tôi. Chúc bạn có một kỳ nghỉ tuyệt vời!</p>
      </div>
      <div class="image-block">
        <img src="https://pistachiohotel.com/UploadFile/Gallery/Overview/a3.jpg" alt="Luxury Hotel Room" class="container-terminal-img">
      </div>
    </section>

    <footer class="page-footer">
      <button class="back-button" @click="goBack">
        <svg class="button-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white"><path d="M0 0h24v24H0z" fill="none"/><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>
        <span class="button-text">QUAY LẠI</span>
      </button>
      <button class="view-booking-button" @click="viewBookingHistory">
        <svg class="button-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white"><path d="M0 0h24v24H0z" fill="none"/><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg>
        <span class="button-text">Xem Lịch Sử Booking</span>
      </button>
    </footer>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'; // Import useRouter để điều hướng

const router = useRouter();

// Hàm xử lý khi click nút "QUAY LẠI"
const goBack = () => {
  // Điều hướng về trang trước đó trong lịch sử trình duyệt
  //router.go(-1);
  // Hoặc điều hướng đến một trang cụ thể:
   router.push('/'); // Ví dụ: về trang chủ
};

// Hàm xử lý khi click nút "XEM BOOKING"
const viewBookingHistory = () => {
  // Điều hướng đến trang lịch sử đặt phòng của bạn
  // Thay '/booking-history' bằng đường dẫn thực tế của trang lịch sử đặt phòng
  router.push('/HistoryBooking');
};
</script>

<style scoped>
/*
  Font family: Montserrat (hoặc một font sans-serif hiện đại tương tự)
  Màu sắc chính:
    - Nền: #132F5C (xanh đậm)
    - Tiêu đề chính: #FFD700 (vàng)
    - Nút Xem Booking: #28a745 (xanh lá)
    - Nút Quay lại: #DC3545 (đỏ tươi)
    - Background overlay: rgba(255, 255, 255, 0.1)
*/

.confirmation-page {
  /* Nền và cấu trúc tổng thể */
  font-family: 'Montserrat', sans-serif;
  background-color: #132F5C; /* Màu nền xanh đậm chính */
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center; /* Căn giữa nội dung theo chiều dọc */
  padding: 40px 20px; /* Padding cho toàn trang */
  position: relative;
  overflow: hidden; /* Quan trọng để cắt các phần tử nền tràn ra */
  color: #fff; /* Màu chữ mặc định cho toàn trang */
  text-align: center;
}

/* Các yếu tố nền (background elements) */
.background-elements {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  pointer-events: none; /* Đảm bảo không cản trở tương tác người dùng */
  z-index: 0; /* Đặt phía sau nội dung chính */
}

.background-map-pattern {
  position: absolute;
  width: 120%; /* Làm lớn hơn để che phủ toàn bộ */
  height: 120%; /* Làm lớn hơn để che phủ toàn bộ */
  object-fit: cover; /* Đảm bảo ảnh bao phủ diện tích */
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  opacity: 0.2; /* Độ trong suốt để chìm vào nền */
}

.floating-aerial-view-img {
  position: absolute;
  top: 30px; /* Điều chỉnh vị trí giống ảnh */
  right: 30px; /* Điều chỉnh vị trí giống ảnh */
  width: 250px; /* Kích thước ảnh */
  height: auto;
  border-radius: 10px; /* Bo góc nhẹ */
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3); /* Bóng đổ sâu hơn */
  opacity: 0.9;
}

/* Tiêu đề trang */
.page-header {
  position: relative;
  z-index: 1; /* Đảm bảo nằm trên các phần tử nền */
  margin-bottom: 50px; /* Khoảng cách với card nội dung */
  text-transform: uppercase;
}

.header-main-title {
  font-size: 3.5em; /* Kích thước lớn */
  font-weight: 800; /* Rất đậm */
  color: #FFD700; /* Màu vàng rực rỡ */
  letter-spacing: 2px; /* Khoảng cách chữ rộng */
  margin-bottom: 10px;
  line-height: 1.2;
}

.header-sub-title {
  font-size: 2.8em; /* Kích thước hơi nhỏ hơn */
  font-weight: 700; /* Đậm */
  color: #fff;
  letter-spacing: 1.5px;
  line-height: 1.2;
}

/* Card nội dung chính */
.main-content-card {
  position: relative;
  z-index: 1;
  background-color: rgba(255, 255, 255, 0.1); /* Nền trong suốt nhẹ */
  backdrop-filter: blur(5px); /* Hiệu ứng làm mờ nền phía sau */
  border: 1px solid rgba(255, 255, 255, 0.2); /* Viền mờ */
  border-radius: 15px; /* Bo góc */
  padding: 50px; /* Padding lớn bên trong */
  max-width: 1000px; /* Chiều rộng tối đa */
  width: 90%; /* Dùng phần trăm để responsive */
  display: flex;
  flex-wrap: wrap; /* Cho phép xuống dòng trên màn hình nhỏ */
  align-items: center;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3); /* Bóng đổ sâu */
  margin-bottom: 60px; /* Khoảng cách với footer */
}

.text-block {
  flex: 2; /* Chiếm nhiều không gian hơn */
  padding-right: 40px; /* Khoảng cách với ảnh */
  text-align: left; /* Căn lề trái cho đoạn văn bản */
}

.text-block p {
  font-size: 1.15em; /* Kích thước chữ */
  line-height: 1.8; /* Chiều cao dòng */
  margin-bottom: 20px;
  font-weight: 400;
  color: #f0f0f0; /* Màu chữ hơi nhạt hơn nền trắng */
}

.image-block {
  flex: 1; /* Chiếm ít không gian hơn */
  display: flex;
  justify-content: center;
  align-items: center;
}

.container-terminal-img {
  max-width: 100%;
  height: auto;
  border-radius: 10px;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2); /* Bóng đổ cho ảnh */
}

/* Footer và các nút */
.page-footer {
  position: relative;
  z-index: 1;
  display: flex; /* Dùng flexbox để sắp xếp các nút */
  gap: 20px; /* Khoảng cách giữa các nút */
  flex-wrap: wrap; /* Cho phép các nút xuống dòng trên màn hình nhỏ */
  justify-content: center;
}

.back-button,
.view-booking-button {
  color: #fff;
  border: none;
  border-radius: 50px; /* Hình viên thuốc (pill shape) */
  padding: 18px 40px; /* Padding lớn hơn */
  cursor: pointer;
  font-size: 1.2em; /* Chữ to hơn */
  font-weight: 600; /* Đậm vừa phải */
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 15px; /* Khoảng cách giữa icon và chữ */
  transition: background-color 0.3s ease, transform 0.2s ease, box-shadow 0.3s ease;
  box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3); /* Bóng đổ cho nút */
  text-transform: uppercase;
  min-width: 200px; /* Đảm bảo nút có chiều rộng tối thiểu */
}

.back-button {
  background-color: #DC3545; /* Màu đỏ nổi bật cho nút Quay lại */
}

.back-button:hover {
  background-color: #c82333; /* Đậm hơn khi hover */
  transform: translateY(-3px); /* Hiệu ứng nhấc nhẹ */
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.4);
}

.view-booking-button {
  background-color: #28a745; /* Màu xanh lá cho nút Xem Booking */
}

.view-booking-button:hover {
  background-color: #218838; /* Đậm hơn khi hover */
  transform: translateY(-3px); /* Hiệu ứng nhấc nhẹ */
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.4);
}

.button-icon {
  width: 25px; /* Kích thước icon */
  height: 25px;
  fill: white; /* Đảm bảo icon SVG có màu trắng */
}

/* Responsive adjustments */
@media (max-width: 992px) {
  .main-content-card {
    flex-direction: column; /* Xếp chồng nội dung và ảnh trên màn hình nhỏ */
    padding: 40px;
  }
  .text-block {
    padding-right: 0;
    margin-bottom: 30px; /* Khoảng cách giữa text và ảnh khi xếp chồng */
  }
  .image-block {
    padding-left: 0;
  }
  .page-footer {
    flex-direction: column; /* Xếp chồng các nút trên màn hình nhỏ */
    gap: 15px;
  }
  .back-button, .view-booking-button {
    width: 100%; /* Các nút chiếm toàn bộ chiều rộng */
    max-width: 300px; /* Giới hạn chiều rộng tối đa */
  }
}

@media (max-width: 768px) {
  .confirmation-page {
    padding: 30px 15px;
  }
  .page-header {
    margin-bottom: 40px;
  }
  .header-main-title {
    font-size: 2.5em;
  }
  .header-sub-title {
    font-size: 2em;
  }
  .main-content-card {
    padding: 30px;
    width: 95%;
  }
  .text-block p {
    font-size: 1em;
  }
  .floating-aerial-view-img {
    width: 180px;
    top: 20px;
    right: 20px;
  }
  .back-button, .view-booking-button {
    padding: 15px 30px;
    font-size: 1em;
    gap: 10px;
  }
  .button-icon {
    width: 20px;
    height: 20px;
  }
}

@media (max-width: 576px) {
  .confirmation-page {
    padding: 20px 10px;
  }
  .page-header {
    margin-bottom: 30px;
  }
  .header-main-title {
    font-size: 2em;
    letter-spacing: 1px;
  }
  .header-sub-title {
    font-size: 1.6em;
    letter-spacing: 1px;
  }
  .main-content-card {
    padding: 25px;
  }
  .text-block p {
    font-size: 0.9em;
  }
  .floating-aerial-view-img {
    width: 120px;
    top: 10px;
    right: 10px;
    border-radius: 5px;
  }
  .back-button, .view-booking-button {
    padding: 12px 25px;
    font-size: 0.9em;
    gap: 8px;
  }
}
</style>