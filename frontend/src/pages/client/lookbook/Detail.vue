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

    <!-- MODALS -->
    <ComboSelectionModal ref="comboModalRef" />
    <QuickViewModal :is-open="isQuickViewOpen" :product="selectedProduct" @close="isQuickViewOpen = false" />
    <CompareModal />

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
import ComboSelectionModal from '@/components/client/ComboSelectionModal.vue';

const route = useRoute();
const router = useRouter();
const compareStore = useCompareStore();

const lookbook = ref(null);
const isLoading = ref(true);

// Tham chiếu đến Component Modal vừa tạo
const comboModalRef = ref(null);

const totalItemsCount = computed(() => {
  return lookbook.value?.products?.length || 0;
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

// Kích hoạt hàm bên trong Component Con
const openComboModal = () => {
  if (comboModalRef.value && lookbook.value) {
      comboModalRef.value.openModal(lookbook.value);
  }
};

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

.font-sans-vn { font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', sans-serif !important; }
.font-decor { font-family: 'Times New Roman', Times, serif; font-style: italic; }

.tracking-widest { letter-spacing: 2px; }
.line-clamp-1 { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }

.custom-grid-5 { display: grid; grid-template-columns: repeat(5, 1fr); gap: 1.5rem; }
@media (max-width: 1199px) { .custom-grid-5 { grid-template-columns: repeat(4, 1fr); } }
@media (max-width: 991px) { .custom-grid-5 { grid-template-columns: repeat(3, 1fr); } }
@media (max-width: 767px) { .custom-grid-5 { grid-template-columns: repeat(2, 1fr); gap: 1rem; } }

.empty-slot-card { display: flex; flex-direction: column; }
.empty-img-wrapper { aspect-ratio: 3/4; }
.hover-border-urban:hover { border-color: var(--color-c-hover, #547792) !important; background-color: transparent !important; }

.btn-urban { background-color: var(--color-c-dark, #213448); color: #fff; border: 1px solid var(--color-c-dark, #213448); transition: all 0.3s ease; }
.btn-urban:hover { background-color: var(--color-c-hover, #547792); border-color: var(--color-c-hover, #547792); color: #fff; }

.btn-outline-urban { background-color: transparent; color: var(--color-c-dark, #213448); border: 1px solid var(--color-c-dark, #213448); transition: all 0.3s ease; }
.btn-outline-urban:hover { background-color: var(--color-c-dark, #213448); color: #fff; }

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