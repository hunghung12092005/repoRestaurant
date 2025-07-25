<template>
  <div class="page-container">
    <!-- Tiêu đề trang -->
    <div class="page-header mb-4">
      <h1 class="page-title">Quản lý Liên hệ</h1>
      <p class="page-subtitle">Xem, phản hồi và quản lý các liên hệ từ khách hàng.</p>
    </div>

    <!-- Hiển thị Loading hoặc Lỗi -->
    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="mt-3 text-muted">Đang tải dữ liệu...</p>
    </div>

    <div v-else-if="error" class="alert alert-danger">{{ error }}</div>

    <!-- Bảng danh sách liên hệ -->
    <div v-else class="table-container">
      <table class="table booking-table align-middle">
        <thead>
          <tr>
            <!-- [THAY ĐỔI] Đặt chiều rộng hợp lý cho cột Khách hàng -->
            <th style="width: 25%;">Khách Hàng</th>
            <!-- [THAY ĐỔI] Bỏ width để cột này tự chiếm không gian còn lại -->
            <th>Lời Nhắn</th>
            <!-- [THAY ĐỔI] Đặt width cố định bằng pixel cho các cột ngắn -->
            <th class="text-center" style="width: 250px;">Trạng Thái</th>
            <th class="text-center" style="width: 130px;">Hành Động</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="contacts.length === 0">
            <td colspan="4" class="text-center py-5">Chưa có liên hệ nào.</td>
          </tr>
          <tr v-for="contact in contacts" :key="contact.id">
            <td>
              <div class="fw-bold type-name">{{ contact.name }}</div>
              <p class="description-text mb-1">{{ contact.email }}</p>
              <p v-if="contact.phone" class="description-text mb-0 text-primary">
                <i class="bi bi-telephone-fill me-1"></i>{{ contact.phone }}
              </p>
            </td>
            <td>
              <div class="comment-content" :title="contact.message">{{ contact.message }}</div>
              <p class="description-text mb-0 mt-2 fst-italic">Gửi lúc: {{ formatDateTime(contact.created_at) }}</p>
            </td>
            <td class="text-center">
              <span :class="['badge', getStatusClass(contact.status)]">
                {{ formatStatus(contact.status) }}
              </span>
            </td>
            <td class="text-center action-buttons">
              <button @click="openReplyModal(contact)" class="btn btn-outline-primary btn-sm" title="Phản hồi">
                <i class="bi bi-reply-all-fill"></i>
              </button>
              <button @click="deleteContact(contact.id)" class="btn btn-outline-danger btn-sm" title="Xóa">
                <i class="bi bi-trash-fill"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal Phản hồi -->
    <div class="modal fade" id="replyModal" tabindex="-1" aria-labelledby="replyModalLabel" aria-hidden="true" ref="modalRef">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content modal-custom" v-if="selectedContact">
          <div class="modal-header modal-header-custom">
            <h5 class="modal-title" id="replyModalLabel">
              Phản hồi tới: {{ selectedContact.name }}
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body p-4">
            <div class="mb-3">
              <label class="form-label">Lời nhắn của khách:</label>
              <div class="contact-message-box p-3 rounded">
                <p class="fst-italic mb-0">{{ selectedContact.message }}</p>
              </div>
            </div>
            <hr/>
            <div class="mb-3">
              <label for="replyMessage" class="form-label">Nội dung phản hồi:</label>
              <textarea v-model="replyMessage" class="form-control" id="replyMessage" rows="6" placeholder="Nhập nội dung phản hồi..."></textarea>
            </div>
          </div>
          <div class="modal-footer modal-footer-custom">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            <button type="button" class="btn btn-primary" @click="sendReply" :disabled="isReplying">
              <span v-if="isReplying" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
              {{ isReplying ? 'Đang gửi...' : 'Gửi Phản hồi' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
// --- TOÀN BỘ SCRIPT CỦA BẠN GIỮ NGUYÊN ---
import { ref, onMounted, onUnmounted, inject } from 'vue';
import { Modal } from 'bootstrap';
import axiosConfig from '../../axiosConfig.js';
import Swal from 'sweetalert2';

const apiUrl = inject('apiUrl');
const contacts = ref([]);
const loading = ref(true);
const error = ref(null);
const selectedContact = ref(null);
const replyMessage = ref('');
const isReplying = ref(false);
const modalRef = ref(null);
let modalInstance = null;
let pollingTimer = null;

const fetchContacts = async () => {
  try {
    loading.value = true;
    error.value = null;
    const response = await axiosConfig.get(`${apiUrl}/api/admin/contacts`);
    if (response.data.status) {
      contacts.value = response.data.data;
    }
  } catch (err) {
    error.value = 'Không thể tải danh sách liên hệ. Vui lòng thử lại sau.';
    console.error(err);
  } finally {
    loading.value = false;
  }
};

const deleteContact = async (id) => {
  const result = await Swal.fire({
    title: 'Bạn có chắc chắn?',
    text: "Bạn sẽ không thể hoàn tác hành động này!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Vâng, xóa nó!',
    cancelButtonText: 'Hủy'
  });

  if (result.isConfirmed) {
    try {
      await axiosConfig.delete(`${apiUrl}/api/admin/contacts/${id}`);
      Swal.fire('Đã xóa!', 'Liên hệ đã được xóa thành công.', 'success');
      contacts.value = contacts.value.filter(contact => contact.id !== id);
    } catch (err) {
      Swal.fire('Lỗi!', 'Không thể xóa liên hệ. Vui lòng thử lại.', 'error');
      console.error(err);
    }
  }
};

const openReplyModal = (contact) => {
  selectedContact.value = contact;
  replyMessage.value = '';
  if (modalInstance) {
    modalInstance.show();
  }
};

const sendReply = async () => {
  if (!replyMessage.value.trim()) {
    Swal.fire('Lỗi', 'Vui lòng nhập nội dung phản hồi.', 'error');
    return;
  }
  isReplying.value = true;
  try {
    const response = await axiosConfig.post(`${apiUrl}/api/admin/contacts/${selectedContact.value.id}/reply`, {
      reply_message: replyMessage.value
    });
    
    if (response.data.status) {
      Swal.fire('Thành công!', response.data.message, 'success');
      modalInstance.hide();
      const contactIndex = contacts.value.findIndex(c => c.id === selectedContact.value.id);
      if (contactIndex !== -1) {
          contacts.value[contactIndex].status = 'replied';
      }
    }
  } catch (err) {
    const errorMessage = err.response?.data?.message || 'Không thể gửi phản hồi. Vui lòng thử lại.';
    Swal.fire('Lỗi!', errorMessage, 'error');
    console.error(err);
  } finally {
    isReplying.value = false;
  }
};

const startPollingForNewContacts = () => {
  if (pollingTimer) clearInterval(pollingTimer);
  pollingTimer = setInterval(pollNewContacts, 7000);
};

const pollNewContacts = async () => {
  if (contacts.value.length === 0 && !loading.value) {
    await fetchContacts(); 
    return;
  }
  
  if(contacts.value.length > 0) {
    const lastContactId = contacts.value[0].id;
    try {
        const response = await axiosConfig.get(`${apiUrl}/api/admin/contacts/new?lastContactId=${lastContactId}`);
        if (response.data.status && response.data.data.length > 0) {
            const newContacts = response.data.data;
            contacts.value.unshift(...newContacts);
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'info',
                title: `Có ${newContacts.length} liên hệ mới!`,
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        }
    } catch (err) {
        console.error("Lỗi khi polling:", err);
        if (err.response && (err.response.status === 401 || err.response.status === 403)) {
            console.warn("Lỗi xác thực, dừng polling.");
            stopPolling();
        }
    }
  }
};

const stopPolling = () => {
  if (pollingTimer) clearInterval(pollingTimer);
};

const formatDateTime = (dateTimeString) => {
  const options = { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' };
  return new Date(dateTimeString).toLocaleDateString('vi-VN', options);
};
const formatStatus = (status) => (status === 'replied' ? 'Đã phản hồi' : 'Chờ phản hồi');
const getStatusClass = (status) => (status === 'replied' ? 'badge-success' : 'badge-info');

onMounted(async () => {
  await fetchContacts();
  startPollingForNewContacts();
  if (modalRef.value) {
    modalInstance = new Modal(modalRef.value);
  }
});

onUnmounted(() => {
  stopPolling();
  if (modalInstance) {
    modalInstance.dispose();
  }
});
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

.table-container { background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); overflow-x: auto; }
.booking-table { font-size: 0.875rem; border-collapse: separate; border-spacing: 0; min-width: 800px; }
.booking-table thead th { background-color: #f8f9fa; color: #7f8c8d; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e5eaee; padding: 1rem; white-space: nowrap; }
.booking-table td { padding: 1.25rem 1rem; border-bottom: 1px solid #e5eaee; }
.booking-table tbody tr:last-child td { border-bottom: none; }
.booking-table tbody tr:hover { background-color: #f9fafb; }

.type-name { font-size: 1rem; font-weight: 600; }
.description-text { font-size: 0.8rem; color: #7f8c8d; }
.comment-content {
  display: -webkit-box;
  -webkit-line-clamp: 2; /* Giới hạn 2 dòng */
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: normal;
  max-width: 100%; /* Đảm bảo nó không tràn ra ngoài cột */
}

.badge { padding: 0.4em 0.8em; font-size: 0.75rem; font-weight: 600; border-radius: 20px; letter-spacing: 0.5px; }
.badge-info { background-color: #eaf6fb; color: #3498db; }
.badge-success { background-color: #e6f9f0; color: #2ecc71; }

.action-buttons { white-space: nowrap; }
.action-buttons .btn { margin: 0 2px; }

/* Modal Styles */
.modal-custom { border-radius: 16px; border: none; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); }
.modal-header-custom { background-color: #f4f7f9; border-bottom: 1px solid #e5eaee; padding: 1.5rem; }
.modal-footer-custom { background-color: #f4f7f9; border-top: 1px solid #e5eaee; padding: 1rem 1.5rem; }
.contact-message-box {
  background-color: #f8f9fa;
  border: 1px solid #e5eaee;
  color: #566573;
}
</style>