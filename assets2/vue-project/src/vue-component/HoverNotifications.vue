<template >
  <div class="hover-notifications" >
    <ul class="notification-items" >
      <li v-for="(item) in notifications" :key="item.key" class="notification-item " >
        <HoverNotificationItem :item="item" />
      </li >
    </ul >
  </div >
</template >

<script lang="ts" >
import Component, {mixins} from 'vue-class-component';
import {Prop, Watch} from "vue-property-decorator";
import HoverNotificationItem from "./HoverNotificationItem.vue";
import {_HoverNotification} from "./enums";
import Vue from "vue";
import {HandleAjax} from "../classes/HandleAjax";

declare var jQuery;
// @Component({
//   name      : 'HoverNotifications',
//   components: {HoverNotificationItem}
// })
// export default class HoverNotifications extends Vue {
@Component({
  // name      : 'HoverNotifications',
  components: {HoverNotificationItem}
})
export default class HoverNotifications extends Vue {
  public name                                     = 'Machine';
  public notifications: Array<_HoverNotification> = [];

  // @Prop({default: []}) readonly notifications!: Array<_HoverNotification>;

  created() {
    // console.log('created');
    jQuery('body').on('addNotification', (event, notification: _HoverNotification) => {
      // console.log('receiving trigger', {notification});
      this.notifications.push(notification);
    });
  }


  // @Watch('notifications')
  // notificationChange() {
  //   console.log('Changed330')
  //   this.$forceUpdate();
  // }
}
</script >

<style lang="scss" scoped >
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

</style >
