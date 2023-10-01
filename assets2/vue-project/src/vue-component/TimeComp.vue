<template >
  <span >
    <span v-if="null !==time" >
      <small class="text-xs" style="font-size: 10px;" ><code >{{ normalDatetime }}</code ></small >
      <br />
      <span class="text-xs" >{{ formattedTime }} </span >
    </span >
  </span >
</template >

<script lang="ts" >

import Vue from 'vue'
import Component, {mixins} from 'vue-class-component';
import {Prop, Watch} from "vue-property-decorator";
import {_Ajax} from "../classes/HandleAjax";
import * as timeago from "timeago.js";
// Define the props by using Vue's canonical way.

// HelloWorld class will be a Vue component
@Component
export default class AjaxAction extends Vue {
  @Prop({default: 0, required: true}) time: string;

  public get formattedTime(): string {
    return timeago.format(this.time, 'en_US');
  }

  public get normalDatetime() {
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
    const datetime = year + "-" + month + "-" + date + " " + hours + ":" + minutes + ":" + seconds;
    return datetime;
  }

  public created() {
    console.log('Time created');
  }

}
</script >

<style lang="scss" scoped >

</style >