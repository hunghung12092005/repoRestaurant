import { createApp } from 'vue';
import App from './App.vue';
import router from './router'; // Import router
import 'bootstrap'; // Import Bootstrap JS
//import 'bootstrap/dist/css/bootstrap.min.css'; // Import Bootstrap CSS
import axios from 'axios';
//import './assets/tailwind.css' // Thêm dòng này
import './assets/style.css'
createApp(App)
  .use(router) // Sử dụng router
  .mount('#app');