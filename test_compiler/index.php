<?php
// show errors at least initially
ini_set('display_errors','1'); error_reporting(E_ALL ^ E_NOTICE);

function _scandir( $dir, $allowed_extension='php' ) {
	$ctt=$alt_ctt='';
	if (!@file_exists($dir))
		trigger_error("Directory '$dir' does not exist!", E_USER_ERROR);
	if (!@is_dir($dir))
		trigger_error("'$dir' is not a directory!", E_USER_ERROR);
	$d = scandir($dir);
	if (false!==$d){
		foreach ($d as $f) {
			if (!in_array($f, array('.', '..'))){
				$f_path = $dir.'/'.$f;
				if (is_file($f_path) && end(explode('.', $f_path))==$allowed_extension){
					$ctt .= _strip_php_tags( file_get_contents($f_path) );
				} elseif (is_dir($f_path)) {
					$alt_ctt .= _scandir( $f_path );
				}
			}
		}		
	}
	return $ctt.$alt_ctt;
}

function _strip_php_tags( $str ) {
	return str_replace(array('<?php','?>','// Endfile'), '', $str);
}

$_php = _scandir( realpath('../OO_Extended_Markdown/Markdown') );
$ok = file_put_contents( 'OO_Extended_Markdown.compile.php', '<?php'.PHP_EOL.$_php);

exit('yo');
// Endfile