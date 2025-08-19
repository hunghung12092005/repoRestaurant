import { createRouter, createWebHistory } from 'vue-router';

// Import các components
import Home2 from '../components/Home2.vue';
import AboutComponent from '../components/AboutComponent.vue';
import ContactComponent from '../components/ContactComponent.vue';
import LoginComponent from '../components/Login.vue';
import page404 from '../components/404.vue';
// ... import các component public khác
import BlogComponent from '../components/News/BlogComponent.vue';
import BlogDetailComponent from '../components/News/BlogDetailComponent.vue';
import RoomTypeDetail from '../components/RoomTypeDetail.vue';
import rooms3 from '../components/booking_room/Booking3.vue'
import HistoryBooking from '../components/booking_room/HistoryBooking.vue';
import ThanksBooking from '../components/booking_room/ThanksBooking.vue';
import userprofile from '../components/user/userProfile.vue'
import forgotPass from '../components/user/forgotPass.vue'

// Import các component của Admin
import AdminDashboardComponent from '../components/admin/AdminDashboardComponent.vue';
import AdminOccupancyComponent from '../components/AdminRoom/AdminOccupancy.vue';
import AdminBookingComponent from '../components/AdminBooking/AdminBookingComponent.vue';
import AdminBookingHistory from '../components/AdminBooking/AdminBookingHistory.vue';
import AdminRoomTypeComponent from '../components/AdminRoom/AdminRoomTypeComponent.vue';
import AdminPriceComponent from '../components/AdminRoom/AdminPriceComponent.vue';
import AdminRoomComponent from '../components/AdminRoom/AdminRoomComponent.vue';
import AdminServicesComponent from '../components/AdminSeAm/AdminServicesComponent.vue';
import AdminAmenitiesComponent from '../components/AdminSeAm/AdminAmenitiesComponent.vue';
import coupons from '../components/admin/coupons/coupons.vue'
import AdminUsersComponent from '../components/admin/AdminUsersComponent.vue';
import StaffComponent from '../components/staff/StaffComponent.vue';
import CustomerComponent from '../components/admin/CustomerComponent.vue';
import AdminNewsComponent from '../components/admin/news/AdminNewsComponent.vue';
import AdminNewsCategoryComponent from '../components/admin/news/AdminNewsCategoryComponent.vue';
import AdminNewsCommentComponent from '../components/admin/news/AdminNewsCommentComponent.vue';
import AdminContacts from '../components/admin/AdminContacts.vue';
import traningAI from '../components/admin/AI/traningAI.vue';
import ChatAdmin from '../components/ChatAdmin.vue';
import AdminNotifications from '../components/admin/AdminNotifications.vue';
import AdminAuditLogComponent from '../components/admin/AdminAuditLog.vue';

// <-- THÊM MỚI: Import các component quản lý quyền
import AdminRolesComponent from '../components/admin/AdminRolesComponent.vue';
// import AdminPermissionsComponent from '../components/admin/AdminPermissionsComponent.vue';
import AdminEvaluate from '../components/admin/AdminEvaluate.vue';

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
  { path: '/admin/dashboard', name: 'AdminDashboard', component: AdminDashboardComponent, meta: { requiresAuth: true } },
  { path: '/admin/occupancy', name: 'AdminOccupancy', component: AdminOccupancyComponent, meta: { requiresAuth: true, permission: 'manage_bookings' } },
  { path: '/admin/bookings', name: 'AdminBookingComponent', component: AdminBookingComponent, meta: { requiresAuth: true, permission: 'manage_bookings' } },
  { path: '/admin/booking-histories', name: 'AdminBookingHistory', component: AdminBookingHistory, meta: { requiresAuth: true, permission: 'manage_reports' } },
  { path: '/admin/room-types', name: 'AdminRoomType', component: AdminRoomTypeComponent, meta: { requiresAuth: true, permission: 'manage_rooms' } },
  { path: '/admin/prices', name: 'AdminPrice', component: AdminPriceComponent, meta: { requiresAuth: true, permission: 'manage_prices' } },
  { path: '/admin/rooms', name: 'AdminRoom', component: AdminRoomComponent, meta: { requiresAuth: true, permission: 'manage_rooms' } },
  { path: '/admin/services', name: 'AdminServices', component: AdminServicesComponent, meta: { requiresAuth: true, permission: 'manage_services' } },
  { path: '/admin/amenities', name: 'AdminAmenities', component: AdminAmenitiesComponent, meta: { requiresAuth: true, permission: 'manage_amenities' } },
  { path: '/admin/coupons', name: 'coupons', component: coupons, meta: { requiresAuth: true, permission: 'manage_coupons' } },
  { path: '/admin/news', name: 'AdminNews', component: AdminNewsComponent, meta: { requiresAuth: true, permission: 'manage_news' } },
  { path: '/admin/news-categories', name: 'AdminNewsCategories', component: AdminNewsCategoryComponent, meta: { requiresAuth: true, permission: 'manage_news' } },
  { path: '/admin/news-comments', name: 'AdminNewsComments', component: AdminNewsCommentComponent, meta: { requiresAuth: true, permission: 'manage_news' } },
  { path: '/admin/contacts', name: 'AdminContacts', component: AdminContacts, meta: { requiresAuth: true, permission: 'manage_contacts' } },
  { path: '/admin/staffs', name: 'StaffComponent', component: StaffComponent, meta: { requiresAuth: true, permission: 'manage_staff' } },
  { path: '/admin/customers', name: 'CustomerComponent', component: CustomerComponent, meta: { requiresAuth: true, permission: 'manage_customers' } }, // Giả sử có quyền này
  { path: '/admin/traningAI', name: 'traningAI', component: traningAI, meta: { requiresAuth: true, permission: 'manage_ai_training' } },
  { path: '/admin/ChatAdmin', name: 'ChatAdmin', component: ChatAdmin, meta: { requiresAuth: true, permission: 'manage_admin_chat' } },
  { path: '/admin/notifications', name: 'AdminNotifications', component: AdminNotifications, meta: { requiresAuth: true } },
  { path: '/admin/audit-log', name: 'AdminAuditLog', component: AdminAuditLogComponent, meta: { requiresAuth: true, permission: 'manage_reports' } },

  // <-- THÊM MỚI: Routes cho quản lý tài khoản và phân quyền
  { path: '/admin/users', name: 'AdminUsers', component: AdminUsersComponent, meta: { requiresAuth: true, permission: 'manage_users' } },
  { path: '/admin/roles', name: 'AdminRoles', component: AdminRolesComponent, meta: { requiresAuth: true, permission: 'manage_users' } },
  { path: '/admin/evaluate', name: 'AdminEvaluate', component: AdminEvaluate, meta: { requiresAuth: true, permission: 'manage_evaluate' } },
  // { path: '/admin/permissions', name: 'AdminPermissions', component: AdminPermissionsComponent, meta: { requiresAuth: true, permission: 'manage_users' } },
 
  // --- MISC & 404 ---
  { path: '/:catchAll(.*)', name: 'NotFound', component: page404 },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.afterEach(() => {
  window.scrollTo(0, 0);
});

// <-- SỬA ĐỔI QUAN TRỌNG: Cập nhật toàn bộ Route Guard
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
      localStorage.removeItem('tokenJwt');
      localStorage.removeItem('userInfo');
      return next({ name: 'LoginComponent' });
    }

    // Lấy tên vai trò và danh sách quyền từ cấu trúc mới
    const userRoleName = userInfo.role?.name;
    const userPermissions = userInfo.role?.permissions || [];

    // Người dùng là 'admin' có toàn quyền truy cập
    if (userRoleName === 'admin') {
      return next();
    }
    
    // Kiểm tra quyền hạn yêu cầu (permission)
    const requiredPermission = to.meta.permission;
    if (requiredPermission) {
      // Kiểm tra xem trong mảng permissions object có object nào có 'name' trùng với quyền yêu cầu không
      const hasPermission = userPermissions.some(p => p.name === requiredPermission);
      if (!hasPermission) {
        // Nếu không có quyền, chuyển hướng về dashboard
        // alert('Bạn không có quyền truy cập chức năng này.');
        return next('/admin/dashboard');
      }
    }
  }

  // 3. Mọi trường hợp khác, cho phép truy cập
  next();
});

export default router;