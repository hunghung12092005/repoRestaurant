import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import { inject } from 'vue';

window.Pusher = Pusher;
// const token = localStorage.getItem('tokenJwt');
const apiUrl = inject('apiUrl');
const echo = new Echo({
    broadcaster: 'pusher',
    key: 'e813391492d8b0e2e194',
    cluster: 'ap1',
    forceTLS: true,
    authEndpoint: `${apiUrl}/broadcasting/auth`,
    auth: {
        headers: {
            'Authorization': `Bearer ${localStorage.getItem('tokenJwt')}`,
            'Accept': 'application/json',
        },
    },
});

export default echo;