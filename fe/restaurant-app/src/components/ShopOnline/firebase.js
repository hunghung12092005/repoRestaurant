// firebase.js
import { initializeApp } from 'firebase/app';
import { getAuth, RecaptchaVerifier, PhoneAuthProvider } from 'firebase/auth';

const firebaseConfig = {
  apiKey: "AIzaSyBCesMu43nXn7aBW41YBJWN4f15fiVVrbI",
  authDomain: "b-vvc-e0984.firebaseapp.com",
  projectId: "b-vvc-e0984",
  storageBucket: "b-vvc-e0984.firebasestorage.app",
  messagingSenderId: "528374300366",
  appId: "1:528374300366:web:5e730a864afd19df939a9f",
  measurementId: "G-K8PE42X9CC"
};

const app = initializeApp(firebaseConfig);
const auth = getAuth(app); // Đảm bảo auth được khởi tạo đúng

export { auth, RecaptchaVerifier, PhoneAuthProvider };