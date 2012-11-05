<?php
/**
 */

##@emd@## CONFIG ##@emd@##

initGamut[_initDetab]=1
initTransformGamut[doDetab]=20

##@emd@## !CONFIG ##@emd@##

##@emd@## GRAMMAR ##@emd@##

	/**
	 * String length function for detab. `_initDetab` will create a function to 
	 * hanlde UTF-8 if the default function does not exist.
	 */
	var $utf8_strlen = 'mb_strlen';
	
	/**
	 * Replace tabs with the appropriate amount of space.
	 *
	 * For each line we separate the line in blocks delemited by
	 * tab characters. Then we reconstruct every line by adding the 
	 * appropriate number of space between each blocks.
	 *
	 * @param string $text The text to be parsed
	 * @return string The text parsed
	 * @see _detab_callback()
	 */
	public function doDetab($text) 
	{
		$text = preg_replace_callback('/^.*\t.*$/m', array(&$this, '_detab_callback'), $text);
		return $text;
	}

	/**
	 * Process tabs replacement
	 *
	 * @param array $matches A set of results of the `doDetab()` function
	 * @return string The line rebuilt
	 */
	protected function _detab_callback($matches) 
	{
		$line = $matches[0];
		$strlen = $this->utf8_strlen; // strlen function for UTF-8.
		
		// Split in blocks.
		$blocks = explode("\t", $line);
		// Add each blocks to the line.
		$line = $blocks[0];
		unset($blocks[0]); // Do not add first block twice.
		foreach ($blocks as $block) {
			// Calculate amount of space, insert spaces, insert block.
			$amount = $this->getOption('tab_width') - $strlen($line, 'UTF-8') % $this->getOption('tab_width');
			$line .= str_repeat(' ', $amount) . $block;
		}
		return $line;
	}

	/**
	 * Check for the availability of the function in the `utf8_strlen` property
	 * (initially `mb_strlen`). If the function is not available, create a 
	 * function that will loosely count the number of UTF-8 characters with a
	 * regular expression.
	 */
	protected function _initDetab() 
	{
		if (function_exists($this->utf8_strlen)) return;
		$this->utf8_strlen = create_function('$text', 'return preg_match_all(
			"/[\\\\x00-\\\\xBF]|[\\\\xC0-\\\\xFF][\\\\x80-\\\\xBF]*/", 
			$text, $m);');
	}

##@emd@## !GRAMMAR ##@emd@##

// Endfile