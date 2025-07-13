<template>
    <div class="modal">
        <article class="modal-container">

            <header class="modal-container-header">
                <span class="modal-container-title">
                    <svg aria-hidden="true" height="28" width="28" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path d="M0 0h24v24H0z" fill="none"></path>
                        <path
                            d="M14 9V4H5v16h6.056c.328.417.724.785 1.18 1.085l1.39.915H3.993A.993.993 0 0 1 3 21.008V2.992C3 2.455 3.449 2 4.002 2h10.995L21 8v1h-7zm-2 2h9v5.949c0 .99-.501 1.916-1.336 2.465L16.5 21.498l-3.164-2.084A2.953 2.953 0 0 1 12 16.95V11zm2 5.949c0 .316.162.614.436.795l2.064 1.36 2.064-1.36a.954.954 0 0 0 .436-.795V13h-5v3.949z"
                            fill="currentColor"></path>
                    </svg>
                    Nguyên Tắc Chung
                </span>
                <button class="icon-button" @click="closePopup">
                    <svg height="28" width="28" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M0 0h24v24H0z" fill="none"></path>
                        <path
                            d="M12 10.586l4.95-4.95 1.414 1.414-4.95 4.95 4.95 4.95-1.414 1.414-4.95-4.95-4.95 4.95-1.414-1.414 4.95-4.95-4.95-4.95L7.05 5.636z"
                            fill="currentColor"></path>
                    </svg>
                </button>
            </header>
            <section class="modal-container-body rtf">
                <h3>Quy Tắc Đặt Phòng</h3>
                <ul>
                    <li>Ở bao nhiêu HXH Hotel tính tiền bấy nhiêu.</li>
                    <li>Phòng nghỉ giờ: Lưu trú 6 tiếng trở xuống tính tiền theo giờ.</li>
                    <li>Phòng ngày đêm: Lưu trú trên 6 tiếng tính tiền 1 ngày đêm.</li>
                    <li>Ngày check-in và check-out phải lớn hơn 1 ngày.</li>
                    <li>Đối với trường hợp khách đã thanh toán online nhưng nếu check-out sớm hơn thì HXH Hotel vẫn sẽ hoàn lại số tiền của khách còn dư theo quy định của khách sạn.</li>
                </ul>
                <h3>Giờ Nhận/ Trả Phòng</h3>
                <ul>
                    <li>Giờ nhận phòng cố định là sau 14:00 chiều.</li>
                    <li>Giờ trả phòng cố định là 12:00 trưa.</li>
                    <li>Khách hàng có thể nhận phòng sớm nếu có phòng sẵn và tính tiền theo giá tiền theo giờ được niêm yết cho tới giờ check-in.</li>
                    <li>Trả phòng sau 14h00 chiều hoặc nhận phòng trước 12h00 sáng tính thêm giờ (sẽ có nhân viên gọi điện xác nhận trước cho khách hàng).</li>
                </ul>

                <h3>Quy Định Hủy Phòng / Dịch Vụ</h3>
                <ul>
                    <li>Báo hủy từ 1 tháng trở lên không thu phí.</li>
                    <li>Báo hủy từ 10 ngày đến dưới 1 tháng bồi thường 20% tổng giá trị hợp đồng.</li>
                    <li>Báo hủy từ 3 đến dưới 10 ngày bồi thường 50% giá trị hợp đồng.</li>
                    <li>Báo hủy dưới 3 ngày bồi thường 100% tổng giá trị hợp đồng.</li>
                    <li>Khách đặt trong ngày và báo hủy trước check-in thu phí 50% tổng tiền đêm đầu tiên.</li>
                </ul>
            </section>
            <div class="flower flower-1"></div>
            <div class="flower flower-2"></div>
            <div class="flower flower-3"></div>
            <div class="flower flower-4"></div>
            <div class="flower flower-5"></div>
            <div class="flower flower-6"></div>
            <div class="flower flower-7"></div>
            <div class="flower flower-8"></div>
            <div class="flower flower-9"></div>
            <div class="flower flower-10"></div>
            <footer class="modal-container-footer">
                <button class="button is-ghost" @click="closePopup">Đóng</button>
            </footer>
        </article>
    </div>
</template>
<script setup>
import { ref } from 'vue';

const props = defineProps(['isVisible']);
const emit = defineEmits(['close']);

function closePopup() {
    emit('close');
}
</script>
<style scoped>
/* Tổng thể Modal */
.modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.65); /* Nền tối hơn một chút để popup nổi bật */
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
    backdrop-filter: blur(3px); /* Hiệu ứng làm mờ nền */
}

.modal-container {
    max-height: 80vh; /* Tăng chiều cao tối đa */
    max-width: 650px; /* Tăng chiều rộng tối đa */
    background-color: #ffffff;
    border-radius: 16px; /* Bo tròn mềm mại hơn */
    overflow: hidden;
    display: flex;
    flex-direction: column;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25); /* Đổ bóng sâu và rõ hơn */
    animation: fadeInScale 0.4s ease-out; /* Hiệu ứng xuất hiện mượt mà hơn */
    position: relative; /* Quan trọng cho vị trí hoa */
}

@keyframes fadeInScale {
    from { opacity: 0; transform: translateY(-30px) scale(0.95); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}

/* Header */
.modal-container-header {
    padding: 25px 35px; /* Tăng padding */
    border-bottom: 1px solid #e0e0e0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background-color: #fcfdfe; /* Nền header rất sáng */
    position: relative; /* Cho phép hoa nằm trên header */
    overflow: hidden; /* Đảm bảo hoa không tràn ra ngoài header */
}

.modal-container-title {
    display: flex;
    align-items: center;
    gap: 15px; /* Tăng khoảng cách giữa icon và chữ */
    font-weight: 700;
    font-size: 2rem; /* Kích thước chữ tiêu đề lớn hơn */
    color: #2c3e50; /* Màu than chì đậm */
    text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.1); /* Đổ bóng chữ rõ hơn */
    font-family: 'Playfair Display', serif; /* Font chữ sang trọng (cần import từ Google Fonts) */
}

.modal-container-title svg {
    color: #4A90E2; /* Màu icon nổi bật */
}

.icon-button {
    padding: 0;
    border: none; /* Bỏ viền */
    background-color: transparent;
    width: 50px; /* Kích thước nút lớn hơn */
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    border-radius: 50%;
    transition: background-color 0.2s ease, transform 0.1s ease;
    color: #7f8c8d;
}

.icon-button:hover {
    background-color: #f0f0f0;
    transform: scale(1.1); /* Phóng to rõ hơn khi hover */
}

/* Body nội dung */
.modal-container-body {
    padding: 30px 45px; /* Tăng padding */
    overflow-y: auto;
    color: #4a4a4a;
    font-family: 'Roboto', sans-serif; /* Font chữ dễ đọc (cần import từ Google Fonts) */
    line-height: 1.7; /* Khoảng cách dòng thoáng hơn */
}

.modal-container-body h3 {
    font-weight: 700;
    margin-top: 35px; /* Tăng khoảng cách trên */
    margin-bottom: 20px; /* Tăng khoảng cách dưới */
    color: #34495e;
    text-align: left;
    font-size: 1.5rem; /* Kích thước tiêu đề con lớn hơn */
    border-bottom: 2px solid #e0e0e0;
    padding-bottom: 10px; /* Khoảng cách giữa chữ và gạch chân */
    font-family: 'Playfair Display', serif;
}

.modal-container-body ul {
    margin-left: 0;
    list-style: none;
    padding: 0;
}

.modal-container-body ul li {
    padding: 15px 0; /* Tăng đệm */
    border-bottom: 1px dashed #e5e5e5; /* Đường viền nét đứt tinh tế */
    transition: background-color 0.2s ease, transform 0.1s ease;
    color: #555;
    position: relative; /* Cho phép thêm icon bullet tùy chỉnh */
    padding-left: 25px; /* Khoảng trống cho bullet */
}

.modal-container-body ul li:last-child {
    border-bottom: none;
}

.modal-container-body ul li:hover {
    background-color: #fcfcfc;
    transform: translateX(8px); /* Di chuyển rõ hơn */
}

/* Bullet tùy chỉnh cho danh sách */
.modal-container-body ul li::before {
    content: '•'; /* Ký tự bullet */
    color: #4A90E2; /* Màu bullet */
    font-size: 1.2em;
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    font-weight: bold;
}

/* Footer */
.modal-container-footer {
    padding: 20px 35px;
    border-top: 1px solid #e0e0e0;
    display: flex;
    justify-content: flex-end;
    background-color: #fcfdfe;
}

.button {
    padding: 16px 28px; /* Tăng padding nút */
    border-radius: 10px; /* Bo tròn nhiều hơn */
    background-color: #4A90E2; /* Màu xanh chủ đạo */
    border: none;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.1s ease, box-shadow 0.3s ease;
    font-size: 1.1rem;
    color: #ffffff; /* Chữ trắng */
    box-shadow: 0 4px 10px rgba(74, 144, 226, 0.2); /* Đổ bóng nhẹ cho nút */
}

.button.is-ghost {
    background-color: transparent;
    border: 1px solid #ccc;
    color: #555;
    box-shadow: none;
}

.button.is-ghost:hover {
    background-color: #e9ecef;
    color: #333;
    border-color: #b0b0b0;
    transform: translateY(-2px); /* Nâng nhẹ nút khi hover */
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
}

/* Import Google Fonts (Thêm vào file CSS chính hoặc thẻ <style> global nếu muốn) */
/*
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Roboto:wght@400;500;700&display=swap');
*/
</style>>