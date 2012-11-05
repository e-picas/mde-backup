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
 * @subpackage 	PHP_Extended_Markdown_DevelopmentTools
 * @license   	BSD
 * @link      	https://github.com/PieroWbmstr/Extended_Markdown
 */

/**
 * Get the Extended Markdown Grammar PHP class
 */
if (!@class_exists('PHP_Extended_Markdown'))
	require_once __DIR__."/PHP_Extended_Markdown.class.php";

/**
 * PHP Extended Markdown Debugger Class
 */
class PHP_Extended_Markdown_Debugger extends PHP_Extended_Markdown
{

	/**
	 * Activate debug
	 */
	protected $debug;

// -----------------------------------
// CONSTRUCTOR & SINGLETON
// -----------------------------------

	public function PHP_Extended_Markdown_Debugger( $options=null, $debug=true )
	{
		// debugging ?
		$this->debug = $debug;
		// parent constructor
		parent::PHP_Extended_Markdown($options);
	}

	public static function getInstance( $options=null, $debug=true )
	{
		if (empty(self::$emd_instance))
		{
			self::$emd_instance = new PHP_Extended_Markdown_Debugger( $options, $debug );
		}
		return self::$emd_instance;
	}

// ----------------------------------
// SETTERS/GETTERS
// ----------------------------------
	
	/**
	 * Get an option value or the whole options registry
	 */	
	public function getOption( $name=null, $default=null )
	{
		$_result = parent::getOption($name, null);
		if (false!==$thisdebug && is_null($_result))
		{
			echo '<p><strong>WARNING : try to get an unknown option \'<em>'.$name.'</em>\'!</strong></p>';
		}
		return !is_null($_result) ? $_result : $default;
	}

// ----------------------------------
// PROCESS GAMUTS
// ----------------------------------
	
	/**
	 */
	public function runGamut( $type, $text=null ) 
	{
		if (false!==$this->debug)
		{
			echo '<br /><hr /><p><strong>Gamut Pile [<em>'.$type.'</em>]</strong></p>';
			if (!empty($text))
			{
				echo '<p>... on text [strlen '.strlen($text).'] :<pre>'.var_export($text,1).'</pre></p>';
			}
		}
		return parent::runGamut( $type, $text );
	}
	
	public function runOneGamut( $gamut_name, $text=null ) 
	{
		if (false!==$this->debug)
		{
//			echo '<br />';
			echo '<p>>> Running gamut [<strong><em>'.$gamut_name.'</em></strong>] ';
			if (!empty($text))
			{
				echo ' with INPUT text [strlen <strong>'.strlen($text).'</strong>]';
			}
		}
		$return = parent::runOneGamut( $gamut_name, $text );
		if (false!==$this->debug && !empty($text))
		{
			echo ' and OUTPUT text [strlen <strong>'.strlen($return).'</strong>]';
		}
		if ('hard'===$this->debug && !empty($text))
		{
			echo '<br />>> INPUT text [strlen '.strlen($text).'] :<pre>'.var_export($text,1).'</pre>';
			echo '<br />>> OUTPUT text [strlen '.strlen($return).'] :<pre>'.var_export($return,1).'</pre>';
		}
		if (false!==$this->debug)
		{
			echo '</p>';
		}
		return $return;
	}
	
}

// Endfile
