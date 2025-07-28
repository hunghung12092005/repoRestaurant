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
  .use(router)
  //.use(Antd)
  .component('QuillEditor', QuillEditor)
  //.provide('apiUrl', 'http://127.0.0.1:8000');

// 🔻 Thêm đoạn xử lý lỗi ở đây
app.config.errorHandler = (err, instance, info) => {
  console.error('Vue error:', err);
  console.error('In component:', instance);
  console.error('Info:', info);

  // Ví dụ: Hiển thị thông báo
  //alert('Đã có lỗi xảy ra. Vui lòng thử lại.');
};

app.mount('#app');
