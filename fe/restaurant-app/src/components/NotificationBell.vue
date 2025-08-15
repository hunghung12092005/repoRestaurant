<template>
  <div class="notification-bell-wrapper dropdown">
    <!-- Icon chuông và badge số thông báo chưa đọc -->
    <i class="bi bi-bell-fill mx-3" @click="toggleDropdown" data-bs-toggle="dropdown" aria-expanded="false" title="Thông báo">
      <span v-if="unreadCount > 0" class="badge rounded-pill bg-danger notification-badge">{{ unreadCount > 9 ? '9+' : unreadCount }}</span>
    </i>

    <!-- Dropdown menu -->
    <transition name="fade">
      <ul v-if="isOpen" class="dropdown-menu dropdown-menu-end notification-dropdown show">
        <!-- Header -->
        <li class="dropdown-header d-flex justify-content-between align-items-center">
          <span class="fw-bold">Thông báo gần đây</span>
          <button v-if="hasUnreadInDropdown" @click.prevent="markAllAsRead" class="btn btn-link btn-sm p-0">
             Đánh dấu đã đọc
          </button>
        </li>
        <li><hr class="dropdown-divider my-0"></li>

        <!-- Vùng nội dung có thể cuộn -->
        <div class="notification-list-container">
          <!-- Trạng thái Loading -->
          <div v-if="isLoading" class="d-flex justify-content-center align-items-center p-5">
            <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>
          </div>

          <!-- Trạng thái Rỗng -->
          <div v-else-if="notifications.length === 0" class="empty-state">
            <i class="bi bi-check2-circle empty-icon"></i>
            <p class="text-muted mb-0">Tuyệt vời!</p>
            <p class="text-muted">Không có thông báo mới.</p>
          </div>

          <!-- Danh sách thông báo -->
          <ul v-else class="list-unstyled mb-0">
            <li v-for="notification in notifications" :key="notification.id">
              <a class="dropdown-item d-flex align-items-start" :class="{ 'unread': !notification.read_at }" @click.prevent="handleNotificationClick(notification)">
                <div class="icon-wrapper flex-shrink-0" :style="{ backgroundColor: getNotificationDetails(notification.type).color }">
                  <i :class="getNotificationDetails(notification.type).icon"></i>
                </div>
                <div class="notification-content flex-grow-1">
                  <p class="mb-1 message-text" :class="{ 'fw-bold': !notification.read_at }">{{ notification.data.message }}</p>
                  <small class="text-muted">{{ timeAgo(notification.created_at) }}</small>
                </div>
                <span v-if="!notification.read_at" class="unread-dot" title="Chưa đọc"></span>
              </a>
            </li>
          </ul>
        </div>

        <!-- Footer -->
        <li><hr class="dropdown-divider my-0"></li>
        <li class="dropdown-footer text-center">
            <a href="#" @click.prevent="viewAllNotifications" class="view-all-link">Xem tất cả thông báo</a>
        </li>
      </ul>
    </transition>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import axiosConfig from '../axiosConfig.js';
// Không cần import echo.js nữa

// --- STATE MANAGEMENT ---
const router = useRouter();
const notifications = ref([]);
const unreadCount = ref(0);
const isOpen = ref(false);
const isLoading = ref(true);
let pollingInterval = null; // Biến để lưu trữ tiến trình lặp lại

const hasUnreadInDropdown = computed(() => notifications.value.some(n => !n.read_at));

// --- HELPER FUNCTIONS ---
const getNotificationDetails = (type) => {
  switch (type) {
    case 'NEW_CONTACT': return { icon: 'bi bi-person-plus-fill', color: 'var(--bs-primary)' };
    case 'NEW_COMMENT': return { icon: 'bi bi-chat-left-text-fill', color: 'var(--bs-success)' };
    case 'NEW_BOOKING': return { icon: 'bi bi-calendar-check-fill', color: 'var(--bs-warning)' }; 
    default: return { icon: 'bi bi-bell-fill', color: 'var(--bs-secondary)' };
  }
};

const timeAgo = (dateString) => {
  if (!dateString) return '';
  const seconds = Math.floor((new Date() - new Date(dateString)) / 1000);
  if (seconds < 5) return "Vừa xong";
  if (seconds < 60) return `${seconds} giây trước`;
  const intervals = { 'năm': 31536000, 'tháng': 2592000, 'ngày': 86400, 'giờ': 3600, 'phút': 60 };
  for (let key in intervals) {
    let interval = seconds / intervals[key];
    if (interval > 1) return Math.floor(interval) + ` ${key} trước`;
  }
  return "Vừa xong";
};

// --- DATA HANDLING (POLLING) ---

/**
 * Hàm này sẽ được gọi định kỳ để "hỏi" server số lượng thông báo chưa đọc.
 * Đây là trái tim của việc cập nhật tự động.
 */
const updateUnreadCount = async () => {
  try {
    const response = await axiosConfig.get('/api/notifications/unread-count');
    if (response.data.status) {
      unreadCount.value = response.data.unread_count;
    }
  } catch (error) {
    // Không log lỗi ra console để tránh làm phiền, vì nó sẽ chạy liên tục.
    // Nếu có lỗi, số thông báo sẽ không được cập nhật cho đến lần hỏi thành công tiếp theo.
  }
};

/**
 * Hàm này chỉ được gọi KHI MỞ dropdown để lấy danh sách chi tiết.
 */
const fetchNotificationsList = async () => {
  try {
    isLoading.value = true;
    const response = await axiosConfig.get('/api/notifications'); 
    if (response.data.status) {
      notifications.value = response.data.data;
    }
  } catch (error) {
    console.error('Lỗi khi lấy danh sách thông báo:', error);
    notifications.value = [];
  } finally {
    isLoading.value = false;
  }
};

// --- USER INTERACTION HANDLERS ---
const toggleDropdown = () => {
  isOpen.value = !isOpen.value;
  if (isOpen.value) {
    fetchNotificationsList();
    // Khi mở dropdown, cập nhật lại số lượng ngay lập tức để đồng bộ
    updateUnreadCount();
  }
};

const handleNotificationClick = async (notification) => {
  const wasUnread = !notification.read_at;
  if (wasUnread) {
    notification.read_at = new Date().toISOString();
    unreadCount.value = Math.max(0, unreadCount.value - 1);
  }
  isOpen.value = false;
  if (notification.data.link) {
    router.push(notification.data.link);
  }
  if (wasUnread) {
    try {
      await axiosConfig.post(`/api/notifications/${notification.id}/mark-as-read`);
    } catch (error) {
      console.error('Lỗi khi đánh dấu đã đọc trên server:', error);
    }
  }
};

const markAllAsRead = async () => {
  try {
    await axiosConfig.post('/api/notifications/mark-all-as-read');
    notifications.value.forEach(n => {
      if (!n.read_at) n.read_at = new Date().toISOString();
    });
    // Sau khi đánh dấu tất cả, cập nhật lại số đếm ngay lập tức
    updateUnreadCount();
  } catch (error) {
    console.error('Lỗi khi đánh dấu tất cả đã đọc:', error);
  }
};

const viewAllNotifications = () => {
    isOpen.value = false;
    router.push('/admin/notifications'); 
};

// --- LIFECYCLE HOOKS ---
onMounted(() => {
  // 1. Cập nhật số lượng ngay lập tức khi tải trang
  updateUnreadCount(); 

  // 2. Bắt đầu "hỏi vòng" server mỗi 5 giây (bạn có thể thay đổi thời gian này)
  pollingInterval = setInterval(updateUnreadCount, 5000); // 5000ms = 5 giây
});

onUnmounted(() => {
  // 3. Dọn dẹp: Dừng việc "hỏi vòng" khi người dùng rời khỏi component
  if (pollingInterval) {
    clearInterval(pollingInterval);
  }
});
</script>

<style scoped>
/* Toàn bộ CSS của bạn giữ nguyên, không cần thay đổi */
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease, transform 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: translateY(-10px); }
.notification-bell-wrapper { position: relative; }
.bi-bell-fill { position: relative; cursor: pointer; font-size: 1.35rem; color: #495057; }
.notification-badge { position: absolute; top: -6px; right: -10px; padding: 3px 6px; font-size: 0.6em; font-weight: bold; border: 2px solid white; }
.notification-dropdown { width: 420px; max-width: 90vw; padding: 0; border-radius: 0.75rem; box-shadow: 0 8px 24px rgba(0,0,0,0.12); border: none; margin-top: 15px !important; display: flex; flex-direction: column; right: 15px !important; left: auto !important; }
.notification-list-container { max-height: 450px; overflow-y: auto; }
.notification-list-container::-webkit-scrollbar { width: 5px; }
.notification-list-container::-webkit-scrollbar-track { background: transparent; }
.notification-list-container::-webkit-scrollbar-thumb { background: #ccc; border-radius: 5px; }
.dropdown-header { padding: 0.75rem 1.25rem; }
.dropdown-header .btn-link { font-size: 0.8rem; text-decoration: none; font-weight: 500; }
.dropdown-item { padding: 1rem 1.25rem; gap: 1rem; border-bottom: 1px solid #f0f0f0; white-space: normal; transition: background-color 0.2s ease; }
.list-unstyled li:last-child .dropdown-item { border-bottom: none; }
.dropdown-item:hover { background-color: #f8f9fa; }
.dropdown-item.unread:hover { background-color: #e9f5ff; }
.icon-wrapper { width: 40px; height: 40px; color: white; font-size: 1.1rem; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
.notification-content { min-width: 0; }
.message-text { line-height: 1.4; margin: 0; color: #555; transition: color 0.2s; word-wrap: break-word; }
.dropdown-item.unread .message-text { color: #212529; }
.unread-dot { width: 10px; height: 10px; background-color: var(--bs-primary); border-radius: 50%; align-self: center; flex-shrink: 0; }
.empty-state { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 3rem 1rem; text-align: center; }
.empty-icon { font-size: 3.5rem; color: #ced4da; margin-bottom: 1rem; }
.dropdown-footer { padding: 0.75rem; background-color: #f8f9fa; }
.view-all-link { font-weight: 500; color: var(--bs-primary); text-decoration: none; }
.view-all-link:hover { text-decoration: underline; }
</style>