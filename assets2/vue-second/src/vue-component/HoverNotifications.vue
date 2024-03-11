<template>
  <div class="hover-notifications">
    <ul class="notification-items">
      <li v-for="(item) in notifications" :key="item.key" class="notification-item ">
        <!--        <HoverNotificationItem :item="item"/>-->
      </li>
    </ul>
  </div>
</template>

<script lang="ts">
import {defineComponent} from "vue";
import type {_HoverNotification} from "@/vue-component/enums";
import {ENUM_NOTIFICATION_TYPE} from "@/vue-component/enums";
import {toast} from "vue3-toastify";
import HoverNotificationItem from "@/vue-component/HoverNotificationItem.vue";

export default defineComponent({
  name: 'HoverNotifications',
  components: {
    HoverNotificationItem
  },
  data: () => ({
    notifications: [] as Array<_HoverNotification>
  }),
  methods: {},
  created() {
    console.log('created');
    // @ts-ignore
    //@ts-ignore
    jQuery('body').on('addNotification', (event, notification: _HoverNotification) => {
      console.log('receiving trigger', {notification});
      // this.notifications.push(notification);
      console.log('notification', notification);

      if (notification.type === ENUM_NOTIFICATION_TYPE.SUCCESS) {
        toast.success(notification.text);
      } else {
        toast.error(notification.text);
      }
    });
  }
});
</script>

<style lang="scss" scoped>
.hover-notifications {
  padding: 10px;
  position: fixed;
  bottom: 0;
  left: 0;
  z-index: 9999999999999;

  ul.notification-items {
    list-style: none;
    padding: 0;
    margin: 0;
  }
}

</style>
