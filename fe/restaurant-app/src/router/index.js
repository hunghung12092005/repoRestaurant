import { createRouter, createWebHistory } from 'vue-router';
import App from '../App.vue';
import HomeComponent from '../components/HomeComponent.vue';
import AboutComponent from '../components/AboutComponent.vue';
import ContactComponent from '../components/ContactComponent.vue';
import ReservationComponent from '../components/booking_table/ReservationComponent.vue';
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
import AdminRoomTypeComponent from '../components/AdminRoom/AdminRoomTypeComponent.vue';
import AdminSeasonComponent from '../components/AdminRoom/AdminSeasonComponent.vue';
import AdminRoomComponent from '../components/AdminRoom/AdminRoomComponent.vue';
import AdminMenuCategoryComponent from '../components/AdminMenu/AdminMenuCategoryComponent.vue';
import AdminMenuComponent from '../components/AdminMenu/AdminMenuComponent.vue';
import AdminBookingComponent from '../components/AdminBookingComponent.vue';


import AdminDashboardComponent from '../components/admin/AdminDashboardComponent.vue';
import AdminStaffsComponent from '../components/admin/AdminStaffsComponent.vue';
import ghn from '../components/ghn/mainghn.vue'
import menu_online from '../components/ShopOnline/menu_online.vue';
import detailMenu from '../components/ShopOnline/detailMenu.vue';

import detailOrderMenu from '../components/menu_item/detailOrderMenu.vue';
import CategoryShopOnline from '../components/ShopOnline/CategoryShopOnline.vue';
import buynow from '../components/ShopOnline/BuyNow.vue';
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
    path: '/blog',
    name: 'BlogComponent',
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
    path: '/blog-detail',
    name: 'BlogDetailComponent',
    component: BlogDetailComponent,
  },
  //bán hàng online
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
   ///props: { msg: 'Hello from Route!' } truyền ngay props vào component
  },
  {
    path: '/detailMenu/:id', // Định nghĩa route với param id
    name: 'detailMenu',
    component: detailMenu,
    props: true // Kích hoạt props để truyền param
  },
  //menu đặt món
  {
    path: '/menu-list',
    name: 'MenuListComponent',
    component: MenuListComponent,
  },
  {
    path: '/detailOrderMenu/:ids', // Định nghĩa route với param id
    name: 'detailOrderMenu',
    component: detailOrderMenu,
    props: true // Kích hoạt props để truyền param
  },
  // Admin routes
  {
    path: '/admin',
    redirect: '/admin/dashboard', // Chuyển hướng /admin đến /admin/dashboard
  },
  {
    path: '/admin/dashboard',
    name: 'AdminDashboard',
    component: AdminDashboardComponent,
    meta: { requiresAdmin: true },
  },
  {
    path: '/admin/staffs',
    name: 'AdminStaffs',
    component: AdminStaffsComponent,
    meta: { requiresAdmin: true },
  },
  {
    path: '/admin/room-types',
    name: 'AdminRoomType',
    component: AdminRoomTypeComponent,
    meta: { requiresAdmin: true },
  },
  {
    path: '/admin/seasons',
    name: 'AdminSeason',
    component: AdminSeasonComponent,
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
    path: '/admin/bookings',
    name: 'AdminBooking',
    component: AdminBookingComponent,
    meta: { requiresAdmin: true },
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});
//cuộn lên đầu trang khi chuyển trang
router.afterEach(() => {
  window.scrollTo(0, 0);
});
// Route Guard để kiểm tra quyền admin
router.beforeEach((to, from, next) => {
  const userInfo = JSON.parse(localStorage.getItem('userInfo') || '{}');
  const isAdmin = userInfo.role === 'admin';

  if (to.meta.requiresAdmin && !isAdmin) {
    next('/login'); // Chuyển hướng về login nếu không phải admin
  } else {
    next(); // Cho phép truy cập
  }
});

export default router;