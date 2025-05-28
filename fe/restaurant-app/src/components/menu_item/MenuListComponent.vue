<template>
  <loading v-if="isLoading" />
  <div v-if="showAlert" class="success">
    <div class="flex">
      <div class="flex-shrink-0">
        <svg aria-hidden="true" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"
          class="succes-svg">
          <path clip-rule="evenodd"
            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
            fill-rule="evenodd"></path>
        </svg>
      </div>
      <div class="success-prompt-wrap">
        <p class="success-prompt-heading">Tạo đơn thành công!</p>

      </div>
    </div>
  </div>
  <div class="menu-board">
    <!-- <AlertSuccess v-if="showAlert" message="Thêm món thành công!" message2="Đây là thông báo phụ!" /> -->
    <h1 class="main-title">Welcome to Our Delicious Menu</h1>
    <div class="menu-container">
      <div class="fix-left">
        <p>Đã thêm <strong>{{ count }}</strong> món ăn</p>
        <router-link :to="{ name: 'detailOrderMenu', params: { ids: JSON.stringify(currentItem) } }">
          <div class="btn-main">Xem đơn hàng </div>
        </router-link>

      </div>
      <div class="menu-section">
        <div class="menu-image">
          <img
            src="https://media.istockphoto.com/id/1268693109/vi/anh/g%C3%A0-n%C6%B0%E1%BB%9Bng.jpg?s=612x612&w=0&k=20&c=XHWMnYeNN-zC2nkP8nwMfOpAVGMcTYKA4GzrYVIoOLg="
            alt="Chicken Image" />
        </div>
        <div class="menu-items">
          <h2># Chicken <!-- {{ count }} - {{ currentItem }} --></h2>
          <ul v-for="item in menuItems" :key="item.name">
            <li>{{ item.Name }} <span>${{ item.Price }} </span>
              <button class="button" @click="toggleAlert(item.id)">
                <svg viewBox="0 0 16 16" class="bi bi-cart-check" height="24" width="24"
                  xmlns="http://www.w3.org/2000/svg" fill="#fff">
                  <path
                    d="M11.354 6.354a.5.5 0 0 0-.708-.708L8 8.293 6.854 7.146a.5.5 0 1 0-.708.708l1.5 1.5a.5.5 0 0 0 .708 0l3-3z">
                  </path>
                  <path
                    d="M.5 1a.5.5 0 0 0 0 1h1.11l.401 1.607 1.498 7.985A.5.5 0 0 0 4 12h1a2 2 0 1 0 0 4 2 2 0 0 0 0-4h7a2 2 0 1 0 0 4 2 2 0 0 0 0-4h1a.5.5 0 0 0 .491-.408l1.5-8A.5.5 0 0 0 14.5 3H2.89l-.405-1.621A.5.5 0 0 0 2 1H.5zm3.915 10L3.102 4h10.796l-1.313 7h-8.17zM6 14a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm7 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0z">
                  </path>
                </svg>
              </button>
            </li>
          </ul>
        </div>
      </div>
      <div class="menu-section reverse">
        <div class="menu-image">
          <img
            src="https://media.istockphoto.com/id/1442417585/vi/anh/ng%C6%B0%E1%BB%9Di-nh%E1%BA%ADn-%C4%91%C6%B0%E1%BB%A3c-m%E1%BB%99t-mi%E1%BA%BFng-pizza-pepperoni-ph%C3%B4-mai.jpg?s=612x612&w=0&k=20&c=5e9ycu8KvpKcIVIwNmEGaxr8yh9x8IMdpeIJ3HdtSxU="
            alt="Pizza Image" />
        </div>
        <div class="menu-items">
          <h2># Pizza</h2>
          <ul>
            <li>Margherita Sushi Pizza ............ <span>$30.00</span></li>
            <li>Sausage Pizza ............ <span>$32.00</span></li>
            <li>Original Pizza ............ <span>$31.00</span></li>
            <li>Pepperoni Pizza ............ <span>$55.00</span></li>
            <li>Italian Pizza ............ <span>$32.00</span></li>
            <li>Vegetarian Pizza ............ <span>$28.00</span></li>
            <li>Supreme Pizza ............ <span>$40.00</span></li>
            <li>Cheese Pizza ............ <span>$27.00</span></li>
            <li>Seafood Pizza ............ <span>$45.00</span></li>
            <li>Hawaiian Pizza ............ <span>$33.00</span></li>
            <button class="button">
              <svg viewBox="0 0 16 16" class="bi bi-cart-check" height="24" width="24"
                xmlns="http://www.w3.org/2000/svg" fill="#fff">
                <path
                  d="M11.354 6.354a.5.5 0 0 0-.708-.708L8 8.293 6.854 7.146a.5.5 0 1 0-.708.708l1.5 1.5a.5.5 0 0 0 .708 0l3-3z">
                </path>
                <path
                  d="M.5 1a.5.5 0 0 0 0 1h1.11l.401 1.607 1.498 7.985A.5.5 0 0 0 4 12h1a2 2 0 1 0 0 4 2 2 0 0 0 0-4h7a2 2 0 1 0 0 4 2 2 0 0 0 0-4h1a.5.5 0 0 0 .491-.408l1.5-8A.5.5 0 0 0 14.5 3H2.89l-.405-1.621A.5.5 0 0 0 2 1H.5zm3.915 10L3.102 4h10.796l-1.313 7h-8.17zM6 14a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm7 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0z">
                </path>
              </svg>
            </button>
          </ul>
        </div>
      </div>
      <div class="menu-section">
        <div class="menu-image">
          <img src="https://ngochieu.com.vn/l3fkjizn4pr57n5e/lich-su-ra-doi-mon-bit-tet-ngon.jpg" alt="Beef Image" />
        </div>
        <div class="menu-items">
          <h2># Beef</h2>
          <ul>
            <li>Beef Steak ............ <span>$50.00</span></li>
            <li>Beef Burger ............ <span>$45.00</span></li>
            <li>Grilled Beef ............ <span>$38.00</span></li>
            <li>Beef Ribs ............ <span>$55.00</span></li>
            <li>Beef Stir-Fry ............ <span>$30.00</span></li>
            <li>Beef Tacos ............ <span>$20.00</span></li>
            <li>Beef Curry ............ <span>$35.00</span></li>
            <li>Beef Skewers ............ <span>$25.00</span></li>
            <li>Beef Meatballs ............ <span>$18.00</span></li>
            <li>Roast Beef ............ <span>$40.00</span></li>
          </ul>
        </div>
      </div>
      <div class="menu-section reverse">
        <div class="menu-image">
          <img
            src="https://i0.wp.com/inchefmode.com/wp-content/uploads/2022/07/PARKROYAL-COLLECTION-Pickering_Lime-Restaurant_Lobsterfest-2022-1-scaled.jpg?resize=810%2C535&ssl=1"
            alt="Seafood Image" />
        </div>
        <div class="menu-items">
          <h2># Seafood</h2>
          <ul>
            <li>Grilled Salmon ............ <span>$45.00</span></li>
            <li>Shrimp Cocktail ............ <span>$30.00</span></li>
            <li>Fried Fish ............ <span>$25.00</span></li>
            <li>Seafood Paella ............ <span>$40.00</span></li>
            <li>Crab Legs ............ <span>$50.00</span></li>
            <li>Clam Chowder ............ <span>$28.00</span></li>
            <li>Lobster Tail ............ <span>$60.00</span></li>
            <li>Shrimp Scampi ............ <span>$35.00</span></li>
            <li>Fish and Chips ............ <span>$22.00</span></li>
            <li>Oyster Platter ............ <span>$45.00</span></li>
          </ul>
        </div>
      </div>
      <div class="menu-section">
        <div class="menu-image">
          <img src="https://thumb.photo-ac.com/e2/e2f2753c5c96aea862484af6845522b7_t.jpeg" alt="Dessert Image" />
        </div>
        <div class="menu-items">
          <h2># Desserts</h2>
          <ul>
            <li>Chocolate Cake ............ <span>$15.00</span></li>
            <li>Apple Pie ............ <span>$12.00</span></li>
            <li>Ice Cream Sundae ............ <span>$10.00</span></li>
            <li>Cheesecake ............ <span>$14.00</span></li>
            <li>Tiramisu ............ <span>$16.00</span></li>
            <li>Fruit Tart ............ <span>$13.00</span></li>
            <li>Brownies ............ <span>$11.00</span></li>
            <li>Pudding ............ <span>$9.00</span></li>
            <li>Creme Brulee ............ <span>$18.00</span></li>
            <li>Macarons ............ <span>$20.00</span></li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<!-- <script>
export default {
  name: 'MenuComponent',
  data() {
    return {
      chickenItems: [
        { name: 'Chicken Food', price: '$25.00' },
        { name: 'Chicken Burger', price: '$70.00' },
        { name: 'Chicken Meal', price: '$10.00' },
        { name: 'Fried Chicken', price: '$31.00' },
        { name: 'Chicken Wings', price: '$22.00' },
        { name: 'Grilled Chicken', price: '$45.00' },
        { name: 'Spicy Chicken', price: '$28.00' },
        { name: 'Chicken Strips', price: '$18.00' },
        { name: 'Chicken Nuggets', price: '$15.00' },
        { name: 'BBQ Chicken', price: '$urous35.00' },
      ],
      pizzaItems: [
        { name: 'Margherita Sushi Pizza', price: '$30.00' },
        { name: 'Sausage Pizza', price: '$32.00' },
        { name: 'Original Pizza', price: '$31.00' },
        { name: 'Pepperoni Pizza', price: '$55.00' },
        { name: 'Italian Pizza', price: '$32.00' },
        { name: 'Vegetarian Pizza', price: '$28.00' },
        { name: 'Supreme Pizza', price: '$40.00' },
        { name: 'Cheese Pizza', price: '$27.00' },
        { name: 'Seafood Pizza', price: '$45.00' },
        { name: 'Hawaiian Pizza', price: '$33.00' },
      ],
      beefItems: [
        { name: 'Beef Steak', price: '$50.00' },
        { name: 'Beef Burger', price: '$45.00' },
        { name: 'Grilled Beef', price: '$38.00' },
        { name: 'Beef Ribs', price: '$55.00' },
        { name: 'Beef Stir-Fry', price: '$30.00' },
        { name: 'Beef Tacos', price: '$20.00' },
        { name: 'Beef Curry', price: '$35.00' },
        { name: 'Beef Skewers', price: '$25.00' },
        { name: 'Beef Meatballs', price: '$18.00' },
        { name: 'Roast Beef', price: '$40.00' },
      ],
      seafoodItems: [
        { name: 'Grilled Salmon', price: '$45.00' },
        { name: 'Shrimp Cocktail', price: '$30.00' },
        { name: 'Fried Fish', price: '$25.00' },
        { name: 'Seafood Paella', price: '$40.00' },
        { name: 'Crab Legs', price: '$50.00' },
        { name: 'Clam Chowder', price: '$28.00' },
        { name: 'Lobster Tail', price: '$60.00' },
        { name: 'Shrimp Scampi', price: '$35.00' },
        { name: 'Fish and Chips', price: '$22.00' },
        { name: 'Oyster Platter', price: '$45.00' },
      ],
      dessertItems: [
        { name: 'Chocolate Cake', price: '$15.00' },
        { name: 'Apple Pie', price: '$12.00' },
        { name: 'Ice Cream Sundae', price: '$10.00' },
        { name: 'Cheesecake', price: '$14.00' },
        { name: 'Tiramisu', price: '$16.00' },
        { name: 'Fruit Tart', price: '$13.00' },
        { name: 'Brownies', price: '$11.00' },
        { name: 'Pudding', price: '$9.00' },
        { name: 'Creme Brulee', price: '$18.00' },
        { name: 'Macarons', price: '$20.00' },
      ],
    };
  },
};
</script> -->
<script setup>
import axios from 'axios';
import { inject, ref } from 'vue';
import { onMounted } from 'vue';
import loading from '../loading.vue';
const apiUrl = inject('apiUrl');
const menuItems = ref([]);
const isLoading = ref(false); // Biến để theo dõi trạng thái tải dữ liệu
//import AlertSuccess from '../AlertSuccess.vue';
const showAlert = ref(false);
const count = ref(0); // Biến đếm để theo dõi số lần nhấn nút
const currentItem = ref([]);
const toggleAlert = (item) => {
  currentItem.value.push(item); // Thêm item vào mảng
  count.value++;
  showAlert.value = true; // Hiện thông báo
  setTimeout(() => {
    showAlert.value = false; // Ẩn thông báo sau 4 giây
  }, 900);
};
// const toggleAlert = () => {
//   count.value++; // Tăng biến đếm mỗi khi nút được nhấn
//   showAlert.value = true; // Hiện thông báo
//   setTimeout(() => {
//     showAlert.value = false; // Ẩn thông báo sau 2 giây
//   }, 900);
// };
const getMenu = async () => {
  isLoading.value = true; // Bắt đầu tải dữ liệu
  try {
    const response = await axios.get(`${apiUrl}/api/menu-items`);
    //console.log(menuItems.value);
    menuItems.value = response.data.topPriceItems;
  } catch (error) {
    console.error('There has been a problem with your fetch operation:', error);
  } finally {
    isLoading.value = false; // Kết thúc tải dữ liệu
  }
};
onMounted(() => {
  getMenu();
});
</script>
<style scoped>
.fix-left {
  position: fixed;
  /* Đặt vị trí cố định */
  bottom: 1rem;
  /* Cách lề dưới 1rem */
  right: 1rem;
  /* Cách lề phải 1rem */
  background-color: wheat;
  /* Màu nền */
  padding: 1rem;
  border-radius: 0.375rem;
  z-index: 1000;
  /* Đảm bảo nó nằm trên các phần tử khác */
}

/* From Uiverse.io by kennyotsu */
.flex {
  display: flex;
}

.flex-shrink-0 {
  flex-shrink: 0;
}

.success {
  position: fixed;
  /* Giữ thông báo ở vị trí cố định */
  padding: 1rem;
  background-color: #ECFDF5;
  /* Màu nền */
  border-radius: 0.375rem;
  opacity: 0;
  /* Bắt đầu với độ mờ 0 */
  transform: translateX(100%);
  /* Bắt đầu từ bên phải */
  animation: slide-in 1.3s forwards;
  /* Chạy animation trong 4 giây */
  z-index: 1000;
  /* Đảm bảo nó nằm trên các phần tử khác */
  right: 1rem;
  /* Đặt cách lề bên phải */
  top: 5rem;
  /* Đặt cách lề bên trên */
}

@keyframes slide-in {
  0% {
    opacity: 0;
    transform: translateX(100%);
    /* Bắt đầu bên phải */
  }

  50% {
    opacity: 1;
    /* Hiện ra hoàn toàn */
    transform: translateX(0);
    /* Về vị trí ban đầu */
  }

  100% {
    opacity: 0;
    /* Mất đi */
    transform: translateX(0);
    /* Giữ vị trí ban đầu */
  }
}

.succes-svg {
  color: rgb(74 222 128);
  width: 1rem;
  height: 1rem;
}

/* end success */
.button {
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 2px 10px;
  gap: 15px;
  outline-offset: -3px;
  border-radius: 5px;
  border: none;
  margin-left: 15px;
  cursor: pointer;
  transition: 400ms;
  background-color: rgb(73, 198, 236);
}

.button svg path {
  transition: 400ms;
}

.button:hover {
  background-color: transparent;
}

.button:hover svg path {
  fill: #181717;
}

.menu-board {
  font-family: 'Poppins', sans-serif;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 40px 20px;
  overflow-x: hidden;
}

.main-title {
  color: #1a2a44;
  font-size: 2.8em;
  font-weight: 700;
  margin-bottom: 40px;
  text-align: center;
  letter-spacing: 0.5px;
  animation: fadeInDown 1s ease-out;
}

.main-title::after {
  content: '';
  display: block;
  width: 80px;
  height: 3px;
  background: #ff6b6b;
  margin: 10px auto;
  border-radius: 2px;
}

.menu-container {
  width: 100%;
  max-width: 1100px;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.menu-section {
  display: flex;
  align-items: stretch;
  padding: 25px;
  border-radius: 15px;
  border: 2px solid white;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
  width: 100%;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  animation: fadeInUp 0.6s ease-out forwards;
  animation-delay: calc(var(--index) * 0.15s);
  opacity: 0;
}

.menu-section:nth-child(1) {
  --index: 1;
}

.menu-section:nth-child(2) {
  --index: 2;
}

.menu-section:nth-child(3) {
  --index: 3;
}

.menu-section:nth-child(4) {
  --index: 4;
}

.menu-section:nth-child(5) {
  --index: 5;
}

.menu-section:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
}

.menu-section.reverse {
  flex-direction: row-reverse;
}

.menu-image {
  flex: 0 0 45%;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 10px;
}

.menu-image img {
  width: 100%;
  height: 450px;
  margin-top: 50px;
  object-fit: cover;
  border-radius: 10px;
  transition: transform 0.3s ease;
}

.menu-image img:hover {
  transform: scale(1.03);
}

.menu-items {
  flex: 0 0 55%;
  padding: 15px;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.menu-items h2 {
  color: #1a2a44;
  font-size: 1.8em;
  font-weight: 600;
  margin-bottom: 15px;
  border-bottom: 2px solid #ff6b6b;
  padding-bottom: 8px;
}

.menu-items ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.menu-items li {
  display: flex;
  justify-content: space-between;
  padding: 10px 0px;
  font-size: 1rem;
  color: #2d3748;
  border-bottom: 1px dotted #16202d;
  transition: color 0.2s ease;
}

.menu-items li:hover {
  color: #ff6b6b;
}

.menu-items li span {
  color: #ff6b6b;
  font-weight: 500;
  font-size: 1rem;
}

@media (max-width: 1024px) {
  .menu-section {
    flex-direction: column;
    align-items: center;
  }

  .menu-section.reverse {
    flex-direction: column;
  }

  .menu-image {
    flex: 0 0 100%;
    max-width: 350px;
  }

  .menu-image img {
    height: 250px;
  }

  .menu-items {
    flex: 0 0 100%;
    text-align: center;
  }

  .menu-items h2 {
    font-size: 1.2em;
  }
}

@media (max-width: 480px) {
  .menu-container {
    margin-bottom: 100px;
  }

  .main-title {
    font-size: 2em;
  }

  .menu-section {
    padding: 15px;
  }

  .menu-image img {
    height: 200px;
  }

  .menu-items h2 {
    font-size: 1.4em;
  }

  .menu-items li {
    font-size: 1em;
  }
}

@keyframes fadeInDown {
  from {
    opacity: 0;
    transform: translateY(-15px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(15px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
