<!-- File: frontend/src/pages/admin/voucher/Create.vue -->
<template>
  <div class="voucher-create-wrapper pb-5 mb-5">
    
    <div v-if="isPageLoading" class="d-flex flex-column justify-content-center align-items-center w-100" style="min-height: 70vh;">
      <h1 class="logo-shimmer mb-3">ZYRO</h1>
      <p class="text-muted dark:text-gray-400 fw-semibold small text-uppercase tracking-widest" style="letter-spacing: 2px;">Đang chuẩn bị không gian...</p>
    </div>

    <div class="container-fluid py-4" v-else>
      
      <!-- HEADER -->
      <div class="row mb-4 align-items-center">
        <div class="col-md-8 d-flex align-items-center mb-3 mb-md-0">
          <router-link :to="{ name: 'admin-vouchers' }" class="btn btn-light dark:bg-[#2b3035] dark:border-gray-600 dark:text-gray-200 shadow-sm me-3 rounded-circle d-flex align-items-center justify-content-center hover-urban transition-all" style="width: 40px; height: 40px;">
            <i class="bi bi-arrow-left fw-bold"></i>
          </router-link>
          <div class="d-flex flex-column">
            <h3 class="fw-bold text-dark dark:text-white mb-0">Tạo Mã Khuyến Mãi</h3>
            <p class="text-muted dark:text-gray-400 small mb-0 mt-1">Cấu hình các loại mã giảm giá và điều kiện áp dụng</p>
          </div>
        </div>
        <div class="col-md-4 text-md-end">
          <button type="submit" form="voucherForm" class="btn btn-urban text-white px-5 py-2.5 fw-bold shadow-sm rounded-pill w-100 w-md-auto" :disabled="isSaving">
            <span v-if="isSaving" class="spinner-border spinner-border-sm me-2"></span> 
            <i class="bi bi-floppy2-fill me-1" v-else></i> LƯU VOUCHER
          </button>
        </div>
      </div>

      <form id="voucherForm" @submit.prevent="submitVoucher" autocomplete="off">
        <div class="row g-4">
          
          <!-- CỘT TRÁI: CẤU HÌNH THÔNG TIN CHÍNH -->
          <div class="col-xl-8 col-lg-7">
            
            <!-- Box 1: Thông tin cơ bản -->
            <div class="card border-0 shadow-sm rounded-4 dark:bg-[#1a2533] p-4 mb-4">
              <h6 class="fw-bold mb-4 text-urban text-uppercase border-bottom dark:border-gray-700 pb-2"><i class="bi bi-info-circle me-2"></i>Thông tin cơ bản</h6>
              
              <div class="row g-4 mb-3">
                <div class="col-md-7">
                  <label class="form-label fw-bold text-dark dark:text-gray-200 small text-uppercase">Tên chiến dịch <span class="text-danger">*</span></label>
                  <input type="text" class="form-control bg-light dark:bg-[#212529] dark:text-white border-0 shadow-sm-hover" v-model="form.name" placeholder="VD: Khuyến mãi mừng Đại lễ 30/4" required>
                  <div class="text-danger small mt-1 fw-bold" v-if="errors.name">{{ errors.name[0] }}</div>
                </div>

                <div class="col-md-5">
                  <label class="form-label fw-bold text-dark dark:text-gray-200 small text-uppercase">Mã giảm giá (Code) <span class="text-danger">*</span></label>
                  <div class="input-group shadow-sm-hover">
                    <input type="text" class="form-control font-monospace text-uppercase bg-light dark:bg-[#212529] dark:text-white border-0" v-model="form.code" @input="formatCode" placeholder="VD: ZYRO304" required>
                    <button class="btn btn-outline-secondary border-0 bg-light dark:bg-[#212529]" type="button" @click="generateRandomCode" title="Tạo mã ngẫu nhiên"><i class="bi bi-magic text-urban"></i></button>
                  </div>
                  <div class="text-danger small mt-1 fw-bold" v-if="errors.code">{{ errors.code[0] }}</div>
                  <small class="text-muted d-block mt-1">Chỉ chứa chữ IN HOA và số viết liền.</small>
                </div>
              </div>
            </div>

            <!-- Box 2: Cấu hình Giá trị -->
            <div class="card border-0 shadow-sm rounded-4 dark:bg-[#1a2533] p-4 mb-4">
              <h6 class="fw-bold mb-4 text-urban text-uppercase border-bottom dark:border-gray-700 pb-2"><i class="bi bi-currency-dollar me-2"></i>Thiết lập giảm giá</h6>
              
              <div class="row g-4 align-items-start">
                <div class="col-md-3">
                  <label class="form-label fw-bold text-dark dark:text-gray-200 small text-uppercase">Loại giảm <span class="text-danger">*</span></label>
                  <select class="form-select bg-light dark:bg-[#212529] dark:text-white border-0 shadow-sm-hover" v-model="form.discount_type">
                    <option value="fixed">Tiền mặt (VNĐ)</option>
                    <option value="percent">Phần trăm (%)</option>
                  </select>
                </div>

                <div class="col-md-4">
                  <label class="form-label fw-bold text-dark dark:text-gray-200 small text-uppercase">Mức giảm <span class="text-danger">*</span></label>
                  <div class="input-group shadow-sm-hover">
                    <input type="number" class="form-control bg-light dark:bg-[#212529] dark:text-white border-0 fw-bold text-danger" v-model.number="form.discount_value" min="1" required>
                    <span class="input-group-text bg-light dark:bg-[#212529] border-0 text-muted">{{ form.discount_type === 'percent' ? '%' : '₫' }}</span>
                  </div>
                  <small class="text-danger fw-bold mt-1 d-block" v-if="form.discount_type === 'fixed' && form.discount_value > 0">{{ formatCurrency(form.discount_value) }}</small>
                  <div class="text-danger small mt-1 fw-bold" v-if="errors.discount_value">{{ errors.discount_value[0] }}</div>
                </div>

                <div class="col-md-5">
                  <label class="form-label fw-bold text-dark dark:text-gray-200 small text-uppercase">Đơn tối thiểu (Min Spend) <span class="text-danger">*</span></label>
                  <div class="input-group shadow-sm-hover">
                    <input type="number" class="form-control bg-light dark:bg-[#212529] dark:text-white border-0 fw-bold text-urban" v-model.number="form.min_spend" min="0" required>
                    <span class="input-group-text bg-light dark:bg-[#212529] border-0 text-muted">₫</span>
                  </div>
                  <small class="text-urban fw-bold mt-1 d-block" v-if="form.min_spend > 0">{{ formatCurrency(form.min_spend) }}</small>
                  <div class="text-danger small mt-1 fw-bold" v-if="errors.min_spend">{{ errors.min_spend[0] }}</div>
                </div>

                <!-- Giảm tối đa (Chỉ hiện khi chọn %) -->
                <div class="col-md-12" v-if="form.discount_type === 'percent'">
                   <div class="p-3 bg-light dark:bg-[#212529] border border-light-subtle dark:border-gray-700 rounded-3">
                     <label class="form-label fw-bold text-dark dark:text-gray-200 small text-uppercase">Giảm tối đa (Max Discount)</label>
                     <div class="input-group shadow-sm-hover w-50">
                       <input type="number" class="form-control border-0 bg-white dark:bg-[#1a2533]" v-model.number="form.max_discount_amount" min="0" placeholder="Bỏ trống nếu không giới hạn">
                       <span class="input-group-text bg-white dark:bg-[#1a2533] border-0 text-muted">₫</span>
                     </div>
                     <small class="text-muted d-block mt-2 fst-italic">Bỏ trống ô này nếu bạn muốn cho phép giảm % không giới hạn số tiền.</small>
                   </div>
                </div>
              </div>
            </div>

            <!-- Box 3: Áp dụng cho -->
            <div class="card border-0 shadow-sm rounded-4 dark:bg-[#1a2533] p-4">
              <h6 class="fw-bold mb-4 text-urban text-uppercase border-bottom dark:border-gray-700 pb-2"><i class="bi bi-box-seam me-2"></i>Phạm vi áp dụng</h6>
              <div class="row g-4">
                <div class="col-md-5">
                  <label class="form-label fw-bold text-dark dark:text-gray-200 small text-uppercase">Áp dụng cho <span class="text-danger">*</span></label>
                  <select class="form-select bg-light dark:bg-[#212529] dark:text-white border-0 shadow-sm-hover" v-model="form.apply_type">
                    <option value="all">Toàn bộ gian hàng</option>
                    <option value="specific_products">Sản phẩm chỉ định</option>
                    <option value="specific_categories">Danh mục chỉ định</option>
                  </select>
                </div>
                
                <div class="col-md-7" v-if="form.apply_type !== 'all'">
                  <label class="form-label fw-bold text-dark dark:text-gray-200 small text-uppercase">
                    Chọn {{ form.apply_type === 'specific_products' ? 'Sản phẩm' : 'Danh mục' }} <span class="text-danger">*</span>
                  </label>
                  
                  <div class="border border-secondary-subtle dark:border-gray-700 rounded-3 overflow-hidden bg-white dark:bg-[#212529] shadow-sm-hover">
                    <!-- Thanh tìm kiếm -->
                    <div class="p-2 border-bottom border-secondary-subtle dark:border-gray-700 bg-light dark:bg-[#1a2533]">
                      <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white dark:bg-[#212529] border-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control border-0 bg-white dark:bg-[#212529] dark:text-white shadow-none" v-model="searchCondition" :placeholder="form.apply_type === 'specific_products' ? 'Tìm tên sản phẩm...' : 'Tìm tên danh mục...'">
                      </div>
                    </div>
                    
                    <!-- Danh sách Scrollable -->
                    <div class="custom-scrollbar-y" style="max-height: 220px; overflow-y: auto;">
                      <!-- Danh sách Danh Mục -->
                      <template v-if="form.apply_type === 'specific_categories'">
                        <div v-for="cat in filteredCategories" :key="cat.id" 
                             class="px-3 py-2 border-bottom border-light-subtle dark:border-gray-700 d-flex align-items-center cursor-pointer hover-bg-light transition-all" 
                             @click="toggleCondition(cat.id)">
                          <input class="form-check-input m-0 me-3 cursor-pointer flex-shrink-0" type="checkbox" :checked="selectedConditions.includes(cat.id)">
                          <div class="bg-white border dark:border-gray-600 rounded shadow-sm d-flex align-items-center justify-content-center flex-shrink-0 me-3" style="width: 36px; height: 36px; padding: 2px;">
                             <img :src="getImageUrl(cat.thumbnail)" @error="handleImageError" class="w-100 h-100 object-fit-contain rounded">
                          </div>
                          <span class="text-dark dark:text-gray-200 fw-medium small text-truncate">{{ cat.name }}</span>
                        </div>
                        <div v-if="filteredCategories.length === 0" class="p-4 text-center text-muted small fst-italic">Không tìm thấy danh mục.</div>
                      </template>

                      <!-- Danh sách Sản Phẩm -->
                      <template v-if="form.apply_type === 'specific_products'">
                        <div v-for="prod in filteredProducts" :key="prod.id" 
                             class="px-3 py-2 border-bottom border-light-subtle dark:border-gray-700 d-flex align-items-center cursor-pointer hover-bg-light transition-all" 
                             @click="toggleCondition(prod.id)">
                          <input class="form-check-input m-0 me-3 cursor-pointer flex-shrink-0" type="checkbox" :checked="selectedConditions.includes(prod.id)">
                          <div class="bg-white border dark:border-gray-600 rounded shadow-sm d-flex align-items-center justify-content-center flex-shrink-0 me-3" style="width: 36px; height: 36px; padding: 2px;">
                             <img :src="getImageUrl(prod.thumbnail_image)" @error="handleImageError" class="w-100 h-100 object-fit-cover rounded">
                          </div>
                          <div class="d-flex flex-column overflow-hidden w-100">
                             <span class="text-dark dark:text-gray-200 fw-medium small text-truncate">{{ prod.name }}</span>
                             <span class="text-danger fw-bold mt-1" style="font-size: 0.7rem;">{{ formatCurrency(prod.base_price) }}</span>
                          </div>
                        </div>
                        <div v-if="filteredProducts.length === 0" class="p-4 text-center text-muted small fst-italic">Không tìm thấy sản phẩm.</div>
                      </template>
                    </div>
                    
                    <!-- Footer Count -->
                    <div class="p-2 bg-light dark:bg-[#1a2533] border-top border-secondary-subtle dark:border-gray-700 d-flex justify-content-between align-items-center">
                      <small class="text-muted fst-italic" style="font-size: 0.7rem;">Click vào dòng để chọn</small>
                      <small class="text-dark dark:text-gray-300 fw-bold">Đã chọn: <span class="text-urban fs-6 ms-1">{{ selectedConditions.length }}</span></small>
                    </div>
                  </div>
                  <div class="text-danger small mt-1 fw-bold" v-if="errors.conditions">{{ errors.conditions[0] }}</div>
                </div>

              </div>
            </div>

          </div>

          <!-- CỘT PHẢI: GIỚI HẠN, THỜI GIAN & TRẠNG THÁI -->
          <div class="col-xl-4 col-lg-5">
            
            <div class="card border-0 shadow-sm rounded-4 dark:bg-[#1a2533] p-4 mb-4">
              <h6 class="fw-bold mb-4 text-urban text-uppercase border-bottom dark:border-gray-700 pb-2"><i class="bi bi-clock-history me-2"></i>Thời gian & Lượt dùng</h6>
              
              <div class="mb-3">
                <label class="form-label fw-bold text-dark dark:text-gray-200 small text-uppercase">Bắt đầu lúc <span class="text-danger">*</span></label>
                <input type="datetime-local" class="form-control bg-light dark:bg-[#212529] dark:text-white border-0 shadow-sm-hover" v-model="form.start_time" required>
                <div class="text-danger small mt-1 fw-bold" v-if="errors.start_time">{{ errors.start_time[0] }}</div>
              </div>

              <!-- ĐÃ FIX: Gỡ bỏ bắt buộc nhập và thêm chú thích -->
              <div class="mb-4">
                <label class="form-label fw-bold text-dark dark:text-gray-200 small text-uppercase">Kết thúc lúc</label>
                <input type="datetime-local" class="form-control bg-light dark:bg-[#212529] dark:text-white border-0 shadow-sm-hover" v-model="form.end_time">
                <div class="text-danger small mt-1 fw-bold" v-if="errors.end_time">{{ errors.end_time[0] }}</div>
                <small class="text-success fw-bold d-block mt-1"><i class="bi bi-infinity"></i> Bỏ trống = Không bao giờ hết hạn</small>
              </div>

              <div class="mb-3">
                <label class="form-label fw-bold text-dark dark:text-gray-200 small text-uppercase">Tổng lượt sử dụng tối đa</label>
                <input type="number" class="form-control bg-light dark:bg-[#212529] dark:text-white border-0 shadow-sm-hover" v-model.number="form.usage_limit" min="1" placeholder="Bỏ trống = Không giới hạn">
              </div>

              <div class="mb-2">
                <label class="form-label fw-bold text-dark dark:text-gray-200 small text-uppercase">Giới hạn số lần dùng / 1 Khách <span class="text-danger">*</span></label>
                <input type="number" class="form-control bg-light dark:bg-[#212529] dark:text-white border-0 shadow-sm-hover fw-bold" v-model.number="form.usage_limit_per_user" min="1" required>
              </div>
            </div>

            <!-- Phân phối mã & Trạng thái -->
            <div class="card border-0 shadow-sm rounded-4 dark:bg-[#1a2533] p-4">
              <h6 class="fw-bold mb-4 text-urban text-uppercase border-bottom dark:border-gray-700 pb-2"><i class="bi bi-shield-check me-2"></i>Phân phối mã</h6>
              
              <div class="p-3 bg-light dark:bg-[#212529] border border-light-subtle dark:border-gray-700 rounded-3 mb-4 d-flex align-items-center justify-content-between transition-all hover-border-urban">
                <div>
                  <h6 class="mb-0 fw-bold text-dark dark:text-white" style="font-size: 0.9rem;">Mã Công Khai</h6>
                  <small class="text-muted" style="font-size: 0.75rem;">Ai cũng có thể thấy và thu thập.</small>
                </div>
                <div class="form-check form-switch m-0">
                  <input class="form-check-input fs-4 cursor-pointer m-0" type="checkbox" role="switch" v-model="form.is_public">
                </div>
              </div>

              <div>
                <label class="form-label fw-bold text-dark dark:text-gray-200 small text-uppercase">Trạng thái phát hành</label>
                <select class="form-select bg-light dark:bg-[#212529] dark:text-white border-0 shadow-sm-hover" v-model="form.status">
                  <option value="active">Kích hoạt (Cho phép dùng)</option>
                  <option value="hidden">Đang ẩn (Tạm ngưng)</option>
                </select>
              </div>
            </div>

          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useRouter } from 'vue-router';
import Swal from 'sweetalert2';
import axios from 'axios';
import defaultImage from '@/assets/images/defaults/placeholder.png';

const router = useRouter();
const isPageLoading = ref(true);
const isDataLoaded = ref(false);
const isSaving = ref(false);
const errors = ref({});

// Multi-select States
const allCategories = ref([]);
const allProducts = ref([]);
const searchCondition = ref('');
const selectedConditions = ref([]);

const generateRandomCode = () => {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    let result = '';
    for (let i = 0; i < 8; i++) {
        result += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    form.value.code = result;
};

const formatCode = (e) => {
    let val = e.target.value;
    val = val.toUpperCase().replace(/[^A-Z0-9]/g, '');
    form.value.code = val;
};

const formatCurrency = (val) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val || 0);

const getImageUrl = (path) => path ? `http://127.0.0.1:8000/storage/${path}` : defaultImage;
const handleImageError = (e) => { e.target.src = defaultImage; };

const form = ref({ 
  name: '', code: '', discount_type: 'percent', discount_value: 0, max_discount_amount: null, min_spend: 0,
  apply_type: 'all', usage_limit: null, usage_limit_per_user: 1, 
  start_time: '', end_time: '', is_public: true, status: 'active' 
});

const getHeaders = () => ({ 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('admin_token')}` });

const filteredCategories = computed(() => {
  if (!searchCondition.value) return allCategories.value;
  const q = searchCondition.value.toLowerCase();
  return allCategories.value.filter(c => c.name.toLowerCase().includes(q));
});

const filteredProducts = computed(() => {
  if (!searchCondition.value) return allProducts.value;
  const q = searchCondition.value.toLowerCase();
  return allProducts.value.filter(p => p.name.toLowerCase().includes(q) || p.slug.toLowerCase().includes(q));
});

const toggleCondition = (id) => {
  const idx = selectedConditions.value.indexOf(id);
  if (idx === -1) selectedConditions.value.push(id);
  else selectedConditions.value.splice(idx, 1);
};

// Theo dõi khi đổi kiểu áp dụng thì clear danh sách đã chọn
watch(() => form.value.apply_type, (newVal, oldVal) => {
  if (isDataLoaded.value && oldVal !== undefined && newVal !== oldVal) {
    selectedConditions.value = [];
    searchCondition.value = '';
  }
});

const fetchData = async () => {
  try {
    const [resCats, resProds] = await Promise.all([
      axios.get('http://127.0.0.1:8000/api/v1/admin/categories', { headers: getHeaders() }),
      axios.get('http://127.0.0.1:8000/api/v1/admin/products?status=published', { headers: getHeaders() })
    ]);

    const cats = Array.isArray(resCats.data?.data) ? resCats.data.data : [];
    allCategories.value = cats.filter(c => c.status === 'active' && !c.deleted_at);

    const prods = Array.isArray(resProds.data?.data?.data) ? resProds.data.data.data : (Array.isArray(resProds.data?.data) ? resProds.data.data : []);
    allProducts.value = prods;

    isDataLoaded.value = true;
  } catch (err) {
    console.error(err);
  } finally { isPageLoading.value = false; }
};

const submitVoucher = async () => {
  isSaving.value = true; errors.value = {};
  
  const payload = {
      ...form.value,
      conditions: selectedConditions.value
  };

  try {
    const res = await axios.post('http://127.0.0.1:8000/api/v1/admin/vouchers', payload, { headers: getHeaders() });
    Swal.fire({ icon: 'success', title: 'Thành công', text: res.data.message, timer: 1500, showConfirmButton: false }).then(() => {
      router.push({ name: 'admin-vouchers' });
    });
  } catch (e) {
    if (e.response && e.response.data && e.response.data.errors) {
       errors.value = e.response.data.errors;
       Swal.fire({ title: 'Dữ liệu không hợp lệ', text: 'Vui lòng kiểm tra các ô báo đỏ', icon: 'error' });
    } else { Swal.fire('Lỗi', e.response?.data?.message || 'Lỗi lưu Voucher.', 'error'); }
  } finally { isSaving.value = false; }
};

onMounted(() => fetchData());
</script>

<style scoped>
.text-urban { color: var(--color-c-hover, #547792) !important; }
.btn-urban { background-color: var(--color-c-hover, #547792); color: white; border: none; transition: 0.2s; }
.btn-urban:hover { background-color: var(--color-c-dark, #213448); color: white; transform: translateY(-2px); }

.shadow-sm-hover { transition: box-shadow 0.2s ease, border-color 0.2s ease; }
.shadow-sm-hover:focus-within { box-shadow: 0 4px 15px rgba(84, 119, 146, 0.1) !important; }
.form-control:focus, .form-select:focus { border-color: var(--color-c-hover, #547792); box-shadow: none !important; }

/* CSS cho Scrollable List */
.hover-bg-light:hover { background-color: rgba(84, 119, 146, 0.05) !important; }
html.dark .hover-bg-light:hover { background-color: rgba(255, 255, 255, 0.05) !important; }
.custom-scrollbar-y::-webkit-scrollbar { width: 4px; }
.custom-scrollbar-y::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar-y::-webkit-scrollbar-thumb { background: var(--color-c-light, #94B4C1); border-radius: 10px; }

/* Cấm bôi đen chữ */
.no-select { user-select: none !important; -webkit-user-select: none !important; }
.hover-border-urban:hover { border-color: var(--color-c-hover, #547792) !important; }
.cursor-pointer { cursor: pointer; }

.logo-shimmer { font-size: 3.5rem; font-weight: 900; letter-spacing: -1.5px; background: linear-gradient(120deg, var(--color-c-dark) 30%, var(--color-c-light) 50%, var(--color-c-dark) 70%); background-size: 200% auto; color: transparent; -webkit-background-clip: text; background-clip: text; animation: shine 1.5s linear infinite; }
@keyframes shine { to { background-position: 200% center; } }
.animation-fade-in { animation: fadeIn 0.4s ease-in-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>