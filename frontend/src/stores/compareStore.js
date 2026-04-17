import { defineStore } from 'pinia';
import { ZyroSwal } from '@/components/client/ZyroSwal';

export const useCompareStore = defineStore('compare', {
  state: () => ({
    // Lấy dữ liệu cũ từ LocalStorage nếu có, không có thì mảng rỗng
    items: JSON.parse(localStorage.getItem('zyro_compare_list')) || []
  }),
  actions: {
    add(product) {
      if (this.items.find(p => p.id === product.id)) {
        ZyroSwal.toastSuccess('Sản phẩm đã có trong danh sách');
        return;
      }
      if (this.items.length >= 10) {
        ZyroSwal.toastSuccess('Chỉ được so sánh tối đa 10 sản phẩm');
        return;
      }
      this.items.push(product);
      this.save();
      ZyroSwal.toastSuccess('Đã thêm vào bảng so sánh');
    },
    remove(index) {
      this.items.splice(index, 1);
      this.save();
    },
    clear() {
      this.items = [];
      this.save();
    },
    save() {
      // Lưu thẳng xuống bộ nhớ trình duyệt
      localStorage.setItem('zyro_compare_list', JSON.stringify(this.items));
    }
  }
});