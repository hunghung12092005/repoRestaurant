<template>
  <div class="dashboard">
    <aside class="sidebar">
      <div class="logo">
        <span class="logo-icon">🍽️</span> Reztro
      </div>
      <nav>
        <ul>
          <li class="active"><i class="icon-dashboard"></i> Dashboard</li>
          <li><i class="icon-orders"></i> Orders</li>
          <li><i class="icon-messages"></i> Messages <span class="badge">1</span></li>
          <li><i class="icon-calendar"></i> Calendar</li>
          <li><i class="icon-menu"></i> Menu</li>
          <li><i class="icon-inventory"></i> Inventory</li>
          <li><i class="icon-reviews"></i> Reviews</li>
        </ul>
      </nav>
    </aside>

    <main class="main-content">
      <header>
        <h2>Hello Orlando, welcome back!</h2>
        <form class="search-bar">
          <span class="search-icon">🔍</span>
          <input type="text" placeholder="Search anything..." />
        </form>
        <div class="user-info">
          <span class="user-name">Orlando Laurentius</span>
          <img
            src="https://media.istockphoto.com/id/1300845620/vector/user-icon-flat-isolated-on-white-background-user-symbol-vector-illustration.jpg?s=612x612&w=0&k=20&c=yBeyba0hUkh14_jgv1OKqIH0CCSWU_4ckRkAoy2p73o="
            alt="User Avatar"
            class="avatar"
          />
          <span class="role-badge">Admin</span>
        </div>
      </header>

      <section class="stats">
        <div class="stat-card">
          <div class="stat-icon">
            <span class="icon-orders">📦</span>
          </div>
          <div class="stat-content">
            <h3>Total Orders</h3>
            <p>48,652 <span class="trend up">+1.8%</span></p>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon">
            <span class="icon-customers">👥</span>
          </div>
          <div class="stat-content">
            <h3>Total Customers</h3>
            <p>12,48</p>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon">
            <span class="icon-revenue">💰</span>
          </div>
          <div class="stat-content">
            <h3>Total Revenue</h3>
            <p>$215,860 <span class="trend up">+2.3%</span></p>
          </div>
        </div>
      </section>

      <section class="charts">
        <div class="chart-card">
          <div class="chart-header">
            <h3>Total Revenue</h3>
            <span class="filter">Last 8 Months</span>
          </div>
          <div class="chart-placeholder">Revenue Chart</div>
          <p class="total">$184,839</p>
        </div>
        <div class="chart-card">
          <div class="chart-header">
            <h3>Top Categories</h3>
            <span class="filter">This Month</span>
          </div>
          <div class="chart-placeholder">Pie Chart</div>
          <ul class="categories">
            <li><span class="dot seafood"></span> Seafood 30%</li>
            <li><span class="dot beverages"></span> Beverages 25%</li>
            <li><span class="dot dessert"></span> Dessert 25%</li>
            <li><span class="dot pasta"></span> Pasta 20%</li>
          </ul>
        </div>
      </section>

      <section class="orders-trending">
        <div class="orders-overview">
          <div class="chart-header">
            <h3>Orders Overview</h3>
            <span class="filter">This Week</span>
          </div>
          <div class="chart-placeholder">Bar Chart</div>
          <p class="highlight">118 orders (Thursday)</p>
          <div class="order-types">
            <div class="chart-header">
              <h4>Order Types</h4>
              <span class="filter">This Month</span>
            </div>
            <ul>
              <li>Dine-in <span>45%</span> <span class="count">900</span></li>
              <li>Takeaway <span>30%</span> <span class="count">600</span></li>
              <li>Online <span>25%</span> <span class="count">500</span></li>
            </ul>
          </div>
        </div>

        <div class="trending-menus">
          <div class="chart-header">
            <h3>Trending Menus</h3>
            <span class="filter">This Week</span>
          </div>
          <div class="menu-card">
            <img
              src="https://t3.ftcdn.net/jpg/05/62/54/06/360_F_562540618_fPHXG2UTKO2j7G46ITuoZcRtLnv2G0MQ.jpg"
              alt="Grilled Chicken Delight"
            />
            <h4>Grilled Chicken Delight</h4>
            <div class="rating">★ 4.9 <span>(350)</span></div>
            <p class="price">$18.00</p>
          </div>
          <div class="menu-card">
            <img
              src="https://images.smuckers.ca/images/recipes/29/Sunny-Citrus-Pound-Cake_desktop.jpg"
              alt="Sunny Citrus Cake"
            />
            <h4>Sunny Citrus Cake</h4>
            <div class="rating">★ 4.8 <span>(400)</span></div>
            <p class="price">$8.50</p>
          </div>
          <div class="menu-card">
            <img src="https://example.com/shrimp-salad.jpg" alt="Fiery Shrimp Salad" />
            <h4>Fiery Shrimp Salad</h4>
            <div class="rating">★ 4.7 <span>(270)</span></div>
            <p class="price">$12.00</p>
          </div>
        </div>
      </section>

      <section class="recent-orders">
        <div class="chart-header">
          <h3>Recent Orders</h3>
          <span class="filter">This Week</span>
          <a href="#" class="see-all">See All Orders</a>
        </div>
        <div class="order-search">
          <span>🔍</span> Search orderholder...
        </div>
        <table>
          <thead>
            <tr>
              <th>Order ID</th>
              <th>Photo</th>
              <th>Menu</th>
              <th>Qty</th>
              <th>Price</th>
              <th>Customer</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>ORD025</td>
              <td><img src="https://example.com/sushi.jpg" alt="Salmon Sushi Roll" /></td>
              <td>Salmon Sushi Roll</td>
              <td>3</td>
              <td>$30.00</td>
              <td>Dana White</td>
              <td><span class="status on-process">On Process</span></td>
            </tr>
            <tr>
              <td>ORD026</td>
              <td><img src="https://example.com/spaghetti.jpg" alt="Spaghetti Carbonara" /></td>
              <td>Spaghetti Carbonara</td>
              <td>1</td>
              <td>$15.00</td>
              <td>Eve Carter</td>
              <td><span class="status cancelled">Cancelled</span></td>
            </tr>
            <tr>
              <td>ORD027</td>
              <td><img src="https://example.com/cheeseburger.jpg" alt="Classic Cheeseburger" /></td>
              <td>Classic Cheeseburger</td>
              <td>1</td>
              <td>$10.00</td>
              <td>Charlie Brown</td>
              <td><span class="status completed">Completed</span></td>
            </tr>
          </tbody>
        </table>
      </section>

      <section class="reviews-activity">
        <div class="customer-reviews">
          <div class="chart-header">
            <h3>Customer Reviews</h3>
            <a href="#" class="see-all">See More Reviews</a>
          </div>
          <div class="review-card">
            <img src="https://example.com/pasta.jpg" alt="Classic Italian Penne" />
            <h4>Classic Italian Penne</h4>
            <p>
              This pasta divine! The flavor is unforgettable taste. Highly recommend for pasta lovers!
            </p>
            <div class="rating">★★★★★ <span>Sarah M. - Oct 5 2025</span></div>
          </div>
          <div class="review-card">
            <img src="https://example.com/pizza.jpg" alt="Smokey Supreme Pizza" />
            <h4>Smokey Supreme Pizza</h4>
            <p>Crispy crust, generous toppings and one of the best pizzas I’ve had!</p>
            <div class="rating">★★★★☆ <span>Michael R. - Oct 15 2025</span></div>
          </div>
        </div>

        <div class="recent-activity">
          <h3>Recent Activity</h3>
          <ul>
            <li>
              Sylvester Quilt
              <span>updated inventory - 10 units of "Organic Chicken Breast" 11:00 AM</span>
            </li>
            <li>
              Maria Kings <span>marked order #ORD028 as completed 10:45 AM</span>
            </li>
            <li>
              William Smith
              <span>added new reservation for 4 guests at 7:00 PM 10:30 AM</span>
            </li>
          </ul>
        </div>
      </section>

      <footer>
        <p>© 2025 Reztro Petadraw. Privacy Policy, Terms & Conditions, Contact</p>
        <div class="social-icons">
          <span>🐦</span>
          <span>📷</span>
          <span>📘</span>
        </div>
      </footer>

      <button class="upgrade-btn">Upgrade Now</button>
    </main>
  </div>
</template>

<script>
export default {
  name: 'AdminDashboard',
};
</script>

<style scoped>
/* General Layout */
.dashboard {
  display: flex;
  height: 100vh;
  font-family: 'Poppins', sans-serif;
  background: #f9f5f0;
}

/* Sidebar */
.sidebar {
  width: 220px;
  background: #fff;
  padding: 30px 20px;
  box-shadow: 2px 0 5px rgba(0, 0, 0, 0.05);
}
.logo {
  display: flex;
  align-items: center;
  font-size: 20px;
  font-weight: 600;
  color: #333;
  margin-bottom: 30px;
}
.logo-icon {
  margin-right: 10px;
}
nav ul {
  list-style: none;
  padding: 0;
}
nav ul li {
  padding: 12px 15px;
  margin: 5px 0;
  border-radius: 8px;
  display: flex;
  align-items: center;
  font-size: 16px;
  color: #666;
  cursor: pointer;
  transition: all 0.3s ease;
}
nav ul li.active,
nav ul li:hover {
  background: #ff6200;
  color: #fff;
}
nav ul li i {
  margin-right: 10px;
}
.badge {
  background: #ff6200;
  color: #fff;
  border-radius: 12px;
  padding: 2px 8px;
  font-size: 12px;
  margin-left: auto;
}

/* Main Content */
.main-content {
  flex: 1;
  padding: 30px;
  overflow-y: auto;
}
header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 30px;
}
header h2 {
  font-size: 24px;
  font-weight: 600;
  color: #333;
}
.search-bar,
.order-search {
  display: flex;
  align-items: center;
  background: #fff;
  border: 1px solid #ddd;
  border-radius: 8px;
  padding: 8px 12px;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
}
.search-bar .search-icon,
.order-search span {
  margin-right: 8px;
  font-size: 16px;
  color: #666;
}
.search-bar input,
.order-search span + * {
  border: none;
  outline: none;
  font-size: 14px;
  color: #666;
  width: 200px;
}
.search-bar input::placeholder,
.order-search span + *::placeholder {
  color: #999;
}
.user-info {
  display: flex;
  align-items: center;
}
.user-info .user-name {
  font-size: 16px;
  color: #333;
  margin-right: 10px;
}
.user-info .avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  margin-right: 10px;
}
.user-info .role-badge {
  background: #ff6200;
  color: #fff;
  font-size: 12px;
  padding: 4px 10px;
  border-radius: 12px;
  text-transform: uppercase;
}

/* Stats Section */
.stats {
  display: flex;
  gap: 20px;
  margin-bottom: 30px;
}
.stat-card {
  flex: 1;
  background: #fff;
  padding: 25px;
  border-radius: 12px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
  display: flex;
  align-items: center;
  transition: transform 0.2s ease;
}
.stat-card:hover {
  transform: translateY(-2px);
}
.stat-icon {
  width: 50px;
  height: 50px;
  background: #ff6200;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 15px;
}
.stat-icon span {
  font-size: 24px;
  color: #fff;
}
.stat-content h3 {
  font-size: 14px;
  font-weight: 500;
  color: #666;
  margin: 0 0 5px 0;
  text-transform: uppercase;
}
.stat-content p {
  font-size: 24px;
  font-weight: 600;
  color: #333;
  margin: 0;
  line-height: 1.2;
}
.stat-content .trend {
  font-size: 12px;
  padding: 2px 6px;
  border-radius: 10px;
  margin-left: 8px;
}
.stat-content .trend.up {
  background: #d4edda;
  color: #155724;
}

/* Charts Section */
.charts {
  display: flex;
  gap: 20px;
  margin-bottom: 30px;
}
.chart-card {
  flex: 1;
  background: #fff;
  padding: 20px;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}
.chart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
}
.chart-header h3,
.chart-header h4 {
  font-size: 18px;
  font-weight: 600;
  color: #333;
  margin: 0;
}
.chart-header .filter {
  font-size: 14px;
  color: #999;
}
.chart-placeholder {
  height: 150px;
  background: #f1f1f1;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #999;
  font-size: 14px;
}
.chart-card .total {
  font-size: 20px;
  font-weight: 600;
  color: #333;
  margin-top: 15px;
}
.categories {
  list-style: none;
  padding: 0;
  margin-top: 15px;
}
.categories li {
  font-size: 14px;
  color: #666;
  margin: 5px 0;
  display: flex;
  align-items: center;
}
.categories .dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  display: inline-block;
  margin-right: 10px;
}
.dot.seafood { background: #ff6200; }
.dot.beverages { background: #666; }
.dot.dessert { background: #ccc; }
.dot.pasta { background: #999; }

/* Orders and Trending Section */
.orders-trending {
  display: flex;
  gap: 20px;
  margin-bottom: 30px;
}
.orders-overview,
.trending-menus {
  background: #fff;
  padding: 20px;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}
.orders-overview {
  flex: 2;
}
.trending-menus {
  flex: 1;
}
.orders-overview .highlight {
  font-size: 16px;
  color: #333;
  margin: 15px 0;
}
.order-types ul {
  list-style: none;
  padding: 0;
}
.order-types li {
  display: flex;
  justify-content: space-between;
  font-size: 14px;
  color: #666;
  margin: 10px 0;
}
.order-types li .count {
  font-weight: 600;
  color: #333;
}
.trending-menus .menu-card {
  background: #fff;
  border-radius: 12px;
  margin-bottom: 15px;
  text-align: center;
}
.menu-card img {
  width: 100%;
  height: 120px;
  object-fit: cover;
  border-radius: 12px;
}
.menu-card h4 {
  font-size: 16px;
  font-weight: 600;
  color: #333;
  margin: 10px 0 5px;
}
.menu-card .rating {
  font-size: 14px;
  color: #ff6200;
}
.menu-card .rating span {
  color: #999;
}
.menu-card .price {
  font-size: 16px;
  font-weight: 600;
  color: #333;
  margin: 5px 0 0;
}

/* Recent Orders */
.recent-orders {
  background: #fff;
  padding: 20px;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  margin-bottom: 30px;
}
.recent-orders table {
  width: 100%;
  border-collapse: collapse;
}
.recent-orders th,
.recent-orders td {
  padding: 12px;
  text-align: left;
  font-size: 14px;
  color: #666;
}
.recent-orders th {
  font-weight: 600;
  color: #333;
}
.recent-orders td img {
  width: 40px;
  height: 40px;
  border-radius: 8px;
}
.recent-orders .status {
  padding: 5px 10px;
  border-radius: 12px;
  font-size: 12px;
}
.status.on-process {
  background: #fff3cd;
  color: #856404;
}
.status.completed {
  background: #d4edda;
  color: #155724;
}
.status.cancelled {
  background: #f8d7da;
  color: #721c24;
}
.see-all {
  font-size: 14px;
  color: #ff6200;
  text-decoration: none;
}

/* Reviews and Activity */
.reviews-activity {
  display: flex;
  gap: 20px;
  margin-bottom: 30px;
}
.customer-reviews,
.recent-activity {
  background: #fff;
  padding: 20px;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}
.customer-reviews {
  flex: 2;
}
.reviews-activity .review-card {
  display: inline-block;
  width: 48%;
  vertical-align: top;
  margin-right: 2%;
}
.review-card img {
  width: 100%;
  height: 120px;
  object-fit: cover;
  border-radius: 12px;
}
.review-card h4 {
  font-size: 16px;
  font-weight: 600;
  color: #333;
  margin: 10px 0 5px;
}
.review-card p {
  font-size: 14px;
  color: #666;
  margin: 5px 0;
}
.review-card .rating {
  font-size: 14px;
  color: #ff6200;
}
.review-card .rating span {
  color: #999;
  margin-left: 10px;
}
.recent-activity {
  flex: 1;
}
.recent-activity ul {
  list-style: none;
  padding: 0;
}
.recent-activity li {
  font-size: 14px;
  color: #666;
  margin: 15px 0;
}
.recent-activity li span {
  color: #999;
  display: block;
}

/* Footer */
footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 14px;
  color: #999;
  margin-top: 30px;
}
.social-icons span {
  margin-left: 10px;
  font-size: 16px;
}

/* Upgrade Button */
.upgrade-btn {
  position: fixed;
  bottom: 30px;
  left: 30px;
  background: #ff6200;
  color: #fff;
  padding: 10px 20px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
}
</style>
