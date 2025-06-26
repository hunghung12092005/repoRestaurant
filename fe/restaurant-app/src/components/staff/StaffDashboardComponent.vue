<template>
  <div class="staff-dashboard container py-4">
    <h1 class="mb-4">Staff Dashboard</h1>
    <div class="card">
      <div class="card-body">
        <h5 class="card-title">Chào mừng, {{ userInfo.name }}!</h5>
        <p class="card-text">Đây là dashboard dành cho nhân viên. Bạn có thể xem công việc, lịch làm việc, hoặc quản lý đơn hàng tại đây.</p>
        <div class="row">
          <div class="col-md-6">
            <div class="card mb-3">
              <div class="card-body">
                <h6>Công việc hôm nay</h6>
                <p>Chưa có công việc được giao.</p>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="card mb-3">
              <div class="card-body">
                <h6>Lịch làm việc</h6>
                <p>Chưa có lịch làm việc.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, inject, onMounted } from 'vue';
import axiosConfig from '../../axiosConfig.js';

const apiUrl = inject('apiUrl');
const userInfo = ref({});

const fetchUserInfo = async () => {
  const token = localStorage.getItem('tokenJwt');
  if (!token) return;

  try {
    const response = await axiosConfig.get(`${apiUrl}/api/protected`);
    userInfo.value = response.data.user;
  } catch (error) {
    console.error('Error fetching user info:', error.response ? error.response.data : error.message);
  }
};

onMounted(() => {
  fetchUserInfo();
});
</script>

<style scoped>
.staff-dashboard {
  max-width: 1200px;
  margin: 0 auto;
}

.card {
  border-radius: 8px;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.card-title {
  color: #16B978;
}

.card-body h6 {
  font-weight: 600;
  margin-bottom: 10px;
}
</style>