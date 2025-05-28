<template>
    <div class="footer">
      <div class="bubbles">
        <div class="bubble"
          style="--size:3.086322244632843rem; --distance:9.937603895783488rem; --position:77.1378973571501%; --time:2.608074679027402s; --delay:-3.6348798607901815s;">
        </div>
        <div class="bubble"
          style="--size:3.808227507742571rem; --distance:7.425727355796341rem; --position:77.41847097949491%; --time:2.0060000160759905s; --delay:-3.433535277851036s;">
        </div>
        <div class="bubble"
          style="--size:2.161144819253492rem; --distance:6.762213494442287rem; --position:36.6649399826768%; --time:3.0796634797495144s; --delay:-2.231066934148482s;">
        </div>
        <div class="bubble"
          style="--size:4.866971077148713rem; --distance:6.120099867228864rem; --position:15.141085137253263%; --time:2.0367265173823417s; --delay:-2.469937106864428s;">
        </div>
        <div class="bubble"
          style="--size:3.527698206367873rem; --distance:6.186009567264217rem; --position:11.946303036666507%; --time:3.841611332427959s; --delay:-3.4456149244221255s;">
        </div>
        <div class="bubble"
          style="--size:4.148353509246295rem; --distance:9.34203762855482rem; --position:20.52877720221919%; --time:2.5364753400250932s; --delay:-2.6213474723436154s;">
        </div>
      </div>
      <div class="content">
        <div>
          <div><b>Eldew</b><a href="#">Secuce</a><a href="#">Drupand</a><a href="#">Oceash</a><a href="#">Ugefe</a><a
              href="#">Babed</a></div>
          <div><b>Spotha</b><a href="#">Miskasa</a><a href="#">Agithe</a><a href="#">Scesha</a><a href="#">Lulle</a>
          </div>
          <div><b>Chashakib</b><a href="#">Chogauw</a><a href="#">Phachuled</a><a href="#">Tiebeft</a><a
              href="#">Ocid</a><a href="#">Izom</a><a href="#">Ort</a></div>
          <div><b>Athod</b><a href="#">Pamuz</a><a href="#">Vapert</a><a href="#">Neesk</a><a href="#">Omemanen</a>
          </div>
        </div>
        <div><a class="image" href="https://codepen.io/z-" target="_blank"
            style="background-image: url(&quot;https://s3-us-west-2.amazonaws.com/s.cdpn.io/199011/happy.svg&quot;)"></a>
          <p>©2019 Not Really</p>
        </div>
      </div>
    </div>
</template>
<style scoped>
body .footer {
  z-index: 1;
  --footer-background: rgb(15, 141, 172);
  display: grid;
  position: relative;
  grid-area: footer;
  min-height: 12rem;
}

body .footer .bubbles {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 1rem;
  background: var(--footer-background);
  filter: url("#blob");
}

body .footer .bubbles .bubble {
  position: absolute;
  left: var(--position, 50%);
  background: var(--footer-background);
  border-radius: 100%;
  -webkit-animation: bubble-size var(--time, 4s) ease-in infinite var(--delay, 0s), bubble-move var(--time, 4s) ease-in infinite var(--delay, 0s);
  animation: bubble-size var(--time, 4s) ease-in infinite var(--delay, 0s), bubble-move var(--time, 4s) ease-in infinite var(--delay, 0s);
  transform: translate(-50%, 100%);
}

body .footer .content {
  z-index: 2;
  display: grid;
  grid-template-columns: 1fr auto;
  grid-gap: 4rem;
  padding: 2rem;
  background: var(--footer-background);
}

body .footer .content a,
body .footer .content p {
  color: #F5F7FA;
  text-decoration: none;
}

body .footer .content b {
  color: white;
}

body .footer .content p {
  margin: 0;
  font-size: 0.75rem;
}

body .footer .content>div {
  display: flex;
  flex-direction: column;
  justify-content: center;
}

body .footer .content>div>div {
  margin: 0.25rem 0;
}

body .footer .content>div>div>* {
  margin-right: 0.5rem;
}

body .footer .content>div .image {
  align-self: center;
  width: 4rem;
  height: 4rem;
  margin: 0.25rem 0;
  background-size: cover;
  background-position: center;
}

@-webkit-keyframes bubble-size {

  0%,
  75% {
    width: var(--size, 4rem);
    height: var(--size, 4rem);
  }

  100% {
    width: 0rem;
    height: 0rem;
  }
}

@keyframes bubble-size {

  0%,
  75% {
    width: var(--size, 4rem);
    height: var(--size, 4rem);
  }

  100% {
    width: 0rem;
    height: 0rem;
  }
}

@-webkit-keyframes bubble-move {
  0% {
    bottom: -4rem;
  }

  100% {
    bottom: var(--distance, 10rem);
  }
}

@keyframes bubble-move {
  0% {
    bottom: -4rem;
  }

  100% {
    bottom: var(--distance, 10rem);
  }
}
@media (max-width: 600px) {
  body .footer .content {
    grid-template-columns: 1fr;
    text-align: center;
  }

  body .footer .content>div {
    align-items: center;
  }

  body .footer .content>div>div {
    flex-direction: row;
    justify-content: center;
  }

  body .footer .content>div>div>* {
    margin-right: 0.25rem;
  }
}
</style>