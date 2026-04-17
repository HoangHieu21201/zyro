import { defineStore } from 'pinia';
import api from '@/utils/axios';
import { ZyroSwal } from '@/components/client/ZyroSwal';

export const useWishlistStore = defineStore('wishlist', {
  state: () => ({
    items: [], // Chỉ lưu mảng product_id để check trạng thái trái tim cho nhanh
    isLoading: false,
  }),

  actions: {
    async fetchWishlist() {
      const token = localStorage.getItem('access_token');
      if (!token) {
        this.items = [];
        return;
      }
      
      this.isLoading = true;
      try {
        const res = await api.get('/client/user/wishlist');
        if (res.data.success) {
          // ĐÃ FIX: Dữ liệu format chuẩn của chúng ta trả về 'id', không còn 'product_id' nữa
          this.items = res.data.data.map(item => item.id);
        }
      } catch (error) {
        console.error('Lỗi lấy danh sách yêu thích', error);
      } finally {
        this.isLoading = false;
      }
    },

    async toggleWishlist(productId) {
      const token = localStorage.getItem('access_token');
      if (!token) {
        ZyroSwal.toastError('Vui lòng đăng nhập để thêm vào yêu thích!');
        return false;
      }

      try {
        const res = await api.post('/client/user/wishlist/toggle', { product_id: productId });
        if (res.data.success) {
          ZyroSwal.toastSuccess(res.data.message);
          
          if (res.data.is_wishlisted) {
            // Thêm vào danh sách nếu chưa có
            if (!this.items.includes(productId)) this.items.push(productId);
          } else {
            // Gỡ khỏi danh sách
            this.items = this.items.filter(id => id !== productId);
          }
          
          return res.data.is_wishlisted;
        }
      } catch (error) {
        ZyroSwal.toastError(error.response?.data?.message || 'Có lỗi xảy ra!');
        return false;
      }
    }
  }
});