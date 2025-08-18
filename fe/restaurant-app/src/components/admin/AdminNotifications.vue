<template>
  <div class="notifications-page">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
      <h2 class="mb-0">Tất cả thông báo</h2>
      <div class="d-flex align-items-center gap-2">
        <!-- Bộ lọc ngày -->
        <input type="date" class="form-control" v-model="filterDate" @change="fetchNotifications(1)" style="width: auto;">

        <!-- Bộ lọc Tất cả / Chưa đọc -->
        <div class="btn-group" role="group">
          <button type="button" class="btn btn-outline-secondary" :class="{ 'active': filter === 'all' }" @click="setFilter('all')">Tất cả</button>
          <button type="button" class="btn btn-outline-secondary" :class="{ 'active': filter === 'unread' }" @click="setFilter('unread')">Chưa đọc</button>
        </div>
        <!-- Nút Đánh dấu tất cả đã đọc -->
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
    <div v-else-if="groupedNotifications.length === 0" class="empty-state text-center p-5 bg-light rounded">
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
import { format, isToday, isYesterday, parseISO } from 'date-fns';
import { vi } from 'date-fns/locale';

const router = useRouter();
const allNotifications = ref([]);
const pagination = ref({});
const isLoading = ref(true);
const filter = ref('all');
const filterDate = ref('');

const filteredNotifications = computed(() => {
  return allNotifications.value.filter(n => {
    if (filter.value === 'unread') return !n.read_at;
    return true;
  });
});

const groupedNotifications = computed(() => {
  if (filteredNotifications.value.length === 0) return [];
  const groups = filteredNotifications.value.reduce((acc, notification) => {
    const date = parseISO(notification.created_at);
    const groupKey = isToday(date) ? 'Hôm nay' : isYesterday(date) ? 'Hôm qua' : format(date, 'EEEE, dd/MM/yyyy', { locale: vi });
    if (!acc[groupKey]) acc[groupKey] = [];
    acc[groupKey].push(notification);
    return acc;
  }, {});
  return Object.keys(groups).map(date => ({ date, notifications: groups[date] }));
});

const hasUnreadNotifications = computed(() => allNotifications.value.some(n => !n.read_at));

const fetchNotifications = async (page = 1) => {
  if (!page) return;
  try {
    isLoading.value = true;
    const params = { page };
    if (filterDate.value) params.date = filterDate.value;
    const response = await axiosConfig.get('/api/notifications/all', { params });
    if (response.data.status) {
      allNotifications.value = response.data.data.data;
      pagination.value = response.data.data;
    }
  } catch (error) {
    console.error('Lỗi khi lấy danh sách thông báo:', error);
  } finally {
    isLoading.value = false;
  }
};

const markAllAsRead = async () => {
  try {
    await axiosConfig.post('/api/notifications/mark-all-as-read');
    allNotifications.value.forEach(n => {
      if (!n.read_at) n.read_at = new Date().toISOString();
    });
  } catch (error) {
    console.error('Lỗi khi đánh dấu tất cả đã đọc:', error);
  }
};

const handleNotificationClick = async (notification) => {
  const wasUnread = !notification.read_at;
  if (notification.data.link) {
    router.push(notification.data.link);
  }
  if (wasUnread) {
    try {
      await axiosConfig.post(`/api/notifications/${notification.id}/mark-as-read`);
      notification.read_at = new Date().toISOString();
    } catch (error) {
      console.error('Lỗi khi đánh dấu đã đọc trên server:', error);
    }
  }
};

const setFilter = (newFilter) => {
  filter.value = newFilter;
};

const getPageNumberFromUrl = (url) => {
  if (!url) return null;
  try {
    return new URL(url).searchParams.get('page');
  } catch (e) { return null; }
};

const getNotificationDetails = (type) => {
  const details = {
    'NEW_CONTACT': { icon: 'bi bi-person-plus-fill', color: 'var(--bs-primary)' },
    'NEW_COMMENT': { icon: 'bi bi-chat-left-text-fill', color: 'var(--bs-success)' },
    'NEW_BOOKING': { icon: 'bi bi-calendar-check-fill', color: 'var(--bs-warning)' },
  };
  return details[type] || { icon: 'bi bi-bell-fill', color: 'var(--bs-secondary)' };
};

const formatTime = (dateString) => dateString ? format(parseISO(dateString), 'HH:mm') : '';

onMounted(() => {
  fetchNotifications();
});
</script>

<style scoped>
@import url('https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css');

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