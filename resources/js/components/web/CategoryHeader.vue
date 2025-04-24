<template>
  <div class="container">
    <div class="category-menu-wrap">
      <ul class="category-menu">
        <CategorySkeleton v-if="isLoading" :rows="8" />

        <li v-for="category in categories" :key="category.id" class="has-sub-item">
          <a :href="getProductLink(category.id)">
            <span>{{ category.name }}</span>
            <i v-if="category.childes.length" class="tio-chevron-right fs-12"></i>
          </a>

          <div class="mega_menu_parent z-2" v-if="category.childes.length">
            <div class="row">
              <div class="col-9">
                <div class="mega_menu" v-if="category.childes.length">
                  <div v-for="sub in category.childes" :key="sub.id" class="mega_menu_inner">
                    <h6>
                      <a :href="getProductLink(sub.id)">
                        {{ sub.name }}
                      </a>
                    </h6>
                    <div v-for="subSub in sub.childes" :key="subSub.id">
                      <a :href="getProductLink(subSub.id)">
                        {{ subSub.name }}
                      </a>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-3" v-if="category.image_ad">
                <img :src="getImageUrl(category.adv_full_url, 'category')" alt="Category Image" />
              </div>

              <div class="col-12" v-if="category.brands_data?.length">
                <div class="mt-sm-3 mb-3 brand-slider">
                  <div class="owl-carousel owl-theme p-2 brands-slider">
                    <div v-for="brand in category.brands_data" :key="brand.id" class="text-center">
                      <a :href="getBrandLink(brand.id)" class="__brand-item">
                        <img :src="getImageUrl(brand.image_full_url, 'brand')" :alt="brand.image_alt_text" />
                      </a>
                      <span>{{ brand.name }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </li>

        <li class="text-center">
          <a href="/categories" class="text-primary font-weight-bold justify-content-center">
            {{ translate('View_All') }}
          </a>
        </li>
      </ul>
    </div>
  </div>
</template>

<script>
import CategorySkeleton from "./partial/CategorySkeleton.vue";

export default {
  name: "category-menu-header",
  components: {
    CategorySkeleton
  },
  data() {
    return {
      categories: [],
      isLoading:true,
    };
  },
  mounted() {
    fetch(this.$getApiUrl('menu_categories'))
      .then(res => res.json())
      .then(data => {
        this.categories = data;
        this.isLoading=false;
        this.$nextTick(() => {
          $('.brands-slider').owlCarousel({ items: 5 });
        });
      });
  },
  methods: {
    getProductLink(id) {
      return `/products?category_id=${id}&data_from=category&page=1`;
    },
    getBrandLink(id) {
      return `/products?brand_id=${id}&data_from=brand&page=1`;
    },
    getImageUrl(path, type) {
      return `${path.path}`;
    },
    translate(key) {
      // استخدم دالة ترجمة JS هنا أو استورد من config
      return key;
    }
  }
};
</script>
