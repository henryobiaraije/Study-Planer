<?php
function extract_zip( $zip_file, $destination ) {
	$zip = new \ZipArchive();
	$x   = $zip->open( $zip_file );
	if ( $x === true ) {
		$zip->extractTo( $destination );
		$zip->close();

		return true;
	}

	return false;
}

// wordpress plugin directory.
$zip_file = WP_PLUGIN_DIR . '/study-planner-pro/test-zip.zip';
$dest     = WP_PLUGIN_DIR . '/study-planner-pro/test-zip';
// extract zip.
//extract_zip( $zip_file, $dest );


function spDeleteDir( $dirPath ) {
	if ( ! is_dir( $dirPath ) ) {
		throw new InvalidArgumentException( "$dirPath must be a directory" );
	}
	if ( substr( $dirPath, strlen( $dirPath ) - 1, 1 ) != '/' ) {
		$dirPath .= '/';
	}
	$files = glob( $dirPath . '*', GLOB_MARK );
	foreach ( $files as $file ) {
		if ( is_dir( $file ) ) {
			spDeleteDir( $file );
		} else {
			unlink( $file );
		}
	}
	rmdir( $dirPath );
}

// Delete folder.
$vendor = WP_PLUGIN_DIR . '/study-planner-pro/vendor';
spDeleteDir( $vendor );
