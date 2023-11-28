<?php
/**
 * REST controller for File uploader.
 *
 * @package Job_And_Promotion\Includes\REST;
 */

namespace StudyPlannerPro\Rest;

use StudyPlannerPro\Libs\Settings;
use StudyPlannerPro\Services\File_Uploader_Service;
use WP_Error;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

if ( ! defined( 'ABSPATH' ) ) {
	exit(); // Exit if accessed directly.
}

/**
 * Class Rest_File_Upload_Controller.
 */
class Rest_File_Upload_Controller extends WP_REST_Controller {

	/**
	 * The resource name.
	 *
	 * @var string $resource_name The resource name.
	 */
	protected string $resource_name;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->namespace     = Settings::REST_NAMESPACE;
		$this->resource_name = 'file-upload';
	}

	/**
	 * Initialize the rest endpoints.
	 *
	 * @return void
	 */
	public function init(): void {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes(): void {
		// Upload image file.
		register_rest_route(
			$this->namespace,
			'/' . $this->resource_name . '/image',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'handle_upload_image' ),
					'permission_callback' => '__return_true',
					'args'                => array(
						'image' => array(
							'required'          => false,
							'validate_callback' => array( $this, 'validate_file' ),
							'type'              => 'file',
							'description'       => __( 'The Image file to upload.', 'teacher-student-manager' ),
						),
					),
				),
			)
		);

		// Get image url.
		register_rest_route(
			$this->namespace,
			'/' . $this->resource_name . '/image-url/(?P<image_id>\d+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'handle_get_image_url' ),
					'permission_callback' => '__return_true',
					'args'                => array(
						'image_id' => array(
							'required'    => true,
							'type'        => 'integer',
							'description' => __( 'The image attachment id.', 'teacher-student-manager' ),
						),
					),
				),
			)
		);
	}

	/**
	 * Check if a given user has permission to do this in weddings.
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return bool|\WP_Error True if the request has read access, WP_Error object otherwise.
	 */
	public function upload_pdf_permission_check( WP_REST_Request $request ) {
		$user = wp_get_current_user();
		if ( ! ( $user instanceof \WP_User ) ) {
			return new WP_Error( 'rest_forbidden',
				esc_html__( 'You are not logged in.', 'teacher-student-manager' ),
				array( 'status' => 401 ) );
		}


		return true;
	}

	/**
	 * Handle upload image.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function handle_upload_image( WP_REST_Request $request ) {
		$files = $request->get_file_params();
		if ( ! array_key_exists( 'image', $files ) ) {
			return new WP_Error(
				'no-file',
				__( 'No file was uploaded. Please add an image file with the key "image"', 'teacher-student-manager' ),
				array( 'status' => 400 )
			);
		}
		$upload_service = new File_Uploader_Service();
		$upload_service = $upload_service
			->set_file( $files['image'] )
			->set_allowed_file_types(
				array(
					File_Uploader_Service::FILE_TYPE_JPG,
					File_Uploader_Service::FILE_TYPE_JPEG,
					File_Uploader_Service::FILE_TYPE_GIF,
					File_Uploader_Service::FILE_TYPE_PNG,
				)
			)
			->handle_upload();

		if ( ! is_wp_error( $upload_service ) ) {
			$attachment_id = $upload_service->get_attachment_id();

			return new WP_REST_Response(
				array(
					'attachment_id' => $attachment_id,
					'url'           => wp_get_attachment_url( $attachment_id ),
				),
				201
			);
		}

		if ( is_wp_error( $upload_service ) ) {
			return $upload_service;
		}

		return new WP_Error( 'cant-create',
			__( 'Error unknown. 8472', 'teacher-student-manager' ),
			array( 'status' => 500 ) );
	}

	/**
	 * Get image url.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function handle_get_image_url( WP_REST_Request $request ) {
		$image_id = $request->get_param( 'image_id' );
		if ( ! $image_id ) {
			return new WP_Error(
				'no-image-id',
				__( 'No image id was provided.', 'teacher-student-manager' ),
				array( 'status' => 400 )
			);
		}

		return new WP_REST_Response(
			wp_get_attachment_url( $image_id ),
			200
		);
	}

	/**
	 * Validate the uploaded file
	 *
	 * @param mixed $file The file parameter value.
	 * @param WP_REST_Request $request The REST API request object.
	 * @param string $param The parameter name.
	 *
	 * @return WP_Error|bool WP_Error if the file is invalid, true otherwise
	 */
	public function validate_file( $file, WP_REST_Request $request, string $param ) {
		if ( ! empty( $file['tmp_name'] ) && ! is_uploaded_file( $file['tmp_name'] ) ) {
			return new WP_Error( 'invalid_file', 'Invalid file.', array( 'status' => 400 ) );
		}

		return true;
	}
}