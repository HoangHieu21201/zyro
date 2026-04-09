<!-- File: frontend/src/pages/client/product/Detail.vue -->
<template>
  <div class="product-detail-page pb-5 mb-5">
    
    <!-- Đẩy nội dung xuống dưới Header -->
    <div class="pt-5 mt-5">
      <div class="zyro-container pt-3">
        
        <!-- ========================================== -->
        <!-- BREADCRUMB ĐIỀU HƯỚNG                      -->
        <!-- ========================================== -->
        <nav aria-label="breadcrumb" class="mb-4">
          <ol class="breadcrumb small fw-semibold text-uppercase" style="letter-spacing: 0.5px;">
            <li class="breadcrumb-item"><router-link to="/" class="text-decoration-none text-muted hover-text-dark">Trang chủ</router-link></li>
            <li class="breadcrumb-item"><router-link to="/category" class="text-decoration-none text-muted hover-text-dark">Nam</router-link></li>
            <li class="breadcrumb-item"><router-link to="/category" class="text-decoration-none text-muted hover-text-dark">Áo Sơ Mi</router-link></li>
            <li class="breadcrumb-item active text-dark text-truncate" aria-current="page" style="max-width: 200px;">{{ product.name }}</li>
          </ol>
        </nav>

        <!-- ========================================== -->
        <!-- KHU VỰC CHÍNH: ẢNH & THÔNG TIN MUA HÀNG    -->
        <!-- ========================================== -->
        <div class="row g-5 mb-5">
          
          <!-- CỘT TRÁI: THƯ VIỆN ẢNH (Bố cục to hơn QuickView) -->
          <div class="col-lg-6">
            <div class="d-flex flex-column gap-3 sticky-top" style="top: 100px; z-index: 1;">
              <!-- Ảnh Chính -->
              <div class="main-img-wrapper rounded-4 overflow-hidden bg-light dark:bg-[#121416] border dark:border-gray-700 position-relative group cursor-zoom-in">
                <img :src="activeImage" class="w-100 object-fit-cover transition-transform group-hover-scale" style="aspect-ratio: 3/4;">
                <span v-if="product.discount_percent" class="position-absolute top-0 start-0 m-3 badge bg-danger fs-6 rounded-pill px-3 py-2 shadow-sm">
                  -{{ product.discount_percent }}%
                </span>
              </div>
              
              <!-- Ảnh Thumbnails (Vuốt ngang) -->
              <div class="d-flex gap-2 overflow-auto custom-scrollbar-x pb-2">
                <div v-for="(img, idx) in product.images" :key="idx" 
                     class="thumb-box border rounded-3 cursor-pointer overflow-hidden flex-shrink-0 bg-light" 
                     :class="activeImage === img ? 'border-urban border-2 shadow-sm' : 'border-light-subtle dark:border-gray-700 opacity-75 hover-opacity-100'" 
                     @click="activeImage = img"
                     style="width: 90px; height: 120px;">
                  <img :src="img" class="w-100 h-100 object-fit-cover p-1 rounded-3">
                </div>
              </div>
            </div>
          </div>

          <!-- CỘT PHẢI: THÔNG TIN CHI TIẾT VÀ CHỐT SALE -->
          <div class="col-lg-6">
            <div class="ps-lg-3">
              
              <!-- Tên & Đánh giá -->
              <h2 class="fw-bold text-dark dark:text-white mb-2">{{ product.name }}</h2>
              <div class="d-flex align-items-center gap-3 mb-3">
                <div class="d-flex text-warning" style="font-size: 0.9rem;">
                  <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-half"></i>
                </div>
                <span class="text-muted small border-start ps-3">(128 đánh giá)</span>
                <span class="text-muted small border-start ps-3">Đã bán: 540</span>
              </div>
              
              <!-- Mã SP & Thương hiệu -->
              <div class="text-muted small mb-4">
                Thương hiệu: <span class="text-urban fw-semibold">{{ product.brand }}</span> | 
                Mã sản phẩm: <span class="text-dark dark:text-gray-300 fw-semibold">{{ product.sku }}</span>
              </div>

              <!-- Giá -->
              <div class="d-flex align-items-center gap-3 mb-4 p-3 bg-light dark:bg-[#1a2533] rounded-3 border dark:border-gray-700">
                <h2 class="text-danger fw-bold mb-0 m-0">{{ formatCurrency(product.price) }}</h2>
                <span v-if="product.old_price" class="text-muted text-decoration-line-through fw-semibold fs-5">
                  {{ formatCurrency(product.old_price) }}
                </span>
              </div>

              <!-- Màu Sắc -->
              <div class="mb-4">
                <label class="fw-bold mb-2 d-block text-dark dark:text-gray-200">
                  Màu sắc: <span class="fw-normal text-muted ms-1">{{ getSelectedColorName }}</span>
                </label>
                <div class="d-flex flex-wrap gap-2">
                  <div v-for="color in product.colors" :key="color.id"
                       class="color-swatch rounded-circle cursor-pointer border shadow-sm transition-all"
                       :class="{'active-swatch': selectedColor === color.id}"
                       :style="{ backgroundColor: color.hex }"
                       @click="selectedColor = color.id"
                       :title="color.name">
                  </div>
                </div>
              </div>

              <!-- Kích Cỡ -->
              <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <label class="fw-bold text-dark dark:text-gray-200 m-0">Kích thước:</label>
                  <button class="btn btn-link text-urban p-0 text-decoration-none small d-flex align-items-center hover-underline">
                    <i class="bi bi-rulers me-1"></i> Hướng dẫn chọn Size
                  </button>
                </div>
                <div class="d-flex flex-wrap gap-2">
                  <button v-for="size in product.sizes" :key="size"
                          type="button" class="btn size-btn fw-semibold text-uppercase"
                          :class="selectedSize === size ? 'btn-outline-urban active text-urban border-2' : 'btn-outline-secondary text-muted border border-light-subtle dark:border-gray-600'"
                          @click="selectedSize = size">
                    {{ size }}
                  </button>
                </div>
              </div>

              <!-- Nút Thao Tác (Số lượng + Giỏ hàng + Mua ngay) -->
              <div class="mb-4 pt-2 border-top dark:border-gray-700 pt-4">
                <label class="fw-bold text-dark dark:text-gray-200 mb-2">Số lượng:</label>
                <div class="d-flex flex-wrap gap-3">
                  <!-- Bộ đếm số lượng -->
                  <div class="quantity-box border border-light-subtle dark:border-gray-600 rounded-3 d-flex bg-white dark:bg-[#212529] shadow-sm" style="width: 130px; height: 50px;">
                    <button class="btn border-0 text-dark dark:text-gray-300 fw-bold fs-5 px-3 bg-transparent h-100 d-flex align-items-center" @click="quantity > 1 ? quantity-- : null"><i class="bi bi-dash"></i></button>
                    <input type="text" class="form-control border-0 text-center fw-bold px-0 h-100 bg-transparent dark:text-white" v-model="quantity" readonly>
                    <button class="btn border-0 text-dark dark:text-gray-300 fw-bold fs-5 px-3 bg-transparent h-100 d-flex align-items-center" @click="quantity++"><i class="bi bi-plus"></i></button>
                  </div>
                  
                  <!-- Yêu thích -->
                  <button class="btn btn-outline-danger rounded-3 d-flex align-items-center justify-content-center shadow-sm hover-bg-danger transition-all" style="width: 50px; height: 50px;" title="Thêm vào yêu thích">
                    <i class="bi bi-heart fs-5"></i>
                  </button>
                </div>
                
                <div class="d-flex flex-column flex-sm-row gap-3 mt-3">
                  <button class="btn btn-outline-dark flex-grow-1 fw-bold py-3 rounded-3 text-uppercase tracking-wide hover-bg-dark transition-all">
                    <i class="bi bi-cart-plus me-2"></i> Thêm Vào Giỏ
                  </button>
                  <button class="btn btn-danger flex-grow-1 fw-bold py-3 rounded-3 text-uppercase tracking-wide shadow hover-transform">
                    Mua Ngay
                  </button>
                </div>
              </div>

              <!-- Trust Badges (Cam kết) -->
              <div class="row g-3 mt-2 border border-light-subtle dark:border-gray-700 rounded-3 p-3 bg-light dark:bg-[#1a2533]">
                <div class="col-12 col-sm-4 d-flex align-items-center gap-2">
                  <i class="bi bi-shield-check fs-3 text-urban"></i>
                  <span class="small fw-medium text-muted lh-sm">Cam kết 100%<br>Chính hãng</span>
                </div>
                <div class="col-12 col-sm-4 d-flex align-items-center gap-2">
                  <i class="bi bi-box-seam fs-3 text-urban"></i>
                  <span class="small fw-medium text-muted lh-sm">Giao hàng toàn quốc<br>Miễn phí từ 500k</span>
                </div>
                <div class="col-12 col-sm-4 d-flex align-items-center gap-2">
                  <i class="bi bi-arrow-return-left fs-3 text-urban"></i>
                  <span class="small fw-medium text-muted lh-sm">Đổi trả dễ dàng<br>trong 30 ngày</span>
                </div>
              </div>

            </div>
          </div>
        </div>

        <!-- ========================================== -->
        <!-- KHU VỰC TABS: MÔ TẢ CHI TIẾT & ĐÁNH GIÁ    -->
        <!-- ========================================== -->
        <div class="product-tabs-section mt-5 pt-4">
          <ul class="nav nav-underline justify-content-center mb-4 gap-4 border-bottom dark:border-gray-700">
            <li class="nav-item">
              <a class="nav-link fw-bold text-uppercase tracking-wide fs-5 pb-3" :class="{ 'active text-dark': activeTab === 'desc', 'text-muted': activeTab !== 'desc' }" href="#" @click.prevent="activeTab = 'desc'">Mô Tả Sản Phẩm</a>
            </li>
            <li class="nav-item">
              <a class="nav-link fw-bold text-uppercase tracking-wide fs-5 pb-3" :class="{ 'active text-dark': activeTab === 'specs', 'text-muted': activeTab !== 'specs' }" href="#" @click.prevent="activeTab = 'specs'">Thông Số Kỹ Thuật</a>
            </li>
            <li class="nav-item">
              <a class="nav-link fw-bold text-uppercase tracking-wide fs-5 pb-3" :class="{ 'active text-dark': activeTab === 'reviews', 'text-muted': activeTab !== 'reviews' }" href="#" @click.prevent="activeTab = 'reviews'">Đánh Giá (128)</a>
            </li>
          </ul>

          <div class="tab-content mx-auto" style="max-width: 900px; min-height: 200px;">
            
            <!-- Tab Mô tả -->
            <div v-show="activeTab === 'desc'" class="animation-fade-up text-muted dark:text-gray-300 lh-lg" v-html="product.description"></div>
            
            <!-- Tab Thông số -->
            <div v-show="activeTab === 'specs'" class="animation-fade-up">
              <table class="table table-bordered table-striped dark:border-gray-700">
                <tbody>
                  <tr><th class="w-25 bg-light dark:bg-[#2b3035] text-muted">Chất liệu</th><td class="text-dark dark:text-gray-300">100% Cotton Compact siêu mềm mịn</td></tr>
                  <tr><th class="w-25 bg-light dark:bg-[#2b3035] text-muted">Kiểu dáng</th><td class="text-dark dark:text-gray-300">Slimfit ôm vừa vặn tôn dáng</td></tr>
                  <tr><th class="w-25 bg-light dark:bg-[#2b3035] text-muted">Họa tiết</th><td class="text-dark dark:text-gray-300">Trơn basic, dễ phối đồ</td></tr>
                  <tr><th class="w-25 bg-light dark:bg-[#2b3035] text-muted">Xuất xứ</th><td class="text-dark dark:text-gray-300">Thiết kế và sản xuất tại Việt Nam bởi ZYRO</td></tr>
                </tbody>
              </table>
            </div>

            <!-- Tab Đánh giá (Demo trống) -->
            <div v-show="activeTab === 'reviews'" class="animation-fade-up text-center py-5">
              <i class="bi bi-chat-square-heart text-light-subtle" style="font-size: 4rem;"></i>
              <h5 class="mt-3 fw-bold text-dark dark:text-white">Chưa có đánh giá nào</h5>
              <p class="text-muted">Hãy là người đầu tiên đánh giá sản phẩm này.</p>
              <button class="btn btn-outline-dark rounded-pill px-4 mt-2">Viết đánh giá</button>
            </div>
          </div>
        </div>

        <!-- ========================================== -->
        <!-- SẢN PHẨM LIÊN QUAN (CROSS-SELL)            -->
        <!-- ========================================== -->
        <div class="related-products mt-5 pt-5 border-top dark:border-gray-700">
          <div class="d-flex justify-content-between align-items-end mb-4">
            <h3 class="fw-bold text-uppercase mb-0 tracking-wide text-dark dark:text-white">Có thể bạn sẽ thích</h3>
            <router-link to="/category" class="text-urban text-decoration-none fw-semibold hover-underline">Xem tất cả <i class="bi bi-arrow-right"></i></router-link>
          </div>
          
          <div class="row g-4">
            <!-- Tái sử dụng ProductCard, chia 4 cột -->
            <div class="col-lg-3 col-md-4 col-6" v-for="rel in relatedProducts" :key="rel.id">
              <ProductCard :product="rel" />
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import ProductCard from '@/components/client/ProductCard.vue';

// --- TRẠNG THÁI TRANG ---
const activeTab = ref('desc');
const activeImage = ref('');
const selectedColor = ref(null);
const selectedSize = ref(null);
const quantity = ref(1);

// --- MOCK DATA SẢN PHẨM HIỆN TẠI ---
const product = ref({
  id: 101,
  name: 'Áo Sơ Mi Nam Cộc Tay Cafe Túi Ngực Chống Nhăn Cao Cấp',
  brand: 'ZYRO MEN',
  sku: 'SM-CAFE-001',
  price: 469000,
  old_price: 650000,
  discount_percent: 28,
  description: `
    <p>Áo sơ mi nam cộc tay Cafe là sự kết hợp hoàn hảo giữa công nghệ sợi Cafe thân thiện môi trường và thiết kế thanh lịch. Khả năng kiểm soát mùi cơ thể vượt trội, chống tia UV và cực kỳ nhanh khô.</p>
    <p><strong>Đặc điểm nổi bật:</strong></p>
    <ul>
      <li>Công nghệ chống nhăn tự nhiên, không cần là ủi sau khi giặt.</li>
      <li>Bề mặt vải mát lạnh, thoáng khí, phù hợp cho mùa hè oi bức.</li>
      <li>Thiết kế túi ngực tiện lợi, phom dáng Slimfit tôn lên đường nét cơ thể.</li>
    </ul>
    <p>Sản phẩm là lựa chọn hoàn hảo cho môi trường công sở, gặp gỡ đối tác hoặc dạo phố cuối tuần.</p>
  `,
  colors: [
    { id: 1, name: 'Xám sáng', hex: '#bdc3c7' },
    { id: 2, name: 'Trắng', hex: '#ffffff' },
    { id: 3, name: 'Xanh dương', hex: '#3498db' }
  ],
  sizes: ['S', 'M', 'L', 'XL', '2XL'],
  images: [
    'https://images.unsplash.com/photo-1602810318383-e386cc2a3ccf?q=80&w=800&auto=format&fit=crop',
    'https://images.unsplash.com/photo-1596755094514-f87e32f85e23?q=80&w=800&auto=format&fit=crop',
    'https://images.unsplash.com/photo-1588359348347-9bc6cbb6858a?q=80&w=800&auto=format&fit=crop',
    'https://images.unsplash.com/photo-1603252109303-2751441dd157?q=80&w=800&auto=format&fit=crop'
  ]
});

// --- MOCK DATA SẢN PHẨM LIÊN QUAN ---
const relatedProducts = ref([
  { id: 103, name: 'Áo Thun Nam Basic Slimfit', price: 149000, old_price: 299000, discount_percent: 50, image: 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?q=80&w=600&auto=format&fit=crop', colors: [{hex: '#7f8fa6'}, {hex: '#000000'}] },
  { id: 104, name: 'Quần Âu Nam Ống Đứng', price: 499000, old_price: 550000, discount_percent: 9, image: 'https://images.unsplash.com/photo-1594938298603-c8148c4dae35?q=80&w=600&auto=format&fit=crop' },
  { id: 105, name: 'Áo Phông Tay Raglan', price: 249000, old_price: 539000, discount_percent: 54, image: 'https://images.unsplash.com/photo-1576566588028-4147f3842f27?q=80&w=600&auto=format&fit=crop' },
  { id: 106, name: 'Quần Kaki Nam Dáng Ôm', price: 399000, old_price: null, image: 'https://images.unsplash.com/photo-1542272604-787c3835535d?q=80&w=600&auto=format&fit=crop' }
]);

// --- COMPUTED & METHODS ---
const getSelectedColorName = computed(() => {
  const c = product.value.colors.find(col => col.id === selectedColor.value);
  return c ? c.name : 'Vui lòng chọn';
});

const formatCurrency = (val) => {
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val || 0);
};

onMounted(() => {
  window.scrollTo(0, 0);
  // Setup giá trị mặc định ban đầu
  activeImage.value = product.value.images[0];
  if (product.value.colors.length > 0) selectedColor.value = product.value.colors[0].id;
  if (product.value.sizes.length > 0) selectedSize.value = product.value.sizes[0];
});
</script>

<style scoped>
.product-detail-page { width: 100%; }

/* =======================================================
   ĐẢM BẢO CHUẨN ZYRO CONTAINER
======================================================== */
.zyro-container {
  width: 100%;
  margin: 0 auto;
  padding-left: 20px;
  padding-right: 20px;
}
@media (min-width: 992px) { .zyro-container { padding-left: 5vw; padding-right: 5vw; } }
@media (min-width: 1600px) { .zyro-container { padding-left: 170px; padding-right: 170px; } }

/* =======================================================
   TIỆN ÍCH & MÀU SẮC
======================================================== */
.text-urban { color: var(--color-c-hover, #547792) !important; }
.border-urban { border-color: var(--color-c-hover, #547792) !important; }
.bg-urban { background-color: var(--color-c-hover, #547792) !important; }

.hover-text-dark:hover { color: #000 !important; }
html.dark .hover-text-dark:hover { color: #fff !important; }
.hover-underline:hover { text-decoration: underline !important; }

.tracking-wide { letter-spacing: 1px; }

/* Nút tương tác */
.hover-bg-dark:hover { background-color: #212529 !important; color: #fff !important; }
html.dark .hover-bg-dark:hover { background-color: #f8f9fa !important; color: #000 !important; border-color: #f8f9fa !important; }

.hover-bg-danger:hover { background-color: #dc3545 !important; color: #fff !important; }
.hover-transform { transition: transform 0.2s ease, box-shadow 0.2s ease; }
.hover-transform:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(220, 53, 69, 0.3) !important; }

/* =======================================================
   HÌNH ẢNH & THUMBNAILS
======================================================== */
.group-hover-scale { transition: transform 0.4s ease; }
.group:hover .group-hover-scale { transform: scale(1.1); }
.cursor-zoom-in { cursor: zoom-in; }

.thumb-box { transition: all 0.2s ease; }
.hover-opacity-100:hover { opacity: 1 !important; }

.custom-scrollbar-x::-webkit-scrollbar { height: 6px; }
.custom-scrollbar-x::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar-x::-webkit-scrollbar-thumb { background: #dee2e6; border-radius: 10px; }

/* =======================================================
   MÀU SẮC & SIZE (Dùng chung style với QuickView)
======================================================== */
.color-swatch {
  width: 32px; height: 32px;
  position: relative;
}
.color-swatch:hover { transform: scale(1.1); }
.color-swatch.active-swatch::after {
  content: ''; position: absolute;
  top: -4px; left: -4px; right: -4px; bottom: -4px;
  border: 1px solid var(--color-c-hover, #547792);
  border-radius: 50%;
}

.size-btn {
  min-width: 60px; height: 42px;
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

/* =======================================================
   TABS CHUYỂN ĐỔI
======================================================== */
.nav-underline .nav-link {
  color: #adb5bd;
  border-bottom: 2px solid transparent;
  transition: all 0.3s ease;
}
.nav-underline .nav-link:hover { color: #495057; }
html.dark .nav-underline .nav-link:hover { color: #f8f9fa; }
.nav-underline .nav-link.active {
  border-bottom-color: #212529;
}
html.dark .nav-underline .nav-link.active {
  color: #fff !important;
  border-bottom-color: #fff;
}

/* Animations */
.animation-fade-up { animation: fadeUp 0.5s ease forwards; }
@keyframes fadeUp {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}
.transition-all { transition: all 0.3s ease; }
</style>