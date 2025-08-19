  <template>
  <div class="container py-5 bg-light min-vh-100">
    <div class="row justify-content-center">
      <div class="col-lg-10">
        <h2 class="mb-5 text-center fw-light text-secondary">
          QUẢN LÝ MÃ GIẢM GIÁ
        </h2>

        <div class="card shadow-sm border-0 mb-5">
          <div class="card-header bg-white border-0 py-3">
            <h5 class="card-title mb-0 fw-bold text-muted">
              {{ form.id ? ' Chỉnh sửa mã' : ' Thêm mã mới' }}
            </h5>
          </div>
          <div class="card-body">
            <form @submit.prevent="saveCode" class="row g-4">
              <div class="col-md-4">
                <label class="form-label text-muted">Mã giảm giá <span class="text-danger">*</span></label>
                <input v-model="form.code" class="form-control form-control-lg bg-light" required />
              </div>
              <div class="col-md-4">
                <label class="form-label text-muted">Số tiền giảm (VNĐ) <span class="text-danger">*</span></label>
                <input v-model.number="form.discount_amount" type="number" class="form-control form-control-lg bg-light" required />
              </div>
              <div class="col-md-4">
                <label class="form-label text-muted">Giới hạn lượt dùng <span class="text-danger">*</span></label>
                <input v-model.number="form.usage_limit" type="number" class="form-control form-control-lg bg-light" required />
              </div>
             
              <div class="col-md-6">
                <label class="form-label text-muted">Mô tả</label>
                <input v-model="form.description" class="form-control form-control-lg bg-light" />
              </div>
              <div class="col-md-4">
                <label class="form-label text-muted">Ngày hết hạn</label>
                <input v-model="form.expires_at" type="date" class="form-control form-control-lg bg-light" />
              </div>
              <div class="col-md-2 d-flex align-items-end">
                <div class="form-check form-switch p-0">
                  <input class="form-check-input ms-0" type="checkbox" v-model="form.is_active" id="activeCheck" role="switch"/>
                  <label class="form-check-label ms-2 text-muted" for="activeCheck">Kích hoạt</label>
                </div>
              </div>
              <div class="col-12 text-end mt-4">
                <button type="submit" class="btn btn-primary me-2 px-4">
                  {{ form.id ? 'Cập nhật' : 'Thêm mới' }}
                </button>
                <button type="button" class="btn btn-outline-secondary px-4" @click="resetForm">
                  Huỷ
                </button>
              </div>
            </form>
          </div>
        </div>

        <div class="card shadow-sm border-0">
          <div class="card-header bg-white border-0 py-3">
            <h5 class="card-title mb-0 fw-bold text-muted">
              📜 Danh sách mã giảm giá
            </h5>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-borderless table-hover align-middle mb-0">
                <thead class="bg-light">
                  <tr>
                    <th class="text-muted fw-normal">#</th>
                    <th class="text-muted fw-normal">Mã giảm giá</th>
                    <th class="text-muted fw-normal">Mô tả</th>
                    <th class="text-muted fw-normal">Giảm (VNĐ)</th>
                    <th class="text-muted fw-normal">Đã dùng</th>
                    <th class="text-muted fw-normal">Giới hạn</th>
                    <th class="text-muted fw-normal">Hết hạn</th>
                    <th class="text-muted fw-normal">Trạng thái</th>
                    <th class="text-muted fw-normal">Thao tác</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="discountCodes.length === 0">
                    <td colspan="9" class="py-4 text-center text-muted">Không có mã giảm giá nào.</td>
                  </tr>
                  <tr v-for="(code, index) in discountCodes" :key="code.id">
                    <td>{{ index + 1 }}</td>
                    <td class="fw-bold text-primary">{{ code.code }}</td>
                    <td>{{ code.description || '—' }}</td>
                    <td class="text-success fw-semibold">{{ formatCurrency(code.discount_amount) }}</td>
                    <td>{{ code.used_count }}</td>
                    <td>{{ code.usage_limit }}</td>
                    <td>
                      <span :class="isExpired(code.expires_at) ? 'text-danger' : ''">
                        {{ formatDate(code.expires_at) }}
                      </span>
                    </td>
                    <td>
                      <span class="badge rounded-pill" :class="code.is_active ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary'">
                        {{ code.is_active ? 'Đang hoạt động' : 'Không hoạt động' }}
                      </span>
                    </td>
                    <td>
                      <button class="btn btn-sm btn-link text-primary me-2" @click="editCode(code)">
                        <i class="fas fa-edit"></i>
                      </button>
                      <button class="btn btn-sm btn-link text-danger" @click="deleteCode(code.id)">
                        <i class="fas fa-trash-alt"></i>
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* CSS Tùy chỉnh để tạo phong cách đơn giản và hài hòa */
.min-vh-100 {
  min-height: 100vh;
}
.card {
  border-radius: 12px;
  transition: all 0.3s ease;
}
.card-header {
  border-bottom: 1px solid #eee;
}
.form-control-lg {
  border-radius: 8px;
  border-color: #e0e0e0;
}
.btn {
  border-radius: 50px;
  font-weight: 500;
}
.btn-primary {
  background-color: #4CAF50; /* Màu xanh lá tươi mới */
  border-color: #4CAF50;
}
.btn-primary:hover {
  background-color: #45a049;
  border-color: #45a049;
}
.table th, .table td {
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #f0f0f0;
}
.table thead th {
  text-transform: uppercase;
  font-size: 0.85rem;
}
.table-hover tbody tr:hover {
  background-color: #f8f9fa;
}
.badge {
  padding: 0.5em 1em;
  font-weight: 600;
}
</style>

    <script setup>
    import { ref, onMounted, inject } from 'vue'
    import axios from 'axios'

    // Danh sách mã
    const discountCodes = ref([])
    const apiUrl = inject('apiUrl')
    // Biểu mẫu mã
    const form = ref({
    id: null,
    code: '',
    description: '',
    discount_amount: 0,
    usage_limit: 1,
    used_count: 0,
    is_active: true,
    expires_at: null,
    })

    // Lấy dữ liệu
    const fetchDiscountCodes = async () => {
    const res = await axios.get('/api/discount-codes')
    discountCodes.value = res.data
    }

    // Thêm hoặc cập nhật
    const saveCode = async () => {
    if (form.value.id) {
        await axios.put(`${apiUrl}/api/discount-codes/${form.value.id}`, form.value)
    } else {
        await axios.post(`${apiUrl}/api/discount-codes`, form.value)
    }
    await fetchDiscountCodes()
    resetForm()
    }

    // Sửa
    const editCode = (code) => {
    form.value = { ...code }
    }

    // Xoá
    const deleteCode = async (id) => {
    if (confirm('Bạn có chắc chắn muốn xoá không?')) {
        await axios.delete(`${apiUrl}/api/discount-codes/${id}`)
        await fetchDiscountCodes()
    }
    }

    // Reset form
    const resetForm = () => {
    form.value = {
        id: null,
        code: '',
        description: '',
        discount_amount: 0,
        usage_limit: 1,
        used_count: 0,
        is_active: true,
        expires_at: null,
    }
    }

    // Format
    const formatDate = (dateStr) => {
    if (!dateStr) return 'Không có'
    return new Date(dateStr).toLocaleDateString('vi-VN')
    }

    const isExpired = (dateStr) => {
    if (!dateStr) return false
    return new Date(dateStr) < new Date()
    }

    const formatCurrency = (num) => {
    return num.toLocaleString('vi-VN') + ' đ'
    }

    onMounted(fetchDiscountCodes)
    </script>
