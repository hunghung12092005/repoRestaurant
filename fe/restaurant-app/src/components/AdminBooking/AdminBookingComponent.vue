<template>
  <div class="container mt-5">
    <!-- Tiêu đề trang -->
    <h1 class="text-heading-md font-semibold text-primary mb-4">Quản Lý Đặt Phòng Khách Sạn</h1>

    <!-- Bộ lọc và tìm kiếm -->
    <div class="card mb-4">
      <div class="card-body">
        <div class="row">
          <div class="col-md-3">
            <div class="form-group">
              <label>Tìm kiếm</label>
              <input 
                v-model="tuKhoaTimKiem" 
                type="text" 
                class="form-control" 
                placeholder="Mã đặt phòng, tên KH..."
                @input="timKiemDatPhong"
              >
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <label>Trạng thái</label>
              <select v-model="locTrangThai" class="form-control" @change="locDanhSach">
                <option value="">Tất cả</option>
                <option value="pending_confirmation">Chờ xác nhận</option>
                <option value="confirmed">Đã xác nhận</option>
              </select>
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <label>Từ ngày</label>
              <input v-model="tuNgay" type="date" class="form-control" @change="locDanhSach">
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <label>Đến ngày</label>
              <input v-model="denNgay" type="date" class="form-control" @change="locDanhSach">
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Hiển thị thông báo lỗi nếu có -->
    <div v-if="thongBaoLoi" class="alert alert-danger" role="alert">
      {{ thongBaoLoi }}
    </div>

    <!-- Bảng danh sách đặt phòng -->
    <div class="bang-du-lieu shadow-sm rounded">
      <table class="table table-striped table-hover" v-if="danhSachHienThi.length > 0">
        <thead class="bg-gray-50">
          <tr>
            <th scope="col" @click="sapXep('booking_id')">
              Mã Đặt Phòng 
              <i v-if="sapXepCot === 'booking_id'" :class="sapXepGiam ? 'fas fa-sort-down' : 'fas fa-sort-up'"></i>
            </th>
            <th scope="col">Khách Hàng</th>
            <th scope="col">Số Điện Thoại</th>
            <th scope="col">Loại Đặt Phòng</th>
            <th scope="col" @click="sapXep('check_in_date')">
              Nhận Phòng
              <i v-if="sapXepCot === 'check_in_date'" :class="sapXepGiam ? 'fas fa-sort-down' : 'fas fa-sort-up'"></i>
            </th>
            <th scope="col" @click="sapXep('check_out_date')">
              Trả Phòng
              <i v-if="sapXepCot === 'check_out_date'" :class="sapXepGiam ? 'fas fa-sort-down' : 'fas fa-sort-up'"></i>
            </th>
            <th scope="col">Tổng Phòng</th>
            <th scope="col">Tổng Giá</th>
            <th scope="col">Trạng Thái</th>
            <th scope="col">Hành Động</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="datPhong in danhSachHienThi" :key="datPhong.booking_id" class="table-row">
            <td>{{ datPhong.booking_id }}</td>
            <td>{{ datPhong.customer?.customer_name || 'Không xác định' }}</td>
            <td>{{ datPhong.customer?.customer_phone || 'Không xác định' }}</td>
            <td>{{ dinhDangLoaiDatPhong(datPhong.booking_type) }}</td>
            <td>{{ dinhDangNgay(datPhong.check_in_date) }}</td>
            <td>{{ dinhDangNgay(datPhong.check_out_date) }}</td>
            <td>{{ datPhong.total_rooms || '0' }}</td>
            <td>{{ dinhDangTien(datPhong.total_price) }}</td>
            <td>
              <span :class="layLopTrangThai(datPhong.status)">
                {{ dinhDangTrangThai(datPhong.status) }}
              </span>
            </td>
            <td>
              <button
                @click="moModalChiTiet(datPhong)"
                class="btn btn-primary btn-sm"
              >
                Xem chi tiết
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Phân trang -->
    <div class="row mt-3" v-if="tongSoTrang > 1">
      <div class="col-md-12">
        <nav aria-label="Page navigation">
          <ul class="pagination justify-content-center">
            <li class="page-item" :class="{ disabled: trangHienTai === 1 }">
              <a class="page-link" href="#" @click.prevent="chuyenTrang(1)">Đầu</a>
            </li>
            <li class="page-item" :class="{ disabled: trangHienTai === 1 }">
              <a class="page-link" href="#" @click.prevent="chuyenTrang(trangHienTai - 1)">Trước</a>
            </li>
            <li class="page-item" v-for="trang in danhSachTrang" :key="trang" 
                :class="{ active: trang === trangHienTai }">
              <a class="page-link" href="#" @click.prevent="chuyenTrang(trang)">{{ trang }}</a>
            </li>
            <li class="page-item" :class="{ disabled: trangHienTai === tongSoTrang }">
              <a class="page-link" href="#" @click.prevent="chuyenTrang(trangHienTai + 1)">Sau</a>
            </li>
            <li class="page-item" :class="{ disabled: trangHienTai === tongSoTrang }">
              <a class="page-link" href="#" @click.prevent="chuyenTrang(tongSoTrang)">Cuối</a>
            </li>
          </ul>
        </nav>
      </div>
    </div>

    <!-- Modal chi tiết đặt phòng -->
    <div v-if="hienModal" class="modal fade show" style="display: block;" tabindex="-1" role="dialog">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title text-primary">Chi Tiết Đặt Phòng #{{ datPhongDuocChon.booking_id }}</h5>
            <button type="button" @click="dongModal" class="btn-close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true"></span>
            </button>
          </div>
          <div class="modal-body">
            <!-- Hiển thị loading khi đang tải dữ liệu -->
            <div v-if="dangTai" class="text-center">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Đang tải...</span>
              </div>
            </div>
            <!-- Hiển thị thông tin chi tiết đặt phòng -->
            <div v-else-if="datPhongDuocChon.booking_id" class="row mb-4">
              <div class="col-md-6">
                <p><strong class="text-dark">Khách Hàng:</strong> {{ datPhongDuocChon.customer?.customer_name || 'Không xác định' }}</p>
                <p><strong class="text-dark">Số Điện Thoại:</strong> {{ datPhongDuocChon.customer?.customer_phone || 'Không xác định' }}</p>
                <p><strong class="text-dark">Email:</strong> {{ datPhongDuocChon.customer?.customer_email || 'Không có' }}</p>
                <p><strong class="text-dark">Loại Đặt Phòng:</strong> {{ dinhDangLoaiDatPhong(datPhongDuocChon.booking_type) }}</p>
              </div>
              <div class="col-md-6">
                <p><strong class="text-dark">Nhận Phòng:</strong> {{ dinhDangNgay(datPhongDuocChon.check_in_date) }}</p>
                <p><strong class="text-dark">Trả Phòng:</strong> {{ dinhDangNgay(datPhongDuocChon.check_out_date) }}</p>
                <p><strong class="text-dark">Tổng Phòng:</strong> {{ datPhongDuocChon.total_rooms || '0' }}</p>
                <p><strong class="text-dark">Tổng Giá:</strong> {{ dinhDangTien(datPhongDuocChon.total_price) }}</p>
                <p><strong class="text-dark">Trạng Thái Thanh Toán:</strong>
                  <span :class="layLopTrangThaiThanhToan(datPhongDuocChon.payment_status)">
                    {{ datPhongDuocChon.payment_status_display || dinhDangTrangThaiThanhToan(datPhongDuocChon.payment_status) }}
                  </span>
                </p>
                <p><strong class="text-dark">Trạng Thái:</strong>
                  <span :class="layLopTrangThai(datPhongDuocChon.status)">
                    {{ dinhDangTrangThai(datPhongDuocChon.status) }}
                  </span>
                </p>
                <p><strong class="text-dark">Ghi Chú:</strong> {{ datPhongDuocChon.note || 'Không có' }}</p>
              </div>
            </div>

            <!-- Bảng chi tiết phòng -->
            <h4 v-if="!dangTai && datPhongDuocChon.booking_id" class="text-primary mb-3">Chi Tiết Phòng</h4>
            <div v-if="!dangTai && datPhongDuocChon.booking_id" class="bang-du-lieu mb-4">
              <table class="table table-striped">
                <thead class="thead-dark">
                  <tr>
                    <th>Mã Chi Tiết</th>
                    <th>Loại Phòng Đã Đặt</th>
                    <th>Phòng Đã Xếp</th>
                    <th>Tổng Giá</th>
                    <th>Xếp Phòng</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="chiTiet in chiTietDatPhongHopLe" :key="chiTiet.booking_detail_id">
                    <td>{{ chiTiet.booking_detail_id }}</td>
                    <td>{{ chiTiet.type_name || 'Loại phòng không xác định' }}</td>
                    <td>{{ chiTiet.room?.room_name || 'Chưa xếp' }}</td>
                    <td>{{ dinhDangTien(chiTiet.total_price) }}</td>
                    <td>
                      <select
                        v-if="!chiTiet.room_id && phongTrong[chiTiet.booking_detail_id]?.length > 0 && datPhongDuocChon.status === 'pending_confirmation'"
                        v-model="chiTiet.room_id"
                        @change="xepPhong(chiTiet)"
                        class="form-control form-control-sm"
                      >
                        <option value="">Chọn phòng</option>
                        <option
                          v-for="phong in phongTrong[chiTiet.booking_detail_id]"
                          :key="phong.room_id"
                          :value="phong.room_id"
                        >
                          {{ phong.room_name }} ({{ phong.type_name || 'Không xác định' }})
                        </option>
                      </select>
                      <span v-else-if="chiTiet.room_id">Đã xếp: {{ chiTiet.room?.room_name || 'Phòng không xác định' }}</span>
                      <span v-else-if="!chiTiet.room_id && datPhongDuocChon.status === 'confirmed'">Chưa xếp (Đặt phòng {{ dinhDangTrangThai(datPhongDuocChon.status) }})</span>
                      <span v-else>Không có phòng trống</span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div v-if="!dangTai && datPhongDuocChon.booking_id && !chiTietDatPhongHopLe.length" class="alert alert-warning text-center">
              Không có chi tiết phòng để hiển thị.
            </div>
          </div>
          <!-- Nút xác nhận và đóng modal -->
          <div v-if="!dangTai && datPhongDuocChon.booking_id" class="modal-footer">
            <button
              v-if="datPhongDuocChon.status === 'pending_confirmation'"
              @click="xacNhanDatPhong"
              class="btn btn-success"
              :disabled="coPhongChuaXep"
            >
              Xác Nhận Đặt Phòng
            </button>
            <button @click="dongModal" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
          </div>
        </div>
      </div>
    </div>
    <div v-if="hienModal" class="modal-backdrop fade show"></div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';

// Trạng thái (state)
const danhSachDatPhong = ref([]);
const hienModal = ref(false);
const datPhongDuocChon = ref({});
const chiTietDatPhong = ref([]);
const phongTrong = ref({});
const thongBaoLoi = ref('');
const dangTai = ref(false);

// Phân trang và lọc
const tuKhoaTimKiem = ref('');
const locTrangThai = ref('');
const tuNgay = ref('');
const denNgay = ref('');
const trangHienTai = ref(1);
const soBanGhiTrenTrang = ref(10);
const sapXepCot = ref('booking_id');
const sapXepGiam = ref(true);

// Tính toán danh sách đã lọc
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
    ketQua = ketQua.filter(item => {
      const ngayDatPhong = new Date(item.check_in_date);
      return ngayDatPhong >= ngayBatDau;
    });
  }
  
  if (denNgay.value) {
    const ngayKetThuc = new Date(denNgay.value);
    ketQua = ketQua.filter(item => {
      const ngayDatPhong = new Date(item.check_in_date);
      return ngayDatPhong <= ngayKetThuc;
    });
  }
  
  ketQua.sort((a, b) => {
    const giaTriA = a[sapXepCot.value];
    const giaTriB = b[sapXepCot.value];
    
    if (sapXepCot.value === 'booking_id') {
      return sapXepGiam.value ? giaTriB - giaTriA : giaTriA - giaTriB;
    } else if (sapXepCot.value === 'check_in_date' || sapXepCot.value === 'check_out_date') {
      const dateA = new Date(giaTriA);
      const dateB = new Date(giaTriB);
      return sapXepGiam.value ? dateB - dateA : dateA - dateB;
    }
    
    return 0;
  });
  
  return ketQua;
});

// Tính toán phân trang
const tongSoBanGhi = computed(() => danhSachLoc.value.length);
const tongSoTrang = computed(() => Math.ceil(tongSoBanGhi.value / soBanGhiTrenTrang.value));
const danhSachTrang = computed(() => {
  const maxPagesToShow = 5;
  const halfPages = Math.floor(maxPagesToShow / 2);
  let trangBatDau = Math.max(1, trangHienTai.value - halfPages);
  let trangKetThuc = Math.min(tongSoTrang.value, trangBatDau + maxPagesToShow - 1);
  
  if (trangKetThuc - trangBatDau + 1 < maxPagesToShow) {
    trangBatDau = Math.max(1, trangKetThuc - maxPagesToShow + 1);
  }
  
  const danhSach = [];
  for (let i = trangBatDau; i <= trangKetThuc; i++) {
    danhSach.push(i);
  }
  return danhSach;
});

// Tính toán danh sách hiển thị trên trang hiện tại
const danhSachHienThi = computed(() => {
  const batDau = (trangHienTai.value - 1) * soBanGhiTrenTrang.value;
  const ketThuc = batDau + soBanGhiTrenTrang.value;
  return danhSachLoc.value.slice(batDau, ketThuc);
});

// Tính toán danh sách chi tiết đặt phòng hợp lệ
const chiTietDatPhongHopLe = computed(() => {
  return chiTietDatPhong.value.filter(chiTiet => chiTiet && chiTiet.booking_detail_id);
});

// Kiểm tra xem có phòng nào chưa được gán hay không
const coPhongChuaXep = computed(() => {
  return chiTietDatPhong.value.some(chiTiet => !chiTiet.room_id);
});

// Lấy danh sách đặt phòng từ API
const layDanhSachDatPhong = async () => {
  try {
    const phanHoi = await axios.get('/api/bookings?status[]=pending_confirmation&status[]=confirmed&status[]=cancelled', {
      headers: { 'Accept': 'application/json' }
    });
    danhSachDatPhong.value = Array.isArray(phanHoi.data) ? phanHoi.data : [];
    trangHienTai.value = 1;
    thongBaoLoi.value = danhSachDatPhong.value.length === 0 ? 'Không có dữ liệu đặt phòng từ server.' : '';
  } catch (loi) {
    console.error('Lỗi khi lấy danh sách đặt phòng:', loi);
    thongBaoLoi.value = 'Không có dữ liệu đặt phòng từ server.';
  }
};

// Mở modal chi tiết đặt phòng
const moModalChiTiet = async (datPhong) => {
  datPhongDuocChon.value = datPhong;
  dangTai.value = true;
  try {
    const phanHoiChiTiet = await axios.get(`/api/booking-details/${datPhong.booking_id}`, {
      headers: { 'Accept': 'application/json' }
    });
    chiTietDatPhong.value = Array.isArray(phanHoiChiTiet.data)
      ? phanHoiChiTiet.data.map(chiTiet => ({
          ...chiTiet,
          room: chiTiet.room || { room_id: null, room_name: 'Chưa xếp', status: 'Không xác định' },
          type_name: chiTiet.roomType?.type_name || 'Loại phòng không xác định',
          room_type: chiTiet.room_type
        }))
      : [];
    console.log('Chi tiết đặt phòng:', chiTietDatPhong.value);

    if (datPhong.status === 'pending_confirmation') {
      await Promise.all(
        chiTietDatPhong.value.map(async (chiTiet) => {
          if (!chiTiet.room_id && chiTiet.room_type) {
            try {
              const maLoaiPhong = Number(chiTiet.room_type);
              if (isNaN(maLoaiPhong) || maLoaiPhong <= 0) {
                throw new Error(`Mã loại phòng không hợp lệ: ${chiTiet.room_type}`);
              }
              const phanHoiPhong = await axios.post('/api/available-rooms', {
                room_type: maLoaiPhong,
                check_in_date: datPhong.check_in_date,
                check_out_date: datPhong.check_out_date
              }, {
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }
              });
              phongTrong.value[chiTiet.booking_detail_id] = Array.isArray(phanHoiPhong.data)
                ? phanHoiPhong.data
                : [];
              console.log(`Phòng trống cho mã chi tiết ${chiTiet.booking_detail_id}:`, phongTrong.value[chiTiet.booking_detail_id]);
            } catch (loi) {
              console.error(`Lỗi khi lấy phòng trống cho mã loại phòng ${chiTiet.room_type}:`, loi);
              phongTrong.value[chiTiet.booking_detail_id] = [];
              thongBaoLoi.value = `Lỗi khi lấy phòng trống: ${loi.response?.data?.message || loi.message}`;
            }
          }
        })
      );
    }

    thongBaoLoi.value = Object.values(phongTrong.value).every(arr => arr.length === 0) && datPhong.status === 'pending_confirmation'
      ? 'Không có phòng trống phù hợp với loại phòng.'
      : '';
  } catch (loi) {
    console.error('Lỗi khi lấy chi tiết đặt phòng:', loi);
    thongBaoLoi.value = loi.response?.data?.error 
      ? `Lỗi server: ${loi.response.data.error} (Mã: ${loi.response.status})`
      : `Lỗi khi lấy chi tiết đặt phòng: ${loi.message}`;
  } finally {
    dangTai.value = false;
    hienModal.value = true;
  }
};

// Gán phòng cho chi tiết đặt phòng
const xepPhong = async (chiTiet) => {
  if (!chiTiet || !chiTiet.booking_detail_id || !chiTiet.room_id) {
    thongBaoLoi.value = 'Chi tiết đặt phòng hoặc phòng không hợp lệ';
    return;
  }
  try {
    await axios.post(`/api/assign-room/${chiTiet.booking_detail_id}`, {
      room_id: chiTiet.room_id,
      check_in_date: datPhongDuocChon.value.check_in_date,
      check_out_date: datPhongDuocChon.value.check_out_date
    }, {
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }
    });
    await moModalChiTiet(datPhongDuocChon.value);
    thongBaoLoi.value = '';
  } catch (loi) {
    console.error('Lỗi khi xếp phòng:', loi);
    thongBaoLoi.value = loi.response?.data?.error 
      ? `Lỗi server: ${loi.response.data.error} (Mã: ${loi.response.status})`
      : `Lỗi khi xếp phòng: ${loi.message}`;
  }
};

// Xác nhận đặt phòng
const xacNhanDatPhong = async () => {
  try {
    await axios.patch(`/api/bookings/${datPhongDuocChon.value.booking_id}`, {
      status: 'confirmed'
    }, {
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }
    });
    await layDanhSachDatPhong();
    alert('Xác nhận đặt phòng thành công!');
    dongModal();
  } catch (loi) {
    console.error('Lỗi khi xác nhận đặt phòng:', loi);
    alert('Lỗi khi xác nhận đặt phòng!');
  }
};

// Đóng modal
const dongModal = () => {
  hienModal.value = false;
  datPhongDuocChon.value = {};
  chiTietDatPhong.value = [];
  phongTrong.value = {};
  thongBaoLoi.value = '';
};

// Chuyển trang
const chuyenTrang = (trang) => {
  if (trang >= 1 && trang <= tongSoTrang.value) {
    trangHienTai.value = trang;
  }
};

// Tìm kiếm đặt phòng
const timKiemDatPhong = () => {
  trangHienTai.value = 1;
};

// Lọc danh sách
const locDanhSach = () => {
  trangHienTai.value = 1;
};

// Sắp xếp
const sapXep = (cot) => {
  if (sapXepCot.value === cot) {
    sapXepGiam.value = !sapXepGiam.value;
  } else {
    sapXepCot.value = cot;
    sapXepGiam.value = cot === 'booking_id' ? true : false;
  }
  trangHienTai.value = 1;
};

// Định dạng ngày
const dinhDangNgay = (ngay) => {
  return ngay ? new Date(ngay).toLocaleDateString('vi-VN') : 'Không xác định';
};

// Định dạng giá tiền
const dinhDangTien = (gia) => {
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(gia || 0);
};

// Định dạng loại đặt phòng
const dinhDangLoaiDatPhong = (loai) => {
  const bangLoai = {
    online: 'Trực tuyến',
    offline: 'Tại chỗ'
  };
  return bangLoai[loai] || loai || 'Không xác định';
};

// Định dạng trạng thái
const dinhDangTrangThai = (trangThai) => {
  const bangTrangThai = {
    pending_confirmation: 'Chờ xác nhận',
    confirmed: 'Đã xác nhận',
  };
  return bangTrangThai[trangThai] || trangThai || 'Không xác định';
};

// Định dạng trạng thái thanh toán
const dinhDangTrangThaiThanhToan = (trangThai) => {
  const bangTrangThaiThanhToan = {
    PENDING: 'Thanh toán tại quầy',
    PAID: 'Đã thanh toán',
    REFUNDED: 'Đã hoàn tiền',
    ERROR: 'Lỗi thanh toán',
    UNKNOWN: 'Không xác định'
  };
  return bangTrangThaiThanhToan[trangThai] || trangThai || 'Không xác định';
};

// Lấy lớp CSS cho trạng thái
const layLopTrangThai = (trangThai) => {
  return {
    'badge': true,
    'badge-warning': trangThai === 'pending_confirmation',
    'badge-success': trangThai === 'confirmed',
    'badge-danger': trangThai === 'cancelled'
  };
};

// Lấy lớp CSS cho trạng thái thanh toán
const layLopTrangThaiThanhToan = (trangThai) => {
  return {
    'badge': true,
    'badge-warning': trangThai === 'PENDING' || trangThai === 'pending',
    'badge-success': trangThai === 'PAID' || trangThai === 'paid',
    'badge-danger': trangThai === 'CANCELLED' || trangThai === 'cancelled' || trangThai === 'ERROR',
    'badge-secondary': trangThai === 'REFUNDED' || trangThai === 'refunded',
    'badge-light': trangThai === 'UNKNOWN'
  };
};

onMounted(() => {
  layDanhSachDatPhong();
});
</script>

<style scoped>
.thong-bao-loi {
  color: #721c24;
  background-color: #f8d7da;
  border-color: #f5c6cb;
  padding: 10px;
  margin-bottom: 20px;
  border-radius: 4px;
}
.thong-bao-thong-tin {
  color: #0c5460;
  background-color: #d1ecf1;
  border-color: #bee5eb;
  padding: 10px;
  margin-bottom: 20px;
  border-radius: 4px;
}
.thong-bao-canh-bao {
  color: #856404;
  background-color: #fff3cd;
  border-color: #ffeeba;
  padding: 10px;
  margin-bottom: 20px;
  border-radius: 4px;
}
.bang-du-lieu {
  overflow-x: auto;
}
.bang-du-lieu table {
  width: 100%;
  border-collapse: collapse;
}
.bang-du-lieu th, .bang-du-lieu td {
  border: 1px solid #ddd;
  padding: 8px;
  text-align: left;
}
.bang-du-lieu th {
  background-color: #f2f2f2;
  cursor: pointer;
}
.bang-du-lieu th:hover {
  background-color: #e9e9e9;
}
.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  z-index: 1040;
}
.modal.fade.show {
  z-index: 1050;
}
.modal-dialog {
  transform: translate(0, 0) !important;
}
.badge {
  display: inline-block;
  padding: 0.25em 0.4em;
  font-size: 75%;
  font-weight: 700;
  line-height: 1;
  text-align: center;
  white-space: nowrap;
  vertical-align: baseline;
  border-radius: 0.25rem;
}
.badge-warning { background-color: #ffc107; color: black; }
.badge-success { background-color: #28a745; color: white; }
.badge-danger { background-color: #dc3545; color: white; }
.badge-secondary { background-color: #6c757d; color: white; }
.badge-light { background-color: #f8f9fa; color: black; }
.page-item.active .page-link {
  background-color: #007bff;
  border-color: #007bff;
}
.page-link {
  color: #007bff;
}
@media (max-width: 768px) {
  .bang-du-lieu {
    overflow-x: auto;
  }
  .btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
  }
}
</style>