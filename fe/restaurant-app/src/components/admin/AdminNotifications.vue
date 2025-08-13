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
// import { format, formatDistanceToNow, isToday, isYesterday } from 'date-fns';
// import { vi } from 'date-fns/locale';

const router = useRouter();
const allNotifications = ref([]);
const pagination = ref({});
const isLoading = ref(true);
const filter = ref('all'); // 'all' or 'unread'

// Tính toán danh sách thông báo đã nhóm
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

const hasUnreadNotifications = computed(() => {
  return allNotifications.value.some(n => !n.read_at);
});

// Các hàm tiện ích
const getNotificationDetails = (type) => { /* ... giữ nguyên như cũ ... */ };
const formatTime = (dateString) => format(new Date(dateString), 'HH:mm');

// Lấy dữ liệu từ API
const fetchNotifications = async (page = 1) => { /* ... giữ nguyên như cũ ... */ };
const getPageNumberFromUrl = (url) => { /* ... giữ nguyên như cũ ... */ };
const handleNotificationClick = async (notification) => { /* ... giữ nguyên như cũ ... */ };

const setFilter = (newFilter) => {
  filter.value = newFilter;
  // Không cần fetch lại vì đã có tất cả dữ liệu, chỉ lọc trên frontend
};

const markAllAsRead = async () => {
  try {
    await axiosConfig.post('/api/notifications/mark-all-as-read');
    // Cập nhật UI
    allNotifications.value.forEach(n => {
      if (!n.read_at) n.read_at = new Date().toISOString();
    });
  } catch (error) {
    console.error('Lỗi khi đánh dấu tất cả đã đọc:', error);
  }
};

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
}
.unread-dot-indicator {
  position: absolute;
  top: 50%;
  right: 0;
  transform: translateY(-50%);
  width: 10px; height: 10px;
  background-color: var(--bs-primary);
  border-radius: 50%;
}
.btn-outline-secondary.active {
  background-color: #6c757d;
  color: #fff;
}
</style>