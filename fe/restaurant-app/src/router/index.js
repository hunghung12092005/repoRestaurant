import { createRouter, createWebHistory } from 'vue-router';
import App from '../App.vue';
import HomeComponent from '../components/HomeComponent.vue';
import AboutComponent from '../components/AboutComponent.vue';
import ContactComponent from '../components/ContactComponent.vue';
import ReservationComponent from '../components/booking_table/ReservationComponent.vue';
import BlogComponent from '../components/BlogComponent.vue';
import MenuComponent from '../components/menu_item/MenuComponent.vue';
import MenuListComponent from '../components/menu_item/MenuListComponent.vue';
import LoginComponent from '../components/Login.vue';
import TestJwtComponent from '../components/testTokenJwt.vue';
import BlogDetailComponent from '../components/BlogDetailComponent.vue';
import ProductDetailComponent from '../components/ProductDetailComponent.vue';

import AdminDashboardComponent from '../components/AdminDashboardComponent.vue';
import AdminStaffsComponent from '../components/AdminStaffsComponent.vue';
import AdminRoomComponent from '../components/AdminRoomComponent.vue';
import AdminBookingComponent from '../components/AdminBookingComponent.vue';


import AdminDashboardComponent from '../components/admin/AdminDashboardComponent.vue';
import AdminStaffsComponent from '../components/admin/AdminStaffsComponent.vue';
import ghn from '../components/ghn/mainghn.vue'
import menu_online from '../components/ShopOnline/menu_online.vue';
import detailMenu from '../components/ShopOnline/detailMenu.vue';

const routes = [
  {
    path: '/',
    name: 'HomeComponent',
    component: HomeComponent,
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
    path: '/menu-list',
    name: 'MenuListComponent',
    component: MenuListComponent,
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
    path: '/admin/rooms',
    name: 'AdminRoom',
    component: AdminRoomComponent,
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