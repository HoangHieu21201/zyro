<template>
  <div ref="mapContainer" class="w-100 h-100 position-relative rounded-3 overflow-hidden">
    
    <!-- KHUNG CHỨA BẢN ĐỒ -->
    <div :id="mapId" class="w-100 h-100 bg-light" style="z-index: 1;"></div>
    
    <!-- ĐÃ THÊM: NÚT RESET GÓC NHÌN (RECENTER) -->
    <button v-if="!isLoading && isMapReady" 
            @click="recenterMap" 
            class="btn btn-white text-urban shadow-lg position-absolute d-flex align-items-center justify-content-center rounded-circle transition-transform hover-scale bg-white" 
            style="bottom: 20px; left: 20px; width: 45px; height: 45px; z-index: 1000; border: 2px solid var(--color-c-hover);"
            title="Xem toàn bộ tuyến đường">
       <i class="bi bi-crosshair fs-5"></i>
    </button>

    <!-- HIỆU ỨNG LOADING KẾT NỐI -->
    <div v-if="isLoading" class="position-absolute top-0 start-0 w-100 h-100 d-flex flex-column align-items-center justify-content-center bg-white bg-opacity-75 z-index-loader">
        <div class="spinner-border text-urban mb-2" style="width: 2.5rem; height: 2.5rem;"></div>
        <div class="fw-bold text-urban small font-sans-vn">Đang thiết lập vệ tinh...</div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, watch, nextTick } from 'vue';
import axios from 'axios';

const props = defineProps({
  mapData: {
    type: Object,
    required: true,
  },
  status: {
    type: String,
    default: 'shipping'
  }
});

const mapId = 'map-' + Math.random().toString(36).substr(2, 9);
const isLoading = ref(true);
const isMapReady = ref(false);

let leafletMap = null;
let routingLine = null;
let truckMarker = null;
let animationFrameId = null;

// Tải thư viện Leaflet (Lazy load)
const loadLeafletScript = () => {
  return new Promise((resolve) => {
    if (window.L) return resolve();
    const link = document.createElement('link');
    link.rel = 'stylesheet'; link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
    document.head.appendChild(link);

    const script = document.createElement('script');
    script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
    script.onload = () => resolve();
    document.head.appendChild(script);
  });
};

// ==========================================
// HÀM RESET GÓC NHÌN (SỬ DỤNG CHO NÚT BẤM VÀ AUTO LOAD)
// ==========================================
const recenterMap = () => {
  if (leafletMap && routingLine) {
    // Ép map cập nhật lại size đề phòng modal mở chậm
    leafletMap.invalidateSize();
    
    // ĐÃ FIX: Giới hạn maxZoom: 12 để không bị zoom sát mái nhà nếu giao gần
    leafletMap.fitBounds(routingLine.getBounds(), { 
        padding: [60, 60], 
        maxZoom: 12, 
        animate: true, 
        duration: 1 
    });
  }
};

const renderMap = async () => {
  if (!props.mapData || !props.mapData.origin || !props.mapData.destination) return;
  isLoading.value = true;
  isMapReady.value = false;

  await loadLeafletScript();
  await nextTick();

  const p1 = props.mapData.origin.coords;
  const p2 = props.mapData.destination.coords;

  if (leafletMap) {
    leafletMap.remove();
    leafletMap = null;
  }
  if (animationFrameId) cancelAnimationFrame(animationFrameId);

  // ĐÃ FIX: KHÓA BẢN ĐỒ TRONG PHẠM VI VIỆT NAM & ĐÔNG NAM Á
  const vnBounds = window.L.latLngBounds(
    window.L.latLng(6.0, 100.0), // Góc Tây Nam
    window.L.latLng(25.0, 115.0) // Góc Đông Bắc
  );

  leafletMap = window.L.map(mapId, { 
      zoomControl: false,
      minZoom: 5,                  // Cấm zoom out ra khỏi Trái Đất
      maxBounds: vnBounds,         // Khóa màn hình không cho lăn sang nước khác
      maxBoundsViscosity: 0.8      // Độ đàn hồi khi cố tình kéo ra ngoài biên
  }).setView(p1, 6);
  
  // Chuyển nút zoom mặc định sang góc dưới phải
  window.L.control.zoom({ position: 'bottomright' }).addTo(leafletMap);

  // Khởi tạo Layer Mapbox Navigation
  const mapboxToken = import.meta.env.VITE_MAPBOX_TOKEN;
  if (mapboxToken) {
    window.L.tileLayer(`https://api.mapbox.com/styles/v1/mapbox/navigation-day-v1/tiles/{z}/{x}/{y}?access_token=${mapboxToken}`, {
      attribution: '© <a href="https://www.mapbox.com/about/maps/">Mapbox</a>',
      tileSize: 512,
      zoomOffset: -1,
    }).addTo(leafletMap);
  } else {
    window.L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png').addTo(leafletMap);
  }

  // Label Điểm Đi / Điểm Đến
  const iconOrigin = window.L.divIcon({ 
    html: '<div class="bg-dark text-white rounded-pill d-flex align-items-center justify-content-center shadow-lg px-3" style="height:32px; font-weight:bold; border:2px solid white; white-space: nowrap; font-family: sans-serif; font-size: 13px;"><i class="bi bi-shop me-2"></i> Kho Gửi</div>', 
    className: '', iconSize: [100, 32], iconAnchor: [50, 32] 
  });
  
  const iconDestination = window.L.divIcon({ 
    html: '<div class="bg-success text-white rounded-pill d-flex align-items-center justify-content-center shadow-lg px-3" style="height:32px; font-weight:bold; border:2px solid white; white-space: nowrap; font-family: sans-serif; font-size: 13px;"><i class="bi bi-house-door-fill me-2"></i> Nơi Nhận</div>', 
    className: '', iconSize: [100, 32], iconAnchor: [50, 32] 
  });
  
  window.L.marker(p1, {icon: iconOrigin}).bindPopup(`<b>Từ:</b> ${props.mapData.origin.name}`).addTo(leafletMap);
  window.L.marker(p2, {icon: iconDestination}).bindPopup(`<b>Đến:</b> ${props.mapData.destination.name}`).addTo(leafletMap);

  try {
     // THUẬT TOÁN NEO TỌA ĐỘ (Đảm bảo bám QL1A)
     const lat1 = p1[0];
     const lat2 = p2[0];
     const maxLat = Math.max(lat1, lat2);
     const minLat = Math.min(lat1, lat2);
     const isLongDistance = (maxLat - minLat) > 2;

     const ql1a_waypoints = [
       { lat: 19.8067, lng: 105.7706, name: 'Thanh Hóa' },
       { lat: 18.6733, lng: 105.6813, name: 'Vinh' },
       { lat: 17.4682, lng: 106.6186, name: 'Đồng Hới' },
       { lat: 16.4637, lng: 107.5909, name: 'Huế' },
       { lat: 16.0471, lng: 108.2068, name: 'Đà Nẵng' },
       { lat: 15.1205, lng: 108.7923, name: 'Quảng Ngãi' },
       { lat: 13.7589, lng: 109.0806, name: 'Quy Nhơn' },
       { lat: 12.2388, lng: 109.1967, name: 'Nha Trang' },
       { lat: 10.9333, lng: 107.1833, name: 'Đồng Nai' }
     ];

     let intermediate = [];
     if (isLongDistance) {
       intermediate = ql1a_waypoints.filter(wp => wp.lat < maxLat - 0.5 && wp.lat > minLat + 0.5);
       if (lat1 > lat2) { intermediate.sort((a, b) => b.lat - a.lat); } 
       else { intermediate.sort((a, b) => a.lat - b.lat); }
     }

     let waypoints = `${p1[1]},${p1[0]}`;
     intermediate.forEach(wp => { waypoints += `;${wp.lng},${wp.lat}`; });
     waypoints += `;${p2[1]},${p2[0]}`;

     let apiUrl = mapboxToken 
        ? `https://api.mapbox.com/directions/v5/mapbox/driving/${waypoints}?geometries=geojson&overview=full&access_token=${mapboxToken}`
        : `https://router.project-osrm.org/route/v1/driving/${waypoints}?overview=full&geometries=geojson`;

     const res = await axios.get(apiUrl);
     const routeCoords = res.data.routes[0].geometry.coordinates.map(c => [c[1], c[0]]);

     routingLine = window.L.polyline(routeCoords, { color: '#009981', weight: 5, opacity: 0.8 }).addTo(leafletMap);
     
     // ĐÃ FIX: Delay lệnh fitBounds 350ms để đợi Modal/DOM mở ra hoàn toàn, tránh lỗi tính nhầm khung hình (gây zoom out quá đà)
     setTimeout(() => {
        recenterMap();
        isMapReady.value = true;
     }, 350);

     // Marker chiếc xe tải
     const truckHtml = `<div class="bg-danger text-white rounded shadow-lg d-flex align-items-center justify-content-center border border-2 border-white" style="width:34px; height:34px;"><i class="bi bi-truck fs-5"></i></div>`;
     const truckDivIcon = window.L.divIcon({ html: truckHtml, className: '', iconSize: [34, 34], iconAnchor: [17, 17] });
     
     let totalPoints = routeCoords.length;
     let currentIndex = 0; 
     let speed = Math.ceil(totalPoints / 250) || 1;

     if (props.status === 'completed' || props.status === 'returned') {
         currentIndex = totalPoints - 1; 
     } else {
         currentIndex = Math.floor(totalPoints * 0.1); 
     }

     truckMarker = window.L.marker(routeCoords[currentIndex], {icon: truckDivIcon}).addTo(leafletMap);
     isLoading.value = false;

     // Animation xe chạy
     if (props.status === 'shipping' && routeCoords.length > 2) {
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
     routingLine = window.L.polyline([p1, p2], { color: '#009981', weight: 4, dashArray: '10, 10' }).addTo(leafletMap);
     setTimeout(() => { recenterMap(); isMapReady.value = true; }, 350);
     isLoading.value = false;
  }
};

watch(() => props.mapData, (newData) => {
  if (newData) renderMap();
}, { deep: true });

onMounted(() => {
  if (props.mapData) renderMap();
});

onBeforeUnmount(() => {
  if (animationFrameId) cancelAnimationFrame(animationFrameId);
  if (leafletMap) {
    leafletMap.remove();
    leafletMap = null;
  }
});
</script>

<style scoped>
.z-index-loader { z-index: 1050; }
.font-sans-vn { font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', sans-serif !important; }

/* CSS cho nút Recenter */
.text-urban { color: var(--color-c-hover, #547792) !important; }
.hover-scale { transition: transform 0.2s ease, background-color 0.2s ease, color 0.2s ease; }
.hover-scale:hover { 
  transform: scale(1.1); 
  background-color: var(--color-c-hover, #547792) !important; 
  color: white !important;
}
</style>