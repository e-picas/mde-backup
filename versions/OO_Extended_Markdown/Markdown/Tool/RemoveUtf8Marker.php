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

class Markdown_Tool_RemoveUtf8Marker extends Markdown_Tool
{
	
	/**
	 * Remove UTF-8 BOM and marker character in input, if present.
	 *
	 * @param string $text The text to parse
	 * @return string The text parsed
	 * @see span_gamut()
	 * @see unhash()
	 */
	public function run($text) 
	{
		return preg_replace('{^\xEF\xBB\xBF|\x1A}', '', $text);
	}
	
}

// Endfile