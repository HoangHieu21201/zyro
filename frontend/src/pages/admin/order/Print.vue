<template>
  <div class="print-container bg-light min-vh-100">
    <!-- Non-print controls -->
    <div class="d-print-none text-center py-4 bg-white shadow-sm mb-4 sticky-top">
      <h4 class="fw-bold mb-3">Chế độ In Phiếu Giao Hàng</h4>
      <div class="d-flex justify-content-center gap-3">
        <button class="btn btn-primary shadow fw-bold px-4 py-2" @click="handlePrint" :disabled="isLoading">
          <i class="bi bi-printer me-2"></i> IN PHIẾU NGAY (Ctrl + P)
        </button>
        <button class="btn btn-outline-secondary px-4 py-2" @click="closeTab">Đóng</button>
      </div>
    </div>

    <div v-if="isLoading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status"></div>
      <div class="mt-2 text-muted">Đang tải dữ liệu đơn hàng...</div>
    </div>

    <!-- Print Pages -->
    <div v-else class="print-pages">
      <div v-for="(order, index) in orders" :key="order.id" class="print-page bg-white shadow-sm mx-auto mb-4 p-4 border" style="width: 105mm; min-height: 148mm; position: relative;">
        <!-- Header -->
        <div class="text-center border-bottom pb-2 mb-3">
          <h2 class="fw-bold mb-1" style="font-size: 1.2rem; letter-spacing: 1px;">ZYRO STORE</h2>
          <div style="font-size: 0.75rem;">Thời trang & Phụ kiện cao cấp</div>
          <svg class="barcode mt-2" :data-value="order.order_code"></svg>
        </div>

        <!-- Shipping Info -->
        <div class="mb-3">
          <div class="fw-bold mb-1 border-bottom border-dark pb-1" style="font-size: 0.9rem;">NGƯỜI NHẬN</div>
          <div class="fw-bold fs-5 mb-1">{{ getCustomerName(order) }}</div>
          <div class="fw-bold fs-5 mb-1">{{ getCustomerPhone(order) }}</div>
          <div class="" style="font-size: 0.85rem; line-height: 1.3;">
            {{ getCustomerAddress(order) }}
          </div>
        </div>

        <!-- Items -->
        <div class="mb-3">
          <div class="fw-bold mb-1 border-bottom border-dark pb-1" style="font-size: 0.9rem;">NỘI DUNG HÀNG (Tổng: {{ order.items.length }} SP)</div>
          <div v-for="item in order.items" :key="item.id" class="d-flex justify-content-between mb-1" style="font-size: 0.8rem; line-height: 1.2;">
            <div class="pe-2 text-truncate" style="max-width: 80%;">
              <strong>{{ item.quantity }}x</strong> 
              {{ item.product?.name || 'Sản phẩm' }}
              <span v-if="item.variant" class="text-muted">
                 ({{ item.variant.attributes?.map(a => a.pivot.value).join(', ') }})
              </span>
            </div>
          </div>
        </div>

        <!-- COD -->
        <div class="p-2 border border-dark border-2 text-center mt-auto">
          <div class="fw-bold" style="font-size: 0.8rem;">TIỀN THU HỘ (COD)</div>
          <div class="fw-bold fs-3">{{ formatCurrency(order.total_amount) }}</div>
        </div>
        
        <div class="text-center mt-2 text-muted" style="font-size: 0.65rem;">
          Đơn hàng tạo lúc: {{ formatDateTime(order.created_at) }}<br>
          <i>(Lưu ý: Không đồng kiểm khi nhận hàng)</i>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import JsBarcode from 'jsbarcode';
import { ZyroSwal } from '@/components/client/ZyroSwal';

const route = useRoute();
const orders = ref([]);
const isLoading = ref(true);

const getHeaders = () => ({ 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('admin_token')}` });

const formatCurrency = (val) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val || 0);
const formatDateTime = (dateString) => {
  if(!dateString) return '';
  const d = new Date(dateString);
  return `${d.toLocaleDateString('vi-VN')} ${d.toLocaleTimeString('vi-VN', {hour: '2-digit', minute:'2-digit'})}`;
};

const getCustomerName = (order) => {
  try {
     const info = typeof order.shipping_info === 'string' ? JSON.parse(order.shipping_info) : order.shipping_info;
     return info.name || 'Khách vãng lai';
  } catch(e) { return 'Khách vãng lai'; }
};

const getCustomerPhone = (order) => {
  try {
     const info = typeof order.shipping_info === 'string' ? JSON.parse(order.shipping_info) : order.shipping_info;
     return info.phone || 'N/A';
  } catch(e) { return 'N/A'; }
};

const getCustomerAddress = (order) => {
  try {
     const info = typeof order.shipping_info === 'string' ? JSON.parse(order.shipping_info) : order.shipping_info;
     return info.address || '';
  } catch(e) { return ''; }
};

const fetchOrders = async () => {
  const ids = route.query.ids;
  if (!ids) {
    ZyroSwal.toastError('Không tìm thấy dữ liệu đơn hàng');
    isLoading.value = false;
    return;
  }
  
  try {
    const idArray = ids.split(',').map(id => parseInt(id.trim())).filter(id => !isNaN(id));
    const res = await axios.post(`${import.meta.env.VITE_API_BASE_URL}/admin/orders/print`, { ids: idArray }, { headers: getHeaders() });
    orders.value = res.data.data;
    
    // Generate Barcodes
    await nextTick();
    document.querySelectorAll('.barcode').forEach(el => {
      const val = el.getAttribute('data-value');
      if (val) {
        JsBarcode(el, val, { format: "CODE128", width: 1.5, height: 40, displayValue: true, fontSize: 14, margin: 0 });
      }
    });

  } catch (e) {
    ZyroSwal.toastError('Lỗi tải dữ liệu in');
  } finally {
    isLoading.value = false;
  }
};

const handlePrint = () => {
  window.print();
};

const closeTab = () => {
  window.close();
};

onMounted(() => {
  fetchOrders();
});
</script>

<style scoped>
@media print {
  @page {
    size: A6;
    margin: 0;
  }
  body, .print-container {
    background-color: white !important;
  }
  .print-page {
    margin: 0 !important;
    box-shadow: none !important;
    border: none !important;
    page-break-after: always;
    width: 100% !important;
    height: 100% !important;
    padding: 5mm !important;
  }
}
</style>
