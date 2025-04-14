<template>
  <div v-if="categories.length > 0">
    <section v-for="category in categories" :key="category.id" v-if="category && category.name && category.products"
      class="container rtl pb-4 px-max-sm-0">
      <div class="__shadow-2">
        <div class="__p-20px rounded bg-white overflow-hidden">
          <div class="d-flex __gap-6px flex-between px-sm-3">
            <div class="category-product-view-title">
              <span class="for-feature-title font-bold __text-20px text-uppercase">
                {{ category.name }}
              </span>
            </div>
            <div class="category-product-view-all">
              <a class="text-capitalize view-all-text text-nowrap web-text-primary"
                :href="`/products?category_id=${category.id}&data_from=category&page=1`">
                {{ translations.view_all }}
                <i :class="`czi-arrow-${isRtl ? 'left mr-1 ml-n1 mt-1 float-left' : 'right ml-1 mr-n1'}`"></i>
              </a>
            </div>
          </div>

          <div class="mt-2">
            <!-- Desktop -->
            <div class="carousel-wrap-2 d-none d-sm-block">
              <div class="owl-carousel owl-theme category-wise-product-slider">
                <div v-for="(product, index) in category.products" :key="index">
                  <div class="product-card">
                    {{ product?.name || '' }}
                  </div>
                </div>
              </div>
            </div>

            <!-- Mobile -->
            <div class="d-sm-none">
              <div class="row g-2">
                <div class="col-6" v-for="(product, index) in category.products.slice(0, 4)" :key="index">
                  <div class="product-card">
                    {{ product?.name || '' }}
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script>
export default {
  props: {
    direction: {
      type: String,
      default: 'ltr'
    }
  },
  data() {
    return {
      translations: {
        view_all: '...'
      },
      categories: []
    };
  },
  computed: {
    isRtl() {
      return this.direction === 'rtl';
    }
  },
  mounted() {
    // this.fetchTranslation('view_all');
    this.fetchCategories();
  },
  methods: {
    fetchTranslation(key) {
      fetch(`vueAPI/translate?key=${encodeURIComponent(key)}`)
        .then(res => res.json())
        .then(data => {
          this.translations[key] = data.translation;
        })
        .catch(err => {
          console.error(`Error loading translation for ${key}`, err);
        });
    },
    fetchCategories() {
      fetch('vueAPI/home_categoray')
        .then(res => res.json())
        .then(data => {
          console.log(data);
          this.categories = data;
          console.log(this.categories.length);
        })
        .catch(err => {
          console.error(`Error loading categories`, err);
        });
    }
  }
}
</script>
