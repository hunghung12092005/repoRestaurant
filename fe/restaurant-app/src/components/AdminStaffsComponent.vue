<template>
  <div class="staff-container">
    <!-- Header Section -->
    <div class="header-section mb-4">
      <h5 class="fw-bold">Staff Management</h5>
      <div class="d-flex justify-content-between align-items-center mt-3">
        <!-- Search Form -->
        <div class="search-form">
          <input
            type="text"
            class="form-control"
            v-model="searchQuery"
            placeholder="Search by name or email..."
            @input="filterStaffs"
          />
          <i class="bi bi-search search-icon"></i>
        </div>
        <!-- Add New Staff Button -->
        <button class="btn btn-primary" @click="addNewStaff">
          <i class="bi bi-plus-circle me-2"></i>Add New Staff
        </button>
      </div>
    </div>

    <!-- Staff Table -->
    <div class="card shadow-sm rounded-3">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover table-bordered mb-0">
            <thead class="table-light">
              <tr>
                <th scope="col">ID</th>
                <th scope="col">Name</th>
                <th scope="col">Email</th>
                <th scope="col">Role</th>
                <th scope="col">Department</th>
                <th scope="col">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="staff in filteredStaffs" :key="staff.id">
                <td>{{ staff.id }}</td>
                <td>{{ staff.name }}</td>
                <td>{{ staff.email }}</td>
                <td>{{ staff.role }}</td>
                <td>{{ staff.department }}</td>
                <td>
                  <button class="btn btn-sm btn-outline-primary me-2" @click="editStaff(staff)">
                    <i class="bi bi-pencil"></i>
                  </button>
                  <button class="btn btn-sm btn-outline-danger" @click="deleteStaff(staff.id)">
                    <i class="bi bi-trash"></i>
                  </button>
                </td>
              </tr>
              <tr v-if="!filteredStaffs.length">
                <td colspan="6" class="text-center text-muted">No staff found</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axiosConfig from '../axiosConfig.js'; // Import axiosConfig để gọi API

const staffs = ref([]);
const searchQuery = ref('');

// Lấy danh sách nhân viên từ API
const fetchStaffs = async () => {
  try {
    const response = await axiosConfig.get('http://127.0.0.1:8000/api/staffs'); // Giả định API endpoint
    staffs.value = response.data.staffs || [];
  } catch (error) {
    console.error('Error fetching staffs:', error.response ? error.response.data : error.message);
  }
};

// Lọc danh sách nhân viên theo searchQuery
const filteredStaffs = computed(() => {
  if (!searchQuery.value) return staffs.value;
  return staffs.value.filter(staff =>
    staff.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
    staff.email.toLowerCase().includes(searchQuery.value.toLowerCase())
  );
});

// Thêm nhân viên mới (chuyển hướng hoặc mở modal)
const addNewStaff = () => {
  // Chuyển hướng đến trang thêm nhân viên mới (có thể thay bằng modal)
  window.location.href = '/admin/staffs/add';
};

// Chỉnh sửa nhân viên (chuyển hướng hoặc mở modal)
const editStaff = (staff) => {
  // Chuyển hướng đến trang chỉnh sửa với ID nhân viên
  window.location.href = `/admin/staffs/edit/${staff.id}`;
};

// Xóa nhân viên
const deleteStaff = async (id) => {
  if (confirm('Are you sure you want to delete this staff?')) {
    try {
      await axiosConfig.delete(`http://127.0.0.1:8000/api/staffs/${id}`); // Giả định API endpoint
      staffs.value = staffs.value.filter(staff => staff.id !== id); // Cập nhật danh sách
      alert('Staff deleted successfully!');
    } catch (error) {
      console.error('Error deleting staff:', error.response ? error.response.data : error.message);
      alert('Failed to delete staff.');
    }
  }
};

// Gọi API khi component được mounted
onMounted(() => {
  fetchStaffs();
});

// Hàm lọc khi nhập liệu
const filterStaffs = () => {
  // Không cần làm gì thêm, computed đã tự xử lý
};
</script>

<style scoped>
.staff-container {
  padding: 20px;
}

.card {
  border: none;
  background: #fff;
}

.table {
  margin-bottom: 0;
}

.table th,
.table td {
  vertical-align: middle;
  padding: 12px;
  text-align: center;
}

.table th {
  background-color: #f8f9fa;
  font-weight: 600;
  color: #333;
}

.table td {
  color: #666;
}

.table-hover tbody tr:hover {
  background-color: #f1f3f5;
}

.btn-primary {
  background-color: #16B978;
  border-color: #16B978;
  transition: background-color 0.3s ease;
}

.btn-primary:hover {
  background-color: #13a567;
  border-color: #13a567;
}

.btn-outline-primary,
.btn-outline-danger {
  padding: 4px 8px;
  font-size: 0.9rem;
}

.btn-outline-primary i,
.btn-outline-danger i {
  font-size: 1rem;
}

/* Search Form Styles */
.search-form {
  position: relative;
  display: inline-block;
}

.search-form input {
  padding: 6px 30px 6px 12px;
  border: 1px solid #ced4da;
  border-radius: 4px;
  font-size: 0.9rem;
  width: 250px;
  transition: border-color 0.3s ease;
}

.search-form input:focus {
  border-color: #16B978;
  outline: none;
  box-shadow: 0 0 5px rgba(22, 185, 120, 0.3);
}

.search-form .search-icon {
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
  color: #666;
  cursor: pointer;
  font-size: 1rem;
}

.search-form .search-icon:hover {
  color: #16B978;
}

/* Header Section Styles */
.header-section {
  display: block;
}

.header-section .d-flex {
  gap: 15px;
}

@media (max-width: 768px) {
  .staff-container {
    padding: 10px;
  }

  .table th,
  .table td {
    padding: 8px;
    font-size: 0.9rem;
  }

  .header-section .d-flex {
    flex-direction: column;
    gap: 15px;
  }

  .search-form input {
    width: 100%;
    max-width: 250px;
  }

  .btn {
    width: 100%;
  }
}
</style>