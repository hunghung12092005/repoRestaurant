<template>
  <div class="container-fluid py-3">
  <div class="card shadow-sm rounded-3 border-0 position-relative" style="max-width: 700px; margin: 0 auto; height: 85vh; display: flex; flex-direction: column;">
    <!-- Circular Badge in Top-Right Corner -->
    <span class="position-absolute top-0 end-0 m-2 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-semibold" style="width: 40px; height: 40px; font-size: 0.8rem;">
      Chat
    </span>

    <!-- Chat Header -->
    <div class="card-header bg-white border-bottom p-3 d-flex align-items-center">
      <img
        src="https://static.vecteezy.com/system/resources/thumbnails/019/194/935/small_2x/global-admin-icon-color-outline-vector.jpg"
        alt="Admin Avatar"
        class="rounded-circle me-2"
        style="width: 32px; height: 32px;"
      />
      <h6 class="mb-0 fw-semibold text-dark">Admin Support</h6>
    </div>

    <!-- Chat Body -->
    <div class="card-body p-3 flex-grow-1 overflow-auto" style="background: #f1f0f0;">
      <div class="chat__conversation-board d-flex flex-column gap-3">
        <!-- Admin Message (Left) -->
        <div class="d-flex mb-2 align-items-start" v-for="(msg, index) in messageReceive" :key="'receive-' + index">
          <div class="me-2">
            <img
              src="https://static.vecteezy.com/system/resources/thumbnails/019/194/935/small_2x/global-admin-icon-color-outline-vector.jpg"
              alt="Admin"
              class="rounded-circle"
              style="width: 30px; height: 30px;"
            />
          </div>
          <div class="bg-white p-3 rounded-3 shadow-sm" style="max-width: 60%; border-radius: 12px 12px 12px 0;">
            <span class="text-dark fs-6 fw-medium d-block mb-1">Admin</span>
            <span class="text-dark fs-6">{{ msg.message }}</span>
          </div>
        </div>

        <!-- User Message (Right) -->
        <div class="d-flex mb-2 justify-content-end align-items-start" v-for="(msgSend, index) in messageSend" :key="'send-' + index">
          <div class="bg-primary text-white p-3 rounded-3 shadow-sm" style="max-width: 60%; border-radius: 12px 12px 0 12px;">
            <span class="fs-6 fw-medium d-block mb-1">{{ msgSend.user }}</span>
            <span class="fs-6">{{ msgSend.message }}</span>
            <img
              v-if="msgSend.file"
              :src="msgSend.file"
              alt="Image"
              class="mt-2 rounded"
              style="max-width: 180px;"
            />
          </div>
          <div class="ms-2">
            <img
              src="https://th.bing.com/th/id/R.f357e2632f7052a0eac815cfb90ba680?rik=oUi9SIYz5kpY%2bw&pid=ImgRaw&r=0"
              alt="User"
              class="rounded-circle"
              style="width: 30px; height: 30px;"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Chat Input -->
    <div class="card-footer p-2 bg-white border-top">
      <div class="d-flex align-items-center gap-2">
        <!-- Suggestions Button -->
        <button class="btn btn-outline-light text-dark rounded-circle p-2" @click="toggleSuggestions">
          <i class="bi bi-plus-lg"></i>
        </button>

        <!-- Suggestions Dropdown -->
        <div v-if="showSuggestions" class="dropdown-menu show p-2" style="min-width: 250px; transform: translateY(-120px);">
          <input
            type="file"
            @change="handleFileUpload"
            accept="image/*"
            class="form-control form-control-sm mb-2"
          />
          <button
            v-for="suggestion in suggestions"
            :key="suggestion"
            class="btn btn-outline-secondary btn-sm d-block w-100 mb-1 text-start"
            @click="sendMessage(suggestion)"
          >
            {{ suggestion }}
          </button>
        </div>

        <!-- Message Input -->
        <input
          class="form-control rounded-pill fs-6"
          v-model="message"
          @keyup.enter="sendMessage()"
          placeholder="Type a message..."
          style="border: 1px solid #e5e5e5;"
        />

        <!-- Send Button -->
        <button class="btn btn-primary rounded-circle p-2" @click="sendMessage()">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            width="18"
            height="18"
            fill="currentColor"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
          >
            <line x1="22" y1="2" x2="11" y2="13"></line>
            <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
          </svg>
        </button>
      </div>
    </div>
  </div>
</div>
</template>

    <script setup>
    import { ref, onMounted, onUnmounted } from 'vue';
    //import socket from '../socket'; // Import socket từ file chung

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

    const MAX_FILE_SIZE = 0.5 * 1024 * 1024; // 5 MB

    const sendMessage = async (suggestion) => {
        const msg = suggestion || message.value;
        if (msg.trim() === '' && !file.value) return;

        if (file.value && file.value.size > MAX_FILE_SIZE) {
            console.error('File size exceeds limit of 5MB');
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

   