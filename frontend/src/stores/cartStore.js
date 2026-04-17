import { defineStore } from 'pinia';
import api from '@/utils/axios';
import Swal from 'sweetalert2';

export const useCartStore = defineStore('cart', {
  state: () => ({
    items: [], 
    isLoading: false,
  }),

  getters: {
    totalQuantity: (state) => {
      return state.items.reduce((total, item) => total + item.quantity, 0);
    },
    totalPrice: (state) => {
      return state.items.reduce((total, item) => {
        const price = item.current_price || item.price || 0;
        return total + (price * item.quantity);
      }, 0);
    }
  },

  actions: {
    async initCart() {
      const token = localStorage.getItem('access_token'); 
      if (token) {
        await this.fetchDBCart();
      } else {
        const localCart = localStorage.getItem('zyro_guest_cart');
        this.items = localCart ? JSON.parse(localCart) : [];
      }
    },

    async fetchDBCart() {
      this.isLoading = true;
      try {
        const res = await api.get('/client/cart');
        if (res.data.success) {
          this.items = res.data.data;
        }
      } catch (error) {
        console.error('Lỗi khi lấy giỏ hàng từ server:', error);
      } finally {
        this.isLoading = false;
      }
    },

    async addToCart(variantId, quantity, productData) {
      const token = localStorage.getItem('access_token');

      if (token) {
        try {
          const res = await api.post('/client/cart/add', {
            variant_id: variantId,
            quantity: quantity
          });
          if (res.data.success) {
            await this.fetchDBCart(); 
          }
        } catch (error) {
          throw error; 
        }
      } else {
        let localCart = JSON.parse(localStorage.getItem('zyro_guest_cart')) || [];
        const existingIndex = localCart.findIndex(item => item.variant_id === variantId);

        if (existingIndex > -1) {
          localCart[existingIndex].quantity += quantity;
          if (localCart[existingIndex].quantity > 50) localCart[existingIndex].quantity = 50;
        } else {
          localCart.push({
            variant_id: variantId,
            quantity: quantity,
            product_name: productData.name,
            current_price: productData.price,
            image: productData.image,
            attributes: `${productData.color || ''} ${productData.size ? '- ' + productData.size : ''}`.trim(),
            product_slug: productData.slug,
          });
        }
        localStorage.setItem('zyro_guest_cart', JSON.stringify(localCart));
        this.items = localCart;
      }
    },

    // ==================================================
    // ĐÃ BỔ SUNG: CẬP NHẬT SỐ LƯỢNG
    // ==================================================
    async updateQuantity(itemId, variantId, quantity) {
      const token = localStorage.getItem('access_token');
      if (token) {
        try {
          // Gửi API thay đổi số lượng (Cần có item_id từ DB)
          await api.put(`/client/cart/${itemId}`, { quantity });
          await this.fetchDBCart();
        } catch (error) {
          console.error(error);
          Swal.fire({toast: true, position: 'top-end', icon: 'error', title: error.response?.data?.message || 'Lỗi cập nhật', showConfirmButton: false, timer: 1500});
          await this.fetchDBCart(); // Revert lại data nếu lỗi
        }
      } else {
        let localCart = JSON.parse(localStorage.getItem('zyro_guest_cart')) || [];
        const index = localCart.findIndex(i => i.variant_id === variantId);
        if (index > -1) {
          localCart[index].quantity = quantity;
          localStorage.setItem('zyro_guest_cart', JSON.stringify(localCart));
          this.items = localCart;
        }
      }
    },

    // ==================================================
    // ĐÃ BỔ SUNG: XÓA SẢN PHẨM KHỎI GIỎ
    // ==================================================
    async removeItem(itemId, variantId) {
      const token = localStorage.getItem('access_token');
      if (token) {
        try {
          await api.delete(`/client/cart/${itemId}`);
          await this.fetchDBCart();
        } catch (error) {
          console.error(error);
        }
      } else {
        let localCart = JSON.parse(localStorage.getItem('zyro_guest_cart')) || [];
        localCart = localCart.filter(i => i.variant_id !== variantId);
        localStorage.setItem('zyro_guest_cart', JSON.stringify(localCart));
        this.items = localCart;
      }
    },

    async mergeCartAfterLogin() {
      const localCart = JSON.parse(localStorage.getItem('zyro_guest_cart')) || [];
      if (localCart.length > 0) {
        try {
          await api.post('/client/cart/merge', { local_items: localCart });
          localStorage.removeItem('zyro_guest_cart');
        } catch (error) {
          console.error("Lỗi gộp giỏ hàng", error);
        }
      }
      await this.fetchDBCart();
    }
  }
});