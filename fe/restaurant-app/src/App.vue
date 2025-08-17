<template>
  <div>
    <!-- ========== PHẦN GIAO DIỆN QUẢN TRỊ (ADMIN & STAFF) ========== -->
    <div v-if="$route.path.startsWith('/admin')">
      <div class="toast-container position-fixed top-0 start-0 p-3">
        <div v-if="showToast" class="toast align-items-center text-bg-light show" role="alert" aria-live="assertive"
          aria-atomic="true">
          <div class="d-flex">
            <div class="toast-header">
              <strong class="me-auto">Thông báo mới</strong>
              <button type="button" class="btn-close" @click="showToast = false" aria-label="Close"></button>
            </div>
          </div>
          <div class="toast-body">{{ latestNotification.message }} - {{ latestNotification.timestamp }}</div>
          <div class="toast-footer"><button class="btn btn-light mt-2" @click="showMoreNotifications">Xem thêm</button>
          </div>
        </div>
        <div v-if="showMore" class="mt-3">
          <div v-for="(notification, index) in recentNotifications" :key="index"
            class="toast align-items-center text-bg-light show mb-2" role="alert">
            <div class="toast-body">{{ notification.message }} - {{ notification.timestamp }}</div>
          </div>
        </div>
      </div>

      <!-- ===== SIDEBAR ĐỘNG CHO ADMIN & STAFF ===== -->
      <div v-if="isAdmin || isStaff" class="sidebar">
        <div class="header text-center p-3 border-bottom">
          <img src="/logo/logo_HXH.png" alt="Luxuria Logo" class="logo" />
          <span class="fw-bold">Hồ Xuân Hương</span>
        </div>
        <div class="profile text-center p-3 border-bottom">
          <p class="mb-0 text-muted">{{ userInfo.name || 'User' }}</p>
          <p class="text-muted">{{ userInfo.email || 'user@example.com' }}</p>
          <div class="icons d-flex justify-content-center mt-2">
            <i class="bi bi-person mx-2" @click="goToProfile"></i>
            <i class="bi bi-box-arrow-right mx-2" @click.prevent="logout"></i>
          </div>
        </div>

        <!-- Render sidebar từ computed property -->
        <ul class="nav flex-column">
          <template v-for="section in groupedSidebar" :key="section.title">
            <li class="nav-section-title">{{ section.title }}</li>
            <li v-for="item in section.items" :key="item.to || item.key" class="nav-item">
              <!-- Xử lý menu con (collapsible) -->
              <template v-if="item.children">
                <a class="nav-link collapsible-toggle" href="#" @click.prevent="toggleSubmenu(item.key)">
                  <span><i :class="item.icon"></i> {{ item.title }}</span>
                  <i class="bi bi-chevron-down toggle-icon" :class="{ 'rotated': openSubmenus[item.key] }"></i>
                </a>
                <ul class="nav-submenu" :class="{ 'open': openSubmenus[item.key] }">
                  <li v-for="child in item.children" :key="child.to">
                    <router-link class="nav-link" :to="child.to"><i :class="child.icon"></i>{{ child.title
                    }}</router-link>
                  </li>
                </ul>
              </template>
              <!-- Xử lý menu đơn -->
              <template v-else>
                <router-link class="nav-link" :to="item.to"><i :class="item.icon"></i> {{ item.title }}</router-link>
              </template>
            </li>
          </template>
        </ul>
      </div>

      <!-- Main Content -->
      <div class="main-content">
        <div class="navbar-top" :class="{ 'scrolled': navbarSticky }">
          <div class="d-flex align-items-center">
            <NotificationBell v-if="isLogin && (isAdmin || isStaff)" />
            <span class="ms-2">{{ userInfo.name || 'User' }}</span>
          </div>
        </div>
        <main class="admin-main">
          <RouterView></RouterView>
        </main>
      </div>
    </div>

    <!-- ========== PHẦN GIAO DIỆN CÔNG KHAI (KHÁCH HÀNG) ========== -->
    <div v-else>
      <chatbot></chatbot>
      <header ref="headerRef">
        <nav class="navbar navbar-expand-lg navbar-light fixed-top" :class="{ 'scrolled': navbarSticky }">
          <div class="container-fluid">
            <button class="navbar-toggler" type="button" @click="toggleMenu" aria-controls="navbarNav"
              aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="navbarNav" ref="navbarRef" :class="{ 'show': navbarActive }">
              <ul class="navbar-nav navbar-nav-left">
                <li class="nav-item"><router-link class="nav-link" to="/">Trang chủ</router-link></li>
                <li class="nav-item"><router-link class="nav-link" to="/about">Giới thiệu</router-link></li>
                <li class="nav-item dropdown">
                  <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                    aria-expanded="false">Các loại phòng</a>
                  <ul class="dropdown-menu">
                    <li v-for="roomType in roomTypes" :key="roomType.type_id"><router-link class="dropdown-item"
                        :to="{ name: 'RoomTypeDetail', params: { id: roomType.type_id } }">{{ roomType.type_name
                        }}</router-link></li>
                  </ul>
                </li>
                <li class="nav-item"><router-link class="nav-link" to="/news">Tin tức</router-link></li>
                <li class="nav-item"><router-link class="nav-link" to="/contact">Liên hệ</router-link></li>
              </ul>
              <ul class="navbar-nav navbar-nav-right align-items-center">
                <li class="nav-item"><router-link class="nav-link" to="/HistoryBooking">Lịch Sử Đặt Phòng</router-link>
                </li>
                <template v-if="!isLogin">
                  <li class="nav-item"><router-link class="btn btn-outline-custom" to="/login">Đăng Nhập</router-link>
                  </li>
                </template>
                <template v-if="isLogin">
                  <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                      aria-expanded="false">{{ userInfo.name }}</a>
                    <ul class="dropdown-menu dropdown-menu-end">
                      <li><router-link class="dropdown-item" to="/userprofile">Thông Tin User</router-link></li>
                      <li v-if="isAdmin || isStaff"><router-link class="dropdown-item" to="/admin">Vào trang quản
                          lý</router-link></li>
                      <li><a class="dropdown-item logout-link" @click.prevent="logout">Đăng Xuất</a></li>
                    </ul>
                  </li>
                </template>
                <li class="nav-item"><router-link class="btn btn-solid-custom" to="/booking_hotel">Đặt
                    Phòng</router-link>
                </li>
              </ul>
            </div>
            <a class="navbar-brand mx-auto" href="/">
              <img src="/logo/logo_HXH.png" alt="Foodie Logo" class="logo-img" />
              <span class="d-none d-lg-inline">Hồ Xuân Hương</span>
            </a>
          </div>
        </nav>
      </header>
      <main><router-view /></main>
      <Footer></Footer>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, provide, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axiosConfig from './axiosConfig.js';
import Footer from './components/Footer.vue';
import chatbot from './components/Chat.vue';
import socket from './socket.js';
import NotificationBell from './components/NotificationBell.vue';

const route = useRoute();
const router = useRouter();
const userInfo = ref({});
const isLogin = ref(false);
const isAdmin = ref(false);
const isStaff = ref(false);

// const staffRoles = ['manager', 'receptionist'];

const setUserRoles = (user) => {
  userInfo.value = user || {};
  isLogin.value = !!user;
  
  const roleName = user?.role?.name;

  // 1. Xác định Admin: Chỉ có vai trò 'admin' mới là Admin.
  isAdmin.value = (roleName === 'admin');

  // 2. Xác định Nhân viên (Staff): Bất kỳ ai CÓ VAI TRÒ và KHÔNG PHẢI là 'client'.
  // Định nghĩa này bao gồm cả 'admin', 'manager', 'receptionist', và bất kỳ vai trò mới nào bạn tạo ra.
  // Đây là những người có quyền truy cập vào trang quản trị.
  isStaff.value = (roleName && roleName !== 'client');
};

const can = (permission) => {
  // 1. Admin luôn có mọi quyền
  if (isAdmin.value) return true;

  // 2. Lấy danh sách quyền từ userInfo một cách an toàn
  const userPermissions = userInfo.value?.role?.permissions || [];

  // 3. Kiểm tra xem trong mảng permissions có object nào có 'name' trùng với quyền yêu cầu không
  return userPermissions.some(p => p.name === permission);
};

// CẤU TRÚC SIDEBAR: Master list của tất cả các mục có thể có
const sidebarStructure = [
  { section: 'TRANG QUẢN TRỊ', title: 'Trang Thống Kê', to: '/admin/dashboard', icon: 'bi bi-grid-1x2', requiredPermission: null },
  { section: 'TRANG QUẢN TRỊ', title: 'Tình Trạng Phòng', to: '/admin/occupancy', icon: 'bi bi-map', requiredPermission: 'manage_bookings' },
  { section: 'TRANG QUẢN TRỊ', title: 'Quản Lý Đặt Phòng', to: '/admin/bookings', icon: 'bi bi-journal-check', requiredPermission: 'manage_bookings' },
  { section: 'TRANG QUẢN TRỊ', title: 'Lịch Sử Đặt Phòng', to: '/admin/booking-histories', icon: 'bi bi-clock-history', requiredPermission: 'manage_reports' },
  { section: 'QUẢN LÝ PHÒNG', title: 'Danh Mục Phòng', to: '/admin/room-types', icon: 'bi bi-tags', requiredPermission: 'manage_rooms' },
  { section: 'QUẢN LÝ PHÒNG', title: 'Quản Lý Giá Phòng', to: '/admin/prices', icon: 'bx bxs-dollar-circle', requiredPermission: 'manage_prices' },
  { section: 'QUẢN LÝ PHÒNG', title: 'Quản Lý Phòng', to: '/admin/rooms', icon: 'bx bx-bed', requiredPermission: 'manage_rooms' },
  { section: 'QUẢN LÝ DỊCH VỤ & TIỆN NGHI', title: 'Quản Lý Dịch Vụ', to: '/admin/services', icon: 'bi bi-box-seam', requiredPermission: 'manage_services' },
  { section: 'QUẢN LÝ DỊCH VỤ & TIỆN NGHI', title: 'Quản Lý Tiện Nghi', to: '/admin/amenities', icon: 'bi bi-gem', requiredPermission: 'manage_amenities' },
  { section: 'QUẢN LÝ DỊCH VỤ & TIỆN NGHI', title: 'Quản Lý Giảm Giá', to: '/admin/coupons', icon: 'bi bi-ticket-perforated', requiredPermission: 'manage_coupons' },
  {
    section: 'QUẢN LÝ TÀI KHOẢN', title: 'Quản lý Vai trò', to: '/admin/roles', icon: 'bi bi-person-check', requiredPermission: 'manage_users'
  },
  { section: 'QUẢN LÝ TÀI KHOẢN', title: 'Quản Lý Tài Khoản', to: '/admin/users', icon: 'bi bi-people', requiredPermission: 'manage_users' },
  { section: 'QUẢN LÝ TÀI KHOẢN', title: 'Quản Lý Nhân Viên', to: '/admin/staffs', icon: 'bi bi-person-workspace', requiredPermission: 'manage_staff' },
  { section: 'QUẢN LÝ TÀI KHOẢN', title: 'Quản Lý Khách hàng', to: '/admin/customers', icon: 'bi bi-person-lines-fill', requiredPermission: 'manage_customers' },

  {
    section: 'QUẢN LÝ HỆ THỐNG', title: 'Quản Lý Tin Tức', icon: 'bi bi-newspaper', requiredPermission: 'manage_news', key: 'news',
    children: [
      { title: 'Tin Tức', to: '/admin/news', icon: 'bi bi-newspaper' },
      { title: 'Danh Mục', to: '/admin/news-categories', icon: 'bi bi-tags' },
      { title: 'Bình Luận', to: '/admin/news-comments', icon: 'bi bi-chat-dots' },
    ]
  },
  { section: 'QUẢN LÝ HỆ THỐNG', title: 'Quản Lý Liên Hệ', to: '/admin/contacts', icon: 'bi bi-envelope', requiredPermission: 'manage_contacts' },
  { section: 'QUẢN LÝ HỆ THỐNG', title: 'Training AI', to: '/admin/traningAI', icon: 'bi bi-robot', requiredPermission: 'manage_ai_training' },
  { section: 'QUẢN LÝ HỆ THỐNG', title: 'Chat Admin', to: '/admin/ChatAdmin', icon: 'bi bi-chat-dots', requiredPermission: 'manage_admin_chat' },
  { section: 'QUẢN LÝ HỆ THỐNG', title: 'Tất cả thông báo', to: '/admin/notifications', icon: 'bi bi-bell', requiredPermission: null },
  { section: 'QUẢN LÝ HỆ THỐNG', title: 'Thoát Trang Quản Trị', to: '/', icon: 'bi bi-box-arrow-left', requiredPermission: null },
];

// COMPUTED PROPERTY: Tự động lọc và nhóm sidebar dựa trên quyền của người dùng
const groupedSidebar = computed(() => {
  const accessibleItems = sidebarStructure.filter(item =>
    item.requiredPermission ? can(item.requiredPermission) : true
  );
  const groups = accessibleItems.reduce((acc, item) => {
    (acc[item.section] = acc[item.section] || []).push(item);
    return acc;
  }, {});
  return Object.entries(groups).map(([title, items]) => ({ title, items }));
});

const openSubmenus = ref({});
const toggleSubmenu = (key) => { openSubmenus.value[key] = !openSubmenus.value[key]; };

const apiUrl = 'http://127.0.0.1:8000';
provide('apiUrl', apiUrl);
const headerRef = ref(null);
const navbarRef = ref(null);
const navbarActive = ref(false);
const navbarSticky = ref(false);
const roomTypes = ref([]);

const updateAndRefreshUserInfo = (updatedUser) => {
  localStorage.setItem('userInfo', JSON.stringify(updatedUser));
  setUserRoles(updatedUser);
};
provide('updateAndRefreshUserInfo', updateAndRefreshUserInfo);

const restoreUserSession = () => {
  const storedUser = localStorage.getItem('userInfo');
  if (storedUser && storedUser !== 'undefined') {
    try { setUserRoles(JSON.parse(storedUser)); }
    catch (e) { console.error('Lỗi phân tích thông tin người dùng từ localStorage:', e); logout(); }
  }
};
const fetchUserInfo = async () => {
  const token = localStorage.getItem('tokenJwt');
  if (!token) { setUserRoles(null); return; }
  try {
    const response = await axiosConfig.get(`${apiUrl}/api/protected`);
    const user = response.data.user;
    localStorage.setItem('userInfo', JSON.stringify(user));
    setUserRoles(user);
  } catch (error) {
    console.error('Lỗi khi lấy thông tin người dùng:', error.response ? error.response.data : error.message);
    if (error.response?.status === 401) { logout(); }
  }
};
const logout = () => {
  localStorage.removeItem('tokenJwt');
  localStorage.removeItem('userInfo');
  setUserRoles(null);
  window.location.href = '/login';
};
const fetchRoomTypes = async () => {
  try {
    const response = await axiosConfig.get(`${apiUrl}/api/room-types`);
    if (response.data.status) { roomTypes.value = response.data.data; }
  } catch (error) { console.error('Lỗi khi lấy danh sách loại phòng:', error); }
};
const goToProfile = () => { router.push('/userprofile'); };
const toggleMenu = () => { navbarActive.value = !navbarActive.value; };
const handleScroll = () => { navbarSticky.value = window.scrollY > 50; };
const handleOutsideClick = (event) => {
  if (window.innerWidth <= 991 && navbarActive.value && navbarRef.value) {
    const isClickInside = navbarRef.value.contains(event.target);
    const isToggleButton = event.target.closest('.navbar-toggler');
    if (!isClickInside && !isToggleButton) { navbarActive.value = false; }
  }
};
const handleUrlParams = () => {
  const urlParams = new URLSearchParams(window.location.search);
  const token = urlParams.get('token');
  const user = urlParams.get('user');
  if (token && user) {
    localStorage.setItem('tokenJwt', token);
    localStorage.setItem('userInfo', user);
    try { setUserRoles(JSON.parse(user)); router.replace({ query: {} }); }
    catch (e) { console.error('Error parsing user from URL:', e); }
  }
};
const notifications = ref([]);
const showToast = ref(false);
const showMore = ref(false);
const latestNotification = ref({});
const recentNotifications = ref([]);
const addNotification = (message) => {
  const timestamp = new Date().toLocaleString('vi-VN');
  const newNotif = { message, timestamp };
  notifications.value.unshift(newNotif);
  if (notifications.value.length > 10) { notifications.value.pop(); }
  latestNotification.value = newNotif;
  showToast.value = true;
};
const showMoreNotifications = () => {
  showMore.value = !showMore.value;
  recentNotifications.value = notifications.value;
};
socket.on('connect', () => { console.log(`Connected with socket ID: ${socket.id}`); });
socket.on('notification', (data) => { addNotification(data.message); });
onMounted(() => {
  handleUrlParams();
  restoreUserSession();
  if (isLogin.value) { fetchUserInfo(); }
  fetchRoomTypes();
  window.addEventListener('scroll', handleScroll);
  document.addEventListener('click', handleOutsideClick);
});
onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll);
  document.removeEventListener('click', handleOutsideClick);
  socket.off('connect');
  socket.off('notification');
});
</script>

<style scoped>
/* Giữ nguyên toàn bộ CSS của bạn */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
@import url('https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css');
@import url('https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css');
@import url('https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css');

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: 'Inter', sans-serif;
}

body {
  background-color: #f8f9fa;
  height: 100%;
  overflow-y: auto;
}

.navbar {
  background-color: #ffffff;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  height: 80px;
  padding: 0 1.5rem;
  transition: all 0.3s ease;
}

.navbar .container-fluid {
  position: relative;
  height: 100%;
}

.navbar-brand {
  display: flex;
  align-items: center;
  font-weight: 600;
  color: #081B54;
  transition: color 0.3s ease;
  position: absolute;
  left: 50%;
  transform: translateX(-50%);
  top: 50%;
  transform: translate(-50%, -50%);
}

.logo-img {
  width: 60px;
  height: 50px;
  margin: 0 10px;
}

.navbar-toggler {
  position: absolute;
  right: 1rem;
  top: 50%;
  transform: translateY(-50%);
}

.navbar-collapse {
  height: 100%;
}

.navbar-nav {
  gap: 1rem;
}

.navbar-nav-left {
  margin-right: auto;
}

.navbar-nav-right {
  margin-left: auto;
}

.nav-link {
  color: #081B54;
  font-weight: 500;
  padding: 0.5rem 1rem;
  transition: color 0.2s ease-in-out, background-color 0.2s ease-in-out;
}

.nav-link:hover {
  color: #A98A66;
  background-color: #f1f3f5;
  border-radius: 6px;
}

.btn-outline-custom {
  border: 1px solid #e9ecef;
  color: #081B54;
  padding: 8px 18px;
  border-radius: 6px;
  background-color: transparent;
  text-decoration: none;
  display: inline-block;
  transition: all 0.3s ease;
}

.btn-outline-custom:hover {
  background-color: #A98A66;
  color: #ffffff;
  border-color: #A98A66;
}

.btn-solid-custom {
  background-color: #A98A66;
  color: #ffffff;
  border: 1px solid #A98A66;
  padding: 8px 18px;
  border-radius: 6px;
  text-decoration: none;
  display: inline-block;
  transition: all 0.3s ease;
}

.btn-solid-custom:hover {
  background-color: #8b6e4b;
  border-color: #8b6e4b;
}

.dropdown-item:hover {
  color: #A98A66 !important;
  background-color: #f1f3f5;
}

main {
  min-height: calc(100vh - 80px);
  width: 100%;
  padding-top: 80px;
}

footer {
  background-color: #081B54;
  color: #fff;
}

.sidebar {
  height: 100vh;
  background: linear-gradient(180deg, #ffffff 0%, #f8f9fa 100%);
  box-shadow: 2px 0 8px rgba(0, 0, 0, 0.1);
  padding-top: 20px;
  position: fixed;
  width: 260px;
  z-index: 1000;
  overflow-y: auto;
  scrollbar-width: none;
  transition: transform 0.3s ease;
}

.sidebar::-webkit-scrollbar {
  display: none;
}

.sidebar .header {
  text-align: center;
  padding: 1.5rem;
  border-bottom: 1px solid #e9ecef;
}

.sidebar .header .logo {
  width: 40px;
  margin: 0.5rem;
}

.sidebar .header span {
  font-size: 1.4rem;
  font-weight: 600;
  color: #081B54;
}

.sidebar .profile {
  text-align: center;
  padding: 1.5rem 0;
  border-bottom: 1px solid #e9ecef;
}

.sidebar .profile p {
  margin: 0;
  font-size: 0.95rem;
  color: #6c757d;
}

.sidebar .profile .icons {
  display: flex;
  justify-content: center;
  margin-top: 1rem;
}

.sidebar .profile .icons i {
  font-size: 1.3rem;
  margin: 0 8px;
  color: #6c757d;
  cursor: pointer;
  transition: color 0.3s ease, transform 0.2s ease;
}

.sidebar .profile .icons i:hover {
  color: #A98A66;
  transform: scale(1.2);
}

.sidebar .nav-link {
  color: #081B54;
  padding: 12px 20px;
  display: flex;
  align-items: center;
  font-weight: 500;
  transition: all 0.3s ease;
  border-left: 3px solid transparent;
}

.sidebar .nav-link:hover,
.sidebar .nav-link.router-link-active {
  color: #A98A66;
  background-color: #f1f3f5;
  border-left: 3px solid #A98A66;
}

.sidebar .nav-link i {
  margin-right: 12px;
  font-size: 1.2rem;
  width: 20px;
  text-align: center;
}

.sidebar .collapsible-toggle {
  cursor: pointer;
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
}

.sidebar .collapsible-toggle .toggle-icon {
  transition: transform 0.3s ease;
  font-size: 0.9rem;
}

.sidebar .collapsible-toggle .toggle-icon.rotated {
  transform: rotate(180deg);
}

.nav-submenu {
  list-style: none;
  padding-left: 0;
  margin: 0;
  max-height: 0;
  overflow: hidden;
  transition: max-height 0.35s ease-in-out;
  background-color: #f8f9fa;
}

.nav-submenu.open {
  max-height: 200px;
}

.nav-submenu .nav-link {
  padding-left: 30px !important;
}

.nav-submenu .nav-link i {
  font-size: 0.8rem;
}

.nav-section-title {
  padding: 8px 20px;
  font-size: 0.9rem;
  font-weight: 400;
  color: #6c757d;
  background-color: #f8f9fa;
  text-transform: uppercase;
  opacity: 0.7;
  border-bottom: 1px solid #e9ecef;
  margin-top: 10px;
}

.main-content {
  margin-left: 260px;
  padding: 20px;
  padding-top: 80px;
  min-height: 100vh;
  background-color: #f8f9fa;
  width: calc(100% - 260px);
  overflow-y: auto;
}

.navbar-top {
  padding: 12px 20px;
  display: flex;
  justify-content: flex-end;
  align-items: center;
  position: fixed;
  top: 0;
  left: 260px;
  right: 0;
  z-index: 900;
  height: 60px;
  background-color: #ffffff;
  transition: all 0.3s ease;
}

.navbar-top.scrolled {
  background-color: #ffffff;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.navbar-top .d-flex span {
  font-weight: 500;
  color: #081B54;
  margin-left: 10px;
}

.admin-main {
  padding: 20px;
  min-height: calc(100vh - 60px);
  width: 100%;
  background-color: transparent;
}

.admin-main>* {
  width: 100%;
  background: #ffffff;
  border-radius: 10px;
  padding: 20px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

@media (max-width: 991px) {
  .navbar-brand {
    position: static;
    transform: none;
    margin-right: auto;
  }

  .navbar-toggler {
    position: static;
    transform: none;
  }

  .navbar .container-fluid {
    display: flex;
    justify-content: space-between;
    width: 100%;
  }

  .collapse.show .navbar-nav {
    flex-direction: column;
    align-items: flex-start !important;
    gap: 1rem;
    padding: 1rem 0;
  }

  .collapse.show .navbar-nav-left,
  .collapse.show .navbar-nav-right {
    margin: 0 !important;
    width: 100%;
  }

  .collapse.show .navbar-nav li {
    width: 100%;
  }

  .collapse.show .navbar-nav .btn {
    width: 100%;
    text-align: left;
  }

  .navbar-collapse {
    position: fixed;
    top: 80px;
    right: 0;
    height: calc(100vh - 80px);
    width: 250px;
    background-color: #fff;
    transform: translateX(100%);
    transition: transform 0.3s ease-in-out;
    padding: 20px;
    z-index: 1000;
    box-shadow: -2px 0 10px rgba(0, 0, 0, 0.1);
  }

  .navbar-collapse.show {
    transform: translateX(0);
  }

  .sidebar {
    transform: translateX(-100%);
  }

  .main-content {
    margin-left: 0;
    width: 100%;
  }

  .navbar-top {
    left: 0;
    width: 100%;
  }
}
</style>