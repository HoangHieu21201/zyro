<!-- File: frontend/src/components/client/QuickViewModal.vue -->
<template>
  <!-- SỬ DỤNG TELEPORT ĐỂ ĐƯA MODAL RA KHỎI THẺ CHA, TRÁNH BỊ TRANSFORM LÀM HỎNG POSITION FIXED -->
  <Teleport to="body">
    <div class="quick-view-wrapper">
      <!-- Overlay mờ tối màn hình -->
      <transition name="fade">
        <div v-if="isOpen" class="qv-backdrop" @click="closeModal"></div>
      </transition>

      <!-- Khung Modal nổi lên -->
      <transition name="zoom">
        <div v-if="isOpen" class="qv-modal shadow-lg bg-white dark:bg-[#1a2533] rounded-3 overflow-hidden d-flex flex-column">
          
          <!-- HEADER XANH NGỌC ĐẶC TRƯNG -->
          <div class="qv-header bg-urban text-white p-3 d-flex justify-content-between align-items-center">
            <span class="fw-semibold text-truncate mb-0" style="font-size: 1rem;">Xem nhanh: {{ product.name }}</span>
            <button class="btn-close btn-close-white" @click="closeModal"></button>
          </div>

          <!-- NỘI DUNG SẢN PHẨM -->
          <div class="qv-body p-4 p-md-5 custom-scrollbar-y flex-grow-1">
            <div class="row g-4 g-lg-5">
              
              <!-- CỘT TRÁI: THƯ VIỆN ẢNH -->
              <div class="col-md-5">
                <!-- Ảnh chính -->
                <div class="main-img-box border border-light-subtle dark:border-gray-700 rounded-3 mb-3 bg-light dark:bg-[#121416] d-flex justify-content-center align-items-center overflow-hidden">
                  <img :src="activeImage || defaultImage" @error="handleImageError" class="w-100 h-100 object-fit-cover" style="aspect-ratio: 3/4;">
                </div>
                
                <!-- Thumbnails -->
                <div class="d-flex gap-2 overflow-auto custom-scrollbar-x pb-2">
                  <div v-for="(img, idx) in productGallery" :key="idx" 
                       class="thumb-box border rounded-3 cursor-pointer overflow-hidden flex-shrink-0" 
                       :class="activeImage === img ? 'border-urban border-2 shadow-sm' : 'border-light-subtle dark:border-gray-700'" 
                       @click="activeImage = img">
                    <img :src="img" @error="handleImageError" class="w-100 h-100 object-fit-cover p-1 rounded-3">
                  </div>
                </div>
              </div>

              <!-- CỘT PHẢI: THÔNG TIN CHI TIẾT & CHỌN MUA -->
              <div class="col-md-7 d-flex flex-column">
                <h4 class="fw-bold mb-2 text-dark dark:text-white">{{ product.name }}</h4>
                <div class="text-muted small mb-3">
                  Thương hiệu: <span class="text-urban">{{ product.brand || 'ZYRO' }}</span> | 
                  Mã sản phẩm: <span class="text-urban">{{ product.sku || 'Đang cập nhật' }}</span>
                </div>
                
                <div class="d-flex align-items-center gap-2 mb-3">
                  <h3 class="text-danger fw-bold mb-0">{{ formatCurrency(product.price) }}</h3>
                  <span v-if="product.old_price && product.old_price > product.price" class="text-muted text-decoration-line-through fw-semibold mt-1">
                    {{ formatCurrency(product.old_price) }}
                  </span>
                </div>
                
                <p class="text-muted small mb-4 pb-4 border-bottom dark:border-gray-700">{{ product.description || 'Thông tin sản phẩm đang cập nhật' }}</p>

                <!-- 1. MÀU SẮC -->
                <div class="mb-4">
                  <label class="fw-bold mb-2 d-block small text-dark dark:text-gray-200">
                    Màu sắc: <span class="fw-normal text-muted ms-1">{{ getSelectedColorName }}</span>
                  </label>
                  <div class="d-flex flex-wrap gap-2">
                    <div v-for="(color, idx) in productColors" :key="idx"
                         class="color-swatch rounded-2 cursor-pointer shadow-sm-hover"
                         :class="{'active-swatch': selectedColor === color.id}"
                         :style="{ backgroundColor: color.hex }"
                         @click="selectedColor = color.id"
                         :title="color.name">
                    </div>
                  </div>
                </div>

                <!-- 2. KÍCH CỠ -->
                <div class="mb-4">
                  <div class="d-flex justify-content-between mb-2">
                    <label class="fw-bold small text-dark dark:text-gray-200 m-0">Size:</label>
                    <a href="#" class="text-urban small text-decoration-none hover-underline">Hướng dẫn chọn Size</a>
                  </div>
                  <div class="d-flex flex-wrap gap-2">
                    <button v-for="size in productSizes" :key="size"
                            type="button"
                            class="btn size-btn fw-semibold"
                            :class="selectedSize === size ? 'btn-outline-urban active text-urban border-2' : 'btn-outline-secondary text-muted border border-light-subtle dark:border-gray-600'"
                            @click="selectedSize = size">
                      {{ size }}
                    </button>
                  </div>
                </div>

                <!-- 3. SỐ LƯỢNG & NÚT THÊM GIỎ HÀNG -->
                <div class="d-flex flex-wrap gap-3 align-items-stretch mt-auto pt-2">
                  <div class="quantity-box border border-light-subtle dark:border-gray-600 rounded-3 d-flex bg-light dark:bg-[#212529]" style="width: 120px; height: 46px;">
                    <button class="btn btn-light border-0 text-urban fw-bold fs-5 px-3 bg-transparent h-100 d-flex align-items-center" @click="quantity > 1 ? quantity-- : null"><i class="bi bi-dash"></i></button>
                    <input type="text" class="form-control border-0 text-center fw-bold px-0 h-100 bg-transparent dark:text-white" v-model="quantity" readonly>
                    <button class="btn btn-light border-0 text-urban fw-bold fs-5 px-3 bg-transparent h-100 d-flex align-items-center" @click="quantity++"><i class="bi bi-plus"></i></button>
                  </div>
                  
                  <!-- Nút Giỏ Hàng MÀU ĐỎ chuẩn mẫu -->
                  <button class="btn btn-danger flex-grow-1 fw-bold fs-6 shadow-sm hover-transform" style="border-radius: 8px;">
                    <i class="bi bi-cart-plus me-2"></i> Thêm vào giỏ hàng
                  </button>
                </div> <!-- End d-flex (3) -->

              </div> <!-- End col-md-7 -->
            </div> <!-- End row -->
          </div> <!-- End qv-body -->
        </div> <!-- End qv-modal -->
      </transition>
    </div> <!-- End quick-view-wrapper -->
  </Teleport>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import defaultImage from '@/assets/images/defaults/client_placeholder.png';

const props = defineProps({
  isOpen: { type: Boolean, default: false },
  product: {
    type: Object,
    default: () => ({ name: '', price: 0, image: '' })
  }
});

const emit = defineEmits(['close']);

// Trạng thái Form
const activeImage = ref('');
const selectedColor = ref(null);
const selectedSize = ref(null);
const quantity = ref(1);

const productColors = computed(() => {
  return props.product.colors && props.product.colors.length > 0 
    ? props.product.colors 
    : [ { id: 1, name: 'Xám', hex: '#808080' }, { id: 2, name: 'Xanh nhạt', hex: '#ADD8E6' }, { id: 3, name: 'Xanh dương', hex: '#2196F3' }, { id: 4, name: 'Đen', hex: '#000000' } ];
});

const productSizes = computed(() => {
  return props.product.sizes && props.product.sizes.length > 0 ? props.product.sizes : ['S', 'M', 'L', 'XL', '2XL', '3XL'];
});

const productGallery = computed(() => {
  if (props.product.images && props.product.images.length > 0) return props.product.images;
  return [props.product.image || defaultImage, props.product.image || defaultImage, props.product.image || defaultImage];
});

const getSelectedColorName = computed(() => {
  const c = productColors.value.find(col => col.id === selectedColor.value);
  return c ? c.name : 'Vui lòng chọn';
});

const formatCurrency = (val) => {
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val || 0);
};

const handleImageError = (e) => { e.target.src = defaultImage; };

const closeModal = () => {
  emit('close');
};

watch(() => props.isOpen, (val) => {
  if (val) {
    document.body.style.overflow = 'hidden';
    activeImage.value = props.product.image || defaultImage;
    quantity.value = 1;
    if (productColors.value.length > 0) selectedColor.value = productColors.value[0].id;
    if (productSizes.value.length > 0) selectedSize.value = productSizes.value[0];
  } else {
    document.body.style.overflow = '';
  }
});
</script>

<style scoped>
.bg-urban { background-color: var(--color-c-hover, #547792) !important; }
.text-urban { color: var(--color-c-hover, #547792) !important; }
.btn-outline-urban { color: var(--color-c-hover, #547792); border-color: var(--color-c-hover, #547792); }
.border-urban { border-color: var(--color-c-hover, #547792) !important; }

.qv-backdrop {
  position: fixed;
  top: 0; left: 0; width: 100vw; height: 100vh;
  background-color: rgba(0, 0, 0, 0.6);
  z-index: 1080;
}

.qv-modal {
  position: fixed;
  top: 50%; left: 50%;
  transform: translate(-50%, -50%);
  width: 95%; max-width: 900px;
  max-height: 90vh;
  z-index: 1090;
  display: flex;
  flex-direction: column;
}

.qv-body { overflow-y: auto; }

.thumb-box {
  width: 70px; height: 90px;
  transition: all 0.2s ease;
}
.thumb-box:hover { opacity: 0.8; }

.color-swatch {
  width: 45px; height: 30px;
  border: 2px solid transparent;
}
.color-swatch.active-swatch {
  border-color: #dc3545 !important;
  transform: scale(1.05);
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
.shadow-sm-hover:hover {
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.size-btn {
  min-width: 50px;
  height: 38px;
  border-radius: 4px;
  background-color: transparent;
  transition: all 0.2s ease;
}
.size-btn:hover {
  border-color: var(--color-c-hover, #547792) !important;
  color: var(--color-c-hover, #547792) !important;
}
.size-btn.active {
  background-color: rgba(84, 119, 146, 0.1);
}

.hover-transform { transition: transform 0.2s ease, box-shadow 0.2s ease; }
.hover-transform:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(220, 53, 69, 0.3) !important; }
.hover-underline:hover { text-decoration: underline !important; }

.custom-scrollbar-y::-webkit-scrollbar { width: 5px; }
.custom-scrollbar-y::-webkit-scrollbar-thumb { background: var(--color-c-light, #94B4C1); border-radius: 10px; }
.custom-scrollbar-x::-webkit-scrollbar { height: 5px; }
.custom-scrollbar-x::-webkit-scrollbar-thumb { background: var(--color-c-light, #94B4C1); border-radius: 10px; }

.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.zoom-enter-active, .zoom-leave-active { transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
.zoom-enter-from, .zoom-leave-to { transform: translate(-50%, -50%) scale(0.9); opacity: 0; }
</style>