<template>
  <!-- From Uiverse.io by ahmed150up -->
  <div class="chat-card" v-if="isFormChat">
    <div class="chat-header">
      <div class="h2">ChatGPT <button type="button" class="btn btn-warning" @click="closePopup">Close Chat</button>
      </div>
    </div>
    <div class="chat-body">
      <div class="message incoming">
        <p>Xin chào, tôi là ChatGPT tôi đã sẵn sàng để giải đáp thắc mắc của bạn?</p>
        <div v-if="isLoadingChat" class=""><!-- From Uiverse.io by G4b413l -->
          <div class="newtons-cradle">
            <div class="newtons-cradle__dot"></div>
            <div class="newtons-cradle__dot"></div>
            <div class="newtons-cradle__dot"></div>
            <div class="newtons-cradle__dot"></div>
          </div>
        </div>
        <div class="result" v-if="result">ChatGPT: {{ result }}</div>
      </div>
    </div>
    <div class="chat-footer">
      <form @submit.prevent="sendRequest">
        <input v-model="inputText" placeholder="Hey ChatGPT" type="text" required />
        <button type="submit">Gửi</button>
      </form>
    </div>
  </div>
  <div class="logo" v-if="isLogoChat"><!-- From Uiverse.io by VassoD -->
    <div class="btn-main" @click="showPopup">Chat Cùng AI</div>
  </div>

  <!-- Blog Grid Banner -->
  <div class="sisf-banner position-relative">
    <div class="banner-img">
      <figure>
        <img src="https://dishify-html.wpthemeverse.com/images/dishify-about-banner.png" alt="Dishify" />
      </figure>
    </div>
    <div class="sisf-page-title sisf-m sisf-title--standard sisf-alignment--center">
      <div class="sisf-m-inner">
        <div class="sisf-m-content sisf-content-grid">
          <h1 class="sisf-m-title entry-title">Blog Grid</h1>
        </div>
      </div>
    </div>
  </div>
  <!-- Page Section -->
  <div class="sisf-page-section blog-section section">
    <div class="sisf-grid sisf-layout--template">
      <div class="sisf-grid-inner container">
        <div class="row">
          <!-- Blog Grid -->
          <div class="col-lg-9">
            <div class="row">
              <div class="col-lg-6 col-md-6" v-for="(post, index) in blogPosts" :key="index">
                <div class="sisf-blog">
                  <div class="sisf-blog-item">
                    <div class="sisf-e-inner">
                      <div class="sisf-e-media">
                        <div class="sisf-e-media-image">
                          <a :href="post.link">
                            <figure class="image-anime reveal">
                              <img :src="post.image" class="image-fluid" alt="Dishify">
                            </figure>
                          </a>
                        </div>
                      </div>
                      <div class="sisf-e-content">
                        <div class="sisf-e-info sisf-info--top mb-2 d-flex align-items-center">
                          <div class="sisf-e-info-date">
                            <a href="blogs.html">{{ post.date }}</a>
                          </div>
                          <span class="sisf-e-info-divider">·</span>
                          <div class="sisf-e-info-category">
                            <a href="blogs.html">{{ post.category }}</a>
                          </div>
                        </div>
                        <div class="sisf-e-text">
                          <h2 class="sisf-e-title">
                            <a class="sisf-e-title-link" :href="post.link">{{ post.title }}</a>
                          </h2>
                          <p class="sisf-e-excerpt">{{ post.excerpt }}</p>
                        </div>
                        <div class="sisf-m-button">
                          <a :href="post.link" class="btn-default blog-button">
                            <span>VIEW MORE <i class="fa-solid fa-arrow-right"></i></span>
                          </a>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- Blog Sidebar -->
          <div class="col-lg-3 col-md-3">
            <div class="sisf-page-sidebar bg-white">
              <!-- Search Widget -->
              <div class="sidebar-widget widget_search">
                <div class="sisf-search-form">
                  <div class="sisf-search-form-inner">
                    <input type="search" class="sisf-search-form-field" v-model="searchQuery" placeholder="Search…"
                      required />
                    <button type="submit" class="sisf-search-form-button">
                      <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                  </div>
                </div>
              </div>
              <div class="sidebar-separator">
                <hr class="separator sidebar-line" />
              </div>
              <!-- Categories Widget -->
              <div class="sidebar-widget widget_categories">
                <h3 class="sidebar-title">Categories</h3>
                <div class="product-categories">
                  <ul class="product-categories-list">
                    <li class="product-categories-list-item" v-for="(category, index) in categories" :key="index">
                      <a :href="category.link">{{ category.name }}</a>
                    </li>
                  </ul>
                </div>
              </div>
              <div class="sidebar-separator">
                <hr class="separator sidebar-line" />
              </div>
              <!-- Popular Posts Widget -->
              <div class="sidebar-widget widget_popular_blog">
                <h3 class="sidebar-title">Popular Posts</h3>
                <div class="sidebar_blog-list">
                  <ul class="blog-list-widget">
                    <li v-for="(post, index) in popularPosts" :key="index">
                      <div class="sisf-blog-image">
                        <a :href="post.link"><img :src="post.image" class="image-fluid" alt="Dishify" /></a>
                      </div>
                      <div class="sisf-blog-content">
                        <h5 class="sisf-blog-title"><a :href="post.link">{{ post.title }}</a></h5>
                        <div class="sisf-date">
                          <span class="publish-date">{{ post.date }}</span>
                        </div>
                      </div>
                    </li>
                  </ul>
                </div>
              </div>
              <div class="sidebar-separator">
                <hr class="separator sidebar-line" />
              </div>
              <!-- Popular Tags Widget -->
              <div class="sidebar-widget widget_popular_tag">
                <h3 class="sidebar-title">Popular Tags</h3>
                <div class="sidebar_tag-list">
                  <a :href="tag.link" class="tag" v-for="(tag, index) in tags" :key="index">{{ tag.name }}</a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script setup>
import { ref } from 'vue';

const inputText = ref('');
const result = ref('');
const isFormChat = ref(true);
const isLoadingChat = ref(false);
const sendRequest = async () => {
  isLoadingChat.value = true;
  try {
    const response = await fetch('https://openrouter.ai/api/v1/chat/completions', {
      method: 'POST',
      headers: {
        Authorization: 'Bearer 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',
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        model: 'gpt-3.5-turbo',
        messages: [{ role: 'user', content: inputText.value }]
      })
    });

    if (!response.ok) {
      throw new Error(`HTTP error! Status: ${response.status}`);
    }

    const data = await response.json();

    // Hiển thị kết quả
    inputText.value = '';
    result.value = data.choices[0].message.content;

  } catch (error) {
    if (error.message.includes('Failed to fetch')) {
      result.value = 'Không thể kết nối tới máy chủ. Vui lòng kiểm tra kết nối mạng hoặc URL.';
    } else if (error.message.includes('ERR_NAME_NOT_RESOLVED')) {
      result.value = 'Không thể giải quyết tên miền. Vui lòng kiểm tra URL hoặc kết nối mạng.';
    } else {
      result.value = 'Đã xảy ra lỗi: ' + error.message;
    }
  } finally {
    isLoadingChat.value = false;
  }
};
const isLogoChat = ref(false);
const closePopup = () => {
  isFormChat.value = false;
  isLogoChat.value = true;
}
const showPopup = ()=>{
  isFormChat.value = true;
  isLogoChat.value = false;
}
</script>
<script>
export default {
  name: 'BlogComponent',
  data() {
    return {
      searchQuery: '',
      blogPosts: [
        {
          image: 'https://dishify-html.wpthemeverse.com/images/blog-list-1.png',
          link: '/blog-detail',
          date: '5 Apr, 2025', /* Updated date to current year */
          category: 'Italian',
          title: 'Discovering Deliciousness, One Recipe at a Time',
          excerpt: 'Discovering Deliciousness, One Recipe at a Time invites you on a culinary journey filled with tantalizing flavors, aromatic spices, and mouthwatering dishes...'
        },
        {
          image: 'https://dishify-html.wpthemeverse.com/images/blog-list-2.png',
          link: '/blog-detail',
          date: '5 Apr, 2025', /* Updated date to current year */
          category: 'Pizza & Fast Food',
          title: 'Indulge in Delicious Creations',
          excerpt: 'Indulge in delicious creations that tantalize your taste buds and transport you to a world of culinary delight...'
        },
        {
          image: 'https://dishify-html.wpthemeverse.com/images/blog-list-3.png',
          link: '/blog-detail',
          date: '5 Apr, 2025', /* Updated date to current year */
          category: 'Pizza & Fast Food',
          title: 'Painting Palates with Flavorful Delights',
          excerpt: 'Nestled within the vibrant heart of a bustling city, Painting Palates with Flavorful Delights stands as a beacon of culinary creativity...'
        },
        {
          image: 'https://dishify-html.wpthemeverse.com/images/blog-list-4.png',
          link: '/blog-detail',
          date: '5 Apr, 2025', /* Updated date to current year */
          category: 'Desserts',
          title: 'Stories Behind the Dishes We Love',
          excerpt: '“Stories Behind the Dishes We Love” delves into the captivating narratives intertwined with some of the most beloved culinary creations...'
        },
        {
          image: 'https://dishify-html.wpthemeverse.com/images/blog-list-5.png',
          link: '/blog-detail',
          date: '5 Apr, 2025', /* Updated date to current year */
          category: 'Italian',
          title: 'Recipes and Reviews for Food Enthusiasts',
          excerpt: 'Recipes and Reviews for Food Enthusiasts is a platform designed to cater to the discerning palates and culinary curiosities of passionate food lovers...'
        },
        {
          image: 'https://dishify-html.wpthemeverse.com/images/blog-list-1.png',
          link: '/blog-detail',
          date: '5 Apr, 2025', /* Updated date to current year */
          category: 'Sea Food',
          title: 'Tales from the Gastronomic World',
          excerpt: 'Amidst the bustling streets and quiet corners of the gastronomic world lie tales waiting to be told—stories that evoke the tantalizing aroma of spices...'
        }
      ],
      categories: [
        { name: 'Desserts', link: 'blogs.html' },
        { name: 'Italian', link: 'blogs.html' },
        { name: 'Mexican', link: 'blogs.html' },
        { name: 'Pizza & Fast Food', link: 'blogs.html' },
        { name: 'Sea Food', link: 'blogs.html' }
      ],
      popularPosts: [
        {
          image: 'https://dishify-html.wpthemeverse.com/images/blog_detail-small-1.png',
          link: '/blog-detail',
          title: 'Discovering Deliciousness,',
          date: 'April 5, 2025' /* Updated date to current year */
        },
        {
          image: 'https://dishify-html.wpthemeverse.com/images/blog_detail-small-2.png',
          link: '/blog-detail',
          title: 'Indulge in Delicious Creations',
          date: 'April 5, 2025' /* Updated date to current year */
        },
        {
          image: 'https://dishify-html.wpthemeverse.com/images/blog_detail-small-3.png',
          link: '/blog-detail',
          title: 'Painting Palates with Flavorful',
          date: 'April 5, 2025' /* Updated date to current year */
        }
      ],
      tags: [
        { name: 'Desserts', link: 'blogs.html' },
        { name: 'Italian', link: 'blogs.html' },
        { name: 'Mexican', link: 'blogs.html' },
        { name: 'Pizza & Fast Food', link: 'blogs.html' },
        { name: 'Sea Food', link: 'blogs.html' }
      ]
    };
  }
};
</script>

<style scoped>
/* From Uiverse.io by G4b413l */
.newtons-cradle {
  --uib-size: 50px;
  --uib-speed: 1.2s;
  --uib-color: #1ae3e3;
  position: relative;
  display: flex;
  margin: 0 auto;
  align-items: center;
  justify-content: center;
  width: var(--uib-size);
  height: var(--uib-size);
}

.newtons-cradle__dot {
  position: relative;
  display: flex;
  align-items: center;
  height: 100%;
  width: 25%;
  transform-origin: center top;
}

.newtons-cradle__dot::after {
  content: '';
  display: block;
  width: 100%;
  height: 25%;
  border-radius: 50%;
  background-color: var(--uib-color);
}

.newtons-cradle__dot:first-child {
  animation: swing var(--uib-speed) linear infinite;
}

.newtons-cradle__dot:last-child {
  animation: swing2 var(--uib-speed) linear infinite;
}

@keyframes swing {
  0% {
    transform: rotate(0deg);
    animation-timing-function: ease-out;
  }

  25% {
    transform: rotate(70deg);
    animation-timing-function: ease-in;
  }

  50% {
    transform: rotate(0deg);
    animation-timing-function: linear;
  }
}

@keyframes swing2 {
  0% {
    transform: rotate(0deg);
    animation-timing-function: linear;
  }

  50% {
    transform: rotate(0deg);
    animation-timing-function: ease-out;
  }

  75% {
    transform: rotate(-70deg);
    animation-timing-function: ease-in;
  }
}


/* From Uiverse.io by ahmed150up */
.chat-card {
  width: 300px;
  /* margin: 0 auto; */
  background-color: #fff;
  border-radius: 5px;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  position: fixed;
  /* Đặt vị trí cố định */
  bottom: 5%;
  right: 2%;
  /* Cách bên phải 10px */
}
.logo{
  position: fixed;
  /* Đặt vị trí cố định */
  bottom: 5%;
  right: 2%;
}


.chat-header {
  padding: 10px;
  background-color: #f2f2f2;
  display: flex;
  align-items: center;
}

.chat-header .h2 {
  font-size: 16px;
  color: #333;
}

.chat-body {
  padding: 20px;
}

.result {
  margin-top: 10px;
  font-size: 14px;
  color: #333;
  max-height: 250px;
  /* Đặt chiều cao tối đa */
  overflow-y: auto;
  /* Thêm thanh cuộn dọc */
  padding: 10px;
  /* Thêm khoảng đệm */
  border: 1px solid #ccc;
  /* Thêm viền */
  border-radius: 5px;
  /* Bo góc */
  background-color: #f9f9f9;
  /* Màu nền */
}

.message {
  margin-bottom: 10px;
  padding: 10px;
  border-radius: 5px;
}

.incoming {
  background-color: #e1e1e1;

}

.outgoing {
  background-color: #f2f2f2;
  text-align: right;
}

.message p {
  font-size: 12px;
  color: orange;
  font-weight: 500px;
  margin: 0;
}

.chat-footer {
  padding: 10px;
  background-color: #f2f2f2;
  display: flex;
}

.chat-footer input[type="text"] {
  flex-grow: 1;
  padding: 5px;
  border: none;
  border-radius: 3px;
}

.chat-footer button {
  padding: 5px 10px;
  border: none;
  background-color: #4285f4;
  color: #fff;
  font-weight: bold;
  cursor: pointer;
  transition: background-color 0.3s;
}

.chat-footer button:hover {
  background-color: #0f9d58;
}

@keyframes chatAnimation {
  0% {
    opacity: 0;
    transform: translateY(10px);
  }

  100% {
    opacity: 1;
    transform: translateY(0);
  }
}

.chat-card .message {
  animation: chatAnimation 0.3s ease-in-out;
  animation-fill-mode: both;
  animation-delay: 0.1s;
}

.chat-card .message:nth-child(even) {
  animation-delay: 0.2s;
}

.chat-card .message:nth-child(odd) {
  animation-delay: 0.3s;
}

/* Bootstrap and custom styles should be included in your project */
@import url('https://fonts.googleapis.com/css2?family=Cormorant+Infant:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&family=Work+Sans:ital,wght@0,100..900;1,100..900&display=swap');
@import url('https://fonts.googleapis.com/css2?family=Cherish&display=swap');
@import url('https://fonts.googleapis.com/css2?family=Corinthia:wght@400;700&display=swap');

/* Hero Banner Styles */
.sisf-banner {
  position: relative;
  width: 100%;
  overflow: hidden;
}

.banner-img {
  width: 100%;
}

.banner-img figure {
  margin: 0;
}

.banner-img img {
  width: 100%;
  height: auto;
  display: block;
}

.sisf-page-title {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 100%;
  text-align: center;
}

.sisf-m-title {
  font-size: 48px;
  color: #fff;
  text-transform: uppercase;
  margin: 0;
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
  font-family: 'Cormorant Infant', serif;
}

/* Blog Section Styles */
.sisf-page-section {
  padding: 60px 0;
  background: #f8f7f7;
}

.sisf-blog-item {
  margin-bottom: 30px;
}

.sisf-e-media-image img {
  width: 100%;
  height: 200px;
  object-fit: cover;
  display: block;
}

.sisf-e-content {
  padding: 20px;
  min-height: 300px;
  /* Ensure equal height for all posts */
}

.sisf-e-info {
  display: flex;
  align-items: center;
}

.sisf-e-info-date a,
.sisf-e-info-category a {
  color: #888;
  font-size: 14px;
  text-decoration: none;
  font-family: 'Work Sans', sans-serif;
}

.sisf-e-info-date a:hover,
.sisf-e-info-category a:hover {
  color: #f4a261;
}

.sisf-e-info-divider {
  margin: 0 5px;
  color: #888;
}

.sisf-e-title {
  font-size: 20px;
  margin: 10px 0;
  font-weight: 600;
  font-family: 'Cormorant Infant', serif;
}

.sisf-e-title-link {
  color: #333;
  text-decoration: none;
}

.sisf-e-title-link:hover {
  color: #f4a261;
}

.sisf-e-excerpt {
  font-size: 14px;
  color: #666;
  line-height: 1.6;
  margin-bottom: 15px;
  font-family: 'Work Sans', sans-serif;
}

.btn-default {
  display: inline-flex;
  align-items: center;
  padding: 8px 15px;
  background: #f4a261;
  color: #fff;
  text-transform: uppercase;
  font-size: 12px;
  border: none;
  text-decoration: none;
  border-radius: 3px;
  transition: background 0.3s;
  font-family: 'Work Sans', sans-serif;
}

.btn-default:hover {
  background: #e76f51;
}

/* Sidebar Styles */
.sisf-page-sidebar {
  padding: 20px;
  border: 1px solid #eee;
  /* Restored border */
  background: #fff;
}

.sidebar-title {
  font-size: 18px;
  margin-bottom: 15px;
  color: #333;
  font-weight: 600;
  text-align: start;
  font-family: 'Cormorant Infant', serif;
}

.sisf-search-form {
  display: flex;
  align-items: center;
  border: 1px solid #ddd;
  border-radius: 5px;
  overflow: hidden;
}

.sisf-search-form-inner {
  display: flex;
  width: 100%;
}

.sisf-search-form-field {
  border: none;
  padding: 10px;
  width: 100%;
  font-size: 14px;
  outline: none;
  font-family: 'Work Sans', sans-serif;
}

.sisf-search-form-button {
  background: none;
  border: none;
  padding: 10px;
  cursor: pointer;
}

.sisf-search-form-button i {
  color: #888;
}

.product-categories-list {
  padding: 0;
  margin: 0;
  list-style: none;
}

.product-categories-list li {
  margin-bottom: 10px;
  text-align: start;
}

.product-categories-list a {
  color: #333;
  font-size: 14px;
  text-decoration: none;
  font-family: 'Work Sans', sans-serif;
}

.product-categories-list a:hover {
  color: #f4a261;
}

.blog-list-widget {
  padding: 0;
  margin: 0;
  list-style: none;
}

.blog-list-widget li {
  display: flex;
  align-items: center;
  margin-bottom: 15px;
}

.sisf-blog-image {
  flex-shrink: 0;
}

.sisf-blog-image img {
  width: 60px;
  height: 60px;
  object-fit: cover;
  border-radius: 5px;
}

.sisf-blog-content {
  flex-grow: 1;
  margin-left: 15px;
  text-align: start;
}

.sisf-blog-title {
  margin: 0;
}

.sisf-blog-title a {
  font-size: 14px;
  color: #333;
  text-decoration: none;
  font-family: 'Work Sans', sans-serif;
}

.sisf-blog-title a:hover {
  color: #f4a261;
}

.publish-date {
  font-size: 12px;
  color: #f4a261;
  font-family: 'Work Sans', sans-serif;
}

.sidebar_tag-list a {
  display: inline-block;
  padding: 5px 10px;
  background: #f1f1f1;
  color: #333;
  margin-right: 5px;
  margin-bottom: 5px;
  text-decoration: none;
  border-radius: 3px;
  font-family: 'Work Sans', sans-serif;
}

.sidebar_tag-list a:hover {
  background: #ddd;
}

.sidebar-separator hr {
  border: 2px solid #5f5c59;
  border-top: 1px solid #eee;
}
</style>
