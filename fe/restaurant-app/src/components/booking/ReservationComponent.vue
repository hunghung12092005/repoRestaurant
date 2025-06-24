<template>
  <div class="reservations-component">
    <!-- Hero Section -->
    <div class="hero_single inner_pages">
      <div class="opacity-mask" :style="{ backgroundColor: 'rgba(0, 0, 0, 0.4)' }">
        <div class="container">
          <div class="row justify-content-center">
            <div class="col-xl-9 col-lg-10 col-md-8 text-center">
              <h1>Hồ Xuân Hương Ecosystem</h1>
              <p>Đặt Bàn Online </p>
            </div>
          </div>
        </div>
      </div>
      <div class="frame white"></div>
    </div>

    <!-- Reservation Form -->
    <div class="pattern_2">
      <div class="container margin_120_100 pb-0">
        <div class="row justify-content-center">
          <div class="col-lg-6 text-center d-none d-lg-block">
            <img src="https://www.ansonika.com/foores/img/chef.png" width="400" height="733" alt="Chef"
              class="img-fluid" />
          </div>
          <div class="col-lg-6 col-md-8">
            <div class="main_title">
              <span><em></em></span>
              <h2>Phục Vụ Tận Tâm Bởi Đầu Bếp Hạng A</h2>
              <p>Sự Thoải Mái Của Bạn Làm Chúng Tôi Hài Lòng</p>
            </div>
            <div id="wizard_container">
              <form @submit.prevent="handleSubmit" id="wrapped">
                <input id="website" name="website" type="text" v-model="form.website" style="display: none;" />

                <!-- Wizard Steps -->
                <div id="middle-wizard">
                  <!-- Step 1: Date Selection -->
                  <div v-show="currentStep === 1" class="step">
                    <h3 class="main_question">
                      <strong>1/3</strong> Please Select a date
                      <span class="required-label" v-if="showRequired && !form.datepicker_field">Required</span>
                    </h3>
                    <div class="calendar-wrapper">
                      <div class="calendar-header">
                        <button @click="prevMonth" class="calendar-nav">
                          <</button>
                            <span>{{ selectedMonth }} {{ selectedYear }}</span>
                            <button @click="nextMonth" class="calendar-nav">></button>
                      </div>
                      <div class="calendar-body">
                        <div class="calendar-days">
                          <span>Su</span><span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span>
                        </div>
                        <div class="calendar-dates">
                          <!-- Empty slots for days before the first day of the month -->
                          <span v-for="n in firstDayOffset" :key="'empty-' + n" class="empty-day"></span>
                          <!-- Actual days -->
                          <span v-for="day in daysInMonth" :key="day" :class="{
                            'disabled': isDayDisabled(day),
                            'selected': day === selectedDay && !isDayDisabled(day)
                          }" @click="selectDay(day)">
                            {{ day }}
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Step 2: Time and Guests -->
                  <div v-show="currentStep === 2" class="step">
                    <h3 class="main_question"><strong>2/3</strong> Select time and guests</h3>
                    <div class="step_wrapper">
                      <h4>
                        Time
                        <span class="required-label" v-if="showRequired && !form.time">Required</span>
                      </h4>
                      <div class="radio-grid">
                        <div v-for="time in timeOptions" :key="time.value" class="radio-item">
                          <input type="radio" :id="'time_' + time.value" name="time" :value="time.value"
                            v-model="form.time" class="required" />
                          <label :for="'time_' + time.value">{{ time.label }}</label>
                        </div>
                      </div>
                    </div>
                    <div class="step_wrapper">
                      <h4>
                        How many people?
                        <span class="required-label" v-if="showRequired && !form.people">Required</span>
                      </h4>
                      <div class="radio-grid">
                        <div v-for="num in peopleOptions" :key="num" class="radio-item">
                          <input type="radio" :id="'people_' + num" name="people" :value="num" v-model="form.people"
                            class="required" />
                          <label :for="'people_' + num">{{ num }}</label>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Step 3: User Details -->
                  <div v-show="currentStep === 3" class="submit step">
                    <h3 class="main_question"><strong>3/3</strong> Please fill with your details</h3>
                    <div class="form-group">
                      <label class="input-label">
                        Name
                        <span class="required-label" v-if="showRequired && !form.name_reserve">Required</span>
                      </label>
                      <input type="text" name="name_reserve" v-model="form.name_reserve" class="form-control required"
                        placeholder="First and Last Name" />
                    </div>
                    <div class="form-group">
                      <label class="input-label">
                        Email
                        <span class="required-label" v-if="showRequired && !form.email_reserve">Required</span>
                      </label>
                      <input type="email" name="email_reserve" v-model="form.email_reserve"
                        class="form-control required" placeholder="Your Email" />
                    </div>
                    <div class="form-group">
                      <label class="input-label">
                        Phone
                        <span class="required-label" v-if="showRequired && !form.telephone_reserve">Required</span>
                      </label>
                      <input type="text" name="telephone_reserve" v-model="form.telephone_reserve"
                        class="form-control required" placeholder="Your Telephone" />
                    </div>
                    <div class="form-group">
                      <label class="input-label">Note</label>
                      <textarea class="form-control" name="opt_message_reserve" v-model="form.opt_message_reserve"
                        placeholder="Please provide any additional info"></textarea>
                    </div>
                    <div class="form-group terms">
                      <label class="container_check">
                        <input type="checkbox" name="terms" v-model="form.terms" true-value="Yes" false-value=""
                          class="required" />
                        <span class="checkmark"></span>
                        I accept the
                        <a href="#" data-bs-toggle="modal" data-bs-target="#terms-txt">Terms and conditions</a>
                        <span class="required-label" v-if="showRequired && form.terms !== 'Yes'">Required</span>
                      </label>
                    </div>
                  </div>
                </div>

                <!-- Wizard Navigation -->
                <div id="bottom-wizard">
                  <!-- Show Prev button only on steps 2 and 3 -->
                  <button v-if="currentStep > 1" type="button" class="backward" @click="prevStep">
                    Prev
                  </button>
                  <!-- Show Next button only on steps 1 and 2 -->
                  <button v-if="currentStep < 3" type="button" class="forward" @click="nextStep">
                    Next
                  </button>
                  <!-- Show Submit button only on step 3 -->
                  <button v-if="currentStep === 3" type="submit" class="submit">
                    Submit
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
<Footer></Footer>
</template>
<script setup>
  import Footer from '../Footer.vue';
</script>
<script>
export default {
  name: 'ReservationsComponent',
  data() {
    return {
      currentStep: 1,
      showRequired: false, // New property to control Required label visibility
      form: {
        website: '',
        datepicker_field: '',
        time: '',
        people: '',
        name_reserve: '',
        email_reserve: '',
        telephone_reserve: '',
        opt_message_reserve: '',
        terms: ''
      },
      timeOptions: [
        { value: '12:30', label: '12:30' },
        { value: '13:00', label: '13:00' },
        { value: '13:30', label: '13:30' },
        { value: '20:00', label: '20:00' },
        { value: '20:30', label: '20:30' },
        { value: '21:00', label: '21:00' },
        { value: '21:30', label: '21:30' }
      ],
      peopleOptions: [1, 2, 3, 4],
      selectedYear: new Date().getFullYear(),
      selectedMonth: new Date().toLocaleString('default', { month: 'long' }),
      selectedDay: null,
      currentDate: new Date()
    };
  },
  computed: {
    isStepValid() {
      if (this.currentStep === 1) {
        return !!this.form.datepicker_field;
      } else if (this.currentStep === 2) {
        return !!this.form.time && !!this.form.people;
      }
      return true;
    },
    isFormValid() {
      return (
        !!this.form.name_reserve &&
        !!this.form.email_reserve &&
        !!this.form.telephone_reserve &&
        this.form.terms === 'Yes'
      );
    },
    daysInMonth() {
      const year = this.selectedYear;
      const month = this.monthIndex;
      const days = new Date(year, month + 1, 0).getDate();
      return Array.from({ length: days }, (_, i) => i + 1);
    },
    monthIndex() {
      return new Date(this.selectedMonth + ' 1, ' + this.selectedYear).getMonth();
    },
    firstDayOffset() {
      const firstDay = new Date(this.selectedYear, this.monthIndex, 1).getDay();
      return firstDay;
    }
  },
  methods: {
    nextStep() {
      if (!this.isStepValid) {
        this.showRequired = true; // Show Required labels if validation fails
      } else {
        this.showRequired = false; // Reset if valid
        if (this.currentStep < 3) {
          this.currentStep++;
        }
      }
    },
    prevStep() {
      if (this.currentStep > 1) {
        this.currentStep--;
        this.showRequired = false; // Reset Required labels when going back
      }
    },
    handleSubmit() {
      if (!this.isFormValid) {
        this.showRequired = true; // Show Required labels if validation fails
      } else {
        this.showRequired = false; // Reset if valid
        console.log('Form submitted:', this.form);
        alert('Reservation submitted successfully!');
        this.form = {
          website: '',
          datepicker_field: '',
          time: '',
          people: '',
          name_reserve: '',
          email_reserve: '',
          telephone_reserve: '',
          opt_message_reserve: '',
          terms: ''
        };
        this.currentStep = 1;
      }
    },
    prevMonth() {
      this.currentDate.setMonth(this.currentDate.getMonth() - 1);
      this.updateDate();
    },
    nextMonth() {
      this.currentDate.setMonth(this.currentDate.getMonth() + 1);
      this.updateDate();
    },
    updateDate() {
      this.selectedYear = this.currentDate.getFullYear();
      this.selectedMonth = this.currentDate.toLocaleString('default', { month: 'long' });
      this.selectedDay = null;
    },
    selectDay(day) {
      if (!this.isDayDisabled(day)) {
        this.selectedDay = day;
        this.form.datepicker_field = `${this.selectedMonth} ${day}, ${this.selectedYear}`;
      }
    },
    isDayDisabled(day) {
      const selectedDate = new Date(this.selectedYear, this.monthIndex, day);
      const currentDate = new Date(2025, 4, 14); // May 14, 2025
      return selectedDate < currentDate;
    }
  }
};
</script>

<style scoped>
.reservations-component {
  font-family: 'Poppins', sans-serif;
}

.hero_single {
  background: url('https://img.tripi.vn/cdn-cgi/image/width=700,height=700/https://gcs.tripi.vn/public-tripi/tripi-feed/img/478954HUx/anh-mo-ta.png') no-repeat center center;
  background-size: cover;
  position: relative;
  height: 300px;
  display: flex;
  align-items: center;
}

.opacity-mask {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  text-align: center;
}

.frame.white {
  border-top: 1px solid #ddd;
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
}

.pattern_2 {
  background: #f9f9f9;
  padding: 60px 0;
}

.main_title {
  text-align: left;
  /* Align to the left */
}

.main_title h2 {
  font-size: 26px;
  margin-bottom: 10px;
  font-weight: 600;
}

.main_title p {
  color: #777;
  font-size: 14px;
}

#wizard_container {
  background: #fff;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

.main_question {
  font-size: 20px;
  margin-bottom: 20px;
}

.required-label {
  color: red;
  font-size: 12px;
  margin-left: 5px;
}

.form-group {
  margin-bottom: 20px;
}

.input-label {
  display: flex;
  align-items: center;
  margin-bottom: 5px;
}

.form-control {
  width: 100%;
  padding: 10px 0;
  border: none;
  border-bottom: 1px solid #ddd;
  /* Bottom border only */
  border-radius: 0;
  background: transparent;
}

.form-control:focus {
  outline: none;
  border-bottom: 1px solid #007bff;
}

.form-control::placeholder {
  color: #aaa;
}

textarea.form-control {
  height: 100px;
  border: 1px solid #ddd;
  /* Full border for textarea */
  padding: 10px;
}

.container_check {
  display: flex;
  align-items: center;
  position: relative;
  padding-left: 30px;
  cursor: pointer;
  font-size: 14px;
}

.container_check input {
  position: absolute;
  opacity: 0;
  cursor: pointer;
}

.checkmark {
  position: absolute;
  top: 50%;
  left: 0;
  transform: translateY(-50%);
  height: 18px;
  width: 18px;
  background-color: #fff;
  border: 1px solid #ddd;
  border-radius: 3px;
}

.container_check input:checked~.checkmark {
  background-color: #007bff;
  border-color: #007bff;
}

.checkmark:after {
  content: "";
  position: absolute;
  display: none;
}

.container_check input:checked~.checkmark:after {
  display: block;
}

.container_check .checkmark:after {
  left: 6px;
  top: 2px;
  width: 5px;
  height: 10px;
  border: solid white;
  border-width: 0 2px 2px 0;
  transform: rotate(45deg);
}

.container_check a {
  color: #007bff;
  text-decoration: underline;
}

.calendar-wrapper {
  margin-top: 20px;
  background: #f5f5f5;
  padding: 15px;
  border-radius: 4px;
  text-align: center;
}

.calendar-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
  font-weight: bold;
}

.calendar-nav {
  background: transparent;
  border: none;
  font-size: 18px;
  cursor: pointer;
  color: #333;
}

.calendar-body {
  display: inline-block;
}

.calendar-days,
.calendar-dates {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 5px;
}

.calendar-days span {
  font-weight: bold;
  padding: 5px;
  color: #666;
}

.calendar-dates span {
  display: inline-block;
  width: 35px;
  height: 35px;
  line-height: 35px;
  text-align: center;
  cursor: pointer;
  border-radius: 4px;
}

.calendar-dates .empty-day {
  width: 35px;
  height: 35px;
}

.calendar-dates span.disabled {
  background: #e0e0e0;
  color: #aaa;
  cursor: not-allowed;
}

.calendar-dates span:hover:not(.disabled),
.calendar-dates span.selected {
  background: #007bff;
  color: white;
}

.step_wrapper {
  margin-bottom: 20px;
}

.step_wrapper h4 {
  font-size: 18px;
  margin-bottom: 10px;
  display: flex;
  align-items: center;
}

.radio-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 15px;
}

.radio-item {
  display: flex;
  align-items: center;
  justify-content: center;
}

.radio-item input[type="radio"] {
  margin-right: 5px;
  display: none;
}

.radio-item label {
  margin: 0;
  padding: 10px 20px;
  background: #f0f0f0;
  border: 1px solid #ddd;
  border-radius: 4px;
  cursor: pointer;
  width: 100%;
  text-align: center;
}

.radio-item input[type="radio"]:checked+label {
  background: #007bff;
  /* Changed to blue color */
  color: white;
  border-color: #007bff;
  /* Changed to blue color */
}

#bottom-wizard {
  display: flex;
  justify-content: flex-end;
  margin-top: 20px;
}

#bottom-wizard button {
  padding: 10px 20px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

#bottom-wizard .backward {
  background: #ddd;
  margin-right: 10px;
}

#bottom-wizard .forward,
#bottom-wizard .submit {
  background: #007bff;
  color: white;
}

#bottom-wizard button:disabled {
  background: #ccc;
  cursor: not-allowed;
}
</style>