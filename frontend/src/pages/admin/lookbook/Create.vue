<template>
  <div class="lookbook-create-wrapper pb-5 mb-5">
    
    <div v-if="isPageLoading" class="d-flex flex-column justify-content-center align-items-center w-100" style="min-height: 70vh;">
      <h1 class="logo-shimmer mb-3">ZYRO</h1>
      <p class="text-muted dark:text-gray-400 fw-semibold small text-uppercase tracking-widest" style="letter-spacing: 2px;">Đang chuẩn bị không gian làm việc...</p>
    </div>

    <div class="container-fluid py-4" v-else>
      
      <div class="row mb-4 align-items-center">
        <div class="col-md-8 d-flex align-items-center mb-3 mb-md-0">
          <router-link :to="{ name: 'admin-lookbooks' }" class="btn btn-light dark:bg-[#2b3035] dark:border-gray-600 dark:text-gray-200 shadow-sm me-3 rounded-circle d-flex align-items-center justify-content-center hover-urban transition-all" style="width: 40px; height: 40px;">
            <i class="bi bi-arrow-left fw-bold"></i>
          </router-link>
          <div class="d-flex flex-column">
            <h3 class="fw-bold text-dark dark:text-white mb-0">Tạo Bộ Sưu Tập Mới</h3>
            <p class="text-muted dark:text-gray-400 small mb-0 mt-1">Cấu hình Shoppable Image - Đính ghim sản phẩm lên ảnh mẫu</p>
          </div>
        </div>
        <div class="col-md-4 text-md-end">
          <button type="submit" form="lookbookForm" class="btn btn-urban text-white px-5 py-2.5 fw-bold shadow-sm rounded-pill w-100 w-md-auto" :disabled="isSaving">
            <span v-if="isSaving" class="spinner-border spinner-border-sm me-2"></span> 
            <i class="bi bi-floppy2-fill me-1" v-else></i> LƯU LOOKBOOK
          </button>
        </div>
      </div>

      <form id="lookbookForm" @submit.prevent="submitLookbook" autocomplete="off">
        
        <!-- TOP BAR: THÔNG TIN CƠ BẢN VÀ ĐỊNH GIÁ -->
        <div class="card border-0 shadow-sm rounded-4 dark:bg-[#1a2533] p-4 mb-4 animation-fade-in">
          <h6 class="fw-bold mb-3 text-urban text-uppercase"><i class="bi bi-info-circle me-2"></i>THÔNG TIN CHUNG</h6>
          
          <div class="row g-3 mb-4">
            <div class="col-xl-3 col-lg-6">
              <label class="form-label fw-bold text-dark dark:text-gray-200 small text-uppercase">Tên BST <span class="text-danger">*</span></label>
              <input type="text" class="form-control bg-light dark:bg-[#212529] dark:text-white border-0 shadow-sm-hover" v-model="form.name" @input="generateSlug" placeholder="VD: BST Mùa Thu 2026" required>
              <div class="text-danger small mt-1 fw-bold" v-if="errors.name">{{ errors.name[0] }}</div>
            </div>

            <div class="col-xl-3 col-lg-6">
              <label class="form-label fw-bold text-dark dark:text-gray-200 small text-uppercase">Đường dẫn (Slug)</label>
              <input type="text" class="form-control bg-light-subtle dark:bg-[#2b3035] text-muted dark:text-gray-400 font-monospace border-0" v-model="form.slug" readonly>
            </div>
            
            <div class="col-xl-2 col-lg-4">
              <label class="form-label fw-bold text-dark dark:text-gray-200 small text-uppercase">Dành cho</label>
              <select class="form-select bg-light dark:bg-[#212529] dark:text-white border-0 shadow-sm-hover" v-model="form.gender">
                <option value="Unisex">Unisex</option>
                <option value="Men">Nam</option>
                <option value="Women">Nữ</option>
                <option value="Kids">Trẻ em</option>
              </select>
            </div>

            <!-- TRƯỜNG USAGE LIMIT ĐƯỢC BỔ SUNG -->
            <div class="col-xl-2 col-lg-4">
              <label class="form-label fw-bold text-dark dark:text-gray-200 small text-uppercase">Giới hạn bán</label>
              <input type="number" class="form-control bg-light dark:bg-[#212529] dark:text-white border-0 shadow-sm-hover" v-model.number="form.usage_limit" placeholder="Vô hạn" min="0">
              <div class="text-danger small mt-1 fw-bold" v-if="errors.usage_limit">{{ errors.usage_limit[0] }}</div>
            </div>

            <div class="col-xl-2 col-lg-4">
              <label class="form-label fw-bold text-dark dark:text-gray-200 small text-uppercase mb-2 d-block">Xuất bản</label>
              <div class="d-flex align-items-center px-3 py-1 bg-light dark:bg-[#212529] border border-light-subtle dark:border-gray-700 rounded-3 shadow-sm h-100" style="min-height: 40px;">
                <div class="form-check form-switch mb-0 d-flex align-items-center gap-2 w-100 ps-0">
                  <input class="form-check-input fs-4 m-0 ms-1 cursor-pointer float-none" type="checkbox" role="switch" id="publishSwitch" v-model="form.isPublished">
                  <label class="form-check-label fw-bold text-urban m-0 cursor-pointer text-nowrap w-100 no-select" for="publishSwitch">Hiển thị</label>
                </div>
              </div>
            </div>
          </div>
          
          <!-- HÀNG 2: TÍNH GIÁ TỰ ĐỘNG -->
          <div class="p-3 bg-light dark:bg-[#212529] border dark:border-gray-700 rounded-3 mb-4">
            <h6 class="fw-bold text-urban mb-3"><i class="bi bi-calculator-fill me-2"></i>Tính giá Set đồ tự động (Up-sale)</h6>
            
            <div class="row g-3 align-items-start">
              <div class="col-xl-3 col-md-6">
                <label class="form-label small text-muted dark:text-gray-400 fw-bold text-uppercase">Tổng giá SP đã thêm</label>
                <div class="form-control bg-white dark:bg-[#1a2533] border-0 text-secondary fw-bold d-flex align-items-center shadow-sm" style="cursor: not-allowed; height: 39px;">
                  {{ formatCurrency(totalPinnedPrice) }}
                </div>
              </div>

              <div class="col-xl-3 col-md-6">
                <label class="form-label small text-muted dark:text-gray-400 fw-bold text-uppercase">Loại giảm giá Set</label>
                <select class="form-select border-0 shadow-sm-hover bg-white dark:bg-[#1a2533] dark:text-white fw-semibold" v-model="discountType" style="height: 39px;">
                  <option value="amount">Giảm tiền mặt (VNĐ)</option>
                  <option value="percent">Giảm phần trăm (%)</option>
                </select>
              </div>

              <div class="col-xl-3 col-md-6">
                <label class="form-label small text-muted dark:text-gray-400 fw-bold text-uppercase">Mức giảm</label>
                <input type="number" class="form-control border-0 shadow-sm-hover bg-white dark:bg-[#1a2533] dark:text-white fw-bold" v-model.number="discountValue" min="0" style="height: 39px;">
                <small class="text-urban fw-bold mt-1 d-block" v-if="discountType === 'amount' && discountValue > 0">- {{ formatCurrency(discountValue) }}</small>
                <small class="text-urban fw-bold mt-1 d-block" v-if="discountType === 'percent' && discountValue > 0">- {{ discountValue }} %</small>
              </div>

              <div class="col-xl-3 col-md-6">
                <label class="form-label small text-urban fw-bold text-uppercase">Giá Set Cuối Cùng <span class="text-danger">*</span></label>
                <div class="input-group shadow-sm-hover" style="height: 39px;">
                  <input type="number" class="form-control fw-bold text-urban border-0 bg-white dark:bg-[#1a2533]" v-model.number="form.total_price_estimate" min="0" required>
                  <span class="input-group-text bg-white dark:bg-[#1a2533] border-0 text-urban fw-bold">₫</span>
                </div>
                <small class="text-urban fw-bold mt-1 d-block" v-if="form.total_price_estimate > 0">{{ formatCurrency(form.total_price_estimate) }}</small>
              </div>
            </div>
          </div>

          <!-- HÀNG 3: MÔ TẢ -->
          <div>
             <label class="form-label fw-bold text-dark dark:text-gray-200 small text-uppercase">Mô tả ngắn (Tùy chọn)</label>
             <input type="text" class="form-control bg-light dark:bg-[#212529] dark:text-white border-0 shadow-sm-hover" v-model="form.description" placeholder="Vài dòng giới thiệu BST...">
          </div>
        </div>

        <!-- ========================================== -->
        <!-- KHÔNG GIAN LÀM VIỆC DƯỚI -->
        <!-- ========================================== -->
        <div class="row g-4">
          
          <!-- CỘT TRÁI: ẢNH NỀN -->
          <div class="col-xl-3 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 dark:bg-[#1a2533] h-100">
              <div class="card-body p-4 text-center d-flex flex-column">
                <h6 class="fw-bold mb-3 text-urban text-start border-bottom dark:border-gray-700 pb-2"><i class="bi bi-image me-2"></i>ẢNH MẪU (BẮT BUỘC) <span class="text-danger">*</span></h6>
                
                <div class="mb-3 position-relative border border-dashed border-2 dark:border-gray-600 rounded-4 overflow-hidden bg-light dark:bg-[#212529] d-flex align-items-center justify-content-center cursor-pointer hover-bg-effect transition-all flex-grow-1" 
                     style="min-height: 400px;" @click="$refs.mainImageInput.click()">
                  <img v-if="mainImagePreview" :src="mainImagePreview" class="w-100 h-100 object-fit-cover p-1 rounded-4">
                  <div v-else class="text-muted dark:text-gray-500 text-center p-3">
                    <i class="bi bi-cloud-arrow-up fs-1 mb-2 text-urban d-block"></i>
                    <span class="small fw-semibold">Click để chọn ảnh nền</span>
                  </div>
                </div>
                <input type="file" class="d-none" ref="mainImageInput" id="mainImageUpload" accept="image/*" @change="handleMainImageUpload">
                <div class="text-danger small fw-bold mb-2" v-if="errors.main_image">{{ errors.main_image[0] }}</div>
                <p class="text-muted small mb-0">Hỗ trợ JPG, PNG, WEBP. Tỷ lệ khuyên dùng: 3:4 hoặc 9:16.</p>
              </div>
            </div>
          </div>

          <!-- CỘT PHẢI: KHÔNG GIAN GHIM (WORKSPACE) -->
          <div class="col-xl-9 col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 dark:bg-[#1a2533] h-100 overflow-hidden d-flex flex-column">
              <div class="card-header bg-white dark:bg-[#1a2533] border-bottom dark:border-gray-700 py-3 px-4 d-flex justify-content-between align-items-center">
                 <div>
                   <h5 class="fw-bold text-dark dark:text-white mb-1"><i class="bi bi-pin-map-fill text-urban me-2"></i>Không gian Ghim (Workspace)</h5>
                   <p class="text-muted small mb-0">Có thể Click trực tiếp lên ảnh để đính ghim, kéo thả ghim, hoặc ấn "Thêm SP tự do" bên phải.</p>
                 </div>
              </div>
              
              <div class="card-body p-0 d-flex flex-column flex-xxl-row bg-c-effect dark:bg-[#121416]">
                
                <!-- CANVAS ẢNH -->
                <div class="flex-grow-1 p-4 d-flex align-items-center justify-content-center" style="min-height: 500px;">
                   <div v-if="!mainImagePreview" class="text-center text-muted opacity-50">
                     <i class="bi bi-ban fs-1 d-block mb-2"></i>
                     Vui lòng tải ảnh mẫu ở cột bên trái để bắt đầu ghim tọa độ.
                   </div>

                   <div v-else class="position-relative shadow-sm border border-2 border-white dark:border-gray-700 bg-white rounded-3 overflow-hidden" style="display: inline-block;" ref="imageCanvas">
                     <img :src="mainImagePreview" class="img-fluid cursor-crosshair pe-none" style="max-height: 75vh; object-fit: contain;">
                     
                     <div class="position-absolute top-0 start-0 w-100 h-100 cursor-crosshair z-0" @click="handleImageClick"></div>

                     <!-- ĐÃ NÂNG CẤP: DRAG & DROP PINS -->
                     <div v-for="(pin, index) in pins" :key="index"
                          class="position-absolute translate-middle pin-marker z-1"
                          :class="{ 'dragging': draggingPinIndex === index }"
                          :style="{ top: pin.y + '%', left: pin.x + '%' }"
                          @mousedown.stop.prevent="startDragPin(index, $event)"
                          @touchstart.stop="startDragPin(index, $event)"
                          title="Kéo thả để di chuyển, Click để sửa SP">
                        <div class="pin-pulse"></div>
                        <span class="badge bg-urban rounded-circle shadow border border-2 border-white position-relative d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; font-size: 0.85rem;">
                          {{ index + 1 }}
                        </span>
                     </div>
                   </div>
                </div>

                <!-- DANH SÁCH SẢN PHẨM ĐÃ THÊM -->
                <div class="bg-white dark:bg-[#1a2533] p-3 border-start dark:border-gray-700 d-flex flex-column" style="width: 100%; max-width: 360px;">
                   <div class="d-flex justify-content-between align-items-center border-bottom dark:border-gray-700 pb-2 mb-3">
                     <h6 class="fw-bold text-dark dark:text-white mb-0">Danh sách SP ({{ pins.length }})</h6>
                     <!-- Nút thêm sản phẩm tự do -->
                     <button type="button" class="btn btn-sm btn-outline-urban rounded-pill fw-bold shadow-sm" @click="openDirectAddModal">
                       <i class="bi bi-plus-lg"></i> Thêm Tự Do
                     </button>
                   </div>
                   
                   <div class="text-danger small fw-bold mb-3" v-if="errors.items_data">{{ errors.items_data[0] }}</div>
                   
                   <div class="custom-scrollbar-y flex-grow-1" style="overflow-y: auto; max-height: 70vh; padding-right: 4px;">
                     <div v-if="pins.length === 0" class="text-muted small fst-italic text-center py-5 bg-light dark:bg-[#212529] rounded-3 border border-dashed dark:border-gray-700 mt-2">
                       Chưa có sản phẩm nào.
                     </div>
                     
                     <div v-else class="d-flex flex-column gap-3 pb-2">
                       <div v-for="(pin, index) in pins" :key="index" class="p-2 border dark:border-gray-600 rounded-3 bg-light dark:bg-[#212529] position-relative transition-all hover-border-urban shadow-sm">
                         <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-urban rounded-pill px-2 shadow-sm">#{{ index + 1 }}</span>
                            <button type="button" class="btn-close btn-close-sm dark:filter-invert" @click.stop="removePin(index)"></button>
                         </div>
                         
                         <!-- Card Hiển thị SP -->
                         <div v-if="pin.product_id" class="d-flex align-items-center bg-white dark:bg-[#1a2533] p-2 border dark:border-gray-700 rounded shadow-sm cursor-pointer" @click="editPin(index)">
                            <!-- BẮT LỖI TỐI THƯỢNG: Thêm @error -->
                            <img :src="pin.product_image" class="rounded object-fit-cover me-2 border dark:border-gray-600" style="width: 45px; height: 45px;" @error="handleImageError">
                            <div class="overflow-hidden">
                              <div class="fw-bold text-dark dark:text-gray-200 small text-truncate" :title="pin.product_name">{{ pin.product_name }}</div>
                              <div class="text-urban fw-bold mt-1" style="font-size: 0.75rem;">{{ formatCurrency(pin.product_price) }}</div>
                            </div>
                         </div>
                         <div v-else class="bg-white dark:bg-[#1a2533] border border-danger border-dashed p-3 rounded text-center cursor-pointer hover-bg-effect" @click="editPin(index)">
                            <i class="bi bi-search text-danger fs-4 mb-1 d-block"></i>
                            <span class="small text-danger fw-semibold">Click để chọn Sản phẩm</span>
                         </div>
                       </div>
                     </div>
                   </div>
                </div>

              </div>
            </div>
          </div>

        </div>
      </form>
    </div>

    <!-- ======================================================== -->
    <!-- MODAL CHỌN SẢN PHẨM KHỔNG LỒ (FULL CỘT + LỌC)-->
    <!-- ======================================================== -->
    <div class="modal fade" id="productSearchModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
      <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content rounded-4 border-0 shadow dark:bg-[#1a2533]">
          <div class="modal-header border-bottom-0 pb-0">
            <h5 class="fw-bold text-dark dark:text-white">
              <i class="bi bi-box-seam text-urban me-2"></i>
              Chọn sản phẩm cho {{ activePinIndex !== null ? 'Ghim #' + (activePinIndex + 1) : 'Bộ Sưu Tập' }}
            </h5>
            <button type="button" class="btn-close dark:filter-invert" data-bs-dismiss="modal"></button>
          </div>
          
          <div class="modal-body p-4">
            <!-- BỘ LỌC ĐA CHIỀU TRONG MODAL -->
            <div class="row g-3 mb-4">
               <div class="col-lg-4">
                 <div class="input-group shadow-sm-hover">
                   <span class="input-group-text bg-white dark:bg-[#212529] border-end-0 text-muted"><i class="bi bi-search"></i></span>
                   <input type="text" class="form-control border-start-0 bg-white dark:bg-[#212529] dark:text-white dark:border-gray-700 shadow-none" v-model="modalSearchQuery" placeholder="Tên SP, mã SKU...">
                 </div>
               </div>
               
               <div class="col-lg-4">
                 <select class="form-select bg-white dark:bg-[#212529] dark:text-white dark:border-gray-700 shadow-sm-hover fw-medium" v-model="modalFilterCategory">
                   <option value="">-- Tất cả Danh mục --</option>
                   <option v-for="cat in hierarchicalCategories" :key="cat.id" :value="cat.id" :class="{'fw-bold text-dark dark:text-white': cat.level === 0}">
                     {{ cat.displayName }}
                   </option>
                 </select>
               </div>
               
               <div class="col-lg-4">
                 <select class="form-select bg-white dark:bg-[#212529] dark:text-white dark:border-gray-700 shadow-sm-hover fw-medium" v-model="modalFilterBrand">
                   <option value="">-- Tất cả Thương hiệu --</option>
                   <option v-for="brand in brands" :key="brand.id" :value="brand.id">{{ brand.name }}</option>
                 </select>
               </div>
            </div>

            <div class="text-center py-4" v-if="isPageLoading">
               <span class="spinner-border text-urban mb-2"></span><br>
               <span class="text-muted small">Đang tải dữ liệu...</span>
            </div>

            <div v-else class="custom-scrollbar-y pe-2 pb-2" style="height: 55vh; overflow-y: auto;">
               <div v-if="filteredProducts.length === 0" class="text-center text-muted fst-italic py-5">
                  <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>
                  Không tìm thấy sản phẩm nào phù hợp (Chỉ hiển thị SP đang Xuất bản).
               </div>
               <div class="row g-3">
                 <div class="col-lg-3 col-md-4 col-sm-6" v-for="prod in filteredProducts" :key="prod.id">
                   <div class="card border border-secondary-subtle dark:border-gray-700 rounded-4 h-100 cursor-pointer shadow-sm-hover product-select-card" 
                        :class="{'border-urban bg-urban bg-opacity-10': activePinData?.product_id === prod.id}"
                        @click="selectProductForPin(prod)">
                     <div class="card-body p-3 d-flex flex-column align-items-center text-center">
                        <!-- BẮT LỖI TỐI THƯỢNG: Thêm @error -->
                        <img :src="getImageUrl(prod.thumbnail_image)" class="rounded-3 border object-fit-cover mb-3 dark:border-gray-600 bg-white" style="width: 80px; height: 80px;" @error="handleImageError">
                        <div class="w-100">
                           <div class="fw-bold text-dark dark:text-white small text-truncate mb-1" :title="prod.name">{{ prod.name }}</div>
                           <div class="text-muted font-monospace mb-2" style="font-size: 0.7rem;">{{ prod.slug }}</div>
                           <div class="text-danger fw-bold fs-6">{{ formatCurrency(prod.base_price) }}</div>
                        </div>
                     </div>
                   </div>
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
import { ref, onMounted, computed, watch } from 'vue';
import { useRouter } from 'vue-router';
import Swal from 'sweetalert2';
import api from '@/utils/axios'; // Đã đổi sang api

const router = useRouter();
const isPageLoading = ref(true);
const isDataLoaded = ref(false); 
const isSaving = ref(false);
const errors = ref({});

// ĐÃ THÊM TRƯỜNG usage_limit
const form = ref({ name: '', slug: '', description: '', gender: 'Unisex', total_price_estimate: 0, isPublished: false, usage_limit: '' });
const mainImageFile = ref(null);
const mainImagePreview = ref(null);
const hasNewImage = ref(false); 
const imageCanvas = ref(null);

const pins = ref([]);

const categories = ref([]);
const brands = ref([]);

const activePinIndex = ref(null);
const activePinData = computed(() => activePinIndex.value !== null ? pins.value[activePinIndex.value] : null);
let productModalInstance = null;

const allPublishedProducts = ref([]);
const modalSearchQuery = ref('');
const modalFilterCategory = ref('');
const modalFilterBrand = ref('');

const discountType = ref('amount'); 
const discountValue = ref(0);

const totalPinnedPrice = computed(() => {
  return pins.value.reduce((sum, p) => sum + (Number(p.product_price) || 0), 0);
});

watch([pins, discountType, discountValue], () => {
  if (!isDataLoaded.value) return; 

  let total = totalPinnedPrice.value;
  let val = Number(discountValue.value) || 0;
  
  if (discountType.value === 'percent') {
      total = total - (total * val / 100);
  } else {
      total = total - val;
  }
  form.value.total_price_estimate = total > 0 ? Math.round(total) : 0;
}, { deep: true });

// --- LÀM SẠCH LOGIC XỬ LÝ ẢNH ---
const defaultImage = '/client_placeholder.png'; // Trỏ thẳng vào public/

const getImageUrl = (path) => {
  if (!path || String(path).trim() === '') return defaultImage;
  let cleanPath = String(path).trim();

  if (cleanPath.startsWith('http') || cleanPath.startsWith(defaultImage)) return cleanPath;
  
  // Dọn rác storage thừa
  cleanPath = cleanPath.replace(/^\/+/, '');
  if (cleanPath.startsWith('storage/')) {
      cleanPath = cleanPath.replace('storage/', '');
  }

  let baseUrl = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000/api/v1';
  baseUrl = baseUrl.replace('/api/v1', '');
  return `${baseUrl}/storage/${cleanPath}`;
};

const handleImageError = (e) => { 
  e.target.src = defaultImage; 
};
// -------------------------------

const formatCurrency = (val) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val || 0);

const generateSlug = () => {
  let s = form.value.name.toLowerCase();
  s = s.replace(/á|à|ả|ạ|ã|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ/gi, 'a');
  s = s.replace(/é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ/gi, 'e');
  s = s.replace(/i|í|ì|ỉ|ĩ|ị/gi, 'i');
  s = s.replace(/ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ/gi, 'o');
  s = s.replace(/ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự/gi, 'u');
  s = s.replace(/ý|ỳ|ỷ|ỹ|ỵ/gi, 'y');
  s = s.replace(/đ/gi, 'd');
  form.value.slug = s.replace(/\s+/g, '-').replace(/[^a-z0-9\-]/g, '').replace(/\-\-+/g, '-');
};

const handleMainImageUpload = (e) => {
  const f = e.target.files[0];
  if (f) {
    if (f.size > 5 * 1024 * 1024) { Swal.fire('Lỗi', 'Ảnh tối đa 5MB', 'error'); return; }
    mainImageFile.value = f;
    mainImagePreview.value = URL.createObjectURL(f);
    hasNewImage.value = true;
  }
};

// ========================================================
// LOGIC KÉO THẢ GHIM (DRAG & DROP PINS)
// ========================================================
const draggingPinIndex = ref(null);
let startDragX = 0;
let startDragY = 0;
let hasMoved = false;

const startDragPin = (index, event) => {
  draggingPinIndex.value = index;
  hasMoved = false;

  const clientX = event.clientX || (event.touches && event.touches[0].clientX);
  const clientY = event.clientY || (event.touches && event.touches[0].clientY);
  
  startDragX = clientX;
  startDragY = clientY;

  document.addEventListener('mousemove', onDragPin);
  document.addEventListener('mouseup', endDragPin);
  document.addEventListener('touchmove', onDragPin, { passive: false });
  document.addEventListener('touchend', endDragPin);
};

const onDragPin = (event) => {
  if (draggingPinIndex.value === null || !imageCanvas.value) return;

  const clientX = event.clientX || (event.touches && event.touches[0].clientX);
  const clientY = event.clientY || (event.touches && event.touches[0].clientY);

  if (Math.abs(clientX - startDragX) > 3 || Math.abs(clientY - startDragY) > 3) {
    hasMoved = true;
  }

  if (hasMoved) {
    if (event.type === 'touchmove') event.preventDefault(); 
    
    const rect = imageCanvas.value.getBoundingClientRect();
    let x = ((clientX - rect.left) / rect.width) * 100;
    let y = ((clientY - rect.top) / rect.height) * 100;

    x = Math.max(0, Math.min(100, x));
    y = Math.max(0, Math.min(100, y));

    pins.value[draggingPinIndex.value].x = x.toFixed(2);
    pins.value[draggingPinIndex.value].y = y.toFixed(2);
  }
};

const endDragPin = (event) => {
  if (draggingPinIndex.value !== null && !hasMoved) {
    editPin(draggingPinIndex.value);
  }

  draggingPinIndex.value = null;
  document.removeEventListener('mousemove', onDragPin);
  document.removeEventListener('mouseup', endDragPin);
  document.removeEventListener('touchmove', onDragPin);
  document.removeEventListener('touchend', endDragPin);
};

const handleImageClick = (e) => {
  if (!imageCanvas.value) return;
  const rect = imageCanvas.value.getBoundingClientRect();
  const x = ((e.clientX - rect.left) / rect.width) * 100;
  const y = ((e.clientY - rect.top) / rect.height) * 100;

  pins.value.push({ x: x.toFixed(2), y: y.toFixed(2), product_id: null, product_name: '', product_price: 0, product_image: null, sort_order: pins.value.length });
  editPin(pins.value.length - 1);
};

const removePin = (index) => { pins.value.splice(index, 1); };

const editPin = (index) => {
  activePinIndex.value = index;
  modalSearchQuery.value = ''; 
  modalFilterCategory.value = '';
  modalFilterBrand.value = '';
  if (!productModalInstance) productModalInstance = new window.bootstrap.Modal(document.getElementById('productSearchModal'));
  productModalInstance.show();
};

const openDirectAddModal = () => {
  activePinIndex.value = null; 
  modalSearchQuery.value = '';
  modalFilterCategory.value = '';
  modalFilterBrand.value = '';
  if (!productModalInstance) productModalInstance = new window.bootstrap.Modal(document.getElementById('productSearchModal'));
  productModalInstance.show();
};

const selectProductForPin = (prod) => {
  const isExist = pins.value.some((p, idx) => p.product_id === prod.id && idx !== activePinIndex.value);
  if (isExist) {
     Swal.fire({ toast:true, position: 'top-end', icon: 'warning', title: 'Sản phẩm này đã có trong set!', showConfirmButton: false, timer: 2000 });
     return;
  }

  if (activePinIndex.value !== null && pins.value[activePinIndex.value]) {
      pins.value[activePinIndex.value].product_id = prod.id;
      pins.value[activePinIndex.value].product_name = prod.name;
      pins.value[activePinIndex.value].product_price = prod.base_price;
      pins.value[activePinIndex.value].product_image = getImageUrl(prod.thumbnail_image);
  } else {
      pins.value.push({
          x: 50, y: 50,
          product_id: prod.id,
          product_name: prod.name,
          product_price: prod.base_price,
          product_image: getImageUrl(prod.thumbnail_image),
          sort_order: pins.value.length
      });
  }
  
  productModalInstance.hide();
};

const hierarchicalCategories = computed(() => {
  const buildTree = (parentId = null, level = 0) => {
    let res = [];
    const children = categories.value.filter(c => (c.parent_id || null) === (parentId || null));
    children.forEach(child => {
      res.push({
        ...child,
        displayName: (level > 0 ? '\u00A0\u00A0\u00A0\u00A0'.repeat(level) + '↳ ' : '') + child.name,
        level: level
      });
      res = res.concat(buildTree(child.id, level + 1));
    });
    return res;
  };
  return buildTree(null);
});

const getAllCategoryIds = (id) => {
  let ids = [id];
  const children = categories.value.filter(c => c.parent_id === id);
  children.forEach(child => {
    ids = ids.concat(getAllCategoryIds(child.id));
  });
  return ids;
};

const filteredProducts = computed(() => {
  let res = allPublishedProducts.value;

  if (modalSearchQuery.value) {
    const q = modalSearchQuery.value.toLowerCase();
    res = res.filter(p => p.name.toLowerCase().includes(q) || p.slug.toLowerCase().includes(q));
  }
  if (modalFilterCategory.value) {
    const targetCategoryIds = getAllCategoryIds(modalFilterCategory.value);
    res = res.filter(p => targetCategoryIds.includes(p.category_id));
  }
  if (modalFilterBrand.value) {
    res = res.filter(p => p.brand_id === modalFilterBrand.value);
  }
  
  return res.slice(0, 30); 
});

const fetchData = async () => {
  isPageLoading.value = true;
  try {
    const [resProds, resCats, resBrands] = await Promise.all([
      api.get('/admin/products?status=published'),
      api.get('/admin/categories'),
      api.get('/admin/brands')
    ]);

    const payload = resProds.data?.data;
    const rawProds = Array.isArray(payload?.data) ? payload.data : (Array.isArray(payload) ? payload : []);
    
    allPublishedProducts.value = rawProds.filter(p => p.status === 'published' && !p.deleted_at);

    categories.value = Array.isArray(resCats.data?.data) ? resCats.data.data.filter(c => !c.deleted_at && c.status === 'active') : [];
    brands.value = Array.isArray(resBrands.data?.data) ? resBrands.data.data.filter(b => !b.deleted_at && b.status === 'active') : [];

    isDataLoaded.value = true;
  } catch (err) {
    console.error('Lỗi tải ds sản phẩm', err);
  } finally { isPageLoading.value = false; }
};

const submitLookbook = async () => {
  if (!mainImageFile.value) { Swal.fire('Lỗi', 'Vui lòng tải lên bức Ảnh Mẫu để chứa sản phẩm.', 'warning'); return; }
  
  const validPins = pins.value.filter(p => p.product_id !== null);
  if (validPins.length === 0) { Swal.fire('Lỗi', 'Bộ sưu tập phải có ít nhất 1 sản phẩm được ghim.', 'warning'); return; }

  isSaving.value = true; errors.value = {};
  
  const formData = new FormData();
  formData.append('name', form.value.name);
  formData.append('slug', form.value.slug);
  formData.append('description', form.value.description);
  formData.append('gender', form.value.gender); 
  formData.append('total_price_estimate', form.value.total_price_estimate);
  formData.append('status', form.value.isPublished ? 'published' : 'draft');
  
  // ĐẨY GIỚI HẠN LÊN BACKEND NẾU CÓ
  if (form.value.usage_limit !== '' && form.value.usage_limit !== null && form.value.usage_limit !== undefined) {
      formData.append('usage_limit', form.value.usage_limit);
  }

  formData.append('main_image', mainImageFile.value);

  const itemsData = validPins.map((p, index) => ({
    product_id: p.product_id,
    pin_coordinates: { x: parseFloat(p.x), y: parseFloat(p.y) },
    sort_order: index
  }));
  formData.append('items_data', JSON.stringify(itemsData));

  try {
    const res = await api.post('/admin/lookbooks', formData, { headers: { 'Content-Type': 'multipart/form-data' } });
    Swal.fire({ icon: 'success', title: 'Tạo thành công', text: res.data.message, timer: 2000, showConfirmButton: false }).then(() => {
      router.push({ name: 'admin-lookbooks' });
    });
  } catch (e) {
    if (e.response && e.response.data && e.response.data.errors) {
       errors.value = e.response.data.errors;
       Swal.fire({ title: 'Dữ liệu không hợp lệ', text: 'Vui lòng kiểm tra các cảnh báo đỏ', icon: 'error' });
    } else { Swal.fire('Lỗi', e.response?.data?.message || 'Lỗi lưu Lookbook.', 'error'); }
  } finally { isSaving.value = false; }
};

onMounted(() => fetchData());
</script>

<style scoped>
.text-urban { color: var(--color-c-hover, #547792) !important; }
.bg-urban { background-color: var(--color-c-hover, #547792) !important; }
.border-urban { border-color: var(--color-c-hover, #547792) !important; }
.btn-urban { background-color: var(--color-c-hover, #547792); color: white; border: none; transition: 0.2s; }
.btn-urban:hover { background-color: var(--color-c-dark, #213448); color: white; transform: translateY(-2px); }

.bg-c-effect { background-color: var(--color-c-effect, #EBF1F5); }
.hover-bg-effect:hover { background-color: var(--color-c-effect, #EBF1F5) !important; }
.hover-border-urban:hover { border-color: var(--color-c-hover, #547792) !important; }

/* FIX CẤM BÔI ĐEN CHỮ */
.no-select { user-select: none !important; -webkit-user-select: none !important; -moz-user-select: none !important; }
.cursor-pointer { cursor: pointer; }
.cursor-crosshair { cursor: crosshair; }

.shadow-sm-hover { transition: box-shadow 0.2s ease, border-color 0.2s ease; }
.shadow-sm-hover:focus-within { box-shadow: 0 4px 15px rgba(84, 119, 146, 0.1) !important; }
.form-control:focus, .form-select:focus { border-color: var(--color-c-hover, #547792); box-shadow: none !important; }
.border-dashed { border-style: dashed !important; border-width: 2px !important; }

/* HIỆU ỨNG GHIM (PIN) CHUẨN MỰC MUA SẮM VÀ KÉO THẢ */
.pin-marker { z-index: 10; cursor: grab; transition: transform 0.2s; }
.pin-marker:active, .pin-marker.dragging { cursor: grabbing; transition: none; transform: scale(1.15) translate(-50%, -50%); }
.pin-marker:hover { transform: scale(1.15) translate(-50%, -50%); transform-origin: 0 0; }
.pin-pulse { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 40px; height: 40px; background-color: rgba(84, 119, 146, 0.4); border-radius: 50%; animation: ripple 1.5s infinite ease-in-out; z-index: -1; }
@keyframes ripple { 0% { transform: translate(-50%, -50%) scale(0.5); opacity: 1; } 100% { transform: translate(-50%, -50%) scale(1.5); opacity: 0; } }

.product-select-card { transition: all 0.2s ease; }
.product-select-card:hover { transform: translateY(-2px); border-color: var(--color-c-hover, #547792) !important; }

.custom-scrollbar-y::-webkit-scrollbar { width: 4px; }
.custom-scrollbar-y::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar-y::-webkit-scrollbar-thumb { background: var(--color-c-light, #94B4C1); border-radius: 10px; }
.animation-fade-in { animation: fadeIn 0.4s ease-in-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

/* Logo Shimmer Loading */
.logo-shimmer { font-size: 3.5rem; font-weight: 900; letter-spacing: -1.5px; background: linear-gradient(120deg, var(--color-c-dark) 30%, var(--color-c-light) 50%, var(--color-c-dark) 70%); background-size: 200% auto; color: transparent; -webkit-background-clip: text; background-clip: text; animation: shine 1.5s linear infinite; }
@keyframes shine { to { background-position: 200% center; } }
</style>