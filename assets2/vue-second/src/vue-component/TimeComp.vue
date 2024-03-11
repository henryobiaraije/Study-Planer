<template>
  <span v-if="null !== time">
      <small class="text-xs" style="font-size: 10px;"><code>{{ normalDatetime }}</code></small>

      <br/>
      <span class="text-xs">{{ formattedTime }} </span>
    </span>
</template>

<script lang="ts">

import {defineComponent} from "vue";
import * as timeago from 'timeago.js';

export default defineComponent({
  name: 'TimeComp',
  props: {
    time: {
      type: String,
      required: true,
      default: 0,
    }
  },
  data: () => ({
    toast: null,
  }),
  methods: {},
  mounted() {
  },
  computed: {
    formattedTime(): string {
      return timeago.format(this.time, 'en_US');
    },
    normalDatetime() {
      let date_ob = new Date('2021-12-08T18:21:54.000000Z');

// adjust 0 before single digit date
      let date = ("0" + date_ob.getDate()).slice(-2);

// current month
      let month = ("0" + (date_ob.getMonth() + 1)).slice(-2);

// current year
      let year = date_ob.getFullYear();

// current hours
      let hours = date_ob.getHours();

// current minutes
      let minutes = date_ob.getMinutes();

// current seconds
      let seconds = date_ob.getSeconds();

// prints date & time in YYYY-MM-DD HH:MM:SS format
      return year + "-" + month + "-" + date + " " + hours + ":" + minutes + ":" + seconds;
    }
  },
  created() {
    console.log('Time created');
  }
});
</script>

<style lang="scss" scoped>

</style>