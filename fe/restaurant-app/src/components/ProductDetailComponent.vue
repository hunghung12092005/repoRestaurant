<template>
  <div class="shop-details-area mt-5">
    <div class="container">
      <!-- Food Details Section -->
      <div class="row">
        <!-- Image -->
        <div class="col-lg-6 col-md-12">
          <div class="shop-details-thumb wow custom-anim-left" data-wow-delay="0.2s" style="position: relative;">
            <img :src="foodItem.image" :alt="foodItem.title" style="width: 100%;">
            <img
              src="https://media.istockphoto.com/id/1500686343/vector/rounded-label-with-flame-icon-and-text-hot-offer.jpg?s=612x612&w=0&k=20&c=UkZwRYbtum2RV3KYOiuKbi1xHyT-NdtmjhqwiuuRSHY="
              alt="Second Image" class="overlay-image"
              style=" width: 40%; height: auto; position: absolute; bottom: 10px; left: 10px;">
            <img
              src="https://marketplace.canva.com/EAGKDhXEhoY/1/0/1600w/canva-brown-and-white-simple-modern-professional-catering-logo-Dvz9NG3gqk0.jpg"
              alt="Second Image" class="overlay-image"
              style=" width: 25%; height: auto; position: absolute; top: 1px; right: 10px;">
          </div>
        </div>
        <!-- Details -->
        <div class="col-lg-6 col-md-12 wow custom-anim-left" data-wow-delay="0.3s">
          <div class="shop-details">
            <div class="shop-details-title">
              <h2>{{ foodItem.title }}</h2>
            </div>
          </div>
          <div class="shop-details-review">
            <i v-for="n in 5" :key="n" :class="n <= foodItem.rating ? 'bi bi-star-fill' : 'bi bi-star'"></i>
            <span>({{ foodItem.reviews }} Customer Review{{ foodItem.reviews !== 1 ? 's' : '' }})</span>
          </div>
          <div class="shop-details-price">
            <h4>${{ foodItem.price.toFixed(2) }} <span>${{ foodItem.originalPrice.toFixed(2) }}</span></h4>
          </div>
          <div class="shop-catagori-tag">
            <ul>
              <li>Số lượt mua: <span>{{ foodItem.sku }}</span></li>
              <li>Danh mục: <span>{{ foodItem.category }}</span></li>
              <li>Thẻ: <span>{{ foodItem.tag }}</span></li>
            </ul>
          </div>
          <div class="shop-details-disc">
            <p>{{ foodItem.description }}</p>
          </div>
          <div class="row">
            <!-- Size Selection -->
            <div class="col-lg-6 col-md-6">
              <div class="widget-check-box">
                <div class="widget-categories-title">
                  <h4>Select Size</h4>
                </div>
                <label v-for="size in foodItem.sizes" :key="size" class="widget-check">
                  {{ size }}
                  <input type="checkbox" v-model="selectedSizes" :value="size">
                  <span class="checkmark"></span>
                </label>
              </div>
            </div>
            <!-- Crust Selection -->
            <!-- <div class="col-lg-6 col-md-6">
              <div class="widget-check-box">
                <div class="widget-categories-title">
                  <h4>Select Crust</h4>
                </div>
                <label v-for="crust in foodItem.crusts" :key="crust" class="widget-check">
                  {{ crust }}
                  <input type="checkbox" v-model="selectedCrusts" :value="crust">
                  <span class="checkmark"></span>
                </label>
              </div>
            </div> -->
          </div>
          <!-- Quantity Input -->
          <input type="range" value="2" min="1" max="10">
          <!-- Add to Cart Button -->
          
          <router-link class="m-2" to="/buynow"><button class="btn btn-success m-2">Đặt Nhận Tại Quán<div class="arrow-wrapper">
              
            </div></button></router-link>
          <router-link to="/ghn"><button class="btn-main">Đặt Ship<div class="arrow-wrapper">
              <div class="arrow"></div>
            </div></button></router-link>

        </div>
      </div>
      <!-- Tabs Section -->
      <div class="row">
        <div class="col-lg-12">
          <div class="appoinment-tab wow custom-anim-left" data-wow-delay="0.4s">
            <div class="tab">
              <ul class="tabs active">
                <li :class="{ current: activeTab === 'additional' }">
                  <a href="#" @click.prevent="activeTab = 'additional'">Additional Information</a>
                </li>
                <li :class="{ current: activeTab === 'description' }">
                  <a href="#" @click.prevent="activeTab = 'description'">Description</a>
                </li>

              </ul>
              <div class="tab_content">
                <!-- Description Tab -->
                <div class="tabs_item" v-show="activeTab === 'description'">
                  <div class="post-comment-description">
                    <p>{{ foodItem.fullDescription }}</p>
                  </div>
                </div>
                <!-- Additional Information Tab -->
                <div class="tabs_item" v-show="activeTab === 'additional'">
                  <table class="tab-items-table">
                    <tbody>
                      <tr v-for="(info, key, index) in foodItem.additionalInfo" :key="key"
                        :class="{ 'tabs-bg': index % 2 !== 0 }">
                        <td class="table-title">{{ key }}</td>
                        <td class="table-text">{{ info }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
                <!-- Reviews Tab -->

              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue';
const foodItem = ref([]);
export default {
  name: 'ProductDetailComponent',
  props: {
    foodItem: {
      type: Object,
      required: true,
      default: () => ({
        image: 'https://bizweb.dktcdn.net/100/350/142/products/0025-349-1-1-1-1728274747587.jpg?v=1728369802727',
        title: 'Signature BBQ Beef',
        rating: 5,
        reviews: 1,
        price: 20.00,
        originalPrice: 21.00,
        sku: '134',
        category: 'Pizza',
        tag: 'Beverages',
        description: 'Gorgonzola, mozzarella, taleggio Red onions, capers, olives Porem ipsum dolor sit amet consectetur adipiscing elit. Duis arcu purus rhoncus fringilla vestibulum veluf, dignissim vel ante. Nulla faciNullama urna sit amet tellus.',
        fullDescription: 'Porem ipsum dolor sit amet, consectetur adipiscing elit. In ut ullamcorper leo, eget euismod orci. Cum sociis natoque penatibus et magnis dis parturient montes adipiscing elit nascetur ridiculus mus. Vestibulum ultricies aliquam convallis Maecenas ut tellus mi. Proin tincidunt, lectusu volutpat mattis, ante metus lacinia tellus, vitae condime Cum sociis ntum nulla enim bibendum nibh. Praesent turpis risus, interdumnec venenpretium sit amet purus et malesuada fames ac ante ipsum primis in faucibus Maecenas tincidunt, malesuada fames ac ante ipsum primis in faucibus Maecenas Proin tincidunt.',
        sizes: ['Large', 'Medium'],
        crusts: ['Double Crust', 'Original Crust', 'Thick Crust', 'Thin Crust'],
        additionalInfo: {
          'Stand Up': 'KKAL',
          'Colors': 'Black, Blue, Purple',
          'Weight': '35 KG',
          'Wheels': '12” air/ Wide Truck Sleek Trade',
          'Back Height': '21.5”',
          'Address': '18F Keangnam Landmark 72, PhamHung Str Hanoi, FL 100000',
          'Rating': '4.40 rating from 5 reviews'
        },
        reviewsData: [
          {
            id: 1,
            author: 'David Alexon',
            date: 'October 01, 2022',
            rating: 4,
            comment: 'Dramatically reinvent adaptive bandwidth vis reliable infrastructures to the progressively iterate distributed interfaces.',
            image: 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e',
            link: 'blog-details.html'
          }
        ]
      })
    }
  },
  data() {
    return {
      activeTab: 'additional',
      selectedSizes: ['Large'],
      selectedCrusts: ['Double Crust'],
      quantity: 1,
      newReview: {
        name: '',
        email: '',
        comment: '',
        rating: 0
      }
    };
  },
  mounted() {
    if (typeof WOW !== 'undefined') {
      new WOW().init();
    }
  }
};
</script>

<style scoped>
.shop-details-area {
  padding: 60px 0;
  position: relative;
}

.shop-details-thumb img {
  width: 80%;
  margin: 0 auto;
  border-radius: 10px;
}

@media (max-width: 767px) {
  .shop-details-thumb img {
    width: 100%;
  }
}

.shop-details-title h2 {
  font-size: 28px;
  font-weight: 700;
  color: #333;
  margin-bottom: 15px;
}

.shop-details-review {
  margin-bottom: 15px;
}

.shop-details-review i {
  color: #ff6f61;
  font-size: 14px;
  margin-right: 5px;
}

.shop-details-review span {
  font-size: 14px;
  color: #666;
}

.shop-details-price h4 {
  font-size: 24px;
  font-weight: 600;
  color: #ff6f61;
  margin-bottom: 15px;
}

.shop-details-price h4 span {
  font-size: 18px;
  color: #999;
  text-decoration: line-through;
  margin-left: 10px;
}

.shop-catagori-tag ul {
  list-style: none;
  padding: 0;
  margin-bottom: 15px;
}

.shop-catagori-tag ul li {
  display: inline-block;
  font-size: 14px;
  color: #666;
  margin-right: 20px;
}

.shop-catagori-tag ul li span {
  color: #333;
  font-weight: 500;
}

.shop-details-disc p {
  font-size: 14px;
  color: #666;
  line-height: 1.8;
  margin-bottom: 20px;
}

.widget-check-box {
  margin-bottom: 20px;
}

.widget-categories-title h4 {
  font-size: 18px;
  font-weight: 600;
  color: #333;
  margin-bottom: 10px;
}

.widget-check {
  position: relative;
  display: block;
  padding-left: 25px;
  margin-bottom: 10px;
  cursor: pointer;
  font-size: 14px;
  color: #666;
}

.widget-check input {
  position: absolute;
  opacity: 0;
  cursor: pointer;
}

.widget-check .checkmark {
  position: absolute;
  top: 2px;
  left: 0;
  height: 16px;
  width: 16px;
  background-color: #fff;
  border: 1px solid #ccc;
  border-radius: 3px;
}

.widget-check input:checked~.checkmark {
  background-color: #ff6f61;
  border-color: #ff6f61;
}

.widget-check input:checked~.checkmark:after {
  content: '';
  position: absolute;
  display: block;
  width: 5px;
  height: 10px;
  border: solid #fff;
  border-width: 0 2px 2px 0;
  transform: rotate(45deg);
  top: 2px;
  left: 5px;
}




.appoinment-tab .tabs {
  list-style: none;
  padding: 0;
  margin-bottom: 20px;
  border-bottom: 1px solid #eee;
}

.appoinment-tab .tabs li {
  display: inline-block;
  margin-right: 30px;
}

.appoinment-tab .tabs li a {
  display: block;
  font-size: 16px;
  color: #666;
  padding: 10px 0;
  text-decoration: none;
}

.appoinment-tab .tabs li.current a {
  color: #ff6f61;
  border-bottom: 2px solid #ff6f61;
}

.tab-items-table {
  width: 100%;
  border-collapse: collapse;
}

.tab-items-table tr {
  border-bottom: 1px solid #eee;
}

.tab-items-table .tabs-bg {
  background-color: #f9f9f9;
}

.tab-items-table td {
  padding: 12px 15px;
  font-size: 14px;
}

.tab-items-table .table-title {
  font-weight: 500;
  color: #333;
  width: 30%;
}

.tab-items-table .table-text {
  color: #666;
}

.post-comment {
  display: flex;
  margin-bottom: 30px;
}

.post-comment-thumb img {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  margin-right: 15px;
}

.post-content {
  flex: 1;
}

.comment-icon-list {
  list-style: none;
  padding: 0;
  margin-bottom: 10px;
}

.comment-icon-list li {
  display: inline-block;
  color: #ff6f61;
  font-size: 14px;
  margin-right: 5px;
}

.post-title {
  font-size: 16px;
  font-weight: 500;
  color: #333;
  margin-bottom: 10px;
}

.post-title .left-date {
  font-size: 14px;
  color: #999;
  float: right;
}

.posts-reply {
  font-size: 14px;
  color: #666;
  line-height: 1.8;
  margin-bottom: 10px;
}

.rights-reply {
  font-size: 14px;
  color: #ff6f61;
  cursor: pointer;
}

.rights-reply i {
  margin-right: 5px;
}

.product-details-respond {
  margin-top: 30px;
}

.contact-form-box2 .sidebar-title h2 {
  font-size: 24px;
  font-weight: 700;
  color: #333;
  margin-bottom: 15px;
}

.sidebar-description p {
  font-size: 14px;
  color: #666;
  margin-bottom: 15px;
}

.sidebar-rating-list {
  margin-bottom: 20px;
}

.sidebar-rating-list .sidebar-text {
  font-size: 14px;
  color: #333;
  margin-bottom: 5px;
}

.sidebar-rating-list ul {
  list-style: none;
  padding: 0;
}

.sidebar-rating-list ul li {
  display: inline-block;
  font-size: 14px;
  color: #ff6f61;
  cursor: pointer;
  margin-right: 5px;
}

.contact-form-box2 form .form-title {
  font-size: 14px;
  font-weight: 500;
  color: #333;
  margin-bottom: 5px;
}

.contact-form-box2 .form-box input,
.contact-form-box2 .form-box textarea {
  width: 100%;
  border: 1px solid #eee;
  border-radius: 5px;
  padding: 10px;
  font-size: 14px;
  color: #666;
}

.contact-form-box2 .form-box textarea {
  height: 120px;
  resize: vertical;
}

.contact-form-box2 .contact-form button {
  background-color: #ff6f61;
  color: #fff;
  font-size: 16px;
  font-weight: 500;
  padding: 12px 30px;
  border: none;
  border-radius: 5px;
  text-transform: uppercase;
  cursor: pointer;
  transition: background-color 0.3s;
}

.contact-form-box2 .contact-form button:hover {
  background-color: #e65a50;
}

.shop-detsils-shape1,
.shop-detsils-shape2 {
  position: absolute;
  z-index: -1;
}

.shop-detsils-shape1 {
  top: 10%;
  left: 5%;
}

.shop-detsils-shape2 {
  bottom: 15%;
  right: 5%;
}

.bounce-animate2 {
  animation: bounce2 3s infinite;
}

.bounce-animate3 {
  animation: bounce3 4s infinite;
}

@keyframes bounce2 {

  0%,
  100% {
    transform: translateY(0);
  }

  50% {
    transform: translateY(-20px);
  }
}

@keyframes bounce3 {

  0%,
  100% {
    transform: translateY(0);
  }

  50% {
    transform: translateY(-30px);
  }
}

/* Responsive Styles */
@media (max-width: 991px) {
  .shop-details-area {
    padding: 40px 0;
  }

  .shop-details-title h2 {
    font-size: 24px;
  }

  .shop-details-price h4 {
    font-size: 20px;
  }

  .appoinment-tab .tabs li {
    margin-right: 15px;
  }
}

@media (max-width: 767px) {
  .shop-details-thumb {
    margin-bottom: 30px;
  }

  .shop-details-title h2 {
    font-size: 20px;
  }

  .shop-details-price h4 {
    font-size: 18px;
  }

  .appoinment-tab .tabs li {
    display: block;
    margin-bottom: 10px;
  }

  .contact-form-box2 .row>div {
    margin-bottom: 15px;
  }
}
</style>
