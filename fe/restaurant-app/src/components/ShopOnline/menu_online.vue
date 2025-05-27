<template>
    <div class="container body">
            <div>
                <router-link :to="{ name: 'detailMenu', params: { id: '12313' } }">Go to Menu with ID 123</router-link>
            </div>
        <div class="row">
            <div class="col-lg-2 col-md-6 column">
            </div>
            <div class="col-lg-2 col-md-6 column">Cột 2</div>
            <div class="col-lg-2 col-md-6 column">Cột 3</div>
            <div class="col-lg-2 col-md-6 column">Cột 4</div>
            <div class="col-lg-2 col-md-6 column">Cột 5</div>
            <div class="col-lg-2 col-md-6 column">Cột 6</div>
        </div>
    </div>
</template>

<style scoped>
/* From Uiverse.io by iSweat-exe */
.column {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    padding: 15px;
    margin-bottom: 15px;
    margin-right: 10px;
    /* Khoảng cách 10px giữa các cột */
}

/* Để cột cuối cùng không có khoảng cách bên phải */
.column:last-child {
    margin-right: 0;
}
</style>
<script setup>

</script>