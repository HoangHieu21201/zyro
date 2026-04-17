<template>
  <div class="modal fade" id="orderDetailModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
      <div class="modal-content rounded-4 border-0 shadow-lg dark:bg-[#1a2533]">
        
        <div class="modal-header border-bottom dark:border-gray-700 bg-light dark:bg-[#212529] p-3 p-md-4">
          <h5 class="fw-bold text-dark dark:text-white mb-0 fs-6 fs-md-5">
            <i class="bi bi-receipt text-urban me-2"></i>Chi Tiết Đơn Hàng 
            <span v-if="selectedOrder" class="font-monospace text-urban ms-1">#{{ selectedOrder.order_code }}</span>
          </h5>
          <button type="button" class="btn-close dark:filter dark:invert" @click="closeModal" aria-label="Close"></button>
        </div>
        
        <div class="modal-body p-3 p-md-4 custom-scrollbar-y bg-light dark:bg-[#121416]">
           
           <div v-if="isModalLoading" class="text-center py-5">
             <div class="spinner-border text-urban" role="status"></div>
             <div class="mt-2 text-muted">Đang tải dữ liệu...</div>
           </div>

           <div v-else-if="selectedOrder">
             
              <!-- STEPPER -->
              <div class="bg-white dark:bg-[#1a2533] p-3 rounded-4 shadow-sm border dark:border-gray-700 mb-3">
                 <h6 class="fw-bold text-dark dark:text-white mb-3"><i class="bi bi-truck text-urban me-2"></i>Trạng thái đơn hàng</h6>
                 
                 <div v-if="selectedOrder.status === 'cancelled'" class="alert alert-danger mb-0 d-flex align-items-center">
                    <i class="bi bi-x-circle-fill fs-4 me-3"></i> 
                    <div>
                      <strong>Đơn hàng đã bị hủy.</strong><br>
                      Lý do: {{ getCancelReason(selectedOrder) }}
                    </div>
                 </div>

                 <div v-else class="stepper-wrapper">
                    <div class="stepper-item" :class="{'completed': stepLevel >= 1}">
                      <div class="step-counter"><i class="bi bi-box-seam"></i></div>
                      <div class="step-name">Đã đặt</div>
                    </div>
                    <div class="stepper-item" :class="{'completed': stepLevel >= 2}">
                      <div class="step-counter"><i class="bi bi-clipboard-check"></i></div>
                      <div class="step-name">Đã xác nhận</div>
                    </div>
                    <div class="stepper-item" :class="{'completed': stepLevel >= 3}">
                      <div class="step-counter"><i class="bi bi-truck"></i></div>
                      <div class="step-name">Đang giao</div>
                    </div>
                    <div class="stepper-item" :class="{'completed': stepLevel >= 4}">
                      <div class="step-counter"><i class="bi bi-check-lg"></i></div>
                      <div class="step-name">Thành công</div>
                    </div>
                 </div>
              </div>

              <div class="row g-3 mb-3">
                <div class="col-12" v-if="stepLevel >= 3 && mapData">
                   <div class="bg-white dark:bg-[#1a2533] p-3 rounded-4 shadow-sm border dark:border-gray-700">
                      <div class="d-flex justify-content-between align-items-center mb-3 px-2">
                         <h6 class="fw-bold text-dark dark:text-white m-0"><i class="bi bi-map text-urban me-2"></i>Hành trình đơn hàng</h6>
                         <span class="badge bg-urban">{{ mapData.shipping_provider || 'Đơn vị Vận Chuyển' }}</span>
                      </div>
                      <div id="client-tracking-map" class="rounded-3 border overflow-hidden" style="height: 350px; background-color: #e5e5e5; z-index: 1;"></div>
                      <div class="small text-muted mt-2 px-2 fst-italic text-end"><i class="bi bi-info-circle me-1"></i>Mô phỏng đường đi theo tuyến quốc lộ.</div>
                   </div>
                </div>

                <div class="col-md-6">
                  <div class="p-3 bg-white dark:bg-[#1a2533] border dark:border-gray-700 rounded-4 shadow-sm h-100">
                    <h6 class="fw-bold text-dark dark:text-white mb-3 border-bottom dark:border-gray-600 pb-2">Địa chỉ nhận hàng</h6>
                    <div class="fw-bold text-dark dark:text-gray-200 fs-5 mb-1">{{ selectedOrder.shipping_info?.name || 'Chưa cập nhật' }}</div>
                    <div class="text-muted small mt-2"><i class="bi bi-telephone-fill text-urban me-2"></i>{{ selectedOrder.shipping_info?.phone || 'Chưa cập nhật' }}</div>
                    <div class="text-muted small mt-2 lh-lg"><i class="bi bi-geo-alt-fill text-urban me-2"></i>{{ selectedOrder.shipping_info?.address || 'Chưa cập nhật' }}</div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="p-3 bg-white dark:bg-[#1a2533] border dark:border-gray-700 rounded-4 shadow-sm h-100">
                    <h6 class="fw-bold text-dark dark:text-white mb-3 border-bottom dark:border-gray-600 pb-2">Thông tin thanh toán</h6>
                    <div class="d-flex justify-content-between mb-2 small text-muted dark:text-gray-400">
                      <span>Phương thức:</span>
                      <span class="fw-bold text-dark dark:text-white text-uppercase">{{ selectedOrder.payment_method || 'COD' }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 small text-muted dark:text-gray-400">
                      <span>Tình trạng:</span>
                      <span class="badge border px-2 py-1 shadow-sm" :class="getPaymentStatusClass(selectedOrder.payment_status)">
                        {{ getPaymentStatusLabel(selectedOrder.payment_status) }}
                      </span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 small text-muted dark:text-gray-400 mt-4">
                      <span>Tạm tính:</span>
                      <span class="fw-bold text-dark dark:text-gray-200">{{ formatCurrency(selectedOrder.total_amount - (selectedOrder.shipping_fee || 0) + (selectedOrder.discount_amount || 0)) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 small text-muted dark:text-gray-400">
                      <span>Phí vận chuyển:</span>
                      <span class="fw-bold text-dark dark:text-gray-200">{{ formatCurrency(selectedOrder.shipping_fee || 0) }}</span>
                    </div>
                    <div v-if="selectedOrder.discount_amount > 0" class="d-flex justify-content-between mb-2 small text-success">
                      <span>Giảm giá/Voucher:</span>
                      <span class="fw-bold">- {{ formatCurrency(selectedOrder.discount_amount) }}</span>
                    </div>
                    <hr class="dark:border-gray-600 my-2">
                    <div class="d-flex justify-content-between align-items-center mt-2">
                      <span class="fw-bold text-dark dark:text-white text-uppercase">Tổng thanh toán:</span>
                      <span class="text-danger fw-bold fs-4">{{ formatCurrency(selectedOrder.total_amount) }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <div class="bg-white dark:bg-[#1a2533] p-3 rounded-4 shadow-sm border dark:border-gray-700">
                <h6 class="fw-bold text-dark dark:text-white mb-3">Sản phẩm đã mua ({{ selectedOrder.items?.length || 0 }})</h6>
                <div class="table-responsive">
                  <table class="table table-borderless align-middle mb-0" style="min-width: 400px;">
                    <tbody class="border-top dark:border-gray-600">
                      <tr v-for="(item, idx) in selectedOrder.items" :key="idx" class="border-bottom dark:border-gray-700">
                        <td class="py-2 px-0">
                          <div class="d-flex align-items-center gap-3">
                            <img :src="getImageUrl(item.variant_image)" class="rounded-3 object-fit-cover border dark:border-gray-600 bg-light" style="width: 45px; height: 55px;" @error="e => e.target.src='/client_placeholder.png'">
                            <div>
                              <div class="fw-bold text-dark dark:text-gray-200 small line-clamp-2">{{ item.product_name }}</div>
                              <div class="text-muted small mt-1 bg-light dark:bg-[#212529] px-2 py-1 rounded d-inline-block border dark:border-gray-600">{{ parseAttributes(item.variant_attributes) }}</div>
                            </div>
                          </div>
                        </td>
                        <td class="text-center fw-bold text-dark dark:text-gray-300 small">x{{ item.quantity }}</td>
                        <td class="text-end fw-bold text-urban">{{ formatCurrency(item.purchased_price * item.quantity) }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

           </div>
        </div>
        
        <div class="modal-footer border-top dark:border-gray-700 bg-white dark:bg-[#1a2533] p-3 justify-content-end rounded-bottom-4 z-index-2 position-relative shadow-sm-top">
          <button v-if="selectedOrder && selectedOrder.status === 'pending'" class="btn btn-outline-danger px-4 rounded-pill fw-bold" @click="cancelOrder(selectedOrder)">Hủy đơn hàng</button>

          <template v-if="selectedOrder && selectedOrder.status === 'completed'">
            <button class="btn btn-outline-danger rounded-pill px-4 fw-semibold transition-all" @click="requestReturn(selectedOrder)">Hoàn trả</button>
            <button class="btn btn-outline-urban rounded-pill px-4 fw-semibold transition-all" @click="buyAgain(selectedOrder)">Mua lại</button>
            
            <!-- ĐÃ CẬP NHẬT: Đổi text nút Đánh giá / Xem lại đánh giá -->
            <button v-if="selectedOrder.is_reviewed" class="btn btn-outline-urban rounded-pill px-4 fw-bold shadow-sm transition-all" @click="openReview(selectedOrder)">
               Xem lại đánh giá
            </button>
            <button v-else class="btn btn-urban rounded-pill px-4 fw-bold shadow-sm transition-all" @click="openReview(selectedOrder)">
               Đánh giá
            </button>
          </template>

          <button type="button" class="btn btn-secondary px-4 px-md-5 rounded-pill fw-bold shadow-sm ms-2" @click="closeModal">Đóng</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, nextTick, onBeforeUnmount } from 'vue';
import { useRouter } from 'vue-router';
import api from '@/utils/axios';
import axios from 'axios';
import Swal from 'sweetalert2';
import { ZyroSwal } from '@/components/client/ZyroSwal';
import { useCartStore } from '@/stores/cartStore';

const emit = defineEmits(['refresh']);
const router = useRouter();
const cartStore = useCartStore();

const selectedOrder = ref(null);
const isModalLoading = ref(false);
let modalInstance = null;

// MAP STATES
const mapData = ref(null);
let leafletMap = null;
let routingLine = null;
let truckMarker = null;
let animationFrameId = null;

const stepLevel = computed(() => {
  if (!selectedOrder.value) return 0;
  const s = selectedOrder.value.status;
  if (s === 'pending') return 1;
  if (s === 'confirmed' || s === 'processing') return 2;
  if (s === 'shipping') return 3;
  if (s === 'completed' || s === 'returned') return 4;
  return 0;
});

const closeModal = () => {
  if (document.activeElement) document.activeElement.blur();
  if (modalInstance) modalInstance.hide();
};

const openModal = async (id) => {
  selectedOrder.value = null;
  mapData.value = null;
  if (!modalInstance) {
    modalInstance = new window.bootstrap.Modal(document.getElementById('orderDetailModal'));
  }
  modalInstance.show();
  
  isModalLoading.value = true;
  try {
    const res = await api.get(`/client/user/orders/${id}`);
    if (res.data.success) {
       selectedOrder.value = res.data.data;
       if (typeof selectedOrder.value.shipping_info === 'string') {
          selectedOrder.value.shipping_info = JSON.parse(selectedOrder.value.shipping_info);
       }
       if (stepLevel.value >= 3) {
          prepareMapData();
       }
    }
  } catch (err) {
    ZyroSwal.toastError('Lỗi tải chi tiết đơn hàng');
    closeModal();
  } finally {
    isModalLoading.value = false;
  }
};

defineExpose({ openModal });

const cancelOrder = (order) => {
  if (order.status !== 'pending') {
     ZyroSwal.toastError('Chỉ có thể hủy đơn chờ xác nhận');
     return;
  }

  ZyroSwal.confirmCancelOrder().then(async (result) => {
    if (result.isConfirmed) {
       try {
         ZyroSwal.showLoading('Đang xử lý...');
         await api.post(`/client/user/orders/${order.id}/cancel`, { reason: result.value });
         ZyroSwal.close();
         ZyroSwal.toastSuccess('Hủy đơn hàng thành công');
         
         closeModal();
         emit('refresh'); 
       } catch (err) {
         ZyroSwal.close();
         ZyroSwal.toastError(err.response?.data?.message || 'Có lỗi xảy ra khi hủy');
       }
    }
  });
};

const requestReturn = (order) => {
  Swal.fire({
      title: 'Yêu cầu hoàn trả',
      input: 'textarea',
      inputPlaceholder: 'Nhập lý do hoàn trả của bạn (Hàng lỗi, sai mẫu, v.v...)',
      showCancelButton: true,
      confirmButtonText: 'Gửi yêu cầu',
      cancelButtonText: 'Đóng',
      confirmButtonColor: '#dc3545',
      inputValidator: (value) => {
        return new Promise((resolve) => {
          if (value.trim()) resolve();
          else resolve('Vui lòng nhập lý do hoàn trả!');
        });
      },
      customClass: { popup: 'rounded-4 dark:bg-[#1a2533]' }
  }).then(async (result) => {
      if (result.isConfirmed) {
          try {
            ZyroSwal.showLoading('Đang gửi yêu cầu...');
            const res = await api.post(`/client/user/orders/${order.id}/return`, { reason: result.value });
            ZyroSwal.close();
            ZyroSwal.toastSuccess(res.data.message);
          } catch (err) {
            ZyroSwal.close();
            ZyroSwal.toastError(err.response?.data?.message || 'Có lỗi xảy ra');
          }
      }
  });
};

const buyAgain = async (order) => {
  try {
      ZyroSwal.showLoading('Đang thêm vào giỏ...');
      const res = await api.post(`/client/user/orders/${order.id}/buy-again`);
      await cartStore.fetchDBCart();
      ZyroSwal.close();
      ZyroSwal.toastSuccess(res.data.message);
      closeModal();
      router.push('/cart');
  } catch (error) {
      ZyroSwal.close();
      ZyroSwal.toastError(error.response?.data?.message || 'Không thể mua lại sản phẩm');
  }
};

const openReview = (order) => {
  closeModal();
  router.push(`/user/review?order_id=${order.id}`);
};

const getCoordinatesForCity = (cityName) => {
  if(!cityName) return [16.0471, 108.2068]; 
  const city = cityName.toLowerCase();
  const coordsMap = {
    'hà nội': [21.0285, 105.8542], 'hồ chí minh': [10.8231, 106.6297], 'đà nẵng': [16.0471, 108.2068],
    'hải phòng': [20.8449, 106.6881], 'cần thơ': [10.0452, 105.7469], 'đắk lắk': [12.6667, 108.0383]
  };
  for (const key in coordsMap) {
    if (city.includes(key)) return coordsMap[key];
  }
  return [16.0471, 108.2068];
};

const prepareMapData = async () => {
  if (!selectedOrder.value || !selectedOrder.value.shipping_info) return;
  const info = selectedOrder.value.shipping_info;
  
  const originName = info.origin_city || 'Hà Nội';
  const destName = info.city || 'Hồ Chí Minh';
  
  mapData.value = {
     origin: { name: originName, coords: getCoordinatesForCity(originName) },
     destination: { name: destName, coords: getCoordinatesForCity(destName) },
     shipping_provider: selectedOrder.value.shipping_provider
  };

  setTimeout(() => renderLeafletMap(), 300);
};

const renderLeafletMap = async () => {
  await nextTick();
  const mapContainer = document.getElementById('client-tracking-map');
  if (!mapContainer) return;

  if (!window.L) {
    const link = document.createElement('link'); link.rel = 'stylesheet'; link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css'; document.head.appendChild(link);
    const script = document.createElement('script'); script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
    await new Promise(r => { script.onload = r; document.head.appendChild(script); });
  }

  const p1 = mapData.value.origin.coords;
  const p2 = mapData.value.destination.coords;

  if (leafletMap) {
    leafletMap.remove();
    leafletMap = null;
  }

  leafletMap = window.L.map('client-tracking-map').setView(p1, 6);
  window.L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png').addTo(leafletMap);

  setTimeout(() => leafletMap.invalidateSize(), 200);

  const iconA = window.L.divIcon({ html: '<div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow" style="width:25px; height:25px; font-size:12px; font-weight:bold; border:2px solid white;">A</div>', className: ''});
  const iconB = window.L.divIcon({ html: '<div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center shadow" style="width:25px; height:25px; font-size:12px; font-weight:bold; border:2px solid white;">B</div>', className: ''});
  
  window.L.marker(p1, {icon: iconA}).bindPopup('<b>Từ:</b> ' + mapData.value.origin.name).addTo(leafletMap);
  window.L.marker(p2, {icon: iconB}).bindPopup('<b>Đến:</b> ' + mapData.value.destination.name).addTo(leafletMap);

  try {
     const osrmUrl = `https://router.project-osrm.org/route/v1/driving/${p1[1]},${p1[0]};${p2[1]},${p2[0]}?overview=simplified&geometries=geojson`;
     const osrmRes = await axios.get(osrmUrl);
     const routeCoords = osrmRes.data.routes[0].geometry.coordinates.map(c => [c[1], c[0]]);

     routingLine = window.L.polyline(routeCoords, { color: '#009981', weight: 4, opacity: 0.8 }).addTo(leafletMap);
     leafletMap.fitBounds(routingLine.getBounds(), { padding: [50, 50] });

     const truckHtml = `<div class="bg-danger text-white rounded shadow-lg d-flex align-items-center justify-content-center border border-2 border-white" style="width:30px; height:30px;"><i class="bi bi-truck fs-6"></i></div>`;
     const truckDivIcon = window.L.divIcon({ html: truckHtml, className: '', iconSize: [30, 30], iconAnchor: [15, 15] });
     
     let totalPoints = routeCoords.length;
     let currentIndex = 0; 
     let speed = Math.ceil(totalPoints / 150) || 1;

     if (selectedOrder.value.status === 'completed' || selectedOrder.value.status === 'returned') {
         currentIndex = totalPoints - 1; 
     } else {
         currentIndex = Math.floor(totalPoints * 0.1); 
     }

     truckMarker = window.L.marker(routeCoords[currentIndex], {icon: truckDivIcon}).addTo(leafletMap);

     if (selectedOrder.value.status === 'shipping' && routeCoords.length > 2) {
        const animate = () => {
           if (currentIndex < totalPoints - 1) {
               currentIndex += speed;
               if(currentIndex >= totalPoints) currentIndex = totalPoints - 1;
               truckMarker.setLatLng(routeCoords[currentIndex]);
               animationFrameId = requestAnimationFrame(animate);
           } else {
               currentIndex = Math.floor(totalPoints * 0.1);
               animationFrameId = requestAnimationFrame(animate);
           }
        };
        animate();
     }
  } catch (err) {
     routingLine = window.L.polyline([p1, p2], { color: '#009981', weight: 4 }).addTo(leafletMap);
     leafletMap.fitBounds(routingLine.getBounds(), { padding: [50, 50] });
  }
};

const getImageUrl = (path) => {
  if (!path) return '/client_placeholder.png';
  if (path.startsWith('http')) return path;
  return import.meta.env.VITE_API_BASE_URL.replace('/api/v1', '/storage/') + path;
};
const formatCurrency = (val) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val);
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

const getCancelReason = (order) => {
  if (!order.histories) return 'Khách hàng hủy đơn';
  const h = order.histories.find(x => x.status === 'cancelled');
  return h ? h.note : 'Khách hàng hủy đơn';
};

const getPaymentStatusClass = (status) => {
  if (status === 'paid') return 'bg-success bg-opacity-10 text-success border-success';
  if (status === 'refunded') return 'bg-dark text-white border-dark';
  return 'bg-warning bg-opacity-10 text-warning border-warning';
};

const getPaymentStatusLabel = (status) => {
  if (status === 'paid') return 'Đã thanh toán';
  if (status === 'refunded') return 'Đã hoàn tiền';
  return 'Chưa thanh toán';
};

onBeforeUnmount(() => {
  if (modalInstance) modalInstance.hide();
  if (animationFrameId) cancelAnimationFrame(animationFrameId);
  if (leafletMap) leafletMap.remove();
  document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
  document.body.className = '';
  document.body.style = '';
});
</script>

<style scoped>
.text-urban { color: var(--color-c-hover, #547792) !important; }
.bg-urban { background-color: var(--color-c-hover, #547792) !important; }
.btn-urban { background-color: var(--color-c-hover, #547792); color: white; border: none; transition: 0.2s; }
.btn-urban:hover { background-color: var(--color-c-dark, #213448); color: white; }
.btn-outline-danger { transition: 0.2s; }
.btn-outline-urban { color: var(--color-c-hover, #547792); border-color: var(--color-c-hover, #547792); background: transparent; transition: 0.2s; }
.btn-outline-urban:hover { background-color: var(--color-c-hover, #547792); color: white; }

.stepper-wrapper { display: flex; justify-content: space-between; position: relative; width: 100%; padding-bottom: 10px; }
.stepper-wrapper::before { content: ''; position: absolute; top: 15px; left: 10%; right: 10%; height: 3px; background-color: #e9ecef; z-index: 1; }
html.dark .stepper-wrapper::before { background-color: #373b3e; }
.stepper-item { position: relative; display: flex; flex-direction: column; align-items: center; flex: 1; z-index: 2; }
.step-counter { width: 32px; height: 32px; border-radius: 50%; background-color: #e9ecef; color: #adb5bd; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: bold; margin-bottom: 10px; transition: all 0.3s ease; border: 3px solid white; }
html.dark .step-counter { background-color: #373b3e; border-color: #1a2533; }
.step-name { font-size: 0.85rem; font-weight: 600; color: #6c757d; text-align: center; }

.stepper-item.completed .step-counter { background-color: var(--color-c-hover, #009981); color: white; box-shadow: 0 0 0 4px rgba(0, 153, 129, 0.2); }
.stepper-item.completed .step-name { color: var(--color-c-hover, #009981); }
.stepper-item.completed + .stepper-item.completed::after { content: ''; position: absolute; top: 15px; right: 50%; width: 100%; height: 3px; background-color: var(--color-c-hover, #009981); z-index: -1; }
.stepper-item:first-child::after { display: none; }

.line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis; line-height: 1.4; }

.custom-scrollbar-y::-webkit-scrollbar { width: 6px; }
.custom-scrollbar-y::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar-y::-webkit-scrollbar-thumb { background: var(--color-c-light, #94B4C1); border-radius: 10px; }
</style>