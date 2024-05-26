<?php

declare( strict_types=1 );

use Phinx\Migration\AbstractMigration;

final class CreateTableUserCards extends AbstractMigration {
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
		// if ( ! $this->schema_builder->hasTable( SP_TABLE_USER_CARDS ) ) {
		//			Capsule::schema()->create(
		//				SP_TABLE_USER_CARDS,
		//				function ( Blueprint $table ) {
		//					$table->id();
		//					$table->foreignId( 'user_id' )->constrained( SP_TABLE_USERS )->cascadeOnDelete()->cascadeOnUpdate();
		//					$table->foreignId( 'card_group_id' )->constrained( SP_TABLE_CARD_GROUPS )->cascadeOnDelete()->cascadeOnUpdate();
		//					$table->softDeletes();
		//					$table->timestamps();
		//				}
		//			);
		//		}
		$exists = $this->hasTable( SP_TABLE_USER_CARDS );
		if ( $exists ) {
			return;
		}

		$table = $this->table( SP_TABLE_USER_CARDS );
		$table
			->addColumn( 'user_id', 'biginteger' )
			->addColumn( 'card_group_id', 'biginteger' )
			->addTimestamps()
			->addColumn( 'deleted_at', 'timestamp', [ 'null' => true ] )
			->addForeignKey( 'user_id', SP_TABLE_USERS, 'id', [ 'delete' => 'CASCADE', 'update' => 'CASCADE' ] )
			->addForeignKey( 'card_group_id', SP_TABLE_CARD_GROUPS, 'id', [
				'delete' => 'CASCADE',
				'update' => 'CASCADE'
			] )
			->create();

	}
}
