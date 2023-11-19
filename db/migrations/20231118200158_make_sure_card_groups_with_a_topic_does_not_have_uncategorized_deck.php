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
		// Step 1: Loop through all card groups that doesn't belong to the uncategorized topic.
		// Step 2: For each cg,
		// If the cg belongs to the uncategorized deck, then change it to the deck the cg's topic belongs to
		// If you can't find the deck the cg's topic belongs to, then set the cg deck to uncategorized deck too.
		$uncategorized_topic_id = get_uncategorized_topic_id();
		$uncategorized_deck_id  = get_uncategorized_deck_id();

		if ( ! $uncategorized_topic_id ) {
			return;
		}
		if ( ! $uncategorized_deck_id ) {
			return;
		}

		// Get all card groups that have a topic that is not the uncategorized topic.
		$card_groups = CardGroup
			::query()
			->with( array(
				'topic' => function ( $query ) use ( $uncategorized_topic_id ) {
					$query->where( 'id', '!=', $uncategorized_topic_id );
				}
			) )
			->whereHas( 'topic', function ( $query ) use ( $uncategorized_topic_id ) {
				$query->where( 'id', '!=', $uncategorized_topic_id );
			} )
			->get();

		// Set all card group's deck_id to the uncategorized deck id.
		foreach ( $card_groups as $card_group ) {
			if ( $card_group->deck_id === (int) $uncategorized_deck_id ) {
				// Then try to change it because the topic is not uncategorized.

				// Get the deck this topic belongs to.
				$topic_deck = Topic
					::query()
					->with( array(
						'deck'
					) )
					->get()
					->first();

				if ( $topic_deck ) {
					$new_deck_id         = $topic_deck->deck_id;
					$card_group->deck_id = $new_deck_id;
					$card_group->save();
				} else {
					$card_group->deck_id = $uncategorized_deck_id;
					$card_group->save();
				}
			}
		}
	}
}
