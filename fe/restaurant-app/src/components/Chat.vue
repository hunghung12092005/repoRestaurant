<template>
    <!-- <div class="chat-container">
        <input v-model="message" @keyup.enter="sendMessage" placeholder="Type your message..." />
        <ul>
            <li v-for="(msg, index) in messages" :key="index">{{ msg.user }}: {{ msg.message }}</li>
        </ul>
        <button @click="getMessages">Get Chat History</button> 
    </div> -->

    <div class="--dark-theme" id="chat">
        <div class="chat__conversation-board">
            <!-- <div class="chat__conversation-board__message-container" v-for="(msg, index) in messageReceive"
                :key="index">
                <div class="chat__conversation-board__message__person">
                    <div class="chat__conversation-board__message__person__avatar"><img
                            src="https://static.vecteezy.com/system/resources/thumbnails/019/194/935/small_2x/global-admin-icon-color-outline-vector.jpg"
                            alt="Monika Figi" /></div>
                </div>
                <div class="chat__conversation-board__message__context">

                    <div class="chat__conversation-board__message__bubble"> <span>{{ msg.message }}</span></div>
                </div>
            </div> -->
            <div class="chat__conversation-board__message-container reversed" v-for="(msgSend, index) in messageSend"
                :key="index">
                <div class="chat__conversation-board__message__person">
                    <div class="chat__conversation-board__message__person__avatar"><img
                            src="https://th.bing.com/th/id/R.f357e2632f7052a0eac815cfb90ba680?rik=oUi9SIYz5kpY%2bw&pid=ImgRaw&r=0"
                            alt="message" /></div><span class="chat__conversation-board__message__person__nickname">{{
                                msgSend.user }}</span>
                </div>
                <div class="chat__conversation-board__message__context">
                    <div class="chat__conversation-board__message__bubble"> <span>{{ msgSend.message }}
                        <img v-if="msgSend.file" :src="msgSend.file" alt="Image" style="max-width: 200px;"/>
                    </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="chat__conversation-panel">
            <div class="chat__conversation-panel__container">

                <!-- <input class="chat__conversation-panel__input panel-item" placeholder="Type a message..." /> -->
                <button class="btn btn-info" @click="toggleSuggestions">💬</button>
                <div class="quick-suggestions" v-if="showSuggestions">
                    <input type="file" @change="handleFileUpload" accept="image/*" style="margin-right: 10px;" />
                    <div class="suggestion-item" v-for="suggestion in suggestions" :key="suggestion"
                        @click="sendMessage(suggestion)">
                        {{ suggestion }}
                    </div>
                </div>
                
                <input class="chat__conversation-panel__input panel-item" v-model="message" @keyup.enter="sendMessage()"
                    placeholder="Type your message..." />


                <button class="chat__conversation-panel__button panel-item btn-icon send-message-button">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        aria-hidden="true" data-reactid="1036">
                        <line x1="22" y1="2" x2="11" y2="13"></line>
                        <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import socket from '../socket'; // Import socket từ file chung

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

const sendMessage = async (suggestion) => {
    const msg = suggestion || message.value;
    if (msg.trim() === '' && !file.value) return; // Không gửi tin nhắn rỗng và không có file

    const messageData = {
        user: user,
        userId: userId,
        message: msg,
        socketId: socketId.value,
        file: file.value ? await convertFileToBase64(file.value) : null // Chuyển file thành base64
    };

    socket.emit('chat message', messageData);
    message.value = ''; // Reset tin nhắn
    file.value = null; // Reset file
    showSuggestions.value = false; // Ẩn gợi ý sau khi gửi
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
.quick-suggestions {
    position: fixed;
    width: 80%;
    bottom: 100px;
    left: 0;
    margin-bottom: 0;
    background: white;
    border: 1px solid #ccc;
    border-radius: 5px;
    padding: 15px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.suggestion-item {
    padding: 5px 10px;
    cursor: pointer;
    transition: background 0.3s;
}

.suggestion-item:hover {
    background: #f0f0f0;
}

.chat-input {
    margin-top: 50px;
    width: 100%;
    padding: 10px;
}

/* fix cứng dữ liệu */

/* fix cứng dữ liệu */
.--dark-theme {
    /* --chat-background: rgba(46, 150, 150, 0.95); */
    --chat-panel-background: #dbe0e3;
    --chat-bubble-background: #000000;
    --chat-add-button-background: #212324;
    --chat-send-button-background: #8147fc;
    --chat-text-color: #a3a3a3;
    --chat-options-svg: #a3a3a3;
}



#chat {
    background: var(--chat-background);
    max-width: 600px;
    margin: 60px auto;
    box-sizing: border-box;
    padding: 1em;
    border-radius: 12px;
    position: relative;
    overflow: hidden;
}

#chat::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;

    z-index: -1;
}

#chat .btn-icon {
    position: relative;
    cursor: pointer;
}

#chat .btn-icon svg {
    stroke: #FFF;
    fill: #FFF;
    width: 50%;
    height: auto;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

#chat .chat__conversation-board {
    padding: 1em 0 2em;
    height: calc(100vh - 55px - 2em - 25px * 2 - .5em - 3em);
    overflow: auto;
}

#chat .chat__conversation-board__message-container.reversed {
    flex-direction: row-reverse;
}

#chat .chat__conversation-board__message-container.reversed .chat__conversation-board__message__bubble {
    position: relative;
}

#chat .chat__conversation-board__message-container.reversed .chat__conversation-board__message__bubble span:not(:last-child) {
    margin: 0 2 10em 0;
}

#chat .chat__conversation-board__message-container.reversed .chat__conversation-board__message__person {
    margin: 0 0 0 1.2em;
}

#chat .chat__conversation-board__message-container.reversed .chat__conversation-board__message__options {
    align-self: center;
    position: absolute;
    left: 0;
    display: none;
}

#chat .chat__conversation-board__message-container {
    position: relative;
    display: flex;
    flex-direction: row;
}

#chat .chat__conversation-board__message-container:hover .chat__conversation-board__message__options {
    display: flex;
    align-items: center;
}

#chat .chat__conversation-board__message-container:hover .option-item:not(:last-child) {
    margin: 0 0.5em 0 0;
}

#chat .chat__conversation-board__message-container:not(:last-child) {
    margin: 0 0 2em 0;
}

#chat .chat__conversation-board__message__person {
    text-align: center;
    margin: 0 1.2em 0 0;
}

#chat .chat__conversation-board__message__person__avatar {
    height: 35px;
    width: 35px;
    margin-right: 10px;
    overflow: hidden;
    border-radius: 50%;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
    ms-user-select: none;
    position: relative;
}

#chat .chat__conversation-board__message__person__avatar::before {
    content: "";
    position: absolute;
    height: 100%;
    width: 100%;
}

#chat .chat__conversation-board__message__person__avatar img {
    height: 100%;
    margin: 0 auto;
    width: auto;
}

#chat .chat__conversation-board__message__person__nickname {
    font-size: 9px;
    color: #050505;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
    margin: 0 auto;
    margin-right: 10px;
    /* display: none; */
}

#chat .chat__conversation-board__message__context {
    max-width: 55%;
    align-self: flex-start;
}

#chat .chat__conversation-board__message__options {
    align-self: center;
    position: absolute;
    right: 0;
    display: none;
}

#chat .chat__conversation-board__message__options .option-item {
    border: 0;
    background: 0;
    padding: 0;
    margin: 0;
    height: 16px;
    width: 16px;
    outline: none;
}

#chat .chat__conversation-board__message__options .emoji-button svg {
    stroke: var(--chat-options-svg);
    fill: transparent;
    width: 100%;
}

#chat .chat__conversation-board__message__options .more-button svg {
    stroke: var(--chat-options-svg);
    fill: transparent;
    width: 100%;
}

#chat .chat__conversation-board__message__bubble span {
    width: -webkit-fit-content;
    width: -moz-fit-content;
    width: fit-content;
    display: inline-table;
    word-wrap: break-word;
    background: rgb(9, 148, 234);
    font-size: 13px;
    color: black;
    padding: 0.5em 2.8em;
    line-height: 2.5;
    border-radius: 6px;
    font-family: "Lato", sans-serif;
}

#chat .chat__conversation-board__message__bubble:not(:last-child) {
    margin: 0 0 0.3em;

}

#chat .chat__conversation-board__message__bubble:active {
    background: var(--chat-bubble-active-background);
}

#chat .chat__conversation-panel {
    background: var(--chat-panel-background);
    border-radius: 12px;
    padding: 0 1em;
    height: 55px;
    margin: 0.5em 0 0;
}

#chat .chat__conversation-panel__container {
    display: flex;
    flex-direction: row;
    align-items: center;
    height: 100%;
}

#chat .chat__conversation-panel__container .panel-item:not(:last-child) {
    margin: 0 1em 0 0;
}

#chat .chat__conversation-panel__button {
    background: grey;
    height: 20px;
    width: 30px;
    border: 0;
    padding: 0;
    outline: none;
    cursor: pointer;
}

#chat .chat__conversation-panel .add-file-button {
    height: 23px;
    min-width: 23px;
    width: 23px;
    background: var(--chat-add-button-background);
    border-radius: 50%;
}

#chat .chat__conversation-panel .add-file-button svg {
    width: 70%;
    stroke: #54575c;
}

#chat .chat__conversation-panel .emoji-button {
    min-width: 23px;
    width: 23px;
    height: 23px;
    background: transparent;
    border-radius: 50%;
}

#chat .chat__conversation-panel .emoji-button svg {
    width: 100%;
    fill: transparent;
    stroke: #54575c;
}

#chat .chat__conversation-panel .send-message-button {
    background: var(--chat-send-button-background);
    height: 30px;
    min-width: 30px;
    border-radius: 50%;
    transition: 0.3s ease;
}

#chat .chat__conversation-panel .send-message-button:active {
    transform: scale(0.97);
}

#chat .chat__conversation-panel .send-message-button svg {
    margin: 1px -1px;
}

#chat .chat__conversation-panel__input {
    width: 100%;
    height: 100%;
    outline: none;
    position: relative;
    color: var(--chat-text-color);
    font-size: 13px;
    background: transparent;
    border: 0;
    font-family: "Lato", sans-serif;
    resize: none;
}

@media only screen and (max-width: 600px) {
    #chat {
        margin: 0;
        border-radius: 0;
    }

    #chat .chat__conversation-board {
        height: calc(100vh - 55px - 2em - .5em - 3em);
    }

    #chat .chat__conversation-board__message__options {
        display: none !important;
    }
}
</style>