<template>
  <div class="product-card cursor-pointer w-100">
    
    <!-- KHUNG HÌNH ẢNH SẢN PHẨM -->
    <div class="product-img-wrapper position-relative overflow-hidden rounded-3 mb-3 bg-light dark:bg-[#1a2533]">
      
      <!-- currentImage sẽ tự động thay đổi khi bấm thẻ màu -->
      <img :src="currentImage" 
           @error="e => e.target.src = '/client_placeholder.png'"
           class="w-100 h-100 object-fit-cover product-img" 
           :alt="product.name">

      <!-- Badge Khuyến mãi -->
      <div class="position-absolute top-0 end-0 m-2 d-flex flex-column gap-1 badge-layer">
        <span v-if="product.discount_percent" class="badge bg-danger rounded px-2 py-1 shadow-sm fw-bold" style="font-size: 0.75rem;">
          -{{ product.discount_percent }}%
        </span>
      </div>

      <!-- Màng đen mờ khi Hover -->
      <div class="position-absolute top-0 start-0 w-100 h-100 hover-overlay pe-none"></div>

      <!-- BỘ NÚT BÊN PHẢI -->
      <div class="action-right-panel position-absolute d-flex flex-column gap-2" style="top: 15px; right: 15px;">
        <button class="btn bg-white rounded-circle shadow action-icon-btn d-flex align-items-center justify-content-center" title="Yêu thích" @click.stop="onWishlist">
          <i class="bi bi-heart text-dark fs-6"></i>
        </button>
        <button class="btn bg-white rounded-circle shadow action-icon-btn d-flex align-items-center justify-content-center" title="Xem nhanh" @click.stop="onQuickView">
          <i class="bi bi-eye text-dark fs-6"></i>
        </button>
        <button class="btn bg-white rounded-circle shadow action-icon-btn d-flex align-items-center justify-content-center" title="So sánh" @click.stop="onCompare">
          <i class="bi bi-arrow-left-right text-dark fs-6"></i>
        </button>
      </div>

      <!-- NÚT TÙY CHỌN DƯỚI ĐÁY -->
      <div class="action-bottom-panel position-absolute bottom-0 start-0 w-100 px-3 pb-3">
        <button class="btn bg-white w-100 fw-semibold shadow-lg rounded-2 py-2 text-dark action-btn-main d-flex align-items-center justify-content-center gap-2" @click.stop="onOptions">
          <i class="bi bi-grid text-secondary"></i> Tùy chọn
        </button>
      </div>
      
    </div>

    <!-- THÔNG TIN SẢN PHẨM -->
    <div class="product-info text-start px-1 overflow-hidden w-100">
      
      <!-- GIÁ TIỀN -->
      <div class="product-price d-flex align-items-center gap-2 mb-1 flex-wrap">
        <span class="fw-bold" 
              :class="product.old_price && product.old_price > product.price ? 'text-danger dark:text-red-400' : 'text-dark dark:text-white'" 
              style="font-size: 1.15rem;">
          {{ formatCurrency(product.price) }}
        </span>
        
        <span v-if="product.old_price && product.old_price > product.price" 
              class="text-muted text-decoration-line-through fw-medium" 
              style="font-size: 0.85rem;">
          {{ formatCurrency(product.old_price) }}
        </span>
      </div>

      <!-- TÊN SẢN PHẨM -->
      <h6 class="product-title text-dark dark:text-gray-200 mb-2 fw-normal line-clamp-1 w-100" :title="product.name" style="font-size: 0.95rem;">
        {{ product.name }}
      </h6>

      <!-- MÀU SẮC -->
      <div class="color-swatches d-flex gap-2" style="min-height: 24px;">
        <template v-if="product.colors && product.colors.length > 0">
          <div v-for="(color, index) in product.colors" :key="index" 
               class="swatch-item rounded-circle shadow-sm-hover"
               :class="{ 
                 'active': activeColorIndex === index && !color.out_of_stock,
                 'cursor-pointer': !color.out_of_stock,
                 'swatch-out-of-stock': color.out_of_stock,
                 'swatch-white': color.hex === '#ffffff' || color.hex === '#FFFFFF' 
               }"
               :style="{ backgroundColor: color.hex }"
               @click.stop="!color.out_of_stock ? activeColorIndex = index : null"
               :title="color.name + (color.out_of_stock ? ' (Hết hàng)' : '')">
          </div>
        </template>

        <template v-else>
          <div class="swatch-item swatch-na rounded-circle" title="Mặc định không phân loại"></div>
        </template>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  product: {
    type: Object,
    required: true,
    default: () => ({
      id: 0,
      name: 'Tên sản phẩm',
      price: 0,
      old_price: null,
      image: '',
      discount_percent: null,
      colors: []
    })
  }
});

const emit = defineEmits(['quick-view', 'wishlist', 'compare', 'options']);
const activeColorIndex = ref(0);

// Lấy ảnh hiển thị dựa trên thẻ màu đang được click
const currentImage = computed(() => {
  if (props.product.colors && props.product.colors.length > 0 && props.product.colors[activeColorIndex.value]) {
    const colorImg = props.product.colors[activeColorIndex.value].image;
    if (colorImg) return colorImg;
  }
  return props.product.image || '/client_placeholder.png';
});

const formatCurrency = (val) => {
  if (val === null || val === undefined || val === '') return '0 ₫';
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val);
};

const onQuickView = () => emit('quick-view', props.product);
const onWishlist = () => emit('wishlist', props.product);
const onCompare = () => emit('compare', props.product);
const onOptions = () => emit('options', props.product);
</script>

<style scoped>
.product-card {
  width: 100%;
}

.product-img-wrapper {
  aspect-ratio: 3 / 4;
  -webkit-mask-image: -webkit-radial-gradient(white, black); 
  z-index: 1;
}

.product-img {
  position: relative;
  z-index: 1; 
  transition: transform 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

.badge-layer { z-index: 5; }

.hover-overlay {
  background-color: rgba(0,0,0,0.08);
  opacity: 0;
  transition: opacity 0.3s ease;
  z-index: 10; 
}

.action-right-panel {
  z-index: 20; 
  opacity: 0;
  visibility: hidden;
  transform: translateX(15px); 
  transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

.action-bottom-panel {
  z-index: 20; 
  opacity: 0;
  visibility: hidden;
  transform: translateY(15px); 
  transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

.product-title {
  transition: color 0.3s ease;
}

.line-clamp-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
  word-break: break-word; 
}

/* HOVER ACTIONS */
.product-card:hover .product-img { transform: scale(1.08); }
.product-card:hover .hover-overlay { opacity: 1; }
.product-card:hover .action-right-panel { opacity: 1; visibility: visible; transform: translateX(0); }
.product-card:hover .action-bottom-panel { opacity: 1; visibility: visible; transform: translateY(0); }
.product-card:hover .product-title { color: var(--color-c-hover, #547792) !important; }

/* NÚT BẤM BÊN PHẢI */
.action-icon-btn { width: 40px; height: 40px; transition: all 0.2s ease; border: none; }
.action-icon-btn:hover { background-color: var(--color-c-dark, #213448) !important; }
.action-icon-btn:hover i { color: white !important; }

.action-btn-main { font-size: 0.95rem; transition: all 0.2s ease; border: 1px solid transparent; }
.action-btn-main:hover { border-color: var(--color-c-dark, #213448); color: var(--color-c-dark, #213448) !important; }
.action-btn-main:hover i { color: var(--color-c-dark, #213448) !important; }

/* SWATCHES */
.swatch-item { width: 18px; height: 18px; border: 1px solid rgba(0,0,0,0.15); position: relative; transition: transform 0.2s ease; }
.swatch-item:hover { transform: scale(1.15); }

/* ĐÃ THÊM: Chống tàng hình cho màu trắng */
.swatch-white { border: 1px solid #d1d5db !important; }

.swatch-item.active::after {
  content: ''; position: absolute;
  top: -3.5px; left: -3.5px; right: -3.5px; bottom: -3.5px; 
  border: 1.5px solid #f6c23e; border-radius: 50%;
}

.swatch-na { background-color: #f8f9fa !important; border-color: #dee2e6 !important; cursor: default !important; overflow: hidden; }
html.dark .swatch-na { background-color: #2b3035 !important; border-color: #495057 !important; }

.swatch-out-of-stock { opacity: 0.4; cursor: not-allowed !important; overflow: hidden; }

.swatch-na::before, .swatch-out-of-stock::before {
  content: ''; position: absolute;
  top: 50%; left: -20%; width: 140%; height: 1.5px;
  background-color: #dc3545; transform: translateY(-50%) rotate(-45deg); z-index: 2;
}

.swatch-na:hover { transform: none !important; }
.swatch-out-of-stock:hover { transform: none !important; }
</style>