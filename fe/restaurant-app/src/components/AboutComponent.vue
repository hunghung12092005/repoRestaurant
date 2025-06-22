<template>
  <div>
    <!-- About Us Banner Start -->
    <section class="home" id="home">
      <!-- <div v-if="userName" class="home-text">
            <span>hh to our</span>
            <h1>Xin chào <br>{{ userName }}</h1>
            <p>Nhà Hàng Vinpearl Thanh Hóa sẵn sàng phục vụ</p>
            <a href="#" class="btn">Xem  Menu</a>
        </div> -->
      <div class="home-text mt-4">
        <span class="">Sầm Sơn Beach</span>
        <h1>Hồ Xuân Hương Eco<br> Kính chào quý khách!</h1>

        <a href="/menu" class="btn">Xem Menu</a>
      </div>
      <div class="home-img">
        <!-- <img
          src="https://png.pngtree.com/png-clipart/20240318/original/pngtree-cutout-isolated-background-young-adult-asian-travel-couple-carry-luggage-for-png-image_14613197.png"
          alt="food image"> -->
        <!-- <img src="https://i.postimg.cc/gJBk5PMz/salad.png" alt="food image"> -->
      </div>
    </section>
    <sliderComponent />
    <!-- About Us Banner End -->
    <!-- From Uiverse.io by vinodjangid07 -->
    <button class="Btn" id="scroll" @click="scrollToTop">
      <svg height="1.2em" class="arrow" viewBox="0 0 512 512">
        <path
          d="M233.4 105.4c12.5-12.5 32.8-12.5 45.3 0l192 192c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L256 173.3 86.6 342.6c-12.5 12.5-32.8 12.5-45.3 0s-12.5-32.8 0-45.3l192-192z">
        </path>
      </svg>
      <p class="textScroll">Back to Top</p>
    </button>

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
            <div class="sisf-sis-section-title sisf-center-text text-center section-title wow fadeInLeft"
              data-wow-delay="0.2s">
              <h3 class="sisf-m-subtitle">Spice Odyssey</h3>
              <h2 class="sisf-m-title">Thiết Kế Sang Trọng</h2>
              <div class="sisf-m-text text-center">
                <p>
                  Với thiết kế hiện đại và sang trọng, mỗi góc nhỏ của  đều được chăm chút tỉ mỉ, mang đến cảm giác
                  thoải mái và ấm cúng.
                  Tiện Nghi Đầy Đủ:  được trang bị đầy đủ tiện nghi hiện đại, từ bể bơi riêng, phòng gym đến khu
                  vực nướng BBQ, phục vụ mọi nhu cầu của khách hàng.
                  Dịch Vụ Chăm Sóc Khách Hàng: Đội ngũ nhân viên chuyên nghiệp và thân thiện luôn sẵn sàng phục vụ, đảm
                  bảo mang đến trải nghiệm nghỉ dưỡng tuyệt vời cho bạn.
                </p>
                <RouterLink to="/reservation"><button class="btn-main mt-4">
                    Click Đặt Phòng Ngay Tại Đây
                    <div class="arrow-wrapper">
                      <div class="arrow"></div>
                    </div>
                  </button></RouterLink>
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
        <!-- <div class="row">
          <div class="col-md-2 d-flex align-items-end">
            <div class="float">
              <figure>
                <img src="https://cdn2.vietnambooking.com/wp-content/uploads/hotel_pro/hotel_335645/c0d87b6831ca64bd8e616cb59b3f5bc6.jpg" alt="Dishify" />
              </figure>
            </div>
          </div>
          <div class="col-md-8">
            <div class="text-end">
              <figure>
                <img
                  src="https://cdn2.vietnambooking.com/wp-content/uploads/hotel_pro/hotel_335645/8ab456af864681dd67ded2299798e194.jpg"
                  alt="Dishify" />
              </figure>
            </div>
          </div>
          <div class="col-md-2">
            <div class="float">
              <figure class="md-none">
                <img src="https://pix10.agoda.net/hotelImages/461265/3446277/3ade519cb63ae73bf7887d0f71543f1e.jpeg?ce=0&s=414x232&ar=16x9" alt="Dishify" />
              </figure>
            </div>
          </div>
        </div> -->
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
                <img
                  src="https://cdn3.ivivu.com/2014/01/SUPER-DELUXE2.jpg"
                  alt="Dishify" />
              </figure>
            </div>
          </div>
          <div class="col-md-6">
            <!-- Section Title Start -->
            <div class="sisf-sis-section-title section-title wow fadeInRight" data-wow-delay="0.2s">
              <h3 class="sisf-m-subtitle">Hồ Xuân Hương </h3>
              <h2 class="sisf-m-title">Địa Điểm Lý Tưởng: Nằm ở vị trí đắc địa,  dễ dàng tiếp cận các điểm tham
                quan nổi tiếng và các hoạt động giải trí thú vị.

              </h2>
              <div class="sisf-m-text pb-4">
                <p>
                  Dịch Vụ Chăm Sóc Khách Hàng: Đội ngũ nhân viên chuyên nghiệp và thân thiện luôn sẵn sàng phục vụ, đảm
                  bảo mang đến trải nghiệm nghỉ dưỡng tuyệt vời cho bạn.
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
              <img
                src="https://cf.bstatic.com/xdata/images/hotel/max1280x900/295360022.jpg?k=995816ce11821db9feb3a04bd749e466fbccb3296ef10f63321447072dced539&o=&hp=1"
                alt="Dishify" />
            </figure>
          </div>
          <div class="col-md-4">
            <figure>
              <img
                src="https://cf.bstatic.com/xdata/images/hotel/max1280x900/295358128.jpg?k=52d82ad91a7e3508fa83259b8d3d74bc01b14aa684a3c9d35dd92111be2ba07b&o=&hp=1"
                alt="Dishify" />
            </figure>
          </div>
          <div class="col-md-6">
            <!-- Section Title Start -->
            <div class="sisf-sis-section-title section-title wow fadeInRight" data-wow-delay="0.2s">
              <h3 class="sisf-m-subtitle">Trung Bình</h3>
              <h2 class="sisf-m-title text-white">Đánh giá dịch vụ</h2>
              <div class="sisf-m-text">
                <p class="text-white">
                  Hồ Xuân Hương  tự hào mang đến trải nghiệm ẩm thực đa dạng, từ các món ăn truyền thống Việt Nam đến các
                  hương vị quốc tế. Chúng tôi cam kết sử dụng nguyên liệu tươi ngon nhất và công thức nấu ăn độc đáo để
                  mang đến cho bạn những món ăn ngon miệng và đáng nhớ.
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
      <button class="btn-main mb-4">
        Đặt Bàn Ngay Tại Đây
        <div class="arrow-wrapper">
          <div class="arrow"></div>

        </div>
      </button>
      <div class="container-fluid p-0">
        <div class="row gallery-items page-gallery-box">
          <!-- Row 1 -->
          <div class="col-lg-3 col-md-6 col-12">
            <div class="photo-gallery wow fadeInUp" data-wow-delay="0.2s">
              <a
                href="https://cf.bstatic.com/xdata/images/hotel/max1280x900/295360421.jpg?k=912ab7e26fa24f68c7b960d4e5d50427b6c24a2e4b097a784bb48c00bf7c586e&o=&hp=1">
                <figure class="image-anime">
                  <img
                    src="https://cf.bstatic.com/xdata/images/hotel/max1280x900/295360421.jpg?k=912ab7e26fa24f68c7b960d4e5d50427b6c24a2e4b097a784bb48c00bf7c586e&o=&hp=1"
                    alt="Dishify" />
                </figure>
              </a>
            </div>
          </div>
          <div class="col-lg-3 col-md-6 col-12">
            <div class="photo-gallery wow fadeInUp" data-wow-delay="0.3s">
              <a
                href="https://cf.bstatic.com/xdata/images/hotel/max1280x900/295359388.jpg?k=1f0387e902c13ec8887814c55a81b08ff9588518731ad96ddc815ac1e0c763cc&o=&hp=1">
                <figure class="image-anime">
                  <img
                    src="https://cf.bstatic.com/xdata/images/hotel/max1280x900/295359388.jpg?k=1f0387e902c13ec8887814c55a81b08ff9588518731ad96ddc815ac1e0c763cc&o=&hp=1"
                    alt="Dishify" />
                </figure>
              </a>
            </div>
          </div>
          <div class="col-lg-3 col-md-6 col-12">
            <div class="photo-gallery wow fadeInUp" data-wow-delay="0.4s">
              <a
                href="https://cf.bstatic.com/xdata/images/hotel/max1280x900/295357582.jpg?k=caefb9fdf722380646ccae0044361d1b6d45adeaf3b55c895f49a50ae1570cc4&o=&hp=1">
                <figure class="image-anime">
                  <img
                    src="https://cf.bstatic.com/xdata/images/hotel/max1280x900/295357582.jpg?k=caefb9fdf722380646ccae0044361d1b6d45adeaf3b55c895f49a50ae1570cc4&o=&hp=1"
                    alt="Dishify" />
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
              <a
                href="https://cf.bstatic.com/xdata/images/hotel/max1280x900/295360818.jpg?k=b08f0eb89eb45fab0a5f8a0c8054deb0abea5c7c57893ffd1ac1a53f3d2af5ab&o=&hp=1">
                <figure class="image-anime">
                  <img
                    src="https://cf.bstatic.com/xdata/images/hotel/max1280x900/295360818.jpg?k=b08f0eb89eb45fab0a5f8a0c8054deb0abea5c7c57893ffd1ac1a53f3d2af5ab&o=&hp=1"
                    alt="Dishify" />
                </figure>
              </a>
            </div>
          </div>
          <div class="col-lg-3 col-md-6 col-12">
            <div class="photo-gallery wow fadeInUp" data-wow-delay="0.4s">
              <a
                href="https://cf.bstatic.com/xdata/images/hotel/max1280x900/295376354.jpg?k=ec2887bc1c3d422bb59acf15f5e900adbbc76ab214b90e5753ff1d6e44661be4&o=&hp=1">
                <figure class="image-anime">
                  <img
                    src="https://cf.bstatic.com/xdata/images/hotel/max1280x900/295376354.jpg?k=ec2887bc1c3d422bb59acf15f5e900adbbc76ab214b90e5753ff1d6e44661be4&o=&hp=1"
                    alt="Dishify" />
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
              <h3 class="sisf-m-subtitle">Giám Đốc Hồ Xuân Hương </h3>
              <h2 class="sisf-m-title">Giám Đốc Hồ Xuân Hương </h2>
            </div>
            <!-- Section Title End -->
            <div class="carousel">
              <div class="carousel-inner">
                <div class="carousel-item" v-for="(testimonial, index) in testimonials" :key="index"
                  :class="{ active: currentTestimonial === index }">
                  <div class="sisf-e-media-image mb-4">
                    <img :src="testimonial.image" alt="Dishify" />
                  </div>
                  <div class="sisf-e-author mb-4">
                    <h5 class="sisf-e-author-name mb-4">{{ testimonial.name }}</h5>
                    <span class="sisf-e-author-job">{{ testimonial.job }}</span>
                  </div>
                  <p class="sisf-e-text">{{ testimonial.text }}</p>
                 
                </div>
              </div>


            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Testimonial Section End -->
    <Footer></Footer>


  </div>
</template>
<script setup>
import sliderComponent from './sliderComponent.vue';
import Footer from './Footer.vue';
</script>
<script>
import { scrollToTop } from '../assets/script'; // Đường dẫn đến tệp JavaScript
import { RouterLink } from 'vue-router';
export default {
  name: 'AboutComponent',
  data() {
    return {
      skills: [
        { country: 'Sang Trọng', percent: '92%' },
        { country: 'Ấm Cúng', percent: '84%' },
        { country: 'Tiện nghi', percent: '98%' },
        { country: 'Mát mẻ', percent: '88%' },
        { country: 'Thoái mái', percent: '88%' },
      ],
      testimonials: [
        {
          image: 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTKLYohDbTqgPOkuIlFi3YY8ZDqHAc6fqYQEg&s',
          name: 'Tên Giám Đốc',
          job: 'Main-Boss',
          text: 'Đại diện pháp lý của Hồ Xuân Hương , chúng tôi cam kết mang đến dịch vụ tốt nhất cho khách hàng. Với đội ngũ nhân viên chuyên nghiệp và tận tâm, chúng tôi luôn sẵn sàng đáp ứng mọi nhu cầu của bạn.',
        }
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
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  scroll-padding-top: 2rem;
  list-style: none;
  text-decoration: none;
  scroll-behavior: smooth;
  font-family: 'Poppins', sans-serif;
}


:root {
  --main-color: #16B978;
  --second-color: #081B54;
}

section {
  padding: 50px 10%;
}

img {
  width: 100%;
}

*::selection {
  color: #FFF;
  background: var(--main-color);
}

header {
  position: fixed;
  width: 100%;
  top: 0;
  right: 0;
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 15px 10%;
  transition: .2s;
}

header.active {
  box-shadow: 0 0 4px rgb(14 55 54 / 15%);
}

header.active .logo,
header.active .navbar a {
  color: #FFF;
}

.logo {
  display: flex;
  align-items: center;
  font-size: 1.1rem;
  font-weight: 600;
  color: #000000;
}

.logo img {
  width: 35px;
  margin-right: 10px;
}

.navbar {
  display: flex;
}

.navbar a {
  font-size: 1rem;
  padding: 10px 20px;
  color: #000000;
  font-weight: 500;
}

.navbar a:hover {
  background: var(--main-color);
  color: #FFF;
  border-radius: 4px;
}

#menu-icon {
  font-size: 24px;
  cursor: pointer;
  z-index: 10000;
  display: none;
}

/* Home Section */
.home {
  position: relative;
  /* Để lớp overlay có thể được định vị tương đối */
  width: 100%;
  min-height: 100vh;
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 1rem;
  z-index: 1;
}

.home::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: url('https://a25hotel.com/files/images/screenshot_1664443543.png') no-repeat center;
  background-size: cover;
  opacity: 0.8;
  /* Độ mờ của hình nền */
  z-index: -1;
  /* Đưa lớp overlay ra phía sau */
}

.home-text {
  flex: 1 1 17rem;
  z-index: 2;
  /* Đảm bảo văn bản nằm trên lớp overlay */
}

.home-text span {
  font-size: 1rem;
  font-weight: 400;
  text-transform: uppercase;
  text-align: center;
  color: var(--main-color);
  animation: fade-in 1s ease-in-out;
  /* Hiệu ứng mờ vào */
}

.home-text h1 {
  font-size: 4rem;
  color: rgb(30, 55, 88);
  animation: bounce 3s infinite;
  /* Hiệu ứng nhảy */
}

.home-text p {
  margin: 0.5rem 0 1.4rem;
  animation: fade-in 2.5s ease-in-out;
  /* Hiệu ứng mờ vào */
}

/* Hiệu ứng nhảy */
@keyframes bounce {

  0%,
  50%,
  100% {
    transform: translateY(0);
  }

  40% {
    transform: translateY(-10px);
  }

  60% {
    transform: translateY(-5px);
  }
}

/* Hiệu ứng mờ vào */
@keyframes fade-in {
  from {
    opacity: 0;
  }

  to {
    opacity: 1;
  }
}

.home-img {
  flex: 1 1 17rem
}

@media (max-width: 991px) {
  .home-text {
    text-align: center;
  }
}

.btn {
  padding: 10px 20px;
  border: 2px solid blue;
  border-radius: 40px;
  color: blue;
  font-weight: 500;
  transition: .3s;
}

.btn:hover {
  letter-spacing: 2px;
  color: #FFF;
  background: blue;
}

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
  background: wheat;
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
  .float {
    display: none;
  }

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
    height: 150px;
    /* Smaller height on mobile */
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
  background: rgb(125, 201, 218);
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
    height: 150px;
    /* Smaller height on mobile */
  }
}
</style>