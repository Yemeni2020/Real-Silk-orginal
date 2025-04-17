// resources/js/component-app.js
import { createApp } from 'vue'
import CategoryComponent from './components/web/CategoryComponent.vue'
import CategoryProducts from './components/web/CategoryProducts.vue'
import DetailsProduct from './components/web/products/detailsProduct.vue'
import ApiPlugin from './plugins/api';


const app = createApp({});

app.use(ApiPlugin);
  
if (document.getElementById('home-categories')) {
    app.component('category-component', CategoryComponent).mount('#home-categories')
}
if (document.getElementById('shop-categories')) {
    app.component('category-products', CategoryProducts).mount('#shop-categories')
}
if (document.getElementById('overview')) {
    app.component('DetailsProduct', DetailsProduct).mount('#overview')
}
