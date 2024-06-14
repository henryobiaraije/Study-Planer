<?php
/**
 * Front end ajax helper file
 */

namespace StudyPlannerPro\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use DateTime;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Model\Answered;
use Model\AnswerLog;
use Model\Card;
use Model\CardGroup;
use Model\CardGroups;
use Model\Deck;
use Model\DeckGroup;
use Model\Study;
use Model\StudyLog;
use PDOException;
use PHPMailer\PHPMailer\Exception;
use StudyPlannerPro\Initializer;
use StudyPlannerPro\Libs\Common;
use StudyPlannerPro\Libs\Settings;
use StudyPlannerPro\Models\Tag;
use StudyPlannerPro\Services\Card_Due_Date_Service;
use Symfony\Component\Console\Helper\Table;
use function StudyPlannerPro\get_all_card_grades;
use function StudyPlannerPro\get_card_group_background_image;

/**
 * Class RunOnceHelpers
 *
 * @package StudyPlannerPro\Helpers
 */
class RunOnceHelpers {
	/**
	 * @var self $instance
	 */
	private static $instance;

	private function __construct() {
		add_action( 'init', [ $this, 'run_all_once' ] );
//        $this->run_all_once();
	}

	public static function get_instance(): self {
		if ( self::$instance ) {
			return self::$instance;
		}

		self::$instance = new self();

		return self::$instance;
	}

	public function run_all_once() {
		$deck_group_table_exists = Manager::schema()->hasTable( SP_TABLE_DECK_GROUPS );
		if ( $deck_group_table_exists ) {
			$this->run_once_update_answers_last_updated_card_ids();
			$this->run_once_fill_the_answer_log();
		}
	}

	public function run_once_update_answers_last_updated_card_ids() {
		return;
		$option = get_option( 'spROUpdAnsLasUpdCId', false );
		if ( $option ) {
			return;
		}
		//        dd($option);
		$table = Manager
			::table( SP_TABLE_ANSWERED . ' as a' )
			->leftJoin( SP_TABLE_CARDS . ' as c', 'c.id', '=', 'a.card_id' )
			->update( [
				'a.card_last_updated_at' => Manager::raw( 'c.updated_at' ),
				'a.answer'               => Manager::raw( 'c.answer' ),
				'a.question'             => Manager::raw( 'c.question' ),
			] );
		update_option( 'spROUpdAnsLasUpdCId', true );
	}

	public function run_once_fill_the_answer_log() {
		$option = get_option( 'spROFillAnswerLog2', false );
		if ( $option ) {
			return;
		}
		//        dd($option);
		$query_answers = Answered
			::where( 'grade', '!=', 'hold' )
			->groupBy( 'id' )
			->orderByDesc( 'id' )
			->get();
		foreach ( $query_answers as $answer ) {
			$old_log = AnswerLog
				::where( 'study_id', '=', $answer->study_id )
				->where( 'card_id', '=', $answer->card_id )
				->get()->first();
			if ( empty( $old_log ) ) {
				$old_log = AnswerLog::create( [
					'study_id' => $answer->study_id,
					'card_id'  => $answer->card_id,
				] );
			}
			if ( ! empty( $answer->question ) && ! empty( $answer->answer ) ) {
				$old_log->update( [
					'last_card_updated_at'    => $answer->card_last_updated_at,
					'accepted_change_comment' => '',
					'question'                => $answer->question,
					'answer'                  => $answer->answer,
				] );
			}
		}

		update_option( 'spROFillAnswerLog2', true );
	}


}