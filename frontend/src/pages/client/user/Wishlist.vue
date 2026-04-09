<!-- File: frontend/src/pages/client/user/Wishlist.vue -->
<template>
  <div class="user-wishlist-wrapper pb-5 mb-5">
    <div class="pt-5 mt-4">
      <div class="zyro-container">
        <nav aria-label="breadcrumb" class="mb-4">
          <ol class="breadcrumb small fw-semibold text-uppercase" style="letter-spacing: 0.5px;">
            <li class="breadcrumb-item"><router-link to="/" class="text-decoration-none text-muted hover-text-dark">Trang chủ</router-link></li>
            <li class="breadcrumb-item"><router-link to="/user/profile" class="text-decoration-none text-muted hover-text-dark">Tài khoản</router-link></li>
            <li class="breadcrumb-item active text-c-dark" aria-current="page">Sản phẩm yêu thích</li>
          </ol>
        </nav>

        <div class="row g-4 g-lg-5">
          <!-- CỘT TRÁI: SIDEBAR QUẢN LÝ -->
          <div class="col-lg-3">
            <UserSidebar />
          </div>

          <!-- CỘT PHẢI: NỘI DUNG WISHLIST -->
          <div class="col-lg-9">
            <div class="card border-0 shadow-sm rounded-4 dark:bg-[#1a2533] animation-fade-in p-4 p-md-5 min-vh-50">
              <div class="mb-4 pb-3 border-bottom dark:border-gray-700 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                  <h4 class="fw-bold text-c-dark dark:text-white mb-1">Sản Phẩm Yêu Thích</h4>
                  <p class="text-muted small mb-0">Lưu lại những sản phẩm bạn quan tâm để mua sắm sau.</p>
                </div>
                <!-- ĐÃ FIX: Dùng class badge-urban-soft chuyên dụng -->
                <span class="badge badge-urban-soft px-3 py-2 fs-6 shadow-sm">
                  {{ wishlists.length }} Sản phẩm
                </span>
              </div>

              <!-- Trạng thái trống -->
              <div v-if="wishlists.length === 0" class="text-center py-5 my-3">
                <div class="bg-light dark:bg-[#212529] rounded-circle d-inline-flex justify-content-center align-items-center mb-3 shadow-sm" style="width: 100px; height: 100px;">
                  <i class="bi bi-heart text-muted opacity-50" style="font-size: 3rem;"></i>
                </div>
                <h5 class="fw-bold text-dark dark:text-white">Danh sách yêu thích trống</h5>
                <p class="text-muted mb-4">Bạn chưa lưu sản phẩm nào vào danh sách yêu thích.</p>
                <router-link to="/category" class="btn btn-urban text-white rounded-pill px-4 py-2 fw-bold shadow-sm hover-transform">
                  Khám phá ngay <i class="bi bi-arrow-right ms-1"></i>
                </router-link>
              </div>

              <!-- Lưới sản phẩm -->
              <div v-else class="row g-4">
                <div class="col-md-4 col-sm-6" v-for="product in wishlists" :key="product.id">
                  <div class="hover-transform h-100">
                    <!-- ĐÃ FIX: Gỡ nút tim đỏ dư thừa, nối trực tiếp tính năng gỡ vào thẻ ProductCard -->
                    <ProductCard :product="product" @wishlist="removeFromWishlist(product.id)" />
                  </div>
                </div>
              </div>

              <!-- Phân trang (Nếu nhiều) -->
              <div class="text-center mt-5" v-if="wishlists.length > 9">
                 <button class="btn btn-outline-dark rounded-pill px-4 fw-bold text-uppercase tracking-widest shadow-sm hover-bg-dark transition-all">
                    Tải thêm sản phẩm
                 </button>
              </div>

            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import Swal from 'sweetalert2';
import UserSidebar from '@/components/client/UserSidebar.vue';
import ProductCard from '@/components/client/ProductCard.vue';

// Mock Data
const wishlists = ref([
  { id: 101, name: 'Sơ Mi Tay Dài Nam Đen Siêu Co Giãn', price: 549000, old_price: null, image: 'https://images.unsplash.com/photo-1596755094514-f87e32f85e23?q=80&w=400', colors: [{hex: '#2c3e50'}, {hex: '#8e44ad'}] },
  { id: 102, name: 'Áo Sơ Mi Nam Cộc Tay Cafe Túi Ngực', price: 469000, old_price: 650000, discount_percent: 28, image: 'https://images.unsplash.com/photo-1602810318383-e386cc2a3ccf?q=80&w=400', colors: [{hex: '#bdc3c7'}, {hex: '#3498db'}] },
  { id: 103, name: 'Áo Phông Nam Basic Cotton', price: 149000, old_price: 299000, discount_percent: 50, image: 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?q=80&w=400', colors: [{hex: '#ffffff'}, {hex: '#000000'}] }
]);

const removeFromWishlist = (id) => {
  Swal.fire({
    title: 'Bỏ yêu thích?',
    text: "Sản phẩm sẽ bị xóa khỏi danh sách của bạn.",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc3545',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Đồng ý xóa'
  }).then((result) => {
    if (result.isConfirmed) {
      wishlists.value = wishlists.value.filter(p => p.id !== id);
      Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Đã xóa', showConfirmButton: false, timer: 1500 });
    }
  });
};

onMounted(() => { window.scrollTo(0, 0); });
</script>

<style scoped>
.user-wishlist-wrapper { width: 100%; }

.zyro-container { width: 100%; max-width: 1310px; margin: 0 auto; padding-left: 20px; padding-right: 20px; }
@media (min-width: 1400px) { .zyro-container { padding-left: 0; padding-right: 0; } }

.text-c-dark { color: var(--color-c-dark, #213448) !important; }
html.dark .text-c-dark { color: #f8f9fa !important; }

/* CSS FIX: Class riêng biệt tránh xung đột opacity */
.badge-urban-soft {
  background-color: rgba(84, 119, 146, 0.1) !important;
  color: var(--color-c-hover, #547792) !important;
  border: 1px solid rgba(84, 119, 146, 0.3) !important;
}
html.dark .badge-urban-soft {
  background-color: rgba(255, 255, 255, 0.1) !important;
  color: #fff !important;
  border-color: rgba(255, 255, 255, 0.2) !important;
}

.btn-urban { background-color: var(--color-c-hover, #547792); border: none; transition: 0.2s; }
.btn-urban:hover { background-color: var(--color-c-dark, #213448); }

.hover-text-dark:hover { color: #000 !important; }
html.dark .hover-text-dark:hover { color: #fff !important; }

.hover-transform { transition: transform 0.2s ease, box-shadow 0.2s ease; }
.hover-transform:hover { transform: translateY(-3px); }
.hover-bg-dark:hover { background-color: #212529 !important; color: #fff !important; border-color: #212529 !important; }

.tracking-widest { letter-spacing: 2px; }
.animation-fade-in { animation: fadeIn 0.4s ease-in-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
</style>