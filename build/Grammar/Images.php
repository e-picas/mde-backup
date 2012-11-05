<?php
/**
 */

##@emd@## CONFIG ##@emd@##

; Process anchor and image tags. Images must come first
; because ![foo][f] looks like an anchor.
spanGamut[doImages]=10

##@emd@## !CONFIG ##@emd@##

##@emd@## OUTPUTFORMAT_INTERFACE ##@emd@##

	/**
	 * @param array $attrs The image attributes
	 * @return string The image tag string
	 */
	public static function buildImage( $attrs=array() );

##@emd@## !OUTPUTFORMAT_INTERFACE ##@emd@##

##@emd@## GRAMMAR ##@emd@##

	/**
	 * Turn Markdown image shortcuts into <img> tags.
	 *
	 * @param string $text Text to parse
	 * @return string The text parsed
	 * @see _doImages_reference_callback()
	 * @see _doImages_inline_callback()
	 */
	public function doImages($text) 
	{
		// First, handle reference-style labeled images: ![alt text][id]
		$text = preg_replace_callback('{
			(				                            # wrap whole match in $1
			  !\[
				('.$this->getOption('nested_brackets_re').')		# alt text = $2
			  \]

			  [ ]?				                      # one optional space
			  (?:\n[ ]*)?		                    # one optional newline followed by spaces

			  \[
				(.*?)		                          # id = $3
			  \]

			)
			}xs', 
			array(&$this, '_doImages_reference_callback'), $text);

		// Next, handle inline images:  ![alt text](url "optional title")
		// Don't forget: encode * and _
		$text = preg_replace_callback('{
			(				                                  # wrap whole match in $1
			  !\[
				('.$this->getOption('nested_brackets_re').')		      # alt text = $2
			  \]
			  \s?			                                # One optional whitespace character
			  \(			                                # literal paren
				[ \n]*
				(?:
					<(\S*)>	# src url = $3
				|
					('.$this->getOption('nested_url_parenthesis_re').')	# src url = $4
				)
				[ \n]*
				(			                                  # $5
				  ([\'"])	                              # quote char = $6
				  (.*?)		                              # title = $7
				  \6		                                # matching quote
				  [ \n]*
				)?			                                # title is optional
			  \)
			)
			}xs',
			array(&$this, '_doImages_inline_callback'), $text);

		return $text;
	}

	/**
	 * @param array $matches A set of results of the `deImages` function
	 * @return string The text parsed
	 * @see encodeAttribute()
	 * @see hashPart()
	 */
	protected function _doImages_reference_callback($matches) 
	{
		$whole_match = $matches[1];
		$alt_text    = $matches[2];
		$link_id     = strtolower($matches[3]);

		if ($link_id == '') {
			$link_id = strtolower($alt_text); // for shortcut links like ![this][].
		}

		$alt_text = $this->encodeAttribute($alt_text);
		if (isset($this->urls[$link_id])) {
			$url = $this->encodeAttribute($this->urls[$link_id]);
//			$result = '<img src="'.$url.'" alt="'.$alt_text.'"';
			$attrs = array( 'src'=>$url, 'alt'=>$alt_text );

			if (isset($this->titles[$link_id])) {
				$title = $this->titles[$link_id];
				$title = $this->encodeAttribute($title);
//				$result .= ' title="'.$title.'"';
				$attrs['title'] = $title;
			}
			if (isset($this->attributes[$link_id])) {
//				$result .= $this->doAttributes( $this->attributes[$link_id] );
				$attrs[] = $this->doAttributes( $this->attributes[$link_id] );
			}
//			$result .= $this->getOption('empty_element_suffix');
//			$result = $this->hashPart($result);

			$result = $this->hashPart(
				$this->runFormaterMethod('buildImage', $attrs)
			);

		}
		else {
			// If there's no such link ID, leave intact:
			$result = $whole_match;
		}

		return $result;
	}

	/**
	 * @param array $matches A set of results of the `doImages` function
	 * @return string The text parsed
	 * @see encodeAttribute()
	 * @see hashPart()
	 */
	protected function _doImages_inline_callback($matches) 
	{
		$whole_match	= $matches[1];
		$alt_text		= $matches[2];
		$url			= $matches[3] == '' ? $matches[4] : $matches[3];
		$title			=& $matches[7];

		$alt_text = $this->encodeAttribute($alt_text);
		$url = $this->encodeAttribute($url);
//		$result = '<img src="'.$url.'" alt="'.$alt_text.'"';
		$attrs = array( 'src'=>$url, 'alt'=>$alt_text );

		if (isset($title)) {
			$title = $this->encodeAttribute($title);
//			$result .=  ' title="'.$title.'"'; # $title already quoted
			$attrs['title'] = $title;
		}
//		$result .= $this->getOption('empty_element_suffix');
//		return $this->hashPart($result);

		return $this->hashPart(
			$this->runFormaterMethod('buildImage', $attrs)
		);
	}

##@emd@## !GRAMMAR ##@emd@##

// Endfile