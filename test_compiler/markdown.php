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

// -----------------------------------
// LIBRARY
// -----------------------------------

// the global library
require_once MARKDOWN_EXTENDED_DIR.'/OO_Extended_Markdown.compile.php';

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

// -----------------------------------
// COMMAND LINE INTERFACE
// -----------------------------------

if (php_sapi_name() == 'cli') Markdown_CLI();

// Endfile