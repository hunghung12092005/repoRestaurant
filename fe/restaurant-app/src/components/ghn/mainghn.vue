<template>
    <loading v-if="isLoading" />
    <div class="container">
        <div class="row">

            <div class="col-md-5">
                <h2>Address</h2>
                <!-- <button class="btn btn-success" @click="getAddress"> -->
                <button class="btn-main" @click="getAddress">
                    <div class="arrow-wrapper">
                        <div class="arrow"></div>

                    </div>
                    <span v-if="address">
                        Nhập địa chỉ chi tiết
                    </span>
                    <span v-else>
                        Lấy địa chỉ nhanh
                    </span>
                </button>
                <div v-if="address">
                    <p>Địa chỉ: {{ address }}</p>
                </div>
                <div v-else class="ghn">
                    <div>
                        <label for="province">Tỉnh:</label>
                        <v-select class="abc" v-model="selectedProvince" :options="provinces" label="ProvinceName"
                            placeholder="Tìm tỉnh..." @change="fetchDistricts" />
                    </div>

                    <div >
                        <label for="district" >Huyện:</label>
                        <v-select  v-model="selectedDistrict" :options="districts" label="DistrictName"
                            placeholder="Tìm huyện..." @change="fetchWards" :disabled="!districts.length" />
                    </div>

                    <div>
                        <label for="ward">Xã:</label>
                        <v-select v-model="selectedWard" :options="wards" label="WardName" placeholder="Tìm xã..."
                            :disabled="!wards.length" />
                    </div>

                    <div>
                        <span v-if="selectedProvince">Đã chọn: Tỉnh {{ selectedProvince?.ProvinceName }} {{
                            selectedDistrict?.DistrictName }} {{
                                selectedWard?.WardName }}</span>
                    </div>

                    <!-- <p>Số điện thoại người nhận: <input v-model="sdt" type="number"></p>
                <p>Tên Người Nhận: <input v-model="name" type="text"></p> -->

                </div>
                <div class="inputGroup">
                    <input type="text" v-model="diachichitiet" required="" autocomplete="off">
                    <label for="name">Số nhà/tên đường</label>
                </div>
                <div class="inputGroup">
                    <input type="text" v-model="sdt" required="" autocomplete="off">
                    <label for="name">Số điện thoại người nhận</label>
                </div>
                <div class="inputGroup">
                    <input type="text" v-model="name" required="" autocomplete="off">
                    <label for="name">Tên Người Nhận</label>
                </div>
                <p><strong>Phí ship + VAT: {{ formatNumber(fee) }}</strong> </p>

            </div>
            <div class="col-md-7">
                <div class="receipt">
                    <p class="shop-name">Chi tiết Bill hàng</p>
                    <p class="infoMarket">
                        Hồ Xuân Hương 23/89 Thành Phố Sầm Sơn<br />
                        City, State ZIP<br />
                        Thời Gian ra Bill: {{ currentDateTime }}<br />

                    </p>

                    <table>
                        <thead>
                            <tr>
                                <th>Tên sản phẩm</th>
                                <th>Số lượng</th>
                                <th>Giá Tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ orderB.name }}<strong> ({{ quantity }} sp)</strong></td>

                                <td>
                                    <input type="range" v-model.number="quantity" min="1" max="5"
                                        @input="updateTotalPrice" />
                                </td>
                                <td>{{ formatNumber(orderB.price) }}</td>
                            </tr>

                        </tbody>
                    </table>

                    <div class="total">
                        <p>Tổng:</p>
                        <p class="">{{ formatNumber(finalPrice) }} </p>
                        <span> {{ formatNumber(fee) }} Phí ship </span>
                        <p> {{ formatNumber(totalPrice) }} Tiền hàng</p>

                    </div>

                    <p class="thanks">Thank you for shopping with us!</p>
                    <div class=" d-flex flex-column flex-md-row">
                        <div
                            class="w-full p-2 aspect-square rounded-lg shadow flex flex-col items-center justify-center gap-2 bg-slate-50 payment">
                            <p class="capitalize font-semibold ">Phương thức thanh toán</p>

                            <label
                                class="inline-flex justify-between w-full items-center rounded-lg p-2 border border-transparent has-[:checked]:border-indigo-500 has-[:checked]:text-indigo-900 has-[:checked]:bg-indigo-50 has-[:checked]:font-bold hover:bg-slate-200 transition-all cursor-pointer has-[:checked]:transition-all has-[:checked]:duration-500 duration-500 relative [&amp;_p]:has-[:checked]:translate-y-0 [&amp;_p]:has-[:checked]:transition-transform [&amp;_p]:has-[:checked]:duration-500 [&amp;_p]:has-[:checked]:opacity-100 overflow-hidden">
                                <div class="inline-flex items-center justify-center gap-2 relative">
                                    <img class=" " style="width: 100%;" src="https://i.gyazo.com/566d62fd25cf0867e0033fb1b9b47927.png"
                                        alt="">

                                </div>

                            </label>
                            <!-- From Uiverse.io by escannord -->
                            <div class="radio-input">
                                <input value="value-1" name="value-radio" id="value-1" type="radio" />
                                <label for="value-1">
                                    <div class="text">
                                        <span class="circle"></span>
                                        Tất toán
                                    </div>
                                    <div class="price">
                                        <span>100%</span>
                                        <span class="small">đơn hàng</span>
                                    </div>
                                </label><input value="value-2" name="value-radio" id="value-2" type="radio" />
                                <label for="value-2">
                                    <div class="text">
                                        <span class="circle"></span>
                                        COD Ship
                                    </div>
                                    <div class="price">
                                        <span>Được kiểm hàng</span>
                                        <span class="small">.</span>
                                    </div>
                                    <span class="info">Thanh toán tiền ship</span>
                                </label>
                            </div>
                        </div>
                        <div class="page">
                            <div class="margin"></div>
                            <p>Nếu gặp vấn đề khi chuyển khoản vui lòng liên hệ nhân viên: 0123471331 hoặc nhắn tin qua
                                chatbot của hệ thống để được hỗ trợ kịp thời</p>

                            <div class="btn-main">
                                Tiến hành chuyển khoản
                                <div class="arrow-wrapper">
                                    <div class="arrow"></div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
    <!-- <Footer></Footer> -->
</template>

<script setup>
import { ref, onMounted, watch, inject } from 'vue';
import axios from 'axios';
import VSelect from 'vue3-select';
import 'vue3-select/dist/vue3-select.css';
import loading from '../loading.vue';
import Footer from '../Footer.vue';
const provinces = ref([]);
const districts = ref([]);
const wards = ref([]);
const selectedProvince = ref(null);
const selectedDistrict = ref(null);
const selectedWard = ref(null);
const valueUser = ref([]);
const wardCode = ref();
const distrisID = ref();
const serviceIds = ref([]);
const servicetype_id = ref([]);
const services = ref([]);
const firstServiceId = ref();
const firstServicetype_id = ref();
const fee = ref(null);
const diachichitiet = ref();
const sdt = ref();
const name = ref();
//
const orderB = ref([]);
const apiUrl = inject('apiUrl');
const quantity = ref(1);
const totalPrice = ref(); // Khởi tạo totalPrice với giá trị fee // Khởi tạo totalPrice
const Price = ref();
const PriceEg = ref();//giá bind vào tổng

const formatNumber = (num) => {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND',
    }).format(num);
};
const getCurrentDateTime = () => {
    const now = new Date();
    return now.toLocaleString(); // Định dạng theo ngôn ngữ địa phương
};

const currentDateTime = ref(getCurrentDateTime()); // Khởi tạo với thời gian hiện tại
//lấy dữ liệu đơn hàng

const detailOrder = async () => {
    try {
        const response = await axios.get(`${apiUrl}/api/items-online/2`);
        orderB.value = response.data;
        Price.value = orderB.value.price;
        PriceEg.value = orderB.value.price;
        totalPrice.value = orderB.value.price;//đặt mặc định của tổng là giá tiền 
        //currentDateTime.value = now.toLocaleString();
        //console.log(response.data);
        //updateTotalPrice();
    } catch (error) {
        console.log(error);
    }
};

// Hàm để cập nhật tổng giá khi thay đổi số lượng
const updateTotalPrice = () => {
    totalPrice.value = Price.value * quantity.value;// thay đổi giá tiền dựa vào số lượng
    updatePrice();
};
//api lấy địa chỉ nhanh
const address = ref(null);
// Thiết lập interceptor để loại bỏ Authorization
const customAxios = axios.create({
    headers: {
        'Authorization': undefined // Không gửi header này
    }
});
const isLoading = ref(false); // Biến để quản lý trạng thái loading
const getAddress = async () => {
    isLoading.value = true; // Bắt đầu loading
    try {
        if (address.value) {
            address.value = null; // Đặt lại địa chỉ
        } else {
            // Gọi API mà không có header Authorization
            const ipResponse = await customAxios.get('https://open.oapi.vn/ip/me', {
                headers: {
                    'authorization': '' // Đảm bảo không gửi token
                }
            });
            const loc = ipResponse.data.data.loc.split(',');
            const lat = loc[0];
            const lon = loc[1];
            const addressResponse = await axios.get(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lon}&format=json`);
            address.value = addressResponse.data.display_name;
        }
    } catch (error) {
        console.error('Lỗi:', error);
    } finally {
        isLoading.value = false; // Kết thúc loading
    }
};
//api ghn
const token = 'f2ee009b-36c6-11f0-9b81-222185cb68c8';

const fetchProvinces = async () => {
    try {
        const response = await axios.get('https://dev-online-gateway.ghn.vn/shiip/public-api/master-data/province', {
            headers: { token: token }
        });
        provinces.value = response.data.data;
    } catch (error) {
        console.error('Error fetching provinces:', error);
    }
};

const fetchDistricts = async () => {
    if (!selectedProvince.value) return;
    try {
        const response = await axios.get(`https://dev-online-gateway.ghn.vn/shiip/public-api/master-data/district?province_id=${selectedProvince.value.ProvinceID}`, {
            headers: { token: token }
        });
        districts.value = response.data.data;
        wards.value = []; // Reset wards when province changes
        selectedDistrict.value = null; // Reset selected district
    } catch (error) {
        console.error('Error fetching districts:', error);
    }
};

const fetchWards = async () => {
    if (!selectedDistrict.value) return;
    try {
        const response = await axios.get(`https://dev-online-gateway.ghn.vn/shiip/public-api/master-data/ward?district_id=${selectedDistrict.value.DistrictID}`, {
            headers: { token: token }
        });
        wards.value = response.data.data;
    } catch (error) {
        console.error('Error fetching wards:', error);
    }
};

// Theo dõi sự thay đổi của selectedWard
watch(selectedWard, (newValue) => {
    if (newValue) {
        valueUser.value = [{ // Cập nhật lại mảng chỉ với giá trị mới nhất
            WardCode: newValue.WardCode,
            DistrictID: newValue.DistrictID
        }];

        // Lấy WardCode từ mảng
        wardCode.value = valueUser.value[0].WardCode;
        //console.log('WardCode:', wardCode.value); // In ra WardCode
        distrisID.value = valueUser.value[0].DistrictID;
        //console.log('distrisId:', distrisID.value); // In ra WardCode
        fetchAvailableServices();
        //console.log('Dữ liệu đã lưu:', valueUser.value);
    }
});

const fetchAvailableServices = async () => {
    const url = 'https://dev-online-gateway.ghn.vn/shiip/public-api/v2/shipping-order/available-services';

    const data = {
        shop_id: 196624,
        from_district: 1616,
        to_district: distrisID.value
    };

    const headers = {
        'token': token // Thay YOUR_API_TOKEN bằng token thực tế của bạn
    };

    try {
        const response = await axios.post(url, data, { headers });
        services.value = response.data; // Lưu dữ liệu vào biến reactive
        serviceIds.value = response.data.data.map(service => service.service_id); // Lấy tất cả
        firstServiceId.value = serviceIds.value[0];
        servicetype_id.value = response.data.data.map(service => service.service_type_id);
        firstServicetype_id.value = servicetype_id.value[0];

        calculateShippingFee();
        // console.log(firstServiceId);
        // console.log(firstServicetype_id);
        //console.log(service_type_id[0]);
        // service_id
        //console.log('Service IDs:', serviceIds[0]); // lấy ra cái đầu 0 là hàng nhẹ 1 hàng nặng
    } catch (error) {
        console.error('Lỗi khi gửi yêu cầu:', error);
    }
};

const calculateShippingFee = async () => {
    const url = 'https://dev-online-gateway.ghn.vn/shiip/public-api/v2/shipping-order/fee';

    const data = {
        from_district_id: 1616,
        from_ward_code: "280117",
        service_id: firstServiceId.value,
        service_type_id: firstServicetype_id.value,
        to_district_id: distrisID.value,
        to_ward_code: wardCode.value,
        height: 0,
        length: 0,
        weight: 2200,
        width: 0,
        insurance_value: 10000,
        cod_failed_amount: 10000,
        coupon: null,
        // items: [
        //     {
        //         name: "TEST1",
        //         quantity: 1,
        //         height: 200,
        //         weight: 1000,
        //         length: 200,
        //         width: 200
        //     }
        // ]
    };

    const headers = {
        'token': token
    };

    try {
        const response = await axios.post(url, data, { headers });
        fee.value = response.data.data.total; // Lưu dữ liệu phí
        //console.log('Phí Vận Chuyển:', response.data.data.total);
        // console.log(totalPrice.value);
        updatePrice();
    } catch (error) {
        console.error('Lỗi khi gửi yêu cầu:', error);
    }
};
const finalPrice = ref(PriceEg);//dùng priceeg để k ảnh hưởng các lỗi
const updatePrice = () => {
    const total = Number(totalPrice.value) + Number(fee.value);
    finalPrice.value = total.toFixed(2);
    //console.log(finalP.value);
};
onMounted(() => {
    fetchProvinces();
    detailOrder();
});
</script>

<style scoped>
/* From Uiverse.io by Yaya12085 */
.page {
    position: relative;
    box-sizing: border-box;
    max-width: 100%;
    font-family: cursive;
    font-size: 20px;
    border-radius: 10px;
    margin-left: 0 auto;
    background-image: linear-gradient(rgba(57, 186, 241,0.04) 1.1rem, white 1.3rem);
    background-size: 100% 1.2rem;
    line-height: 1.2rem;
    padding: 1.4rem 0.5rem 0.3rem 4.5rem;
}

.page::before,
.page::after {
    position: absolute;
    content: "";
    bottom: 10px;
    width: 40%;
    height: 10px;
    box-shadow: 0 5px 14px rgba(0, 0, 0, 0.7);
    z-index: -1;
    transition: all 0.3s ease;
}

.page::before {
    left: 15px;
    transform: skew(-5deg) rotate(-5deg);
}

.page::after {
    right: 15px;
    transform: skew(5deg) rotate(5deg);
}

.page:hover::before,
.page:hover::after {
    box-shadow: 0 2px 14px rgba(0, 0, 0, 0.4);
}

.margin {
    position: absolute;
    border-left: 1px solid #d88;
    height: 100%;
    left: 3.3rem;
    top: 0;
}

.page p {
    margin: 0;
    padding-top: 10px;
    text-indent: 2rem;
    padding-bottom: 1.8rem;
    color: black;
    line-height: 35px;
}

@media (max-width: 768px) {
    .page {
        width: 100%;
    }

}

/* From Uiverse.io by Maximinodotpy */
.inputGroup {
    font-family: 'Segoe UI', sans-serif;
    margin: 1em 0 1em 0;
    max-width: 100%;
    position: relative;
}

.inputGroup input {
    font-size: 100%;
    padding: 0.8em;
    outline: none;
    border: 2px solid rgb(156, 136, 203);
    background-color: transparent;
    border-radius: 10px;
    width: 100%;
}

.inputGroup label {
    font-size: 100%;
    position: absolute;
    left: 0;
    padding: 0.8em;
    margin-left: 0.5em;
    pointer-events: none;
    transition: all 0.3s ease;
    color: rgb(100, 100, 100);
}

.inputGroup :is(input:focus, input:valid)~label {
    transform: translateY(-50%) scale(.9);
    margin: 0em;
    margin-left: 1.3em;
    padding: 0.4em;
    background-color: white;
}

.inputGroup :is(input:focus, input:valid) {
    border-color: rgb(150, 150, 200);
}

.payment {
    width: 100%;
}

.imgPay img {
    width: 100%;
}

/* From Uiverse.io by escannord */
.radio-input input {
    display: none;
}

.radio-input label {
    --border-color: #a1b0d8;
    border: 2px solid rgba(0, 0, 255,0.2);
    border-radius: 6px;
    min-width: 5rem;
    margin: 1rem;
    padding: 1rem;
    display: flex;
    justify-content: space-between;
    position: relative;
    align-items: center;
}

.radio-input input:checked+label {
    --border-color: #2f64d8;
    border-color: var(--border-color);
    border-width: 2px;
}

.radio-input label:hover {
    --border-color: #2f64d8;
    border-color: var(--border-color);
}
.abc{
    background-color: white;
}
.radio-input {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-direction: column;
}

.circle {
    display: inline-block;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background-color: rgb(189, 187, 207);
    margin-right: 0.5rem;
    position: relative;
}

.radio-input input:checked+label span.circle::before {
    content: "";
    display: inline;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background-color: #2f64d8;
    width: 15px;
    height: 15px;
    border-radius: 50%;
}

.text {
    display: flex;
    align-items: center;
}

.price {
    display: flex;
    flex-direction: column;
    text-align: right;
    font-weight: bold;
}

.small {
    font-size: 10px;
    color: rgb(136, 138, 139);
    font-weight: 100;
}

.info {
    position: absolute;
    display: inline-block;
    font-size: 11px;
    background-color: rgb(31, 236, 123);
    border-radius: 20px;
    padding: 1px 9px;
    top: 0;
    transform: translateY(-50%);
    right: 5px;
}

/* From Uiverse.io by Cksunandh */
.receipt {
    width: 100%;
    background: rgba(57, 186, 241,0.1);
    border: 2px dashed #ccc;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.shop-name {
    font-size: 1.2rem;
    font-weight: bold;
    text-align: center;
    margin-bottom: 10px;
}

.infoMarket {
    text-align: center;
    font-size: 0.85rem;
    margin-bottom: 15px;
}

.receipt table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 15px;
    font-size: 0.85rem;
}

.receipt table th,
.receipt table td {
    padding: 4px;
    text-align: left;
    border-bottom: 1px solid #eee;
}

.total {
    display: flex;
    justify-content: space-between;
    font-size: 1rem;
    font-weight: bold;
    margin-bottom: 15px;
}

.barcode {
    display: flex;
    justify-content: center;
    margin-top: 15px;
}

.thanks {
    font-size: 0.85rem;
    text-align: center;
    margin-top: 10px;
}
</style>