<template>
    <div>
        <p>ID: {{ id }}</p>
    </div>
</template>

<script setup>
import { defineProps } from 'vue';
// Định nghĩa props
const props = defineProps({
    id: {
        type: String,
        required: true
    },
});
const id = props.id;
console.log('ID:', id);
</script>

<style>
/* CSS tùy chọn cho giao diện */
</style>