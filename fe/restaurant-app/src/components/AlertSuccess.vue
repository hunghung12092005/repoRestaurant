<template>
    <!-- From Uiverse.io by kennyotsu -->
    <div v-if="isVisible" class="success">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg aria-hidden="true" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"
                    class="succes-svg">
                    <path clip-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        fill-rule="evenodd"></path>
                </svg>
            </div>
            <div class="success-prompt-wrap">
                <p class="success-prompt-heading">{{ message }}!</p>
                <div class="success-prompt-prompt">
                    <p>
                        {{ message2 }}!
                        😂
                    </p>
                </div>
                <div class="success-button-container">
                    <button class="success-button-main" type="button">
                        {{ message3 }}
                    </button>
                    <button class="success-button-secondary" type="button">
                        {{ message4 }}
                    </button>
                </div>
            </div>
        </div>
    </div>

</template>
<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { defineProps } from 'vue';

const props = defineProps({
    message: {
        type: String,
        default: 'Thông báo mặc định'
    },
    message2: {
        type: String,
        default: 'Thông báo mặc định2'
    },
    message3: {
        type: String,
        default: 'Thông báo mặc định3'
    },
    message4: {
        type: String,
        default: 'Thông báo mặc định4'
    }
});

const isVisible = ref(false);
let timeoutId = null;

const showNotification = () => {
    isVisible.value = true; // Hiện thông báo
    clearTimeout(timeoutId); // Xóa timeout cũ nếu có
    timeoutId = setTimeout(() => {
        isVisible.value = false; // Ẩn thông báo sau 2 giây
    }, 2000);
};

onMounted(() => {
    showNotification(); // Gọi khi component được mount
});

onBeforeUnmount(() => {
    clearTimeout(timeoutId); // Dọn dẹp timeout khi component bị hủy
});
</script>

<style scoped>
/* From Uiverse.io by kennyotsu */


.flex {
    display: flex;
}

.flex-shrink-0 {
    flex-shrink: 0;
}

.success {
    position: fixed;
    /* Hoặc absolute nếu trong một container cụ thể */
    padding: 1rem;
    background-color: #ECFDF5;
    /* Màu nền */
    border-radius: 0.375rem;
    opacity: 0;
    /* Bắt đầu với độ mờ 0 */
    transform: translateX(100%);
    /* Bắt đầu từ bên phải */
    animation: slide-in 4s forwards;
    /* Chạy animation trong 4 giây */
    z-index: 1000;
    /* Đảm bảo nó nằm trên các phần tử khác */
}

@keyframes slide-in {
    0% {
        opacity: 0;
        transform: translateX(100%);
    }

    50% {
        opacity: 1;
        /* Hiện ra hoàn toàn */
        transform: translateX(0);
    }

    100% {
        opacity: 0;
        /* Mất đi */
        transform: translateX(0);
    }
}

.succes-svg {
    color: rgb(74 222 128);
    width: 1rem;
    height: 1rem;
}

.success-prompt-wrap {
    margin-left: 0.75rem;
}

.success-prompt-heading {
    font-weight: bold;
    color: rgb(22 101 52);
}

.success-prompt-prompt {
    margin-top: 0.5rem;
    color: rgb(21 128 61);
}

.success-button-container {
    display: flex;
    margin-top: 0.875rem;
    margin-bottom: -0.375rem;
    margin-left: -0.5rem;
    margin-right: -0.5rem;
}

.success-button-main {
    padding-top: 0.375rem;
    padding-bottom: 0.375rem;
    padding-left: 0.5rem;
    padding-right: 0.5rem;
    background-color: #ECFDF5;
    color: rgb(22 101 52);
    font-size: 0.875rem;
    line-height: 1.25rem;
    font-weight: bold;
    border-radius: 0.375rem;
    border: none
}

.success-button-main:hover {
    background-color: #D1FAE5;
}

.success-button-secondary {
    padding-top: 0.375rem;
    padding-bottom: 0.375rem;
    padding-left: 0.5rem;
    padding-right: 0.5rem;
    margin-left: 0.75rem;
    background-color: #ECFDF5;
    color: #065F46;
    font-size: 0.875rem;
    line-height: 1.25rem;
    border-radius: 0.375rem;
    border: none;
}

.success-button-secondary:hover {
    background-color: #D1FAE5;
    color: rgb(22 101 52);
}
</style>