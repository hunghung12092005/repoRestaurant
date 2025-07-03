<template>
    <div class="modal">
        <article class="modal-container">

            <header class="modal-container-header">
                <span class="modal-container-title">
                    <svg aria-hidden="true" height="24" width="24" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path d="M0 0h24v24H0z" fill="none"></path>
                        <path
                            d="M14 9V4H5v16h6.056c.328.417.724.785 1.18 1.085l1.39.915H3.993A.993.993 0 0 1 3 21.008V2.992C3 2.455 3.449 2 4.002 2h10.995L21 8v1h-7zm-2 2h9v5.949c0 .99-.501 1.916-1.336 2.465L16.5 21.498l-3.164-2.084A2.953 2.953 0 0 1 12 16.95V11zm2 5.949c0 .316.162.614.436.795l2.064 1.36 2.064-1.36a.954.954 0 0 0 .436-.795V13h-5v3.949z"
                            fill="currentColor"></path>
                    </svg>
                    Nguyên Tắc Chung
                </span>
                <button class="icon-button" @click="closePopup">
                    <svg height="24" width="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
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
                    <li>Ở bao nhiêu HXH Hotel tính tiền bấy nhiêu</li>
                    <li>Phòng nghỉ giờ: Lưu trú 6 tiếng trở xuống tính tiền theo giờ.</li>
                    <li>Phòng ngày đêm: Lưu trú trên 6 tiếng tính tiền 1 ngày đêm.</li>
                    <li>Ngày check-in và check_out phải lớn hơn 1 ngày</li>
                    <li>Đối với trường hợp khách đã thanh toán online nhưng nếu check-out sớm hơn thì HXH Hotel vẫn sẽ hoàn lại số tiền của khách còn dư theo quy định của khách sạn</li>
                </ul>
                <h3>Giờ Nhận/ Trả Phòng</h3>
                <ul>
                    <li>Giờ nhận phòng cố định là sau 14:00 chiều.</li>
                    <li>Giờ trả phòng cố định là 12:00 trưa.</li>
                    <li>Khách hàng có thể nhận phòng sớm nếu có phòng sẵn và tính tiền theo giá tiền theo giờ được niêm yết cho tới giờ check-in.</li>
                  
                    <li>Trả phòng sau 14h00 chiều hoặc nhận phòng trước 12h00 sáng tính thêm giờ(sẽ có nhân viên gọi điện xác nhận trước cho khách hàng).</li>
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
            <div class="flower"></div>
            <div class="flower"></div>
            <div class="flower"></div>
            <div class="flower"></div>
            <div class="flower"></div>
            <div class="flower"></div>
            <div class="flower"></div>
            <div class="flower"></div>
            <div class="flower"></div>
            <div class="flower"></div>
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
.modal{
    z-index: 1100120;
}
@keyframes flower-animation {
    0% {
        transform: translate(0, 0) scale(1);
        opacity: 1;
    }

    50% {
        transform: translate(-20px, -30px) scale(1.1);
        opacity: 0.6;
    }

    100% {
        transform: translate(0, 0) scale(1);
        opacity: 1;
    }
}

.flower {
    position: absolute;
    width: 24px;
    height: 24px;
    background-image: url('../../public/hoa-removebg-preview.png');
    /* Thay thế bằng hình ảnh hoa */
    background-size: cover;
    opacity: 0;
    animation: flower-animation 3s ease-in-out infinite;
}

.flower:nth-child(1) {
    left: 10%;
    top: 20%;
    animation-delay: 0s;
}

.flower:nth-child(2) {
    left: 30%;
    top: 30%;
    animation-delay: 0.5s;
}

.flower:nth-child(3) {
    left: 50%;
    top: 10%;
    animation-delay: 1s;
}

.flower:nth-child(4) {
    left: 70%;
    top: 25%;
    animation-delay: 1.5s;
}

.flower:nth-child(5) {
    left: 90%;
    top: 15%;
    animation-delay: 2s;
}

.flower:nth-child(6) {
    left: 20%;
    top: 40%;
    animation-delay: 0.3s;
}

.flower:nth-child(7) {
    left: 40%;
    top: 50%;
    animation-delay: 1.2s;
}

.flower:nth-child(8) {
    left: 60%;
    top: 5%;
    animation-delay: 1.8s;
}

.flower:nth-child(9) {
    left: 80%;
    top: 35%;
    animation-delay: 2.5s;
}

.flower:nth-child(10) {
    left: 15%;
    top: 10%;
    animation-delay: 2.9s;
}
.modal-container-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 700;
    font-size: 1.5rem;
    /* Tăng kích thước chữ */
    color: #4A90E2;
    /* Màu chữ đẹp hơn */
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
    /* Hiệu ứng đổ bóng cho chữ */
}

.modal-container-body h3 {
    font-weight: 700;
    margin-top: 20px;
    color: #333;
    /* Màu sắc chữ */
    text-align: center;
    /* Căn giữa tiêu đề */
}

.modal-container-body ul {
    margin-left: 0;
    /* Bỏ lề trái */
    list-style: none;
    /* Bỏ kiểu danh sách */
    padding: 0;
    /* Bỏ đệm */
}

.modal-container-body ul li {
    padding: 8px 16px;
    /* Thêm đệm cho các mục */
    border-bottom: 1px solid #ddd;
    /* Đường viền dưới */
    transition: background-color 0.2s;
    /* Hiệu ứng chuyển đổi */
}

.modal-container-body ul li:hover {
    background-color: #f0f0f0;
    /* Hiệu ứng hover cho mục */
}

.modal {
    margin-top: 20px;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.7);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 100;
}

.modal-container {
    max-height: 600px;
    max-width: 500px;
    margin-left: auto;
    margin-right: auto;
    background-color: #fff;
    border-radius: 16px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.25);
}

.modal-container-header {
    padding: 16px 32px;
    border-bottom: 1px solid #ddd;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.icon-button {
    padding: 0;
    border: 0;
    background-color: transparent;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    border-radius: 8px;
    transition: 0.15s ease;
}

.icon-button:hover {
    background-color: #dfdad7;
}

.modal-container-body {
    padding: 24px 32px;
    overflow-y: auto;
}

.button {
    padding: 12px 20px;
    border-radius: 8px;
    background-color: transparent;
    border: 0;
    font-weight: 600;
    cursor: pointer;
    transition: 0.15s ease;
}

.button.is-ghost:hover {
    background-color: #dfdad7;
}
</style>