// resources/js/component-app.js
import { createApp } from 'vue'
import CategoryComponent from './components/CategoryComponent.vue'

if (document.getElementById('home-categories')) {
    createApp({}).component('category-component', CategoryComponent).mount('#home-categories')
}
