<template>
  <div class="review-page-wrapper pb-5 mb-5" style="padding-top: 100px;">
    <div class="zyro-container">
      
      <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small fw-semibold text-uppercase" style="letter-spacing: 0.5px;">
          <li class="breadcrumb-item"><router-link to="/" class="text-muted text-decoration-none">Trang chủ</router-link></li>
          <li class="breadcrumb-item"><router-link to="/user/orders" class="text-muted text-decoration-none">Đơn mua hàng</router-link></li>
          <li class="breadcrumb-item active text-c-dark" aria-current="page">Đánh giá sản phẩm</li>
        </ol>
      </nav>

      <div class="row g-4 g-lg-5">
        
        <div class="col-lg-3">
          <UserSidebar />
        </div>

        <div class="col-lg-9">
          <div class="card border-0 shadow-sm rounded-4 dark:bg-[#1a2533] p-4 p-md-5">
            <div class="d-flex justify-content-between align-items-center border-bottom dark:border-gray-700 pb-3 pt-2 mb-4">
              <h4 class="fw-bold text-c-dark dark:text-white m-0">
                {{ isEditingMode ? 'Xem & Cập Nhật Đánh Giá' : 'Đánh Giá Sản Phẩm' }}
              </h4>
              <router-link to="/user/orders" class="btn btn-sm btn-light dark:bg-[#2b3035] dark:text-gray-300 rounded-pill px-3 fw-bold border">
                 Quay lại
              </router-link>
            </div>

            <!-- ============================================== -->
            <!-- ĐÃ FIX: SKELETON LOADING ĐỒNG BỘ FORM ĐÁNH GIÁ -->
            <!-- ============================================== -->
            <div v-if="isLoading" class="pe-none">
               <div v-for="i in 2" :key="'rskel'+i" class="mb-4 bg-light dark:bg-[#212529] p-3 p-md-4 rounded-4 border dark:border-gray-700 shadow-sm">
                 <div class="d-flex align-items-center gap-3 mb-4 border-bottom dark:border-gray-600 pb-3">
                   <div class="shimmer rounded-3" style="width: 65px; height: 85px;"></div>
                   <div>
                     <div class="shimmer rounded mb-2" style="width: 200px; height: 18px;"></div>
                     <div class="shimmer rounded" style="width: 120px; height: 14px;"></div>
                   </div>
                 </div>
                 <div class="mb-4 d-flex flex-column align-items-center">
                    <div class="d-flex gap-2 mb-2">
                       <div class="shimmer rounded-circle" style="width: 35px; height: 35px;" v-for="s in 5" :key="'s'+s"></div>
                    </div>
                    <div class="shimmer rounded" style="width: 80px; height: 14px;"></div>
                 </div>
                 <div class="mb-4">
                    <div class="shimmer rounded-3 w-100" style="height: 80px;"></div>
                 </div>
                 <div class="mb-4 d-flex gap-3">
                    <div class="shimmer rounded-3" style="width: 90px; height: 90px;"></div>
                 </div>
                 <div class="row g-3 bg-white dark:bg-[#1a2533] p-3 rounded-4 border dark:border-gray-600">
                    <div class="col-12">
                       <div class="shimmer rounded mb-2" style="width: 150px; height: 16px;"></div>
                       <div class="shimmer rounded-pill w-100" style="height: 38px;"></div>
                    </div>
                 </div>
               </div>
            </div>

            <div v-else-if="itemsToReview.length === 0" class="text-center py-5">
              <i class="bi bi-star-fill text-warning mb-3" style="font-size: 3rem;"></i>
              <h5 class="fw-bold dark:text-white">Tuyệt vời! Bạn đã hoàn thành tất cả đánh giá.</h5>
              <router-link to="/user/orders" class="btn btn-urban rounded-pill mt-3 px-4 shadow-sm text-white">Quay lại Đơn hàng</router-link>
            </div>

            <form v-else @submit.prevent="submitReviews">
              <div v-for="(item, index) in itemsToReview" :key="item.id" class="mb-4 bg-light dark:bg-[#212529] p-3 p-md-4 rounded-4 border dark:border-gray-700 shadow-sm-hover transition-all" :class="{'border-urban': item.is_reviewed}">
                
                <div class="d-flex align-items-center gap-3 mb-4 border-bottom dark:border-gray-600 pb-3 position-relative">
                  <span v-if="item.is_reviewed" class="position-absolute top-0 end-0 badge bg-success text-white px-2 py-1"><i class="bi bi-check-circle me-1"></i> Đã đánh giá</span>
                  
                  <img :src="getImageUrl(item.variant_image)" class="rounded-3 border object-fit-cover shadow-sm" style="width: 65px; height: 85px;" @error="e => e.target.src='/client_placeholder.png'">
                  <div>
                    <h6 class="fw-bold mb-1 dark:text-gray-200" style="font-size: 1rem;">{{ item.product_name }}</h6>
                    <span class="badge bg-white text-muted border dark:bg-[#1a2533] dark:border-gray-600 fw-normal px-2 py-1">Phân loại: {{ parseAttributes(item.variant_attributes) }}</span>
                  </div>
                </div>

                <div class="mb-4 text-center">
                  <div class="d-flex justify-content-center gap-2 mb-2">
                    <i v-for="star in 5" :key="star" 
                       class="bi fs-1 cursor-pointer transition-all star-icon"
                       :class="(forms[index].rating >= star) ? 'bi-star-fill text-warning' : 'bi-star text-secondary opacity-50'"
                       @click="forms[index].rating = star">
                    </i>
                  </div>
                  <div class="fw-bold text-urban text-uppercase small tracking-widest">{{ getRatingLabel(forms[index].rating) }}</div>
                </div>

                <div class="mb-4">
                  <textarea class="form-control custom-input" rows="3" v-model="forms[index].comment" placeholder="Sản phẩm tuyệt vời, chất liệu rất ưng ý..."></textarea>
                </div>

                <div class="mb-4">
                  <label class="form-label small fw-bold text-muted mb-2"><i class="bi bi-images me-1"></i> Hình ảnh thực tế (Tối đa 5 ảnh)</label>
                  <div class="small text-muted mb-2 fst-italic" v-if="item.is_reviewed">Nếu bạn chọn ảnh mới, các ảnh cũ sẽ bị thay thế.</div>
                  
                  <div class="d-flex gap-3 flex-wrap mt-1">
                    <div v-for="(preview, imgIdx) in forms[index].previewImages" :key="imgIdx" class="position-relative border rounded-3 overflow-hidden bg-white shadow-sm" style="width: 90px; height: 90px;">
                      <img :src="preview" class="w-100 h-100 object-fit-cover">
                      <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 rounded-circle p-0 d-flex align-items-center justify-content-center m-1 shadow" style="width: 22px; height: 22px;" @click="removeImage(index, imgIdx)">
                        <i class="bi bi-x" style="font-size: 14px;"></i>
                      </button>
                    </div>

                    <label v-if="forms[index].images.length < 5 && forms[index].previewImages.length < 5" class="border border-dashed dark:border-gray-600 rounded-3 d-flex flex-column align-items-center justify-content-center cursor-pointer hover-bg-effect transition-all" style="width: 90px; height: 90px; background-color: rgba(0,0,0,0.02);">
                      <i class="bi bi-plus-lg fs-3 text-muted"></i>
                      <span class="small text-muted mt-1" style="font-size: 0.65rem;">Tải lên</span>
                      <input type="file" multiple accept="image/*" class="d-none" @change="onFileChange($event, index)">
                    </label>
                  </div>
                </div>

                <div class="row g-3 bg-white dark:bg-[#1a2533] p-3 rounded-4 border dark:border-gray-600 shadow-sm">
                  <div class="col-12 col-md-6 mb-2">
                     <span class="small fw-bold text-muted mb-2 d-block">Độ vừa vặn (Tùy chọn):</span>
                     <div class="d-flex gap-2">
                       <label v-for="fit in ['Chật', 'Vừa vặn', 'Rộng']" :key="fit" class="fit-radio flex-grow-1 text-center border rounded-pill py-2 cursor-pointer transition-all" :class="{'active': forms[index].fit_feedback === fit}">
                         <input type="radio" v-model="forms[index].fit_feedback" :value="fit" class="d-none"> {{ fit }}
                       </label>
                     </div>
                  </div>

                  <div class="col-12 col-md-6">
                    <div class="row g-2">
                       <div class="col-6">
                         <label class="small text-muted mb-1">Chiều cao (cm)</label>
                         <input type="number" class="form-control custom-input py-2 px-3" v-model="forms[index].reviewer_height" placeholder="170">
                       </div>
                       <div class="col-6">
                         <label class="small text-muted mb-1">Cân nặng (kg)</label>
                         <input type="number" class="form-control custom-input py-2 px-3" v-model="forms[index].reviewer_weight" placeholder="65">
                       </div>
                    </div>
                  </div>
                </div>

                <!-- Phản hồi từ Admin (nếu có) -->
                <div v-if="forms[index].admin_reply" class="mt-3 p-3 bg-urban bg-opacity-10 border border-urban border-opacity-25 rounded-3">
                   <span class="fw-bold text-urban small"><i class="bi bi-reply-all-fill me-1"></i>Phản hồi từ ZYRO:</span>
                   <p class="mb-0 small text-dark dark:text-gray-200 mt-1">{{ forms[index].admin_reply }}</p>
                </div>

              </div>

              <div class="text-end border-top dark:border-gray-700 pt-4 mt-4 position-sticky bottom-0 bg-white dark:bg-[#1a2533] py-3 z-index-2 shadow-sm-top" style="margin-left: -3rem; margin-right: -3rem; padding-left: 3rem; padding-right: 3rem;">
                <button type="submit" class="btn btn-urban btn-lg px-5 rounded-pill fw-bold shadow-lg hover-transform text-white text-uppercase tracking-widest" :disabled="isSubmitting">
                  <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-2"></span>
                  {{ isEditingMode ? 'Cập Nhật Đánh Giá' : 'Gửi Đánh Giá Ngay' }}
                </button>
              </div>
            </form>

          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '@/utils/axios';
import { ZyroSwal } from '@/components/client/ZyroSwal';
import UserSidebar from '@/components/client/UserSidebar.vue';

const route = useRoute();
const router = useRouter();
const orderId = route.query.order_id;

const isLoading = ref(true);
const isSubmitting = ref(false);
const itemsToReview = ref([]);
const forms = ref([]);

const isEditingMode = computed(() => {
  return itemsToReview.value.some(item => item.is_reviewed);
});

const getImageUrl = (path) => {
  if (!path) return '/client_placeholder.png';
  if (path.startsWith('http')) return path;
  return import.meta.env.VITE_API_BASE_URL.replace('/api/v1', '/storage/') + path;
};

const parseAttributes = (jsonStr) => {
  if(!jsonStr) return 'Mặc định';
  if(Array.isArray(jsonStr)) return jsonStr.join(' - ');
  if(typeof jsonStr === 'string') {
     try { 
         const parsed = JSON.parse(jsonStr);
         return Array.isArray(parsed) ? parsed.join(' - ') : parsed;
     } catch(e) { return jsonStr; }
  }
  return 'Mặc định';
};

const fetchReviewItems = async () => {
  if (!orderId) {
    router.push('/user/orders'); return;
  }
  try {
    const res = await api.get(`/client/user/reviews/order/${orderId}`);
    itemsToReview.value = res.data.data;
    
    forms.value = itemsToReview.value.map(item => {
      const oldData = item.review_data || {};
      
      let oldImages = [];
      if (oldData.images && Array.isArray(oldData.images)) {
         oldImages = oldData.images.map(img => getImageUrl(img));
      }

      return {
        product_id: item.product_id,
        variant_name: parseAttributes(item.variant_attributes),
        rating: oldData.rating || 5,
        comment: oldData.comment || '',
        images: [], 
        previewImages: oldImages, 
        fit_feedback: oldData.fit_feedback || 'Vừa vặn',
        reviewer_height: oldData.reviewer_height || null,
        reviewer_weight: oldData.reviewer_weight || null,
        admin_reply: oldData.admin_reply || null,
        is_reviewed: item.is_reviewed
      };
    });

  } catch (error) {
    const errorMsg = error.response?.data?.message || 'Không thể tải danh sách sản phẩm cần đánh giá';
    ZyroSwal.toastError(errorMsg);
    setTimeout(() => { router.push('/user/orders'); }, 2500);
  } finally {
    isLoading.value = false;
  }
};

const getRatingLabel = (star) => {
  const labels = ['Tệ', 'Không hài lòng', 'Bình thường', 'Hài lòng', 'Tuyệt vời'];
  return labels[star - 1] || 'Vui lòng chọn';
};

const onFileChange = (event, index) => {
  const files = Array.from(event.target.files);
  const form = forms.value[index];

  if (form.is_reviewed && form.images.length === 0) {
      form.previewImages = []; 
  }

  if (form.images.length + files.length > 5) {
    ZyroSwal.toastError('Tối đa 5 ảnh mỗi sản phẩm');
    return;
  }

  files.forEach(file => {
    if (!file.type.startsWith('image/')) return;
    if (file.size > 5 * 1024 * 1024) { ZyroSwal.toastError(`Ảnh ${file.name} vượt quá 5MB`); return; }
    form.images.push(file);
    
    const reader = new FileReader();
    reader.onload = (e) => { form.previewImages.push(e.target.result); };
    reader.readAsDataURL(file);
  });
  event.target.value = ''; 
};

const removeImage = (formIdx, imgIdx) => {
  forms.value[formIdx].images.splice(imgIdx, 1);
  forms.value[formIdx].previewImages.splice(imgIdx, 1);
};

const submitReviews = async () => {
  isSubmitting.value = true;
  ZyroSwal.showLoading('Đang lưu dữ liệu...');
  
  const formData = new FormData();
  formData.append('order_id', orderId);

  forms.value.forEach((form, index) => {
    formData.append(`reviews[${index}][product_id]`, form.product_id);
    formData.append(`reviews[${index}][variant_name]`, form.variant_name);
    formData.append(`reviews[${index}][rating]`, form.rating);
    if(form.comment) formData.append(`reviews[${index}][comment]`, form.comment);
    if(form.fit_feedback) formData.append(`reviews[${index}][fit_feedback]`, form.fit_feedback);
    if(form.reviewer_height) formData.append(`reviews[${index}][reviewer_height]`, form.reviewer_height);
    if(form.reviewer_weight) formData.append(`reviews[${index}][reviewer_weight]`, form.reviewer_weight);
    
    form.images.forEach((img) => {
      formData.append(`reviews[${index}][images][]`, img);
    });
  });

  try {
    await api.post('/client/user/reviews', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });
    ZyroSwal.close();
    ZyroSwal.toastSuccess('Cập nhật Đánh giá thành công!');
    router.push('/user/orders');
  } catch (error) {
    ZyroSwal.close();
    ZyroSwal.toastError(error.response?.data?.message || 'Có lỗi xảy ra');
  } finally {
    isSubmitting.value = false;
  }
};

onMounted(() => {
  window.scrollTo(0, 0);
  fetchReviewItems();
});
</script>

<style scoped>
.review-page-wrapper { width: 100%; }
.zyro-container { width: 100%; max-width: 1310px; margin: 0 auto; padding-left: 20px; padding-right: 20px; }
@media (min-width: 1400px) { .zyro-container { padding-left: 0; padding-right: 0; } }

.btn-urban { background-color: var(--color-c-hover, #547792); color: white; border: none; transition: 0.2s; }
.btn-urban:hover { background-color: var(--color-c-dark, #213448); color: white; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
.border-urban { border-color: var(--color-c-hover, #547792) !important; }
.text-urban { color: var(--color-c-hover, #547792) !important; }

.custom-input {
  background-color: #ffffff;
  border: 1.5px solid var(--color-c-light); 
  color: var(--color-c-dark);
  font-size: 0.95rem;
  border-radius: 0.5rem;
  transition: all 0.2s ease;
  box-shadow: none !important; 
}
html.dark .custom-input { background-color: #1a2533; border-color: #373b3e; color: white; }
.custom-input:focus { border-color: var(--color-c-hover) !important; box-shadow: 0 0 0 3px rgba(148, 180, 193, 0.2) !important; }

.star-icon:hover { transform: scale(1.2); }
.tracking-widest { letter-spacing: 2px; }

.fit-radio { color: #6c757d; border: 1.5px solid #dee2e6; font-size: 0.85rem; background: #fff; }
html.dark .fit-radio { background: #212529; border-color: #373b3e; color: #adb5bd; }
.fit-radio.active { background-color: var(--color-c-hover, #547792); color: white; border-color: var(--color-c-hover, #547792); font-weight: bold; transform: scale(1.05); }

.border-dashed { border-style: dashed !important; border-width: 2px !important; }
.hover-bg-effect:hover { background-color: rgba(84, 119, 146, 0.1) !important; }

.shadow-sm-hover:hover { box-shadow: 0 8px 25px rgba(0,0,0,0.08) !important; }
.hover-transform:hover { transform: translateY(-3px); }
.transition-all { transition: all 0.3s ease; }
.shadow-sm-top { box-shadow: 0 -10px 15px -3px rgba(0,0,0,0.05); }
.z-index-2 { z-index: 2; }

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

</style>