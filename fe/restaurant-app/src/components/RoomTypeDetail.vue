<template>
  <div class="container mt-5 pt-5">
    <div v-if="loading" class="text-center">
      <div class="spinner-border" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
    </div>
    <div v-else-if="roomType" class="card">
      <div class="card-header">
        <h1>{{ roomType.type_name }}</h1>
      </div>
      <div class="card-body">
        <p class="card-text">{{ roomType.description }}</p>
        <hr>
        <h5><i class="bi bi-info-circle-fill"></i> Thông tin chi tiết</h5>
        <ul class="list-group list-group-flush">
          <li class="list-group-item"><strong>Số giường:</strong> {{ roomType.bed_count }}</li>
          <li class="list-group-item"><strong>Số người tối đa:</strong> {{ roomType.max_occupancy }}</li>
        </ul>
        
        <div class="row mt-4">
          <div class="col-md-6">
            <h5><i class="bi bi-gem"></i> Tiện nghi</h5>
            <ul v-if="roomType.amenities && roomType.amenities.length > 0" class="list-unstyled">
              <li v-for="amenity in roomType.amenities" :key="amenity.amenity_id">
                <i class="bi bi-check-circle-fill text-success"></i> {{ amenity.name }}
              </li>
            </ul>
            <p v-else>Không có thông tin tiện nghi.</p>
          </div>
          <div class="col-md-6">
            <h5><i class="bi bi-patch-check-fill"></i> Dịch vụ đi kèm</h5>
            <ul v-if="roomType.services && roomType.services.length > 0" class="list-unstyled">
              <li v-for="service in roomType.services" :key="service.service_id">
                <i class="bi bi-check-circle-fill text-success"></i> {{ service.name }}
              </li>
            </ul>
             <p v-else>Không có thông tin dịch vụ.</p>
          </div>
        </div>
      </div>
      <div class="card-footer text-center">
          <router-link to="/rooms2" class="btn btn-primary">Đặt phòng ngay</router-link>
      </div>
    </div>
    <div v-else class="alert alert-danger">
      Không tìm thấy thông tin loại phòng.
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, inject, watch } from 'vue';
import { useRoute } from 'vue-router';
import axiosConfig from '../axiosConfig.js';

const route = useRoute();
const roomType = ref(null);
const loading = ref(true);
const apiUrl = inject('apiUrl');

const fetchRoomTypeDetails = async () => {
  loading.value = true;
  const typeId = route.params.id;
  
  if (!typeId) {
    loading.value = false;
    return;
  }

  try {
    const response = await axiosConfig.get(`${apiUrl}/api/room-types`);
    if (response.data.status) {
      const foundRoomType = response.data.data.find(rt => rt.type_id == typeId);
      roomType.value = foundRoomType || null;
    }
  } catch (error) {
    console.error(`Lỗi khi lấy chi tiết loại phòng ${typeId}:`, error);
    roomType.value = null;
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchRoomTypeDetails();
});

watch(() => route.params.id, (newId) => {
  if (newId) {
    fetchRoomTypeDetails();
  }
});
</script>

<style scoped>
.container {
  padding-bottom: 2rem;
  padding-top: 5rem !important; /* Thêm khoảng đệm cho navbar fixed */
}
.card {
    margin-top: 2rem;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
.card-header h1 {
    margin: 0;
    color: #081B54; /* Sử dụng màu cụ thể thay vì var() để đảm bảo hoạt động */
}
.btn-primary {
    background-color: #16B978;
    border-color: #16B978;
}
.btn-primary:hover {
    opacity: 0.9;
}
.list-unstyled i {
    margin-right: 8px;
}
</style>