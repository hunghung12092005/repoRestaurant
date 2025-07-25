<template>
  <div class="page-container">
    <!-- Tiêu đề trang -->
    <div class="page-header mb-4">
      <h1 class="page-title">Lịch Sử Đặt Phòng</h1>
      <p class="page-subtitle">Tra cứu thông tin các lượt lưu trú đã hoàn thành của khách hàng.</p>
    </div>

    <!-- Bộ lọc và tìm kiếm -->
    <div class="card filter-card mb-4">
      <div class="card-body">
        <div class="row g-3 align-items-end">
          <div class="col-lg-4 col-md-12">
            <label for="search-input" class="form-label">Tìm kiếm khách hàng</label>
            <input 
              id="search-input"
              v-model="searchKeyword" 
              type="text" 
              class="form-control" 
              placeholder="Nhập tên, SĐT hoặc CCCD..."
              @keyup.enter="fetchBookings"
            >
          </div>
          <div class="col-lg-3 col-md-5">
            <label for="filter-date" class="form-label">Lọc theo ngày</label>
            <input 
              id="filter-date"
              type="date" 
              v-model="selectedDate" 
              class="form-control"
            />
          </div>
          <div class="col-lg-3 col-md-4">
             <button @click="fetchBookings" class="btn btn-primary w-100">
               <i class="bi bi-search me-2"></i>Tìm Kiếm
            </button>
          </div>
           <div class="col-lg-2 col-md-3">
             <button @click="clearFilters" class="btn btn-outline-secondary w-100">Xóa lọc</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Bảng danh sách lịch sử -->
    <div class="table-container">
      <table class="table booking-table align-middle">
        <thead>
          <tr>
            <th style="width: 5%;">STT</th>
            <th style="width: 30%;">Khách Hàng</th>
            <th class="text-center" style="width: 15%;">Phòng</th>
            <th class="text-center" style="width: 20%;">Loại Phòng</th>
            <th style="width: 30%;">Thời Gian Lưu Trú</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="allBookings.length === 0">
            <td colspan="5" class="text-center py-5">Không có lịch sử đặt phòng phù hợp.</td>
          </tr>
          <tr v-for="(booking, index) in allBookings" :key="booking.status_id">
            <td class="text-center">{{ totalItems - ((currentPage - 1) * perPage + index) }}</td>
            <td>
              <div class="fw-bold type-name">{{ booking.customer_name }}</div>
              <p class="description-text mb-1"><i class="bi bi-envelope-fill me-2"></i>{{ booking.customer_email || 'N/A' }}</p>
              <p class="description-text mb-1"><i class="bi bi-telephone-fill me-2"></i>{{ booking.customer_phone }}</p>
              <p class="description-text mb-0"><i class="bi bi-person-badge-fill me-2"></i>{{ booking.customer_id_number || 'N/A' }}</p>
            </td>
            <!-- [THAY ĐỔI] Bỏ badge, chỉ giữ lại fw-bold -->
            <td class="text-center">
              <span class="fw-bold">{{ booking.room_name }}</span>
            </td>
            <td class="text-center">
              <span>{{ booking.type_name }}</span>
            </td>
            <td>
              <div class="d-flex align-items-center mb-1">
                <i class="bi bi-box-arrow-in-right text-success me-2 fs-5"></i>
                <span><strong>Nhận phòng:</strong> {{ formatDate(booking.check_in) }}</span>
              </div>
              <div class="d-flex align-items-center">
                <i class="bi bi-box-arrow-left text-danger me-2 fs-5"></i>
                <span><strong>Trả phòng:</strong> {{ formatDate(booking.check_out) }}</span>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Phân trang -->
    <nav v-if="totalPages > 1" aria-label="Page navigation" class="mt-4">
      <ul class="pagination justify-content-center">
        <li class="page-item" :class="{ disabled: currentPage === 1 }"><a class="page-link" href="#" @click.prevent="chuyenTrang(1)">««</a></li>
        <li class="page-item" :class="{ disabled: currentPage === 1 }"><a class="page-link" href="#" @click.prevent="chuyenTrang(currentPage - 1)">«</a></li>
        <li v-for="page in pageRange" :key="page" class="page-item" :class="{ active: page === currentPage }"><a class="page-link" href="#" @click.prevent="chuyenTrang(page)">{{ page }}</a></li>
        <li class="page-item" :class="{ disabled: currentPage === totalPages }"><a class="page-link" href="#" @click.prevent="chuyenTrang(currentPage + 1)">»</a></li>
        <li class="page-item" :class="{ disabled: currentPage === totalPages }"><a class="page-link" href="#" @click.prevent="chuyenTrang(totalPages)">»»</a></li>
      </ul>
    </nav>
  </div>
</template>

<script setup>
// --- TOÀN BỘ SCRIPT CỦA BẠN GIỮ NGUYÊN ---
import { ref, onMounted, inject, computed } from 'vue';
import axios from 'axios';

const apiUrl = inject('apiUrl');
const selectedDate = ref(new Date().toISOString().substr(0, 10));
const allBookings = ref([]);
const currentPage = ref(1);
const totalPages = ref(1);
const perPage = 10;
const totalItems = ref(0);
const searchKeyword = ref('');

const formatDate = (date) => {
  if (!date) return '...';
  return new Date(date).toLocaleString('vi-VN', {
    year: 'numeric', month: '2-digit', day: '2-digit',
    hour: '2-digit', minute: '2-digit'
  });
};

const clearFilters = () => {
  selectedDate.value = '';
  searchKeyword.value = '';
  currentPage.value = 1;
  fetchBookings();
};

const fetchBookings = async () => {
  try {
    const params = {
        page: currentPage.value,
        per_page: perPage
    };
    if (selectedDate.value) params.date = selectedDate.value;
    if (searchKeyword.value.trim()) params.search = searchKeyword.value.trim();
    
    const res = await axios.get(`${apiUrl}/api/booking-histories`, { params });
    totalItems.value = res.data.total;

    allBookings.value = res.data.data.map(b => ({
      status_id: b.status_id,
      booking_id: b.booking_id,
      customer_name: b.customer?.customer_name || 'Chưa có',
      customer_phone: b.customer?.customer_phone || '',
      customer_email: b.customer?.customer_email || '',
      address: b.customer?.address || '',
      customer_id_number: b.customer?.customer_id_number || '',
      room_name: b.room?.room_name || '',
      type_name: b.room?.type_name || '',
      check_in: b.check_in,
      check_out: b.check_out
    }));

    totalPages.value = res.data.last_page;
    currentPage.value = res.data.current_page;
  } catch (err) {
    console.error(err);
    alert("Không thể tải lịch sử đặt phòng.");
  }
};

const pageRange = computed(() => {
  const maxPages = 5;
  const halfPages = Math.floor(maxPages / 2);
  let start = Math.max(1, currentPage.value - halfPages);
  let end = Math.min(totalPages.value, start + maxPages - 1);
  if (end - start + 1 < maxPages) {
    start = Math.max(1, end - maxPages + 1);
  }
  return Array.from({ length: end - start + 1 }, (_, i) => start + i);
});

const chuyenTrang = (page) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
        fetchBookings();
    }
}

onMounted(fetchBookings);
</script>

<style scoped>
/* Copied styles from other components for consistency */
@import url('https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700&display=swap');
@import url('https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css');

.page-container {
  font-family: 'Be Vietnam Pro', sans-serif;
  background-color: #f4f7f9;
  padding: 2rem;
  color: #34495e;
}
.page-header { border-bottom: 1px solid #e5eaee; padding-bottom: 1rem; }
.page-title { font-size: 2rem; font-weight: 700; }
.page-subtitle { font-size: 1rem; color: #7f8c8d; }

.filter-card { background-color: #ffffff; border: none; border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); }
.form-label { font-weight: 500; margin-bottom: 0.5rem; font-size: 0.875rem; }
.form-control, .form-select { border-radius: 8px; border: 1px solid #e5eaee; transition: all 0.2s ease-in-out; }
.form-control:focus, .form-select:focus { border-color: #3498db; box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.15); }

.table-container { background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); overflow-x: auto; }
.booking-table { font-size: 0.875rem; border-collapse: separate; border-spacing: 0; min-width: 900px; }
.booking-table thead th { background-color: #f8f9fa; color: #7f8c8d; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e5eaee; padding: 1rem; white-space: nowrap; }
.booking-table td { padding: 1rem; border-bottom: 1px solid #e5eaee; vertical-align: middle; }
.booking-table tbody tr:last-child td { border-bottom: none; }
.booking-table tbody tr:hover { background-color: #f9fafb; }
.type-name { font-size: 1rem; font-weight: 600; }
.description-text { font-size: 0.85rem; color: #7f8c8d; }

.pagination .page-link { border: none; border-radius: 8px; margin: 0 4px; color: #7f8c8d; font-weight: 600; }
.pagination .page-item.active .page-link { background-color: #3498db; color: white; }
</style>