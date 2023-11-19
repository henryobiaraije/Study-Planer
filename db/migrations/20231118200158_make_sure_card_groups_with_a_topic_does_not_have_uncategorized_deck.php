<?php

declare( strict_types=1 );

use Model\CardGroup;
use Model\Topic;
use Phinx\Migration\AbstractMigration;

use function StudyPlannerPro\get_uncategorized_deck_id;
use function StudyPlannerPro\get_uncategorized_topic_id;

final class MakeSureCardGroupsWithATopicDoesNotHaveUncategorizedDeck extends AbstractMigration {
	/**
	 * Change Method.
	 *
	 * Write your reversible migrations using this method.
	 *
	 * More information on writing migrations is available here:
	 * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
	 *
	 * Remember to call "create()" or "update()" and NOT "save()" when working
	 * with the Table class.
	 */
	public function change(): void {
		Topic::make_sure_card_groups_with_real_topic_also_have_a_real_deck();
	}
}
