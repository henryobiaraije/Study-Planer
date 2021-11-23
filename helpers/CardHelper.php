<?php

	namespace StudyPlanner\Helpers;

	use DateTime;
	use Model\Answered;
	use Model\Card;
	use StudyPlanner\Libs\Common;
	use WP_Error;

	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	class CardHelper {

		public static function get_next_dew_date_of_card( $card_id, $study_id ) {
			//f' = max(1300, f - 200) | For again
			//f' = max(1300, f - 150) | For Hard
			//f' = f                  | For Good.
			//f' = max(1300, f + 150) | Easy

			// i1 = m0 * i is the new interval for failed reviews. It can have a minimum.
			// i2 = max(i + 1, (i + d/4) * 1.2 * m) is the new interval for hard reviews.
			// i3 = max(i2 + 1, (i + d/2) * (f / 1000) * m) is the new interval for ok reviews.
			// i4 = max(i3 + 1, (i + d) * (f / 1000) * m * m4) is the new interval for easy reviews.

			$d = 0;
			$i = 0;
			$f = 0;
			/** Get (d) (Default = 0) : Delay (in days) from due date to reviewed date */
			$card = Card::find( $card_id );
			if ( empty( $card ) ) {
				return new WP_Error( 404, 'Card not found' );
			}
			$_last_2_answers = Answered
				::where( 'card_id', '=', $card_id )
				->where( 'study_id', '=', $study_id )
				->limit( 2 )->orderBy( 'id', 'desc' );

			$last_answer           = $_last_2_answers->skip( 0 )->take( 1 )->first();
			$second_to_last_answer = $_last_2_answers->skip( 1 )->take( 1 )->first();
			if ( null === $second_to_last_answer ) {
				// todo maybe calculate from when the study was created
			} else {
				$last_due_at      = $second_to_last_answer->next_due_at;
				$last_answered_at = $last_answer->created_at;
				$date1            = new DateTime( $last_due_at );
				$date2            = new DateTime( $last_answered_at );
				$interval         = $date2->diff( $date1 );
				$d                = $interval->days;
			}


			Common::send_error( [
				__METHOD__,
				'd'                      => $d,
				'i'                      => $i,
				'f'                      => $f,
				'card'                   => $card,
				'$last_answer'           => $last_answer,
				'card_id'                => $card_id,
				'$second_to_last_answer' => $second_to_last_answer,
				'$last_due_at'           => $last_due_at,
				'$last_answered_at'      => $last_answered_at,
			] );


			// Get (f') (Default = 0) : the last review grade
			// Get (i) : (Default = 0) The most recent interval (in days) a card was reviewed


		}

		private static function get_d( $card_id, $study_id ) {
			/** Get (d) (Default = 0) : Delay (in days) from due date to reviewed date */
			$d    = 0;
			$card = Card::find( $card_id );
			if ( empty( $card ) ) {
				return new WP_Error( 404, 'Card not found' );
			}
			$_last_2_answers = Answered
				::where( 'card_id', '=', $card_id )
				->where( 'study_id', '=', $study_id )
				->limit( 2 )->orderBy( 'id', 'desc' );

			$last_answer           = $_last_2_answers->skip( 0 )->take( 1 )->first();
			$second_to_last_answer = $_last_2_answers->skip( 1 )->take( 1 )->first();
			if ( null === $second_to_last_answer ) {
				// todo maybe calculate from when the study was created
			} else {
				$last_due_at      = $second_to_last_answer->next_due_at;
				$last_answered_at = $last_answer->created_at;
				$date1            = new DateTime( $last_due_at );
				$date2            = new DateTime( $last_answered_at );
				$interval         = $date2->diff( $date1 );
				$d                = $interval->days;
			}

			return $d;
		}
	}