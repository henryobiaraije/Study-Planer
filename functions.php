<?php

	namespace StudyPlanner;

	use DateTime;
	use StudyPlanner\Libs\Common;
	use StudyPlanner\Libs\Settings;

	function load_template( $template ) {
		require __DIR__ . '/templates/' . $template . '.php';
	}

	function get_template_path( $template ) {
		return __DIR__ . '/templates/' . $template . '.php';
	}

	function get_all_card_grades() {
		$default = [ 'again', 'hard', 'good', 'easy', 'hold' ];

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
//						$hour_and_minute = trim( $hour_and_minute, 'T' );
				$explode       = explode( ':', $hour_and_minute );
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

	function get_user_timezone_date_midnight_today( $user_id ) {
		$user_timezone_minutes_from_now = get_user_timezone_minutes_to_add( $user_id );
		$_date_today                    = Common::getDateTime();
		$_datetime                      = new DateTime( $_date_today );
		$_datetime->modify( "$user_timezone_minutes_from_now minutes" );
		$_datetime->setTime( 0, 0, 0 );
		$the_date = $_datetime->format( 'Y-m-d H:i:s' );

		return $the_date;
	}
