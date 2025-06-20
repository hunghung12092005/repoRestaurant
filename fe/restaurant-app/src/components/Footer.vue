<template>
  
    
    <footer class="footer">
     
      <div class="footer-content">
        <div class="footer-text1">
          <p>Capitalist Hotel</p>
          <p>Royal Boulevard, Suite 1001</p>
          <p>Elegance District</p>
          <p>Opulent City, Luxuria 12345</p>
          <p>Exquisite Realm</p>
        </div>
        <div class="footer-logo-container">
          <img class="footer-logo" src="https://i.postimg.cc/d3pNGXPN/7c6764b8-de90-474c-9b98-05019aef3193.png"
            alt="footer-logo">
        </div>
        <div class="footer-text2">
          <p>Nhân viên lễ tân:</p>
          <p>+84 (VN) 123-4567</p>
          <p>Nhân viên lễ tân 2: </p>
          <p>+84 (VN) 123-4567</p>
          <p>Nhân viên lễ tân 3: </p>
          <p>+84 (VN) 123-4567</p>
          
        </div>
      </div>
      <div id="footer-navbar">
        <nav id="footer-navbar">
          <a class="footer-nav-link" href="#welcome-section">Đặt Phòng</a>
          <p>│</p>
         
          <a class="footer-nav-link" href="#profile-link">About us</a>
          <p>│</p>
          <a class="footer-nav-link" href="#profile-link">Home</a>
          
        </nav>
      </div>
      <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3753.7506646911206!2d105.77672157425476!3d19.808166228581847!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3136f9a98875450b%3A0x9da52c446fa634a0!2zS2jDoWNoIFPhuqFuIFBoxrDhu6NuZyBIb8OgbmcgMw!5e0!3m2!1svi!2s!4v1750238720851!5m2!1svi!2s" width="100%" height="100" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
      <p class="copyright">© HXH Hotel 2025</p>
    </footer>


</template>
<style scoped>
@import url('https://fonts.googleapis.com/css?family=Poppins:400,500,600,700&display=swap');

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: 'Poppins', sans-serif;
}



.footer {
  background-color: #3baae6;
  color: white;
  padding: 20px;
  text-align: center;
  margin-top: 0px;

}

footer p {
  margin: 1;
  /* Remove default margin for paragraphs inside the footer */
}

footer a {
  color: white;
  /* Adjust link color in the footer */
  text-decoration: none;
  /* Remove underline from links */
  font-weight: bold;
  /* Optionally, make the links bold */
}

.footer-logo {
  width: 150px;
  /* Adjust the width as needed */
  height: auto;
  /* Allow the logo to adjust its height while maintaining the aspect ratio */
}

.footer-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.footer-text1 {
  flex: 1;
  text-align: right;
  font-size: 12px;
}

.footer-text2 {
  flex: 1;
  text-align: left;
  font-size: 12px;
}

.footer-text1 {
  margin-right: 10px;
  /* Adjusted margin to reduce space between text and logo */
}

.footer-text2 {
  margin-left: 10px;
  /* Adjusted margin to reduce space between text and logo */
}

.footer-logo-container {
  flex-basis: 150px;
  /* Set a basis for flex item to ensure it doesn't grow too much */
  flex-shrink: 0;
  /* Disable shrinking to prevent the logo from getting smaller */
}

#footer-navbar {
  display: flex;
  justify-content: center;
  /* Center the links horizontally */
  align-items: center;
  /* Center the links vertically */
  margin-top: 10px;
  background-color: #3baae6;
  /* Set the background color for the footer navbar */
}

.footer-nav-link {
  text-decoration: none;
  color: white;
  margin: 0 15px;
  /* Adjusted margin for spacing between links */
  font-weight: 400;
}

.copyright {
  margin-top: 20px;
  /* Adjusted margin for spacing */
}
</style>