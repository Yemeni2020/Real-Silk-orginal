// resources/js/component-app.js
import { createApp } from 'vue'
import CategoryComponent from './components/CategoryComponent.vue'
import CategoryProducts from './components/CategoryProducts.vue'

if (document.getElementById('home-categories')) {
    createApp({}).component('category-component', CategoryComponent).mount('#home-categories')
}
if (document.getElementById('shop-categories')) {
    createApp({}).component('category-products', CategoryProducts).mount('#shop-categories')
}
