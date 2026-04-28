<template>
  <div class="mega-menu-wrapper">
    <transition name="fade">
      <div v-if="isOpen" class="mega-menu-backdrop" @click="closeMenu"></div>
    </transition>

    <transition name="mega-slide">
      <div v-if="isOpen" class="mega-menu-panel shadow-lg rounded-4 bg-white dark:bg-[#1a2533] d-flex flex-column">
        
        <div class="mega-menu-content custom-scrollbar-y px-4 px-lg-5 pt-4 pb-2 flex-grow-1">
          
          <!-- ============================================== -->
          <!-- SKELETON LOADING (HIỂN THỊ KHI ĐANG TẢI DATA)  -->
          <!-- ============================================== -->
          <div v-if="!categories || categories.length === 0" class="w-100 pe-none animation-fade-in">
             <!-- Skeleton Lookbook -->
             <div class="mb-5 pb-2 border-bottom dark:border-gray-700">
                <div class="shimmer mx-auto mb-4 rounded-pill" style="width: 180px; height: 20px;"></div>
                <div class="lookbook-auto-grid is-centered px-4">
                   <div v-for="i in 4" :key="'skel-lb-'+i" class="lookbook-card flex-shrink-0">
                      <div class="shimmer rounded-4 mb-3 w-100 shadow-sm" style="aspect-ratio: 16/9;"></div>
                      <div class="shimmer rounded-pill mx-auto" style="width: 70%; height: 14px;"></div>
                   </div>
                </div>
             </div>
             
             <!-- Skeleton Danh mục -->
             <div class="category-smart-grid px-2 px-md-4">
                <div v-for="i in 3" :key="'skel-cat-'+i" class="category-smart-col">
                   <div class="shimmer rounded-pill mb-4" style="width: 120px; height: 26px;"></div>
                   <div class="d-flex flex-column gap-4">
                      <div v-for="j in 4" :key="'skel-sub-'+j" class="d-flex align-items-center justify-content-between">
                         <div class="d-flex align-items-center gap-3 w-100">
                           <div class="shimmer rounded-circle shadow-sm flex-shrink-0" style="width: 40px; height: 40px;"></div>
                           <div class="shimmer rounded-pill" style="width: 60%; height: 18px;"></div>
                         </div>
                         <div class="shimmer rounded-circle flex-shrink-0" style="width: 15px; height: 15px;"></div>
                      </div>
                   </div>
                </div>
             </div>
          </div>

          <!-- ============================================== -->
          <!-- NỘI DUNG CHÍNH (KHI CÓ DATA)                   -->
          <!-- ============================================== -->
          <div v-else class="animation-fade-in">
            <!-- KHU VỰC BỘ SƯU TẬP (LOOKBOOK) -->
            <div v-if="lookbooks && lookbooks.length > 0" class="mb-5 pb-2 border-bottom dark:border-gray-700">
              <h6 class="text-center fw-bold text-dark dark:text-white mb-4 text-uppercase tracking-widest font-sans-vn">BỘ SƯU TẬP</h6>
              
              <div class="lookbook-auto-grid custom-scrollbar-x pb-4" 
                  :class="lookbooks.length <= 4 ? 'is-centered' : 'is-swipeable'">
                
                <!-- Click vào ảnh là điều hướng đến chi tiết Lookbook -->
                <router-link :to="`/lookbook/${lb.slug}`" v-for="lb in lookbooks" :key="lb.id" class="lookbook-card flex-shrink-0 cursor-pointer group text-decoration-none" @click="closeMenu">
                  <div class="position-relative rounded-4 overflow-hidden mb-3 shadow-sm" style="aspect-ratio: 16/9; width: 100%;">
                    <img :src="lb.main_image || '/client_placeholder.png'" class="w-100 h-100 object-fit-cover transition-transform group-hover-scale" @error="e => e.target.src='/client_placeholder.png'">
                    <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark opacity-25 transition-opacity group-hover-opacity-50"></div>
                    
                    <div class="position-absolute bottom-0 start-0 w-100 p-3 bg-gradient-dark-bottom">
                      <h6 class="fw-bold mb-1 text-white text-truncate font-sans-vn" style="font-size: 0.95rem;">{{ lb.name }}</h6>
                    </div>
                  </div>
                  <div class="text-muted dark:text-gray-400 small text-truncate text-center px-2 font-sans-vn">{{ lb.description || 'Khám phá ngay' }}</div>
                </router-link>

              </div>
            </div>

            <!-- KHU VỰC DANH MỤC 3 CỘT TỐI ƯU -->
            <div class="pb-3">
              <div class="category-smart-grid">
                
                <div class="category-smart-col" v-for="parent in categories" :key="parent.id">
                  
                  <router-link :to="`/category/${parent.slug}`" class="text-decoration-none d-inline-block mb-4" @click="closeMenu">
                    <h5 class="fw-bold text-dark dark:text-white m-0 text-uppercase tracking-widest hover-text-urban transition-color font-sans-vn">
                      {{ parent.name }}
                    </h5>
                  </router-link>
                  
                  <div class="d-flex flex-column gap-2 w-100 overflow-hidden">
                    <!-- CHỈ HIỂN THỊ TỐI ĐA 5 DANH MỤC ĐẦU TIÊN, PHẦN CÒN LẠI SẼ ẨN VÀO TRONG NÚT XEM THÊM -->
                    <template v-for="(child, idx) in parent.children" :key="child.id">
                      <div v-show="idx < 5 || isParentExpanded(parent.id)">
                        
                        <!-- Header của Menu con -->
                        <div class="d-flex align-items-center justify-content-between py-2 px-2 rounded-3 cursor-pointer transition-all hover-bg-light dark-hover-bg group border border-transparent hover-border-subtle" 
                            @click="toggleCategory(child.id)">
                          <div class="d-flex align-items-center gap-3 overflow-hidden pe-2 w-100">
                            <img :src="child.image || '/client_placeholder.png'" class="rounded-circle object-fit-cover shadow-sm border border-light-subtle dark:border-gray-600 bg-white dark:bg-gray-800 flex-shrink-0" style="width: 42px; height: 42px;" @error="e => e.target.src='/client_placeholder.png'">
                            <!-- TEXT-TRUNCATE CHO TÊN DANH MỤC NẾU QUÁ DÀI -->
                            <span class="fw-bold text-dark dark:text-gray-200 group-hover-text-urban transition-color font-sans-vn text-truncate" style="font-size: 0.95rem;" :title="child.name">{{ child.name }}</span>
                          </div>
                          <i class="bi text-muted transition-transform flex-shrink-0" :class="isCategoryOpen(child.id) ? 'bi-chevron-up' : 'bi-chevron-down'" style="font-size: 0.85rem;"></i>
                        </div>

                        <!-- Danh sách sản phẩm thả xuống -->
                        <div v-show="isCategoryOpen(child.id)" class="ps-5 ms-3 py-2 accordion-content animation-fade-down font-sans-vn overflow-hidden w-100">
                          <ul class="list-unstyled mb-0 d-flex flex-column gap-2 border-start border-2 border-light-subtle dark:border-gray-700 ps-3 py-1 w-100">
                            
                            <!-- Luôn hiển thị 5 sản phẩm đầu tiên (KÈM TRUNCATE) -->
                            <li v-for="(prodName, pIdx) in (child.products || []).slice(0, 5)" :key="pIdx" class="w-100">
                              <router-link :to="`/category/${child.slug}?search=${prodName}`" 
                                           class="text-muted dark:text-gray-400 text-decoration-none hover-text-urban transition-all fw-medium d-inline-block text-truncate w-100" 
                                           style="font-size: 0.85rem;" 
                                           :title="prodName"
                                           @click="closeMenu">
                                {{ prodName }}
                              </router-link>
                            </li>
                            
                            <!-- Hiển thị phần còn lại NẾU người dùng bấm mở rộng (KÈM TRUNCATE) -->
                            <template v-if="isSubcatExpanded(child.id)">
                               <li v-for="(prodName, pIdx) in (child.products || []).slice(5)" :key="'exp-'+pIdx" class="animation-fade-down w-100">
                                 <router-link :to="`/category/${child.slug}?search=${prodName}`" 
                                              class="text-muted dark:text-gray-400 text-decoration-none hover-text-urban transition-all fw-medium d-inline-block text-truncate w-100" 
                                              style="font-size: 0.85rem;" 
                                              :title="prodName"
                                              @click="closeMenu">
                                   {{ prodName }}
                                 </router-link>
                               </li>
                            </template>

                            <!-- Nút Xem thêm / Thu gọn (Chỉ hiện khi mảng có hơn 5 sản phẩm) -->
                            <li v-if="child.products && child.products.length > 5" class="mt-1">
                               <span class="text-urban small fw-bold cursor-pointer transition-color hover-text-dark font-sans-vn bg-light dark:bg-[#212529] px-3 py-1.5 rounded-pill shadow-sm d-inline-block" @click.stop="toggleSubcatExpand(child.id)">
                                 <i class="bi" :class="isSubcatExpanded(child.id) ? 'bi-dash' : 'bi-plus'"></i>
                                 {{ isSubcatExpanded(child.id) ? 'Thu gọn' : `Xem thêm ${child.products.length - 5} sản phẩm` }}
                               </span>
                            </li>
                            
                            <!-- Placeholder nếu chưa có sản phẩm -->
                            <li v-if="!child.products || child.products.length === 0">
                               <span class="text-muted small fst-italic">Đang cập nhật sản phẩm...</span>
                            </li>

                          </ul>
                        </div>
                      </div>
                    </template>

                    <!-- NÚT XEM THÊM DANH MỤC NẾU CỘT CÓ QUÁ 5 DANH MỤC -->
                    <div v-if="parent.children && parent.children.length > 5" class="mt-2 text-center w-100 px-2 animation-fade-in">
                       <div class="cursor-pointer py-2 rounded-3 transition-all hover-bg-light dark-hover-bg border border-dashed border-secondary border-opacity-25" @click.stop="toggleParentExpand(parent.id)">
                         <span class="text-urban small fw-bold font-sans-vn">
                           <i class="bi" :class="isParentExpanded(parent.id) ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                           {{ isParentExpanded(parent.id) ? 'Thu gọn bớt' : `Xem thêm ${parent.children.length - 5} danh mục` }}
                         </span>
                       </div>
                    </div>

                  </div>
                </div>

              </div>
            </div>
          </div>

        </div>

        <!-- FOOTER NÚT ĐÓNG MEGA MENU -->
        <div class="mega-menu-footer text-center pb-4 pt-3 bg-white dark:bg-[#1a2533] rounded-bottom-4 shadow-sm-top z-index-1">
          <button class="btn btn-light bg-light dark:bg-[#2b3035] text-dark dark:text-gray-300 dark:border-gray-600 shadow-sm border border-light-subtle rounded-pill px-5 py-2.5 fw-bold hover-bg-effect transition-all font-sans-vn" @click="closeMenu">
            <i class="bi bi-x-lg me-1"></i> Đóng Menu
          </button>
        </div>

      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';

const props = defineProps({
  isOpen: { type: Boolean, default: false },
  offsetTop: { type: Number, default: 90 },
  categories: { type: Array, default: () => [] },
  lookbooks: { type: Array, default: () => [] }
});

const emit = defineEmits(['close']);

const closeMenu = () => {
  emit('close');
};

watch(() => props.isOpen, (val) => {
  if (val) document.body.style.overflow = 'hidden';
  else document.body.style.overflow = '';
});

// Quản lý Mở/Đóng Accordion danh mục con (Trổ xuống hiển thị Sản phẩm)
const openCategoryIds = ref([]);
const toggleCategory = (id) => {
  const index = openCategoryIds.value.indexOf(id);
  if (index > -1) {
    openCategoryIds.value.splice(index, 1);
  } else {
    openCategoryIds.value.push(id);
  }
};
const isCategoryOpen = (id) => openCategoryIds.value.includes(id);

// Quản lý Mở/Đóng nút "Xem thêm" Sản phẩm (Giới hạn 5)
const expandedSubcats = ref([]);
const toggleSubcatExpand = (childId) => {
  if (expandedSubcats.value.includes(childId)) {
      expandedSubcats.value = expandedSubcats.value.filter(id => id !== childId);
  } else {
      expandedSubcats.value.push(childId);
  }
};
const isSubcatExpanded = (childId) => expandedSubcats.value.includes(childId);

// ĐÃ THÊM: Quản lý Mở/Đóng nút "Xem thêm" Danh mục con (Giới hạn 5)
const expandedParents = ref([]);
const toggleParentExpand = (parentId) => {
  if (expandedParents.value.includes(parentId)) {
      expandedParents.value = expandedParents.value.filter(id => id !== parentId);
  } else {
      expandedParents.value.push(parentId);
  }
};
const isParentExpanded = (parentId) => expandedParents.value.includes(parentId);

onMounted(() => {
  window.addEventListener('popstate', closeMenu);
});
</script>

<style scoped>
.font-sans-vn { font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', sans-serif !important; }

.mega-menu-backdrop {
  position: fixed;
  top: 0; left: 0; width: 100vw; height: 100vh;
  background-color: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(3px);
  z-index: 1050;
}

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

.mega-menu-content { overflow-y: auto; overflow-x: hidden; }

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

.category-smart-grid {
  display: flex;
  flex-wrap: wrap;
  justify-content: center; 
  gap: 3rem;
}

.category-smart-col {
  flex: 1; 
  min-width: 240px; 
  max-width: 320px; 
  overflow: hidden;
}

.bg-gradient-dark-bottom {
  background: linear-gradient(to top, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0) 100%);
}
.shadow-sm-top { box-shadow: 0 -4px 15px rgba(0,0,0,0.04); }
.tracking-widest { letter-spacing: 2px; }

.group-hover-scale { transition: transform 0.5s ease; }
.group:hover .group-hover-scale { transform: scale(1.08); }
.group-hover-opacity-50 { transition: opacity 0.3s ease; }
.group:hover .group-hover-opacity-50 { opacity: 0.1; }

.border-transparent { border-color: transparent !important; }
.border-dashed { border-style: dashed !important; border-width: 1.5px !important; }
.hover-border-subtle:hover { border-color: #dee2e6 !important; }
html.dark .hover-border-subtle:hover { border-color: #373b3e !important; }

.hover-bg-light:hover { background-color: rgba(84, 119, 146, 0.05); }
html.dark .dark-hover-bg:hover { background-color: rgba(255, 255, 255, 0.03); }

.text-urban { color: var(--color-c-hover, #547792) !important; }
.hover-text-urban:hover { color: var(--color-c-hover, #547792) !important; text-decoration: underline !important; }
.group:hover .group-hover-text-urban { color: var(--color-c-hover, #547792) !important; }

.hover-bg-effect:hover { background-color: var(--color-c-effect, #EBF1F5) !important; color: var(--color-c-hover, #547792) !important; border-color: var(--color-c-hover, #547792) !important; }
html.dark .hover-bg-effect:hover { background-color: #343a40 !important; color: #fff !important; border-color: #495057 !important; }

/* SKELETON CSS */
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

.animation-fade-in { animation: fadeIn 0.4s ease-in-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

.animation-fade-down { animation: fadeDown 0.3s ease forwards; }
@keyframes fadeDown {
  from { opacity: 0; transform: translateY(-5px); }
  to { opacity: 1; transform: translateY(0); }
}

.custom-scrollbar-y::-webkit-scrollbar { width: 6px; }
.custom-scrollbar-y::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar-y::-webkit-scrollbar-thumb { background: var(--color-c-light, #94B4C1); border-radius: 10px; }
.custom-scrollbar-x::-webkit-scrollbar { height: 0px; display: none; } 

.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.mega-slide-enter-active, .mega-slide-leave-active { transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); }
.mega-slide-enter-from, .mega-slide-leave-to { transform: translate(-50%, -20px) scale(0.98); opacity: 0; }

.transition-color { transition: color 0.2s ease, background-color 0.2s ease; border-color: 0.2s ease; }
.transition-transform { transition: transform 0.3s ease; }
</style>