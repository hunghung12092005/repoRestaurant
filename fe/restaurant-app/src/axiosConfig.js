// axiosConfig.js
import axios from 'axios';

const axiosInstance = axios.create({
    baseURL: 'http://127.0.0.1:8000',
    headers: {
        'Accept': 'application/json',
    }
});

// Cấu hình Axios để gửi token trong header
axiosInstance.interceptors.request.use(
    (config) => {
        const token = localStorage.getItem('tokenJwt');
        if (token) {
            // Đảm bảo rằng header Authorization được đặt đúng
            config.headers['Authorization'] = `Bearer ${token}`;
        }
        return config;
    },
    (error) => {
        // Xử lý lỗi request
        return Promise.reject(error);
    }
);

// 3. Export instance đã được cấu hình
export default axiosInstance;