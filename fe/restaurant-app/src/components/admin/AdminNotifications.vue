<template>
  <div class="notifications-page">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
      <h2 class="mb-0">Tất cả thông báo</h2>
      <div class="d-flex align-items-center">
        <!-- Bộ lọc -->
        <div class="btn-group me-2" role="group">
          <button type="button" class="btn btn-outline-secondary" :class="{ 'active': filter === 'all' }" @click="setFilter('all')">Tất cả</button>
          <button type="button" class="btn btn-outline-secondary" :class="{ 'active': filter === 'unread' }" @click="setFilter('unread')">Chưa đọc</button>
        </div>
        <!-- Hành động -->
        <button class="btn btn-primary" @click="markAllAsRead" :disabled="!hasUnreadNotifications">
          <i class="bi bi-check2-all me-1"></i> Đánh dấu tất cả đã đọc
        </button>
      </div>
    </div>

    <!-- Trạng thái Loading -->
    <div v-if="isLoading" class="d-flex justify-content-center align-items-center p-5">
      <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>
    </div>

    <!-- Trạng thái Rỗng -->
    <div v-else-if="!groupedNotifications.length" class="empty-state text-center p-5 bg-light rounded">
      <i class="bi bi-check2-circle display-4 text-success"></i>
      <h4 class="mt-3 fw-normal">Tuyệt vời!</h4>
      <p class="text-muted">Không có thông báo nào phù hợp với bộ lọc của bạn.</p>
    </div>

    <!-- Danh sách thông báo được nhóm theo ngày -->
    <div v-else>
      <div v-for="group in groupedNotifications" :key="group.date" class="notification-group mb-4">
        <h5 class="date-header">{{ group.date }}</h5>
        <ul class="list-group">
          <li v-for="notification in group.notifications" :key="notification.id"
            class="list-group-item notification-item"
            :class="{ 'unread': !notification.read_at }"
            @click="handleNotificationClick(notification)">
            
            <div class="d-flex align-items-center w-100">
              <!-- Icon -->
              <div class="icon-wrapper flex-shrink-0" :style="{ backgroundColor: getNotificationDetails(notification.type).color }">
                <i :class="getNotificationDetails(notification.type).icon"></i>
              </div>
  
              <!-- Nội dung -->
              <div class="notification-content flex-grow-1 mx-3">
                <p class="mb-0 message-text">{{ notification.data.message }}</p>
              </div>
  
              <!-- Thời gian và chấm chưa đọc -->
              <div class="notification-meta text-end flex-shrink-0">
                <small class="text-muted d-block">{{ formatTime(notification.created_at) }}</small>
                <span v-if="!notification.read_at" class="unread-dot-indicator mt-1" title="Chưa đọc"></span>
              </div>
            </div>
          </li>
        </ul>
      </div>

      <!-- Phân trang -->
      <nav v-if="pagination.last_page > 1" class="mt-4 d-flex justify-content-center">
        <ul class="pagination">
          <li v-for="link in pagination.links" :key="link.label"
            class="page-item" :class="{ 'active': link.active, 'disabled': !link.url }">
            <a class="page-link" href="#" @click.prevent="fetchNotifications(getPageNumberFromUrl(link.url))" v-html="link.label"></a>
          </li>
        </ul>
      </nav>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import axiosConfig from '../../axiosConfig.js';
import { format, isToday, isYesterday } from 'date-fns';
import { vi } from 'date-fns/locale';

const router = useRouter();
const allNotifications = ref([]);
const pagination = ref({});
const isLoading = ref(true);
const filter = ref('all'); // 'all' or 'unread'

// Tính toán danh sách thông báo đã được lọc và nhóm theo ngày
const groupedNotifications = computed(() => {
  const filtered = allNotifications.value.filter(n => {
    if (filter.value === 'unread') return !n.read_at;
    return true;
  });

  if (!filtered.length) return [];

  const groups = filtered.reduce((acc, notification) => {
    const date = new Date(notification.created_at);
    let groupKey;
    if (isToday(date)) {
      groupKey = 'Hôm nay';
    } else if (isYesterday(date)) {
      groupKey = 'Hôm qua';
    } else {
      groupKey = format(date, 'EEEE, dd/MM/yyyy', { locale: vi });
    }
    
    if (!acc[groupKey]) {
      acc[groupKey] = [];
    }
    acc[groupKey].push(notification);
    return acc;
  }, {});

  return Object.keys(groups).map(date => ({
    date,
    notifications: groups[date]
  }));
});

// Kiểm tra xem có thông báo nào chưa đọc không để bật/tắt nút "Đánh dấu đã đọc"
const hasUnreadNotifications = computed(() => {
  return allNotifications.value.some(n => !n.read_at);
});

// Hàm lấy icon và màu sắc dựa trên loại thông báo
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

// Hàm định dạng giờ:phút từ chuỗi ngày
const formatTime = (dateString) => {
  if (!dateString) return '';
  return format(new Date(dateString), 'HH:mm');
};

// Hàm lấy danh sách thông báo từ API, có hỗ trợ phân trang
const fetchNotifications = async (page = 1) => {
  if (!page) return; // Không làm gì nếu link phân trang bị vô hiệu hóa
  try {
    isLoading.value = true;
    const response = await axiosConfig.get(`/api/notifications/all?page=${page}`);
    if (response.data.status) {
      allNotifications.value = response.data.data.data;
      pagination.value = response.data.data; // Lưu trữ toàn bộ thông tin phân trang
    }
  } catch (error) {
    console.error('Lỗi khi lấy danh sách thông báo:', error);
    allNotifications.value = [];
  } finally {
    isLoading.value = false;
  }
};

// Hàm lấy số trang từ URL mà Laravel Pagination trả về
const getPageNumberFromUrl = (url) => {
  if (!url) return null;
  try {
    const urlObject = new URL(url);
    return urlObject.searchParams.get('page');
  } catch (e) {
    return null;
  }
};

// Hàm xử lý khi người dùng click vào một thông báo
const handleNotificationClick = async (notification) => {
  const wasUnread = !notification.read_at;
  // Cập nhật giao diện ngay lập tức
  if (wasUnread) {
    notification.read_at = new Date().toISOString();
  }
  // Chuyển hướng nếu có link
  if (notification.data.link) {
    router.push(notification.data.link);
  }
  // Gửi yêu cầu đánh dấu đã đọc lên server trong nền
  if (wasUnread) {
    try {
      await axiosConfig.post(`/api/notifications/${notification.id}/mark-as-read`);
    } catch (error) {
      console.error('Lỗi khi đánh dấu đã đọc trên server:', error);
    }
  }
};

// Hàm thay đổi bộ lọc (Tất cả / Chưa đọc)
const setFilter = (newFilter) => {
  filter.value = newFilter;
};

// Hàm đánh dấu tất cả thông báo là đã đọc
const markAllAsRead = async () => {
  try {
    await axiosConfig.post('/api/notifications/mark-all-as-read');
    // Cập nhật giao diện cho tất cả thông báo
    allNotifications.value.forEach(n => {
      if (!n.read_at) {
        n.read_at = new Date().toISOString();
      }
    });
  } catch (error) {
    console.error('Lỗi khi đánh dấu tất cả đã đọc:', error);
  }
};

// Hook được gọi khi component được tải
onMounted(() => {
  fetchNotifications();
});
</script>

<style scoped>
.notifications-page {
  background-color: #f8f9fa;
  min-height: 100%;
}
.page-header h2 {
  font-weight: 700;
  color: #343a40;
}
.notification-group .date-header {
  font-size: 0.9rem;
  font-weight: 600;
  color: #6c757d;
  text-transform: uppercase;
  padding: 8px 0;
  margin-bottom: 0.5rem;
  border-bottom: 1px solid #dee2e6;
}
.notification-item {
  background-color: #fff;
  border: 1px solid #e9ecef;
  border-left: 4px solid transparent;
  padding: 1rem;
  margin-bottom: 0.5rem !important;
  border-radius: 8px;
  transition: all 0.2s ease;
  cursor: pointer;
}
.notification-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}
.notification-item.unread {
  background-color: #f7fbff;
  border-left-color: var(--bs-primary);
}
.icon-wrapper {
  width: 40px; height: 40px; color: white;
  border-radius: 50%; display: flex; align-items: center; justify-content: center;
  font-size: 1.1rem;
}
.message-text {
  color: #212529;
  font-weight: 500;
}
.notification-item.unread .message-text {
  font-weight: 700;
}
.notification-meta {
  min-width: 60px;
  position: relative;
  text-align: right;
}
.unread-dot-indicator {
  display: inline-block;
  width: 10px; height: 10px;
  background-color: var(--bs-primary);
  border-radius: 50%;
}
.btn-outline-secondary.active {
  background-color: #6c757d;
  color: #fff;
}
</style>