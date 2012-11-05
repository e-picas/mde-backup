<?php
/**
 */

##@emd@## CONFIG ##@emd@##

blockGamut[doBlockQuotes]=60

##@emd@## !CONFIG ##@emd@##

##@emd@## OUTPUTFORMAT_INTERFACE ##@emd@##

	/**
	 * @param string $text The blockquote text content
	 * @param array $attrs The blockquote attributes if so
	 * @return string The blockquote tag string
	 */
	public static function buildBlockquote( $text, $attrs=array() );

##@emd@## !OUTPUTFORMAT_INTERFACE ##@emd@##

##@emd@## GRAMMAR ##@emd@##

	/**
	 * Create blockquotes blocks
	 *
	 * @param string $text Text to parse
	 * @return string The text parsed
	 * @see _doBlockQuotes_callback()
	 */
	public function doBlockQuotes($text) 
	{
		return preg_replace_callback('/
			  (								# Wrap whole match in $1
				(?>
				  ^[ ]*>[ ]?		# ">" at the start of a line
					(?:\((.+?)\))?
					.+\n					# rest of the first line
				  (.+\n)*				# subsequent consecutive lines
				  \n*						# blanks
				)+
			  )
			/xm',
			array(&$this, '_doBlockQuotes_callback'), $text);
	}

	/**
	 * Build each blockquote block
	 *
	 * @param array $matches A set of results of the `doBlockQuotes()` function
	 * @return string The text parsed
	 * @see runBlockGamut()
	 * @see _doBlockQuotes_callback2()
	 */
	protected function _doBlockQuotes_callback($matches) 
	{
		$bq = $matches[1];
		$cite = $matches[2];
		// trim one level of quoting - trim whitespace-only lines
		$bq = preg_replace('/^[ ]*>[ ]?(\((.+?)\))?|^[ ]+$/m', '', $bq);
		$bq = $this->runGamut( 'blockGamut', $bq);		# recurse

		$bq = preg_replace('/^/m', "  ", $bq);
		// These leading spaces cause problem with <pre> content, 
		// so we need to fix that:
		$bq = preg_replace_callback('{(\s*<pre>.+?</pre>)}sx', array(&$this, '_doBlockQuotes_callback2'), $bq);

//		return "\n". $this->hashBlock('<blockquote'
//			.( !empty($cite) ? ' cite="'.$cite.'"' : '' )
//			.'>'."\n".$bq."\n".'</blockquote>')."\n\n";

		$attrs = array();
		if (!empty($cite))
		{
			$attrs['cite'] = $cite;
		}
		return "\n". $this->hashBlock(
			$this->runFormaterMethod('buildBlockquote', "\n".$bq."\n", $attrs)
		)."\n\n";
	}

	/**
	 * Deletes the last sapces, for <pre> blocks
	 *
	 * @param array $matches A set of results of the `_doBlockQuotes_callback()` function
	 * @return string The text parsed
	 */
	protected function _doBlockQuotes_callback2($matches) 
	{
		$pre = $matches[1];
		$pre = preg_replace('/^  /m', '', $pre);
		return $pre;
	}

##@emd@## !GRAMMAR ##@emd@##

// Endfile