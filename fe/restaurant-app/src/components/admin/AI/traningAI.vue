<template>
  <div class="page-container">
    <!-- Tiêu đề trang -->
    <div class="page-header mb-4">
      <h1 class="page-title">Traning AI: Quản lý kiến thức</h1>
      <p class="page-subtitle">Thêm, chỉnh sửa và quản lý các thông tin cần thiết để huấn luyện AI.</p>
    </div>

    <!-- Form Thêm/Sửa -->
    <div class="card filter-card mb-4">
      <div class="card-body">
        <h5 class="card-title mb-3">{{ form.id ? 'Cập nhật thông tin AI' : 'Thêm nội dung huấn luyện mới' }}</h5>
        <form @submit.prevent="saveInfo">
          <div class="mb-3">
            <label for="title-input" class="form-label">Tiêu đề nội dung</label>
            <input
              id="title-input"
              v-model="form.title"
              placeholder="Ví dụ: Cách đặt phòng, Chính sách hủy..."
              required
              class="form-control"
            />
          </div>
          <div class="mb-3">
            <label for="content-textarea" class="form-label">Nội dung chi tiết</label>
            <textarea
              id="content-textarea"
              v-model="form.content"
              placeholder="Cung cấp thông tin chi tiết và đầy đủ để AI học hỏi."
              rows="8"
              required
              class="form-control"
            ></textarea>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-3">
            <button v-if="form.id" type="button" @click="resetForm" class="btn btn-secondary">
              Hủy Bỏ
            </button>
            <button type="submit" class="btn btn-primary">
              <i class="bi me-2" :class="form.id ? 'bi-check-circle-fill' : 'bi-plus-circle-fill'"></i>
              {{ form.id ? 'Lưu Cập Nhật' : 'Thêm Mới' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Danh sách nội dung -->
    <div class="list-section">
      <h4 class="mb-3">Kho nội dung AI hiện có</h4>
      <div v-if="hotelInfos.length" class="info-grid">
        <div class="card info-card" v-for="info in hotelInfos" :key="info.id">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <h5 class="card-title info-card-title">{{ info.title }}</h5>
                    <div class="action-buttons">
                        <button @click="editInfo(info)" title="Chỉnh sửa" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil-fill"></i>
                        </button>
                        <button @click="deleteInfo(info.id)" title="Xóa" class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-trash-fill"></i>
                        </button>
                    </div>
                </div>
                <p class="card-text info-card-content">{{ info.content }}</p>
            </div>
        </div>
      </div>
      <div v-else class="alert alert-light text-center">
        <i class="bi bi-box-open fs-3 d-block mb-2"></i>
        Chưa có nội dung nào được thêm. Hãy bắt đầu tạo mới!
      </div>
    </div>
  </div>
</template>

<script setup>
// --- TOÀN BỘ SCRIPT CỦA BẠN GIỮ NGUYÊN ---
import { ref, onMounted, inject } from 'vue'
import axios from 'axios'

const hotelInfos = ref([])
const form = ref({
  id: null,
  title: '',
  content: ''
})
const apiUrl = inject('apiUrl');
const API_BASE_URL = `${apiUrl}/api/hotel-infos`; 

const fetchInfos = async () => {
  try {
    const res = await axios.get(API_BASE_URL)
    hotelInfos.value = res.data
  } catch (error) {
    console.error('Lỗi khi tải thông tin AI:', error)
  }
}

const saveInfo = async () => {
  try {
    if (form.value.id) {
      await axios.put(`${API_BASE_URL}/${form.value.id}`, form.value)
    } else {
      await axios.post(API_BASE_URL, form.value)
    }
    await fetchInfos()
    resetForm()
  } catch (error) {
    console.error('Lỗi khi lưu thông tin:', error)
  }
}

const editInfo = (info) => {
  form.value = { ...info }
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

const deleteInfo = async (id) => {
  if (confirm('Bạn có chắc chắn muốn xóa nội dung này vĩnh viễn không?')) {
    try {
      await axios.delete(`${API_BASE_URL}/${id}`)
      await fetchInfos()
    } catch (error) {
      console.error('Lỗi khi xóa thông tin:', error)
    }
  }
}

const resetForm = () => {
  form.value = { id: null, title: '', content: '' }
}

onMounted(fetchInfos)
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
.form-control, .form-select { border-radius: 8px; border: 1px solid #e5eaee; transition: all 0.2s ease-in-out; }
.form-control:focus, .form-select:focus { border-color: #3498db; box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.15); }

/* List Section Styles */
.info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 1.5rem;
}

.info-card {
  transition: all 0.2s ease-in-out;
}
.info-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
}
.info-card-title {
  font-size: 1.1rem;
  font-weight: 600;
  margin-right: 1rem; /* Tạo khoảng cách với nút */
  flex-grow: 1; /* Cho phép tiêu đề co giãn */
}

.info-card-content {
  font-size: 0.9rem;
  color: #566573;
  line-height: 1.6;
  display: -webkit-box;
  -webkit-line-clamp: 4; /* Giới hạn 4 dòng */
  line-clamp: 4; /* Chuẩn CSS cho giới hạn dòng */
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
  margin-top: 0.75rem;
}

.action-buttons {
  display: flex;
  gap: 0.5rem;
}

</style>