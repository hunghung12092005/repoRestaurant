<template>
    <div>
        <h1>User Info</h1>
        <div v-if="userInfo">
            <p><strong>ID:</strong> {{ userInfo.id }}</p>
            <p><strong>Name:</strong> {{ userInfo.name }}</p>
            <p><strong>Role:</strong> {{ userInfo.role }}</p>
        </div>
        <div v-else>
            <p>Loading user info...</p>
        </div>
    </div>
</template>

<script>
import axiosConfig from '../axiosConfig'; // Import axiosConfig

export default {
    data() {
        return {
            userInfo: null,
        };
    },
    mounted() {
        this.fetchUserInfo();
    },
    methods: {
        fetchUserInfo() {
            axiosConfig.get('/api/protected')
                .then(response => {
                    //console.log('Response:', response.data); // Kiểm tra phản hồi
                    this.userInfo = response.data.user; // Lưu thông tin người dùng
                })
                .catch(error => {
                    console.error('Error:', error.response ? error.response.data : error.message);
                });
        }
    },
};
</script>