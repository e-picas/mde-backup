<?php
/**
 */

##@emd@## CONFIG ##@emd@##

setupGamut[_setupAnchors]=1
spanGamut[doAnchors]=20

##@emd@## !CONFIG ##@emd@##

##@emd@## OUTPUTFORMAT_INTERFACE ##@emd@##

	/**
	 * @param string $text The anchor text content
	 * @param array $attrs The anchor attributes if so
	 * @return string The anchor tag string
	 */
	public static function buildAnchor( $text, $attrs=array() );

##@emd@## !OUTPUTFORMAT_INTERFACE ##@emd@##

##@emd@## GRAMMAR ##@emd@##

	/**
	 * Status flag to avoid invalid nesting.
	 */
	protected $in_anchor = false;
	
	protected function _setupAnchors() 
	{
		$this->in_anchor = false;
	}

	/**
	 * Turn Markdown link shortcuts into XHTML <a> tags.
	 *
	 * @param string $text Text to parse
	 * @return string The text parsed
	 * @see _doAnchors_reference_callback()
	 * @see _doAnchors_inline_callback()
	 * @see _doAnchors_reference_callback()
	 */
	public function doAnchors($text) 
	{
		if ($this->in_anchor) return $text;
		$this->in_anchor = true;
		
		// First, handle reference-style links: [link text] [id]
		$text = preg_replace_callback('{
			(					                        # wrap whole match in $1
			  \[
				('.$this->getOption('nested_brackets_re').')	# link text = $2
			  \]

			  [ ]?				                    # one optional space
			  (?:\n[ ]*)?		                  # one optional newline followed by spaces

			  \[
				(.*?)		                        # id = $3
			  \]
			)
			}xs',
			array(&$this, '_doAnchors_reference_callback'), $text);

		// Next, inline-style links: [link text](url "optional title")
		$text = preg_replace_callback('{
			(				                                    # wrap whole match in $1
			  \[
				('.$this->getOption('nested_brackets_re').')	          # link text = $2
			  \]
			  \(			                                  # literal paren
				[ \n]*
				(?:
					<(.+?)>	                                # href = $3
				|
					('.$this->getOption('nested_url_parenthesis_re').')	# href = $4
				)
				[ \n]*
				(			                                    # $5
				  ([\'"])	                                # quote char = $6
				  (.*?)		                                # Title = $7
				  \6		                                  # matching quote
				  [ \n]*	                                # ignore any spaces/tabs between closing quote and )
				)?			                                  # title is optional
			  \)
			)
			}xs',
			array(&$this, '_doAnchors_inline_callback'), $text);

		// Last, handle reference-style shortcuts: [link text]
		// These must come last in case you've also got [link text][1]
		// or [link text](/foo)
		$text = preg_replace_callback('{
			(					      # wrap whole match in $1
			  \[
				([^\[\]]+)		# link text = $2; can\'t contain [ or ]
			  \]
			)
			}xs',
			array(&$this, '_doAnchors_reference_callback'), $text);

		$this->in_anchor = false;
		return $text;
	}

	/**
	 * @param array $matches A set of results of the `doAnchors` function
	 * @return string The text parsed
	 * @see encodeAttribute()
	 * @see runSpanGamut()
	 * @see hashPart()
	 */
	protected function _doAnchors_reference_callback($matches) 
	{
		$whole_match =  $matches[1];
		$link_text   =  $matches[2];
		$link_id     =& $matches[3];

		if ($link_id == '') {
			// for shortcut links like [this][] or [this].
			$link_id = $link_text;
		}
		
		// lower-case and turn embedded newlines into spaces
		$link_id = strtolower($link_id);
		$link_id = preg_replace('{[ ]?\n}', ' ', $link_id);

		if (isset($this->urls[$link_id])) {
			$url = $this->urls[$link_id];
			$url = $this->encodeAttribute($url);

			$attrs=array( 'href'=>$url );			
//			$result = '<a href="'.$url.'"';
			if ( isset( $this->titles[$link_id] ) ) {
				$title = $this->titles[$link_id];
				$title = $this->encodeAttribute($title);
//				$result .=  ' title="'.$title.'"';
				$attrs['title'] = $title;
			}
			if (isset($this->attributes[$link_id])) {
//				$result .= $this->doAttributes( $this->attributes[$link_id] );
				$attrs[] = $this->doAttributes( $this->attributes[$link_id] );
			}
		
			$link_text = $this->runGamut( 'spanGamut', $link_text );
//			$result .= '>'.$link_text.'</a>';
//			$result = $this->hashPart($result);
			$result = $this->hashPart(
				$this->runFormaterMethod('buildAnchor', $link_text, $attrs)
			);
		}
		else {
			$result = $whole_match;
		}
		return $result;
	}

	/**
	 * @param array $matches A set of results of the `doAnchors` function
	 * @return string The text parsed
	 * @see encodeAttribute()
	 * @see runSpanGamut()
	 * @see hashPart()
	 */
	protected function _doAnchors_inline_callback($matches) 
	{
		$whole_match	=  $matches[1];
		$link_text		=  $this->runGamut( 'spanGamut', $matches[2]);
		$url			=  $matches[3] == '' ? $matches[4] : $matches[3];
		$title			=& $matches[7];

		$url = $this->encodeAttribute($url);

			$attrs=array( 'href'=>$url );			
//		$result = '<a href="'.$url.'"';
		if (isset($title)) {
			$title = $this->encodeAttribute($title);
//			$result .=  ' title="'.$title.'"';
			$attrs['title'] = $title;
		}
		$link_text = $this->runGamut( 'spanGamut', $link_text);
//		$result .= '>'.$link_text.'</a>';

//		return $this->hashPart($result);
//			$result = $this->hashPart($result);
		return $this->hashPart(
			$this->runFormaterMethod('buildAnchor', $link_text, $attrs)
		);
	}

##@emd@## !GRAMMAR ##@emd@##

// Endfile