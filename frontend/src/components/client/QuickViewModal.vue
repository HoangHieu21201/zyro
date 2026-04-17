<template>
  <Teleport to="body">
    <div class="quick-view-wrapper">
      <transition name="fade">
        <div v-if="isOpen" class="qv-backdrop" @click="closeModal"></div>
      </transition>

      <transition name="zoom">
        <div v-if="isOpen" class="qv-modal shadow-lg bg-white dark:bg-[#1a2533] rounded-3 overflow-hidden d-flex flex-column">
          
          <div class="qv-header bg-urban text-white p-3 d-flex justify-content-between align-items-center">
            <span class="fw-semibold text-truncate mb-0" style="font-size: 1rem;">Xem nhanh: {{ product.name }}</span>
            <button class="btn-close btn-close-white" @click="closeModal"></button>
          </div>

          <div class="qv-body p-4 p-md-5 custom-scrollbar-y flex-grow-1">
            <div class="row g-4 g-lg-5">
              
              <!-- THƯ VIỆN ẢNH -->
              <div class="col-md-5">
                <div class="main-img-box border border-light-subtle dark:border-gray-700 rounded-3 mb-3 bg-light dark:bg-[#121416] d-flex justify-content-center align-items-center overflow-hidden">
                  <img :src="activeImage || defaultImage" @error="handleImageError" class="w-100 h-100 object-fit-cover" style="aspect-ratio: 3/4;">
                </div>
                
                <div class="d-flex gap-2 overflow-auto custom-scrollbar-x pb-2">
                  <div v-for="(img, idx) in productGallery" :key="'thumb'+idx" 
                       class="thumb-box border rounded-3 cursor-pointer overflow-hidden flex-shrink-0" 
                       :class="activeImage === img ? 'border-urban border-2 shadow-sm' : 'border-light-subtle dark:border-gray-700'" 
                       @click="activeImage = img">
                    <img :src="img" @error="handleImageError" class="w-100 h-100 object-fit-cover p-1 rounded-3">
                  </div>
                </div>
              </div>

              <!-- THÔNG TIN CHI TIẾT & CHỌN MUA -->
              <div class="col-md-7 d-flex flex-column">
                <h4 class="fw-bold mb-2 text-dark dark:text-white">{{ product.name }}</h4>
                <div class="text-muted small mb-3">
                  Thương hiệu: <span class="text-urban">{{ product.brand?.name || 'ZYRO' }}</span> | 
                  Danh mục: <span class="text-urban">{{ product.category?.name || 'Chưa cập nhật' }}</span>
                </div>
                
                <div class="d-flex align-items-center gap-2 mb-3">
                  <h3 class="text-danger fw-bold mb-0">{{ formatCurrency(product.price) }}</h3>
                  <span v-if="product.old_price && product.old_price > product.price" class="text-muted text-decoration-line-through fw-semibold mt-1">
                    {{ formatCurrency(product.old_price) }}
                  </span>
                  <span v-if="product.discount_percent" class="badge bg-danger ms-2">-{{ product.discount_percent }}%</span>
                </div>
                
                <p class="text-muted small mb-4 pb-4 border-bottom dark:border-gray-700 line-clamp-3">
                  {{ product.description || 'Thông tin sản phẩm đang cập nhật' }}
                </p>

                <!-- CHỌN MÀU SẮC -->
                <div class="mb-4">
                  <label class="fw-bold mb-2 d-block small text-dark dark:text-gray-200">
                    Màu sắc: <span class="fw-normal text-muted ms-1">{{ selectedColor || 'Vui lòng chọn' }}</span>
                  </label>
                  <div class="d-flex flex-wrap gap-2">
                    <template v-if="productColors.length > 0">
                      <div v-for="(color, idx) in productColors" :key="'col'+idx"
                           class="color-swatch rounded-2 cursor-pointer shadow-sm-hover position-relative"
                           :class="{
                             'active-swatch': selectedColor === color.name,
                             'swatch-white': color.hex === '#ffffff' || color.hex === '#FFFFFF',
                             'swatch-out-of-stock': color.out_of_stock
                           }"
                           :style="{ backgroundColor: color.hex }"
                           @click="selectColor(color)"
                           :title="color.name + (color.out_of_stock ? ' (Hết hàng)' : '')">
                      </div>
                    </template>
                    <template v-else>
                      <span class="text-muted small fst-italic">Mặc định</span>
                    </template>
                  </div>
                </div>

                <!-- CHỌN KÍCH CỠ -->
                <div class="mb-4">
                  <div class="d-flex justify-content-between mb-2">
                    <label class="fw-bold small text-dark dark:text-gray-200 m-0">Kích cỡ:</label>
                    <a :href="product.size_guide_url || '#'" class="text-urban small text-decoration-none hover-underline">Hướng dẫn chọn Size</a>
                  </div>
                  <div class="d-flex flex-wrap gap-2">
                    <template v-if="productSizes.length > 0">
                      <button v-for="size in productSizes" :key="size.name"
                              type="button"
                              class="btn size-btn fw-semibold"
                              :class="[
                                selectedSize === size.name ? 'btn-outline-urban active text-urban border-2' : 'btn-outline-secondary text-muted border border-light-subtle dark:border-gray-600',
                                {'disabled-size text-decoration-line-through opacity-50': size.out_of_stock}
                              ]"
                              :disabled="size.out_of_stock"
                              @click="selectedSize = size.name"
                              :title="size.out_of_stock ? 'Hết hàng' : ''">
                        {{ size.name }}
                      </button>
                    </template>
                    <template v-else>
                      <span class="text-muted small fst-italic">Mặc định / Freesize</span>
                    </template>
                  </div>
                </div>

                <!-- FORM ĐẶT HÀNG -->
                <div class="d-flex flex-wrap gap-3 align-items-stretch mt-auto pt-2">
                  <div class="quantity-box border border-light-subtle dark:border-gray-600 rounded-3 d-flex bg-light dark:bg-[#212529]" style="width: 120px; height: 46px;">
                    <button class="btn btn-light border-0 text-urban fw-bold fs-5 px-3 bg-transparent h-100 d-flex align-items-center" @click="quantity > 1 ? quantity-- : null"><i class="bi bi-dash"></i></button>
                    <input type="text" class="form-control border-0 text-center fw-bold px-0 h-100 bg-transparent dark:text-white" v-model="quantity" readonly>
                    <button class="btn btn-light border-0 text-urban fw-bold fs-5 px-3 bg-transparent h-100 d-flex align-items-center" @click="quantity++"><i class="bi bi-plus"></i></button>
                  </div>
                  
                  <button class="btn btn-danger flex-grow-1 fw-bold fs-6 shadow-sm hover-transform" style="border-radius: 8px;" @click="addToCart">
                    <i class="bi bi-cart-plus me-2"></i> Thêm vào giỏ hàng
                  </button>
                </div> 

              </div> 
            </div> 
          </div> 
        </div> 
      </transition>
    </div> 
  </Teleport>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import { useCartStore } from '@/stores/cartStore'; 
import { ZyroSwal } from '@/components/client/ZyroSwal';

const props = defineProps({
  isOpen: { type: Boolean, default: false },
  product: {
    type: Object,
    default: () => ({ name: '', price: 0, image: '', colors: [] })
  }
});

const emit = defineEmits(['close']);
const defaultImage = '/client_placeholder.png'; 

const activeImage = ref('');
const selectedColor = ref(null);
const selectedSize = ref(null);
const quantity = ref(1);

const productColors = computed(() => props.product.colors || []);

const productSizes = computed(() => {
  if (!selectedColor.value) return [];
  const colorObj = productColors.value.find(c => c.name === selectedColor.value);
  return colorObj && colorObj.sizes ? colorObj.sizes : [];
});

const productGallery = computed(() => {
  const images = new Set();
  if (props.product.image) images.add(props.product.image);
  if (props.product.colors) {
    props.product.colors.forEach(c => {
      if (c.image) images.add(c.image);
    });
  }
  return Array.from(images).length > 0 ? Array.from(images) : [defaultImage];
});

const selectColor = (color) => {
  if (color.out_of_stock) return;
  selectedColor.value = color.name;
  if (color.image) {
    activeImage.value = color.image;
  }
};

watch(selectedColor, (newColor) => {
  if (newColor) {
    const colorObj = productColors.value.find(c => c.name === newColor);
    if (colorObj && colorObj.sizes && colorObj.sizes.length > 0) {
      const firstAvailable = colorObj.sizes.find(s => !s.out_of_stock);
      selectedSize.value = firstAvailable ? firstAvailable.name : colorObj.sizes[0].name;
    } else {
      selectedSize.value = null;
    }
  }
});

const formatCurrency = (val) => {
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val || 0);
};

const handleImageError = (e) => { e.target.src = defaultImage; };
const closeModal = () => { emit('close'); };

const addToCart = async () => {
  if (productColors.value.length > 0 && !selectedColor.value) {
    ZyroSwal.toastSuccess('Vui lòng chọn Màu sắc!');
    return;
  }
  if (productSizes.value.length > 0 && !selectedSize.value) {
    ZyroSwal.toastSuccess('Vui lòng chọn Kích cỡ!');
    return;
  }

  let variantId = null;

  if (productColors.value.length > 0) {
    const colorObj = productColors.value.find(c => c.name === selectedColor.value);
    if (colorObj && colorObj.sizes) {
      const sizeObj = colorObj.sizes.find(s => s.name === selectedSize.value);
      if (sizeObj) variantId = sizeObj.variant_id || sizeObj.id;
    }
  } else if (props.product.variants && props.product.variants.length > 0) {
    variantId = props.product.variants[0].id;
  }

  if (!variantId) {
    ZyroSwal.toastSuccess('Không xác định được phân loại sản phẩm!');
    return;
  }

  try {
    const cartStore = useCartStore();
    await cartStore.addToCart(variantId, quantity.value, {
      name: props.product.name,
      price: props.product.price,
      image: activeImage.value,
      color: selectedColor.value,
      size: selectedSize.value,
      slug: props.product.slug
    });

    ZyroSwal.toastSuccess(`Đã thêm ${quantity.value} sản phẩm vào giỏ hàng`);
    closeModal();
  } catch (error) {
    console.error(error);
    ZyroSwal.toastSuccess('Có lỗi xảy ra khi thêm vào giỏ');
  }
};

watch(() => props.isOpen, (val) => {
  if (val && props.product) {
    document.body.style.overflow = 'hidden';
    quantity.value = 1;
    
    const availableColor = productColors.value.find(c => !c.out_of_stock) || productColors.value[0];
    if (availableColor) {
      selectedColor.value = availableColor.name; 
      activeImage.value = availableColor.image || props.product.image;
    } else {
      selectedColor.value = null;
      selectedSize.value = null;
      activeImage.value = props.product.image || defaultImage;
    }
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

.swatch-white { border: 1px solid #d1d5db !important; }
.swatch-out-of-stock { opacity: 0.4; cursor: not-allowed !important; overflow: hidden; }
.swatch-out-of-stock::before {
  content: ''; position: absolute;
  top: 50%; left: -20%; width: 140%; height: 1.5px;
  background-color: #dc3545; transform: translateY(-50%) rotate(-45deg); z-index: 2;
}

.size-btn {
  min-width: 50px;
  height: 38px;
  border-radius: 4px;
  background-color: transparent;
  transition: all 0.2s ease;
}
.size-btn:hover:not(.disabled-size) {
  border-color: var(--color-c-hover, #547792) !important;
  color: var(--color-c-hover, #547792) !important;
}
.size-btn.active {
  background-color: rgba(84, 119, 146, 0.1);
}
.disabled-size {
  cursor: not-allowed;
  background-color: #f8f9fa;
}
html.dark .disabled-size { background-color: #2b3035; }

.hover-transform { transition: transform 0.2s ease, box-shadow 0.2s ease; }
.hover-transform:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(220, 53, 69, 0.3) !important; }
.hover-underline:hover { text-decoration: underline !important; }

.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.custom-scrollbar-y::-webkit-scrollbar { width: 5px; }
.custom-scrollbar-y::-webkit-scrollbar-thumb { background: var(--color-c-light, #94B4C1); border-radius: 10px; }
.custom-scrollbar-x::-webkit-scrollbar { height: 5px; }
.custom-scrollbar-x::-webkit-scrollbar-thumb { background: var(--color-c-light, #94B4C1); border-radius: 10px; }

.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
.zoom-enter-active, .zoom-leave-active { transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
.zoom-enter-from, .zoom-leave-to { transform: translate(-50%, -50%) scale(0.9); opacity: 0; }
</style>