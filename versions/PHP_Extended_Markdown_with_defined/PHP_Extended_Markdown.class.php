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

/**
 * Get the Extended Markdown Grammar PHP class
 */
if (!@class_exists('PHP_Extended_Markdown_Grammar'))
	require_once __DIR__."/PHP_Extended_Markdown_Grammar.class.php";

/**
 * Get the output formats interface of Extended Markdown PHP class
 */
require_once __DIR__."/PHP_Extended_Markdown_OutputFormat.class.php";

/**
 * PHP Extended Markdown Class
 *
 * This class is the global Markdown API.
 *
 * * it uses all the grammar rules defined in PHP_Extended_Markdown_Grammar class
 * * it manages a pile of markdown processed contents to retain each of them
 * * it works with a specific and external Output Format class, which is by default HTML
 * * Markdown contents are transformed performing a set of "Gamuts" that allow contents processing in certain orders
 *
 */
class PHP_Extended_Markdown extends PHP_Extended_Markdown_Grammar
{

	/**#@+
	 * Class infos
	 */
	static $class_name = 'PHP Extended Markdown';
	static $class_version = '1.0';
	static $class_sources = 'https://github.com/PieroWbmstr/Extended_Markdown';
	/**#@-*/

	/**
	 * Original text
	 */
	protected $text_input;

	/**
	 * Final result
	 */
	protected $text_output;

	/**
	 * Registry of the parser stacks
	 */
	protected $stacks=array();

	/**
	 * Are-we processing an object ?
	 */
	var $in_stack=false;

	/**
	 * The output format (default is HTML)
	 */
	protected $format='html';

	/**#@+
	 * GAMUTS
	 */

	/**
	 * These are very first in transforming
	 */
	var $transform_gamut = array(
		'doRemoveUtf8Marker'       	=> 5,
		'doStandardizeLineEnding'  	=> 10,
		'doAppendEndingNewLines'   	=> 15,
		'detab'                  	=> 20,
		'hashHTMLBlocks'            => 25,
		'stripSapcedLines'       	=> 30,
	);

	/**
	 * These are first executed commands
	 */
	var $document_gamut = array(
		"stripMetaData"          	  => 1,
		"doFencedCodeBlocks"          => 5,
		"stripNotes"                  => 10,
		"stripLinkDefinitions"        => 20,
		"stripAbbreviations"          => 25,
		"runBasicBlockGamut"          => 30,
		"appendMetaData"          	  => 35,
		"appendNotes"                 => 40,
//		"doDebug"=>50,
	);

	/**
	 * These are all the transformations that occur *within* block-level
	 * tags like paragraphs, headers, and list items.
	 */
	var $span_gamut = array(
		// Process character escapes, code spans, and inline HTML in one shot.
		"parseSpan"                => -30,
//		"doDebug"=>4,
		"doNotes" 				   => 5,
		// Process anchor and image tags. Images must come first,
		// because ![foo][f] looks like an anchor.
		"doImages"                 => 10,
		"doAnchors"                => 20,
		// Make links out of things like `<http://example.com/>`
		// Must come after doAnchors, because you can use < and >
		// delimiters in inline links like [this](<url>).
		"doAutoLinks"              => 30,
		"encodeAmpsAndAngles"      => 40,
		"doEmphasis"               => 50,
		"doHardBreaks"             => 60,
		"doAbbreviations"          => 70,
	);

	/**
	 * These are all the transformations that form block-level
	 * tags like paragraphs, headers, and list items.
	 */
	var $block_gamut = array(
		"doFencedCodeBlocks" => 5,
		"doHeaders"          => 10,
		"doTables"           => 15,
		"doHorizontalRules"  => 20,
		"doLists"            => 40,
		"doDefinitionsLists" => 45,
		"doCodeBlocks"       => 50,
		"doBlockQuotes"      => 60,
	);
	/**#@-*/

// -----------------------------------
// PARENT CONSTRUCTOR
// -----------------------------------

	public function PHP_Extended_Markdown()
	{
		// parent constructor
		parent::PHP_Extended_Markdown_Grammar();

		// build the formater
		$this->buildFormater();
		
		// Sort document, block, and span gamuts in ascendent priority order
		asort($this->document_gamut);
		asort($this->block_gamut);
		asort($this->span_gamut);
	}

	protected static $emd_instance;

	public static function getInstance()
	{
		if (empty(self::$emd_instance))
		{
			self::$emd_instance = new PHP_Extended_Markdown;
		}
		return self::$emd_instance;
	}

// -----------------------------------
// OUTPUT FORMATERS
// -----------------------------------

	public function buildFormater()
	{
		$formater_f = strtoupper($this->format).'.class.php';
		$formater_cl = 'PHP_Extended_Markdown_OutputFormat_'.strtoupper($this->format);
		$_f = __DIR__.'/OutputFormat/'.$formater_f;
		if (@file_exists($_f)) {
			require_once $_f;
			$this->output_formater = new $formater_cl;
		} else {
			throw new Exception( sprintf("Definition class for output format '%s' not found!", $this->format) );
		}
	}

	public function runFormaterMethod( )
	{
		$args = func_get_args();
		$_meth = array_shift($args);
		if (method_exists($this->output_formater, $_meth)) {
			return call_user_func_array(array($this->output_formater, $_meth), $args);
		} else {
			if (method_exists($this, $_meth)) {
				return call_user_func_array(array($this, $_meth), $args);
			} else {
				throw new Exception( sprintf("Unknown method '%s' in output formater!", $_meth) );
			}
		}
	}

// -----------------------------------
// INFORMATION STRING
// -----------------------------------

	/**
	 * Returns an informational string about class infos (version, name and sources)
	 * @param bool $html May we construct an HTML string (default is false)
	 * @return string The informational string
	 */
	static function info( $html=false )
	{
		return 
			( $html ? '<strong>' : '' )
			.self::$class_name
			.( $html ? '</strong>' : '' )
			.' version '.self::$class_version
			.' ('
			.( $html ? '<a href="'.self::$class_sources.'" target="_blank" title="See online">' : '' )
			.self::$class_sources
			.( $html ? '</a>' : '' )
			.')';
	}

	/**
	 * Debug function : get a dump of the $what param and then exit (or not)
	 * WARNING: first argument is not used (to allow doDebug from Gamut functions)
	 * @param misc $a Must stay empty
	 * @param misc $what Anything to debug
	 * @param bool $exit May we exit the script after debugging
	 * @return null Nothing is returned
	 */
	public function doDebug( $a='', $what=null, $exit=true ) 
	{
		echo '<pre>';
		if (!is_null($what)) var_export($what);
		else var_export( $this );
		echo '</pre>';
		if ($exit) exit(0);
	}

// -----------------------------------
// SETTERS/GETTERS
// -----------------------------------

	/**
	 * Set the initial text value
	 * @param string $txt The input text string
	 * @return object $this for method chaining
	 */	
	public function setInputText( $txt )
	{
		$this->text_input = $txt;
		return $this;
	}

	/**
	 * Get the initial text value
	 * @return string The original input text string
	 */	
	public function getInputText()
	{
		return $this->text_input;
	}

	/**
	 * Set the final text value
	 * @param string $txt The output text string
	 * @return object $this for method chaining
	 */	
	public function setOutputText( $txt )
	{
		$this->text_output = $txt;
		return $this;
	}

	/**
	 * Get the final parsed text value
	 * @return string The final parsed output text string
	 */	
	public function getOutputText()
	{
		return $this->text_output;
	}

	/**
	 * Get an object property value
	 * If we are not processing an object, the last stack is load and returned value
	 * will be from the last stack object
	 */	
	public function get( $what, $default=null )
	{
		if (false===$this->in_stack) {
			$_this = $this->getStack();			
			return !is_null($_this) && property_exists($_this, $what) ? $_this->$what : $default;
		} else {
			return property_exists($this, $what) ? $this->$what : $default;
		}
	}

	/**
	 * Clear all object stacks
	 * @return object $this for method chaining
	 */	
	public function clearStacks()
	{
		$this->stacks[] = array();
		return $this;
	}

	/**
	 * Add a new stack
	 * @param obj $_this A clone of the whole PHP_Extended_Markdown_Parser object
	 * @return object $this for method chaining
	 */	
	public function addStack( $_this )
	{
		$_this->clearStacks();
		$this->stacks[] = $_this;
		return $this;
	}

	/**
	 * Get an object property value
	 * @param int $index The index of the stack to get (optional)
	 * @return obj The object found in the stacks
	 */	
	public function getStack( $index=null )
	{
		if (is_null($index)) {
			return count($this->stacks)>0 ? end($this->stacks) : null;
		} else {
			return isset($this->stacks[$index]) ? $this->stacks[$index] : null;
		}
	}

// ----------------------------------
// INIT/SETUP/RESET
// ----------------------------------
	
	/**
	 * Setting up Extra-specific variables.
	 */
	protected function _setup() 
	{
		// Clear global hashes.
		$this->urls = $this->predef_urls;
		$this->titles = $this->predef_titles;
		$this->attributes = $this->predef_attributes;
		$this->html_hashes = array();
		$this->in_anchor = false;
		$this->footnotes = array();
		$this->glossaries = array();
		$this->citations = array();
		$this->notes_ordered = array();
		$this->abbr_desciptions = array();
		$this->abbr_word_re = '';
		$this->footnote_counter = 1;
		$this->notes_counter = 0;
		
		foreach ($this->predef_abbr as $abbr_word => $abbr_desc) {
			if ($this->abbr_word_re)
				$this->abbr_word_re .= '|';
			$this->abbr_word_re .= preg_quote($abbr_word);
			$this->abbr_desciptions[$abbr_word] = trim($abbr_desc);
		}
	}
	
	/**
	 * Clearing Extra-specific variables.
	 */
	protected function _teardown() 
	{
		$this->footnotes = array();
		$this->glossaries = array();
		$this->citations = array();
		$this->notes_ordered = array();
		$this->abbr_desciptions = array();
		$this->abbr_word_re = '';
		$this->urls = array();
		$this->titles = array();
		$this->attributes = array();
		$this->html_hashes = array();
		$this->in_stack=false;
	}
	
	
// ----------------------------------
// USER INTERFACE
// ----------------------------------
	
	/**
	 * Main function. Performs some preprocessing on the input text
	 * and pass it through the document gamut.
	 *
	 * @param string $text_arg The text to be parsed
	 * @return string The text parsed
	 * @see detab()
	 * @see hashHTMLBlocks()
	 * @see teardown()
	 * @see $document_gamut
	 */
	public function transform( $text_arg ) 
	{
		$this->_setup();
		$this->in_stack=true;

		// text in argument ?
		if (!empty($text_arg))
			$this->setInputText( $text_arg );

		// text to work on
		$text = $this->text_input;
	
		// Run transform gamut methods.
		foreach ($this->transform_gamut as $method => $priority) {
			$text = $this->$method($text);
		}

		// Run document gamut methods.
		foreach ($this->document_gamut as $method => $priority) {
			$text = $this->$method($text);
		}

		// setting parsed output
		$this->setOutputText( $text . "\n" );

		// remind current object in a stack
		$this->addStack( clone $this );
		// global clean
		$this->_teardown();

		return $this->text_output;
	}
	

// ----------------------------------
// PROCESS GAMUTS
// ----------------------------------
	
	/**
	 * Run block gamut tranformations.
	 *
	 * We need to escape raw HTML in Markdown source before doing anything 
	 * else. This need to be done for each block, and not only at the 
	 * begining in the Markdown function since hashed blocks can be part of
	 * list items and could have been indented. Indented blocks would have 
	 * been seen as a code block in a previous pass of hashHTMLBlocks.
	 *
	 * @param string $text The text to be parsed
	 * @return string The text parsed
	 * @see detab()
	 * @see runBasicBlockGamut()
	 */
	public function runBlockGamut($text) 
	{
		$text = $this->hashHTMLBlocks($text);
		return $this->runBasicBlockGamut($text);
	}
	
	/**
	 * Run block gamut tranformations, without hashing HTML blocks. This is 
	 * useful when HTML blocks are known to be already hashed, like in the first
	 * whole-document pass.
	 *
	 * @param string $text The text to be parsed
	 * @return string The text parsed
	 * @see $block_gamut
	 * @see formParagraphs()
	 */
	public function runBasicBlockGamut($text) 
	{
		foreach ($this->block_gamut as $method => $priority) {
			$text = $this->$method($text);
		}
		// Finally form paragraph and restore hashed blocks.
		$text = $this->formParagraphs($text);
		return $text;
	}
	
	/**
	 * Run span gamut tranformations
	 *
	 * @param string $text The text to be parsed
	 * @return string The text parsed
	 * @see $span_gamut
	 * @see formParagraphs()
	 */
	public function runSpanGamut($text) 
	{
		foreach ($this->span_gamut as $method => $priority) {
			$text = $this->$method($text);
		}
		return $text;
	}
	
}

// Endfile
