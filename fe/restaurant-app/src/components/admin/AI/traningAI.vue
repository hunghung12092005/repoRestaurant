<template>
  <div class="hotel-info-manager">
    <h2 class="title">🏨 Traning AI </h2>

    <!-- Form thêm/sửa -->
    <div class="form-section">
      <h3>{{ form.id ? '✏️ Chỉnh sửa thông tin' : '➕ Thêm thông tin mới' }}</h3>
      <form @submit.prevent="saveInfo">
        <div class="form-group">
          <label>Tiêu đề</label>
          <input v-model="form.title" placeholder="Nhập tiêu đề..." required />
        </div>
        <div class="form-group">
          <label>Nội dung</label>
          <textarea v-model="form.content" placeholder="Nội dung chi tiết..." rows="10" required></textarea>
        </div>
        <div class="form-buttons">
          <button type="submit" class="btn btn-save">
            {{ form.id ? '💾 Lưu cập nhật' : '➕ Thêm mới' }}
          </button>
          <button v-if="form.id" type="button" @click="resetForm" class="btn btn-cancel">Huỷ</button>
        </div>
      </form>
    </div>

    <!-- Danh sách -->
    <div class="list-section">
      <h3>📄 Danh sách nội dung</h3>
      <div v-if="hotelInfos.length" class="info-list">
        <div class="info-card" v-for="info in hotelInfos" :key="info.id">
          <div class="info-header">
            <strong>{{ info.title }}</strong>
            <div class="card-actions">
              <button @click="editInfo(info)" title="Sửa">✏️</button>
              <button @click="deleteInfo(info.id)" title="Xoá">🗑</button>
            </div>
          </div>
          <p class="info-content">{{ info.content }}</p>
        </div>
      </div>
      <p v-else class="empty-msg">Chưa có nội dung nào.</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const hotelInfos = ref([])
const form = ref({
  id: null,
  title: '',
  content: ''
})

const fetchInfos = async () => {
  const res = await axios.get('/api/hotel-infos')
  hotelInfos.value = res.data
}

const saveInfo = async () => {
  if (form.value.id) {
    await axios.put(`/api/hotel-infos/${form.value.id}`, form.value)
  } else {
    await axios.post('/api/hotel-infos', form.value)
  }
  await fetchInfos()
  resetForm()
}

const editInfo = (info) => {
  form.value = { ...info }
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

const deleteInfo = async (id) => {
  if (confirm('Bạn có chắc chắn muốn xoá?')) {
    await axios.delete(`/api/hotel-infos/${id}`)
    await fetchInfos()
  }
}

const resetForm = () => {
  form.value = { id: null, title: '', content: '' }
}

onMounted(fetchInfos)
</script>

<style scoped>
.hotel-info-manager {
  max-width: 780px;
  margin: 20px auto;
  padding: 16px;
  font-family: 'Segoe UI', sans-serif;
  color: #333;
}
.title {
  text-align: center;
  color: #2c7be5;
  margin-bottom: 24px;
}
.form-section {
  background: #f0f7ff;
  padding: 20px;
  border-radius: 12px;
  margin-bottom: 30px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
}
.form-group {
  margin-bottom: 16px;
}
.form-group label {
  font-weight: 600;
  display: block;
  margin-bottom: 6px;
}
input,
textarea {
  width: 100%;
  padding: 8px 10px;
  border: 1px solid #ccc;
  border-radius: 8px;
  font-size: 14px;
}
.form-buttons {
  display: flex;
  gap: 10px;
  margin-top: 10px;
}
.btn {
  padding: 8px 16px;
  font-weight: bold;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  transition: 0.2s ease;
}
.btn-save {
  background-color: #2c7be5;
  color: white;
}
.btn-cancel {
  background-color: #eee;
  color: #555;
}
.btn:hover {
  opacity: 0.9;
}
.list-section {
  margin-top: 20px;
}
.info-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.info-card {
  background: #fff;
  border: 1px solid #dbe4f0;
  padding: 16px;
  border-radius: 10px;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.03);
}
.info-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.card-actions button {
  margin-left: 8px;
  background: none;
  border: none;
  font-size: 16px;
  cursor: pointer;
}
.card-actions button:hover {
  opacity: 0.8;
}
.info-content {
  margin-top: 8px;
  font-size: 14px;
  color: #444;
}
.empty-msg {
  color: #777;
  font-style: italic;
}
</style>
