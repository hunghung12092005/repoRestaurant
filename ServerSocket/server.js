import express from 'express';
import http from 'http';
import { Server } from 'socket.io';
import { createClient } from 'redis';
import { createAdapter } from 'socket.io-redis';
import cors from 'cors'; // Sử dụng default export
const app = express();
// Cấu hình CORS cho Express
app.use(cors({
    origin: "http://127.0.0.1:5173",
    methods: ["GET", "POST"],
    allowedHeaders: ["Content-Type", "Authorization"],
    credentials: true
}));
const server = http.createServer(app);
const io = new Server(server, {
    cors: {
        origin: "http://127.0.0.1:5173",
        methods: ["GET", "POST"],
        allowedHeaders: ["Content-Type", "Authorization"],
        credentials: true
    }
});

// Cấu hình Redis

const redisClient = createClient({
    // url: 'redis://localhost:6379',
    // password: '12092005a' // Thay thế bằng mật khẩu bạn đã cấu hình
});


// Kết nối adapter Redis cho Socket.IO
io.adapter(createAdapter(redisClient));

redisClient.connect()
    .then(() => {
        console.log('Connected to Redis');
    })
    .catch((err) => {
        console.error('Redis connection error:', err);
    });

const socketIds = {}; // Đối tượng để lưu socketId của người dùng
import fs from 'fs';
import path from 'path';

// Xác định đường dẫn thư mục uploads
const uploadDir = path.join(process.cwd(), 'uploads');
// Phục vụ file tĩnh từ thư mục uploads
app.use('/uploads', express.static(uploadDir));
// Kiểm tra xem thư mục đã tồn tại chưa, nếu chưa thì tạo mới
if (!fs.existsSync(uploadDir)) {
    fs.mkdirSync(uploadDir); // Tạo thư mục nếu chưa tồn tại
}
// Hàm lưu file
const saveFile = (dataUrl) => {
    try {
        const base64Data = dataUrl.split(',')[1]; // Tách base64
        const buffer = Buffer.from(base64Data, 'base64'); // Chuyển đổi base64 thành buffer
        const fileName = `image-${Date.now()}.png`; // Tạo tên file duy nhất
        const filePath = path.join(uploadDir, fileName); // Đường dẫn đầy đủ

        fs.writeFileSync(filePath, buffer); // Lưu file
        return filePath; // Trả về đường dẫn file đã lưu
    } catch (error) {
        console.error('Error saving file:', error);
        throw error; // Ném lỗi ra ngoài để xử lý ở nơi gọi
    }
};
io.on('connection', (socket) => {
    console.log(`Client connected: ${socket.id}`);
    socket.on('join', (userName) => {
        socket.userName = userName;
        // console.log(socket.userName);
        io.emit('noticeAdminJoin', { user: 'System', message: `${userName} vừa vào phòng.` });
    });
    socket.on('register', (userId) => {
        console.log(`User registered with ID: ${userId}`);
        socket.userId = userId;
        socketIds[userId] = socket.id;
    });
    // Xử lý nhận tin nhắn và lưu vào Redis
    socket.on('chat message', (data) => {
        // Kiểm tra xem có file không
        //console.log('Received message:', data);
        if (data.file) {
            //console.log('Received file:');
            const filePath = saveFile(data.file); // Lưu file và lấy đường dẫn
            data.file = `http://localhost:6001/uploads/${path.basename(filePath)}`; // Cập nhật đường dẫn HTTP
        }
        // Gửi lại thông điệp cho client
        io.to(data.socketId).emit('chat messageSend', {
            user: data.user,
            userId: data.userId,
            message: data.message,
            file: data.file // Gửi đường dẫn file
        });
    
        // Gửi lại cho admin
        io.emit('chat messageSendAdmin', data);
    
        const recipientId = '6'; // ID của admin
        const key = `chat:${data.userId}:${recipientId}`;
        redisClient.lPush(key, JSON.stringify(data)).catch(err => {
            console.error('Error saving message to Redis:', err);
        });
    });

    // Lấy lịch sử tin nhắn ở client cho user đó
    socket.on('get user messages', (userId) => {
        const recipientId = '6'; // ID của admin
        const key = `chat:${userId}:${recipientId}`;
        redisClient.lRange(key, 0, -1).then(messages => {
            const parsedMessages = messages.map(msg => JSON.parse(msg)).reverse();
            socket.emit('chat history', parsedMessages); // Gửi lịch sử tin nhắn về client
        }).catch(err => {
            console.error('Error fetching messages:', err);
            socket.emit('chat history', []);
        });
    });
    //phần admin
    socket.on('chat messageToUser', (data) => {
        const socketId = socketIds[data.userId]; // Lấy socketId từ userId
        //console.log('socketID gửi tới người dùng',socketId);
        //console.log(data);
        if (socketId) {
            io.to(socketId).emit('chat messageSend', {
                user: data.user,
                message: data.message,
                userId: data.userId
            });
            io.emit('chat messageSendAdmin', data); // Trả về cho admin
            const recipientId = '6'; // ID của admin
            const key = `chat:${data.userId}:${recipientId}`;
            //console.log('Redis key:', key);
            redisClient.lPush(key, JSON.stringify(data)).catch(err => {
                console.error('Error saving message to Redis:', err);
            });
        } else {
            console.log(`User with ID ${data.userId} is not connected.`);
        }
    });
    //
    socket.on('disconnect', () => {
        if (socket.userName) {
            io.emit('noticeAdminJoin', { user: 'Thông Báo', message: `${socket.userName} đã rời khỏi phòng chat.` });
        }
    });
});

// Endpoint để lấy tất cả tin nhắn
app.get('/api/messages', async (req, res) => {
    try {
        const keys = await redisClient.keys('chat:*');
        const allMessages = [];
        await Promise.all(keys.map(async (key) => {
            const messages = await redisClient.lRange(key, 0, -1);
            const parsedMessages = messages.map(msg => JSON.parse(msg));
            allMessages.push(...parsedMessages);
        }));

        res.json(allMessages);
    } catch (error) {
        console.error('Error fetching messages:', error);
        res.status(500).send('Internal Server Error');
    }
});

// Endpoint để xóa tất cả tin nhắn
app.delete('/api/messages', async (req, res) => {
    try {
        const keys = await redisClient.keys('chat:*');
        await Promise.all(keys.map(async (key) => {
            await redisClient.del(key); // Xóa từng khóa tin nhắn
        }));

        res.status(204).send(); // Trả về phản hồi thành công với mã 204
    } catch (error) {
        console.error('Error deleting messages:', error);
        res.status(500).send('Internal Server Error');
    }
});
//xem key
app.get('/api/messages/:userId/:recipientId', async (req, res) => {
    const { userId, recipientId } = req.params;
    const key = `chat:${userId}:${recipientId}`;
    try {
        // Lấy tin nhắn từ Redis
        const messages = await redisClient.lRange(key, 0, -1);
        // Đảo ngược thứ tự tin nhắn
        const parsedMessages = messages.map(msg => JSON.parse(msg)).reverse();
        res.json(parsedMessages);
    } catch (error) {
        console.error('Error fetching messages:', error);
        res.status(500).send('Internal Server Error');
    }
});
//phần admin
app.get('/api/users', async (req, res) => {
    try {
        const keys = await redisClient.keys('chat:*:6');
        const users = new Set();

        // Lưu trữ thông tin người dùng
        const userInfo = {};

        // Lấy danh sách người dùng từ Redis
        await Promise.all(keys.map(async key => {
            const parts = key.split(':');
            const userId = parts[1];
            if (userId !== '6') {
                users.add(userId);

                // Lấy thông tin người dùng từ Redis
                const userDetail = await redisClient.hGet(`userId:${userId}`, 'user'); // Lấy tên người dùng
                if (userDetail) {
                    userInfo[userId] = userDetail; // Lưu tên người dùng vào userInfo
                }
            }
        }));

        // Tạo mảng người dùng với thông tin tên
        const userArray = Array.from(users).map(userId => ({
            userId,
            name: userInfo[userId] || 'Unknown' // Lấy tên hoặc 'Unknown' nếu không có
        }));

        res.json(userArray);
        // Phát sự kiện sau khi trả về danh sách người dùng
        io.emit('userListUpdated', userArray);
    } catch (error) {
        console.error('Error fetching users:', error);
        res.status(500).send('Internal Server Error');
    }
});

server.listen(6001, () => {
    console.log('Socket.IO server running on port 6001');
});