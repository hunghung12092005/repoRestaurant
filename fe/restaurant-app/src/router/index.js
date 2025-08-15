import { createRouter, createWebHistory } from 'vue-router';

// Import các components
import HomeComponent from '../components/HomeComponent.vue';
import Home2 from '../components/Home2.vue';
import AboutComponent from '../components/AboutComponent.vue';
import ContactComponent from '../components/ContactComponent.vue';
import ReservationComponent from '../components/booking/ReservationComponent.vue';
import BookRoomComponent from '../components/booking/BookRoomComponent.vue';
import BlogComponent from '../components/News/BlogComponent.vue';
import LoginComponent from '../components/Login.vue';
import TestJwtComponent from '../components/testTokenJwt.vue';
import BlogDetailComponent from '../components/News/BlogDetailComponent.vue';
import AdminRoomTypeComponent from '../components/AdminRoom/AdminRoomTypeComponent.vue';
import AdminRoomComponent from '../components/AdminRoom/AdminRoomComponent.vue';
import AdminPriceComponent from '../components/AdminRoom/AdminPriceComponent.vue';
import AdminOccupancyComponent from '../components/AdminRoom/AdminOccupancy.vue';
import AdminAmenitiesComponent from '../components/AdminSeAm/AdminAmenitiesComponent.vue';
import AdminServicesComponent from '../components/AdminSeAm/AdminServicesComponent.vue';
import AdminBookingComponent from '../components/AdminBooking/AdminBookingComponent.vue';
import AdminDashboardComponent from '../components/admin/AdminDashboardComponent.vue';
import AdminUsersComponent from '../components/admin/AdminUsersComponent.vue';
import AdminContacts from '../components/admin/AdminContacts.vue';
import test from '../components/booking_room/test.vue';
import chatbot from '../components/booking_room/chatbot.vue';
import page404 from '../components/404.vue';
import ChatAdmin from '../components/ChatAdmin.vue';
import Chat from '../components/Chat.vue';
import qrCodeCCCD from '../components/qrCodeCCCD.vue';
import rooms from '../components/booking_room/BookRoomComponent.vue';
import rooms3 from '../components/booking_room/Booking3.vue'
import HistoryBooking from '../components/booking_room/HistoryBooking.vue';
import ThanksBooking from '../components/booking_room/ThanksBooking.vue';
import userprofile from '../components/user/userProfile.vue'
import forgotPass from '../components/user/forgotPass.vue'
import AdminNewsComponent from '../components/admin/news/AdminNewsComponent.vue';
import AdminNewsCategoryComponent from '../components/admin/news/AdminNewsCategoryComponent.vue';
import AdminNewsCommentComponent from '../components/admin/news/AdminNewsCommentComponent.vue';
import RoomTypeDetail from '../components/RoomTypeDetail.vue';
import traningAI from '../components/admin/AI/traningAI.vue';
import AdminBookingHistory from '../components/AdminBooking/AdminBookingHistory.vue';
import coupons from '../components/admin/coupons/coupons.vue'
import StaffComponent from '../components/staff/StaffComponent.vue';
import AdminNotifications from '../components/admin/AdminNotifications.vue';

const routes = [
  // --- PUBLIC ROUTES ---
  { path: '/', name: 'Home2', component: Home2 },
  { path: '/about', name: 'AboutComponent', component: AboutComponent },
  { path: '/contact', name: 'ContactComponent', component: ContactComponent },
  { path: '/news', name: 'NewsList', component: BlogComponent },
  { path: '/news/:id', name: 'NewsDetail', component: BlogDetailComponent },
  { path: '/room-types/:id', name: 'RoomTypeDetail', component: RoomTypeDetail, props: true },
  { path: '/booking_hotel', name: 'rooms3', component: rooms3 },
  { path: '/login', name: 'LoginComponent', component: LoginComponent },
  { path: '/forgotPass', name: 'forgotPass', component: forgotPass },
  { path: '/ThanksBooking', name: 'ThanksBooking', component: ThanksBooking },
  { path: '/HistoryBooking', name: 'HistoryBooking', component: HistoryBooking },

  // --- AUTHENTICATED USER ROUTES ---
  { path: '/userprofile', name: 'userprofile', component: userprofile, meta: { requiresAuth: true } },

  // --- ADMIN & STAFF ROUTES ---
  { path: '/admin', redirect: '/admin/dashboard' },
  {
    path: '/admin/dashboard',
    name: 'AdminDashboard',
    component: AdminDashboardComponent,
    meta: { requiresAuth: true }, // Mọi staff/admin đều có thể xem
  },
  {
    path: '/admin/occupancy',
    name: 'AdminOccupancy',
    component: AdminOccupancyComponent,
    meta: { requiresAuth: true, permission: 'manage_bookings' },
  },
  {
    path: '/admin/bookings',
    name: 'AdminBookingComponent',
    component: AdminBookingComponent,
    meta: { requiresAuth: true, permission: 'manage_bookings' },
  },
  {
    path: '/admin/services',
    name: 'AdminServices',
    component: AdminServicesComponent,
    meta: { requiresAuth: true }, // Tạm cho admin/staff
  },
  {
    path: '/admin/amenities',
    name: 'AdminAmenities',
    component: AdminAmenitiesComponent,
    meta: { requiresAuth: true }, // Tạm cho admin/staff
  },
  {
    path: '/admin/notifications',
    name: 'AdminNotifications',
    component: AdminNotifications,
    meta: { requiresAuth: true },
  },

  // --- PERMISSION-BASED ROUTES ---
  {
    path: '/admin/news',
    name: 'AdminNews',
    component: AdminNewsComponent,
    meta: { requiresAuth: true, permission: 'manage_news' },
  },
  {
    path: '/admin/news-categories',
    name: 'AdminNewsCategories',
    component: AdminNewsCategoryComponent,
    meta: { requiresAuth: true, permission: 'manage_news' },
  },
  {
    path: '/admin/news-comments',
    name: 'AdminNewsComments',
    component: AdminNewsCommentComponent,
    meta: { requiresAuth: true, permission: 'manage_news' },
  },
  {
    path: '/admin/contacts',
    name: 'AdminContacts',
    component: AdminContacts,
    meta: { requiresAuth: true, permission: 'manage_contacts' },
  },
  {
    path: '/admin/room-types',
    name: 'AdminRoomType',
    component: AdminRoomTypeComponent,
    meta: { requiresAuth: true, permission: 'manage_rooms' }, // Sử dụng permission
  },
  {
    path: '/admin/prices',
    name: 'AdminPrice',
    component: AdminPriceComponent,
    meta: { requiresAuth: true, permission: 'manage_prices' }, // Sử dụng permission
  },
  {
    path: '/admin/rooms',
    name: 'AdminRoom',
    component: AdminRoomComponent,
    meta: { requiresAuth: true, permission: 'manage_rooms' }, // Sử dụng permission
  },
  {
    path: '/admin/users',
    name: 'AdminUsers',
    component: AdminUsersComponent,
    meta: { requiresAuth: true, permission: 'manage_users' },
  },
   {
    path: '/admin/staffs',
    name: 'StaffComponent',
    component: StaffComponent,
    meta: { requiresAuth: true, permission: 'manage_staff' },
  },
  {
    path: '/admin/booking-histories',
    name: 'AdminBookingHistory',
    component: AdminBookingHistory,
    meta: { requiresAuth: true, permission: 'manage_reports' },
  },
  {
    path: '/admin/traningAI',
    name: 'traningAI',
    component: traningAI,
    meta: { requiresAuth: true, permission: 'manage_ai_training' },
  },
  {
    path: '/admin/ChatAdmin',
    name: 'ChatAdmin',
    component: ChatAdmin,
    meta: { requiresAuth: true, permission: 'manage_admin_chat' },
  },
  {
    path: '/admin/coupons',
    name: 'coupons',
    component: coupons,
    meta: { requiresAuth: true, permission: 'manage_coupons' },
  },
 
  // --- MISC & 404 ---
  { path: '/test', name: 'test', component: test },
  { path: '/:catchAll(.*)', name: 'NotFound', component: page404 },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.afterEach(() => {
  window.scrollTo(0, 0);
});

// Route Guard để kiểm tra quyền truy cập
router.beforeEach((to, from, next) => {
  const isAuthenticated = !!localStorage.getItem('tokenJwt');
  const requiresAuth = to.matched.some(record => record.meta.requiresAuth);

  // 1. Nếu route yêu cầu đăng nhập mà chưa đăng nhập -> về trang login
  if (requiresAuth && !isAuthenticated) {
    return next({ name: 'LoginComponent', query: { redirect: to.fullPath } });
  }

  // 2. Nếu đã đăng nhập và route yêu cầu quyền
  if (isAuthenticated && requiresAuth) {
    let userInfo = {};
    try {
      const raw = localStorage.getItem('userInfo');
      userInfo = raw && raw !== 'undefined' ? JSON.parse(raw) : {};
    } catch (e) {
      console.error('Lỗi khi parse userInfo:', e);
      // Nếu lỗi, đăng xuất để đảm bảo an toàn
      localStorage.removeItem('tokenJwt');
      localStorage.removeItem('userInfo');
      return next({ name: 'LoginComponent' });
    }

    const userRole = userInfo.role || 'client';
    const userPermissions = userInfo.permissions || [];

    // Người dùng là 'admin' có toàn quyền truy cập
    if (userRole === 'admin') {
      return next();
    }
    
    // Kiểm tra quyền hạn yêu cầu (permission)
    const requiredPermission = to.meta.permission;
    if (requiredPermission && !userPermissions.includes(requiredPermission)) {
        // Có thể hiển thị thông báo "Không có quyền truy cập"
        // alert('Bạn không có quyền truy cập chức năng này.');
        return next('/admin/dashboard'); // Chuyển hướng về trang an toàn
    }
  }

  // 3. Mọi trường hợp khác, cho phép truy cập
  next();
});

export default router;