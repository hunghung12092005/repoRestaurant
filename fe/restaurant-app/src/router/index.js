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
    name: 'contact',
    component: contact,
  },
  {
    path: '/reservation',
    name: 'ReservationComponent',
    component: ReservationComponent,
  },
  {
    path: '/BlogComponent',
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
    name: 'testJwt',
    component: testJwt,
  },
  {
    path: '/blog',
    name: 'BlogDetailComponent',
    component: BlogDetailComponent,
  },
  {
    path: '/ProductDetailComponent',
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

export default router;