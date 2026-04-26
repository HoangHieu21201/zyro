<template>
  <div class="order-edit-wrapper pb-5 mb-5">
    
    <div v-if="isPageLoading" class="d-flex flex-column justify-content-center align-items-center w-100" style="min-height: 70vh;">
      <h1 class="logo-shimmer mb-3">ZYRO</h1>
      <p class="text-muted dark:text-gray-400 fw-semibold small text-uppercase tracking-widest" style="letter-spacing: 2px;">Đang tải chi tiết hóa đơn...</p>
    </div>

    <div class="container-fluid py-4" v-else>
      
      <!-- HEADER -->
      <div class="row mb-4 align-items-center">
        <div class="col-md-8 d-flex align-items-center mb-3 mb-md-0">
          <router-link :to="{ name: 'admin-orders' }" class="btn btn-light dark:bg-[#2b3035] dark:border-gray-600 dark:text-gray-200 shadow-sm me-3 rounded-circle d-flex align-items-center justify-content-center hover-urban transition-all" style="width: 40px; height: 40px;">
            <i class="bi bi-arrow-left fw-bold"></i>
          </router-link>
          <div class="d-flex flex-column">
            <h3 class="fw-bold text-dark dark:text-white mb-0 d-flex align-items-center flex-wrap gap-2 font-sans-vn">
              Đơn hàng <span class="text-urban font-monospace">#{{ order.order_code }}</span>
              <span class="badge border px-3 py-1.5 ms-2 fs-6 shadow-sm font-sans-vn" :class="getOrderStatusClass(order.status)">
                <i class="bi me-1" :class="getOrderStatusIcon(order.status)"></i> {{ getOrderStatusLabel(order.status) }}
              </span>
            </h3>
            <p class="text-muted dark:text-gray-400 small mb-0 mt-1 font-sans-vn"><i class="bi bi-calendar-event me-1"></i>Ngày đặt: {{ formatDateTime(order.created_at) }}</p>
          </div>
        </div>
      </div>

      <div class="row g-4">
        
        <!-- CỘT TRÁI: DANH SÁCH SP & TRACKING & TÀI CHÍNH -->
        <div class="col-xl-8 col-lg-7">
          
          <!-- Bảng Sản phẩm -->
          <div class="card border-0 shadow-sm rounded-4 dark:bg-[#1a2533] p-4 mb-4 animation-fade-in">
            <h6 class="fw-bold text-urban text-uppercase mb-4 border-bottom dark:border-gray-700 pb-2 font-sans-vn"><i class="bi bi-bag-check-fill me-2"></i>Chi tiết sản phẩm mua ({{ order.items?.length || 0 }})</h6>
            
            <div class="bg-white dark:bg-[#212529] rounded-3 border dark:border-gray-700 overflow-hidden">
               <template v-for="(group, gIdx) in cartGroups(order.items)" :key="'grp'+gIdx">

                  <!-- 1. HIỂN THỊ GÓI COMBO -->
                  <div v-if="group.isLookbook" class="p-3 p-md-4" :class="{'border-top dark:border-gray-700': gIdx > 0}">
                     <div class="d-flex align-items-start gap-3">
                        <div class="position-relative flex-shrink-0" style="width: 80px; height: 100px;">
                          <img :src="getImageUrl(group.lookbook_image)" class="w-100 h-100 object-fit-cover rounded-3 border dark:border-gray-600 bg-white" @error="e => e.target.src='/client_placeholder.png'">
                        </div>
                        
                        <div class="flex-grow-1 d-flex flex-column justify-content-between h-100 py-1">
                           <div class="row w-100 m-0">
                              <div class="col-8 col-sm-9 p-0 pe-2">
                                 <h6 class="fw-bold text-dark dark:text-gray-200 mb-2 line-clamp-2 font-sans-vn" style="font-size: 1rem; line-height: 1.3;">
                                    {{ group.lookbook_name }}
                                 </h6>
                                 <span class="d-inline-block bg-light dark:bg-[#2b3035] text-muted dark:text-gray-400 border dark:border-gray-600 px-2 py-1 rounded-2 fw-medium mb-2 font-sans-vn" style="font-size: 0.75rem;">
                                    <i class="bi bi-magic me-1"></i> Combo Set {{ group.items.length }} món
                                 </span>
                                 <div class="mt-1">
                                    <span class="text-urban small fw-bold cursor-pointer hover-text-dark transition-color font-sans-vn" @click.stop="toggleGroup(group.lookbook_id)">
                                       {{ isGroupExpanded(group.lookbook_id) ? 'Thu gọn chi tiết' : 'Xem chi tiết combo' }} 
                                       <i class="bi ms-1" :class="isGroupExpanded(group.lookbook_id) ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                                    </span>
                                 </div>
                              </div>
                              
                              <div class="col-4 col-sm-3 p-0 text-end d-flex flex-column justify-content-between">
                                 <div>
                                    <div class="text-muted small mb-1 font-sans-vn" style="font-size: 0.75rem;">Giá Set đã thanh toán:</div>
                                    <div class="fw-bold text-danger fs-6 font-sans-vn">{{ formatCurrency(group.totalPrice) }}</div>
                                 </div>
                                 <div class="fw-bold text-dark dark:text-gray-300 mt-auto font-sans-vn">SL: {{ group.comboQuantity }}</div>
                              </div>
                           </div>
                        </div>
                     </div>

                     <!-- CHI TIẾT CÁC MÓN BÊN TRONG COMBO (Xổ xuống) -->
                     <div v-show="isGroupExpanded(group.lookbook_id)" class="mt-4 pt-3 border-top border-dashed ps-2" @click.stop>
                        <div class="d-flex flex-column">
                           <div v-for="(item, idx) in group.items" :key="'cb_item_'+item.id" class="d-flex align-items-center gap-3 mb-3 last-no-border">
                              <img :src="getImageUrl(item.variant_image)" style="width: 45px; height: 60px;" class="rounded-2 border dark:border-gray-600 object-fit-cover bg-light" @error="e => e.target.src='/client_placeholder.png'">
                              <div class="flex-grow-1">
                                 <div class="fw-bold text-dark dark:text-gray-200 line-clamp-1 font-sans-vn" style="font-size: 0.85rem;">{{ item.product_name }}</div>
                                 <div class="text-muted d-flex justify-content-between pe-1 mt-1 font-sans-vn" style="font-size: 0.8rem;">
                                    <span>
                                       Mã: <span class="text-dark dark:text-gray-300">{{ item.variant_sku }}</span> <span class="mx-2 opacity-50">|</span> 
                                       PL: <span class="text-dark dark:text-gray-300">{{ parseAttributes(item.variant_attributes) }}</span> <span class="mx-2 opacity-50">|</span> 
                                       <span class="fw-semibold text-dark dark:text-gray-300">{{ formatCurrency(item.purchased_price) }}</span>
                                    </span>
                                    <span class="text-muted fw-bold">x{{ item.quantity }}</span>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>

                  <!-- 2. SẢN PHẨM MUA LẺ -->
                  <template v-else>
                     <div v-for="(item, idx) in group.items" :key="item.id" 
                          class="d-flex p-3 p-md-4 gap-3 position-relative"
                          :class="{'border-top dark:border-gray-700': gIdx > 0 || idx > 0}">
                        
                        <div class="position-absolute top-0 start-0 w-100 h-100 bg-c-effect opacity-25 dark:bg-[#121416] pe-none z-0"></div>

                        <div class="position-relative z-1" style="width: 80px; height: 100px; flex-shrink: 0;">
                          <img :src="getImageUrl(item.variant_image)" class="w-100 h-100 object-fit-cover rounded-3 border dark:border-gray-600 bg-white" @error="e => e.target.src='/client_placeholder.png'">
                        </div>
                        
                        <div class="flex-grow-1 position-relative z-1 d-flex flex-column justify-content-between py-1">
                          <div class="row w-100 m-0">
                             <div class="col-8 col-sm-9 p-0 pe-2">
                                <h6 class="fw-bold text-dark dark:text-gray-200 mb-2 line-clamp-2 font-sans-vn" style="font-size: 0.95rem; line-height: 1.3;">{{ item.product_name }}</h6>
                                <span class="d-inline-block bg-light dark:bg-[#2b3035] text-muted dark:text-gray-400 border dark:border-gray-600 px-2 py-1 rounded-2 fw-medium font-sans-vn" style="font-size: 0.75rem;">
                                   Mã: {{ item.variant_sku }} | Phân loại: {{ parseAttributes(item.variant_attributes) }}
                                </span>
                             </div>
                             <div class="col-4 col-sm-3 p-0 text-end d-flex flex-column justify-content-between">
                                <div>
                                   <div class="text-muted small mb-1 font-sans-vn" style="font-size: 0.75rem;">Giá mua:</div>
                                   <div class="fw-bold text-danger fs-6 font-sans-vn">{{ formatCurrency(item.purchased_price) }}</div>
                                </div>
                                <div class="fw-bold text-dark dark:text-gray-300 mt-auto font-sans-vn">SL: {{ item.quantity }}</div>
                             </div>
                          </div>
                        </div>
                     </div>
                  </template>
               </template>
            </div>

            <!-- TỔNG KẾT TÀI CHÍNH TRẢN VIỀN -->
            <div class="row mt-4 g-3">
              <div class="col-md-6">
                <div class="p-3 bg-light dark:bg-[#212529] border dark:border-gray-700 rounded-3 h-100 font-sans-vn d-flex flex-column">
                   <div class="d-flex align-items-center mb-2">
                     <i class="bi bi-chat-left-text text-urban me-2"></i>
                     <span class="fw-bold text-muted small text-uppercase">Ghi chú từ khách hàng</span>
                   </div>
                   <div class="p-3 bg-white dark:bg-[#1a2533] rounded-3 border dark:border-gray-600 mt-2 flex-grow-1 shadow-sm">
                     <p class="mb-0 text-dark dark:text-gray-300 fst-italic" v-if="order.order_note">
                       "{{ order.order_note }}"
                     </p>
                     <p class="mb-0 text-muted fst-italic" v-else>
                       Khách hàng không để lại ghi chú.
                     </p>
                   </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="p-3 bg-light dark:bg-[#212529] border dark:border-gray-700 rounded-3 font-sans-vn h-100">
                  <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted dark:text-gray-400 small fw-semibold">Tổng tiền hàng:</span>
                    <span class="text-dark dark:text-gray-200 fw-bold">{{ formatCurrency(order.sub_total) }}</span>
                  </div>
                  <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted dark:text-gray-400 small fw-semibold">Phí vận chuyển:</span>
                    <span class="text-dark dark:text-gray-200 fw-bold">{{ formatCurrency(order.shipping_fee) }}</span>
                  </div>
                  <div class="d-flex justify-content-between mb-2 text-success" v-if="order.discount_amount > 0">
                    <span class="small fw-semibold">Khuyến mãi / Voucher:</span>
                    <span class="fw-bold">- {{ formatCurrency(order.discount_amount) }}</span>
                  </div>
                  <div class="d-flex justify-content-between mb-2 text-info" v-if="order.refunded_amount > 0">
                    <span class="small fw-semibold">Đã hoàn tiền:</span>
                    <span class="fw-bold">- {{ formatCurrency(order.refunded_amount) }}</span>
                  </div>
                  <hr class="dark:border-gray-600 my-2">
                  <div class="d-flex justify-content-between align-items-center mt-2">
                    <span class="text-uppercase fw-bold text-dark dark:text-white">TỔNG THU</span>
                    <span class="text-danger fw-bold fs-4">{{ formatCurrency(order.total_amount) }}</span>
                  </div>
                </div>
              </div>
            </div>
            
          </div>

          <!-- Tiến trình & Lịch sử -->
          <div class="card border-0 shadow-sm rounded-4 dark:bg-[#1a2533] p-4 animation-fade-in">
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom dark:border-gray-700 pb-2">
                <h6 class="fw-bold text-muted small text-uppercase mb-0 font-sans-vn"><i class="bi bi-clock-history me-1"></i>Lịch sử Xử lý & Giao vận</h6>
                <button type="button" class="btn btn-sm btn-urban text-white fw-bold rounded-pill shadow-sm d-flex align-items-center transition-all hover-transform font-sans-vn px-3" @click="openMapSimulation">
                   <i class="bi bi-map-fill me-2"></i> Xem Hành Trình Live
                </button>
            </div>
            
            <ul class="list-group list-group-flush rounded-3 border dark:border-gray-700 custom-scrollbar-y mb-4" style="max-height: 250px; overflow-y: auto;">
               <li v-for="his in order.histories" :key="his.id" class="list-group-item bg-transparent dark:border-gray-700 p-3">
                 <div class="d-flex justify-content-between align-items-start">
                   <div>
                     <div class="fw-bold text-dark dark:text-gray-200 small mb-1 font-sans-vn">
                        Cập nhật: <span class="text-secondary text-decoration-line-through">{{ getOrderStatusLabel(his.old_status) }}</span> <i class="bi bi-arrow-right mx-1 text-muted"></i> <span class="text-urban">{{ getOrderStatusLabel(his.new_status) }}</span>
                     </div>
                     <div class="text-muted fst-italic font-sans-vn" style="font-size: 0.8rem;">Note: "{{ his.note || 'Không có ghi chú' }}"</div>
                   </div>
                   <div class="text-end ms-2">
                     <span class="badge bg-secondary bg-opacity-10 text-secondary border mb-1 font-sans-vn"><i class="bi bi-person-badge"></i> {{ his.changer?.fullname || 'Hệ thống' }}</span>
                     <div class="text-muted font-monospace" style="font-size: 0.7rem;">{{ formatDateTime(his.created_at) }}</div>
                   </div>
                 </div>
               </li>
            </ul>
          </div>
        </div>

        <!-- CỘT PHẢI: KHÁCH HÀNG & THANH TOÁN & TRẠNG THÁI -->
        <div class="col-xl-4 col-lg-5">
           
           <div class="card border-0 shadow-sm rounded-4 dark:bg-[#1a2533] p-4 mb-4">
              <h6 class="fw-bold mb-3 text-urban text-uppercase border-bottom dark:border-gray-700 pb-2 font-sans-vn"><i class="bi bi-person-lines-fill me-2"></i>Khách Hàng</h6>
              <div class="d-flex align-items-center mb-3">
                <img :src="getImageUrl(order.user?.avatar_url)" @error="handleImageError" class="rounded-circle object-fit-cover me-3 border shadow-sm dark:border-gray-600" style="width: 50px; height: 50px;">
                <div class="overflow-hidden font-sans-vn">
                  <h6 class="mb-0 fw-bold text-dark dark:text-gray-200 text-truncate">{{ order.user?.full_name || shippingInfoParsed?.name || 'Khách vãng lai' }}</h6>
                  <small class="text-muted dark:text-gray-400 d-block mt-1 font-monospace"><i class="bi bi-envelope me-1"></i>{{ order.user?.email || 'N/A' }}</small>
                </div>
              </div>
              <div class="bg-light dark:bg-[#212529] p-3 rounded-3 border dark:border-gray-700 small text-dark dark:text-gray-300 font-sans-vn">
                 <div class="mb-2"><i class="bi bi-telephone text-urban me-2 fw-bold"></i> SĐT: <strong>{{ shippingInfoParsed?.phone || 'N/A' }}</strong></div>
                 <div><i class="bi bi-geo-alt-fill text-urban me-2 fw-bold"></i> Đ/C: {{ shippingInfoParsed?.address }}, {{ shippingInfoParsed?.ward }}, {{ shippingInfoParsed?.district }}, {{ shippingInfoParsed?.city }}</div>
              </div>
           </div>

           <!-- Thanh toán & Giao nhận -->
           <div class="card border-0 shadow-sm rounded-4 dark:bg-[#1a2533] p-4 mb-4 font-sans-vn">
              <h6 class="fw-bold mb-3 text-urban text-uppercase border-bottom dark:border-gray-700 pb-2"><i class="bi bi-truck me-2"></i>Giao dịch & Vận chuyển</h6>
              
              <div class="mb-3 d-flex justify-content-between align-items-center">
                 <span class="text-muted small fw-bold text-uppercase">Cổng thanh toán:</span>
                 <span class="badge bg-dark text-white shadow-sm border px-3 py-1.5"><i class="bi bi-wallet2 me-1"></i> {{ order.payment_method === 'cod' ? 'Tiền mặt (COD)' : (order.payment_method || 'N/A').toUpperCase() }}</span>
              </div>
              <div class="mb-3 d-flex justify-content-between align-items-center">
                 <span class="text-muted small fw-bold text-uppercase">Trạng thái T.Toán:</span>
                 <span class="badge border px-3 py-1.5 shadow-sm" :class="getPaymentStatusClass(order.payment_status)">{{ getPaymentStatusLabel(order.payment_status) }}</span>
              </div>

              <form @submit.prevent="onOriginCityChange">
                <div class="mb-3">
                  <label class="form-label text-muted small fw-bold text-uppercase mb-1 d-flex align-items-center justify-content-between">
                    <span>Kho gửi hàng (Nơi xuất phát)</span>
                    <span v-if="isSavingShipping" class="spinner-border spinner-border-sm text-urban"></span>
                  </label>
                  <div class="position-relative dropdown-container" ref="provinceDropdownRef">
                    <input type="text"
                           class="form-control form-control-sm bg-light dark:bg-[#212529] dark:text-white dark:border-gray-700 fw-semibold shadow-sm-hover"
                           v-model="provinceSearchQuery"
                           @focus="!isShippingLocked ? showProvinceDropdown = true : null"
                           @input="!isShippingLocked ? showProvinceDropdown = true : null"
                           :disabled="isShippingLocked"
                           placeholder="Tìm hoặc chọn Tỉnh/Thành phố...">
                    <i class="bi bi-chevron-down position-absolute top-50 end-0 translate-middle-y me-2 text-muted" style="pointer-events: none; font-size: 0.75rem;"></i>

                    <div v-if="showProvinceDropdown && !isShippingLocked" class="custom-dropdown-menu shadow w-100 mt-1 position-absolute" style="z-index: 1050;">
                      <div v-if="filteredProvinces.length === 0" class="custom-dropdown-item text-muted small fst-italic px-3 py-2 border-0">Không tìm thấy</div>
                      <div v-else v-for="p in filteredProvinces" :key="p.code"
                          class="cursor-pointer small px-3 py-2 custom-dropdown-item"
                          :class="{'selected-dropdown-item': formShipping.origin_city === p.name}"
                          @click="selectProvince(p.name)">
                        {{ p.name }}
                      </div>
                    </div>
                  </div>
                  <small class="text-success fw-bold mt-1 d-block" style="font-size: 0.65rem;"><i class="bi bi-check-circle me-1"></i>Đã tự động ghi nhớ cho lần sau.</small>
                  <small v-if="isShippingLocked" class="text-danger fw-bold mt-1 d-block" style="font-size: 0.65rem;"><i class="bi bi-shield-lock me-1"></i>Đơn hàng đã xuất kho, không thể đổi thông tin vận chuyển.</small>
                </div>

                <label class="form-label text-muted small fw-bold text-uppercase mb-1">Mã vận đơn (Tracking Code)</label>
                <div class="input-group shadow-sm-hover mb-2">
                  <input type="text" class="form-control form-control-sm bg-light dark:bg-[#212529] dark:text-white dark:border-gray-700 font-monospace text-uppercase" v-model="formShipping.tracking_number" placeholder="Nhập mã vận đơn..." :disabled="isShippingLocked">
                  <button class="btn btn-sm btn-urban fw-bold" type="button" @click="onOriginCityChange" :disabled="isSavingShipping || isShippingLocked"><i class="bi bi-floppy-fill"></i></button>
                </div>
                <div class="d-flex gap-2">
                  <select class="form-select form-select-sm bg-light dark:bg-[#212529] dark:text-white dark:border-gray-700 fw-semibold" v-model="formShipping.shipping_provider" @change="onOriginCityChange" :disabled="isShippingLocked">
                    <option value="">-- Đơn vị vận chuyển --</option>
                    <option value="GHN">Giao Hàng Nhanh</option>
                    <option value="GHTK">Giao Hàng Tiết Kiệm</option>
                    <option value="VNPOST">VNPost</option>
                    <option value="VIETTEL">Viettel Post</option>
                    <option value="JNT">J&T Express</option>
                  </select>
                </div>
              </form>
           </div>

           <!-- FORM XỬ LÝ TRẠNG THÁI CHÍNH -->
           <div class="card border-0 shadow-sm rounded-4 dark:bg-[#1a2533] p-4 sticky-top font-sans-vn" style="top: 20px;">
              <h6 class="fw-bold mb-3 text-urban text-uppercase border-bottom dark:border-gray-700 pb-2"><i class="bi bi-toggles me-2"></i>Quyết định xử lý</h6>
              
              <div v-if="order.return_status === 'pending' || order.return_status === 'proposing'" class="alert alert-warning border-0 p-3 rounded-3 shadow-sm mb-3 dark:bg-yellow-900/20 dark:text-yellow-200">
                 <i class="bi bi-arrow-return-left me-1 fs-5 align-middle"></i> <strong>YÊU CẦU HOÀN TRẢ!</strong><br>
                 <span class="small">Khách hàng đang yêu cầu hoàn tiền/đổi hàng. Vui lòng chuyển sang trang <b>Quản lý Đơn Hoàn</b> để xử lý.</span>
              </div>
              <div v-else-if="order.status === 'cancelled' || order.status === 'returned'" class="alert alert-danger border-0 p-3 rounded-3 shadow-sm mb-3">
                 <i class="bi bi-exclamation-triangle-fill me-1"></i> Đơn hàng này đã kết thúc ở trạng thái <strong>{{ getOrderStatusLabel(order.status) }}</strong>. Bạn không thể thay đổi tiến trình nữa.
              </div>
              
              <div v-if="!['cancelled', 'returned'].includes(order.status) && !['pending', 'proposing'].includes(order.return_status)">
                <div v-if="order.status === 'completed'" class="alert alert-success border-0 p-3 rounded-3 shadow-sm mb-3">
                  <i class="bi bi-check-circle-fill me-1"></i> Đơn hàng đã giao thành công. Bạn vẫn có thể đối soát cập nhật trạng thái thanh toán bên dưới.
                </div>

                <form @submit.prevent="updateOrderStatus">
                  <div class="mb-3">
                    <label class="form-label text-muted small fw-bold text-uppercase mb-2">Đổi trạng thái đơn <span class="text-danger">*</span></label>
                    <select class="form-select form-select-lg fw-bold shadow-sm-hover dark:bg-[#212529] dark:text-white dark:border-gray-700" :class="getOrderStatusClass(formStatus.status)" v-model="formStatus.status" @change="onStatusChange">
                      <option v-for="st in getValidNextStatuses(order.status)" :key="st.value" :value="st.value" class="text-dark bg-white fw-bold">{{ st.label }}</option>
                    </select>
                    <small class="text-urban d-block mt-1 fst-italic"><i class="bi bi-info-circle me-1"></i>Hệ thống tự động ẩn các hướng đi không hợp lệ.</small>
                  </div>

                  <div class="mb-3">
                    <label class="form-label text-muted small fw-bold text-uppercase mb-2">Đổi trạng thái Tiền <span class="text-danger">*</span></label>
                    <select class="form-select fw-semibold shadow-sm-hover dark:bg-[#212529] dark:text-white dark:border-gray-700" v-model="formStatus.payment_status">
                      <option value="unpaid" :disabled="['paid', 'refunded'].includes(order.payment_status)">Chưa thanh toán</option>
                      <option value="paid" :disabled="order.payment_status === 'refunded'">Đã thanh toán (Thu đủ)</option>
                      <option value="refunded" disabled>Đã hoàn tiền (Refunded)</option>
                    </select>
                    <small v-if="['paid', 'refunded'].includes(order.payment_status)" class="text-danger d-block mt-1 fst-italic"><i class="bi bi-shield-lock me-1"></i>Hệ thống đã khóa, không thể lùi trạng thái thanh toán.</small>
                  </div>

                  <!-- ======================================================== -->
                  <!-- ĐÃ CẬP NHẬT ĐỒNG BỘ: CHECKLIST PHẢN HỒI NHANH (MẶC ĐỊNH CHỌN) -->
                  <!-- ======================================================== -->
                  <div class="mb-4">
                    <label class="form-label text-muted small fw-bold text-uppercase mb-2">Lời nhắn / Ghi chú cho Khách</label>
                    
                    <div class="d-flex flex-column gap-2 mb-3">
                       <div v-for="(note, idx) in quickNotes" :key="idx" class="form-check custom-checklist-item p-0">
                          <input class="form-check-input d-none" type="checkbox" :id="'order-note-'+idx" :value="note" v-model="selectedNotes">
                          <label class="form-check-label w-100 p-2 rounded-3 border transition-all cursor-pointer small fw-medium font-sans-vn" 
                                 :for="'order-note-'+idx" 
                                 :class="selectedNotes.includes(note) ? 'bg-urban text-white border-urban shadow-sm' : 'bg-light dark:bg-[#212529] text-muted dark:text-gray-400 border-light-subtle dark:border-gray-700 hover-bg-effect'">
                             <i class="bi me-2" :class="selectedNotes.includes(note) ? 'bi-check-circle-fill' : 'bi-circle'"></i>
                             {{ note }}
                          </label>
                       </div>
                       
                       <div class="form-check custom-checklist-item p-0">
                          <input class="form-check-input d-none" type="checkbox" id="order-note-custom" v-model="isCustomNote">
                          <label class="form-check-label w-100 p-2 rounded-3 border transition-all cursor-pointer small fw-bold font-sans-vn" 
                                 for="order-note-custom"
                                 :class="isCustomNote ? 'bg-dark text-white border-dark shadow-sm' : 'bg-light dark:bg-[#212529] text-muted dark:text-gray-400 border-light-subtle dark:border-gray-700 hover-bg-effect'">
                             <i class="bi me-2" :class="isCustomNote ? 'bi-pencil-square' : 'bi-plus-circle'"></i>
                             Nội dung khác / Ghi chú tay...
                          </label>
                       </div>
                    </div>

                    <transition name="slide-fade">
                      <div v-if="isCustomNote" class="mt-2">
                         <label class="form-label small fw-bold text-muted text-uppercase mb-1">Mô tả chi tiết bổ sung</label>
                         <textarea class="form-control bg-light dark:bg-[#212529] dark:text-white dark:border-gray-700 shadow-sm-hover" 
                                   v-model="customTextNote" rows="3" 
                                   placeholder="Gõ chi tiết ghi chú thủ công vào đây..."></textarea>
                      </div>
                    </transition>
                  </div>

                  <button type="submit" class="btn btn-urban btn-lg text-white w-100 fw-bold shadow-sm rounded-pill hover-transform" :disabled="isSavingStatus">
                    <span v-if="isSavingStatus" class="spinner-border spinner-border-sm me-2"></span> CHỐT TRẠNG THÁI
                  </button>
                </form>
              </div>
           </div>

        </div>
      </div>
    </div>

    <!-- MODAL LIVE MAP TRACKING -->
    <div class="modal fade glass-modal" id="mapTrackingModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
      <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content rounded-4 border-0 shadow-lg dark:bg-[#1a2533] overflow-hidden">
          <div class="modal-header border-bottom dark:border-gray-700 bg-light dark:bg-[#212529] p-3">
            <div class="d-flex align-items-center font-sans-vn">
              <div class="bg-urban text-white rounded p-2 me-3 d-flex align-items-center justify-content-center shadow-sm">
                <i class="bi bi-truck fs-5"></i>
              </div>
              <div>
                <h5 class="fw-bold text-dark dark:text-white mb-0">Hành Trình Giao Vận Thực Tế</h5>
                <p class="text-muted small mb-0 font-monospace">Lộ trình: <span class="text-urban fw-bold">{{ mapData?.origin?.name }}</span> <i class="bi bi-arrow-right"></i> <span class="text-urban fw-bold">{{ mapData?.destination?.name }}</span></p>
              </div>
            </div>
            <button type="button" class="btn-close dark:filter-invert" data-bs-dismiss="modal" aria-label="Close" @click="closeMapSimulation"></button>
          </div>
          
          <div class="modal-body p-0 position-relative" style="height: 60vh;">
             <TrackingMap v-if="isMapOpen && mapData" :map-data="mapData" :status="order.status" />
          </div>
          
          <div class="modal-footer border-top-0 bg-light dark:bg-[#212529] p-3 justify-content-between align-items-center font-sans-vn">
            <div class="small text-muted"><i class="bi bi-info-circle me-1"></i>Hành trình mô phỏng dựa trên dữ liệu Mapbox Navigation.</div>
            <button type="button" class="btn btn-secondary px-4 rounded-pill fw-bold shadow-sm" data-bs-dismiss="modal" @click="closeMapSimulation">Đóng bản đồ</button>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, computed, onBeforeUnmount } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import Swal from 'sweetalert2';
import axios from 'axios';
import defaultAvatar from '@/assets/images/defaults/avatar1.png';
import TrackingMap from '@/components/shared/TrackingMap.vue';

const route = useRoute();
const router = useRouter();
const orderId = route.params.id;

const isPageLoading = ref(true);
const isSavingShipping = ref(false);
const isSavingStatus = ref(false);

const order = ref({});
const shippingInfoParsed = ref({});
const provinces = ref([]); 

const showProvinceDropdown = ref(false);
const provinceSearchQuery = ref('');
const provinceDropdownRef = ref(null);

const formShipping = ref({ tracking_number: '', shipping_provider: '', origin_city: '' });
const formStatus = ref({ status: '', payment_status: '', note: '' });

// ====== BẢO MẬT & RÀNG BUỘC LOGIC ======
const isShippingLocked = computed(() => {
  return ['shipping', 'completed', 'returned', 'cancelled'].includes(order.value.status);
});

// ====== LOGIC QUICK NOTES DẠNG CHECKLIST ======
const selectedNotes = ref([]);
const isCustomNote = ref(false);
const customTextNote = ref('');

const mapData = ref(null);
const isMapOpen = ref(false);

const quickNotes = computed(() => {
  const s = formStatus.value.status;
  if (s === 'confirmed' || s === 'processing') return ['Đơn hàng đã được xác nhận và đang đóng gói.', 'Đang điều phối hàng từ kho tổng.'];
  if (s === 'shipping') return ['Đã bàn giao cho đơn vị vận chuyển.', 'Hàng đang trên đường đến bưu cục phát.'];
  if (s === 'completed') return ['Đã giao thành công. Cảm ơn quý khách!', 'Khách đã nhận và kiểm tra hàng hóa.'];
  if (s === 'cancelled') return ['Không liên lạc được với khách hàng.', 'Khách hàng yêu cầu hủy đơn.', 'Hết hàng trong kho, mong quý khách thông cảm.'];
  if (s === 'returned') return ['Khách từ chối nhận hàng.', 'Sai thông tin địa chỉ/SĐT.', 'Hàng bị lỗi trong quá trình vận chuyển.'];
  return ['Vui lòng kiểm tra lại thông tin.', 'Cập nhật hệ thống nội bộ.'];
});

const getHeaders = () => ({ 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('admin_token')}` });
const getImageUrl = (path) => path ? `http://127.0.0.1:8000/storage/${path}` : defaultAvatar;
const handleImageError = (e) => { e.target.src = defaultAvatar; };

const formatCurrency = (val) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val || 0);
const formatDateTime = (dateString) => {
  if(!dateString) return '';
  const d = new Date(dateString);
  return `${d.toLocaleDateString('vi-VN')} ${d.toLocaleTimeString('vi-VN', {hour: '2-digit', minute:'2-digit'})}`;
};
const parseAttributes = (jsonStr) => {
  if(!jsonStr) return {};
  if(typeof jsonStr === 'object') return jsonStr;
  try { return JSON.parse(jsonStr); } catch(e) { return {}; }
};

// UI Helpers
const getOrderStatusClass = (status) => {
  const map = { 'pending': 'bg-warning text-dark border-warning', 'confirmed': 'bg-info text-white border-info', 'processing': 'bg-primary text-white border-primary', 'shipping': 'bg-primary text-white border-primary', 'completed': 'bg-success text-white border-success', 'cancelled': 'bg-danger text-white border-danger', 'returned': 'bg-secondary text-white border-secondary' };
  return map[status] || 'bg-light text-secondary border-secondary';
};

const getOrderStatusLabel = (status) => {
  const map = { 'pending': 'Chờ xác nhận', 'confirmed': 'Đã xác nhận', 'processing': 'Đang chuẩn bị', 'shipping': 'Đang giao', 'completed': 'Thành công', 'cancelled': 'Đã hủy', 'returned': 'Đã hoàn trả' };
  return map[status] || status;
};

const getOrderStatusIcon = (status) => {
  const map = { 'pending': 'bi-hourglass-split', 'confirmed': 'bi-check2-circle', 'processing': 'bi-box-seam', 'shipping': 'bi-truck', 'completed': 'bi-check-circle-fill', 'cancelled': 'bi-x-circle-fill', 'returned': 'bi-arrow-return-left' };
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

const getValidNextStatuses = (currentStatus) => {
  const all = [
    { value: 'pending', label: 'Chờ xác nhận' }, { value: 'confirmed', label: 'Đã xác nhận' },
    { value: 'processing', label: 'Đang xử lý/chuẩn bị' }, { value: 'shipping', label: 'Bắt đầu Giao hàng' },
    { value: 'completed', label: 'Giao Thành công' }, { value: 'cancelled', label: 'Khách Hủy đơn' },
    { value: 'returned', label: 'Giao thất bại (Hoàn trả)' }
  ];
  const rules = { 'pending': ['pending', 'confirmed', 'cancelled'], 'confirmed': ['confirmed', 'processing', 'shipping', 'cancelled'], 'processing': ['processing', 'shipping', 'cancelled'], 'shipping': ['shipping', 'completed', 'returned'], 'completed': ['completed'], 'cancelled': ['cancelled'], 'returned': ['returned'] };
  return all.filter(s => (rules[currentStatus] || [currentStatus]).includes(s.value));
};

const removeAccents = (str) => {
  if (!str) return '';
  return str.toString().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/đ/g, 'd').replace(/Đ/g, 'D').toLowerCase().trim();
};

const filteredProvinces = computed(() => {
  if (!provinceSearchQuery.value) return provinces.value;
  const queryWords = removeAccents(provinceSearchQuery.value).split(' ').filter(Boolean);
  return provinces.value.filter(p => queryWords.every(word => removeAccents(p.name).includes(word)));
});

const fetchProvinces = async () => {
  try { const res = await axios.get('https://provinces.open-api.vn/api/p/'); provinces.value = res.data; } 
  catch (err) { console.error(err); }
};

const fetchData = async (isSilent = false) => {
  if (!isSilent) isPageLoading.value = true;
  try {
    const res = await axios.get(`${import.meta.env.VITE_API_BASE_URL}/admin/orders/${orderId}`, { headers: getHeaders() });
    order.value = res.data.data;
    shippingInfoParsed.value = typeof order.value.shipping_info === 'string' ? JSON.parse(order.value.shipping_info) : order.value.shipping_info;

    formShipping.value.tracking_number = order.value.tracking_number || '';
    formShipping.value.shipping_provider = order.value.shipping_provider || '';
    formShipping.value.origin_city = shippingInfoParsed.value?.origin_city || localStorage.getItem('default_origin_city') || 'Thành phố Hà Nội';
    provinceSearchQuery.value = formShipping.value.origin_city;

    formStatus.value.status = order.value.status;
    formStatus.value.payment_status = order.value.payment_status;
    
    // ĐÃ FIX ĐỒNG BỘ: Mặc định chọn câu trả lời đầu tiên
    selectedNotes.value = quickNotes.value.length > 0 ? [quickNotes.value[0]] : [];
    
  } catch (e) {
    if (!isSilent) {
       Swal.fire('Lỗi', 'Không tìm thấy đơn hàng', 'error');
       router.push({ name: 'admin-orders' });
    }
  } finally { 
    if (!isSilent) isPageLoading.value = false; 
  }
};

const selectProvince = (name) => {
  formShipping.value.origin_city = name;
  provinceSearchQuery.value = name;
  showProvinceDropdown.value = false;
  onOriginCityChange();
};

const onOriginCityChange = () => {
  if (formShipping.value.origin_city) localStorage.setItem('default_origin_city', formShipping.value.origin_city);
  updateShippingInfo(); 
};

const updateShippingInfo = async () => {
  isSavingShipping.value = true;
  const payload = { ...formShipping.value, shipping_info: { ...shippingInfoParsed.value, origin_city: formShipping.value.origin_city } };
  try {
    await axios.put(`${import.meta.env.VITE_API_BASE_URL}/admin/orders/${orderId}`, payload, { headers: getHeaders() });
    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Đã lưu cấu hình Vận đơn', showConfirmButton: false, timer: 1500 });
    await fetchData(true);
  } catch(e) {
    Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: 'Lỗi lưu thông tin', showConfirmButton: false, timer: 1500 });
  } finally { isSavingShipping.value = false; }
};

const onStatusChange = () => {
  if (formStatus.value.status === 'completed' && formStatus.value.payment_status === 'unpaid') {
      formStatus.value.payment_status = 'paid';
      Swal.fire({ toast: true, position: 'top-end', icon: 'info', title: 'Tự động chuyển: Đã thanh toán', showConfirmButton: false, timer: 3000 });
  }
  // ĐÃ FIX ĐỒNG BỘ: Khi đổi trạng thái thì reset về chọn câu đầu tiên
  selectedNotes.value = quickNotes.value.length > 0 ? [quickNotes.value[0]] : [];
  isCustomNote.value = false;
  customTextNote.value = '';
};

const updateOrderStatus = async () => {
  if (['cancelled', 'returned'].includes(formStatus.value.status) && formStatus.value.status !== order.value.status) {
      const confirm = await Swal.fire({ title: 'Cảnh báo Bảo mật Kho', text: `Kho hàng của các sản phẩm sẽ tự động được cộng lại. Bạn chắc chắn chứ?`, icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Đồng ý' });
      if(!confirm.isConfirmed) return;
  }
  if (formStatus.value.status === 'shipping' && order.value.status !== 'shipping') {
      const confirmShip = await Swal.fire({ title: 'Xác nhận Bắt đầu Giao Hàng?', html: `Xuất kho từ:<br><b class="text-urban fs-5">${formShipping.value.origin_city || 'Chưa xác định'}</b><br>Đúng chưa sếp?`, icon: 'info', showCancelButton: true, confirmButtonColor: 'var(--color-c-hover)', confirmButtonText: 'Giao hàng!' });
      if(!confirmShip.isConfirmed) return;
  }

  // ĐÃ FIX ĐỒNG BỘ: Gộp chuỗi từ Form Checklist
  let finalNote = selectedNotes.value.join(' | ');
  if (isCustomNote.value && customTextNote.value.trim()) {
      finalNote = finalNote ? `${finalNote}. Ghi chú thêm: ${customTextNote.value}` : customTextNote.value;
  }
  
  if (!finalNote) {
      Swal.fire('Chú ý', 'Vui lòng chọn hoặc nhập Ghi chú xử lý đơn hàng.', 'warning');
      return;
  }
  
  formStatus.value.note = finalNote;
  isSavingStatus.value = true;
  
  try {
    await axios.patch(`${import.meta.env.VITE_API_BASE_URL}/admin/orders/${orderId}/status`, formStatus.value, { headers: getHeaders() });
    
    Swal.fire({ icon: 'success', title: 'Thành công', text: 'Cập nhật trạng thái đơn hàng & Email đã được gửi!', timer: 2000, showConfirmButton: false }).then(() => {
        if (formStatus.value.status === 'completed') { openMapSimulation(); }
    });
    await fetchData(true);
  } catch(e) {
    Swal.fire('Lỗi', e.response?.data?.message || 'Lỗi xử lý trạng thái', 'error');
    formStatus.value.status = order.value.status;
  } finally { isSavingStatus.value = false; }
};

const handleClickOutside = (event) => {
  if (provinceDropdownRef.value && !provinceDropdownRef.value.contains(event.target)) {
    showProvinceDropdown.value = false;
    if (provinceSearchQuery.value !== formShipping.value.origin_city) provinceSearchQuery.value = formShipping.value.origin_city;
  }
};

const expandedGroups = ref([]);
const groupOrderItems = (items) => {
  const result = [];
  const normalGroup = { isLookbook: false, items: [] };
  if (!items) return result;

  items.forEach(item => {
    if (item.lookbook_id) {
      let group = result.find(g => g.isLookbook && g.lookbook_id === item.lookbook_id);
      if (!group) {
        let lbName = item.lookbook ? item.lookbook.name : 'Combo / Set Đồ';
        let lbImage = item.lookbook ? item.lookbook.main_image : item.variant_image;

        group = { isLookbook: true, lookbook_id: item.lookbook_id, lookbook_name: lbName, lookbook_image: lbImage, items: [], comboQuantity: item.quantity, totalPrice: 0 };
        result.push(group);
      }
      group.items.push(item);
      group.totalPrice += (item.purchased_price * item.quantity);
    } else { normalGroup.items.push(item); }
  });

  if (normalGroup.items.length > 0) result.push(normalGroup);
  return result;
};

const cartGroups = (items) => groupOrderItems(items);
const toggleGroup = (lookbookId) => {
  if (expandedGroups.value.includes(lookbookId)) { expandedGroups.value = expandedGroups.value.filter(k => k !== lookbookId); } 
  else { expandedGroups.value.push(lookbookId); }
};
const isGroupExpanded = (lookbookId) => expandedGroups.value.includes(lookbookId);

const getCoordinatesForCity = (cityName) => {
  if(!cityName) return [21.0285, 105.8542]; 
  const city = cityName.toLowerCase();
  const coordsMap = {
    'hà nội': [21.0285, 105.8542], 'hồ chí minh': [10.8231, 106.6297], 'đà nẵng': [16.0471, 108.2068],
    'hải phòng': [20.8449, 106.6881], 'cần thơ': [10.0452, 105.7469], 'đắk lắk': [12.6667, 108.0383]
  };
  for (const key in coordsMap) { if (city.includes(key)) return coordsMap[key]; }
  return [21.0285, 105.8542];
};

const openMapSimulation = () => {
  const modalEl = document.getElementById('mapTrackingModal');
  const modal = new window.bootstrap.Modal(modalEl);
  const info = shippingInfoParsed.value || {};
  const originName = formShipping.value.origin_city || info.origin_city || 'Hà Nội';
  const destName = info.city || 'Hồ Chí Minh';
  
  mapData.value = {
     origin: { name: originName, coords: getCoordinatesForCity(originName) },
     destination: { name: destName, coords: getCoordinatesForCity(destName) },
     shipping_provider: formShipping.value.shipping_provider || order.value.shipping_provider
  };
  
  isMapOpen.value = true; modal.show();
  modalEl.addEventListener('hidden.bs.modal', function onHidden() { closeMapSimulation(); modalEl.removeEventListener('hidden.bs.modal', onHidden); });
};

const closeMapSimulation = () => { isMapOpen.value = false; };

onMounted(() => { fetchProvinces(); fetchData(); document.addEventListener('click', handleClickOutside); });
onBeforeUnmount(() => { document.removeEventListener('click', handleClickOutside); });
</script>

<style scoped>
.text-urban { color: var(--color-c-hover, #547792) !important; }
.bg-urban { background-color: var(--color-c-hover, #547792) !important; }
.border-urban { border-color: var(--color-c-hover, #547792) !important; }
.btn-urban { background-color: var(--color-c-hover, #547792); color: white; border: none; transition: 0.2s; }
.btn-urban:hover { background-color: var(--color-c-dark, #213448); color: white; transform: translateY(-1px); }

.hover-bg-effect:hover { background-color: rgba(84, 119, 146, 0.05); }
.cursor-pointer { cursor: pointer; }
.hover-transform:hover { transform: translateY(-3px); box-shadow: 0 8px 15px rgba(0,0,0,0.1) !important; }

/* CSS Checklist */
.custom-checklist-item label { border: 1px solid transparent; transition: 0.2s; }
.custom-checklist-item label:hover { border-color: var(--color-c-hover); }

.shadow-sm-hover { transition: box-shadow 0.2s ease, border-color 0.2s ease; }
.shadow-sm-hover:focus-within { box-shadow: 0 4px 15px rgba(84, 119, 146, 0.1) !important; }
.form-control:focus, .form-select:focus { border-color: var(--color-c-hover, #547792); box-shadow: none !important; }

.custom-dropdown-menu { display: block; max-height: 250px; overflow-y: auto; border-radius: 8px; padding: 0.5rem 0; background-color: #fff; border: 1px solid #dee2e6; }
.custom-dropdown-item { color: #212529; transition: background-color 0.2s ease, color 0.2s ease; border: none; }
.custom-dropdown-item:hover { background-color: var(--color-c-effect); }

html.dark .custom-dropdown-menu { background-color: #212529; border-color: #373b3e; }
html.dark .custom-dropdown-item { color: #f8f9fa; }
html.dark .custom-dropdown-item:hover { background-color: #2b3035; color: #fff; }

.selected-dropdown-item { background-color: rgba(84, 119, 146, 0.15) !important; color: var(--color-c-hover, #547792) !important; font-weight: bold; }
html.dark .selected-dropdown-item { background-color: rgba(255, 255, 255, 0.1) !important; color: #fff !important; }

.font-sans-vn { font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', sans-serif !important; }
.line-clamp-1 { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis; line-height: 1.4; }
.line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis; line-height: 1.4; }
.border-dashed { border-style: dashed !important; border-color: #dee2e6 !important; }
html.dark .border-dashed { border-color: #373b3e !important; }
.last-no-border:last-child { margin-bottom: 0 !important; padding-bottom: 0 !important; border: none !important; }
.transition-color { transition: color 0.2s ease; }
.hover-text-dark:hover { color: #000 !important; }
html.dark .hover-text-dark:hover { color: #fff !important; }

.slide-fade-enter-active { transition: all 0.3s ease-out; }
.slide-fade-leave-active { transition: all 0.2s cubic-bezier(1, 0.5, 0.8, 1); }
.slide-fade-enter-from, .slide-fade-leave-to { transform: translateY(-10px); opacity: 0; }

.tracking-timeline .timeline-item:last-child .timeline-content { border-bottom: none !important; }
.glass-modal { backdrop-filter: blur(8px); background-color: rgba(0, 0, 0, 0.4); }

.logo-shimmer { font-size: 3.5rem; font-weight: 900; letter-spacing: -1.5px; background: linear-gradient(120deg, var(--color-c-dark) 30%, var(--color-c-light) 50%, var(--color-c-dark) 70%); background-size: 200% auto; color: transparent; -webkit-background-clip: text; background-clip: text; animation: shine 1.5s linear infinite; }
@keyframes shine { to { background-position: 200% center; } }
.animation-fade-in { animation: fadeIn 0.4s ease-in-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

.custom-scrollbar-y::-webkit-scrollbar { width: 4px; }
.custom-scrollbar-y::-webkit-scrollbar-thumb { background: var(--color-c-light, #94B4C1); border-radius: 10px; }
</style>