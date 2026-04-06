<!-- File: frontend/src/pages/admin/lookbook/Edit.vue -->
<template>
  <div class="lookbook-edit-wrapper pb-5 mb-5">
    
    <div v-if="isPageLoading" class="d-flex flex-column justify-content-center align-items-center w-100" style="min-height: 70vh;">
      <h1 class="logo-shimmer mb-3">ZYRO</h1>
      <p class="text-muted dark:text-gray-400 fw-semibold small text-uppercase tracking-widest" style="letter-spacing: 2px;">Đang tải hồ sơ...</p>
    </div>

    <div class="container-fluid py-4" v-else>
      
      <!-- HEADER CÙNG NÚT LƯU -->
      <div class="row mb-4 align-items-center">
        <div class="col-md-8 d-flex align-items-center mb-3 mb-md-0">
          <router-link :to="{ name: 'admin-lookbooks' }" class="btn btn-light dark:bg-[#2b3035] dark:border-gray-600 dark:text-gray-200 shadow-sm me-3 rounded-circle d-flex align-items-center justify-content-center hover-urban transition-all" style="width: 40px; height: 40px;">
            <i class="bi bi-arrow-left fw-bold"></i>
          </router-link>
          <div class="d-flex flex-column">
            <h3 class="fw-bold text-dark dark:text-white mb-0">Cập nhật Bộ Sưu Tập</h3>
            <p class="text-muted dark:text-gray-400 small mb-0 mt-1">Chỉnh sửa nội dung và ghim lại sản phẩm trên ảnh</p>
          </div>
        </div>
        <div class="col-md-4 text-md-end">
          <button type="submit" form="lookbookForm" class="btn btn-urban text-white px-5 py-2.5 fw-bold shadow-sm rounded-pill w-100 w-md-auto" :disabled="isSaving">
            <span v-if="isSaving" class="spinner-border spinner-border-sm me-2"></span> 
            <i class="bi bi-floppy2-fill me-1" v-else></i> LƯU CẬP NHẬT
          </button>
        </div>
      </div>

      <form id="lookbookForm" @submit.prevent="submitLookbook" autocomplete="off">
        
        <!-- ========================================== -->
        <!-- TOP BAR: THÔNG TIN CƠ BẢN VÀ ĐỊNH GIÁ -->
        <!-- ========================================== -->
        <div class="card border-0 shadow-sm rounded-4 dark:bg-[#1a2533] p-4 mb-4 animation-fade-in">
          <h6 class="fw-bold mb-3 text-urban text-uppercase"><i class="bi bi-info-circle me-2"></i>THÔNG TIN CHUNG</h6>
          
          <!-- HÀNG 1: THÔNG TIN CƠ BẢN -->
          <div class="row g-3 mb-4">
            <div class="col-xl-4 col-lg-6">
              <label class="form-label fw-bold text-dark dark:text-gray-200 small text-uppercase">Tên BST <span class="text-danger">*</span></label>
              <input type="text" class="form-control bg-light dark:bg-[#212529] dark:text-white border-0 shadow-sm-hover" v-model="form.name" @input="generateSlug" required>
              <div class="text-danger small mt-1 fw-bold" v-if="errors.name">{{ errors.name[0] }}</div>
            </div>

            <div class="col-xl-3 col-lg-6">
              <label class="form-label fw-bold text-dark dark:text-gray-200 small text-uppercase">Đường dẫn (Slug)</label>
              <input type="text" class="form-control bg-light-subtle dark:bg-[#2b3035] text-muted dark:text-gray-400 font-monospace border-0" v-model="form.slug" readonly>
            </div>
            
            <div class="col-xl-2 col-lg-6">
              <label class="form-label fw-bold text-dark dark:text-gray-200 small text-uppercase">Dành cho (Gender)</label>
              <select class="form-select bg-light dark:bg-[#212529] dark:text-white border-0 shadow-sm-hover" v-model="form.gender">
                <option value="Unisex">Unisex</option>
                <option value="Men">Nam</option>
                <option value="Women">Nữ</option>
                <option value="Kids">Trẻ em</option>
              </select>
            </div>

            <div class="col-xl-3 col-lg-6">
              <label class="form-label fw-bold text-dark dark:text-gray-200 small text-uppercase mb-2 d-block">Trạng thái xuất bản</label>
              <!-- ĐÃ FIX CSS: Switch thu gọn, không còn bị xô lệch và chìm chữ -->
              <div class="d-flex align-items-center px-3 py-1 bg-light dark:bg-[#212529] border border-light-subtle dark:border-gray-700 rounded-3 shadow-sm h-100" style="min-height: 40px;">
                <div class="form-check form-switch mb-0 d-flex align-items-center gap-2 w-100 ps-0">
                  <input class="form-check-input fs-4 m-0 ms-1 cursor-pointer float-none" type="checkbox" role="switch" id="publishSwitch" v-model="form.isPublished">
                  <label class="form-check-label fw-bold text-urban m-0 cursor-pointer text-nowrap w-100 no-select" for="publishSwitch">Hiển thị công khai</label>
                </div>
              </div>
            </div>
          </div>
          
          <!-- HÀNG 2: TÍNH GIÁ TỰ ĐỘNG (ĐÃ GỠ BACKGROUND LỖI VÀ GIỮ NGUYÊN CẤU TRÚC ĐẸP) -->
          <div class="p-3 bg-light dark:bg-[#212529] border dark:border-gray-700 rounded-3 mb-4">
            <h6 class="fw-bold text-urban mb-3"><i class="bi bi-calculator-fill me-2"></i>Tính giá Set đồ tự động (Up-sale)</h6>
            
            <div class="row g-3 align-items-start">
              
              <div class="col-xl-3 col-md-6">
                <label class="form-label small text-muted dark:text-gray-400 fw-bold text-uppercase">Tổng giá SP đã ghim</label>
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
             <input type="text" class="form-control bg-light dark:bg-[#212529] dark:text-white border-0 shadow-sm-hover" v-model="form.description">
          </div>
        </div>

        <!-- ========================================== -->
        <!-- KHÔNG GIAN LÀM VIỆC DƯỚI -->
        <!-- ========================================== -->
        <div class="row g-4">
          
          <!-- CỘT TRÁI: CHỈ ĐỂ TẢI ẢNH NỀN -->
          <div class="col-xl-3 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 dark:bg-[#1a2533] h-100">
              <div class="card-body p-4 text-center d-flex flex-column">
                <h6 class="fw-bold mb-3 text-urban text-start border-bottom dark:border-gray-700 pb-2"><i class="bi bi-image me-2"></i>ẢNH MẪU <span class="text-danger">*</span></h6>
                
                <div class="mb-3 position-relative border border-dashed border-2 dark:border-gray-600 rounded-4 overflow-hidden bg-light dark:bg-[#212529] d-flex align-items-center justify-content-center cursor-pointer hover-bg-effect transition-all flex-grow-1" 
                     style="min-height: 400px;" @click="$refs.mainImageInput.click()">
                  <img v-if="mainImagePreview" :src="mainImagePreview" class="w-100 h-100 object-fit-cover p-1 rounded-4" @error="handleImageError">
                  <div v-else class="text-muted dark:text-gray-500 text-center p-3">
                    <i class="bi bi-cloud-arrow-up fs-1 mb-2 text-urban d-block"></i>
                    <span class="small fw-semibold">Click để chọn ảnh nền</span>
                  </div>
                </div>
                <input type="file" class="d-none" ref="mainImageInput" id="mainImageUpload" accept="image/*" @change="handleMainImageUpload">
                <label for="mainImageUpload" class="btn btn-urban rounded-pill w-100 fw-semibold shadow-sm cursor-pointer mb-2"><i class="bi bi-cloud-arrow-up me-1"></i> Thay đổi ảnh</label>
                <div class="text-danger small fw-bold mb-2" v-if="errors.main_image">{{ errors.main_image[0] }}</div>
                
                <div class="alert alert-warning small border-0 p-2 text-start mt-auto" v-if="hasNewImage">
                  <i class="bi bi-exclamation-triangle fw-bold me-1"></i> Các ghim tọa độ cũ đã bị xóa do đổi ảnh (Tỷ lệ khác). Vui lòng tạo ghim lại!
                </div>
              </div>
            </div>
          </div>

          <!-- CỘT PHẢI: KHÔNG GIAN GHIM (WORKSPACE) -->
          <div class="col-xl-9 col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 dark:bg-[#1a2533] h-100 overflow-hidden d-flex flex-column">
              <div class="card-header bg-white dark:bg-[#1a2533] border-bottom dark:border-gray-700 py-3 px-4 d-flex justify-content-between align-items-center">
                 <div>
                   <h5 class="fw-bold text-dark dark:text-white mb-1"><i class="bi bi-pin-map-fill text-urban me-2"></i>Không gian Ghim (Workspace)</h5>
                   <p class="text-muted small mb-0">Click trực tiếp vào vị trí trên ảnh mẫu bên dưới để thả ghim và gắn sản phẩm.</p>
                 </div>
              </div>
              
              <div class="card-body p-0 d-flex flex-column flex-xxl-row bg-c-effect dark:bg-[#121416]">
                
                <!-- CANVAS ẢNH (Khu vực thao tác chính) -->
                <div class="flex-grow-1 p-4 d-flex align-items-center justify-content-center" style="min-height: 500px;">
                   <div v-if="!mainImagePreview" class="text-center text-muted opacity-50">
                     <i class="bi bi-ban fs-1 d-block mb-2"></i>
                     Vui lòng tải ảnh mẫu ở cột bên trái để bắt đầu.
                   </div>

                   <div v-else class="position-relative shadow-sm border border-2 border-white dark:border-gray-700 bg-white rounded-3 overflow-hidden" style="display: inline-block;">
                     <img :src="mainImagePreview" class="img-fluid cursor-crosshair" @click="handleImageClick" ref="imageCanvas" style="max-height: 75vh; object-fit: contain;" @error="handleImageError">
                     
                     <!-- Render các ghim (Pins) -->
                     <div v-for="(pin, index) in pins" :key="index"
                          class="position-absolute translate-middle pin-marker"
                          :style="{ top: pin.y + '%', left: pin.x + '%' }"
                          @click.stop="editPin(index)"
                          title="Click để sửa sản phẩm">
                        <div class="pin-pulse"></div>
                        <span class="badge bg-urban rounded-circle shadow border border-2 border-white position-relative d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; font-size: 0.85rem;">
                          {{ index + 1 }}
                        </span>
                     </div>
                   </div>
                </div>

                <!-- DANH SÁCH SẢN PHẨM ĐÃ GHIM (Cột nhỏ bên phải Workspace) -->
                <div class="bg-white dark:bg-[#1a2533] p-3 border-start dark:border-gray-700 custom-scrollbar-y" style="width: 100%; max-width: 350px; overflow-y: auto; max-height: 80vh;">
                   <h6 class="fw-bold text-dark dark:text-white mb-3 border-bottom dark:border-gray-700 pb-2">Danh sách Đã Ghim ({{ pins.length }})</h6>
                   <div class="text-danger small fw-bold mb-3" v-if="errors.items_data">{{ errors.items_data[0] }}</div>
                   
                   <div v-if="pins.length === 0" class="text-muted small fst-italic text-center py-5 bg-light dark:bg-[#212529] rounded-3 border border-dashed dark:border-gray-700">
                     Chưa có ghim nào được tạo.
                   </div>
                   
                   <div v-else class="d-flex flex-column gap-3">
                     <div v-for="(pin, index) in pins" :key="index" class="p-2 border dark:border-gray-600 rounded-3 bg-light dark:bg-[#212529] position-relative transition-all hover-border-urban shadow-sm">
                       <div class="d-flex justify-content-between align-items-center mb-2">
                          <span class="badge bg-urban rounded-pill px-2 shadow-sm">Ghim #{{ index + 1 }}</span>
                          <button type="button" class="btn-close btn-close-sm dark:filter-invert" @click.stop="removePin(index)"></button>
                       </div>
                       
                       <!-- Nút chọn / Hiển thị sản phẩm -->
                       <div v-if="pin.product_id" class="d-flex align-items-center bg-white dark:bg-[#1a2533] p-2 border dark:border-gray-700 rounded shadow-sm cursor-pointer" @click="editPin(index)">
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
      </form>
    </div>

    <!-- MODAL TÌM KIẾM & GẮN SẢN PHẨM CHO GHIM -->
    <div class="modal fade" id="productSearchModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow dark:bg-[#1a2533]">
          <div class="modal-header border-bottom-0 pb-0">
            <h5 class="fw-bold text-dark dark:text-white"><i class="bi bi-box-seam text-urban me-2"></i>Chọn sản phẩm cho Ghim #{{ activePinIndex !== null ? activePinIndex + 1 : '' }}</h5>
            <button type="button" class="btn-close dark:filter-invert" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body p-4">
            <div class="input-group input-group-lg shadow-sm mb-4">
               <span class="input-group-text bg-white dark:bg-[#212529] border-end-0 text-muted"><i class="bi bi-search"></i></span>
               <input type="text" class="form-control border-start-0 bg-white dark:bg-[#212529] dark:text-white dark:border-gray-700 shadow-sm-hover" v-model="modalSearchQuery" placeholder="Nhập mã SKU hoặc Tên sản phẩm để tìm...">
            </div>

            <div class="text-center py-4" v-if="isSearchingProducts">
               <span class="spinner-border text-urban mb-2"></span><br>
               <span class="text-muted small">Đang tìm kiếm...</span>
            </div>

            <div v-else class="custom-scrollbar-y pe-2" style="max-height: 40vh; overflow-y: auto;">
               <div v-if="filteredProducts.length === 0" class="text-center text-muted fst-italic py-3">Không tìm thấy sản phẩm nào (Chỉ hiển thị SP đang Xuất bản).</div>
               <div class="row g-3">
                 <div class="col-md-6" v-for="prod in filteredProducts" :key="prod.id">
                   <div class="card border border-secondary-subtle dark:border-gray-700 rounded-3 h-100 cursor-pointer shadow-sm-hover product-select-card" 
                        :class="{'border-urban bg-urban bg-opacity-10': activePinData?.product_id === prod.id}"
                        @click="selectProductForPin(prod)">
                     <div class="card-body p-2 d-flex align-items-center">
                        <img :src="getImageUrl(prod.thumbnail_image)" class="rounded border object-fit-cover me-3 dark:border-gray-600 bg-white" style="width: 55px; height: 55px;" @error="handleImageError">
                        <div class="overflow-hidden">
                           <div class="fw-bold text-dark dark:text-white small text-truncate" :title="prod.name">{{ prod.name }}</div>
                           <div class="text-muted font-monospace mt-1 mb-1" style="font-size: 0.7rem;">{{ prod.slug }}</div>
                           <div class="text-urban fw-bold" style="font-size: 0.8rem;">{{ formatCurrency(prod.base_price) }}</div>
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
import { useRouter, useRoute } from 'vue-router';
import Swal from 'sweetalert2';
import axios from 'axios';
import defaultImage from '@/assets/images/defaults/placeholder.png';

const router = useRouter();
const route = useRoute();
const lookbookId = route.params.id;

const isPageLoading = ref(true);
const isDataLoaded = ref(false); 
const isSaving = ref(false);
const errors = ref({});

const form = ref({ name: '', slug: '', description: '', gender: 'Unisex', total_price_estimate: 0, isPublished: false });
const mainImageFile = ref(null);
const mainImagePreview = ref(null);
const hasNewImage = ref(false); 
const imageCanvas = ref(null);

const pins = ref([]);

const activePinIndex = ref(null);
const activePinData = computed(() => activePinIndex.value !== null ? pins.value[activePinIndex.value] : null);
let productModalInstance = null;
const allPublishedProducts = ref([]);
const isSearchingProducts = ref(false);
const modalSearchQuery = ref('');

// LOGIC TÍNH GIÁ TỰ ĐỘNG
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

const getHeaders = () => ({ 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('admin_token')}` });
const getImageUrl = (path) => path ? `http://127.0.0.1:8000/storage/${path}` : defaultImage;
const handleImageError = (e) => { e.target.src = defaultImage; };
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
    pins.value = []; // Reset pins khi đổi ảnh vì sai tọa độ
  }
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
  if (!productModalInstance) productModalInstance = new window.bootstrap.Modal(document.getElementById('productSearchModal'));
  productModalInstance.show();
};

const selectProductForPin = (prod) => {
  if (activePinIndex.value !== null && pins.value[activePinIndex.value]) {
     const isExist = pins.value.some((p, idx) => p.product_id === prod.id && idx !== activePinIndex.value);
     if (isExist) {
        Swal.fire({ toast:true, position: 'top-end', icon: 'warning', title: 'Sản phẩm này đã được ghim trong ảnh!', showConfirmButton: false, timer: 2000 });
        return;
     }

     pins.value[activePinIndex.value].product_id = prod.id;
     pins.value[activePinIndex.value].product_name = prod.name;
     pins.value[activePinIndex.value].product_price = prod.base_price;
     pins.value[activePinIndex.value].product_image = getImageUrl(prod.thumbnail_image);
     
     productModalInstance.hide();
  }
};

const filteredProducts = computed(() => {
  if (!modalSearchQuery.value) return allPublishedProducts.value.slice(0, 20); 
  const q = modalSearchQuery.value.toLowerCase();
  return allPublishedProducts.value.filter(p => p.name.toLowerCase().includes(q) || p.slug.toLowerCase().includes(q));
});

const fetchData = async () => {
  isPageLoading.value = true;
  try {
    const [resLB, resProd] = await Promise.all([
      axios.get(`http://127.0.0.1:8000/api/v1/admin/lookbooks/${lookbookId}`, { headers: getHeaders() }),
      axios.get('http://127.0.0.1:8000/api/v1/admin/products?status=published', { headers: getHeaders() })
    ]);
    
    const lb = resLB.data?.data;
    if (!lb) throw new Error("Lookbook rỗng");

    form.value.name = lb.name || '';
    form.value.slug = lb.slug || '';
    form.value.description = lb.description || '';
    form.value.gender = lb.gender || 'Unisex';
    form.value.total_price_estimate = lb.total_price_estimate || 0;
    form.value.isPublished = lb.status === 'published';
    
    if (lb.main_image) mainImagePreview.value = getImageUrl(lb.main_image);

    if (lb.items && lb.items.length > 0) {
       pins.value = lb.items.map(item => {
           const coords = typeof item.pin_coordinates === 'string' ? JSON.parse(item.pin_coordinates) : item.pin_coordinates;
           return {
               x: coords?.x || 0,
               y: coords?.y || 0,
               product_id: item.product_id,
               product_name: item.product ? item.product.name : 'SP Không tồn tại',
               product_price: item.product ? item.product.base_price : 0,
               product_image: item.product ? getImageUrl(item.product.thumbnail_image) : defaultImage,
               sort_order: item.sort_order || 0
           };
       });
    }

    let sumPins = pins.value.reduce((acc, p) => acc + (Number(p.product_price) || 0), 0);
    if (sumPins >= form.value.total_price_estimate) {
        discountType.value = 'amount';
        discountValue.value = sumPins - form.value.total_price_estimate;
    }

    const payload = resProd.data?.data;
    allPublishedProducts.value = Array.isArray(payload?.data) ? payload.data : (Array.isArray(payload) ? payload : []);
    
    isDataLoaded.value = true;
  } catch (err) {
    console.error("Lỗi tải data Edit", err);
    Swal.fire('Lỗi', 'Không tìm thấy thông tin Lookbook', 'error');
    router.push({ name: 'admin-lookbooks' });
  } finally { isPageLoading.value = false; }
};

const submitLookbook = async () => {
  const validPins = pins.value.filter(p => p.product_id !== null);
  if (validPins.length === 0 && !hasNewImage.value) { 
      Swal.fire('Lỗi', 'Lookbook phải có ít nhất 1 sản phẩm được ghim trên ảnh.', 'warning'); 
      return; 
  }

  isSaving.value = true; errors.value = {};
  
  const formData = new FormData();
  formData.append('_method', 'PUT'); 
  formData.append('name', form.value.name);
  formData.append('slug', form.value.slug);
  formData.append('description', form.value.description);
  formData.append('gender', form.value.gender); 
  formData.append('total_price_estimate', form.value.total_price_estimate);
  formData.append('status', form.value.isPublished ? 'published' : 'draft');
  
  if (mainImageFile.value) {
    formData.append('main_image', mainImageFile.value);
  }

  const itemsData = validPins.map((p, index) => ({
    product_id: p.product_id,
    pin_coordinates: { x: parseFloat(p.x), y: parseFloat(p.y) },
    sort_order: index
  }));
  formData.append('items_data', JSON.stringify(itemsData));

  try {
    const res = await axios.post(`http://127.0.0.1:8000/api/v1/admin/lookbooks/${lookbookId}`, formData, { headers: { ...getHeaders(), 'Content-Type': 'multipart/form-data' } });
    Swal.fire({ icon: 'success', title: 'Cập nhật thành công', text: res.data.message, timer: 2000, showConfirmButton: false }).then(() => {
      router.push({ name: 'admin-lookbooks' });
    });
  } catch (e) {
    if (e.response && e.response.data && e.response.data.errors) {
       errors.value = e.response.data.errors;
       Swal.fire({ title: 'Dữ liệu không hợp lệ', text: 'Vui lòng kiểm tra các cảnh báo đỏ', icon: 'error' });
    } else { Swal.fire('Lỗi', e.response?.data?.message || 'Lỗi cập nhật Lookbook.', 'error'); }
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

/* Cấm bôi đen chữ */
.no-select { user-select: none !important; -webkit-user-select: none !important; -moz-user-select: none !important; }
.cursor-pointer { cursor: pointer; }
.cursor-crosshair { cursor: crosshair; }

.shadow-sm-hover { transition: box-shadow 0.2s ease, border-color 0.2s ease; }
.shadow-sm-hover:focus-within { box-shadow: 0 4px 15px rgba(84, 119, 146, 0.1) !important; }
.form-control:focus, .form-select:focus { border-color: var(--color-c-hover, #547792); box-shadow: none !important; }
.border-dashed { border-style: dashed !important; border-width: 2px !important; }

/* HIỆU ỨNG GHIM (PIN) CHUẨN MỰC MUA SẮM */
.pin-marker { z-index: 10; cursor: pointer; transition: transform 0.2s; }
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
</style>