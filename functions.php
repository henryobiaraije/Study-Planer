<?php

namespace StudyPlannerPro;

use DateTime;
use Model\Study;
use Phinx\Console\PhinxApplication;
use Phinx\Wrapper\TextWrapper;
use StudyPlannerPro\Db\Initialize_Db;
use StudyPlannerPro\Libs\Common;
use StudyPlannerPro\Libs\Settings;
use StudyPlannerPro\Services\Log_Service;

function load_template( $template ) {
	require __DIR__ . '/templates/' . $template . '.php';
}

function get_template_path( $template ) {
	return __DIR__ . '/templates/' . $template . '.php';
}

function get_all_card_grades() {
	$default = array( 'again', 'hard', 'good', 'easy', 'hold' );

	return apply_filters( 'sp_all_card_grades', $default );
}

function get_user_timezone_minutes_to_add( $user_id ) {

	$timezones     = Common::get_time_zones();
	$user_timezone = get_user_meta( $user_id, Settings::UM_USER_TIMEZONE, true );
	if ( empty( $user_timezone ) ) {
		return 0;
	}
	if ( array_key_exists( $user_timezone, $timezones ) ) {
		$zone = $timezones[ $user_timezone ];
		$re   = '/GMT[-+]{1}[0-9]{1,2}:[-0-9]{1,3}/m';
		$str  = $zone;
		preg_match( $re, $str, $matches );
		if ( ! empty( $matches ) ) {
			$m_first         = $matches[0];
			$operator        = substr( $m_first, 3, 1 );
			$hour_and_minute = substr( $m_first, 3 );
			// $hour_and_minute = trim( $hour_and_minute, 'T' );
			$explode       = explode( ':', $hour_and_minute );
			$hour          = (int) $explode[0];
			$minute        = (int) $explode[1];
			$_hour         = $hour * 60;
			$total_minutes = $_hour + $minute;
			// Common::send_error( [
			// '$zone'            => $zone,
			// '$matches'         => $matches,
			// '$m_first'         => $m_first,
			// '$operator'        => $operator,
			// '$hour_and_minute' => $hour_and_minute,
			// '$hour'            => $hour,
			// '$minute'          => $minute,
			// '$explode'         => $explode,
			// '$total_minutes'   => $total_minutes,
			// '$_hour'           => $_hour,
			// ] );
			// dd( $zone, $matches, $m_first );
			// dd( $zone, $matches, $m_first );
			return $total_minutes;
		}

		return 0;
	}
}

function get_user_timezone_date_early_morning_today( $user_id ) {
	$user_timezone_minutes_from_now = get_user_timezone_minutes_to_add( $user_id );

	$_date_today = Common::getDateTime();
//	Common::send_error( array(
//		__METHOD__,
//		__LINE__
//	) );
	$_datetime = new DateTime( $_date_today );
	$_datetime->modify( "$user_timezone_minutes_from_now minutes" );
	$_datetime->setTime( 0, 0, 0 );
	$the_date = $_datetime->format( 'Y-m-d H:i:s' );

	return $the_date;
}

function get_user_timezone_date_midnight_today( $user_id ) {
	$user_timezone_minutes_from_now = get_user_timezone_minutes_to_add( $user_id );
	$_date_today                    = Common::getDateTime();
	$_datetime                      = new DateTime( $_date_today );
	$_datetime->modify( "$user_timezone_minutes_from_now minutes" );
	$_datetime->setTime( 23, 59, 59 );
	$the_date = $_datetime->format( 'Y-m-d H:i:s' );

	return $the_date;
}

function get_default_image_display_type() {
	$default = array(
		'hide_all_ask_one',
		'hide_all_ask_all',
		'hide_one_ask_one',
	);

	return \apply_filters( 'sp_default_image_display_type', $default );
}

function get_mature_card_days() {
	$option = (int) get_option( Settings::OPTION_MATURED_CARD_DAYS, 27 );
	if ( empty( $option ) ) {
		return 27;
	}

	return $option;
}


function get_uncategorized_deck_group_id() {
	$id = get_option( Settings::OP_UNCATEGORIZED_DECK_GROUP_ID, 0 );
	if ( ! empty( $id ) ) {
		return $id;
	}
	Initialize_Db::get_instance()->create_default_rows();
	$id = get_option( Settings::OP_UNCATEGORIZED_DECK_GROUP_ID, 0 );

	return $id;
}

function get_uncategorized_deck_id() {
	$id = get_option( Settings::OP_UNCATEGORIZED_DECK_ID, 0 );
	if ( ! empty( $id ) ) {
		return $id;
	}
	Initialize_Db::get_instance()->create_default_rows();
	$id = get_option( Settings::OP_UNCATEGORIZED_DECK_ID, 0 );

	return $id;
}

function get_uncategorized_topic_id() {
	$id = get_option( Settings::OP_UNCATEGORIZED_TOPIC_ID, 0 );
	if ( ! empty( $id ) ) {
		return $id;
	}
	Initialize_Db::get_instance()->create_default_rows();

	return (int) get_option( Settings::OP_UNCATEGORIZED_TOPIC_ID, 0 );
}

/**
 * Get Log Service instance.
 *
 * @return Log_Service
 */
function mp_log(): ?Log_Service {
	return Initializer::get_instance()->log_service;
}


function get_card_group_background_image( $cg_image_id ) {
	$image_url = wp_get_attachment_image_url( $cg_image_id );
	if ( ! $image_url ) {
		$cg_image_id = get_option( Settings::OP_DEFAULT_CARD_BG_IMAGE, 0 );
		$image_url   = wp_get_attachment_image_url( $cg_image_id );
	}

	return $image_url;
}

/**
 * Save user's debug form data.
 *
 * @param int $user_id
 * @param string $current_study_date
 */
function sp_save_user_debug_form( int $user_id, string $current_study_date ): void {
	update_user_meta( $user_id, Settings::UM_CURRENT_STUDY_DATE, $current_study_date );
}

/**
 * Get user's debug form data.
 *
 * @param int $user_id
 *
 * @return array
 */
function sp_get_user_debug_form( int $user_id = null ): array {
	if ( null === $user_id ) {
		$user_id = get_current_user_id();
	}

	$current_study_date = get_user_meta( $user_id, Settings::UM_CURRENT_STUDY_DATE, true );
	if ( empty( $current_study_date ) ) {
		$current_study_date = date( 'Y-m-d H:i:s' );
	}

	// format to: 2020-12-31 23:59:59.
	$current_study_date = date( 'Y-m-d H:i:s', strtotime( $current_study_date ) );

	return array(
		'current_study_date' => $current_study_date,
	);
}

/**
 * Get user's study object.
 * Only one study object per user.
 *
 * @param int $user_id The user ID.
 *
 * @return Study The study object.
 */
function sp_get_user_study( int $user_id ): Study {
	$study = Study::where( 'user_id', $user_id )->first();
	if ( empty( $study ) ) {
		$study                    = new Study();
		$study->no_to_revise      = 0;
		$study->no_of_new         = 0;
		$study->no_on_hold        = 0;
		$study->revise_all        = 0;
		$study->study_all_new     = 0;
		$study->study_all_on_hold = 0;
		$study->user_id           = $user_id;
		$study->deck_id           = 0;
		$study->all_tags          = 0;

		$study->save();
	}

	return $study;
}

/**
 * Get user studies.
 *
 * @param int $user_id The user ID.
 *
 * @return array
 */
function sp_get_user_studies( int $user_id ): array {
	$studies = Study::where( 'user_id', $user_id )->get();
	if ( empty( $studies ) ) {
		return array(
			'studies'   => array(),
			'study_ids' => array(),
		);
	}

	return array(
		'studies'   => $studies,
		'study_ids' => $studies->pluck( 'id' )->toArray(),
	);
}

function sp_get_db_prefix() {
	global $wpdb;

	return $wpdb->prefix . 'sp_';
}

/**
 * Add card group ids to user ignored card group ids.
 *
 * @param int $user_id The user ID.
 * @param array $ignored_card_group_ids The card group IDs to be added.
 *
 * @return void
 */
function sp_save_user_ignored_card_groups( int $user_id, array $ignored_card_group_ids ) {
	$user_ignored_card_group_ids = (array) get_user_meta( $user_id, Settings::UM_IGNORED_CARD_GROUP_IDS, true );
	if ( empty( $user_ignored_card_group_ids ) ) {
		$user_ignored_card_group_ids = [];
	}

	foreach ( $ignored_card_group_ids as $card_id ) {
		if ( ! in_array( $card_id, $user_ignored_card_group_ids ) ) {
			$user_ignored_card_group_ids[] = $card_id;
		}
	}

	update_user_meta( $user_id, Settings::UM_IGNORED_CARD_GROUP_IDS, $user_ignored_card_group_ids );

}

/**
 * Add card group ids to user ignored card group ids.
 *
 * @param int $user_id The user ID.
 * @param array $ignored_card_group_ids The card group IDs to be removed.
 *
 * @return void
 */
function sp_remove_user_ignored_card_groups( int $user_id, array $ignored_card_group_ids ) {
	$user_ignored_card_group_ids = (array) get_user_meta( $user_id, Settings::UM_IGNORED_CARD_GROUP_IDS, true );
	if ( empty( $user_ignored_card_group_ids ) ) {
		$user_ignored_card_group_ids = [];
	}

	foreach ( $ignored_card_group_ids as $card_id ) {
		if ( in_array( $card_id, $user_ignored_card_group_ids ) ) {
			$key = array_search( $card_id, $user_ignored_card_group_ids, true );
			unset( $user_ignored_card_group_ids[ $key ] );
		}
	}

	update_user_meta( $user_id, Settings::UM_IGNORED_CARD_GROUP_IDS, $user_ignored_card_group_ids );
}

/**
 * Get user ignored card group ids.
 *
 * @param int $user_id The user id.
 *
 * @return int[]
 */
function sp_get_user_ignored_card_group_ids( int $user_id ): array {
	$user_ignored_card_group_ids = get_user_meta( $user_id, Settings::UM_IGNORED_CARD_GROUP_IDS, true );
	if ( empty( $user_ignored_card_group_ids ) || ! is_array( $user_ignored_card_group_ids ) ) {


		$user_ignored_card_group_ids = [];
	}

	// make sure non of the card group ids are 0 or empty.
	foreach ( $user_ignored_card_group_ids as $key => $card_group_id ) {
		if ( empty( $card_group_id ) ) {
			unset( $user_ignored_card_group_ids[ $key ] );
		}
	}

	return $user_ignored_card_group_ids;
}


global $wpdb;
$prefix = sp_get_db_prefix();
define( 'SP_DB_PREFIX', $prefix );
define( 'SP_TABLE_DECK_GROUPS', SP_DB_PREFIX . 'deck_groups' );
define( 'SP_TABLE_TAGS', SP_DB_PREFIX . 'tags' );
define( 'SP_TABLE_TAGGABLES', SP_DB_PREFIX . 'taggables' );
define( 'SP_TABLE_TAGGABLES_EXCLUDED', SP_DB_PREFIX . 'taggables_excluded' );
define( 'SP_TABLE_DECKS', SP_DB_PREFIX . 'decks' );
define( 'SP_TABLE_CARD_GROUPS', SP_DB_PREFIX . 'card_groups' );
define( 'SP_TABLE_CARDS', SP_DB_PREFIX . 'cards' );
define( 'SP_TABLE_STUDY', SP_DB_PREFIX . 'study' );
define( 'SP_TABLE_ANSWERED', SP_DB_PREFIX . 'answered' );
define( 'SP_TABLE_ANSWER_LOG', SP_DB_PREFIX . 'answer_log' );
define( 'SP_TABLE_USERS', $wpdb->prefix . 'users' );
define( 'SP_TABLE_STUDY_LOG', SP_DB_PREFIX . 'study_log' );
define( 'SP_TABLE_TOPICS', SP_DB_PREFIX . 'topics' );
define( 'SP_TABLE_COLLECTIONS', SP_DB_PREFIX . 'collections' );
define( 'SP_TABLE_USER_CARDS', SP_DB_PREFIX . 'user_cards' );

/**
 * Create a new migration.
 */
function phinx_migrate(): void {
	$phinx = new PhinxApplication();
	$phinx->setAutoExit( false );

	$wrap = new TextWrapper( $phinx );
	$wrap->setOption( 'configuration', __DIR__ . '/phinx.php' );
	$wrap->getMigrate( 'development' );
	if ( $wrap->getExitCode() ) {
		mp_log()?->log( 'Phinx migrations encountered an error.', array() );
	}
}

