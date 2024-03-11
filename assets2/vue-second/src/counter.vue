<template lang="html">
  <div>
    <p>Count: {{ count }}</p>
    <button @click="increment">Increment</button>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
  name: 'Counter',
  components: {
  },
  props: {},
  data() {
    return {
      count: 0
    }
  },
  setup: (props, ctx) => {

    return {

    }
  },
  computed: {
    },
  created() {

  },
  methods: {
    increment() {
      this.count++
    }
  }
});

</script>
<style src="./scss/counter.scss"></style>