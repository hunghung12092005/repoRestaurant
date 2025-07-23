<template>
  <div class="container-fluid py-3" style="height: 85vh; overflow: hidden;">
    <div class="card shadow-sm rounded-4 border-0 h-100 d-flex flex-column" style="max-width: 1200px; margin: auto;">
      <!-- Header -->
      <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <span class="badge  text-dark rounded-circle px-3 py-2">CHAT REALTIME</span>
      </div>

      <div class="d-flex flex-grow-1 overflow-hidden">
        <!-- Sidebar -->
        <aside class="bg-light border-end" style="width: 300px; overflow-y: auto;">
          <div class="p-3 border-bottom bg-white">
            <div class="d-flex align-items-center">
              <img src="https://cdn.tgdd.vn/GameApp/3/222698/Screentshots/messages-222698-logo-16-05-2020.png"
                class="rounded-circle me-2" style="width: 40px; height: 40px;" />
              <div>
                <h6 class="mb-0 fw-bold">Admin</h6>
              </div>
            </div>
          </div>

          <ul class="list-group list-group-flush">
            <li v-for="user in users" :key="user.userId" @click="selectUser(user.userId)"
              class="list-group-item d-flex justify-content-between align-items-center list-group-item-action"
              :class="{ active: selectedUserId === user.userId }" style="cursor: pointer;">
              <div>
                <strong>{{ user.name }}</strong><br>
                <small class="text-muted">{{ user.userId }}</small>
              </div>
              <span v-if="unreadCounts[user.userId] > 0" class="badge bg-danger rounded-pill">{{
                unreadCounts[user.userId] }}</span>
            </li>
          </ul>
        </aside>

        <!-- Chat Content -->
        <section class="flex-grow-1 d-flex flex-column overflow-hidden">
          <!-- Chat header -->
          <div class="bg-white border-bottom p-3 d-flex align-items-center">
            <img
              src="https://png.pngtree.com/element_our/20190528/ourlarge/pngtree-blue-sms-message-communication-2-5d-resume-small-icon-image_1185735.jpg"
              class="rounded-circle me-2" style="width: 36px; height: 36px;" />
            <div>
              <h6 class="mb-0 text-dark">{{ selectedUserId || 'Chọn người dùng' }}</h6>
              <!-- <small class="text-muted">Đang trò chuyện</small> -->
            </div>
          </div>

          <!-- Message area -->
          <div class="flex-grow-1 overflow-auto p-3" style="background-color: #f0f2f5;">
            <div v-if="selectedMessages.length">
              <div v-for="(msg, index) in selectedMessages" :key="index" class="mb-3 d-flex"
                :class="{ 'justify-content-end': msg.user === 'Admin', 'justify-content-start': msg.user !== 'Admin' }">
                <div class="p-3 rounded-3 shadow-sm"
                  :class="{ 'bg-primary text-white': msg.user === 'Admin', 'bg-white': msg.user !== 'Admin' }"
                  style="max-width: 70%;">
                  <p class="mb-1"><strong>{{ msg.user }}:</strong> {{ msg.message }}</p>
                  <!-- hien thi anh tu client -->
                <img style="width: 400px;" v-if="msg.file" :src="msg.file" class="image-preview mt-2" />
                  <!-- Hiển thị ảnh admin gui-->
                  <div v-if="msg.file?.type?.startsWith('image/')">
                    <img :src="msg.file.data" :alt="msg.file.name || 'Ảnh đính kèm'"
                      style="max-width: 200px; border-radius: 8px; margin-top: 5px;" />
                  </div>
                </div>
              </div>
            </div>
            <div v-else class="text-center text-muted mt-5">
              <p>Vui lòng chọn người dùng để bắt đầu trò chuyện</p>
            </div>
          </div>

          <!-- Message input -->
          <div class="bg-light border-top p-3">
            <div class="input-group">
              <input v-model="messageContent" @keyup.enter="sendMessageToUser" type="text" class="form-control"
                placeholder="Nhập tin nhắn..." />

              <input ref="imageInput" type="file" accept="image/*" @change="handleImageUpload" class="form-control"
                style="max-width: 150px;" />

              <button class="btn btn-primary" @click="sendMessageToUser">
                <i class="fa fa-paper-plane"></i>
              </button>
            </div>
          </div>

        </section>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, inject } from 'vue';
import socket from '../socket'; // Import socket từ file chung
import axios from 'axios';

const users = ref([]);
const selectedMessages = ref([]);
const selectedMessagesReceive = ref([]);
const unreadCounts = ref({}); // { userId: số lượng chưa đọc }
const apiUrl = inject('apiUrl');
const joinNotice = ref([]);
const latestNotice = ref({ user: 'System', message: 'Start' }); // Biến để lưu thông báo mới nhất
const selectedUserId = ref(null);
socket.on('connect', () => {
  console.log(`Connected with socket ID: ${socket.id}`);
  socket.on('noticeAdminJoin', (data) => {
    const timestamp = new Date().toLocaleTimeString(); // Lấy thời gian hiện tại
    const noticeWithTime = {
      ...data, // Giữ lại các thuộc tính cũ
      time: timestamp // Thêm thuộc tính thời gian
    };
    joinNotice.value.push(noticeWithTime); // Thêm tin nhắn mới vào danh sách
    latestNotice.value = noticeWithTime; // Cập nhật thông báo mới nhất
    //console.log(latestNotice.value);
  });
  // Lắng nghe sự kiện khi danh sách người dùng được cập nhật
  socket.on('userListUpdated', (userArray) => {
    users.value = userArray; // Cập nhật danh sách người dùng
  });
  // console.log(users.value);
});
onMounted(async () => {
  listenForMessages();
  // Lấy danh sách người dùng khi component được mount
  // Lắng nghe cập nhật danh sách người dùng từ server
  socket.on('userListUpdated', (userArray) => {
    users.value = userArray;
  });
  // console.log(users.value);
  // Gửi yêu cầu lấy danh sách user ban đầu
  socket.emit('getUserList');
});
const getUserMessages = async (userId) => {
  selectedUserId.value = userId;
  const response = await fetch(`http://localhost:6001/api/messages/${userId}/6`);
  selectedMessages.value = await response.json();
};

const socketIdUser = ref();
const listenForMessages = () => {
  socket.on('chat messageSendAdmin', (data2) => {
    //console.log(data2);
    const userId = data2.userId;
    const messageObject = { user: data2.user, message: data2.message };
    if (data2.file) messageObject.file = data2.file;

    if (userId == selectedUserId.value) {
      // Admin đang xem user này → thêm trực tiếp vào tin nhắn
      selectedMessages.value.push(messageObject);
    } else {
      // Admin không xem user này → tăng số tin chưa đọc
      if (!unreadCounts.value[userId]) unreadCounts.value[userId] = 0;
      unreadCounts.value[userId]++;
    }
  });

  socket.on('chat messageReceiveAdmin', (dataReceive) => {
    //socketIdUser.value = dataReceive.socketID;
    if (dataReceive.userId == selectedUserId.value) {
      selectedMessagesReceive.value.push(dataReceive); // Thêm tin nhắn mới vào danh sách
      // console.log(selectedMessages.value.data);
      //socketIdUser.value = 
    }
  });
};
//gửi tin nhắn từ admin
const messageContent = ref(''); // Nội dung tin nhắn bạn muốn gửi
// Hàm chọn user
// const selectUser = async (userId) => {
//     selectedUserId.value = userId; // Lưu userId toàn cục
//     await getUserMessages(userId);  // Gọi hàm để lấy tin nhắn
//     // Gọi sendMessageToUser sau khi chọn user
// };
const selectUser = async (userId) => {
  selectedUserId.value = userId;
  unreadCounts.value[userId] = 0; // Đánh dấu đã đọc
  await getUserMessages(userId);
};

// const sendMessageToUser = () => {
//     //console.log(selectedUserId.value);
//     const messageData = {
//         user: 'Admin',
//         message: messageContent.value,
//         userId: selectedUserId.value
//     };
//     console.log('dữ liệu gửi đi:', messageData);
//     // Gửi dữ liệu đến server qua WebSocket
//     socket.emit('chat messageToUser', messageData);
//     messageContent.value = '';
// };
const imageFile = ref(null); // chứa ảnh
const MAX_FILE_SIZE = 0.5 * 1024 * 1024; // 0.5 MB

const imageInput = ref(null); // ref cho input file
const handleImageUpload = (event) => {
  const file = event.target.files[0];
  //console.log(file.size)
   if (file.size > MAX_FILE_SIZE) {
    alert('vui long chon file nho hon 1mb');
    messageContent.value = '';
  imageFile.value = null;
  if (imageInput.value) imageInput.value.value = '';
    return;
  }
  if (file && file.type.startsWith('image/')) {
    imageFile.value = file;
  } else {
    imageFile.value = null;
  }
};

const sendMessageToUser = async () => {
  try {
    if (!selectedUserId.value) return;

    const hasText = messageContent.value.trim() !== '';
    const hasImage = !!imageFile.value;

    if (!hasText && !hasImage) return;

    let fileUrl = null;

    if (hasImage) {
      const formData = new FormData();
      formData.append('file', imageFile.value);

      const res = await axios.post(`${apiUrl}/api/upload`, formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });

      if (res.data && res.data.url) {
        fileUrl = res.data.url;
      } else {
        console.error('Upload failed:', res.data);
        alert('Không thể upload ảnh.');
        return;
      }
    }

    const messageData = {
      user: 'Admin',
      userId: selectedUserId.value,
      message: messageContent.value.trim(),
      file: fileUrl,
    };

    socket.emit('chat messageToUser', messageData);

    // Reset form
    messageContent.value = '';
    imageFile.value = null;
    if (imageInput.value) imageInput.value.value = '';
  } catch (error) {
    console.error('Lỗi khi gửi tin nhắn:', error);
    alert('Đã xảy ra lỗi khi gửi tin nhắn.');
  }
};



</script>
