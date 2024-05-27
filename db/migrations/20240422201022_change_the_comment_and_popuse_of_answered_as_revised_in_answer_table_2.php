<?php

declare( strict_types=1 );

use Phinx\Migration\AbstractMigration;

final class ChangeTheCommentAndPopuseOfAnsweredAsRevisedInAnswerTable2 extends AbstractMigration {
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
		$this->table( SP_TABLE_ANSWERED )
		     ->changeColumn(
			     "answered_as_revised", "string", [
			     "null"    => true,
			     "comment" => "If the previouls answer of this card for this study was revised, i.e Not answered as new and also not answered as onhold."
		     ] )
		     ->save();
	}
}
