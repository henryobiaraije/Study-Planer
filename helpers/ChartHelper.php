<?php
	/**
	 * File to handle chart data sourcing
	 */

	namespace StudyPlanner\Helpers;

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
	use StudyPlanner\Initializer;
	use StudyPlanner\Libs\Common;
	use StudyPlanner\Libs\Settings;
	use StudyPlanner\Models\Tag;
	use StudyPlanner\Services\Card_Due_Date_Service;
	use function StudyPlanner\get_all_card_grades;

	/**
	 * Class ChartHelper
	 *
	 * @package StudyPlanner\Helpers
	 */
	class ChartHelper {

		public static function get_chart_forecast( $user_id ) {

		}

	}