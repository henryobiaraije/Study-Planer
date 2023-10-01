<template>
  <div>Testing class comp here</div>
  <input type="radio" v-model="valueAgent" value="foo">
</template>
<script lang="ts">
// DO NOT USE <script setup>

//vue options API.
/*
import { defineComponent } from "vue";
export default defineComponent({});
*/

//class component
import {Component, Vue, toNative, Prop, Vanilla, Model} from "vue-facing-decorator";

@Component
class VueHi extends Vue {
  property = 'property value';
  @Prop({
    default: 'foo'
  })
  p!: string;
  foo = ''
  @Vanilla
  set setter(bar: string) {
    this.foo = bar
  }
  @Model
  valueAgent!: string;

}

export default toNative(VueHi)
</script>
<style>
</style>