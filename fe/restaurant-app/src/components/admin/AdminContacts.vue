<template>
  <div class="admin-contacts-container p-4">
    <h1 class="h3 mb-4">Quản lý Liên hệ</h1>

    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="mt-2">Đang tải dữ liệu...</p>
    </div>

    <div v-else-if="error" class="alert alert-danger">{{ error }}</div>

    <div v-else class="card shadow-sm">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th scope="col">#</th>
                <th scope="col">Khách hàng</th>
                <th scope="col">Lời nhắn</th>
                <th scope="col">Ngày gửi</th>
                <th scope="col">Trạng thái</th>
                <th scope="col" class="text-center">Hành động</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="contacts.length === 0">
                <td colspan="6" class="text-center text-muted py-4">Không có liên hệ nào.</td>
              </tr>
              <tr v-for="(contact, index) in contacts" :key="contact.id">
                <td>{{ index + 1 }}</td>
                <td>
                  <div class="fw-bold">{{ contact.name }}</div>
                  <div class="small text-muted">{{ contact.email }}</div>
                  <!-- Hiển thị SĐT nếu có -->
                  <div v-if="contact.phone" class="small text-primary mt-1">
                    <i class="bi bi-telephone-fill me-1"></i>{{ contact.phone }}
                  </div>
                </td>
                <td class="message-cell" :title="contact.message">{{ contact.message }}</td>
                <td>{{ formatDateTime(contact.created_at) }}</td>
                <td>
                  <span :class="['badge', getStatusClass(contact.status)]">
                    {{ formatStatus(contact.status) }}
                  </span>
                </td>
                <td class="text-center">
                  <div class="btn-group">
                    <button @click="openReplyModal(contact)" class="btn btn-sm btn-outline-primary" title="Phản hồi">
                      <i class="bi bi-reply-fill"></i> Phản hồi
                    </button>
                    <button @click="deleteContact(contact.id)" class="btn btn-sm btn-outline-danger" title="Xóa">
                      <i class="bi bi-trash-fill"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Modal Phản hồi -->
    <div class="modal fade" id="replyModal" tabindex="-1" aria-labelledby="replyModalLabel" aria-hidden="true" ref="modalRef">
      <div class="modal-dialog modal-lg">
        <div class="modal-content" v-if="selectedContact">
          <div class="modal-header">
            <h5 class="modal-title" id="replyModalLabel">
              Phản hồi tới: {{ selectedContact.name }}
              <!-- Hiển thị email và SĐT trong modal title -->
              <small class="text-muted d-block">{{ selectedContact.email }} <span v-if="selectedContact.phone">| {{ selectedContact.phone }}</span></small>
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label fw-bold">Lời nhắn của khách:</label>
              <p class="text-muted fst-italic bg-light p-2 rounded border">{{ selectedContact.message }}</p>
            </div>
            <div class="mb-3">
              <label for="replyMessage" class="form-label fw-bold">Nội dung phản hồi của bạn:</label>
              <textarea v-model="replyMessage" class="form-control" id="replyMessage" rows="6" placeholder="Nhập nội dung phản hồi..."></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            <button type="button" class="btn btn-primary" @click="sendReply" :disabled="isReplying">
              <span v-if="isReplying" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
              {{ isReplying ? 'Đang gửi...' : 'Gửi Phản hồi' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
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
      fetchContacts();
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
      fetchContacts();
    }
  } catch (err) {
    const errorMessage = err.response?.data?.message || 'Không thể gửi phản hồi. Vui lòng thử lại.';
    Swal.fire('Lỗi!', errorMessage, 'error');
    console.error(err);
  } finally {
    isReplying.value = false;
  }
};

const formatDateTime = (dateTimeString) => {
  const options = { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' };
  return new Date(dateTimeString).toLocaleDateString('vi-VN', options);
};

const formatStatus = (status) => {
  if (status === 'replied') return 'Đã phản hồi';
  return 'Mới';
};

const getStatusClass = (status) => {
  if (status === 'replied') return 'bg-success';
  return 'bg-info';
};

onMounted(() => {
  fetchContacts();
  if (modalRef.value) {
    modalInstance = new Modal(modalRef.value);
  }
});

onUnmounted(() => {
  if (modalInstance) {
    modalInstance.dispose();
  }
});
</script>

<style scoped>
.admin-contacts-container {
  background-color: #f5f7fb;
  min-height: calc(100vh - 50px);
}
.message-cell {
  max-width: 300px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  cursor: help;
}
.table th, .table td {
  vertical-align: middle;
}
.btn-group .btn {
  margin-right: 5px;
}
.btn-group .btn:last-child {
  margin-right: 0;
}
</style>