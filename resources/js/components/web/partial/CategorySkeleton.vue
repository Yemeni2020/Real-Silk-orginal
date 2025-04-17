<template>
  <div class="skeleton-wrapper">
    <div class="skeleton-category" v-for="n in rows" :key="n"></div>
  </div>
</template>

<script>
export default {
  name: 'CategorySkeleton',
  props: {
    rows: {
      type: Number,
      default: 6, // عدد الأسطر الافتراضي
    },
  },
};
</script>

<style scoped>
.skeleton-wrapper {
  margin: 10px 0;
  width: 100% !important;
}

.skeleton-category {
  height: 30px;
  /* background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%); */
  background: linear-gradient(90deg, #e0d1d1 25%, var(--web-primary) 50%, var(--web-secondary) 75%);
  background-size: 200% 100%;
  animation: loading 1.2s infinite;
  margin-bottom: 10px;
  border-radius: 5px;
}

@keyframes loading {
  0% {
    background-position: 200% 0;
  }

  100% {
    background-position: -200% 0;
  }
}
</style>