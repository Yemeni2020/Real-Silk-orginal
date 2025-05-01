<template>
  <CategorySkeleton v-if="isLoading" :rows="8" />
  <div class="col-12">
    <div>
      <h6 class="font-semibold d-inline-block fs-15 mb-2">{{ t_shipping_method }}</h6>
      <label for="sorting">
        <select @change="chose_method($event)" class="form-control custom-select">
          <option selected disabled>Choose</option>
          <option v-for="method in shippingMethods" :key="method.id" :value="method.id">
            {{ method.title }}
          </option>
        </select>
      </label>
    </div>
  </div>
</template>
<script>
import CategorySkeleton from "../partial/CategorySkeleton.vue";

export default {
  name: "ShippingMethod",
  components: {
    CategorySkeleton,
  },
  props: {
    shippingMethods: {
      type: Array,
      required: true,
    },
  },
  data() {
    return {
      isLoading: false, // أو اجعلها prop حسب احتياجك
      t_shipping_method: 'shipping_method',
    };
  },
  async mounted() {
    this.t_shipping_method = await this.$translate(this.t_shipping_method);
    console.log(this.shippingMethods);
  },
  methods: {
    chose_method($event) {
      const selectedId = event.target.value;
      fetch(this.$getApiUrl("product_categories/"+selectedId))
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
          this.isLoading = false;
        })
        .catch((err) => {
          console.error("Error loading categories", err);
          this.isLoading = false;
        });
    }
  },
};
</script>