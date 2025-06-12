import { io } from 'socket.io-client';

const socket = io('http://localhost:6001'); // Kết nối đến server Socket.IO

export default socket;