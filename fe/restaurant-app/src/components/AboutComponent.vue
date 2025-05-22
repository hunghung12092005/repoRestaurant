<template>
  <div>
    <!-- About Us Banner Start -->
    <div class="sisf-banner position-relative">
      <div class="banner-img">
        <figure>
          <img src="https://dishify-html.wpthemeverse.com/images/dishify-about-banner.png" alt="Dishify" />
        </figure>
      </div>
      <div class="sisf-page-title sisf-m sisf-title--standard sisf-alignment--center">
        <div class="sisf-m-inner">
          <div class="sisf-m-content sisf-content-grid">
            <h1 class="sisf-m-title entry-title">About Us</h1>
          </div>
        </div>
      </div>
    </div>
    <!-- About Us Banner End -->

    <!-- Culinary Journey Section Start -->
    <div class="colinary-journey section">
      <div class="container-fluid p-0">
        <div class="row align-items-center">
          <div class="col-md-3">
            <figure>
              <img src="https://dishify-html.wpthemeverse.com/images/dishify-graphic4.png" alt="Dishify" />
            </figure>
          </div>
          <div class="col-md-6">
            <!-- Section Title Start -->
            <div class="sisf-sis-section-title sisf-center-text text-center section-title wow fadeInLeft" data-wow-delay="0.2s">
              <h3 class="sisf-m-subtitle">Spice Odyssey</h3>
              <h2 class="sisf-m-title">A Culinary Journey</h2>
              <div class="sisf-m-text text-center">
                <p>
                  A culinary journey that transcends taste, inviting patrons to savor not just flavors, but narratives woven into each dish. From the heritage of ingredients to the passion of chefs, every element unfolds a tale, creating an immersive dining experience that delights both palate and imagination. A culinary journey that transcends taste, inviting patrons to savor not just flavors, but narratives woven into each dish.
                </p>
              </div>
            </div>
            <!-- Section Title End -->
          </div>
          <div class="col-md-3">
            <div class="text-end">
              <figure>
                <img src="https://dishify-html.wpthemeverse.com/images/dishify-graphic5.png" alt="Dishify" />
              </figure>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-2 d-flex align-items-end">
            <div class="float">
              <figure>
                <img src="https://dishify-html.wpthemeverse.com/images/dishify-culinary_img2.png" alt="Dishify" />
              </figure>
            </div>
          </div>
          <div class="col-md-8">
            <div class="text-end">
              <figure>
                <img src="https://dishify-html.wpthemeverse.com/images/dishify-culinary_img1.png" alt="Dishify" />
              </figure>
            </div>
          </div>
          <div class="col-md-2">
            <div class="float">
              <figure>
                <img src="https://dishify-html.wpthemeverse.com/images/dishify-culinary_img3.png" alt="Dishify" />
              </figure>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Culinary Journey Section End -->

    <!-- Delicious Discovery Section Start -->
    <div class="delicious-discovery section sisf-extended-grid sisf-extended-grid--left">
      <div class="container-fluid p-0">
        <div class="row align-items-center">
          <div class="col-md-6 ps-0">
            <div class="sisf-e-img-holder">
              <figure>
                <img src="https://dishify-html.wpthemeverse.com/images/global_img.png" alt="Dishify" />
              </figure>
            </div>
          </div>
          <div class="col-md-6">
            <!-- Section Title Start -->
            <div class="sisf-sis-section-title section-title wow fadeInRight" data-wow-delay="0.2s">
              <h3 class="sisf-m-subtitle">The Joy of Eating</h3>
              <h2 class="sisf-m-title">Global Cuisine at Your Fingertips</h2>
              <div class="sisf-m-text pb-4">
                <p>
                  A culinary journey that transcends taste, inviting patrons to savor not just flavors, but narratives woven into each dish. From the heritage of ingredients to the passion of chefs, every element unfolds a tale, creating an immersive dining experience that delights both palate and imagination. A culinary journey that transcends taste, inviting patrons to savor not just flavors, but narratives woven into each dish. From the heritage of ingredients to the passion of chefs, every element unfolds a tale, creating an immersive dining experience that delights both palate and imagination.
                </p>
              </div>
            </div>
            <!-- Section Title End -->
          </div>
        </div>
      </div>
    </div>
    <!-- Delicious Discovery Section End -->

    <!-- Flavors Of The World Section Start -->
    <div class="flavors-of-the-world section sisf-extended-grid sisf-extended-grid--left">
      <div class="container-fluid ps-0">
        <div class="row align-items-center">
          <div class="col-md-2">
            <figure>
              <img src="https://dishify-html.wpthemeverse.com/images/dishify-exploring_img2.png" alt="Dishify" />
            </figure>
          </div>
          <div class="col-md-4">
            <figure>
              <img src="https://dishify-html.wpthemeverse.com/images/dishify-exploring_img1.png" alt="Dishify" />
            </figure>
          </div>
          <div class="col-md-6">
            <!-- Section Title Start -->
            <div class="sisf-sis-section-title section-title wow fadeInRight" data-wow-delay="0.2s">
              <h3 class="sisf-m-subtitle">Flavors of the World</h3>
              <h2 class="sisf-m-title text-white">Exploring the World</h2>
              <div class="sisf-m-text">
                <p class="text-white">
                  A culinary journey that transcends taste, inviting patrons to savor not just flavors, but narratives woven into each dish. From the heritage of ingredients to the passion.
                </p>
              </div>
            </div>
            <!-- Section Title End -->
            <div class="progressbar">
              <!-- Skills Progress Bar Start -->
              <div class="skills-progress-bar" v-for="(skill, index) in skills" :key="index">
                <div class="skillbar" :data-percent="skill.percent">
                  <div class="skill-data">
                    <div class="skill-title text-white">{{ skill.country }}</div>
                    <div class="skill-no">{{ skill.percent }}</div>
                  </div>
                  <div class="skill-progress">
                    <div class="count-bar" :style="{ width: skill.percent }"></div>
                  </div>
                </div>
              </div>
              <!-- Skills Progress Bar End -->
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Flavors Of The World Section End -->

    <!-- Gallery Section Start -->
    <div class="section gallery-section position-relative pb-0">
      <div class="container-fluid p-0">
        <div class="row gallery-items page-gallery-box">
          <!-- Row 1 -->
          <div class="col-lg-3 col-md-6 col-12">
            <div class="photo-gallery wow fadeInUp" data-wow-delay="0.2s">
              <a href="https://dishify-html.wpthemeverse.com/images/gallery_img1.png">
                <figure class="image-anime">
                  <img src="https://dishify-html.wpthemeverse.com/images/gallery_img1.png" alt="Dishify" />
                </figure>
              </a>
            </div>
          </div>
          <div class="col-lg-3 col-md-6 col-12">
            <div class="photo-gallery wow fadeInUp" data-wow-delay="0.3s">
              <a href="https://dishify-html.wpthemeverse.com/images/gallery_img6.png">
                <figure class="image-anime">
                  <img src="https://dishify-html.wpthemeverse.com/images/gallery_img6.png" alt="Dishify" />
                </figure>
              </a>
            </div>
          </div>
          <div class="col-lg-3 col-md-6 col-12">
            <div class="photo-gallery wow fadeInUp" data-wow-delay="0.4s">
              <a href="https://dishify-html.wpthemeverse.com/images/gallery_img5.png">
                <figure class="image-anime">
                  <img src="https://dishify-html.wpthemeverse.com/images/gallery_img5.png" alt="Dishify" />
                </figure>
              </a>
            </div>
          </div>
          <div class="col-lg-3 col-md-6 col-12">
            <div class="photo-gallery wow fadeInUp" data-wow-delay="0.5s">
              <a href="https://dishify-html.wpthemeverse.com/images/gallery_img2.png">
                <figure class="image-anime">
                  <img src="https://dishify-html.wpthemeverse.com/images/gallery_img2.png" alt="Dishify" />
                </figure>
              </a>
            </div>
          </div>
          <!-- Row 2 -->
          <div class="col-lg-3 col-md-6 col-12">
            <div class="photo-gallery wow fadeInUp" data-wow-delay="0.2s">
              <a href="https://dishify-html.wpthemeverse.com/images/gallery_img_1.png">
                <figure class="image-anime">
                  <img src="https://dishify-html.wpthemeverse.com/images/gallery_img_1.png" alt="Dishify" />
                </figure>
              </a>
            </div>
          </div>
          <div class="col-lg-3 col-md-6 col-12">
            <div class="photo-gallery wow fadeInUp" data-wow-delay="0.3s">
              <a href="https://dishify-html.wpthemeverse.com/images/gallery_img_5.png">
                <figure class="image-anime">
                  <img src="https://dishify-html.wpthemeverse.com/images/gallery_img_5.png" alt="Dishify" />
                </figure>
              </a>
            </div>
          </div>
          <div class="col-lg-3 col-md-6 col-12">
            <div class="photo-gallery wow fadeInUp" data-wow-delay="0.4s">
              <a href="https://dishify-html.wpthemeverse.com/images/gallery_img_4.png">
                <figure class="image-anime">
                  <img src="https://dishify-html.wpthemeverse.com/images/gallery_img_4.png" alt="Dishify" />
                </figure>
              </a>
            </div>
          </div>
          <div class="col-lg-3 col-md-6 col-12">
            <div class="photo-gallery wow fadeInUp" data-wow-delay="0.5s">
              <a href="https://dishify-html.wpthemeverse.com/images/gallery_img3.png">
                <figure class="image-anime">
                  <img src="https://dishify-html.wpthemeverse.com/images/gallery_img3.png" alt="Dishify" />
                </figure>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Gallery Section End -->

    <!-- Testimonial Section Start -->
    <div class="testimonial section">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12 text-center">
            <!-- Section Title Start -->
            <div class="sisf-sis-section-title sisf-center-text section-title wow bounceIn" data-wow-delay="0.2s">
              <h3 class="sisf-m-subtitle">Testimonials</h3>
              <h2 class="sisf-m-title">Words From Customers</h2>
            </div>
            <!-- Section Title End -->
            <div class="carousel">
              <div class="carousel-inner">
                <div class="carousel-item" v-for="(testimonial, index) in testimonials" :key="index" :class="{ active: currentTestimonial === index }">
                  <div class="sisf-e-media-image mb-4">
                    <img :src="testimonial.image" alt="Dishify" />
                  </div>
                  <div class="sisf-e-author mb-4">
                    <h5 class="sisf-e-author-name mb-4">{{ testimonial.name }}</h5>
                    <span class="sisf-e-author-job">{{ testimonial.job }}</span>
                  </div>
                  <p class="sisf-e-text">{{ testimonial.text }}</p>
                  <div class="sisf-icon-holder">
                    <i class="fa-solid fa-quote-left"></i>
                  </div>
                </div>
              </div>
              <!-- Left and right controls -->
              <button class="carousel-control-prev" @click="prevTestimonial">
                <span class="carousel-control-prev-icon"></span>
              </button>
              <button class="carousel-control-next" @click="nextTestimonial">
                <span class="carousel-control-next-icon"></span>
              </button>
              <!-- Pagination -->
              <div class="carousel-indicators">
                <button
                  v-for="(testimonial, index) in testimonials"
                  :key="index"
                  :class="{ active: currentTestimonial === index }"
                  @click="goToTestimonial(index)"
                  :aria-label="'Slide ' + (index + 1)"
                ></button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Testimonial Section End -->

    <!-- Blog Section Start -->
    <div class="letest-blog-section section">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <!-- Section Title Start -->
            <div class="sisf-sis-section-title text-center sisf-center-text section-title wow bounceIn" data-wow-delay="0.2s">
              <h3 class="sisf-m-subtitle">Welcome</h3>
              <h2 class="sisf-m-title">Latest Blog</h2>
            </div>
            <!-- Section Title End -->
          </div>
        </div>
        <div class="row">
          <div class="col-md-4" v-for="(blog, index) in blogs" :key="index">
            <div class="letest-blog sisf-blog-item mb-0">
              <div class="sisf-e-inner">
                <div class="sisf-e-media">
                  <div class="sisf-e-media-image">
                    <a :href="blog.link">
                      <img :src="blog.image" alt="Dishify" />
                    </a>
                  </div>
                </div>
                <div class="sisf-e-content">
                  <div class="sisf-e-info sisf-info--top">
                    <div class="sisf-e-info-item sisf-e-info-category mb-3">
                      <a href="blogs.html">{{ blog.category }}</a>
                    </div>
                  </div>
                  <div class="sisf-e-text">
                    <h5 class="sisf-e-title entry-title">
                      <a class="sisf-e-title-link" :href="blog.link">{{ blog.title }}</a>
                    </h5>
                  </div>
                  <div class="sisf-e-info-item sisf-e-info-date entry-date">
                    <a href="blogs.html">{{ blog.date }}</a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Blog Section End -->
  </div>
</template>

<script>
export default {
  name: 'AboutComponent',
  data() {
    return {
      skills: [
        { country: 'United States', percent: '92%' },
        { country: 'Germany', percent: '84%' },
        { country: 'India', percent: '48%' },
        { country: 'Australia', percent: '38%' },
        { country: 'South Africa', percent: '88%' },
      ],
      testimonials: [
        {
          image: 'https://dishify-html.wpthemeverse.com/images/testimonial-chris.png',
          name: 'Chris Veldor',
          job: 'Food Blogger',
          text: 'Vestibulum eu quam nec neque pellentesque efficitur id eget nisl. Proin porta est convallis lacus blandit pretium sed non enim. Maecenas lacinia non orci at aliquam. Donec finibus, Proin urna bibendum ultricies est laoreet. Proin porta est convallis lacus blandit pretium sed non enim.',
        },
        {
          image: 'https://dishify-html.wpthemeverse.com/images/testimonial-chris.png',
          name: 'Chris Veldor',
          job: 'Food Blogger',
          text: 'Vestibulum eu quam nec neque pellentesque efficitur id eget nisl. Proin porta est convallis lacus blandit pretium sed non enim. Maecenas lacinia non orci at aliquam. Donec finibus, Proin urna bibendum ultricies est laoreet. Proin porta est convallis lacus blandit pretium sed non enim.',
        },
        {
          image: 'https://dishify-html.wpthemeverse.com/images/testimonial-chris.png',
          name: 'Chris Veldor',
          job: 'Food Blogger',
          text: 'Vestibulum eu quam nec neque pellentesque efficitur id eget nisl. Proin porta est convallis lacus blandit pretium sed non enim. Maecenas lacinia non orci at aliquam. Donec finibus, Proin urna bibendum ultricies est laoreet. Proin porta est convallis lacus blandit pretium sed non enim.',
        },
      ],
      currentTestimonial: 0,
      blogs: [
        {
          image: 'https://dishify-html.wpthemeverse.com/images/blog_detail1.png',
          category: 'Italian',
          title: 'Discovering Deliciousness, One Recipe at a Time',
          link: 'blog-single.html',
          date: '5 Apr, 2024',
        },
        {
          image: 'https://dishify-html.wpthemeverse.com/images/blog_detail2.png',
          category: 'Pizza & Fast Food',
          title: 'Indulge in Delicious Creations',
          link: 'blog-single.html',
          date: '5 Apr, 2024',
        },
        {
          image: 'https://dishify-html.wpthemeverse.com/images/blog_detail3.png',
          category: 'Pizza & Fast Food',
          title: 'Painting Palates with Flavorful Delights',
          link: 'blog-single.html',
          date: '5 Apr, 2024',
        },
      ],
    };
  },
  methods: {
    nextTestimonial() {
      this.currentTestimonial = (this.currentTestimonial + 1) % this.testimonials.length;
    },
    prevTestimonial() {
      this.currentTestimonial = (this.currentTestimonial - 1 + this.testimonials.length) % this.testimonials.length;
    },
    goToTestimonial(index) {
      this.currentTestimonial = index;
    },
  },
  mounted() {
    // Initialize WOW.js for animations
    if (typeof window !== 'undefined' && window.WOW) {
      new window.WOW().init();
    }
  },
};
</script>

<style scoped>
/* Bootstrap Grid and Utilities */
@import url('https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css');

/* WOW.js Animation */
@import url('https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css');

/* Custom Styles from custom.css (simplified and scoped) */
.sisf-banner {
  position: relative;
}
.sisf-banner .banner-img img {
  width: 100%;
  height: auto;
}
.sisf-page-title {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  text-align: center;
  color: #fff;
}

.colinary-journey .sisf-m-subtitle {
  font-family: 'Cherish', cursive;
  color: #f24709e8;
  font-size: 50px;
  line-height: 1.2;
  margin-bottom: 10px;
}

.sisf-page-title h1 {
  font-size: 3rem;
  font-family: 'Cormorant Infant', serif;
}
.section {
  padding: 60px 0;
}

/* Delicious Discovery Section Specific Styles */
.delicious-discovery {
  position: relative;
  background: #fff;
  background-image: url(https://png.pngtree.com/thumb_back/fh260/background/20231227/pngtree-crinkled-ivory-space-paper-textured-background-image_13861197.png);
  background-size: cover;
  background-position: center;
}
.delicious-discovery .sisf-sis-section-title {
  padding: 0 20px;
}
.delicious-discovery .sisf-m-subtitle {
  font-family: 'Cherish', cursive;
  color: #f24709e8;
  font-size: 50px;
  line-height: 1.2;
  margin-bottom: 10px;
}
.delicious-discovery .sisf-m-title {
  font-family: 'Cormorant Infant', serif;
  font-size: 3rem;
  font-weight: 700;
  color: #333;
  line-height: 1.2;
  margin-bottom: 20px;
}
.delicious-discovery .sisf-m-text p {
  font-family: 'Work Sans', sans-serif;
  color: #666;
  font-size: 1rem;
  line-height: 1.6;
  max-width: 500px;
}

.sisf-e-img-holder {
  margin-left: 0;
  padding-left: 0;
}
.sisf-e-img-holder figure {
  margin: 0;
}
.sisf-e-img-holder img {
  max-width: 100%;
  height: auto;
  display: block;
  margin-left: 0;
}

/* General Section Styles */
.sisf-sis-section-title h3 {
  font-family: 'Cherish', cursive;
  color: #f15a22;
  font-size: 1.5rem;
}
.sisf-sis-section-title h2 {
  font-family: 'Cormorant Infant', serif;
  font-size: 2.5rem;
  margin-bottom: 20px;
}
.sisf-m-text p {
  font-family: 'Work Sans', sans-serif;
  color: #666;
}
.colinary-journey img,
.flavors-of-the-world img {
  max-width: 100%;
  height: auto;
}
.flavors-of-the-world {
  background: #1a1a1a;
}
.skillbar {
  margin-bottom: 20px;
}
.skill-data {
  display: flex;
  justify-content: space-between;
  color: #fff;
  font-family: 'Work Sans', sans-serif;
}
.skill-progress {
  background: #333;
  height: 6px;
  border-radius: 3px;
  overflow: hidden;
}
.count-bar {
  background: #f15a22;
  height: 100%;
  transition: width 1s ease-in-out;
}
.photo-gallery {
  margin-bottom: 20px;
}
.photo-gallery img {
  width: 100%;
  height: 300px;
  object-fit: cover;
  transition: transform 0.3s;
}
.photo-gallery img:hover {
  transform: scale(1.05);
}
.carousel {
  position: relative;
}
.carousel-item {
  display: none;
}
.carousel-item.active {
  display: block;
}
.carousel-control-prev,
.carousel-control-next {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  width: 40px;
  height: 40px;
  background: #f15a22;
  border: none;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
}
.carousel-control-prev {
  left: 10px;
}
.carousel-control-next {
  right: 10px;
}
.carousel-control-prev-icon,
.carousel-control-next-icon {
  width: 20px;
  height: 20px;
  background: url('data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white"%3E%3Cpath d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/%3E%3C/svg%3E') no-repeat center;
}
.carousel-control-next-icon {
  transform: rotate(180deg);
}
.carousel-indicators {
  position: absolute;
  bottom: 10px;
  left: 50%;
  transform: translateX(-50%);
  display: flex;
  gap: 10px;
}
.carousel-indicators button {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: #ccc;
  border: none;
  cursor: pointer;
}
.carousel-indicators button.active {
  background: #f15a22;
}
.sisf-e-media-image img {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  margin: 0 auto;
}
.sisf-e-author-name {
  font-family: 'Cormorant Infant', serif;
  font-size: 1.5rem;
}
.sisf-e-author-job {
  font-family: 'Work Sans', sans-serif;
  color: #f15a22;
}
.sisf-e-text {
  font-family: 'Work Sans', sans-serif;
  color: #666;
  max-width: 600px;
  margin: 0 auto;
}
.sisf-icon-holder i {
  font-size: 2rem;
  color: #f15a22;
}
.letest-blog img {
  width: 100%;
  height: auto;
}
.sisf-e-info-category a {
  color: #f15a22;
  text-decoration: none;
}
.sisf-e-title a {
  font-family: 'Cormorant Infant', serif;
  color: #333;
  text-decoration: none;
}
.sisf-e-info-date a {
  color: #666;
  text-decoration: none;
}

/* Font Awesome */

/* Responsive Adjustments */
@media (max-width: 767px) {
  .sisf-page-title h1 {
    font-size: 2rem;
  }
  .delicious-discovery .sisf-m-title {
    font-size: 2rem;
  }
  .delicious-discovery .sisf-m-subtitle {
    font-size: 1.2rem;
  }
  .delicious-discovery .sisf-m-text p {
    font-size: 0.9rem;
  }
  .photo-gallery img {
    height: 150px; /* Smaller height on mobile */
  }
}
.sisf-banner .banner-img img {
  width: 100%;
  height: auto;
}
.sisf-page-title {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  text-align: center;
  color: #fff;
}

.colinary-journey .sisf-m-subtitle {
  font-family: 'Cherish', cursive;
  color: #f24709e8;
  font-size: 50px;
  line-height: 1.2;
  margin-bottom: 10px;
}

.sisf-page-title h1 {
  font-size: 3rem;
  font-family: 'Cormorant Infant', serif;
}
.section {
  padding: 60px 0;
}

/* Delicious Discovery Section Specific Styles */
.delicious-discovery {
  position: relative;
  background: #fff;
  background-image: url(https://png.pngtree.com/thumb_back/fh260/background/20231227/pngtree-crinkled-ivory-space-paper-textured-background-image_13861197.png);
  background-size: cover;
  background-position: center;
}
.delicious-discovery .sisf-sis-section-title {
  padding: 0 20px;
}
.delicious-discovery .sisf-m-subtitle {
  font-family: 'Cherish', cursive;
  color: #f24709e8;
  font-size: 50px;
  line-height: 1.2;
  margin-bottom: 10px;
}
.delicious-discovery .sisf-m-title {
  font-family: 'Cormorant Infant', serif;
  font-size: 3rem;
  font-weight: 700;
  color: #333;
  line-height: 1.2;
  margin-bottom: 20px;
}
.delicious-discovery .sisf-m-text p {
  font-family: 'Work Sans', sans-serif;
  color: #666;
  font-size: 1rem;
  line-height: 1.6;
  max-width: 500px;
}

.sisf-e-img-holder {
  margin-left: 0;
  padding-left: 0;
}
.sisf-e-img-holder figure {
  margin: 0;
}
.sisf-e-img-holder img {
  max-width: 100%;
  height: auto;
  display: block;
  margin-left: 0;
}

/* General Section Styles */
.sisf-sis-section-title h3 {
  font-family: 'Cherish', cursive;
  color: #f15a22;
  font-size: 1.5rem;
}
.sisf-sis-section-title h2 {
  font-family: 'Cormorant Infant', serif;
  font-size: 2.5rem;
  margin-bottom: 20px;
}
.sisf-m-text p {
  font-family: 'Work Sans', sans-serif;
  color: #666;
}
.colinary-journey img,
.flavors-of-the-world img {
  max-width: 100%;
  height: auto;
}
.flavors-of-the-world {
  background: #1a1a1a;
}
.skillbar {
  margin-bottom: 20px;
}
.skill-data {
  display: flex;
  justify-content: space-between;
  color: #fff;
  font-family: 'Work Sans', sans-serif;
}
.skill-progress {
  background: #333;
  height: 6px;
  border-radius: 3px;
  overflow: hidden;
}
.count-bar {
  background: #f15a22;
  height: 100%;
  transition: width 1s ease-in-out;
}
.photo-gallery {
  margin-bottom: 20px;
}
.photo-gallery img {
  width: 100%;
  height: 300px;
  object-fit: cover;
  transition: transform 0.3s;
}
.photo-gallery img:hover {
  transform: scale(1.05);
}
.carousel {
  position: relative;
}
.carousel-item {
  display: none;
}
.carousel-item.active {
  display: block;
}
.carousel-control-prev,
.carousel-control-next {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  width: 40px;
  height: 40px;
  background: #f15a22;
  border: none;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
}
.carousel-control-prev {
  left: 10px;
}
.carousel-control-next {
  right: 10px;
}
.carousel-control-prev-icon,
.carousel-control-next-icon {
  width: 20px;
  height: 20px;
  background: url('data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white"%3E%3Cpath d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/%3E%3C/svg%3E') no-repeat center;
}
.carousel-control-next-icon {
  transform: rotate(180deg);
}
.carousel-indicators {
  position: absolute;
  bottom: 10px;
  left: 50%;
  transform: translateX(-50%);
  display: flex;
  gap: 10px;
}
.carousel-indicators button {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: #ccc;
  border: none;
  cursor: pointer;
}
.carousel-indicators button.active {
  background: #f15a22;
}
.sisf-e-media-image img {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  margin: 0 auto;
}
.sisf-e-author-name {
  font-family: 'Cormorant Infant', serif;
  font-size: 1.5rem;
}
.sisf-e-author-job {
  font-family: 'Work Sans', sans-serif;
  color: #f15a22;
}
.sisf-e-text {
  font-family: 'Work Sans', sans-serif;
  color: #666;
  max-width: 600px;
  margin: 0 auto;
}
.sisf-icon-holder i {
  font-size: 2rem;
  color: #f15a22;
}
.letest-blog img {
  width: 100%;
  height: auto;
}
.sisf-e-info-category a {
  color: #f15a22;
  text-decoration: none;
}
.sisf-e-title a {
  font-family: 'Cormorant Infant', serif;
  color: #333;
  text-decoration: none;
}
.sisf-e-info-date a {
  color: #666;
  text-decoration: none;
}

/* Font Awesome */

/* Responsive Adjustments */
@media (max-width: 767px) {
  .sisf-page-title h1 {
    font-size: 2rem;
  }
  .delicious-discovery .sisf-m-title {
    font-size: 2rem;
  }
  .delicious-discovery .sisf-m-subtitle {
    font-size: 1.2rem;
  }
  .delicious-discovery .sisf-m-text p {
    font-size: 0.9rem;
  }
  .photo-gallery img {
    height: 150px; /* Smaller height on mobile */
  }
}
</style>