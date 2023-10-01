<?php
/**
 * File service class.
 */

namespace StudyPlanner\Services;

use StudyPlanner\Initializer;

class FileService {

	public $js_files = [];
	public $css_files = [];


	/**
	 * Retrieve an array of files with a specific extension in a folder and its subfolders.
	 *
	 * @param string $folder The folder directory to search for files.
	 * @param string $extension The file extension to search for (e.g., 'js', 'css').
	 * @param string $relativePath (Internal use) Relative path from the $folder.
	 *
	 * @return array An array of arrays, where each sub-array contains full and relative file paths.
	 */
	function get_files_recursive( string $folder, string $extension, string $relativePath = '' ) {
		$matching_files = []; // Initialize an empty array to store matching files.

		// Check if the folder exists.
		if ( is_dir( $folder ) ) {
			// Use the opendir function to open the directory.
			if ( $dir_handle = opendir( $folder ) ) {
				// Loop through each item in the directory.
				while ( ( $file = readdir( $dir_handle ) ) !== false ) {
					// Exclude . and .. directories.
					if ( $file !== '.' && $file !== '..' ) {
						$file_path = $folder . DIRECTORY_SEPARATOR . $file;

						// Calculate the relative path by including the folder structure.
						$relative_file_path = empty( $relativePath ) ? $file : $relativePath . DIRECTORY_SEPARATOR . $file;

						// Check if the item is a file and has the specified extension.
						if ( is_file( $file_path ) && pathinfo( $file_path, PATHINFO_EXTENSION ) === $extension ) {
							// Add the full and relative file paths to the array.
							$matching_files[] = [ $file_path, $relative_file_path ];
						} elseif ( is_dir( $file_path ) ) {
							// If it's a directory, recursively call the function with updated relative path.
							$subfolder_matching_files = $this->get_files_recursive( $file_path, $extension, $relative_file_path );
							foreach ( $subfolder_matching_files as $subfile ) {
								$matching_files[] = $subfile;
							}
						}
					}
				}

				closedir( $dir_handle ); // Close the directory handle.
			}
		}

		return $matching_files; // Return the array of arrays containing file paths.
	}

	/**
	 *
	 * @param string $file_name
	 * @param string $ext
	 *
	 * @return string
	 */
	private function mp_get_file_url( string $file_name, string $ext = 'js' ): string {
		$all_files = $this->get_files_recursive( Initializer::$js_dir, $ext );
		$file_name = str_replace( '/', DIRECTORY_SEPARATOR, $file_name );
		$js_url    = '';
		foreach ( $all_files as $file ) {
			if ( str_contains( $file[1], $file_name ) ) {
				$js_url = Initializer::$js_url . '/' . str_replace( '\\', '/', $file[1] ); // e.g. 'js/admin/admin-topics.js
				break;
			}
		}

		// Get js url by dynamically looking in the js folder for a file, e.g. $filename + hash + 'js'
		return $js_url;
	}

	public static function mp_get_js_url( string $file_name ): string {
		return ( new self() )->mp_get_file_url( $file_name, 'js' );
	}

	public static function mp_get_css_url( string $file_name ): string {
		return ( new self() )->mp_get_file_url( $file_name, 'css' );
	}


}
