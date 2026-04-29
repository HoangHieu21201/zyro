<template>
  <div class="checkout-page-wrapper pb-5 mb-5" style="padding-top: 30px;">
    <div class="pt-5 mt-4">
      <div class="zyro-container">
        
        <!-- breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
          <ol class="breadcrumb small fw-semibold text-uppercase font-sans-vn" style="letter-spacing: 0.5px;">
            <li class="breadcrumb-item"><router-link to="/" class="text-decoration-none text-muted hover-text-dark">Trang chủ</router-link></li>
            <li class="breadcrumb-item"><router-link to="/cart" class="text-decoration-none text-muted hover-text-dark">Giỏ hàng</router-link></li>
            <li class="breadcrumb-item active text-c-dark" aria-current="page">Thanh toán</li>
          </ol>
        </nav>

        <div class="mb-4 pb-3 border-bottom dark:border-gray-700">
          <h2 class="fw-bold text-c-dark dark:text-white m-0 text-uppercase tracking-widest font-sans-vn" style="letter-spacing: 2px;">Thanh toán an toàn</h2>
        </div>

        <!-- skeleton chờ tải dữ liệu -->
        <div v-if="isInitLoading" class="row g-5 mb-5 pe-none animation-fade-in">
          <div class="col-lg-7">
             <div class="bg-white dark:bg-[#1a2533] p-4 rounded-4 shadow-sm border border-light-subtle dark:border-gray-700 mb-4">
                <div class="d-flex align-items-center mb-4">
                   <div class="shimmer rounded-circle me-3" style="width: 28px; height: 28px;"></div>
                   <div class="shimmer rounded-2" style="width: 180px; height: 20px;"></div>
                </div>
                <div class="row g-3">
                   <div class="col-12"><div class="shimmer rounded-2 w-100" style="height: 45px;"></div></div>
                   <div class="col-6"><div class="shimmer rounded-2 w-100" style="height: 45px;"></div></div>
                   <div class="col-6"><div class="shimmer rounded-2 w-100" style="height: 45px;"></div></div>
                   <div class="col-12"><div class="shimmer rounded-2 w-100" style="height: 45px;"></div></div>
                </div>
             </div>
          </div>
          <div class="col-lg-5">
             <div class="bg-white dark:bg-[#1a2533] p-4 rounded-4 shadow-sm border border-light-subtle dark:border-gray-700">
                <div class="shimmer rounded-2 w-50 mb-4" style="height: 24px;"></div>
                <div class="shimmer rounded-3 w-100 mb-3" style="height: 90px;"></div>
                <div class="shimmer rounded-pill w-100 mt-4" style="height: 55px;"></div>
             </div>
          </div>
        </div>

        <div v-else class="animation-fade-in">
            <form @submit.prevent="placeOrder" autocomplete="off">
            <div class="row g-5">
                
                <!-- cột trái -->
                <div class="col-lg-7">
                
                <!-- 1. thông tin liên hệ và giao hàng -->
                <div class="mb-5">
                    <h5 class="fw-bold text-c-dark dark:text-white mb-4 d-flex align-items-center font-sans-vn">
                    <span class="step-number rounded-circle d-flex align-items-center justify-content-center me-2 shadow-sm" style="width: 28px; height: 28px; font-size: 0.9rem;">1</span> 
                    Thông tin giao hàng
                    </h5>
                    
                    <div v-if="savedAddresses.length > 0" class="mb-4 p-3 bg-urban-soft-box rounded-4 border border-urban-soft transition-all">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-bold text-urban m-0 font-sans-vn"><i class="bi bi-journal-bookmark-fill me-2"></i>Chọn từ sổ địa chỉ</h6>
                        <button type="button" class="btn btn-sm btn-outline-urban rounded-pill fw-bold font-sans-vn" @click="toggleAddressMode">
                            {{ useSavedAddress ? 'Nhập địa chỉ mới' : 'Dùng sổ địa chỉ' }}
                        </button>
                    </div>
                    <select v-if="useSavedAddress" class="form-select custom-input fw-semibold mt-2 font-sans-vn" v-model="selectedSavedAddressId" @change="applySelectedAddress">
                        <option v-for="addr in savedAddresses" :key="addr.id" :value="addr.id">
                            {{ addr.customer_name }} - {{ addr.customer_phone }} ({{ addr.shipping_address }}, {{ addr.ward }}, {{ addr.district }}, {{ addr.city }})
                        </option>
                    </select>
                    </div>

                    <div class="row g-3 font-sans-vn">
                    <div class="col-md-12">
                        <label class="form-label small fw-bold text-muted text-uppercase">Họ và tên người nhận <span class="text-danger">*</span></label>
                        <input type="text" class="form-control custom-input" v-model="form.fullname" @input="handleAddressEdit" required placeholder="Nhập họ và tên">
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted text-uppercase">Số điện thoại <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control custom-input" v-model="form.phone" @input="handleAddressEdit" required placeholder="Nhập số điện thoại">
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted text-uppercase">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control custom-input" v-model="form.email" required placeholder="Nhập địa chỉ email">
                    </div>

                    <!-- tỉnh thành -->
                    <div class="col-md-4 mt-3 position-relative">
                        <label class="form-label small fw-bold text-muted text-uppercase">Tỉnh/Thành phố <span class="text-danger">*</span></label>
                        <input type="text" class="form-control custom-input dropdown-search-input" 
                            v-model="searchProvince" 
                            @focus="showProvinceDrop = true"
                            @input="handleAddressEdit"
                            @blur="handleBlur('province')"
                            placeholder="Tìm Tỉnh/Thành..." required>
                        <i class="bi bi-chevron-down position-absolute text-muted" style="right: 1.2rem; top: 2.7rem; pointer-events: none; font-size: 0.8rem;"></i>
                        <ul v-if="showProvinceDrop" class="dropdown-menu w-100 show shadow border-0 custom-scrollbar-y p-1 dark:bg-[#212529]" style="max-height: 200px; position: absolute; z-index: 1050; top: 100%;">
                        <li v-for="c in filteredProvinces" :key="c.code"><a class="dropdown-item py-2 px-3 cursor-pointer rounded-2 transition-all hover-bg-effect dark:text-gray-300" @mousedown.prevent="selectProvince(c)">{{ c.name }}</a></li>
                        <li v-if="filteredProvinces.length === 0"><span class="dropdown-item text-muted py-2 fst-italic">Không tìm thấy</span></li>
                        </ul>
                    </div>

                    <!-- quận huyện -->
                    <div class="col-md-4 mt-3 position-relative">
                        <label class="form-label small fw-bold text-muted text-uppercase">Quận/Huyện <span class="text-danger">*</span></label>
                        <input type="text" class="form-control custom-input dropdown-search-input" 
                            v-model="searchDistrict" 
                            @focus="showDistrictDrop = true"
                            @input="handleAddressEdit"
                            @blur="handleBlur('district')"
                            placeholder="Tìm Quận/Huyện..." required :disabled="!addressHelper.province || loadingDistricts">
                        <i class="bi bi-chevron-down position-absolute text-muted" style="right: 1.2rem; top: 2.7rem; pointer-events: none; font-size: 0.8rem;"></i>
                        <ul v-if="showDistrictDrop && addressHelper.province" class="dropdown-menu w-100 show shadow border-0 custom-scrollbar-y p-1 dark:bg-[#212529]" style="max-height: 200px; position: absolute; z-index: 1050; top: 100%;">
                        <li v-for="d in filteredDistricts" :key="d.code"><a class="dropdown-item py-2 px-3 cursor-pointer rounded-2 transition-all hover-bg-effect dark:text-gray-300" @mousedown.prevent="selectDistrict(d)">{{ d.name }}</a></li>
                        <li v-if="filteredDistricts.length === 0"><span class="dropdown-item text-muted py-2 fst-italic">Không tìm thấy</span></li>
                        </ul>
                    </div>

                    <!-- phường xã -->
                    <div class="col-md-4 mt-3 position-relative">
                        <label class="form-label small fw-bold text-muted text-uppercase">Phường/Xã <span class="text-danger">*</span></label>
                        <input type="text" class="form-control custom-input dropdown-search-input" 
                            v-model="searchWard" 
                            @focus="showWardDrop = true"
                            @input="handleAddressEdit"
                            @blur="handleBlur('ward')"
                            placeholder="Tìm Phường/Xã..." required :disabled="!addressHelper.district || loadingWards">
                        <i class="bi bi-chevron-down position-absolute text-muted" style="right: 1.2rem; top: 2.7rem; pointer-events: none; font-size: 0.8rem;"></i>
                        <ul v-if="showWardDrop && addressHelper.district" class="dropdown-menu w-100 show shadow border-0 custom-scrollbar-y p-1 dark:bg-[#212529]" style="max-height: 200px; position: absolute; z-index: 1050; top: 100%;">
                        <li v-for="w in filteredWards" :key="w.code"><a class="dropdown-item py-2 px-3 cursor-pointer rounded-2 transition-all hover-bg-effect dark:text-gray-300" @mousedown.prevent="selectWard(w)">{{ w.name }}</a></li>
                        <li v-if="filteredWards.length === 0"><span class="dropdown-item text-muted py-2 fst-italic">Không tìm thấy</span></li>
                        </ul>
                    </div>
                    
                    <div class="col-12 mt-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Địa chỉ cụ thể <span class="text-danger">*</span></label>
                        <input type="text" class="form-control custom-input" v-model="addressHelper.detail" @input="handleAddressEdit" required placeholder="Nhập số nhà, tên đường">
                    </div>

                    <div class="col-12 mt-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Ghi chú đơn hàng</label>
                        <textarea class="form-control custom-input" rows="2" v-model="form.note" placeholder="Nhập ghi chú (nếu có)"></textarea>
                    </div>
                    </div>
                </div>

                <!-- 2. phương thức vận chuyển -->
                <div class="mb-5 font-sans-vn">
                    <h5 class="fw-bold text-c-dark dark:text-white mb-4 d-flex align-items-center">
                    <span class="step-number rounded-circle d-flex align-items-center justify-content-center me-2 shadow-sm" style="width: 28px; height: 28px; font-size: 0.9rem;">2</span> 
                    Phương thức vận chuyển
                    </h5>
                    
                    <label class="card rounded-4 shadow-sm cursor-pointer payment-method-card active-card" for="shipStandard">
                    <div class="card-body p-3 d-flex align-items-center">
                        <div class="form-check m-0 d-flex align-items-center w-100">
                        <input class="form-check-input fs-4 m-0 me-3 custom-radio" type="radio" name="shippingMethod" id="shipStandard" checked>
                        <div class="w-100 cursor-pointer d-flex justify-content-between align-items-center">
                            <div>
                            <div class="fw-bold text-c-dark dark:text-white fs-6">Giao hàng tiêu chuẩn</div>
                            <div class="small text-muted dark:text-gray-400">Thời gian giao hàng từ 2-4 ngày làm việc.</div>
                            </div>
                            <div class="fw-bold text-c-hover fs-5" v-if="shippingFee === 0">Miễn phí</div>
                            <div class="fw-bold text-c-dark dark:text-white fs-5" v-else>{{ formatCurrency(shippingFee) }}</div>
                        </div>
                        </div>
                    </div>
                    </label>
                </div>

                <!-- 3. phương thức thanh toán -->
                <div class="mb-5 font-sans-vn">
                    <h5 class="fw-bold text-c-dark dark:text-white mb-4 d-flex align-items-center">
                    <span class="step-number rounded-circle d-flex align-items-center justify-content-center me-2 shadow-sm" style="width: 28px; height: 28px; font-size: 0.9rem;">3</span> 
                    Phương thức thanh toán
                    </h5>
                    
                    <div class="d-flex flex-column gap-3">
                    <label class="card rounded-4 shadow-sm cursor-pointer payment-method-card" :class="form.payment_method === 'cod' ? 'active-card' : 'inactive-card'">
                        <div class="card-body p-3 d-flex align-items-center">
                        <input class="form-check-input fs-4 m-0 me-3 custom-radio" type="radio" name="paymentMethod" value="cod" v-model="form.payment_method">
                        <div class="d-flex align-items-center gap-3 w-100">
                            <div class="icon-box rounded p-2 d-flex align-items-center justify-content-center">
                            <i class="bi bi-cash-stack fs-3"></i>
                            </div>
                            <div>
                            <div class="fw-bold text-c-dark dark:text-white fs-6">Thanh toán khi nhận hàng (COD)</div>
                            <div class="small text-muted dark:text-gray-400">Thanh toán bằng tiền mặt khi shipper giao hàng tới.</div>
                            </div>
                        </div>
                        </div>
                    </label>

                    <label class="card rounded-4 shadow-sm cursor-pointer payment-method-card" :class="form.payment_method === 'momo' ? 'active-card' : 'inactive-card'">
                        <div class="card-body p-3 d-flex align-items-center">
                        <input class="form-check-input fs-4 m-0 me-3 custom-radio" type="radio" name="paymentMethod" value="momo" v-model="form.payment_method">
                        <div class="d-flex align-items-center gap-3 w-100">
                            <div class="icon-box rounded p-2 d-flex align-items-center justify-content-center border-0" style="background-color: #A50064;">
                            <span class="fw-bold fst-italic text-white" style="letter-spacing: -0.5px;">MoMo</span>
                            </div>
                            <div>
                            <div class="fw-bold text-c-dark dark:text-white fs-6">Thanh toán qua Ví MoMo</div>
                            <div class="small text-muted dark:text-gray-400">Quét mã QR hoặc chuyển hướng qua App MoMo an toàn.</div>
                            </div>
                        </div>
                        </div>
                    </label>
                    </div>
                </div>

                <!-- 4. xuất hóa đơn vat -->
                <div class="mb-5 font-sans-vn">
                    <div class="form-check d-flex align-items-center mb-3 p-0">
                    <input class="form-check-input ms-0 me-2 custom-radio fs-5" type="checkbox" id="requireVAT" v-model="requireVAT">
                    <label class="form-check-label fw-bold text-c-dark dark:text-gray-200 cursor-pointer" for="requireVAT">
                        Yêu cầu xuất hóa đơn đỏ (VAT) cho Công ty
                    </label>
                    </div>

                    <transition name="slide-fade">
                    <div v-if="requireVAT" class="p-4 rounded-4 vat-box mt-2 shadow-sm">
                        <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label small fw-bold text-muted">Tên công ty <span class="text-danger">*</span></label>
                            <input type="text" class="form-control custom-input" v-model="vatInfo.company_name" placeholder="Nhập tên công ty">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Mã số thuế <span class="text-danger">*</span></label>
                            <input type="text" class="form-control custom-input" v-model="vatInfo.tax_code" placeholder="Nhập mã số thuế">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Email nhận hóa đơn</label>
                            <input type="email" class="form-control custom-input" v-model="vatInfo.email" placeholder="Nhập email nhận hóa đơn">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label small fw-bold text-muted">Địa chỉ công ty <span class="text-danger">*</span></label>
                            <input type="text" class="form-control custom-input" v-model="vatInfo.address" placeholder="Nhập địa chỉ công ty">
                        </div>
                        </div>
                    </div>
                    </transition>
                </div>

                </div>

                <!-- cột phải -->
                <div class="col-lg-5">
                <div class="card shadow-sm rounded-4 summary-box sticky-top p-4 p-md-5" style="top: 100px;">
                    <h5 class="fw-bold text-c-dark dark:text-white mb-4 border-bottom border-light-subtle dark:border-gray-700 pb-3 text-uppercase tracking-wide font-sans-vn">
                    Tổng Kết Đơn Hàng
                    </h5>

                    <!-- danh sách sản phẩm -->
                    <div class="custom-scrollbar-y p-3 ms-n3 mb-4 font-sans-vn" style="max-height: 350px; overflow-y: auto; overflow-x: visible;">
                    
                    <template v-for="(group, gIdx) in cartGroups" :key="'grp'+gIdx">

                        <!-- combo -->
                        <div v-if="group.isLookbook" 
                            class="mb-3 pb-3 border-bottom border-light-subtle dark:border-gray-700 position-relative transition-all"
                            :class="{'pe-none opacity-50': updatingItemId === 'combo_' + group.lookbook_id}">
                        
                        <div v-if="updatingItemId === 'combo_' + group.lookbook_id" class="position-absolute top-50 start-50 translate-middle bouncing-loader" style="z-index: 10;">
                            <span></span><span></span><span></span>
                        </div>

                        <button type="button" class="btn-delete-circle d-flex align-items-center justify-content-center transition-all" @click="removeCombo(group)" title="Xóa khỏi đơn">
                            <i class="bi bi-x-lg"></i>
                        </button>

                        <div class="d-flex gap-3">
                            <div class="position-relative flex-shrink-0 mt-2 ms-2" style="width: 70px; height: 95px;">
                            <img :src="group.lookbook_image || group.items[0]?.image || '/client_placeholder.png'" 
                                    class="rounded-3 border border-light-subtle dark:border-gray-600 object-fit-cover bg-white w-100 h-100 cursor-pointer" 
                                    @click="toggleGroup(group.lookbook_id)" 
                                    @error="e => e.target.src='/client_placeholder.png'">
                            </div>
                            <div class="flex-grow-1 d-flex flex-column justify-content-between py-1 pe-2 mt-2">
                                <div>
                                <div class="cursor-pointer d-inline-block" @click="toggleGroup(group.lookbook_id)">
                                    <span class="badge bg-urban text-white rounded-pill px-2 py-1 shadow-sm font-sans-vn mb-1" style="font-size: 0.65rem;">
                                    <i class="bi bi-magic me-1"></i> {{ group.lookbook_name }}
                                    </span>
                                    <div class="text-muted small fw-bold font-sans-vn mb-1" style="font-size: 0.75rem;">Combo {{ group.items.length }} món đồ</div>
                                </div>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                <div class="quantity-box border dark:border-gray-600 rounded-2 d-flex bg-light dark:bg-[#212529] shadow-sm overflow-hidden" style="height: 28px; width: 75px;">
                                    <button type="button" class="btn p-0 border-0 text-dark dark:text-gray-300 w-100 h-100 d-flex align-items-center justify-content-center hover-urban" @click="decreaseComboQty(group)"><i class="bi bi-dash small"></i></button>
                                    <input type="number" class="form-control border-0 text-center fw-bold px-0 h-100 bg-transparent dark:text-white shadow-none p-0 no-spinners" style="font-size: 0.8rem;" :value="group.comboQuantity" @change="onManualComboQtyChange(group, $event.target.value)">
                                    <button type="button" class="btn p-0 border-0 text-dark dark:text-gray-300 w-100 h-100 d-flex align-items-center justify-content-center hover-urban" @click="increaseComboQty(group)"><i class="bi bi-plus small"></i></button>
                                </div>
                                <div class="fw-bold text-c-dark dark:text-gray-300" style="font-size: 0.95rem;">{{ formatCurrency(group.totalPrice) }}</div>
                                </div>
                            </div>
                        </div>
                        
                        <div v-show="expandedGroups.includes(group.lookbook_id)" class="combo-details mt-3 pt-3 border-top border-light-subtle dark:border-gray-700 ps-2">
                            <div class="d-flex flex-column gap-2">
                                <div v-for="item in group.items" :key="'cb_item_'+item.variant_id" class="d-flex align-items-center gap-3">
                                <img :src="item.image || '/client_placeholder.png'" style="width: 35px; height: 45px;" class="rounded-2 border dark:border-gray-600 object-fit-cover shadow-sm bg-white">
                                <div class="flex-grow-1">
                                    <div class="fw-semibold text-dark dark:text-gray-200 line-clamp-1 font-sans-vn" style="font-size: 0.8rem;">{{ item.product_name }}</div>
                                    <div class="text-secondary dark:text-gray-400 font-sans-vn d-flex justify-content-between pe-1 mt-1" style="font-size: 0.7rem;">
                                        <span>{{ item.attributes || 'Mặc định' }} <span class="mx-1">|</span> <span class="fw-bold text-dark dark:text-gray-300">{{ formatCurrency(item.current_price) }}</span></span>
                                        <span class="fw-bold">x{{ item.quantity }}</span>
                                    </div>
                                </div>
                                </div>
                            </div>
                        </div>
                        </div>

                        <!-- sản phẩm lẻ -->
                        <template v-else>
                        <div v-for="item in group.items" :key="'cartItem'+item.variant_id" 
                            class="d-flex gap-3 mb-3 pb-3 border-bottom border-light-subtle dark:border-gray-700 position-relative transition-all"
                            :class="{'pe-none opacity-50': updatingItemId === item.variant_id}">
                            
                            <div v-if="updatingItemId === item.variant_id" class="position-absolute top-50 start-50 translate-middle bouncing-loader" style="z-index: 10;">
                            <span></span><span></span><span></span>
                            </div>

                            <button type="button" class="btn-delete-circle d-flex align-items-center justify-content-center transition-all" @click="removeItem(item)" title="Xóa khỏi đơn">
                            <i class="bi bi-x-lg"></i>
                            </button>

                            <div class="position-relative flex-shrink-0 mt-2 ms-2" style="width: 70px; height: 95px;">
                            <img :src="item.image || '/client_placeholder.png'" class="rounded-3 border border-light-subtle dark:border-gray-600 object-fit-cover bg-white w-100 h-100" @error="e => e.target.src='/client_placeholder.png'">
                            </div>
                            
                            <div class="flex-grow-1 d-flex flex-column justify-content-between py-1 pe-2 mt-2">
                            <div>
                                <h6 class="fw-bold text-c-dark dark:text-gray-200 mb-1 line-clamp-2 pe-4" style="font-size: 0.85rem;">{{ item.product_name }}</h6>
                                <div class="text-muted small mb-1 d-flex align-items-center gap-2" style="font-size: 0.75rem;">
                                <span class="bg-white dark:bg-[#2b3035] text-dark dark:text-gray-300 border dark:border-gray-600 px-1 py-0.5 rounded shadow-sm">{{ item.attributes || 'Mặc định' }}</span>
                                </div>
                                <div v-if="item.stock_warning" class="text-danger small mt-1 fw-bold" style="font-size: 0.7rem;"><i class="bi bi-exclamation-triangle"></i> Vượt tồn kho</div>
                                <div v-if="!item.is_available" class="text-danger small mt-1 fw-bold" style="font-size: 0.7rem;"><i class="bi bi-x-circle"></i> Ngừng kinh doanh</div>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <div class="quantity-box border dark:border-gray-600 rounded-2 d-flex bg-light dark:bg-[#212529] shadow-sm overflow-hidden" style="height: 28px; width: 75px;">
                                <button type="button" class="btn p-0 border-0 text-dark dark:text-gray-300 w-100 h-100 d-flex align-items-center justify-content-center hover-urban" @click="decreaseQty(item)"><i class="bi bi-dash small"></i></button>
                                <input type="number" class="form-control border-0 text-center fw-bold px-0 h-100 bg-transparent dark:text-white shadow-none p-0 no-spinners" style="font-size: 0.8rem;" :value="item.quantity" @change="onManualQtyChange(item, $event.target.value)">
                                <button type="button" class="btn p-0 border-0 text-dark dark:text-gray-300 w-100 h-100 d-flex align-items-center justify-content-center hover-urban" @click="increaseQty(item)"><i class="bi bi-plus small"></i></button>
                                </div>
                                <div class="fw-bold text-c-dark dark:text-gray-300" style="font-size: 0.95rem;">{{ formatCurrency(item.current_price * item.quantity) }}</div>
                            </div>
                            </div>
                        </div>
                        </template>

                    </template>
                    </div>

                    <!-- mã khuyến mãi -->
                    <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2 voucher-dropdown-container position-relative font-sans-vn">
                        <label class="form-label small fw-bold text-muted text-uppercase tracking-wide m-0">Mã ưu đãi / Khuyến mãi</label>
                        
                        <a href="#" class="text-urban small text-decoration-none fw-bold d-flex align-items-center transition-color hover-text-dark" @click.prevent="isVoucherDropdownOpen = !isVoucherDropdownOpen">
                        <i class="bi bi-card-list me-1"></i> Chọn mã
                        </a>
                        
                        <transition name="fade">
                        <div v-if="isVoucherDropdownOpen" class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 p-0 dark:bg-[#1a2533] z-index-dropdown d-block position-absolute mt-2" style="width: 360px; max-width: 90vw; right: 0; top: 100%;">
                            <div class="bg-light dark:bg-[#212529] px-4 py-3 border-bottom dark:border-gray-700 rounded-top-4">
                                <h6 class="fw-bold m-0 text-dark dark:text-white"><i class="bi bi-ticket-perforated text-urban me-2"></i>Mã Giảm Giá Của Bạn</h6>
                            </div>
                            
                            <div class="custom-scrollbar-y p-3" style="max-height: 380px; overflow-y: auto;">
                                <div v-if="availableCoupons.length === 0" class="text-center py-4 text-muted fst-italic small">
                                Chưa có mã giảm giá nào khả dụng.
                                </div>
                                
                                <div v-else v-for="coupon in availableCoupons" :key="coupon.code" 
                                    class="card border border-light-subtle dark:border-gray-600 rounded-3 mb-3 shadow-sm position-relative overflow-hidden transition-all" 
                                    :class="{'opacity-50 bg-light dark:bg-[#121416] pe-none': subtotalAfterTier < coupon.min_spend}">
                                
                                <div v-if="subtotalAfterTier < coupon.min_spend" class="position-absolute top-0 start-0 w-100 h-100 bg-white opacity-25 dark:bg-black z-index-1 pe-none"></div>

                                <div class="card-body p-3 position-relative z-index-2">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="badge bg-urban text-white fs-6 font-monospace shadow-sm">{{ coupon.code }}</span>
                                        <button v-if="subtotalAfterTier >= coupon.min_spend" type="button" class="btn btn-sm btn-outline-urban rounded-pill px-3 fw-bold" @click="applyVoucherFromList(coupon)">Dùng mã</button>
                                        <span v-else class="text-danger fw-bold" style="font-size: 0.7rem;">Chưa đủ điều kiện</span>
                                    </div>
                                    
                                    <h6 class="fw-bold text-dark dark:text-white mb-1" style="font-size: 0.9rem;">{{ coupon.name || 'Mã giảm giá' }}</h6>
                                    
                                    <p class="text-muted mb-2" style="font-size: 0.8rem; line-height: 1.4;">
                                        Giảm <strong class="text-danger">{{ formatDiscount(coupon) }}</strong> cho đơn từ {{ formatCurrency(coupon.min_spend) }}
                                        <span v-if="coupon.max_discount_amount > 0"> (Tối đa {{ formatCurrency(coupon.max_discount_amount) }})</span>.
                                    </p>
                                    
                                    <div class="d-flex justify-content-between align-items-center mt-2" v-if="coupon.usage_limit">
                                        <div class="progress flex-grow-1 me-3 bg-secondary bg-opacity-25 rounded-pill" style="height: 6px;">
                                            <div class="progress-bar bg-danger rounded-pill" :style="{ width: getVoucherProgress(coupon) + '%' }"></div>
                                        </div>
                                        <span class="text-muted fw-semibold" style="font-size: 0.7rem;">Đã dùng {{ getVoucherProgress(coupon) }}%</span>
                                    </div>
                                    <div v-else class="text-success fw-semibold mt-2" style="font-size: 0.7rem;">
                                        <i class="bi bi-infinity"></i> Không giới hạn lượt dùng
                                    </div>
                                    
                                    <div class="mt-2 text-danger fw-semibold" style="font-size: 0.7rem;" v-if="coupon.end_time">
                                        <i class="bi bi-clock me-1"></i> HSD: {{ formatDateTime(coupon.end_time) }}
                                    </div>
                                </div>
                                </div>
                            </div>
                        </div>
                        </transition>
                    </div>
                    
                    <div class="input-group custom-input-group rounded-3 overflow-hidden bg-white dark:bg-[#212529]" :class="{'border-success': appliedCouponValid}">
                        <span class="input-group-text bg-transparent border-0 text-muted"><i class="bi bi-ticket-perforated"></i></span>
                        <input type="text" class="form-control bg-transparent border-0 dark:text-white font-monospace text-uppercase shadow-none" v-model="selectedCouponCode" placeholder="NHẬP MÃ ZYRO..." :readonly="appliedCouponValid">
                        
                        <button v-if="appliedCouponValid" class="btn btn-danger fw-bold px-4 rounded-0 font-sans-vn" type="button" @click="removePromo">Gỡ bỏ</button>
                        <button v-else class="btn btn-c-dark fw-bold px-4 rounded-0 font-sans-vn" type="button" @click="applyPromo">Áp dụng</button>
                    </div>
                    
                    <div v-if="appliedCouponValid" class="text-success small fw-bold mt-2 d-flex align-items-center animation-fade-in font-sans-vn">
                        <i class="bi bi-check-circle-fill me-1"></i> Đã áp dụng mã ưu đãi thành công!
                    </div>
                    </div>

                    <!-- chi tiết tính tiền -->
                    <div class="d-flex justify-content-between mb-2 text-c-dark dark:text-gray-300 small font-sans-vn">
                      <span>Tạm tính ({{ cartStore.totalQuantity }} SP)</span>
                      <span class="fw-semibold">{{ formatCurrency(cartStore.totalPrice) }}</span>
                    </div>

                    <div class="d-flex justify-content-between mb-2 text-success small font-sans-vn" v-if="tierDiscountAmount > 0">
                      <span>
                        Ưu đãi Hạng <span class="fw-bold">{{ currentTierData?.name }}</span>
                        <span class="fst-italic" style="opacity: 0.85;">
                          (-{{ currentTierData?.discount_percent }}%<span v-if="currentTierData?.max_discount_amount > 0">, max {{ formatCurrency(currentTierData.max_discount_amount) }}</span>)
                        </span>:
                      </span>
                      <span class="fw-bold">- {{ formatCurrency(tierDiscountAmount) }}</span>
                    </div>

                    <div class="d-flex justify-content-between mb-2 text-success small font-sans-vn" v-if="voucherDiscountAmount > 0">
                      <span>
                        Giảm giá Voucher
                        <span v-if="appliedVoucherInfo" class="fst-italic" style="opacity: 0.85;">
                          (-{{ appliedVoucherInfo.label }})
                        </span>:
                      </span>
                      <span class="fw-bold">- {{ formatCurrency(voucherDiscountAmount) }}</span>
                    </div>

                    <div class="d-flex justify-content-between mb-4 text-c-dark dark:text-gray-300 small font-sans-vn">
                      <span>Phí vận chuyển</span>
                      <span class="fw-bold text-c-hover" v-if="shippingFee === 0">Miễn phí</span>
                      <span class="fw-bold" v-else>{{ formatCurrency(shippingFee) }}</span>
                    </div>

                    <!-- tổng cộng -->
                    <div class="d-flex justify-content-between align-items-center pt-3 border-top border-light-subtle dark:border-gray-700 mb-4 font-sans-vn">
                      <span class="fw-bold text-uppercase fs-5 text-c-dark dark:text-white">Tổng Cộng</span>
                      <div class="text-end">
                          <span class="fw-bold text-danger fs-3 d-block lh-1 mb-1">{{ formatCurrency(finalTotal) }}</span>
                          <small class="text-muted fst-italic" style="font-size: 0.7rem;">(Đã bao gồm VAT)</small>
                      </div>
                    </div>

                    <button type="submit" class="btn btn-c-dark btn-lg w-100 rounded-pill fw-bold text-uppercase tracking-widest shadow-lg mt-2 hover-transform d-flex align-items-center justify-content-center font-sans-vn" :disabled="isProcessing">
                      <span v-if="isProcessing" class="spinner-border spinner-border-sm me-2"></span>
                      <template v-else>ĐẶT HÀNG <i class="bi bi-bag-check ms-2"></i></template>
                    </button>

                    <p class="text-center text-muted mt-3 mb-0 font-sans-vn" style="font-size: 0.7rem;">
                    Bằng việc bấm Đặt hàng, bạn đồng ý với <router-link to="/terms-of-service" target="_blank" class="text-c-dark dark:text-gray-300 text-decoration-underline">Điều khoản sử dụng</router-link> của ZYRO.
                    </p>

                    <div class="mt-4 pt-3 border-top border-light-subtle dark:border-gray-700 d-flex justify-content-center gap-3 opacity-75">
                    <i class="bi bi-shield-check fs-4 text-c-dark dark:text-gray-400" title="Thanh toán an toàn 100%"></i>
                    <i class="bi bi-arrow-return-left fs-4 text-c-dark dark:text-gray-400" title="Đổi trả dễ dàng"></i>
                    <i class="bi bi-credit-card-fill fs-4 text-c-dark dark:text-gray-400" title="Đa dạng phương thức"></i>
                    </div>
                </div>
                </div>

            </div>
            </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, reactive, onMounted, onBeforeUnmount, watch } from 'vue';
import { useRouter } from 'vue-router';
import Swal from 'sweetalert2';
import axios from 'axios';
import api from '@/utils/axios';

import { useCartStore } from '@/stores/cartStore';
import { ZyroSwal } from '@/components/client/ZyroSwal';

const router = useRouter();
const cartStore = useCartStore();

const isInitLoading = ref(true);
const isProcessing = ref(false);
const updatingItemId = ref(null);

const form = ref({
  fullname: '',
  phone: '',
  email: '',
  note: '',
  payment_method: 'cod'
});

const savedAddresses = ref([]);
const useSavedAddress = ref(false);
const selectedSavedAddressId = ref('');
const currentUser = ref({});

const provinces = ref([]);
const districts = ref([]);
const wards = ref([]);
const loadingDistricts = ref(false);
const loadingWards = ref(false);
const addressHelper = reactive({ province: '', district: '', ward: '', detail: '' });

const searchProvince = ref('');
const searchDistrict = ref('');
const searchWard = ref('');

const showProvinceDrop = ref(false);
const showDistrictDrop = ref(false);
const showWardDrop = ref(false);

const availableCoupons = ref([]);
const selectedCouponCode = ref('');
const appliedCouponValid = ref(false);
const isVoucherDropdownOpen = ref(false);

// state logic
const allTiers = ref([]);
const totalSpent = ref(0);

const currentTierData = computed(() => {
  if (allTiers.value.length === 0) return null;
  let current = null;
  for (let i = 0; i < allTiers.value.length; i++) {
    if (totalSpent.value >= allTiers.value[i].min_spent) {
        current = allTiers.value[i];
    }
  }
  return current;
});

// tính toán ưu đãi hạng
const tierDiscountAmount = computed(() => {
    if (!currentTierData.value || !currentTierData.value.discount_percent) return 0;
    let amount = (cartStore.totalPrice * currentTierData.value.discount_percent) / 100;
    
    if (currentTierData.value.max_discount_amount > 0 && amount > currentTierData.value.max_discount_amount) {
        amount = parseFloat(currentTierData.value.max_discount_amount);
    }
    return amount;
});

const subtotalAfterTier = computed(() => {
    return Math.max(0, cartStore.totalPrice - tierDiscountAmount.value);
});


const FREESHIP_THRESHOLD = 1000000;

const requireVAT = ref(false);
const vatInfo = ref({ company_name: '', tax_code: '', email: '', address: '' });

const formatCurrency = (val) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val || 0);

const removeAccents = (str) => {
  if (!str) return '';
  return str.toString().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/đ/g, 'd').replace(/Đ/g, 'D').toLowerCase().trim();
};

const filteredProvinces = computed(() => {
  if (!searchProvince.value) return provinces.value;
  const q = removeAccents(searchProvince.value);
  return provinces.value.filter(p => removeAccents(p.name).includes(q));
});
const filteredDistricts = computed(() => {
  if (!searchDistrict.value) return districts.value;
  const q = removeAccents(searchDistrict.value);
  return districts.value.filter(d => removeAccents(d.name).includes(q));
});
const filteredWards = computed(() => {
  if (!searchWard.value) return wards.value;
  const q = removeAccents(searchWard.value);
  return wards.value.filter(w => removeAccents(w.name).includes(q));
});

const initCheckoutData = async () => {
  try {
    const res = await api.get('/client/checkout/init');
    if (res.data.success) {
      savedAddresses.value = res.data.addresses || [];
      availableCoupons.value = res.data.coupons || [];
      currentUser.value = res.data.user || {};
      
      allTiers.value = res.data.tiers || [];
      totalSpent.value = res.data.user?.total_spent || 0;

      if (savedAddresses.value.length > 0) {
        const defaultAddr = savedAddresses.value.find(a => a.is_default) || savedAddresses.value[0];
        selectedSavedAddressId.value = defaultAddr.id;
        applySavedAddress(defaultAddr);
        useSavedAddress.value = true;
      } else {
        form.value.fullname = currentUser.value.name || '';
        form.value.phone = currentUser.value.phone || '';
        form.value.email = currentUser.value.email || '';
      }
    }
  } catch (err) {
    console.error('Lỗi Init Checkout', err);
  }
};

const toggleAddressMode = () => {
  useSavedAddress.value = !useSavedAddress.value;
  if (useSavedAddress.value && savedAddresses.value.length > 0) {
    applySelectedAddress();
  } else {
    form.value.fullname = currentUser.value?.name || '';
    form.value.phone = currentUser.value?.phone || '';
    form.value.email = currentUser.value?.email || '';
    
    addressHelper.province = ''; searchProvince.value = '';
    addressHelper.district = ''; searchDistrict.value = '';
    addressHelper.ward = ''; searchWard.value = '';
    addressHelper.detail = '';
  }
};

const handleAddressEdit = () => {
  if (useSavedAddress.value) {
    useSavedAddress.value = false;
    selectedSavedAddressId.value = '';
  }
};

const applySavedAddress = (addr) => {
  form.value.fullname = addr.customer_name;
  form.value.phone = addr.customer_phone;
  form.value.email = currentUser.value?.email || '';
  
  addressHelper.province = addr.city;
  searchProvince.value = addr.city;
  addressHelper.district = addr.district;
  searchDistrict.value = addr.district;
  addressHelper.ward = addr.ward;
  searchWard.value = addr.ward;
  addressHelper.detail = addr.shipping_address;
};

const applySelectedAddress = () => {
  const addr = savedAddresses.value.find(a => a.id === selectedSavedAddressId.value);
  if (addr) applySavedAddress(addr);
};

const fetchProvinces = async () => {
  try {
    const res = await axios.get('https://provinces.open-api.vn/api/p/');
    provinces.value = res.data;
  } catch (err) { }
};

const onProvinceChange = async () => {
  addressHelper.district = ''; addressHelper.ward = ''; districts.value = []; wards.value = [];
  const p = provinces.value.find(i => i.name === addressHelper.province);
  if (p) {
    loadingDistricts.value = true;
    try {
      const res = await axios.get(`https://provinces.open-api.vn/api/p/${p.code}?depth=2`);
      districts.value = res.data.districts;
    } finally { loadingDistricts.value = false; }
  }
};

const onDistrictChange = async () => {
  addressHelper.ward = ''; wards.value = [];
  const d = districts.value.find(i => i.name === addressHelper.district);
  if (d) {
    loadingWards.value = true;
    try {
      const res = await axios.get(`https://provinces.open-api.vn/api/d/${d.code}?depth=2`);
      wards.value = res.data.wards;
    } finally { loadingWards.value = false; }
  }
};

const selectProvince = (p) => {
  handleAddressEdit();
  addressHelper.province = p.name; searchProvince.value = p.name; showProvinceDrop.value = false; onProvinceChange();
};
const selectDistrict = (d) => {
  handleAddressEdit();
  addressHelper.district = d.name; searchDistrict.value = d.name; showDistrictDrop.value = false; onDistrictChange();
};
const selectWard = (w) => {
  handleAddressEdit();
  addressHelper.ward = w.name; searchWard.value = w.name; showWardDrop.value = false;
};

const handleBlur = (type) => {
  setTimeout(() => {
      showProvinceDrop.value = false; showDistrictDrop.value = false; showWardDrop.value = false;
      if (type === 'province' && searchProvince.value !== addressHelper.province) searchProvince.value = addressHelper.province;
      else if (type === 'district' && searchDistrict.value !== addressHelper.district) searchDistrict.value = addressHelper.district;
      else if (type === 'ward' && searchWard.value !== addressHelper.ward) searchWard.value = addressHelper.ward;
  }, 200);
};

const expandedGroups = ref([]);

const cartGroups = computed(() => {
  const result = [];
  const normalGroup = { isLookbook: false, items: [] };

  cartStore.items.forEach(item => {
    if (item.lookbook_id) {
      let group = result.find(g => g.isLookbook && g.lookbook_id === item.lookbook_id);
      if (!group) {
        group = { 
          isLookbook: true, 
          lookbook_id: item.lookbook_id, 
          lookbook_name: item.lookbook_name || 'Combo Phong Cách',
          lookbook_image: item.lookbook_image,
          items: [],
          comboQuantity: item.quantity, 
          totalPrice: 0 
        };
        result.push(group);
      }
      group.items.push(item);
      group.totalPrice += (item.current_price * item.quantity);
    } else {
      normalGroup.items.push(item);
    }
  });

  if (normalGroup.items.length > 0) {
    result.push(normalGroup);
  }

  return result;
});

const toggleGroup = (id) => {
  if (expandedGroups.value.includes(id)) {
    expandedGroups.value = expandedGroups.value.filter(gId => gId !== id);
  } else {
    expandedGroups.value.push(id);
  }
};

const increaseComboQty = async (group) => {
  const newQty = group.comboQuantity + 1;
  if (newQty > 50) return;
  
  updatingItemId.value = 'combo_' + group.lookbook_id;
  try {
    for (const item of group.items) {
      await cartStore.updateQuantity(item.item_id, item.variant_id, newQty);
    }
  } finally {
    updatingItemId.value = null;
  }
};

const decreaseComboQty = async (group) => {
  if (group.comboQuantity <= 1) return;
  const newQty = group.comboQuantity - 1;
  
  updatingItemId.value = 'combo_' + group.lookbook_id;
  try {
    for (const item of group.items) {
      await cartStore.updateQuantity(item.item_id, item.variant_id, newQty);
    }
  } finally {
    updatingItemId.value = null;
  }
};

const onManualComboQtyChange = async (group, newQty) => {
  let qty = parseInt(newQty);
  if (isNaN(qty) || qty < 1) qty = 1;
  if (qty > 50) qty = 50;

  if (qty === group.comboQuantity) {
     const input = document.activeElement;
     if (input && input.tagName === 'INPUT') input.value = qty;
     return; 
  }

  updatingItemId.value = 'combo_' + group.lookbook_id;
  try {
    for (const item of group.items) {
      await cartStore.updateQuantity(item.item_id, item.variant_id, qty);
    }
  } finally {
    updatingItemId.value = null;
  }
};

const removeCombo = (group) => {
  ZyroSwal.confirmDelete(group.lookbook_name).then(async (result) => {
    if (result.isConfirmed) {
      updatingItemId.value = 'combo_' + group.lookbook_id;
      try {
        for (const item of group.items) {
          await cartStore.removeItem(item.item_id, item.variant_id);
        }
        ZyroSwal.toastSuccess('Đã xóa bộ sưu tập khỏi đơn');
        if (cartStore.items.length === 0) router.push('/cart');
      } finally {
        updatingItemId.value = null;
      }
    }
  });
};

const increaseQty = async (item) => {
  const maxStock = item.current_stock !== undefined ? item.current_stock : 50; 
  if (item.quantity >= maxStock) return;
  updatingItemId.value = item.variant_id;
  try { await cartStore.updateQuantity(item.item_id, item.variant_id, item.quantity + 1); } 
  finally { updatingItemId.value = null; }
};

const decreaseQty = async (item) => {
  if (item.quantity > 1) {
    updatingItemId.value = item.variant_id;
    try { await cartStore.updateQuantity(item.item_id, item.variant_id, item.quantity - 1); } 
    finally { updatingItemId.value = null; }
  }
};

const onManualQtyChange = async (item, newQty) => {
  let qty = parseInt(newQty);
  if (isNaN(qty) || qty < 1) qty = 1;
  
  const maxStock = item.current_stock !== undefined ? item.current_stock : 50;
  const limit = Math.min(50, maxStock);
  if (qty > limit) qty = limit;

  if (qty === item.quantity) {
     const input = document.activeElement;
     if (input && input.tagName === 'INPUT') input.value = qty;
     return;
  }

  updatingItemId.value = item.variant_id;
  try {
    await cartStore.updateQuantity(item.item_id, item.variant_id, qty);
  } finally {
    updatingItemId.value = null;
  }
};

const removeItem = (item) => {
  ZyroSwal.confirmDelete(item.product_name).then(async (result) => {
    if (result.isConfirmed) {
      updatingItemId.value = item.variant_id;
      try {
        await cartStore.removeItem(item.item_id, item.variant_id);
        ZyroSwal.toastSuccess('Đã xóa sản phẩm');
        if (cartStore.items.length === 0) router.push('/cart');
      } finally { updatingItemId.value = null; }
    }
  });
};

const formatDiscount = (coupon) => {
  if (coupon.discount_type === 'percent' || coupon.discount_type === 'percentage') {
    return `${parseFloat(coupon.discount_value)}%`;
  }
  return formatCurrency(coupon.discount_value);
};
const formatDateTime = (dateString) => {
  if (!dateString) return '';
  const d = new Date(dateString);
  return d.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' });
};
const getVoucherProgress = (coupon) => {
  if (!coupon.usage_limit) return 0;
  const count = coupon.usage_count || 0;
  let percent = (count / coupon.usage_limit) * 100;
  return Math.min(100, Math.round(percent));
};

const closeVoucherDropdown = (e) => {
  if (isVoucherDropdownOpen.value && !e.target.closest('.voucher-dropdown-container')) {
    isVoucherDropdownOpen.value = false;
  }
};

const applyVoucherFromList = (coupon) => {
  selectedCouponCode.value = coupon.code;
  applyPromo();
  isVoucherDropdownOpen.value = false;
};

const applyPromo = () => {
  if (!selectedCouponCode.value) return;
  
  const code = selectedCouponCode.value.toUpperCase();
  const coupon = availableCoupons.value.find(c => c.code.toUpperCase() === code);
  
  if (!coupon) {
     appliedCouponValid.value = false;
     ZyroSwal.toastError('Mã ưu đãi không tồn tại hoặc đã hết hạn!');
     return;
  }

  if (subtotalAfterTier.value < coupon.min_spend) {
     appliedCouponValid.value = false;
     ZyroSwal.toastError(`Đơn hàng cần đạt tối thiểu ${formatCurrency(coupon.min_spend)} sau khi áp dụng Hạng để dùng mã này.`);
     return;
  }

  appliedCouponValid.value = true;
  selectedCouponCode.value = code; 
  ZyroSwal.toastSuccess('Áp dụng mã ưu đãi thành công!');
};

const removePromo = () => {
  selectedCouponCode.value = '';
  appliedCouponValid.value = false;
  ZyroSwal.toastSuccess('Đã gỡ mã giảm giá');
};

watch(() => subtotalAfterTier.value, (newTotal) => {
  if (appliedCouponValid.value && selectedCouponCode.value) {
    const c = availableCoupons.value.find(cp => cp.code.toUpperCase() === selectedCouponCode.value.toUpperCase());
    if (c && newTotal < c.min_spend) {
      removePromo();
      ZyroSwal.toastError('Đơn hàng không còn đủ điều kiện áp dụng mã giảm giá do thay đổi tạm tính.');
    }
  }
});

const appliedVoucherInfo = computed(() => {
  if (!appliedCouponValid.value || !selectedCouponCode.value) return null;
  const coupon = availableCoupons.value.find(c => c.code.toUpperCase() === selectedCouponCode.value.toUpperCase());
  if (!coupon) return null;
  
  if (coupon.discount_type === 'percent' || coupon.discount_type === 'percentage') {
      let label = `${parseFloat(coupon.discount_value)}%`;
      if (coupon.max_discount_amount > 0) {
          label += `, tối đa ${formatCurrency(coupon.max_discount_amount)}`;
      }
      return { label };
  } else {
      return { label: `${formatCurrency(coupon.discount_value)}` };
  }
});

const voucherDiscountAmount = computed(() => {
  if (!appliedCouponValid.value || !selectedCouponCode.value) return 0;
  
  const coupon = availableCoupons.value.find(c => c.code.toUpperCase() === selectedCouponCode.value.toUpperCase());
  if (!coupon) return 0;
  
  if (subtotalAfterTier.value < coupon.min_spend) return 0;

  let amount = 0;
  if (coupon.discount_type === 'percent' || coupon.discount_type === 'percentage') {
      amount = (subtotalAfterTier.value * parseFloat(coupon.discount_value)) / 100;
      if (coupon.max_discount_amount && amount > coupon.max_discount_amount) {
          amount = parseFloat(coupon.max_discount_amount);
      }
  } else {
      amount = parseFloat(coupon.discount_value);
  }
  return amount;
});

// tự động tính phí vận chuyển dựa trên địa chỉ (hợp bộ với backend)
const shippingFee = computed(() => {
  if (subtotalAfterTier.value >= FREESHIP_THRESHOLD) return 0;

  const province = addressHelper.province ? addressHelper.province.toLowerCase() : '';
  if (province.includes('hà nội') || province.includes('hồ chí minh')) {
      return 30000;
  }
  return 40000;
});

const finalTotal = computed(() => {
  const total = subtotalAfterTier.value + shippingFee.value - voucherDiscountAmount.value;
  return total > 0 ? total : 0;
});

const placeOrder = async () => {
  if (cartStore.items.length === 0) { ZyroSwal.toastError('Giỏ hàng trống'); return; }
  
  const invalidItem = cartStore.items.find(i => !i.is_available || i.stock_warning);
  if (invalidItem) {
     Swal.fire('Chú ý', `Sản phẩm "${invalidItem.product_name}" đã hết hàng hoặc vượt quá tồn kho khả dụng. Vui lòng kiểm tra lại.`, 'warning');
     return;
  }

  if (!addressHelper.province || !addressHelper.district || !addressHelper.ward || !addressHelper.detail) {
    Swal.fire('Thiếu thông tin', 'Vui lòng chọn đầy đủ địa chỉ giao hàng.', 'warning');
    return;
  }

  isProcessing.value = true;
  ZyroSwal.showLoading('Đang xử lý đơn hàng');
  
  try {
    const payload = {
      customer_name: form.value.fullname,
      customer_phone: form.value.phone,
      customer_email: form.value.email,
      customer_address: `${addressHelper.detail}, ${addressHelper.ward}, ${addressHelper.district}, ${addressHelper.province}`,
      user_address_id: useSavedAddress.value ? selectedSavedAddressId.value : null,
      order_note: form.value.note,
      payment_method: form.value.payment_method, 
      coupon_code: appliedCouponValid.value ? selectedCouponCode.value : null,
      require_vat: requireVAT.value,
      vat_info: requireVAT.value ? vatInfo.value : null
    };

    const res = await api.post('/client/checkout/process', payload);

    if (res.data.success) {
       if (form.value.payment_method === 'momo' && res.data.payment_url) {
           ZyroSwal.close();
           window.location.href = res.data.payment_url;
       } else {
           await cartStore.fetchDBCart(); 
           ZyroSwal.close();
           router.push(`/checkout/success?order=${res.data.data?.order_code || ''}`); 
       }
    }
  } catch (err) {
    ZyroSwal.close();
    if (err.response && err.response.status === 429) {
        Swal.fire('Chú ý', err.response.data.message, 'warning');
    } else if (err.response && err.response.status === 422) {
        const errors = err.response.data.errors;
        let errorMessage = err.response.data.message;
        if (errors) {
            errorMessage = Object.values(errors).flat()[0];
        }
        Swal.fire('Thiếu thông tin', errorMessage, 'warning');
    } else {
        Swal.fire('Lỗi đặt hàng', err.response?.data?.message || 'Có sự cố xảy ra. Vui lòng thử lại.', 'error');
    }
  } finally {
    isProcessing.value = false;
  }
};

onMounted(async () => {
  window.scrollTo(0, 0);
  document.addEventListener('click', closeVoucherDropdown);

  isInitLoading.value = true;

  try {
    await cartStore.initCart();
    if (cartStore.items.length === 0) {
        router.push('/cart');
        return;
    }
    
    fetchProvinces();
    
    const token = localStorage.getItem('access_token');
    
    const minDelay = new Promise(resolve => setTimeout(resolve, 800));
    const initTask = token ? initCheckoutData() : Promise.resolve();

    await Promise.all([initTask, minDelay]);

  } catch (error) {
    console.error("Lỗi:", error);
  } finally {
    isInitLoading.value = false;
  }
});

onBeforeUnmount(() => {
  document.removeEventListener('click', closeVoucherDropdown);
});
</script>

<style scoped>
.checkout-page-wrapper { width: 100%; }

.zyro-container { width: 100%; max-width: 1310px; margin: 0 auto; padding-left: 20px; padding-right: 20px; }
@media (min-width: 1400px) { .zyro-container { padding-left: 0; padding-right: 0; } }

.text-c-dark { color: var(--color-c-dark) !important; }
html.dark .text-c-dark { color: #f8f9fa !important; }
.text-c-hover { color: var(--color-c-hover) !important; }
.bg-c-dark { background-color: var(--color-c-dark) !important; color: white; }
.btn-c-dark { background-color: var(--color-c-dark); color: white; border: none; transition: 0.2s ease; }
.btn-c-dark:hover { background-color: var(--color-c-hover); color: white; }

.bg-urban-soft-box { background-color: rgba(84, 119, 146, 0.08) !important; }
html.dark .bg-urban-soft-box { background-color: rgba(255, 255, 255, 0.05) !important; }
.border-urban-soft { border-color: rgba(84, 119, 146, 0.2) !important; }
html.dark .border-urban-soft { border-color: rgba(255, 255, 255, 0.1) !important; }
.text-urban { color: var(--color-c-hover, #547792) !important; }
.btn-outline-urban { color: var(--color-c-hover, #547792); border-color: var(--color-c-hover, #547792); background: transparent; transition: 0.2s; }
.btn-outline-urban:hover { background-color: var(--color-c-hover, #547792); color: white; }
.bg-urban { background-color: var(--color-c-hover, #547792) !important; }
.font-sans-vn { font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', sans-serif !important; }
.font-decor { font-family: 'Times New Roman', Times, serif; font-style: italic; }

.step-number { background-color: var(--color-c-dark); color: white; }

/* input chung */
.custom-input {
  background-color: #ffffff;
  border: 1.5px solid var(--color-c-light); 
  color: var(--color-c-dark);
  padding: 0.65rem 1rem; 
  font-size: 0.95rem;
  border-radius: 0.5rem;
  transition: all 0.2s ease-in-out;
  box-shadow: none !important; 
}
html.dark .custom-input { background-color: #1a2533; border-color: #373b3e; color: white; }
.custom-input:focus, .custom-input:focus-within {
  border-color: var(--color-c-hover) !important;
  background-color: var(--color-c-effect);
  outline: none;
  box-shadow: 0 0 0 3px rgba(148, 180, 193, 0.2) !important; 
}
html.dark .custom-input:focus { background-color: #212529; box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.1) !important; }

/* select dropdown */
select.custom-input {
  cursor: pointer; appearance: none;
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23547792' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
  background-repeat: no-repeat; background-position: right 1rem center; background-size: 16px 12px; padding-right: 2.5rem;
}
html.dark select.custom-input { background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23ffffff' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e"); }
select.custom-input.placeholder-active { color: #6c757d; font-weight: 400 !important; }
html.dark select.custom-input.placeholder-active { color: #adb5bd; }
select.custom-input option:disabled { color: #aaa; font-style: italic; }

/* xóa giỏ hàng */
.btn-delete-circle {
  position: absolute;
  top: 1rem; right: 0.5rem;
  width: 28px; height: 28px;
  border: 1.5px solid #dc3545; color: #dc3545;
  background: transparent; border-radius: 50%; padding: 0;
  z-index: 2; cursor: pointer;
}
.btn-delete-circle i { font-size: 0.8rem; font-weight: bold; }
.btn-delete-circle:hover { background: #dc3545; color: #fff; transform: scale(1.1); box-shadow: 0 4px 8px rgba(220, 53, 69, 0.2); }
html.dark .btn-delete-circle { border-color: #ef4444; color: #ef4444; }
html.dark .btn-delete-circle:hover { background: #ef4444; color: #fff; }

/* ẩn mũi tên input number */
.no-spinners::-webkit-outer-spin-button,
.no-spinners::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
.no-spinners {
  -moz-appearance: textfield;
}

/* hiệu ứng loader */
.bouncing-loader { display: flex; align-items: center; justify-content: center; gap: 6px; z-index: 10; }
.bouncing-loader span { display: block; width: 8px; height: 8px; background-color: var(--color-c-hover, #547792); border-radius: 50%; animation: bounce 1.4s infinite ease-in-out both; }
html.dark .bouncing-loader span { background-color: #94B4C1; }
.bouncing-loader span:nth-child(1) { animation-delay: -0.32s; }
.bouncing-loader span:nth-child(2) { animation-delay: -0.16s; }
@keyframes bounce { 0%, 80%, 100% { transform: scale(0); } 40% { transform: scale(1.3); } }

/* payment cards */
.payment-method-card { border: 1.5px solid var(--color-c-effect); background-color: #ffffff; transition: all 0.2s ease-in-out; }
html.dark .payment-method-card { border-color: #373b3e; background-color: transparent; }
.payment-method-card:hover { border-color: var(--color-c-light); }
.active-card { border-color: var(--color-c-hover) !important; background-color: var(--color-c-effect) !important; }
html.dark .active-card { border-color: var(--color-c-light) !important; background-color: rgba(148, 180, 193, 0.1) !important; }
.icon-box { width: 45px; height: 45px; background-color: #ffffff; color: var(--color-c-hover); border: 1.5px solid var(--color-c-effect); transition: all 0.2s ease; }
html.dark .icon-box { background-color: #212529; border-color: #373b3e !important; }
.active-card .icon-box { color: var(--color-c-dark); border-color: var(--color-c-hover) !important; }

/* utils */
.z-index-1 { z-index: 1; }
.z-index-2 { z-index: 2; }
.z-index-dropdown { z-index: 1050; }
.custom-radio { cursor: pointer; border-color: var(--color-c-light); }
.custom-radio:checked { background-color: var(--color-c-hover); border-color: var(--color-c-hover); }
.custom-input-group { border: 1.5px solid var(--color-c-light); transition: all 0.2s ease-in-out; }
.custom-input-group:focus-within { border-color: var(--color-c-hover); box-shadow: 0 0 0 3px rgba(148, 180, 193, 0.2); }
html.dark .custom-input-group { border-color: #373b3e; }
html.dark .custom-input-group:focus-within { border-color: var(--color-c-light); box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.1); }

.summary-box, .vat-box { background-color: var(--color-c-effect); border: 1.5px solid transparent; }
html.dark .summary-box, html.dark .vat-box { background-color: #1a2533; border-color: #373b3e; }

.tracking-widest { letter-spacing: 2px; } .tracking-wide { letter-spacing: 1px; }
.cursor-pointer { cursor: pointer; } .sticky-top { transition: all 0.3s ease; }
.hover-transform { transition: transform 0.2s ease, box-shadow 0.2s ease; }
.hover-transform:hover { transform: translateY(-3px); box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15) !important; }
.line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis; line-height: 1.4; }
.hover-urban:hover { color: var(--color-c-hover, #547792) !important; }
.hover-text-dark:hover { color: var(--color-c-dark, #213448) !important; }

.custom-scrollbar-y::-webkit-scrollbar { width: 5px; }
.custom-scrollbar-y::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar-y::-webkit-scrollbar-thumb { background: var(--color-c-light, #94B4C1); border-radius: 10px; }

.slide-fade-enter-active { transition: all 0.3s ease-out; }
.slide-fade-leave-active { transition: all 0.2s cubic-bezier(1, 0.5, 0.8, 1); }
.slide-fade-enter-from, .slide-fade-leave-to { transform: translateY(-10px); opacity: 0; }
.animation-fade-in { animation: fadeIn 0.4s ease-in-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }

.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

/* skeleton css chuẩn xác */
.shimmer {
  background: #e2e8f0;
  background-image: linear-gradient(to right, #e2e8f0 0%, #f1f5f9 20%, #e2e8f0 40%, #e2e8f0 100%);
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