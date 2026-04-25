<template>
  <div class="product-detail-page pb-5 bg-white dark:bg-[#1a2533] min-vh-100" style="padding-top: 120px;">
    
    <div class="bg-light py-3 mb-4 dark:bg-[#121416]">
      <div class="zyro-container">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item">
              <router-link to="/" class="text-decoration-none text-muted hover-text-urban transition-color">Trang chủ</router-link>
            </li>
            <li v-if="product?.category" class="breadcrumb-item">
               <router-link :to="`/category?search=${product.category.name.split(' | ')[0]}`" class="text-decoration-none text-muted hover-text-urban transition-color">
                 {{ product.category.name.split(' | ')[0] }}
               </router-link>
            </li>
            <li class="breadcrumb-item active text-urban fw-medium text-truncate" style="max-width: 200px;" aria-current="page">
              {{ product?.name || 'Đang tải...' }}
            </li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="zyro-container">
      
      <div v-if="isLoading" class="row g-4 g-lg-5 mb-5 pe-none">
        <div class="col-lg-6">
          <div class="position-sticky d-flex gap-3" style="top: 110px; align-items: flex-start;">
            <div class="d-flex flex-column gap-2 overflow-hidden pe-2" style="width: 80px; flex-shrink: 0; max-height: calc(100vh - 150px);">
              <div v-for="i in 6" :key="i" class="skeleton-thumb shimmer rounded-3 flex-shrink-0" style="height: 105px;"></div>
            </div>
            <div class="skeleton-img-wrapper flex-grow-1 shimmer rounded-4 w-100" style="aspect-ratio: 3/4;"></div>
          </div>
        </div>
        <div class="col-lg-6 pt-3">
          <div class="skeleton-title shimmer mb-4 w-75" style="height: 35px;"></div>
          <div class="skeleton-text shimmer mb-4 w-50" style="height: 20px;"></div>
          <div class="skeleton-price shimmer mb-5 w-25" style="height: 50px;"></div>
          <div class="skeleton-text shimmer mb-4 w-100" style="height: 100px;"></div>
          <div class="d-flex gap-3 mb-5">
            <div class="skeleton-swatch shimmer rounded-circle" style="width: 45px; height: 45px;"></div>
            <div class="skeleton-swatch shimmer rounded-circle" style="width: 45px; height: 45px;"></div>
            <div class="skeleton-swatch shimmer rounded-circle" style="width: 45px; height: 45px;"></div>
          </div>
          <div class="skeleton-btn shimmer rounded-3 w-100" style="height: 60px;"></div>
        </div>
      </div>

      <div v-else-if="product" class="row g-4 g-lg-5 mb-5">
        
        <div class="col-lg-6">
           <div class="position-sticky d-flex gap-3" style="top: 110px; align-items: flex-start;">
              
              <div class="d-flex flex-column gap-2 overflow-auto custom-scrollbar-y pe-2 pb-2" style="width: 80px; flex-shrink: 0; max-height: calc(100vh - 150px);">
                 <div v-for="(img, idx) in productGallery" :key="'g'+idx"
                      class="thumb-box border rounded-3 cursor-pointer overflow-hidden flex-shrink-0 transition-all bg-light dark:bg-[#212529]"
                      :class="activeImage === img ? 'border-urban border-2 shadow-sm scale-102' : 'border-light-subtle dark:border-gray-700 opacity-75 hover-opacity-100'"
                      @click="activeImage = img" style="height: 105px; width: 100%;">
                     <img :src="img" @error="handleImageError" class="w-100 h-100 object-fit-cover p-1 rounded-3">
                 </div>
              </div>

              <div class="main-img-box flex-grow-1 position-relative rounded-4 overflow-hidden cursor-zoom-in group shadow-sm" @click="openZoom">
                 <img :src="activeImage" @error="handleImageError" class="w-100 h-auto transition-transform group-hover-zoom" :alt="product.name">
                 
                 <div v-if="product.discount_percent" class="position-absolute top-0 start-0 m-3 z-index-2">
                    <span class="badge bg-danger fs-6 px-3 py-2 rounded-pill shadow-sm">-{{ product.discount_percent }}%</span>
                 </div>

                 <div class="position-absolute bottom-0 end-0 m-3 d-flex gap-2 z-index-2" v-if="productGallery.length > 1">
                   <button class="btn btn-light bg-white dark:bg-[#2b3035] dark:border-gray-600 rounded-circle shadow d-flex align-items-center justify-content-center nav-btn-zoom" style="width: 45px; height: 45px;" @click.stop="prevImage">
                     <i class="bi bi-chevron-left fs-5 text-dark dark:text-white"></i>
                   </button>
                   <button class="btn btn-light bg-white dark:bg-[#2b3035] dark:border-gray-600 rounded-circle shadow d-flex align-items-center justify-content-center nav-btn-zoom" style="width: 45px; height: 45px;" @click.stop="nextImage">
                     <i class="bi bi-chevron-right fs-5 text-dark dark:text-white"></i>
                   </button>
                 </div>
              </div>

           </div>
        </div>

        <div class="col-lg-6 pt-2 pt-lg-0">
           <h2 class="fw-bold mb-3 text-dark dark:text-white lh-base" style="font-size: 2.1rem;">{{ product.name }}</h2>
           
           <div class="d-flex flex-wrap align-items-center gap-3 text-muted mb-4 pb-3 border-bottom dark:border-gray-700">
             <div>Thương hiệu: <span class="text-urban fw-bold">{{ product.brand?.name || 'ZYRO' }}</span></div>
             <div class="vr"></div>
             <div>Mã SP: <span class="fw-semibold text-dark dark:text-gray-300">{{ product.sku || 'N/A' }}</span></div>
             <div class="vr"></div>
             <div class="d-flex align-items-center gap-1 text-warning">
               <i class="bi bi-star-fill"></i>
               <i class="bi bi-star-fill"></i>
               <i class="bi bi-star-fill"></i>
               <i class="bi bi-star-fill"></i>
               <i class="bi bi-star-half"></i>
               <span class="text-muted ms-1 small">(Chưa có đánh giá)</span>
             </div>
           </div>

           <div class="d-flex align-items-end gap-3 mb-4">
             <h1 class="text-danger fw-bold mb-0" style="font-size: 2.5rem; letter-spacing: -1px;">{{ formatCurrency(product.price) }}</h1>
             <span v-if="product.old_price && product.old_price > product.price" class="text-muted text-decoration-line-through fs-5 fw-medium mb-1">
               {{ formatCurrency(product.old_price) }}
             </span>
           </div>

           <div class="mb-4 pt-2">
             <label class="fw-bold mb-3 d-block text-dark dark:text-gray-200 fs-6">
               Màu sắc: <span class="fw-normal text-muted ms-2 fs-6">{{ selectedColor || 'Vui lòng chọn' }}</span>
             </label>
             <div class="d-flex flex-wrap gap-3">
                <template v-if="productColors.length > 0">
                  <div v-for="(color, idx) in productColors" :key="'c'+idx"
                       class="color-swatch-lg rounded-circle cursor-pointer shadow-sm-hover position-relative transition-transform"
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
                  <span class="text-muted fst-italic">Sản phẩm không phân loại màu.</span>
                </template>
             </div>
           </div>

           <div class="mb-5">
             <div class="d-flex justify-content-between align-items-end mb-3">
               <label class="fw-bold text-dark dark:text-gray-200 m-0 fs-6">Kích cỡ:</label>
               <div class="d-flex gap-2 align-items-center">
                 <a v-if="product.size_guide_url" :href="product.size_guide_url" target="_blank" class="text-urban text-decoration-none hover-underline small fw-medium">
                   <i class="bi bi-ruler me-1"></i> Bảng tính Size
                 </a>
                 <!-- Nút bật hướng dẫn chọn Size -->
                 <button type="button" @click="showSizeGuide = true" class="btn btn-link p-0 text-urban small text-decoration-none hover-underline shadow-none">
                   Hướng dẫn chọn Size
                 </button>
               </div>
             </div>
             
             <div class="d-flex flex-wrap gap-3">
                <template v-if="productSizes.length > 0">
                  <button v-for="size in productSizes" :key="size.name"
                          type="button"
                          class="size-btn-lg fw-bold d-flex align-items-center justify-content-center"
                          :class="[
                            {'active-size': selectedSize === size.name},
                            {'disabled-size text-decoration-line-through opacity-50': size.out_of_stock}
                          ]"
                          :disabled="size.out_of_stock"
                          @click="selectedSize = size.name"
                          :title="size.out_of_stock ? 'Hết hàng' : ''">
                    {{ size.name }}
                  </button>
                </template>
                <template v-else>
                  <span class="text-muted fst-italic border border-dashed rounded px-3 py-2">Freesize (Mặc định)</span>
                </template>
             </div>
           </div>

           <div class="d-flex flex-wrap gap-3 mb-4">
             <div class="quantity-box border border-light-subtle dark:border-gray-600 rounded-3 d-flex bg-white dark:bg-[#212529] shadow-sm" style="width: 150px; height: 60px;">
               <button class="btn border-0 text-urban fw-bold fs-4 px-3 bg-transparent h-100 d-flex align-items-center hover-bg-light" @click="quantity > 1 ? quantity-- : null"><i class="bi bi-dash"></i></button>
               <input type="text" class="form-control border-0 text-center fw-bold px-0 h-100 bg-transparent dark:text-white fs-5 shadow-none" v-model="quantity" readonly>
               <button class="btn border-0 text-urban fw-bold fs-4 px-3 bg-transparent h-100 d-flex align-items-center hover-bg-light" @click="quantity++"><i class="bi bi-plus"></i></button>
             </div>
             
             <button class="btn btn-danger flex-grow-1 fw-bold fs-5 shadow hover-transform text-uppercase tracking-wide d-flex align-items-center justify-content-center" style="border-radius: 8px; height: 60px;" @click="addToCart" :disabled="isAddingToCart">
               <span v-if="isAddingToCart" class="spinner-border spinner-border-sm me-2"></span>
               <template v-else><i class="bi bi-cart-plus me-2 fs-4"></i> Thêm vào giỏ</template>
             </button>
           </div>
           
           <div class="d-flex flex-wrap gap-3 pb-4 border-bottom dark:border-gray-700">
             <button class="btn border fw-bold shadow-sm transition-all d-flex align-items-center justify-content-center flex-grow-1"
                     :class="isWishlisted ? 'btn-danger text-white border-danger' : 'btn-light bg-white dark:bg-[#2b3035] dark:text-gray-300 dark:border-gray-600 text-dark hover-text-danger'"
                     @click="toggleWishlist" :disabled="isTogglingWishlist" style="height: 48px; border-radius: 8px;">
               <span v-if="isTogglingWishlist" class="spinner-border spinner-border-sm me-2"></span>
               <template v-else>
                 <i class="bi fs-5 me-2" :class="isWishlisted ? 'bi-heart-fill' : 'bi-heart'"></i>
                 {{ isWishlisted ? 'Đã yêu thích' : 'Yêu thích' }}
               </template>
             </button>

             <button class="btn btn-light bg-white dark:bg-[#2b3035] dark:text-gray-300 dark:border-gray-600 border text-dark fw-bold shadow-sm transition-all hover-text-urban d-flex align-items-center justify-content-center flex-grow-1"
                     @click="compareStore.add(product)" style="height: 48px; border-radius: 8px;">
               <i class="bi bi-arrow-left-right fs-5 me-2"></i> So sánh
             </button>
           </div>

           <div class="mt-4 p-4 bg-light dark:bg-[#212529] rounded-4 border dark:border-gray-700">
             <div class="row g-4">
                <div class="col-sm-6 d-flex align-items-center gap-3">
                   <div class="bg-white dark:bg-[#1a2533] p-2 rounded-circle shadow-sm">
                      <i class="bi bi-truck text-urban fs-3"></i>
                   </div>
                   <div><span class="d-block fw-bold text-dark dark:text-white">Freeship Toàn Quốc</span><small class="text-muted">Cho đơn từ 1.000.000đ</small></div>
                </div>
                <div class="col-sm-6 d-flex align-items-center gap-3">
                   <div class="bg-white dark:bg-[#1a2533] p-2 rounded-circle shadow-sm">
                      <i class="bi bi-arrow-repeat text-urban fs-3"></i>
                   </div>
                   <div><span class="d-block fw-bold text-dark dark:text-white">Đổi Trả Dễ Dàng</span><small class="text-muted">Trong vòng 15 ngày</small></div>
                </div>
             </div>
           </div>

        </div>
      </div>

      <div v-else class="text-center py-5 my-5">
        <i class="bi bi-emoji-frown fs-1 text-muted mb-3 d-block"></i>
        <h3 class="text-dark dark:text-white fw-bold">Không tìm thấy sản phẩm!</h3>
        <p class="text-muted mb-4">Sản phẩm này không tồn tại, đã bị xóa hoặc đang tạm ẩn.</p>
        <router-link to="/" class="btn btn-urban rounded-pill px-5 py-3 fw-bold text-uppercase tracking-wide">Quay về trang chủ</router-link>
      </div>

      <div v-if="product" class="mb-5 pt-4">
         <ul class="nav nav-tabs custom-tabs mb-4 justify-content-center border-0 gap-2 gap-md-4" role="tablist">
           <li class="nav-item" role="presentation">
             <button class="nav-link active fw-bold px-3 px-md-4 py-2 py-md-3 text-uppercase tracking-wide rounded-pill transition-all" data-bs-toggle="tab" data-bs-target="#desc-tab" type="button">Chi Tiết Sản Phẩm</button>
           </li>
           <li class="nav-item" role="presentation">
             <button class="nav-link fw-bold px-3 px-md-4 py-2 py-md-3 text-uppercase tracking-wide rounded-pill transition-all" data-bs-toggle="tab" data-bs-target="#specs-tab" type="button">Thông Số Kỹ Thuật</button>
           </li>
           <li class="nav-item" role="presentation">
             <button class="nav-link fw-bold px-3 px-md-4 py-2 py-md-3 text-uppercase tracking-wide rounded-pill transition-all" data-bs-toggle="tab" data-bs-target="#care-tab" type="button">Bảo Quản</button>
           </li>
         </ul>
         
         <div class="tab-content py-4 px-3 px-md-5 bg-white dark:bg-[#1a2533] rounded-4 shadow-sm border dark:border-gray-700">
           
           <div class="tab-pane fade show active" id="desc-tab" role="tabpanel">
             <div class="product-html-content text-dark dark:text-gray-300 lh-lg" v-html="product.description || '<p class=\'text-muted fst-italic text-center py-4\'>Chưa có thông tin mô tả chi tiết cho sản phẩm này.</p>'"></div>
           </div>
           
           <div class="tab-pane fade" id="specs-tab" role="tabpanel">
             <div v-if="product.specifications && Object.keys(product.specifications).length > 0" class="row justify-content-center">
                <div class="col-md-8">
                  <table class="table table-bordered dark:table-dark mb-0 rounded overflow-hidden shadow-sm">
                     <tbody>
                        <tr v-for="(value, key) in product.specifications" :key="key">
                           <th class="w-25 bg-light dark:bg-[#212529] text-dark dark:text-gray-200 py-3">{{ key }}</th>
                           <td class="text-muted dark:text-gray-400 py-3">{{ value }}</td>
                        </tr>
                     </tbody>
                  </table>
                </div>
             </div>
             <div v-else class="text-muted fst-italic text-center py-4">Sản phẩm này chưa có bảng thông số kỹ thuật.</div>
           </div>
           
           <div class="tab-pane fade" id="care-tab" role="tabpanel">
             <div class="product-html-content text-dark dark:text-gray-300 lh-lg" v-html="product.care_instructions || '<p class=\'text-muted fst-italic text-center py-4\'>Sản phẩm không có hướng dẫn bảo quản đặc biệt.</p>'"></div>
           </div>
           
         </div>
      </div>

      <div v-if="product" class="related-products-section mt-5 pt-5 border-top dark:border-gray-700 mb-2">
        
        <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-end mb-4 gap-3">
          <div class="flex-shrink-0">
            <h3 class="fw-bold text-uppercase mb-0 tracking-wide text-dark dark:text-white text-nowrap" style="font-size: 1.6rem;">Có Thể Bạn Sẽ Thích</h3>
          </div>
          
          <div class="d-flex align-items-center justify-content-start justify-content-xl-end w-100 overflow-hidden">
            <ul class="nav nav-underline section-tabs border-0 m-0 flex-nowrap overflow-auto custom-scrollbar-x pb-1 w-100 justify-content-xl-end" style="white-space: nowrap;">
              <li class="nav-item">
                <a class="nav-link fw-bold px-1 mx-2" :class="{ active: activeRelatedTab === 'related' }" href="#" @click.prevent="fetchRelatedProducts('related')">Sản phẩm liên quan</a>
              </li>
              <li class="nav-item">
                <a class="nav-link fw-bold px-1 mx-2" :class="{ active: activeRelatedTab === 'flash_sale' }" href="#" @click.prevent="fetchRelatedProducts('flash_sale')">Flash Sale</a>
              </li>
              <li class="nav-item">
                <a class="nav-link fw-bold px-1 mx-2" :class="{ active: activeRelatedTab === 'top' }" href="#" @click.prevent="fetchRelatedProducts('top')">Đánh giá cao</a>
              </li>
            </ul>
            
            <div class="d-none d-md-flex gap-2 ms-3 border-start dark:border-gray-700 ps-3 flex-shrink-0">
              <button class="btn btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center p-0 hover-btn-urban transition-all" style="width: 36px; height: 36px;" @click="scrollRelated('left')"><i class="bi bi-chevron-left"></i></button>
              <button class="btn btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center p-0 hover-btn-urban transition-all" style="width: 36px; height: 36px;" @click="scrollRelated('right')"><i class="bi bi-chevron-right"></i></button>
            </div>
          </div>
        </div>

        <div class="product-swiper-container pb-4 px-1 cursor-grab" ref="relatedSwiperRef" @mousedown="startDrag" @mouseleave="endDrag" @mouseup="endDrag" @mousemove="doDrag">
          
          <template v-if="isRelatedLoading">
             <div v-for="i in 5" :key="'skel-rel'+i" class="product-swiper-slide">
               <div class="skeleton-card w-100" aria-hidden="true">
                  <div class="skeleton-img-wrapper shimmer rounded-3 mb-3 w-100" style="aspect-ratio: 3/4;"></div>
                  <div class="product-info px-1 w-100">
                    <div class="skeleton-price shimmer mb-2" style="height: 22px; width: 45%; border-radius: 4px;"></div>
                    <div class="skeleton-title shimmer mb-3" style="height: 16px; width: 90%; border-radius: 4px;"></div>
                    <div class="d-flex gap-2">
                      <div class="skeleton-swatch shimmer rounded-circle" style="width: 18px; height: 18px;"></div>
                      <div class="skeleton-swatch shimmer rounded-circle" style="width: 18px; height: 18px;"></div>
                    </div>
                  </div>
                </div>
             </div>
          </template>

          <div v-else-if="relatedProducts.length === 0" class="w-100 text-center py-5 text-muted fst-italic">
            Chưa có sản phẩm nào phù hợp.
          </div>

          <template v-else>
             <div class="product-swiper-slide h-100" v-for="prod in relatedProducts" :key="'rel'+prod.id">
               <ProductCard class="h-100" :product="prod" @compare="compareStore.add" @quick-view="handleOpenQuickView" @options="handleGoToDetail" />
             </div>
          </template>
        </div>

      </div>

    </div>

    <GallerySection :images="lookbookImages" />

    <transition name="fade">
      <div v-if="isZoomOpen" class="custom-zoom-overlay d-flex align-items-center justify-content-center" @click="closeZoom">
        
        <button type="button" class="btn-close-zoom position-absolute top-0 end-0 m-4 bg-dark bg-opacity-75 text-white rounded-circle border-0 d-flex align-items-center justify-content-center shadow-lg transition-all" @click.stop="closeZoom">
          <i class="bi bi-x-lg fs-5"></i>
        </button>
        
        <img :src="zoomedImageUrl" class="zoomed-img object-fit-contain shadow-lg" @click.stop>
        
      </div>
    </transition>

    <CompareModal />
    <QuickViewModal :is-open="isQuickViewOpen" :product="selectedProduct" @close="isQuickViewOpen = false" />
    <SizeGuideModal :show="showSizeGuide" :product="product" :activeImage="activeImage" @close="showSizeGuide = false" />

  </div>
</template>

<script setup>
import { ref, watch, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '@/utils/axios';

import ProductCard from '@/components/client/ProductCard.vue';
import CompareModal from '@/components/client/CompareModal.vue';
import QuickViewModal from '@/components/client/QuickViewModal.vue';
import SizeGuideModal from '@/components/client/SizeGuideModal.vue';
import GallerySection from '@/components/client/home/GallerySection.vue';

import { useCartStore } from '@/stores/cartStore';
import { useCompareStore } from '@/stores/compareStore';
import { useWishlistStore } from '@/stores/wishlistStore';
import { ZyroSwal } from '@/components/client/ZyroSwal';

const route = useRoute();
const router = useRouter();
const cartStore = useCartStore();
const compareStore = useCompareStore();
const wishlistStore = useWishlistStore();
const defaultImage = '/client_placeholder.png'; 

const isLoading = ref(true);
const isAddingToCart = ref(false);
const isTogglingWishlist = ref(false);
const product = ref(null);

const activeImage = ref('');
const selectedColor = ref(null);
const selectedSize = ref(null);
const quantity = ref(1);

const isWishlisted = computed(() => {
  return product.value ? wishlistStore.items.includes(product.value.id) : false;
});

const zoomedImageUrl = ref('');
const isZoomOpen = ref(false);
const showSizeGuide = ref(false);

const isQuickViewOpen = ref(false);
const selectedProduct = ref({});
const handleOpenQuickView = (prod) => {
  selectedProduct.value = prod;
  isQuickViewOpen.value = true;
};

const lookbookImages = ref([
  'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?w=400',
  'https://images.unsplash.com/photo-1529139574466-a303027c1d8b?w=400',
  'https://images.unsplash.com/photo-1503342217505-b0a15ec3261c?w=400',
  'https://images.unsplash.com/photo-1551163943-3f6a855d1153?w=400',
]);

const handleGoToDetail = (prod) => {
  router.push(`/product/${prod.id}`);
};

const fetchProductDetail = async () => {
  isLoading.value = true;
  try {
    const res = await api.get(`/client/products/${route.params.id}`);
    if (res.data.success) {
      product.value = res.data.data;
      initSelectors(); 
      fetchRelatedProducts('related');
    }
  } catch (error) {
    console.error('Lỗi lấy chi tiết sản phẩm', error);
    product.value = null;
  } finally {
    isLoading.value = false;
  }
};

const initSelectors = () => {
  quantity.value = 1;
  const colors = productColors.value;
  
  if (colors.length > 0) {
    const availableColor = colors.find(c => !c.out_of_stock) || colors[0];
    selectedColor.value = availableColor.name;
    activeImage.value = availableColor.image || product.value.image || defaultImage;
  } else {
    selectedColor.value = null;
    selectedSize.value = null;
    activeImage.value = product.value.image || defaultImage;
  }
};

const productColors = computed(() => product.value?.colors || []);

const productSizes = computed(() => {
  if (!selectedColor.value || !product.value) return [];
  const colorObj = productColors.value.find(c => c.name === selectedColor.value);
  return colorObj && colorObj.sizes ? colorObj.sizes : [];
});

const productGallery = computed(() => {
  if (!product.value) return [defaultImage];
  if (product.value.gallery && product.value.gallery.length > 0) {
      return product.value.gallery;
  }
  const images = new Set();
  if (product.value.image) images.add(product.value.image);
  if (product.value.colors) {
    product.value.colors.forEach(c => {
      if (c.image) images.add(c.image);
    });
  }
  return Array.from(images).length > 0 ? Array.from(images) : [defaultImage];
});

const prevImage = () => {
  if (!productGallery.value.length) return;
  let currentIndex = productGallery.value.indexOf(activeImage.value);
  if (currentIndex > 0) {
    activeImage.value = productGallery.value[currentIndex - 1];
  } else {
    activeImage.value = productGallery.value[productGallery.value.length - 1]; 
  }
};

const nextImage = () => {
  if (!productGallery.value.length) return;
  let currentIndex = productGallery.value.indexOf(activeImage.value);
  if (currentIndex < productGallery.value.length - 1) {
    activeImage.value = productGallery.value[currentIndex + 1];
  } else {
    activeImage.value = productGallery.value[0]; 
  }
};

const selectColor = (color) => {
  if (color.out_of_stock) return;
  selectedColor.value = color.name;
  if (color.image) activeImage.value = color.image;
};

watch(selectedColor, (newColor) => {
  if (newColor && product.value) {
    const colorObj = productColors.value.find(c => c.name === newColor);
    if (colorObj && colorObj.sizes && colorObj.sizes.length > 0) {
      const firstAvailable = colorObj.sizes.find(s => !s.out_of_stock);
      selectedSize.value = firstAvailable ? firstAvailable.name : colorObj.sizes[0].name;
    } else {
      selectedSize.value = null;
    }
  }
});

watch(() => route.params.id, (newId) => {
    if (newId) {
        window.scrollTo({ top: 0, behavior: 'smooth' });
        fetchProductDetail();
    }
});

const formatCurrency = (val) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val || 0);
const handleImageError = (e) => { e.target.src = defaultImage; };

const openZoom = () => {
  zoomedImageUrl.value = activeImage.value;
  isZoomOpen.value = true;
  document.body.style.overflow = 'hidden'; 
};

const closeZoom = () => {
  isZoomOpen.value = false;
  document.body.style.overflow = ''; 
};

const toggleWishlist = async () => {
  if (product.value) {
    isTogglingWishlist.value = true;
    await wishlistStore.toggleWishlist(product.value.id);
    isTogglingWishlist.value = false;
  }
};

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
    } else if (product.value.variants && product.value.variants.length > 0) {
      variantId = product.value.variants[0].id;
    }

    if (!variantId) {
      ZyroSwal.toastSuccess('Không xác định được phân loại sản phẩm!');
      return;
    }

    isAddingToCart.value = true;
    try {
      await cartStore.addToCart(variantId, quantity.value, {
        name: product.value.name,
        price: product.value.price,
        image: activeImage.value,
        color: selectedColor.value,
        size: selectedSize.value,
        slug: product.value.slug
      });

      ZyroSwal.toastSuccess(`Đã thêm ${quantity.value} sản phẩm vào giỏ hàng`);
    } catch (error) {
      console.error(error);
      ZyroSwal.toastSuccess('Có lỗi xảy ra khi thêm vào giỏ');
    } finally {
      isAddingToCart.value = false;
    }
};

const activeRelatedTab = ref('related');
const relatedProducts = ref([]);
const isRelatedLoading = ref(false);

const fetchRelatedProducts = async (tab) => {
  if (isRelatedLoading.value) return;
  activeRelatedTab.value = tab;
  isRelatedLoading.value = true;
  relatedProducts.value = [];

  try {
    let res;
    if (tab === 'related') {
      const catId = product.value?.category?.id;
      if (catId) {
        res = await api.get(`/client/home/new-arrivals-tab?category_id=${catId}`);
        let products = res.data.data.filter(p => p.id !== product.value.id);

        if (products.length < 8 && product.value?.category?.name.includes(' | ')) {
           const parentName = product.value.category.name.split(' | ')[0].trim();
           
           const homeRes = await api.get('/client/home');
           const rootCategories = homeRes.data.data.new_arrivals.tabs;
           
           const parentCat = rootCategories.find(c => c.name === parentName);
           if (parentCat) {
               const parentRes = await api.get(`/client/home/new-arrivals-tab?category_id=${parentCat.id}`);
               const parentProducts = parentRes.data.data.filter(p => p.id !== product.value.id);
               
               const existingIds = new Set(products.map(p => p.id));
               for (const fp of parentProducts) {
                 if (!existingIds.has(fp.id)) {
                   products.push(fp);
                   existingIds.add(fp.id);
                 }
                 if (products.length >= 8) break; 
               }
           }
        }
        relatedProducts.value = products;
      }
    } else if (tab === 'flash_sale') {
      res = await api.get(`/client/home`);
      relatedProducts.value = res.data.data.flash_sale?.products?.filter(p => p.id !== product.value.id) || [];
    } else if (tab === 'top') {
      res = await api.get(`/client/home`);
      relatedProducts.value = res.data.data.most_loved?.hot_trends?.filter(p => p.id !== product.value.id) || [];
    }
    
    if (relatedSwiperRef.value) relatedSwiperRef.value.scrollLeft = 0;

  } catch (error) {
    console.error("Lỗi tải Sản phẩm liên quan:", error);
  } finally {
    isRelatedLoading.value = false;
  }
};

const relatedSwiperRef = ref(null);
let isDown = false; let startX; let scrollLeft;
const startDrag = (e) => { isDown = true; relatedSwiperRef.value.classList.add('active-drag'); startX = e.pageX - relatedSwiperRef.value.offsetLeft; scrollLeft = relatedSwiperRef.value.scrollLeft; };
const endDrag = () => { isDown = false; if (relatedSwiperRef.value) relatedSwiperRef.value.classList.remove('active-drag'); };
const doDrag = (e) => { if (!isDown) return; e.preventDefault(); const x = e.pageX - relatedSwiperRef.value.offsetLeft; const walk = (x - startX) * 1.5; relatedSwiperRef.value.scrollLeft = scrollLeft - walk; };

const scrollRelated = (direction) => {
  if (relatedSwiperRef.value) {
    const cardWidth = relatedSwiperRef.value.offsetWidth / 4; 
    const scrollAmount = cardWidth * 2; 
    if (direction === 'left') {
      relatedSwiperRef.value.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
    } else {
      relatedSwiperRef.value.scrollBy({ left: scrollAmount, behavior: 'smooth' });
    }
  }
};

onMounted(() => {
    window.scrollTo(0,0);
    fetchProductDetail();
});
</script>

<style scoped>
.bg-urban { background-color: var(--color-c-hover, #547792) !important; }
.text-urban { color: var(--color-c-hover, #547792) !important; }
.btn-urban { background-color: var(--color-c-hover, #547792); color: white; border: none; }
.btn-urban:hover { background-color: var(--color-c-dark, #213448); color: white; }
.hover-text-urban:hover { color: var(--color-c-hover, #547792) !important; }

.main-img-box {
  transition: all 0.3s ease;
}
.cursor-zoom-in { cursor: zoom-in; }
.group-hover-zoom { transition: transform 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94); }
.group:hover .group-hover-zoom { transform: scale(1.05); }
.z-index-2 { z-index: 2; }

.nav-btn-zoom {
  transition: all 0.2s ease;
}
.nav-btn-zoom:hover {
  background-color: var(--color-c-hover, #547792) !important;
  color: white !important;
  border-color: var(--color-c-hover, #547792) !important;
}
.nav-btn-zoom:hover i {
  color: white !important;
}

.thumb-box {
  transition: all 0.3s ease;
}
.hover-opacity-100:hover { opacity: 1 !important; }
.scale-102 { transform: scale(1.03); }

.color-swatch-lg {
  width: 45px; height: 45px;
  border: 2px solid transparent;
}
.color-swatch-lg.active-swatch {
  border-color: #dc3545 !important;
  transform: scale(1.1);
  box-shadow: 0 6px 12px rgba(0,0,0,0.15);
}
.color-swatch-lg.active-swatch::after {
  content: ''; position: absolute;
  top: -4px; left: -4px; right: -4px; bottom: -4px; 
  border: 2px solid #f6c23e; border-radius: 50%;
}
.shadow-sm-hover:hover { box-shadow: 0 6px 12px rgba(0,0,0,0.1); transform: scale(1.05); }
.swatch-white { border: 1px solid #d1d5db !important; }
.swatch-out-of-stock { opacity: 0.3; cursor: not-allowed !important; overflow: hidden; }
.swatch-out-of-stock::before {
  content: ''; position: absolute;
  top: 50%; left: -20%; width: 140%; height: 2px;
  background-color: #dc3545; transform: translateY(-50%) rotate(-45deg); z-index: 2;
}

.size-btn-lg {
  min-width: 60px;
  height: 45px;
  border-radius: 8px;
  background-color: #fff;
  color: #212529;
  border: 1px solid #dee2e6;
  transition: all 0.2s ease;
}
html.dark .size-btn-lg { 
  background-color: #212529; 
  color: #f8f9fa; 
  border-color: #495057; 
}
.size-btn-lg:hover:not(.disabled-size) {
  border-color: #547792 !important; 
  color: #547792 !important;
  background-color: rgba(84, 119, 146, 0.05);
}
.size-btn-lg.active-size {
  background-color: #547792 !important;
  color: #ffffff !important;
  border-color: #547792 !important;
  box-shadow: 0 4px 6px rgba(84, 119, 146, 0.3);
  transform: scale(1.02);
}
.disabled-size {
  cursor: not-allowed;
  background-color: #f8f9fa !important;
  color: #adb5bd !important;
}
html.dark .disabled-size { 
  background-color: #2b3035 !important; 
  color: #6c757d !important; 
}

.hover-transform { transition: transform 0.2s ease, box-shadow 0.2s ease; }
.hover-transform:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(220, 53, 69, 0.3) !important; }
.hover-underline:hover { text-decoration: underline !important; }
.hover-bg-light:hover { background-color: rgba(0,0,0,0.05) !important; }
html.dark .hover-bg-light:hover { background-color: rgba(255,255,255,0.05) !important; }
.transition-color { transition: color 0.2s ease; }
.transition-all { transition: all 0.3s ease; }
.tracking-wide { letter-spacing: 1px; }

.custom-tabs .nav-link {
  color: #6c757d;
  background: transparent;
  border: 1px solid transparent;
}
.custom-tabs .nav-link:hover { color: var(--color-c-hover, #547792); }
.custom-tabs .nav-link.active {
  color: white !important;
  background-color: var(--color-c-hover, #547792) !important;
  box-shadow: 0 4px 10px rgba(84, 119, 146, 0.3);
}
html.dark .custom-tabs .nav-link { color: #adb5bd; }
html.dark .custom-tabs .nav-link.active { color: white !important; }

.product-swiper-container { 
  display: flex; gap: 20px; overflow-x: auto; 
  scroll-snap-type: x mandatory; scroll-behavior: smooth; 
  -webkit-overflow-scrolling: touch; 
}
.product-swiper-container.active-drag { scroll-snap-type: none; scroll-behavior: auto; }
.product-swiper-slide { 
  width: calc((100% - 80px) / 5); min-width: 220px; 
  flex-shrink: 0; scroll-snap-align: start; 
}
@media (max-width: 1199px) { .product-swiper-slide { width: calc((100% - 60px) / 4); } }
@media (max-width: 991px) { .product-swiper-slide { width: calc((100% - 40px) / 3); } }
@media (max-width: 767px) { .product-swiper-slide { width: calc((100% - 20px) / 2); min-width: 160px; } }
.product-swiper-container::-webkit-scrollbar { height: 0px; display: none; }

.section-tabs .nav-link { 
  color: #6c757d; border-bottom: 2px solid transparent; 
  padding-bottom: 8px; transition: all 0.3s ease; font-size: 0.95rem; 
}
.section-tabs .nav-link:hover, .section-tabs .nav-link.active { 
  color: var(--color-c-hover, #009981) !important; 
  border-bottom-color: var(--color-c-hover, #009981) !important; 
}
.hover-btn-urban:hover { 
  background-color: var(--color-c-hover, #009981) !important; 
  color: white !important; border-color: var(--color-c-hover, #009981) !important; 
}

.product-html-content :deep(img) { max-width: 100%; height: auto; border-radius: 8px; margin: 15px 0; }
.product-html-content :deep(p) { margin-bottom: 1rem; }

.shimmer {
  background: #f6f7f8;
  background-image: linear-gradient(to right, #f6f7f8 0%, #edeef1 20%, #f6f7f8 40%, #f6f7f8 100%);
  background-repeat: no-repeat;
  background-size: 800px 100%;
  animation: placeholderShimmer 1.5s infinite linear;
}
html.dark .shimmer {
  background: #2b3035;
  background-image: linear-gradient(to right, #2b3035 0%, #343a40 20%, #2b3035 40%, #2b3035 100%);
}
@keyframes placeholderShimmer { 0% { background-position: -400px 0; } 100% { background-position: 400px 0; } }
.skeleton-img-wrapper { background-color: #f0f0f0; }
.skeleton-thumb { background-color: #f0f0f0; }
.skeleton-title { background-color: #f0f0f0; border-radius: 6px; }
.skeleton-price { background-color: #f0f0f0; border-radius: 8px; }
.skeleton-text { background-color: #f0f0f0; border-radius: 4px; }
.skeleton-swatch { background-color: #f0f0f0; }
.skeleton-btn { background-color: #f0f0f0; }
html.dark .skeleton-img-wrapper, html.dark .skeleton-thumb, html.dark .skeleton-title, html.dark .skeleton-price, html.dark .skeleton-text, html.dark .skeleton-swatch, html.dark .skeleton-btn { background-color: #2b3035; }

.custom-scrollbar-y::-webkit-scrollbar { width: 6px; }
.custom-scrollbar-y::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar-y::-webkit-scrollbar-thumb { background: var(--color-c-light, #94B4C1); border-radius: 10px; }

.custom-scrollbar-x::-webkit-scrollbar { height: 4px; }
.custom-scrollbar-x::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar-x::-webkit-scrollbar-thumb { background: transparent; border-radius: 10px; }
.custom-scrollbar-x:hover::-webkit-scrollbar-thumb { background: #dee2e6; }
html.dark .custom-scrollbar-x:hover::-webkit-scrollbar-thumb { background: #495057; }

.custom-zoom-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-color: rgba(0, 0, 0, 0.9);
  z-index: 1060;
  backdrop-filter: blur(5px);
}
.zoomed-img {
  max-width: 95vw;
  max-height: 95vh;
  border-radius: 8px;
  cursor: default;
}
.btn-close-zoom {
  width: 45px;
  height: 45px;
  transition: background-color 0.2s ease, transform 0.2s ease;
}
.btn-close-zoom:hover {
  background-color: rgba(220, 53, 69, 0.9) !important;
  transform: scale(1.1);
}

.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>