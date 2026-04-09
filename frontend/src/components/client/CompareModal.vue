<!-- File: frontend/src/components/client/CompareModal.vue -->
<template>
  <Teleport to="body">
    
    <!-- THANH CÔNG CỤ BÁM ĐÁY (FLOATING BAR) CHUẨN DESIGN -->
    <transition name="slide-up">
      <div v-if="compareList.length > 0 && !isMinimized" 
           class="compare-floating-bar fixed-bottom shadow-lg z-index-floating bg-white dark:bg-[#1a2533]" 
           style="border-top: 1px solid rgba(0,0,0,0.1); padding: 1rem;">
        
        <div class="d-flex align-items-center justify-content-between mx-auto flex-wrap gap-3" style="max-width: 1300px; width: 100%;">
          
          <!-- Trái: Các ô chứa sản phẩm -->
          <div class="d-flex gap-2 overflow-auto custom-scrollbar-x pe-2 pb-1 flex-grow-1">
            <!-- Hiển thị đủ số ô: tối thiểu 4 ô, nếu chọn nhiều hơn thì nó dài ra cho scroll -->
            <div v-for="i in Math.max(4, compareList.length)" :key="i" 
                 class="compare-slot border dark:border-gray-600 bg-white dark:bg-[#212529] d-flex align-items-center justify-content-center position-relative flex-shrink-0" 
                 style="width: 85px; height: 100px;">
              
              <template v-if="compareList[i-1]">
                <img :src="getImageUrl(compareList[i-1].thumbnail_image || compareList[i-1].image)" class="w-100 h-100 object-fit-cover p-1">
                <!-- Nút X góc mảnh, không bo tròn, màu đen -->
                <button class="btn-close-custom position-absolute top-0 end-0 m-1 text-dark dark:text-gray-300" @click="$emit('remove', i-1)">
                  <i class="bi bi-x fs-5"></i>
                </button>
              </template>
              
              <!-- Ô trống hiển thị dấu + màu xanh dương -->
              <template v-else>
                <i class="bi bi-plus fs-2 text-primary"></i>
              </template>
            </div>
          </div>

          <!-- Phải: Cụm Nút Thao Tác -->
          <div class="d-flex flex-column gap-2 flex-shrink-0 ms-md-3" style="width: 140px;">
            <button class="btn btn-success fw-bold w-100 py-2 shadow-sm rounded-1" 
                    style="background-color: #198754; border-color: #198754;"
                    @click="showModal = true" 
                    :disabled="compareList.length < 2">
              So sánh
            </button>
            <div class="d-flex gap-2 w-100">
              <button class="btn btn-danger flex-grow-1 d-flex align-items-center justify-content-center rounded-1 py-1 px-0 shadow-sm" 
                      style="background-color: #dc3545; border-color: #dc3545;" 
                      @click="$emit('clear')" title="Xóa tất cả">
                <i class="bi bi-trash3"></i>
              </button>
              <button class="btn bg-white dark:bg-[#2b3035] dark:text-gray-300 border border-secondary-subtle dark:border-gray-600 flex-grow-1 d-flex align-items-center justify-content-center rounded-1 py-1 px-0 text-dark shadow-sm hover-bg-light" 
                      @click="isMinimized = true" title="Thu gọn">
                <i class="bi bi-dash fs-5"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </transition>

    <!-- NÚT MỞ RỘNG (KHI THU GỌN) -->
    <transition name="fade">
      <div v-if="compareList.length > 0 && isMinimized" class="position-fixed bottom-0 end-0 m-4 z-index-floating">
         <button class="btn btn-success rounded-pill shadow-lg px-4 py-2 fw-bold text-white d-flex align-items-center gap-2 hover-transform" 
                 @click="isMinimized = false" style="background-color: #198754; border-color: #198754;">
           <i class="bi bi-arrow-left-right"></i> So sánh ({{ compareList.length }})
         </button>
      </div>
    </transition>

    <!-- POPUP BẢNG SO SÁNH FULL MÀN HÌNH CHUẨN DESIGN -->
    <transition name="zoom">
      <div v-if="showModal" class="compare-modal-fullscreen z-index-modal bg-white dark:bg-[#121416]">
        
        <!-- Header -->
        <div class="compare-modal-header d-flex justify-content-between align-items-center px-4 py-3 border-bottom dark:border-gray-700 bg-white dark:bg-[#1a2533] shadow-sm">
          <h5 class="fw-bold text-dark dark:text-white m-0">Bảng so sánh sản phẩm</h5>
          <button class="btn-close-custom text-muted hover-danger" @click="showModal = false"><i class="bi bi-x-lg fs-4"></i></button>
        </div>

        <!-- Body có Swiper Table -->
        <div class="compare-modal-body custom-scrollbar-x custom-scrollbar-y p-0 m-0 flex-grow-1 d-flex">
          <table class="compare-table w-100 m-0 bg-white dark:bg-[#1a2533]">
            <tbody>
              
              <!-- Dòng 1: Tiêu đề Sản Phẩm & Nút X -->
              <tr>
                <td class="sticky-col text-center text-dark dark:text-gray-200 fw-bold small text-uppercase">Sản phẩm</td>
                <td v-for="(p, index) in compareList" :key="'name'+p.id" class="product-col position-relative bg-white dark:bg-[#1a2533]">
                  <div class="d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark dark:text-gray-200 text-truncate pe-3" style="font-size: 1.05rem;" :title="p.name">{{ p.name }}</span>
                    <button class="btn-close-custom text-muted hover-danger flex-shrink-0" title="Gỡ khỏi so sánh" @click="removeAndCheck(index)"><i class="bi bi-x-lg fs-5"></i></button>
                  </div>
                </td>
              </tr>

              <!-- Dòng 2: Hình ảnh -->
              <tr>
                <td class="sticky-col text-center text-dark dark:text-gray-200 fw-bold small text-uppercase">Hình ảnh</td>
                <td v-for="p in compareList" :key="'img'+p.id" class="product-col text-center py-3">
                   <!-- Đã tối ưu kích thước ảnh để cân đối hơn -->
                   <img :src="getImageUrl(p.thumbnail_image || p.image)" class="rounded-3 object-fit-cover border shadow-sm dark:border-gray-600 bg-light" style="width: 160px; height: 210px;">
                </td>
              </tr>

              <!-- Dòng 3: Giá -->
              <tr>
                <td class="sticky-col text-center text-dark dark:text-gray-200 fw-bold small text-uppercase">Giá</td>
                <td v-for="p in compareList" :key="'price'+p.id" class="product-col text-danger fw-bold fs-5 text-center">{{ formatCurrency(p.base_price || p.price) }}</td>
              </tr>

              <!-- Dòng 4: Tình trạng -->
              <tr>
                <td class="sticky-col text-center text-dark dark:text-gray-200 fw-bold small text-uppercase">Tình trạng</td>
                <td v-for="p in compareList" :key="'status'+p.id" class="product-col text-dark dark:text-gray-300 fw-medium text-center">
                  {{ p.status === 'published' || p.status === 'active' ? 'Còn hàng' : 'Hết hàng' }}
                </td>
              </tr>

              <!-- Dòng 5: Thương hiệu -->
              <tr>
                <td class="sticky-col text-center text-dark dark:text-gray-200 fw-bold small text-uppercase">Thương hiệu</td>
                <td v-for="p in compareList" :key="'brand'+p.id" class="product-col text-dark dark:text-gray-300 fw-medium text-center">
                  {{ p.brand?.name || 'ZYRO' }}
                </td>
              </tr>

              <!-- Dòng 6: Màu sắc -->
              <tr>
                <td class="sticky-col text-center text-dark dark:text-gray-200 fw-bold small text-uppercase">Màu sắc</td>
                <td v-for="p in compareList" :key="'color'+p.id" class="product-col text-center">
                  <div class="d-flex justify-content-center gap-2 flex-wrap">
                    <div v-for="c in (p.colors || defaultColors)" :key="c.id" 
                         class="rounded-circle border border-secondary-subtle shadow-sm" 
                         :style="{ backgroundColor: c.hex, width: '22px', height: '22px' }" 
                         :title="c.name"></div>
                  </div>
                </td>
              </tr>

              <!-- Dòng 7: Size -->
              <tr>
                <td class="sticky-col text-center text-dark dark:text-gray-200 fw-bold border-bottom-0 small text-uppercase">Size</td>
                <td v-for="p in compareList" :key="'size'+p.id" class="product-col text-center border-bottom-0">
                  <div class="d-flex justify-content-center gap-2 flex-wrap">
                    <span v-for="s in (p.sizes || defaultSizes)" :key="s" 
                          class="bg-white dark:bg-[#2b3035] text-dark dark:text-gray-300 border dark:border-gray-600 px-3 py-1.5 fw-medium shadow-sm" style="font-size: 0.85rem;">
                      {{ s }}
                    </span>
                  </div>
                </td>
              </tr>

            </tbody>
          </table>
        </div>
      </div>
    </transition>

  </Teleport>
</template>

<script setup>
import { ref } from 'vue';
import defaultPlaceholder from '@/assets/images/defaults/client_placeholder.png';

const props = defineProps({
  compareList: {
    type: Array,
    required: true,
    default: () => []
  }
});

const emit = defineEmits(['remove', 'clear']);

const isMinimized = ref(false);
const showModal = ref(false);

// Dữ liệu giả lập cho những SP thiếu cấu hình màu/size
const defaultColors = [ { id: 1, name: 'Xám', hex: '#bdc3c7' }, { id: 2, name: 'Trắng', hex: '#ffffff' }, { id: 3, name: 'Xanh', hex: '#00a8ff' }, { id: 4, name: 'Đen', hex: '#000000' } ];
const defaultSizes = ['S', 'M', 'L', 'XL', '2XL'];

const getImageUrl = (path) => path ? `http://127.0.0.1:8000/storage/${path}` : defaultPlaceholder;

const formatCurrency = (val) => {
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val || 0);
};

const removeAndCheck = (index) => {
  emit('remove', index);
  if (props.compareList.length <= 2) {
    showModal.value = false; // Tự đóng bảng nếu xóa còn < 2 SP
  }
};
</script>

<style scoped>
/* Mức Z-index chuẩn xác */
.z-index-floating { z-index: 1070; }
.z-index-modal { z-index: 1090; }

/* CSS FULL MÀN HÌNH CHO MODAL */
.compare-modal-fullscreen {
  position: fixed;
  top: 0; left: 0;
  width: 100vw; height: 100vh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

/* ========================================================
   CSS CHO BẢNG SWIPER SO SÁNH (CỐ ĐỊNH CỘT TRÁI)
======================================================== */
.compare-table {
  border-collapse: collapse;
  table-layout: fixed; /* Chìa khóa vàng giúp các cột giãn đều nhau */
}

.compare-table th, .compare-table td {
  border: 1px solid #dee2e6;
  padding: 1rem; /* Đã thu gọn khoảng cách (padding) lại để gọn gàng hơn */
  vertical-align: middle;
}
html.dark .compare-table th, html.dark .compare-table td {
  border-color: #373b3e;
}

/* ĐÃ THU GỌN: Cột Label bên trái nhỏ hơn, gọn gàng hơn */
.sticky-col {
  position: sticky;
  left: 0;
  background-color: #f8f9fa; /* Màu nền xám nhạt để tách biệt */
  z-index: 5;
  width: 130px;
  min-width: 130px;
  max-width: 130px;
}
html.dark .sticky-col { background-color: #212529; }

/* ĐÃ LOẠI BỎ WIDTH CỐ ĐỊNH: Cho phép giãn đều 50-50 nếu chỉ có 2 SP */
.product-col {
  min-width: 280px; 
  scroll-snap-align: start; /* Khấc dừng khi vuốt */
}

/* ========================================================
   CSS NÚT BẤM VÀ SCROLLBAR
======================================================== */
.btn-close-custom {
  background: transparent;
  border: none;
  padding: 0;
  line-height: 1;
  cursor: pointer;
  transition: color 0.2s ease, transform 0.2s ease;
}

.hover-danger:hover { color: #dc3545 !important; transform: scale(1.2); }
.hover-bg-light:hover { background-color: #f8f9fa !important; }
html.dark .hover-bg-light:hover { background-color: rgba(255,255,255,0.05) !important; }
.hover-transform { transition: transform 0.2s ease, box-shadow 0.2s ease; }
.hover-transform:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.15) !important; }
.line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis; line-height: 1.4; }

/* Scrollbar Style - Cuộn ngang Swiper */
.compare-modal-body {
  overflow-x: auto;
  scroll-snap-type: x mandatory; /* Kích hoạt Swiper Native */
  -webkit-overflow-scrolling: touch;
}

.custom-scrollbar-y::-webkit-scrollbar { width: 5px; }
.custom-scrollbar-y::-webkit-scrollbar-thumb { background: var(--color-c-light, #94B4C1); border-radius: 10px; }
.custom-scrollbar-x::-webkit-scrollbar { height: 6px; }
.custom-scrollbar-x::-webkit-scrollbar-thumb { background: var(--color-c-light, #94B4C1); border-radius: 10px; }

/* Animations */
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
.slide-up-enter-active, .slide-up-leave-active { transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.4s ease; }
.slide-up-enter-from, .slide-up-leave-to { transform: translateY(100%); opacity: 0; }
.zoom-enter-active, .zoom-leave-active { transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
.zoom-enter-from, .zoom-leave-to { transform: scale(0.98); opacity: 0; }
</style>