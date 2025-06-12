import { createRouter, createWebHistory } from 'vue-router';
import App from '../App.vue';
import HomeComponent from '../components/HomeComponent.vue';
import AboutComponent from '../components/AboutComponent.vue';
import ContactComponent from '../components/ContactComponent.vue';
import ReservationComponent from '../components/booking/ReservationComponent.vue';
import BlogComponent from '../components/News/BlogComponent.vue';
import MenuComponent from '../components/menu_item/MenuComponent.vue';
import MenuListComponent from '../components/menu_item/MenuListComponent.vue';
import LoginComponent from '../components/Login.vue';
import TestJwtComponent from '../components/testTokenJwt.vue';
import BlogDetailComponent from '../components/News/BlogDetailComponent.vue';
import ProductDetailComponent from '../components/ProductDetailComponent.vue';
import AdminDashboardComponent from '../components/admin/AdminDashboardComponent.vue';
import AdminStaffsComponent from '../components/admin/AdminStaffsComponent.vue';
import AdminDepartmentsComponent from '../components/admin/AdminDepartmentsComponent.vue';
import AdminNewsComponent from '../components/admin/news/AdminNewsComponent.vue';
import AdminNewsCategoryComponent from '../components/admin/news/AdminNewsCategoryComponent.vue';
import AdminNewsCommentComponent from '../components/admin/news/AdminNewsCommentComponent.vue';
import Shop from '../components/Shop.vue';
import ghn from '../components/ghn/mainghn.vue';
import menu_online from '../components/ShopOnline/menu_online.vue';
import detailMenu from '../components/ShopOnline/detailMenu.vue';
import detailOrderMenu from '../components/menu_item/detailOrderMenu.vue';
import CategoryShopOnline from '../components/ShopOnline/CategoryShopOnline.vue';
import buynow from '../components/ShopOnline/BuyNow.vue';
import StaffDashboardComponent from '../components/staff/StaffDashboardComponent.vue';

import ChatAdmin from '../components/ChatAdmin.vue';
import Chat from '../components/Chat.vue';
import qrCodeCCCD from '../components/qrCodeCCCD.vue';
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
    path: '/shop',
    name: 'Shop',
    component: Shop,
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
    path: '/admin/staffs',
    name: 'AdminStaffs',
    component: AdminStaffsComponent,
    meta: { requiresAdmin: true },
  },
  {
    path: '/admin/departments',
    name: 'AdminDepartments',
    component: AdminDepartmentsComponent,
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
  // Staff routes
  {
    path: '/staff/dashboard',
    name: 'StaffDashboard',
    component: StaffDashboardComponent,
    meta: { requiresStaff: true },
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