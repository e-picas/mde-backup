<?php
/**
 */

##@emd@## CONFIG ##@emd@##

spanGamut[doHardBreaks]=60

##@emd@## !CONFIG ##@emd@##

##@emd@## GRAMMAR ##@emd@##

	/**
	 * @param string $text The text to be parsed
	 * @return string The text parsed
	 * @see _doHardBreaks_callback()
	 */
	public function doHardBreaks($text) 
	{
		// Do hard breaks:
		return preg_replace_callback('/ {2,}\n/', array(&$this, '_doHardBreaks_callback'), $text);
	}

	/**
	 * @param array $matches A set of results of the `doHardBreak()` function
	 * @return string The text parsed
	 * @see hashPart()
	 */
	protected function _doHardBreaks_callback($matches) 
	{
		return $this->hashPart('<br'.$this->getOption('empty_element_suffix')."\n");
	}

##@emd@## !GRAMMAR ##@emd@##

// Endfile