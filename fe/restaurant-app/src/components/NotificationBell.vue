<template>
  <div class="notification-bell-wrapper dropdown">
    <!-- Icon chuông và badge số thông báo chưa đọc -->
    <i class="bi bi-bell-fill mx-3" @click="toggleDropdown" data-bs-toggle="dropdown" aria-expanded="false" title="Thông báo">
      <!-- 
        VÙNG NÀY SẼ TỰ ĐỘNG CẬP NHẬT
        khi `unreadCount.value` thay đổi nhờ vào sức mạnh của Vue.
      -->
      <span v-if="unreadCount > 0" class="badge rounded-pill bg-danger notification-badge">{{ unreadCount > 9 ? '9+' : unreadCount }}</span>
    </i>

    <!-- Dropdown menu -->
    <transition name="fade">
      <ul v-if="isOpen" class="dropdown-menu dropdown-menu-end notification-dropdown show">
        <!-- Header -->
        <li class="dropdown-header d-flex justify-content-between align-items-center">
          <span class="fw-bold">Thông báo hôm nay</span>
          <button v-if="unreadCount > 0" @click.prevent="markAllAsRead" class="btn btn-link btn-sm p-0">
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
import { ref, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import axiosConfig from '../axiosConfig.js';
import echo from '../echo.js';

const router = useRouter();
const notifications = ref([]);
const unreadCount = ref(0);
const isOpen = ref(false);
const isLoading = ref(true);
const userInfo = ref(JSON.parse(localStorage.getItem('userInfo') || '{}'));

const getNotificationDetails = (type) => {
  switch (type) {
    case 'NEW_CONTACT':
      return { icon: 'bi bi-person-plus-fill', color: 'var(--bs-primary)' };
    case 'NEW_COMMENT':
      return { icon: 'bi bi-chat-left-text-fill', color: 'var(--bs-success)' };
    case 'NEW_BOOKING':
      return { icon: 'bi bi-calendar-check-fill', color: 'var(--bs-warning)' }; 
    default:
      return { icon: 'bi bi-bell-fill', color: 'var(--bs-secondary)' };
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

const fetchNotifications = async () => {
  try {
    isLoading.value = true;
    const response = await axiosConfig.get('/api/notifications');
    if (response.data.status) {
      notifications.value = response.data.data;
      unreadCount.value = notifications.value.filter(n => !n.read_at).length;
    }
  } catch (error) {
    console.error('Lỗi khi lấy thông báo:', error);
    notifications.value = [];
  } finally {
    isLoading.value = false;
  }
};

const markAllAsRead = async () => {
  try {
    await axiosConfig.post('/api/notifications/mark-all-as-read');
    notifications.value.forEach(n => {
      if (!n.read_at) n.read_at = new Date().toISOString();
    });
    unreadCount.value = 0;
  } catch (error) {
    console.error('Lỗi khi đánh dấu tất cả đã đọc:', error);
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

const toggleDropdown = () => {
  isOpen.value = !isOpen.value;
  if (isOpen.value) {
    fetchNotifications();
  }
};

const listenForNotifications = () => {
  if (userInfo.value && userInfo.value.id) {
    // Lắng nghe sự kiện trên kênh riêng của user
    echo.private(`App.Models.User.${userInfo.value.id}`)
      .listen('.new-notification', (data) => {
        // Cập nhật danh sách nếu dropdown đang mở
        if (isOpen.value) {
           notifications.value.unshift(data);
        }
        
        // === LOGIC CẬP NHẬT REAL-TIME ĐÂY RỒI! ===
        // Tăng số đếm thông báo chưa đọc lên 1.
        // Vì unreadCount là một ref(), Vue sẽ tự động cập nhật lại giao diện.
        // Logic này chạy ngay cả khi dropdown đang đóng.
        unreadCount.value++; 
      });
  }
};

const viewAllNotifications = () => {
    isOpen.value = false;
    router.push('/admin/notifications'); 
};

onMounted(() => {
  fetchNotifications();
  listenForNotifications(); // Bắt đầu lắng nghe khi component được tạo
});

onUnmounted(() => {
  if (userInfo.value && userInfo.value.id) {
    echo.leave(`App.Models.User.${userInfo.value.id}`); // Ngừng lắng nghe để tránh memory leak
  }
});
</script>

<style scoped>
/* Toàn bộ CSS của bạn giữ nguyên */
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