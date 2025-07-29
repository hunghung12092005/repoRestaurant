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
// For Firebase JS SDK v7.20.0 and later, measurementId is optional
// const firebaseConfig = {
//   apiKey: "AIzaSyB3j5q6mTg7VmyYCJaFQSMO7822f4U2gPs",
//   authDomain: "projecthxh-667e3.firebaseapp.com",
//   projectId: "projecthxh-667e3",
//   storageBucket: "projecthxh-667e3.firebasestorage.app",
//   messagingSenderId: "275055873317",
//   appId: "1:275055873317:web:a3dc3447c37c8581ea5ecc",
//   measurementId: "G-XH7HR2DRD1"
// };
const app = initializeApp(firebaseConfig);
const auth = getAuth(app); // Đảm bảo auth được khởi tạo đúng

export { auth, RecaptchaVerifier, PhoneAuthProvider };