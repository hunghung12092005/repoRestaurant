<template>
  <div>
    <!-- Popup nhập tên -->
    <div v-if="showPopup" class="popup">
      <div class="popup-content">
        <h3>Nhập tên của bạn</h3>
        <input v-model="userName" placeholder="Tên của bạn" class="form-control mb-3" />
        <div class="popup-actions">
          <button class="btn btn-warning m-2" @click="closePopup">Hủy</button>
          <button class="btn btn-primary m-2" @click="saveName">Lưu</button>
        </div>
      </div>
    </div>
    <!-- Nút mở/đóng chat-container -->
    <button @click="toggleChatContainer" class="toggle-chat-btn">
      <small>CHATBOT HXH </small>
      <img src="https://img.freepik.com/free-vector/chatbot-conversation-vectorart_78370-4107.jpg?semt=ais_hybrid&w=740"
        alt="Toggle Chat" />
    </button>
    <!-- Chat Container -->
    <div v-if="showChat" class="chat-container">
      <div class="chat-card">
        <!-- Header -->
        <div class="chat-header">
          <img
            src="https://img.freepik.com/free-vector/chatbot-conversation-vectorart_78370-4107.jpg?semt=ais_hybrid&w=740"
            class="avatar" alt="Support" />
          <div>
            <h6>{{ activeTab === 'ai' ? 'Hỏi đáp cùng ChatBot AI 🤖' : 'Admin Support' }}</h6>
            <small>{{ activeTab === 'ai' ? 'Hệ thống hỗ trợ tự động 24/7' : 'Online Gần Đây' }}</small>
          </div>
          <span @click="toggleChatContainer" class="badge">Close</span>
        </div>

        <!-- Tabs -->
        <div class="chat-tabs d-flex border-bottom">

          <button :class="['tab-btn', { active: activeTab === 'ai' }]" @click="switchTab('ai')">Chat với AI</button>
          <button :class="['tab-btn', { active: activeTab === 'human' }]" @click="switchTab('human')">Chat với Nhân
            viên</button>
        </div>

        <!-- Suggested Questions (AI Tab Only) -->
        <div v-if="activeTab === 'ai'" class="chat-suggestions">
          <button @click="handleAvailabilityCheck">📦 Còn phòng trống?</button>
          <div class="suggestion-buttons">
            <button v-for="suggestion in aiSuggestions" :key="suggestion" @click="sendMessage(suggestion)">
              {{ suggestion }}
            </button>
          </div>
        </div>

        <!-- Messages -->
        <div class="chat-body">
          <div class="messages" ref="messagesRef">
            <div v-for="(msg, index) in currentMessages" :key="'msg-' + index"
              :class="['message', msg.user === user ? 'user' : 'admin']">
              <img v-if="msg.user !== user"
                src="https://img.freepik.com/free-vector/chatbot-conversation-vectorart_78370-4107.jpg?semt=ais_hybrid&w=740"
                class="avatar-sm" />
              <div class="bubble">
                <div class="meta">{{ msg.user === user ? msg.user : (activeTab === 'ai' ? 'AI' : 'Admin') }}</div>
                <div class="text">{{ msg.message }}</div>
<img v-if="msg.file" :src="msg.file" class="image-preview mt-2" />
              </div>
              <img v-if="msg.user === user"
                src="https://jbagy.me/wp-content/uploads/2025/03/Hinh-anh-avatar-nam-cute-2.jpg" class="avatar-sm" />
            </div>
            <div v-if="loading" class="message admin loading">
              <div class="bubble">
                <div class="meta">{{ activeTab === 'ai' ? 'AI' : 'Admin' }}</div>
                <div class="text">Đang xử lý<span class="dots"></span></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="chat-footer">
          <div class="tools">
            <button @click="toggleSuggestions">+</button>
            <div v-if="showSuggestions" class="suggestions">
              <input type="file" @change="handleFileUpload" accept="image/*" :disabled="activeTab === 'ai'" />
              <button v-for="suggestion in suggestions" :key="suggestion" @click="sendMessage(suggestion)">
                {{ suggestion }}
              </button>
            </div>
          </div>
          <input type="text" v-model="message" @keyup.enter="sendMessage()"
            :placeholder="activeTab === 'ai' ? 'Bạn cần hỏi gì?' : 'Type a message...'" />
          <button @click="sendMessage()" :disabled="loading">
            <i class="bi bi-send"></i>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, nextTick, onMounted, onUnmounted, inject } from 'vue';
import axios from 'axios';
import socket from '../socket'; // Import socket từ file chung

// Khai báo biến trạng thái cho chat-container
const showChat = ref(true);

// Hàm để bật/tắt chat-container
const toggleChatContainer = () => {
  showChat.value = !showChat.value;
};

const API_KEY = 'AIzaSyDdyQPlin693Vo16vKOWnI38qLJ5U2z5LQ';
const apiUrl = inject('apiUrl');
const showPopup = ref(false);
const userName = ref('');
const message = ref('');
const loading = ref(false);
const activeTab = ref('ai');
const nameU = JSON.parse(localStorage.getItem('userInfo'))?.name || '';

const aiMessages = ref([
  { user: 'AI', message: `Xin chào ${nameU}! Tôi là AI ChatBot HXH. Bạn muốn hỏi gì về khách sạn ạ?` },
]);
const messageSend = ref([]);
const aiSuggestions = ref([
  '🕒 Giờ nhận và trả phòng là khi nào?',
  '💰 Giá phòng bao nhiêu?',
  // '📞 Tôi muốn liên hệ khách sạn',
]);
const suggestions = ref([
  'Tôi cần hỗ trợ chuyển khoản lỗi/nhầm',
  'Hỗ trợ đặt phòng nhanh',
]);
const showSuggestions = ref(false);
const messagesRef = ref(null);
const socketId = ref('');
const file = ref(null);
const MAX_FILE_SIZE = 0.5 * 1024 * 1024; // 0.5 MB
var user = JSON.parse(localStorage.getItem('userInfo'))?.name || 'MR';
var userId = JSON.parse(localStorage.getItem('userInfo'))?.id;

// Computed property to display messages based on active tab
const currentMessages = computed(() => {
  if (activeTab.value === 'ai') return aiMessages.value;
  return messageSend.value;
});

// Popup Functions
const saveName = () => {
  if (userName.value) {
    localStorage.setItem('userInfo', JSON.stringify({ name: userName.value, id: socket.id }));
    showPopup.value = false;
    window.location.reload();
  } else {
    alert('Vui lòng nhập tên');
  }
};

const closePopup = () => {
  showPopup.value = false;
};

// Chat Functions
const toggleSuggestions = () => {
  showSuggestions.value = !showSuggestions.value;
};

const switchTab = (tab) => {
  activeTab.value = tab;
  if (tab === 'human') {
    showPopup.value = !localStorage.getItem('userInfo');
    socket.emit('join', user);
    socket.emit('register', userId);
    getMessages();
    //listenForMessages();
    //showPopup.value = false;
  }
  else {
    showPopup.value = false;
  }
  scrollToBottom();
};

const scrollToBottom = () => {
  nextTick(() => {
    const el = messagesRef.value;
    if (el) el.scrollTop = el.scrollHeight;
  });
};

const handleFileUpload = (event) => {
  file.value = event.target.files[0];
};

const convertFileToBase64 = (file) => {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.onload = () => resolve(reader.result);
    reader.onerror = (error) => reject(error);
    reader.readAsDataURL(file);
  });
};

const sendMessage = async (suggestion = null) => {
  const msg = suggestion || message.value.trim();
  if (!msg && !file.value) return;

  if (file.value && file.value.size > MAX_FILE_SIZE) {
    alert('File size exceeds limit of 0.5MB. Please choose a smaller file.');
    return;
  }

  // const fileBase64 = file.value ? await convertFileToBase64(file.value) : null;
  // const messageData = {
  //   user,
  //   userId,
  //   message: msg,
  //   socketId: socketId.value,
  //   file: fileBase64,
  // };
  let fileUrl = null;

if (file.value) {
  const formData = new FormData();
  formData.append('file', file.value);
  try {
    const response = await axios.post(`${apiUrl}/api/upload`, formData);
    fileUrl = response.data.url;
  } catch (error) {
    alert('Upload file thất bại!');
    return;
  }
}

const messageData = {
  user,
  userId,
  message: msg,
  socketId: socketId.value,
  file: fileUrl, // 👈 Gửi URL file thay vì base64
};

  if (activeTab.value === 'ai') {
    aiMessages.value.push(messageData);
    loading.value = true;
    try {
      const docResponse = await fetch(`${apiUrl}/api/chat-ai/hotel-info`);
      const hotelDocs = await docResponse.text();
      const prompt = `
      Người dùng name is ${nameU},
        Dưới đây là toàn bộ thông tin về khách sạn:
        ${hotelDocs}

        Người dùng hỏi: "${msg}"
        → Trả lời ngắn gọn, rõ ràng, thân thiện dựa trên thông tin khách sạn trên.
        → Trả lời như một lễ tân chuyên nghiệp, thân thiện, dễ hiểu. Dùng ngôn ngữ tiếng Việt tự nhiên, nhẹ nhàng.
      `;

      const response = await fetch(
        `https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=${API_KEY}`,
        {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            contents: [{ parts: [{ text: prompt }] }],
          }),
        }
      );

      const data = await response.json();
      const reply = data?.candidates?.[0]?.content?.parts?.[0]?.text || '❗ Không có phản hồi từ AI.';
      aiMessages.value.push({ user: 'AI', message: reply });
      message.value = ''; // Clear input after sending
    } catch (err) {
      aiMessages.value.push({ user: 'AI', message: 'Error: ' + err.message });
    } finally {
      loading.value = false;
      scrollToBottom();
    }
  } else {
    // messageSend.value.push(messageData);
    socket.emit('chat message', messageData);
    console.log('Sent message:', messageData);
  }

  message.value = '';
  file.value = null;
  showSuggestions.value = false;
  scrollToBottom();
};

const handleAvailabilityCheck = async () => {
  if (activeTab.value !== 'ai') return;
  const userText = 'Còn bao nhiêu phòng trống?';
  aiMessages.value.push({ user, message: userText });
  scrollToBottom();
  loading.value = true;

  try {
    const res = await axios.get(`${apiUrl}/api/chat-ai/check-availability`);
    const rooms = res.data;
    const roomList = rooms.map((room) => `- ${room.room_name}: ${room.available_rooms} phòng`).join('\n');
    const prompt = `
      Khách hỏi về tình trạng phòng trống.
      Dữ liệu hiện tại:
      ${roomList}
      Hãy trả lời khách bằng tiếng Việt, giọng thân thiện, dễ hiểu và chuyên nghiệp.
    `.trim();

    const response = await fetch(
      `https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=${API_KEY}`,
      {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ contents: [{ parts: [{ text: prompt }] }] }),
      }
    );

    const data = await response.json();
    const reply = data?.candidates?.[0]?.content?.parts?.[0]?.text || '❗ Không có phản hồi.';
    aiMessages.value.push({ user: 'AI', message: reply });
  } catch (err) {
    aiMessages.value.push({ user: 'AI', message: '❌ Không lấy được dữ liệu phòng.' });
  } finally {
    loading.value = false;
    scrollToBottom();
  }
};

// Socket.IO Functions
socket.on('connect', () => {
  socketId.value = socket.id;
  console.log(`Connected with socket ID: ${socket.id}`);
  socket.emit('join', user);
  socket.emit('register', userId);
});
 // Sự kiện khi kết nối thành công
  // Sự kiện khi mất kết nối
  socket.on('disconnect', (reason) => {
    console.warn('⚠️ Socket.IO bị ngắt kết nối:', reason)
  });

  // Sự kiện lỗi kết nối
  socket.on('connect_error', (error) => {
    console.error('❌ Lỗi kết nối Socket.IO:', error.message)
  });
const getMessages = () => {
  socket.emit('get user messages', userId);
};

const listenForMessages = () => {
  socket.on('chat messageSend', (data) => {
    //console.log(data);
    if (activeTab.value === 'human') {
      const messageObject = { user: data.user, message: data.message, userId: data.userId };
      if (data.file) {
        messageObject.file = data.file;
      }
      messageSend.value.push(messageObject);
      scrollToBottom();
    }
  });

};
socket.on('chat history', (chatMessages) => {
  messageSend.value = [];
  chatMessages.forEach((msg) => {
    messageSend.value.push(msg); // Thêm tin nhắn vào danh sách
  });
  //scrollToBottom();
});
// Lifecycle Hooks
onMounted(() => {
  getMessages();
  listenForMessages();
});

// onUnmounted(() => {
//   socket.disconnect();
// });
</script>

<style scoped>
.toggle-chat-btn {
  background: none;
  border: none;
  cursor: pointer;
  position: fixed;
  /* Đặt ở vị trí bạn muốn */
  bottom: 20px;
  /* Ví dụ: gần đáy */
  right: 20px;
  /* Ví dụ: gần bên phải */
  z-index: 1001;
  /* Đảm bảo nó nằm trên cùng */
}

.toggle-chat-btn>img {
  width: 50px;
  /* Kích thước hình ảnh */
  height: 50px;
  /* Kích thước hình ảnh */
  border-radius: 50%;
  /* Làm tròn hình ảnh */
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
  /* Thêm bóng đổ */
}

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
  width: 300px;
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

/* CHỈNH SỬA VỊ TRÍ CHAT WIDGET */
.chat-container {
  position: fixed;
  bottom: 90px;
  right: 20px;
  width: 360px;
  height: auto;
  background-color: transparent;
  z-index: 1000;
  padding: 0;
}

/* THU NHỎ KHUNG CHAT */
.chat-card {
  width: 100%;
  max-width: 360px;
  height: 600px;
  display: flex;
  flex-direction: column;
  background: white;
  border-radius: 16px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
  overflow: hidden;
  position: relative;
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

.chat-tabs {
  display: flex;
  background: #f3f4f6;
  border-bottom: 1px solid #e5e7eb;
}

.tab-btn {
  flex: 1;
  padding: 10px;
  background: none;
  border: none;
  font-size: 14px;
  cursor: pointer;
  text-align: center;
  color: #1e3a8a;
}

.tab-btn.active {
  background: #e0e7ff;
  font-weight: bold;
}

.chat-suggestions {
  padding: 10px;
  background: #f3f4f6;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}

.chat-suggestions button {
  background: #e0e7ff;
  border: none;
  border-radius: 4px;
  padding: 6px 10px;
  font-size: 12px;
  cursor: pointer;
  color: #1e3a8a;
}

.suggestion-buttons {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.suggestion-buttons button {
  font-size: 12px;
  padding: 4px 8px;
  border-radius: 12px;
  border: none;
  background-color: #e3efff;
  color: #0066cc;
  cursor: pointer;
  transition: 0.2s;
}

.suggestion-buttons button:hover {
  background-color: #cde2ff;
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

.message.loading .bubble {
  font-style: italic;
  color: #6b7280;
}

.dots::after {
  content: '';
  display: inline-block;
  animation: dots 1s steps(3, end) infinite;
}

@keyframes dots {
  0% {
    content: '';
  }

  33% {
    content: '.';
  }

  66% {
    content: '..';
  }

  100% {
    content: '...';
  }
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
  top: -210px;
  background: white;
  border: 1px solid #ddd;
  padding: 10px;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  z-index: 999;
  width: 320px;

}

.suggestions input[type="file"] {
  margin-bottom: 10px;
}

.suggestions button {
  color: black;

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
