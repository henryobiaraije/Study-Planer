<?php
/**
 * Service to manage debug data
 */

namespace StudyPlannerPro\Services;

use Model\Deck;
use Model\Topic as TopicModel;
use Model\DeckGroup;
use StudyPlannerPro\Models\Tag;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Debug_Data_Manager
 *
 * @package StudyPlannerPro\Services
 */
class Debug_Data_Manager {

	private $tags = [
		'Advanced',
		'Basic',
		'Intermediate',
		'Theory',
		'Practical',
		'Experimental',
		'Foundational',
		'Specialized',
		'Core',
		'Elective'
	];


	public function init(): void {
		add_action( 'init', [ $this, 'generate_test_data' ] );
	}

	public function generate_test_data() {
		$generate = filter_input( INPUT_GET, 'sp_generate_data', FILTER_SANITIZE_STRING );
		if ( 'yes' !== $generate ) {
			return;
		}

		$all_tag_details = $this->get_tag_details();
		$all_groups      = $this->generateData();
		foreach ( $all_groups as $group ) {
			// Group.
			$create_group  = DeckGroup::firstOrCreate( array( 'name' => $group->name ) );
			$deck_group_id = $create_group->id;
			$deck_group    = DeckGroup::find( $deck_group_id );
			$deck_group->tags()->detach();
			$group_tags = array_values( array_filter( $all_tag_details, static function ( $tag ) use ( $group ) {
				return in_array( $tag['name'], $group->tags, true );
			} ) );
			foreach ( $group_tags as $one_group ) {
				$tag = Tag::find( $one_group['id'] );
				$deck_group->tags()->save( $tag );
			}

			// Subject.
			foreach ( $group->subjects as $subject ) {
				$create_deck = Deck::firstOrCreate( array(
					'name'          => $subject->name,
					'deck_group_id' => $deck_group_id
				) );
				$deck        = Deck::find( $create_deck->id );
				$deck->deck_group()->associate( $deck_group );
				$deck->save();

				$deck->tags()->detach();
				$subject_tags = array_values( array_filter( $all_tag_details, static function ( $tag ) use ( $subject ) {
					return in_array( $tag['name'], $subject->tags, true );
				} ) );
				foreach ( $subject_tags as $one_subject ) {
					$tag = Tag::find( $one_subject['id'] );
					$deck->tags()->save( $tag );
				}

				// Topic.
				foreach ( $subject->topics as $the_topic_data ) {
					$create_topic = TopicModel::firstOrCreate( array(
						'name'    => $the_topic_data->name,
						'deck_id' => $deck->id
					) );
					$topic        = TopicModel::find( $create_topic->id );
					$topic->deck()->associate( $deck );
					$topic->save();

					$topic->tags()->detach();
					$topic_tags = array_values( array_filter( $all_tag_details, static function ( $tag ) use ( $subject ) {
						return in_array( $tag['name'], $subject->tags, true );
					} ) );
					foreach ( $topic_tags as $one ) {
						$tag = Tag::find( $one['id'] );
						$topic->tags()->save( $tag );
					}
				}
			}
		}
	}

	public function get_tag_details(): array {
		$all_tags    = $this->tags;
		$tag_details = [];
		foreach ( $all_tags as $tag ) {
			$create        = Tag::firstOrCreate( array( 'name' => $tag ) );
			$tag_details[] = [
				'id'   => $create->id,
				'name' => $tag,
			];
		}

		return $tag_details;
	}

	private function getRandomTags() {
		$tags = $this->tags;
		shuffle( $tags );

		return array_slice( $tags, 0, 2 );
	}


	public function generateData() {
		return [
			new Group( 'Engineering', $this->getRandomTags(), [
				new Subject( 'Mechanical', $this->getRandomTags(), [
					new Topic( 'Manufacturing Processes', $this->getRandomTags() ),
					new Topic( 'Fluid Mechanics', $this->getRandomTags() ),
					new Topic( 'Solid Mechanics', $this->getRandomTags() ),
					new Topic( 'Manufacturing', $this->getRandomTags() ),
					new Topic( 'Machine Design', $this->getRandomTags() )
				] ),
				new Subject( 'Electrical', $this->getRandomTags(), [
					new Topic( 'Circuit Analysis', $this->getRandomTags() ),
					new Topic( 'Control Systems', $this->getRandomTags() ),
					new Topic( 'Power Systems', $this->getRandomTags() ),
					new Topic( 'Electromagnetics', $this->getRandomTags() ),
					new Topic( 'Signal Processing', $this->getRandomTags() )
				] ),
				new Subject( 'Civil', $this->getRandomTags(), [
					new Topic( 'Structural Analysis', $this->getRandomTags() ),
					new Topic( 'Geotechnical Engineering', $this->getRandomTags() ),
					new Topic( 'Hydraulics', $this->getRandomTags() ),
					new Topic( 'Environmental Engineering', $this->getRandomTags() ),
					new Topic( 'Transportation Engineering', $this->getRandomTags() )
				] ),
				new Subject( 'Computer Science', $this->getRandomTags(), [
					new Topic( 'Algorithms', $this->getRandomTags() ),
					new Topic( 'Data Structures', $this->getRandomTags() ),
					new Topic( 'Operating Systems', $this->getRandomTags() ),
					new Topic( 'Databases', $this->getRandomTags() ),
					new Topic( 'Networks', $this->getRandomTags() )
				] ),
				new Subject( 'Chemical', $this->getRandomTags(), [
					new Topic( 'Process Engineering', $this->getRandomTags() ),
					new Topic( 'Chemical Reactions', $this->getRandomTags() ),
					new Topic( 'Thermodynamics', $this->getRandomTags() ),
					new Topic( 'Material Science', $this->getRandomTags() ),
					new Topic( 'Transport Phenomena', $this->getRandomTags() )
				] )
			] ),
			new Group( 'Medicine', $this->getRandomTags(), [
				new Subject( 'Anatomy', $this->getRandomTags(), [
					new Topic( 'Skeletal System', $this->getRandomTags() ),
					new Topic( 'Muscular System', $this->getRandomTags() ),
					new Topic( 'Nervous System', $this->getRandomTags() ),
					new Topic( 'Cardiovascular System', $this->getRandomTags() ),
					new Topic( 'Digestive System', $this->getRandomTags() )
				] ),
				new Subject( 'Physiology', $this->getRandomTags(), [
					new Topic( 'Cell Physiology', $this->getRandomTags() ),
					new Topic( 'Respiratory Physiology', $this->getRandomTags() ),
					new Topic( 'Renal Physiology', $this->getRandomTags() ),
					new Topic( 'Neurophysiology', $this->getRandomTags() ),
					new Topic( 'Endocrinology', $this->getRandomTags() )
				] ),
				new Subject( 'Pathology', $this->getRandomTags(), [
					new Topic( 'General Pathology', $this->getRandomTags() ),
					new Topic( 'Systemic Pathology', $this->getRandomTags() ),
					new Topic( 'Hematopathology', $this->getRandomTags() ),
					new Topic( 'Clinical Pathology', $this->getRandomTags() ),
					new Topic( 'Forensic Pathology', $this->getRandomTags() )
				] ),
				new Subject( 'Pharmacology', $this->getRandomTags(), [
					new Topic( 'Drug Metabolism', $this->getRandomTags() ),
					new Topic( 'Chemotherapy', $this->getRandomTags() ),
					new Topic( 'Toxicology', $this->getRandomTags() ),
					new Topic( 'Clinical Pharmacology', $this->getRandomTags() ),
					new Topic( 'Neuropharmacology', $this->getRandomTags() )
				] ),
				new Subject( 'Surgery', $this->getRandomTags(), [
					new Topic( 'General Surgery', $this->getRandomTags() ),
					new Topic( 'Orthopedic Surgery', $this->getRandomTags() ),
					new Topic( 'Neurosurgery', $this->getRandomTags() ),
					new Topic( 'Cardiothoracic Surgery', $this->getRandomTags() ),
					new Topic( 'Plastic Surgery', $this->getRandomTags() )
				] )
			] ),
			new Group( 'Physics', $this->getRandomTags(), [
				new Subject( 'Mechanics', $this->getRandomTags(), [
					new Topic( "Newton's Laws", $this->getRandomTags() ),
					new Topic( 'Motion and Forces', $this->getRandomTags() ),
					new Topic( 'Work and Energy', $this->getRandomTags() ),
					new Topic( 'Momentum', $this->getRandomTags() ),
					new Topic( 'Rotational Dynamics', $this->getRandomTags() )
				] ),
				new Subject( 'Thermodynamics', $this->getRandomTags(), [
					new Topic( 'Laws of Thermodynamics', $this->getRandomTags() ),
					new Topic( 'Heat Transfer', $this->getRandomTags() ),
					new Topic( 'Entropy', $this->getRandomTags() ),
					new Topic( 'Thermal Expansion', $this->getRandomTags() ),
					new Topic( 'Statistical Mechanics', $this->getRandomTags() )
				] ),
				new Subject( 'Electromagnetism', $this->getRandomTags(), [
					new Topic( 'Electric Fields', $this->getRandomTags() ),
					new Topic( 'Magnetic Fields', $this->getRandomTags() ),
					new Topic( 'Electromagnetic Waves', $this->getRandomTags() ),
					new Topic( 'Circuits', $this->getRandomTags() ),
					new Topic( 'Induction', $this->getRandomTags() )
				] ),
				new Subject( 'Quantum Mechanics', $this->getRandomTags(), [
					new Topic( 'Wave-Particle Duality', $this->getRandomTags() ),
					new Topic( 'Quantum States', $this->getRandomTags() ),
					new Topic( 'Quantum Entanglement', $this->getRandomTags() ),
					new Topic( 'Quantum Tunneling', $this->getRandomTags() ),
					new Topic( 'Uncertainty Principle', $this->getRandomTags() )
				] ),
				new Subject( 'Optics', $this->getRandomTags(), [
					new Topic( 'Reflection and Refraction', $this->getRandomTags() ),
					new Topic( 'Wave Optics', $this->getRandomTags() ),
					new Topic( 'Optical Instruments', $this->getRandomTags() ),
					new Topic( 'Photonics', $this->getRandomTags() ),
					new Topic( 'Fiber Optics', $this->getRandomTags() )
				] )
			] ),
			new Group( 'Chemistry', $this->getRandomTags(), [
				new Subject( 'Organic Chemistry', $this->getRandomTags(), [
					new Topic( 'Hydrocarbons', $this->getRandomTags() ),
					new Topic( 'Alcohols and Ethers', $this->getRandomTags() ),
					new Topic( 'Carboxylic Acids', $this->getRandomTags() ),
					new Topic( 'Amines and Amides', $this->getRandomTags() ),
					new Topic( 'Aromatic Compounds', $this->getRandomTags() )
				] ),
				new Subject( 'Inorganic Chemistry', $this->getRandomTags(), [
					new Topic( 'Periodic Table', $this->getRandomTags() ),
					new Topic( 'Chemical Bonding', $this->getRandomTags() ),
					new Topic( 'Coordination Compounds', $this->getRandomTags() ),
					new Topic( 'Crystal Field Theory', $this->getRandomTags() ),
					new Topic( 'Transition Metals', $this->getRandomTags() )
				] ),
				new Subject( 'Physical Chemistry', $this->getRandomTags(), [
					new Topic( 'Chemical Kinetics', $this->getRandomTags() ),
					new Topic( 'Chemical Equilibrium', $this->getRandomTags() ),
					new Topic( 'Quantum Chemistry', $this->getRandomTags() ),
					new Topic( 'Surface Chemistry', $this->getRandomTags() ),
					new Topic( 'Spectroscopy', $this->getRandomTags() )
				] ),
				new Subject( 'Analytical Chemistry', $this->getRandomTags(), [
					new Topic( 'Qualitative Analysis', $this->getRandomTags() ),
					new Topic( 'Quantitative Analysis', $this->getRandomTags() ),
					new Topic( 'Instrumental Methods', $this->getRandomTags() ),
					new Topic( 'Chromatography', $this->getRandomTags() ),
					new Topic( 'Electrochemical Analysis', $this->getRandomTags() )
				] ),
				new Subject( 'Biochemistry', $this->getRandomTags(), [
					new Topic( 'Enzymes', $this->getRandomTags() ),
					new Topic( 'Metabolism', $this->getRandomTags() ),
					new Topic( 'DNA and RNA', $this->getRandomTags() ),
					new Topic( 'Proteins', $this->getRandomTags() ),
					new Topic( 'Lipids', $this->getRandomTags() )
				] )
			] )
		];
	}

}

class Topic {
	public $name;
	public $tags;

	public function __construct( $name, $tags ) {
		$this->name = $name;
		$this->tags = $tags;
	}
}

class Subject {
	public $name;
	public $tags;
	public $topics;

	public function __construct( $name, $tags, $topics ) {
		$this->name   = $name;
		$this->tags   = $tags;
		$this->topics = $topics;
	}
}

class Group {
	public $name;
	public $tags;
	public $subjects;

	public function __construct( $name, $tags, $subjects ) {
		$this->name     = $name;
		$this->tags     = $tags;
		$this->subjects = $subjects;
	}
}