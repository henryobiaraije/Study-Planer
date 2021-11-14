import VueCompositionAPI from '@vue/composition-api';
import Vue from "vue";
import PickImage from "../vue-component/PickImage.vue";

Vue.use(VueCompositionAPI);
Vue.component('pick-image', PickImage);
