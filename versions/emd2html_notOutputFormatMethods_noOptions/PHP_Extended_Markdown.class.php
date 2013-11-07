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
if (!@interface_exists('PHP_Extended_Markdown_OutputFormat'))
	require_once __DIR__."/PHP_Extended_Markdown_OutputFormat.class.php";

/**
 * PHP Extended Markdown Class
 *
 * This class is the global Markdown API.
 *
 * -   it uses all the grammar rules defined in PHP_Extended_Markdown_Grammar class
 * -   it manages a pile of markdown processed contents to retain each of them
 * -   it works with a specific and external Output Format class, which is by default HTML
 * -   Markdown contents are transformed performing a set of "Gamuts" that allow contents processing in certain orders
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
	 * Are we processing an object ?
	 */
	protected $in_stack=false;

	/**
	 * The output format (in lower case - default is HTML)
	 */
	protected $format='html';

	/**
	 * Options registry
	 */
	protected $options=array();

	/**
	 * Default options INI file
	 */
	protected $config_file='emd_config.ini';

// -----------------------------------
// CONSTRUCTOR & INIT
// -----------------------------------

	/**
	 * Object constructor
	 * 
	 * The argument can be a full array of configuration values or a path to an INI
	 * configuration file.
	 */
	public function PHP_Extended_Markdown( $options=null )
	{
		// get default options
		if (!empty($options))
		{
			if (is_string($options))
			{
				$this->config_file = $options;
			} 
			elseif (is_array($options) && isset($options['config_file'])) 
			{
				$this->config_file = $options['config_file'];
				unset($options['config_file']);
			}
		}
		$this->init();
		
		// treat $options argument
		if (!empty($options) && is_array($options))
		{
			foreach($options as $_var=>$_val)
			{
				$this->extend( $_var, $_val );
			}
		}

		// parent constructor
		parent::PHP_Extended_Markdown_Grammar();
	}

	/**
	 * Initialize the object loading the INI configuration file if so
	 */
	protected function init()
	{
		if (!empty($this->config_file))
		{
			if (false===@file_exists($this->config_file))
			{
				$original_config_file = $this->config_file;
				$this->config_file = __DIR__.'/'.$this->config_file;
				if (false===@file_exists($this->config_file))
				{
					throw new Exception( sprintf("Config file for Extended Markdown instance doesn't exist (%s)!", $original_config_file) );
				}
			}
			$this->configure( parse_ini_file( $this->config_file, true ) );
		}
		$this->runGamut('initGamut');
	}

	/**
	 * Set a full set of options in the object
	 */	
	protected function configure( $options )
	{
		// sort all gamuts piles in ascendent priority order
		foreach($options as $_var=>$_val)
		{
			$this->extend( $_var, $_val, false );
			if (is_array($_val))
			{
				asort( $this->$_var );
			}
		}
	}
	
	/**
	 * Extend an option value, by merge if it is an array
	 */	
	public function extend( $name, $value, $recursive=true )
	{
		if (isset($this->$name) && is_array($this->$name))
		{
			if ($recursive)
			{
				$this->$name = array_merge_recursive(
					$this->$name, $value
				);
			}
			else
			{
				$this->$name = $value;
			}
		}
		elseif ('true'===$value)
		{
			$this->$name = true;
		}
		elseif ('false'===$value)
		{
			$this->$name = false;
		}
		else
		{
			$this->$name = $value;
		}
		return $this;
	}

// -----------------------------------
// SINGLETON INSTANCE (optional)
// -----------------------------------

	/**
	 * Object singleton instance
	 */
	protected static $emd_instance;

	public static function getInstance( $options=null )
	{
		if (empty(self::$emd_instance))
		{
			self::$emd_instance = new PHP_Extended_Markdown( $options );
		}
		return self::$emd_instance;
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
	public function doDebug( $a='', $what=null, $exit=true, $html=true ) 
	{
		if (true===$html) echo '<pre>';
		if (!is_null($what)) var_export($what);
		else var_export( $this );
		if (true===$html) echo '</pre>';
		if ($exit) exit(0);
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
	 * @see doDetab()
	 * @see hashHTMLBlocks()
	 * @see teardown()
	 * @see $document_gamut
	 */
	public function transform( $text_arg=null ) 
	{
		$this->setup();
		$this->in_stack=true;

		// text in argument ?
		if (!empty($text_arg))
			$this->setInputText( $text_arg );

		// text to work on
		$text = $this->text_input;
	
		// Run initTransform gamut methods.
		$text = $this->runGamut('initTransformGamut', $text);

		// Run document gamut methods.
		$text = $this->runGamut('documentGamut', $text);

		// setting parsed output
		$this->setOutputText( $text . "\n" );

		// remind current object in a stack
		$this->addStack( clone $this );
		// global clean
		$this->teardown();

		return $this->text_output;
	}
	
// -----------------------------------
// SETTERS/GETTERS
// -----------------------------------

	/**
	 * Get an object property value
	 * If we are not processing an object, the last stack is load and returned value
	 * will be from the last stack object
	 */	
	public function get( $what, $default=null )
	{
		if (!$this->in_stack) {
			$_this = $this->getStack();			
			return !is_null($_this) && property_exists($_this, $what) ? $_this->$what : $default;
		} else {
			return property_exists($this, $what) ? $this->$what : $default;
		}
	}

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

// ----------------------------------
// STACK REGISTRY
// ----------------------------------
	
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

	/**
	 * Clear all object stacks
	 * @return object $this for method chaining
	 */	
	public function clearStacks()
	{
		$this->stacks[] = array();
		return $this;
	}

// ----------------------------------
// PROCESS GAMUTS
// ----------------------------------
	
	/**
	 */
	public function runGamut( $type, $text=null ) 
	{
		$_text_argument = $text;
		$_type_argument = $type;
//		$gamut_pile = $this->getOption( $type );
		$gamut_pile = $this->$type;
		if (empty($gamut_pile))
		{
//			$gamut_pile = $this->getOption( $type.'Gamut' );
			$gamut_pile_name = $type.'Gamut';
			$gamut_pile = $this->$gamut_pile_name;
			if (empty($gamut_pile))
			{
				throw new Exception( sprintf("Unknown Gamut pile call [%s]!", $_type_argument) );
			}
		}
		foreach ($gamut_pile as $method => $priority) 
		{
			if (!is_null($_text_argument))
			{
				$text = $this->runOneGamut( $method, $text );
			}
			else
			{
				$this->runOneGamut( $method );
			}
		}
		return !is_null($_text_argument) ? $text : true;
	}
	
	public function runOneGamut( $gamut_name, $text=null ) 
	{
		$_text_argument = $text;
		$_name_argument = $gamut_name;
		if (false!==strpos($gamut_name, ':'))
		{
			list($_what, $_name) = explode(':', $gamut_name);
			if ('runGamut'==$_what)
			{
				return $this->runGamut( $_name, $text );
			}
		}

		if (!method_exists($this, $gamut_name))
		{
			throw new Exception( sprintf("Unknown Gamut method call [%s]!", $_name_argument) );
		}
		if (!is_null($_text_argument))
		{
			return $this->$gamut_name( $text );
		}
		else
		{
			return $this->$gamut_name();
		}
	}
	
}

// Endfile
