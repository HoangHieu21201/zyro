<template>
  <div class="lookbook-detail-wrapper pb-5 mb-5 bg-white dark:bg-[#121416] position-relative">
    
    <div class="pt-5 mt-5">
      <div class="zyro-container pt-3">
        
        <!-- BREADCRUMB ĐIỀU HƯỚNG -->
        <nav aria-label="breadcrumb" class="mb-4">
          <ol class="breadcrumb small fw-semibold text-uppercase" style="letter-spacing: 0.5px;">
            <li class="breadcrumb-item"><router-link to="/" class="text-decoration-none text-muted hover-text-urban font-sans-vn">Trang chủ</router-link></li>
            <li class="breadcrumb-item"><router-link to="/lookbooks" class="text-decoration-none text-muted hover-text-urban font-sans-vn">Bộ sưu tập</router-link></li>
            <li class="breadcrumb-item active text-urban-dark dark:text-gray-300 font-sans-vn" aria-current="page">
               <span v-if="isLoading" class="shimmer d-inline-block rounded" style="width: 100px; height: 14px;"></span>
               <span v-else>{{ lookbook?.name || 'Đang tải...' }}</span>
            </li>
          </ol>
        </nav>

        <!-- SKELETON LÚC MỚI VÀO TRANG -->
        <div v-if="isLoading" class="pe-none mb-5">
           <div class="row mb-5 align-items-center">
             <div class="col-lg-6">
                <div class="shimmer rounded-4 w-100" style="height: 600px;"></div>
             </div>
             <div class="col-lg-6 mt-4 mt-lg-0 px-lg-5">
                <div class="shimmer rounded mb-3" style="width: 80%; height: 40px;"></div>
                <div class="shimmer rounded mb-4" style="width: 40%; height: 24px;"></div>
                <div class="shimmer rounded mb-2 w-100" style="height: 16px;"></div>
                <div class="shimmer rounded mb-4 w-75" style="height: 16px;"></div>
                <div class="d-flex gap-3">
                  <div class="shimmer rounded-pill" style="width: 180px; height: 50px;"></div>
                  <div class="shimmer rounded-pill" style="width: 180px; height: 50px;"></div>
                </div>
             </div>
           </div>
        </div>

        <!-- ========================================== -->
        <!-- GIAO DIỆN CHÍNH THỨC -->
        <!-- ========================================== -->
        <div v-else-if="lookbook">
          
          <!-- HERO SECTION: BÁN HÀNG MINH BẠCH & UP-SELL -->
          <div class="row align-items-stretch mb-5 pb-4 border-bottom border-light-subtle dark:border-gray-800">
            
            <!-- Hình ảnh Lookbook -->
            <div class="col-lg-6 position-relative">
              <div class="rounded-4 overflow-hidden shadow-lg h-100 position-relative group bg-urban-effect">
                 <img :src="lookbook.main_image || '/client_placeholder.png'" 
                      @error="e => e.target.src='/client_placeholder.png'" 
                      class="w-100 h-100 object-fit-cover transition-transform group-hover-scale"
                      style="min-height: 500px;"
                      :alt="lookbook.name || 'Lookbook Image'">
                 <div class="position-absolute top-0 start-0 m-4">
                    <span class="badge bg-white text-urban-dark py-2 px-3 fw-bold text-uppercase tracking-widest shadow-sm rounded-pill font-decor">
                      Must Have
                    </span>
                 </div>
              </div>
            </div>

            <!-- KHU VỰC CHỐT SALE & THÔNG TIN GIÁ TRỊ (PRICE TRANSPARENCY) -->
            <div class="col-lg-6 d-flex flex-column justify-content-center px-lg-5 mt-5 mt-lg-0">
               <div class="mb-2 d-flex align-items-center justify-content-between">
                 <span class="text-muted fw-bold tracking-widest text-uppercase small font-sans-vn">Gợi ý Phối Đồ Từ Stylist</span>
                 <span class="badge bg-urban-effect text-urban-dark border border-secondary border-opacity-25 dark:bg-gray-700 dark:text-white rounded-pill px-3 py-1 fw-semibold font-sans-vn">
                   Trọn bộ {{ totalItemsCount }} món đồ
                 </span>
               </div>
               
               <h1 class="display-4 fw-bold text-urban-dark dark:text-white mb-4 text-uppercase font-sans-vn" style="line-height: 1.2;">
                 {{ lookbook.name }}
               </h1>
               
               <!-- BẢNG GIÁ CHI TIẾT SỰ TIẾT KIỆM -->
               <div class="price-breakdown bg-urban-effect dark:bg-[#1a2533] rounded-4 p-4 mb-4 border border-light-subtle dark:border-gray-700 position-relative overflow-hidden">
                 <!-- Icon background trang trí -->
                 <i class="bi bi-tag-fill position-absolute text-white opacity-25 dark:text-black dark:opacity-10" style="font-size: 8rem; right: -20px; top: -30px;"></i>
                 
                 <div class="d-flex flex-column gap-3 position-relative z-index-2">
                    <!-- Tổng giá trị gốc -->
                    <div class="d-flex justify-content-between align-items-center">
                      <span class="text-muted font-sans-vn">Tổng giá trị {{ totalItemsCount }} món đồ:</span>
                      <span class="text-decoration-line-through text-muted fw-semibold">{{ formatCurrency(originalTotalPrice) }}</span>
                    </div>
                    
                    <!-- Mức tiết kiệm -->
                    <div class="d-flex justify-content-between align-items-center">
                      <span class="text-muted font-sans-vn">Tiết kiệm khi mua Combo:</span>
                      <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill fw-bold border border-success border-opacity-25 px-2 py-1">
                          -{{ savedPercent }}%
                        </span>
                        <span class="text-success fw-bold font-sans-vn">-{{ formatCurrency(savedAmount) }}</span>
                      </div>
                    </div>
                 </div>
                 
                 <hr class="my-3 border-secondary border-opacity-25 position-relative z-index-2">
                 
                 <!-- Giá thanh toán -->
                 <div class="d-flex justify-content-between align-items-center position-relative z-index-2">
                   <span class="fw-bold text-urban-dark dark:text-white fs-5 font-sans-vn">Giá Trọn Bộ:</span>
                   <span class="text-danger fw-bold display-6 font-sans-vn">{{ formatCurrency(lookbook.price_estimate) }}</span>
                 </div>
               </div>
               
               <p class="text-muted fs-6 mb-5 lh-lg dark:text-gray-400 font-sans-vn" v-if="lookbook.description">
                 {{ lookbook.description }}
               </p>
               
               <!-- BỘ NÚT E-COMMERCE -->
               <div class="d-flex flex-wrap gap-3">
                 <button @click="openComboModal" 
                         class="btn btn-urban rounded-pill px-5 py-3 fw-bold text-uppercase tracking-wide shadow-sm d-flex align-items-center gap-2 font-sans-vn hover-shadow-lg transition-all">
                   <i class="bi bi-bag-plus-fill fs-5"></i> Mua Trọn Bộ Này
                 </button>
                 
                 <button @click="scrollToProducts" 
                         class="btn btn-outline-urban rounded-pill px-4 py-3 fw-bold text-uppercase tracking-wide transition-all d-flex align-items-center gap-2 font-sans-vn">
                   Tùy Chọn Mua Lẻ <i class="bi bi-arrow-down"></i>
                 </button>
               </div>
            </div>
          </div>

          <!-- KHU VỰC SẢN PHẨM CHI TIẾT ĐỂ THÊM VÀO GIỎ LẺ -->
          <div id="combo-products-section" class="pt-4">
             <div class="d-flex flex-column flex-md-row align-items-md-end justify-content-between mb-5">
               <div>
                 <h2 class="fw-bold m-0 text-urban-dark dark:text-white tracking-widest text-uppercase font-sans-vn">Chi Tiết {{ totalItemsCount }} Món Đồ</h2>
                 <p class="text-muted mt-2 mb-0 font-sans-vn">Sử dụng nút "Xem nhanh" hoặc "Tùy chọn" trên ảnh nếu bạn chỉ muốn mua lẻ từng món.</p>
               </div>
             </div>

             <!-- LƯỚI SẢN PHẨM -->
             <div v-if="lookbook.products && lookbook.products.length > 0" class="custom-grid-5 mb-5">
               <ProductCard v-for="product in lookbook.products" :key="'lb-prod-'+product.id" 
                            :product="product" @quick-view="handleOpenQuickView" @compare="compareStore.add" @options="handleGoToDetail" />
               
               <div v-for="i in emptySlots" :key="'empty-'+i" class="empty-slot-card w-100 group cursor-default">
                  <div class="empty-img-wrapper position-relative overflow-hidden rounded-3 mb-3 bg-urban-effect dark:bg-[#1a2533] d-flex flex-column align-items-center justify-content-center border border-dashed border-secondary border-opacity-25 transition-all hover-border-urban" style="aspect-ratio: 3/4;">
                    <div class="rounded-circle bg-white dark:bg-[#2b3035] shadow-sm d-flex align-items-center justify-content-center mb-3 group-hover-scale transition-all" style="width: 55px; height: 55px;">
                      <i :class="['bi', emptySlotContent[i-1]?.icon || 'bi-stars', 'text-urban-dark dark:text-white fs-4']"></i>
                    </div>
                  </div>
                  <div class="empty-info px-1 w-100 text-center">
                    <h6 class="fw-bold text-urban-dark dark:text-white font-sans-vn mb-1 line-clamp-1" style="font-size: 0.95rem;">
                      {{ emptySlotContent[i-1]?.title || 'Coming Soon' }}
                    </h6>
                    <span class="small text-muted font-decor" style="font-size: 0.8rem;">
                      {{ emptySlotContent[i-1]?.desc || 'Stylist đang chọn lựa' }}
                    </span>
                  </div>
               </div>
             </div>
             
             <!-- NẾU CHƯA GẮN SẢN PHẨM NÀO VÀO LOOKBOOK -->
             <div v-else class="text-center py-5 my-5 text-muted bg-urban-effect dark:bg-[#1a2533] rounded-4 border border-dashed border-secondary">
                <i class="bi bi-box-seam fs-1 d-block mb-3 opacity-50"></i>
                <h5 class="fw-normal font-sans-vn">Chưa có sản phẩm nào được thêm vào bộ sưu tập này.</h5>
             </div>
          </div>

        </div>

        <!-- NẾU KHÔNG TÌM THẤY LOOKBOOK -->
        <div v-else class="text-center py-5 my-5 text-muted">
           <i class="bi bi-search fs-1 d-block mb-3 opacity-50"></i>
           <h5 class="fw-normal mb-4 font-sans-vn">Không tìm thấy bộ sưu tập bạn yêu cầu.</h5>
           <router-link to="/lookbooks" class="btn btn-outline-urban rounded-pill px-4 py-2 fw-bold text-uppercase tracking-widest transition-all font-sans-vn">
             Quay Lại Danh Sách
           </router-link>
        </div>

      </div>
    </div>

    <!-- ========================================== -->
    <!-- MODAL TÙY CHỈNH COMBO CHUYÊN SÂU -->
    <!-- Đảm bảo người dùng chọn đúng và đủ biến thể trước khi thêm -->
    <!-- ========================================== -->
    <div v-if="isComboModalOpen && lookbook" class="modal-backdrop fade show" style="background: rgba(0,0,0,0.8); backdrop-filter: blur(8px); z-index: 1050;"></div>
    <div v-if="lookbook" class="modal fade" :class="{ 'show d-block': isComboModalOpen }" tabindex="-1" role="dialog" aria-modal="true" style="z-index: 1060;">
      <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
         <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden bg-white dark:bg-[#1a2533]">
            
            <!-- HEADER MODAL -->
            <div class="modal-header border-bottom border-light-subtle dark:border-gray-700 bg-urban-effect dark:bg-[#121416] py-3 px-4 position-relative">
               <div>
                 <h5 class="modal-title fw-bold text-urban-dark dark:text-white text-uppercase tracking-wide font-sans-vn m-0">Tùy Chỉnh Biến Thể Combo</h5>
                 <span class="small text-muted font-sans-vn">Bộ sưu tập: <strong class="text-dark dark:text-white">{{ lookbook.name }}</strong></span>
               </div>
               <button type="button" class="btn-close dark:filter-invert" @click="closeComboModal" aria-label="Close"></button>
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
                       
                       <!-- Dòng Text Trạng Thái (VD: Đã chọn 2/5 sản phẩm) -->
                       <div class="d-flex justify-content-between align-items-center mb-3 font-sans-vn small fw-semibold">
                         <span :class="isAllVariantsSelected ? 'text-success' : 'text-danger'">
                           <i :class="isAllVariantsSelected ? 'bi bi-check-circle-fill' : 'bi bi-info-circle-fill'"></i>
                           Tiến độ chọn Size/Màu:
                         </span>
                         <span :class="isAllVariantsSelected ? 'text-success' : 'text-danger'">
                           {{ completedSelectionsCount }} / {{ totalItemsCount }}
                         </span>
                       </div>

                       <!-- Progress Bar Trực Quan -->
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

    <!-- MODALS KHÁC -->
    <QuickViewModal :is-open="isQuickViewOpen" :product="selectedProduct" @close="isQuickViewOpen = false" />
    <CompareModal />

    <!-- THÔNG BÁO MUA THÀNH CÔNG -->
    <div class="toast-container position-fixed bottom-0 end-0 p-4" style="z-index: 1080;">
      <div id="comboSuccessToast" class="toast align-items-center text-bg-success border-0 shadow-lg rounded-4" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
          <div class="toast-body fw-medium font-sans-vn px-3 py-3">
            <i class="bi bi-check-circle-fill me-2 fs-5 align-middle"></i> Trọn bộ phong cách đã được thêm vào giỏ hàng thành công!
          </div>
          <button type="button" class="btn-close btn-close-white me-3 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '@/utils/axios';

import { useCompareStore } from '@/stores/compareStore';
import ProductCard from '@/components/client/ProductCard.vue';
import QuickViewModal from '@/components/client/QuickViewModal.vue';
import CompareModal from '@/components/client/CompareModal.vue';

const route = useRoute();
const router = useRouter();
const compareStore = useCompareStore();

const lookbook = ref(null);
const isLoading = ref(true);

// ==========================================
// STATE & LOGIC MUA COMBO CẤP ĐỘ CAO
// ==========================================
const isComboModalOpen = ref(false);
const isAddingCombo = ref(false);
const comboSelections = ref({}); 

const totalItemsCount = computed(() => {
  return lookbook.value?.products?.length || 0;
});

// Đếm số lượng sản phẩm ĐÃ CHỌN ĐỦ Variant
const completedSelectionsCount = computed(() => {
  let count = 0;
  for (const id in comboSelections.value) {
    const item = comboSelections.value[id];
    // Nếu sản phẩm không có size/màu (Mặc định) thì coi như đủ.
    if (!item.selectedColor?.sizes || item.selectedColor.sizes.length === 0) {
       count++;
    } else if (item.selectedSize && item.selectedSize.variant_id) {
       count++; // Đã chọn size hợp lệ
    }
  }
  return count;
});

// Biến boolean kiểm tra đã chọn FULL
const isAllVariantsSelected = computed(() => {
  return totalItemsCount.value > 0 && completedSelectionsCount.value === totalItemsCount.value;
});

// FIX LỖI NaN: Ép kiểu sang Number, nếu không hợp lệ thì gán bằng 0
const originalTotalPrice = computed(() => {
  if (!lookbook.value || !lookbook.value.products) return 0;
  let total = 0;
  lookbook.value.products.forEach(p => {
    let price = Number(p.old_price);
    // Ưu tiên giá cũ (old_price), nếu không có thì lấy giá bán hiện tại (price)
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

// Khởi tạo Modal (Giữ lại UX chọn sẵn size/màu đầu tiên còn hàng)
const openComboModal = () => {
  if (!lookbook.value || !lookbook.value.products) return;
  
  comboSelections.value = {};
  document.body.style.overflow = 'hidden'; 

  lookbook.value.products.forEach(p => {
    const firstColor = p.colors?.find(c => !c.out_of_stock) || p.colors?.[0];
    const firstSize = firstColor?.sizes?.find(s => !s.out_of_stock) || firstColor?.sizes?.[0];
    
    comboSelections.value[p.id] = {
      product: p,
      selectedColor: firstColor,
      selectedSize: firstSize // Nếu không có size nào khả dụng, nó sẽ undefined (đòi hỏi user tự chọn thủ công)
    };
  });

  isComboModalOpen.value = true;
};

const closeComboModal = () => {
  isComboModalOpen.value = false;
  document.body.style.overflow = '';
};

// ĐÃ SỬA: Hàm chọn Màu sẽ cố gắng giữ nguyên Size đang chọn (nếu có thể)
const selectColorForCombo = (productId, color) => {
  if (color.out_of_stock) return;
  
  const item = comboSelections.value[productId];
  
  // Lưu lại tên Size mà người dùng đang chọn trước khi đổi màu
  const currentSizeName = item.selectedSize?.name;
  
  // Đổi sang màu mới
  item.selectedColor = color;
  
  // Tìm xem màu mới này có Size trùng tên với Size người dùng vừa chọn và còn hàng không
  const matchedSize = color.sizes?.find(s => s.name === currentSizeName && !s.out_of_stock);
  
  if (matchedSize) {
    // Nếu có, giữ nguyên Size đó cho họ
    item.selectedSize = matchedSize;
  } else {
    // Nếu màu mới này không có Size đó hoặc đã hết hàng Size đó, tự nhảy về Size đầu tiên còn hàng
    const availableSize = color.sizes?.find(s => !s.out_of_stock);
    item.selectedSize = availableSize ? availableSize : null; 
  }
};

const selectSizeForCombo = (productId, size) => {
  if (size.out_of_stock) return;
  comboSelections.value[productId].selectedSize = size;
};

// ==========================================
// GỬI PAYLOAD CHUẨN XÁC LÊN SERVER
// ==========================================
const confirmAddComboToCart = async () => {
  isAddingCombo.value = true;
  try {
    // Thu thập dữ liệu các lựa chọn thành mảng JSON đúng theo Schema DB
    const selectionsArray = [];
    
    for (const id in comboSelections.value) {
      const item = comboSelections.value[id];
      if (item.selectedSize && item.selectedSize.variant_id) {
        selectionsArray.push({
          product_id: item.product.id,
          variant_id: item.selectedSize.variant_id,
          quantity: 1, // Lookbook thường mua 1 bộ
          attributes: `${item.selectedColor?.name} - ${item.selectedSize?.name}` // Lưu vết
        });
      }
    }

    // Payload Gộp thành 1 Request chứa Lookbook ID
    const payload = {
      lookbook_id: lookbook.value.id,
      quantity: 1, 
      lookbook_selections: selectionsArray
    };

    // Yêu cầu Backend Laravel: Cần mở 1 route POST /client/cart/add-lookbook 
    // Trong Controller, bạn đón biến $request->lookbook_id và insert vào cột lookbook_id của CartItem
    await api.post('/client/cart/add-lookbook', payload);

    closeComboModal();
    
    const toastEl = document.getElementById('comboSuccessToast');
    if (toastEl && window.bootstrap) {
      new window.bootstrap.Toast(toastEl, { delay: 3500 }).show();
    }
  } catch (error) {
    console.error('Lỗi khi thêm Lookbook vào giỏ:', error);
    alert('Không thể thêm trọn bộ vào giỏ hàng. Vui lòng thử lại sau.');
  } finally {
    isAddingCombo.value = false;
  }
};

// ==========================================
// CÁC LOGIC CHUNG KHÁC
// ==========================================
const emptySlotContent = [
  { icon: 'bi-magic', title: 'Phiên Bản Giới Hạn', desc: 'Mảnh ghép giới hạn sắp ra mắt' },
  { icon: 'bi-bag-heart', title: 'Hàng Sắp Về', desc: 'Cùng chờ đón đợt restock tới' },
  { icon: 'bi-gem', title: 'Mảnh Ghép Đặc Biệt', desc: 'Stylist đang tinh chỉnh phụ kiện' },
  { icon: 'bi-stars', title: 'Sắp Bật Mí', desc: 'Mảnh ghép hoàn hảo đang đến' }
];

const emptySlots = computed(() => {
  if (!lookbook.value || !lookbook.value.products) return 0;
  const count = lookbook.value.products.length;
  if (count === 0) return 0;
  return (5 - (count % 5)) % 5;
});

const fetchLookbookDetail = async () => {
  isLoading.value = true; 
  try {
    const slug = route.params.slug;
    const res = await api.get(`/client/lookbook-detail/${slug}`);
    if (res.data && res.data.data) {
        lookbook.value = res.data.data;
    } else {
        throw new Error("Dữ liệu Lookbook không hợp lệ");
    }
  } catch (err) {
    console.error('Lỗi khi tải chi tiết Lookbook:', err);
  } finally {
    isLoading.value = false;
  }
};

// FIX LỖI NaN BẰNG CÁCH ÉP KIỂU TRƯỚC KHI FORMAT
const formatCurrency = (val) => {
  const num = Number(val);
  if (isNaN(num)) return '0đ';
  return new Intl.NumberFormat('vi-VN').format(num) + 'đ';
};

const scrollToProducts = () => {
  const el = document.getElementById('combo-products-section');
  if (el) {
    const y = el.getBoundingClientRect().top + window.scrollY - 100;
    window.scrollTo({ top: y, behavior: 'smooth' });
  }
};

const handleGoToDetail = (product) => {
  router.push(`/product/${product.id}`);
};

const isQuickViewOpen = ref(false);
const selectedProduct = ref({});
const handleOpenQuickView = (productData) => {
  selectedProduct.value = productData;
  isQuickViewOpen.value = true;
};

onMounted(() => { 
    window.scrollTo(0, 0); 
    fetchLookbookDetail();
});
</script>

<style scoped>
.lookbook-detail-wrapper { width: 100%; }

.text-urban-dark { color: var(--color-c-dark, #213448) !important; }
.bg-urban-effect { background-color: var(--color-c-effect, #ebf1f5) !important; }
.hover-text-urban:hover { color: var(--color-c-hover, #547792) !important; }
html.dark .hover-text-urban:hover { color: #fff !important; }

/* BỘ FONT CHUẨN ĐỂ KHÔNG BAO GIỜ BỊ LỖI DẤU TIẾNG VIỆT */
.font-sans-vn { font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', sans-serif !important; }
.font-decor { font-family: 'Times New Roman', Times, serif; font-style: italic; }

.tracking-widest { letter-spacing: 2px; }
.tracking-wide { letter-spacing: 1px; }
.line-clamp-1 { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }
.last-no-border:last-child { border-bottom: none !important; padding-bottom: 0 !important; margin-bottom: 0 !important; }

/* HỆ LƯỚI 5 CỘT */
.custom-grid-5 { display: grid; grid-template-columns: repeat(5, 1fr); gap: 1.5rem; }
@media (max-width: 1199px) { .custom-grid-5 { grid-template-columns: repeat(4, 1fr); } }
@media (max-width: 991px) { .custom-grid-5 { grid-template-columns: repeat(3, 1fr); } }
@media (max-width: 767px) { .custom-grid-5 { grid-template-columns: repeat(2, 1fr); gap: 1rem; } }

/* CSS FIX CHO THẺ TRỐNG */
.empty-slot-card { display: flex; flex-direction: column; }
.empty-img-wrapper { aspect-ratio: 3/4; }
.hover-border-urban:hover { border-color: var(--color-c-hover, #547792) !important; background-color: transparent !important; }

/* NÚT CTAs BÁN HÀNG */
.btn-urban { background-color: var(--color-c-dark, #213448); color: #fff; border: 1px solid var(--color-c-dark, #213448); transition: all 0.3s ease; }
.btn-urban:hover { background-color: var(--color-c-hover, #547792); border-color: var(--color-c-hover, #547792); color: #fff; }
.btn-urban:disabled { opacity: 0.7; pointer-events: none; background-color: #6c757d; border-color: #6c757d; }

.btn-outline-urban { background-color: transparent; color: var(--color-c-dark, #213448); border: 1px solid var(--color-c-dark, #213448); transition: all 0.3s ease; }
.btn-outline-urban:hover { background-color: var(--color-c-dark, #213448); color: #fff; }

.border-urban { border: 2px solid var(--color-c-dark, #213448) !important; }
.combo-swatch { width: 24px; height: 24px; border: 1px solid #dee2e6; cursor: pointer; }
.combo-swatch.active { transform: scale(1.15); box-shadow: 0 0 0 2px rgba(33,52,72,0.3); }
.combo-swatch.out-of-stock { opacity: 0.3; cursor: not-allowed; position: relative; }
.combo-swatch.out-of-stock::after { content: ''; position: absolute; top: 50%; left: -10%; width: 120%; height: 2px; background: red; transform: rotate(-45deg); }

.combo-size-btn { min-width: 40px; }
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #dee2e6; border-radius: 10px; }

.transition-all { transition: all 0.3s ease; }
.group-hover-scale { transition: transform 0.7s cubic-bezier(0.25, 0.46, 0.45, 0.94); }
.group:hover .group-hover-scale { transform: scale(1.05); }
.hover-shadow-lg:hover { box-shadow: 0 1rem 3rem rgba(0,0,0,0.175) !important; transform: translateY(-3px); }

/* SKELETON */
.shimmer {
  background: var(--color-c-effect, #ebf1f5);
  background-image: linear-gradient(to right, var(--color-c-effect) 0%, rgba(255,255,255,0.6) 20%, var(--color-c-effect) 40%, var(--color-c-effect) 100%);
  background-repeat: no-repeat;
  background-size: 800px 100%;
  animation: placeholderShimmer 1.5s infinite linear;
}
html.dark .shimmer { background: #2b3035; background-image: linear-gradient(to right, #2b3035 0%, #343a40 20%, #2b3035 40%, #2b3035 100%); }
@keyframes placeholderShimmer { 0% { background-position: -400px 0; } 100% { background-position: 400px 0; } }
</style>