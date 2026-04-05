// File: frontend/src/main.js

import { createApp } from 'vue'
import { createPinia } from 'pinia'

import App from './App.vue'
import router from './router'

import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap/dist/js/bootstrap.bundle.min.js'

// ==========================================
// 1. IMPORT THƯ VIỆN REAL-TIME
// ==========================================
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

// ==========================================
// 2. CẤU HÌNH LARAVEL ECHO (BỌC ÁO GIÁP BẢO VỆ MÀN HÌNH)
// ==========================================
const reverbKey = import.meta.env.VITE_REVERB_APP_KEY;

if (reverbKey) {
    try {
        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: import.meta.env.VITE_REVERB_APP_KEY,
            wsHost: import.meta.env.VITE_REVERB_HOST,
            wsPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
            wssPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
            forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
            enabledTransports: ['ws', 'wss'],

            authEndpoint: 'http://127.0.0.1:8000/api/broadcasting/auth',

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

// ==========================================
// 3. KHỞI TẠO APP VUE
// ==========================================
const app = createApp(App)

app.use(createPinia())
app.use(router)

app.mount('#app')