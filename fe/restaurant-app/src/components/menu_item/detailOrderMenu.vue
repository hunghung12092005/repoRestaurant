<template>
    <loading v-if="isLoading" />

    <!-- From Uiverse.io by Cksunandh -->
    <div class="receipt">
        <p class="shop-name">UI Market</p>
        <p class="info">
            1234 Market Street, Suite 101<br />
            City, State ZIP<br />
            Date: {{ currentDateTime }}<br />

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
                <tr v-for="item in iteam" :key="item.id">
                    <td>{{ item.Name }}</td>
                    <td>
                        <input type="range" v-model.number="quantity" min="1" max="5" @input="updateTotalPrice" />
                    </td>
                    <td>${{ item.Price }}</td>
                </tr>

            </tbody>
        </table>

        <div class="total">
            <p>Total:</p>
            <p>$45.00</p>
        </div>

        <p class="thanks">Thank you for shopping with us!</p>
    </div>

</template>
<script setup>
import { useRoute } from 'vue-router';
import { inject, onMounted, ref } from 'vue';
import axios from 'axios';
import loading from '../loading.vue';
const apiUrl = inject('apiUrl');
const route = useRoute();
const props = defineProps({
    ids: {
        type: String,
        required: true
    }
});

const getCurrentDateTime = () => {
    const now = new Date();
    return now.toLocaleString(); // Định dạng theo ngôn ngữ địa phương
};
const currentDateTime = ref(getCurrentDateTime()); // Khởi tạo với thời gian hiện tại

const idsArray = JSON.parse(props.ids);//chuyển từ string sang object
const id = ref([]);
const name = ref([]);
const isLoading = ref(false);
const iteam = ref([]); // Mảng chứa các món ăn
const getArrayItem = async () => {
    //console.log(props.ids);
    isLoading.value = true; // Bật trạng thái loading
    try {
        const data = {
            ids: idsArray
        }
        const response = await axios.post(`${apiUrl}/api/menu-itemsarray/ids`, data);
        //console.log(response.data);
        iteam.value = response.data; // Lưu toàn bộ dữ liệu vào mảng iteam
        console.log(iteam.value);
        // Nếu muốn xử lý dữ liệu trả về, bạn có thể làm như sau:
        response.data.forEach(item => {
            //console.log(`ID: ${item.id}`);
            id.value.push(item.id); // Lưu ID vào mảng id
            //console.log(`Name: ${item.Name}`);
            //name.value.push(item.Name); // Lưu Name vào mảng name
            // console.log(`Description: ${item.Description}`);
            // console.log(`Price: ${item.Price}`);
            // console.log(`Created At: ${item.CreatedAt}`);
            // console.log('---'); // Ngăn cách giữa các món ăn
        });
    } catch (error) {
        console.error('Lỗi khi lấy dữ liệu từ API:', error);
    } finally {
        isLoading.value = false; // Tắt trạng thái loading
    }
}
onMounted(() => {
    getArrayItem(); // Gọi hàm khi component được mount
});
</script>
<style scoped>
/* From Uiverse.io by Cksunandh */
.receipt {
    width: 500px;
    margin: auto auto;
    background: white;
    border: 2px dashed #ccc;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

@media(max-width: 600px) {
    .receipt {
        width: 88%;
        padding: 10px;
    }
}

.shop-name {
    font-size: 1.2rem;
    font-weight: bold;
    text-align: center;
    margin-bottom: 10px;
}

.info {
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