<template>
  <li v-if="item" @dblclick="closeToast()" :key="item.key" class="notification-item ">
    <div :id="'item-'+item.key" :class="{'bg-danger' : item.type === 'error','bg-success' : item.type === 'success'}"
         class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body">
          {{ item.text }}{{ item.additionalMessage }}
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                aria-label="Close"></button>
      </div>
    </div>
  </li>
</template>
<script lang="ts">
import type {_HoverNotification} from "@/vue-component/enums";
import {defineComponent, PropType} from "vue";
import {ENUM_NOTIFICATION_TYPE} from "@/vue-component/enums";

export default defineComponent({
  props: {
    item: {
      type: Object as PropType<_HoverNotification>,
      required: true,
    }
  },
  data: () => ({
    toast: null,
  }),
  methods: {
    closeToast() {
      this.toast.hide();
    }
  },
  mounted() {
    setTimeout(() => {
      if (!this.item) return;
      // console.log('created item');
      const elem = document.getElementById('item-' + this.item.key);
      // console.log('elem', {elem}, this.item);
      let delay = 5000;
      if (this.item.type === ENUM_NOTIFICATION_TYPE.ERROR) {
        delay = 30000;
      }
      const toast = new bootstrap.Toast(elem, {
        animation: true,
        delay: delay,
      })
      this.toast = toast;
      toast.show();
    }, 200);
  }
});
</script>


<style lang="scss" scoped>
li {
  margin-bottom: 5px;
}
</style>
