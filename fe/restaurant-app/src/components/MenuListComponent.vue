<template>
  <div class="menu-container">
    <!-- Header Section -->
    <div class="flex justify-between items-center mb-6">
      <a href="#" class="text-sm text-gray-600 hover:text-gray-800">Recent Gallery</a>
      <h1 class="text-3xl font-bold text-gray-800">Our All Menu</h1>
      <div></div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex space-x-6 border-b border-gray-200 mb-6">
      <button
        v-for="tab in tabs"
        :key="tab"
        @click="activeTab = tab"
        :class="[
          'py-2 px-4 text-sm font-medium',
          activeTab === tab
            ? 'bg-yellow-600 text-white rounded-t-md'
            : 'text-gray-600 hover:text-gray-800'
        ]"
      >
        {{ tab }}
      </button>
    </div>

    <!-- Menu Items Section -->
    <div>
      <h2 class="text-xl font-semibold text-gray-800 mb-4">{{ activeTab }}</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div
          v-for="item in filteredMenuItems"
          :key="item.name"
          class="bg-gray-100 rounded-lg overflow-hidden shadow-md hover:shadow-lg transition-shadow"
        >
          <img :src="item.image" alt="Menu item" class="w-full h-48 object-cover" />
          <div class="p-4">
            <h3 class="text-lg font-semibold text-gray-800">{{ item.name }}</h3>
            <p class="text-sm text-gray-600 mt-1">Thing lesser replenish evening called...</p>
            <p class="text-lg font-bold text-gray-800 mt-2">{{ item.price }}</p>
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
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
  background-color: #f5f5f5;
}

button:focus {
  outline: none;
}
</style>
