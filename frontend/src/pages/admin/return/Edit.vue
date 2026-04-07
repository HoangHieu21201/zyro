<!-- File: frontend/src/pages/admin/return/Edit.vue -->
<template>
  <div class="return-edit-wrapper pb-5 mb-5">
    
    <div v-if="isPageLoading" class="d-flex flex-column justify-content-center align-items-center w-100" style="min-height: 70vh;">
      <h1 class="logo-shimmer mb-3">ZYRO</h1>
      <p class="text-muted dark:text-gray-400 fw-semibold small text-uppercase tracking-widest" style="letter-spacing: 2px;">Đang tải hồ sơ hoàn trả...</p>
    </div>

    <div class="container-fluid py-4" v-else>
      
      <div class="row mb-4 align-items-center">
        <div class="col-md-8 d-flex align-items-center mb-3 mb-md-0">
          <router-link :to="{ name: 'admin-returns' }" class="btn btn-light dark:bg-[#2b3035] dark:border-gray-600 dark:text-gray-200 shadow-sm me-3 rounded-circle d-flex align-items-center justify-content-center hover-urban transition-all" style="width: 40px; height: 40px;">
            <i class="bi bi-arrow-left fw-bold"></i>
          </router-link>
          <div class="d-flex flex-column">
            <h3 class="fw-bold text-dark dark:text-white mb-0 d-flex align-items-center flex-wrap gap-2">
              Xử lý Đơn <span class="text-urban font-monospace">#{{ order.order_code }}</span>
              <span class="badge border px-3 py-1.5 ms-2 fs-6 shadow-sm" :class="getReturnStatusClass(order)">
                {{ getReturnStatusLabel(order) }}
              </span>
            </h3>
            <p class="text-muted dark:text-gray-400 small mb-0 mt-1"><i class="bi bi-calendar-event me-1"></i>Yêu cầu tạo lúc: {{ formatDateTime(order.updated_at) }}</p>
          </div>
        </div>
      </div>

      <div class="row g-4">
        <!-- CỘT TRÁI: SẢN PHẨM & TÀI CHÍNH GỐC -->
        <div class="col-xl-8 col-lg-7">
          
          <div class="card border-0 shadow-sm rounded-4 dark:bg-[#1a2533] p-4 mb-4 animation-fade-in">
            <h6 class="fw-bold text-urban text-uppercase mb-4 border-bottom dark:border-gray-700 pb-2"><i class="bi bi-bag-check-fill me-2"></i>Sản phẩm khách muốn hoàn ({{ order.items?.length || 0 }})</h6>
            
            <div class="table-responsive custom-scrollbar-x border rounded-3 dark:border-gray-700">
              <table class="table table-bordered mb-0 align-middle opacity-75" style="pointer-events: none;">
                <thead class="bg-light dark:bg-[#2b3035]">
                  <tr>
                    <th class="dark:text-gray-300 border-0">Sản phẩm</th>
                    <th class="dark:text-gray-300 border-0 text-center" style="width: 120px;">Đơn giá</th>
                    <th class="dark:text-gray-300 border-0 text-center" style="width: 80px;">SL</th>
                    <th class="dark:text-gray-300 border-0 text-end" style="width: 120px;">Thành tiền</th>
                  </tr>
                </thead>
                <tbody class="dark:border-gray-700">
                  <tr v-for="item in order.items" :key="item.id" class="dark:bg-[#1a2533]">
                    <td>
                      <div class="d-flex align-items-center">
                        <img :src="getImageUrl(item.product?.thumbnail_image)" class="rounded object-fit-cover border dark:border-gray-600 bg-white me-2" style="width: 40px; height: 40px;" @error="handleImageError">
                        <div>
                          <div class="fw-bold text-dark dark:text-gray-200 small mb-1 text-truncate" style="max-width: 200px;">{{ item.product_name }}</div>
                          <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary" style="font-size: 0.65rem;">{{ item.variant_sku }}</span>
                        </div>
                      </div>
                    </td>
                    <td class="text-center text-dark dark:text-gray-200 fw-bold">{{ formatCurrency(item.purchased_price) }}</td>
                    <td class="text-center fw-bold">x {{ item.quantity }}</td>
                    <td class="text-end text-danger fw-bold">{{ formatCurrency(item.total_price) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Tổng kết Tài chính -->
            <div class="row mt-4">
              <div class="col-md-6 offset-md-6 col-xl-5 offset-xl-7">
                <div class="p-3 bg-light dark:bg-[#212529] border dark:border-gray-700 rounded-3">
                  <div class="d-flex justify-content-between mb-2 text-success">
                    <span class="small fw-semibold">Khuyến mãi đã áp:</span>
                    <span class="fw-bold">- {{ formatCurrency(order.discount_amount) }}</span>
                  </div>
                  <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted dark:text-gray-400 small fw-semibold">Khách đã thanh toán:</span>
                    <span class="text-dark dark:text-gray-200 fw-bold">{{ formatCurrency(order.total_amount) }}</span>
                  </div>
                  <hr class="dark:border-gray-600 my-2">
                  <div class="d-flex justify-content-between align-items-center mt-2">
                    <span class="text-uppercase fw-bold text-dark dark:text-white">GIÁ TRỊ HOÀN TỐI ĐA</span>
                    <span class="text-danger fw-bold fs-4">{{ formatCurrency(order.total_amount) }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Tiến trình & Lịch sử -->
          <div class="card border-0 shadow-sm rounded-4 dark:bg-[#1a2533] p-4 animation-fade-in">
            <h6 class="fw-bold text-muted small text-uppercase mb-3 border-bottom dark:border-gray-700 pb-2"><i class="bi bi-clock-history me-1"></i>Lịch sử Giao dịch & Xử lý</h6>
            <ul class="list-group list-group-flush rounded-3 border dark:border-gray-700 custom-scrollbar-y" style="max-height: 250px; overflow-y: auto;">
               <li v-for="his in order.histories" :key="his.id" class="list-group-item bg-transparent dark:border-gray-700 p-3">
                 <div class="d-flex justify-content-between align-items-start">
                   <div>
                     <div class="fw-bold text-dark dark:text-gray-200 small mb-1">
                        Cập nhật: <span class="text-secondary text-decoration-line-through">{{ getOrderStatusLabel(his.old_status) }}</span> <i class="bi bi-arrow-right mx-1 text-muted"></i> <span class="text-urban">{{ getOrderStatusLabel(his.new_status) }}</span>
                     </div>
                     <div class="text-muted fst-italic" style="font-size: 0.8rem;">Note: "{{ his.note || 'Không có ghi chú' }}"</div>
                   </div>
                   <div class="text-end ms-2">
                     <span class="badge bg-secondary bg-opacity-10 text-secondary border mb-1"><i class="bi bi-person-badge"></i> {{ his.changer?.fullname || 'Khách/Hệ thống' }}</span>
                     <div class="text-muted font-monospace" style="font-size: 0.7rem;">{{ formatDateTime(his.created_at) }}</div>
                   </div>
                 </div>
               </li>
            </ul>
          </div>
        </div>

        <!-- CỘT PHẢI: FORM KẾ TOÁN XỬ LÝ -->
        <div class="col-xl-4 col-lg-5">
           <!-- Thông tin Khách -->
           <div class="card border-0 shadow-sm rounded-4 dark:bg-[#1a2533] p-4 mb-4">
              <h6 class="fw-bold mb-3 text-urban text-uppercase border-bottom dark:border-gray-700 pb-2"><i class="bi bi-person-lines-fill me-2"></i>Tài khoản yêu cầu</h6>
              <div class="d-flex align-items-center mb-3">
                <div class="bg-light dark:bg-[#212529] rounded-circle d-flex align-items-center justify-content-center me-3 border shadow-sm dark:border-gray-600 flex-shrink-0" style="width: 45px; height: 45px;">
                   <i class="bi bi-person-fill text-muted fs-4"></i>
                </div>
                <div class="overflow-hidden">
                  <h6 class="mb-0 fw-bold text-dark dark:text-gray-200 text-truncate">{{ order.user?.full_name || shippingInfoParsed?.name || 'Khách vãng lai' }}</h6>
                  <small class="text-muted dark:text-gray-400 d-block mt-1 font-monospace"><i class="bi bi-telephone me-1"></i>{{ shippingInfoParsed?.phone || 'N/A' }}</small>
                </div>
              </div>
           </div>

           <!-- FORM XỬ LÝ HOÀN TIỀN -->
           <div class="card border-0 shadow-sm rounded-4 dark:bg-[#1a2533] p-4 sticky-top" style="top: 20px;">
              <h6 class="fw-bold mb-3 text-urban text-uppercase border-bottom dark:border-gray-700 pb-2"><i class="bi bi-cash-stack me-2"></i>Quyết định Kế toán</h6>
              
              <!-- Cảnh báo nếu đã xử lý xong -->
              <div v-if="order.payment_status === 'refunded'" class="alert alert-success border-0 p-3 rounded-3 shadow-sm mb-3">
                 <i class="bi bi-check-circle-fill me-1"></i> Đơn hàng này đã được <strong>Hoàn tiền thành công</strong>. Luồng xử lý RMA đã khép lại.
              </div>
              <div v-else-if="order.refunded_amount === 0 && order.payment_status === 'paid'" class="alert alert-danger border-0 p-3 rounded-3 shadow-sm mb-3">
                 <i class="bi bi-x-circle-fill me-1"></i> Yêu cầu này đã bị <strong>Từ chối hoàn tiền</strong>. 
              </div>

              <!-- Form thay đổi trạng thái -->
              <form @submit.prevent="submitRefund" v-else>
                <div class="mb-3">
                  <label class="form-label text-muted small fw-bold text-uppercase mb-2">Hành động <span class="text-danger">*</span></label>
                  <select class="form-select form-select-lg fw-bold shadow-sm-hover dark:bg-[#212529] dark:text-white dark:border-gray-700" v-model="refundForm.action" :class="getRefundActionClass(refundForm.action)">
                    <option value="propose">Đề xuất hoàn một phần / Toàn bộ</option>
                    <option value="reject">Từ chối hoàn tiền</option>
                    <option value="refunded">Xác nhận đã Bank tiền (Chốt sổ)</option>
                  </select>
                </div>

                <div class="mb-3" v-if="refundForm.action !== 'reject'">
                  <label class="form-label text-muted small fw-bold text-uppercase mb-2">Số tiền hoàn (VNĐ) <span class="text-danger">*</span></label>
                  <div class="input-group shadow-sm-hover">
                    <input type="number" class="form-control form-control-lg fw-bold text-danger dark:bg-[#212529] dark:text-white border-0" v-model.number="refundForm.refund_amount" min="0" :max="order.total_amount" required>
                    <span class="input-group-text bg-white dark:bg-[#212529] border-0 text-muted">₫</span>
                  </div>
                  <small class="text-danger fw-bold d-block mt-1">{{ formatCurrency(refundForm.refund_amount) }}</small>
                </div>

                <div class="mb-4">
                  <label class="form-label text-muted small fw-bold text-uppercase mb-2">Ghi chú cho Khách (Tùy chọn)</label>
                  <textarea class="form-control bg-light dark:bg-[#212529] dark:text-white dark:border-gray-700 shadow-sm-hover" v-model="refundForm.refund_note" rows="3" placeholder="VD: Tiền phí ship 30k không được hoàn lại theo quy định..."></textarea>
                </div>

                <button type="submit" class="btn btn-urban btn-lg text-white w-100 fw-bold shadow-sm rounded-pill" :disabled="isSavingRefund">
                  <span v-if="isSavingRefund" class="spinner-border spinner-border-sm me-2"></span> XÁC NHẬN YÊU CẦU
                </button>
              </form>
           </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import Swal from 'sweetalert2';
import axios from 'axios';
import defaultAvatar from '@/assets/images/defaults/placeholder.png';

const route = useRoute();
const router = useRouter();
const orderId = route.params.id;

const isPageLoading = ref(true);
const isSavingRefund = ref(false);

const order = ref({});
const shippingInfoParsed = ref({});

const refundForm = ref({ action: 'propose', refund_amount: 0, refund_note: '' });

const getHeaders = () => ({ 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('admin_token')}` });
const getImageUrl = (path) => path ? `http://127.0.0.1:8000/storage/${path}` : defaultAvatar;
const handleImageError = (e) => { e.target.src = defaultAvatar; };

const formatCurrency = (val) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val || 0);
const formatDateTime = (dateString) => {
  if(!dateString) return '';
  const d = new Date(dateString);
  return `${d.toLocaleDateString('vi-VN')} ${d.toLocaleTimeString('vi-VN', {hour: '2-digit', minute:'2-digit'})}`;
};

// UI Helpers Dành riêng cho RMA (Hoàn trả)
const getReturnStatusClass = (order) => {
    if (order.payment_status === 'refunded') return 'bg-success text-white border-success';
    if (order.refunded_amount === null) return 'bg-warning text-dark border-warning';
    if (order.refunded_amount > 0) return 'bg-info text-white border-info';
    if (order.refunded_amount == 0) return 'bg-danger text-white border-danger';
    return 'bg-secondary text-white';
};

const getReturnStatusLabel = (order) => {
    if (order.payment_status === 'refunded') return 'Đã hoàn tiền';
    if (order.refunded_amount === null) return 'Chờ Kế toán xử lý';
    if (order.refunded_amount > 0) return 'Đang thỏa thuận';
    if (order.refunded_amount == 0) return 'Đã từ chối hoàn';
    return 'Đang xử lý';
};

const getOrderStatusLabel = (status) => {
  const map = { 'pending': 'Chờ xác nhận', 'confirmed': 'Đã xác nhận', 'processing': 'Đang chuẩn bị', 'shipping': 'Đang giao', 'completed': 'Thành công', 'cancelled': 'Đã hủy', 'returned': 'Đã hoàn trả/Từ chối nhận' };
  return map[status] || status;
};

const getRefundActionClass = (action) => {
  if (action === 'propose') return 'text-info';
  if (action === 'reject') return 'text-danger';
  return 'text-success';
};

const fetchData = async () => {
  isPageLoading.value = true;
  try {
    const res = await axios.get(`http://127.0.0.1:8000/api/v1/admin/orders/${orderId}`, { headers: getHeaders() });
    order.value = res.data.data;
    
    shippingInfoParsed.value = typeof order.value.shipping_info === 'string' ? JSON.parse(order.value.shipping_info) : order.value.shipping_info;

    // Load data vào form nếu đã có đề xuất
    refundForm.value.refund_amount = order.value.refunded_amount !== null ? order.value.refunded_amount : order.value.total_amount;
    
  } catch (e) {
    Swal.fire('Lỗi', 'Không tìm thấy đơn hàng RMA', 'error');
    router.push({ name: 'admin-returns' });
  } finally { isPageLoading.value = false; }
};

const submitRefund = async () => {
  if (refundForm.value.action === 'refunded') {
      const confirm = await Swal.fire({
          title: 'Xác nhận đã chuyển khoản?',
          text: `Hành động này sẽ khép lại tiến trình RMA và không thể hoàn tác. Bạn đã chuyển ${formatCurrency(refundForm.value.refund_amount)} cho khách hàng?`,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#28a745',
          confirmButtonText: 'Đã chuyển tiền'
      });
      if(!confirm.isConfirmed) return;
  } else if (refundForm.value.action === 'reject') {
      if (!refundForm.value.refund_note) {
          Swal.fire('Thiếu thông tin', 'Vui lòng nhập lý do từ chối vào ô Ghi chú.', 'warning');
          return;
      }
  }

  isSavingRefund.value = true;
  try {
    await axios.post(`http://127.0.0.1:8000/api/v1/admin/orders/${orderId}/refund`, refundForm.value, { headers: getHeaders() });
    Swal.fire({ icon: 'success', title: 'Đã xử lý', text: 'Giao dịch hoàn tiền đã được ghi nhận hệ thống.', timer: 2000, showConfirmButton: false });
    await fetchData();
  } catch(e) {
    Swal.fire('Lỗi', e.response?.data?.message || 'Lỗi xử lý Kế toán', 'error');
  } finally { isSavingRefund.value = false; }
};

onMounted(() => { fetchData(); });
</script>

<style scoped>
.text-urban { color: var(--color-c-hover, #547792) !important; }
.bg-urban { background-color: var(--color-c-hover, #547792) !important; }
.border-urban { border-color: var(--color-c-hover, #547792) !important; }
.btn-urban { background-color: var(--color-c-hover, #547792); color: white; border: none; transition: 0.2s; }
.btn-urban:hover { background-color: var(--color-c-dark, #213448); color: white; transform: translateY(-1px); }

.shadow-sm-hover { transition: box-shadow 0.2s ease, border-color 0.2s ease; }
.shadow-sm-hover:focus-within { box-shadow: 0 4px 15px rgba(84, 119, 146, 0.1) !important; }
.form-control:focus, .form-select:focus { border-color: var(--color-c-hover, #547792); box-shadow: none !important; }

.logo-shimmer { font-size: 3.5rem; font-weight: 900; letter-spacing: -1.5px; background: linear-gradient(120deg, var(--color-c-dark) 30%, var(--color-c-light) 50%, var(--color-c-dark) 70%); background-size: 200% auto; color: transparent; -webkit-background-clip: text; background-clip: text; animation: shine 1.5s linear infinite; }
@keyframes shine { to { background-position: 200% center; } }
.animation-fade-in { animation: fadeIn 0.4s ease-in-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

.custom-scrollbar-x::-webkit-scrollbar { height: 6px; }
.custom-scrollbar-x::-webkit-scrollbar-thumb { background: var(--color-c-light, #94B4C1); border-radius: 10px; }
.custom-scrollbar-y::-webkit-scrollbar { width: 4px; }
.custom-scrollbar-y::-webkit-scrollbar-thumb { background: var(--color-c-light, #94B4C1); border-radius: 10px; }
</style>