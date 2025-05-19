
import { createApp } from 'vue';
import { createRouter, createWebHistory } from 'vue-router';
import App from './components/App.vue';
import HomeComponent from './components/HomeComponent.vue';
import AboutComponent from './components/AboutComponent.vue';
import ProductComponent from './components/ProductComponent.vue';
import ContactComponent from './components/ContactComponent.vue';
import ReservationComponent from './components/ReservationComponent.vue';
import BlogComponent from './components/BlogComponent.vue';
import MenuComponent from './components/MenuComponent.vue';
import BlogDetailComponent from './components/BlogDetailComponent.vue';
import ProductDetailComponent from './components/ProductDetailComponent.vue';
import AdminDashboardComponent from './components/AdminDashboardComponent.vue';

import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap';

// Cấu hình router
const routes = [
    { path: '/', component: HomeComponent },
    { path: '/about', component: AboutComponent },
    { path: '/products', component: ProductComponent },
    { path: '/blog', component: BlogComponent },
    { path: '/menu', component: MenuComponent },
    { path: '/blog-detail', component: BlogDetailComponent },
    { path: '/product-detail', component: ProductDetailComponent },
    { path: '/contact', component: ContactComponent },
    { path: '/reservation', component: ReservationComponent },
    { path: '/admin-dashboard', component: AdminDashboardComponent },
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

// Tạo ứng dụng Vue
const app = createApp(App);

// Sử dụng router
app.use(router);

// Mount ứng dụng
app.mount('#app');
