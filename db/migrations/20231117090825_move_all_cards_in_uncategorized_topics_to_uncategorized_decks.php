<?php

declare( strict_types=1 );

use Model\CardGroup;
use Phinx\Migration\AbstractMigration;

use function StudyPlannerPro\get_uncategorized_deck_id;
use function StudyPlannerPro\get_uncategorized_topic_id;

final class MoveAllCardsInUncategorizedTopicsToUncategorizedDecks extends AbstractMigration {
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
		$uncategorized_topic_id = get_uncategorized_topic_id();
		$uncategorized_deck_id  = get_uncategorized_deck_id();

		if ( ! $uncategorized_topic_id ) {
			return;
		}
		$card_groups = CardGroup
			::query()
			->with( array(
				'topic' => function ( $query ) use ( $uncategorized_topic_id ) {
					$query->where( 'id', $uncategorized_topic_id );
				}
			) )
			->whereHas( 'topic', function ( $query ) use ( $uncategorized_topic_id ) {
				$query->where( 'id', $uncategorized_topic_id );
			} )
			->get();

		// Set all card group's deck_id to the uncategorized deck id.
		foreach ( $card_groups as $card_group ) {
			$card_group->deck_id = $uncategorized_deck_id;
			$card_group->save();
		}
	}
}
