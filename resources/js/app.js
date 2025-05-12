import './bootstrap';
import { createApp } from 'vue';
import { createRouter, createWebHistory } from 'vue-router';
import App from './components/App.vue';
import HomeComponent from './components/HomeComponent.vue';
import AboutComponent from './components/AboutComponent.vue';
import ProductComponent from './components/ProductComponent.vue';
import ContactComponent from './components/ContactComponent.vue';



// Cấu hình router
const routes = [
    { path: '/', component: HomeComponent },
    { path: '/about', component: AboutComponent },
    { path: '/products', component: ProductComponent },
    { path: '/contact', component: ContactComponent },
];

const router = createRouter({
    history: createWebHistory(), // Sử dụng history mode
    routes
});

// Tạo ứng dụng Vue
const app = createApp(App);

// Sử dụng router
app.use(router);

// Mount ứng dụng
app.mount('#app');