<?php

	use StudyPlanner\Libs\Common;

	global $wp;
	$current_url = home_url( $wp->request );

	$url_dg       = $current_url . '?' . http_build_query( array_merge( $_GET, array( "dashboard-page" => "deck-groups" ) ) );
	$url_stats    = $current_url . '?' . http_build_query( array_merge( $_GET, array( "dashboard-page" => "stats" ) ) );
	$url_profile  = $current_url . '?' . http_build_query( array_merge( $_GET, array( "dashboard-page" => "profile" ) ) );
	$url_settings = $current_url . '?' . http_build_query( array_merge( $_GET, array( "dashboard-page" => "settings" ) ) );


?>


<div class="sp-sc-ud" >
	<span class="reset-vue" @click="resetVue()" ></span >
	<?php /*** Tabs **/ ?>
	<div class="sp-tab flex gap-2 justify-content-center my-4 all-loaded" style="display: none" >
		<div class="sp-one-tab " >
			<a href="<?php echo esc_url_raw( $url_dg ); ?>" class=" text-sp-800 px-4 py-2 fs-5 rounded-t-2xl hover:bg-sp-400 :hover:text-white cursor-pointer
			text-decoration-none bg-sp-200"
			   @click.prevent="gotoMenu('deck-groups')"
			   :class="{'font-bold bg-sp-500 text-white ' : menu === 'deck-groups'}"
			>Deck Groups</a >
		</div >
		<div class="sp-one-tab " >
			<a href="<?php echo esc_url_raw( $url_settings ); ?>"
			   class=" text-sp-800 px-4 py-2 fs-5 rounded-t-2xl hover:bg-sp-400 :hover:text-white cursor-pointer text-decoration-none bg-sp-200"
			   @click.prevent="gotoMenu('stats')"
			   :class="{'font-bold bg-sp-500 text-white ' : menu === 'stats'}" >Stats</a >
		</div >
		<div class="sp-one-tab " >
			<a href="<?php echo esc_url_raw( $url_settings ); ?>"
			   class=" text-sp-800 px-4 py-2 fs-5 rounded-t-2xl hover:bg-sp-400 :hover:text-white cursor-pointer text-decoration-none bg-sp-200"
			   @click.prevent="gotoMenu('settings')"
			   :class="{'font-bold bg-sp-500 text-white ' : menu === 'settings'}" >Settings</a >
		</div >
		<div class="sp-one-tab " >
			<a href="<?php echo esc_url_raw( $url_profile ); ?>" class=" text-sp-800 px-4 py-2 fs-5 rounded-t-2xl hover:bg-sp-400 :hover:text-white cursor-pointer text-decoration-none
			bg-sp-200"
			   @click.prevent="gotoMenu('profile')"
			   :class="{'font-bold bg-sp-500 text-white ' : menu === 'profile'}" >Profile</a >
		</div >
	</div >


	<div class="all-loaded" style="display: none" >

		<?php /** Deck groups */ ?>
		<div v-if="menu === 'deck-groups'" class="section-deck-groups" >
			<?php \StudyPlanner\load_template( 'shortcodes/dashboard/deck-groups' ); ?>
		</div >
		<div v-if="menu === 'stats'" class="section-stats" >
			<?php \StudyPlanner\load_template( 'shortcodes/dashboard/stats' ); ?>
		</div >
		<div v-if="menu === 'settings'" class="section-settings" >
			<?php \StudyPlanner\load_template( 'shortcodes/dashboard/settings' ); ?>
		</div >
		<div v-if="menu === 'profile'" class="section-profile" >
			<?php \StudyPlanner\load_template( 'shortcodes/dashboard/profile' ); ?>
		</div >
	</div >

	<?php /** Edit Study Modal */ ?>
	<?php \StudyPlanner\load_template( 'shortcodes/dashboard/study-modal' ); ?>

	<?php /** Question display modal */ ?>
	<?php \StudyPlanner\load_template( 'shortcodes/dashboard/question-modal' ); ?>

	<?php /** Question display modal */ ?>
	<?php \StudyPlanner\load_template( 'shortcodes/dashboard/study-complete-modal' ); ?>

	<?php /***** Notifications ***/ ?>
	<hover-notifications ></hover-notifications >
	<div class="all-loading" style="width: 100%;height: 400px;display: flex;align-items: center;" >
		<div style="text-align: center;flex: 12;font-size: 50px;" >
			<i class="fa fa-spin fa-spinner" ></i ></div >
	</div >
</div >
