<template>
  <div>
    <!-- Backdrop -->
    <div v-if="isOpen && lookbook" class="modal-backdrop fade show" style="background: rgba(0,0,0,0.8); backdrop-filter: blur(8px); z-index: 1050;"></div>
    
    <!-- Modal -->
    <div v-if="lookbook" class="modal fade" :class="{ 'show d-block': isOpen }" tabindex="-1" role="dialog" aria-modal="true" style="z-index: 1060;">
      <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
         <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden bg-white dark:bg-[#1a2533]">
            
            <!-- HEADER MODAL -->
            <div class="modal-header border-bottom border-light-subtle dark:border-gray-700 bg-urban-effect dark:bg-[#121416] py-3 px-4 position-relative">
               <div>
                 <h5 class="modal-title fw-bold text-urban-dark dark:text-white text-uppercase tracking-wide font-sans-vn m-0">Tùy Chỉnh Biến Thể Combo</h5>
                 <span class="small text-muted font-sans-vn">Bộ sưu tập: <strong class="text-dark dark:text-white">{{ lookbook.name }}</strong></span>
               </div>
               <button type="button" class="btn-close dark:filter-invert" @click="closeModal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-0">
               <div class="row g-0 h-100">
                  <!-- LEFT: DANH SÁCH SẢN PHẨM CẦN CHỌN BIẾN THỂ -->
                  <div class="col-lg-8 p-4 border-end border-light-subtle dark:border-gray-700 overflow-auto custom-scrollbar" style="max-height: 65vh;">
                     
                     <div v-for="item in comboSelections" :key="item.product.id" class="combo-item-row d-flex gap-4 mb-4 pb-4 border-bottom border-light-subtle dark:border-gray-700 last-no-border position-relative">
                        
                        <!-- Trạng thái hoàn thành góc phải -->
                        <div class="position-absolute top-0 end-0">
                          <i v-if="item.selectedSize?.variant_id" class="bi bi-check-circle-fill text-success fs-5" title="Đã chọn đủ thông tin"></i>
                          <i v-else class="bi bi-exclamation-circle-fill text-warning fs-5" title="Cần chọn Size/Màu"></i>
                        </div>

                        <!-- Ảnh nhảy theo biến thể màu -->
                        <div class="combo-item-img flex-shrink-0 rounded-3 overflow-hidden border border-light-subtle dark:border-gray-600 bg-urban-effect" style="width: 120px; height: 160px;">
                           <img :src="item.selectedColor?.image || item.product.image || '/client_placeholder.png'" class="w-100 h-100 object-fit-cover">
                        </div>
                        
                        <!-- Block chọn thuộc tính -->
                        <div class="combo-item-info flex-grow-1 pe-4">
                           <h6 class="fw-bold text-urban-dark dark:text-white font-sans-vn mb-1">{{ item.product.name }}</h6>
                           <div class="text-danger fw-bold mb-3 font-sans-vn">{{ formatCurrency(item.product.price) }}</div>
                           
                           <!-- Chọn Màu Sắc -->
                           <div class="mb-3" v-if="item.product.colors && item.product.colors.length > 0 && item.product.colors[0].name !== 'Mặc định'">
                              <span class="d-block small fw-semibold text-muted mb-2 font-sans-vn">Màu sắc: <span class="text-urban-dark dark:text-gray-300 fw-bold">{{ item.selectedColor?.name }}</span></span>
                              <div class="d-flex flex-wrap gap-2">
                                 <div v-for="color in item.product.colors" :key="color.name" 
                                      class="combo-swatch rounded-circle shadow-sm transition-all"
                                      :class="{ 'active border-urban': item.selectedColor?.name === color.name, 'out-of-stock': color.out_of_stock }"
                                      :style="{ backgroundColor: color.hex }"
                                      @click="!color.out_of_stock ? selectColorForCombo(item.product.id, color) : null"
                                      :title="color.name + (color.out_of_stock ? ' (Hết hàng)' : '')">
                                 </div>
                              </div>
                           </div>

                           <!-- Chọn Kích Cỡ -->
                           <div v-if="item.selectedColor?.sizes && item.selectedColor.sizes.length > 0">
                              <span class="d-block small fw-semibold text-muted mb-2 font-sans-vn">Kích cỡ: <span v-if="!item.selectedSize" class="text-danger fst-italic fw-normal">(Vui lòng chọn)</span></span>
                              <div class="d-flex flex-wrap gap-2">
                                 <button v-for="size in item.selectedColor.sizes" :key="size.name"
                                         class="btn btn-sm combo-size-btn border font-sans-vn transition-all fw-semibold"
                                         :class="{ 'btn-urban shadow-sm': item.selectedSize?.name === size.name, 'btn-light bg-white dark:bg-transparent dark:text-gray-300 dark:border-gray-600 text-dark': item.selectedSize?.name !== size.name, 'disabled opacity-25 text-decoration-line-through': size.out_of_stock }"
                                         @click="!size.out_of_stock ? selectSizeForCombo(item.product.id, size) : null">
                                   {{ size.name }}
                                 </button>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>

                  <!-- RIGHT: HÓA ĐƠN TÓM TẮT & XÁC NHẬN MUA -->
                  <div class="col-lg-4 p-4 bg-urban-effect dark:bg-[#121416] d-flex flex-column justify-content-between border-start border-light-subtle dark:border-gray-700">
                     <div>
                       <h5 class="fw-bold text-urban-dark dark:text-white font-sans-vn border-bottom border-secondary border-opacity-25 pb-3 mb-4">Hóa Đơn Combo</h5>
                       <ul class="list-unstyled mb-4 font-sans-vn">
                         <li class="d-flex justify-content-between mb-3 text-muted">
                           <span>Tổng số lượng:</span>
                           <span class="fw-bold text-urban-dark dark:text-white">{{ totalItemsCount }} sản phẩm</span>
                         </li>
                         <li class="d-flex justify-content-between mb-3 text-muted">
                           <span>Giá trị gốc:</span>
                           <span class="text-decoration-line-through">{{ formatCurrency(originalTotalPrice) }}</span>
                         </li>
                         <li class="d-flex justify-content-between mb-3 text-success">
                           <span>Tiết kiệm Combo:</span>
                           <div class="d-flex gap-2 align-items-center">
                             <span class="badge bg-success bg-opacity-10 text-success rounded px-2 py-1 fw-bold">-{{ savedPercent }}%</span>
                             <span class="fw-bold">-{{ formatCurrency(savedAmount) }}</span>
                           </div>
                         </li>
                       </ul>
                     </div>
                     
                     <div class="border-top border-secondary border-opacity-25 pt-4">
                       <div class="d-flex justify-content-between align-items-center mb-4 font-sans-vn">
                         <span class="fw-bold text-urban-dark dark:text-white fs-5">Thanh toán:</span>
                         <span class="fw-bold text-danger display-6">{{ formatCurrency(lookbook.price_estimate) }}</span>
                       </div>
                       
                       <div class="d-flex justify-content-between align-items-center mb-3 font-sans-vn small fw-semibold">
                         <span :class="isAllVariantsSelected ? 'text-success' : 'text-danger'">
                           <i :class="isAllVariantsSelected ? 'bi bi-check-circle-fill' : 'bi bi-info-circle-fill'"></i>
                           Tiến độ chọn Size/Màu:
                         </span>
                         <span :class="isAllVariantsSelected ? 'text-success' : 'text-danger'">
                           {{ completedSelectionsCount }} / {{ totalItemsCount }}
                         </span>
                       </div>

                       <div class="progress mb-4 bg-white dark:bg-gray-700 border border-light-subtle shadow-sm" style="height: 8px;">
                         <div class="progress-bar" :class="isAllVariantsSelected ? 'bg-success' : 'bg-warning progress-bar-striped progress-bar-animated'" 
                              role="progressbar" :style="{ width: (completedSelectionsCount / totalItemsCount) * 100 + '%' }"></div>
                       </div>

                       <!-- Nút Submit Gửi Payload Chuẩn Xác -->
                       <button @click="confirmAddComboToCart" 
                               class="btn btn-urban w-100 rounded-pill py-3 fw-bold text-uppercase tracking-wide shadow font-sans-vn position-relative overflow-hidden"
                               :disabled="isAddingCombo || !isAllVariantsSelected">
                         <span v-if="isAddingCombo" class="spinner-border spinner-border-sm me-2" role="status"></span>
                         {{ isAddingCombo ? 'Đang thêm vào giỏ...' : 'Xác Nhận Đưa Vào Giỏ' }}
                       </button>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import api from '@/utils/axios';
import { useCartStore } from '@/stores/cartStore';
import { ZyroSwal } from '@/components/client/ZyroSwal';

const cartStore = useCartStore();

// STATE & LOGIC
const isOpen = ref(false);
const lookbook = ref(null);
const isAddingCombo = ref(false);
const comboSelections = ref({}); 

const totalItemsCount = computed(() => {
  return lookbook.value?.products?.length || 0;
});

const completedSelectionsCount = computed(() => {
  let count = 0;
  for (const id in comboSelections.value) {
    const item = comboSelections.value[id];
    if (!item.selectedColor?.sizes || item.selectedColor.sizes.length === 0) {
       count++;
    } else if (item.selectedSize && item.selectedSize.variant_id) {
       count++; 
    }
  }
  return count;
});

const isAllVariantsSelected = computed(() => {
  return totalItemsCount.value > 0 && completedSelectionsCount.value === totalItemsCount.value;
});

const originalTotalPrice = computed(() => {
  if (!lookbook.value || !lookbook.value.products) return 0;
  let total = 0;
  lookbook.value.products.forEach(p => {
    let price = Number(p.old_price);
    if (!price || isNaN(price) || price === 0) {
      price = Number(p.price);
    }
    if (!isNaN(price)) {
      total += price;
    }
  });
  return total;
});

const savedAmount = computed(() => {
  if (!lookbook.value || originalTotalPrice.value === 0) return 0;
  const estimate = Number(lookbook.value.price_estimate) || 0;
  return Math.max(0, originalTotalPrice.value - estimate);
});

const savedPercent = computed(() => {
  if (originalTotalPrice.value === 0 || savedAmount.value <= 0) return 0;
  return Math.round((savedAmount.value / originalTotalPrice.value) * 100);
});

// EXPOSE METHOD TO PARENT
const openModal = (lookbookData) => {
  if (!lookbookData || !lookbookData.products) return;
  
  lookbook.value = lookbookData;
  comboSelections.value = {};
  document.body.style.overflow = 'hidden'; 

  lookbook.value.products.forEach(p => {
    const firstColor = p.colors?.find(c => !c.out_of_stock) || p.colors?.[0];
    const firstSize = firstColor?.sizes?.find(s => !s.out_of_stock) || firstColor?.sizes?.[0];
    
    comboSelections.value[p.id] = {
      product: p,
      selectedColor: firstColor,
      selectedSize: firstSize 
    };
  });

  isOpen.value = true;
};

const closeModal = () => {
  isOpen.value = false;
  document.body.style.overflow = '';
};

defineExpose({ openModal, closeModal });

const selectColorForCombo = (productId, color) => {
  if (color.out_of_stock) return;
  
  const item = comboSelections.value[productId];
  const currentSizeName = item.selectedSize?.name;
  
  item.selectedColor = color;
  const matchedSize = color.sizes?.find(s => s.name === currentSizeName && !s.out_of_stock);
  
  if (matchedSize) {
    item.selectedSize = matchedSize;
  } else {
    const availableSize = color.sizes?.find(s => !s.out_of_stock);
    item.selectedSize = availableSize ? availableSize : null; 
  }
};

const selectSizeForCombo = (productId, size) => {
  if (size.out_of_stock) return;
  comboSelections.value[productId].selectedSize = size;
};

const confirmAddComboToCart = async () => {
  isAddingCombo.value = true;
  try {
    const selectionsArray = [];
    
    for (const id in comboSelections.value) {
      const item = comboSelections.value[id];
      if (item.selectedSize && item.selectedSize.variant_id) {
        selectionsArray.push({
          product_id: item.product.id,
          variant_id: item.selectedSize.variant_id,
          quantity: 1, 
          attributes: `${item.selectedColor?.name} - ${item.selectedSize?.name}`
        });
      }
    }

    const payload = {
      lookbook_id: lookbook.value.id,
      quantity: 1, 
      lookbook_selections: selectionsArray
    };

    await api.post('/client/cart/add-lookbook', payload);

    if (typeof cartStore.initCart === 'function') {
        await cartStore.initCart();
    } else if (typeof cartStore.fetchDBCart === 'function') {
        await cartStore.fetchDBCart();
    }

    closeModal();
    ZyroSwal.toastSuccess('Trọn bộ phong cách đã được thêm vào giỏ hàng!');

  } catch (error) {
    console.error('Lỗi khi thêm Lookbook vào giỏ:', error);
    ZyroSwal.toastError(error.response?.data?.message || 'Không thể thêm trọn bộ vào giỏ hàng. Vui lòng thử lại sau.');
  } finally {
    isAddingCombo.value = false;
  }
};

const formatCurrency = (val) => {
  const num = Number(val);
  if (isNaN(num)) return '0đ';
  return new Intl.NumberFormat('vi-VN').format(num) + 'đ';
};
</script>

<style scoped>
.text-urban-dark { color: var(--color-c-dark, #213448) !important; }
.bg-urban-effect { background-color: var(--color-c-effect, #ebf1f5) !important; }
.btn-urban { background-color: var(--color-c-dark, #213448); color: #fff; border: 1px solid var(--color-c-dark, #213448); transition: all 0.3s ease; }
.btn-urban:hover { background-color: var(--color-c-hover, #547792); border-color: var(--color-c-hover, #547792); color: #fff; }
.btn-urban:disabled { opacity: 0.7; pointer-events: none; background-color: #6c757d; border-color: #6c757d; }
.border-urban { border: 2px solid var(--color-c-dark, #213448) !important; }

.font-sans-vn { font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', sans-serif !important; }
.tracking-wide { letter-spacing: 1px; }
.line-clamp-1 { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }
.last-no-border:last-child { border-bottom: none !important; padding-bottom: 0 !important; margin-bottom: 0 !important; }

.combo-swatch { width: 24px; height: 24px; border: 1px solid #dee2e6; cursor: pointer; }
.combo-swatch.active { transform: scale(1.15); box-shadow: 0 0 0 2px rgba(33,52,72,0.3); }
.combo-swatch.out-of-stock { opacity: 0.3; cursor: not-allowed; position: relative; }
.combo-swatch.out-of-stock::after { content: ''; position: absolute; top: 50%; left: -10%; width: 120%; height: 2px; background: red; transform: rotate(-45deg); }

.combo-size-btn { min-width: 40px; }
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #dee2e6; border-radius: 10px; }

.transition-all { transition: all 0.3s ease; }
</style>