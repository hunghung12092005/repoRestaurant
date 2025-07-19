<template>
  <div class="hotel-info-manager-pro">
    <header class="app-header">
      <h1 class="app-title">Traning AI: Quản lý kiến thức </h1>
      <p class="app-description">Thêm, chỉnh sửa và quản lý các thông tin cần thiết để huấn luyện AI.</p>
    </header>

    <section class="card form-section">
      <h2 class="card-heading">{{ form.id ? 'Cập nhật thông tin AI' : 'Thêm nội dung huấn luyện mới' }}</h2>
      <form @submit.prevent="saveInfo">
        <div class="form-group">
          <label for="title-input">Tiêu đề nội dung</label>
          <input
            id="title-input"
            v-model="form.title"
            placeholder="Ví dụ: Cách đặt phòng, Chính sách hủy..."
            required
            class="text-input"
          />
        </div>
        <div class="form-group">
          <label for="content-textarea">Nội dung chi tiết</label>
          <textarea
            id="content-textarea"
            v-model="form.content"
            placeholder="Cung cấp thông tin chi tiết và đầy đủ để AI học hỏi."
            rows="10"
            required
            class="text-input"
          ></textarea>
        </div>
        <div class="form-actions">
          <button type="submit" class="button button-primary">
            <i class="fas fa-save icon-left" v-if="form.id"></i>
            <i class="fas fa-plus-circle icon-left" v-else></i>
            {{ form.id ? 'Lưu Cập Nhật' : 'Thêm Mới' }}
          </button>
          <button v-if="form.id" type="button" @click="resetForm" class="button button-secondary">
            <i class="fas fa-times-circle icon-left"></i>
            Hủy Bỏ
          </button>
        </div>
      </form>
    </section>

    <section class="list-section">
      <h2 class="section-heading">Kho nội dung AI hiện có</h2>
      <div v-if="hotelInfos.length" class="info-grid">
        <div class="info-card" v-for="info in hotelInfos" :key="info.id">
          <div class="info-card-header">
            <h3 class="info-card-title">{{ info.title }}</h3>
            <div class="info-card-actions">
              <button @click="editInfo(info)" title="Chỉnh sửa" class="action-button edit-button">
                <i class="fas fa-pencil-alt"></i>
              </button>
              <button @click="deleteInfo(info.id)" title="Xóa" class="action-button delete-button">
                <i class="fas fa-trash-alt"></i>
              </button>
            </div>
          </div>
          <p class="info-card-content">{{ info.content }}</p>
        </div>
      </div>
      <p v-else class="empty-state-message">
        <i class="fas fa-box-open icon-left"></i>
        Chưa có nội dung nào được thêm. Hãy bắt đầu tạo mới!
      </p>
    </section>
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

const API_BASE_URL = '/api/hotel-infos'; 

const fetchInfos = async () => {
  try {
    const res = await axios.get(API_BASE_URL)
    hotelInfos.value = res.data
  } catch (error) {
    console.error('Lỗi khi tải thông tin AI:', error)
    // Optionally show a user-friendly error notification
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
    // Optionally show a user-friendly error notification
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
      // Optionally show a user-friendly error notification
    }
  }
}

const resetForm = () => {
  form.value = { id: null, title: '', content: '' }
}

onMounted(fetchInfos)
</script>

<style scoped>
/* Google Fonts - Poppins for headings, Inter for body text */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@500;600;700&display=swap');

/* Base Styles & Typography */
:root {
  --color-primary: #3498db; /* A slightly softer blue */
  --color-primary-dark: #2980b9;
  --color-secondary: #ecf0f1; /* Light grey for secondary actions/backgrounds */
  --color-secondary-dark: #bdc3c7;
  --color-text-dark: #2c3e50;
  --color-text-medium: #555;
  --color-text-light: #7f8c8d;
  --color-background-light: #f8faff; /* Very light blue tint */
  --color-background-card: #ffffff;
  --color-border: #e0e6ec;
  --color-danger: #e74c3c;
  --color-success: #2ecc71;

  --shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.04);
  --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.08);
  --shadow-lg: 0 8px 24px rgba(0, 0, 0, 0.1);

  --border-radius-sm: 6px;
  --border-radius-md: 10px;
}

body {
  margin: 0;
  background-color: var(--color-background-light);
  font-family: 'Inter', sans-serif;
  color: var(--color-text-dark);
  line-height: 1.6;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

.hotel-info-manager-pro {
  max-width: 960px; /* Slightly wider for more content */
  margin: 40px auto;
  padding: 30px;
  background-color: var(--color-background-card);
  border-radius: var(--border-radius-md);
  box-shadow: var(--shadow-lg);
  display: flex;
  flex-direction: column;
  gap: 30px; /* Space between sections */
}

/* Header */
.app-header {
  text-align: center;
  padding-bottom: 20px;
  border-bottom: 1px solid var(--color-border);
}

.app-title {
  font-family: 'Poppins', sans-serif;
  font-size: 2.8em;
  font-weight: 700;
  color: var(--color-primary);
  margin-bottom: 10px;
  letter-spacing: -0.03em;
}

.app-description {
  font-size: 1.1em;
  color: var(--color-text-medium);
  max-width: 600px;
  margin: 0 auto;
}

/* Card General Styling */
.card {
  background-color: var(--color-background-card);
  border-radius: var(--border-radius-md);
  box-shadow: var(--shadow-md);
  padding: 30px;
  border: 1px solid var(--color-border);
}

.card-heading, .section-heading {
  font-family: 'Poppins', sans-serif;
  font-size: 1.8em;
  font-weight: 600;
  color: var(--color-text-dark);
  margin-bottom: 25px;
  padding-bottom: 15px;
  border-bottom: 2px solid var(--color-border);
}

/* Form Styles */
.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  font-weight: 600;
  margin-bottom: 8px;
  color: var(--color-text-dark);
  font-size: 0.95em;
}

.text-input {
  width: 100%;
  padding: 12px 15px;
  border: 1px solid var(--color-border);
  border-radius: var(--border-radius-sm);
  font-size: 1em;
  font-family: 'Inter', sans-serif;
  color: var(--color-text-dark);
  background-color: #fcfdfe;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.text-input:focus {
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2); /* Light primary color glow */
  outline: none;
}

textarea.text-input {
  resize: vertical;
  min-height: 150px;
}

.form-actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  margin-top: 25px;
}

/* Buttons */
.button {
  padding: 12px 25px;
  font-weight: 600;
  border: none;
  border-radius: var(--border-radius-sm);
  cursor: pointer;
  transition: background-color 0.2s ease, transform 0.1s ease, box-shadow 0.2s ease;
  font-size: 1em;
  display: flex;
  align-items: center;
  gap: 8px;
}

.button-primary {
  background-color: rgb(49, 168, 215);
  color: white;
  box-shadow: var(--shadow-sm);
}

.button-primary:hover {
  background-color: var(--color-primary-dark);
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
}

.button-secondary {
  background-color: var(--color-secondary);
  color: var(--color-text-medium);
  box-shadow: var(--shadow-sm);
}

.button-secondary:hover {
  background-color: var(--color-secondary-dark);
  color: var(--color-text-dark);
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
}

.icon-left {
  margin-right: 5px;
}

/* List Section */
.list-section {
  padding-top: 10px; /* Adjust if needed based on the previous section's margin */
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(380px, 1fr)); /* Larger cards */
  gap: 20px;
}

.info-card {
  background: var(--color-background-card);
  border: 1px solid var(--color-border);
  padding: 25px;
  border-radius: var(--border-radius-md);
  box-shadow: var(--shadow-sm);
  display: flex;
  flex-direction: column;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.info-card:hover {
  transform: translateY(-4px);
  box-shadow: var(--shadow-md);
}

.info-card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 15px;
  padding-bottom: 10px;
  border-bottom: 1px solid #f0f4f7; /* Lighter border for header */
}

.info-card-title {
  font-family: 'Poppins', sans-serif;
  font-size: 1.35em;
  font-weight: 600;
  color: var(--color-text-dark);
  margin: 0;
  flex-grow: 1;
  padding-right: 15px;
}

.info-card-actions {
  display: flex;
  gap: 8px;
}

.action-button {
  background: none;
  border: none;
  font-size: 1.1em;
  cursor: pointer;
  padding: 8px;
  border-radius: 50%; /* Make them circular */
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background-color 0.2s ease, color 0.2s ease;
  color: var(--color-text-light); /* Default icon color */
}

.action-button:hover {
  color: var(--color-text-dark);
}

.edit-button:hover {
  background-color: rgba(52, 152, 219, 0.1); /* Light blue background */
  color: var(--color-primary);
}

.delete-button:hover {
  background-color: rgba(231, 76, 60, 0.1); /* Light red background */
  color: var(--color-danger);
}

.info-card-content {
  font-size: 0.95em;
  color: var(--color-text-medium);
  line-height: 1.7;
  flex-grow: 1;
  overflow: hidden; /* Ensure content doesn't break layout */
  text-overflow: ellipsis; /* Optional: adds "..." if content overflows */
  display: -webkit-box;
  -webkit-line-clamp: 5; /* Limit content to 5 lines for a cleaner look */
  -webkit-box-orient: vertical;
}

.empty-state-message {
  text-align: center;
  color: var(--color-text-light);
  font-style: italic;
  padding: 40px;
  border: 2px dashed var(--color-border);
  border-radius: var(--border-radius-md);
  background-color: #fdfdfd;
  margin-top: 25px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  font-size: 1.1em;
}

/* Font Awesome for Icons (Ensure you link it in public/index.html) */
/* Example: <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"> */
</style>