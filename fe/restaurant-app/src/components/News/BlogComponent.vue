<template>
  <div class="blog-page-wrapper">
    <!-- Khu vực Banner với ảnh nền -->
    <section class="blog-banner">
      <div class="banner-content-wrapper text-center">
        <h1 class="banner-title">Tin Tức & Blog</h1>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb justify-content-center">
            <li class="breadcrumb-item"><a href="/">Trang Chủ</a></li>
            <li class="breadcrumb-item active" aria-current="page">Tin Tức</li>
          </ol>
        </nav>
      </div>
    </section>

    <!-- Khu vực Nội dung chính -->
    <section class="blog-content-area py-5">
      <div class="container">
        <div class="row">
          <!-- Cột các bài viết Blog -->
          <div class="col-lg-8">
            <div class="blog-posts-wrapper">
              
              <!-- Trạng thái đang tải -->
              <div v-if="loading" class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                  <span class="visually-hidden">Đang tải...</span>
                </div>
              </div>

              <!-- Nội dung khi đã tải xong -->
              <div v-else>
                <!-- Lưới chứa các bài viết -->
                <div class="row">
                  <div v-if="!blogPosts.length" class="col-12 text-center py-5">
                    <p>Không tìm thấy bài viết nào.</p>
                  </div>
                  <!-- Vòng lặp qua các bài viết từ API -->
                  <div v-for="post in blogPosts" :key="post.id" class="col-md-6">
                    <div class="blog-post-item mb-5">
                      <div class="post-image">
                        <img :src="getThumbnailUrl(post.thumbnail)" :alt="post.title" class="img-fluid rounded">
                      </div>
                      <div class="post-content p-3">
                        <div class="post-meta d-flex align-items-center mb-2">
                          <span class="me-4"><i class="bi bi-person me-2"></i>Bởi {{ post.author ? post.author.name : 'Không rõ' }}</span>
                          <span><i class="bi bi-calendar-event me-2"></i>{{ formatDate(post.publish_date) }}</span>
                        </div>
                        <h3 class="post-title">
                          <router-link :to="`/news/${post.id}`">{{ post.title }}</router-link>
                        </h3>
                        <p class="post-excerpt">{{ post.summary }}</p>
                        <router-link :to="`/news/${post.id}`" class="btn btn-sea-primary mt-2">ĐỌC THÊM</router-link>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Phân trang động -->
                <nav v-if="pagination && pagination.last_page > 1" aria-label="Page navigation">
                  <ul class="pagination justify-content-start mt-4">
                    <li class="page-item" :class="{ 'disabled': pagination.current_page === 1 }">
                      <a class="page-link" href="#" @click.prevent="changePage(pagination.current_page - 1)">Trước</a>
                    </li>
                    <li v-for="page in pagination.last_page" :key="page" class="page-item" :class="{ 'active': page === pagination.current_page }">
                      <a class="page-link" href="#" @click.prevent="changePage(page)">{{ page }}</a>
                    </li>
                    <li class="page-item" :class="{ 'disabled': pagination.current_page === pagination.last_page }">
                      <a class="page-link" href="#" @click.prevent="changePage(pagination.current_page + 1)">Sau</a>
                    </li>
                  </ul>
                </nav>
              </div>

            </div>
          </div>

          <!-- Cột Sidebar -->
          <div class="col-lg-4">
            <div class="blog-sidebar">
              <div class="sidebar-widget p-4 rounded mb-4">
                <h4 class="widget-title mb-3">Tìm Kiếm Tại Đây</h4>
                <div class="input-group">
                  <input type="text" v-model="searchQuery" @keyup.enter="handleSearch" class="form-control" placeholder="Tìm kiếm tại đây..." aria-label="Tìm kiếm tại đây">
                  <button class="btn btn-search" type="button" @click="handleSearch"><i class="bi bi-search"></i></button>
                </div>
              </div>

              <div class="sidebar-widget p-4 rounded mb-4">
                <h4 class="widget-title mb-3">Danh Mục</h4>
                <ul class="list-unstyled category-list">
                  <!-- [THAY ĐỔI] Thêm lại "Tất cả danh mục" -->
                  <li>
                    <router-link 
                      to="/news" 
                      class="d-flex justify-content-between align-items-center"
                      :class="{ 'active-category': isAllCategoriesActive }"
                    >
                      <span>Tất cả danh mục</span>
                    </router-link>
                  </li>
                  <!-- Lặp qua các danh mục -->
                  <li v-for="category in categories" :key="category.id">
                    <router-link 
                      :to="{ path: '/news', query: { category: category.id } }"
                      class="d-flex justify-content-between align-items-center"
                      :class="{ 'active-category': isCategoryActive(category.id) }"
                    >
                      <span>{{ category.name }}</span>
                    </router-link>
                  </li>
                </ul>
              </div>

              <div class="sidebar-widget p-4 rounded mb-4">
                <h4 class="widget-title mb-3">Bài Viết Gần Đây</h4>
                <div v-for="post in recentPosts" :key="post.id" class="recent-post-item d-flex align-items-center mb-3">
                  <img :src="getThumbnailUrl(post.thumbnail)" :alt="post.title" class="rounded me-3">
                  <div>
                    <h6 class="mb-1"><router-link :to="`/news/${post.id}`">{{ post.title }}</router-link></h6>
                    <small class="text-muted"><i class="bi bi-calendar-event me-1"></i>{{ formatDate(post.publish_date) }}</small>
                  </div>
                </div>
              </div>
              
              <div class="sidebar-widget p-4 rounded mb-4">
                <h4 class="widget-title mb-3">Thẻ Phổ Biến</h4>
                <div class="tag-cloud">
                    <a href="#" v-for="tag in popularTags" :key="tag.name">{{ tag.name }}</a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted, inject, watch, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';

// --- QUẢN LÝ TRẠNG THÁI ---
const route = useRoute();
const router = useRouter();
const blogPosts = ref([]);
const categories = ref([]);
const recentPosts = ref([]);
const pagination = ref({});
const loading = ref(true);
const searchQuery = ref('');

const popularTags = ref([
    { name: 'Đồ Ăn Nhanh' }, { name: 'Bữa Trưa' }, { name: 'Nhà Hàng' },
    { name: 'Burger' }, { name: 'Bữa Tối' }, { name: 'Gà Rán' },
]);

const apiUrl = inject('apiUrl');

// [THAY ĐỔI] Các computed property để xác định trạng thái active
const isAllCategoriesActive = computed(() => {
  return !route.query.category;
});

const isCategoryActive = (categoryId) => {
  return Number(route.query.category) === categoryId;
};

// --- HÀM HỖ TRỢ ---
const getThumbnailUrl = (thumbnail) => {
  if (thumbnail) {
    return `${apiUrl}/images/news_thumbnails/${thumbnail}`;
  }
  return 'https://via.placeholder.com/400x300.png?text=No+Image';
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  const day = String(date.getDate()).padStart(2, '0');
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const year = date.getFullYear();
  return `${day}/${month}/${year}`;
};

// --- GỌI API ---
const fetchNews = async (page = 1, query = '', categoryId = null) => {
  loading.value = true;
  try {
    let url = `${apiUrl}/api/news?page=${page}`;
    if (query) {
      url += `&q=${query}`;
    }
    if (categoryId) {
      url += `&category_id=${categoryId}`;
    }
    const response = await axios.get(url);
    blogPosts.value = response.data.data;
    pagination.value = {
      current_page: response.data.current_page,
      last_page: response.data.last_page,
    };
  } catch (error) {
    console.error("Lỗi khi tải tin tức:", error);
  } finally {
    loading.value = false;
  }
};

const fetchCategories = async () => {
  try {
    const response = await axios.get(`${apiUrl}/api/news-categories`);
    categories.value = response.data;
  } catch (error) {
    console.error("Lỗi khi tải danh mục:", error);
  }
};

const fetchRecentPosts = async () => {
    try {
        const response = await axios.get(`${apiUrl}/api/news?per_page=3`);
        recentPosts.value = response.data.data;
    } catch (error) {
        console.error("Lỗi khi tải bài viết gần đây:", error);
    }
};

// --- HÀM XỬ LÝ SỰ KIỆN ---
const handleSearch = () => {
  router.push({ path: '/news', query: { q: searchQuery.value, category: route.query.category } });
};

const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    router.push({ path: '/news', query: { ...route.query, page: page } });
  }
};

// --- VÒNG ĐỜI COMPONENT ---
watch(
  () => route.query,
  (newQuery) => {
    fetchNews(newQuery.page, newQuery.q, newQuery.category);
  },
  { deep: true, immediate: true }
);

onMounted(() => {
  fetchCategories();
  fetchRecentPosts();
});
</script>

<style scoped>
/* Kiểu dáng chung */
a {
  text-decoration: none;
  color: #343a40;
  transition: color 0.3s ease;
}
.post-title a:hover,
.recent-post-item h6 a:hover {
  color: #007bff;
}
h1, h2, h3, h4, h5, h6 {
  font-weight: 700;
  color: #343a40;
}
.blog-post-item {
  display: flex;
  flex-direction: column;
  height: 100%;
}
.post-content {
  flex-grow: 1;
  display: flex;
  flex-direction: column;
}

/* Khu vực Banner */
.blog-banner {
  width: 100%;
  height: 350px;
  position: relative;
  display: flex;
  justify-content: center;
  align-items: center;
  background: 
    linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)),
    url('https://anhphathotel.com/wp-content/uploads/2023/06/resort-thanh-hoa-4.jpg');
  background-size: cover;
  background-position: center;
}
.banner-title {
  font-size: 3.5rem;
  font-weight: 800;
  color: #fff;
}
.breadcrumb {
  background-color: transparent;
  padding: 0;
}
.breadcrumb-item a {
  color: #f1f1f1;
  font-weight: 600;
}
.breadcrumb-item.active {
  color: #ffc107;
}

/* Khu vực Nội dung chính */
.blog-content-area {
  background-color: #ffffff;
}
.post-meta {
  color: #6c757d;
  font-size: 0.85rem;
}
.post-title {
  font-size: 1.25rem;
  margin-top: 10px;
  margin-bottom: 15px;
}
.post-excerpt {
  color: #343a40;
  line-height: 1.5;
  font-size: 0.9rem;
  margin-bottom: 1rem;
}
.btn-sea-primary {
  background-color: #007bff;
  color: #fff;
  padding: 8px 20px;
  font-weight: 600;
  border-radius: 5px;
  border: none;
  transition: background-color 0.3s ease;
  align-self: flex-start;
  margin-top: auto;
}
.btn-sea-primary:hover {
  background-color: #0056b3;
  color: #fff;
}
.post-image img {
    width: 100%;
    height: 200px;
    object-fit: cover;
}

/* Phân trang */
.pagination .page-item .page-link {
  border: 1px solid #dee2e6;
  background-color: #fff;
  margin: 0 5px;
  border-radius: 8px;
  color: #343a40;
  font-weight: 600;
  cursor: pointer;
}
.pagination .page-item.active .page-link {
  background-color: #007bff;
  color: #fff;
  border-color: #007bff;
}
.pagination .page-item.disabled .page-link {
    color: #6c757d;
    pointer-events: none;
    background-color: #e9ecef;
    border-color: #dee2e6;
}
.pagination .page-link:hover {
  background-color: #e9ecef;
}
.pagination .page-item.active .page-link:hover {
  background-color: #007bff;
}

/* Thanh bên (Sidebar) */
.blog-sidebar .sidebar-widget {
  background-color: #f8f9fa;
  border: 1px solid #dee2e6;
}
.widget-title {
  position: relative;
  padding-bottom: 10px;
  border-bottom: 2px solid #dee2e6;
}

/* Widget Tìm kiếm */
.sidebar-widget .form-control:focus {
  box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
  border-color: #007bff;
}
.sidebar-widget .btn-search {
  background-color: #007bff;
  color: white;
}
.sidebar-widget .btn-search:hover {
  background-color: #0056b3;
}

/* Widget Danh mục & Thẻ */
.category-list li a, .tag-cloud a {
  background-color: #fff;
  color: #343a40;
  border: 1px solid #ddd;
  transition: all 0.3s ease;
}
.category-list li a:hover {
  background-color: #007bff;
  color: #fff;
  border-color: #007bff;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
/* Style cho danh mục đang được chọn */
.category-list li a.active-category,
.category-list li a.active-category:hover {
  background-color: #007bff;
  color: #fff;
  border-color: #007bff;
  transform: translateY(0);
  box-shadow: none;
}
.category-list li { margin-bottom: 10px; }
.category-list li a { display: block; padding: 12px 15px; border-radius: 5px; font-weight: 500; }
.category-list li a .bi-arrow-right { opacity: 0; transition: opacity 0.3s ease; }
.category-list li a:hover .bi-arrow-right { opacity: 1; }
.tag-cloud a { display: inline-block; padding: 8px 15px; margin: 0 5px 10px 0; border-radius: 20px; font-size: 0.9rem; }
.tag-cloud a:hover {
  background-color: #007bff;
  color: #fff;
  border-color: #007bff;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

/* Widget Bài viết gần đây */
.recent-post-item img { 
    width: 70px; 
    height: 70px; 
    object-fit: cover;
}
.recent-post-item h6 { font-size: 0.95rem; line-height: 1.3; }
</style>