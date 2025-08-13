<template>
  <div class="hxh-spinner-overlay">
    <div class="hxh-loader-container">
      <div class="hxh-logo-wrapper">
        <img src="/logo/logo_HXH.png" 
             alt="HXH Hotel Logo" 
             class="hxh-loading-logo">
        <div class="hxh-logo-aura"></div>
      </div>
      <div class="hxh-loading-progress">
        <div class="hxh-progress-bar"></div>
      </div>
      <p class="hxh-loading-text">HXH Hotel - Luxury Awaits</p>
    </div>
  </div>
</template>

<script>
export default {
    name: 'HXHSpinnerOverlay'
}
</script>

<style scoped>
/* Import elegant fonts */
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600&family=Montserrat:wght@300;400&display=swap');

/* --- Spinner Overlay --- */
.hxh-spinner-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(245, 245, 245, 0.96); /* Nền sáng hơn */
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    animation: hxh-overlay-fade-in 0.8s cubic-bezier(0.19, 1, 0.22, 1) forwards;
}

/* --- Loader Container --- */
.hxh-loader-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(240, 240, 240, 0.98) 100%); /* Nền container sáng */
    padding: 50px 70px;
    border-radius: 16px;
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15); /* Đổ bóng nhẹ nhàng */
    border: 1px solid rgba(212, 175, 55, 0.3); /* Viền vàng nhạt */
    position: relative;
    overflow: hidden;
    animation: hxh-container-pop-up 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
}

.hxh-loader-container::before {
    content: '';
    position: absolute;
    top: -2px;
    left: -2px;
    right: -2px;
    bottom: -2px;
    background: linear-gradient(45deg, 
                rgba(212, 175, 55, 0) 0%, 
                rgba(212, 175, 55, 0.15) 30%, 
                rgba(212, 175, 55, 0) 70%);
    z-index: -1;
    animation: hxh-border-glow 3s linear infinite;
}

/* --- Logo Wrapper with Aura Effect --- */
.hxh-logo-wrapper {
    position: relative;
    width: 180px;
    height: 180px;
    margin-bottom: 40px;
}

.hxh-loading-logo {
    width: 100%;
    height: auto;
    position: relative;
    z-index: 2;
    animation: hxh-logo-float 3s ease-in-out infinite;
    filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.1)); /* Đổ bóng nhẹ cho logo */
}

.hxh-logo-aura {
    position: absolute;
    top: -20%;
    left: -20%;
    width: 140%;
    height: 140%;
    background: radial-gradient(circle, rgba(212, 175, 55, 0.1) 0%, rgba(212, 175, 55, 0) 70%);
    border-radius: 50%;
    animation: hxh-aura-pulse 4s ease-in-out infinite;
    z-index: 1;
}

/* --- Progress Bar --- */
.hxh-loading-progress {
    width: 200px;
    height: 2px;
    background-color: rgba(0, 0, 0, 0.08); /* Màu nền thanh progress nhạt */
    margin: 30px 0;
    overflow: hidden;
    position: relative;
}

.hxh-progress-bar {
    position: absolute;
    left: 0;
    top: 0;
    height: 100%;
    width: 0;
    background: linear-gradient(90deg, 
                rgba(212, 175, 55, 0) 0%, 
                rgba(212, 175, 55, 0.8) 50%, 
                rgba(212, 175, 55, 0) 100%);
    animation: hxh-progress-loading 2.5s ease-in-out infinite;
}

/* --- Loading Text --- */
.hxh-loading-text {
    font-family: 'Playfair Display', serif;
    color: #B08D57; /* Màu vàng đậm nhẹ hơn */
    font-size: 1.4rem;
    font-weight: 600;
    letter-spacing: 1px;
    margin-top: 20px;
    text-transform: uppercase;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    animation: hxh-text-flicker 3s ease-in-out infinite;
}

/* --- Keyframe Animations --- */
@keyframes hxh-overlay-fade-in {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes hxh-container-pop-up {
    0% { transform: translateY(20px) scale(0.95); opacity: 0; }
    100% { transform: translateY(0) scale(1); opacity: 1; }
}

@keyframes hxh-logo-float {
    0%, 100% { transform: translateY(0) rotate(0deg); }
    50% { transform: translateY(-15px) rotate(2deg); }
}

@keyframes hxh-aura-pulse {
    0%, 100% { transform: scale(0.95); opacity: 0.7; }
    50% { transform: scale(1.1); opacity: 1; }
}

@keyframes hxh-progress-loading {
    0% { left: -100%; width: 30%; }
    50% { width: 40%; }
    100% { left: 100%; width: 30%; }
}

@keyframes hxh-border-glow {
    0% { transform: translateX(-100%) translateY(-100%); }
    100% { transform: translateX(100%) translateY(100%); }
}

@keyframes hxh-text-flicker {
    0%, 19%, 21%, 23%, 25%, 54%, 56%, 100% { opacity: 1; }
    20%, 22%, 24%, 55% { opacity: 0.8; }
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .hxh-loader-container {
        padding: 40px 50px;
    }
    .hxh-logo-wrapper {
        width: 140px;
        height: 140px;
        margin-bottom: 30px;
    }
    .hxh-loading-progress {
        width: 180px;
    }
    .hxh-loading-text {
        font-size: 1.2rem;
    }
}

@media (max-width: 480px) {
    .hxh-loader-container {
        padding: 30px 40px;
        border-radius: 12px;
    }
    .hxh-logo-wrapper {
        width: 100px;
        height: 100px;
        margin-bottom: 25px;
    }
    .hxh-loading-progress {
        width: 150px;
        margin: 20px 0;
    }
    .hxh-loading-text {
        font-size: 1rem;
        margin-top: 15px;
    }
}
</style>