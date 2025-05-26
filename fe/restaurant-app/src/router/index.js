import { createRouter, createWebHistory } from 'vue-router';
import App from '../App.vue';
import HomeComponent from '../components/HomeComponent.vue'
import AboutComponent from '../components/AboutComponent.vue'
import contact from '../components/ContactComponent.vue'
import ReservationComponent from '../components/ReservationComponent.vue'
import BlogComponent from '../components/BlogComponent.vue'
import MenuComponent from '../components/MenuComponent.vue'
import MenuListComponent from '../components/MenuListComponent.vue'
import LoginComponent from '../components/Login.vue'
import testJwt from '../components/testTokenJwt.vue'
import BlogDetailComponent from '../components/BlogDetailComponent.vue'
import ProductDetailComponent from '../components/ProductDetailComponent.vue'
import AdminDashboardComponent from '../components/AdminDashboardComponent.vue'
import mainghn from '../components/ghn/mainghn.vue';
import shopOnline from '../components/ShopOnline/menu_online.vue';
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
    name: 'mainghn',
    component: mainghn,
  },
  {
    path: '/shop-online',
    name: 'shop-online',
    component: shopOnline,
  },
  // Thêm các route khác ở đây
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