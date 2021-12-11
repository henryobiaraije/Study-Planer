<?php

namespace StudyPlanner;

use DateTime;
use StudyPlanner\Db\Initialize_Db;
use StudyPlanner\Libs\Common;
use StudyPlanner\Libs\Settings;

function load_template($template) {
    require __DIR__.'/templates/'.$template.'.php';
}

function get_template_path($template) {
    return __DIR__.'/templates/'.$template.'.php';
}

function get_all_card_grades() {
    $default = ['again', 'hard', 'good', 'easy', 'hold'];

    return apply_filters('sp_all_card_grades', $default);
}

function get_user_timezone_minutes_to_add($user_id) {

    $timezones     = Common::get_time_zones();
    $user_timezone = get_user_meta($user_id, Settings::UM_USER_TIMEZONE, true);
    if (empty($user_timezone)) {
        return 0;
    }
    if (array_key_exists($user_timezone, $timezones)) {
        $zone = $timezones[$user_timezone];
        $re   = '/GMT[-+]{1}[0-9]{1,2}:[-0-9]{1,3}/m';
        $str  = $zone;
        preg_match($re, $str, $matches);
        if (!empty($matches)) {
            $m_first         = $matches[0];
            $operator        = substr($m_first, 3, 1);
            $hour_and_minute = substr($m_first, 3);
            //						$hour_and_minute = trim( $hour_and_minute, 'T' );
            $explode       = explode(':', $hour_and_minute);
            $hour          = (int) $explode[0];
            $minute        = (int) $explode[1];
            $_hour         = $hour * 60;
            $total_minutes = $_hour + $minute;
            //			Common::send_error( [
            //				'$zone'            => $zone,
            //				'$matches'         => $matches,
            //				'$m_first'         => $m_first,
            //				'$operator'        => $operator,
            //				'$hour_and_minute' => $hour_and_minute,
            //				'$hour'            => $hour,
            //				'$minute'          => $minute,
            //				'$explode'         => $explode,
            //				'$total_minutes'   => $total_minutes,
            //				'$_hour'           => $_hour,
            //			] );
            //			dd( $zone, $matches, $m_first );
            //			dd( $zone, $matches, $m_first );
            return $total_minutes;
        }

        return 0;
    }
}

function get_user_timezone_date_midnight_today($user_id) {
    $user_timezone_minutes_from_now = get_user_timezone_minutes_to_add($user_id);
    $_date_today                    = Common::getDateTime();
    $_datetime                      = new DateTime($_date_today);
    $_datetime->modify("$user_timezone_minutes_from_now minutes");
    $_datetime->setTime(0, 0, 0);
    $the_date = $_datetime->format('Y-m-d H:i:s');

    return $the_date;
}

function get_default_image_display_type() {
    $default = [
        'hide_all_ask_one',
        'hide_all_ask_all',
        'hide_one_ask_one',
    ];

    return \apply_filters('sp_default_image_display_type', $default);
}

function get_mature_card_days() {
    $option = (int) get_option(Settings::OPTION_MATURED_CARD_DAYS, 27);
    if (empty($option)) {
        return 27;
    }
    return $option;
}

function print_log($log) {
    if (true === WP_DEBUG) {
        if (is_array($log) || is_object($log)) {
            error_log(print_r($log, true));
        } else {
            error_log($log);
        }
    }
}

function get_uncategorized_deck_group_id() {
    $id = get_option(Settings::OP_UNCATEGORIZED_DECK_GROUP_ID, 0);
    if (!empty($id)) {
        return $id;
    }
    Initialize_Db::get_instance()->create_default_rows();
    $id = get_option(Settings::OP_UNCATEGORIZED_DECK_GROUP_ID, 0);
    return $id;
}

function get_uncategorized_deck_id() {
    $id = get_option(Settings::OP_UNCATEGORIZED_DECK_ID, 0);
    if (!empty($id)) {
        return $id;
    }
    Initialize_Db::get_instance()->create_default_rows();
    $id = get_option(Settings::OP_UNCATEGORIZED_DECK_ID, 0);
    return $id;
}


global $wpdb;
$prefix = $wpdb->prefix.'sp_';
define('SP_DB_PREFIX', $prefix);
define('SP_TABLE_DECK_GROUPS', SP_DB_PREFIX.'deck_groups');
define('SP_TABLE_TAGS', SP_DB_PREFIX.'tags');
define('SP_TABLE_TAGGABLES', SP_DB_PREFIX.'taggables');
define('SP_TABLE_DECKS', SP_DB_PREFIX.'decks');
define('SP_TABLE_CARD_GROUPS', SP_DB_PREFIX.'card_groups');
define('SP_TABLE_CARDS', SP_DB_PREFIX.'cards');
define('SP_TABLE_STUDY', SP_DB_PREFIX.'study');
define('SP_TABLE_ANSWERED', SP_DB_PREFIX.'answered');
define('SP_TABLE_USERS', $wpdb->prefix.'users');
define('SP_TABLE_STUDY_LOG', SP_DB_PREFIX.'study_log');