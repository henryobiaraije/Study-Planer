<?php

namespace StudyPlannerPro;

use DateTime;
use Model\Study;
use StudyPlannerPro\Db\Initialize_Db;
use StudyPlannerPro\Libs\Common;
use StudyPlannerPro\Libs\Settings;

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
	$_date_today                    = Common::getDateTime();
	$_datetime                      = new DateTime( $_date_today );
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

function print_log( $log ) {
	if ( true === WP_DEBUG ) {
		if ( is_array( $log ) || is_object( $log ) ) {
			error_log( print_r( $log, true ) );
		} else {
			error_log( $log );
		}
	}
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
		$current_study_date = Common::getDateTime();
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
 * @return Study
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

function sp_get_db_prefix() {
	global $wpdb;

	return $wpdb->prefix . 'sp_';
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
