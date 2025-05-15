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
            <div class="blog-search-mobile mb-4 d-lg-none">
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

            <div class="blog-filter-bar mb-4">
              <div class="d-flex flex-wrap align-items-center">
                <span class="me-3 filter-label">Filter by:</span>
                <div class="filter-tags">
                  <button
                    v-for="(category, index) in categories"
                    :key="index"
                    class="filter-tag"
                    :class="{ active: activeCategory === category.name }"
                    @click="filterByCategory(category.name)"
                  >
                    {{ category.name }}
                  </button>
                </div>
                <button
                  class="filter-tag reset-filter"
                  @click="resetFilters"
                >
                  Reset
                </button>
              </div>
            </div>

            <div v-if="filteredPosts.length === 0" class="no-results text-center py-5">
              <i class="fa-solid fa-magnifying-glass fa-3x mb-3"></i>
              <h3>No articles found</h3>
              <p>Try adjusting your search or filter criteria</p>
              <button class="btn btn-primary mt-3" @click="resetFilters">Reset Filters</button>
            </div>

            <div class="row" v-else>
              <div class="col-lg-6" v-for="(post, index) in filteredPosts" :key="index">
                <div class="sisf-blog">
                  <div class="sisf-blog-item">
                    <div class="sisf-e-inner">
                      <div class="sisf-e-media">
                        <div class="sisf-e-media-image" v-if="post.type === 'image'">
                          <a :href="post.link">
                            <figure class="image-anime reveal">
                              <img :src="post.image" class="image-fluid" alt="Dishify">
                              <div class="post-category-badge">{{ post.category }}</div>
                            </figure>
                          </a>
                        </div>
                        <div class="sisf-e-media-video" v-else-if="post.type === 'video'">
                          <video class="blog-video" autoplay muted loop>
                            <source :src="post.video" type="video/mp4">
                          </video>
                          <div class="post-category-badge">{{ post.category }}</div>
                        </div>
                      </div>
                      <div class="sisf-e-content">
                        <div class="sisf-e-info sisf-info--top mb-2">
                          <div class="sisf-e-info-date">
                            <i class="far fa-calendar me-1"></i> {{ post.date }}
                          </div>
                          <span class="sisf-e-info-divider">•</span>
                          <div class="sisf-e-info-author">
                            <i class="far fa-user me-1"></i> {{ post.author }}
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
                            <span>Read More <i class="fa-solid fa-arrow-right ms-2"></i></span>
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
          <div class="col-lg-3">
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
                    <li
                      class="product-categories-list-item"
                      v-for="(category, index) in categories"
                      :key="index"
                      @click="filterByCategory(category.name)"
                    >
                      <a :class="{ active: activeCategory === category.name }">
                        <span>{{ category.name }}</span>
                        <span class="category-count">({{ getCategoryCount(category.name) }})</span>
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

              <!-- Newsletter Widget -->
              <div class="sidebar-widget widget_newsletter">
                <h3 class="sidebar-title">Newsletter</h3>
                <div class="newsletter-form">
                  <p>Subscribe to get the latest food news and recipes</p>
                  <div class="input-group mb-3">
                    <input type="email" class="form-control" placeholder="Your email" v-model="email">
                    <button class="btn btn-subscribe" @click="subscribeNewsletter">
                      <i class="fa-solid fa-paper-plane"></i>
                    </button>
                  </div>
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
          type: 'image',
          image: 'https://dishify-html.wpthemeverse.com/images/blog-list-1.png',
          link: 'blog-single.html',
          date: '5 Apr, 2024',
          category: 'Italian',
          author: 'Chef Marco',
          title: 'Discovering Deliciousness, One Recipe at a Time',
          excerpt: 'Discovering Deliciousness, One Recipe at a Time invites you on a culinary journey filled with tantalizing flavors, aromatic spices, and mouthwatering dishes. It\'s an exploration of global cuisines that will delight your senses.',
          tags: ['pasta', 'recipes']
        },
        {
          type: 'image',
          image: 'https://dishify-html.wpthemeverse.com/images/blog-list-2.png',
          link: 'blog-single.html',
          date: '5 Apr, 2024',
          category: 'Pizza & Fast Food',
          author: 'Chef Sophia',
          title: 'Indulge in Delicious Creations',
          excerpt: 'Indulge in delicious creations that tantalize your taste buds and transport you to a world of culinary delight. From mouthwatering desserts to savory delights, our menu is a symphony of flavors.',
          tags: ['burgers', 'fastfood']
        },
        {
          type: 'image',
          image: 'https://dishify-html.wpthemeverse.com/images/blog-list-3.png',
          link: 'blog-single.html',
          date: '5 Apr, 2024',
          category: 'Pizza & Fast Food',
          author: 'Chef Antonio',
          title: 'Painting Palates with Flavorful Delights',
          excerpt: 'Nestled within the vibrant heart of a bustling city, Painting Palates with Flavorful Delights stands as a beacon of culinary creativity, inviting patrons on a journey of gastronomic discovery.',
          tags: ['pizza', 'italian']
        },
        {
          type: 'image',
          image: 'https://dishify-html.wpthemeverse.com/images/blog-list-4.png',
          link: 'blog-single.html',
          date: '5 Apr, 2024',
          category: 'Desserts',
          author: 'Pastry Chef Emma',
          title: 'Stories Behind the Dishes We Love',
          excerpt: 'Stories Behind the Dishes We Love delves into the captivating narratives intertwined with some of the most beloved culinary creations from around the globe. Through interviews with chefs and food historians, we uncover the origins of your favorite dishes.',
          tags: ['sweets', 'baking']
        },
        {
          type: 'image',
          image: 'https://dishify-html.wpthemeverse.com/images/blog-list-5.png',
          link: 'blog-single.html',
          date: '5 Apr, 2024',
          category: 'Italian',
          author: 'Chef Giovanni',
          title: 'Recipes and Reviews for Food Enthusiasts',
          excerpt: 'Recipes and Reviews for Food Enthusiasts is a platform designed to cater to the discerning palates and culinary curiosities of passionate food lovers. Bursting with a tantalizing array of recipes, restaurant reviews, and cooking tips.',
          tags: ['recipes', 'cooking']
        },
        {
          type: 'video',
          video: 'https://dishify.wpthemeverse.com/wp-content/uploads/2024/04/burger2.mp4',
          link: 'blog-single.html',
          date: '5 Apr, 2024',
          category: 'Sea Food',
          author: 'Chef Pierre',
          title: 'Tales from the Gastronomic World',
          excerpt: 'Amidst the bustling streets and quiet corners of the gastronomic world lie tales waiting to be told—stories that evoke the tantalizing aroma of spices, the sizzle of a hot pan, and the joy of shared meals.',
          tags: ['seafood', 'recipes']
        },
        {
          type: 'image',
          image: 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80',
          link: 'blog-single.html',
          date: '12 Apr, 2024',
          category: 'Desserts',
          author: 'Pastry Chef Lily',
          title: 'The Art of Plating: Making Your Dishes Instagram-Worthy',
          excerpt: 'Learn the secrets of professional plating techniques that will make your homemade dishes look like they came from a Michelin-starred restaurant. Simple tricks to elevate your food presentation.',
          tags: ['presentation', 'photography']
        },
        {
          type: 'image',
          image: 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80',
          link: 'blog-single.html',
          date: '18 Apr, 2024',
          category: 'Mexican',
          author: 'Chef Carlos',
          title: 'Authentic Mexican Street Food You Can Make at Home',
          excerpt: 'Bring the vibrant flavors of Mexican street food to your kitchen with these authentic recipes. From tacos al pastor to elote, discover how to recreate these beloved dishes at home.',
          tags: ['mexican', 'streetfood']
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
          image: 'https://dishify-html.wpthemeverse.com/images/blog-list-1.png',
          link: 'blog-single.html',
          title: 'Discovering Deliciousness, One Recipe at a Time',
          date: 'April 5, 2024'
        },
        {
          image: 'https://dishify-html.wpthemeverse.com/images/blog-list-2.png',
          link: 'blog-single.html',
          title: 'Indulge in Delicious Creations',
          date: 'April 5, 2024'
        },
        {
          image: 'https://dishify-html.wpthemeverse.com/images/blog-list-3.png',
          link: 'blog-single.html',
          title: 'Painting Palates with Flavorful Delights',
          date: 'April 5, 2024'
        }
      ],
      tags: [
        { name: 'pasta', link: '#' },
        { name: 'recipes', link: '#' },
        { name: 'italian', link: '#' },
        { name: 'baking', link: '#' },
        { name: 'seafood', link: '#' },
        { name: 'mexican', link: '#' },
        { name: 'streetfood', link: '#' }
      ]
    }
  },
  computed: {
    filteredPosts() {
      let filtered = this.blogPosts;

      // Filter by search query
      if (this.searchQuery) {
        const query = this.searchQuery.toLowerCase();
        filtered = filtered.filter(post =>
          post.title.toLowerCase().includes(query) ||
          post.excerpt.toLowerCase().includes(query) ||
          post.category.toLowerCase().includes(query) ||
          post.tags.some(tag => tag.toLowerCase().includes(query))
        );
      }

      // Filter by category
      if (this.activeCategory) {
        filtered = filtered.filter(post => post.category === this.activeCategory);
      }

      // Filter by tag
      if (this.activeTag) {
        filtered = filtered.filter(post => post.tags.includes(this.activeTag));
      }

      return filtered;
    },
    paginatedPosts() {
      const start = (this.currentPage - 1) * this.postsPerPage;
      const end = start + this.postsPerPage;
      return this.filteredPosts.slice(start, end);
    },
    totalPages() {
      return Math.ceil(this.filteredPosts.length / this.postsPerPage);
    }
  },
  methods: {
    searchPosts() {
      this.currentPage = 1; // Reset to first page when searching
    },
    filterByCategory(category) {
      this.activeCategory = this.activeCategory === category ? null : category;
      this.activeTag = null;
      this.currentPage = 1;
    },
    filterByTag(tag) {
      this.activeTag = this.activeTag === tag ? null : tag;
      this.activeCategory = null;
      this.currentPage = 1;
    },
    resetFilters() {
      this.searchQuery = '';
      this.activeCategory = null;
      this.activeTag = null;
      this.currentPage = 1;
    },
    getCategoryCount(categoryName) {
      return this.blogPosts.filter(post => post.category === categoryName).length;
    },
    subscribeNewsletter() {
      if (this.email) {
        alert(`Thank you for subscribing with ${this.email}!`);
        this.email = '';
      } else {
        alert('Please enter your email address');
      }
    }
  },
  watch: {
    filteredPosts() {
      if (this.paginatedPosts.length === 0 && this.currentPage > 1) {
        this.currentPage = this.totalPages;
      }
    }
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
  opacity: 0.9;
}

/* Blog Grid Styles */
.sisf-page-section {
  padding: 60px 0;
  font-family: var(--font-body);
  background-color: var(--light-color);
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 15px;
}

.blog-search-mobile {
  background: white;
  padding: 15px;
  border-radius: 8px;
  box-shadow: 0 2px 10px var(--shadow-color);
}

.blog-filter-bar {
  background: white;
  padding: 15px 20px;
  border-radius: 8px;
  box-shadow: 0 2px 10px var(--shadow-color);
}

.filter-label {
  font-weight: 600;
  color: var(--dark-color);
}

.filter-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.filter-tag {
  padding: 6px 15px;
  background: var(--light-color);
  border: none;
  border-radius: 20px;
  font-size: 0.9rem;
  color: var(--medium-color);
  cursor: pointer;
  transition: var(--transition);
}

.filter-tag:hover {
  background: var(--primary-color);
  color: white;
}

.filter-tag.active {
  background: var(--primary-color);
  color: white;
}

.reset-filter {
  margin-left: auto;
  background: transparent;
  color: var(--primary-color);
  font-weight: 600;
}

.reset-filter:hover {
  background: transparent;
  color: var(--primary-dark);
}

.no-results {
  background: white;
  padding: 40px;
  border-radius: 8px;
  box-shadow: 0 2px 10px var(--shadow-color);
}

.no-results i {
  color: var(--primary-color);
  opacity: 0.7;
}

.no-results h3 {
  font-family: var(--font-heading);
  color: var(--dark-color);
  margin-bottom: 10px;
}

.no-results p {
  color: var(--medium-color);
  margin-bottom: 20px;
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

.sisf-e-media-image img,
.sisf-e-media-video video {
  width: 100%;
  height: 220px;
  object-fit: cover;
  transition: transform 0.5s ease;
}

.sisf-blog-item:hover .sisf-e-media-image img {
  transform: scale(1.05);
}

.post-category-badge {
  position: absolute;
  top: 15px;
  right: 15px;
  background: var(--primary-color);
  color: white;
  padding: 5px 15px;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 600;
}

.sisf-e-media {
  position: relative;
  overflow: hidden;
}

.sisf-e-content {
  padding: 25px;
}

.sisf-e-info {
  display: flex;
  align-items: center;
  font-size: 0.85rem;
  color: var(--medium-color);
  margin-bottom: 15px;
  flex-wrap: wrap;
}

.sisf-e-info i {
  font-size: 0.9rem;
}

.sisf-e-info-divider {
  margin: 0 10px;
}

.sisf-e-title {
  font-family: var(--font-heading);
  font-size: 1.7rem;
  font-weight: 600;
  margin-bottom: 15px;
  line-height: 1.3;
  color: var(--dark-color);
}

.sisf-e-title a {
  color: inherit;
  text-decoration: none;
  transition: var(--transition);
}

.sisf-e-title a:hover {
  color: var(--primary-color);
}

.sisf-e-excerpt {
  font-size: 1rem;
  color: var(--medium-color);
  line-height: 1.6;
  margin-bottom: 20px;
}

.btn-default {
  display: inline-flex;
  align-items: center;
  padding: 10px 25px;
  background: var(--primary-color);
  color: white;
  border: none;
  border-radius: 30px;
  font-size: 0.9rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  text-decoration: none;
  transition: var(--transition);
  cursor: pointer;
}

.btn-default:hover {
  background: var(--primary-dark);
  transform: translateX(5px);
}

.btn-default i {
  margin-left: 8px;
  font-size: 0.9rem;
  transition: var(--transition);
}

.btn-default:hover i {
  transform: translateX(3px);
}

.blog-pagination {
  display: flex;
  justify-content: center;
  gap: 5px;
}

.pagination-btn {
  width: 40px;
  height: 40px;
  border: 1px solid var(--border-color);
  background: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: var(--transition);
}

.pagination-btn:hover {
  background: var(--primary-color);
  color: white;
  border-color: var(--primary-color);
}

.pagination-btn.active {
  background: var(--primary-color);
  color: white;
  border-color: var(--primary-color);
}

.pagination-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Sidebar Styles */
.sisf-page-sidebar {
  padding: 25px;
  border-radius: 8px;
  box-shadow: 0 5px 15px var(--shadow-color);
  background: white;
  position: sticky;
  top: 30px;
}

.sidebar-title {
  font-family: var(--font-heading);
  font-size: 1.5rem;
  font-weight: 600;
  margin-bottom: 20px;
  color: var(--dark-color);
  position: relative;
  padding-bottom: 10px;
}

.sidebar-title::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  width: 50px;
  height: 2px;
  background: var(--primary-color);
}

.sisf-search-form {
  position: relative;
  margin-bottom: 25px;
}

.sisf-search-form-inner {
  display: flex;
  border: 1px solid var(--border-color);
  border-radius: 30px;
  overflow: hidden;
}

.sisf-search-form-field {
  flex: 1;
  padding: 12px 20px;
  border: none;
  font-size: 0.95rem;
  outline: none;
}

.sisf-search-form-button {
  padding: 0 20px;
  background: var(--primary-color);
  color: white;
  border: none;
  cursor: pointer;
  transition: var(--transition);
}

.sisf-search-form-button:hover {
  background: var(--primary-dark);
}

.product-categories-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.product-categories-list-item {
  margin-bottom: 12px;
}

.product-categories-list-item a {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px 0;
  color: var(--medium-color);
  text-decoration: none;
  transition: var(--transition);
  border-bottom: 1px dashed var(--border-color);
}

.product-categories-list-item a:hover {
  color: var(--primary-color);
  transform: translateX(5px);
}

.product-categories-list-item a.active {
  color: var(--primary-color);
  font-weight: 600;
}

.category-count {
  background: var(--light-color);
  padding: 2px 8px;
  border-radius: 10px;
  font-size: 0.8rem;
}

.sidebar_blog-list {
  margin-bottom: 25px;
}

.blog-list-widget {
  list-style: none;
  padding: 0;
  margin: 0;
}

.blog-list-widget li {
  display: flex;
  margin-bottom: 20px;
  padding-bottom: 20px;
  border-bottom: 1px dashed var(--border-color);
}

.blog-list-widget li:last-child {
  margin-bottom: 0;
  padding-bottom: 0;
  border-bottom: none;
}

.sisf-blog-image {
  flex: 0 0 80px;
  margin-right: 15px;
  border-radius: 5px;
  overflow: hidden;
}

.sisf-blog-image img {
  width: 100%;
  height: 80px;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.blog-list-widget li:hover .sisf-blog-image img {
  transform: scale(1.1);
}

.sisf-blog-content {
  flex: 1;
}

.sisf-blog-title {
  font-size: 1rem;
  margin-bottom: 5px;
  line-height: 1.4;
}

.sisf-blog-title a {
  color: var(--dark-color);
  text-decoration: none;
  transition: var(--transition);
}

.sisf-blog-title a:hover {
  color: var(--primary-color);
}

.publish-date {
  font-size: 0.8rem;
  color: var(--medium-color);
}

.sidebar_tag-list {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-bottom: 25px;
}

.sidebar_tag-list a {
  display: inline-block;
  padding: 6px 12px;
  background: var(--light-color);
  color: var(--medium-color);
  font-size: 0.8rem;
  border-radius: 3px;
  text-decoration: none;
  transition: var(--transition);
}

.sidebar_tag-list a:hover {
  background: var(--primary-color);
  color: white;
}

.newsletter-form {
  margin-top: 15px;
}

.newsletter-form p {
  font-size: 0.9rem;
  color: var(--medium-color);
  margin-bottom: 15px;
}

.input-group {
  display: flex;
}

.form-control {
  flex: 1;
  padding: 10px 15px;
  border: 1px solid var(--border-color);
  border-radius: 30px 0 0 30px;
  outline: none;
  font-size: 0.9rem;
}

.btn-subscribe {
  padding: 0 20px;
  background: var(--primary-color);
  color: white;
  border: none;
  border-radius: 0 30px 30px 0;
  cursor: pointer;
  transition: var(--transition);
}

.btn-subscribe:hover {
  background: var(--primary-dark);
}

.sidebar-separator {
  margin: 25px 0;
}

.sidebar-line {
  border: none;
  height: 1px;
  background: linear-gradient(to right, transparent, var(--border-color), transparent);
  margin: 0;
}

/* Responsive Adjustments */
@media (max-width: 992px) {
  .col-lg-9 {
    margin-bottom: 40px;
  }

  .sisf-page-sidebar {
    position: static;
  }

  .sisf-m-title {
    font-size: 3rem;
  }
}

@media (max-width: 768px) {
  .sisf-banner {
    height: 350px;
  }

  .sisf-m-title {
    font-size: 2.5rem;
  }

  .sisf-m-subtitle {
    font-size: 1.2rem;
  }

  .filter-label {
    width: 100%;
    margin-bottom: 10px;
  }
}

@media (max-width: 576px) {
  .sisf-banner {
    height: 300px;
  }

  .sisf-m-title {
    font-size: 2rem;
  }

  .sisf-e-title {
    font-size: 1.5rem;
  }

  .blog-list-widget li {
    flex-direction: column;
  }

  .sisf-blog-image {
    margin-right: 0;
    margin-bottom: 15px;
  }
}
</style>
