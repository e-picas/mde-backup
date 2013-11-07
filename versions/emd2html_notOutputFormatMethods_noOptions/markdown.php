<?php
/**
 * PHP Extended Markdown
 * Copyright (c) 2012 Pierre Cassat
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
 *
 * @package 	PHP_Extended_Markdown
 * @license   	BSD
 * @link      	https://github.com/PieroWbmstr/Extended_Markdown
 */

// -----------------------------------
// LIBRARY
// -----------------------------------

require_once __DIR__."/PHP_Extended_Markdown.class.php";

// -----------------------------------
// STANDARD FUNCTION INTERFACE
// -----------------------------------

/**
 * Initialize the parser and return the result of its transform method
 * @param string $text A Markdown text to parse
 * @param string|array $options A set of options to extend defaults, or a config file path
 * @param bool $return_parser Set to TRUE to get the parser object
 * @return misc Parsed string or the parser itself
 * @see PHP_Extended_Markdown_Parser
 */
function Markdown( $text, $options=null, $return_parser=false ) {
	require_once __DIR__.'/OutputFormat/emd2html.class.php';
	// setup static parser variable
	static $parser;
	if (!isset($parser)) {
		$parser = new emd2html();
		$parser->PHP_Extended_Markdown( $options );
	}
	//$parser->doDebug();
	// transform text using the parser
	$_md = $parser->transform($text);
	// returns parser or transformed text
	return true===$return_parser ? $parser : $_md;
}

// Endfile
