    <template>
    <div class="container py-4">
        <h2 class="mb-4 fw-bold text-primary">🎟️ Quản lý mã giảm giá</h2>

        <!-- Bảng mã giảm giá -->
        <div class="table-responsive">
        <table class="table table-bordered align-middle text-center">
            <thead class="table-dark text-white">
            <tr>
                <th>#</th>
                <th>Mã giảm giá</th>
                <th>Mô tả</th>
                <th>Giảm (VNĐ)</th>
                <th>Đã dùng</th>
                <th>Giới hạn</th>
                <th>Hết hạn</th>
                <th>Trạng thái</th>
                <th>Thao tác</th>
            </tr>
            </thead>
            <tbody>
            <tr v-if="discountCodes.length === 0">
                <td colspan="9" class="text-muted">Không có mã giảm giá nào.</td>
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
                <span class="badge" :class="code.is_active ? 'bg-success' : 'bg-secondary'">
                    {{ code.is_active ? 'Đang hoạt động' : 'Không hoạt động' }}
                </span>
                </td>
                <td>
                <button class="btn btn-sm btn-outline-primary me-2" @click="editCode(code)">Sửa</button>
                <button class="btn btn-sm btn-outline-danger" @click="deleteCode(code.id)">Xoá</button>
                </td>
            </tr>
            </tbody>
        </table>
        </div>

        <!-- Form -->
        <div class="card mt-5 shadow-sm">
        <div class="card-header bg-primary text-white">
            <strong>{{ form.id ? '✏️ Chỉnh sửa mã' : '➕ Thêm mã giảm giá mới' }}</strong>
        </div>
        <div class="card-body">
            <form @submit.prevent="saveCode" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Mã giảm giá *</label>
                <input v-model="form.code" class="form-control" required />
            </div>
            <div class="col-md-4">
                <label class="form-label">Số tiền giảm *</label>
                <input v-model.number="form.discount_amount" type="number" class="form-control" required />
            </div>
            <div class="col-md-4">
                <label class="form-label">Lượt sử dụng tối đa *</label>
                <input v-model.number="form.usage_limit" type="number" class="form-control" required />
            </div>
            <div class="col-md-6">
                <label class="form-label">Mô tả</label>
                <input v-model="form.description" class="form-control" />
            </div>
            <div class="col-md-3">
                <label class="form-label">Ngày hết hạn</label>
                <input v-model="form.expires_at" type="date" class="form-control" />
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <div class="form-check">
                <input class="form-check-input" type="checkbox" v-model="form.is_active" id="activeCheck" />
                <label class="form-check-label" for="activeCheck">Kích hoạt</label>
                </div>
            </div>
            <div class="col-12 text-end">
                <button type="submit" class="btn btn-success me-2">{{ form.id ? 'Cập nhật' : 'Thêm mới' }}</button>
                <button type="button" class="btn btn-secondary" @click="resetForm">Huỷ</button>
            </div>
            </form>
        </div>
        </div>
    </div>
    </template>

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
