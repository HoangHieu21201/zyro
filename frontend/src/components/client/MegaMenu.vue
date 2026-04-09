<!-- File: frontend/src/components/client/MegaMenu.vue -->
<template>
  <div class="mega-menu-wrapper">
    <!-- Lớp phủ tối màn hình -->
    <transition name="fade">
      <div v-if="isOpen" class="mega-menu-backdrop" @click="closeMenu"></div>
    </transition>

    <!-- KHUNG MEGA MENU DẠNG POPUP NỔI -->
    <transition name="mega-slide">
      <div v-if="isOpen" class="mega-menu-panel shadow-lg rounded-4 bg-white dark:bg-[#1a2533] d-flex flex-column">
        
        <!-- NỘI DUNG CUỘN ĐƯỢC -->
        <div class="mega-menu-content custom-scrollbar-y px-4 px-lg-5 pt-4 pb-2 flex-grow-1">
          
          <!-- ========================================== -->
          <!-- SECTION 1: BỘ SƯU TẬP (AUTO SWIPER LOGIC)  -->
          <!-- ========================================== -->
          <div class="mb-5 pb-2 border-bottom dark:border-gray-700">
            <h6 class="text-center fw-bold text-dark dark:text-white mb-4 text-uppercase tracking-widest">BỘ SƯU TẬP</h6>
            
            <div class="lookbook-auto-grid custom-scrollbar-x pb-4" 
                 :class="mockLookbooks.length <= 4 ? 'is-centered' : 'is-swipeable'">
              
              <div v-for="lb in mockLookbooks" :key="lb.id" class="lookbook-card flex-shrink-0 cursor-pointer group" @click="closeMenu">
                <div class="position-relative rounded-4 overflow-hidden mb-3" style="aspect-ratio: 16/9; width: 100%;">
                  <img :src="lb.image" class="w-100 h-100 object-fit-cover transition-transform group-hover-scale">
                  <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark opacity-25 transition-opacity group-hover-opacity-50"></div>
                  
                  <div class="position-absolute bottom-0 start-0 w-100 p-3 bg-gradient-dark-bottom">
                    <h6 class="fw-bold mb-1 text-white text-truncate">{{ lb.title }}</h6>
                  </div>
                </div>
                <div class="text-muted dark:text-gray-400 small text-truncate text-center px-2">{{ lb.subtitle }}</div>
              </div>
            </div>
          </div>

          <!-- ========================================== -->
          <!-- SECTION 2: DANH MỤC (FLEX GRID THÔNG MINH) -->
          <!-- ========================================== -->
          <div class="pb-3">
            <div class="category-smart-grid">
              
              <!-- Cột Danh mục cha -->
              <div class="category-smart-col" v-for="parent in mockCategories" :key="parent.id">
                
                <!-- ĐÃ FIX: BỌC ROUTER-LINK VÀO TIÊU ĐỀ DANH MỤC -->
                <router-link to="/category" class="text-decoration-none d-inline-block mb-3" @click="closeMenu">
                  <h6 class="fw-bold text-dark dark:text-white m-0 text-uppercase tracking-widest hover-text-urban transition-color">
                    {{ parent.name }}
                  </h6>
                </router-link>
                
                <div class="d-flex flex-column gap-1">
                  <!-- Danh mục con & Accordion -->
                  <div v-for="child in parent.children" :key="child.id">
                    
                    <div class="d-flex align-items-center justify-content-between py-2 rounded cursor-pointer transition-all hover-bg-light dark-hover-bg group pe-2" 
                         @click="toggleCategory(child)">
                      <div class="d-flex align-items-center gap-3">
                        <img :src="child.image" class="rounded-circle object-fit-cover shadow-sm bg-light" style="width: 36px; height: 36px;">
                        <span class="fw-medium text-dark dark:text-gray-200 group-hover-text-urban transition-color" style="font-size: 0.9rem;">{{ child.name }}</span>
                      </div>
                      <i class="bi text-muted transition-transform" :class="child.isOpen ? 'bi-chevron-up' : 'bi-chevron-down'" style="font-size: 0.8rem;"></i>
                    </div>

                    <!-- Sản phẩm thả xuống (Category Level 3) -->
                    <div v-show="child.isOpen" class="ps-5 py-2 accordion-content animation-fade-down">
                      <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
                        <li v-for="prod in child.products" :key="prod">
                          <!-- ĐÃ BỔ SUNG LINK CHO CÁC SẢN PHẨM THẢ XUỐNG -->
                          <router-link to="/category" class="text-muted dark:text-gray-400 text-decoration-none small hover-text-urban transition-all" @click="closeMenu">
                            {{ prod }}
                          </router-link>
                        </li>
                      </ul>
                    </div>
                  </div>
                </div>
              </div>

            </div>
          </div>

        </div>

        <!-- NÚT ĐÓNG DƯỚI CÙNG -->
        <div class="mega-menu-footer text-center pb-4 pt-2 bg-white dark:bg-[#1a2533] rounded-bottom-4 shadow-sm-top z-index-1">
          <button class="btn btn-light dark:bg-[#2b3035] dark:text-gray-300 dark:border-gray-600 shadow-sm border rounded-pill px-5 py-2 fw-bold hover-bg-effect transition-all" @click="closeMenu">
            <i class="bi bi-x-lg me-1"></i> Đóng
          </button>
        </div>

      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  offsetTop: {
    type: Number,
    default: 90
  }
});

const emit = defineEmits(['close']);

const closeMenu = () => {
  emit('close');
};

// Khóa cuộn trang khi mở menu
watch(() => props.isOpen, (val) => {
  if (val) {
    document.body.style.overflow = 'hidden';
  } else {
    document.body.style.overflow = '';
  }
});

// ========================================================
// MOCK DATA
// ========================================================
const mockLookbooks = ref([
  { id: 1, title: 'PEACEFUL SUMMER', subtitle: 'Khoác thanh lịch - Chạm bình yên', image: 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?q=80&w=600&auto=format&fit=crop' },
  { id: 2, title: 'QUẦN JEANS', subtitle: 'X2 Tôn dáng - Tự tin chuyển động', image: 'https://images.unsplash.com/photo-1542272604-787c3835535d?q=80&w=600&auto=format&fit=crop' },
  { id: 3, title: 'SMART.COOL', subtitle: 'Tự do trong từng chuyển động', image: 'https://images.unsplash.com/photo-1556821840-3a63f95609a7?q=80&w=600&auto=format&fit=crop' },
  { id: 4, title: 'BST ÁO GIÓ', subtitle: 'Cản gió - Trượt nước', image: 'https://images.unsplash.com/photo-1551028719-00167b16eac5?q=80&w=600&auto=format&fit=crop' }
]);

const mockCategories = ref([
  {
    id: 1, name: 'NAM',
    children: [
      { id: 11, name: 'Áo khoác', image: 'https://images.unsplash.com/photo-1591047139829-d91aecb6caea?q=80&w=150&auto=format&fit=crop', isOpen: false, products: ['Áo chống nắng', 'Áo vest', 'Áo gió', 'Áo phao'] },
      { id: 12, name: 'Áo', image: 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?q=80&w=150&auto=format&fit=crop', isOpen: false, products: ['Áo thun nam', 'Áo sơ mi nam', 'Áo Polo'] },
      { id: 13, name: 'Quần', image: 'https://images.unsplash.com/photo-1542272604-787c3835535d?q=80&w=150&auto=format&fit=crop', isOpen: false, products: ['Quần Jeans', 'Quần Kaki', 'Quần Âu'] },
      { id: 14, name: 'Đồ thể thao', image: 'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?q=80&w=150&auto=format&fit=crop', isOpen: false, products: ['Bộ thể thao', 'Áo chạy bộ'] }
    ]
  },
  {
    id: 2, name: 'NỮ',
    children: [
      { id: 21, name: 'Áo khoác', image: 'https://images.unsplash.com/photo-1551028719-00167b16eac5?q=80&w=150&auto=format&fit=crop', isOpen: false, products: ['Áo gió nữ', 'Áo khoác len'] },
      { id: 22, name: 'Áo', image: 'https://images.unsplash.com/photo-1434389673229-a178bcdaab30?q=80&w=150&auto=format&fit=crop', isOpen: false, products: ['Áo phông nữ', 'Áo sơ mi lụa'] },
      { id: 23, name: 'Quần', image: 'https://images.unsplash.com/photo-1541099649105-f69ad21f3246?q=80&w=150&auto=format&fit=crop', isOpen: false, products: ['Quần ống rộng', 'Quần Jeans nữ'] },
      { id: 24, name: 'Đồ mặc trong & Đồ lót', image: 'https://images.unsplash.com/photo-1618354691438-25bc04584c23?q=80&w=150&auto=format&fit=crop', isOpen: false, products: ['Đồ lót nữ', 'Váy lót'] },
    ]
  },
  {
    id: 3, name: 'TRẺ EM',
    children: [
      { id: 31, name: 'Áo khoác', image: 'https://images.unsplash.com/photo-1622290291468-a28f7a7dc6a8?q=80&w=150&auto=format&fit=crop', isOpen: false, products: ['Áo khoác bé trai', 'Áo khoác bé gái'] },
      { id: 32, name: 'Áo', image: 'https://images.unsplash.com/photo-1519278409-1f56fdda70db?q=80&w=150&auto=format&fit=crop', isOpen: false, products: ['Áo thun trẻ em', 'Áo nỉ'] },
      { id: 33, name: 'Quần', image: 'https://images.unsplash.com/photo-1594882645126-14020914d58d?q=80&w=150&auto=format&fit=crop', isOpen: false, products: ['Quần short', 'Quần dài'] },
      { id: 35, name: 'Sản phẩm khác', image: 'https://images.unsplash.com/photo-1560506840-0ca20786fb8a?q=80&w=150&auto=format&fit=crop', isOpen: false, products: ['Phụ kiện trẻ em'] },
    ]
  },
  {
    id: 4, name: 'GIÀY DÉP',
    children: [
      { id: 41, name: 'Xăng đan', image: 'https://images.unsplash.com/photo-1603487742131-4160ec999306?q=80&w=150&auto=format&fit=crop', isOpen: false, products: ['Xăng đan nam', 'Xăng đan nữ'] },
      { id: 42, name: 'Giày bệt', image: 'https://images.unsplash.com/photo-1543163521-1bf539c55dd2?q=80&w=150&auto=format&fit=crop', isOpen: false, products: ['Giày búp bê', 'Giày lười'] },
      { id: 43, name: 'Giày thể thao', image: 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?q=80&w=150&auto=format&fit=crop', isOpen: false, products: ['Giày chạy bộ', 'Sneakers'] },
      { id: 44, name: 'Dép xỏ ngón', image: 'https://images.unsplash.com/photo-1602144564887-e2be90e0ab11?q=80&w=150&auto=format&fit=crop', isOpen: false, products: ['Dép đi biển'] },
    ]
  }
]);

const toggleCategory = (child) => {
  child.isOpen = !child.isOpen;
};

// Đóng menu khi người dùng ấn nút Back trên trình duyệt
onMounted(() => {
  window.addEventListener('popstate', closeMenu);
});
</script>

<style scoped>
/* Màn phủ đen mờ */
.mega-menu-backdrop {
  position: fixed;
  top: 0; left: 0; width: 100vw; height: 100vh;
  background-color: rgba(0, 0, 0, 0.5);
  z-index: 1050;
}

/* KHUNG MODAL POPUP */
.mega-menu-panel {
  position: fixed;
  top: 20px; 
  left: 50%;
  transform: translateX(-50%); 
  width: 96%; 
  max-width: 1300px; 
  max-height: calc(100vh - 40px); 
  z-index: 1060; 
  overflow: hidden; 
  border: 1px solid rgba(0,0,0,0.08);
}
html.dark .mega-menu-panel { border: 1px solid rgba(255,255,255,0.05); }

/* NỘI DUNG CUỘN ĐƯỢC */
.mega-menu-content { overflow-y: auto; }

/* ========================================================== */
/* 1. THUẬT TOÁN CSS LOOKBOOK SWIPER (AUTO CĂN LỀ/VUỐT)       */
/* ========================================================== */
.lookbook-auto-grid {
  display: flex;
  gap: 1.5rem;
  scroll-snap-type: x mandatory;
  scroll-behavior: smooth;
  -webkit-overflow-scrolling: touch;
}

.lookbook-auto-grid.is-centered { justify-content: center; }
.lookbook-auto-grid.is-swipeable { justify-content: flex-start; }

.lookbook-card {
  width: calc(25% - 1.2rem); 
  min-width: 220px; 
  max-width: 280px; 
  scroll-snap-align: start; 
}

/* ========================================================== */
/* 2. THUẬT TOÁN CSS CATEGORY FLEX (CHỮA BỆNH OCD BẤT ĐỐI XỨNG)*/
/* ========================================================== */
.category-smart-grid {
  display: flex;
  flex-wrap: wrap;
  justify-content: center; 
  gap: 2rem;
}

.category-smart-col {
  flex: 1; 
  min-width: 220px; 
  max-width: 280px; 
}

/* Tiện ích đồ họa thêm */
.bg-gradient-dark-bottom {
  background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0) 100%);
}
.shadow-sm-top { box-shadow: 0 -4px 10px rgba(0,0,0,0.03); }
.tracking-widest { letter-spacing: 2px; }

/* Lookbook Card Hover */
.group-hover-scale { transition: transform 0.5s ease; }
.group:hover .group-hover-scale { transform: scale(1.08); }
.group-hover-opacity-50 { transition: opacity 0.3s ease; }
.group:hover .group-hover-opacity-50 { opacity: 0.1; }

/* Danh mục tương tác mượt mà */
.hover-bg-light:hover { background-color: rgba(84, 119, 146, 0.05); }
html.dark .dark-hover-bg:hover { background-color: rgba(255, 255, 255, 0.05); }
.hover-text-urban:hover { color: var(--color-c-hover, #547792) !important; text-decoration: underline !important; }
.group:hover .group-hover-text-urban { color: var(--color-c-hover, #547792) !important; }

.hover-bg-effect:hover { background-color: var(--color-c-effect, #EBF1F5); color: var(--color-c-hover, #547792) !important; }
html.dark .hover-bg-effect:hover { background-color: #343a40 !important; color: #fff !important; }

/* Animation Accordion */
.animation-fade-down { animation: fadeDown 0.3s ease forwards; }
@keyframes fadeDown {
  from { opacity: 0; transform: translateY(-5px); }
  to { opacity: 1; transform: translateY(0); }
}

/* Scrollbar Style cho Popup */
.custom-scrollbar-y::-webkit-scrollbar { width: 6px; }
.custom-scrollbar-y::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar-y::-webkit-scrollbar-thumb { background: var(--color-c-light, #94B4C1); border-radius: 10px; }
.custom-scrollbar-x::-webkit-scrollbar { height: 0px; display: none; } 

/* Transitions Vue: Thay đổi animation để nó bật ra từ giữa */
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.mega-slide-enter-active, .mega-slide-leave-active { transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); }
.mega-slide-enter-from, .mega-slide-leave-to { transform: translate(-50%, -20px) scale(0.98); opacity: 0; }

.transition-color { transition: color 0.2s ease; }
.transition-transform { transition: transform 0.3s ease; }
</style>