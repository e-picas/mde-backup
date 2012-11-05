<?php
/**
 */

##@emd@## CONFIG ##@emd@##

; Change to `true` to disallow entities.
no_entities = false;

initTransformGamut[doRemoveUtf8Marker]=5
initTransformGamut[doStandardizeLineEnding]=10
initTransformGamut[doAppendEndingNewLines]=15
initTransformGamut[stripSapcedLines]=30
spanGamut[encodeAmpsAndAngles]=40

##@emd@## !CONFIG ##@emd@##

##@emd@## GRAMMAR ##@emd@##

	/**
	 * Remove UTF-8 BOM and marker character in input, if present.
	 */
	public function doRemoveUtf8Marker($text) 
	{
		return preg_replace('{^\xEF\xBB\xBF|\x1A}', '', $text);
	}

	/**
	 * Standardize line endings: DOS to Unix and Mac to Unix
	 */
	public function doStandardizeLineEnding($text) 
	{
		return preg_replace('{\r\n?}', "\n", $text);
	}

	/**
	 * Make sure $text ends with a couple of newlines:
	 */
	public function doAppendEndingNewLines($text) 
	{
		return $text."\n\n";
	}

	/**
	 * Remove one level of line-leading tabs or spaces
	 *
	 * @param string $text The text to be parsed
	 * @return string The text parsed
	 */
	public function doOutdent($text) 
	{
		return preg_replace('/^(\t|[ ]{1,'.$this->getOption('tab_width').'})/m', '', $text);
	}

	/**
	 * Strip any lines consisting only of spaces and tabs.
	 * This makes subsequent regexen easier to write, because we can
	 * match consecutive blank lines with /\n+/ instead of something
	 * contorted like /[ ]*\n+/ .
	 */
	public function stripSapcedLines($text) 
	{
		return preg_replace('/^[ ]+$/m', '', $text);
	}

	/**
	 * Rebuild attributes string 'a="b"'.
	 *
	 * @param string $attributes The attributes to parse
	 * @return string The attributes processed
	 */
	public function doAttributes($attributes)
	{
		return preg_replace('{
			(\S+)=
			(["\']?)                  # $2: simple or double quote or nothing
			(?:
				([^"|\']\S+|.*?[^"|\']) # anything but quotes
			)
			\\2                       # rematch $2
			}xsi', ' $1="$3"', $attributes);
	}

	/**
	 * Encode text for a double-quoted HTML attribute. This function
	 * is *not* suitable for attributes enclosed in single quotes.
	 *
	 * @param string $text The attributes content
	 * @return string The attributes content processed
	 */
	public function encodeAttribute($text) 
	{
		$text = $this->encodeAmpsAndAngles($text);
		$text = str_replace('"', '&quot;', $text);
		return $text;
	}
	
	/**
	 * Smart processing for ampersands and angle brackets that need to 
	 * be encoded. Valid character entities are left alone unless the
	 * no-entities mode is set.
	 *
	 * @param string $text The text to encode
	 * @return string The encoded text
	 */
	public function encodeAmpsAndAngles($text) 
	{
		if (true===$this->getOption('no_entities')) {
			$text = str_replace('&', '&amp;', $text);
		} else {
			// Ampersand-encoding based entirely on Nat Irons's Amputator
			// MT plugin: <http://bumppo.net/projects/amputator/>
			$text = preg_replace('/&(?!#?[xX]?(?:[0-9a-fA-F]+|\w+);)/', '&amp;', $text);
		}
		// Encode remaining <'s
		$text = str_replace('<', '&lt;', $text);

		return $text;
	}

	public function header2Label($text) 
	{
  	// strip all Markdown characters
	  	$text = str_replace( 
  			array("'", '"', '?', '*', '`', '[', ']', '(', ')', '{', '}', '+', '-', '.', '!', "\n", "\r", "\t"), 
  			'', strtolower($text)
  		);
	  	// strip the rest for visual signification
  		$text = str_replace( array('#', ' ', '__', '/', '\\'), '_', $text );
		// strip non-ascii characters
		return preg_replace("/[^\x9\xA\xD\x20-\x7F]/", '', $text);
	}

##@emd@## !GRAMMAR ##@emd@##

// Endfile