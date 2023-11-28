<?php
/**
 * File uploader service.
 *
 * @package StudyPlannerPro\Services;
 */

namespace StudyPlannerPro\Services;

use StudyPlannerPro\Initializer;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit(); // Exit if accessed directly.
}

/**
 * Class File_Uploader_Service.
 */
class File_Uploader_Service {
	/**
	 * The file to be uploaded ($_FILES).
	 *
	 * @var array $file The file to be uploaded ($_FILES)
	 */
	private array $file = array();

	public const FILE_TYPE_PDF = 'application/pdf';
	public const FILE_TYPE_JPEG = 'image/jpeg';
	public const FILE_TYPE_JPG = 'image/jpg';
	public const FILE_TYPE_PNG = 'image/png';
	public const FILE_TYPE_GIF = 'image/gif';

	/**
	 * The allowed file types.
	 *
	 * @var array $allowed_file_types The allowed file types
	 */
	private array $allowed_file_types = array();

	/**
	 * The maximum file size in MB.
	 *
	 * @var int $max_file_size The maximum file size in MB
	 */
	private int $max_file_size = 10;

	/**
	 * Attachment ID.
	 *
	 * @var int $attachment_id Attachment ID.
	 */
	private int $attachment_id = 0;

	/**
	 * Attachment URL.
	 *
	 * @var string $attachment_url Attachment URL.
	 */
	private string $attachment_url = '';

	/**
	 * File_Uploader_Service constructor.
	 *
	 */
	public function __construct() {
	}

	public function init() {
//		$this->check_and_delete_unused_files();

		return $this;
	}

	// <editor-fold desc="SETTERS BEGINS">.

	/**
	 * Set the file to be uploaded.
	 *
	 * @param array $file The file to be uploaded ($_FILES).
	 *
	 * @return self
	 */
	public function set_file( array $file ): self {
		$this->file = $file;

		return $this;
	}

	/**
	 * Set the allowed file types.
	 *
	 * @param array $allowed_file_types An array of allowed file types (e.g., [self::FILE_TYPE_PDF, self::FILE_TYPE_JPEG]).
	 *
	 * @return self
	 */
	public function set_allowed_file_types( array $allowed_file_types ): self {
		$this->allowed_file_types = $allowed_file_types;

		// Add the allowed file types.
		add_filter(
			'upload_mimes',
			array( $this, 'add_wp_custom_mime_types' )
		);

		return $this;
	}

	/**
	 * Set the maximum file size (in MB).
	 *
	 * @param int $max_file_size The maximum file size in MB.
	 *
	 * @return self
	 */
	public function set_max_file_size( int $max_file_size ): self {
		$this->max_file_size = $max_file_size;

		return $this;
	}

	/**
	 * Set the attachment ID.
	 *
	 * @param int $attachment_id The attachment ID.
	 *
	 * @return self
	 */
	private function set_attachment_id( int $attachment_id ): self {
		$this->attachment_id = $attachment_id;

		return $this;
	}

	/**
	 * Set the attachment URL.
	 *
	 * @param string $attachment_url The attachment URL.
	 *
	 * @return self
	 */
	private function set_attachment_url( string $attachment_url ): self {
		$this->attachment_url = $attachment_url;

		return $this;
	}

	// </editor-fold desc="SETTERS ENDS">.

	// <editor-fold desc="GETTERS BEGINS">.

	/**
	 * Get the attachment ID.
	 *
	 * @return int
	 */
	public function get_attachment_id(): int {
		return $this->attachment_id;
	}

	/**
	 * Get the attachment URL.
	 *
	 * @return string
	 */
	public function get_attachment_url(): string {
		return $this->attachment_url;
	}

	/**
	 * Get allowed file types.
	 *
	 * @return array Allowed file types.
	 */
	public function get_allowed_file_types(): array {
		// Apply filter.
		/**
		 * Filter the allowed file types.
		 *
		 * @param array $allowed_file_types Allowed file types.
		 *
		 * @since  0.0.2
		 */
		return apply_filters( 'jap_allowed_file_types', $this->allowed_file_types );
	}

	// </editor-fold desc="GETTERS ENDS">.

	/**
	 * Handle the file upload.
	 *
	 * @return self|Wp_Error Wp_Error if the file upload failed, self otherwise.
	 */
	public function handle_upload() {
		//		if ( ! defined( 'ALLOW_UNFILTERED_UPLOADS' ) ) {
		//			define( 'ALLOW_UNFILTERED_UPLOADS', true );
		//		}

		// Make sure the file is valid.
		if ( ! isset( $this->file['tmp_name'] ) ) {
			return new WP_Error(
				'invalid_file',
				__( 'Invalid file. Temporary file name not found.', 'teacher-student-manager' ),
				array(
					'temp_name' => $this->file['tmp_name'],
				)
			);
		}

		// Make sure the upload was from HTTP POST.
		if ( ! is_uploaded_file( $this->file['tmp_name'] ) ) {
			return new WP_Error(
				'invalid_upload_method',
				__( 'Invalid upload method. The file was not uploaded using HTTP POST.', 'teacher-student-manager' ),
			);
		}

		//		$file_type_info = wp_check_filetype( $this->file['name'], $this->get_allowed_file_types() );
		//		$file_type      = $file_type_info['type'];
		$file_type = $this->file['type'];

		// Make sure the file type is allowed.
		if ( ! in_array( $file_type, $this->get_allowed_file_types(), true ) ) {
			return new WP_Error(
				'invalid_file_type',
				__( 'Invalid file type.', 'teacher-student-manager' ),
				array(
					'file_type'          => $file_type,
					'allowed_file_types' => $this->allowed_file_types,
				)
			);
		}

		// Make sure the file size is not too large.
		$max_file_size_in_bytes = $this->max_file_size * 1024 * 1024;
		if ( $max_file_size_in_bytes > 0 && $this->file['size'] > $max_file_size_in_bytes ) {
			return new WP_Error(
				'file_too_large',
				__( 'File too large.', 'teacher-student-manager' ),
				array(
					'file_size'           => $this->file['size'],
					'max_file_size'       => $max_file_size_in_bytes,
					'max_file_size_in_mb' => $this->max_file_size,
				)
			);
		}

		$ext           = pathinfo( $this->file['name'], PATHINFO_EXTENSION );
		$new_file_name = Initializer::$plugin_name . '-' . wp_generate_uuid4() . '.' . $ext;

		// phpcs:disable WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$upload = wp_upload_bits( $new_file_name, null, file_get_contents( $this->file['tmp_name'] ) );

		if ( $upload['error'] ) {
			return new WP_Error(
				'upload_failed',
				__( 'Upload failed.', 'teacher-student-manager' ),
				array(
					'error' => $upload['error'],
				)
			);
		}

		$uploaded_url  = $upload['url'];
		$filetype      = wp_check_filetype( basename( $uploaded_url ), null );
		$wp_upload_dir = wp_upload_dir();
		$attachment    = array(
			'guid'           => $wp_upload_dir['url'] . '/' . basename( $uploaded_url ),
			'post_mime_type' => $filetype['type'],
			'post_title'     => preg_replace( '/\.[^.]+ $/', '', basename( $uploaded_url ) ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		);

		if ( null !== $new_file_name ) {
			$attachment['post_title'] = $new_file_name;
		}

		$attach_id = wp_insert_attachment( $attachment, $uploaded_url );

		if ( is_wp_error( $attach_id ) ) {
			return new WP_Error(
				'attachment_failed',
				__( 'Attachment failed.', 'teacher-student-manager' ),
				array(
					'error'   => $attach_id->get_error_message(),
					'code'    => $attach_id->get_error_code(),
					'message' => $attach_id->get_error_message(),
				)
			);
		}

		if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
			require_once ABSPATH . 'wp-admin/includes/image.php';
		}

		$file_url = $upload['url'];
		$this->set_attachment_id( $attach_id );
		$this->set_attachment_url( $file_url );

		$this->set_delete_date();

		return $this;
	}

	/**
	 * Set when the attachment will be deleted.
	 * Used to ensure all unused files are deleted. When used, clear the delete date.
	 * Set the delete date to tomorrow.
	 *
	 * @return void
	 */
	protected function set_delete_date(): void {
		// Set the delete date to tomorrow.
//		update_post_meta( $this->get_attachment_id(), Settings::PM_ATTACHMENT_DELETE_DATE, strtotime( '+1 day' ) );
	}

	/**
	 * Add custom mime types.
	 *
	 * @param array $mime_type Mime types.
	 *
	 * @return array Custom mime types.
	 */
	public function add_wp_custom_mime_types( array $mime_type ): array {
		$allowed_file_types = $this->get_allowed_file_types();

		$my_mimes = array();
		foreach ( $allowed_file_types as $file_type ) {
			$ext              = substr( $file_type, strpos( $file_type, '/' ) + 1 );
			$my_mimes[ $ext ] = $file_type;
		}

		return array_merge( $mime_type, $my_mimes );
	}
}
