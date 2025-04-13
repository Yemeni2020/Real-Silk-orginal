<template>
  <div>
    <h1>مرحبا بك!</h1>
    <ul>
      <li v-for="category in categories" :key="category.id">
        {{ category.name }}
      </li>
    </ul>
  </div>
</template>

<script>
export default {
  props: {
    categories: Array
  }
}
</script>