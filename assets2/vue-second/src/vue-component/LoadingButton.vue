<template >
  <div >
    <!--    <div v-if="ajax.success" class="alert btn-success animated zoomIn" >{{ ajax.successMessage }}</div >-->
    <div style="margin: 10px 0;" :style="{'text-align': align}" >
      <!--      <button v-if="showClose" type="button" class="button button-link-delete" data-bs-dismiss="modal" >Close</button >-->
      <button @click="startSending" :disabled="ajax.sending || disabled" type="button" class="btn btn-primary" >{{ buttonText }}
        <i v-if="ajax.sending" class="fa fa-spin fa-spinner" ></i ></button >
    </div >
    <!--    <div v-if="ajax.error" class="alert btn-danger animated zoomIn" >{{ ajax.errorMessage }}</div >-->
  </div >
</template >

<script lang="ts" >

import Vue from 'vue'
import Component, {mixins} from 'vue-class-component';
import {Prop} from "vue-property-decorator";
import {_Ajax} from "../classes/HandleAjax";

// Define the props by using Vue's canonical way.


// HelloWorld class will be a Vue component
@Component
export default class AjaxAction extends Vue {
  // Declared as component data
  public message = 'Hello World!';

  @Prop() ajax: _Ajax;
  @Prop({default: 'Submit'}) readonly buttonText: String;
  @Prop({default: false}) readonly showClose: boolean;
  @Prop({default: 'right'}) readonly align: string;
  @Prop({default: false}) readonly disabled: boolean;


  public startSending(event): void {
    this.$emit('click');
    // if (this.ajax.sending) {
    //   event.preventDefault();
    // }
  }
}
</script >

<style lang="scss" scoped >

</style >