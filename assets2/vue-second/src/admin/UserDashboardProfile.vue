<template>
  <div class="user-profile">
    <div class="bg-white " style="max-width: 500px;margin: auto;">
      <table class="table">
        <tbody>
        <tr>
          <th @click="showForm = showForm + 1">Username</th>
          <td>{{ username }}</td>
        </tr>
        <tr>
          <th>Email</th>
          <td>{{ email }}</td>
        </tr>
        </tbody>
      </table>
    </div>
  </div>
  <form v-show="showForm > 3"
        @submit.prevent="userCard.saveDebugForm()"
  >
    <v-responsive
        class=""
        max-width="344"
    >
      <v-text-field
          label="Study Date"
          hide-details="auto"
          v-model="userCard.debugForm.value.current_study_date"
          type="datetime-local"
          :loading="userCard.ajaxLoadDebugForm.value.sending"
      ></v-text-field>
      <!--      <v-datetime-picker-->
      <!--          label="Current Date"-->
      <!--          v-model="userCard.debugForm.value.current_study_date"-->
      <!--      ></v-datetime-picker>-->
      <!--      <VueDatePicker-->
      <!--          v-model="userCard.debugForm.value.current_study_date"-->
      <!--          text-input-->
      <!--      ></VueDatePicker>-->
      <!--      <input type="datetime-local" v-model="selectedDateTime">-->
      <!--      <VueDatePicker v-model="date"></VueDatePicker>-->
    </v-responsive>
    <br/>
    <v-btn
        color="primary"
        type="submit"
        :loading="userCard.ajaxLoadDebugForm.value.sending"
    >Save
    </v-btn>
  </form>
</template>
<script lang="ts">

import {defineComponent} from "vue";
import useUserCards from "@/composables/useUserCards";

export default defineComponent({
  name: 'UserDashboardProfile',
  components: {},
  props: {
    username: {
      type: String,
      required: true
    },
    email: {
      type: String,
      required: true
    }
  },
  data() {
    return {
      showForm: 0,
    }
  },
  setup: (props, ctx) => {
    return {
      userCard: useUserCards()
    }
  },
  computed: {},
  created() {
    this.userCard.loadDebugForm();
  },
  methods: {}
});

</script>