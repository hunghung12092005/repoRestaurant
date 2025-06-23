<template>
  <div class="container py-5 menu-container">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <a href="#" class="text-muted small text-decoration-none hover-link">Recent Gallery</a>
      <h1 class="display-6 fw-bold text-primary-emphasis">Our All Menu</h1>
      <div></div>
    </div>

    <!-- Navigation Tabs -->
    <ul class="nav nav-tabs custom-tabs mb-5">
      <li class="nav-item" v-for="tab in tabs" :key="tab">
        <button
          class="nav-link"
          :class="{ active: activeTab === tab }"
          @click="activeTab = tab"
        >
          {{ tab }}
        </button>
      </li>
    </ul>

    <!-- Menu Items Section -->
    <div>
      <h2 class="h5 fw-bold text-secondary mb-3">{{ activeTab }}</h2>
      <div class="row g-4">
        <div
          class="col-12 col-sm-6 col-lg-3"
          v-for="item in filteredMenuItems"
          :key="item.name"
        >
          <div class="card h-100 menu-card border-0 shadow-sm hover-card">
            <img :src="item.image" class="card-img-top rounded-top" alt="Menu item">
            <div class="card-body bg-white rounded-bottom">
              <h5 class="card-title text-dark">{{ item.name }}</h5>
              <p class="card-text text-muted small">Thing lesser replenish evening called...</p>
              <p class="card-text fw-bold text-success fs-5">{{ item.price }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "MenuComponent",
  data() {
    return {
      activeTab: "All Active",
      tabs: ["All Active", "Breakfast Food", "Lunch Food", "Dinner Menu", "Best Offer"],
      menuItems: [
        {
          name: "MI Tierra Burger",
          price: "$17.65",
          image: "https://img.freepik.com/photos-premium/burger-mot-burger-dessus_878462-37.jpg?w=360",
          category: "Breakfast Food"
        },
        {
          name: "Signature Beef",
          price: "$18.36",
          image: "https://img.freepik.com/photos-premium/burger-mot-burger-dessus_878462-37.jpg?w=360",
          category: "Breakfast Food"
        },
        {
          name: "Pastram Sandwic",
          price: "$11.28",
          image: "https://via.placeholder.com/300x200?text=Pastram+Sandwic",
          category: "Breakfast Food"
        },
        {
          name: "Classic Pasta",
          price: "$17.50",
          image: "https://via.placeholder.com/300x200?text=Classic+Pasta",
          category: "Breakfast Food"
        },
        {
          name: "Classic Pasta",
          price: "$17.50",
          image: "https://via.placeholder.com/300x200?text=Classic+Pasta",
          category: "Lunch Food"
        },
        {
          name: "Classic Pasta",
          price: "$17.50",
          image: "https://via.placeholder.com/300x200?text=Classic+Pasta",
          category: "Lunch Food"
        },
        {
          name: "Classic Pasta",
          price: "$17.50",
          image: "https://via.placeholder.com/300x200?text=Classic+Pasta",
          category: "Best Offer"
        },
        {
          name: "Classic Pasta",
          price: "$17.50",
          image: "https://via.placeholder.com/300x200?text=Classic+Pasta",
          category: "Best Offer"
        }
      ]
    };
  },
  computed: {
    filteredMenuItems() {
      if (this.activeTab === "All Active") {
        return this.menuItems;
      }
      return this.menuItems.filter(item => item.category === this.activeTab);
    }
  }
};
</script>
<style scoped>
.menu-container {
  background: linear-gradient(to right, #fffbe7, #fdf2f8);
  border-radius: 20px;
  max-width: 1200px; 
  margin: 0 auto;
  padding: 3rem 1rem; 
}

.hover-link:hover {
  color: #ff6f61 !important;
}

.custom-tabs .nav-link {
  color: #6c757d;
  font-weight: 500;
  background-color: transparent;
  border: none;
  border-radius: 10px 10px 0 0;
  transition: all 0.3s ease;
}

.custom-tabs .nav-link:hover {
  color: #ff6f61;
  background-color: #ffe4e1;
}

.custom-tabs .nav-link.active {
  background-color: #ff6f61;
  color: white !important;
  font-weight: bold;
}

.menu-card {
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  border-radius: 15px;
}

.menu-card img {
  height: 180px;
  object-fit: cover;
}

.hover-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 16px rgba(0,0,0,0.15);
}
</style>
