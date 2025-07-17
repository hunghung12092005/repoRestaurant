<template>
  <div class="hxh-spinner-overlay">
    <div class="hxh-loader-container">
      <img src="https://i.postimg.cc/d3pNGXPN/7c6764b8-de90-474c-9b98-05019aef3193.png" 
           alt="HXH Hotel Logo" 
           class="hxh-loading-logo">
      <p class="hxh-loading-text">Đang tải, xin vui lòng chờ...</p>
    </div>
  </div>
</template>

<script>
export default {
    name: 'HXHSpinnerOverlay'
}
</script>

<style scoped>
/* Optional: Import a hotel-appropriate font if not already globally imported */
@import url('https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;700&display=swap');

.hxh-spinner-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(35, 36, 32, 0.85); /* Slightly more opaque for stronger effect */
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    backdrop-filter: blur(5px); /* Increased blur for more dramatic effect */
    -webkit-backdrop-filter: blur(5px);
    animation: hxh-overlay-appear 0.5s ease-out forwards; /* Fade in the overlay */
}

.hxh-loader-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background-color: rgba(20, 20, 20, 0.7);
    padding: 30px 40px;
    border-radius: 10px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.5);
    border: 1px solid rgba(255, 255, 255, 0.1);
    animation: hxh-container-zoom 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55) forwards; /* Pop-in effect */
}

.hxh-loading-logo {
    width: 120px;
    height: auto;
    animation: 
        hxh-logo-pulse 2s infinite ease-in-out, /* Existing pulse */
        hxh-logo-glow 2s infinite alternate ease-in-out; /* New: Subtle glow */
    filter: drop-shadow(0 0 10px rgba(212, 175, 55, 0.5));
    margin-bottom: 20px;
}

.hxh-loading-text {
    font-family: 'Cormorant Garamond', serif;
    color: #D4AF37;
    font-size: 1.5rem;
    font-weight: 600;
    text-shadow: 0 0 8px rgba(212, 175, 55, 0.5);
    animation: hxh-text-fade 2s infinite alternate ease-in-out;
}

/* Keyframe Animations */

/* Overlay initial appearance */
@keyframes hxh-overlay-appear {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

/* Container pop-in effect */
@keyframes hxh-container-zoom {
  0% {
    transform: scale(0.8);
    opacity: 0;
  }
  100% {
    transform: scale(1);
    opacity: 1;
  }
}

/* Existing logo pulse (slight modification to remove opacity from here as it's in new glow) */
@keyframes hxh-logo-pulse {
  0% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.05);
  }
  100% {
    transform: scale(1);
  }
}

/* New: Logo subtle glow */
@keyframes hxh-logo-glow {
  0% {
    filter: drop-shadow(0 0 5px rgba(212, 175, 55, 0.4));
  }
  50% {
    filter: drop-shadow(0 0 15px rgba(212, 175, 55, 0.8)); /* Brighter glow */
  }
  100% {
    filter: drop-shadow(0 0 5px rgba(212, 175, 55, 0.4));
  }
}

/* Existing text fade */
@keyframes hxh-text-fade {
  0% {
    opacity: 0.7;
  }
  50% {
    opacity: 1;
  }
  100% {
    opacity: 0.7;
  }
}

/* Responsive adjustments for smaller screens */
@media (max-width: 768px) {
  .hxh-loading-logo {
    width: 100px;
  }
  .hxh-loading-text {
    font-size: 1.3rem;
  }
  .hxh-loader-container {
    padding: 25px 35px;
  }
}

@media (max-width: 480px) {
  .hxh-loading-logo {
    width: 80px;
  }
  .hxh-loading-text {
    font-size: 1.1rem;
  }
  .hxh-loader-container {
    padding: 20px 30px;
  }
}
</style>