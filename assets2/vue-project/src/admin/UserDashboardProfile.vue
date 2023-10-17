<template>
  <div class="user-profile">
    <div class="bg-white " style="max-width: 500px;margin: auto;">
      <table class="table">
        <tbody>
        <tr>
          <th>Username</th>
          <td>{{ username}} </td>
        </tr>
        <tr>
          <th>Email</th>
          <td>{{ email}} </td>
        </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
<script lang="ts">

import {defineComponent} from "vue";

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
    return {}
  },
  setup: (props, ctx) => {
    return {}
  },
  computed: {},
  created() {
  },
  methods: {}
});

</script>