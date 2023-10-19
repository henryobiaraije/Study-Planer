<template>
  <component :is="el">
    <button @click="startSending" :disabled="ajax.sending || disable" type="submit"
            :class="[
                cssClasses,
                'border border-solid border-sp-400 px-4 py-2 rounded-md',
                '!bg-sp-500 !text-white !font-bold',
                '!hover:bg-sp-800 !hover:text-white',
                'disabled:opacity-50 disabled:cursor-not-allowed',
              ]"
    >{{ buttonText }}
      <template v-if="showIcon">
        <i v-if="icon.length > 0 && !ajax.sending" :class="[icon]"></i>
      </template>
      <i v-if="ajax.sending" class="fa fa-spin fa-spinner"></i>
    </button>
  </component>
</template>

<script setup lang="ts">

import type {PropType} from "vue";
import type {_Ajax} from "@/classes/HandleAjax";

const emit = defineEmits(['click']);
const props = defineProps({
  ajax: {
    type: Object as PropType<_Ajax>,
    required: true
  },
  el: {
    type: String,
    default: 'span'
  },
  buttonText: {
    type: String,
    default: 'Submit'
  },
  showClose: {
    type: Boolean,
    default: true
  },
  align: {
    type: String,
    default: 'left'
  },
  cssClasses: {
    type: String,
    default: 'button button-primary'
  },
  icon: {
    type: String,
    default: ''
  },
  showIcon: {
    type: Boolean,
    default: true
  },
  disable: {
    type: Boolean,
    default: false
  }
});

function startSending(event) {
  emit('click');
}

</script>

<style lang="scss" scoped>

</style>