<template>
  <div class="page-container">
    <!-- Tiêu đề trang -->
    <div class="page-header mb-4">
      <h1 class="page-title">Quản Lý Đặt Phòng</h1>
      <p class="page-subtitle">Tổng quan và quản lý các lượt đặt phòng của khách sạn.</p>
    </div>

    <!-- Bộ lọc và tìm kiếm -->
    <div class="card filter-card mb-4">
      <div class="card-body">
        <div class="row g-3">
          <div class="col-lg-4 col-md-12">
            <label for="search-input" class="form-label">Tìm kiếm</label>
            <input 
              id="search-input"
              v-model="tuKhoaTimKiem" 
              type="text" 
              class="form-control" 
              placeholder="Mã đặt phòng, tên KH, SĐT..."
              @input="timKiemDatPhong"
            >
          </div>
          <div class="col-lg-2 col-md-4">
            <label for="status-filter" class="form-label">Trạng thái</label>
            <select id="status-filter" v-model="locTrangThai" class="form-select" @change="locDanhSach">
              <option value="">Tất cả</option>
              <option value="pending_confirmation">Chờ xác nhận</option>
              <option value="confirmed">Đã xác nhận</option>
              <option value="pending_cancel">Chờ xác nhận hủy</option>
              <option value="cancelled">Đã hủy</option>
            </select>
          </div>
          <div class="col-lg-3 col-md-4">
            <label for="from-date" class="form-label">Từ ngày</label>
            <input id="from-date" v-model="tuNgay" type="date" class="form-control" @change="locDanhSach">
          </div>
          <div class="col-lg-3 col-md-4">
            <label for="to-date" class="form-label">Đến ngày</label>
            <input id="to-date" v-model="denNgay" type="date" class="form-control" @change="locDanhSach">
          </div>
        </div>
      </div>
    </div>

    <!-- Hiển thị thông báo -->
    <div v-if="thongBaoLoi" class="alert alert-danger" role="alert">
      {{ thongBaoLoi }}
    </div>

    <!-- Bảng danh sách đặt phòng -->
    <div class="table-container">
      <table class="table booking-table" v-if="danhSachHienThi.length > 0">
        <thead>
          <tr>
            <th scope="col" @click="sapXep('booking_id')">
              Mã ĐP 
              <i v-if="sapXepCot === 'booking_id'" :class="sapXepGiam ? 'fas fa-sort-down' : 'fas fa-sort-up'"></i>
            </th>
            <th scope="col">Khách Hàng</th>
            <th scope="col" @click="sapXep('check_in_date')">
              Nhận Phòng
              <i v-if="sapXepCot === 'check_in_date'" :class="sapXepGiam ? 'fas fa-sort-down' : 'fas fa-sort-up'"></i>
            </th>
            <th scope="col" @click="sapXep('check_out_date')">
              Trả Phòng
              <i v-if="sapXepCot === 'check_out_date'" :class="sapXepGiam ? 'fas fa-sort-down' : 'fas fa-sort-up'"></i>
            </th>
            <th scope="col">Tổng Giá</th>
            <th scope="col">Trạng Thái</th>
            <th scope="col" class="text-center">Hành Động</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="datPhong in danhSachHienThi" :key="datPhong.booking_id">
            <td><span class="booking-id">#{{ datPhong.booking_id }}</span></td>
            <td>
              <div class="customer-name">{{ datPhong.customer?.customer_name || 'Không xác định' }}</div>
              <div class="customer-phone">0{{ datPhong.customer?.customer_phone || 'Không có SĐT' }}</div>
            </td>
            <td>{{ dinhDangNgay(datPhong.check_in_date) }}</td>
            <td>{{ dinhDangNgay(datPhong.check_out_date) }}</td>
            <td><span class="price-tag">{{ dinhDangTien(datPhong.total_price) }}</span></td>
            <td>
              <span class="badge" :class="layLopTrangThai(datPhong.status)">
                {{ dinhDangTrangThai(datPhong.status) }}
              </span>
            </td>
            <td class="text-center">
              <button @click="moModalChiTiet(datPhong)" class="btn btn-outline-primary btn-sm">
                Chi tiết
              </button>
            </td>
          </tr>
        </tbody>
      </table>
      <div v-else class="alert alert-light text-center">
        Không tìm thấy dữ liệu đặt phòng phù hợp.
      </div>
    </div>

    <!-- Phân trang -->
    <nav aria-label="Page navigation" class="mt-4" v-if="tongSoTrang > 1">
        <ul class="pagination justify-content-center">
            <li class="page-item" :class="{ disabled: trangHienTai === 1 }">
              <a class="page-link" href="#" @click.prevent="chuyenTrang(1)">««</a>
            </li>
            <li class="page-item" :class="{ disabled: trangHienTai === 1 }">
              <a class="page-link" href="#" @click.prevent="chuyenTrang(trangHienTai - 1)">«</a>
            </li>
            <li class="page-item" v-for="trang in danhSachTrang" :key="trang" :class="{ active: trang === trangHienTai }">
              <a class="page-link" href="#" @click.prevent="chuyenTrang(trang)">{{ trang }}</a>
            </li>
            <li class="page-item" :class="{ disabled: trangHienTai === tongSoTrang }">
              <a class="page-link" href="#" @click.prevent="chuyenTrang(trangHienTai + 1)">»</a>
            </li>
            <li class="page-item" :class="{ disabled: trangHienTai === tongSoTrang }">
              <a class="page-link" href="#" @click.prevent="chuyenTrang(tongSoTrang)">»»</a>
            </li>
        </ul>
    </nav>

    <!-- Modal chi tiết đặt phòng -->
    <div v-if="hienModal" class="modal-backdrop fade show"></div>
    <div v-if="hienModal" class="modal fade show d-block" tabindex="-1" role="dialog">
      <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content modal-custom">
          <div class="modal-header modal-header-custom">
            <h5 class="modal-title">
              Chi Tiết Đặt Phòng #{{ datPhongDuocChon.booking_id }}
            </h5>
            <button type="button" @click="dongModal" class="btn-close" aria-label="Close"></button>
          </div>
          <div class="modal-body p-4">
            <div v-if="dangTai" class="text-center py-5">
              <div class="spinner-border text-primary" role="status"></div>
              <p class="mt-3 text-muted">Đang tải dữ liệu chi tiết...</p>
            </div>
            
            <div v-else>
              <!-- Thông tin chung -->
              <div class="row g-4 mb-4">
                <div class="col-lg-6">
                    <h6 class="info-title">Thông tin khách hàng</h6>
                    <ul class="info-list">
                      <li><span>Họ tên:</span><strong>{{ datPhongDuocChon.customer?.customer_name || 'N/A' }}</strong></li>
                      <li><span>Điện thoại:</span><strong>0{{ datPhongDuocChon.customer?.customer_phone || 'N/A' }}</strong></li>
                      <li><span>Email:</span><strong>{{ datPhongDuocChon.customer?.customer_email || 'N/A' }}</strong></li>
                    </ul>
                </div>
                <div class="col-lg-6">
                    <h6 class="info-title">Thông tin đặt phòng</h6>
                    <ul class="info-list">
                      <li><span>Nhận phòng:</span><strong>{{ dinhDangNgay(datPhongDuocChon.check_in_date) }}</strong></li>
                      <li><span>Trả phòng:</span><strong>{{ dinhDangNgay(datPhongDuocChon.check_out_date) }}</strong></li>
                      <li><span>Loại đặt:</span><strong>{{ dinhDangLoaiDatPhong(datPhongDuocChon.booking_type) }}</strong></li>
                      <li><span>Ghi chú:</span><strong>{{ datPhongDuocChon.note || 'Không có' }}</strong></li>
                    </ul>
                </div>
              </div>

              <!-- Thông tin hủy (nếu có) -->
              <div v-if="thongTinHuy" class="row g-4 mb-4">
                <div class="col-12">
                  <h6 class="info-title">Thông tin hủy đặt phòng</h6>
                  <ul class="info-list">
                    <li><span>Lý do hủy:</span><strong>{{ thongTinHuy.reason || 'Không có' }}</strong></li>
                    <li><span>Số tiền hoàn lại:</span><strong>{{ dinhDangTien(thongTinHuy.refund_amount) }}</strong></li>
                    <li><span>Ngân hàng:</span><strong>{{ thongTinHuy.refund_bank || 'N/A' }}</strong></li>
                    <li><span>Số tài khoản:</span><strong>{{ thongTinHuy.refund_account_number || 'N/A' }}</strong></li>
                    <li><span>Tên tài khoản:</span><strong>{{ thongTinHuy.refund_account_name || 'N/A' }}</strong></li>
                    <li><span>Ngày yêu cầu hủy:</span><strong>{{ dinhDangNgay(thongTinHuy.cancellation_date) }}</strong></li>
                    <li><span>Trạng thái:</span><strong>{{ dinhDangTrangThaiHuy(thongTinHuy.status) }}</strong></li>
                  </ul>
                </div>
              </div>

              <!-- Status Bar -->
              <div class="status-bar">
                <div>
                  Trạng thái đặt phòng:
                  <span class="badge ms-2" :class="layLopTrangThai(datPhongDuocChon.status)">
                    {{ dinhDangTrangThai(datPhongDuocChon.status) }}
                  </span>
                </div>
                <div>
                  Trạng thái thanh toán:
                  <span class="badge ms-2" :class="layLopTrangThaiThanhToan(datPhongDuocChon.payment_status)">
                    {{ datPhongDuocChon.payment_status_display || dinhDangTrangThaiThanhToan(datPhongDuocChon.payment_status) }}
                  </span>
                </div>
                <div class="total-price">
                  Tổng cộng: <strong>{{ dinhDangTien(datPhongDuocChon.total_price) }}</strong>
                </div>
              </div>

              <!-- Bảng chi tiết phòng -->
              <h6 class="info-title mt-4">Chi Tiết Các Phòng Đã Đặt</h6>
              <div class="table-responsive">
                <table class="table room-assignment-table">
                  <thead>
                    <tr>
                      <th>Loại Phòng Đặt</th>
                      <th>Phòng Được Xếp</th>
                      <th class="text-end">Giá</th>
                      <th style="width: 35%;">Xếp Phòng</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-if="chiTietDatPhongHopLe.length === 0">
                        <td colspan="4" class="text-center text-muted py-3">Không có chi tiết phòng để hiển thị.</td>
                    </tr>
                    <tr v-for="chiTiet in chiTietDatPhongHopLe" :key="chiTiet.booking_detail_id">
                      <td>{{ chiTiet.type_name || 'Không xác định' }}</td>
                      <td>
                        <span v-if="chiTiet.room?.room_name && chiTiet.room.room_name !== 'Chưa xếp'" class="text-success fw-bold">{{ chiTiet.room.room_name }}</span>
                        <span v-else class="text-muted">Chưa xếp</span>
                      </td>
                      <td class="text-end">{{ dinhDangTien(chiTiet.total_price) }}</td>
                      <td>
                        <div v-if="datPhongDuocChon.status === 'pending_confirmation' && !chiTiet.room_id">
                          <select
                            v-if="phongTrong[chiTiet.booking_detail_id]?.length > 0"
                            v-model="chiTiet.room_id"
                            @change="xepPhong(chiTiet)"
                            class="form-select form-select-sm"
                          >
                            <option value="">-- Chọn phòng --</option>
                            <option v-for="phong in phongTrong[chiTiet.booking_detail_id]" :key="phong.room_id" :value="phong.room_id">
                              {{ phong.room_name }}
                            </option>
                          </select>
                          <div v-else class="text-warning small">Hết phòng trống loại này</div>
                        </div>
                        <div v-else-if="chiTiet.room_id" class="text-muted small">
                          Đã xếp phòng
                        </div>
                        <div v-else-if="datPhongDuocChon.status !== 'pending_confirmation'" class="text-muted small">
                          Quá hạn xếp phòng
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
          <div v-if="!dangTai && datPhongDuocChon.booking_id" class="modal-footer modal-footer-custom">
            <div class="me-auto" v-if="datPhongDuocChon.status === 'pending_confirmation' && coPhongChuaXep">
                <small class="text-danger">Vui lòng xếp tất cả các phòng để xác nhận.</small>
            </div>
            <button @click="dongModal" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
            <button
              v-if="datPhongDuocChon.status === 'pending_confirmation'"
              @click="xacNhanDatPhong"
              class="btn btn-primary"
              :disabled="coPhongChuaXep"
            >
              Xác Nhận Đặt Phòng
            </button>
            <button
              v-if="datPhongDuocChon.status === 'pending_cancel' && thongTinHuy?.status === 'requested'"
              @click="xacNhanHuyDatPhong"
              class="btn btn-danger"
            >
              Xác Nhận Hủy
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import Loading from '../loading.vue';

const danhSachDatPhong = ref([]);
const hienModal = ref(false);
const datPhongDuocChon = ref({});
const chiTietDatPhong = ref([]);
const phongTrong = ref({});
const thongBaoLoi = ref('');
const dangTai = ref(false);
const thongTinHuy = ref(null); // Thêm biến để lưu thông tin hủy

const tuKhoaTimKiem = ref('');
const locTrangThai = ref('');
const tuNgay = ref('');
const denNgay = ref('');
const trangHienTai = ref(1);
const soBanGhiTrenTrang = ref(10);
const sapXepCot = ref('booking_id');
const sapXepGiam = ref(true);

const danhSachLoc = computed(() => {
  let ketQua = [...danhSachDatPhong.value];
  if (tuKhoaTimKiem.value) {
    const tuKhoa = tuKhoaTimKiem.value.toLowerCase();
    ketQua = ketQua.filter(item => 
      (item.booking_id && item.booking_id.toString().includes(tuKhoa)) ||
      (item.customer?.customer_name && item.customer.customer_name.toLowerCase().includes(tuKhoa)) ||
      (item.customer?.customer_phone && item.customer.customer_phone.includes(tuKhoa))
    );
  }
  if (locTrangThai.value) {
    ketQua = ketQua.filter(item => item.status === locTrangThai.value);
  }
  if (tuNgay.value) {
    const ngayBatDau = new Date(tuNgay.value);
    ngayBatDau.setHours(0, 0, 0, 0);
    ketQua = ketQua.filter(item => new Date(item.check_in_date) >= ngayBatDau);
  }
  if (denNgay.value) {
    const ngayKetThuc = new Date(denNgay.value);
    ngayKetThuc.setHours(23, 59, 59, 999);
    ketQua = ketQua.filter(item => new Date(item.check_in_date) <= ngayKetThuc);
  }
  ketQua.sort((a, b) => {
    const giaTriA = a[sapXepCot.value];
    const giaTriB = b[sapXepCot.value];
    if (sapXepCot.value.includes('date')) {
      return sapXepGiam.value ? new Date(giaTriB) - new Date(giaTriA) : new Date(giaTriA) - new Date(giaTriB);
    }
    return sapXepGiam.value ? Number(giaTriB) - Number(giaTriA) : Number(giaTriA) - Number(giaTriB);
  });
  return ketQua;
});

const tongSoTrang = computed(() => Math.ceil(danhSachLoc.value.length / soBanGhiTrenTrang.value));

const danhSachTrang = computed(() => {
  const maxPages = 5, halfPages = Math.floor(maxPages / 2);
  let start = Math.max(1, trangHienTai.value - halfPages);
  let end = Math.min(tongSoTrang.value, start + maxPages - 1);
  if (end - start + 1 < maxPages) {
    start = Math.max(1, end - maxPages + 1);
  }
  return Array.from({ length: end - start + 1 }, (_, i) => start + i);
});

const danhSachHienThi = computed(() => {
  const batDau = (trangHienTai.value - 1) * soBanGhiTrenTrang.value;
  return danhSachLoc.value.slice(batDau, batDau + soBanGhiTrenTrang.value);
});

const chiTietDatPhongHopLe = computed(() => chiTietDatPhong.value.filter(c => c && c.booking_detail_id));

const coPhongChuaXep = computed(() => {
  if (!chiTietDatPhong.value || chiTietDatPhong.value.length === 0) return true;
  return chiTietDatPhong.value.some(c => !c.room_id);
});

const layDanhSachDatPhong = async () => {
  try {
    const res = await axios.get('/api/bookings?status[]=pending_confirmation&status[]=confirmed&status[]=cancelled&status[]=pending_cancel', { headers: { 'Accept': 'application/json' } });
    danhSachDatPhong.value = Array.isArray(res.data) ? res.data : [];
    thongBaoLoi.value = '';
  } catch (err) {
    console.error('Lỗi khi lấy danh sách đặt phòng:', err);
    thongBaoLoi.value = 'Không thể kết nối đến server để lấy dữ liệu đặt phòng.';
    danhSachDatPhong.value = [];
  }
};

const moModalChiTiet = async (datPhong) => {
  datPhongDuocChon.value = datPhong;
  dangTai.value = true;
  hienModal.value = true;
  thongBaoLoi.value = '';
  phongTrong.value = {};
  thongTinHuy.value = null;

  try {
    const resDetails = await axios.get(`/api/booking-details/${datPhong.booking_id}`);
    chiTietDatPhong.value = Array.isArray(resDetails.data) ? resDetails.data.map(c => ({
      ...c,
      room: c.room || { room_id: null, room_name: 'Chưa xếp' },
      type_name: c.roomType?.type_name || 'N/A'
    })) : [];

    if (datPhong.status === 'pending_cancel' || datPhong.status === 'cancelled') {
      try {
        const cancelRes = await axios.get(`/api/booking-cancel/${datPhong.booking_id}`);
        thongTinHuy.value = cancelRes.data.data || null;
      } catch (err) {
        console.error('Lỗi chi tiết khi lấy thông tin hủy:', err.response ? err.response.data : err.message);
        thongTinHuy.value = null;
        thongBaoLoi.value = 'Không thể tải thông tin hủy do lỗi server.';
        hienModal.value = false;
        return;
      }
    }

    if (datPhong.status === 'pending_confirmation') {
      await Promise.all(
        chiTietDatPhong.value.map(async (chiTiet) => {
          if (!chiTiet.room_id && chiTiet.room_type) {
            try {
              const resPhong = await axios.post('/api/available-rooms', {
                room_type: Number(chiTiet.room_type),
                check_in_date: datPhong.check_in_date,
                check_out_date: datPhong.check_out_date
              });
              phongTrong.value[chiTiet.booking_detail_id] = Array.isArray(resPhong.data) ? resPhong.data : [];
            } catch (err) {
              console.error(`Lỗi lấy phòng trống cho loại ${chiTiet.room_type}:`, err);
              phongTrong.value[chiTiet.booking_detail_id] = [];
            }
          }
        })
      );
    }
  } catch (err) {
    console.error('Lỗi khi lấy chi tiết đặt phòng:', err);
    chiTietDatPhong.value = [];
    thongBaoLoi.value = `Không thể tải chi tiết đặt phòng. Lỗi: ${err.response?.data?.error || err.message}`;
    hienModal.value = false;
  } finally {
    dangTai.value = false;
  }
};

const xepPhong = async (chiTiet) => {
  if (!chiTiet.room_id) return;
  try {
    await axios.post(`/api/assign-room/${chiTiet.booking_detail_id}`, { 
      room_id: chiTiet.room_id, 
      check_in_date: datPhongDuocChon.value.check_in_date, 
      check_out_date: datPhongDuocChon.value.check_out_date 
    }, { headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' } });
    const selectedRoom = phongTrong.value[chiTiet.booking_detail_id].find(p => p.room_id == chiTiet.room_id);
    if (selectedRoom) chiTiet.room = { ...selectedRoom };
  } catch (err) {
    console.error('Lỗi khi xếp phòng:', err);
    alert(`Lỗi khi xếp phòng: ${err.response?.data?.error || err.message}`);
    chiTiet.room_id = "";
  }
};

const xacNhanDatPhong = async () => {
  try {
    await axios.patch(`/api/bookings/${datPhongDuocChon.value.booking_id}`, { 
      status: 'confirmed' 
    }, { headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' } });
    await layDanhSachDatPhong();
    alert('Xác nhận đặt phòng thành công!');
    dongModal();
  } catch (err) {
    console.error('Lỗi khi xác nhận đặt phòng:', err);
    alert('Lỗi khi xác nhận đặt phòng!');
  }
};

const xacNhanHuyDatPhong = async () => {
  try {
    const cancelId = thongTinHuy.value?.cancel_id; // Lấy cancel_id từ thongTinHuy
    if (!cancelId) {
      thongBaoLoi.value = 'Không tìm thấy thông tin hủy để xác nhận.';
      return;
    }

    const response = await axios.patch(`/api/booking-cancel/${cancelId}`, {
      status: 'processed', // Có thể thay bằng 'failed' tùy logic
      refund_bank: 'Ngân hàng ABC',
      refund_account_number: '123456789',
      refund_account_name: 'Nguyen Van A'
    });

    thongBaoLoi.value = 'Xác nhận hủy thành công!';
    hienModal.value = false;
  } catch (err) {
    console.error('Lỗi khi xác nhận hủy:', {
      message: err.message,
      status: err.response?.status,
      data: err.response?.data
    });
    thongBaoLoi.value = `Lỗi khi xác nhận hủy: ${err.response?.data?.message || err.message}`;
  }
};

const dongModal = () => {
  hienModal.value = false;
  datPhongDuocChon.value = {};
  chiTietDatPhong.value = [];
  thongTinHuy.value = null;
};

const chuyenTrang = (trang) => { 
  if (trang >= 1 && trang <= tongSoTrang.value) trangHienTai.value = trang; 
};
const timKiemDatPhong = () => { trangHienTai.value = 1; };
const locDanhSach = () => { trangHienTai.value = 1; };
const sapXep = (cot) => {
  sapXepGiam.value = sapXepCot.value === cot ? !sapXepGiam.value : true;
  sapXepCot.value = cot;
  trangHienTai.value = 1;
};

const dinhDangNgay = (ngay) => ngay ? new Date(ngay).toLocaleDateString('vi-VN') : 'N/A';
const dinhDangTien = (gia) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(gia || 0);
const dinhDangLoaiDatPhong = (loai) => ({ online: 'Trực tuyến', offline: 'Tại chỗ' }[loai] || loai || 'N/A');
const dinhDangTrangThai = (trangThai) => ({
  pending_confirmation: 'Chờ xác nhận',
  confirmed: 'Đã xác nhận',
  pending_cancel: 'Chờ xác nhận hủy',
  cancelled: 'Đã hủy'
}[trangThai] || 'Không rõ');
const dinhDangTrangThaiThanhToan = (trangThai) => ({
  PENDING: 'Thanh toán tại quầy',
  PAID: 'Đã thanh toán',
  REFUNDED: 'Đã hoàn tiền',
  ERROR: 'Lỗi thanh toán'
}[trangThai] || 'Không xác định');
const dinhDangTrangThaiHuy = (trangThai) => ({
  requested: 'Yêu cầu hủy',
  processed: 'Đã hủy',
  failed: 'Hủy thất bại'
}[trangThai] || 'Không rõ');

const layLopTrangThai = (trangThai) => {
  switch (trangThai) {
    case 'pending_confirmation': return 'badge-warning';
    case 'confirmed': return 'badge-success';
    case 'pending_cancel': return 'badge-warning';
    case 'cancelled': return 'badge-danger';
    default: return 'badge-secondary';
  }
};
const layLopTrangThaiThanhToan = (trangThai) => {
  switch (trangThai) {
    case 'PENDING': return 'badge-warning';
    case 'PAID': return 'badge-success';
    case 'ERROR': return 'badge-danger';
    case 'REFUNDED': return 'badge-secondary';
    default: return 'badge-secondary';
  }
};

onMounted(layDanhSachDatPhong);
</script>
<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700&display=swap');


/* Đảm bảo toàn bộ component dùng chung một font */
.page-container {
  font-family: 'monospace', sans-serif;
  background-color: #f4f7f9;
  /* padding: 5rem; */
  color: #34495e;
}

.page-title {
  font-size: 2.5rem;
  font-weight: 700;
  color: #454d55;
}

.page-subtitle {
  font-size: 1rem;
  color: #7f8c8d;
}

.filter-card {
  background-color: #ffffff;
  border: none;
  border-radius: 12px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.05);
}

.form-label {
  font-weight: 500;
  margin-bottom: 0.5rem;
  font-size: 0.875rem;
}

.form-control, .form-select {
  border-radius: 8px;
  border: 1px solid #e5eaee;
  transition: all 0.2s ease-in-out;
}
.form-control:focus, .form-select:focus {
  border-color: #3498db;
  box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.15);
}

.table-container {
    background-color: #ffffff;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    overflow: hidden;
}

/* Giảm kích thước font của bảng chính */
.booking-table {
  font-size: 0.9rem; /* Giảm từ 0.95rem */
  border-collapse: separate;
  border-spacing: 0;
  width: 100%;
}
.booking-table thead th {
  background-color: #f8f9fa;
  color: #454d55;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  border-bottom: 2px solid #e5eaee;
  padding: 0.8rem 1rem; /* Giảm padding */
  cursor: pointer;
}
.booking-table td {
  padding: 1rem 1rem; /* Giảm padding */
  border-bottom: 1px solid #e5eaee;
  vertical-align: middle;
}
.booking-table tbody tr:last-child td {
    border-bottom: none;
}
.booking-table tbody tr:hover {
    background-color: #f9fafb;
}

.booking-id {
  font-weight: 600;
  color: #3498db;
}
.customer-name {
  color: #454d55;
  font-weight: 600;
}
.customer-phone {
    font-size: 0.9em;
    color: #7f8c8d;
}

.badge {
  padding: 0.4em 0.8em;
  font-size: 0.75rem;
  font-weight: 600;
  border-radius: 20px;
  text-transform: capitalize;
}

.badge-warning { background-color: #f1c40f; color: #34495e; }
.badge-success { background-color: #27ae60; color: white; }
.badge-danger { background-color: #e74c3c; color: white; }
.badge-secondary { background-color: #bdc3c7; color: #34495e; }
.btn-danger { background-color: #e74c3c; border-color: #e74c3c;}

.btn-danger:hover {
  background-color: #c0392b;
  border-color: #c0392b;
}

.pagination .page-link {
    border: none;
    border-radius: 8px;
    margin: 0 4px;
    color: #7f8c8d;
    font-weight: 600;
    transition: all 0.2s ease;
}
.pagination .page-link:hover {
    background-color: #e9ecef;
    color: #34495e;
}
.pagination .page-item.active .page-link {
    background-color: #3498db;
    color: white;
    box-shadow: 0 2px 5px rgba(52, 152, 219, 0.3);
}
.pagination .page-item.disabled .page-link {
    background-color: transparent;
    color: #cccccc;
}

.modal-backdrop { background-color: rgba(0,0,0,0.4); }
.modal-custom {
  border-radius: 16px;
  border: none;
  box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}
.modal-header-custom {
  background-color: #f4f7f9;
  border-bottom: 1px solid #e5eaee;
  padding: 1.5rem;
}
.modal-header-custom .modal-title {
    font-weight: 600;
    font-size: 1.25rem;
}
.modal-footer-custom {
    background-color: #f4f7f9;
    border-top: 1px solid #e5eaee;
    padding: 1rem 1.5rem;
    display: flex;
    align-items: center;
}

.btn-outline-primary {
    color: #3498db;
    border-color: #3498db;
}
.btn-outline-primary:hover {
    background-color: #3498db;
    color: #ffffff;
}
.btn-primary {
    background-color: #3498db;
    border-color: #3498db;
}
.btn-primary:hover {
    background-color: #2980b9;
    border-color: #2980b9;
}
.btn-primary:disabled {
    background-color: #3498db;
    border-color: #3498db;
    opacity: 0.5;
    cursor: not-allowed;
}

.info-title {
  font-weight: 600;
  color: #34495e;
  margin-bottom: 1rem;
  font-size: 1rem;
  padding-bottom: 0.5rem;
  border-bottom: 2px solid #3498db;
  display: inline-block;
}

.info-list {
  list-style: none;
  padding: 0;
  margin: 0;
}
/* Sửa layout của danh sách thông tin trong Modal */
.info-list li {
  display: flex;
  justify-content: space-between;
  align-items: baseline; /* Căn theo baseline của chữ, giúp nhãn và dòng đầu tiên của nội dung thẳng hàng */
  gap: 1rem; /* Tạo khoảng cách giữa nhãn và nội dung */
  padding: 0.6rem 0.5rem;
  border-bottom: 1px solid #e5eaee;
  font-size: 0.9rem; /* Giảm kích thước font */
}
.info-list li:hover {
  background-color: #fdfdfe;
}
.info-list li:last-child { border-bottom: none; }

/* Nhãn (Họ tên:, Điện thoại:,...) */
.info-list li span { 
  color: #7f8c8d;
  flex-shrink: 0; /* Không cho phép nhãn bị co lại */
}

/* Nội dung (Tên khách hàng, số điện thoại,...) */
.info-list li strong { 
    color: #34495e;
    text-align: left;
    word-break: break-word; /* Quan trọng: Cho phép từ dài tự động xuống dòng */
}

.status-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
  background-color: #f0f5fa;
  padding: 1rem 1.5rem;
  border-radius: 12px;
  margin: 2rem 0;
  border-left: 5px solid #3498db;
}
.status-bar > div { font-weight: 500; }
.status-bar .total-price { font-size: 1.2rem; color: #3498db; }

/* Giảm kích thước font của bảng xếp phòng */
.room-assignment-table {
    font-size: 0.85rem; /* Giảm từ 0.9rem */
    width: 100%;
}
.room-assignment-table thead th {
    background-color: #f8f9fa;
    color: #7f8c8d;
    font-weight: 600;
    border-bottom: 2px solid #e5eaee;
}
.room-assignment-table td, .room-assignment-table th {
    vertical-align: middle;
}
</style>