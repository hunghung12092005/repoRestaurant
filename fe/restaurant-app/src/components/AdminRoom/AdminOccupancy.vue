  <template>
    <loading v-if="isLoading"></loading>
    <div class="page-container">
      <!-- Tiêu đề trang -->
      <div class="page-header mb-4">
        <h1 class="page-title">Sơ đồ Phòng</h1>
        <p class="page-subtitle">Quản lý trạng thái và thao tác với các phòng trong khách sạn.</p>
      </div>
      <!-- Bộ lọc -->
      <div class="card filter-card mb-4">
        <div class="card-body">
          <div class="row g-3 align-items-end">
            <div class="col-lg-2 col-md-6">
              <label for="filter-date" class="form-label">Xem theo ngày</label>
              <input type="date" id="filter-date" class="form-control" v-model="selectedDate" @change="fetchRooms" />
            </div>
            <div class="col-lg-2 col-md-6">
              <label for="filter-time" class="form-label">Thời gian</label>
              <input type="time" id="filter-time" class="form-control" v-model="selectedTime" @change="fetchRooms" />
            </div>
            <div class="col-lg-2 col-md-6">
              <label for="status" class="form-label">Trạng thái</label>
              <select id="status" class="form-select" v-model="selectedStatus">
                <option>Tất cả</option>
                <option>Còn trống</option>
                <option>Đã đặt</option>
              </select>
            </div>
            <div class="col-lg-2 col-md-6">
              <label for="room-type" class="form-label">Loại phòng</label>
              <select id="room-type" class="form-select" v-model="selectedRoomType">
                <option v-for="type in roomTypes" :key="type" :value="type">{{ type }}</option>
              </select>
            </div>
            <div class="col-lg-2 col-md-6">
              <label for="floor" class="form-label">Tầng</label>
              <select id="floor" class="form-select" v-model="selectedFloor">
                <option v-for="floor in floors" :key="floor" :value="floor">{{ floor }}</option>
              </select>
            </div>
            <div class="col-lg-2 col-md-12 text-end">
              <button @click="openFutureBookings" class="btn btn-outline-danger w-100">
                <i class="bi bi-calendar-x me-2"></i>Hủy phòng trước
              </button>
            </div>

            <div class="col-lg-2 col-md-12 text-end">
              <button @click="openMultiBookingModal" class="btn btn-outline-success w-100">
                <i class="bi bi-plus-square me-2"></i>Đặt nhiều phòng
              </button>
            </div>
            <div class="col-lg-2 col-md-12 text-end">
              <button @click="openPayGroupModal" class="btn btn-outline-warning w-100">
                <i class="bi bi-cash-stack me-2"></i> Thanh toán nhóm
              </button>
            </div>

            <div class="col-lg-2 col-md-12 text-end">
              <button @click="clearFilters" class="btn btn-outline-secondary w-100">
                <i class="bi bi-arrow-clockwise me-2"></i>Xóa lọc
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Sơ đồ phòng -->
      <div v-if="filteredRooms.length > 0">
        <div v-for="floorGroup in groupedAndSortedRooms" :key="floorGroup.floor" class="floor-section">
          <h2 class="floor-header">Tầng {{ floorGroup.floor }}</h2>
          <div class="room-grid">
            <div v-for="room in floorGroup.rooms" :key="room.room_id" class="room-card"
              :class="{ 'booked': room.status === 'Đã đặt' }">
              <div class="card-header">
                <h5 class="room-number">{{ room.number }}</h5>
                <span class="badge" :class="room.status === 'Đã đặt' ? 'badge-booked' : 'badge-available'">{{
                  room.status }}</span>
                <span v-if="room.payment_status === 'completed'" class="badge badge-paid">Đã thanh toán</span>
              </div>
              <div class="card-body">
                <p class="room-type">{{ room.type }}</p>
                <p class="room-bedsize">{{ room.bedSize }}</p>
                <p class="room-capacity">
                  <i class="bi bi-people-fill"></i> {{ room.max_occupancy }} Người lớn & {{ room.max_occupancy_child }}
                  Trẻ em
                </p>
              </div>
              <div class="card-footer">
                <div v-if="room.status === 'Đã đặt'" class="action-grid">
                  <div class="action-row">
                    <button class="btn btn-sm btn-outline-primary" @click.prevent="showGuestDetails(room)">Chi
                      tiết</button>
                  </div>
                  <button class="btn btn-sm btn-outline-danger w-100 mt-2" @click.prevent="checkoutRoom(room)"
                    :disabled="room.payment_status === 'completed'">Trả phòng</button>
                  <button class="btn btn-sm btn-outline-danger w-100 mt-2"
                    @click.prevent="openRoomChangePopup(room)">Chuyen
                    phòng</button>
                </div>
                <button v-else class="btn btn-sm btn-outline-primary w-100"
                  @click.prevent="showAddGuest(room.room_id)"><i class="bi bi-person-plus-fill me-1"></i> Thêm
                  khách</button>
              </div>
            </div>

          </div>
        </div>
      </div>
      <div v-else class="alert alert-light text-center">Không có phòng nào khớp với bộ lọc hiện tại.</div>
      <!-- popup roi phong -->
      <div v-if="showPopupLeaveRoom" class="modal-backdrop fade show"></div>
      <div v-if="showPopupLeaveRoom" class="modal fade show d-block" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
          <div class="modal-content modal-custom">
            <div class="modal-header modal-header-custom">
              <h5 class="modal-title">Chọn phòng mới</h5>
              <button type="button" class="btn-close" @click="showPopupLeaveRoom = false"></button>
            </div>
            <div class="modal-body p-4">
              <table v-if="availableRoomsLeaveRoom.length" class="table table-striped table-bordered">
                <thead>
                  <tr>
                    <th>Tên phòng</th>
                    <th>Tầng</th>
                    <th>Trạng thái</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="room in availableRoomsLeaveRoom" :key="room.room_id">
                    <td>{{ room.room_name }}</td>
                    <td>{{ room.floor_number }}</td>
                    <td>Còn trống</td>
                    <td>
                      <button class="btn btn-primary btn-sm" @click="selectRoom(room)">
                        Chuyển sang
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
              <div v-else class="text-center text-muted">Không có phòng trống để chuyển</div>
            </div>
            <div class="modal-footer modal-footer-custom">
              <button type="button" class="btn btn-secondary" @click="showPopupLeaveRoom = false">
                Đóng
              </button>
            </div>
          </div>
        </div>
      </div>
      <!-- end roi phong -->
      <!-- Modal Thêm Khách -->
      <div v-if="showForm" class="modal-backdrop fade show"></div>
      <div v-if="showForm" class="modal fade show d-block" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
          <form @submit.prevent="submitCustomerForm" class="modal-content modal-custom">
            <div class="modal-header modal-header-custom">
              <h5 class="modal-title">Đăng ký khách hàng</h5><button type="button" class="btn-close"
                @click="showForm = false"></button>
            </div>
            <div class="modal-body p-4">
              <div class="row g-3">
                <div class="col-12"><label class="form-label">Ảnh CCCD</label>
                  <div class="input-group"><input type="file" @change="onFileChange" accept="image/*"
                      class="form-control" /><button type="button" class="btn btn-outline-secondary"
                      @click="uploadImage">Quét CCCD</button></div>
                </div>
                <div class="col-md-6"><label class="form-label">Họ tên</label><input v-model="formData.customer_name"
                    required class="form-control" /></div>
                <div class="col-md-6"><label class="form-label">Số điện thoại</label><input
                    v-model="formData.customer_phone" required class="form-control" /></div>
                <div class="col-md-6"><label class="form-label">Số CCCD</label><input
                    v-model="formData.customer_id_number" required class="form-control" /></div>
                <div class="col-md-6"><label class="form-label">Email</label><input v-model="formData.customer_email"
                    type="email" required class="form-control" /></div>
                <div class="col-12"><label class="form-label">Địa chỉ</label><input v-model="formData.address"
                    class="form-control" /></div>
                <div class="col-md-6"><label class="form-label">Ngày nhận phòng</label><input type="date"
                    v-model="formData.check_in_date" required class="form-control" /></div>
                <div class="col-md-6"><label class="form-label">Giờ nhận phòng</label><input type="time"
                    v-model="formData.check_in_time" class="form-control" placeholder="14:00" /></div>
                <div class="col-md-6"><label class="form-label">Ngày trả phòng</label><input type="date"
                    v-model="formData.check_out_date" required class="form-control" /></div>
                <div class="col-md-6"><label class="form-label">Giờ trả phòng</label><input type="time"
                    v-model="formData.check_out_time" class="form-control" placeholder="12:00" /></div>
                <div class="col-12 mt-3">
                  <label class="form-label">Tổng tiền ước tính:</label>
                  <div v-if="totalPricePreview && isFinite(totalPricePreview)" class="fw-bold fs-5 text-success">
                    {{ Number(totalPricePreview).toLocaleString('vi-VN') + ' VND' }}
                  </div>
                  <div v-else class="text-danger">
                    {{ pricePreviewError || 'Không thể tính giá. Vui lòng kiểm tra thời gian đặt phòng.' }}
                  </div>
                </div>
              </div>
            </div>
            <div class="modal-footer modal-footer-custom"><button type="button" class="btn btn-secondary"
                @click="showForm = false">Hủy</button><button type="submit" class="btn btn-primary">Lưu</button></div>
          </form>
        </div>
      </div>

      <!-- Modal Chi Tiết Khách -->
      <div v-if="showGuestModal" class="modal-backdrop fade show"></div>
      <div v-if="showGuestModal" class="modal fade show d-block" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
          <div class="modal-content modal-custom" v-if="guestInfo.room">
            <div class="modal-header modal-header-custom">
              <h5 class="modal-title">Chi tiết phòng & khách</h5><button type="button" class="btn-close"
                @click="showGuestModal = false"></button>
            </div>
            <div class="modal-body p-4">
              <div class="row g-4">
                <div class="col-md-6">
                  <h6 class="info-title">Thông tin phòng</h6>
                  <ul class="info-list">
                    <li><span>Phòng:</span><strong>{{ guestInfo.room.room_name }} (Tầng {{ guestInfo.room.floor_number
                    }})</strong></li>
                    <li><span>Loại phòng:</span><strong>{{ guestInfo.room.type_name }}</strong></li>
                  </ul>
                </div>
                <div class="col-md-6">
                  <h6 class="info-title">Thông tin khách hàng</h6>
                  <ul class="info-list">
                    <li><span>Họ tên:</span><strong>{{ guestInfo.customer?.customer_name || 'N/A' }}</strong></li>
                    <li><span>SĐT:</span><strong>{{ guestInfo.customer?.customer_phone || 'N/A' }}</strong></li>
                    <li><span>Email:</span><strong>{{ guestInfo.customer?.customer_email || 'N/A' }}</strong></li>
                    <li><span>Địa chỉ:</span><strong>{{ guestInfo.customer?.address || 'N/A' }}</strong></li>
                  </ul>
                </div>
                <div class="col-12">
                  <h6 class="info-title">Chi tiết lưu trú</h6>
                  <ul class="info-list">
                    <li><span>Nhận phòng:</span><strong>{{ formatDateTime(guestInfo.booking?.check_in_date,
                      guestInfo.booking?.check_in_time) || 'N/A' }}</strong></li>
                    <li><span>Trả phòng dự kiến:</span><strong>{{ formatDateTime(guestInfo.booking?.check_out_date,
                      guestInfo.booking?.check_out_time) || 'N/A' }}</strong></li>
                    <li><span>Trả phòng thực tế:</span><strong>{{ getActualCheckout(guestInfo.booking) }}</strong></li>
                    <li><span>Tổng tiền:</span><strong class="text-success fs-6">{{ guestInfo.booking?.total_price ?
                      Number(guestInfo.booking.total_price).toLocaleString('vi-VN') + ' VND' : 'N/A' }}</strong></li>
                  </ul>
                </div>
              </div>
            </div>
            <div class="modal-footer modal-footer-custom"><button type="button" class="btn btn-secondary"
                @click="showGuestModal = false">Đóng</button><button type="button" class="btn btn-primary"
                @click="editCustomerInfo(guestInfo.customer)">Sửa thông tin</button></div>
          </div>
        </div>
      </div>

      <!-- Modal Gia hạn -->
      <div v-if="showExtendModal" class="modal-backdrop fade show"></div>
      <div v-if="showExtendModal" class="modal fade show d-block" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
          <form @submit.prevent="submitExtendForm" class="modal-content modal-custom">
            <div class="modal-header modal-header-custom">
              <h5 class="modal-title">Gia hạn thuê phòng</h5><button type="button" class="btn-close"
                @click="showExtendModal = false"></button>
            </div>
            <div class="modal-body p-4">
              <div class="mb-3"><label class="form-label">Ngày giờ trả mới:</label><input type="datetime-local"
                  v-model="extendForm.check_out_date" required class="form-control" /></div>
            </div>
            <div class="modal-footer modal-footer-custom"><button type="button" class="btn btn-secondary"
                @click="showExtendModal = false">Hủy</button><button type="submit" class="btn btn-primary">Xác
                nhận</button></div>
          </form>
        </div>
      </div>

      <!-- Modal Dịch vụ -->
      <div v-if="showServiceModal" class="modal-backdrop fade show"></div>
      <div v-if="showServiceModal" class="modal fade show d-block" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content modal-custom">
            <div class="modal-header modal-header-custom">
              <h5 class="modal-title">Chọn dịch vụ sử dụng</h5><button type="button" class="btn-close"
                @click="showServiceModal = false"></button>
            </div>
            <div class="modal-body p-4">
              <div v-if="allServices.length === 0">Đang tải dịch vụ...</div>
              <div v-else>
                <div v-for="(service, index) in allServices" :key="service.service_id" class="service-item">
                  <div>{{ service.service_name }} - {{ formatPrice(service.price) }}</div>
                  <div class="qty-controls">
                    <button @click="decreaseQty(index)" class="btn btn-sm btn-outline-secondary">-</button>
                    <input type="number" v-model.number="service.quantity" min="0"
                      class="form-control form-control-sm text-center" />
                    <button @click="increaseQty(index)" class="btn btn-sm btn-outline-secondary">+</button>
                  </div>
                </div>
                <hr />
                <div class="mb-2"><label>Tổng tiền dịch vụ:</label><span class="fw-bold text-danger ms-2">{{
                  serviceTotal.toLocaleString('vi-VN') }} VND</span></div>
                <div class="mb-2"><label class="form-label">Phí phụ thu (VND):</label><input type="number"
                    v-model.number="additionalFee" min="0" class="form-control form-control-sm" /></div>
                <div class="mb-2"><label class="form-label">Lý do phụ thu:</label><input type="text"
                    v-model="surchargeReason" class="form-control form-control-sm" /></div>
              </div>
            </div>
            <div class="modal-footer modal-footer-custom"><button type="button" class="btn btn-secondary"
                @click="showServiceModal = false">Hủy</button><button type="button" class="btn btn-danger"
                @click="confirmPayment">Xác nhận trả phòng</button></div>
          </div>
        </div>
      </div>

      <!-- Modal Sửa Thông Tin Khách -->
      <div v-if="showEditForm" class="modal-backdrop fade show"></div>
      <div v-if="showEditForm" class="modal fade show d-block" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
          <form @submit.prevent="submitEditForm" class="modal-content modal-custom">
            <div class="modal-header modal-header-custom">
              <h5 class="modal-title">Sửa thông tin khách hàng</h5><button type="button" class="btn-close"
                @click="showEditForm = false"></button>
            </div>
            <div class="modal-body p-4">
              <div class="mb-3">
                <label class="form-label">Ngày nhận phòng:</label>
                <input type="date" v-model="editFormData.check_in_date" class="form-control" />
              </div>
              <div class="mb-3">
                <label class="form-label">Giờ nhận phòng:</label>
                <input type="time" v-model="editFormData.check_in_time" class="form-control" />
              </div>
              <div class="mb-3">
                <label class="form-label">Ngày trả phòng:</label>
                <input type="date" v-model="editFormData.check_out_date" class="form-control" />
              </div>
              <div class="mb-3">
                <label class="form-label">Giờ trả phòng:</label>
                <input type="time" v-model="editFormData.check_out_time" class="form-control" />
              </div>

              <div class="mb-3"><label class="form-label">Họ tên:</label><input v-model="editFormData.customer_name"
                  required class="form-control" /></div>
              <div class="mb-3"><label class="form-label">SĐT:</label><input v-model="editFormData.customer_phone"
                  class="form-control" /></div>
              <div class="mb-3"><label class="form-label">Email:</label><input v-model="editFormData.customer_email"
                  class="form-control" /></div>
              <div class="mb-3"><label class="form-label">Địa chỉ:</label><input v-model="editFormData.address"
                  class="form-control" /></div>
            </div>
            <div class="modal-footer modal-footer-custom">
              <button type="button" class="btn btn-secondary" @click="showEditForm = false">Hủy</button>
              <button type="submit" class="btn btn-primary">Lưu</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div v-if="futureBookingModal" class="modal-backdrop fade show"></div>
    <div v-if="futureBookingModal" class="modal fade show d-block" tabindex="-1">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content modal-custom">
          <div class="modal-header modal-header-custom">
            <h5 class="modal-title">Danh sách hủy phòng trong tương lai</h5>
            <button type="button" class="btn-close" @click="futureBookingModal = false"></button>
          </div>
          <div class="modal-body p-4">
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th>Phòng</th>
                  <th>Khách</th>
                  <th>Nhận phòng</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="booking in futureBookings" :key="booking.id">
                  <td>{{ booking.room_name }}</td>
                  <td>{{ booking.customer_name }}</td>
                  <td>{{ booking.check_in_time }}</td>
                  <td>
                    <button class="btn btn-sm btn-danger" @click="openCancelNowModal(booking)">Hủy</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal nhập lý do hủy -->
    <div v-if="cancelNowModal" class="modal-backdrop fade show"></div>
    <div v-if="cancelNowModal" class="modal fade show d-block" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-custom">
          <div class="modal-header modal-header-custom">
            <h5 class="modal-title">Xác nhận hủy phòng</h5>
            <button type="button" class="btn-close" @click="cancelNowModal = false"></button>
          </div>
          <div class="modal-body p-4">
            <textarea v-model="cancelNowReason" class="form-control" placeholder="Lý do hủy (tuý chọn)"
              rows="3"></textarea>
          </div>
          <div class="modal-footer modal-footer-custom">
            <button class="btn btn-secondary" @click="cancelNowModal = false">Hủy</button>
            <button class="btn btn-danger" @click="confirmCancelNow">Xác nhận</button>
          </div>
        </div>
      </div>
    </div>
    <!-- Modal Đặt nhiều phòng -->
    <div v-if="showMultiBookingModal" class="modal-backdrop fade show"></div>
    <div v-if="showMultiBookingModal" class="modal fade show d-block" tabindex="-1">
      <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content modal-custom">
          <div class="modal-header modal-header-custom">
            <h5 class="modal-title">Đặt nhiều phòng</h5>
            <button type="button" class="btn-close" @click="showMultiBookingModal = false"></button>
          </div>
          <div class="modal-body p-4">
            <div v-for="(booking, index) in multiBookings" :key="index" class="border rounded p-3 mb-3 bg-light">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <h6>Phòng #{{ index + 1 }}</h6>
                <button class="btn btn-sm btn-outline-danger" @click="removeBooking(index)">Xóa</button>
              </div>
              <div class="row g-3">
                <div class="col-md-3">
                  <label class="form-label">Phòng</label>
                  <select class="form-select" v-model="booking.room_id">
                    <option :value="null">-- Chọn phòng --</option>
                    <option v-for="room in availableRooms" :key="room.room_id" :value="room.room_id">{{ room.number }}
                    </option>
                  </select>
                </div>
                <div class="col-md-3"><label class="form-label">Họ tên</label><input v-model="booking.customer_name"
                    class="form-control" /></div>
                <div class="col-md-3"><label class="form-label">SĐT</label><input v-model="booking.customer_phone"
                    class="form-control" /></div>
                <div class="col-md-3"><label class="form-label">CCCD</label><input v-model="booking.customer_id_number"
                    class="form-control" /></div>
                <div class="col-md-4"><label class="form-label">Email</label><input v-model="booking.customer_email"
                    class="form-control" /></div>
                <div class="col-md-4"><label class="form-label">Ngày nhận</label><input type="date"
                    v-model="booking.check_in_date" class="form-control" /></div>
                <div class="col-md-4"><label class="form-label">Giờ nhận</label><input type="time"
                    v-model="booking.check_in_time" class="form-control" /></div>
                <div class="col-md-4"><label class="form-label">Ngày trả</label><input type="date"
                    v-model="booking.check_out_date" class="form-control" /></div>
                <div class="col-md-4"><label class="form-label">Giờ trả</label><input type="time"
                    v-model="booking.check_out_time" class="form-control" /></div>
                <div class="col-md-4">
                  <label class="form-label">Loại giá</label>
                  <select v-model="booking.pricing_type" class="form-select">
                    <option value="hourly">Theo giờ</option>
                    <option value="nightly">Theo đêm</option>
                  </select>
                </div>
              </div>
            </div>
            <button class="btn btn-outline-secondary" @click="addBooking">+ Thêm phòng khác</button>
          </div>
          <div class="modal-footer modal-footer-custom">
            <button class="btn btn-secondary" @click="showMultiBookingModal = false">Hủy</button>
            <button class="btn btn-success" @click="submitMultiBookings">Xác nhận đặt</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Thanh Toán Nhóm -->
    <!-- Backdrop -->
    <div v-if="showPayGroupModal" class="modal-backdrop fade show"></div>

    <!-- Modal -->
    <div v-if="showPayGroupModal" class="modal fade show d-block" tabindex="-1">
      <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content shadow-lg rounded-3">

          <!-- Header -->
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title fw-bold">
              <i class="bi bi-credit-card-2-front me-2"></i> Thanh toán nhóm
            </h5>
            <button type="button" class="btn-close btn-close-white" @click="showPayGroupModal = false"></button>
          </div>

          <!-- Body -->
          <div class="modal-body p-4 bg-light">

            <!-- Chọn Booking -->
            <label class="form-label fw-semibold">Chọn Booking ID:</label>
            <select class="form-select mb-4" v-model="selectedBookingId" @change="loadBookingGroupRooms">
              <option disabled value="">-- Chọn Booking ID --</option>
              <option v-for="booking in unpaidBookings" :key="booking.booking_id" :value="booking.booking_id">
                {{ booking.booking_id }} - {{ booking.customer_name }}
              </option>
            </select>

            <!-- Danh sách phòng -->
            <div v-if="bookingGroupRooms.length > 0">
              <div v-for="room in bookingGroupRooms" :key="room.booking_detail_id" class="card mb-3 shadow-sm"
                :class="{ 'opacity-50': room.is_paid }">
                <div class="card-header d-flex justify-content-between align-items-center">
                  <span class="fw-bold">
                    <i class="bi bi-door-open me-1"></i>
                    {{ room.room_name }} ({{ room.type_name }})
                  </span>
                  <span v-if="room.is_paid" class="badge bg-success">Đã thanh toán</span>
                </div>

                <div class="card-body bg-white">
                  <!-- Dịch vụ -->
                  <h6 class="fw-semibold mb-2">Dịch vụ:</h6>
                  <div v-for="(service, sIndex) in room.services" :key="sIndex"
                    class="d-flex justify-content-between align-items-center border-bottom py-2">
                    <span>{{ service.service_name }} ({{ formatPrice(service.price) }})</span>
                    <div class="d-flex align-items-center">
                      <button class="btn btn-sm btn-outline-secondary"
                        @click="service.quantity = Math.max(service.quantity - 1, 0)"
                        :disabled="room.is_paid">-</button>
                      <input type="number" v-model.number="service.quantity"
                        class="form-control form-control-sm text-center mx-2" min="0" :disabled="room.is_paid" />
                      <button class="btn btn-sm btn-outline-secondary" @click="service.quantity++"
                        :disabled="room.is_paid">+</button>
                    </div>
                  </div>

                  <!-- Tổng tiền dịch vụ -->
                  <div class="mt-2 text-end fw-bold text-primary">
                    Tổng tiền dịch vụ: {{ formatPrice(calcServiceTotal(room.services)) }}
                  </div>

                  <!-- Phụ thu -->
                  <div class="mt-3">
                    <label class="form-label">Phụ thu:</label>
                    <input type="number" v-model.number="room.additional_fee" class="form-control form-control-sm"
                      placeholder="0" :disabled="room.is_paid" />
                    <label class="form-label mt-2">Lý do phụ thu:</label>
                    <input type="text" v-model="room.surcharge_reason" class="form-control form-control-sm"
                      :disabled="room.is_paid" />
                  </div>
                </div>
              </div>
            </div>
            <div v-else class="alert alert-warning">Chưa có phòng nào trong booking này hoặc đã thanh toán.</div>
          </div>

          <!-- Footer -->
          <div class="modal-footer">
            <button class="btn btn-secondary" @click="showPayGroupModal = false">
              <i class="bi bi-x-circle me-1"></i> Hủy
            </button>
            <button class="btn btn-success" @click="submitPayGroup" :disabled="!bookingGroupRooms.length">
              <i class="bi bi-check-circle me-1"></i> Thanh toán tất cả
            </button>
          </div>
        </div>
      </div>
    </div>

  </template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import axios from 'axios';
import { inject } from 'vue';
import loading from '../loading.vue';

const apiUrl = inject('apiUrl');
const allRooms = ref([]);
const isLoading = ref(false);
const selectedStatus = ref('Tất cả');
const selectedRoomType = ref('Tất cả');
const selectedFloor = ref('Tất cả');
const selectedDate = ref(new Date().toISOString().substr(0, 10));
const selectedTime = ref(new Date().toLocaleTimeString('it-IT', { hour: '2-digit', minute: '2-digit' }));
const showForm = ref(false);
const totalPricePreview = ref(null);
const pricePreviewError = ref('');
const formData = ref({
  customer_name: 'name',
  customer_phone: '0325697601',
  customer_email: 'hxh@gmail.com',
  address: '123 sam son',
  customer_id_number: '123456789122',
  room_id: null,
  check_in_date: '',
  check_in_time: '11:00',
  check_out_date: '',
  check_out_time: '12:00',
  pricing_type: 'hourly'
});
const showEditForm = ref(false);
const editFormData = ref({
  customer_id: null,
  customer_name: 'name',
  customer_phone: '0325697601',
  customer_email: 'hxh@gmail.com',
  address: 'adda',
  check_in_date: '123 sam son',
  check_in_time: '',
  check_out_date: '',
  check_out_time: ''
});

const showServiceModal = ref(false);
const allServices = ref([]);
const currentBookingDetailId = ref(null);
const showGuestModal = ref(false);
const guestInfo = ref({});
const showExtendModal = ref(false);
const extendForm = ref({ booking_detail_id: null, check_out_date: '' });
const additionalFee = ref(0);
const surchargeReason = ref('');
const apiKey = 'XXjjI5g9j7gk4NcZE9Dh9PPLCrvrR6zJ';
const imageFile = ref(null);

const serviceTotal = computed(() => allServices.value.reduce((sum, s) => sum + s.price * s.quantity, 0));
const increaseQty = (index) => { allServices.value[index].quantity++; };
const decreaseQty = (index) => { if (allServices.value[index].quantity > 0) allServices.value[index].quantity--; };
const formatPrice = (price) => price.toLocaleString('vi-VN') + ' VND';

const futureBookingModal = ref(false);
const cancelNowModal = ref(false);
const cancelNowBookingId = ref(null);
const cancelNowReason = ref('');
const futureBookings = ref([]);

const showMultiBookingModal = ref(false);
const multiBookings = ref([]);


// Danh sách phòng còn trống để chọn
const availableRooms = computed(() =>
  allRooms.value.filter(r => r.status === 'Còn trống')
);
const openMultiBookingModal = () => {
  const now = new Date();
  const checkInDate = now.toISOString().slice(0, 10); // yyyy-mm-dd
  const checkInTime = now.toTimeString().slice(0, 5); // hh:mm

  const out = new Date(now.getTime() + 2 * 60 * 60 * 1000); // +2h
  const checkOutDate = out.toISOString().slice(0, 10);
  const checkOutTime = out.toTimeString().slice(0, 5);

  showMultiBookingModal.value = true;
  multiBookings.value = [{
    room_id: null,
    customer_name: 'name',
    customer_phone: '0325697601',
    customer_email: 'hxh@gmail.com',
    customer_id_number: '123456789122',
    check_in_date: checkInDate,
    check_in_time: checkInTime,
    check_out_date: checkOutDate,
    check_out_time: checkOutTime,
    pricing_type: 'hourly'
  }];
};

const addBooking = () => {
  const now = new Date();
  const checkInDate = now.toISOString().slice(0, 10);
  const checkInTime = now.toTimeString().slice(0, 5); // giờ hiện tại

  const out = new Date(now.getTime() + 2 * 60 * 60 * 1000); // +2 tiếng
  const checkOutDate = out.toISOString().slice(0, 10);
  const checkOutTime = out.toTimeString().slice(0, 5);

  // multiBookings.value.push({
  //   room_id: null,
  //   customer_name: 'name',
  //   customer_phone: '0325697601',
  //   customer_email: 'hxh@gmail.com',
  //   customer_id_number: '123456789122',
  //   check_in_date: checkInDate,
  //   check_in_time: checkInTime,
  //   check_out_date: checkOutDate,
  //   check_out_time: checkOutTime,
  //   pricing_type: 'hourly'
  // });
  const base = multiBookings.value[0];
   multiBookings.value.push({
      ...base,
      room_id: null, // reset lại phòng
    });
};


const removeBooking = (index) => {
  multiBookings.value.splice(index, 1);
};
const submitMultiBookings = async () => {
  if (multiBookings.value.length === 0) {
    return alert("Bạn cần thêm ít nhất 1 phòng!");
  }

  if (!window.confirm("Xác nhận đặt nhiều phòng?")) return;

  try {
    const now = new Date();
    const defaultCheckInTime = now.toTimeString().slice(0, 5);

    const out = new Date(now.getTime() + 2 * 60 * 60 * 1000);
    const defaultCheckOutTime = out.toTimeString().slice(0, 5);

    const payload = {
      bookings: multiBookings.value.map(b => ({
        room_id: b.room_id,
        customer_name: b.customer_name,
        customer_phone: b.customer_phone,
        customer_email: b.customer_email,
        customer_id_number: b.customer_id_number,
        check_in_date: b.check_in_date,
        check_in_time: b.check_in_time || defaultCheckInTime,
        check_out_date: b.check_out_date,
        check_out_time: b.check_out_time || defaultCheckOutTime,
        pricing_type: b.pricing_type
      }))
    };

    const res = await axios.post(`${apiUrl}/api/occupancy/add-multiple`, payload);
    console.log('Đặt nhiều phòng thành công:', res.data);
    alert(res.data.message + '\nMã booking nhóm: ' + res.data.booking_id);
    showMultiBookingModal.value = false;
    multiBookings.value = [];
    await fetchRooms();
    // window.location.reload();

  } catch (e) {
    console.error('Lỗi đặt nhiều phòng:', e);
    alert(e.response?.data?.message || 'Lỗi khi đặt nhiều phòng.');
  }
};




const openFutureBookings = async () => {
  try {
    const res = await axios.get(`${apiUrl}/api/occupancy/future-bookings`);
    futureBookings.value = res.data;
    futureBookingModal.value = true;
  } catch (err) {
    alert('Lỗi khi tải danh sách hủy phòng trước.');
  }
};

const openCancelNowModal = (booking) => {
  cancelNowBookingId.value = booking.id;
  cancelNowReason.value = '';
  cancelNowModal.value = true;
};

const confirmCancelNow = async () => {
  try {
    await axios.post(`${apiUrl}/api/occupancy/cancel-now`, {
      detail_id: cancelNowBookingId.value,
      reason: cancelNowReason.value
    });
    cancelNowModal.value = false;
    futureBookingModal.value = false;
    alert('Hủy phòng thành công!');
    await fetchRooms();
  } catch (err) {
    alert(err.response?.data?.message || 'Lỗi khi hủy phòng!');
  }
};

const formatDateTime = (date, time) => {
  if (!date || !time) return 'N/A';
  return `${date} ${time}`;
};

const editCustomerInfo = (customer) => {
  if (!customer || !guestInfo.value.booking) return;
  const booking = guestInfo.value.booking;

  editFormData.value = {
    customer_id: customer.customer_id,
    customer_name: customer.customer_name || '',
    customer_phone: customer.customer_phone || '',
    customer_email: customer.customer_email || '',
    address: customer.address || '',
    check_in_date: booking.check_in_date || '',
    check_in_time: booking.check_in_time || '',
    check_out_date: booking.check_out_date || '',
    check_out_time: booking.check_out_time || '',
  };

  showGuestModal.value = false;
  showEditForm.value = true;
};


const submitEditForm = async () => {
  try {
    const isValidTime = (val) => typeof val === 'string' && /^\d{2}:\d{2}$/.test(val);

    const checkInTime = isValidTime(editFormData.value.check_in_time) ? editFormData.value.check_in_time : '14:00';
    const checkOutTime = isValidTime(editFormData.value.check_out_time) ? editFormData.value.check_out_time : '12:00';
    const res = await axios.post(`${apiUrl}/api/bookings/${guestInfo.value.booking.booking_id}/update-time`, {
      check_in_date: editFormData.value.check_in_date,
      check_in_time: checkInTime,
      check_out_date: editFormData.value.check_out_date,
      check_out_time: checkOutTime,

      customer_name: editFormData.value.customer_name,
      customer_phone: editFormData.value.customer_phone,
      customer_email: editFormData.value.customer_email,
      address: editFormData.value.address,
    });

    alert('Cập nhật thành công!\n' + res.data.message);
    showEditForm.value = false;
    await fetchRooms();
  } catch (e) {
    console.error("Lỗi cập nhật:", e);
    alert(e.response?.data?.message || "Không thể cập nhật thông tin.");
  }
};

const onFileChange = (e) => {
  const file = e.target.files[0];
  if (file) imageFile.value = file;
};

const uploadImage = async () => {
  if (!imageFile.value) return alert('Chọn ảnh CCCD trước!');
  isLoading.value = true;
  try {
    const formDataSend = new FormData();
    formDataSend.append('image', imageFile.value);
    const res = await fetch('https://api.fpt.ai/vision/idr/vnm/', { method: 'POST', headers: { api_key: apiKey }, body: formDataSend });
    const data = await res.json();
    if (data && data.data && data.data.length > 0) {
      const d = data.data[0];
      formData.value.customer_name = d.name || '';
      formData.value.address = d.home || '';
      formData.value.customer_id_number = d.id || '';
      alert('Lấy thông tin từ CCCD thành công!');
    } else alert('Không nhận diện được CCCD!');
  } catch (e) {
    console.error('Lỗi gửi CCCD:', e);
    alert('Đã xảy ra lỗi khi gửi ảnh.');
  } finally {
    isLoading.value = false;
  }
};

const checkoutRoom = async (room) => {
  if (!room.booking_detail_id) {
    alert('Không tìm thấy thông tin đặt phòng cho phòng này.');
    return;
  }
  currentBookingDetailId.value = room.booking_detail_id;
  try {
    const res = await axios.get(`${apiUrl}/api/services/indexAllService`);
    allServices.value = res.data.map(service => ({ ...service, price: Number(service.price) || 0, quantity: 0 }));
    additionalFee.value = 0;
    surchargeReason.value = '';
    showServiceModal.value = true;
  } catch (error) {
    console.error("Không thể tải dịch vụ:", error);
    alert("Không thể tải danh sách dịch vụ.");
  }
};
const updateRoomInGroups = (roomId) => {
  // console.log("Updating room status for roomId:", roomId);

  // Sửa trực tiếp vào filteredRooms (nguồn gốc của groupedAndSortedRooms)
  const room = filteredRooms.value.find(r => r.room_id === roomId);
  console.log("Updating room in filteredRooms:", room);

  if (room) {
    //console.log("Found room in filteredRooms:", room);
    room.status = 'Còn trống';
    room.booking_detail_id = null;
  }
};


const confirmPayment = async () => {
  if (!window.confirm("Xác nhận thanh toán và trả phòng?")) return;
  try {
    const services = allServices.value
      .filter(s => Number(s.quantity) > 0)
      .map(s => ({ service_id: Number(s.service_id), quantity: Number(s.quantity) }));

    const response = await axios.post(`${apiUrl}/api/booking-details/${currentBookingDetailId.value}/checkout`, {
      services,
      additional_fee: additionalFee.value,
      surcharge_reason: surchargeReason.value
    });
    const data = response.data;
    console.log("Thanh toán thành công:", data.booking_detail_id);

    updateRoomInGroups(data.room_id);

    //console.log("Thanh toán thành công:", data.room_id);

    const alertMessage = [
      data.message,
      `\n--------------------------------`,
      `🛏️ Tiền phòng: ${Number(data.room_total || 0).toLocaleString('vi-VN')} VND`,
      `   ➡️ Cách tính: ${data.calculation_note || 'N/A'}`,
      `🧾 Tiền dịch vụ: ${Number(data.service_total || 0).toLocaleString('vi-VN')} VND`,
      `➕ Phí phụ thu: ${Number(data.additional_fee || 0).toLocaleString('vi-VN')} VND` + (data.surcharge_reason ? ` (Lý do: ${data.surcharge_reason})` : ''),
      `--------------------------------`,
      `💳 TỔNG THANH TOÁN: ${Number(data.actual_total || 0).toLocaleString('vi-VN')} VND`,
      data.booking_completed ? `📋 TỔNG TIỀN BOOKING: ${Number(data.booking_total || 0).toLocaleString('vi-VN')} VND` : '',
      `\n📝 Ghi chú: ${data.note || 'Không có'}`
    ].filter(line => line).join('\n');

    alert(alertMessage);

    showServiceModal.value = false;
    const now = new Date();
    selectedDate.value = now.toISOString().slice(0, 10);
    selectedTime.value = now.toTimeString().slice(0, 5);
    await fetchRooms();
  } catch (error) {
    console.error("Lỗi thanh toán:", error);
    const errorMessage = error.response?.data?.message || "Không thể thanh toán phòng.";
    alert(errorMessage);
  }
};

const mapApiStatusToVietnamese = (s) => {
  if (s === 'available') return 'Còn trống';
  if (s === 'occupied' || s === 'pending' || s === 'confirmed' || s === 'cancellation_requested') return 'Đã đặt';
  return 'Không xác định';
};

const mapBedCountToString = (c) => c === 1 ? 'Giường đơn' : c === 2 ? 'Giường đôi' : `${c} giường`;

const fetchRooms = async () => {
  isLoading.value = true;
  try {
    let timeValue = selectedTime.value;

    if (timeValue) {
      let date = new Date(`1970-01-01T${timeValue}:00`);
      date.setMinutes(date.getMinutes() + 4);

      // format lại thành HH:mm
      let hours = String(date.getHours()).padStart(2, '0');
      let minutes = String(date.getMinutes()).padStart(2, '0');
      timeValue = `${hours}:${minutes}`;
    }

    const res = await axios.get(`${apiUrl}/api/occupancy/by-date`, {
      params: {
        date: selectedDate.value,
        time: timeValue || undefined
      }
    });
    console.log(timeValue);
    
    allRooms.value = res.data.map(r => ({
      room_id: r.room_id,
      number: r.room_name,
      floor: r.floor_number,
      status: mapApiStatusToVietnamese(r.status),
      type: r.type_name,
      bedSize: mapBedCountToString(r.bed_count),
      max_occupancy: r.max_occupancy || 0,
      max_occupancy_child: r.max_occupancy_child || 0,
      booking_detail_id: r.booking_detail_id || null,
      payment_status: r.payment_status || 'pending',
      group_id: r.group_id || null,
      room_type: r.type_id || null,
    }));
  } catch (e) {
    console.error("Lỗi load phòng:", e);
    alert("Không thể tải thông tin phòng. Vui lòng thử lại.");
  } finally {
    isLoading.value = false;
  }
};

const showAddGuest = (room_id) => {
  const now = new Date();
  const checkInDate = now.toISOString().slice(0, 10);
  const checkInTime = now.toTimeString().slice(0, 5); // 'HH:mm'

  const out = new Date(now.getTime() + 2 * 60 * 60 * 1000);
  const checkOutTime = out.toTimeString().slice(0, 5);
  const checkOutDate = out.toISOString().slice(0, 10);

  formData.value = {
    customer_name: '',
    customer_phone: '',
    customer_email: '',
    address: '',
    customer_id_number: '',
    room_id,
    check_in_date: checkInDate,
    check_in_time: checkInTime,
    check_out_date: checkOutDate,
    check_out_time: checkOutTime,
    pricing_type: 'hourly'
  };

  showForm.value = true;
  pricePreviewError.value = '';
  calculateTotalPricePreview();
};

const submitCustomerForm = async () => {
  if (!window.confirm("Xác nhận lưu khách hàng?")) return;
  try {
    const res = await axios.post(`${apiUrl}/api/rooms/${formData.value.room_id}/add-guest`, {
      ...formData.value,
      check_in_time: formData.value.check_in_time || '14:00',
      check_out_time: formData.value.check_out_time || '12:00'
    });
    alert(`${res.data.message}\nTổng tiền: ${Number(res.data.total_price).toLocaleString('vi-VN')} VND`);
    showForm.value = false;
    await fetchRooms();
  } catch (e) {
    console.error("Lỗi gửi dữ liệu:", e);
    const errorMessage = e.response?.data?.message || "Không thể lưu thông tin khách.";
    alert(errorMessage);
  }
};

const calculateTotalPricePreview = async () => {
  if (!formData.value.room_id || !formData.value.check_in_date || !formData.value.check_out_date) {
    totalPricePreview.value = null;
    pricePreviewError.value = 'Vui lòng nhập đầy đủ thông tin phòng và thời gian.';
    return;
  }
  try {
    const res = await axios.post(`${apiUrl}/api/rooms/preview-price`, {
      ...formData.value,
      check_in_time: formData.value.check_in_time || '14:00',
      check_out_time: formData.value.check_out_time || '12:00',
      is_extend: false
    });
    if (res.data.total_price && isFinite(res.data.total_price)) {
      totalPricePreview.value = res.data.total_price;
      pricePreviewError.value = '';
    } else {
      totalPricePreview.value = null;
      pricePreviewError.value = 'API trả về giá không hợp lệ.';
      console.error('API trả về total_price không hợp lệ:', res.data);
    }
  } catch (e) {
    totalPricePreview.value = null;
    pricePreviewError.value = e.response?.data?.message || 'Lỗi tính giá phòng. Vui lòng kiểm tra thời gian đặt phòng.';
    console.error('Lỗi tính giá:', e.response?.data?.message || e.message);
    if (e.response?.status === 422) {
      alert('Thời gian đặt phòng không hợp lệ: ' + (e.response.data.message || 'Vui lòng kiểm tra lại.'));
    }
  }
};

const showGuestDetails = async (room) => {
  try {
    const res = await axios.get(`${apiUrl}/api/rooms/${room.room_id}/customer`, {
      params: {
        date: selectedDate.value,
        time: selectedTime.value,
      },
    });
    guestInfo.value = res.data;
    showGuestModal.value = true;
  } catch (e) {
    if (e.response?.status === 404) {
      alert("Không tìm thấy thông tin khách tại thời điểm này.");
    } else {
      alert("Lỗi khi lấy thông tin khách: " + (e.response?.data?.message || 'Lỗi server.'));
      console.error('Lỗi showGuestDetails:', e);
    }
  }
};


const submitExtendForm = async () => {
  try {
    const res = await axios.post(`${apiUrl}/api/booking-details/${extendForm.value.booking_detail_id}/extend`, { ...extendForm.value });
    alert(res.data.message + '\nTổng tiền mới: ' + res.data.total_price);
    showExtendModal.value = false;
    await fetchRooms();
  } catch (e) {
    console.error("Lỗi gia hạn:", e);
    const errorMessage = e.response?.data?.message || "Không thể gia hạn.";
    alert(errorMessage);
  }
};

const getActualCheckout = (booking) => {
  if (!booking || !booking.actual_check_out_time) return 'Chưa trả';
  return booking.actual_check_out_time;
};

const roomTypes = computed(() => ['Tất cả', ...[...new Set(allRooms.value.map(r => r.type))].sort()]);
const floors = computed(() => ['Tất cả', ...[...new Set(allRooms.value.map(r => r.floor))].sort((a, b) => a - b).map(f => `Tầng ${f}`)]);
const filteredRooms = computed(() => allRooms.value.filter(r =>
  (selectedStatus.value === 'Tất cả' || r.status === selectedStatus.value) &&
  (selectedRoomType.value === 'Tất cả' || r.type === selectedRoomType.value) &&
  (selectedFloor.value === 'Tất cả' || `Tầng ${r.floor}` === selectedFloor.value)
));
const groupedAndSortedRooms = computed(() => {
  const groups = {};
  for (const room of filteredRooms.value) {
    if (!groups[room.floor]) groups[room.floor] = [];
    groups[room.floor].push(room);
  }
 // console.log('Grouped rooms:', groups);
  return Object.keys(groups).sort((a, b) => a - b).map(f => ({ floor: f, rooms: groups[f] }));
});

const clearFilters = () => {
  selectedStatus.value = 'Tất cả';
  selectedRoomType.value = 'Tất cả';
  selectedFloor.value = 'Tất cả';
  selectedTime.value = new Date().toLocaleTimeString('it-IT', { hour: '2-digit', minute: '2-digit' });
  fetchRooms();
};

onMounted(fetchRooms);
watch(() => [formData.value.check_in_date, formData.value.check_in_time, formData.value.check_out_date, formData.value.check_out_time, formData.value.pricing_type, formData.value.room_id], calculateTotalPricePreview, { deep: true });
watch(() => [selectedDate, selectedTime], fetchRooms);

const showPayGroupModal = ref(false);
const selectedBookingId = ref('');
const unpaidBookings = ref([]);
const bookingGroupRooms = ref([]);

// Mở modal
const openPayGroupModal = async () => {
  try {
    const res = await axios.get(`${apiUrl}/api/bookings/unpaid-list`);
    unpaidBookings.value = res.data;
    selectedBookingId.value = '';
    bookingGroupRooms.value = [];
    showPayGroupModal.value = true;
  } catch (e) {
    alert("Lỗi khi tải danh sách booking chưa thanh toán.");
  }
};

// Tính tổng tiền dịch vụ của 1 phòng
const calcServiceTotal = (services) =>
  services.reduce((sum, s) => sum + (s.price * (s.quantity || 0)), 0);
// Tải các phòng thuộc booking_id
const loadBookingGroupRooms = async () => {
  try {
    const [detailsRes, servicesRes] = await Promise.all([
      axios.get(`${apiUrl}/api/bookings/${selectedBookingId.value}/details`),
      axios.get(`${apiUrl}/api/services/indexAllService`)
    ]);

    const services = servicesRes.data.map(s => ({
      ...s,
      price: Number(s.price) || 0,
      quantity: 0
    }));

    bookingGroupRooms.value = detailsRes.data.map(room => ({
      ...room,
      services: JSON.parse(JSON.stringify(services)), // clone độc lập cho từng phòng
      additional_fee: 0,
      surcharge_reason: ''
    }));
  } catch (e) {
    console.error("Lỗi load booking group rooms:", e);
    alert("Không thể tải danh sách phòng hoặc dịch vụ.");
  }
};
const submitPayGroup = async () => {
  if (!selectedBookingId.value) {
    alert("Vui lòng chọn một Booking ID!");
    return;
  }
  if (bookingGroupRooms.value.length === 0) {
    alert("Không có phòng nào để thanh toán.");
    return;
  }
  if (!window.confirm("Bạn có chắc chắn muốn thanh toán tất cả các phòng trong nhóm này?")) return;
  try {
    const payload = {
      booking_id: selectedBookingId.value,
      rooms: bookingGroupRooms.value.map(room => ({
        booking_detail_id: room.booking_detail_id,
        services: room.services.filter(s => s.quantity > 0).map(s => ({
          service_id: s.service_id,
          quantity: s.quantity
        })),
        additional_fee: Number(room.additional_fee || 0),
        surcharge_reason: room.surcharge_reason || ''
      }))
    };
    console.log("Submitting pay group with payload:", payload);


    const res = await axios.patch(`${apiUrl}/api/bookings/pay-by-booking`, payload);

    alert(res.data.message || "Thanh toán nhóm thành công!");
    showPayGroupModal.value = false;
    selectedBookingId.value = '';
    bookingGroupRooms.value = [];

    await fetchRooms(); // Cập nhật lại trạng thái phòng
  } catch (error) {
    console.error("Lỗi thanh toán nhóm:", error);
    const errMsg = error.response?.data?.message || 'Lỗi khi thanh toán nhóm.';
    alert(errMsg);
  }
};

const showPopupLeaveRoom = ref(false)
const availableRoomsLeaveRoom = ref([])
const selectedRoomLeaveRoom = ref(null)
const booking_detail_idLeaveRoom = ref(null);
const openRoomChangePopup = async (room) => {
  booking_detail_idLeaveRoom.value = room.booking_detail_id;
  console.log("Opening room change popup for room:", room);
  console.log("Rời phòng:", room.room_type, "Room ID:", room.room_id);

  try {
    const { data } = await axios.get(
      `${apiUrl}/api/rooms/availableleaveroom/${room.room_type}/${room.room_id}`
    );

    // Loại bỏ phòng hiện tại
    availableRoomsLeaveRoom.value = data.filter(r => r.room_id !== room.room_id);

    console.log("Available rooms for change:", availableRoomsLeaveRoom.value);

    showPopupLeaveRoom.value = true;
  } catch (error) {
    console.error(error);
    alert("Không thể tải danh sách phòng");
  }
};

const selectRoom = async (room) => {
  console.log("Sbooking_detail_idLeaveRoom:", booking_detail_idLeaveRoom.value);
  console.log("Selected room for change:", room);
  console.log("room_id new:", room.room_id);

  // Hiển thị popup nhập lý do
  const reason = prompt("Nhập lý do đổi phòng:");
  if (reason === null || reason.trim() === "") {
    return alert("Bạn phải nhập lý do để đổi phòng!");
  }

  try {
    const res = await axios.post(`${apiUrl}/api/change-room`, {
      booking_detail_id: booking_detail_idLeaveRoom.value,
      new_room_id: room.room_id,
      reason: reason.trim() // gửi thêm lý do
    });

    if (res.data.success) {
      alert('Đổi phòng thành công!');
      // Optional: Cập nhật lại UI hoặc gọi API load data mới
      fetchRooms();
      showPopupLeaveRoom.value = false;
    }
  } catch (error) {
    console.error(error);
    alert('Có lỗi xảy ra khi đổi phòng!');
  }
};



</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700&display=swap');
@import url('https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css');

.page-container {
  font-family: 'Be Vietnam Pro', sans-serif;
  background-color: #f4f7f9;
  padding: 2rem;
  color: #34495e;
}

.page-header {
  border-bottom: 1px solid #e5eaee;
  padding-bottom: 1rem;
}

.page-title {
  font-size: 2rem;
  font-weight: 700;
}

.page-subtitle {
  font-size: 1rem;
  color: #7f8c8d;
}

.filter-card {
  background-color: #ffffff;
  border: none;
  border-radius: 12px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
}

.form-label {
  font-weight: 500;
  margin-bottom: 0.5rem;
  font-size: 0.875rem;
}

.form-control,
.form-select {
  border-radius: 8px;
  border: 1px solid #e5eaee;
  transition: all 0.2s ease-in-out;
}

.form-control:focus,
.form-select:focus {
  border-color: #3498db;
  box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.15);
}

.form-control[type="time"] {
  width: 100%;
}

.floor-section {
  margin-bottom: 2.5rem;
}

.floor-header {
  font-size: 1.75rem;
  font-weight: 600;
  color: #2c3e50;
  padding-bottom: 0.75rem;
  border-bottom: 2px solid #e5eaee;
  margin-bottom: 1.5rem;
}

.room-grid {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 1.5rem;
}

@media (max-width: 1200px) {
  .room-grid {
    grid-template-columns: repeat(4, 1fr);
  }
}

@media (max-width: 992px) {
  .room-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (max-width: 768px) {
  .room-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

.room-capacity {
  font-size: 0.8rem;
  color: #7f8c8d;
  margin-bottom: 0;
  display: flex;
  align-items: center;
  gap: 0.4rem;
}

.room-capacity .bi {
  color: #3498db;
}

.room-card {
  background-color: #ffffff;
  border-radius: 12px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
  border: 1px solid #e5eaee;
  transition: all 0.2s ease-in-out;
  display: flex;
  flex-direction: column;
}

.room-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
}

.room-card.booked {
  border-left: 4px solid #f39c12;
}

.room-card:not(.booked) {
  border-left: 4px solid #3498db;
}

.badge-available {
  background-color: #eaf6fb;
  color: #3498db;
}

.badge-booked {
  background-color: #fef5e7;
  color: #f39c12;
}

.badge-paid {
  background-color: #d4edda;
  color: #155724;
  margin-left: 0.5rem;
}

.room-card .card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 1rem;
  border-bottom: 1px solid #f0f2f5;
}

.room-card .room-number {
  font-size: 1.5rem;
  font-weight: 700;
  margin: 0;
  color: #2c3e50;
}

.room-card .badge {
  padding: 0.4em 0.8em;
  font-size: 0.7rem;
  font-weight: 600;
  border-radius: 20px;
}

.room-card .card-body {
  padding: 1rem;
  flex-grow: 1;
}

.room-card .room-type {
  font-weight: 600;
  margin-bottom: 0.25rem;
}

.room-card .room-bedsize {
  font-size: 0.85rem;
  color: #7f8c8d;
  margin-bottom: 0;
}

.room-card .card-footer {
  background-color: #fafbfc;
  padding: 0.75rem 1rem;
  border-top: 1px solid #f0f2f5;
}

.action-grid .action-row {
  display: flex;
  gap: 0.5rem;
}

.action-grid .action-row .btn {
  flex-grow: 1;
}

.modal-backdrop {
  background-color: rgba(0, 0, 0, 0.4);
}

.modal-custom {
  border-radius: 16px;
  border: none;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.modal-header-custom {
  background-color: #f4f7f9;
  border-bottom: 1px solid #e5eaee;
  padding: 1.5rem;
}

.modal-footer-custom {
  background-color: #f4f7f9;
  border-top: 1px solid #e5eaee;
  padding: 1rem 1.5rem;
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

.info-list li {
  display: flex;
  justify-content: space-between;
  align-items: baseline;
  gap: 1rem;
  padding: 0.6rem 0.5rem;
  border-bottom: 1px solid #e5eaee;
  font-size: 0.9rem;
}

.info-list li:last-child {
  border-bottom: none;
}

.info-list li span {
  color: #7f8c8d;
  flex-shrink: 0;
}

.info-list li strong {
  color: #34495e;
  text-align: right;
  word-break: break-word;
}

.btn-primary {
  background-color: #3498db;
  border-color: #3498db;
}

.btn-primary:hover {
  background-color: #2980b9;
  border-color: #2980b9;
}

.btn-outline-primary {
  color: #3498db;
  border-color: #3498db;
}

.btn-outline-primary:hover {
  background-color: #3498db;
  color: white;
}

.btn-outline-warning {
  color: #f39c12;
  border-color: #f39c12;
}

.btn-outline-warning:hover {
  background-color: #f39c12;
  color: white;
}

.service-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.5rem 0;
}

.qty-controls {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.qty-controls input {
  width: 50px;
}
</style>