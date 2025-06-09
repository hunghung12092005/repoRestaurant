<template>
  <loading v-if="isLoading" />
  <div class="receipt">
    <p class="shop-name">UI Market</p>
    <p class="info">
      1234 Market Street, Suite 101<br />
      City, State ZIP<br />
      Date: 13<!-- {{ currentDateTime }} --><br />
    </p>
    <table>
      <thead>
        <tr>
          <th>Item</th>
          <th>Qty</th>
          <th>Price</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>adad</td>
          <td>
            <input type="range" min="1" max="5" />
          </td>
          <td>$1331</td>
        </tr>

      </tbody>
    </table>

    <div class="total">
      <p>Total:</p>
      <p>$45.00</p>
    </div>

    <p class="thanks">Thank you for shopping with us!</p>
    <div>
      <!-- <button @click="showPopup">Hiển thị OTP Popup</button> -->
      <!-- form gửi otp -->
      <form v-if="isFormOTP" @submit.prevent="sendVerificationCode">
        <!-- <input v-model="phoneNumber" type="text" placeholder="Nhập số điện thoại" required /> -->
        <!-- From Uiverse.io by Galahhad -->
        <div class="ui-wrapper">
          <input checked="" id="Austria" name="flag" type="radio">
          <input class="dropdown-checkbox" name="dropdown" id="dropdown" type="checkbox">
          <label class="dropdown-container" for="dropdown"></label>
          <div class="input-wrapper">
            <div class="textfield">
              <input v-model="phoneNumber" pattern="\d+" maxlength="9" id="phonenumber" type="text"
                placeholder="Nhập số điện thoại"> <span class="invalid-msg">This is not a valid phone number</span>
            </div>
          </div>
          <div class="select-wrapper">
            <ul>
              <li class="Austria"><label for="Austria"><span>VN</span>VietNam (+84)</label></li>
            </ul>
          </div>
        </div>
        <button type="submit" class="btn btn-info m-2">Gửi mã xác nhận</button>
      </form>
      <!-- popup xác nhận otp -->
      <div>
        <div v-if="isPopupVisible" class="overlay">
          <form class="otp-Form" @submit.prevent="verifyCode">
            <span class="mainHeading">Enter OTP</span>
            <p class="otpSubheading">We have sent a verification code to your mobile number</p>
            <div class="inputContainer">
              <input required maxlength="1" type="text" class="otp-input" id="otp-input1" v-model="otpInputs[0]"
                @input="handleInput(0)">
              <input required maxlength="1" type="text" class="otp-input" id="otp-input2" v-model="otpInputs[1]"
                @input="handleInput(1)">
              <input required maxlength="1" type="text" class="otp-input" id="otp-input3" v-model="otpInputs[2]"
                @input="handleInput(2)">
              <input required maxlength="1" type="text" class="otp-input" id="otp-input4" v-model="otpInputs[3]"
                @input="handleInput(3)">
              <input required maxlength="1" type="text" class="otp-input" id="otp-input5" v-model="otpInputs[4]"
                @input="handleInput(4)">
              <input required maxlength="1" type="text" class="otp-input" id="otp-input6" v-model="otpInputs[5]"
                @input="handleInput(5)">
            </div>
            <button class="verifyButton" type="submit">Verify</button>
            <button type="button" class="exitBtn" @click="closePopup">×</button>
            <p class="resendNote">Didn't receive the code? <button type="button" class="resendBtn"
                @click="sendVerificationCode">Resend Code</button></p>
          </form>
        </div>
      </div>
      <!-- Nút xác nhận giao hàng -->
      <div v-if="isOrderConfirmed" class="btn-main m-5">
        Xác Nhận Đặt Hàng
      </div>

      <!-- <form v-if="verificationId" @submit.prevent="verifyCode">
        <input v-model="verificationCode" type="text" placeholder="Nhập mã xác nhận" required />
        <button type="submit">Xác nhận</button>
      </form> -->

      <div id="recaptcha-container"></div> <!-- ReCAPTCHA -->
    </div>
  </div>
</template>
<script setup>
import { ref } from 'vue';
import { auth, RecaptchaVerifier, PhoneAuthProvider } from './firebase';
import { signInWithPhoneNumber, signInWithCredential } from 'firebase/auth';
import loading from '../loading.vue';
const phoneNumber = ref('');
const isFormOTP = ref(true);
//const verificationCode = ref('');
const verificationId = ref(null);
const isLoading = ref(false);
const otpInputs = ref(Array(6).fill(''));
const isPopupVisible = ref(false);
const showPopup = () => {
  isPopupVisible.value = true;
};

//đóng popup
const closePopup = () => {
  isPopupVisible.value = false;
  otpInputs.value = Array(6).fill(''); // Clear OTP inputs
};
//tự động nhảy số qua ô tiếp theo khi nhập
const handleInput = (index) => {
  // Nếu ô nhập có giá trị, chuyển đến ô tiếp theo
  if (otpInputs.value[index].length === 1 && index < otpInputs.value.length - 1) {
    otpInputs.value[index + 1] = ''; // Clear next input
    // Tìm ô tiếp theo và focus vào ô đó
    const nextInput = document.querySelector(`#otp-input${index + 2}`);
    if (nextInput) {
      nextInput.focus();
    }
  }
  // Nếu ô nhập trống và không phải là ô đầu tiên, quay lại ô trước
  if (otpInputs.value[index].length === 0 && index > 0) {
    const prevInput = document.querySelector(`#otp-input${index}`);
    if (prevInput) {
      prevInput.focus();
    }
  }
};

const sendVerificationCode = async () => {
  isPopupVisible.value = true;
  isLoading.value = true; // Bắt đầu quá trình tải
  try {
    // Kiểm tra xem auth có được khởi tạo đúng cách
    if (!auth) {
      throw new Error('auth chưa được khởi tạo. Kiểm tra cấu hình Firebase.');
    }

    // Khởi tạo RecaptchaVerifier
    const appVerifier = new RecaptchaVerifier(auth, 'recaptcha-container', {
      size: 'invisible',
      callback: (response) => {
        //console.log('ReCAPTCHA đã được xác minh:', response);
      },
      'expired-callback': () => {
        //console.warn('ReCAPTCHA đã hết hạn.');
      },
    });
    // Thêm +84 vào trước số điện thoại
    const fullPhoneNumber = `+84${phoneNumber.value}`;
    const confirmationResult = await signInWithPhoneNumber(auth, fullPhoneNumber, appVerifier);
    verificationId.value = confirmationResult.verificationId;
    //alert('Mã xác nhận đã được gửi thành công! Vui lòng kiểm tra tin nhắn.');
  } catch (error) {
    console.error('Lỗi gửi mã xác nhận:', error.message || error);
    alert(`Lỗi gửi mã xác nhận: ${error.message || error}`);
  } finally {
    isLoading.value = false; // Kết thúc quá trình tải
  }
};
const isOrderConfirmed = ref(false);
const verifyCode = async () => {
  isLoading.value = true;
  try {
    const otpCode = otpInputs.value.join('');//chuyển mảng thành chuỗi
    const credential = PhoneAuthProvider.credential(verificationId.value, otpCode);
    const result = await signInWithCredential(auth, credential);
    alert('Xác nhận thành công!');
    closePopup(); // Đóng popup sau khi xác nhận thành công
    isFormOTP.value = false; //ẩn form  hiển thị otp
    isOrderConfirmed.value = true; // Đặt trạng thái đơn hàng đã được xác nhận
    // Thực hiện hành động tiếp theo
  } catch (error) {
    console.error('Lỗi xác minh mã:', error.message || error);
    alert(`Lỗi xác minh mã: ${error.message || error}`);
  } finally{
    isLoading.value = false;
  }
};
</script>
<style scoped>
/* From Uiverse.io by Galahhad */
.ui-wrapper {
  --width: 250px;
  --height: 50px;
  --background: #fff;
  --text-color: rgb(73, 73, 73);
  --border-color: rgb(185, 184, 184);
  --border-focus-color: rgb(0, 110, 255);
  --shadow-color: rgba(34, 60, 80, 0.2);
  --shadow-focus-color: rgba(0, 110, 255, 0.2);
  --dropdown-button-color: #e6e6e6;
  --dropdown-button-hover-color: #dad9d9;
}

.ui-wrapper *,
.ui-wrapper *::before,
.ui-wrapper *::after {
  -webkit-box-sizing: border-box;
  box-sizing: border-box;
  font-family: sans-serif;
  color: var(--text-color);
}

.ui-wrapper {
  width: var(--width);
  height: var(--height);
  display: -webkit-inline-box;
  display: -ms-inline-flexbox;
  display: inline-flex;
  border-radius: 10px;
  position: relative;
  border: 1px solid var(--border-color);
  background-color: var(--background);
  -webkit-box-pack: justify;
  -ms-flex-pack: justify;
  justify-content: space-between;
  padding-right: 10px;
  -webkit-box-shadow: 0px 2px 5px 0px var(--shadow-color);
  box-shadow: 0px 2px 5px 0px var(--shadow-color);
  -webkit-transition: .4s;
  -o-transition: .4s;
  transition: .4s;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
}

.ui-wrapper>input {
  -webkit-appearance: none;
  -moz-appearance: none;
  appearance: none;
  margin: 0;
  width: 0;
  height: 0;
  opacity: 0;
  position: absolute;
  left: 9999px;
}

.dropdown-container {
  display: -webkit-box;
  display: -ms-flexbox;
  display: flex;
  -webkit-box-pack: center;
  -ms-flex-pack: center;
  justify-content: center;
  -webkit-box-align: center;
  -ms-flex-align: center;
  align-items: center;
  padding: 0 10px;
  cursor: pointer;
  border-radius: 9px 0 0 9px;
  background-color: var(--dropdown-button-color);
}

.dropdown-container::before {
  content: "🇦🇹";
  font-size: 20px;
  background: none !important;
}

.dropdown-container::after {
  content: "";
  background-image: url("data:image/svg+xml;base64,PHN2ZyBmaWxsPSIjNDk0OTQ5IiB3aWR0aD0iNzAwcHQiIGhlaWdodD0iNzAwcHQiIHZlcnNpb249IjEuMSIgdmlld0JveD0iMCAwIDcwMCA3MDAiCiAgICB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIj4KICAgIDxwYXRoCiAgICAgICAgZD0ibTM4MC4zOSA0ODQuNzkgMzA3LjA0LTMwNS45OWMxNi43NjYtMTcuODEyIDE2Ljc2Ni00NS4wNTkgMC02MS44MjgtMTYuNzY2LTE2Ljc2Ni00NS4wNTktMTYuNzY2LTYxLjgyOCAwbC0yNzUuNiAyNzUuNi0yNzUuNi0yNzUuNmMtMTcuODEyLTE2Ljc2Ni00NS4wNTktMTYuNzY2LTYxLjgyOCAwLTE2Ljc2NiAxNi43NjYtMTYuNzY2IDQ0LjAxMiAwIDYxLjgyOGwzMDUuOTkgMzA1Ljk5YzE3LjgxMiAxNi43NjYgNDUuMDU5IDE2Ljc2NiA2MS44MjggMHoiCiAgICAgICAgZmlsbC1ydWxlPSJjdXJyZW50Q29sb3IiIC8+Cjwvc3ZnPg==");
  width: 12px;
  height: 12px;
  background-position: center;
  background-size: cover;
  margin-left: 5px;
  -webkit-transition: .2s;
  -o-transition: .2s;
  transition: .2s;
}

.select-wrapper {
  width: var(--width);
  position: absolute;
  top: calc(var(--height) + 20px);
  left: 0;
  opacity: 0;
  visibility: hidden;
  -webkit-transition: .2s;
  -o-transition: .2s;
  transition: .2s;
}

.select-wrapper ul {
  width: 100%;
  background-color: white;
  border-radius: 10px;
  padding: 10px;
  margin: 0;
  list-style: none;
  height: 300px;
  display: -webkit-inline-box;
  display: -ms-inline-flexbox;
  display: inline-flex;
  -webkit-box-orient: vertical;
  -webkit-box-direction: normal;
  -ms-flex-direction: column;
  flex-direction: column;
  -webkit-box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
  overflow-y: auto;
  white-space: nowrap;
}

.select-wrapper ul li {
  display: -webkit-box;
  display: -ms-flexbox;
  display: flex;
  -webkit-box-align: center;
  -ms-flex-align: center;
  align-items: center;
  padding: 5px;
  border-radius: 5px;
}

.select-wrapper ul li label {
  width: 100%;
}

.select-wrapper ul li,
.select-wrapper ul li * {
  cursor: pointer;
}

.select-wrapper ul li:hover {
  background: lightgray;
}

.select-wrapper ul li span {
  display: inline-block;
  margin-right: 15px;
}

.input-wrapper {
  width: 100%;
  padding-left: 10px;
  display: -webkit-box;
  display: -ms-flexbox;
  display: flex;
  -webkit-box-orient: vertical;
  -webkit-box-direction: normal;
  -ms-flex-direction: column;
  flex-direction: column;
  -webkit-box-pack: center;
  -ms-flex-pack: center;
  justify-content: center;
  row-gap: 2px;
}

.input-wrapper legend {
  font-size: 11px;
}

.input-wrapper .textfield {
  width: 100%;
  display: -webkit-box;
  display: -ms-flexbox;
  display: flex;
  -webkit-box-align: center;
  -ms-flex-align: center;
  align-items: center;
}

.input-wrapper .textfield::before {
  content: "+31";
  margin-right: 5px;
  white-space: nowrap;
}

.input-wrapper .textfield input {
  width: 100%;
  font-size: 16px;
  outline: none;
  border: none;
  background: none;
}

.invalid-msg {
  font-size: 12px;
  position: absolute;
  color: red;
  top: 115%;
  left: 0;
  opacity: 0;
  visibility: hidden;
  -webkit-transition: .2s;
  -o-transition: .2s;
  transition: .2s;
}

/* actions */

#phonenumber:invalid+.invalid-msg {
  top: 110%;
  opacity: 1;
  visibility: visible;
}

.ui-wrapper:focus-within {
  border-color: var(--border-focus-color);
  -webkit-box-shadow: 0px 2px 5px 0px var(--shadow-focus-color);
  box-shadow: 0px 2px 5px 0px var(--shadow-focus-color);
}

.dropdown-container:hover {
  background-color: var(--dropdown-button-hover-color);
}

.dropdown-checkbox:checked~.select-wrapper {
  top: calc(var(--height) + 5px);
  opacity: 1;
  visibility: visible;
}

.dropdown-checkbox:checked+.dropdown-container::after {
  rotate: 180deg;
  translate: 0 -2px;
}

.ui-wrapper input#Austria:checked~.dropdown-container::before,
.ui-wrapper input#Austria:checked~.select-wrapper li.Austria {
  content: "VN";
  background-color: lightgray;
}


.ui-wrapper input#Austria:checked~.input-wrapper .textfield::before {
  content: "+84";
}


/* điện thoại end */
.overlay {
  display: flex;
  justify-content: center;
  align-items: center;
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.7);
  z-index: 1000;
}

.otp-Form {
  width: 230px;
  height: 300px;
  margin: 0 auto;
  background-color: rgb(255, 255, 255);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 20px 30px;
  gap: 20px;
  position: relative;
  box-shadow: 0px 0px 20px rgba(0, 0, 0, 0.082);
  border-radius: 15px;
}

.mainHeading {
  font-size: 1.1em;
  color: rgb(15, 15, 15);
  font-weight: 700;
}

.otpSubheading {
  font-size: 0.7em;
  color: black;
  line-height: 17px;
  text-align: center;
}

.inputContainer {
  width: 100%;
  display: flex;
  flex-direction: row;
  gap: 5px;
  align-items: center;
  justify-content: center;
}

.otp-input {
  background-color: rgb(228, 228, 228);
  width: 30px;
  height: 30px;
  text-align: center;
  border: none;
  border-radius: 7px;
  caret-color: rgb(127, 129, 255);
  color: rgb(44, 44, 44);
  outline: none;
  font-weight: 600;
}

.verifyButton {
  width: 100%;
  height: 30px;
  border: none;
  background-color: rgb(127, 129, 255);
  color: white;
  font-weight: 600;
  cursor: pointer;
  border-radius: 10px;
}

.exitBtn {
  position: absolute;
  top: 5px;
  right: 5px;
  background-color: rgb(255, 255, 255);
  border-radius: 50%;
  width: 25px;
  height: 25px;
  border: none;
  color: black;
  font-size: 1.1em;
  cursor: pointer;
}

.resendNote {
  font-size: 0.7em;
  color: black;
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 5px;
}

.resendBtn {
  background-color: transparent;
  border: none;
  color: rgb(127, 129, 255);
  cursor: pointer;
}
</style>