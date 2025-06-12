import express from 'express';
import http from 'http';
import { Server } from 'socket.io';
import redis from 'redis';
import { createAdapter } from 'socket.io-redis';

const app = express();
const server = http.createServer(app);
const io = new Server(server, {
    cors: {
        origin: "http://127.0.0.1:5173", // Địa chỉ frontend của bạn
        methods: ["GET", "POST"],
        allowedHeaders: ["Content-Type", "Authorization"], // Thêm Authorization nếu cần
        credentials: true
    }   
});

// Redis configuration
const redisClient = redis.createClient();
io.adapter(createAdapter(redisClient));

io.on('connection', (socket) => {
    // console.log('A user connected');

    // Gửi thông báo đến tất cả người dùng khi có người kết nối
    socket.on('join', (userName) => {
        socket.userName = userName; // Lưu tên người dùng vào socket toàn cục
        // console.log(`User connected: ${userName}`); // Hiển thị tên người dùng trong console
        io.emit('chat message', { user: 'System', message: `${userName} vừa vào phòng.` }); //io.emit là gửi tin nhắn tới vue để hiển thị lên tạo ra cái biến có tên chat message để lưu thông tin cần lưu
    });
    // cái io io.emit('chat message') nó đang là kiểu object, nên khi nhận được tin nhắn từ vue thì nó sẽ là kiểu object
    socket.on('chat message', (data) => {
        // console.log('Message received:', data); // In ra tin nhắn nhận được
        io.emit('chat message', data); //io.emit là gửi tin nhắn tới vue để hiển thị lên
    });

    socket.on('disconnect', () => {
        // console.log('User disconnected');
        // Sử dụng socket.userName để phát thông báo
        if (socket.userName) {//socket.userName là tên người dùng đã lưu khi kết nối
            io.emit('chat message', { user: 'Thông Báo', message: `${socket.userName} đã rời khỏi phòng chat.` });
        }
    });
});
// io.on('disconnect', (socket) =>{
//     socket.on('disconnect', (userName) => {
//         io.emit('chat message', { user: 'Thông Báo', message: `${userName} đã rời khỏi phòng chat.` });
//         // console.log('User disconnected');
//     });
// });
server.listen(6001, () => {
    console.log('Socket.IO server running on port 6001');
});