<?php
/**
 * PHP Extended Markdown
 * Copyright (c) 2004-2012 Pierre Cassat
 *
 * original MultiMarkdown
 * Copyright (c) 2005-2009 Fletcher T. Penney
 * <http://fletcherpenney.net/>
 *
 * original PHP Markdown & Extra
 * Copyright (c) 2004-2012 Michel Fortin  
 * <http://michelf.com/projects/php-markdown/>
 *
 * original Markdown
 * Copyright (c) 2004-2006 John Gruber  
 * <http://daringfireball.net/projects/markdown/>
 */

// -----------------------------------
// GLOBAL SETTINGS
// -----------------------------------

define('OO_DIR', __DIR__.'/../OO_Extended_Markdown');

// Global configuration file
@define('MARKDOWN_CONFIGFILE', OO_DIR."/markdown_config.ini");

// Markdown directory (for safe inclusions)
@define('MARKDOWN_EXTENDED_DIR', __DIR__);

// The compiled version of Markdwon
@define('MARKDOWN_COMPILED_FILE', MARKDOWN_EXTENDED_DIR.'/OO_Extended_Markdown.compile.php');

// -----------------------------------
// LIBRARY
// -----------------------------------

// requires PHP 5.1+
if (version_compare(PHP_VERSION, '5.1.0', '<')) {
	throw new Exception(sprintf(
		"Markdown Extended requires that your system runs PHP version 5.1 minimum, current version is <%s> on a sytem [%s]"
		, PHP_VERSION, php_uname()
	));
}

// -----------------------------------
// STANDARD FUNCTION INTERFACE
// -----------------------------------

/**
 * Initialize the parser and return the result of its transform method
 */
function Markdown($text, $options=null) {
	// Setup static MD object
	$markdown = Markdown_Extended::getInstance();
	$parser = $markdown::get('Markdown_Extended_Parser', $options);
	// Transform text using the parser
	return $parser->transform($text);
}

/**
 * Use the Markdown Extended OO command line interface
 */
function Markdown_CLI() {
	// Setup static MD object
	$markdown = Markdown_Extended::getInstance();
	$markdown::load('Markdown_Builder');
	$console = $markdown::get('Markdown_Extended_Console');
	// Build documentation content
	return $console->run();
}

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

// -----------------------------------
// COMPILATION AT RUNTIME
// -----------------------------------

// the global library
if (!@file_exists(MARKDOWN_COMPILED_FILE)){
	$_php = _scandir( realpath( OO_DIR.'/Markdown' ) );
	$ok = file_put_contents( MARKDOWN_COMPILED_FILE, '<?php'.PHP_EOL.$_php);
	if (false===$ok){
		trigger_error("Can't write compiled file!", E_USER_ERROR);
	}
}
require_once MARKDOWN_COMPILED_FILE;

// -----------------------------------
// COMMAND LINE INTERFACE
// -----------------------------------

if (php_sapi_name() == 'cli') Markdown_CLI();

// Endfile