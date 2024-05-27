<?php

declare( strict_types=1 );

use Phinx\Migration\AbstractMigration;

final class AddTopicIdToCardGroups extends AbstractMigration {
	public function change(): void {
		// if ( ! $this->schema_builder->hasColumn( SP_TABLE_CARD_GROUPS, 'topic_id' ) ) {
		//			Capsule::schema()->table(
		//				SP_TABLE_CARD_GROUPS,
		//				function ( Blueprint $table ) {
		//					$table
		//						->foreignId( 'topic_id' )
		//						->constrained( SP_TABLE_TOPICS )
		//						->cascadeOnDelete()
		//						->cascadeOnUpdate()
		//						->nullOnDelete();
		//				}
		//			);
		//		}
		require_once __DIR__ . '/table-definitions.php';
		$exists = $this->hasTable( SP_TABLE_CARD_GROUPS );
		if ( ! $exists ) {
			return;
		}
		$table = $this->table( SP_TABLE_CARD_GROUPS );
		$table
			->addColumn( 'topic_id', 'integer' , [ 'null' => true ] )
			->addForeignKey( 'topic_id', SP_TABLE_TOPICS, 'id', [
				'delete' => 'CASCADE',
				'update' => 'CASCADE'
			] )
			->update();
	}
}
