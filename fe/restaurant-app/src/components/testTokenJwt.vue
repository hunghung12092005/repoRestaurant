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

<script setup>
import { ref, onMounted, inject } from 'vue';
import axiosConfig from '../axiosConfig';

const userInfo = ref(null);
const apiUrl = inject('apiUrl');
const fetchUserInfo = () => {
    axiosConfig.get(`${apiUrl}/api/protected`)
        .then(response => {
            userInfo.value = response.data.user;
        })
        .catch(error => {
            console.error('Error:', error.response ? error.response.data : error.message);
        });
};

onMounted(fetchUserInfo);
</script>