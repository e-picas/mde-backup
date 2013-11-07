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
 * @subpackage 	PHP_Extended_Markdown_OutputFormat
 */

/**
 */
class PHP_Extended_Markdown_OutputFormat_HTML
	implements PHP_Extended_Markdown_OutputFormat
{

	public function render()
	{
	}

	public function buildHeader( $text, $level=1, $attr=array() )
	{
		return "<h$level$attr>$text</h$level>";
	}


}

// Endfile