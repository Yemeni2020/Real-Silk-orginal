<template>
  <div
    v-for="category in categories"
    :key="category.id"
    class="menu--caret-accordion"
  >
    <!-- المستوى الأول -->
    <div class="card-header flex-between" @click="toggle(category)">
      <label
        class="for-hover-label cursor-pointer get-view-by-onclick"
        :data-link="generateLink(category.id)"
      >
        {{ category.name }}
      </label>
      <div class="px-2 cursor-pointer menu--caret">
        <strong class="pull-right for-brand-hover">
          <i
            v-if="category.childes.length > 0"
            :class="category.show ? 'tio-chevron-up fs-13' : 'tio-chevron-right fs-13'"
          ></i>
        </strong>
      </div>
    </div>

    <!-- الأقسام الفرعية مع transition -->
    <transition name="accordion">
      <div v-show="category.show" class="card-body p-0 ms-2">
        <!-- المستوى الثاني -->
        <div
          v-for="child in category.childes"
          :key="child.id"
          class="menu--caret-accordion"
        >
          <div class="card-header flex-between" @click="toggle(child)">
            <label
              class="cursor-pointer get-view-by-onclick"
              :data-link="generateLink(child.id)"
            >
              {{ child.name }}
            </label>
            <div class="px-2 cursor-pointer menu--caret">
              <strong class="pull-right">
                <i
                  v-if="child.childes.length > 0"
                  :class="child.show ? 'tio-chevron-up fs-13' : 'tio-chevron-right fs-13'"
                ></i>
              </strong>
            </div>
          </div>

          <transition name="accordion">
            <div v-show="child.show" class="card-body p-0 ms-2">
              <!-- المستوى الثالث -->
              <div
                v-for="subChild in child.childes"
                :key="subChild.id"
                class="card-header"
              >
                <label
                  class="for-hover-label d-block cursor-pointer text-left get-view-by-onclick"
                  :data-link="generateLink(subChild.id)"
                >
                  {{ subChild.name }}
                </label>
              </div>
            </div>
          </transition>
        </div>
      </div>
    </transition>
  </div>
</template>


<script>
export default {
  data() {
    return {
      categories: [],
    };
  },
  mounted() {
    this.fetchCategories();
  },
  methods: {
    fetchCategories() {
      fetch("/vueAPI/ProductCategories")
        .then((res) => res.json())
        .then((data) => {
          // أضف خاصية show للتحكم بالفتح/الإغلاق
          const addShowFlag = (items) => {
            return items.map((item) => {
              item.show = false;
              if (item.childes && item.childes.length) {
                item.childes = addShowFlag(item.childes);
              }
              return item;
            });
          };

          this.categories = addShowFlag(data);
        })
        .catch((err) => {
          console.error("Error loading categories", err);
        });
    },
    toggle(item) {
      item.show = !item.show;
    },
    generateLink(categoryId) {
      return `/products?category_id=${categoryId}&data_from=category&page=1`;
    },
  },
};
</script>
<style scoped>
.accordion-enter-active,
.accordion-leave-active {
  transition: max-height 0.3s ease, opacity 0.3s ease;
  overflow: hidden;
}

.accordion-enter-from,
.accordion-leave-to {
  max-height: 0;
  opacity: 0;
}

.accordion-enter-to,
.accordion-leave-from {
  max-height: 500px; /* عدل الرقم حسب أقصى ارتفاع متوقع */
  opacity: 1;
}

</style>