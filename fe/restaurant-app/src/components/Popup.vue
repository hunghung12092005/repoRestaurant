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
                <h3>Giờ Nhận/ Trả Phòng</h3>
                <ul>
                    <li>Giờ nhận phòng cố định là sau 12:00 chiều.</li>
                    <li>Giờ trả phòng cố định là 12:00 trưa.</li>
                    <li>Khách hàng có thể nhận phòng sớm nếu có phòng sẵn.</li>
                    <li>Phòng nghỉ giờ: Lưu trú 2 tiếng trở xuống.</li>
                    <li>Phòng ngày đêm: Lưu trú 24 tiếng hoặc phòng nghỉ giờ.</li>
                    <li>Trả phòng sau 03h00 sáng hoặc nhận phòng trước 06h00 sáng tính thêm giờ.</li>
                </ul>

                <h3>Quy Định Hủy Phòng</h3>
                <ul>
                    <li>Báo hủy từ 1 tháng trở lên không thu phí.</li>
                    <li>Báo hủy từ 10 ngày đến dưới 1 tháng bồi thường 20% tổng giá trị hợp đồng.</li>
                    <li>Báo hủy từ 3 đến dưới 10 ngày bồi thường 50% giá trị hợp đồng.</li>
                    <li>Báo hủy dưới 3 ngày bồi thường 100% tổng giá trị hợp đồng.</li>
                    <li>Khách đặt trong ngày và báo hủy trước check-in thu phí 50% tổng tiền đêm đầu tiên.</li>
                </ul>
            </section>
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

.modal-container-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 700;
    font-size: 1.125rem;
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

.rtf h3 {
    font-weight: 700;
    margin-top: 20px;
}

.rtf ul {
    margin-left: 20px;
}

.modal-container-footer {
    padding: 20px 32px;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    border-top: 1px solid #ddd;
    gap: 12px;
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