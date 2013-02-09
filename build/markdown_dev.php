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

/**
 * The development mode :
 * * REBUILD : the grammar class is rebuilt on every request
 * * DEV : use existing generated files
 */
@define('MARKDOWN_MODE', 'DEV');

/**
 * The directory containing the Markdown class
 */
@define('MARKDOWN_GLOBAL_DIR', __DIR__.'/../src/');

/**
 * The directory for Markdown development
 */
@define('MARKDOWN_DEV_DIR', __DIR__.'/');

/**
 * The directory containing the Markdown class
 */
@define('MARKDOWN_GENERATED_DIR', __DIR__.'/generated/');

/**
 * The Markdwon Debugger class
 */
@define('MARKDOWN_FILE', MARKDOWN_GLOBAL_DIR.'PHP_Extended_Markdown.class.php');

/**
 * The Markdwon Debugger class
 */
@define('MARKDOWN_DEBUGGER_FILE', MARKDOWN_DEV_DIR.'PHP_Extended_Markdown_Debugger.class.php');

/**
 * The Markdwon Console class
 */
@define('MARKDOWN_CONSOLE_FILE', MARKDOWN_GLOBAL_DIR.'PHP_Extended_Markdown_Console.class.php');

/**
 * The Markdwon Builder class
 */
@define('MARKDOWN_BUILDER_FILE', MARKDOWN_DEV_DIR.'PHP_Extended_Markdown_Builder.class.php');

/**
 * The compiled version of Markdwon Grammar
 */
@define('MARKDOWN_GRAMMAR_FILENAME', 'PHP_Extended_Markdown_Grammar.compile.php');

/**
 * The compiled version of Markdwon OutputFormat Interface
 */
@define('MARKDOWN_OUTPUTFORMAT_INTERFACE_FILENAME', 'PHP_Extended_Markdown_OutputFormat.compile.php');

/**
 * The path to compiled version of Markdwon Grammar
 */
@define('MARKDOWN_GRAMMAR_FILE', MARKDOWN_GENERATED_DIR.MARKDOWN_GRAMMAR_FILENAME);

/**
 * The path to compiled version of Markdwon OutputFormat Interface
 */
@define('MARKDOWN_OUTPUTFORMAT_INTERFACE_FILE', MARKDOWN_GENERATED_DIR.MARKDOWN_OUTPUTFORMAT_INTERFACE_FILENAME);

/**
 * The Grammar directory
 */
@define('MARKDOWN_GRAMMAR_DIR', 'Grammar');

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
	// setup static parser variable
	static $parser;
	if (!isset($parser)) {
		$parser = new PHP_Extended_Markdown( $options );
	}
	// transform text using the parser
	$_md = $parser->transform($text);
	//$parser->doDebug();
	// returns parser or transformed text
	return true===$return_parser ? $parser : $_md;
}

/**
 * Initialize a unique instance of the parser and return the result of its transform method
 * @param string $text A Markdown text to parse
 * @param string|array $options A set of options to extend defaults, or a config file path
 * @param bool $return_parser Set to TRUE to get the parser object
 * @return misc Parsed string or the parser itself
 * @see PHP_Extended_Markdown_Parser
 */
function MarkdownAsSingleton( $text, $options=null, $return_parser=false ) {
	// setup static parser variable
	$parser =& PHP_Extended_Markdown::getInstance( $options );
	// transform text using the parser
	$_md = $parser->transform($text);
	//$parser->doDebug();
	// returns parser or transformed text
	return true===$return_parser ? $parser : $_md;
}

/**
 * Initialize the parser debugger and return the result of its transform method
 * @param string $text A Markdown text to parse
 * @param string|array $options A set of options to extend defaults, or a config file path
 * @param bool $debug Activate debug infos
 * @param bool $return_parser Set to TRUE to get the parser object
 * @return misc Parsed string or the parser itself
 * @see PHP_Extended_Markdown_Parser
 */
function MarkdownDebug( $text, $options=null, $debug=true, $return_parser=false ) {
	// setup static parser variable
	static $debug_parser;
	if (!isset($debug_parser)) {
		require_once MARKDOWN_DEBUGGER_FILE;
		$debug_parser = new PHP_Extended_Markdown_Debugger( $options, $debug );
	}
	// transform text using the parser
	$_md = $debug_parser->transform($text);
	//$parser->doDebug();
	// returns parser or transformed text
	return true===$return_parser ? $debug_parser : $_md;
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
		require_once MARKDOWN_CONSOLE_FILE;
		$console = new PHP_Extended_Markdown_Console;
	}
	// run the interface
	return $console->run();
}

// -----------------------------------
// COMPILATION AT RUNTIME
// -----------------------------------

// the global library
if (!@file_exists(MARKDOWN_COMPILED_FILE) || MARKDOWN_MODE==='REBUILD')
{
	require_once MARKDOWN_BUILDER_FILE;
	$builder = new PHP_Extended_Markdown_Builder;
	$builder->directory_to_scan = MARKDOWN_GRAMMAR_DIR;
	$builder->grammar_filename = MARKDOWN_GRAMMAR_FILENAME;
	$builder->outputformat_interface_filename = MARKDOWN_OUTPUTFORMAT_INTERFACE_FILENAME;
	$builder->compile( MARKDOWN_MODE==='REBUILD' ? true : false );
}
require_once MARKDOWN_GRAMMAR_FILE;
require_once MARKDOWN_OUTPUTFORMAT_INTERFACE_FILE;
require_once MARKDOWN_FILE;

// Endfile