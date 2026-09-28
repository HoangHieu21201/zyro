import { createApp } from 'vue'
import { createPinia } from 'pinia'

import App from './App.vue'
import router from './router'

import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap/dist/js/bootstrap.bundle.min.js'
import { VueQueryPlugin } from '@tanstack/vue-query'

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const reverbKey = import.meta.env.VITE_REVERB_APP_KEY;

const backendHost = import.meta.env.VITE_API_BASE_URL
    ? import.meta.env.VITE_BACKEND_URL
    : import.meta.env.VITE_BACKEND_URL;

if (reverbKey) {
    try {
        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: import.meta.env.VITE_REVERB_APP_KEY,
            wsHost: import.meta.env.VITE_REVERB_HOST || '127.0.0.1',
            wsPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
            wssPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
            forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'http') === 'https',
            enabledTransports: ['ws', 'wss'],
            authEndpoint: `${backendHost}/api/broadcasting/auth`,
            auth: {
                headers: {
                    Authorization: 'Bearer ' + localStorage.getItem('admin_token'),
                    Accept: 'application/json'
                }
            }
        });
    } catch (error) {
        console.error("Lỗi cấu hình Websocket:", error);
    }
} else {
    console.warn("⚠️ BỎ QUA REAL-TIME: Không tìm thấy VITE_REVERB_APP_KEY trong file .env của Frontend. Vui lòng thêm key và khởi động lại npm run dev.");
}

const app = createApp(App)

app.use(createPinia())
app.use(router)
app.use(VueQueryPlugin)

app.mount('#app')