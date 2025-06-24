<template>
  <div class="blog-page-wrapper">
    <!-- Khu vực Banner (Giữ nguyên) -->
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
          <!-- Cột nội dung chi tiết bài viết -->
          <div class="col-lg-8">
            <!-- Trạng thái đang tải -->
            <div v-if="loading" class="text-center py-5">
              <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="visually-hidden">Đang tải...</span>
              </div>
            </div>

            <!-- Nội dung chi tiết khi đã tải xong -->
            <div v-else-if="postDetail" class="news-detail-wrapper">
              <h1 class="post-detail-title">{{ postDetail.title }}</h1>
              <div class="post-detail-meta mb-4 text-muted">
                <span><i class="bi bi-person-fill"></i> {{ postDetail.author ? postDetail.author.name : 'Không rõ' }}</span>
                <span class="mx-2">|</span>
                <span><i class="bi bi-folder-fill"></i> {{ postDetail.category?.name || 'N/A' }}</span>
                <span class="mx-2">|</span>
                <span><i class="bi bi-calendar-event-fill"></i> {{ formatDate(postDetail.publish_date) }}</span>
                <span class="mx-2">|</span>
                <span><i class="bi bi-eye-fill"></i> {{ postDetail.views.toLocaleString('vi-VN') }} lượt xem</span>
              </div>
              <hr>
              <div class="post-detail-content" v-html="postDetail.content"></div>

              <!-- Khu vực Bình luận -->
              <div class="comments-section mt-5">
                <h3 class="mb-4">Bình luận ({{ comments.length }})</h3>
                
                <!-- Form gửi bình luận -->
                <div class="comment-form-wrapper mb-5 p-4 bg-light rounded">
                  <h4 class="mb-3">Để lại bình luận</h4>
                  <form @submit.prevent="submitComment">
                    <div class="mb-3">
                      <textarea class="form-control" v-model="newComment.content" rows="4" placeholder="Viết bình luận của bạn..." required></textarea>
                    </div>
                    <!-- Giả sử người dùng chưa đăng nhập, ta có thể yêu cầu tên -->
                    <!-- <div class="mb-3">
                      <input type="text" class="form-control" v-model="newComment.name" placeholder="Tên của bạn" required>
                    </div> -->
                    <button type="submit" class="btn btn-primary" :disabled="isSubmittingComment">
                      <span v-if="isSubmittingComment" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                      Gửi bình luận
                    </button>
                  </form>
                </div>
                
                <!-- Danh sách bình luận -->
                <ul v-if="comments.length" class="list-unstyled">
                  <li v-for="comment in comments" :key="comment.id" class="comment-item d-flex mb-4">
                    <img src="https://i.pravatar.cc/60" alt="avatar" class="rounded-circle me-3" style="width: 60px; height: 60px;">
                    <div class="comment-body">
                      <h6 class="comment-author mb-1">{{ comment.user ? comment.user.name : 'Khách' }}</h6>
                      <small class="comment-date text-muted">{{ formatDate(comment.created_at) }}</small>
                      <p class="comment-text mt-2">{{ comment.content }}</p>
                    </div>
                  </li>
                </ul>
                <div v-else class="text-center text-muted">
                  Chưa có bình luận nào. Hãy là người đầu tiên!
                </div>
              </div>

            </div>
            
            <!-- Trường hợp không tìm thấy bài viết -->
            <div v-else class="text-center py-5">
              <h2>404 - Không tìm thấy bài viết</h2>
              <p>Bài viết bạn đang tìm kiếm không tồn tại hoặc đã bị xóa.</p>
              <router-link to="/news" class="btn btn-primary">Quay lại trang tin tức</router-link>
            </div>
          </div>

          <!-- Cột Sidebar (Giữ nguyên) -->
          <div class="col-lg-4">
            <div class="blog-sidebar">
              <div class="sidebar-widget p-4 rounded mb-4">
                <h4 class="widget-title mb-3">Tìm Kiếm Tại Đây</h4>
                <div class="input-group">
                  <input type="text" v-model="searchQuery" @keyup.enter="handleSearch" class="form-control" placeholder="Tìm kiếm..." aria-label="Search here">
                  <button class="btn btn-search" type="button" @click="handleSearch"><i class="bi bi-search"></i></button>
                </div>
              </div>

              <div class="sidebar-widget p-4 rounded mb-4">
                <h4 class="widget-title mb-3">Danh Mục</h4>
                <ul class="list-unstyled category-list">
                  <li v-for="category in categories" :key="category.id">
                    <a href="#" class="d-flex justify-content-between align-items-center">
                      <span>{{ category.name }}</span>
                    </a>
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
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted, inject, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';

// --- STATE & ROUTING ---
const route = useRoute();
const router = useRouter();
const apiUrl = inject('apiUrl');

const postDetail = ref(null);
const comments = ref([]);
const newComment = ref({ content: '' }); // Giả sử người dùng đã đăng nhập và có user_id
const loading = ref(true);
const isSubmittingComment = ref(false);

// Sidebar state
const categories = ref([]);
const recentPosts = ref([]);
const searchQuery = ref('');

// --- HELPER FUNCTIONS ---
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

// --- API CALLS ---
const fetchNewsDetail = async (id) => {
  loading.value = true;
  postDetail.value = null; // Reset
  try {
    const response = await axios.get(`${apiUrl}/api/news/${id}`);
    postDetail.value = response.data;
    // Lấy bình luận ngay sau khi có chi tiết bài viết
    await fetchComments(id); 
  } catch (error) {
    console.error("Lỗi khi tải chi tiết tin tức:", error);
    postDetail.value = false; // Dùng `false` để biết là lỗi, khác với `null` là đang tải
  } finally {
    loading.value = false;
  }
};

const fetchComments = async (id) => {
    try {
        // Giả định API trả về bình luận tại endpoint này
        // Thường API chi tiết tin tức đã bao gồm bình luận, nên có thể bạn không cần gọi riêng
        if(postDetail.value && postDetail.value.comments) {
            comments.value = postDetail.value.comments.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
        }
    } catch (error) {
        console.error("Lỗi khi tải bình luận:", error);
    }
}

const submitComment = async () => {
    if (!newComment.value.content.trim()) {
        alert("Vui lòng nhập nội dung bình luận.");
        return;
    }
    isSubmittingComment.value = true;

    try {
        // Giả sử API nhận bình luận tại endpoint này
        // Bạn cần gửi ID người dùng đã đăng nhập
        const user = JSON.parse(localStorage.getItem('userInfo')); 
        if (!user || !user.id) {
            alert('Bạn cần đăng nhập để bình luận.');
            router.push('/login');
            return;
        }

        const payload = {
            news_id: postDetail.value.id,
            user_id: user.id,
            content: newComment.value.content
        };

        const response = await axios.post(`${apiUrl}/api/comments`, payload);
        
        // Thêm bình luận mới vào đầu danh sách để hiển thị ngay lập tức
        comments.value.unshift(response.data);
        newComment.value.content = ''; // Xóa form
        alert('Gửi bình luận thành công!');

    } catch (error) {
        console.error("Lỗi khi gửi bình luận:", error);
        alert('Đã có lỗi xảy ra khi gửi bình luận.');
    } finally {
        isSubmittingComment.value = false;
    }
}

// Sidebar API Calls
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

// --- EVENT HANDLERS ---
const handleSearch = () => {
  if(searchQuery.value.trim()){
    router.push({ path: '/news', query: { q: searchQuery.value } });
  }
};

// --- LIFECYCLE & WATCHERS ---
onMounted(() => {
  const newsId = route.params.id;
  fetchNewsDetail(newsId);
  fetchCategories();
  fetchRecentPosts();
});

// Nếu người dùng click vào một link bài viết gần đây khác khi đang ở trang chi tiết,
// component sẽ không `unmount` và `mount` lại, nên `onMounted` không chạy lại.
// Chúng ta cần `watch` sự thay đổi của route.params.id để tải lại dữ liệu.
watch(() => route.params.id, (newId) => {
    if (newId) {
        fetchNewsDetail(newId);
    }
});
</script>

<style scoped>
/* ----- SAO CHÉP STYLE TỪ TRANG LIST ----- */
/* Kiểu dáng chung, Banner, Sidebar... */
.blog-banner {
  width: 100%; height: 350px; position: relative; display: flex;
  justify-content: center; align-items: center;
  background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)),
    url('https://anhphathotel.com/wp-content/uploads/2023/06/resort-thanh-hoa-4.jpg');
  background-size: cover; background-position: center;
}
.banner-title { font-size: 3.5rem; font-weight: 800; color: #fff; }
.breadcrumb { background-color: transparent; padding: 0; }
.breadcrumb-item a { color: #f1f1f1; font-weight: 600; text-decoration: none; }
.breadcrumb-item.active { color: #ffc107; }
.blog-content-area {
  background-color: #ffffff;
}
/* Sidebar Styles */
.blog-sidebar .sidebar-widget { background-color: #f8f9fa; border: 1px solid #dee2e6; }
.widget-title { position: relative; padding-bottom: 10px; border-bottom: 2px solid #dee2e6; font-weight: 700; color: #343a40;}
.sidebar-widget .form-control:focus { box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25); border-color: #007bff; }
.sidebar-widget .btn-search { background-color: #007bff; color: white; }
.sidebar-widget .btn-search:hover { background-color: #0056b3; }
.category-list li a { display: block; padding: 12px 15px; border-radius: 5px; font-weight: 500; background-color: #fff; color: #343a40; border: 1px solid #ddd; transition: all 0.3s ease; text-decoration: none;}
.category-list li a:hover { background-color: #007bff; color: #fff; border-color: #007bff; }
.category-list li { margin-bottom: 10px; }
.recent-post-item img { width: 70px; height: 70px; object-fit: cover; }
.recent-post-item h6 { font-size: 0.95rem; line-height: 1.3; font-weight: 600;}
.recent-post-item a { text-decoration: none; color: #343a40;}
.recent-post-item a:hover { color: #007bff;}
/* ----- END STYLE SAO CHÉP ----- */


/* ----- STYLE MỚI CHO TRANG CHI TIẾT ----- */
.post-detail-title {
  font-size: 2.5rem;
  font-weight: 700;
  color: #212529;
  line-height: 1.3;
}
.post-detail-meta {
  font-size: 0.9rem;
  display: flex;
  flex-wrap: wrap;
  gap: 5px;
  align-items: center;
}

/* Định dạng cho nội dung v-html, giống trang admin */
.post-detail-content {
    line-height: 1.8;
    word-wrap: break-word;
    font-size: 1.1rem;
    color: #343a40;
}
.post-detail-content :deep(p) { margin-bottom: 1.25rem; }
.post-detail-content :deep(h1),
.post-detail-content :deep(h2),
.post-detail-content :deep(h3) {
    margin-top: 2rem;
    margin-bottom: 1rem;
    font-weight: 600;
    line-height: 1.4;
}
.post-detail-content :deep(img) {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    margin: 1.5rem 0;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
.post-detail-content :deep(blockquote) {
    border-left: 5px solid #007bff;
    padding-left: 1.5rem;
    margin: 2rem 0;
    font-style: italic;
    color: #6c757d;
    background-color: #f8f9fa;
    padding-top: 1rem;
    padding-bottom: 1rem;
    border-radius: 0 5px 5px 0;
}
.post-detail-content :deep(ul), .post-detail-content :deep(ol) {
    padding-left: 2rem;
    margin-bottom: 1.5rem;
}

/* Định dạng cho khu vực bình luận */
.comments-section {
  border-top: 1px solid #dee2e6;
  padding-top: 2rem;
}
.comment-item {
  border-bottom: 1px solid #e9ecef;
  padding-bottom: 1.5rem;
}
.comment-item:last-child {
  border-bottom: none;
  padding-bottom: 0;
}
.comment-author {
  font-weight: 600;
  color: #343a40;
}
.comment-text {
  color: #495057;
  line-height: 1.6;
}
</style>