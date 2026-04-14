<!-- File: frontend/src/pages/admin/flash_sales/Create.vue -->
<template>
  <div class="flash-sale-create-wrapper pb-5 mb-5">
    <div class="container-fluid py-4">
      <div class="d-flex align-items-center justify-content-between mb-4">
        <h3 class="fw-bold text-dark dark:text-white mb-0 d-flex align-items-center">
          <router-link :to="{ name: 'admin-flash-sales' }" class="text-decoration-none text-muted me-3 hover:text-urban transition-all">
            <i class="bi bi-arrow-left-circle fs-3"></i>
          </router-link>
          Tạo Chiến Dịch Mới
        </h3>
        <button type="submit" form="flashSaleForm" class="btn btn-urban text-white px-5 fw-bold shadow-sm rounded-pill" :disabled="isSubmitting">
          <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-2"></span> <i class="bi bi-floppy2-fill me-1" v-else></i> LƯU CHIẾN DỊCH
        </button>
      </div>

      <form id="flashSaleForm" @submit.prevent="submitForm" autocomplete="off">
        <div class="row g-4">
          <!-- ========================================== -->
          <!-- CỘT TRÁI: THÔNG TIN CHUNG -->
          <!-- ========================================== -->
          <div class="col-lg-4">
            <div class="card shadow-sm border-0 rounded-4 dark:bg-[#1a2533] p-4 h-100">
              <h6 class="fw-bold text-urban text-uppercase mb-4 border-bottom dark:border-gray-700 pb-2"><i class="bi bi-info-circle me-2"></i>Thiết lập cơ bản</h6>
              
              <div class="mb-3">
                <label class="form-label fw-bold text-dark dark:text-gray-200 small text-uppercase">Tên chiến dịch <span class="text-danger">*</span></label>
                <input type="text" class="form-control bg-light dark:bg-[#212529] dark:text-white border-0 shadow-sm-hover" v-model="form.name" @input="generateSlug" required>
                <div class="text-danger small mt-1 fw-bold" v-if="errors.name">{{ errors.name[0] }}</div>
              </div>

              <div class="mb-4">
                <label class="form-label fw-bold text-dark dark:text-gray-200 small text-uppercase">Đường dẫn (Slug)</label>
                <input type="text" class="form-control bg-light-subtle dark:bg-[#2b3035] text-muted dark:text-gray-400 font-monospace border-0" v-model="form.slug" readonly>
              </div>

              <!-- Tách thành 2 hàng riêng biệt cho rộng rãi thoải mái -->
              <div class="mb-4">
                <label class="form-label fw-bold text-dark dark:text-gray-200 small text-uppercase"><i class="bi bi-play-circle-fill text-success me-1"></i> Bắt đầu lúc <span class="text-danger">*</span></label>
                <input type="datetime-local" class="form-control form-control-lg bg-light dark:bg-[#212529] dark:text-white border-0 shadow-sm-hover fs-6" v-model="form.start_time" required>
                <div class="text-danger small mt-1 fw-bold" v-if="errors.start_time">{{ errors.start_time[0] }}</div>
              </div>
              
              <div class="mb-4">
                <label class="form-label fw-bold text-dark dark:text-gray-200 small text-uppercase"><i class="bi bi-stop-circle-fill text-danger me-1"></i> Kết thúc lúc <span class="text-danger">*</span></label>
                <input type="datetime-local" class="form-control form-control-lg bg-light dark:bg-[#212529] dark:text-white border-0 shadow-sm-hover fs-6" v-model="form.end_time" required>
                <div class="text-danger small mt-1 fw-bold" v-if="errors.end_time">{{ errors.end_time[0] }}</div>
              </div>

              <div class="mb-4">
                <label class="form-label fw-bold text-dark dark:text-gray-200 small text-uppercase">Trạng thái phát hành</label>
                <select class="form-select bg-light dark:bg-[#212529] dark:text-white border-0 shadow-sm-hover fw-bold" v-model="form.status" :class="form.status === 'active' ? 'text-success' : 'text-warning'">
                  <option value="active">Đang chạy (Active)</option>
                  <option value="hidden">Tạm ẩn (Hidden)</option>
                </select>
              </div>

              <div class="mt-auto pt-3 border-top dark:border-gray-700">
                <label class="form-label fw-bold text-dark dark:text-gray-200 small text-uppercase">Banner Quảng Cáo</label>
                <div class="p-3 border border-dashed border-2 dark:border-gray-600 rounded-4 text-center bg-light dark:bg-[#212529]">
                  <div v-if="previewBanner" class="position-relative d-inline-block w-100">
                    <img :src="previewBanner" class="rounded object-fit-cover border shadow-sm w-100" style="height: 120px;" @error="handleImageError">
                    <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2 rounded-circle px-2 shadow" @click="removeBanner"><i class="bi bi-x-lg"></i></button>
                  </div>
                  <div v-else class="text-muted py-3">
                    <i class="bi bi-image fs-1 opacity-50 mb-2 d-block"></i>
                    <span class="small">Chưa có banner</span>
                  </div>
                  <button type="button" class="btn btn-outline-urban rounded-pill btn-sm fw-bold w-100 mt-2" @click="$refs.bannerInput.click()">
                    <i class="bi bi-upload me-1"></i> Tải ảnh lên
                  </button>
                  <input type="file" ref="bannerInput" @change="onBannerChange" class="d-none" accept="image/*">
                  <div class="text-danger small mt-2 fw-bold" v-if="errors.banner_image">{{ errors.banner_image[0] }}</div>
                </div>
              </div>
            </div>
          </div>

          <!-- ========================================== -->
          <!-- CỘT PHẢI: QUẢN LÝ SẢN PHẨM KHUYẾN MÃI -->
          <!-- ========================================== -->
          <div class="col-lg-8">
            <div class="card shadow-sm border-0 rounded-4 dark:bg-[#1a2533] p-4 h-100 d-flex flex-column">
              
              <div class="d-flex justify-content-between align-items-center mb-3 border-bottom dark:border-gray-700 pb-3">
                <div>
                  <h6 class="fw-bold mb-0 text-urban text-uppercase"><i class="bi bi-box-seam me-2"></i>Danh sách áp dụng ({{ items.length }})</h6>
                  <p class="text-muted small mb-0 mt-1">Cấu hình giá sốc và giới hạn số lượng cho từng biến thể.</p>
                </div>
                <button type="button" class="btn btn-urban rounded-pill fw-bold shadow-sm px-4" @click="openProductModal">
                  <i class="bi bi-plus-lg me-1"></i> Thêm Hàng
                </button>
              </div>

              <!-- THANH CÔNG CỤ XỬ LÝ HÀNG LOẠT (BATCH ACTIONS) -->
              <transition name="fade">
                <div v-if="selectedItemIndexes.length > 0" class="p-3 bg-urban bg-opacity-10 border border-urban border-opacity-50 rounded-4 mb-3 d-flex flex-wrap align-items-end gap-3 shadow-sm">
                  <div>
                    <label class="form-label small fw-bold text-urban mb-1">Thao tác hàng loạt ({{selectedItemIndexes.length}} SP)</label>
                    <select class="form-select form-select-sm fw-bold border-urban text-urban bg-white dark:bg-[#212529]" v-model="batchAction.type" style="width: 160px; height: 36px;">
                      <option value="percent">Giảm theo %</option>
                      <option value="amount">Giảm tiền mặt (₫)</option>
                      <option value="fixed">Đặt đồng giá (₫)</option>
                    </select>
                  </div>
                  <div>
                    <label class="form-label small fw-bold text-muted mb-1">Giá trị áp dụng</label>
                    <div class="input-group input-group-sm shadow-sm" style="width: 150px; height: 36px;">
                      <input type="text" class="form-control fw-bold text-end border-urban dark:bg-[#212529] dark:text-white" :value="formatThousand(batchAction.value)" @input="e => updateNumber(e, batchAction, 'value')" min="0">
                      <span class="input-group-text bg-white dark:bg-[#212529] text-urban fw-bold border-urban">{{ batchAction.type === 'percent' ? '%' : '₫' }}</span>
                    </div>
                  </div>
                  <button type="button" class="btn btn-sm btn-urban fw-bold px-4 shadow-sm" style="height: 36px;" @click="applyBatchDiscount">ÁP DỤNG</button>
                  <div class="vr mx-2 text-urban opacity-50"></div>
                  <button type="button" class="btn btn-sm btn-outline-danger fw-bold px-3 shadow-sm bg-white" style="height: 36px;" @click="deleteBatchSelected"><i class="bi bi-trash3 me-1"></i> XÓA NHANH</button>
                </div>
              </transition>

              <!-- Bảng Dữ Liệu -->
              <div class="table-responsive border rounded-3 dark:border-gray-700 flex-grow-1 custom-scrollbar-y" style="max-height: 600px;">
                <table class="table table-hover align-middle mb-0 text-center">
                  <thead class="bg-light dark:bg-[#2b3035] sticky-top" style="z-index: 10;">
                    <tr>
                      <th class="border-0 text-center" style="width: 5%;">
                        <input class="form-check-input cursor-pointer border-secondary" type="checkbox" v-model="selectAllItems">
                      </th>
                      <th class="dark:text-gray-300 border-0" style="width: 40%;">Sản phẩm & Phân loại</th>
                      <th class="dark:text-gray-300 border-0" style="width: 30%;">Giá Flash Sale</th>
                      <th class="dark:text-gray-300 border-0" style="width: 20%;">SL Mở Bán</th>
                      <th class="dark:text-gray-300 border-0" style="width: 5%;"></th>
                    </tr>
                  </thead>
                  <tbody class="dark:border-gray-700 dark:bg-[#1a2533]">
                    <tr v-if="items.length === 0">
                      <td colspan="5" class="text-center text-muted py-5 fst-italic">
                        <i class="bi bi-inbox fs-2 d-block opacity-25 mb-2"></i>
                        Bấm "Thêm hàng" để đưa sản phẩm vào chiến dịch.
                      </td>
                    </tr>
                    <tr v-for="(item, index) in items" :key="item.variant_id" :class="{'bg-urban bg-opacity-10': selectedItemIndexes.includes(index)}">
                      <td class="text-center">
                        <input class="form-check-input cursor-pointer border-secondary" type="checkbox" :value="index" v-model="selectedItemIndexes">
                      </td>
                      
                      <!-- SP Info -->
                      <td class="text-start py-3">
                        <div class="d-flex align-items-center">
                          <img :src="item.image" @error="handleImageError" class="rounded object-fit-cover me-2 border dark:border-gray-600 bg-white shadow-sm flex-shrink-0" style="width: 45px; height: 45px;">
                          <div class="overflow-hidden">
                            <div class="fw-bold text-dark dark:text-gray-200 small text-truncate" :title="item.product_name">{{ item.product_name }}</div>
                            <div class="d-flex align-items-center gap-2 mt-1">
                              <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary" style="font-size: 0.65rem;">{{ item.variant_name }}</span>
                              <span class="text-muted text-decoration-line-through font-monospace" style="font-size: 0.7rem;">{{ formatCurrency(item.original_price) }}</span>
                            </div>
                          </div>
                        </div>
                      </td>
                      
                      <!-- Giá Sale -->
                      <td>
                        <div class="input-group input-group-sm shadow-sm-hover mx-auto" style="max-width: 140px;">
                          <input type="text" class="form-control text-danger fw-bold text-end border-end-0 dark:bg-[#212529] dark:text-white dark:border-gray-600" 
                                 :value="formatThousand(item.flash_sale_price)" @input="e => updateNumber(e, item, 'flash_sale_price')" required>
                          <span class="input-group-text bg-white dark:bg-[#212529] text-muted border-start-0 dark:border-gray-600">₫</span>
                        </div>
                      </td>

                      <!-- Giới hạn -->
                      <td>
                         <input type="number" class="form-control form-control-sm text-center fw-bold dark:bg-[#212529] dark:text-white dark:border-gray-600 mx-auto shadow-sm-hover" style="max-width: 80px;" v-model.number="item.quantity_limit" min="1" required>
                      </td>

                      <!-- Xóa -->
                      <td>
                        <button type="button" class="btn btn-link text-danger p-0 hover-opacity" @click="removeItemRow(index)" title="Gỡ khỏi Flash Sale"><i class="bi bi-x-circle-fill fs-5"></i></button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>

    <!-- ======================================================== -->
    <!-- MODAL CHỌN SẢN PHẨM KHỔNG LỒ (ĐÃ NÂNG CẤP CHECKBOX)      -->
    <!-- ======================================================== -->
    <div class="modal fade" id="productSearchModalCreate" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
      <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content rounded-4 border-0 shadow dark:bg-[#1a2533]">
          <div class="modal-header border-bottom-0 pb-0">
            <h5 class="fw-bold text-dark dark:text-white">
              <i class="bi bi-box-seam text-urban me-2"></i> Chọn Sản Phẩm Vào Flash Sale
            </h5>
            <button type="button" class="btn-close dark:filter-invert" data-bs-dismiss="modal"></button>
          </div>
          
          <div class="modal-body p-4">
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

            <!-- ĐÃ BỔ SUNG: KHU VỰC THÊM HÀNG LOẠT VÀ THÊM NHANH THEO BỘ LỌC -->
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
              <div class="form-check m-0">
                 <input class="form-check-input border-secondary cursor-pointer" type="checkbox" id="selectAllModal" v-model="selectAllModalProducts">
                 <label class="form-check-label fw-bold text-dark dark:text-white cursor-pointer" for="selectAllModal">Chọn tất cả SP đang hiển thị</label>
              </div>

              <div class="d-flex gap-2">
                <!-- NÚT CHỌN NHANH THEO NHÓM (Chỉ hiện khi đang dùng bộ lọc) -->
                <transition name="fade">
                  <button v-if="(modalFilterCategory || modalFilterBrand) && filteredProductsFull.length > 0" 
                          type="button" class="btn btn-outline-success fw-bold shadow-sm rounded-pill px-3 bg-white dark:bg-[#212529]" 
                          @click="addAllFilteredProducts" :disabled="isModalLoading">
                    <i class="bi bi-collection-fill me-1"></i> Thêm toàn bộ {{ filteredProductsFull.length }} SP đang lọc
                  </button>
                </transition>

                <transition name="fade">
                  <button v-if="modalSelectedProductIds.length > 0" class="btn btn-urban fw-bold shadow-sm rounded-pill px-4" @click="addSelectedProducts" :disabled="isModalLoading">
                    <span v-if="isModalLoading" class="spinner-border spinner-border-sm me-2"></span>
                    Thêm {{ modalSelectedProductIds.length }} SP đã chọn <i class="bi bi-arrow-right ms-1"></i>
                  </button>
                </transition>
              </div>
            </div>

            <div class="text-center py-4" v-if="isModalLoading && modalSelectedProductIds.length === 0">
               <span class="spinner-border text-urban mb-2"></span><br>
               <span class="text-muted small">Đang nạp dữ liệu biến thể...</span>
            </div>

            <div v-else class="custom-scrollbar-y pe-2 pb-2" style="height: 50vh; overflow-y: auto;">
               <div v-if="displayFilteredProducts.length === 0" class="text-center text-muted fst-italic py-5 border rounded-4 bg-light dark:bg-[#212529] dark:border-gray-700">
                  <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i> Không tìm thấy sản phẩm.
               </div>
               <div class="row g-3">
                 <div class="col-lg-3 col-md-4 col-sm-6" v-for="prod in displayFilteredProducts" :key="prod.id">
                   <label class="card border border-secondary-subtle dark:border-gray-700 rounded-4 h-100 cursor-pointer shadow-sm-hover product-select-card position-relative" 
                          :class="{'border-urban bg-urban bg-opacity-10': modalSelectedProductIds.includes(prod.id)}">
                     <input type="checkbox" class="d-none" :value="prod.id" v-model="modalSelectedProductIds">
                     <div class="position-absolute top-0 end-0 m-2 custom-check-circle" :class="{'checked': modalSelectedProductIds.includes(prod.id)}">
                        <i class="bi bi-check-lg text-white"></i>
                     </div>

                     <div class="card-body p-3 d-flex flex-column align-items-center text-center">
                        <img :src="getImageUrl(prod.thumbnail_image)" class="rounded-3 border object-fit-cover mb-3 dark:border-gray-600 bg-white" style="width: 80px; height: 80px;" @error="handleImageError">
                        <div class="w-100 mt-auto">
                           <div class="fw-bold text-dark dark:text-white small text-truncate mb-1" :title="prod.name">{{ prod.name }}</div>
                           <div class="text-muted font-monospace mb-2" style="font-size: 0.7rem;">{{ prod.slug }}</div>
                           <div class="text-danger fw-bold fs-6">{{ formatCurrency(prod.base_price) }}</div>
                        </div>
                     </div>
                   </label>
                 </div>
               </div>
               <div v-if="filteredProductsFull.length > 30" class="text-center mt-3 text-muted small fst-italic">
                 Đang hiển thị 30 / {{ filteredProductsFull.length }} sản phẩm. Hãy dùng bộ lọc để tìm kiếm thêm.
               </div>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, computed, onBeforeUnmount } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import Swal from 'sweetalert2';
import defaultImage from '@/assets/images/defaults/placeholder.png';

const router = useRouter();

const isSubmitting = ref(false);
const previewBanner = ref(null);
const bannerInput = ref(null);

const form = ref({ name: '', slug: '', start_time: '', end_time: '', status: 'active', banner_image: null });
const items = ref([]); 
const errors = ref({});

// Modal Data
const categories = ref([]);
const brands = ref([]);
const allPublishedProducts = ref([]);
const modalSearchQuery = ref('');
const modalFilterCategory = ref('');
const modalFilterBrand = ref('');
const isModalLoading = ref(false);
let productModalInstance = null;

// ==========================================
// STATE & LOGIC CHO CHỌN NHIỀU SP TRONG MODAL
// ==========================================
const modalSelectedProductIds = ref([]);

const selectAllModalProducts = computed({
  get() {
    return displayFilteredProducts.value.length > 0 && modalSelectedProductIds.value.length === displayFilteredProducts.value.length;
  },
  set(val) {
    if(val) modalSelectedProductIds.value = displayFilteredProducts.value.map(p => p.id);
    else modalSelectedProductIds.value = [];
  }
});

// ĐÃ BỔ SUNG: Logic bấm 1 phát thêm toàn bộ SP đang lọc
const addAllFilteredProducts = async () => {
  if (filteredProductsFull.value.length === 0) return;
  modalSelectedProductIds.value = filteredProductsFull.value.map(p => p.id);
  await addSelectedProducts();
};

const addSelectedProducts = async () => {
  if (modalSelectedProductIds.value.length === 0) return;
  isModalLoading.value = true;
  let addedCount = 0;
  
  try {
    const requests = modalSelectedProductIds.value.map(id => axios.get(`${import.meta.env.VITE_API_BASE_URL}/admin/products/${id}`, { headers: getHeaders() }));
    const responses = await Promise.all(requests);
    
    responses.forEach(res => {
        const prodDetail = res.data.data;
        if (prodDetail.variants && prodDetail.variants.length > 0) {
            prodDetail.variants.forEach(v => {
                if (!items.value.some(i => i.variant_id === v.id)) {
                    items.value.push({
                        variant_id: v.id, 
                        flash_sale_price: v.price, 
                        quantity_limit: 100, 
                        sold_quantity: 0,
                        product_name: prodDetail.name, 
                        variant_name: parseVariantAttributes(v.attribute_values),
                        original_price: v.price, 
                        image: getImageUrl(v.image_url || prodDetail.thumbnail_image)
                    });
                    addedCount++;
                }
            });
        }
    });

    if (addedCount > 0) {
        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: `Đã thêm ${addedCount} phân loại vào bảng`, showConfirmButton: false, timer: 2000 });
        productModalInstance.hide();
        modalSelectedProductIds.value = []; 
    } else {
        Swal.fire({ toast: true, position: 'top-end', icon: 'info', title: 'Tất cả phân loại của các SP này đã nằm trong bảng', showConfirmButton: false, timer: 2000 });
    }
  } catch (error) {
    Swal.fire('Lỗi', 'Không tải được chi tiết biến thể', 'error');
  } finally {
    isModalLoading.value = false;
  }
};


// ==========================================
// STATE & LOGIC CHO THAO TÁC HÀNG LOẠT (MAIN TABLE)
// ==========================================
const selectedItemIndexes = ref([]);
const batchAction = ref({ type: 'percent', value: 0 });

const selectAllItems = computed({
  get() {
    return items.value.length > 0 && selectedItemIndexes.value.length === items.value.length;
  },
  set(val) {
    if(val) selectedItemIndexes.value = items.value.map((_, i) => i);
    else selectedItemIndexes.value = [];
  }
});

const applyBatchDiscount = () => {
  if (batchAction.value.value <= 0 && batchAction.value.type !== 'fixed') {
     Swal.fire({ toast: true, position: 'top-end', icon: 'warning', title: 'Vui lòng nhập giá trị hợp lệ', showConfirmButton: false, timer: 2000 });
     return;
  }
  
  selectedItemIndexes.value.forEach(idx => {
      const item = items.value[idx];
      let newPrice = item.original_price;
      const val = Number(batchAction.value.value);
      
      if (batchAction.value.type === 'percent') {
          newPrice = item.original_price * (1 - val / 100);
      } else if (batchAction.value.type === 'amount') {
          newPrice = item.original_price - val;
      } else if (batchAction.value.type === 'fixed') {
          newPrice = val;
      }
      
      // Chống số âm
      if (newPrice < 0) newPrice = 0;
      item.flash_sale_price = Math.round(newPrice);
  });
  
  Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: `Đã áp dụng giá mới cho ${selectedItemIndexes.value.length} dòng`, showConfirmButton: false, timer: 2000 });
};

const deleteBatchSelected = () => {
  Swal.fire({ title: 'Xóa hàng loạt?', text: `Sẽ xóa ${selectedItemIndexes.value.length} dòng đã chọn khỏi chiến dịch.`, icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Đồng ý' }).then((result) => {
    if (result.isConfirmed) {
      const sortedIndexes = [...selectedItemIndexes.value].sort((a,b) => b - a);
      sortedIndexes.forEach(idx => {
          items.value.splice(idx, 1);
      });
      selectedItemIndexes.value = [];
      Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Đã xóa', showConfirmButton: false, timer: 1500 });
    }
  });
};

const getHeaders = () => ({ 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('admin_token')}` });
const getImageUrl = (path) => path ? `http://127.0.0.1:8000/storage/${path}` : defaultImage;
const handleImageError = (e) => { e.target.src = defaultImage; };

const formatCurrency = (val) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND', maximumFractionDigits: 0 }).format(val || 0);
const formatThousand = (val) => {
  if (val === null || val === undefined || val === '') return '';
  return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
};
const updateNumber = (e, targetObj, key) => {
  let rawValue = e.target.value.replace(/\D/g, ''); 
  if (rawValue === '') { targetObj[key] = 0; e.target.value = '0'; } 
  else {
    let numValue = parseInt(rawValue, 10);
    targetObj[key] = numValue;
    e.target.value = formatThousand(numValue);
  }
};
const generateSlug = () => {
  let s = form.value.name.toLowerCase();
  s = s.replace(/á|à|ả|ạ|ã|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ/gi, 'a').replace(/é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ/gi, 'e').replace(/i|í|ì|ỉ|ĩ|ị/gi, 'i').replace(/ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ/gi, 'o').replace(/ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự/gi, 'u').replace(/ý|ỳ|ỷ|ỹ|ỵ/gi, 'y').replace(/đ/gi, 'd');
  form.value.slug = s.replace(/\s+/g, '-').replace(/[^a-z0-9\-]/g, '').replace(/\-\-+/g, '-');
};

const parseVariantAttributes = (attrValues) => {
    if (!attrValues || attrValues.length === 0) return 'Mặc định';
    return attrValues.map(a => a.value).join(' - ');
};

const fetchDataForModal = async () => {
  try {
    const [resProds, resCats, resBrands] = await Promise.all([
      axios.get('http://127.0.0.1:8000/api/v1/admin/products?status=published', { headers: getHeaders() }),
      axios.get('http://127.0.0.1:8000/api/v1/admin/categories', { headers: getHeaders() }),
      axios.get('http://127.0.0.1:8000/api/v1/admin/brands', { headers: getHeaders() })
    ]);
    const prods = Array.isArray(resProds.data?.data?.data) ? resProds.data.data.data : (Array.isArray(resProds.data?.data) ? resProds.data.data : []);
    allPublishedProducts.value = prods.filter(p => p.status === 'published' && !p.deleted_at);
    categories.value = Array.isArray(resCats.data?.data) ? resCats.data.data.filter(c => !c.deleted_at && c.status === 'active') : [];
    brands.value = Array.isArray(resBrands.data?.data) ? resBrands.data.data.filter(b => !b.deleted_at && b.status === 'active') : [];
  } catch (error) { console.error("Lỗi lấy dữ liệu Modal:", error); }
};

const onBannerChange = (e) => {
  const file = e.target.files[0];
  if (!file) return;
  if (file.size > 5 * 1024 * 1024) { Swal.fire('Lỗi', 'Ảnh không vượt quá 5MB', 'error'); return; }
  form.value.banner_image = file;
  const reader = new FileReader();
  reader.onload = (e) => { previewBanner.value = e.target.result; };
  reader.readAsDataURL(file);
};
const removeBanner = () => { previewBanner.value = null; form.value.banner_image = null; };

const removeItemRow = (index) => {
   items.value.splice(index, 1);
   const idxInSelected = selectedItemIndexes.value.indexOf(index);
   if(idxInSelected > -1) selectedItemIndexes.value.splice(idxInSelected, 1);
   selectedItemIndexes.value = selectedItemIndexes.value.map(val => val > index ? val - 1 : val);
};

const hierarchicalCategories = computed(() => {
  const buildTree = (parentId = null, level = 0) => {
    let res = [];
    const children = categories.value.filter(c => (c.parent_id || null) === (parentId || null));
    children.forEach(child => {
      res.push({ ...child, displayName: (level > 0 ? '\u00A0\u00A0\u00A0\u00A0'.repeat(level) + '↳ ' : '') + child.name, level: level });
      res = res.concat(buildTree(child.id, level + 1));
    });
    return res;
  };
  return buildTree(null);
});

const getAllCategoryIds = (id) => {
  let ids = [id];
  const children = categories.value.filter(c => c.parent_id === id);
  children.forEach(child => { ids = ids.concat(getAllCategoryIds(child.id)); });
  return ids;
};

// ĐÃ BỔ SUNG: Tách biến full data và biến display (chống lag)
const filteredProductsFull = computed(() => {
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
  return res; 
});

const displayFilteredProducts = computed(() => {
  return filteredProductsFull.value.slice(0, 30); // Giữ tối đa 30 SP để Modal render mượt
});

const openProductModal = () => {
  modalSelectedProductIds.value = []; 
  if (!productModalInstance) productModalInstance = new window.bootstrap.Modal(document.getElementById('productSearchModalCreate'));
  productModalInstance.show();
};

const submitForm = async () => {
  if (items.value.length === 0) return Swal.fire('Lỗi', 'Vui lòng thêm ít nhất 1 sản phẩm vào Flash Sale!', 'warning');

  isSubmitting.value = true;
  const formData = new FormData();
  formData.append('name', form.value.name);
  formData.append('slug', form.value.slug);
  formData.append('start_time', form.value.start_time);
  formData.append('end_time', form.value.end_time);
  formData.append('status', form.value.status);
  
  if (form.value.banner_image) formData.append('banner_image', form.value.banner_image);
  
  const payloadItems = items.value.map(i => ({
      variant_id: i.variant_id,
      flash_sale_price: i.flash_sale_price,
      quantity_limit: i.quantity_limit
  }));
  formData.append('items_data', JSON.stringify(payloadItems));

  try {
    await axios.post('/api/v1/admin/flash_sales', formData, {
      headers: { ...getHeaders(), 'Content-Type': 'multipart/form-data' }
    });
    Swal.fire('Thành công', 'Tạo chiến dịch Flash Sale thành công!', 'success');
    router.push('/admin/flash-sales');
  } catch (error) {
    Swal.fire('Lỗi', error.response?.data?.message || 'Có lỗi xảy ra', 'error');
  } finally {
    isSubmitting.value = false;
  }
};

onMounted(fetchDataForModal);
onBeforeUnmount(() => {
  if (productModalInstance) productModalInstance.hide();
  document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
  document.body.className = '';
  document.body.style = '';
});
</script>

<style scoped>
.text-urban { color: var(--color-c-hover, #547792) !important; }
.bg-urban { background-color: var(--color-c-hover, #547792) !important; }
.border-urban { border-color: var(--color-c-hover, #547792) !important; }
.btn-urban { background-color: var(--color-c-hover, #547792); color: white; border: none; transition: 0.2s; }
.btn-urban:hover { background-color: var(--color-c-dark, #213448); color: white; transform: translateY(-2px); }
.btn-outline-urban { color: var(--color-c-hover, #547792); border-color: var(--color-c-hover, #547792); background: transparent; transition: 0.2s; }
.btn-outline-urban:hover { background-color: var(--color-c-hover, #547792); color: white; }

.shadow-sm-hover { transition: box-shadow 0.2s ease, border-color 0.2s ease; }
.shadow-sm-hover:focus-within { box-shadow: 0 4px 15px rgba(84, 119, 146, 0.1) !important; border-color: var(--color-c-hover, #547792) !important; }
.form-control:focus, .form-select:focus { border-color: var(--color-c-hover, #547792); box-shadow: none !important; }

.hover-opacity:hover { opacity: 0.7; }
.product-select-card { transition: all 0.2s ease; }
.product-select-card:hover { transform: translateY(-2px); border-color: var(--color-c-hover, #547792) !important; }

.custom-check-circle {
   width: 24px; height: 24px;
   border: 2px solid #dee2e6;
   border-radius: 50%;
   display: flex; justify-content: center; align-items: center;
   transition: all 0.2s ease;
   background: rgba(255,255,255,0.8);
}
.custom-check-circle i { opacity: 0; font-size: 0.9rem; transition: opacity 0.2s ease; }
.custom-check-circle.checked { background-color: var(--color-c-hover, #547792); border-color: var(--color-c-hover, #547792); }
.custom-check-circle.checked i { opacity: 1; }
html.dark .custom-check-circle { border-color: #495057; background: rgba(0,0,0,0.5); }
html.dark .custom-check-circle.checked { background-color: var(--color-c-hover, #547792); border-color: var(--color-c-hover, #547792); }

.logo-shimmer { font-size: 3.5rem; font-weight: 900; letter-spacing: -1.5px; background: linear-gradient(120deg, var(--color-c-dark) 30%, var(--color-c-light) 50%, var(--color-c-dark) 70%); background-size: 200% auto; color: transparent; -webkit-background-clip: text; background-clip: text; animation: shine 1.5s linear infinite; }
@keyframes shine { to { background-position: 200% center; } }
.animation-fade-in { animation: fadeIn 0.4s ease-in-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

.custom-scrollbar-y::-webkit-scrollbar { width: 5px; }
.custom-scrollbar-y::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar-y::-webkit-scrollbar-thumb { background: var(--color-c-light, #94B4C1); border-radius: 10px; }
.custom-scrollbar-x::-webkit-scrollbar { height: 6px; }
.custom-scrollbar-x::-webkit-scrollbar-thumb { background: var(--color-c-light, #94B4C1); border-radius: 10px; }
</style>