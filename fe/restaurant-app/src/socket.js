import { io } from 'socket.io-client';

const socket = io('https://socket.hxhhotel.online', {
  transports: ['websocket'],
  secure: true,
});

// const socket = io('http://localhost:6001', {
//   transports: ['websocket'],
//   secure: true,
// });


export default socket;
