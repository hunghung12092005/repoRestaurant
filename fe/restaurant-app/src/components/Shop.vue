<template>
  <div>
    <!-- Page Banner -->
    <section class="page_banner" style="background: url(/assets/images/page_banner_bg.jpg);">
      <div class="page_banner_overlay">
        <div class="container">
          <div class="row">
            <div class="col-12">
              <div class="page_banner_text text-center">
                <h1>Shop</h1>
                <ul class="d-flex flex-wrap align-items-center justify-content-center gap-3">
                  <li><a href="#" class="text-white"><i class="bi bi-house-fill me-1"></i> Home</a></li>
                  <li><a href="#" class="text-white">Shop</a></li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Shop Page -->
    <section class="shop_page mt-5 mb-5">
      <div class="container">
        <div class="row">
          <!-- Sidebar -->
          <div class="col-xxl-2 col-lg-4 col-xl-3">
            <div id="sticky_sidebar">
              <div class="shop_filter_btn d-lg-none" @click="toggleFilter">Filter</div>
              <div class="shop_filter_area" :class="{ 'd-none': !filterVisible, 'd-lg-block': true }">
                <div class="sidebar_range">
                  <h3>Price Range</h3>
                  <div class="range_slider">
                    <input type="range" v-model.number="priceRange[0]" :min="0" :max="100" step="1" @input="updatePriceRange">
                    <input type="range" v-model.number="priceRange[1]" :min="0" :max="100" step="1" @input="updatePriceRange">
                    <div>Price: ${{ priceRange[0] }} - ${{ priceRange[1] }}</div>
                  </div>
                </div>
                <div class="sidebar_status">
                  <h3>Product Status</h3>
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" v-model="productStatus.onSale" id="flexCheckDefault" @change="updateProductStatus">
                    <label class="form-check-label" for="flexCheckDefault">On sale</label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" v-model="productStatus.inStock" id="flexCheckChecked" @change="updateProductStatus">
                    <label class="form-check-label" for="flexCheckChecked">In Stock</label>
                  </div>
                </div>
                <div class="sidebar_related_product">
                  <h3>Top Rated Products</h3>
                  <ul>
                    <li v-for="relatedProduct in relatedProducts" :key="relatedProduct.id" class="d-flex align-items-center">
                      <a :href="relatedProduct.link" class="img">
                        <img :src="relatedProduct.image" :alt="relatedProduct.title" class="img-fluid">
                      </a>
                      <div class="text ms-3">
                        <p class="rating">
                          <i v-for="n in 5" :key="n" :class="n <= relatedProduct.rating ? 'bi bi-star-fill' : 'bi bi-star'" class="text-warning"></i>
                          <span>({{ relatedProduct.reviews }})</span>
                        </p>
                        <a class="title" :href="relatedProduct.link">{{ relatedProduct.title }}</a>
                        <p class="price">${{ relatedProduct.price.toFixed(2) }}</p>
                      </div>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>

          <!-- Main Content -->
          <div class="col-xxl-10 col-lg-8 col-xl-9">
            <!-- Product Page Top -->
            <div class="product_page_top">
              <div class="row">
                <div class="col-4 col-xl-6 col-md-6">
                  <div class="product_page_top_button d-flex align-items-center gap-3">
                    <ul class="nav nav-tabs border-0" id="nav-tab" role="tablist">
                      <li class="nav-item">
                        <button class="nav-link" :class="{ active: viewMode === 'grid' }" @click="viewMode = 'grid'" id="nav-home-tab" data-bs-toggle="tab" data-bs-target="#nav-home" type="button" role="tab" aria-controls="nav-home" aria-selected="true">
                          <i class="bi bi-grid-fill"></i>
                        </button>
                      </li>
                      <li class="nav-item">
                        <button class="nav-link" :class="{ active: viewMode === 'list' }" @click="viewMode = 'list'" id="nav-profile-tab" data-bs-toggle="tab" data-bs-target="#nav-profile" type="button" role="tab" aria-controls="nav-profile" aria-selected="false">
                          <i class="bi bi-list-ul"></i>
                        </button>
                      </li>
                    </ul>
                    <p>Showing 1–14 of 26 results</p>
                  </div>
                </div>
                <div class="col-8 col-xl-6 col-md-6">
                  <ul class="product_page_sorting d-flex gap-2 justify-content-end">
                    <li>
                      <select v-model="sortOption" class="form-select">
                        <option>Default Sorting</option>
                        <option>Low to High</option>
                        <option>High to Low</option>
                        <option>New Added</option>
                        <option>On Sale</option>
                      </select>
                    </li>
                    <li>
                      <select v-model="itemsPerPage" class="form-select">
                        <option>Show: 12</option>
                        <option>Show: 16</option>
                        <option>Show: 20</option>
                        <option>Show: 24</option>
                        <option>Show: 26</option>
                      </select>
                    </li>
                  </ul>
                </div>
              </div>
            </div>

            <!-- Product Display -->
            <div class="tab-content" id="nav-tabContent">
              <!-- Grid View -->
              <div class="tab-pane fade" :class="{ 'show active': viewMode === 'grid' }" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab" tabindex="0">
                <div class="row">
                  <div class="col-xxl-3 col-6 col-md-4 col-lg-6 col-xl-4" v-for="product in filteredProducts" :key="product.id">
                    <div class="product_item_2 product_item">
                      <div class="product_img">
                        <img :src="product.image" :alt="product.title" class="img-fluid w-100">
                        <ul class="discount_list">
                          <li v-if="product.isNew" class="new">new</li>
                        </ul>
                        <ul class="btn_list">
                          <li><a href="#"><img src="https://html.narzotech.com/zenis/assets/images/compare_icon_white.svg" alt="Compare" class="img-fluid"></a></li>
                          <li><a href="#"><img src="https://html.narzotech.com/zenis/assets/images/love_icon_white.svg" alt="Love" class="img-fluid"></a></li>
                          <li><a href="#"><img src="https://html.narzotech.com/zenis/assets/images/cart_icon_white.svg" alt="Cart" class="img-fluid"></a></li>
                        </ul>
                      </div>
                      <div class="product_text">
                        <a class="title" :href="product.link">{{ product.title }}</a>
                        <p class="price">${{ product.price.toFixed(2) }}</p>
                        <p class="rating">
                          <i v-for="n in 5" :key="n" :class="n <= product.rating ? 'bi bi-star-fill' : 'bi bi-star'" class="text-warning"></i>
                          <span>({{ product.reviews }} reviews)</span>
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="pagination_area">
                    <nav aria-label="Pagination">
                      <ul class="pagination justify-content-start mt-5">
                        <li class="page-item">
                          <a class="page-link" href="#" @click.prevent="changePage(currentPage - 1)">
                            <i class="bi bi-arrow-left"></i>
                          </a>
                        </li>
                        <li class="page-item" v-for="page in totalPages" :key="page">
                          <a class="page-link" :class="{ active: currentPage === page }" href="#" @click.prevent="changePage(page)">{{ page }}</a>
                        </li>
                        <li class="page-item">
                          <a class="page-link" href="#" @click.prevent="changePage(currentPage + 1)">
                            <i class="bi bi-arrow-right"></i>
                          </a>
                        </li>
                      </ul>
                    </nav>
                  </div>
                </div>
              </div>

              <!-- List View -->
              <div class="tab-pane fade" :class="{ 'show active': viewMode === 'list' }" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab" tabindex="0">
                <div class="row">
                  <div class="col-6 col-xxl-10 col-sm-12" v-for="product in filteredProducts" :key="product.id">
                    <div class="product_list_item product_item_2 product_item">
                      <div class="row align-items-center">
                        <div class="col-md-5 col-sm-6 col-xxl-4">
                          <div class="product_img">
                            <img :src="product.image" :alt="product.title" class="img-fluid w-100">
                            <ul class="discount_list">
                              <li v-if="product.isNew" class="new">new</li>
                              <li v-if="product.discount" class="discount"><b>-</b>{{ product.discount }}%</li>
                            </ul>
                            <ul class="btn_list">
                              <li><a href="#"><img src="https://html.narzotech.com/zenis/assets/images/compare_icon_white.svg" alt="Compare" class="img-fluid"></a></li>
                              <li><a href="#"><img src="https://html.narzotech.com/zenis/assets/images/love_icon_white.svg" alt="Love" class="img-fluid"></a></li>
                            </ul>
                          </div>
                        </div>
                        <div class="col-md-7 col-sm-6 col-xxl-8">
                          <div class="product_text">
                            <a class="title" :href="product.link">{{ product.title }}</a>
                            <p class="rating">
                              <i v-for="n in 5" :key="n" :class="n <= product.rating ? 'bi bi-star-fill' : 'bi bi-star'" class="text-warning"></i>
                              <span>({{ product.reviews }} reviews)</span>
                            </p>
                            <p class="price">${{ product.price.toFixed(2) }} <del v-if="product.originalPrice">${{ product.originalPrice.toFixed(2) }}</del></p>
                            <p class="short_description">{{ product.description }}</p>
                            <a class="common_btn" :href="product.link">add to cart <i class="bi bi-arrow-right"></i></a>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="pagination_area">
                    <nav aria-label="Pagination">
                      <ul class="pagination justify-content-start mt-5">
                        <li class="page-item">
                          <a class="page-link" href="#" @click.prevent="changePage(currentPage - 1)">
                            <i class="bi bi-arrow-left"></i>
                          </a>
                        </li>
                        <li class="page-item" v-for="page in totalPages" :key="page">
                          <a class="page-link" :class="{ active: currentPage === page }" href="#" @click.prevent="changePage(page)">{{ page }}</a>
                        </li>
                        <li class="page-item">
                          <a class="page-link" href="#" @click.prevent="changePage(currentPage + 1)">
                            <i class="bi bi-arrow-right"></i>
                          </a>
                        </li>
                      </ul>
                    </nav>
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

<script>
export default {
  data() {
    return {
      viewMode: 'grid',
      sortOption: 'Default Sorting',
      itemsPerPage: 'Show: 12',
      currentPage: 1,
      priceRange: [0, 100],
      productStatus: {
        onSale: false,
        inStock: false
      },
      filterVisible: false,
      products: [
        {
          id: 1,
          title: "Kid's dresses for summer",
          price: 70.00,
          image: "https://html.narzotech.com/zenis/assets/images/product_23.png",
          rating: 4,
          reviews: 44,
          isNew: true,
          link: "shop_details.html"
        },
        {
          id: 2,
          title: "Denim Jeans Pants For Men",
          price: 50.00,
          originalPrice: 60.00,
          image: "https://html.narzotech.com/zenis/assets/images/product_17.png",
          rating: 5,
          reviews: 27,
          isNew: true,
          discount: 75,
          description: "Lorem ipsum dolor sit amet consectetur, adipisicing elit. Exercitationem inventore libero accusantium ex ipsam, provident voluptas facere nemo, quas assumenda reprehenderit nihil ratione quaerat ad.",
          link: "shop_details.html"
        }
        // Add more products as needed
      ],
      relatedProducts: [
        {
          id: 1,
          title: "Kid's Western Party Dress",
          price: 59.00,
          image: "https://html.narzotech.com/zenis/assets/images/product_18.png",
          rating: 3.5,
          reviews: 29,
          link: "shop_details.html"
        },
        {
          id: 2,
          title: "Kid's dresses for summer",
          price: 54.00,
          image: "https://html.narzotech.com/zenis/assets/images/product_23.png",
          rating: 3.5,
          reviews: 12,
          link: "shop_details.html"
        },
        {
          id: 3,
          title: "Sharee Petticoat For Women",
          price: 28.00,
          image: "https://html.narzotech.com/zenis/assets/images/product_13.png",
          rating: 3.5,
          reviews: 9,
          link: "shop_details.html"
        },
        {
          id: 4,
          title: "Denim 2 Quarter Pant",
          price: 54.00,
          image: "https://html.narzotech.com/zenis/assets/images/product_7.png",
          rating: 3.5,
          reviews: 35,
          link: "shop_details.html"
        }
      ]
    };
  },
  computed: {
    filteredProducts() {
      let filtered = [...this.products];
      // Apply sorting
      if (this.sortOption === 'Low to High') {
        filtered.sort((a, b) => a.price - b.price);
      } else if (this.sortOption === 'High to Low') {
        filtered.sort((a, b) => b.price - b.price);
      }
      // Apply price range filter
      filtered = filtered.filter(product => product.price >= this.priceRange[0] && product.price <= this.priceRange[1]);
      // Apply product status filter
      if (this.productStatus.onSale) {
        filtered = filtered.filter(product => product.discount);
      }
      if (this.productStatus.inStock) {
        filtered = filtered.filter(product => true); // Assume all products are in stock for demo
      }
      // Apply pagination
      const items = parseInt(this.itemsPerPage.split(': ')[1]);
      const start = (this.currentPage - 1) * items;
      return filtered.slice(start, start + items);
    },
    totalPages() {
      const items = parseInt(this.itemsPerPage.split(': ')[1]);
      return Math.ceil(this.products.length / items);
    }
  },
  methods: {
    changePage(page) {
      if (page >= 1 && page <= this.totalPages) {
        this.currentPage = page;
      }
    },
    toggleFilter() {
      this.filterVisible = !this.filterVisible;
    },
    updatePriceRange() {
      this.priceRange = [this.priceRange[0], this.priceRange[1]];
    },
    updateProductStatus() {
      this.productStatus = { ...this.productStatus };
    }
  }
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Jost:ital,wght@0,100..900;1,100..900&family=Oregano:ital@0;1&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap');

:root {
  --themeColoeOne: #0A69D8;
  --themeColorTwo: #ffa500;
  --colorGreen: #05A845;
  --colorOlive: #AB9774;
  --colorYellow: #FFD43A;
  --colorRed: #DB4437;
  --paraColor: #7d7b7b;
  --colorBlack: #333333;
  --colorWhite: #ffffff;
  --lightBg: #edf5ff;
  --lightBg2: #F5F5F5;
  --headingFont: "Jost", serif;
  --bodyFont: "Roboto", serif;
  --cursiveFont: "Oregano", serif;
  --ratingColor: #F9A61C;
  --boxShadow: rgb(149 157 165 / 16%) 0px 3px 22px;
}

/* Page Banner Styles */
.page_banner {
  background-position: center;
  background-repeat: no-repeat;
  background-size: cover;
}

.page_banner_overlay {
  background: rgba(7, 28, 31, 0.38);
  padding: 50px 0;
}

.page_banner_text h1 {
  color: var(--colorWhite);
  font-size: 54px;
  font-weight: 500;
  margin-bottom: 5px;
  text-transform: capitalize;
}

.page_banner_text ul li a {
  color: var(--colorWhite);
  font-size: 16px;
  font-weight: 400;
  line-height: 24px;
  position: relative;
  opacity: 0.9;
  transition: all linear 0.3s;
}

.page_banner_text ul li a:hover {
  opacity: 1;
}

.page_banner_text ul li a::after {
  content: "\f054";
  font-family: 'Bootstrap Icons';
  font-weight: 600;
  font-size: 10px;
  color: var(--colorWhite);
  position: absolute;
  top: 0;
  right: -18px;
}

.page_banner_text ul li:last-child a::after {
  display: none;
}

/* Shop Page Styles */
.shop_page {
  font-family: var(--bodyFont);
}

/* Sidebar Styles */
.shop_filter_area h3 {
  font-size: 18px;
  font-weight: 500;
  margin-bottom: 15px;
}

.sidebar_status .form-check {
  margin-bottom: 5px;
}

.sidebar_status .form-check-input {
  width: 16px;
  height: 16px;
  border-radius: 3px;
}

.sidebar_status .form-check-label {
  font-size: 16px;
  font-weight: 400;
  text-transform: capitalize;
  color: var(--paraColor);
  font-family: var(--headingFont);
}

.sidebar_related_product {
  border-top: 1px solid #ddd;
  padding-top: 30px;
  margin-top: 30px;
}

.sidebar_related_product .img {
  width: 65px;
  background: var(--lightBg2);
  border-radius: 6px;
  overflow: hidden;
}

.sidebar_related_product .text {
  width: 73%;
}

.sidebar_related_product .text .rating {
  color: var(--ratingColor);
  font-size: 11px;
}

.sidebar_related_product .text .rating span {
  font-size: 12px;
  margin-left: 3px;
}

.sidebar_related_product .text .title {
  font-size: 14px;
  margin-top: 3px;
  margin-bottom: 1px;
}

.sidebar_related_product .text .price {
  font-size: 14px;
}

.shop_filter_btn {
  background: var(--themeColorTwo);
  padding: 10px 20px;
  margin-bottom: 25px;
  color: var(--colorWhite);
  text-transform: capitalize;
  font-size: 18px;
  font-weight: 600;
  cursor: pointer;
  border-radius: 8px;
  position: relative;
}

.shop_filter_btn::after,
.shop_filter_btn::before {
  content: "";
  position: absolute;
  background: var(--colorWhite);
  top: 50%;
  right: 20px;
  transform: translateY(-50%);
  transition: all linear 0.3s;
}

.shop_filter_btn::after {
  width: 15px;
  height: 3px;
}

.shop_filter_btn::before {
  width: 3px;
  height: 15px;
  right: 26px;
}

.shop_filter_btn.show::before {
  height: 0;
}

.range_slider {
  position: relative;
}

.range_slider input[type="range"] {
  width: 100%;
  margin: 10px 0;
}

/* Product Page Top Styles */
.product_page_top_button .nav-link {
  width: 40px;
  height: 40px;
  line-height: 40px;
  text-align: center;
  border: 1px solid #ddd;
  color: var(--paraColor);
  border-radius: 6px;
  transition: all linear 0.3s;
}

.product_page_top_button .nav-link.active {
  color: var(--themeColorTwo);
  border-color: var(--themeColorTwo);
}

.product_page_sorting .form-select {
  border-radius: 6px;
  font-size: 15px;
  color: var(--colorBlack);
}

.product_page_sorting .form-select.show {
  width: 130px;
}

/* Product Grid Styles */
.product_item_2 .product_img {
  height: 275px;
  position: relative;
}

.product_item .product_img img {
  object-fit: cover;
}

.product_item .product_text {
  padding: 10px 0;
}

.product_item .title {
  font-size: 18px;
  font-weight: 600;
  color: var(--colorBlack);
  text-transform: capitalize;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.product_item .title:hover {
  color: var(--themeColoeOne);
}

.product_item .price {
  font-size: 16px;
  color: var(--colorBlack);
}

.product_item .rating {
  color: var(--ratingColor);
  font-size: 12px;
}

.product_item .rating span {
  font-size: 12px;
  margin-left: 3px;
}

.discount_list {
  position: absolute;
  top: 10px;
  left: 10px;
}

.discount_list li {
  background: var(--themeColorTwo);
  color: var(--colorWhite);
  padding: 5px 10px;
  font-size: 12px;
  border-radius: 4px;
}

.btn_list {
  position: absolute;
  bottom: 10px;
  right: 10px;
  display: flex;
  gap: 5px;
}

.btn_list li a img {
  width: 24px !important;
  height: 24px !important;
}

/* Product List Styles */
.product_list_item .product_img {
  height: 335px;
  position: relative;
}

.product_list_item .product_text {
  padding: 0 0 0 25px;
}

.product_list_item .title {
  font-size: 22px;
  margin-bottom: 8px;
  -webkit-line-clamp: 1;
}

.product_list_item .rating {
  margin: 5px 0 10px 0;
}

.product_list_item .price {
  margin-bottom: 10px;
}

.product_list_item .short_description {
  font-size: 14px;
  margin-bottom: 15px;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.common_btn {
  background: var(--themeColoeOne);
  padding: 12px 25px;
  color: var(--colorWhite);
  text-transform: capitalize;
  font-weight: 500;
  font-size: 15px;
  border-radius: 4px;
  transition: all 0.3s ease;
}

.common_btn:hover {
  background: var(--colorBlack);
}
</style>