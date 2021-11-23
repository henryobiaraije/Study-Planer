<?php
	/**
	 * Service to get card next due date, e.t.c
	 */

	namespace StudyPlanner\Services;

	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	use DateTime;
	use Exception;
	use Model\Answered;
	use Model\Card;
	use Model\Study;
	use StudyPlanner\Libs\Common;

	/**
	 * Class Card_Due_Date_Service
	 *
	 * @package StudyPlanner\Services
	 */
	class Card_Due_Date_Service {

		/**
		 * @var \Model\Study
		 */
		public $study;

		/**
		 * @var \Model\Card
		 */
		public $card;

		/**
		 * Delay (in days) from due date to reviewed date
		 *
		 * @var int $d
		 */
		private $d = 0;

		/**
		 * the last review grade
		 *
		 * @var int $f
		 */
		private $f = 0;

		/**
		 * The most recent interval (in days) a card was reviewed
		 *
		 * @var int $i
		 */
		private $i = 0;

		/**
		 * @var Answered $last_answer
		 */
		public $last_answer;

		/**
		 * @var Answered $second_to_last_answer
		 */
		public $second_to_last_answer;
		/**
		 * @var Answered $second_to_last_answer
		 */
		public $third_to_last_answer;

		private $next_due;

		private $debug = [];


		/**
		 * @throws \Exception
		 */
		public function __construct( $args ) {
			$default = [
				'study_id' => 0,
				'card_id'  => 0,
			];
			$args    = wp_parse_args( $args, $default );
			$card    = Card::find( $args['card_id'] );
			if ( empty( $card ) ) {
				throw new Exception( 'Invalid card' );
			}
			$study = Study::find( $args['study_id'] );
			if ( empty( $study ) ) {
				throw new Exception( 'Invalid study' );
			}
			$this->card  = $card;
			$this->study = $study;
			$this->load_answers();
			$this->calculate_d();
			$this->calculate_f();
			$this->calculate_i();
		}

		public function get_next_due_date() {
			// i1 = m0 * i is the new interval for failed reviews. It can have a minimum.
			// i2 = max(i + 1, (i + d/4) * 1.2 * m) is the new interval for hard reviews.
			// i3 = max(i2 + 1, (i + d/2) * (f / 1000) * m) is the new interval for ok reviews.
			// i4 = max(i3 + 1, (i + d) * (f / 1000) * m * m4) is the new interval for easy reviews.

			$m  = 1;
			$m4 = 1.3;
			$i  = $this->i;
			$d  = $this->d;
			$f  = $this->f;

			$next_interval = 0;
			$next_due_date = Common::getDateTime();
			$i1            = 0 * $i;
			$i2            = max( ( $i + 1 ), ( ( $i + ( $d / 4 ) ) * 1.2 * $m ) );
			$i3            = max( ( $i2 + 1 ), ( ( $i + ( $d / 2 ) ) * ( $f / 1000 ) * $m ) );
			$i4            = max( ( $i3 + 1 ), ( ( $i + ( $d ) ) * ( $f / 1000 ) * $m * $m4 ) );

			$i1_floor = floor( $i1 );
			$i2_floor = floor( $i2 );
			$i3_floor = floor( $i3 );
			$i4_floor = floor( $i4 );

			if ( ! empty( $this->last_answer ) ) {
				$last_grade = $this->last_answer->grade;
				if ( 'again' === $last_grade ) {
					$next_due_date = Common::getDateTime( $i1_floor );
					$next_interval = $i1_floor;
				} elseif ( 'hard' === $last_grade ) {
					$next_due_date = Common::getDateTime( $i2_floor );
					$next_interval = $i2_floor;
				} elseif ( 'good' === $last_grade ) {
					$next_due_date = Common::getDateTime( $i3_floor );
					$next_interval = $i3_floor;
				} elseif ( 'easy' === $last_grade ) {
					$next_due_date = Common::getDateTime( $i4_floor );
					$next_interval = $i4_floor;
				}
			}


			$previous_answer       = $this->second_to_last_answer;
			$last_answer           = $this->last_answer;
			$new_date              = new DateTime( $next_due_date );
			$next_due_date_morning = $new_date->setTime( 0, 0, 0 )->format( 'Y-m-d H:i:s' );

			$debug_display = [
				'previously_answered_at' => $previous_answer ? $previous_answer->created_at : '',
				'last_answered_at'       => $last_answer->created_at,
				'next_due_date'          => $next_due_date,
				'next_due_date_morning'  => $next_due_date_morning,
				'next_interval'          => $next_interval,
				'last_button'            => $last_answer->grade,
				'previous_button'        => $previous_answer->grade,
				'i'                      => $i,
				'd'                      => $d,
				'f'                      => $f,
				'i1'                     => $i1,
				'i2'                     => $i2,
				'i3'                     => $i3,
				'i4'                     => $i4,
				'i1_floor'               => $i1_floor,
				'i2_floor'               => $i2_floor,
				'i3_floor'               => $i3_floor,
				'i4_floor'               => $i4_floor,
			];

			return [
				'next_due_date'         => $next_due_date,
				'next_due_date_morning' => $next_due_date_morning,
				'debug_display'         => $debug_display,
				'next_interval'         => $next_interval,
			];

//			Common::send_error( [
//				__METHOD__,
//				'debug'          => $this->debug,
//				'this'           => $this,
//				'f'              => $this->f,
//				'd'              => $this->d,
//				'i'              => $this->i,
//				'i1'             => $i1,
//				'i2'             => $i2,
//				'i3'             => $i3,
//				'i4'             => $i4,
//				'$i1_floor'      => $i1_floor,
//				'$i2_floor'      => $i2_floor,
//				'$i3_floor'      => $i3_floor,
//				'$i4_floor'      => $i4_floor,
//				'$next_interval' => $next_interval,
//				'$next_due_date' => $next_due_date,
//			] );

		}


		private function load_answers() {
			$_last_2_answers = Answered
				::where( 'card_id', '=', $this->card->id )
				->where( 'study_id', '=', $this->study->id )
				->limit( 3 )->orderBy( 'id', 'desc' );

			$this->last_answer           = $_last_2_answers->skip( 0 )->take( 1 )->first();
			$this->second_to_last_answer = $_last_2_answers->skip( 1 )->take( 1 )->first();
			$this->third_to_last_answer  = $_last_2_answers->skip( 2 )->take( 1 )->first();
		}

		/**
		 * Delay (in days) from due date to reviewed date
		 */
		private function calculate_d() {
			/** Get (d) (Default = 0) : Delay (in days) from due date to reviewed date */

			$last_answer           = $this->last_answer;
			$second_to_last_answer = $this->second_to_last_answer;
			if ( null === $second_to_last_answer ) {
				// todo maybe calculate from when the study was created
			} else {
				$last_due_at      = $second_to_last_answer->next_due_at;
				$last_answered_at = $last_answer->created_at;
				$date1            = new DateTime( $last_due_at );
				$date2            = new DateTime( $last_answered_at );
				$interval         = $date2->diff( $date1 );
				$this->d          = $interval->days;
			}

			$this->debug[ __METHOD__ ] = [
				'$last_answer'           => $last_answer,
				'$second_to_last_answer' => $second_to_last_answer,
				'$last_due_at'           => $last_due_at,
				'$last_answered_at'      => $last_answered_at,
			];


		}

		/**
		 * Most recent interval (in days) a card was viewed
		 * (i) (Default = 0)
		 */
		private function calculate_i() {
			if ( empty( $this->second_to_last_answer ) ) {
				return;
			}
			$last_answer           = $this->last_answer;
			$second_to_last_answer = $this->second_to_last_answer;
			if ( ! empty( $second_to_last_answer ) ) {
				$second_last_answered_at   = $second_to_last_answer->created_at;
				$last_answered_at          = $last_answer->created_at;
				$date1                     = new DateTime( $second_last_answered_at );
				$date2                     = new DateTime( $last_answered_at );
				$interval                  = $date2->diff( $date1 );
				$this->i                   = $interval->days;
				$this->debug[ __METHOD__ ] = [
					'$last_answer'             => $last_answer,
					'$second_to_last_answer'   => $second_to_last_answer,
					'$second_last_answered_at' => $second_last_answered_at,
					'$last_answered_at'        => $last_answered_at,
				];
			}

		}

		private function _get_max_f( string $grade, int $old_f ) {
			//f' = max(1300, f - 200) | For again
			//f' = max(1300, f - 150) | For Hard
			//f' = f                  | For Good.
			//f' = max(1300, f + 150) | Easy
			$f = $old_f;
			if ( 'again' === $grade ) {
				$_factor = $old_f - 200;
				$f       = max( 1300, $_factor );
			} elseif ( 'again' === $grade ) {
				$_factor = $old_f - 150;
				$f       = max( 1300, $_factor );
			} elseif ( 'again' === $grade ) {
				$_factor = $old_f + 150;
				$f       = max( 1300, $_factor );
			}
			$this->debug[ __METHOD__ ] = [
				'$grade' => $grade,
				'$old_f' => $old_f,
			];

			return $f;
		}

		/**
		 * Calculate the last review grade
		 *
		 */
		private function calculate_f() {
			$old_f = 0;
			if ( ! empty( $this->second_to_last_answer ) ) {
				$old_f = (int) $this->second_to_last_answer->ease_factor;
			}

			if ( ! empty( $this->last_answer ) ) {
				$last_grade = $this->last_answer->grade;

				$this->f = $this->_get_max_f( $last_grade, $old_f );
			}

			$this->debug[ __METHOD__ ] = [
				'$old_f' => $old_f,
			];

		}

		/**
		 * Delay between the due date and the answered date
		 *
		 * @return int
		 */
		public function get_d() : int {

			return $this->d;
		}

		/**
		 * The last button the user clicked
		 *
		 * @return int
		 */
		public function get_f() : int {

			return $this->f;
		}

		/**
		 * Most recent interval (in days) a card was viewed
		 *
		 * @return int
		 */
		public function get_i() : int {
			return $this->i;
		}


	}