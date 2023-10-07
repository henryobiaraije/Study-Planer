<template>
  <comment :is="el" style="margin: 10px 0;" :style="{'text-align': align}">
    <button @click="startSending" :disabled="ajax.sending" type="button" :class="cssClasses">{{ buttonText }}
      <i v-if="icon.length > 0 && !ajax.sending" :class="[icon]"></i>
      <i v-if="ajax.sending" class="fa fa-spin fa-spinner"></i>
    </button>
  </comment>
</template>

<script lang="ts">

import {defineComponent} from "vue";
import type {_Ajax} from "@/classes/HandleAjax";

export default defineComponent({
  data: () => ({
    message: 'Hello World!',
  }),
  props: {
    ajax: {
      type: Object as () => _Ajax,
      required: true,
      default: false,
    },
    el: {
      type: String,
      required: false,
      default: 'span',
    },
    buttonText: {
      type: String,
      required: false,
      default: 'Submit',
    },
    showClose: {
      type: Boolean,
      required: false,
      default: true,
    },
    align: {
      type: String,
      required: false,
      default: 'left',
    },
    cssClasses: {
      type: String,
      required: false,
      default: 'button button-primary',
    },
    icon: {
      type: String,
      required: false,
      default: '',
    },
  },
  methods: {
    startSending(event) {
      // if (this.ajax.sending) {
      //   event.preventDefault();
      // }
      this.$emit('click');
    }
  },
});

</script>

<style lang="scss" scoped>

</style>