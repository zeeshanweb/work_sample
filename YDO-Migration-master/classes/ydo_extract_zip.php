<?php
    $file = 'ydo_migration.zip';	 
	$path = pathinfo( realpath( $file ), PATHINFO_DIRNAME );	 
	$zip = new ZipArchive;
	$res = $zip->open($file);
	if ($res === TRUE) {
		$zip->extractTo( $path );
		$zip->close();
		echo "WOOT! $file extracted to $path";
	}
	else {
		echo "Doh! I couldn't open $file";
	}