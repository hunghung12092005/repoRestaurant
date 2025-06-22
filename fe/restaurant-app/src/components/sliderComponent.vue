<template>
    <div class="slider">
        <div class="slide-track">
            <div class="slide">
                <img src="https://rubee.com.vn/admin/webroot/upload/image/images/bai-viet/logo-tr%C3%A0-s%E1%BB%AFa-tocotoco.jpg" height="100" width="250" alt="" />
            </div>
            <div class="slide">
                <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQrrIGU54LtGQBiuNArjH-DpkfIKL5O4DVjHXDLtJIY0hyulkvBOurunDwj34t4irlk-G0&usqp=CAU" height="100" width="50%" alt="" />
            </div>
            <div class="slide">
                <img src="https://mixueviet.com/wp-content/uploads/2024/05/Img-Mixue-VN.png" height="100" width="10%" alt="" />
            </div>
            <div class="slide">
                <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQm1NtydsflfrTJHNkcYDkZ6D6j3Z5y_S5ADg&s" height="100" width="250" alt="" />
            </div>
            <div class="slide">
                <img src="https://ib.vib.com.vn/banners/Promotion/20241209134441503_logo_Panasonic_.png" height="100" width="250" alt="" />
            </div>
            <!-- <div class="slide">
                <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSIK9SylxDSKjUsuNaWB6Kts5JD8dFWt6UA6A&s" height="100" width="250" alt="" />
            </div> -->
            <div class="slide">
                <img src="https://cdn.prod.website-files.com/5fb85f26f126ce08d792d2d9/6453fb70c3d3d020845387fe_New_XANH_SM.jpg" height="100" width="250" alt="" />
            </div>
            <div class="slide">
                <img src="https://rubee.com.vn/admin/webroot/upload/image/images/bai-viet/logo-tr%C3%A0-s%E1%BB%AFa-tocotoco.jpg" height="100" width="250" alt="" />
            </div>
            <div class="slide">
                <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQrrIGU54LtGQBiuNArjH-DpkfIKL5O4DVjHXDLtJIY0hyulkvBOurunDwj34t4irlk-G0&usqp=CAU" height="100" width="50%" alt="" />
            </div>
            <div class="slide">
                <img src="https://mixueviet.com/wp-content/uploads/2024/05/Img-Mixue-VN.png" height="100" width="10%" alt="" />
            </div>
            <div class="slide">
                <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQm1NtydsflfrTJHNkcYDkZ6D6j3Z5y_S5ADg&s" height="100" width="250" alt="" />
            </div>
            <div class="slide">
                <img src="https://ib.vib.com.vn/banners/Promotion/20241209134441503_logo_Panasonic_.png" height="100" width="250" alt="" />
            </div>
            <!-- <div class="slide">
                <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSIK9SylxDSKjUsuNaWB6Kts5JD8dFWt6UA6A&s" height="100" width="250" alt="" />
            </div> -->
            <div class="slide">
                <img src="https://cdn.prod.website-files.com/5fb85f26f126ce08d792d2d9/6453fb70c3d3d020845387fe_New_XANH_SM.jpg" height="100" width="250" alt="" />
            </div>
        </div>
    </div>
</template>

<style scoped>
/* slider quảng cáo */
body {
    align-items: center;
    background: #E3E3E3;
    display: flex;
    height: 100vh;
    justify-content: center;
}

@-webkit-keyframes scroll {
    0% {
        transform: translateX(0);
    }

    100% {
        transform: translateX(calc(-250px * 7));
    }
}

@keyframes scroll {
    0% {
        transform: translateX(0);
    }

    100% {
        transform: translateX(calc(-250px * 7));
    }
}

.slider {
    background: white;
    box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.125);
    height: 100px;
    margin: auto;
    overflow: hidden;
    position: relative;
    width: 100%;
}

.slider::before,
.slider::after {
    background: linear-gradient(to right, white 0%, rgba(255, 255, 255, 0) 100%);
    content: "";
    height: 100px;
    position: absolute;
    width: 200px;
    z-index: 2;
}

.slider::after {
    right: 0;
    top: 0;
    transform: rotateZ(180deg);
}

.slider::before {
    left: 0;
    top: 0;
}

.slider .slide-track {
    -webkit-animation: scroll 80s linear infinite;
    animation: scroll 90s linear infinite;
    display: flex;
    width: calc(350px * 14);
}

.slider .slide {
    height: 150px;
    width: 180px;
}
.slide img{
    width: 90%;
    /* height: 80%; */
}
/* end quảng cáo */ 
</style>