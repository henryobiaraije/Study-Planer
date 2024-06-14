<?php

declare( strict_types=1 );

use Phinx\Migration\AbstractMigration;

final class AddCommentToAnswerLogTable extends AbstractMigration {
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
		$table = SP_TABLE_ANSWER_LOG;
		if ( ! $this->hasTable( $table ) ) {
			return;
		}

		$this->table( $table )
		     ->addColumn(
			     'comment',
			     'text',
			     [
				     'comment' => 'Holds the last question and answer answered by user, unique by study_id, card_id. This way, when
					 we show the user their last answered card detals from here and when admin updates a card, we ask the user to chose one and then store ther
					 selection back here and continues to serve questions and answers from here instead.',
				     'null'    => true
			     ]
		     )
		     ->update();
	}
}
