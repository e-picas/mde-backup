<?php
/**
 */

##@emd@## CONFIG ##@emd@##

; Make links out of things like `<http://example.com/>`
; Must come after doAnchors, because you can use < and >
; delimiters in inline links like [this](<url>).
spanGamut[doAutoLinks]=30

##@emd@## !CONFIG ##@emd@##

##@emd@## OUTPUTFORMAT_INTERFACE ##@emd@##

	/**
	 * @param string $text The link text content
	 * @param array $attrs The link attributes if so
	 * @return string The link tag string
	 */
	public static function buildLink( $text, $attrs=array() );

	/**
	 * @param string $text The mailto link address
	 * @param array $attrs The mailto link attributes if so
	 * @return string The mailto link tag string (transformed if so)
	 */
	public static function buildMailto( $address, $attrs=array() );

##@emd@## !OUTPUTFORMAT_INTERFACE ##@emd@##

##@emd@## GRAMMAR ##@emd@##

	/**
	 * @param string $text Text to parse
	 * @return string The text parsed
	 * @see _doAutoLinks_url_callback()
	 * @see _doAutoLinks_email_callback()
	 */
	public function doAutoLinks($text) 
	{
		$text = preg_replace_callback('{<((https?|ftp|dict):[^\'">\s]+)>}i', 
			array(&$this, '_doAutoLinks_url_callback'), $text);

		// Email addresses: <address@domain.foo>
		return preg_replace_callback('{
			<
			(?:mailto:)?
			(
				(?:
					[-!#$%&\'*+/=?^_`.{|}~\w\x80-\xFF]+
				|
					".*?"
				)
				\@
				(?:
					[-a-z0-9\x80-\xFF]+(\.[-a-z0-9\x80-\xFF]+)*\.[a-z]+
				|
					\[[\d.a-fA-F:]+\]	# IPv4 & IPv6
				)
			)
			>
			}xi',
			array(&$this, '_doAutoLinks_email_callback'), $text);
	}

	/**
	 * @param array $matches A set of results of the `doAutoLinks` function
	 * @return string The text parsed
	 * @see encodeAttribute()
	 * @see hashPart()
	 */
	protected function _doAutoLinks_url_callback($matches) 
	{
		$url = $this->encodeAttribute($matches[1]);
//		$link = '<a href="'.$url.'">'.$url.'</a>';
//		return $this->hashPart($link);

		return $this->hashPart(
			$this->runFormaterMethod('buildLink', $url, array('href'=>$url))
		);
	}

	/**
	 * @param array $matches A set of results of the `doAutoLinks` function
	 * @return string The text parsed
	 * @see encodeEmailAddress()
	 * @see hashPart()
	 */
	protected function _doAutoLinks_email_callback($matches) 
	{
		$address = $matches[1];
//		$link = $this->encodeEmailAddress($address);
//		return $this->hashPart($link);
		return $this->hashPart(
			$this->runFormaterMethod('buildMailto', $address)
		);
	}

##@emd@## !GRAMMAR ##@emd@##

// Endfile