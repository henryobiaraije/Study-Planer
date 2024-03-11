declare module "*.vue" {
  // @ts-ignore
  import Vue from "vue";
  import { ComponentOptions } from 'vue'
  const component: ComponentOptions
  export default component;
}
declare module 'vue-good-table'{
  import VueGoodTable from 'vue-good-table';
}