<template>
  <div class="order-index-wrapper">
    
    <div v-if="isFirstLoad" class="d-flex flex-column justify-content-center align-items-center w-100" style="min-height: 70vh;">
      <h1 class="logo-shimmer mb-3">ZYRO</h1>
      <p class="text-muted dark:text-gray-400 fw-semibold small text-uppercase tracking-widest" style="letter-spacing: 2px;">Đang tải dữ liệu đơn hàng...</p>
    </div>

    <div class="container-fluid py-4" v-else>
      <div class="row mb-4 align-items-center">
        <div class="col-md-6 col-12 mb-3 mb-md-0">
          <h3 class="fw-bold text-dark dark:text-white mb-0">Quản Lý Đơn Hàng</h3>
          <p class="text-muted dark:text-gray-400 small mb-0 mt-1 d-none d-md-block">Theo dõi và xử lý tiến trình giao dịch của khách hàng.</p>
        </div>
        <div class="col-md-6 col-12 text-md-end d-flex justify-content-md-end align-items-center gap-3 flex-wrap">
          <div class="border rounded px-3 py-1.5 bg-white dark:bg-[#1a2533] dark:border-gray-700 shadow-sm text-muted dark:text-gray-300 small" v-if="currentPageLevel">
            <i class="bi bi-shield-check text-success me-1"></i>
            Trang yêu cầu: <span class="badge" :class="getLevelColor(currentPageLevel)">Cấp {{ currentPageLevel }}</span>
          </div>
        </div>
      </div>

      <!-- TABS TRẠNG THÁI ĐƠN HÀNG -->
      <div class="mb-4 overflow-auto custom-scrollbar-x">
        <ul class="nav nav-underline border-bottom dark:border-gray-700 mb-2 pb-1 d-flex flex-nowrap" style="gap: 8px;">
          <li class="nav-item text-nowrap">
            <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab" href="#" :class="{ 'active-tab': activeTab === 'all' }" @click.prevent="switchTab('all')">
              <i class="bi bi-collection me-2"></i> Tất cả
              <span class="badge ms-2 rounded-pill tab-badge" :class="{'active-badge': activeTab === 'all'}">{{ counts.all || 0 }}</span>
            </a>
          </li>
          <li class="nav-item text-nowrap">
            <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab" href="#" :class="{ 'active-tab': activeTab === 'pending' }" @click.prevent="switchTab('pending')">
              <i class="bi bi-hourglass-split me-2 text-warning"></i> Chờ xác nhận
              <span class="badge ms-2 rounded-pill tab-badge" :class="{'active-badge': activeTab === 'pending'}">{{ counts.pending || 0 }}</span>
            </a>
          </li>
          <li class="nav-item text-nowrap">
            <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab" href="#" :class="{ 'active-tab': activeTab === 'confirmed' }" @click.prevent="switchTab('confirmed')">
              <i class="bi bi-box-seam me-2 text-info"></i> Đã xác nhận
              <span class="badge ms-2 rounded-pill tab-badge" :class="{'active-badge': activeTab === 'confirmed'}">{{ counts.confirmed || 0 }}</span>
            </a>
          </li>
          <li class="nav-item text-nowrap">
            <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab" href="#" :class="{ 'active-tab': activeTab === 'processing' }" @click.prevent="switchTab('processing')">
              <i class="bi bi-box2 me-2 text-primary"></i> Đang chuẩn bị
              <span class="badge ms-2 rounded-pill tab-badge" :class="{'active-badge': activeTab === 'processing'}">{{ counts.processing || 0 }}</span>
            </a>
          </li>
          <li class="nav-item text-nowrap">
            <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab" href="#" :class="{ 'active-tab': activeTab === 'shipping' }" @click.prevent="switchTab('shipping')">
              <i class="bi bi-truck me-2 text-primary"></i> Đang giao
              <span class="badge ms-2 rounded-pill tab-badge" :class="{'active-badge': activeTab === 'shipping'}">{{ counts.shipping || 0 }}</span>
            </a>
          </li>
          <li class="nav-item text-nowrap">
            <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab" href="#" :class="{ 'active-tab': activeTab === 'completed' }" @click.prevent="switchTab('completed')">
              <i class="bi bi-check-circle-fill me-2 text-success"></i> Thành công
              <span class="badge ms-2 rounded-pill tab-badge" :class="{'active-badge': activeTab === 'completed'}">{{ counts.completed || 0 }}</span>
            </a>
          </li>
          <li class="nav-item text-nowrap ms-auto">
            <a class="nav-link py-2 px-3 d-flex align-items-center custom-tab text-danger" href="#" :class="{ 'active-tab': activeTab === 'cancelled' }" @click.prevent="switchTab('cancelled')">
              <i class="bi bi-x-circle-fill me-2 text-danger"></i> Đã hủy
              <span class="badge ms-2 rounded-pill tab-badge" :class="{'active-badge': activeTab === 'cancelled', 'bg-danger text-white border-danger': activeTab !== 'cancelled'}">{{ counts.cancelled || 0 }}</span>
            </a>
          </li>
        </ul>
      </div>

      <!-- BỘ LỌC AUTO-FILTER & BULK ACTIONS -->
      <div class="card border-0 shadow-sm rounded-4 mb-4 dark:bg-[#1a2533] animation-fade-in position-relative">
        <div class="card-body p-4">
          
          <!-- THANH CÔNG CỤ XỬ LÝ HÀNG LOẠT (HIỆN KHI CÓ CHỌN) -->
          <div v-if="selectedOrders.length > 0" class="alert alert-info border-info border-opacity-25 bg-info bg-opacity-10 d-flex justify-content-between align-items-center rounded-3 p-3 mb-0 animation-fade-in">
             <div class="d-flex align-items-center">
                 <i class="bi bi-check-all fs-4 text-info me-3"></i>
                 <div>
                     <div class="fw-bold text-info">Đã chọn {{ selectedOrders.length }} đơn hàng</div>
                     <span class="small text-dark dark:text-gray-300">Chọn thao tác để áp dụng cho các đơn hàng này</span>
                 </div>
             </div>
             <div class="d-flex gap-2">
                 <button v-if="activeTab === 'pending'" @click="bulkUpdateStatus('confirmed')" class="btn btn-sm btn-info text-white fw-bold shadow-sm px-3"><i class="bi bi-check2-circle me-1"></i> Xác nhận hàng loạt</button>
                 <button v-if="activeTab === 'confirmed'" @click="bulkUpdateStatus('processing')" class="btn btn-sm btn-primary fw-bold shadow-sm px-3"><i class="bi bi-box-seam me-1"></i> Chuẩn bị hàng loạt</button>
                 <button v-if="activeTab === 'processing'" @click="bulkUpdateStatus('shipping')" class="btn btn-sm btn-primary fw-bold shadow-sm px-3"><i class="bi bi-truck me-1"></i> Giao hàng loạt</button>
                  <button v-if="activeTab === 'shipping'" @click="bulkUpdateStatus('completed')" class="btn btn-sm btn-success fw-bold shadow-sm px-3"><i class="bi bi-check-circle me-1"></i> Giao thành công hàng loạt</button>
                  <button v-if="activeTab === 'shipping'" @click="bulkUpdateStatus('returned')" class="btn btn-sm btn-warning fw-bold shadow-sm px-3 text-dark"><i class="bi bi-arrow-return-left me-1"></i> Hoàn trả hàng loạt</button>
                 
                 <button v-if="['pending', 'confirmed', 'processing'].includes(activeTab)" @click="bulkUpdateStatus('cancelled')" class="btn btn-sm btn-danger fw-bold shadow-sm px-3"><i class="bi bi-x-circle me-1"></i> Hủy hàng loạt</button>
                 
                 <button @click="selectedOrders = []" class="btn btn-sm btn-outline-secondary px-3 fw-bold">Bỏ chọn</button>
             </div>
          </div>

          <div class="row g-3 align-items-end" v-else>
            <div class="col-xl-3 col-md-6">
              <label class="form-label small fw-bold text-muted text-uppercase mb-1"><i class="bi bi-search me-1"></i>Tìm kiếm</label>
              <input type="text" class="form-control shadow-sm-hover bg-light dark:bg-[#212529] dark:text-white border-secondary-subtle dark:border-gray-700" 
                     v-model="filters.search" @input="onSearchInput" placeholder="Mã đơn, SĐT hoặc Tên khách...">
            </div>
            
            <div class="col-xl-2 col-md-6">
              <label class="form-label small fw-bold text-muted text-uppercase mb-1"><i class="bi bi-credit-card me-1"></i>Thanh toán</label>
              <select class="form-select shadow-sm-hover bg-light dark:bg-[#212529] dark:text-white border-secondary-subtle dark:border-gray-700 fw-semibold" 
                      v-model="filters.payment_status" @change="applyFilters">
                <option value="">Tất cả</option>
                <option value="unpaid">Chưa thanh toán</option>
                <option value="paid">Đã thanh toán (Paid)</option>
                <option value="refunded">Đã hoàn tiền (Refunded)</option>
              </select>
            </div>

            <div class="col-xl-4 col-md-8">
              <label class="form-label small fw-bold text-muted text-uppercase mb-1"><i class="bi bi-calendar-range me-1"></i>Thời gian đặt hàng</label>
              <div class="input-group input-group-sm shadow-sm-hover" style="height: 38px;">
                <input type="date" class="form-control border-secondary-subtle dark:border-gray-700 bg-light dark:bg-[#212529] dark:text-white" 
                       v-model="filters.date_from" @change="applyFilters">
                <span class="input-group-text border-secondary-subtle dark:border-gray-700 bg-white dark:bg-[#1a2533]">-</span>
                <input type="date" class="form-control border-secondary-subtle dark:border-gray-700 bg-light dark:bg-[#212529] dark:text-white" 
                       v-model="filters.date_to" @change="applyFilters">
              </div>
            </div>

            <div class="col-xl-3 col-md-4 text-end">
               <div class="d-flex justify-content-end gap-2 align-items-center h-100">
                 <span v-if="isLoading && !isFirstLoad" class="spinner-border spinner-border-sm text-urban me-2" title="Đang tải dữ liệu..."></span>
                 <button class="btn btn-light dark:bg-[#2b3035] dark:text-gray-300 border dark:border-gray-600 px-4 fw-semibold rounded-pill shadow-sm hover-danger transition-all w-100" @click="resetFilters" v-if="hasActiveFilters">
                   <i class="bi bi-x-circle me-1"></i>Xóa bộ lọc
                 </button>
               </div>
            </div>
          </div>
        </div>
      </div>

      <!-- BẢNG DỮ LIỆU ĐƠN HÀNG -->
      <div class="card border-0 shadow-sm rounded-4 mb-4 dark:bg-[#1a2533] transition-all position-relative overflow-hidden">
        <div v-if="isLoading && !isFirstLoad" class="position-absolute top-0 start-0 w-100 h-100 rounded-4 bg-white dark:bg-[#1a2533] d-flex align-items-center justify-content-center" style="z-index: 10; opacity: 0.6;">
           <div class="spinner-border text-urban" style="width: 3rem; height: 3rem;"></div>
        </div>

        <div class="card-body p-0">
          <!-- GIAO DIỆN PC -->
          <div class="table-responsive d-none d-lg-block">
            <table class="table table-hover align-middle mb-0" style="table-layout: fixed; width: 100%; min-width: 1100px;">
              <thead class="bg-light dark:bg-[#212529]">
                <tr>
                  <th class="py-3 px-3 text-center border-0" style="width: 5%;">
                    <input class="form-check-input cursor-pointer" type="checkbox" :checked="isAllSelected" @change="toggleSelectAll" :disabled="selectableOrders.length === 0">
                  </th>
                  <th class="py-3 px-2 text-secondary dark:text-gray-400 border-0" style="width: 15%;">Mã Đơn</th>
                  <th class="py-3 px-2 text-secondary dark:text-gray-400 border-0" style="width: 20%;">Khách hàng</th>
                  <th class="py-3 px-2 text-secondary dark:text-gray-400 border-0 text-end" style="width: 15%;">Tổng thu</th>
                  <th class="py-3 px-2 text-secondary dark:text-gray-400 border-0 text-center" style="width: 15%;">Thanh toán</th>
                  <th class="py-3 px-2 text-secondary dark:text-gray-400 border-0 text-center" style="width: 15%;">Trạng thái</th>
                  <th class="py-3 px-4 text-secondary dark:text-gray-400 text-center border-0" style="width: 15%;">Thao tác</th>
                </tr>
              </thead>
              <tbody class="dark:border-gray-700">
                <tr v-if="orders.length === 0">
                  <td colspan="7" class="text-center py-5 text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>Không có đơn hàng nào.
                  </td>
                </tr>
                <tr v-else v-for="order in orders" :key="order.id" :class="{'bg-light opacity-75 dark:bg-[#121416]': order.deleted_at, 'bg-urban-soft': selectedOrders.includes(order.id)}">
                  <td class="px-3 py-3 text-center">
                    <input class="form-check-input cursor-pointer" type="checkbox" :value="order.id" v-model="selectedOrders" v-if="canSelect(order)">
                  </td>
                  
                  <td class="px-2 py-3 font-monospace fw-bold text-urban">
                    <router-link :to="{ name: 'admin-orders-edit', params: { id: order.id } }" class="text-decoration-none transition-all hover-text-danger" :class="getOrderStatusTextColor(order.status)">
                      #{{ order.order_code }}
                    </router-link>
                    <div class="text-muted small fw-normal mt-1" style="font-size: 0.7rem;">{{ formatDateTime(order.created_at) }}</div>
                  </td>
                  
                  <td class="px-2 py-3">
                    <div class="d-flex align-items-center">
                      <div class="bg-light dark:bg-[#212529] rounded-circle d-flex align-items-center justify-content-center me-3 border shadow-sm dark:border-gray-600 flex-shrink-0" style="width: 40px; height: 40px;">
                         <i class="bi bi-person-fill text-muted fs-5"></i>
                      </div>
                      <div class="overflow-hidden">
                        <h6 class="mb-0 fw-bold text-dark dark:text-gray-200 text-truncate">{{ getCustomerName(order) }}</h6>
                        <small class="text-muted dark:text-gray-400 d-block mt-1 text-truncate font-monospace">{{ getCustomerPhone(order) }}</small>
                      </div>
                    </div>
                  </td>
                  
                  <td class="px-2 text-end">
                    <div class="fw-bold text-danger fs-6">{{ formatCurrency(order.total_amount) }}</div>
                    <div class="text-muted mt-1 d-flex justify-content-end flex-wrap gap-1" style="font-size: 0.7rem;">
                      <span v-if="getComboCount(order.items) > 0" class="badge bg-secondary bg-opacity-10 text-secondary border fw-medium" title="Combo">
                        <i class="bi bi-magic me-1"></i>{{ getComboCount(order.items) }}
                      </span>
                      <span v-if="getRetailCount(order.items) > 0" class="badge bg-light dark:bg-[#2b3035] text-dark dark:text-gray-300 border dark:border-gray-600 fw-medium" title="Sản phẩm lẻ">
                        <i class="bi bi-box-seam me-1"></i>{{ getRetailCount(order.items) }}
                      </span>
                    </div>
                  </td>

                  <td class="px-2 text-center">
                    <span class="badge border px-3 py-2 shadow-sm w-100" :class="getPaymentStatusClass(order.payment_status)">
                      {{ getPaymentStatusLabel(order.payment_status) }}
                    </span>
                    <div class="text-muted mt-1 font-monospace" style="font-size: 0.65rem;">{{ order.payment_method ? order.payment_method.toUpperCase() : 'N/A' }}</div>
                  </td>

                  <td class="px-2 text-center">
                    <div class="d-flex align-items-center justify-content-center">
                      <AdminStatusUpdate 
                        v-model="order.localStatus"
                        :originalStatus="order.status"
                        :isUpdating="order.isUpdatingStatus"
                        :options="getOrderStatusOptions(order)"
                        :selectClass="getOrderStatusClass(order.localStatus)"
                        @save="saveOrderStatus(order)"
                        @cancel="cancelStatusChange(order)"
                        :disabled="getValidTransitions(order).length === 0"
                      />
                    </div>
                  </td>

                  <td class="px-4 text-center">
                    <div class="d-flex justify-content-center gap-2 font-sans-vn">
                      
                      <!-- NEXT STATUS QUICK ACTION -->
                      <button v-if="order.status === 'pending'" @click="updateSingleStatus(order.id, 'confirmed')" class="btn btn-sm btn-light dark:bg-[#2b3035] dark:border-gray-600 text-info shadow-sm border fw-semibold" title="Xác nhận đơn" :disabled="processingOrderId === order.id">
                        <span v-if="processingOrderId === order.id" class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                        <i v-else class="bi bi-check2-circle"></i>
                      </button>
                      <button v-else-if="order.status === 'confirmed'" @click="updateSingleStatus(order.id, 'processing')" class="btn btn-sm btn-light dark:bg-[#2b3035] dark:border-gray-600 text-primary shadow-sm border fw-semibold" title="Đang chuẩn bị" :disabled="processingOrderId === order.id">
                        <span v-if="processingOrderId === order.id" class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                        <i v-else class="bi bi-box-seam"></i>
                      </button>
                      <button v-else-if="order.status === 'processing'" @click="updateSingleStatus(order.id, 'shipping')" class="btn btn-sm btn-light dark:bg-[#2b3035] dark:border-gray-600 text-primary shadow-sm border fw-semibold" title="Giao hàng" :disabled="processingOrderId === order.id">
                        <span v-if="processingOrderId === order.id" class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                        <i v-else class="bi bi-truck"></i>
                      </button>

                      <router-link :to="{ name: 'admin-orders-edit', params: { id: order.id } }" class="btn btn-sm btn-light dark:bg-[#2b3035] dark:border-gray-600 text-urban shadow-sm border fw-semibold" title="Xem chi tiết">
                        <i class="bi bi-eye-fill"></i>
                      </router-link>

                      <!-- CANCEL QUICK ACTION -->
                      <button v-if="['pending', 'confirmed', 'processing'].includes(order.status)" @click="updateSingleStatus(order.id, 'cancelled')" class="btn btn-sm btn-light dark:bg-[#2b3035] dark:border-gray-600 text-danger shadow-sm border px-3" title="Hủy đơn" :disabled="processingOrderId === order.id">
                        <span v-if="processingOrderId === order.id" class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                        <i v-else class="bi bi-trash"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- GIAO DIỆN MOBILE -->
          <div class="d-block d-lg-none p-3 bg-light dark:bg-[#121416]">
            <div v-if="orders.length === 0" class="text-center py-5 text-muted">Không có dữ liệu.</div>
            <div v-else class="d-flex flex-column gap-3">
              <div v-for="order in orders" :key="order.id" class="card border-0 shadow-sm rounded-4 dark:bg-[#212529]" :class="{'opacity-75': order.deleted_at}">
                <div class="card-body p-3 position-relative">
                  
                  <div class="position-absolute top-0 end-0 p-3" v-if="canSelect(order)">
                     <input class="form-check-input cursor-pointer" type="checkbox" :value="order.id" v-model="selectedOrders" style="width: 1.25rem; height: 1.25rem;">
                  </div>

                  <div class="d-flex justify-content-between align-items-center mb-3 border-bottom dark:border-gray-700 pb-2 pe-5">
                     <div>
                       <div class="fw-bold font-monospace" :class="getOrderStatusTextColor(order.status)">#{{ order.order_code }}</div>
                       <small class="text-muted">{{ formatDateTime(order.created_at) }}</small>
                     </div>
                     <div>
                        <AdminStatusUpdate 
                          v-model="order.localStatus"
                          :originalStatus="order.status"
                          :isUpdating="order.isUpdatingStatus"
                          :options="getOrderStatusOptions(order)"
                          :selectClass="getOrderStatusClass(order.localStatus)"
                          @save="saveOrderStatus(order)"
                          @cancel="cancelStatusChange(order)"
                          :disabled="getValidTransitions(order).length === 0"
                        />
                     </div>
                  </div>

                  <div class="d-flex align-items-center mb-3">
                    <div class="bg-light dark:bg-[#1a2533] rounded-circle d-flex align-items-center justify-content-center me-2 border shadow-sm dark:border-gray-600 flex-shrink-0" style="width: 35px; height: 35px;">
                       <i class="bi bi-person-fill text-muted"></i>
                    </div>
                    <div class="overflow-hidden w-100">
                      <div class="fw-bold dark:text-gray-200 text-truncate" style="font-size: 0.9rem;">{{ getCustomerName(order) }}</div>
                      <small class="text-muted dark:text-gray-400 font-monospace">{{ getCustomerPhone(order) }}</small>
                    </div>
                  </div>
                  
                  <div class="d-flex justify-content-between align-items-end pt-2 border-top dark:border-gray-700 mt-2 mb-3">
                    <div>
                      <div class="d-flex gap-1 mb-1">
                        <span v-if="getComboCount(order.items) > 0" class="badge bg-secondary bg-opacity-10 text-secondary border fw-medium" style="font-size: 0.65rem;">
                          <i class="bi bi-magic"></i> {{ getComboCount(order.items) }} Combo
                        </span>
                        <span v-if="getRetailCount(order.items) > 0" class="badge bg-light dark:bg-[#2b3035] text-dark dark:text-gray-300 border dark:border-gray-600 fw-medium" style="font-size: 0.65rem;">
                          <i class="bi bi-box-seam"></i> {{ getRetailCount(order.items) }} SP
                        </span>
                      </div>
                      <span class="badge border px-2 py-1 mt-1" :class="getPaymentStatusClass(order.payment_status)">{{ getPaymentStatusLabel(order.payment_status) }}</span>
                    </div>
                    <div class="text-danger fw-bold fs-5">
                      {{ formatCurrency(order.total_amount) }}
                    </div>
                  </div>

                  <div class="d-flex gap-2">
                      <button v-if="order.status === 'pending'" @click="updateSingleStatus(order.id, 'confirmed')" class="btn btn-info text-white shadow-sm fw-bold btn-sm py-2 px-3" :disabled="processingOrderId === order.id">
                        <span v-if="processingOrderId === order.id" class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                        <i v-else class="bi bi-check2-circle"></i>
                      </button>
                      <button v-else-if="order.status === 'confirmed'" @click="updateSingleStatus(order.id, 'processing')" class="btn btn-primary shadow-sm fw-bold btn-sm py-2 px-3" :disabled="processingOrderId === order.id">
                        <span v-if="processingOrderId === order.id" class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                        <i v-else class="bi bi-box-seam"></i>
                      </button>
                      <button v-else-if="order.status === 'processing'" @click="updateSingleStatus(order.id, 'shipping')" class="btn btn-primary shadow-sm fw-bold btn-sm py-2 px-3" :disabled="processingOrderId === order.id">
                        <span v-if="processingOrderId === order.id" class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                        <i v-else class="bi bi-truck"></i>
                      </button>

                      <router-link :to="{ name: 'admin-orders-edit', params: { id: order.id } }" class="btn btn-urban text-white shadow-sm fw-bold btn-sm w-100 py-2 d-flex justify-content-center align-items-center" :class="{'disabled': processingOrderId === order.id}">
                        CHI TIẾT
                      </router-link>

                      <button v-if="['pending', 'confirmed', 'processing'].includes(order.status)" @click="updateSingleStatus(order.id, 'cancelled')" class="btn btn-outline-danger shadow-sm fw-bold btn-sm py-2 px-3" :disabled="processingOrderId === order.id">
                        <span v-if="processingOrderId === order.id" class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                        <i v-else class="bi bi-trash"></i>
                      </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="d-flex flex-column flex-md-row justify-content-between align-items-center p-3 border-top dark:border-gray-700 gap-3" v-if="pagination.last_page > 1">
        <span class="text-muted dark:text-gray-400 small">Trang {{ pagination.current_page }} / {{ pagination.last_page }}</span>
        <nav>
          <ul class="pagination pagination-sm mb-0 shadow-sm flex-wrap justify-content-center">
            <li class="page-item" :class="{ disabled: pagination.current_page === 1 }"><button class="page-link text-urban dark:bg-[#212529] dark:border-gray-600" @click="changePage(pagination.current_page - 1)"><i class="bi bi-chevron-left"></i></button></li>
            
            <li class="page-item" v-for="page in pagination.last_page" :key="page" :class="{ active: pagination.current_page === page }">
              <button class="page-link dark:border-gray-600" :class="pagination.current_page === page ? 'bg-urban border-urban text-white' : 'text-dark dark:text-gray-300 dark:bg-[#212529]'" @click="changePage(page)">{{ page }}</button>
            </li>

            <li class="page-item" :class="{ disabled: pagination.current_page === pagination.last_page }"><button class="page-link text-urban dark:bg-[#212529] dark:border-gray-600" @click="changePage(pagination.current_page + 1)"><i class="bi bi-chevron-right"></i></button></li>
          </ul>
        </nav>
      </div>
    </div>

    <!-- EXCEL EXPORT FLOATING ACTION BUTTON (FAB) -->
    <button data-bs-toggle="modal" data-bs-target="#exportExcelModal"
            class="btn btn-success rounded-circle shadow-lg position-fixed d-flex align-items-center justify-content-center border-0" 
            style="bottom: 30px; right: 30px; width: 60px; height: 60px; z-index: 999; background-color: #198754; transition: transform 0.2s;"
            onmouseover="this.style.transform='scale(1.1)'"
            onmouseout="this.style.transform='scale(1)'"
            title="Tùy chọn Xuất Excel Nâng Cao">
      <i class="bi bi-file-earmark-excel fs-3 text-white"></i>
    </button>

    <!-- EXCEL EXPORT MODAL -->
    <div class="modal fade" id="exportExcelModal" tabindex="-1" aria-labelledby="exportExcelModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
          <div class="modal-header bg-success text-white border-0">
            <h5 class="modal-title fw-bold" id="exportExcelModalLabel"><i class="bi bi-file-earmark-excel me-2"></i>Xuất Excel Nâng Cao</h5>
            <button type="button" class="btn btn-link text-white p-0 border-0 fs-5" data-bs-dismiss="modal" aria-label="Close">
              <i class="bi bi-x-lg"></i>
            </button>
          </div>
          <div class="modal-body p-4">
            
            <!-- Export Type -->
            <div class="mb-4">
              <label class="form-label fw-bold small text-muted text-uppercase mb-2">Phạm vi xuất</label>
              <select class="form-select form-select-sm border-gray-300" v-model="exportConfig.type">
                <option value="all">Toàn bộ đơn hàng (Theo bộ lọc bên dưới)</option>
                <option value="selected">Chỉ xuất các đơn hàng đang chọn ({{ selectedOrders.length }} đơn)</option>
              </select>
            </div>

            <!-- Date Range -->
            <div class="mb-4" v-if="exportConfig.type === 'all'">
              <label class="form-label fw-bold small text-muted text-uppercase mb-2">Thời gian tạo đơn</label>
              <div class="d-flex gap-2 mb-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" @click="setExportDate('today')">Hôm nay</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" @click="setExportDate('this_week')">Tuần này</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" @click="setExportDate('this_month')">Tháng này</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" @click="setExportDate('all')">Tất cả thời gian</button>
              </div>
              <div class="row g-2">
                <div class="col-6">
                  <input type="date" class="form-control form-control-sm" v-model="exportConfig.date_from" title="Từ ngày">
                </div>
                <div class="col-6">
                  <input type="date" class="form-control form-control-sm" v-model="exportConfig.date_to" title="Đến ngày">
                </div>
              </div>
            </div>

            <!-- Statuses -->
            <div class="mb-4" v-if="exportConfig.type === 'all'">
              <label class="form-label fw-bold small text-muted text-uppercase mb-2">Trạng thái đơn hàng</label>
              <div class="d-flex flex-wrap gap-2">
                <div class="form-check form-check-inline m-0" v-for="status in exportStatusOptions" :key="status.value">
                  <input class="form-check-input" type="checkbox" :id="'exp_st_'+status.value" :value="status.value" v-model="exportConfig.statuses">
                  <label class="form-check-label small" :for="'exp_st_'+status.value">{{ status.label }}</label>
                </div>
              </div>
            </div>

            <!-- Split By -->
            <div class="mb-2">
              <label class="form-label fw-bold small text-muted text-uppercase mb-2">Chia trang tính (Sheet)</label>
              <select class="form-select form-select-sm border-gray-300" v-model="exportConfig.split_by">
                <option value="">Gộp chung 1 Sheet (Mặc định)</option>
                <option value="status">Chia Sheet theo Trạng thái đơn hàng</option>
                <option value="month">Chia Sheet theo Tháng</option>
              </select>
            </div>

          </div>
          <div class="modal-footer bg-light border-0">
            <button type="button" class="btn btn-light border fw-bold" data-bs-dismiss="modal">Hủy</button>
            <button type="button" class="btn btn-success fw-bold px-4" @click="executeAdvancedExport" :disabled="isExporting">
              <span v-if="isExporting" class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>
              <i v-else class="bi bi-download me-2"></i> Xuất File
            </button>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import { ZyroSwal } from '@/components/client/ZyroSwal';
import LoadingDots from '@/components/admin/LoadingDots.vue';
import AdminStatusUpdate from '@/components/admin/AdminStatusUpdate.vue';

const route = useRoute();

const orders = ref([]);
const counts = ref({});
const systemModules = ref([]);
const currentPageLevel = ref(null);

const isLoading = ref(false);
const processingOrderId = ref(null);
const isFirstLoad = ref(true);

const isExporting = ref(false);

const exportStatusOptions = [
  { value: 'pending', label: 'Chờ xác nhận' },
  { value: 'confirmed', label: 'Đã xác nhận' },
  { value: 'processing', label: 'Đang chuẩn bị' },
  { value: 'shipping', label: 'Đang giao' },
  { value: 'completed', label: 'Thành công' },
  { value: 'cancelled', label: 'Đã hủy' },
  { value: 'returned', label: 'Hoàn trả' },
];

const exportConfig = ref({
  type: 'all',
  date_from: '',
  date_to: '',
  statuses: [],
  split_by: 'status'
});

const setExportDate = (mode) => {
  const today = new Date();
  if (mode === 'all') {
    exportConfig.value.date_from = '';
    exportConfig.value.date_to = '';
  } else if (mode === 'today') {
    const d = today.toISOString().split('T')[0];
    exportConfig.value.date_from = d;
    exportConfig.value.date_to = d;
  } else if (mode === 'this_week') {
    const firstDay = new Date(today.setDate(today.getDate() - today.getDay() + 1));
    const lastDay = new Date(today.setDate(today.getDate() - today.getDay() + 7));
    exportConfig.value.date_from = firstDay.toISOString().split('T')[0];
    exportConfig.value.date_to = lastDay.toISOString().split('T')[0];
  } else if (mode === 'this_month') {
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
    const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
    exportConfig.value.date_from = firstDay.toISOString().split('T')[0];
    exportConfig.value.date_to = lastDay.toISOString().split('T')[0];
  }
}; 

// Backend Pagination & Filters
const pagination = ref({ current_page: 1, last_page: 1, total: 0 });
const filters = ref({
  search: '',
  status: '', 
  payment_status: '',
  date_from: '',
  date_to: ''
});

const activeTab = ref('all'); 
let searchTimeout = null;

// Bulk Selection
const selectedOrders = ref([]);

const getHeaders = () => ({ 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('admin_token')}` });

const formatCurrency = (val) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val || 0);
const formatDateTime = (dateString) => {
  if(!dateString) return '';
  const d = new Date(dateString);
  return `${d.toLocaleDateString('vi-VN')} ${d.toLocaleTimeString('vi-VN', {hour: '2-digit', minute:'2-digit'})}`;
};

const getLevelColor = (level) => {
  const map = { 1: 'bg-danger text-white', 2: 'bg-warning text-dark', 3: 'bg-info text-dark', 4: 'bg-primary bg-opacity-10 text-primary', 5: 'bg-success bg-opacity-10 text-success' };
  return map[level] || 'bg-secondary';
};

const getOrderStatusClass = (status) => {
  const map = {
    'pending': 'bg-warning bg-opacity-10 text-warning border-warning',
    'confirmed': 'bg-info bg-opacity-10 text-info border-info',
    'processing': 'bg-primary bg-opacity-10 text-primary border-primary',
    'shipping': 'bg-primary bg-opacity-10 text-primary border-primary',
    'completed': 'bg-success bg-opacity-10 text-success border-success',
    'cancelled': 'bg-danger bg-opacity-10 text-danger border-danger',
    'returned': 'bg-secondary bg-opacity-10 text-secondary border-secondary',
    'refunded': 'bg-dark bg-opacity-10 text-dark border-dark'
  };
  return map[status] || 'bg-light text-secondary border-secondary';
};

const getOrderStatusTextColor = (status) => {
  const map = {
    'pending': 'text-warning',
    'confirmed': 'text-info',
    'processing': 'text-primary',
    'shipping': 'text-primary',
    'completed': 'text-success',
    'cancelled': 'text-danger',
    'returned': 'text-secondary',
    'refunded': 'text-dark'
  };
  return map[status] || 'text-urban';
};

const getOrderStatusLabel = (status) => {
  const map = {
    'pending': 'Chờ xác nhận',
    'confirmed': 'Đã xác nhận',
    'processing': 'Đang chuẩn bị',
    'shipping': 'Đang giao',
    'completed': 'Thành công',
    'cancelled': 'Đã hủy',
    'returned': 'Đã hoàn trả',
    'refunded': 'Đã hoàn tiền'
  };
  return map[status] || status;
};

const getOrderStatusIcon = (status) => {
  const map = {
    'pending': 'bi-hourglass-split',
    'confirmed': 'bi-check2-circle',
    'processing': 'bi-box-seam',
    'shipping': 'bi-truck',
    'completed': 'bi-check-circle-fill',
    'cancelled': 'bi-x-circle-fill',
    'returned': 'bi-arrow-return-left',
    'refunded': 'bi-cash-coin'
  };
  return map[status] || 'bi-record-circle';
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

const getCustomerName = (order) => {
  if (order.user) return order.user.full_name;
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

const getComboCount = (items) => {
  if (!items || items.length === 0) return 0;
  const comboIds = new Set();
  items.forEach(item => {
      if (item.lookbook_id) comboIds.add(item.lookbook_id);
  });
  return comboIds.size;
};

const getRetailCount = (items) => {
  if (!items || items.length === 0) return 0;
  return items.filter(item => !item.lookbook_id).reduce((sum, item) => sum + item.quantity, 0);
};



const exportOrdersExcel = async () => {
    if (selectedOrders.value.length === 0) return;
    try {
        const res = await axios.post('/admin/orders/export', { ids: selectedOrders.value }, { responseType: 'blob' });
        const url = window.URL.createObjectURL(new Blob([res.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', 'zyro_orders_export.xlsx');
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        ZyroSwal.toastSuccess('Xuất file thành công!');
    } catch (error) {
        ZyroSwal.toastError('Lỗi xuất file Excel');
    }
};

const handleImportTracking = async (event) => {
    const file = event.target.files[0];
    if (!file) return;

    const { value: provider } = await window.Swal.fire({
      title: 'Chọn Hãng Vận Chuyển',
      text: 'Vui lòng chọn hãng vận chuyển tương ứng với file Excel này:',
      input: 'select',
      inputOptions: {
        'ghtk': 'Giao Hàng Tiết Kiệm (GHTK)',
        'ghn': 'Giao Hàng Nhanh (GHN)',
        'viettel_post': 'Viettel Post',
        'jt': 'J&T Express'
      },
      inputPlaceholder: 'Chọn ĐVVC',
      showCancelButton: true,
      confirmButtonText: 'Tiến hành Import',
      cancelButtonText: 'Hủy'
    });

    if (provider) {
        const formData = new FormData();
        formData.append('file', file);
        formData.append('provider', provider);

        ZyroSwal.fire({ title: 'Đang xử lý...', didOpen: () => { window.Swal.showLoading(); }});
        
        try {
            const res = await axios.post('/admin/orders/import-tracking', formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });
            window.Swal.fire('Thành công!', res.data.message, 'success');
            fetchOrders();
            selectedOrders.value = [];
        } catch (error) {
            window.Swal.fire('Lỗi', error.response?.data?.message || 'Có lỗi xảy ra', 'error');
        }
    }
    event.target.value = ''; // Reset input
};

const printSelectedOrders = () => {
    if (selectedOrders.value.length === 0) return;
    const url = router.resolve({ name: 'admin-orders-print', query: { ids: selectedOrders.value.join(',') } }).href;
    window.open(url, '_blank');
};

const getValidTransitions = (order) => {
    const status = order.status;
    const validTransitions = {
        'pending': ['confirmed', 'cancelled'],
        'confirmed': ['processing', 'shipping', 'cancelled'],
        'processing': ['shipping', 'cancelled'],
        'shipping': ['completed', 'returned'],
        'completed': ['returned'],
        'cancelled': [],
        'returned': [],
        'refunded': []
    };
    let allowed = validTransitions[status] || [];
    
    // Rule: Online payment + unpaid -> ONLY allow cancellation
    const isOnline = ['MOMO', 'VNPAY', 'ZALOPAY', 'PAYPAL', 'STRIPE'].includes(order.payment_method?.toUpperCase());
    if (isOnline && order.payment_status === 'unpaid') {
        allowed = allowed.filter(st => st === 'cancelled');
    }
    return allowed;
};

// Selection Logic
const canSelect = (order) => {
    return !order.deleted_at && !['completed', 'cancelled', 'returned', 'refunded'].includes(order.status);
};

const selectableOrders = computed(() => {
    return orders.value.filter(o => canSelect(o));
});

const isAllSelected = computed(() => {
    return selectableOrders.value.length > 0 && selectedOrders.value.length === selectableOrders.value.length;
});

const toggleSelectAll = (e) => {
    if (e.target.checked) {
        selectedOrders.value = selectableOrders.value.map(o => o.id);
    } else {
        selectedOrders.value = [];
    }
};

const getOrderStatusOptions = (order) => {
    const valid = getValidTransitions(order);
    const options = [
        { value: order.status, label: getOrderStatusLabel(order.status) }
    ];
    valid.forEach(st => {
        options.push({ value: st, label: getOrderStatusLabel(st) });
    });
    return options;
};

const saveOrderStatus = async (order) => {
    if (order.localStatus === 'completed') {
        if (order.payment_status === 'unpaid') {
            const confirmed = await ZyroSwal.fire({
                title: 'Chưa Thanh Toán',
                text: 'Đơn hàng này chưa được thanh toán. Bạn có chắc chắn muốn xác nhận giao thành công và chuyển trạng thái sang Đã Thanh Toán?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#0dcaf0',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Đồng ý & Đã thu tiền',
                cancelButtonText: 'Hủy'
            });
            if (!confirmed.isConfirmed) {
                cancelStatusChange(order);
                return;
            }
        }
    }
    order.isUpdatingStatus = true;
    try {
      const res = await axios.patch(`${import.meta.env.VITE_API_BASE_URL}/admin/orders/${order.id}/status`, { status: order.localStatus, payment_status: order.localStatus === "completed" ? "paid" : undefined }, { headers: getHeaders() });
      
      ZyroSwal.toastSuccess(res.data.message);
      order.status = order.localStatus;
      fetchData();
    } catch (e) {
      ZyroSwal.toastError(e.response?.data?.message || 'Có lỗi xảy ra!');
      order.localStatus = order.status;
    } finally {
      order.isUpdatingStatus = false;
    }
};

const cancelStatusChange = (order) => {
    order.localStatus = order.status;
};

// Quick Actions
const updateSingleStatus = async (orderId, newStatus) => {
  const actionName = getOrderStatusLabel(newStatus).toLowerCase();
  
  let confirmText = `Bạn có chắc muốn chuyển trạng thái đơn hàng này sang "${getOrderStatusLabel(newStatus)}"?`;
  if(newStatus === 'cancelled') confirmText = 'Bạn có chắc chắn muốn hủy đơn hàng này không? Không thể hoàn tác!';
    if(newStatus === 'completed') confirmText = 'Có chắc đơn hàng này đã được thanh toán và xác nhận đã giao? (Sẽ tự động cập nhật trạng thái đã thanh toán)';

  const confirmed = await ZyroSwal.fire({
    title: newStatus === 'completed' ? 'Xác nhận Đã Giao Hàng' : 'Xác nhận thay đổi',
    text: confirmText,
    icon: newStatus === 'cancelled' ? 'warning' : 'info',
    showCancelButton: true,
    confirmButtonColor: newStatus === 'cancelled' ? '#dc3545' : '#0dcaf0',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Đồng ý',
    cancelButtonText: 'Không'
  });

  if (confirmed.isConfirmed) {
    processingOrderId.value = orderId;
    try {
      const res = await axios.patch(`${import.meta.env.VITE_API_BASE_URL}/admin/orders/${orderId}/status`, { status: newStatus, payment_status: newStatus === "completed" ? "paid" : undefined }, { headers: getHeaders() });
      
      ZyroSwal.toastSuccess(res.data.message);
      fetchData();
    } catch (e) {
      ZyroSwal.toastError(e.response?.data?.message || 'Có lỗi xảy ra!');
    } finally {
      processingOrderId.value = null;
    }
  }
};

const executeAdvancedExport = async () => {
    if (exportConfig.value.type === 'selected' && selectedOrders.value.length === 0) {
        return ZyroSwal.toastError('Bạn chưa chọn đơn hàng nào!');
    }
    
    isExporting.value = true;
    try {
        const payload = {
            export_type: exportConfig.value.type,
            order_ids: exportConfig.value.type === 'selected' ? selectedOrders.value : [],
            date_from: exportConfig.value.date_from,
            date_to: exportConfig.value.date_to,
            statuses: exportConfig.value.statuses,
            split_by: exportConfig.value.split_by
        };
        
        const res = await axios.post(`${import.meta.env.VITE_API_BASE_URL}/admin/orders/export`, payload, { 
            headers: getHeaders(),
            responseType: 'blob' 
        });
        
        const url = window.URL.createObjectURL(new Blob([res.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `don_hang_zyro_${new Date().getTime()}.xlsx`);
        document.body.appendChild(link);
        link.click();
        link.remove();
        ZyroSwal.toastSuccess('Xuất file thành công!');
        
        // Đóng modal tự động bằng cách mô phỏng click
        const closeBtn = document.querySelector('#exportExcelModal [data-bs-dismiss="modal"]');
        if (closeBtn) closeBtn.click();
        
        // Backup: đóng thủ công qua API
        if (window.bootstrap && window.bootstrap.Modal) {
            const modalEl = document.getElementById('exportExcelModal');
            const modalIns = window.bootstrap.Modal.getInstance(modalEl);
            if (modalIns) modalIns.hide();
        }
    } catch (error) {
        if (error.response && error.response.data instanceof Blob) {
            const text = await error.response.data.text();
            try {
                const json = JSON.parse(text);
                ZyroSwal.toastError(json.message || 'Lỗi khi xuất file');
            } catch (e) {
                ZyroSwal.toastError('Lỗi khi xuất file');
            }
        } else {
            ZyroSwal.toastError(error.response?.data?.message || 'Lỗi khi xuất file');
        }
    } finally {
        isExporting.value = false;
    }
};

const bulkUpdateStatus = async (newStatus) => {
  if(selectedOrders.value.length === 0) return;

  const actionName = getOrderStatusLabel(newStatus).toLowerCase();
  
  let confirmText = `Bạn có chắc muốn chuyển trạng thái cho ${selectedOrders.value.length} đơn hàng sang "${getOrderStatusLabel(newStatus)}"?`;
  if(newStatus === 'cancelled') confirmText = `Bạn có chắc chắn muốn hủy ${selectedOrders.value.length} đơn hàng này không? Không thể hoàn tác!`;
    if(newStatus === 'completed') confirmText = 'Có chắc đơn hàng này đã được thanh toán và xác nhận đã giao? (Sẽ tự động cập nhật trạng thái đã thanh toán)';

  const confirmed = await ZyroSwal.fire({
    title: newStatus === 'completed' ? 'Xác nhận Đã Giao Hàng' : 'Xác nhận thao tác hàng loạt',
    text: confirmText,
    icon: newStatus === 'cancelled' ? 'warning' : 'info',
    showCancelButton: true,
    confirmButtonColor: newStatus === 'cancelled' ? '#dc3545' : '#0dcaf0',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Đồng ý thao tác',
    cancelButtonText: 'Hủy'
  });

  if (confirmed.isConfirmed) {
    isLoading.value = true;
    try {
      const res = await axios.post(`${import.meta.env.VITE_API_BASE_URL}/admin/orders/bulk-status`, { order_ids: selectedOrders.value, status: newStatus, payment_status: newStatus === "completed" ? "paid" : undefined }, { headers: getHeaders() });
      
      ZyroSwal.toastSuccess(res.data.message);
      selectedOrders.value = [];
      fetchData();
    } catch (e) {
      ZyroSwal.toastError(e.response?.data?.message || 'Có lỗi xảy ra!');
    } finally {
      isLoading.value = false;
    }
  }
};

// Lọc & Phân trang
const hasActiveFilters = computed(() => {
  return filters.value.search !== '' || filters.value.payment_status !== '' || filters.value.date_from !== '' || filters.value.date_to !== '';
});

const resetFilters = () => {
  filters.value = { search: '', status: '', payment_status: '', date_from: '', date_to: '' };
  activeTab.value = 'all';
  applyFilters();
};

const applyFilters = () => {
  pagination.value.current_page = 1;
  selectedOrders.value = [];
  fetchData();
};

const onSearchInput = () => {
  if (searchTimeout) clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => { applyFilters(); }, 500); 
};

const switchTab = (tab) => {
  activeTab.value = tab;
  if (tab === 'all') {
      filters.value.status = '';
  } else if (tab === 'processing') {
      filters.value.status = 'processing';
  } else if (tab === 'confirmed') {
      filters.value.status = 'confirmed'; 
  } else {
      filters.value.status = tab;
  }
  applyFilters();
};

const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    pagination.value.current_page = page;
    selectedOrders.value = [];
    fetchData();
  }
};

const fetchData = async (isSilent = false) => {
  if (!isSilent) isLoading.value = true;
  
  try {
    if (isFirstLoad.value) {
        const resModules = await axios.get(`${import.meta.env.VITE_API_BASE_URL}/admin/modules`, { headers: getHeaders() });
        systemModules.value = resModules.data.data;
        const currentModule = systemModules.value.find(m => m.module_code === (route.meta?.moduleCode || 'admin_orders'));
        if (currentModule) currentPageLevel.value = currentModule.required_level;
    }

    const params = new URLSearchParams();
    params.append('page', pagination.value.current_page);
    
    if(filters.value.status) {
        params.append('status', filters.value.status); 
    }

    if(filters.value.payment_status) params.append('payment_status', filters.value.payment_status);
    if(filters.value.search) params.append('search', filters.value.search);
    if(filters.value.date_from) params.append('date_from', filters.value.date_from);
    if(filters.value.date_to) params.append('date_to', filters.value.date_to);

    const res = await axios.get(`${import.meta.env.VITE_API_BASE_URL}/admin/orders?${params.toString()}`, { headers: getHeaders() });
    
    orders.value = (res.data.data.data || []).map(o => ({
       ...o,
       localStatus: o.status,
       isUpdatingStatus: false
    }));
    counts.value = res.data.counts || {};
    pagination.value = {
        current_page: res.data.data.current_page,
        last_page: res.data.data.last_page,
        total: res.data.data.total
    };
    
  } catch (err) { 
    console.error('Lỗi khi tải dữ liệu', err); 
  } finally { 
    isLoading.value = false;
    isFirstLoad.value = false;
  }
};

const setupRealtime = () => {
  if (window.Echo) {
    window.Echo.private('admin.orders').listen('.OrderEvent', () => { fetchData(true); });
  }
};

onMounted(() => { fetchData(); setupRealtime(); });
onUnmounted(() => { if (window.Echo) window.Echo.leave('admin.orders'); });
</script>

<style scoped>
.text-urban { color: var(--color-c-hover, #547792) !important; }
.bg-urban { background-color: var(--color-c-hover, #547792) !important; }
.bg-urban-soft { background-color: rgba(84, 119, 146, 0.1) !important; }
.border-urban { border-color: var(--color-c-hover, #547792) !important; }
.btn-urban { background-color: var(--color-c-hover, #547792); color: white; border: none; transition: 0.2s; }
.btn-urban:hover { background-color: var(--color-c-dark, #213448); color: white; transform: translateY(-2px); }
.hover-text-danger:hover { color: #dc3545 !important; }

/* TABS STYLING */
.custom-tab { font-weight: 600 !important; color: #6c757d; border-bottom: 2px solid transparent !important; margin-bottom: -1px; transition: color 0.2s ease; }
.custom-tab:hover, .custom-tab.active-tab { color: var(--color-c-hover, #547792) !important; }
.custom-tab.active-tab { border-bottom-color: var(--color-c-hover, #547792) !important; }

.tab-badge { font-size: 0.75rem; font-weight: 600; background-color: #f8f9fa; color: #6c757d; border: 1px solid #dee2e6; transition: all 0.2s ease; }
.active-badge { background-color: rgba(84, 119, 146, 0.1) !important; color: var(--color-c-hover, #547792) !important; border-color: var(--color-c-hover, #547792) !important; }

html.dark .tab-badge { background-color: #2b3035; color: #adb5bd; border-color: #495057; }
html.dark .active-badge { background-color: rgba(255, 255, 255, 0.1) !important; color: #fff !important; border-color: #fff !important; }

.shadow-sm-hover { transition: box-shadow 0.2s ease, border-color 0.2s ease; }
.shadow-sm-hover:focus-within { box-shadow: 0 4px 15px rgba(84, 119, 146, 0.1) !important; border-color: var(--color-c-hover, #547792) !important; }
.form-control:focus, .form-select:focus { border-color: var(--color-c-hover, #547792); box-shadow: none !important; }

.hover-danger:hover { color: #dc3545 !important; border-color: #dc3545 !important; }

.logo-shimmer { font-size: 3.5rem; font-weight: 900; letter-spacing: -1.5px; background: linear-gradient(120deg, var(--color-c-dark) 30%, var(--color-c-light) 50%, var(--color-c-dark) 70%); background-size: 200% auto; color: transparent; -webkit-background-clip: text; background-clip: text; animation: shine 1.5s linear infinite; }
@keyframes shine { to { background-position: 200% center; } }

.transition-all { transition: all 0.3s ease; }
.animation-fade-in { animation: fadeIn 0.4s ease-in-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

.custom-scrollbar-x::-webkit-scrollbar { height: 6px; }
.custom-scrollbar-x::-webkit-scrollbar-thumb { background: var(--color-c-light, #94B4C1); border-radius: 10px; }

/* CHECKBOX STYLING */
.form-check-input {
  width: 1.35rem;
  height: 1.35rem;
  border-width: 2px;
  border-color: #aeb5bc;
  cursor: pointer;
  transition: all 0.2s;
}
.form-check-input:checked {
  background-color: var(--color-c-hover, #547792);
  border-color: var(--color-c-hover, #547792);
}
html.dark .form-check-input {
  background-color: #2b3035;
  border-color: #6c757d;
}
html.dark .form-check-input:checked {
  background-color: var(--color-c-hover, #547792);
  border-color: var(--color-c-hover, #547792);
}
</style>
