<template>
  <div class="blog-page-wrapper">
    <!-- Khu vực Banner -->
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
          <div class="col-lg-8 order-2 order-lg-1">
            <div class="blog-posts-wrapper">
              
              <!-- Trạng thái đang tải -->
              <div v-if="loading" class="loading-state text-center py-5">
                <div class="spinner-border text-primary" role="status">
                  <span class="visually-hidden">Đang tải...</span>
                </div>
                <p class="mt-2">Đang tải bài viết, vui lòng chờ...</p>
              </div>

              <!-- Nội dung khi đã tải xong -->
              <div v-else>
                <!-- Lưới chứa các bài viết -->
                <div class="row g-4">
                  <div v-if="!blogPosts.length" class="col-12 text-center py-5">
                    <i class="bi bi-journal-x fs-1 text-muted"></i>
                    <p class="mt-3 fs-5">Không tìm thấy bài viết nào phù hợp.</p>
                  </div>
                  
                  <!-- Vòng lặp qua các bài viết với thiết kế card mới -->
                  <div v-for="post in blogPosts" :key="post.id" class="col-md-6">
                    <div class="blog-post-card">
                      <div class="post-image-wrapper">
                        <router-link :to="`/news/${post.id}`">
                          <img :src="getThumbnailUrl(post.thumbnail)" :alt="post.title" class="post-image">
                        </router-link>
                      </div>
                      <div class="post-content">
                        <div class="post-meta">
                          <span class="meta-item"><i class="bi bi-person me-1"></i>{{ post.author ? post.author.name : 'Admin' }}</span>
                          <span class="meta-item"><i class="bi bi-calendar3 me-1"></i>{{ formatDate(post.publish_date) }}</span>
                        </div>
                        <h3 class="post-title">
                          <router-link :to="`/news/${post.id}`">{{ post.title }}</router-link>
                        </h3>
                        <p class="post-excerpt">{{ post.summary }}</p>
                        <router-link :to="`/news/${post.id}`" class="read-more-link">
                          Đọc thêm <i class="bi bi-arrow-right"></i>
                        </router-link>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Phân trang động -->
                <nav v-if="pagination && pagination.last_page > 1" class="mt-5" aria-label="Page navigation">
                  <ul class="pagination justify-content-center">
                    <li class="page-item" :class="{ 'disabled': pagination.current_page === 1 }">
                      <a class="page-link" href="#" @click.prevent="changePage(pagination.current_page - 1)">
                        <i class="bi bi-chevron-left"></i>
                      </a>
                    </li>
                    <li v-for="page in pagination.last_page" :key="page" class="page-item" :class="{ 'active': page === pagination.current_page }">
                      <a class="page-link" href="#" @click.prevent="changePage(page)">{{ page }}</a>
                    </li>
                    <li class="page-item" :class="{ 'disabled': pagination.current_page === pagination.last_page }">
                      <a class="page-link" href="#" @click.prevent="changePage(pagination.current_page + 1)">
                        <i class="bi bi-chevron-right"></i>
                      </a>
                    </li>
                  </ul>
                </nav>
              </div>

            </div>
          </div>

          <!-- Cột Sidebar -->
          <div class="col-lg-4 order-1 order-lg-2 mb-5 mb-lg-0">
            <aside class="blog-sidebar">
              <!-- Widget Tìm kiếm -->
              <div class="sidebar-widget">
                <h4 class="widget-title">Tìm Kiếm</h4>
                <div class="search-form">
                  <input type="text" v-model="searchQuery" @keyup.enter="handleSearch" class="form-control" placeholder="Nhập từ khóa...">
                  <button class="btn-search" type="button" @click="handleSearch"><i class="bi bi-search"></i></button>
                </div>
              </div>

              <!-- Widget Danh mục -->
              <div class="sidebar-widget">
                <h4 class="widget-title">Danh Mục</h4>
                <ul class="category-list list-unstyled">
                  <li>
                    <router-link to="/news" :class="{ 'active-category': isAllCategoriesActive }">
                      <span>Tất cả danh mục</span>
                      <i class="bi bi-chevron-right"></i>
                    </router-link>
                  </li>
                  <li v-for="category in categories" :key="category.id">
                    <router-link :to="{ path: '/news', query: { category: category.id } }" :class="{ 'active-category': isCategoryActive(category.id) }">
                      <span>{{ category.name }}</span>
                      <i class="bi bi-chevron-right"></i>
                    </router-link>
                  </li>
                </ul>
              </div>

              <!-- Widget Bài viết nổi bật -->
              <div v-if="pinnedPosts.length > 0" class="sidebar-widget">
                <h4 class="widget-title">Bài Viết Nổi Bật</h4>
                <div v-for="post in pinnedPosts" :key="post.id" class="recent-post-item">
                  <router-link :to="`/news/${post.id}`" class="recent-post-img-link">
                    <img :src="getThumbnailUrl(post.thumbnail)" :alt="post.title" class="recent-post-img">
                  </router-link>
                  <div class="recent-post-info">
                    <h6 class="recent-post-title"><router-link :to="`/news/${post.id}`">{{ post.title }}</router-link></h6>
                    <small class="recent-post-date"><i class="bi bi-calendar3 me-1"></i>{{ formatDate(post.publish_date) }}</small>
                  </div>
                </div>
              </div>

              <!-- Widget Bài viết gần đây -->
              <div class="sidebar-widget">
                <h4 class="widget-title">Bài Viết Gần Đây</h4>
                <div v-for="post in recentPosts" :key="post.id" class="recent-post-item">
                   <router-link :to="`/news/${post.id}`" class="recent-post-img-link">
                    <img :src="getThumbnailUrl(post.thumbnail)" :alt="post.title" class="recent-post-img">
                  </router-link>
                  <div class="recent-post-info">
                    <h6 class="recent-post-title"><router-link :to="`/news/${post.id}`">{{ post.title }}</router-link></h6>
                    <small class="recent-post-date"><i class="bi bi-calendar3 me-1"></i>{{ formatDate(post.publish_date) }}</small>
                  </div>
                </div>
              </div>
            </aside>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
// --- PHẦN SCRIPT GIỮ NGUYÊN KHÔNG THAY ĐỔI ---
import { ref, onMounted, inject, watch, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';

// --- QUẢN LÝ TRẠNG THÁI ---
const route = useRoute();
const router = useRouter();
const blogPosts = ref([]);
const categories = ref([]);
const recentPosts = ref([]);
const pinnedPosts = ref([]);
const pagination = ref({});
const loading = ref(true);
const searchQuery = ref('');

const apiUrl = inject('apiUrl');

const isAllCategoriesActive = computed(() => !route.query.category);
const isCategoryActive = (categoryId) => Number(route.query.category) === categoryId;

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
    if (query) url += `&q=${query}`;
    if (categoryId) url += `&category_id=${categoryId}`;
    
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

const fetchPinnedPosts = async () => {
  try {
    const response = await axios.get(`${apiUrl}/api/news/pinned`);
    pinnedPosts.value = response.data;
  } catch (error) {
    console.error("Lỗi khi tải bài viết nổi bật:", error);
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
watch(() => route.query,
  (newQuery) => {
    fetchNews(newQuery.page, newQuery.q, newQuery.category);
  },
  { deep: true, immediate: true }
);

onMounted(() => {
  fetchCategories();
  fetchRecentPosts();
  fetchPinnedPosts();
});
</script>

<style>
/* Thêm Google Font */
@import url('https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;700;800&display=swap');
</style>

<style scoped>
/* --- THIẾT KẾ TỔNG THỂ --- */
.blog-page-wrapper {
  font-family: 'Public Sans', sans-serif;
  background-color: #ffffff;
}
a {
  text-decoration: none;
  color: #212529;
}
h1, h2, h3, h4, h5, h6 {
  font-weight: 700;
  color: #212529;
}

/* --- BANNER --- */
.blog-banner {
  width: 100%;
  height: 350px;
  position: relative;
  display: flex;
  justify-content: center;
  align-items: center;
  background: 
    linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)),
    url('https://anhphathotel.com/wp-content/uploads/2023/06/resort-thanh-hoa-4.jpg') center center / cover no-repeat;
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

/* --- KHU VỰC NỘI DUNG CHÍNH --- */
.blog-content-area {
  background-color: #f8f9fa;
}

/* --- CARD BÀI VIẾT MỚI --- */
.blog-post-card {
  background-color: #fff;
  border: 1px solid #e9ecef;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  height: 100%;
  display: flex;
  flex-direction: column;
}
.blog-post-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}
.post-image-wrapper {
  overflow: hidden;
  height: 220px;
}
.post-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.4s ease;
}
.blog-post-card:hover .post-image {
  transform: scale(1.05);
}
.post-content {
  padding: 20px;
  flex-grow: 1;
  display: flex;
  flex-direction: column;
}
.post-meta {
  display: flex;
  gap: 1rem;
  font-size: 0.85rem;
  color: #6c757d;
  margin-bottom: 0.75rem;
}
.post-meta .meta-item {
  display: flex;
  align-items: center;
}
.post-title {
  font-size: 1.25rem;
  margin-bottom: 1rem;
  line-height: 1.4;
}
.post-title a {
  color: #212529;
  transition: color 0.3s ease;
}
.post-title a:hover {
  color: #937451;
}
.post-excerpt {
  color: #6c757d;
  font-size: 0.95rem;
  line-height: 1.6;
  margin-bottom: 1.5rem;
}
.read-more-link {
  font-weight: 600;
  color: #937451;
  transition: color 0.3s ease;
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  margin-top: auto;
  align-self: flex-start;
}
.read-more-link:hover {
  color: #c57820;
}
.read-more-link .bi-arrow-right {
  transition: transform 0.3s ease;
}
.read-more-link:hover .bi-arrow-right {
  transform: translateX(4px);
}

/* --- PHÂN TRANG --- */
.pagination {
  gap: 0.5rem;
}
.pagination .page-item .page-link {
  border: 1px solid #e9ecef;
  background-color: #fff;
  border-radius: 8px;
  color: #6c757d;
  font-weight: 600;
  min-width: 40px;
  height: 40px;
  display: flex;
  justify-content: center;
  align-items: center;
  transition: all 0.3s ease;
}
.pagination .page-item .page-link:hover {
  background-color: #f8f9fa;
  color: #937451;
}
.pagination .page-item.active .page-link {
  background-color: #937451;
  color: #fff;
  border-color: #937451;
}
.pagination .page-item.disabled .page-link {
  color: #adb5bd;
  background-color: #f8f9fa;
  pointer-events: none;
}

/* --- SIDEBAR --- */
.blog-sidebar {
  display: flex;
  flex-direction: column;
  gap: 1.75rem;
}
.sidebar-widget {
  background-color: #fff;
  padding: 25px;
  border-radius: 12px;
  border: 1px solid #e9ecef;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
}
.widget-title {
  font-size: 1.3rem;
  padding-bottom: 10px;
  margin-bottom: 20px;
  position: relative;
}
.widget-title::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  width: 40px;
  height: 3px;
  background-color: #937451;
  border-radius: 2px;
}

/* Widget Tìm kiếm */
.search-form {
  display: flex;
  position: relative;
}
.search-form .form-control {
  height: 48px;
  padding-left: 15px;
  padding-right: 50px;
  border: 1px solid #e9ecef;
  border-radius: 8px;
}
.search-form .form-control:focus {
  border-color: #937451;
  box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}
.search-form .btn-search {
  position: absolute;
  right: 5px;
  top: 50%;
  transform: translateY(-50%);
  background: #937451;
  color: white;
  border: none;
  width: 40px;
  height: 40px;
  border-radius: 6px;
  font-size: 1rem;
}

/* Widget Danh mục */
.category-list li { margin-bottom: 8px; }
.category-list li a {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 15px;
  border-radius: 8px;
  font-weight: 500;
  color: #6c757d;
  background-color: #f8f9fa;
  transition: all 0.3s ease;
}
.category-list li a:hover {
  background-color: #937451;
  color: #fff;
  transform: translateX(5px);
}
.category-list li a.active-category,
.category-list li a.active-category:hover {
  background-color: #937451;
  color: #fff;
  transform: translateX(0);
}

/* Widget Bài viết gần đây & Nổi bật */
.recent-post-item {
  display: flex;
  align-items: center;
  gap: 15px;
}
.recent-post-item:not(:last-child) {
  margin-bottom: 20px;
}
.recent-post-img-link {
  flex-shrink: 0;
}
.recent-post-img {
  width: 75px;
  height: 75px;
  object-fit: cover;
  border-radius: 8px;
}
.recent-post-info {
  flex-grow: 1;
}
.recent-post-title {
  font-size: 0.95rem;
  line-height: 1.4;
  font-weight: 600;
  margin-bottom: 5px;
}
.recent-post-title a:hover {
  color: #937451;
}
.recent-post-date {
  color: #6c757d;
  font-size: 0.8rem;
}

/* --- RESPONSIVE --- */
@media (max-width: 576px) {
  .banner-title {
    font-size: 2.5rem;
  }
  .post-meta {
    flex-direction: column;
    gap: 0.5rem;
    align-items: flex-start;
  }
}
</style>