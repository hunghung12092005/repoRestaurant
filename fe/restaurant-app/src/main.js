import { createApp } from 'vue';
import App from './App.vue';
import router from './router'; // Import router
import 'bootstrap'; // Import Bootstrap JS
//import 'bootstrap/dist/css/bootstrap.min.css'; // Import Bootstrap CSS (bỏ comment nếu cần)
import axios from 'axios';
import './assets/style.css'; // Import CSS tùy chỉnh
// Ant Design
//import Antd from 'ant-design-vue';
import { QuillEditor } from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.snow.css';
// Flatpickr
import 'flatpickr/dist/flatpickr.css';
import * as bootstrap from 'bootstrap'
window.bootstrap = bootstrap

const app = createApp(App);

app
  .use(router) // Sử dụng router
  //.use(Antd)   // Sử dụng Ant Design Vue
  .component('QuillEditor', QuillEditor) // Đăng ký component QuillEditor
  .provide('apiUrl', 'http://localhost:8000/api')
  .mount('#app');