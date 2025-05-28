// axiosConfig.js
import axios from 'axios';

// Cấu hình Axios để gửi token trong header
axios.interceptors.request.use(config => {
    const token = localStorage.getItem('tokenJwt');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

export default axios;