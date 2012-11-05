<?php
/**
 */

##@emd@## CONFIG ##@emd@##

setupGamut[_clearHashes]=1
teardownGamut[_clearHashes]=1

##@emd@## !CONFIG ##@emd@##

##@emd@## GRAMMAR ##@emd@##

	/**
	 * Internal hashes used during transformation.
	 */
	protected $html_hashes = array();

	protected function _clearHashes() 
	{
		$this->html_hashes = array();
	}

	/**
	 * Called whenever a tag must be hashed when a function insert an atomic 
	 * element in the text stream. Passing $text to through this function gives
	 * a unique text-token which will be reverted back when calling unhash.
	 *
	 * The $boundary argument specify what character should be used to surround
	 * the token. By convension, "B" is used for block elements that needs not
	 * to be wrapped into paragraph tags at the end, ":" is used for elements
	 * that are word separators and "X" is used in the general case.
	 *
	 * @param string $text The text to be parsed
	 * @param string $boundary A one letter boundary
	 * @return string The text parsed
	 * @see unhash()
	 */
	public function hashPart($text, $boundary = 'X') 
	{
		// Swap back any tag hash found in $text so we do not have to `unhash`
		// multiple times at the end.
		$text = $this->unhash($text);
		// Then hash the block.
		static $i = 0;
		$key = $boundary."\x1A" . ++$i . $boundary;
		$this->html_hashes[$key] = $text;
		return $key; // String that will replace the tag.
	}

	/**
	 * Shortcut function for hashPart with block-level boundaries.
	 *
	 * @param string $text The text to be parsed
	 * @return function Pass results of the `hashPart()` function
	 * @see hashPart()
	 */
	public function hashBlock($text) 
	{
		return $this->hashPart($text, 'B');
	}

	/**
	 * Swap back in all the tags hashed by _HashHTMLBlocks.
	 *
	 * @param string $text The text to be parsed
	 * @return function Pass results of the `_unhash_callback()` function
	 * @see _unhash_callback()
	 */
	public function unhash($text) 
	{
		return preg_replace_callback('/(.)\x1A[0-9]+\1/', array(&$this, '_unhash_callback'), $text);
	}

	/**
	 * @param array $matches A set of results of the `unhash()` function
	 * @return empty
	 */
	protected function _unhash_callback($matches) 
	{
		return $this->html_hashes[$matches[0]];
	}

	/**
	 * Called whenever a tag must be hashed when a function insert a "clean" tag
	 * in $text, it pass through this function and is automaticaly escaped, 
	 * blocking invalid nested overlap.
	 *
	 * @param string $text Text to parse
	 * @return string Text parsed
	 * @see hashPart()
	 */
	public function hashClean($text) 
	{
		return $this->hashPart($text, 'C');
	}

##@emd@## !GRAMMAR ##@emd@##

// Endfile