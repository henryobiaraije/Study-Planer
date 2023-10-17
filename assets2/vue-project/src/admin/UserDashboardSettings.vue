<template>
  <div class="user-settings shadow p-4 rounded">
    <p v-if="loading">Loading...</p>
    <form v-if="!loading" @submit.prevent="timezones.updateUserTimezone" class="sp-wrapper ">
      <label class="block mb-2">
        <span class="block fs-5">Time zone</span>
        <select v-model="timezones.userTimeZone.value" required class="px-2 py-2 fs-5" style="max-width: 300px">
          <option value="">Select your timezone</option>
          <option v-for="(value,name) in theTimezones" :value="name">
            {{ value }}
          </option>
        </select>
      </label>
      <ajax-action
          button-text="Save"
          css-classes="button"
          icon="fa fa-save"
          :ajax="timezones.ajax">
      </ajax-action>
    </form>
  </div>
</template>
<script lang="ts">

import {defineComponent, PropType} from "vue";
import useTimezones from "@/composables/useTimezones";
import AjaxAction from "@/vue-component/AjaxAction.vue";

export default defineComponent({
  name: 'UserDashboardSettings',
  components: {AjaxAction},
  props: {
    theTimezones: {
      type: Array as PropType<Array<{ [key: string]: string }>>,
      required: true
    },
    loading: {
      type: Boolean,
      required: true
    },
    userTimeZone: {
      type: String,
      required: true
    },
    // Array<{ [key: string]: string }>
  },
  data() {
    return {}
  },
  setup: (props, ctx) => {
    return {
      timezones: useTimezones(),
    }
  },
  computed: {},
  created() {
  },
  methods: {},
  watch: {
    userTimeZone: {
      handler: function (val) {
        this.timezones.userTimeZone.value = val;
      },
      immediate: true
    }
  }
});

</script>