<!-- frontend/src/components/client/SizeGuideModal.vue -->
<template>
  <transition name="modal-fade">
    <div v-if="show" class="zyro-modal-overlay" @click.self="closeModal">
      <div class="zyro-modal-dialog">
        <div class="zyro-modal-content rounded-4 border-0 shadow-xl overflow-hidden bg-white d-flex flex-column">
          
          <!-- HEADER -->
          <div class="modal-header border-bottom border-light-subtle p-3 px-4 bg-white sticky-top z-2 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-uppercase tracking-wider" style="color: var(--color-c-dark);">
              <i class="bi bi-rulers me-2"></i> Hướng Dẫn Chọn Kích Cỡ
            </h6>
            <button type="button" class="btn-close shadow-none" @click="closeModal"></button>
          </div>

          <!-- BODY: BỐ CỤC 2 CỘT TẬP TRUNG VÀO THÔNG SỐ (ĐÃ BỎ ẢNH THUMBNAIL) -->
          <div class="modal-body p-0 d-flex flex-column">
            
            <!-- THÔNG TIN SẢN PHẨM TRÊN CÙNG -->
            <div class="p-3 px-4 border-bottom border-light-subtle d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2" style="background-color: var(--color-c-effect);">
              <h6 class="fw-bold mb-0 line-clamp-1" style="color: var(--color-c-dark);">{{ product?.name || 'Sản phẩm ZYRO' }}</h6>
              <div class="small text-muted lh-sm text-nowrap">
                <span v-if="product?.fit_type === 'oversize'" class="badge bg-white text-dark border border-light-subtle shadow-sm">
                  <i class="bi bi-info-circle-fill me-1" style="color: var(--color-c-hover);"></i>Form <strong>Oversize</strong> (Rộng rãi)
                </span>
                <span v-else-if="product?.fit_type === 'slim_fit'" class="badge bg-white text-dark border border-light-subtle shadow-sm">
                  <i class="bi bi-info-circle-fill me-1" style="color: var(--color-c-hover);"></i>Form <strong>Slim Fit</strong> (Ôm dáng)
                </span>
                <span v-else class="badge bg-white text-dark border border-light-subtle shadow-sm">
                  <i class="bi bi-info-circle-fill me-1" style="color: var(--color-c-hover);"></i>Form <strong>Regular</strong> (Tiêu chuẩn)
                </span>
              </div>
            </div>

            <!-- NỘI DUNG CHÍNH: CHIA 2 CỘT RỘNG RÃI -->
            <div class="row g-0 flex-grow-1">
              
              <!-- CỘT TRÁI: CÔNG CỤ TÍNH SIZE TỰ ĐỘNG -->
              <div class="col-md-5 border-end border-light-subtle p-4 d-flex flex-column bg-white">
                <h6 class="fw-bold mb-4 text-uppercase tracking-wider text-dark d-flex align-items-center" style="font-size: 0.95rem;">
                  <i class="bi bi-magic me-2 fs-5" style="color: var(--color-c-hover);"></i> Công Cụ Tính Size
                </h6>

                <p class="text-muted small mb-4">Nhập số đo của bạn để ZYRO tính toán kích cỡ phù hợp nhất dựa trên dải size thực tế của sản phẩm này.</p>

                <!-- Form nhập liệu -->
                <div class="row g-3 mb-4">
                  <div class="col-12" v-if="!isKidsProduct">
                    <label class="form-label small fw-bold text-muted mb-1">Giới tính</label>
                    <select v-model="userGender" class="form-select border-light-subtle shadow-none py-2 fw-semibold">
                      <option value="Nam">Nam</option>
                      <option value="Nữ">Nữ</option>
                      <option value="Khác">Khác</option>
                    </select>
                  </div>
                  <div class="col-6">
                    <label class="form-label small fw-bold text-muted mb-1">Chiều cao (cm)</label>
                    <input type="number" v-model="userHeight"
                           class="form-control border-light-subtle shadow-none py-2 text-center fw-semibold fs-5" 
                           placeholder="VD: 170" />
                  </div>
                  <div class="col-6">
                    <label class="form-label small fw-bold text-muted mb-1">Cân nặng (kg)</label>
                    <input type="number" v-model="userWeight"
                           class="form-control border-light-subtle shadow-none py-2 text-center fw-semibold fs-5" 
                           placeholder="VD: 65" />
                  </div>
                </div>

                <!-- Vùng hiển thị lỗi -->
                <div class="text-center d-flex align-items-center justify-content-center mb-3" style="min-height: 24px;">
                  <transition name="fade">
                    <span v-if="errorMessage" class="text-danger small fw-bold">
                      <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ errorMessage }}
                    </span>
                  </transition>
                </div>

                <button @click="calculateSize" class="btn w-100 fw-bold py-3 text-white mb-4 zyro-btn-hover shadow-sm" style="background-color: var(--color-c-dark); font-size: 1.05rem;">
                  TÌM SIZE CỦA TÔI
                </button>

                <!-- KHUNG KẾT QUẢ GỢI Ý -->
                <div class="suggest-result-box rounded-4 text-center mt-auto p-4 d-flex flex-column justify-content-center" 
                     style="background-color: var(--color-c-effect); border: 1px dashed var(--color-c-light); min-height: 180px;">
                  
                  <template v-if="suggestedSize">
                    <p class="mb-2 text-muted small fw-bold text-uppercase tracking-wider">Kích cỡ gợi ý</p>
                    <div class="display-1 fw-black mb-2 transition-all" style="color: var(--color-c-hover); line-height: 1;">
                      {{ suggestedSize }}
                    </div>
                    <p class="small mb-0 fw-medium px-2 mt-2" :class="isWarningNote ? 'text-danger' : 'text-muted'">
                      {{ suggestedNote }}
                    </p>
                  </template>
                  
                  <template v-else>
                    <div class="opacity-50">
                      <i class="bi bi-person-bounding-box display-4 mb-3 d-block" style="color: var(--color-c-light);"></i>
                      <p class="mb-0 text-muted fw-medium small">
                        Kích cỡ gợi ý sẽ hiển thị ở đây<br>sau khi hệ thống tính toán.
                      </p>
                    </div>
                  </template>
                </div>

              </div>

              <!-- CỘT PHẢI: BẢNG SIZE CHI TIẾT -->
              <div class="col-md-7 p-4 d-flex flex-column bg-light bg-opacity-50 dark:bg-transparent custom-scrollbar-y" style="max-height: 700px; overflow-y: auto;">
                <h6 class="fw-bold mb-4 text-uppercase tracking-wider text-dark d-flex align-items-center" style="font-size: 0.95rem;">
                  <i class="bi bi-table me-2 fs-5" style="color: var(--color-c-hover);"></i> Bảng Thông Số Sản Phẩm
                </h6>

                <!-- Ưu tiên ảnh bảng size từ Admin -->
                <div v-if="product?.size_guide_url" class="flex-grow-1 d-flex align-items-center justify-content-center bg-white rounded-3 overflow-hidden border border-light-subtle p-2 shadow-sm">
                  <img :src="product.size_guide_url" alt="Bảng size" class="img-fluid rounded object-fit-contain w-100 h-100" />
                </div>
                
                <!-- Bảng HTML Fallback (Đã chia ra 5 trường hợp thông minh) -->
                <div v-else class="flex-grow-1 d-flex flex-column">
                  <div class="alert alert-secondary border-0 bg-white shadow-sm small text-muted mb-4">
                    <i class="bi bi-info-circle me-1"></i> Dưới đây là bảng size tiêu chuẩn mang tính chất tham khảo. Kích thước thực tế có thể xê dịch từ 1-2cm tùy thuộc vào thiết kế.
                  </div>

                  <div class="table-responsive rounded-3 border border-light-subtle flex-grow-1 bg-white dark:bg-[#1a2533] shadow-sm">
                    <table class="table table-hover mb-0 align-middle text-center m-0">
                      <thead class="table-light">
                        <tr class="text-muted text-uppercase" style="font-size: 0.85rem; letter-spacing: 0.05em;">
                          <th class="py-3 border-0">Size</th>
                          <th class="py-3 border-0">Chiều Cao (cm)</th>
                          <th class="py-3 border-0">Cân Nặng (kg)</th>
                        </tr>
                      </thead>

                      <!-- BẢNG 1: TRẺ EM THEO CHIỀU CAO (110, 120...) -->
                      <tbody v-if="detectedSizeType === 'kids_height'" class="border-top border-light-subtle">
                        <tr><td class="fw-bold py-3 text-dark fs-6">110</td><td>100 - 110</td><td>15 - 20kg</td></tr>
                        <tr><td class="fw-bold py-3 text-dark fs-6">120</td><td>110 - 120</td><td>20 - 25kg</td></tr>
                        <tr><td class="fw-bold py-3 text-dark fs-6">130</td><td>120 - 130</td><td>25 - 30kg</td></tr>
                        <tr><td class="fw-bold py-3 text-dark fs-6">140</td><td>130 - 140</td><td>30 - 35kg</td></tr>
                        <tr><td class="fw-bold py-3 text-dark fs-6">150</td><td>140 - 150</td><td>35 - 45kg</td></tr>
                      </tbody>

                      <!-- BẢNG 2: TRẺ EM THEO CHỮ (S, M, L...) -->
                      <tbody v-else-if="detectedSizeType === 'kids_letter'" class="border-top border-light-subtle">
                        <tr><td class="fw-bold py-3 text-dark fs-6">S</td><td>100 - 110</td><td>15 - 20kg</td></tr>
                        <tr><td class="fw-bold py-3 text-dark fs-6">M</td><td>110 - 120</td><td>20 - 25kg</td></tr>
                        <tr><td class="fw-bold py-3 text-dark fs-6">L</td><td>120 - 130</td><td>25 - 30kg</td></tr>
                        <tr><td class="fw-bold py-3 text-dark fs-6">XL</td><td>130 - 140</td><td>30 - 35kg</td></tr>
                        <tr><td class="fw-bold py-3 text-dark fs-6">XXL</td><td>140 - 150</td><td>35 - 40kg</td></tr>
                      </tbody>

                      <!-- BẢNG 3: QUẦN TRẺ EM THEO SỐ (24, 25, 26...) -->
                      <tbody v-else-if="detectedSizeType === 'kids_number'" class="border-top border-light-subtle">
                        <tr><td class="fw-bold py-3 text-dark fs-6">24</td><td>100 - 110</td><td>15 - 20kg</td></tr>
                        <tr><td class="fw-bold py-3 text-dark fs-6">25</td><td>110 - 120</td><td>20 - 25kg</td></tr>
                        <tr><td class="fw-bold py-3 text-dark fs-6">26</td><td>120 - 130</td><td>25 - 30kg</td></tr>
                        <tr><td class="fw-bold py-3 text-dark fs-6">27</td><td>130 - 140</td><td>30 - 35kg</td></tr>
                        <tr><td class="fw-bold py-3 text-dark fs-6">28</td><td>140 - 150</td><td>35 - 40kg</td></tr>
                      </tbody>

                      <!-- BẢNG 4: QUẦN NGƯỜI LỚN THEO SỐ (28, 29, 30...) -->
                      <tbody v-else-if="detectedSizeType === 'adult_number'" class="border-top border-light-subtle">
                        <tr><td class="fw-bold py-3 text-dark fs-6">28</td><td>160 - 165</td><td>50 - 55kg</td></tr>
                        <tr><td class="fw-bold py-3 text-dark fs-6">29</td><td>165 - 170</td><td>55 - 60kg</td></tr>
                        <tr><td class="fw-bold py-3 text-dark fs-6">30</td><td>170 - 175</td><td>60 - 65kg</td></tr>
                        <tr><td class="fw-bold py-3 text-dark fs-6">31</td><td>170 - 175</td><td>65 - 70kg</td></tr>
                        <tr><td class="fw-bold py-3 text-dark fs-6">32</td><td>175 - 180</td><td>70 - 75kg</td></tr>
                        <tr><td class="fw-bold py-3 text-dark fs-6">33</td><td>180 - 185</td><td>75 - 80kg</td></tr>
                        <tr><td class="fw-bold py-3 text-dark fs-6">34</td><td>185 - 190</td><td>80 - 85kg</td></tr>
                      </tbody>

                      <!-- BẢNG 5: ÁO NGƯỜI LỚN THEO CHỮ (S, M, L...) -->
                      <tbody v-else class="border-top border-light-subtle">
                        <tr><td class="fw-bold py-3 text-dark fs-6">S</td><td>150 - 160</td><td>40 - 50kg</td></tr>
                        <tr><td class="fw-bold py-3 text-dark fs-6">M</td><td>160 - 168</td><td>50 - 60kg</td></tr>
                        <tr><td class="fw-bold py-3 text-dark fs-6">L</td><td>168 - 175</td><td>60 - 70kg</td></tr>
                        <tr><td class="fw-bold py-3 text-dark fs-6">XL</td><td>175 - 185</td><td>70 - 85kg</td></tr>
                        <tr><td class="fw-bold py-3 text-dark fs-6">XXL</td><td>180 - 190</td><td>85 - 95kg</td></tr>
                      </tbody>
                    </table>
                  </div>
                </div>

              </div>

            </div>
          </div>

        </div>
      </div>
    </div>
  </transition>
</template>

<script setup>
import { ref, watch, computed, nextTick } from 'vue';
import axios from '@/utils/axios';

const props = defineProps({
  show: { type: Boolean, default: false },
  product: { type: Object, default: () => ({}) }
});

const emit = defineEmits(['close']);

const userGender = ref('Nam');
const userHeight = ref('');
const userWeight = ref('');
const suggestedSize = ref('');
const suggestedNote = ref('');
const errorMessage = ref('');
const isWarningNote = ref(false);

// ==========================================
// 1. NHẬN DIỆN SẢN PHẨM TRẺ EM / NGƯỜI LỚN
// ==========================================
const isKidsProduct = computed(() => {
  const name = (props.product?.name || '').toLowerCase();
  const cat = (props.product?.category?.name || '').toLowerCase();
  return name.includes('trẻ em') || name.includes('kid') || name.includes('bé') || cat.includes('trẻ em') || cat.includes('kid');
});

// ==========================================
// 2. QUÉT DATABASE LẤY DẢI SIZE THỰC TẾ
// ==========================================
const actualAvailableSizes = computed(() => {
  const sizes = new Set();
  if (props.product?.colors) {
    props.product.colors.forEach(colorObj => {
      if (colorObj.sizes) {
        colorObj.sizes.forEach(s => sizes.add(String(s.name).trim().toUpperCase()));
      }
    });
  }
  return Array.from(sizes);
});

// ==========================================
// 3. MA TRẬN 2 LỚP: XÁC ĐỊNH LOẠI BẢNG SIZE
// ==========================================
const detectedSizeType = computed(() => {
  const sizes = actualAvailableSizes.value;
  const isKids = isKidsProduct.value;

  const hasLetters = sizes.some(s => ['S', 'M', 'L', 'XL', 'XXL'].includes(s));
  const numSizes = sizes.map(s => parseInt(s)).filter(n => !isNaN(n));
  const hasNumbers = numSizes.length > 0;

  if (isKids) {
    if (hasLetters) return 'kids_letter';
    if (hasNumbers) {
      const maxSize = Math.max(...numSizes);
      if (maxSize >= 80) return 'kids_height'; // Ví dụ size 110, 120
      return 'kids_number'; // Ví dụ quần size 24, 25
    }
    return 'kids_height'; // Fallback cho trẻ em
  } else {
    if (hasLetters) return 'adult_letter';
    if (hasNumbers) return 'adult_number';
    return 'adult_letter'; // Fallback cho người lớn
  }
});

// ==========================================
// LẤY PROFILE NGẦM 
// ==========================================
const fetchUserProfile = async () => {
  if (isKidsProduct.value) {
    // Không dùng thông số người lớn cho trẻ em
    userHeight.value = '';
    userWeight.value = '';
    return;
  }

  try {
    const response = await axios.get('/client/user/profile');
    if (response.data && response.data.success && response.data.data) {
      const u = response.data.data;
      
      let uGender = 'Khác';
      if (u.gender) {
        const g = String(u.gender).toLowerCase().trim();
        if (g === 'male' || g === 'nam' || g === '1') uGender = 'Nam';
        else if (g === 'female' || g === 'nữ' || g === 'nu' || g === '0') uGender = 'Nữ';
      }

      let pGender = 'Khác'; 
      if (props.product?.gender) {
        const pg = String(props.product.gender).toLowerCase().trim();
        if (pg === 'male' || pg === 'nam') pGender = 'Nam';
        else if (pg === 'female' || pg === 'nữ' || pg === 'nu') pGender = 'Nữ';
      }

      const isGenderMatch = (uGender === pGender) || (pGender === 'Khác');

      if (isGenderMatch) {
        userGender.value = uGender;
        let hasData = false;

        if (u.height_cm) { userHeight.value = parseInt(u.height_cm, 10); hasData = true; }
        if (u.weight_kg) { userWeight.value = parseFloat(u.weight_kg); hasData = true; }

        if (hasData && userHeight.value && userWeight.value) {
            await nextTick();
            calculateSize();
        }
      } else {
        userGender.value = pGender !== 'Khác' ? pGender : 'Nữ'; 
      }
    }
  } catch (error) {
    // Bỏ qua
  }
};

const closeModal = () => {
  emit('close');
};

watch(() => props.show, (newVal) => {
  if (newVal) {
    suggestedSize.value = '';
    suggestedNote.value = '';
    errorMessage.value = '';
    userHeight.value = ''; 
    userWeight.value = '';
    isWarningNote.value = false;

    if (props.product?.gender && !isKidsProduct.value) {
      const pg = String(props.product.gender).toLowerCase().trim();
      if (pg === 'male' || pg === 'nam') userGender.value = 'Nam';
      else if (pg === 'female' || pg === 'nữ' || pg === 'nu') userGender.value = 'Nữ';
      else userGender.value = 'Khác';
    } else {
      userGender.value = 'Nam';
    }
    
    fetchUserProfile();
  }
}, { immediate: true });

// ==========================================
// TÍNH TOÁN THEO TỪNG LOẠI BẢNG SIZE
// ==========================================
const calculateSize = () => {
  errorMessage.value = '';
  suggestedSize.value = '';
  suggestedNote.value = '';
  isWarningNote.value = false;

  const h = parseFloat(userHeight.value);
  const w = parseFloat(userWeight.value);

  if (!userHeight.value || !userWeight.value || isNaN(h) || isNaN(w)) {
    errorMessage.value = 'Vui lòng nhập đầy đủ số đo!';
    return;
  }

  const sType = detectedSizeType.value;
  let rawSuggestion = '';

  // VALIDATE & TÍNH TOÁN CHO TỪNG NHÓM
  if (sType.startsWith('kids')) {
    if (h < 50 || h > 160) { errorMessage.value = 'Đồ trẻ em: Chiều cao từ 50cm - 160cm.'; return; }
    if (w < 5 || w > 60) { errorMessage.value = 'Đồ trẻ em: Cân nặng từ 5kg - 60kg.'; return; }

    if (sType === 'kids_height') {
      if (h < 110 || w < 20) rawSuggestion = '110';
      else if (h < 120 || w < 25) rawSuggestion = '120';
      else if (h < 130 || w < 30) rawSuggestion = '130';
      else if (h < 140 || w < 35) rawSuggestion = '140';
      else rawSuggestion = '150';
    } 
    else if (sType === 'kids_letter') {
      if (h < 110 || w < 20) rawSuggestion = 'S';
      else if (h < 120 || w < 25) rawSuggestion = 'M';
      else if (h < 130 || w < 30) rawSuggestion = 'L';
      else if (h < 140 || w < 35) rawSuggestion = 'XL';
      else rawSuggestion = 'XXL';
    }
    else if (sType === 'kids_number') {
      if (h < 110 || w < 20) rawSuggestion = '24';
      else if (h < 120 || w < 25) rawSuggestion = '25';
      else if (h < 130 || w < 30) rawSuggestion = '26';
      else if (h < 140 || w < 35) rawSuggestion = '27';
      else rawSuggestion = '28';
    }
  } 
  else {
    // NGƯỜI LỚN
    if (h < 130 || h > 250) { errorMessage.value = 'Chiều cao người lớn: 130cm - 250cm.'; return; }
    if (w < 30 || w > 150) { errorMessage.value = 'Cân nặng người lớn: 30kg - 150kg.'; return; }

    if (sType === 'adult_number') {
      if (userGender.value === 'Nữ') {
        if (w <= 45) rawSuggestion = '26';
        else if (w <= 50) rawSuggestion = '27';
        else if (w <= 55) rawSuggestion = '28';
        else if (w <= 60) rawSuggestion = '29';
        else rawSuggestion = '30';
      } else {
        if (w < 55) rawSuggestion = '28';
        else if (w < 60) rawSuggestion = '29';
        else if (w < 65) rawSuggestion = '30';
        else if (w < 70) rawSuggestion = '31';
        else if (w < 75) rawSuggestion = '32';
        else if (w < 80) rawSuggestion = '33';
        else rawSuggestion = '34';
      }
    } 
    else if (sType === 'adult_letter') {
      if (userGender.value === 'Nữ') {
        if (h <= 155 && w <= 45) rawSuggestion = 'S';
        else if ((h > 155 && h <= 162) || (w > 45 && w <= 52)) rawSuggestion = 'M';
        else if ((h > 162 && h <= 170) || (w > 52 && w <= 60)) rawSuggestion = 'L';
        else if ((h > 170 && h <= 175) || (w > 60 && w <= 70)) rawSuggestion = 'XL';
        else rawSuggestion = 'XXL';
      } else {
        if (h < 160 && w < 50) rawSuggestion = 'S';
        else if ((h >= 160 && h < 170) || (w >= 50 && w < 62)) rawSuggestion = 'M';
        else if ((h >= 170 && h < 178) || (w >= 62 && w < 75)) rawSuggestion = 'L';
        else if ((h >= 178 && h < 185) || (w >= 75 && w < 85)) rawSuggestion = 'XL';
        else rawSuggestion = 'XXL';
      }
    }
  }

  // ==========================================
  // ĐỐI CHIẾU THỰC TẾ & XUẤT KẾT QUẢ
  // ==========================================
  const available = actualAvailableSizes.value;
  
  if (available.length > 0 && !available.includes(rawSuggestion)) {
    suggestedSize.value = rawSuggestion;
    isWarningNote.value = true;
    suggestedNote.value = `ZYRO gợi ý size ${rawSuggestion}, nhưng có vẻ sản phẩm này không có sẵn kích cỡ này hoặc dùng hệ đo lường khác. Vui lòng xem bảng bên phải.`;
  } else {
    suggestedSize.value = rawSuggestion;
    isWarningNote.value = false;
    
    if (props.product?.fit_type === 'oversize') {
      suggestedNote.value = `Sản phẩm form Oversize. Kích cỡ này sẽ mặc rộng rãi thoải mái.`;
    } else if (props.product?.fit_type === 'slim_fit') {
      suggestedNote.value = `Sản phẩm form ôm dáng. Cân nhắc tăng size nếu muốn mặc thoải mái.`;
    } else {
      suggestedNote.value = `Dựa trên số đo và dải size thực tế, đây là kích cỡ vừa vặn nhất!`;
    }
  }
};
</script>

<style scoped>
/* OVERLAY MODAL */
.zyro-modal-overlay {
  position: fixed;
  inset: 0;
  z-index: 2100; 
  background-color: rgba(18, 20, 22, 0.75);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
}

/* DIALOG RỘNG CHUẨN 2 CỘT TẬP TRUNG VÀO THÔNG SỐ */
.zyro-modal-dialog {
  width: 100%;
  max-width: 1000px; 
}

/* Modal Animation */
.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity 0.25s ease; }
.modal-fade-enter-active .zyro-modal-dialog, .modal-fade-leave-active .zyro-modal-dialog { transition: transform 0.25s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; }
.modal-fade-enter-from .zyro-modal-dialog, .modal-fade-leave-to .zyro-modal-dialog { transform: scale(0.95) translateY(20px); }

/* TEXT & UTILS */
.fw-black { font-weight: 900; }
.tracking-wider { letter-spacing: 0.05em; }
.line-clamp-1 { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }

/* BUTTON HOVER */
.zyro-btn-hover { transition: all 0.2s ease; }
.zyro-btn-hover:hover { background-color: var(--color-c-hover) !important; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(33, 52, 72, 0.15); }

/* FADE TRANSITION */
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

/* INPUT FOCUS */
.form-control:focus, .form-select:focus { border-color: var(--color-c-hover) !important; box-shadow: 0 0 0 0.25rem rgba(148, 180, 193, 0.25) !important; }

/* SCROLLBAR */
.custom-scrollbar-y::-webkit-scrollbar { width: 4px; }
.custom-scrollbar-y::-webkit-scrollbar-thumb { background: var(--color-c-light); border-radius: 10px; }

/* DARK MODE OVERRIDES */
[data-bs-theme="dark"] .zyro-modal-content { background-color: #1a2533 !important; border: 1px solid #2b3035 !important; }
[data-bs-theme="dark"] .modal-header, [data-bs-theme="dark"] .border-bottom, [data-bs-theme="dark"] .border-end { border-color: #2b3035 !important; }
[data-bs-theme="dark"] .bg-light { background-color: #121416 !important; }
[data-bs-theme="dark"] .btn-close { filter: invert(1) brightness(200%); }
[data-bs-theme="dark"] .form-control, [data-bs-theme="dark"] .form-select { background-color: #121416 !important; border-color: #2b3035 !important; color: #fff !important; }
[data-bs-theme="dark"] .suggest-result-box { background-color: #212c3a !important; border-color: #374151 !important; }
[data-bs-theme="dark"] .table-light { background-color: #212c3a !important; }
[data-bs-theme="dark"] .table { color: #adb5bd !important; }
[data-bs-theme="dark"] .text-dark { color: #fff !important; }
</style>