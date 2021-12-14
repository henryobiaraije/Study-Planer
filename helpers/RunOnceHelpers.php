<?php
/**
 * Front end ajax helper file
 */

namespace StudyPlanner\Helpers;

if (!defined('ABSPATH')) {
    exit;
}

use DateTime;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Model\Answered;
use Model\Card;
use Model\CardGroup;
use Model\CardGroups;
use Model\Deck;
use Model\DeckGroup;
use Model\Study;
use Model\StudyLog;
use PDOException;
use PHPMailer\PHPMailer\Exception;
use StudyPlanner\Initializer;
use StudyPlanner\Libs\Common;
use StudyPlanner\Libs\Settings;
use StudyPlanner\Models\Tag;
use StudyPlanner\Services\Card_Due_Date_Service;
use Symfony\Component\Console\Helper\Table;
use function StudyPlanner\get_all_card_grades;
use function StudyPlanner\get_card_group_background_image;

/**
 * Class RunOnceHelpers
 *
 * @package StudyPlanner\Helpers
 */
class RunOnceHelpers {
    /**
     * @var self $instance
     */
    private static $instance;

    private function __construct() {
        $this->run_all_once();
    }

    public static function get_instance(): self {
        if (self::$instance) {
            return self::$instance;
        }

        self::$instance = new self();

        return self::$instance;
    }

    public function run_all_once() {
        $this->run_once_update_answers_last_updated_card_ids();
    }

    public function run_once_update_answers_last_updated_card_ids() {
        $option = get_option('spROUpdAnsLasUpdCId', false);
        if ($option) {
            return;
        }
        $table = Manager
            ::table(SP_TABLE_ANSWERED.' as a')
            ->leftJoin(SP_TABLE_CARDS.' as c', 'c.id', '=', 'a.card_id')
            ->update([
                'card_last_updated_at' => Manager::raw('c.updated_at'),
            ]);
        get_option('spROUpdAnsLasUpdCId', true);
    }


}