<template>
  <CategorySkeleton v-if="isLoading" :rows="8" />
  <div v-else
    class="text-body col-lg-12 col-md-12 overflow-scroll fs-13 text-justify details-text-justify rich-editor-html-content">
    <div v-html="details"></div>
  </div>
</template>
<script>
import CategorySkeleton from "../partial/CategorySkeleton.vue";

export default {
  name: "detailsProduct",
  components: {
    CategorySkeleton
  },
  props: {
    id: {
      type: Number,
      default: 0,
    }
  },
  data() {
    return {
      details: 'product details not found',
      isLoading: true,

    }
  },
  mounted() {
    console.log(this.$getApiUrl('test'));

    this.fetchData();
  },
  methods: {
    fetchData() {
      fetch(this.$getApiUrl('product/details/' + this.id))
        .then(res => res.json())
        .then(data => {

          // console.log(data);
          this.details = data;
          this.isLoading = false;
        })
        .catch(err => {
          console.error(`Error loading categories ddd=====`);
          console.error(`Error loading categories`, err);
          this.isLoading = false;
          this.details = 'product details not found';
        });
    }
  }

}
</script>