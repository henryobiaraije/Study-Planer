<?php /** @noinspection ForgottenDebugOutputInspection */

declare( strict_types=1 );

use Phinx\Migration\AbstractMigration;

final class StudyAddActiveToStudy extends AbstractMigration {

	public function change(): void {
		require_once __DIR__ . '/table-definitions.php';
		$tb_exists = $this->hasTable( SP_TABLE_STUDY );
		if ( ! $tb_exists ) {
			return;
		}

		$col_exists = $this->table( SP_TABLE_STUDY )->hasColumn( 'active3' );
		if ( $col_exists ) {
			return;
		}

		$this->table( SP_TABLE_STUDY )
		     ->addColumn( 'active3', 'integer', [ 'default' => 1 ] )
		     ->update();
	}

}
