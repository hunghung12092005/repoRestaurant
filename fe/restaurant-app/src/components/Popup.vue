<template>
    <div class="modal">
        <article class="modal-container">
            <header class="modal-container-header">
                <div class="title-wrapper">
                    <svg class="title-icon" aria-hidden="true" height="28" width="28" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M0 0h24v24H0z" fill="none"></path>
                        <path
                            d="M14 9V4H5v16h6.056c.328.417.724.785 1.18 1.085l1.39.915H3.993A.993.993 0 0 1 3 21.008V2.992C3 2.455 3.449 2 4.002 2h10.995L21 8v1h-7zm-2 2h9v5.949c0 .99-.501 1.916-1.336 2.465L16.5 21.498l-3.164-2.084A2.953 2.953 0 0 1 12 16.95V11zm2 5.949c0 .316.162.614.436.795l2.064 1.36 2.064-1.36a.954.954 0 0 0 .436-.795V13h-5v3.949z"
                            fill="currentColor"></path>
                    </svg>
                    <h1 class="modal-container-title">Nguyên Tắc Chung</h1>
                </div>
                <button class="close-button" @click="closePopup" aria-label="Đóng popup">
                    <svg height="24" width="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M0 0h24v24H0z" fill="none"></path>
                        <path
                            d="M12 10.586l4.95-4.95 1.414 1.414-4.95 4.95 4.95 4.95-1.414 1.414-4.95-4.95-4.95 4.95-1.414-1.414 4.95-4.95-4.95-4.95L7.05 5.636z"
                            fill="currentColor"></path>
                    </svg>
                </button>
            </header>

            <section class="modal-container-body">
                <div class="policy-section">
                    <h2 class="section-title">Quy Tắc Đặt Phòng</h2>
                    <ul class="policy-list">
                        <li class="policy-item">
                            <span class="bullet-icon">•</span>
                            <span class="item-text">Ở bao nhiêu HXH Hotel tính tiền bấy nhiêu.</span>
                        </li>
                        <li class="policy-item">
                            <span class="bullet-icon">•</span>
                            <span class="item-text">Phòng nghỉ giờ: Lưu trú 6 tiếng trở xuống tính tiền theo giờ.</span>
                        </li>
                        <li class="policy-item">
                            <span class="bullet-icon">•</span>
                            <span class="item-text">Phòng ngày đêm: Lưu trú trên 6 tiếng tính tiền 1 ngày đêm.</span>
                        </li>
                        <li class="policy-item">
                            <span class="bullet-icon">•</span>
                            <span class="item-text">Ngày check-in và check-out phải lớn hơn 1 ngày.</span>
                        </li>
                        <li class="policy-item">
                            <span class="bullet-icon">•</span>
                            <span class="item-text">Đối với trường hợp khách đã thanh toán online nhưng nếu check-out sớm hơn thì HXH Hotel vẫn sẽ hoàn lại số tiền của khách còn dư theo quy định của khách sạn.</span>
                        </li>
                    </ul>
                </div>

                <div class="policy-section">
                    <h2 class="section-title">Giờ Nhận/Trả Phòng</h2>
                    <ul class="policy-list">
                        <li class="policy-item">
                            <span class="bullet-icon">•</span>
                            <span class="item-text">Giờ nhận phòng cố định là sau 14:00 chiều.</span>
                        </li>
                        <li class="policy-item">
                            <span class="bullet-icon">•</span>
                            <span class="item-text">Giờ trả phòng cố định là 12:00 trưa.</span>
                        </li>
                        <li class="policy-item">
                            <span class="bullet-icon">•</span>
                            <span class="item-text">Khách hàng có thể nhận phòng sớm nếu có phòng sẵn và tính tiền theo giá tiền theo giờ được niêm yết cho tới giờ check-in.</span>
                        </li>
                        <li class="policy-item">
                            <span class="bullet-icon">•</span>
                            <span class="item-text">Trả phòng sau 14h00 chiều hoặc nhận phòng trước 12h00 sáng tính thêm giờ (sẽ có nhân viên gọi điện xác nhận trước cho khách hàng).</span>
                        </li>
                    </ul>
                </div>

                <div class="policy-section">
                    <h2 class="section-title">Quy Định Hủy Phòng/Dịch Vụ</h2>
                    <ul class="policy-list">
                        <li class="policy-item">
                            <span class="bullet-icon">•</span>
                            <span class="item-text">Báo hủy từ 7 ngày trở lên không thu phí.</span>
                        </li>
                        <li class="policy-item">
                            <span class="bullet-icon">•</span>
                            <span class="item-text">Báo hủy từ 1 ngày đến 3 ngày  bồi thường 50% tổng giá trị hợp đồng.</span>
                        </li>
                        <li class="policy-item">
                            <span class="bullet-icon">•</span>
                            <span class="item-text">Báo hủy từ 3 đến dưới 7 ngày bồi thường 30% giá trị hợp đồng.</span>
                        </li>
                       
                       
                    </ul>
                </div>
            </section>

            <footer class="modal-container-footer">
                <button class="confirm-button" @click="closePopup">Đã hiểu</button>
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
/* Font chữ */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

/* Tổng thể modal */
.modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 10010;
    backdrop-filter: blur(4px);
    font-family: 'Inter', sans-serif;
}

.modal-container {
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    background-color: #ffffff;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    animation: fadeIn 0.3s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Header */
.modal-container-header {
    padding: 20px 24px;
    background-color: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.title-wrapper {
    display: flex;
    align-items: center;
    gap: 12px;
}

.title-icon {
    color: #4a6bdf;
    flex-shrink: 0;
}

.modal-container-title {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
    color: #2d3748;
}

.close-button {
    background: none;
    border: none;
    padding: 8px;
    border-radius: 50%;
    cursor: pointer;
    color: #64748b;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.close-button:hover {
    background-color: #f1f5f9;
    color: #475569;
}

.close-button svg {
    width: 20px;
    height: 20px;
}

/* Body content */
.modal-container-body {
    padding: 24px;
    overflow-y: auto;
    flex-grow: 1;
}

.policy-section {
    margin-bottom: 28px;
}

.policy-section:last-child {
    margin-bottom: 0;
}

.section-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #2d3748;
    margin: 0 0 16px 0;
    padding-bottom: 8px;
    border-bottom: 1px solid #e2e8f0;
}

.policy-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.policy-item {
    display: flex;
    align-items: flex-start;
    padding: 12px 0;
    line-height: 1.5;
    border-bottom: 1px solid #f1f5f9;
}

.policy-item:last-child {
    border-bottom: none;
}

.bullet-icon {
    color: #4a6bdf;
    font-weight: bold;
    margin-right: 12px;
    flex-shrink: 0;
    margin-top: 2px;
}

.item-text {
    color: #475569;
    font-size: 0.9375rem;
}

/* Footer */
.modal-container-footer {
    padding: 16px 24px;
    background-color: #f8f9fa;
    border-top: 1px solid #e9ecef;
    display: flex;
    justify-content: flex-end;
}

.confirm-button {
    background-color: #4a6bdf;
    color: white;
    border: none;
    padding: 10px 24px;
    border-radius: 8px;
    font-weight: 500;
    font-size: 0.9375rem;
    cursor: pointer;
    transition: all 0.2s ease;
}

.confirm-button:hover {
    background-color: #3a5ad6;
}

/* Responsive */
@media (max-width: 480px) {
    .modal-container {
        width: 95%;
    }
    
    .modal-container-header {
        padding: 16px 20px;
    }
    
    .modal-container-body {
        padding: 20px;
    }
    
    .modal-container-footer {
        padding: 14px 20px;
    }
    
    .policy-item {
        padding: 10px 0;
    }
}
</style>