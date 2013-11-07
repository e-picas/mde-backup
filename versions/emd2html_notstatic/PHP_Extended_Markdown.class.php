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
	 * Are-we processing an object ?
	 */
	protected $in_stack=false;

	/**
	 * The output format (default is HTML)
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
				$this->extendOption( $_var, $_val );
			}
		}

		// parent constructor
		parent::PHP_Extended_Markdown_Grammar();

		// build the formater
		$this->buildFormater();
	}

	protected function init()
	{
		if (!@file_exists($this->config_file))
		{
			$original_config_file = $this->config_file;
			$this->config_file = __DIR__.'/'.$this->config_file;
			if (!@file_exists($this->config_file))
			{
				throw new Exception( sprintf("Config file for Extended Markdown instance doesn't exist (%s)!", $original_config_file) );
			}
		}
		$this->options = parse_ini_file( $this->config_file, true );
		// sort all gamuts piles in ascendent priority order
		foreach($this->options as $_var=>$_val)
		{
			if (is_array($_val))
			{
				asort( $this->options[$_var] );
			}
		}
		$this->runGamut('initGamut');
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

// -----------------------------------
// OUTPUT FORMATERS
// -----------------------------------

	public function buildFormater()
	{
		return;
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
				return call_user_func_array(array($this, $_meth), $args);
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
		if (false===$this->in_stack) {
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
// OPTIONS REGISTRY
// ----------------------------------

	/**
	 * Set an option value
	 */	
	public function setOption( $name, $value )
	{
		$this->options[$name] = $value;
		return $this;
	}

	/**
	 * Extend an option value if array
	 */	
	public function extendOption( $name, $value )
	{
		if (isset($this->options[$name]) && is_array($this->options[$name]))
		{
			$this->options[$name] = array_merge_recursive(
				$this->options[$name], $value
			);
		}
		else
		{
			$this->options[$name] = $value;
		}
		return $this;
	}

	/**
	 * Get an option value or the whole options registry
	 */	
	public function getOption( $name=null, $default=null )
	{
		if (is_null($name))
		{
			return $this->options;
		}
		return isset($this->options[$name]) ? $this->options[$name] : $default;
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
		$gamut_pile = $this->getOption( $type );
		if (empty($gamut_pile))
		{
			$gamut_pile = $this->getOption( $type.'Gamut' );
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
