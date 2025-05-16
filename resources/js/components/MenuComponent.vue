<template>
  <section class="menu section">
    <h2 class="section__title">Menu Của Chúng Tôi</h2>
    <div class="menu__tabs">
      <button @click="currentTab = 'breakfast'" :class="{ active: currentTab === 'breakfast' }">Bữa sáng</button>
      <button @click="currentTab = 'lunch'" :class="{ active: currentTab === 'lunch' }">Bữa trưa</button>
      <button @click="currentTab = 'dinner'" :class="{ active: currentTab === 'dinner' }">Bữa tối</button>
    </div>

    <div class="menu__items">
      <div
        v-for="(item, index) in filteredItems"
        :key="index"
        class="menu__item"
      >
        <img :src="item.image" :alt="item.name" class="menu__img" />
        <div class="menu__details">
          <div class="menu__title">
            <h3>{{ item.name }}</h3>
            <span class="menu__price">${{ item.price }}</span>
          </div>
          <p>{{ item.description }}</p>
        </div>
      </div>
    </div>

    <div class="menu__btn">
      <button>Xem thêm</button>
    </div>
  </section>
</template>

<script>
export default {
  data() {
    return {
      currentTab: 'breakfast',
      breakfastItems: [
        {
          name: 'Bánh Mì Gà',
          price: 24,
          description: 'Bánh mì gà nướng thơm ngon với rau xà lách, cà chua và sốt mayo.',
          image: 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSmWZ9OBxXbdPPVS7wMmzb0JvzVs_yTVmn5Ew&s'
        },
        {
          name: 'Cà Phê Đen',
          price: 20,
          description: 'Cà phê đen thơm lừng, được pha chế hoàn hảo.',
          image: 'https://file.hstatic.net/1000274203/article/tac_dung_cua_ca_phe_den_2a1a0e12486f430cb893203b10dea6c7.jpg'
        },
        {
          name: 'Bánh Mì Trứng',
          price: 22,
          description: 'Bánh mì nóng giòn kẹp trứng chiên và rau thơm.',
          image: 'https://cdn1z.reatimes.vn/media/uploaded/17/2017/05/21/banh-mi-trung-viet-nam-mon-an-sang-ngon-nhat-the-gioi-hinh-anh-1-1-tieudungplus.jpg'
        },
        {
          name: 'Sinh Tố Dâu',
          price: 15,
          description: 'Sinh tố dâu tươi mát, ngọt dịu.',
          image: 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR4viLoyHDeoN2EwnTHhWNOU4URm_7ZIB0AyQ&s'
        },
      ],
      lunchItems: [
        {
          name: 'Cơm Gà Hải Nam',
          price: 35,
          description: 'Gà luộc mềm, cơm thơm béo cùng nước chấm đặc trưng.',
          image: 'https://storage.googleapis.com/onelife-public/blog.onelife.vn/2021/10/cach-lam-com-ga-hai-nam-mon-chinh-128712856017.jpg'
        },
        {
          name: 'Phở Bò',
          price: 30,
          description: 'Phở bò truyền thống với nước dùng đậm đà.',
          image: 'https://www.cleanipedia.com/images/5iwkm8ckyw6v/1nkKwq9bh6895XmQbw1u57/ff61e91cc6b72de45c919eec412face3/aHVvbmctZGFuLWNhY2gtbmF1LXBoby1iby1jaHVhbi12aS1uZ29haS1oYW5nLXRob20tbmdvbi1oYXAtZGFuLmpwZw/600w/t%C3%B4-ph%E1%BB%9F-b%C3%B2-v%E1%BB%9Bi-h%C3%A0nh%2C-rau-m%C3%B9i%2C-%E1%BB%9Bt%2C-v%C3%A0-chanh-c%E1%BA%A1nh-%E1%BA%A5m-n%C6%B0%E1%BB%9Bc-ch%E1%BA%A5m..jpg'
        },
        {
          name: 'Bún Thịt Nướng',
          price: 32,
          description: 'Bún kèm thịt nướng, rau sống và nước mắm chua ngọt.',
          image: 'https://cdn.tgdd.vn/2021/12/CookRecipe/GalleryStep/3-3.jpg'
        },
        {
          name: 'Gỏi Cuốn',
          price: 28,
          description: 'Gỏi cuốn tươi ngon, ăn kèm nước chấm đặc biệt.',
          image: 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRmZMNTkaFVX25zsQkPxsGK9K4wukuws-BhPg&s'
        }
      ],
      dinnerItems: [
        {
          name: 'Lẩu Hải Sản',
          price: 55,
          description: 'Lẩu đậm đà với tôm, mực, cá, rau và nước dùng thơm ngon.',
          image: 'https://www.cet.edu.vn/wp-content/uploads/2018/01/lau-hai-san.jpg'
        },
        {
          name: 'Tôm Rim Mặn',
          price: 40,
          description: 'Tôm rim với nước mắm và tiêu, ăn cùng cơm trắng.',
          image: 'https://haisanloccantho.com/wp-content/uploads/2024/11/thuong-thuc-mon-tom-rim-man-ngot.jpg'
        },
        {
          name: 'Canh Chua Cá',
          price: 38,
          description: 'Canh chua cá lóc kiểu miền Nam, thanh mát.',
          image: 'https://i-giadinh.vnecdn.net/2021/03/19/ca2-1616122035-2163-1616122469.jpg'
        },
        {
          name: 'Mực Nướng Sa Tế',
          price: 45,
          description: 'Mực nướng thơm phức, cay nồng cùng sa tế đặc biệt.',
          image: 'https://static.hawonkoo.vn/hwk02/images/2023/10/cach-lam-muc-nuong-sa-te-bang-noi-chien-khong-dau-1.jpg'
        }
      ]
    };
  },
  computed: {
    filteredItems() {
      if (this.currentTab === 'breakfast') return this.breakfastItems;
      if (this.currentTab === 'lunch') return this.lunchItems;
      if (this.currentTab === 'dinner') return this.dinnerItems;
      return [];
    }
  }
};
</script>

<style scoped>
.section {
  padding: 2rem;
  text-align: center;
}

.section__title {
  font-size: 2rem;
  color: #d92d3a;
  margin-bottom: 1.5rem;
}

.menu__tabs {
  display: flex;
  justify-content: center;
  gap: 1rem;
  margin-bottom: 2rem;
}

.menu__tabs button {
  padding: 0.5rem 1rem;
  border: none;
  background-color: #eee;
  font-weight: bold;
  cursor: pointer;
  border-radius: 5px;
  transition: background 0.3s;
}

.menu__tabs .active {
  background-color: #d92d3a;
  color: white;
}

.menu__items {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 2rem;
}

.menu__item {
  width: 250px;
  text-align: left;
  border: 1px solid #eee;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  background: #fff;
  display: flex;
  flex-direction: column;
}

.menu__img {
  width: 100%;
  height: 160px;
  object-fit: cover;
  display: block;
}

.menu__details {
  padding: 1rem;
}

.menu__title {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.menu__title h3 {
  margin: 0;
  color: #b23234;
  font-size: 1.1rem;
  font-weight: 600;
}

.menu__price {
  font-weight: bold;
  font-style: italic;
  color: #333;
}

.menu__btn {
  margin-top: 2rem;
}

.menu__btn button {
  background-color: #d92d3a;
  color: white;
  border: none;
  padding: 0.7rem 1.5rem;
  font-weight: bold;
  border-radius: 8px;
  cursor: pointer;
}
</style>
