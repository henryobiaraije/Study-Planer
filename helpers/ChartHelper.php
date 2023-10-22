<?php
	/**
	 * File to handle chart data sourcing
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
	use PDOException;
	use PHPMailer\PHPMailer\Exception;
	use StudyPlannerPro\Initializer;
	use StudyPlannerPro\Libs\Common;
	use StudyPlannerPro\Libs\Settings;
	use StudyPlannerPro\Models\Tag;
	use StudyPlannerPro\Services\Card_Due_Date_Service;
	use function StudyPlannerPro\get_all_card_grades;

	/**
	 * Class ChartHelper
	 *
	 * @package StudyPlannerPro\Helpers
	 */
	class ChartHelper {

		public static function get_chart_forecast( $user_id ) {

		}

	}