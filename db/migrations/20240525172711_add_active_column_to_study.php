<?php

declare( strict_types=1 );

use Phinx\Migration\AbstractMigration;

final class AddActiveColumnToStudy extends AbstractMigration {
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
	// // Add active column.
	//		if ( ! $this->schema_builder->hasColumn( SP_TABLE_STUDY, 'active' ) ) {
	//			Capsule::schema()->table(
	//				SP_TABLE_STUDY,
	//				function ( Blueprint $table ) {
	//					$table
	//						->boolean( 'active' )
	//						->default( 1 )
	//						->after( 'no_on_hold' )
	//						->comment( 'Whether the study is active or not.' );
	//				}
	//			);
	//		}
	public function change(): void {
		require_once __DIR__ . '/table-definitions.php';
		$exists = $this->hasTable( SP_TABLE_STUDY );
		if ( ! $exists ) {
			return;
		}

		$table = $this->table( SP_TABLE_STUDY );
		if ( ! $table->hasColumn( 'active' ) ) {
			$table->addColumn( 'active', 'boolean', [
				'default' => 1,
				'after'   => 'no_on_hold',
				'comment' => 'Whether the study is active or not.',
			] );
		}
	}
}
