<?php


	namespace StudyPlanner\Helpers;


	use Illuminate\Database\Capsule\Manager;
	use Illuminate\Database\Eloquent\Model;
	use Model\Card;
	use Model\CardGroup;
	use Model\CardGroups;
	use Model\Deck;
	use Model\DeckGroup;
	use Model\Study;
	use PDOException;
	use PHPMailer\PHPMailer\Exception;
	use StudyPlanner\Initializer;
	use StudyPlanner\Libs\Common;
	use StudyPlanner\Libs\Settings;
	use StudyPlanner\Models\Tag;

	class AjaxFrontHelper {
		/**
		 * @var self $instance
		 */
		private static $instance;

		private function __construct() {
		}

		public static function get_instance() : self {
			if ( self::$instance ) {
				return self::$instance;
			}

			self::$instance = new self();
			self::$instance->init_ajax();

			return self::$instance;
		}

		private function init_ajax() {
			add_action( 'admin_sp_ajax_front_get_deck_groups', array( $this, 'ajax_front_get_deck_groups' ) );
			add_action( 'admin_sp_ajax_front_create_study', array( $this, 'ajax_front_create_study' ) );
		}


		// <editor-fold desc="Gap Cards">

		public function ajax_front_create_study( $post ) : void {
//			Common::send_error( [
//				'ajax_front_create_study',
//				'post' => $post,
//			] );

			$all               = $post[ Common::VAR_2 ]['study'];
			$deck_id           = (int) sanitize_text_field( $all['deck']['id'] );
			$study_id          = (int) sanitize_text_field( $all['study']['id'] );
			$tags              = $all['tags'];
			$no_of_new         = (int) sanitize_text_field( $all['no_of_new'] );
			$no_on_hold        = (int) sanitize_text_field( $all['no_on_hold'] );
			$no_to_revise      = (int) sanitize_text_field( $all['no_to_revise'] );
			$revise_all        = (bool) sanitize_text_field( $all['revise_all'] );
			$study_all_new     = (bool) sanitize_text_field( $all['study_all_new'] );
			$study_all_on_hold = (bool) sanitize_text_field( $all['study_all_on_hold'] );
			$all_tags          = (bool) sanitize_text_field( $all['all_tags'] );

			$deck = Deck::find( $deck_id );
			if ( empty( $deck ) ) {
				Common::send_error( 'Invalid deck',[
					'post' => $post,
				] );
			}
			$study = null;
			if ( ! empty( $study_id ) ) {
				$study = Study::find( $study_id );
				if ( empty( $study ) ) {
					Common::send_error( 'Invalid study plan' );
				}
			}

			$user_id = get_current_user_id();

			Manager::beginTransaction();


//			Common::send_error( [
//				'ajax_front_create_study',
//				'post'               => $post,
//				'$study_id'          => $study_id,
//				'$study'             => $study,
//				'$deck_id'           => $deck_id,
//				'$tags'              => $tags,
//				'$no_of_new'         => $no_of_new,
//				'$no_on_hold'        => $no_on_hold,
//				'$no_to_revise'      => $no_to_revise,
//				'$revise_all'        => $revise_all,
//				'$study_all_new'     => $study_all_new,
//				'$study_all_on_hold' => $study_all_on_hold,
//			] );

			if ( empty( $study ) ) {
				$study = new Study();
			}
			$study->no_to_revise      = $no_to_revise;
			$study->no_of_new         = $no_of_new;
			$study->no_on_hold        = $no_on_hold;
			$study->revise_all        = $revise_all;
			$study->study_all_new     = $study_all_new;
			$study->study_all_on_hold = $study_all_on_hold;
			$study->user_id           = $user_id;
			$study->deck_id           = $deck_id;
			$study->all_tags          = $all_tags;

			$study->save();

			$study->tags()->detach();
			foreach ( $tags as $one ) {
				$tag = Tag::find( $one['id'] );
				$study->tags()->save( $tag );
			}

			Manager::commit();
			$new_study = Study::get_user_study_by_id( $study->id );
//			$new_study = Study::with( 'tags', 'deck' )->find( $study->id );;


//			Common::send_error( [
//				'ajax_front_create_study',
//				'post'               => $post,
//				'$study_id'          => $study_id,
//				'$new_study'         => $new_study,
//				'$study'             => $study,
//				'$deck_id'           => $deck_id,
//				'$tags'              => $tags,
//				'$no_of_new'         => $no_of_new,
//				'$no_on_hold'        => $no_on_hold,
//				'$no_to_revise'      => $no_to_revise,
//				'$revise_all'        => $revise_all,
//				'$study_all_new'     => $study_all_new,
//				'$study_all_on_hold' => $study_all_on_hold,
//			] );

			Common::send_success( 'Saved.',$new_study );

		}

		public function ajax_front_get_deck_groups( $post ) : void {
//			Common::send_error( [
//				'ajax_admin_load_deck_group',
//				'post' => $post,
//			] );

			$params         = $post[ Common::VAR_2 ]['params'];
			$per_page       = (int) sanitize_text_field( $params['per_page'] );
			$page           = (int) sanitize_text_field( $params['page'] );
			$search_keyword = sanitize_text_field( $params['search_keyword'] );
			$status         = sanitize_text_field( $params['status'] );

			$deck_groups = DeckGroup::get_deck_groups_front_end( [
				'search'       => '',//$search_keyword,
				'page'         => 1,//$page,
				'per_page'     => 1000,//$per_page,
				'only_trashed' => false,//( 'trash' === $status ) ? true : false,
			] );
			$studies     = Study::get_user_studies( [
				'search'       => '',//$search_keyword,
				'page'         => 1,//$page,
				'per_page'     => 1000,//$per_page,
				'only_trashed' => false,//( 'trash' === $status ) ? true : false,
			] );

//			Common::send_error( [
//				'ajax_admin_load_deck_group',
//				'post'            => $post,
//				'$params'         => $params,
//				'$per_page'       => $per_page,
//				'$page'           => $page,
//				'$search_keyword' => $search_keyword,
//				'$deck_groups'    => $deck_groups,
//				'$status'         => $status,
//			] );


			Common::send_success( 'Deck group loaded.', [
				'details' => $deck_groups,
				'studies' => $studies,
			] );

		}

		// </editor-fold desc="Gap Cards">


	}