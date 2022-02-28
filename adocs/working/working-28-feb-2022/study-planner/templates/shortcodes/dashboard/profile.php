<?php
?>


<div class="user-profile">
    <div v-if="null !== useUserProfile.profile.value" class="bg-white " style="max-width: 500px;margin: auto;">
        <table class="table">
            <tbody>
            <tr>
                <th>Username</th>
                <td>{{useUserProfile.profile.value.user_name}}</td>
            </tr>
            <tr>
                <th>Email</th>
                <td>{{useUserProfile.profile.value.user_email}}</td>
            </tr>
            </tbody>
        </table>
    </div>
</div>
