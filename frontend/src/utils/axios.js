import axios from 'axios';
import Swal from 'sweetalert2';

const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL,
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
});

// --- REFRESH TOKEN LOGIC ---
let isRefreshing = false;
let failedQueue = [];

const processQueue = (error, token = null) => {
  failedQueue.forEach(prom => {
    if (error) {
      prom.reject(error);
    } else {
      prom.resolve(token);
    }
  });
  failedQueue = [];
};

api.interceptors.request.use(
  (config) => {
    // Determine if it's an admin request or client request based on URL
    if (config.url.includes('/admin')) {
      const adminToken = localStorage.getItem('admin_token');
      // Special case: if calling /refresh-token, we use the refresh_token in the Authorization header
      if (config.url.includes('/refresh-token')) {
        const adminRefreshToken = localStorage.getItem('admin_refresh_token');
        if (adminRefreshToken) {
          config.headers.Authorization = `Bearer ${adminRefreshToken}`;
        }
      } else if (adminToken) {
        config.headers.Authorization = `Bearer ${adminToken}`;
      }
    } else {
      // Client
      const clientToken = localStorage.getItem('access_token');
      if (config.url.includes('/refresh-token')) {
        const clientRefreshToken = localStorage.getItem('client_refresh_token');
        if (clientRefreshToken) {
          config.headers.Authorization = `Bearer ${clientRefreshToken}`;
        }
      } else if (clientToken) {
        config.headers.Authorization = `Bearer ${clientToken}`;
      }
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

api.interceptors.response.use(
  (response) => {
    return response;
  },
  async (error) => {
    const originalRequest = error.config;

    // Bỏ qua nếu lỗi không phải 401 hoặc API đang gọi đã là /refresh-token (để tránh lặp vô hạn)
    if (error.response?.status === 401 && !originalRequest._retry && !originalRequest.url.includes('/refresh-token') && !originalRequest.url.includes('/login')) {
      
      const isAdmin = originalRequest.url.includes('/admin');
      const refreshToken = isAdmin ? localStorage.getItem('admin_refresh_token') : localStorage.getItem('client_refresh_token');
      
      // Nếu không có refresh token thì bắt buộc phải login lại
      if (!refreshToken) {
        handleLogout(isAdmin);
        return Promise.reject(error);
      }

      if (isRefreshing) {
        // Đang lấy token mới, đưa request vào hàng đợi
        return new Promise(function(resolve, reject) {
          failedQueue.push({ resolve, reject });
        }).then(token => {
          originalRequest.headers['Authorization'] = 'Bearer ' + token;
          return api(originalRequest);
        }).catch(err => {
          return Promise.reject(err);
        });
      }

      originalRequest._retry = true;
      isRefreshing = true;

      try {
        // Gửi request lấy token mới
        const endpoint = isAdmin ? '/admin/refresh-token' : '/refresh-token';
        const res = await api.post(endpoint);
        const newAccessToken = res.data.token || res.data.access_token;
        const newRefreshToken = res.data.refresh_token;

        // Lưu token mới
        if (isAdmin) {
          localStorage.setItem('admin_token', newAccessToken);
          localStorage.setItem('admin_refresh_token', newRefreshToken);
        } else {
          localStorage.setItem('access_token', newAccessToken);
          localStorage.setItem('client_refresh_token', newRefreshToken);
        }

        // Cập nhật header cho request gốc và chạy lại các request trong queue
        api.defaults.headers.common['Authorization'] = 'Bearer ' + newAccessToken;
        originalRequest.headers['Authorization'] = 'Bearer ' + newAccessToken;
        
        processQueue(null, newAccessToken);
        isRefreshing = false;
        
        return api(originalRequest);
        
      } catch (refreshError) {
        // Refresh token cũng hết hạn hoặc hỏng
        processQueue(refreshError, null);
        isRefreshing = false;
        handleLogout(isAdmin);
        return Promise.reject(refreshError);
      }
    }

    return Promise.reject(error);
  }
);

function handleLogout(isAdmin) {
  if (isAdmin) {
    localStorage.removeItem('admin_token');
    localStorage.removeItem('admin_refresh_token');
    localStorage.removeItem('admin_info');
    Swal.fire({
      icon: 'warning',
      title: 'Hết phiên đăng nhập',
      text: 'Phiên đăng nhập của bạn đã hết hạn. Vui lòng đăng nhập lại để tiếp tục.',
      confirmButtonText: 'Đăng nhập lại'
    }).then(() => {
      window.location.href = '/admin/login';
    });
  } else {
    localStorage.removeItem('access_token');
    localStorage.removeItem('client_refresh_token');
    localStorage.removeItem('user_info');
    Swal.fire({
      icon: 'warning',
      title: 'Hết phiên đăng nhập',
      text: 'Phiên đăng nhập của bạn đã hết hạn. Vui lòng đăng nhập lại để tiếp tục.',
      confirmButtonText: 'Đăng nhập lại'
    }).then(() => {
      window.location.href = '/login';
    });
  }
}

export default api;
