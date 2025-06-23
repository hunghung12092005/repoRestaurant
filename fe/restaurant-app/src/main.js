import { createApp } from 'vue';
import App from './App.vue';
import router from './router'; // Import router
import 'bootstrap'; // Import Bootstrap JS
//import 'bootstrap/dist/css/bootstrap.min.css'; // Import Bootstrap CSS
import axios from 'axios';
//import './assets/tailwind.css' // Thêm dòng này
import './assets/style.css'
//news
import { QuillEditor } from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.snow.css';
import 'flatpickr/dist/flatpickr.css';
createApp(App)
  .use(router) // Sử dụng router
  .component('QuillEditor', QuillEditor)
  .mount('#app');