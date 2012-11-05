<?php
/**
 *
 * This file was automatically generated with PHP_Extended_Markdown_Builder class on {%DATE%}
 *
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
 * PHP Extended Markdown Grammar Class
 */
class PHP_Extended_Markdown_Grammar
{

	/**
	 * Predefined urls and titles for reference links and images.
	 */
	var $predef_urls = array();
	var $predef_titles = array();
	var $predef_attributes = array();

	/**
	 * Internal hashes used during transformation.
	 */
	protected $urls = array();
	protected $titles = array();
	protected $attributes = array();
	protected $ids = array();
	
// ----------------------------------
// CONSTRUCTOR
// ----------------------------------
	
	/**
	 * Constructor function. Initialize the parser object.
	 */
	public function PHP_Extended_Markdown_Grammar() 
	{
		$this->setOption(
			'nested_brackets_re', 
			str_repeat('(?>[^\[\]]+|\[', $this->getOption('nested_brackets_depth')).
			str_repeat('\])*', $this->getOption('nested_brackets_depth'))
		);
		$this->setOption(
			'nested_url_parenthesis_re', 
			str_repeat('(?>[^()\s]+|\(', $this->getOption('nested_url_parenthesis_depth')).
			str_repeat('(?>\)))*', $this->getOption('nested_url_parenthesis_depth'))
		);
		$this->setOption(
			'escape_chars_re', 
			'['.preg_quote($this->getOption('escape_chars')).']'
		);
	}
	
	/**
	 * Setting up Extra-specific variables and run setupGamuts
	 */
	protected function setup() 
	{
		$this->urls = $this->predef_urls;
		$this->titles = $this->predef_titles;
		$this->attributes = $this->predef_attributes;
		$this->runGamut('setupGamut');
	}
	
	/**
	 * Clearing Extra-specific variables and run teardownGamuts
	 */
	protected function teardown() 
	{
		$this->in_stack=false;
		$this->urls = array();
		$this->titles = array();
		$this->attributes = array();
		$this->runGamut('teardownGamut');
	}
	
{%GRAMMAR%}

}

// Endfile
