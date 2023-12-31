<?php
/**
 * File to handle chart ChartReviewHelper data sourcing
 */

namespace StudyPlannerPro\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use DateTime;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Eloquent\Model;
use Model\Answered;
use Model\Card;
use Model\CardGroup;
use Model\CardGroups;
use Model\Deck;
use Model\DeckGroup;
use Model\Study;
use Model\User;
use PDOException;
use PHPMailer\PHPMailer\Exception;
use StudyPlannerPro\Initializer;
use StudyPlannerPro\Libs\Common;
use StudyPlannerPro\Libs\Settings;
use StudyPlannerPro\Models\Tag;
use StudyPlannerPro\Services\Card_Due_Date_Service;
use function StudyPlannerPro\get_all_card_grades;

/**
 * Class ChartReviewHelper
 *
 * @package StudyPlannerPro\Helpers
 */
class ChartAddedHelper {

	public static function get_all_new_cards_added( $args ) {
		$default = [
			'user_id'         => 0,
			'start_date'      => null,
			'end_date'        => null,
			'no_date_limit'   => false,
			'card_ids_not_in' => [],
			'card_ids_in'     => [],
		];
		$args    = wp_parse_args( $args, $default );

		$user = User
			::with( [
				'studies.answers' => function ( $query ) use ( $args ) {
					$query->select( '*',
						Manager::raw( 'DATEDIFF(DATE(created_at),DATE(created_at)) as day_diff' ),
						Manager::raw( 'DATEDIFF(DATE(created_at),DATE("' . $args['start_date'] . '")) as day_diff_today' ),
					);
					$query->where( 'answered_as_new', '=', true );
					if ( $args['no_date_limit'] ) {
						$query->where( 'created_at', '>=', $args['start_date'] );
					} else {
						$query->whereBetween( 'created_at', [ $args['start_date'], $args['end_date'] ] );
					}
				},
			] )
			->whereHas( 'studies.answers', function ( $query ) use ( $args ) {
				$query->where( 'answered_as_new', '=', true );
			} )
			->where( 'ID', '=', $args['user_id'] );
		if ( empty( $user->get()->all() ) ) {
			$answers = [];
		} else {
			$answers = $user->get()->first()->studies->pluck( 'answers' )->flatten();
		}

		return [
			'answers' => $answers,
		];
	}

}