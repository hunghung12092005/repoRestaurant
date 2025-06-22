<template>
  <div class="main-wrapper">
    <!-- Banner -->
    <div class="banner">Đặt Phòng </div>

    <!-- Progress Steps -->
    <div class="progress-steps container">
      <div class="step"
        :class="{ active: currentStep >= 1 || completedSteps.includes(1), completed: completedSteps.includes(1) }"
        @click="setStep(1)" role="button" tabindex="0" aria-label="Bước 1: Chọn Ngày">
        <span class="step-number" :class="{ completed: completedSteps.includes(1) }">1</span>
        <span class="step-label">Chọn Ngày</span>
      </div>
      <div class="step-connector" :class="{ active: currentStep > 1 || completedSteps.includes(2) }"
        role="presentation"></div>
      <div class="step"
        :class="{ active: currentStep >= 2 || completedSteps.includes(2), completed: completedSteps.includes(2) }"
        @click="setStep(2)" role="button" tabindex="0" aria-label="Bước 2: Chọn Phòng">
        <span class="step-number" :class="{ completed: completedSteps.includes(2) }">2</span>
        <span class="step-label">Chọn Phòng</span>
      </div>
      <div class="step-connector" :class="{ active: currentStep > 2 || completedSteps.includes(3) }"
        role="presentation"></div>
      <div class="step"
        :class="{ active: currentStep >= 3 || completedSteps.includes(3), completed: completedSteps.includes(3) }"
        @click="setStep(3)" role="button" tabindex="0" aria-label="Bước 3: Đặt Phòng">
        <span class="step-number" :class="{ completed: completedSteps.includes(3) }">3</span>
        <span class="step-label">Đặt Phòng</span>
      </div>
      <div class="step-connector" :class="{ active: currentStep > 3 || completedSteps.includes(4) }"
        role="presentation"></div>
      <div class="step"
        :class="{ active: currentStep >= 4 || completedSteps.includes(4), completed: completedSteps.includes(4) }"
        @click="setStep(4)" role="button" tabindex="0" aria-label="Bước 4: Xác Nhận">
        <span class="step-number" :class="{ completed: completedSteps.includes(4) }">4</span>
        <span class="step-label">Xác Nhận</span>
      </div>
    </div>

    <!-- Quy tắc chung khi đặt phòng -->
    <div class="booking-rules container mb-4">
      <div class="card" v-if="showRules">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h3 class="mb-0">Quy tắc chung khi đặt phòng</h3>
          <button class="btn btn-info" @click="toggleRules(false)" aria-label="Ẩn Quy tắc">
            Đã Hiểu - Ẩn Quy Tắc
          </button>
        </div>
        <div class="card-body">
          <h5>Quy định về giờ nhận/ trả phòng:</h5>
          <ul>
            <li>Giờ nhận phòng cố định của khách sạn là sau 12:00 chiều</li>
            <li>Giờ trả phòng cố định của khách sạn là 12:00 trưa</li>
            <li>Khách hàng có thể nhận phòng sớm hơn quy định trong điều kiện khách sạn có phòng sẵn và lưu trú đủ 24
              tiếng tính từ khi khách nhận phòng đến khi trả phòng. Đối với những trường hợp nhận phòng sớm này, A25
              khuyến khích quý khách nên đặt phòng cả đêm trước đó để đảm bảo luôn có phòng sẵn sàng.</li>
            <li>Phòng nghỉ giờ: Áp dụng cho Khách hàng lưu trú 2 tiếng trở xuống, nếu nghỉ quá 2 tiếng sẽ tính giá thêm
              giờ theo bảng giá hiện hành. Trong trường hợp vượt quá giá ngày đêm sẽ tính giá ngày đêm.</li>
            <li>Phòng ngày đêm: Áp dụng cho Khách hàng lưu trú 24 tiếng hoặc phòng nghỉ giờ</li>
            <li>Thêm giờ bằng giá ngày đêm.</li>
            <li>Trả phòng sau 03h00 sáng hoặc nhận phòng trước 06h00 sáng nếu Khách hàng lưu trú từ 5 tiếng trở xuống sẽ
              tính phòng nghỉ giờ cộng thêm giờ, lưu trú trên 05 tiếng sẽ tính giá ngày đêm, lưu trú đến khi đủ 24
              tiếng.</li>
            <li>Khách hàng ở lưu quá 24 tiếng giờ sẽ cộng tiền thêm giờ, nếu tiền thêm giờ bằng giá ngày đêm sẽ tính
              tiền phòng ngày đêm cho đến khi đủ 24 tiếng tiếp theo.</li>
          </ul>
          <h5>Quy định hủy phòng:</h5>
          <ul>
            <li>Báo hủy từ 1 tháng trở lên không thu phí</li>
            <li>Báo hủy từ 10 ngày đến dưới 1 tháng bồi thường 20% tổng giá trị hợp đồng</li>
            <li>Báo hủy từ 3 đến dưới 10 ngày bồi thường 50% giá trị hợp đồng</li>
            <li>Báo hủy dưới 3 ngày dự kiến hoặc đã đặt nhưng không đến: bồi thường 100% tổng giá trị hợp đồng (Đối với
              cả booking OTA)</li>
            <li>Với các khách đặt trong ngày và dự kiến check in trong ngày, khi khách báo hủy trước check in thu phí
              bồi thường 50% tổng tiền đêm đầu tiên khách dự kiến lưu trú</li>
          </ul>
        </div>
      </div>
      <div v-else class="text-center">
        <button class="btn-main" @click="toggleRules(true)" aria-label="Xem Quy tắc">
          Xem quy tắc
        </button>
      </div>
    </div>
    <!-- <sliderComponent/> -->

    <!-- Main Content -->
    <div class="container">
      <!-- Chọn Ngày View Bước 1-->
      <div v-if="currentStep === 1" class="content-section">
        <div class="row justify-content-center">
          <!-- Thông tin Đặt phòng (Trái, 1/3 chiều rộng) -->
          <div class="col-md-4 reservation-section">
            <h2 class="section-title">Thông tin Đặt phòng</h2>
            <form @submit.prevent>
              <div class="mb-3">
                <label for="checkIn" class="form-label">Nhận phòng <span class="text-muted small">(Bắt
                    buộc)</span></label>
                <input type="text" class="form-control" id="checkIn" v-model="checkIn" readonly
                  aria-describedby="checkInHelp">
                <small id="checkInHelp" class="form-text text-muted">Nhấp vào lịch để chọn ngày.</small>
              </div>
              <div class="mb-3">
                <label for="checkOut" class="form-label">Trả phòng <span class="text-muted small">(Bắt
                    buộc)</span></label>
                <input type="text" class="form-control" id="checkOut" v-model="checkOut" readonly
                  aria-describedby="checkOutHelp">
                <small id="checkOutHelp" class="form-text text-muted">Chọn ngày sau Nhận phòng.</small>
              </div>
              <!-- <div class="mb-3">
                <label for="rooms" class="form-label">Số phòng <span class="text-muted small">(Bắt buộc)</span></label>
                <select class="form-select" v-model="rooms" aria-describedby="roomsHelp">
                  <option v-for="n in 5" :value="n" :key="n">{{ n }}</option>
                  <small id="roomsHelp" class="form-text text-muted">Tối đa 5 phòng.</small>
                </select>
              </div>
              <div class="mb-3">
                <label for="adults" class="form-label">Người lớn <span class="text-muted small">(Bắt buộc)</span></label>
                <select class="form-select" v-model="adults" aria-describedby="adultsHelp">
                  <option v-for="n in 10" :value="n" :key="n">{{ n }}</option>
                  <small id="adultsHelp" class="form-text text-muted">Tối đa 10 người lớn.</small>
                </select>
              </div>
              <div class="mb-3">
                <label for="children" class="form-label">Trẻ em</label>
                <select class="form-select" v-model="children" aria-describedby="childrenHelp">
                  <option v-for="n in 10" :value="n" :key="n">{{ n }}</option>
                  <small id="childrenHelp" class="form-text text-muted">Tối đa 10 trẻ em.</small>
                </select>
              </div> -->
              <button type="button" class="btn btn-primary w-100 mt-3" @click.prevent="nextStep"
                :disabled="!isReservationComplete" aria-label="Kiểm tra Tình trạng">Tiến Hành Chọn Phòng</button>
              <div v-if="!isReservationComplete" class="text-danger small mt-2">Vui lòng điền đầy đủ các trường bắt buộc
                để tiếp tục.</div>
            </form>
          </div>

          <!-- Lịch (Phải, 2/3 chiều rộng) -->
          <div class="col-md-8 calendar-section">
            <h2 class="section-title">Lịch</h2>
            <div class="mb-3 d-flex align-items-center justify-content-between">
              <button class="btn btn-link" @click="prevMonth" aria-label="Tháng Trước">
                <i class="bi bi-chevron-left"></i>
              </button>
              <span class="calendar-title">{{ currentMonth }} {{ currentYear }}</span>
              <button class="btn btn-link" @click="nextMonth" aria-label="Tháng Sau">
                <i class="bi bi-chevron-right"></i>
              </button>
            </div>
            <div class="calendar-header">
              <div v-for="day in days" :key="day" class="day-header">{{ day }}</div>
            </div>
            <div class="calendar-grid">
              <div v-for="day in calendarDays" :key="day.date" class="calendar-day" :class="{
                'past': day.isPast,
                'today': day.isToday,
                'selected': isSelected(day.date),
                'in-range': isInRange(day.date)
              }" @click="selectDate(day.date)" @keydown.enter="selectDate(day.date)" role="button" tabindex="0"
                :aria-label="`Ngày ${day.day}, ${day.isPast ? 'quá khứ' : day.isToday ? 'hôm nay' : ''}`">
                {{ day.day }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Chọn Phòng View -->
      <div v-if="currentStep === 2" class="content-section">
        <div class="row">

          <!-- Chọn Loại Phòng (Phải, 2/3 chiều rộng) -->
          <div class="col-md-8 room-selection-section">
            <div class="row row-cols-1 row-cols-md-2 g-4">
              <div v-for="type in roomTypes" :key="type.id" class="col">
                <div class="card h-100">
                  <iframe width="100%" height="315" src="https://www.youtube.com/embed/1IbCt-nY09Q?si=VNRifz2UegA4rH-R"
                    title="YouTube video player" frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                    referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                  <!-- <iframe width="100%" height="315"
                    src="https://www.youtube.com/embed/1IbCt-nY09Q?si=VNRifz2UegA4rH-R&autoplay=1"
                    title="YouTube video player" frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                    referrerpolicy="strict-origin-when-cross-origin" allowfullscreen>
                  </iframe> -->
                  <div class="card-body">
                    <div class="room-info">
                      <div class="room-stats">
                        <span class="room-stat"><i class="bi bi-arrow"></i> {{ type.beds }} Giường lớn</span>
                        <span class="room-stat"><i class="bi bi-people"></i> {{ type.guests }} Người</span>
                        <span class="room-stat"><i class="bi bi-rulers"></i> {{ type.area }} m²</span>
                      </div>
                      <div class="room-details" :class="{ 'collapsed': !type.isExpanded }">
                        <p>Diện tích phòng: {{ type.area }} m²</p>
                        <p>Loại giường: {{ type.bedTypes }}</p>
                        <p>Tiêu chuẩn: {{ type.capacity }}</p>
                        <p>{{ type.noBath ? 'Không có bàn tắm' : '' }}</p>
                        <p>Mỗi số phòng có ban công</p>
                        <p>Giá phòng được bao gồm ăn sáng</p>
                      </div>
                      <button v-if="type.isExpanded" class="btn btn-link btn-sm" @click="toggleExpand(type.id, false)"
                        aria-label="Thu gọn">Thu gọn</button>
                      <button v-else class="btn btn-link btn-sm" @click="toggleExpand(type.id, true)"
                        aria-label="Xem thêm">Xem thêm chi tiết loại phòng</button>
                    </div>
                    <div class="room-price mt-3">
                      <p>{{ type.price2h }} VND / 2 Giờ Đầu</p>
                      <p>{{ type.priceNight }} VND / Đêm</p>
                    </div>
                    <button class="btn-main mt-4" @click="bookRoomType(type)"
                      :disabled="selectedRoomTypes.length >= rooms" aria-label="Đặt Phòng">Đặt Phòng - {{ type.name
                      }}</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- Tóm tắt Đặt phòng (Trái, 1/3 chiều rộng) -->

          <div class="col-md-4 reservation-section">
            <h2 class="section-title">Thông tin Đặt phòng</h2>
            <div class="mb-3">
              <label class="form-label">Phòng đã chọn:</label>
              <ul class="list-group" v-if="selectedRoomTypes.length > 0">
                <li v-for="(room, index) in selectedRoomTypes" :key="room.id"
                  class="list-group-item d-flex justify-content-between align-items-center">
                  {{ room.name }}
                  <button type="button" class="btn btn-danger btn-sm" @click="removeRoom(index)"
                    aria-label="Xóa Phòng">X</button>
                </li>
              </ul>
              <p v-else class="form-control-plaintext">Chưa chọn phòng</p>
            </div>

            <div class="mb-3">
              <label class="form-label">Nhận phòng:</label>
              <p class="form-control-plaintext">{{ checkIn || 'Chưa chọn' }}</p>
            </div>
            <div class="mb-3">
              <label class="form-label">Trả phòng:</label>
              <p class="form-control-plaintext">{{ checkOut || 'Chưa chọn' }}</p>
            </div>
            <div class="mb-3">
              <label for="rooms" class="form-label">Số phòng <span class="text-muted small">(Bắt buộc)</span></label>
              <select class="form-select" v-model="rooms" aria-describedby="roomsHelp">
                <option v-for="n in 5" :value="n" :key="n">{{ n }}</option>
                <small id="roomsHelp" class="form-text text-muted">Tối đa 5 phòng.</small>
              </select>
            </div>
            <div class="mb-3">
              <label for="adults" class="form-label">Người lớn <span class="text-muted small">(Bắt buộc)</span></label>
              <select class="form-select" v-model="adults" aria-describedby="adultsHelp">
                <option v-for="n in 10" :value="n" :key="n">{{ n }}</option>
                <small id="adultsHelp" class="form-text text-muted">Tối đa 10 người lớn.</small>
              </select>
            </div>
            <div class="mb-3">
              <label for="children" class="form-label">Trẻ em</label>
              <select class="form-select" v-model="children" aria-describedby="childrenHelp">
                <option v-for="n in 10" :value="n" :key="n">{{ n }}</option>
                <small id="childrenHelp" class="form-text text-muted">Tối đa 10 trẻ em.</small>
              </select>
            </div>
            <button type="button" class="btn btn-primary w-100 mt-3" @click.prevent="nextStep"
              :disabled="selectedRoomTypes.length === 0" aria-label="Kiểm tra Tình trạng">Kiểm tra Tình trạng</button>
          </div>
        </div>
      </div>

      <!-- Đặt Phòng View Bước 3 -->
      <div v-if="currentStep === 3" class="content-section">
        <h2 class="section-title">Đặt Phòng</h2>
        <div class="row justify-content-center">
          <!-- Thông tin Khách hàng (Trái, 1/3 chiều rộng) -->
          <div class="col-md-4 reservation-section">
            <h3 class="section-subtitle">Thông tin Khách hàng</h3>
            <form @submit.prevent>
              <div class="mb-3">
                <label for="country" class="form-label">Quốc gia <span class="text-muted small">(Bắt
                    buộc)</span></label>
                <select class="form-select" id="country" v-model="customer.country" required
                  aria-describedby="countryHelp">
                  <option value="Vietnam">Vietnam</option>

                  <option value="United Kingdom (UK)">United Kingdom (UK)</option>
                  <option value="United States">United States</option>
                </select>
                <small id="countryHelp" class="form-text text-muted">Chọn quốc gia của bạn.</small>
              </div>
              <div class="mb-3">
                <label for="fullName" class="form-label">Họ và tên <span class="text-muted small">(Bắt
                    buộc)</span></label>
                <input type="text" class="form-control" id="fullName" v-model="customer.fullName" required
                  aria-describedby="fullNameHelp">
                <small id="fullNameHelp" class="form-text text-muted">Nhập họ và tên đầy đủ.</small>
              </div>
              <!-- <div class="mb-3">
                <label for="address" class="form-label">Địa chỉ <span class="text-muted small">(Bắt buộc)</span></label>
                <input type="text" class="form-control" id="address" v-model="customer.address" required
                  aria-describedby="addressHelp">
                <small id="addressHelp" class="form-text text-muted">Nhập địa chỉ đường phố.</small>
              </div>
              <div class="mb-3">
                <label for="email" class="form-label">Địa chỉ Email <span class="text-muted small">(Bắt
                    buộc)</span></label>
                <input type="email" class="form-control" id="email" v-model="customer.email" required
                  aria-describedby="emailHelp">
                <small id="emailHelp" class="form-text text-muted">Nhập email hợp lệ để nhận xác nhận.</small>
              </div> -->
              <div class="mb-3">
                <label for="phone" class="form-label">Số điện thoại <span class="text-muted small">(Bắt
                    buộc)</span></label>
                <input type="tel" class="form-control" id="phone" v-model="customer.phone" required
                  aria-describedby="phoneHelp">
                <small id="phoneHelp" class="form-text text-muted">Nhập số điện thoại hợp lệ.</small>
              </div>
              <div class="mb-3">
                <label for="orderNotes" class="form-label">Ghi chú Đặt hàng (Tùy chọn)</label>
                <textarea class="form-control" id="orderNotes" v-model="customer.orderNotes" rows="3"
                  aria-describedby="orderNotesHelp"></textarea>

              </div>
              <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="createAccount" v-model="customer.createAccount">
                <label class="form-check-label" for="createAccount">Tôi hoàn toàn đồng ý với quy tắc chung của khách
                  sạn</label>
              </div>
            </form>
          </div>

          <!-- Cách Thanh toán (Phải, 2/3 chiều rộng) -->
          <div class="col-md-8 payment-section">
            <div class="receipt">
              <p class="shop-name">UI Market</p>
              <p class="info">
                1234 Market Street, Suite 101<br />
                City, State ZIP<br />
                Date: {{ currentDateTime }}<br />

              </p>
              <table>
                <thead>
                  <tr>
                    <!-- <th>Item</th> -->
                    <th>Số lượng phòng</th>
                    <th>Thời gian </th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <!-- <td>{{ selectedRoomTypes.name }}</td> -->
                    <td>
                      {{ selectedRoomTypes.length }}
                    </td>
                    <td>{{ checkIn }} đến {{
                      checkOut }}</td>
                  </tr>

                </tbody>
              </table>

              <div class="total">
                <p>Total:</p>
                <p>${{ totalPrice }}</p>
              </div>

              <p class="thanks">Thank you for shopping with us!</p>
            </div>

            <!-- <h3 class="section-subtitle">Cách Thanh toán</h3>
            <div class="mb-3">
              <label class="form-label">Tổng tiền: ${{ totalPrice }}</label>
              <p class="form-control-plaintext">Bao gồm: {{ selectedRoomTypes.length }} phòng từ {{ checkIn }} đến {{
                checkOut }}</p>
            </div>
            <div class="mb-3">
              <label class="form-label">Phương thức thanh toán:</label>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="paymentMethod" id="cash" v-model="paymentMethod"
                  value="cash" required>
                <label class="form-check-label" for="cash">Thanh toán bằng tiền mặt</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="paymentMethod" id="card" v-model="paymentMethod"
                  value="card" required>
                <label class="form-check-label" for="card">Thanh toán bằng thẻ tín dụng</label>
              </div>
              <div v-if="paymentMethod === 'card'" class="mt-3">
                <label for="cardNumber" class="form-label">Số thẻ <span class="text-muted small">(Bắt
                    buộc)</span></label>
                <input type="text" class="form-control" id="cardNumber" v-model="cardDetails.number" required
                  aria-describedby="cardNumberHelp">
                <small id="cardNumberHelp" class="form-text text-muted">Nhập số thẻ 16 chữ số.</small>
                <div class="row">
                  <div class="col-6">
                    <label for="expiryDate" class="form-label">Ngày hết hạn <span class="text-muted small">(Bắt
                        buộc)</span></label>
                    <input type="text" class="form-control" id="expiryDate" v-model="cardDetails.expiry"
                      placeholder="MM/YY" required aria-describedby="expiryDateHelp">
                    <small id="expiryDateHelp" class="form-text text-muted">Định dạng: MM/YY.</small>
                  </div>
                  <div class="col-6">
                    <label for="cvv" class="form-label">CVV <span class="text-muted small">(Bắt buộc)</span></label>
                    <input type="text" class="form-control" id="cvv" v-model="cardDetails.cvv" required
                      aria-describedby="cvvHelp">
                    <small id="cvvHelp" class="form-text text-muted">Nhập mã CVV 3-4 chữ số.</small>
                  </div>
                </div>
              </div>
            </div>
            <div class="text-center mt-4">
              <button class="btn btn-outline-primary me-2" @click.prevent="prevStep" aria-label="Quay lại">Quay
                lại</button>
              <button class="btn btn-primary" @click.prevent="nextStep" :disabled="!isPaymentComplete"
                aria-label="Tiếp tục">Tiếp tục</button>
              <div v-if="!isPaymentComplete" class="text-danger small mt-2">Vui lòng điền đầy đủ thông tin để tiếp tục.
              </div>
            </div> -->
          </div>
        </div>
      </div>

      <!-- Xác Nhận View Bước 4-->
      <div v-if="currentStep === 4" class="content-section">
        <h2 class="section-title">Xác Nhận</h2>
        <p>
          Đặt phòng đã xác nhận cho:
          {{selectedRoomTypes.map(r => r.name).join(', ')}} từ {{ checkIn }} đến {{ checkOut }}
        </p>
        <p>
          Thông tin khách hàng:
          {{ customer.fullName }}, {{ customer.email }}, {{ customer.phone }},
          {{ customer.address }}, {{ customer.country }},
          {{ customer.orderNotes || 'Không có ghi chú' }}
        </p>
        <p>
          Phương thức thanh toán:
          {{ paymentMethod === 'cash' ? 'Thanh toán bằng tiền mặt' : 'Thanh toán bằng thẻ tín dụng' }}
        </p>
        <div class="text-center mt-4">
          <button class="btn btn-outline-primary" @click.prevent="prevStep" aria-label="Quay lại">Quay lại</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import sliderComponent from '../sliderComponent.vue';
const checkIn = ref(null);
const checkOut = ref(null);
const rooms = ref(1);
const adults = ref(1);
const children = ref(0);
const currentMonth = ref('');
const currentYear = ref(0);
const days = ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'];
const calendarDays = ref([]);
const today = new Date(2025, 5, 16, 11, 50, 0); // June 16, 2025, 11:50 AM +07
const currentStep = ref(1);
const completedSteps = ref([1]); // Initialize with step 1 completed
const showRules = ref(true);
const roomTypes = ref([
  {
    id: 1, name: 'Phòng Sang Trọng', price: 200, price2h: '280000', priceNight: '1100000', count: 2, beds: 2, guests: 3, area: 25, bedTypes: '1 giường 2m² và 1 giường 1m²', capacity: '2-3 người', noBath: true, isExpanded: false, image: 'https://images.unsplash.com/photo-1611892440504-42a792e24d32?ixlib=rb-4.0.3&auto=format&fit=crop&w=1350&q=80',
    videoId: 'https://www.youtube.com/watch?si=p4lMrVtAXVVaZ7nJ&v=llMFdIVBDBQ&feature=youtu.be'
  },
  { id: 2, name: 'Phòng Cặp', price: 100, price2h: '200000', priceNight: '800000', count: 3, beds: 1, guests: 2, area: 20, bedTypes: '1 giường 2m²', capacity: '1-2 người', noBath: false, isExpanded: false, image: 'https://images.unsplash.com/photo-1611892440504-42a792e24d32?ixlib=rb-4.0.3&auto=format&fit=crop&w=1350&q=80' },
  { id: 3, name: 'Phòng Tiêu Chuẩn', price: 80, price2h: '150000', priceNight: '600000', count: 4, beds: 1, guests: 2, area: 15, bedTypes: '1 giường 1.5m²', capacity: '1-2 người', noBath: true, isExpanded: false, image: 'https://images.unsplash.com/photo-1611892440504-42a792e24d32?ixlib=rb-4.0.3&auto=format&fit=crop&w=1350&q=80' },
  { id: 4, name: 'Phòng Gia Đình', price: 120, price2h: '300000', priceNight: '1200000', count: 3, beds: 3, guests: 5, area: 30, bedTypes: '2 giường 2m² và 1 giường 1m²', capacity: '3-5 người', noBath: false, isExpanded: false, image: 'https://images.unsplash.com/photo-1611892440504-42a792e24d32?ixlib=rb-4.0.3&auto=format&fit=crop&w=1350&q=80' },
  { id: 4, name: 'Phòng Gia Đình', price: 120, price2h: '300000', priceNight: '1200000', count: 3, beds: 3, guests: 5, area: 30, bedTypes: '2 giường 2m² và 1 giường 1m²', capacity: '3-5 người', noBath: false, isExpanded: false, image: 'https://images.unsplash.com/photo-1611892440504-42a792e24d32?ixlib=rb-4.0.3&auto=format&fit=crop&w=1350&q=80' },
]);
const selectedRoomTypes = ref([]);
const allRooms = ref([]);
const customer = ref({
  country: 'United Kingdom (UK)',
  fullName: '',
  address: '',
  email: '',
  phone: '',
  orderNotes: '',
  createAccount: false
});
const paymentMethod = ref('');
const cardDetails = ref({
  number: '',
  expiry: '',
  cvv: ''
});

const isReservationComplete = computed(() => {
  return checkIn.value && checkOut.value && rooms.value > 0 && adults.value > 0;
});

const totalPrice = computed(() => {
  const nights = (new Date(checkOut.value) - new Date(checkIn.value)) / (1000 * 60 * 60 * 24);
  return selectedRoomTypes.value.reduce((sum, room) => sum + room.price * nights, 0) || 0;
});

const isPaymentComplete = computed(() => {
  return customer.value.country && customer.value.fullName && customer.value.address && customer.value.email && customer.value.phone && (paymentMethod.value === 'cash' || (paymentMethod.value === 'card' && cardDetails.value.number && cardDetails.value.expiry && cardDetails.value.cvv));
});

onMounted(() => {
  updateCalendar(today.getFullYear(), today.getMonth());
  initializeRooms();
});

function initializeRooms() {
  const roomsPerFloor = 12;
  allRooms.value = [];
  const totalRooms = roomTypes.value.reduce((sum, type) => sum + type.count, 0);
  if (totalRooms !== roomsPerFloor) {
    console.warn(`Tổng số phòng (${totalRooms}) không khớp với dự kiến (${roomsPerFloor}). Có thể cần điều chỉnh số lượng.`);
  }
  let roomNumber = 1;
  roomTypes.value.forEach(type => {
    for (let floor = 1; floor <= 10; floor++) {
      for (let i = 0; i < type.count; i++) {
        allRooms.value.push({
          id: `${floor}-${type.id}-${roomNumber}`,
          name: type.name,
          number: roomNumber++,
          floor: floor,
          booked: Math.random() > 0.5,
        });
      }
    }
    roomNumber = 1;
  });
}

function updateCalendar(year, month) {
  const firstDay = new Date(year, month, 1);
  const lastDay = new Date(year, month + 1, 0);
  const startDay = firstDay.getDay();
  const daysInMonth = lastDay.getDate();
  currentMonth.value = firstDay.toLocaleString('default', { month: 'long' });
  currentYear.value = year;

  calendarDays.value = [];
  for (let i = 0; i < startDay; i++) {
    calendarDays.value.push({ day: '', date: null, isPast: false, isToday: false });
  }
  for (let i = 1; i <= daysInMonth; i++) {
    const date = new Date(year, month, i);
    const isPast = date < today.setHours(0, 0, 0, 0);
    const isToday = date.toDateString() === today.toDateString();
    calendarDays.value.push({ day: i, date, isPast, isToday });
  }
}

function prevMonth() {
  let year = currentYear.value;
  let month = new Date(currentYear.value, currentMonthToIndex(currentMonth.value) - 1, 1).getMonth();
  year = month === 11 ? year - 1 : year;
  updateCalendar(year, month);
}

function nextMonth() {
  let year = currentYear.value;
  let month = new Date(currentYear.value, currentMonthToIndex(currentMonth.value) + 1, 1).getMonth();
  year = month === 0 ? year + 1 : year;
  updateCalendar(year, month);
}

function currentMonthToIndex(monthName) {
  return new Date(Date.parse(monthName + " 1, 2000")).getMonth();
}

function selectDate(date) {
  if (!date || date < today.setHours(0, 0, 0, 0)) return;

  if (checkIn.value && date.toLocaleDateString() === checkIn.value) {
    checkIn.value = null;
    checkOut.value = null;
    updateCalendar(date.getFullYear(), date.getMonth());
    return;
  }

  if (!checkIn.value) {
    checkIn.value = date.toLocaleDateString();
    checkOut.value = null;
    updateCalendar(date.getFullYear(), date.getMonth());
  } else if (date > new Date(checkIn.value)) {
    checkOut.value = date.toLocaleDateString();
    updateCalendar(date.getFullYear(), date.getMonth());
  }
}

function isSelected(date) {
  if (!date) return false;
  const d = date.toLocaleDateString();
  return d === checkIn.value || d === checkOut.value;
}

function isInRange(date) {
  if (!date || !checkIn.value || !checkOut.value) return false;
  const d = new Date(date);
  const start = new Date(checkIn.value);
  const end = new Date(checkOut.value);
  return d > start && d < end;
}

function setStep(step) {
  if (step >= 1 && step <= 4) {
    currentStep.value = step;
    completedSteps.value = Array.from({ length: step }, (_, i) => i + 1);
  }
}

function nextStep() {
  if (currentStep.value === 1 && !isReservationComplete.value) return;
  if (currentStep.value === 2 && selectedRoomTypes.value.length === 0) return;
  if (currentStep.value === 3 && !isPaymentComplete.value) return;
  if (currentStep.value < 4) {
    currentStep.value++;
    if (!completedSteps.value.includes(currentStep.value)) {
      completedSteps.value.push(currentStep.value);
    }
  }
}

function prevStep() {
  if (currentStep.value > 1) {
    currentStep.value--;
  }
}

function bookRoomType(type) {
  if (selectedRoomTypes.value.length < rooms.value) {
    selectedRoomTypes.value.push(type);
  }
}

function removeRoom(index) {
  selectedRoomTypes.value.splice(index, 1);
}

function toggleRules(show) {
  showRules.value = show;
}

function toggleExpand(id, expand) {
  const room = roomTypes.value.find(r => r.id === id);
  if (room) {
    room.isExpanded = expand;
  }
}
</script>

<style scoped>
.main-wrapper {
  background-color: #ffffff;
  min-height: 100vh;
  color: #333;
}

.banner {
  width: 100%;
  height: 500px;
  background: url('https://noithatantin.com/wp-content/uploads/2024/07/phong-khach-hep-3m-5.png') repeat;
  display: flex;
  justify-content: center;
  align-items: center;
  color: white;
  font-size: 2.5rem;
  font-weight: bold;
  text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.3);
}

.progress-steps {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px 0;
  gap: 10px;
}

.step {
  display: flex;
  align-items: center;
  cursor: pointer;
  opacity: 0.5;
  transition: opacity 0.3s;
}

.step.active {
  opacity: 1;
}

.step-number {
  width: 30px;
  height: 30px;
  background: linear-gradient(135deg, #6e8efb, #a777e3);
  color: #fff;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 10px;
  transition: transform 0.2s;
}

.step-number.completed {
  background: linear-gradient(135deg, #6e8efb, #a777e3);
  transform: scale(1.1);
}

.step-label {
  font-size: 1rem;
  font-weight: 500;
  color: #555;
}

.step-connector {
  width: 50px;
  height: 3px;
  background: linear-gradient(135deg, #6e8efb, #a777e3);
  margin: 0 10px;
  transition: opacity 0.3s;
}

.step-connector.active {
  opacity: 1;
}

.step-connector:not(.active) {
  opacity: 0.3;
}

.booking-rules {
  padding: 15px 0;
}

.booking-rules .card {
  /* outline: 2px solid rgba(22, 109, 207, 0.461); */
  /* border: 1px solid #ddd; */
  border-radius: 8px;
}

.booking-rules .card-header {
  /* background-color: #f8f9fa; */
  border-bottom: 1px solid #ddd;
}

.booking-rules .card-body ul {
  padding-left: 20px;
}

.booking-rules .card-body li {
  margin-bottom: 10px;
}

.content-section {
  padding: 20px 0;

}

.section-title {
  font-size: 1.75rem;
  margin-bottom: 1.5rem;
  color: #2c3e50;

}

.section-subtitle {
  font-size: 1.25rem;
  margin-bottom: 1rem;
  color: #2c3e50;
}

.reservation-section,
.calendar-section,
.payment-section {
  padding: 20px;
  /* outline: 2px solid rgba(22, 109, 207, 0.461); */
  /* background: #ff0000; */
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.reservation-section {

  min-height: 400px;
  /* Đảm bảo chiều cao tối thiểu độc lập */
  max-height: auto;
  /* Cho phép tự động điều chỉnh nếu cần */
}

.calendar-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 2px;
  margin-top: 10px;
}

.calendar-day {
  padding: 10px;
  text-align: center;
  border: 1px solid #eee;
  cursor: pointer;
  /* background: #fff; */
  transition: background 0.2s, color 0.2s;
}

.calendar-day.past {
  /* background-color: #e9ecef; */
  color: #6c757d;
  cursor: not-allowed;
}

.calendar-day.today {
  background-color: #6591bf;
  color: white;
  font-weight: bold;
}

.calendar-day.selected {
  background-color: #339ab7;
  color: white;
}

.calendar-day.in-range {
  background-color: #339ab7;
}

.calendar-header {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 2px;
  text-align: center;
  font-weight: 600;
  color: #2c3e50;
  margin-bottom: 10px;
  padding: 5px 0;
  background: #f1f3f5;
  border-radius: 4px;
}

.day-header {
  padding: 5px;
}

.form-label {
  font-weight: 500;
  color: #2c3e50;
}

.form-control,
.form-select {
  border-radius: 4px;
  border-color: #ced4da;
}

.form-control-plaintext {
  /* background-color: #fff; */
  border: 1px solid #ced4da;
  padding: 0.375rem 0.75rem;
  border-radius: 0.25rem;
}

.card {
  border: 1px solid #ddd;
  border-radius: 8px;
}

.card-img-top {
  width: 50%;
  height: 150px;
  object-fit: cover;

}

.card-body {
  padding: 1.25rem;
  border-bottom-right-radius: 10px;
}

.card-title {
  font-size: 1.25rem;
  font-weight: 600;
}

.room-info {
  margin-bottom: 1rem;
}

.room-stats {
  display: flex;
  gap: 1rem;
  margin-bottom: 0.5rem;
}

.room-stat {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  font-weight: 500;
  color: #6c757d;
  /* Màu xám cho chữ */
}

.room-stat i {
  color: #6e8efb;
  /* Màu icon hợp với giao diện */
}

.room-details {
  margin-bottom: 0.5rem;
}

.room-details.collapsed {
  max-height: 0;
  overflow: hidden;
  transition: max-height 0.3s ease-out;
}

.room-details p {
  margin: 0.25rem 0;
}

.room-price p {
  margin: 0;
  font-weight: 500;
}

.card-text {
  font-size: 1rem;
  color: #666;
}

.btn-primary {
  /* background-color: #007bff; */
  border-color: #007bff;
}

.btn-primary:hover {
  /* background-color: #0056b3; */
  border-color: #0056b3;
}

.btn-primary:disabled {
  /* background-color: #6c757d; */
  border-color: #6c757d;
  opacity: 0.65;
  cursor: not-allowed;
}

.btn-danger {
  /* background-color: #dc3545; */
  border-color: #dc3545;
}

.btn-danger:hover {
  /* background-color: #c82333; */
  border-color: #bd2130;
}

.btn-outline-primary {
  color: #007bff;
  border-color: #007bff;
}

.btn-outline-primary:hover {
  /* background-color: #007bff; */
  color: #fff;
}

.btn-link {
  color: #007bff;
  text-decoration: none;
  padding: 0;
}

.btn-link:hover {
  text-decoration: underline;
  color: #0056b3;
}

@media (max-width: 768px) {
  .banner {
    height: 250px;
    font-size: 2rem;
  }

  .section-title {
    font-size: 1.5rem;
  }

  .reservation-section,
  .calendar-section,
  .payment-section {
    padding: 15px;
  }

  .col-md-4,
  .col-md-8 {
    flex: 0 0 100%;
    max-width: 100%;
  }

  .calendar-day {
    padding: 8px;
    font-size: 0.9rem;
  }

  .card-img-top {
    height: 150px;
  }

  .progress-steps {
    flex-direction: column;
    gap: 15px;
  }

  .step-connector {
    width: 100%;
    height: 2px;
  }

  .room-stats {
    flex-direction: column;
    gap: 0.5rem;
  }
}
</style>