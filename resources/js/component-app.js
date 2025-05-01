// resources/js/component-app.js
import { createApp } from 'vue'
import CategoryComponent from './components/web/CategoryComponent.vue'
import CategoryProducts from './components/web/CategoryProducts.vue'
import DetailsProduct from './components/web/products/detailsProduct.vue'
import CategoryHeader from './components/web/CategoryHeader.vue'
import shippingMethod from './components/web/shipping/shippingMethod.vue'
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
if (document.getElementById('category-menu-header')) {
    const app2 = createApp(CategoryHeader);
    app2.use(ApiPlugin);
    app2.mount('#category-menu-header');
}
if (document.getElementById('category-menu-header')) {
    const app2 = createApp(CategoryHeader);
    app2.use(ApiPlugin);
    app2.mount('#category-menu-header');
}
if (document.getElementById('shipping-method-vue')) {
    document.querySelectorAll('.shipping-method-vue').forEach((el) => {
        const methods = JSON.parse(el.dataset.methods); // استخرج البيانات من data-methods
    
        const app = createApp({
            components: { shippingMethod },
            template: `<shippingMethod :methods="methods" />`,
            data() {
                return { methods };
            }
        });
    
        app.use(ApiPlugin);
        app.mount(el);
    });
}