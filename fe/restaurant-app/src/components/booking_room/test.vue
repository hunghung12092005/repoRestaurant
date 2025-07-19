
<template>
  <div v-if="showPopup" class="popup">
    <div class="popup-content">
      <h3>Nhập tên của bạn</h3>
      <input v-model="userName" placeholder="Tên của bạn" />
        <div class="cf-turnstile" data-sitekey="0x4AAAAAABhcDWU29f0qZu4n"></div>
      <button class="btn btn-primary m-2" @click="saveName">Lưu</button>
      <button class="btn btn-warning m-2" @click="closePopup">Hủy</button>
    </div>
  </div>
  <div class="chat-container">
    <div class="chat-card">
      <!-- Header -->
      <div class="chat-header">
        <img src="https://jbagy.me/wp-content/uploads/2025/03/Hinh-anh-avatar-nam-cute-2.jpg" class="avatar" alt="Admin" />
        <div>
          <h6>Admin Support</h6>
          <small>Online 24/7</small>
        </div>
        <span class="badge">Chat</span>
      </div>

      <!-- Messages -->
      <div class="chat-body">
        <div class="messages">
          <div v-for="(msg, index) in messageReceive" :key="'admin-' + index" class="message admin">
            <img src="https://jbagy.me/wp-content/uploads/2025/03/Hinh-anh-avatar-nam-cute-2.jpg" class="avatar-sm" />
            <div class="bubble">
              <div class="meta">Admin</div>
              <div class="text">{{ msg.message }}</div>
            </div>
          </div>

          <div v-for="(msg, index) in messageSend" :key="'user-' + index" class="message user">
            <div class="bubble">
              <div class="meta">{{ msg.user }}</div>
              <div class="text">{{ msg.message }}</div>
              <img v-if="msg.file" :src="msg.file" class="image-preview" />
            </div>
            <img src="https://jbagy.me/wp-content/uploads/2025/03/Hinh-anh-avatar-nam-cute-2.jpg" class="avatar-sm" />
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="chat-footer">
        <div class="tools">
          <button @click="toggleSuggestions">+</button>

          <div v-if="showSuggestions" class="suggestions">
            <input type="file" @change="handleFileUpload" accept="image/*" />
            <button v-for="suggestion in suggestions" :key="suggestion" @click="sendMessage(suggestion)">
              {{ suggestion }}
            </button>
          </div>
        </div>
        <input type="text" v-model="message" @keyup.enter="sendMessage()" placeholder="Type a message..." />
        <button @click="sendMessage()">
          <i class="bi bi-send"></i>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import socket from '../../socket'; // Import socket từ file chung
//[popup
const showPopup = ref(false);
const userName = ref('');

const saveName = () => {
   // Lấy giá trị Turnstile response
    const turnstileResponse = document.querySelector('input[name="cf-turnstile-response"]').value; console.log("Turnstile response:", turnstileResponse); // In giá trị ra console
    if (!turnstileResponse) {
        showError("Please complete the Turnstile challenge."); // Hiển thị thông báo nếu Turnstile chưa hoàn thành
        isLoading.value = false;
        return;
    }
  if (userName.value) {
    localStorage.setItem('userInfo', JSON.stringify({ name: userName.value, id: socket.id }));
    showPopup.value = false;
    window.location.reload(); // Tải lại trang để cập nhật tên người dùng
  } else {
    alert('Vui lòng nhập tên');
  }
};

const closePopup = () => {
  showPopup.value = false;
};

// Kiểm tra userInfo khi component được khởi tạo
if (!localStorage.getItem('userInfo')) {
  showPopup.value = true;
}
const message = ref('');
const messageSend = ref([]);
const messageReceive = ref([]);
const messages = ref([]);
const socketId = ref();
//const socket = io('http://localhost:6001'); // Kết nối đến server Socket.IO
socket.on('connect', () => {
  console.log(`Connected with socket ID: ${socket.id}`);
  socketId.value = socket.id;
});
const user = JSON.parse(localStorage.getItem('userInfo'))?.name || 'User chưa login'; // Lấy tên người dùng
const userId = JSON.parse(localStorage.getItem('userInfo'))?.id;
socket.emit('join', user); // Gửi dữ liệu tới server
socket.emit('register', userId); // Thay 'user-id-example' bằng ID thực tế
// Hàm gửi tin nhắn
const suggestions = ref(['Tôi cần hỗ trợ chuyển khoản lỗi/nhầm', 'Hỗ trợ đặt bàn nhanh', 'Hỗ trợ đặt phòng nhanh']);
const showSuggestions = ref(false);
const toggleSuggestions = () => {
  showSuggestions.value = !showSuggestions.value; // Chuyển đổi trạng thái hiển thị gợi ý
};

//gửi file
const file = ref(null);

const handleFileUpload = (event) => {
  file.value = event.target.files[0]; // Lưu file được chọn
  sendMessage();
};

const MAX_FILE_SIZE = 0.5 * 1024 * 1024; // 0.5 MB

const sendMessage = async (suggestion) => {
  const msg = suggestion || message.value;
  if (msg.trim() === '' && !file.value) return;

  if (file.value && file.value.size > MAX_FILE_SIZE) {
    //console.error('File size exceeds limit of 5MB');
    alert('File size exceeds limit of 1MB. Please choose a smaller file.'); // Thông báo cho người dùng
    return;
  }

  const fileBase64 = file.value ? await convertFileToBase64(file.value) : null;
  //console.log('Converted file to base64:', fileBase64);

  const messageData = {
    user: user,
    userId: userId,
    message: msg,
    socketId: socketId.value,
    file: fileBase64
  };
  console.log('Sending message:', messageData);

  socket.emit('chat message', messageData);
  message.value = '';
  file.value = null;
  showSuggestions.value = false;
};

// Hàm chuyển file thành base64
const convertFileToBase64 = (file) => {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.onload = () => resolve(reader.result);
    reader.onerror = (error) => reject(error);
    reader.readAsDataURL(file);
  });
};

// Hàm lắng nghe tin nhắn từ server
const listenForMessages = () => {
  socket.on('chat messageSend', (data) => {
    const messageObject = { user: data.user, message: data.message };

    // Kiểm tra xem có file không
    if (data.file) {
      messageObject.file = data.file; // Lưu đường dẫn file vào đối tượng tin nhắn
    }

    messageSend.value.push(messageObject); // Lưu tin nhắn vào messages
  });

  // socket.on('chat messageReceive', (data) => {
  //     messageReceive.value.push({ user: data.user, message: data.message }); // Lưu tin nhắn vào messages
  // });
};

// Hàm lấy lịch sử chat từ server
const getMessages = () => {
  socket.emit('get user messages', userId); // Gửi yêu cầu lấy lịch sử chat cho userId
};
// Lắng nghe lịch sử chat từ server
socket.on('chat history', (chatMessages) => {
  messageSend.value = []; // Xóa danh sách hiện tại trước khi thêm
  chatMessages.forEach(msg => {
    messageSend.value.push(msg); // Thêm tin nhắn vào danh sách
  });
});

// Lắng nghe tin nhắn khi component được gắn vào DOM
onMounted(() => {
  getMessages();
  listenForMessages(); // Bắt đầu lắng nghe tin nhắn
});

// Ngắt kết nối khi component bị hủy
onUnmounted(() => {
  socket.disconnect();
});
</script>
<style scoped>
.popup {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.popup-content {
  background: white;
  padding: 30px;
  border-radius: 10px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
  text-align: center;
  width: 300px; /* Đặt chiều rộng tối đa cho popup */
}

.popup-content h3 {
  margin-bottom: 20px;
}

.popup-content input {
  width: 100%;
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 5px;
  margin-bottom: 20px;
}

.popup-actions {
  display: flex;
  justify-content: space-between;
}


.chat-container {
  padding: 20px;
  display: flex;
  justify-content: center;
  background-color: #f9fafb;
  height: 100vh;
}

.chat-card {
  width: 100%;
  max-width: 700px;
  height: 85vh;
  display: flex;
  flex-direction: column;
  background: white;
  border-radius: 16px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.chat-header {
  display: flex;
  align-items: center;
  padding: 16px;
  border-bottom: 1px solid #eee;
  background-color: #f3f4f6;
  position: relative;
}

.chat-header .avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  margin-right: 10px;
}

.chat-header h6 {
  margin: 0;
  font-size: 16px;
}

.chat-header .badge {
  position: absolute;
  top: 10px;
  right: 10px;
  background: #4f46e5;
  color: white;
  padding: 4px 8px;
  border-radius: 20px;
  font-size: 12px;
}

.chat-body {
  flex: 1;
  overflow-y: auto;
  padding: 16px;
  background: #f9fafb;
}

.messages {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.message {
  display: flex;
  align-items: flex-start;
}

.message.user {
  justify-content: flex-end;
}

.message .avatar-sm {
  width: 30px;
  height: 30px;
  border-radius: 50%;
}

.bubble {
  max-width: 70%;
  background: #e5e7eb;
  padding: 12px;
  border-radius: 16px;
  position: relative;
}

.message.user .bubble {
  background: #dbeafe;
}

.meta {
  font-size: 12px;
  color: #6b7280;
  margin-bottom: 4px;
}

.image-preview {
  margin-top: 8px;
  max-width: 200px;
  border-radius: 8px;
}

.chat-footer {
  display: flex;
  padding: 12px;
  border-top: 1px solid #eee;
  align-items: center;
  background-color: #fff;
}

.chat-footer input[type="text"] {
  flex: 1;
  border: 1px solid #ddd;
  border-radius: 20px;
  padding: 8px 12px;
  margin: 0 8px;
  font-size: 14px;
}

.chat-footer button {
  background: #4f46e5;
  border: none;
  border-radius: 50%;
  width: 36px;
  height: 36px;
  color: white;
  display: flex;
  justify-content: center;
  align-items: center;
}

.tools {
  position: relative;
}

.suggestions {
  position: absolute;
  top: -160px;
  background: white;
  border: 1px solid #ddd;
  padding: 10px;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  z-index: 999;
  width: 220px;
}

.suggestions button {
  display: block;
  width: 100%;
  padding: 6px;
  margin-bottom: 6px;
  border: none;
  background: #f3f4f6;
  cursor: pointer;
  font-size: 13px;
}
</style>

