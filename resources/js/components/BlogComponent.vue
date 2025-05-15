<template>
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
          <h1 class="sisf-m-title entry-title">Foodie Journal</h1>
          <p class="sisf-m-subtitle">Discover culinary stories, recipes, and inspiration</p>
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
              <div class="col-lg-6" v-for="(post, index) in blogPosts" :key="index">
                <div class="sisf-blog">
                  <div class="sisf-blog-item">
                    <div class="sisf-e-inner">
                      <div class="sisf-e-media">
                        <div class="sisf-e-media-image">
                          <a :href="post.link">
                            <figure class="image-anime reveal">
                              <img :src="post.image" class="image-fluid" alt="Dishify">
                              <div class="post-category-badge">{{ post.category }}</div>
                            </figure>
                          </a>
                        </div>
                        <div class="sisf-e-media-video" v-else-if="post.type === 'video'">
                          <video class="blog-video" autoplay muted loop controls>
                            <source :src="post.video" type="video/mp4">
                          </video>
                        </div>
                      </div>
                      <div class="sisf-e-content">
                        <div class="sisf-e-info sisf-info--top mb-2 d-flex align-items-center">
                          <div class="sisf-e-info-date">
                            <i class="far fa-calendar me-1"></i> {{ post.date }}
                          </div>
                          <span class="sisf-e-info-divider">.</span>
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
                            <span class="sisf-m-icon">
                              <span class="sisf-m-icon-inner"></span>
                            </span>
                          </a>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="blog-pagination mt-5" v-if="filteredPosts.length > 0">
              <button
                class="pagination-btn"
                :disabled="currentPage === 1"
                @click="currentPage--"
              >
                <i class="fas fa-chevron-left"></i>
              </button>

              <button
                v-for="page in totalPages"
                :key="page"
                class="pagination-btn"
                :class="{ active: currentPage === page }"
                @click="currentPage = page"
              >
                {{ page }}
              </button>

              <button
                class="pagination-btn"
                :disabled="currentPage === totalPages"
                @click="currentPage++"
              >
                <i class="fas fa-chevron-right"></i>
              </button>
            </div>
          </div>

          <!-- Blog Sidebar -->
          <div class="col-lg-3 col-md-3">
            <div class="sisf-page-sidebar bg-white">
              <!-- Search Widget -->
              <div class="sidebar-widget widget_search">
                <h3 class="sidebar-title">Search Articles</h3>
                <div class="sisf-search-form">
                  <div class="sisf-search-form-inner">
                    <input
                      type="search"
                      class="sisf-search-form-field"
                      v-model="searchQuery"
                      placeholder="Search articles..."
                      @keyup.enter="searchPosts"
                    />
                    <button type="submit" class="sisf-search-form-button" @click="searchPosts">
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
                      <a :href="category.link">
                        <span>{{ category.name }}</span>
                      </a>
                    </li>
                  </ul>
                </div>
              </div>
              <div class="sidebar-separator">
                <hr class="separator sidebar-line" />
              </div>

              <!-- Popular Posts Widget -->
              <div class="sidebar-widget widget_popular_blog">
                <h3 class="sidebar-title">Trending Now</h3>
                <div class="sidebar_blog-list">
                  <ul class="blog-list-widget">
                    <li v-for="(post, index) in popularPosts" :key="index">
                      <div class="sisf-blog-image">
                        <a :href="post.link"><img :src="post.image" class="image-fluid" alt="Dishify" /></a>
                      </div>
                      <div class="sisf-blog-content">
                        <h5 class="sisf-blog-title"><a :href="post.link">{{ post.title }}</a></h5>
                        <div class="sisf-date">
                          <i class="far fa-calendar me-1"></i>
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
                  <a
                    href="#"
                    class="tag"
                    v-for="(tag, index) in tags"
                    :key="index"
                    @click.prevent="filterByTag(tag.name)"
                  >
                    #{{ tag.name }}
                  </a>
                </div>
              </div>
              <div class="sidebar-separator">
                <hr class="separator sidebar-line" />
              </div>
              <!-- Follow Us Widget -->
              <div class="sidebar-widget widget_follow_us">
                <h3 class="sidebar-title">Follow Us</h3>
                <div class="sisf-author-info text-center">
                  <a class="sisf-author-info-image mb-3 d-block" href="#">
                    <img src="https://dishify-html.wpthemeverse.com/images/our_banner.png" class="image-fluid" alt="Dishify" />
                  </a>
                  <h4 class="sisf-author-info-name mb-3"><a href="#"><span class="fn">David Parker</span></a></h4>
                  <p class="sisf-author-info-description mb-0">Passionate Food Entrepreneur</p>
                </div>
                <div class="sisf-social-links-widget my-4">
                  <ul class="sisf-social-list d-flex justify-content-between">
                    <li class="sisf-social-icon mb-0" v-for="(social, index) in socialLinks" :key="index">
                      <a :href="social.link"><i :class="social.icon"></i></a>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'BlogComponent',
  data() {
    return {
      searchQuery: '',
      activeCategory: null,
      activeTag: null,
      currentPage: 1,
      postsPerPage: 4,
      email: '',
      blogPosts: [
        {
          image: 'https://dishify-html.wpthemeverse.com/images/blog-list-1.png',
          link: 'blog-single.html',
          date: '5 Apr, 2025', /* Updated date to current year */
          category: 'Italian',
          author: 'Chef Marco',
          title: 'Discovering Deliciousness, One Recipe at a Time',
          excerpt: 'Discovering Deliciousness, One Recipe at a Time invites you on a culinary journey filled with tantalizing flavors, aromatic spices, and mouthwatering dishes. It’s an explorat...'
        },
        {
          image: 'https://dishify-html.wpthemeverse.com/images/blog-list-2.png',
          link: 'blog-single.html',
          date: '5 Apr, 2025', /* Updated date to current year */
          category: 'Pizza & Fast Food',
          author: 'Chef Sophia',
          title: 'Indulge in Delicious Creations',
          excerpt: 'Indulge in delicious creations that tantalize your taste buds and transport you to a world of culinary delight. From mouthwatering desserts to savory delights, our menu is a sympho...'
        },
        {
          image: 'https://dishify-html.wpthemeverse.com/images/blog-list-3.png',
          link: 'blog-single.html',
          date: '5 Apr, 2025', /* Updated date to current year */
          category: 'Pizza & Fast Food',
          author: 'Chef Antonio',
          title: 'Painting Palates with Flavorful Delights',
          excerpt: 'Nestled within the vibrant heart of a bustling city, Painting Palates with Flavorful Delights stands as a beacon of culinary creativity, inviting patrons on a journey of gastronomi...'
        },
        {
          image: 'https://dishify-html.wpthemeverse.com/images/blog-list-4.png',
          link: 'blog-single.html',
          date: '5 Apr, 2025', /* Updated date to current year */
          category: 'Desserts',
          author: 'Pastry Chef Emma',
          title: 'Stories Behind the Dishes We Love',
          excerpt: '“Stories Behind the Dishes We Love” delves into the captivating narratives intertwined with some of the most beloved culinary creations from around the globe. Through i...'
        },
        {
          image: 'https://dishify-html.wpthemeverse.com/images/blog-list-5.png',
          link: 'blog-single.html',
          date: '5 Apr, 2025', /* Updated date to current year */
          category: 'Italian',
          author: 'Chef Giovanni',
          title: 'Recipes and Reviews for Food Enthusiasts',
          excerpt: 'Recipes and Reviews for Food Enthusiasts is a platform designed to cater to the discerning palates and culinary curiosities of passionate food lovers. Bursting with a tantalizing a...'
        },
        {
          image: 'https://dishify-html.wpthemeverse.com/images/blog-list-1.png',
          link: 'blog-single.html',
          date: '5 Apr, 2025', /* Updated date to current year */
          category: 'Sea Food',
          author: 'Chef Pierre',
          title: 'Tales from the Gastronomic World',
          excerpt: 'Amidst the bustling streets and quiet corners of the gastronomic world lie tales waiting to be told—stories that evoke the tantalizing aroma of spices, the sizzle of a hot pan, a...'
        }
      ],
      categories: [
        { name: 'Desserts', link: '#' },
        { name: 'Italian', link: '#' },
        { name: 'Mexican', link: '#' },
        { name: 'Pizza & Fast Food', link: '#' },
        { name: 'Sea Food', link: '#' }
      ],
      popularPosts: [
        {
          image: '/images/blog_detail-small-1.png',
          link: 'blog-single.html',
          title: 'Discovering Deliciousness,',
          date: 'April 5, 2024'
        },
        {
          image: '/images/blog_detail-small-2.png',
          link: 'blog-single.html',
          title: 'Indulge in Delicious Creations',
          date: 'April 5, 2025' /* Updated date to current year */
        },
        {
          image: '/images/blog_detail-small-3.png',
          link: 'blog-single.html',
          title: 'Painting Palates with Flavorful',
          date: 'April 5, 2024'
        }
      ],
      tags: [
        { name: 'Desserts', link: 'blogs.html' },
        { name: 'Italian', link: 'blogs.html' },
        { name: 'Mexican', link: 'blogs.html' },
        { name: 'Pizza & Fast Food', link: 'blogs.html' },
        { name: 'Sea Food', link: 'blogs.html' }
      ],
      socialLinks: [
        { link: '#', icon: 'fa-brands fa-facebook' },
        { link: '#', icon: 'fa-brands fa-x-twitter' },
        { link: '#', icon: 'fa-brands fa-linkedin' },
        { link: '#', icon: 'fa-brands fa-instagram' }
      ]
    };
  }
}
</script>

<style scoped>
/* Base Styles */
:root {
  --primary-color: #FF6B6B;
  --primary-dark: #E05555;
  --secondary-color: #FFD166;
  --accent-color: #06D6A0;
  --dark-color: #2D2D2D;
  --medium-color: #6C757D;
  --light-color: #F8F9FA;
  --lighter-color: #FFFFFF;
  --border-color: #E9ECEF;
  --shadow-color: rgba(0, 0, 0, 0.1);
  --font-heading: 'Cormorant Infant', serif;
  --font-body: 'Work Sans', sans-serif;
  --transition: all 0.3s ease;
}

/* Banner Styles */
.sisf-banner {
  position: relative;
  width: 100%;
  height: 450px;
  overflow: hidden;
  margin-bottom: 60px;
}

.banner-img {
  width: 100%;
  height: 100%;
}

.banner-img figure {
  margin: 0;
  height: 100%;
}

.banner-img img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.sisf-page-title {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 100%;
  text-align: center;
  color: white;
  text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.3);
}

.sisf-m-title {
  font-family: var(--font-heading);
  font-size: 4rem;
  font-weight: 700;
  margin: 0 0 15px;
  letter-spacing: 1px;
}

.sisf-m-subtitle {
  font-family: var(--font-heading);
  font-size: 1.5rem;
  font-weight: 400;
  margin: 0;
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
}

/* Blog Grid Styles */
.sisf-page-section {
  padding: 60px 0;
}

.sisf-blog-item {
  margin-bottom: 30px;
  background: white;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 5px 15px var(--shadow-color);
  transition: var(--transition);
  height: 100%;
}

.sisf-blog-item:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
}

.sisf-e-media-image img {
  width: 100%;
  height: auto;
}

.sisf-e-content {
  padding: 20px;
}

.sisf-e-title {
  font-size: 24px;
  margin-bottom: 10px;
}

.sisf-e-excerpt {
  font-size: 16px;
  color: #666;
}

.btn-default {
  display: inline-flex;
  align-items: center;
  padding: 10px 20px;
  background: #000;
  color: #fff;
  text-transform: uppercase;
  font-size: 14px;
}

.btn-default:hover {
  background: #333;
}

.sisf-page-sidebar {
  padding: 20px;
  border: 1px solid #eee;
}

.sidebar-title {
  font-size: 20px;
  margin-bottom: 15px;
}

.sisf-search-form {
  position: relative;
  margin-bottom: 25px;
}

.sisf-search-form-inner {
  display: flex;
  align-items: center;
}

.sisf-search-form-field {
  flex: 1;
  padding: 12px 20px;
  border: none;
  padding: 10px;
  width: 100%;
}

.sisf-search-form-button {
  padding: 0 20px;
  background: var(--primary-color);
  color: white;
  border: none;
  padding: 10px;
}

.product-categories-list li,
.blog-list-widget li,
.sidebar_tag-list a,
.sisf-social-list li {
  margin-bottom: 10px;
}

.sidebar_tag-list a {
  display: inline-block;
  padding: 5px 10px;
  background: #f5f5f5;
  margin-right: 5px;
  margin-bottom: 5px;
}

.sisf-social-list {
  padding: 0;
  list-style: none;
}

.sisf-social-icon a {
  font-size: 18px;
  color: #000;
}

/* Add more styles as needed from the original CSS files */
</style>
