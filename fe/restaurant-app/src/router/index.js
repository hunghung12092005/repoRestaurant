import { createRouter, createWebHistory } from 'vue-router';
import App from '../App.vue';
import HomeComponent from '../components/HomeComponent.vue';
import AboutComponent from '../components/AboutComponent.vue';
import ContactComponent from '../components/ContactComponent.vue';
import ReservationComponent from '../components/booking/ReservationComponent.vue';
import BookRoomComponent from '../components/booking/BookRoomComponent.vue';
import BlogComponent from '../components/News/BlogComponent.vue';
import MenuComponent from '../components/menu_item/MenuComponent.vue';
import MenuListComponent from '../components/menu_item/MenuListComponent.vue';
import LoginComponent from '../components/Login.vue';
import TestJwtComponent from '../components/testTokenJwt.vue';
import BlogDetailComponent from '../components/News/BlogDetailComponent.vue';
import ProductDetailComponent from '../components/ProductDetailComponent.vue';

// import AdminDashboardComponent from '../components/AdminDashboardComponent.vue';
// import AdminStaffsComponent from '../components/AdminStaffsComponent.vue';
import AdminTableComponent from '../components/AdminTable/AdminTableComponent.vue';
import AdminTableTypeComponent from '../components/AdminTable/AdminTableTypeComponent.vue';

// AdmỉnRoom - Phòng
import AdminRoomTypeComponent from '../components/AdminRoom/AdminRoomTypeComponent.vue';
import AdminRoomComponent from '../components/AdminRoom/AdminRoomComponent.vue';
import AdminPriceComponent from '../components/AdminRoom/AdminPriceComponent.vue';
import AdminMenuCategoryComponent from '../components/AdminMenu/AdminMenuCategoryComponent.vue';
import AdminMenuComponent from '../components/AdminMenu/AdminMenuComponent.vue';

import AdminOccupancyComponent from '../components/AdminRoom/AdminOccupancy.vue';
// Admin Amenities and Services - Tiện ích và Dịch vụ
import AdminAmenitiesComponent from '../components/AdminSeAm/AdminAmenitiesComponent.vue';
import AdminServicesComponent from '../components/AdminSeAm/AdminServicesComponent.vue';

// AdminBooking - Đặt phòng

import AdminDashboardComponent from '../components/admin/AdminDashboardComponent.vue';
import AdminUsersComponent from '../components/admin/AdminUsersComponent.vue';
import Shop from '../components/Shop.vue';
import ghn from '../components/ghn/mainghn.vue';
import menu_online from '../components/ShopOnline/menu_online.vue';
import detailMenu from '../components/ShopOnline/detailMenu.vue';

import detailOrderMenu from '../components/menu_item/detailOrderMenu.vue';
import CategoryShopOnline from '../components/ShopOnline/CategoryShopOnline.vue';
import buynow from '../components/ShopOnline/BuyNow.vue';

import ChatAdmin from '../components/ChatAdmin.vue';
import Chat from '../components/Chat.vue';
import qrCodeCCCD from '../components/qrCodeCCCD.vue';
//đặt phòng
import rooms from '../components/booking_room/BookRoomComponent.vue'
import rooms2 from '../components/booking_room/Booking2.vue'
//thông tin user
import userprofile from '../components/user/userProfile.vue'
import forgotPass from '../components/user/forgotPass.vue'
//news
import AdminNewsComponent from '../components/admin/news/AdminNewsComponent.vue';
import AdminNewsCategoryComponent from '../components/admin/news/AdminNewsCategoryComponent.vue';
import AdminNewsCommentComponent from '../components/admin/news/AdminNewsCommentComponent.vue';
// AdminBookingComponent
import AdminBookingsComponent from '../components/AdminBooking/AdminBookingComponent.vue'
const routes = [
  {
    path: '/',
    name: 'HomeComponent',
    component: AboutComponent,
  },
  {
    path: '/about',
    name: 'AboutComponent',
    component: AboutComponent,
  },
  
  {
    path: '/contact',
    name: 'ContactComponent',
    component: ContactComponent,
  },
  {
    path: '/reservation',
    name: 'ReservationComponent',
    component: ReservationComponent,
  },
  {
    path: '/booking',
    name: 'BookRoomComponent',
    component: BookRoomComponent,
  },
  {
    path: '/shop',
    name: 'Shop',
    component: Shop,
  },
  {
    path: '/news', 
    name: 'NewsList',
    component: BlogComponent,
  },
  {
    path: '/menu',
    name: 'MenuComponent',
    component: MenuComponent,
  },
  {
    path: '/login',
    name: 'LoginComponent',
    component: LoginComponent,
  },
  {
    path: '/testJwt',
    name: 'TestJwtComponent',
    component: TestJwtComponent,
  },
  {
    path: '/news/:id', 
    name: 'NewsDetail', 
    component: BlogDetailComponent,
  },
  {
    path: '/product-detail',
    name: 'ProductDetailComponent',
    component: ProductDetailComponent,
  },
  {
    path: '/ghn',
    name: 'ghn',
    component: ghn,
  },
  {
    path: '/CategoryShopOnline',
    name: 'CategoryShopOnline',
    component: CategoryShopOnline,
  },
  {
    path: '/buynow',
    name: 'buynow',
    component: buynow,
  },
  {
    path: '/menu_online',
    name: 'menu_online',
    component: menu_online,
  },
  {
    path: '/detailMenu/:id',
    name: 'detailMenu',
    component: detailMenu,
    props: true,
  },
  {
    path: '/menu-list',
    name: 'MenuListComponent',
    component: MenuListComponent,
  },
 
  {
    path: '/detailOrderMenu/:ids',
    name: 'detailOrderMenu',
    component: detailOrderMenu,
    props: true,
  },
  {
    path: '/ChatAdmin',
    name: 'ChatAdmin',
    component: ChatAdmin,
  }, {
    path: '/Chat',
    name: 'Chat',
    component: Chat,
  },
  //qr code cccd
  {
    path: '/cccd',
    name: 'qrCodeCCCD',
    component: qrCodeCCCD,
  },
  // đặt phòng
  {
    path: '/rooms',
    name: 'rooms',
    component: rooms,
  },
  {
    path: '/rooms2',
    name: 'rooms2',
    component: rooms2,
  },
  //quản lý thông tin user
  {
    path: '/userprofile',
    name: 'userprofile',
    component: userprofile,
  },
  {
    path: '/forgotPass',
    name: 'forgotPass',
    component: forgotPass,
  },
  // Admin routes
  {
    path: '/admin',
    redirect: '/admin/dashboard',
  },
  {
    path: '/admin/dashboard',
    name: 'AdminDashboard',
    component: AdminDashboardComponent,
    meta: { requiresAdmin: true },
  },
  {
    path: '/admin/occupancy',
    name: 'AdminOccupancy',
    component: AdminOccupancyComponent,
    meta: { requiresAdmin: true },
  },
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
    path: '/admin/table-types',
    name: 'AdminTableType',
    component: AdminTableTypeComponent,
    meta: { requiresAdmin: true },
  },
  {
    path: '/admin/tables',
    name: 'AdminTable',
    component: AdminTableComponent,
    meta: { requiresAdmin: true },
  },
  {
    path: '/admin/menu-categories',
    name: 'AdminMenuCategory',
    component: AdminMenuCategoryComponent,
    meta: { requiresAdmin: true },
  },
  {
    path: '/admin/menus',
    name: 'AdminMenu',
    component: AdminMenuComponent,
    meta: { requiresAdmin: true },
  },
  
  {
    path: '/admin/services',
    name: 'AdminServices',
    component: AdminServicesComponent,
    meta: { requiresAdmin: true },
  },
  {
    path: '/admin/amenities',
    name: 'AdminAmenities',
    component: AdminAmenitiesComponent,
    meta: { requiresAdmin: true },
  },
  {
    path: '/admin/news',
    name: 'AdminNews',
    component: AdminNewsComponent,
    meta: { requiresAdmin: true },
  },
  {
    path: '/admin/news-categories',
    name: 'AdminNewsCategories',
    component: AdminNewsCategoryComponent,
    meta: { requiresAdmin: true },
  },
  {
    path: '/admin/news-comments',
    name: 'AdminNewsComments',
    component: AdminNewsCommentComponent,
    meta: { requiresAdmin: true },
  },
  
  {
    path: '/admin/users',
    name: 'AdminUsers',
    component: AdminUsersComponent,
    meta: { requiresAdmin: true },
  },
  {
    path: '/admin/bookings',
    name: 'AdminBookings',
    component: AdminBookingsComponent,
    meta: { requiresAdmin: true },
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

// Cuộn lên đầu trang khi chuyển trang
router.afterEach(() => {
  window.scrollTo(0, 0);
});

// Route Guard để kiểm tra quyền admin và staff
router.beforeEach((to, from, next) => {
  const userInfo = JSON.parse(localStorage.getItem('userInfo') || '{}');
  const isAdmin = userInfo.role === 'admin';
  const isStaff = userInfo.role === 'staff';
  const isAuthenticated = !!localStorage.getItem('tokenJwt');

  if (to.meta.requiresAdmin && (!isAuthenticated || !isAdmin)) {
    next('/login'); // Chuyển hướng về login nếu không phải admin
  } else if (to.meta.requiresStaff && (!isAuthenticated || !isStaff)) {
    next('/login'); // Chuyển hướng về login nếu không phải staff
  } else {
    next(); // Cho phép truy cập
  }
});

export default router;