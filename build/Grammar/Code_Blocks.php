<?php
/**
 */

##@emd@## CONFIG ##@emd@##

documentGamut[doFencedCodeBlocks]=5
blockGamut[doFencedCodeBlocks]=5
blockGamut[doCodeBlocks]=50

##@emd@## !CONFIG ##@emd@##

##@emd@## OUTPUTFORMAT_INTERFACE ##@emd@##

	/**
	 * @param string $text The code block text content
	 * @param array $attrs The code block attributes if so
	 * @return string The code block tag string
	 */
	public static function buildCodeBlock( $text, $attrs=array() );

	/**
	 * @param string $text The code span text content
	 * @param array $attrs The code span attributes if so
	 * @return string The code span tag string
	 */
	public static function buildCodeSpan( $text, $attrs=array() );

##@emd@## !OUTPUTFORMAT_INTERFACE ##@emd@##

##@emd@## GRAMMAR ##@emd@##

	/**
	 *	Process Markdown `<pre><code>` blocks.
	 *
	 * @param string $text Text to parse
	 * @return string The text parsed
	 * @see _doCodeBlocks_callback()
	 */
	public function doCodeBlocks($text) 
	{
		return preg_replace_callback('{
				(?:\n\n|\A\n?)
				(	                                      # $1 = the code block -- one or more lines, starting with a space/tab
				  (?>
					[ ]{'.$this->getOption('tab_width').'}             # Lines must start with a tab or a tab-width of spaces
					.*\n+
				  )+
				)
				((?=^[ ]{0,'.$this->getOption('tab_width').'}\S)|\Z)	# Lookahead for non-space at line-start, or end of doc
			}xm',
			array(&$this, '_doCodeBlocks_callback'), $text);
	}

	/**
	 * Build `<pre><code>` blocks.
	 *
	 * @param array $matches A set of results of the `doCodeBlocks()` function
	 * @return string Text parsed
	 * @see hashBlock()
	 */
	protected function _doCodeBlocks_callback($matches) 
	{
		$codeblock = $matches[1];

		$codeblock = $this->doOutdent($codeblock);
		$codeblock = htmlspecialchars($codeblock, ENT_NOQUOTES);

		# trim leading newlines and trailing newlines
		$codeblock = preg_replace('/\A\n+|\n+\z/', '', $codeblock);

//		$codeblock = '<pre><code>'.$codeblock."\n".'</code></pre>';
//		return "\n\n".$this->hashBlock($codeblock)."\n\n";

		return "\n\n".$this->hashBlock(
			$this->runFormaterMethod('buildCodeBlock', $codeblock."\n")
		)."\n\n";
	}

	/**
	 * Create a code span markup for $code. Called from handleSpanToken.
	 *
	 * @param string $text Text to parse
	 * @return string The text parsed
	 * @see hashPart()
	 */
	public function makeCodeSpan($code) 
	{
		$code = htmlspecialchars(trim($code), ENT_NOQUOTES);
//		return $this->hashPart('<code>'.$code.'</code>');
		return $this->hashPart(
			$this->runFormaterMethod('buildCodeSpan', $code)
		);
	}

// ----------------------------------
// FENCED CODE BLOCK
// ----------------------------------
	
	/**
	 * Adding the fenced code block syntax to regular Markdown:
	 *
	 *     ~~~
	 *     Code block
	 *     ~~~
	 *
	 * @param string $text The text to parse
	 * @return string The text parsed
	 * @see _doFencedCodeBlocks_callback()
	 */
	public function doFencedCodeBlocks($text) 
	{
		return preg_replace_callback('{
				(?:\n|\A)           # 1: Opening marker
				(
					~{3,}             # Marker: three tilde or more.
				)
				(\w+)?              # 2: Language
				[ ]* \n             # Whitespace and newline following marker.
				(                   # 3: Content
					(?>
						(?!\1 [ ]* \n)	# Not a closing marker.
						.*\n+
					)+
				)
				\1 [ ]* \n          # Closing marker
			}xm',
			array(&$this, '_doFencedCodeBlocks_callback'), $text);
	}

	/**
	 * Process the fenced code blocks
	 *
	 * @param array $matches Results form the `doFencedCodeBlocks()` function
	 * @return string The text parsed
	 * @see _doFencedCodeBlocks_newlines()
	 * @see hashBlock()
	 */
	protected function _doFencedCodeBlocks_callback($matches) 
	{
		$codeblock = $matches[3];
		$language  = $matches[2];
		$codeblock = htmlspecialchars($codeblock, ENT_NOQUOTES);
		$codeblock = preg_replace_callback('/^\n+/', array(&$this, '_doFencedCodeBlocks_newlines'), $codeblock);
/*
		$codeblock = '<pre><code'
			.( !empty($language) ? ' class="language-'.$language.'"' : '' )
			.'>'.$codeblock.'</code></pre>';
		return "\n\n".$this->hashBlock($codeblock)."\n\n";
*/
		$attrs = array();
		if (!empty($language))
		{
			$attrs['class'] = 'language-'.$language;
		}
		return "\n\n".$this->hashBlock(
			$this->runFormaterMethod('buildCodeBlock', $codeblock, $attrs)
		)."\n\n";
	}

	/**
	 * Process the fenced code blocks new lines
	 *
	 * @param array $matches Results form the `doFencedCodeBlocks()` function (passed from the `_doFencedCodeBlocks_callback()` function)
	 * @return string The block parsed
	 */
	protected function _doFencedCodeBlocks_newlines($matches) 
	{
		return str_repeat( '<br'.$this->getOption('empty_element_suffix'), strlen($matches[0]) );
	}

##@emd@## !GRAMMAR ##@emd@##

// Endfile