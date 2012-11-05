<?php
/**
 */

##@emd@## CONFIG ##@emd@##

blockGamut[doHeaders]=10

##@emd@## !CONFIG ##@emd@##

##@emd@## OUTPUTFORMAT_INTERFACE ##@emd@##

	/**
	 * @param string $text The header content (title)
	 * @param int $level The header level
	 * @param string $attrs The attributes array of the built header tag
	 * @return string The header tag string
	 */
	public static function buildHeader( $text, $level=1, $attrs=array() );

##@emd@## !OUTPUTFORMAT_INTERFACE ##@emd@##

##@emd@## GRAMMAR ##@emd@##
	
	/**
	 * Redefined to add id attribute support.
	 *
	 * Setext-style headers:
	 *	  Header 1  {#header1}
	 *	  ========
	 *  
	 *	  Header 2  {#header2}
	 *	  --------
	 *
	 * ATX-style headers:
	 *	# Header 1        {#header1}
	 *	## Header 2       {#header2}
	 *	## Header 2 with closing hashes ##  {#header3}
	 *	...
	 *	###### Header 6   {#header2}
	 *
	 * @param string $text Text to parse
	 * @return string Text with all headers parsed
	 * @see _doHeaders_callback_setext()
	 * @see _doHeaders_callback_atx()
	 */
	public function doHeaders($text) 
	{
		// Setext-style headers:
		$text = preg_replace_callback(
			'{
				(^.+?)								            # $1: Header text
				(?:[ ]+\{\#([-_:a-zA-Z0-9]+)\})?	# $2: Id attribute
				[ ]*\n(=+|-+)[ ]*\n+				      # $3: Header footer
			}mx',
			array(&$this, '_doHeaders_callback_setext'), $text);

		// atx-style headers:
		$text = preg_replace_callback('{
				^(\#{1,6})	                     # $1 = string of #\'s
				[ ]*
				(.+?)		                         # $2 = Header text
				[ ]*
				\#*			                         # optional closing #\'s (not counted)
				(?:[ ]+\{\#([-_:a-zA-Z0-9]+)\})? # id attribute
				[ ]*
				\n+
			}xm',
			array(&$this, '_doHeaders_callback_atx'), $text);

		return $text;
	}

	/**
	 * Process setext-style headers:
	 *	  Header 1  {#header1}
	 *	  ========
	 *  
	 *	  Header 2  {#header2}
	 *	  --------
	 *
	 * @param array $matches The results from the `doHeaders()` function
	 * @return string Text with header parsed
	 * @see _doHeaders_attr()
	 * @see runSpanGamut()
	 * @see hashBlock()
	 */
	protected function _doHeaders_callback_setext($matches) 
	{
		if ($matches[3] == '-' && preg_match('{^- }', $matches[1]))
			return $matches[0];
		$level = $matches[3]{0} == '=' ? 1 : 2;
		$attr  = $this->_doHeaders_attr($id =& $matches[2]);
//		$block = "<h$level$attr>".$this->runSpanGamut($matches[1])."</h$level>";

		$block = $this->runFormaterMethod('buildHeader', 
			$this->runGamut( 'spanGamut', $matches[1] ), $level, $attr);

		return "\n" . $this->hashBlock($block) . "\n\n";
	}

	/**
	 * Process ATX-style headers:
	 *	# Header 1        {#header1}
	 *	## Header 2       {#header2}
	 *	## Header 2 with closing hashes ##  {#header3}
	 *	...
	 *	###### Header 6   {#header2}
	 *
	 * @param array $matches The results from the `doHeaders()` function
	 * @return string Text with header parsed
	 * @see _doHeaders_attr()
	 * @see runSpanGamut()
	 * @see hashBlock()
	 */
	protected function _doHeaders_callback_atx($matches) 
	{
		$level = strlen($matches[1]);
		if (!empty($matches[3]))
//			$attr  = $this->_doHeaders_attr($id =& $matches[3]);
			$id  = $this->_doHeaders_attr($matches[3]);
		else
//			$attr  = $this->_doHeaders_attr($id =& $this->header2Label($matches[2]));
			$id  = $this->_doHeaders_attr($this->header2Label($matches[2]));
//		$block = "<h$level$attr>".$this->runSpanGamut($matches[2])."</h$level>";

		$attr = array( 'id'=>$id );
		$block = $this->runFormaterMethod('buildHeader', 
			$this->runGamut( 'spanGamut', $matches[2]), $level, $attr);

		return "\n" . $this->hashBlock($block) . "\n\n";
	}

	/**
	 * The header builder
	 * @formater_overload
	protected function buildHeader( $text, $level=1, $attr='' )
	{
		return '<h'.$level.$attr.'>'.$text.'</h'.$level.'>';
	}
	 */

	/**
	 * Adding headers attributes if so 
	 *
	 * @param str $attr The attributes string
	 * @return string Text to add in the header tag
	 */
	protected function _doHeaders_attr($attr) 
	{
		if (empty($attr)) return '';
		$id = $attr;
		if (in_array($id, $this->ids)) {
			$i=0;
			while (in_array($id, $this->ids)) {
				$i++;
				$id = (string)$attr.$i;
			}
		}
		$this->ids[] = $id;
//		return ' id="'.$id.'"';
//		return array( 'id'=>$id );
		return $id;
	}

##@emd@## !GRAMMAR ##@emd@##

// Endfile