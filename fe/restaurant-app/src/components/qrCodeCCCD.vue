<template>
  <div v-if="isLoading"></div>
    <div>
      <h1>QR Code Scanner & Tải Ảnh</h1>
      <button @click="startScanning">Quét QR</button>
      <div id="reader" style="width: 600px; height: 400px; display: none;"></div>
  
      <input type="file" @change="onFileChange" accept="image/*" />
      <button @click="uploadImage">Tải Ảnh Lên</button>
  
      <div v-if="result">
        <h2>Kết quả:</h2>
        <pre>{{ result }}</pre>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref, onMounted } from 'vue';
  import { Html5Qrcode } from 'html5-qrcode';
  import loading from './loading.vue';
  const isLoading = ref(false);
  const result = ref(null);
  const apiKey = 'XXjjI5g9j7gk4NcZE9Dh9PPLCrvrR6zJ'; 
  const imageFile = ref(null);
  let html5QrCode = null;
  
  const onScanSuccess = (decodedText) => {
    sendImageToApi(decodedText);
    stopScanning();
  };
  
  const sendImageToApi = async (imageData) => {
    isLoading.value = true;
    try {
      const formData = new FormData();
      formData.append('image', imageData);
  
      const response = await fetch('https://api.fpt.ai/vision/idr/vnm/', {
        method: 'POST',
        headers: {
          api_key: apiKey,
        },
        body: formData,
      });
  
      const data = await response.json();
      result.value = data;
    } catch (error) {
      console.error('Lỗi:', error);
    }finally{
      isLoading.value = false;

    }
  };
  
  const onFileChange = (event) => {
    const file = event.target.files[0];
    if (file) {
      imageFile.value = file;
    }
  };
  
  const uploadImage = () => {
    if (imageFile.value) {
      sendImageToApi(imageFile.value);
    }
  };
  
  const startScanning = () => {
    const reader = document.getElementById('reader');
    reader.style.display = 'block'; // Hiện div quét QR
    html5QrCode.start(
      { facingMode: "environment" },
      { fps: 10, qrbox: { width: 250, height: 250 } },
      onScanSuccess,
      (errorMessage) => { console.warn(`Lỗi quét: ${errorMessage}`); }
    ).catch(err => {
      console.error(`Lỗi khởi động: ${err}`);
    });
  };
  
  const stopScanning = () => {
    if (html5QrCode) {
      html5QrCode.stop().then(() => {
        const reader = document.getElementById('reader');
        reader.style.display = 'none'; // Ẩn div quét QR
      }).catch(err => {
        console.error(`Lỗi dừng quét: ${err}`);
      });
    }
  };
  
  onMounted(() => {
    html5QrCode = new Html5Qrcode("reader");
  });
  </script>
  
  <style scoped>
  /* Thêm CSS nếu cần */
  </style>