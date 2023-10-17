<template>
  <div class="user-profile">
    <div v-if="null !== useUserProfile.profile.value" class="bg-white " style="max-width: 500px;margin: auto;">
      <table class="table">
        <tbody>
        <tr>
          <th>Username</th>
          <td>{{ useUserProfile.profile.value.user_name }}</td>
        </tr>
        <tr>
          <th>Email</th>
          <td>{{ useUserProfile.profile.value.user_email }}</td>
        </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
<script lang="ts">

import {defineComponent} from "vue";
import useUserProfile from "@/composables/useUserProfile";

export default defineComponent({
  name: 'UserDashboardProfile',
  components: {},
  props: {},
  data() {
    return {}
  },
  setup: (props, ctx) => {
    return {
      useUserProfile: useUserProfile(),
    }
  },
  computed: {},
  created() {

  },
  methods: {}
});

</script>