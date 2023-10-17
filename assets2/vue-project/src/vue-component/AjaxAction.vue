<template>
  <component :is="el">
    <button @click="startSending" :disabled="ajax.sending" type="submit"
            :class="[
                cssClasses,
                'border border-solid border-sp-400 px-4 py-2 rounded-md',
                'bg-sp-500 text-white font-bold',
                'hover:bg-sp-700 hover:text-white',
              ]"
    >{{ buttonText }}
      <i v-if="icon.length > 0 && !ajax.sending" :class="[icon]"></i>
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
  }
});

function startSending(event) {
  emit('click');
}

</script>

<style lang="scss" scoped>

</style>