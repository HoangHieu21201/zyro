import axios from 'axios';

// 1. Tạo một instance mới của axios với cấu hình mặc định
const api = axios.create({
  // Lấy URL từ file .env (ví dụ: VITE_API_BASE_URL=http://127.0.0.1:8000/api/v1)
  baseURL: import.meta.env.VITE_API_BASE_URL,
  timeout: 10000, // Quá 10 giây không phản hồi sẽ báo lỗi mạng
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
});

// ==============================================================
// 2. REQUEST INTERCEPTOR: Tự động gắn Token vào mọi request
// ==============================================================
api.interceptors.request.use(
  (config) => {
    // ĐÃ FIX: Phân luồng Token thông minh dựa trên Endpoint
    // Tránh tình trạng "râu ông nọ cắm cằm bà kia" gây lỗi 500 Server
    
    if (config.url.includes('/admin')) {
      const adminToken = localStorage.getItem('admin_token');
      if (adminToken) {
        config.headers.Authorization = `Bearer ${adminToken}`;
      }
    } else if (config.url.includes('/client')) {
      const clientToken = localStorage.getItem('access_token');
      if (clientToken) {
        config.headers.Authorization = `Bearer ${clientToken}`;
      }
    }
    
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// ==============================================================
// 3. RESPONSE INTERCEPTOR: Xử lý lỗi chung toàn cục
// ==============================================================
api.interceptors.response.use(
  (response) => {
    // Nếu API trả về OK, cứ thế pass qua
    return response;
  },
  (error) => {
    // Nếu token hết hạn (lỗi 401), văng cảnh báo
    if (error.response && error.response.status === 401) {
      console.warn("Token hết hạn hoặc không hợp lệ. Vui lòng đăng nhập lại!");
    }
    return Promise.reject(error);
  }
);

export default api;