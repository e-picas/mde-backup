<?php
#
# Markdown Extra  -  A text-to-HTML conversion tool for web writers
#
# PHP Markdown & Extra
# Copyright (c) 2004-2009 Michel Fortin  
# <http://michelf.com/projects/php-markdown/>
#
# Original Markdown
# Copyright (c) 2004-2006 John Gruber  
# <http://daringfireball.net/projects/markdown/>
#

// -----------------------------------
// LIBRARY
// -----------------------------------

require_once __DIR__."/Markdown/Parser.php";
require_once __DIR__."/Markdown/ExtraParser.php";

// -----------------------------------
// CONFIG
// -----------------------------------

# Change to ">" for HTML output
@define( 'MARKDOWN_EMPTY_ELEMENT_SUFFIX',  " />");

# Define the width of a tab for code blocks.
@define( 'MARKDOWN_TAB_WIDTH',     4 );

# Optional title attribute for footnote links and backlinks.
@define( 'MARKDOWN_FN_LINK_TITLE',         "" );
@define( 'MARKDOWN_FN_BACKLINK_TITLE',     "" );

# Optional class attribute for footnote links and backlinks.
@define( 'MARKDOWN_FN_LINK_CLASS',         "" );
@define( 'MARKDOWN_FN_BACKLINK_CLASS',     "" );


// -----------------------------------
// STANDARD FUNCTION INTERFACE
// -----------------------------------

@define( 'MARKDOWN_PARSER_CLASS',  'Markdown\ExtraParser' );

/**
 * Initialize the parser and return the result of its transform method
 * @param string $text A Markdown text to parse
 * @param string|array $options A set of options to extend defaults, or a config file path
 * @param bool $return_parser Set to TRUE to get the parser object
 * @return misc Parsed string or the parser itself
 * @see PHP_Extended_Markdown_Parser
 */
function Markdown( $text, $options=null, $return_parser=false ) {
	// setup static parser variable
	static $parser;
	if (!isset($parser)) {
		$parser_class = MARKDOWN_PARSER_CLASS;
		$parser = new $parser_class( $options );
	}
	// transform text using the parser
	$_md = $parser->transform($text);
	// returns parser or transformed text
	return true===$return_parser ? $parser : $_md;
}

/**
 * Use the Markdown Extended command line interface
 * @return misc The result of the Extended Markdown command line interface
 * @see PHP_Extended_Markdown_Console
 */
function MarkdownCommandLine() {
	// setup static console variable
	static $console;
	if (!isset($console)) {
		require_once __DIR__."/Console.php";
		$console = new Console;
	}
	// run the interface
	return $console->run();
}

// Endfile