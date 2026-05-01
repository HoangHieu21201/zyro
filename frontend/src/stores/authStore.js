import { defineStore } from 'pinia';
import api from '@/utils/axios';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: JSON.parse(localStorage.getItem('user_info')) || null,
    isAuthenticated: !!localStorage.getItem('access_token')
  }),
  actions: {
    async fetchUser() {
      try {
        const token = localStorage.getItem('access_token');
        if (!token) return;

        // Gọi API lấy profile (đường dẫn theo ClientProfileController)
        const response = await api.get('/client/user/profile');
        if (response.data.success) {
          this.user = response.data.data;
          this.isAuthenticated = true;
          // Lưu lại thông tin vào Storage để các component khác xài nhanh
          localStorage.setItem('user_info', JSON.stringify(this.user));
          
          // Phát tín hiệu toàn hệ thống để cập nhật Avatar, Tên trên Header
          window.dispatchEvent(new CustomEvent('user-profile-updated'));
        }
      } catch (error) {
        console.error('Lỗi xác thực người dùng:', error);
        this.logout();
      }
    },
    logout() {
      this.user = null;
      this.isAuthenticated = false;
      localStorage.removeItem('access_token');
      localStorage.removeItem('user_info');
    }
  }
});