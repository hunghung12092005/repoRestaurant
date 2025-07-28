<template>
  <div class="blog-page-wrapper">
    <!-- Khu vực Banner -->
    <section class="blog-banner">
      <div class="banner-content-wrapper text-center">
        <h1 class="banner-title">Tin Tức & Blog</h1>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb justify-content-center">
            <li class="breadcrumb-item"><a href="/">Trang Chủ</a></li>
            <li class="breadcrumb-item"><a href="/news">Tin Tức</a></li>
            <li class="breadcrumb-item active" aria-current="page">Chi tiết</li>
          </ol>
        </nav>
      </div>
    </section>

    <!-- Khu vực Nội dung chính -->
    <section class="blog-content-area py-5">
      <div class="container">
        <div class="row">
          <!-- Cột nội dung chi tiết bài viết (Thay đổi class order) -->
          <div class="col-lg-8 order-2 order-lg-1">
            <!-- Trạng thái đang tải -->
            <div v-if="loading" class="loading-state text-center py-5">
              <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="visually-hidden">Đang tải...</span>
              </div>
              <p class="mt-3">Đang tải nội dung bài viết...</p>
            </div>

            <!-- Nội dung chi tiết khi đã tải xong -->
            <article v-else-if="postDetail" class="news-detail-wrapper">
              <header class="post-header mb-4">
                <h1 class="post-detail-title">{{ postDetail.title }}</h1>
                <div class="post-detail-meta">
                  <span class="meta-item" title="Tác giả"><i class="bi bi-person-fill"></i> {{ postDetail.author ? postDetail.author.name : 'Admin' }}</span>
                  <span class="meta-item" title="Danh mục"><i class="bi bi-folder-fill"></i> {{ postDetail.category?.name || 'N/A' }}</span>
                  <span class="meta-item" title="Ngày đăng"><i class="bi bi-calendar-event-fill"></i> {{ formatDate(postDetail.publish_date) }}</span>
                  <span class="meta-item" title="Lượt xem"><i class="bi bi-eye-fill"></i> {{ postDetail.views.toLocaleString('vi-VN') }}</span>
                </div>
              </header>
              
              <div class="post-detail-content" v-html="postDetail.content"></div>

              <!-- Khu vực Bình luận (Thiết kế lại) -->
              <div class="comments-section-wrapper mt-5">
                <h3 class="comments-title">Bình luận ({{ allComments.length }})</h3>
                
                <!-- Form gửi bình luận -->
                <div class="comment-form-wrapper">
                  <h4 class="comment-form-title">Để lại bình luận của bạn</h4>
                  <form @submit.prevent="submitComment">
                    <div class="mb-3">
                      <textarea class="form-control" v-model="newComment.content" rows="4" placeholder="Nội dung bình luận..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-submit-comment" :disabled="isSubmittingComment">
                      <span v-if="isSubmittingComment" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                      {{ isSubmittingComment ? 'Đang gửi...' : 'Gửi bình luận' }}
                    </button>
                  </form>
                </div>
                
                <!-- Danh sách bình luận -->
                <div class="comment-list mt-5">
                  <ul v-if="visibleComments.length" class="list-unstyled">
                    <li v-for="comment in visibleComments" :key="comment.id" class="comment-item">
                      <div class="initials-avatar" :style="{ backgroundColor: getAvatarColor(comment.user ? comment.user.name : 'Khách') }">
                        <span>{{ getInitials(comment.user ? comment.user.name : 'Khách') }}</span>
                      </div>
                      <div class="comment-body">
                        <div class="comment-header">
                          <h6 class="comment-author">{{ comment.user ? comment.user.name : 'Khách' }}</h6>
                          <small class="comment-date text-muted" :title="formatDate(comment.created_at)">
                            {{ formatTimeAgo(comment.created_at) }}
                          </small>
                        </div>
                        <p class="comment-text mt-1">{{ comment.content }}</p>
                      </div>
                    </li>
                  </ul>
                  <div v-else class="text-center text-muted p-4">
                    Chưa có bình luận nào. Hãy là người đầu tiên!
                  </div>

                  <!-- Nút "Tải thêm / Thu gọn" -->
                  <div v-if="showToggleButton" class="text-center mt-4">
                      <button @click="toggleCommentsView" class="btn btn-toggle-comments">
                          {{ toggleButtonText }}
                      </button>
                  </div>
                </div>
              </div>
            </article>
            
            <!-- Trường hợp không tìm thấy bài viết -->
            <div v-else class="text-center py-5">
              <h2>404 - Không tìm thấy bài viết</h2>
              <p>Bài viết bạn đang tìm kiếm không tồn tại hoặc đã bị xóa.</p>
              <router-link to="/news" class="btn btn-primary">Quay lại trang tin tức</router-link>
            </div>
          </div>

          <!-- Cột Sidebar (Thay đổi class order và cấu trúc để đồng bộ) -->
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

              <!-- Widget Danh Mục -->
              <div class="sidebar-widget">
                <h4 class="widget-title">Danh Mục</h4>
                <ul v-if="categories.length" class="category-list list-unstyled">
                  <li>
                    <router-link to="/news">
                      <span>Tất cả danh mục</span>
                      <i class="bi bi-chevron-right"></i>
                    </router-link>
                  </li>
                  <li v-for="category in categories" :key="category.id">
                    <router-link :to="{ path: '/news', query: { category: category.id } }">
                      <span>{{ category.name }}</span>
                      <i class="bi bi-chevron-right"></i>
                    </router-link>
                  </li>
                </ul>
                <div v-else class="text-muted">Đang tải danh mục...</div>
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
                <div v-if="recentPosts.length">
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
                <div v-else class="text-muted">Không có bài viết nào.</div>
              </div>

              <!-- Widget Quảng cáo -->
              <div class="sidebar-widget p-0">
                 <AdPlaceholder />
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
import AdPlaceholder from '../AdPlaceholder.vue';

const route = useRoute();
const router = useRouter();
const apiUrl = inject('apiUrl');

// State cho trang chi tiết
const postDetail = ref(null);
const newComment = ref({ content: '' });
const loading = ref(true);
const isSubmittingComment = ref(false);

// State cho bình luận
const COMMENTS_INITIAL_LIMIT = 10;
const allComments = ref([]);
const isCommentsExpanded = ref(false);

// State cho Sidebar
const categories = ref([]);
const recentPosts = ref([]);
const pinnedPosts = ref([]);
const searchQuery = ref('');

// Computed Properties
const visibleComments = computed(() => {
    if (isCommentsExpanded.value) {
        return allComments.value;
    }
    return allComments.value.slice(0, COMMENTS_INITIAL_LIMIT);
});

const showToggleButton = computed(() => {
    return allComments.value.length > COMMENTS_INITIAL_LIMIT;
});

const toggleButtonText = computed(() => {
    return isCommentsExpanded.value ? 'Thu gọn bình luận' : 'Tải thêm bình luận';
});

// Helper Functions
const getThumbnailUrl = (thumbnail) => {
  return thumbnail ? `${apiUrl}/images/news_thumbnails/${thumbnail}` : 'https://via.placeholder.com/400x300.png?text=No+Image';
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  const day = String(date.getDate()).padStart(2, '0');
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const year = date.getFullYear();
  return `${day}/${month}/${year}`;
};

const formatTimeAgo = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    const now = new Date();
    const seconds = Math.round((now - date) / 1000);
    if (seconds < 60) return "Vài giây trước";
    const minutes = Math.round(seconds / 60);
    if (minutes < 60) return `${minutes} phút trước`;
    const hours = Math.round(minutes / 60);
    if (hours < 24) return `${hours} giờ trước`;
    const days = Math.round(hours / 24);
    if (days < 30) return `${days} ngày trước`;
    const months = Math.round(days / 30.44);
    if (months < 12) return `${months} tháng trước`;
    const years = Math.round(days / 365);
    return `${years} năm trước`;
};

const getInitials = (name) => {
  if (!name || typeof name !== 'string' || name.trim() === '') {
    return '?';
  }
  const words = name.trim().split(/\s+/).filter(Boolean);
  if (words.length === 0) {
    return '?';
  }
  if (words.length === 1) {
    return words[0].charAt(0).toUpperCase();
  }
  const firstInitial = words[0].charAt(0);
  const lastInitial = words[words.length - 1].charAt(0);
  return (firstInitial + lastInitial).toUpperCase();
};

const getAvatarColor = (name) => {
  if (!name || name.trim() === '') {
    return '#6c757d';
  }
  let hash = 0;
  for (let i = 0; i < name.length; i++) {
    hash = name.charCodeAt(i) + ((hash << 5) - hash);
  }
  const hue = hash % 360;
  return `hsl(${hue}, 60%, 45%)`;
};


// API Functions
const fetchNewsDetail = async (id) => {
  loading.value = true;
  postDetail.value = null; 
  allComments.value = [];
  isCommentsExpanded.value = false;
  try {
    const response = await axios.get(`${apiUrl}/api/news/${id}`);
    postDetail.value = response.data;
    await fetchComments(id); 
  } catch (error) {
    console.error("Lỗi khi tải chi tiết tin tức:", error);
    postDetail.value = false;
  } finally {
    loading.value = false;
  }
};

const fetchComments = async (id) => {
    try {
        const response = await axios.get(`${apiUrl}/api/news/${id}/comments`);
        allComments.value = response.data;
    } catch (error) {
        console.error("Lỗi khi tải bình luận:", error);
    }
}

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
    const response = await axios.get(`${apiUrl}/api/news?per_page=5`);
    const currentPostId = parseInt(route.params.id, 10);
    recentPosts.value = response.data.data.filter(post => post.id !== currentPostId);
  } catch (error) {
    console.error("Lỗi khi tải bài viết gần đây:", error);
  }
};

const fetchPinnedPosts = async () => {
  try {
    const response = await axios.get(`${apiUrl}/api/news/pinned`);
    const currentPostId = parseInt(route.params.id, 10);
    pinnedPosts.value = response.data.filter(post => post.id !== currentPostId);
  } catch (error) {
    console.error("Lỗi khi tải bài viết nổi bật:", error);
  }
};

// Event Handlers
const submitComment = async () => {
    const trimmedContent = newComment.value.content.trim();
    if (trimmedContent.length < 3) {
        alert("Nội dung bình luận phải có ít nhất 3 ký tự.");
        return;
    }
    const userInfo = localStorage.getItem('userInfo');
    if (!userInfo) {
        alert('Bạn cần đăng nhập để bình luận.');
        router.push('/login');
        return;
    }
    const user = JSON.parse(userInfo);
    if (!user || !user.id) {
         alert('Không tìm thấy thông tin người dùng. Vui lòng đăng nhập lại.');
         router.push('/login');
         return;
    }
    isSubmittingComment.value = true;
    try {
        const payload = {
            content: trimmedContent,
            user_id: user.id
        };
        const response = await axios.post(
            `${apiUrl}/api/news/${postDetail.value.id}/comments`, 
            payload
        );
        allComments.value.unshift(response.data);
        newComment.value.content = ''; 
    } catch (error) {
        console.error("Lỗi khi gửi bình luận:", error.response?.data || error.message);
        alert('Đã có lỗi xảy ra khi gửi bình luận. Vui lòng thử lại.');
    } finally {
        isSubmittingComment.value = false;
    }
}

const toggleCommentsView = () => {
    isCommentsExpanded.value = !isCommentsExpanded.value;
}

const handleSearch = () => {
  if(searchQuery.value.trim()){
    router.push({ path: '/news', query: { q: searchQuery.value } });
  }
};

// Lifecycle Hooks
onMounted(() => {
  const newsId = route.params.id;
  if (newsId) {
    fetchNewsDetail(newsId);
    fetchCategories();
    fetchRecentPosts();
    fetchPinnedPosts();
  }
});

watch(() => route.params.id, (newId, oldId) => {
    if (newId && newId !== oldId) {
        window.scrollTo(0, 0);
        fetchNewsDetail(newId);
        fetchRecentPosts();
        fetchPinnedPosts();
    }
});
</script>


<style>
/* Thêm Google Font */
@import url('https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700;800&display=swap');
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
  width: 100%; height: 250px; position: relative; display: flex;
  justify-content: center; align-items: center;
  background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)),
    url('https://anhphathotel.com/wp-content/uploads/2023/06/resort-thanh-hoa-4.jpg') center center / cover no-repeat;
}
.banner-title { font-size: 3rem; font-weight: 800; color: #fff; }
.breadcrumb-item a { color: #f1f1f1; font-weight: 500; }
.breadcrumb-item.active { color: #ffc107; }

/* --- KHU VỰC NỘI DUNG --- */
.blog-content-area { background-color: #f8f9fa; }
.news-detail-wrapper {
  background-color: #fff;
  padding: 2rem;
  border-radius: 12px;
  border: 1px solid #e9ecef;
}

/* --- CHI TIẾT BÀI VIẾT --- */
.post-detail-title {
  font-size: 2.25rem;
  font-weight: 800;
  color: #212529;
  line-height: 1.3;
  margin-bottom: 1rem;
}
.post-detail-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem 1.5rem;
  color: #6c757d;
  font-size: 0.9rem;
  padding-bottom: 1.5rem;
  border-bottom: 1px solid #e9ecef;
  margin-bottom: 1.5rem;
}
.post-detail-meta .meta-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

/* Định dạng nội dung từ v-html */
.post-detail-content {
    line-height: 1.8;
    word-wrap: break-word;
    font-size: 1.05rem;
    color: #343a40;
}
.post-detail-content :deep(p) { margin-bottom: 1.25rem; }
.post-detail-content :deep(h1),
.post-detail-content :deep(h2),
.post-detail-content :deep(h3) {
    margin-top: 2.5rem;
    margin-bottom: 1.25rem;
    font-weight: 700;
    line-height: 1.4;
    color: #1a1a1a;
}
.post-detail-content :deep(img) {
    max-width: 100%;
    height: auto;
    border-radius: 12px;
    margin: 1.5rem 0;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
}
.post-detail-content :deep(blockquote) {
    border-left: 4px solid #007bff;
    padding: 1rem 1.5rem;
    margin: 2rem 0;
    font-style: italic;
    color: #495057;
    background-color: #f8f9fa;
    border-radius: 0 8px 8px 0;
}
.post-detail-content :deep(ul), .post-detail-content :deep(ol) {
    padding-left: 2rem;
    margin-bottom: 1.5rem;
}
.post-detail-content :deep(li) {
    margin-bottom: 0.5rem;
}

/* --- KHU VỰC BÌNH LUẬN --- */
.comments-section-wrapper {
  border-top: 1px solid #e9ecef;
  padding-top: 2rem;
  margin-top: 3rem;
  background-color: #fff;
}
.comments-title {
  font-size: 1.75rem;
  margin-bottom: 1.5rem;
}
.comment-form-wrapper {
  background-color: #f8f9fa;
  padding: 1.5rem;
  border-radius: 12px;
}
.comment-form-title {
  font-size: 1.25rem;
  font-weight: 600;
  margin-bottom: 1rem;
}
.comment-form-wrapper .form-control {
  border-radius: 8px;
  border: 1px solid #dee2e6;
}
.comment-form-wrapper .form-control:focus {
  border-color: #007bff;
  box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}
.btn-submit-comment {
  background-color: #007bff;
  color: #fff;
  border: none;
  border-radius: 8px;
  padding: 10px 20px;
  font-weight: 600;
  transition: background-color 0.3s ease;
}
.btn-submit-comment:hover:not(:disabled) {
  background-color: #0056b3;
}
.comment-item {
  display: flex;
  gap: 1rem;
  padding-bottom: 1.5rem;
  margin-bottom: 1.5rem;
  border-bottom: 1px solid #e9ecef;
}
.comment-list .comment-item:last-child {
  border-bottom: none;
  margin-bottom: 0;
}
.initials-avatar {
  width: 50px; height: 50px;
  color: white; font-weight: 600; font-size: 1.25rem;
  flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
  border-radius: 50%;
}
.comment-header {
  display: flex; justify-content: space-between; align-items: baseline;
  flex-wrap: wrap; gap: 10px;
}
.comment-author { font-weight: 600; color: #343a40; margin-bottom: 0; }
.comment-date { font-size: 0.8rem; flex-shrink: 0; }
.comment-text { color: #495057; line-height: 1.6; font-size: 0.95rem; }
.btn-toggle-comments {
  font-weight: 600; color: #007bff;
  border: 1px solid #007bff;
}
.btn-toggle-comments:hover {
  background-color: #007bff; color: #fff;
}

/* --- SIDEBAR (GIỐNG HỆT TRANG LIST) --- */
.blog-sidebar {
  display: flex; flex-direction: column; gap: 1.75rem;
}
.sidebar-widget {
  background-color: #fff; padding: 25px; border-radius: 12px;
  border: 1px solid #e9ecef;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
}
.widget-title {
  font-size: 1.3rem; padding-bottom: 10px; margin-bottom: 20px;
  position: relative;
}
.widget-title::after {
  content: ''; position: absolute; bottom: 0; left: 0;
  width: 40px; height: 3px; background-color: #007bff; border-radius: 2px;
}
.search-form { display: flex; position: relative; }
.search-form .form-control {
  height: 48px; padding-left: 15px; padding-right: 50px;
  border: 1px solid #dee2e6; border-radius: 8px;
}
.search-form .form-control:focus {
  border-color: #007bff; box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}
.search-form .btn-search {
  position: absolute; right: 5px; top: 50%; transform: translateY(-50%);
  background: #007bff; color: white; border: none;
  width: 40px; height: 40px; border-radius: 6px; font-size: 1rem;
}
.category-list li { margin-bottom: 8px; }
.category-list li a {
  display: flex; justify-content: space-between; align-items: center;
  padding: 12px 15px; border-radius: 8px; font-weight: 500;
  color: #6c757d; background-color: #f8f9fa; transition: all 0.3s ease;
}
.category-list li a:hover {
  background-color: #007bff; color: #fff; transform: translateX(5px);
}
.recent-post-item { display: flex; align-items: center; gap: 15px; }
.recent-post-item:not(:last-child) { margin-bottom: 20px; }
.recent-post-img-link { flex-shrink: 0; }
.recent-post-img {
  width: 75px; height: 75px; object-fit: cover; border-radius: 8px;
}
.recent-post-info { flex-grow: 1; }
.recent-post-title { font-size: 0.95rem; line-height: 1.4; font-weight: 600; margin-bottom: 5px; }
.recent-post-title a:hover { color: #007bff; }
.recent-post-date { color: #6c757d; font-size: 0.8rem; }

/* --- RESPONSIVE --- */
@media (min-width: 992px) {
  .blog-sidebar {
    position: sticky;
    top: 20px;
  }
}
@media (max-width: 768px) {
  .post-detail-title { font-size: 1.75rem; }
  .news-detail-wrapper { padding: 1.5rem; }
}
@media (max-width: 576px) {
  .banner-title { font-size: 2.5rem; }
  .post-detail-meta { gap: 0.5rem 1rem; }
}
</style>