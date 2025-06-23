<template>
  <div class="occupancy-page">
    <div class="header">
      <h1 class="title">Quản lý Phòng</h1>
      <div class="header-link">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-house-door-fill" viewBox="0 0 16 16">
          <path d="M6.5 14.5v-3.505c0-.245.25-.495.5-.495h2c.25 0 .5.25.5.495v3.505A.5.5 0 0 0 10 15h2a.5.5 0 0 0 .5-.5V8.207l.646.647a.5.5 0 0 0 .708-.708L8.354 1.146a.5.5 0 0 0-.708 0l-6 6a.5.5 0 0 0 .708.708L2.5 8.207V14.5a.5.5 0 0 0 .5.5h2a.5.5 0 0 0 .5-.5z"/>
        </svg>
        <span>Quản lý phòng</span>
      </div>
    </div>

    <div class="filter-container">
      <div class="filter-group">
        <label for="status">Trạng thái:</label>
        <select id="status" v-model="selectedStatus">
          <option>Tất cả</option>
          <option>Còn trống</option>
          <option>Đã đặt</option>
        </select>
      </div>
      <div class="filter-group">
        <label for="room-type">Loại phòng:</label>
        <select id="room-type" v-model="selectedRoomType">
          <option v-for="type in roomTypes" :key="type" :value="type">{{ type }}</option>
        </select>
      </div>
      <div class="filter-group">
        <label for="floor">Tầng:</label>
        <select id="floor" v-model="selectedFloor">
          <option v-for="floor in floors" :key="floor" :value="floor">{{ floor }}</option>
        </select>
      </div>
      <button @click="clearFilters" class="clear-btn">Xóa lọc</button>
    </div>

    <div v-if="loading" class="loading-state">Đang tải danh sách phòng...</div>
    
    <div v-else>
      <div v-for="floorGroup in groupedAndSortedRooms" :key="floorGroup.floor" class="floor-section">
        
        <div class="floor-header-container">
          <h2 class="floor-header-text">Tầng {{ floorGroup.floor }}</h2>
          <div class="floor-header-line"></div>
        </div>
        
        <div class="room-grid">
          <div v-for="room in floorGroup.rooms" :key="room.room_id" class="room-card">
            <div :class="['status-badge', room.status === 'Đã đặt' ? 'booked' : 'available']">
              {{ room.status }}
            </div>
            
            <p class="room-label">Phòng</p>
            <h2 class="room-number">{{ room.number }}</h2>
            
            <h3 class="room-type">{{ room.type }}</h3>
            <p class="bed-size">{{ room.bedSize }}</p>

            <a href="#" class="action-link" v-if="room.status === 'Đã đặt'" @click.prevent="showGuestDetails(room)">
              Chi tiết khách
            </a>
            <a href="#" class="action-link" v-else @click.prevent="showAddGuest(room)">
              Thêm khách
            </a>
          </div>
        </div>
      </div>
      <div v-if="filteredRooms.length === 0" class="no-results">
        Không có phòng nào khớp với bộ lọc hiện tại.
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import axios from 'axios';
// Các import khác không thay đổi
import { useRouter } from 'vue-router';
import { inject } from 'vue';
import { Modal } from 'bootstrap';

const allRooms = ref([]);
const loading = ref(true);

const selectedStatus = ref('Tất cả');
const selectedRoomType = ref('Tất cả');
const selectedFloor = ref('Tất cả');

// Các hàm tiện ích để chuyển đổi dữ liệu từ API sang định dạng hiển thị
const mapApiStatusToVietnamese = (status) => {
  if (status === 'available') return 'Còn trống';
  if (status === 'occupied') return 'Đã đặt';
  return 'Không xác định'; // Mặc định
};

const mapBedCountToString = (count) => {
  if (count === 1) return 'Giường đơn';
  if (count === 2) return 'Giường đôi';
  if (count === 3) return '3 giường';
  return `${count} giường`;
};

// Hàm fetch dữ liệu chính (đã được tối ưu)
const fetchRooms = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/api/occupancy/rooms');
    
    // Giả định rằng API luôn trả về một mảng JSON trực tiếp
    // Nếu API Laravel của bạn trả về { "data": [...] }, hãy dùng `const roomsData = response.data.data;`
    const roomsData = response.data;

    if (!Array.isArray(roomsData)) {
      console.error("Dữ liệu từ API không phải là một mảng!", roomsData);
      throw new Error("Invalid data format from API.");
    }

    allRooms.value = roomsData.map(apiRoom => ({
      room_id: apiRoom.room_id,
      number: apiRoom.room_name,
      floor: apiRoom.floor_number,
      status: mapApiStatusToVietnamese(apiRoom.status),
      type: apiRoom.type_name,
      bedSize: mapBedCountToString(apiRoom.bed_count),
    }));

  } catch (error) {
    console.error("Lỗi khi tải dữ liệu phòng:", error);
    // Ở đây bạn có thể thêm logic để hiển thị thông báo lỗi cho người dùng
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchRooms(); // Gọi hàm fetch dữ liệu khi component được gắn vào
});

// Các computed property và methods còn lại không cần thay đổi
const roomTypes = computed(() => {
  const types = new Set(allRooms.value.map(room => room.type));
  return ['Tất cả', ...Array.from(types).sort()];
});

const floors = computed(() => {
    const floorNumbers = new Set(allRooms.value.map(room => room.floor));
    const sortedFloors = Array.from(floorNumbers).sort((a,b) => a - b);
    return ['Tất cả', ...sortedFloors.map(f => `Tầng ${f}`)];
});

const filteredRooms = computed(() => {
  return allRooms.value.filter(room => {
    const statusMatch = selectedStatus.value === 'Tất cả' || room.status === selectedStatus.value;
    const typeMatch = selectedRoomType.value === 'Tất cả' || room.type === selectedRoomType.value;
    const floorMatch = selectedFloor.value === 'Tất cả' || `Tầng ${room.floor}` === selectedFloor.value;
    return statusMatch && typeMatch && floorMatch;
  });
});

const groupedAndSortedRooms = computed(() => {
  const grouped = filteredRooms.value.reduce((acc, room) => {
    const floor = room.floor;
    if (!acc[floor]) acc[floor] = [];
    acc[floor].push(room);
    return acc;
  }, {});

  return Object.keys(grouped)
    .sort((a, b) => Number(a) - Number(b))
    .map(floorKey => ({
      floor: floorKey,
      rooms: grouped[floorKey]
    }));
});

const clearFilters = () => {
  selectedStatus.value = 'Tất cả';
  selectedRoomType.value = 'Tất cả';
  selectedFloor.value = 'Tất cả';
};

const showGuestDetails = (room) => { alert(`Hiển thị chi tiết khách tại Phòng ${room.number}.`); };
const showAddGuest = (room) => { alert(`Mở biểu mẫu thêm khách vào Phòng ${room.number}.`); };

watch([selectedStatus, selectedRoomType, selectedFloor], (newValues) => {
  console.log('Bộ lọc đã thay đổi:', newValues);
});
</script>

<style scoped>
/* CSS giữ nguyên như phiên bản trước */
.occupancy-page {
  font-family: 'Be Vietnam Pro', sans-serif;
  /* background-color: #f0f2f5; */
  padding: 24px; 
  color: #333;
}
.header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
.title { font-size: 28px; font-weight: 600; }
.header-link { display: flex; align-items: center; gap: 8px; color: #888; }
.filter-container {
  background-color: #ffffff; padding: 20px; border-radius: 8px; display: flex; align-items: flex-end;
  gap: 24px; margin-bottom: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}
.filter-group { position: relative; margin-top: 10px; }
.filter-group label {
  position: absolute; top: -8px; left: 10px; font-size: 12px; color: #888;
  background-color: #ffffff; padding: 0 4px; z-index: 1;
}
.filter-group select {
  padding: 10px 12px; border: 1px solid #adb5bd; border-radius: 6px; min-width: 180px;
  background-color: #fff; font-size: 14px; -webkit-appearance: none; -moz-appearance: none; appearance: none;
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
  background-repeat: no-repeat; background-position: right 12px center; background-size: 16px 12px;
  transition: border-color 0.2s, box-shadow 0.2s; outline: none;
}
.filter-group select:focus { border-color: #6c5ffc; box-shadow: 0 0 0 3px rgba(108, 95, 252, 0.2); }
.clear-btn {
  background-color: #6c5ffc; color: white; border: none; padding: 10px 24px; border-radius: 6px;
  cursor: pointer; font-weight: 500; height: 42px;
}
.loading-state, .no-results { text-align: center; padding: 50px; font-size: 18px; color: #888; }

.floor-section:not(:last-child) {
  margin-bottom: 40px; 
}

.floor-header-container {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-bottom: 20px;
}
.floor-header-text {
  font-size: 33px;
  font-weight: 600;
  color: #333;
  white-space: nowrap; 
}
.floor-header-line {
  flex-grow: 1; 
  height: 3px;
  background-color: #020202; 
}

.room-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 16px; }
.room-card {
  background-color: #ffffff; border: 1px solid #000000; border-radius: 12px;
  padding: 16px; position: relative; transition: box-shadow 0.3s, transform 0.3s;
}
.room-card:hover { box-shadow: 0 4px 15px rgba(0,0,0,0.1); transform: translateY(-5px); }
.status-badge {
  position: absolute; top: 0; right: 0; padding: 3px 10px; border-radius: 0 12px;
  font-size: 12px; font-weight: 500; color: #ffffff;
}
.status-badge.booked { background-color: #FFECDC; color: #FD8220 ; font-weight: bold;}
.status-badge.available { background-color: #DDEDE6; color: #66B6AA; font-weight: bold;}
.room-label { font-size: 13px; color: #888; margin-bottom: 0; }
.room-number { font-size: 36px; font-weight: 700; margin: -5px 0 12px 0; }
.room-type { font-size: 16px; font-weight: 600; margin-bottom: 4px; }
.bed-size { 
  font-size: 13px; 
  color: #888; 
  margin: 2px 0 20px 0;
}

.action-link { color: #6c5ffc; text-decoration: none; font-weight: 600; font-size: 14px; }
.action-link:hover { text-decoration: underline; }
</style>