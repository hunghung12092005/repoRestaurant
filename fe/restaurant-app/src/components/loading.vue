<template>
  <div class="hxh-spinner-overlay">
    <div class="hxh-loader-container">
      <img src="https://i.postimg.cc/d3pNGXPN/7c6764b8-de90-474c-9b98-05019aef3193.png" 
           alt="HXH Hotel Logo" 
           class="hxh-loading-logo">
    </div>
  </div>
</template>

<script>
export default {
    name: 'HXHSpinnerOverlay'
}
</script>

<style scoped>
/* Import font for consistent branding */
@import url('https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;700&display=swap');

/* --- Spinner Overlay (Background Dimming) --- */
.hxh-spinner-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(35, 36, 32, 0.8); /* Slightly more opaque for focus */
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    backdrop-filter: blur(6px); /* Moderate blur effect */
    -webkit-backdrop-filter: blur(6px);
    animation: hxh-overlay-fade-in 1s ease-out forwards; /* Smooth fade-in */
}

/* --- Loader Container (The box holding logo and text) --- */
.hxh-loader-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background-color: rgba(20, 20, 20, 0.75); /* Dark, slightly transparent background */
    padding: 40px 60px; /* Ample padding for a premium feel */
    border-radius: 12px; /* Softly rounded corners */
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.6); /* Prominent shadow */
    border: 1px solid rgba(255, 255, 255, 0.15); /* Subtle border */
    animation: hxh-container-pop-up 0.7s cubic-bezier(0.68, -0.55, 0.265, 1.55) forwards; /* Pop-up with slight bounce */
}

/* --- HXH Hotel Loading Logo (Main animation target) --- */
.hxh-loading-logo {
    width: 160px; /* Larger size for prominence */
    height: auto;
    display: block; /* Remove extra space below image */
    margin-bottom: 30px; /* Space between logo and text */
    
    /* Core Loading Animations */
    animation: 
        hxh-logo-pulse 1s infinite ease-in-out, /* Subtle pulsating */
        hxh-logo-glow 2s infinite alternate ease-in-out, /* Ethereal glowing */
        hxh-logo-spin 6s linear infinite; /* Gentle continuous spin */
    
    /* Initial glow for better visibility */
    filter: drop-shadow(0 0 15px rgba(212, 175, 55, 0.7)); 
    will-change: transform, filter; /* Optimize for animation performance */
}

/* --- Loading Text --- */
.hxh-loading-text {
    font-family: 'Cormorant Garamond', serif;
    color: #D4AF37; /* Gold color */
    font-size: 1.8rem; /* Prominent text size */
    font-weight: 600;
    text-shadow: 0 0 12px rgba(212, 175, 55, 0.7); /* Matching glow for text */
    animation: hxh-text-fade 2s infinite alternate ease-in-out; /* Subtle text fade */
}

/* --- Keyframe Animations --- */

/* Overlay initial appearance */
@keyframes hxh-overlay-fade-in {
  from { opacity: 0; }
  to { opacity: 1; }
}

/* Container pop-up with bounce effect */
@keyframes hxh-container-pop-up {
  0% { transform: scale(0.7); opacity: 0; }
  70% { transform: scale(1.05); opacity: 1; }
  100% { transform: scale(1); opacity: 1; }
}

/* Logo pulsating effect (slight scale change) */
@keyframes hxh-logo-pulse {
  0% { transform: scale(1); }
  50% { transform: scale(1.03); } /* Gently scales up */
  100% { transform: scale(1); }
}

/* Logo glowing effect (changing drop-shadow intensity) */
@keyframes hxh-logo-glow {
  0%, 100% { filter: drop-shadow(0 0 10px rgba(212, 175, 55, 0.6)); } /* Softer glow */
  50% { filter: drop-shadow(0 0 30px rgba(212, 175, 55, 1)); } /* Brighter, more intense glow */
}

/* New: Logo continuous subtle spin */
@keyframes hxh-logo-spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); } /* Full rotation */
}

/* Loading text subtle fade (opacity change) */
@keyframes hxh-text-fade {
  0% { opacity: 0.6; }
  50% { opacity: 1; }
  100% { opacity: 0.6; }
}

/* Responsive adjustments for smaller screens */
@media (max-width: 768px) {
  .hxh-loading-logo {
    width: 120px;
    margin-bottom: 25px;
  }
  .hxh-loading-text {
    font-size: 1.5rem;
  }
  .hxh-loader-container {
    padding: 30px 50px;
  }
}

@media (max-width: 480px) {
  .hxh-loading-logo {
    width: 90px;
    margin-bottom: 20px;
  }
  .hxh-loading-text {
    font-size: 1.2rem;
  }
  .hxh-loader-container {
    padding: 25px 40px;
  }
}
</style>