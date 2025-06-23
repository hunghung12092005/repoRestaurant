<template>
  <div class="blog-page-wrapper">
    <!-- Banner Section with Background Image -->
    <section class="blog-banner">
      <div class="banner-content-wrapper text-center">
        <h1 class="banner-title">News & Blogs</h1>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb justify-content-center">
            <li class="breadcrumb-item"><a href="/">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">News</li>
          </ol>
        </nav>
      </div>
    </section>

    <!-- Main Content Section -->
    <section class="blog-content-area py-5">
      <div class="container">
        <div class="row">
          <!-- Blog Posts Column -->
          <div class="col-lg-8">
            <div class="blog-posts-wrapper">
              
              <!-- Loading State -->
              <div v-if="loading" class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                  <span class="visually-hidden">Loading...</span>
                </div>
              </div>

              <!-- Content when loaded -->
              <div v-else>
                <!-- Grid container for blog posts -->
                <div class="row">
                  <div v-if="!blogPosts.length" class="col-12 text-center py-5">
                    <p>No news articles found.</p>
                  </div>
                  <!-- Loop through posts from API -->
                  <div v-for="post in blogPosts" :key="post.id" class="col-md-6">
                    <div class="blog-post-item mb-5">
                      <div class="post-image">
                        <img :src="getThumbnailUrl(post.thumbnail)" :alt="post.title" class="img-fluid rounded">
                      </div>
                      <div class="post-content p-3">
                        <div class="post-meta d-flex align-items-center mb-2">
                          <span class="me-4"><i class="bi bi-person me-2"></i>By {{ post.author ? post.author.name : 'Unknown' }}</span>
                          <span><i class="bi bi-calendar-event me-2"></i>{{ formatDate(post.publish_date) }}</span>
                        </div>
                        <h3 class="post-title">
                          <router-link :to="`/news/${post.id}`">{{ post.title }}</router-link>
                        </h3>
                        <p class="post-excerpt">{{ post.summary }}</p>
                        <!-- Đã xóa class mt-2 vì đã xử lý trong CSS -->
                        <router-link :to="`/news/${post.id}`" class="btn btn-sea-primary mt-2">READ MORE</router-link>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Dynamic Pagination -->
                <nav v-if="pagination && pagination.last_page > 1" aria-label="Page navigation">
                  <ul class="pagination justify-content-start mt-4">
                    <li class="page-item" :class="{ 'disabled': pagination.current_page === 1 }">
                      <a class="page-link" href="#" @click.prevent="changePage(pagination.current_page - 1)">Prev</a>
                    </li>
                    <li v-for="page in pagination.last_page" :key="page" class="page-item" :class="{ 'active': page === pagination.current_page }">
                      <a class="page-link" href="#" @click.prevent="changePage(page)">{{ page }}</a>
                    </li>
                    <li class="page-item" :class="{ 'disabled': pagination.current_page === pagination.last_page }">
                      <a class="page-link" href="#" @click.prevent="changePage(pagination.current_page + 1)">Next</a>
                    </li>
                  </ul>
                </nav>
              </div>

            </div>
          </div>

          <!-- Sidebar Column -->
          <div class="col-lg-4">
            <div class="blog-sidebar">
              <div class="sidebar-widget p-4 rounded mb-4">
                <h4 class="widget-title mb-3">Search Here</h4>
                <div class="input-group">
                  <input type="text" v-model="searchQuery" @keyup.enter="handleSearch" class="form-control" placeholder="Search here..." aria-label="Search here">
                  <button class="btn btn-search" type="button" @click="handleSearch"><i class="bi bi-search"></i></button>
                </div>
              </div>

              <div class="sidebar-widget p-4 rounded mb-4">
                <h4 class="widget-title mb-3">Categories</h4>
                <ul class="list-unstyled category-list">
                  <li v-for="category in categories" :key="category.id">
                    <a href="#" class="d-flex justify-content-between align-items-center">
                      <span>{{ category.name }}</span>
                    </a>
                  </li>
                </ul>
              </div>

              <div class="sidebar-widget p-4 rounded mb-4">
                <h4 class="widget-title mb-3">Recent Post</h4>
                <div v-for="post in recentPosts" :key="post.id" class="recent-post-item d-flex align-items-center mb-3">
                  <img :src="getThumbnailUrl(post.thumbnail)" :alt="post.title" class="rounded me-3">
                  <div>
                    <h6 class="mb-1"><router-link :to="`/news/${post.id}`">{{ post.title }}</router-link></h6>
                    <small class="text-muted"><i class="bi bi-calendar-event me-1"></i>{{ formatDate(post.publish_date) }}</small>
                  </div>
                </div>
              </div>
              
              <div class="sidebar-widget p-4 rounded mb-4">
                <h4 class="widget-title mb-3">Popular Tags</h4>
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
import { ref, onMounted, inject } from 'vue';
import axios from 'axios';
import { Modal } from 'bootstrap';

// --- STATE MANAGEMENT ---
const blogPosts = ref([]);
const categories = ref([]);
const recentPosts = ref([]);
const pagination = ref({});
const loading = ref(true);
const searchQuery = ref('');

const popularTags = ref([
    { name: 'Fast Foods' }, { name: 'Lunch' }, { name: 'Restaurant' },
    { name: 'Burger' }, { name: 'Dinner' }, { name: 'Chicken' },
]);

const apiUrl = inject('apiUrl');

// --- HELPER FUNCTIONS ---
const getThumbnailUrl = (thumbnail) => {
  if (thumbnail) {
    return `${apiUrl}/images/news_thumbnails/${thumbnail}`;
  }
  return 'https://via.placeholder.com/400x300.png?text=No+Image';
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  
  const date = new Date(dateString);
  
  const day = date.getDate();
  const month = date.getMonth() + 1;
  const year = date.getFullYear();

  const formattedDay = String(day).padStart(2, '0');
  const formattedMonth = String(month).padStart(2, '0');

  return `${formattedDay}/${formattedMonth}/${year}`;
};

// --- API CALLS ---
const fetchNews = async (page = 1, query = '') => {
  loading.value = true;
  try {
    let url = `${apiUrl}/api/news?page=${page}`;
    if (query) {
      url += `&q=${query}`;
    }
    const response = await axios.get(url);
    blogPosts.value = response.data.data;
    pagination.value = {
      current_page: response.data.current_page,
      last_page: response.data.last_page,
    };
  } catch (error) {
    console.error("Failed to fetch news:", error);
  } finally {
    loading.value = false;
  }
};

const fetchCategories = async () => {
  try {
    const response = await axios.get(`${apiUrl}/api/news-categories`);
    categories.value = response.data;
  } catch (error) {
    console.error("Failed to fetch categories:", error);
  }
};

const fetchRecentPosts = async () => {
    try {
        const response = await axios.get(`${apiUrl}/api/news?per_page=3`);
        recentPosts.value = response.data.data;
    } catch (error) {
        console.error("Failed to fetch recent posts:", error);
    }
};

// --- EVENT HANDLERS ---
const handleSearch = () => {
  fetchNews(1, searchQuery.value);
};

const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    fetchNews(page, searchQuery.value);
  }
};

// --- LIFECYCLE HOOK ---
onMounted(() => {
  fetchNews();
  fetchCategories();
  fetchRecentPosts();
});
</script>

<style scoped>
/* General Styling */
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
/* ĐÃ XÓA flex-grow: 1; để nút không bị đẩy xuống dưới cùng */
.post-excerpt {
  /* flex-grow: 1; */
}

/* Banner Section */
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

/* Main Content Area */
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
  margin-bottom: 1rem; /* Thêm khoảng cách dưới cho đoạn văn */
}
/* ĐÃ CẬP NHẬT: margin-top: auto để đẩy nút xuống */
.btn-sea-primary {
  background-color: #007bff;
  color: #fff;
  padding: 8px 20px;
  font-weight: 600;
  border-radius: 5px;
  border: none;
  transition: background-color 0.3s ease;
  align-self: flex-start;
  margin-top: auto; /* Đẩy nút xuống dưới cùng của flex container */
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

/* Pagination */
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

/* Sidebar */
.blog-sidebar .sidebar-widget {
  background-color: #f8f9fa;
  border: 1px solid #dee2e6;
}
.widget-title {
  position: relative;
  padding-bottom: 10px;
  border-bottom: 2px solid #dee2e6;
}

/* Search Widget */
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

/* Categories & Tags */
.category-list li a, .tag-cloud a {
  background-color: #fff;
  color: #343a40;
  border: 1px solid #ddd;
  transition: all 0.3s ease;
}
.category-list li a:hover, .tag-cloud a:hover {
  background-color: #007bff;
  color: #fff;
  border-color: #007bff;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
.category-list li { margin-bottom: 10px; }
.category-list li a { display: block; padding: 12px 15px; border-radius: 5px; font-weight: 500; }
.category-list li a .bi-arrow-right { opacity: 0; transition: opacity 0.3s ease; }
.category-list li a:hover .bi-arrow-right { opacity: 1; }
.tag-cloud a { display: inline-block; padding: 8px 15px; margin: 0 5px 10px 0; border-radius: 20px; font-size: 0.9rem; }

/* Recent Post Widget */
.recent-post-item img { 
    width: 70px; 
    height: 70px; 
    object-fit: cover;
}
.recent-post-item h6 { font-size: 0.95rem; line-height: 1.3; }
</style>