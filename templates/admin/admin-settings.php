<?php

use StudyPlanner\Initializer;
use StudyPlanner\Libs\Settings;


?>


<div class="admin-settings wrap">
    <h1 class="wp-heading-inline">Settings</h1>
    <br/>
    <div class=" all-loaded" style="display: none;">
        <form v-if="null !== useSettings.settings.value" @submit.prevent="useSettings._updateSettings" class="bg-sp-200 p-4 rounded-2 margin-auto"
              style="max-width: 500px; margin:auto;">
            <label class="bg-white p-2 rounded block mb-2 sp-slide-in">
                <span class="font-medium block mb-1">Mature card Days (27 by default)</span>
                <input v-model.number="useSettings.settings.value.mature_card_days"
                       class="bg-white border-1 border-gray-500 w-full" type="number">
            </label>
            <ajax-action
                    button-text="Save"
                    css-classes="button"
                    icon="fa fa-upload"
                    :ajax="useSettings.ajaxUpdate.value">
            </ajax-action>
        </form>
    </div>

    <hover-notifications></hover-notifications>
    <div class="all-loading" style="width: 100%;height: 400px;display: flex;align-items: center;">
        <div style="text-align: center;flex: 12;font-size: 50px;">
            <i class="fa fa-spin fa-spinner"></i></div>
    </div>
</div>
