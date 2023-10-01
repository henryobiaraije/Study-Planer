<template >
  <comment :is="el" style="margin: 10px 0;" :style="{'text-align': align}" >
    <button @click="startSending" :disabled="ajax.sending" type="button" :class="cssClasses" >{{ buttonText }}
      <i v-if="icon.length > 0 && !ajax.sending" :class="[icon]" ></i >
      <i v-if="ajax.sending" class="fa fa-spin fa-spinner" ></i >
    </button >
  </comment >
</template >

<script lang="ts" >

import Vue from 'vue'
import Component, {mixins} from 'vue-class-component';
import {Prop, Watch} from "vue-property-decorator";
import {_Ajax} from "../classes/HandleAjax";

// Define the props by using Vue's canonical way.


// HelloWorld class will be a Vue component
@Component
export default class AjaxActionNotForm extends Vue {
  // Declared as component data
  public message = 'Hello World!';

  @Prop({default: false, required: true}) readonly ajax: _Ajax;
  @Prop({default: 'span'}) readonly el: String;
  @Prop({default: 'Submit'}) readonly buttonText: String;
  @Prop({default: true}) readonly showClose: boolean;
  @Prop({default: 'left'}) readonly align: string;
  @Prop({default: 'button button-primary'}) readonly cssClasses: string;
  @Prop({default: ''}) readonly icon: string;


  public startSending(event): void {
    // if (this.ajax.sending) {
    //   event.preventDefault();
    // }
    this.$emit('click');
  }

  // @Watch('ajax.sending')
  // onAjaxChange(old, newValue) {
  //   console.log({old, newValue})
  // }

}
</script >

<style lang="scss" scoped >

</style >