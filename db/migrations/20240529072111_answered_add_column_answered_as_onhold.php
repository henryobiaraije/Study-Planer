<?php

declare( strict_types=1 );

use Phinx\Migration\AbstractMigration;

final class AnsweredAddColumnAnsweredAsOnhold extends AbstractMigration {
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
		require_once __DIR__ . '/table-definitions.php';
		$table = SP_TABLE_ANSWERED;
		if ( ! $this->hasTable( $table ) ) {
			return;
		}

		$colum = 'answered_as_on_hold';
		if ( $this->table( $table )->hasColumn( $colum ) ) {
			return;
		}

		$this->table( $table )
		     ->addColumn( $colum, 'boolean', [
			     'default' => 0,
			     'null'    => false,
			     'comment' => 'If the last answere of this card before this current answer was on hold'
		     ] )
		     ->update();

		$this->table( $table )
		     ->changeComment( 'Stores all the answeres to cards from users according to their study ids attached to decks(Subjects) and topics' )
		     ->update();

	}
}
