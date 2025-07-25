import { io } from 'socket.io-client';

const socket = io('http://socket.hxhhotel.online:6001');
//const socket = 'j'; // Kết nối đến server Socket.IO
export default socket;