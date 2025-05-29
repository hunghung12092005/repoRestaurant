<template>
    <!-- <div class="container body">

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
    </div> -->
    <router-link :to="{ name: 'detailMenu', params: { id: '12313' } }">Go to Menu with ID 123</router-link>
    <header>
        <ul>
            <i class='fa fa-shopping-cart'>
                <span class='counter'></span>
            </i>
        </ul>
    </header>
    <div class='row'>
        <div class='product--blue'>
            <div class='product_inner'>
                <img src='https://bizweb.dktcdn.net/thumb/large/100/350/142/products/344-1709707725062.jpg?v=1710126483757'
                    width='300'>
                <p>Nike Air (Women)</p>
                <p>Size 7</p>
                <p>Price £199.99</p>
                <button>Add to basket</button>
            </div>
            <div class='product_overlay'>
                <h2>Added to basket</h2>
                <i class='fa fa-check'></i>
            </div>
        </div>
        <div class='product--orange'>
            <div class='product_inner'>
                <img src='https://bizweb.dktcdn.net/thumb/large/100/350/142/products/wide-mouth-946ml-1731554876226.jpg?v=1731554880193'
                    width='300'>
                <p>Nike Air (Men)</p>
                <p>Size 10</p>
                <p>Price £99.99</p>
                <button>Add to basket</button>
            </div>
            <div class='product_overlay'>
                <h2>Added to basket</h2>
                <i class='fa fa-check'></i>
            </div>
        </div>
        <div class='product--red'>
            <div class='product_inner'>
                <img src='http://wellandgood.com/wp-content/uploads/2012/07/Nike-Free-30-Womens-Running-Shoe-511495_600_A.png'
                    width='300'>
                <p>Nike Air (Women)</p>
                <p>Size 8</p>
                <p>Price £399.99</p>
                <button>Add to basket</button>
            </div>
            <div class='product_overlay'>
                <h2>Added to basket</h2>
                <i class='fa fa-check'></i>
            </div>
        </div>
        <div class='product--green'>
            <div class='product_inner'>
                <img src='http://wellandgood.com/wp-content/uploads/2012/07/Nike-Free-30-Womens-Running-Shoe-511495_600_A.png'
                    width='300'>
                <p>Nike Air (Men)</p>
                <p>Size 11</p>
                <p>Price £299.99</p>
                <button>Add to basket</button>
            </div>
            <div class='product_overlay'>
                <h2>Added to basket</h2>
                <i class='fa fa-check'></i>
            </div>
        </div>
        <div class='product--yellow'>
            <div class='product_inner'>
                <img src='http://wellandgood.com/wp-content/uploads/2012/07/Nike-Free-30-Womens-Running-Shoe-511495_600_A.png'
                    width='300'>
                <p>Nike Air (Women)</p>
                <p>Size 11</p>
                <p>Price £299.99</p>
                <button>Add to basket</button>
            </div>
            <div class='product_overlay'>
                <h2>Added to basket</h2>
                <i class='fa fa-check'></i>
            </div>
        </div>
        <div class='product--pink'>
            <div class='product_inner'>
                <img src='http://wellandgood.com/wp-content/uploads/2012/07/Nike-Free-30-Womens-Running-Shoe-511495_600_A.png'
                    width='300'>
                <p>Nike Air (Men)</p>
                <p>Size 11</p>
                <p>Price £299.99</p>
                <button>Add to basket</button>
            </div>
            <div class='product_overlay'>
                <h2>Added to basket</h2>
                <i class='fa fa-check'></i>
            </div>
        </div>
        <div class='product--pink'>
            <div class='product_inner'>
                <img src='http://wellandgood.com/wp-content/uploads/2012/07/Nike-Free-30-Womens-Running-Shoe-511495_600_A.png'
                    width='300'>
                <p>Nike Air (Men)</p>
                <p>Size 11</p>
                <p>Price £299.99</p>
                <button>Add to basket</button>
            </div>
            <div class='product_overlay'>
                <h2>Added to basket</h2>
                <i class='fa fa-check'></i>
            </div>
        </div>
    </div>

</template>

<style scoped>
@charset "UTF-8";

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: "Open Sans Condensed", sans-serif;
    font-weight: 700;
}

html,
body {
    background: #EEE5E9;
}

header {
    width: 95%;
    display: flex;
    justify-content: flex-end;
    flex-flow: row wrap;
}

header ul {
    padding: 1.2em 0 0.5em;
}

header span {
    padding: 0 0 0 0.5em;
}

header span,
header i {
    color: #2B2D42;
}

header i:nth-of-type(2) {
    cursor: pointer;
}

.row {
    width: 100%;
    justify-content: flex-start;
    display: flex;
    flex-flow: row wrap;
}

.product,
.product--pink,
.product--yellow,
.product--green,
.product--red,
.product--orange,
.product--blue {
    box-shadow: 1px 5px 15px #CCC;
    width: 15em;
    height: auto;
    border-radius: 3px;
    padding: 2em;
    margin: 1em;
    overflow: hidden;
    position: relative;
    flex: auto;
}

.product--blue {
    background: linear-gradient(-45deg, #92DCE5 50%, rgba(255, 255, 255, 0.5) 50%);
}

.product--orange {
    background: linear-gradient(-45deg, #EF6F6C 50%, rgba(255, 255, 255, 0.5) 50%);
}

.product--red {
    background: linear-gradient(-45deg, #E84855 50%, rgba(255, 255, 255, 0.5) 50%);
}

.product--green {
    background: linear-gradient(-45deg, #70C1B3 50%, rgba(255, 255, 255, 0.5) 50%);
}

.product--yellow {
    background: linear-gradient(-45deg, #E8DB7D 50%, rgba(255, 255, 255, 0.5) 50%);
}

.product--pink {
    background: linear-gradient(-45deg, #FF386D 50%, rgba(255, 255, 255, 0.5) 50%);
}

.product img,
.product--blue img,
.product--orange img,
.product--red img,
.product--green img,
.product--yellow img,
.product--pink img {
    max-width: 100%;
    height: auto !important;
    text-align: center;
}

.product_inner {
    display: flex;
    align-items: center;
    flex-flow: column wrap;
}

.product_inner p {
    color: rgba(255, 255, 255, 0.9);
}

.product_inner button {
    border: 1px solid rgba(255, 255, 255, 0.5);
    color: #FFF;
    border-radius: 3px;
    padding: 1em 3em;
    margin: 1em 0 0 0;
    background: none;
    cursor: pointer;
    transition: background ease-in 0.25s;
}

.product_inner button:hover {
    background: white;
    color: #2B2D42;
}

.product_inner button:before {
    font-family: FontAwesome;
    content: "";
    color: #FFF;
    position: absolute;
    font-size: 1.5em;
    margin: 0 -1.5em;
}

.product_inner button:hover:before {
    color: #2B2D42;
}

.product_overlay {
    background: rgba(255, 255, 255, 0.9);
    width: 100%;
    height: 100%;
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    transform: translateY(-500px);
    opacity: 0;
    display: flex;
    flex-flow: column wrap;
    justify-content: center;
    align-items: center;
}

.product_overlay h2 {
    color: rgba(43, 45, 66, 0.7);
    font-size: 1.2em;
    margin: 1em 0;
}

.product_overlay i {
    color: rgba(43, 45, 66, 0.7);
    font-size: 1.5em;
}
</style>
<script setup>

</script>
<script>
</script>