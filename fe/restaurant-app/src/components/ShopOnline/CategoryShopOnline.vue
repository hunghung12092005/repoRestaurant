<template>
    <div class="app-container">
        <div class="app-left">
            <div class="app-header">
                <div class="app-filter-wrapper">
                    <button class="filter-dropdown-button">
                        <span>Tìm Kiếm</span>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-down"
                            viewBox="0 0 24 24">
                            <path d="M6 9l6 6 6-6" />
                        </svg>
                    </button>
                    <input class="filter-search-input" type="text" placeholder="Search...">
                </div>
            </div>
            <div class="app-left-content">
                <div class="app-left-content-header">
                    <h1 class="content-title">Danh mục </h1>
                </div>
                <div class="app-content-field">
                    <div class="product-box medium">
                        <router-link to="/menu_online">
                            <img class="product-box-image"
                                src="https://images.unsplash.com/photo-1531471689044-dd3ca86632e1?ixlib=rb-1.2.1&ixid=MXwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHw%3D&auto=format&fit=crop&w=2250&q=80"
                                alt="Product">
                            <div class="product-box-details">Decoration <span>55</span></div>
                        </router-link>
                    </div>
                    <div class="product-boxes">
                        <div class="product-box-wrapper three">
                            <div class="product-box medium">
                                <img class="product-box-image"
                                    src="https://images.unsplash.com/photo-1510877073473-6d4545e9c2af?ixid=MXwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHw%3D&ixlib=rb-1.2.1&auto=format&fit=crop&w=1234&q=80"
                                    alt="Product">
                                <div class="product-box-details">Tables <span>55</span></div>
                            </div>
                            <div class="product-box medium">
                                <img class="product-box-image"
                                    src="https://images.unsplash.com/photo-1519710164239-da123dc03ef4?ixlib=rb-1.2.1&ixid=MXwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHw%3D&auto=format&fit=crop&w=1234&q=80"
                                    alt="Product">
                                <div class="product-box-details">Lamps <span>45</span></div>
                            </div>
                            <div class="product-box medium">
                                <img class="product-box-image"
                                    src="https://images.unsplash.com/photo-1526827826797-7b05204a22ef?ixid=MXwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHw%3D&ixlib=rb-1.2.1&auto=format&fit=crop&w=1234&q=80"
                                    alt="Product">
                                <div class="product-box-details">Shelves <span>25</span></div>
                            </div>
                        </div>
                        <div class="product-box-wrapper two">
                            <div class="product-box medium">
                                <img class="product-box-image"
                                    src="https://images.unsplash.com/photo-1519961655809-34fa156820ff?ixlib=rb-1.2.1&ixid=MXwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHw%3D&auto=format&fit=crop&w=1234&q=80"
                                    alt="Product">
                                <div class="product-box-details">Sofa <span>55</span></div>
                            </div>
                            <div class="product-box medium">
                                <img class="product-box-image"
                                    src="https://images.unsplash.com/photo-1556910585-09baa3a3998e?ixid=MXwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHw%3D&ixlib=rb-1.2.1&auto=format&fit=crop&w=1301&q=80"
                                    alt="Product">
                                <div class="product-box-details">Kitchen <span>105</span></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="app-content-field second">
                    <div class="product-box-wrapper three">
                        <div class="product-box medium">
                            <img class="product-box-image"
                                src="https://images.unsplash.com/photo-1502920924215-5646fe685bef?ixlib=rb-1.2.1&ixid=MXwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHw%3D&auto=format&fit=crop&w=2250&q=80"
                                alt="Product">
                            <div class="product-box-details">Mirrors <span>35</span></div>
                        </div>
                        <div class="product-box medium">
                            <img class="product-box-image"
                                src="https://images.unsplash.com/photo-1453904300235-0f2f60b15b5d?ixid=MXwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHw%3D&ixlib=rb-1.2.1&auto=format&fit=crop&w=1233&q=80"
                                alt="Product">
                            <div class="product-box-details">Plants <span>25</span></div>
                        </div>
                        <div class="product-box medium">
                            <img class="product-box-image"
                                src="https://images.unsplash.com/photo-1604014237256-11d475e2a2d8?ixlib=rb-1.2.1&ixid=MXwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHw%3D&auto=format&fit=crop&w=1234&q=80"
                                alt="Product">
                            <div class="product-box-details">Bathroom <span>25</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

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

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap");

* {
    box-sizing: border-box;
}

:root {
    --main-color: #29394f;
    --main-color-2: #29394f;
    --main-blue-hover: #485377;
    --product-list-item-bg: #16263c;
    --main-active: #041429;
    --text-color-dark: #222935;
    --main-box-shadow: rgba(149, 157, 165, 0.2) 0px 8px 24px;
}

.app-container {
    /* background-color: rgba(250, 250, 253,0.5); */
    border-radius: 32px;
    width: 90%;
    margin: 0 auto;
    height: 90%;
    /* max-width: 1400px;
   max-height: 920px; */
    display: flex;
    padding: 16px;
    overflow: hidden;
}

.app-left {
    flex: 1;
    margin: 0 auto;
    height: 100%;
    width: 100%;
    padding: 0 32px;
    display: flex;
    flex-direction: column;
}

.app-left.hide {
    display: none;
}

.app-left-content {
    overflow-y: auto;
    height: 100%;
}


.app-filter-wrapper {
    display: flex;
    align-items: center;
    background-color: #fff;
    border-radius: 12px;
    box-shadow: var(--main-box-shadow);
    max-width: 480px;
    margin-left: auto;
}

.filter-dropdown-button {
    background-color: transparent;
    border: none;
    display: flex;
    align-items: center;
    padding: 16px;
    transition: 0.2s linear;
    cursor: pointer;
    color: var(--text-color-dark);
    flex-shrink: 0;
    position: relative;
    flex-basis: 140px;
    max-width: 140px;
}

.filter-dropdown-button span {
    font-size: 14px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 120px;
}

.filter-dropdown-button:before {
    content: "";
    position: absolute;
    background-color: #e3e4e6;
    width: 1px;
    height: 24px;
    top: 50%;
    transform: translatey(-50%);
    right: 0;
}

.filter-dropdown-button:hover {
    color: var(--main-color);
}

.filter-dropdown-button svg {
    width: 14px;
    height: 14px;
    margin-left: 4px;
    flex-shrink: 0;
}

.filter-search-input {
    border: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    padding: 0 16px;
    outline: none;
    font-size: 16px;
    line-height: 40px;
    caret-color: var(--main-color);
    color: var(--text-color-dark);
    position: relative;
    flex: 1;
    max-width: calc(100% - 140px);
}

.filter-search-input:before {
    content: "";
    position: absolute;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' stroke='%23afb6cc' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-search' viewBox='0 0 24 24'%3E%3Ccircle cx='11' cy='11' r='8'/%3E%3Cpath d='M21 21l-4.35-4.35'/%3E%3C/svg%3E%0A");
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    width: 24px;
    height: 24px;
    background-color: red;
    left: 0;
    top: 50%;
    transform: translatey(-50%);
    display: inline-block;
}

.filter-search-input::-moz-placeholder {
    color: #afb6cc;
}

.filter-search-input:-ms-input-placeholder {
    color: #afb6cc;
}

.filter-search-input::placeholder {
    color: #afb6cc;
}

.app-left-content-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.content-title {
    font-size: 32px;
    line-height: 40px;
    color: var(--text-color-dark);
    font-weight: 600;
}

.content-title span {
    font-weight: 400;
}

.content-link {
    color: var(--main-color);
    font-size: 14px;
    line-height: 16px;
    transition: 0.2s linear;
}

.content-link:hover {
    color: var(--main-color-2);
}

.app-content-field {
    height: 100%;
    min-height: 300px;
    max-height: 480px;
    display: flex;
}

.app-content-field.second {
    height: auto;
    min-height: unset;
}

.app-content-field.second .product-box-wrapper {
    padding-top: 24px;
    height: 180px;
}

.app-content-field.second .product-box-wrapper .product-box:first-child {
    margin-left: 0;
}

.product-box {
    border-radius: 16px;
    overflow: hidden;
    position: relative;
    transition: 0.2s linear;
    box-shadow: var(--main-box-shadow);
    -webkit-animation: listItems 0.6s 0.2s both;
    animation: listItems 0.6s 0.2s both;
    cursor: pointer;
}

.product-box:hover {
    box-shadow: var(--main-box-shadow);
}

.product-box:hover .product-box-image {
    transform: scale(1.1);
}

.product-box-details {
    position: absolute;
    bottom: 12px;
    left: 12px;
    color: var(--text-color-dark);
    max-width: 80%;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.product-box-details span {
    color: #556b8e;
}

.product-box-image {
    width: 100%;
    height: 100%;
    -o-object-fit: cover;
    object-fit: cover;
    transition: 0.2s linear;
}

.product-box-wrapper {
    display: flex;
}

.product-box-wrapper.three {
    flex: 1;
    height: 120px;
}

.product-box-wrapper.three .product-box {
    width: 33.3%;
    margin-left: 16px;
}

.product-box-wrapper.three .product-box-details {
    font-size: 14px;
}

.product-box-wrapper.two {
    flex: 2;
    max-height: calc(100% - 120px);
}

.product-box-wrapper.two .product-box {
    width: 50%;
    margin: 16px 0 0 16px;
}

.product-box.medium {
    flex: 1;
}

.product-boxes {
    flex: 2;
    display: flex;
    flex-direction: column;
}

.app-right-hide {
    position: absolute;
    top: 12px;
    right: 12px;
    width: 24px;
    height: 24px;
    background-color: transparent;
    border: none;
    padding: 0;
    color: #fff;
    display: none;
}

@media screen and (max-width: 1200px) {
    .app-content-field {
        flex-direction: column;
        min-height: unset;
        max-height: unset;
    }

    .product-box.medium {
        min-height: 120px;
        margin-bottom: 24px;
    }

    .product-box-wrapper.three .product-box:first-child,
    .product-box-wrapper.two .product-box:first-child {
        margin-left: 0;
    }
}

@media screen and (max-width: 880px) {
    .app-container {
        position: relative;
    }

    .product-box.medium {
        height: 150px;
    }

    .product-box-wrapper.three {
        height: 150px;
    }

    .product-boxes {
        flex: inherit;
    }

    .app-header {
        display: flex;
        justify-content: space-between;
        margin: 0 auto;
    }

    .app-left-content-header,
    .app-content-field {
        padding: 0 16px;
    }

    div.app-left {
        padding: 0;
    }

    .app-right-hide {
        display: block;
    }

    .app-container {
        border-radius: 16px;
    }

    .action-button {
        display: flex;
    }

    .app-filter-wrapper {
        margin-left: 0;
    }

    .app-content-field.second .product-box-wrapper {
        padding-top: 0;
    }

    .app-right {
        display: none;
        position: absolute;
        height: 0;
        width: calc(100% - 32px);
        transition: 0.2s linear;
        opacity: 0;
        z-index: 2;
        right: -100%;
        height: calc(100% - 32px);
        border-radius: 8px;
    }

    .app-right.isOpen {
        display: flex;
        height: calc(100% - 32px);
        right: 16px;
        opacity: 1;
    }

    .app-left {
        padding: 16px;
    }

    .product-box-wrapper.two {
        flex: unset;
        margin-bottom: 24px;
    }
}

@media screen and (max-width: 520px) {
    .product-img-wrapper {
        flex-basis: 80px;
    }

    .product-box-wrapper.two {
        margin-bottom: 0;
    }

    .app-right-content {
        padding: 32px 16px;
    }

    .product-box-wrapper {
        flex-direction: column;
    }

    .product-box-wrapper.three .product-box {
        width: 100%;
        margin-left: 0;
    }

    .product-box.medium {
        height: 120px;
        flex: unset;
    }

    .product-box-wrapper.three {
        height: unset;
    }

    .product-boxes {
        flex: unset;
    }

    .product-box-wrapper.two .product-box {
        width: 100%;
        margin-left: 0;
    }

    .product-box-wrapper.two {
        flex: unset;
        max-height: unset;
    }

    .product-box-wrapper.three {
        flex: unset;
        height: unset;
    }

    .app-content-field.second {
        height: unset;
        flex-shrink: 0;
        padding-top: 24px;
    }

    .app-content-field {
        flex-shrink: 0;
    }

    .app-left-content {
        display: flex;
        flex-direction: column;
    }

    .app-content-field {
        height: auto;
    }

    .content-title {
        font-size: 24px;
    }

    .app-filter-wrapper {
        max-width: 220px;
    }

    .app-left {
        padding: 8px;
    }

    .filter-dropdown-button {
        max-width: 90px;
    }

    .filter-search-input {
        max-width: calc(100% - 90px);
    }

    .app-container {
        width: 95%;
    }

    .product-box-wrapper.two .product-box:first-child {
        margin-top: 0;
    }

    .app-container {
        max-height: 90%;
    }
}
</style>