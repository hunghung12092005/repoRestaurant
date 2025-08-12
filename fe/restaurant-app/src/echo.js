import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;
// const token = localStorage.getItem('tokenJwt');

const echo = new Echo({
    broadcaster: 'pusher',
    key: 'e813391492d8b0e2e194',
    cluster: 'ap1',
    forceTLS: true,
    authEndpoint: 'http://127.0.0.1:8000/broadcasting/auth',
    auth: {
        headers: {
            'Authorization': `Bearer ${localStorage.getItem('tokenJwt')}`,
            'Accept': 'application/json',
        },
    },
});

export default echo;