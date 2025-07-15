<template>
    <div>
        <!-- Nút mở chat -->
        <button class="chat-toggle" @click="toggleChat">💬</button>

        <!-- Khung chat -->
        <div v-if="showChat" class="chatbox">
            <div class="chatbox-header">
                <div>
                    <strong>Hỏi đáp cùng ChatBot AI 🤖</strong>
                    <p class="sub-text">Hệ thống hỗ trợ tự động 24/7</p>
                </div>
                <span class="close-btn" @click="toggleChat">✕</span>
            </div>

            <!-- Gợi ý -->
            <div class="chat-suggestions">
                <button @click="handleAvailabilityCheck">📦 Còn phòng trống không?</button>
                <div class="suggestion-buttons">
    <button @click="fillMessage('🕒 Giờ nhận và trả phòng là khi nào?')">🕒Nhận/trả phòng?</button>
    <button @click="fillMessage('💰 Giá phòng bao nhiêu?')">💰 Giá phòng ?</button>
    <button @click="fillMessage('📞 Tôi muốn liên hệ khách sạn')">📞 Liên hệ khách sạn</button>
  </div>
            </div>

            <!-- Tin nhắn -->
            <div class="chat-messages" ref="messagesRef">
                <div v-for="(msg, index) in messages" :key="index" :class="['message', msg.sender]">
                    <div class="bubble">
                        <span v-if="msg.sender === 'admin'">🤖 </span>
                        <span v-if="msg.sender === 'user'">🧑 </span>{{ msg.text }}
                    </div>
                </div>

                <!-- Loading -->
                <div v-if="loading" class="message admin loading">
                    <div class="bubble">
                        🤖 AI đang suy nghĩ<span class="dots">...</span>
                    </div>
                </div>
            </div>

            <!-- Nhập tin -->
            <div class="chat-input">
                <input v-model="newMessage" @keydown.enter="sendMessage" placeholder="Bạn cần hỏi gì?" />
                <button :disabled="loading" @click="sendMessage">
                    {{ loading ? '...' : 'Gửi' }}
                </button>
            </div>

            <!-- Nút gọi nhân viên -->
            <div class="chat-footer">
                <button class="staff-button">
                    <router-link class="dropdown-item" to="/chat">
                  💬 Chat ngay với nhân viên
                </router-link>
                    
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, nextTick, inject } from 'vue'
import axios from 'axios'

const API_KEY = 'AIzaSyD8i7zo013XnMvK1w9g_Xg4eY5AJC-nY58'
//AIzaSyDdyQPlin693Vo16vKOWnI38qLJ5U2z5LQ,AIzaSyD8i7zo013XnMvK1w9g_Xg4eY5AJC-nY58
const showChat = ref(true)
const newMessage = ref('')
const loading = ref(false)
const messages = ref([
    { sender: 'admin', text: 'Xin chào! Tôi là AI ChatBot HXH Bạn muốn hỏi gì về khách sạn ạ?' },
])
const apiUrl = inject('apiUrl');
const messagesRef = ref(null)
function fillMessage(text) {
  newMessage.value = text
  sendMessage();
}

function toggleChat() {
    showChat.value = !showChat.value
}

function scrollToBottom() {
    nextTick(() => {
        const el = messagesRef.value
        if (el) el.scrollTop = el.scrollHeight
    })
}

async function sendMessage() {
    const userText = newMessage.value.trim();
    if (!userText || loading.value) return;

    messages.value.push({ sender: 'user', text: userText });
    newMessage.value = '';
    scrollToBottom();
    loading.value = true;

    try {
        // Gọi API docs từ Laravel
        const docResponse = await fetch(`${apiUrl}/api/chat-ai/hotel-info`);
        const hotelDocs = await docResponse.text();
        //console.log('Hotel Docs:', hotelDocs);
        // Prompt cho Gemini đọc nội dung trước khi trả lời
        const prompt = `
Dưới đây là toàn bộ thông tin về khách sạn (để AI đọc trước):
${hotelDocs}

Người dùng hỏi: "${userText}"
→ Hãy trả lời ngắn gọn, rõ ràng, thân thiện dựa trên thông tin khách sạn trên.
→ Trả lời như một lễ tân chuyên nghiệp, thân thiện, dễ hiểu. Dùng ngôn ngữ tiếng Việt tự nhiên, nhẹ nhàng.

`;

        // Gọi Gemini API
        const response = await fetch(
            'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' + API_KEY,
            {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    contents: [
                        {
                            parts: [{ text: prompt }],
                        },
                    ],
                }),
            }
        );

        const data = await response.json();
        const reply = data?.candidates?.[0]?.content?.parts?.[0]?.text || '❗ Không có phản hồi từ AI.';
        messages.value.push({ sender: 'admin', text: reply });

    } catch (err) {
        messages.value.push({
            sender: 'admin',
            text: '❌ Lỗi khi gọi Gemini: ' + err.message,
        });
    } finally {
        loading.value = false;
        scrollToBottom();
    }
}


async function sendQuick(text) {
    messages.value.push({ sender: 'user', text })
    scrollToBottom()
    await sendToGemini(text)
}

async function handleAvailabilityCheck() {
    const userText = 'Còn bao nhiêu phòng trống?'
    messages.value.push({ sender: 'user', text: userText })
    scrollToBottom()
    loading.value = true

    try {
        const res = await axios.get(`${apiUrl}/api/chat-ai/check-availability`)
        const rooms = res.data
        const roomList = rooms
            .map((room) => `- ${room.room_name}: ${room.available_rooms} phòng`)
            .join('\n')

        const prompt = `
Khách hỏi về tình trạng phòng trống.

Dữ liệu hiện tại:
${roomList}

Hãy trả lời khách bằng tiếng Việt, giọng thân thiện, dễ hiểu và chuyên nghiệp.
    `.trim()

        await sendToGemini(prompt)
    } catch (err) {
        messages.value.push({ sender: 'admin', text: '❌ Không lấy được dữ liệu phòng.' })
    } finally {
        loading.value = false
        scrollToBottom()
    }
}

async function sendToGemini(prompt) {
    loading.value = true
    scrollToBottom()

    try {
        const response = await fetch(
            `https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=${API_KEY}`,
            {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ contents: [{ parts: [{ text: prompt }] }] }),
            }
        )

        const data = await response.json()
        const reply = data?.candidates?.[0]?.content?.parts?.[0]?.text || '❗ Không có phản hồi.'

        messages.value.push({ sender: 'admin', text: reply })
    } catch (err) {
        messages.value.push({ sender: 'admin', text: '❌ Lỗi khi gọi Gemini: ' + err.message })
    } finally {
        loading.value = false
        scrollToBottom()
    }
}

function contactHuman() {
    messages.value.push({
        sender: 'admin',
        text: '💁 Vui lòng chờ trong giây lát, chúng tôi sẽ kết nối bạn với nhân viên hỗ trợ...',
    })
}
</script>

<style scoped>

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

.chat-toggle {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background: #4f46e5;
    color: white;
    border: none;
    border-radius: 50%;
    width: 52px;
    height: 52px;
    font-size: 24px;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    z-index: 1000;
}

.chatbox {
    position: fixed;
    bottom: 80px;
    right: 20px;
    width: 360px;
    max-height: 600px;
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 10px 24px rgba(0, 0, 0, 0.25);
    display: flex;
    flex-direction: column;
    z-index: 999;
    overflow: hidden;
}

.chatbox-header {
    background: #4f46e5;
    color: white;
    padding: 12px 16px;
    font-size: 14px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.chatbox-header .sub-text {
    font-size: 11px;
    font-weight: normal;
    margin: 2px 0 0;
    opacity: 0.8;
}

.close-btn {
    font-size: 16px;
    cursor: pointer;
    opacity: 0.9;
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

.chat-messages {
    flex: 1;
    padding: 12px;
    overflow-y: auto;
    background: #fafafa;
}

.message {
    margin-bottom: 10px;
}

.message .bubble {
    padding: 8px 12px;
    border-radius: 8px;
    background: #e2e8f0;
    display: inline-block;
    max-width: 85%;
}

.message.user .bubble {
    background: #dbeafe;
    text-align: right;
    margin-left: auto;
}

.message.admin .bubble {
    background: #f3f4f6;
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

.chat-input {
    display: flex;
    border-top: 1px solid #ddd;
}

.chat-input input {
    flex: 1;
    padding: 10px;
    border: none;
    font-size: 13px;
    outline: none;
}

.chat-input button {
    padding: 10px 14px;
    border: none;
    background: #4f46e5;
    color: white;
    cursor: pointer;
    font-size: 13px;
}

.chat-footer {
    padding: 10px;
    background: #f9fafb;
    border-top: 1px solid #e5e7eb;
    text-align: center;
}

.staff-button {
    background: #10b981;
    color: white;
    border: none;
    border-radius: 6px;
    padding: 8px 12px;
    font-size: 13px;
    cursor: pointer;
}
</style>
