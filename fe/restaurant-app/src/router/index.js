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
import rooms from '../components/booking_room/BookRoomComponent.vue'
import rooms2 from '../components/booking_room/Booking2.vue'
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

const routes = [
  // --- PUBLIC ROUTES ---
  { path: '/', name: 'Home2', component: Home2 },
  { path: '/about', name: 'AboutComponent', component: AboutComponent },
  { path: '/contact', name: 'ContactComponent', component: ContactComponent },
  { path: '/news', name: 'NewsList', component: BlogComponent },
  { path: '/news/:id', name: 'NewsDetail', component: BlogDetailComponent },
  { path: '/room-types/:id', name: 'RoomTypeDetail', component: RoomTypeDetail, props: true },
  { path: '/rooms3', name: 'rooms3', component: rooms3 },
  { path: '/login', name: 'LoginComponent', component: LoginComponent },
  { path: '/forgotPass', name: 'forgotPass', component: forgotPass },
  { path: '/ThanksBooking', name: 'ThanksBooking', component: ThanksBooking },

  // --- AUTHENTICATED USER ROUTES ---
  { path: '/userprofile', name: 'userprofile', component: userprofile, meta: { requiresAuth: true } },
  { path: '/HistoryBooking', name: 'HistoryBooking', component: HistoryBooking, meta: { requiresAuth: true } },

  // --- ADMIN & STAFF ROUTES ---
  { path: '/admin', redirect: '/admin/dashboard' },
  {
    path: '/admin/dashboard',
    name: 'AdminDashboard',
    component: AdminDashboardComponent,
    meta: { requiresAuth: true, roles: ['admin', 'staff'] },
  },
  {
    path: '/admin/occupancy',
    name: 'AdminOccupancy',
    component: AdminOccupancyComponent,
    meta: { requiresAuth: true, roles: ['admin', 'staff'] },
  },
  {
    path: '/admin/bookings',
    name: 'AdminBookingComponent',
    component: AdminBookingComponent,
    meta: { requiresAuth: true, roles: ['admin', 'staff'] },
  },
  {
    path: '/admin/services',
    name: 'AdminServices',
    component: AdminServicesComponent,
    meta: { requiresAuth: true, roles: ['admin', 'staff'] },
  },
  {
    path: '/admin/amenities',
    name: 'AdminAmenities',
    component: AdminAmenitiesComponent,
    meta: { requiresAuth: true, roles: ['admin', 'staff'] },
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
  
  // --- ADMIN-ONLY ROUTES ---
  {
    path: '/admin/room-types',
    name: 'AdminRoomType',
    component: AdminRoomTypeComponent,
    meta: { requiresAdmin: true },
  },
  {
    path: '/admin/prices',
    name: 'AdminPrice',
    component: AdminPriceComponent,
    meta: { requiresAdmin: true },
  },
  {
    path: '/admin/rooms',
    name: 'AdminRoom',
    component: AdminRoomComponent,
    meta: { requiresAdmin: true },
  },
  {
    path: '/admin/users',
    name: 'AdminUsers',
    component: AdminUsersComponent,
    meta: { requiresAdmin: true },
  },
  {
    path: '/admin/booking-histories',
    name: 'AdminBookingHistory',
    component: AdminBookingHistory,
    meta: { requiresAdmin: true },
  },
  {
    path: '/admin/traningAI',
    name: 'traningAI',
    component: traningAI,
    meta: { requiresAdmin: true },
  },
   {
    path: '/admin/ChatAdmin',
    name: 'ChatAdmin',
    component: ChatAdmin,
    meta: { requiresAdmin: true },
  },

  // --- MISC & 404 ---
  { path: '/test', name: 'test', component: test },
  { path: '/:catchAll(.*)', name: 'NotFound', component: page404 },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

// Cuộn lên đầu trang khi chuyển trang
router.afterEach(() => {
  window.scrollTo(0, 0);
});

// Route Guard để kiểm tra quyền truy cập
router.beforeEach((to, from, next) => {
  const isAuthenticated = !!localStorage.getItem('tokenJwt');
  
  // Kiểm tra xem route có yêu cầu bất kỳ loại xác thực nào không
  const requiresAuth = to.matched.some(record => record.meta.requiresAuth || record.meta.requiresAdmin || record.meta.permission || record.meta.roles);

  // 1. Nếu route yêu cầu xác thực nhưng người dùng chưa đăng nhập
  if (requiresAuth && !isAuthenticated) {
    return next({ name: 'LoginComponent', query: { redirect: to.fullPath } });
  }
  
  // 2. Nếu người dùng đã đăng nhập, tiến hành kiểm tra quyền
  if (isAuthenticated) {
    const userInfo = JSON.parse(localStorage.getItem('userInfo') || '{}');
    const isAdmin = userInfo.role === 'admin';
    const isStaff = userInfo.role === 'staff';
    const userPermissions = userInfo.permissions || [];

    // 2.1. Kiểm tra vai trò (dành cho các route meta.roles)
    if (to.meta.roles && !to.meta.roles.includes(userInfo.role)) {
        // Nếu vai trò không được phép, về dashboard
        return next('/admin/dashboard');
    }

    // 2.2. Kiểm tra quyền Admin
    if (to.meta.requiresAdmin && !isAdmin) {
      // Nếu yêu cầu admin mà không phải admin -> về dashboard
      return next('/admin/dashboard'); 
    }

    // 2.3. Kiểm tra quyền cụ thể (permission)
    if (to.meta.permission) {
      // Admin luôn có quyền, cho qua
      if (isAdmin) {
        return next();
      }
      // Nếu không phải admin, kiểm tra trong mảng permissions
      if (!userPermissions.includes(to.meta.permission)) {
        // Không có quyền -> về dashboard
        return next('/admin/dashboard');
      }
    }
  }

  // 3. Nếu tất cả các kiểm tra đều qua, cho phép truy cập
  next();
});

export default router;