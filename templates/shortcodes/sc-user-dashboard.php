<?php

use StudyPlannerPro\Libs\Common;

global $wp;
$current_url = home_url($wp->request);

$url_dg       = $current_url.'?'.http_build_query(array_merge($_GET, array("dashboard-page" => "deck-groups")));
$url_stats    = $current_url.'?'.http_build_query(array_merge($_GET, array("dashboard-page" => "stats")));
$url_profile  = $current_url.'?'.http_build_query(array_merge($_GET, array("dashboard-page" => "profile")));
$url_settings = $current_url.'?'.http_build_query(array_merge($_GET, array("dashboard-page" => "settings")));


?>


<div class="sp sp-sc-ud">
    <span class="reset-vue" @click="resetVue()"></span>
    <?php /*** Tabs **/ ?>
    <div class="sp-tab flex gap-2 justify-content-center my-4 all-loaded" style="display: none">
        <div class="sp-one-tab ">
            <a href="<?php echo esc_url_raw($url_dg); ?>" class="px-2 whitespace-nowrap md:px-4 py-2 fs-5 rounded-t-2xl hover:bg-sp-400  hover:text-white focus:text-white  cursor-pointer
			text-decoration-none bg-sp-200"
               @click.prevent="gotoMenu('deck-groups')"
               :class="[menu === 'deck-groups' ? 'font-bold bg-sp-500 text-white' : 'text-sp-800']"
            >Deck Groups</a>
        </div>
        <div class="sp-one-tab ">
            <a href="<?php echo esc_url_raw($url_settings); ?>"
               class="px-2 whitespace-nowrap  md:px-4 py-2 fs-5 rounded-t-2xl hover:bg-sp-400 hover:text-white focus:text-white cursor-pointer text-decoration-none bg-sp-200"
               @click.prevent="gotoMenu('stats')"
               :class="[menu === 'stats' ? 'font-bold bg-sp-500 text-white' : 'text-sp-800']">Stats</a>
        </div>
        <div class="sp-one-tab ">
            <a href="<?php echo esc_url_raw($url_settings); ?>"
               class="px-2 whitespace-nowrap text-sp-800 md:px-4 py-2 fs-5 rounded-t-2xl hover:bg-sp-400  hover:text-white focus:text-white  cursor-pointer text-decoration-none bg-sp-200"
               @click.prevent="gotoMenu('settings')"
               :class="[menu === 'settings' ? 'font-bold bg-sp-500 text-white' : 'text-sp-800']">Settings</a>
        </div>
        <div class="sp-one-tab ">
            <a href="<?php echo esc_url_raw($url_profile); ?>"
               class="px-2 whitespace-nowrap text-sp-800 md:px-4 py-2 fs-5 rounded-t-2xl hover:bg-sp-400  hover:text-white focus:text-white  cursor-pointer text-decoration-none
			bg-sp-200"
               @click.prevent="gotoMenu('profile')"
               :class="[menu === 'profile' ? 'font-bold bg-sp-500 text-white' : 'text-sp-800']">Profile</a>
        </div>
    </div>


    <div class="all-loaded" style="display: none">

        <?php /** Deck groups */ ?>
        <div v-if="menu === 'deck-groups'" class="section-deck-groups">
            <?php \StudyPlannerPro\load_template('shortcodes/dashboard/deck-groups'); ?>
        </div>
        <div v-if="menu === 'stats'" class="section-stats">
            <?php \StudyPlannerPro\load_template('shortcodes/dashboard/stats'); ?>
        </div>
        <div v-if="menu === 'settings'" class="section-settings">
            <?php \StudyPlannerPro\load_template('shortcodes/dashboard/settings'); ?>
        </div>
        <div v-if="menu === 'profile'" class="section-profile">
            <?php \StudyPlannerPro\load_template('shortcodes/dashboard/profile'); ?>
        </div>
    </div>

    <?php /** Question display modal */ ?>
    <div>
        <?php \StudyPlannerPro\load_template('shortcodes/dashboard/study-complete-modal'); ?>
    </div>


    <?php /** Edit Study Modal */ ?>
    <div>
        <?php \StudyPlannerPro\load_template('shortcodes/dashboard/study-modal'); ?>
    </div>

    <?php /** Question display modal */ ?>
    <div>
        <?php \StudyPlannerPro\load_template('shortcodes/dashboard/question-modal'); ?>
    </div>

    <?php /** Modal Chart Deck's card type */ ?>
    <div>
        <?php \StudyPlannerPro\load_template('shortcodes/dashboard/modal-decks-card-types-chart'); ?>
    </div>


    <?php /***** Notifications ***/ ?>
    <hover-notifications></hover-notifications>
    <div class="all-loading" style="width: 100%;height: 400px;display: flex;align-items: center;padding: 0">
        <div style="text-align: center;flex: 12;font-size: 50px;">
            <i class="fa fa-spin fa-spinner"></i></div>
    </div>
</div>
