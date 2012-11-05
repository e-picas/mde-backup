<?php
/**
 */

##@emd@## CONFIG ##@emd@##

setupGamut[_setupAbbreviations]=1
teardownGamut[_teardownAbbreviations]=1
documentGamut[stripAbbreviations]=25
spanGamut[doAbbreviations]=70

##@emd@## !CONFIG ##@emd@##

##@emd@## OUTPUTFORMAT_INTERFACE ##@emd@##

	/**
	 * @param string $text The abbreviation text
	 * @param array $attrs The abbreviation attributes if so
	 * @return string The abbreviation tag string
	 */
	public static function buildAbbreviation( $text, $attrs=array() );

##@emd@## !OUTPUTFORMAT_INTERFACE ##@emd@##

##@emd@## GRAMMAR ##@emd@##

	/**
	 * Predefined abbreviations.
	 */
	var $predef_abbr = array();

	/**
	 * Extra variables used during extra transformations.
	 */
	protected $abbr_desciptions = array();
	protected $abbr_word_re = '';
	
	protected function _setupAbbreviations() 
	{
		$this->abbr_desciptions = array();
		$this->abbr_word_re = '';
		foreach ($this->predef_abbr as $abbr_word => $abbr_desc) {
			if ($this->abbr_word_re)
				$this->abbr_word_re .= '|';
			$this->abbr_word_re .= preg_quote($abbr_word);
			$this->abbr_desciptions[$abbr_word] = trim($abbr_desc);
		}
	}

	protected function _teardownAbbreviations() 
	{
		$this->abbr_desciptions = array();
		$this->abbr_word_re = '';
	}

	/**
	 * Find defined abbreviations in text and wrap them in <abbr> elements.
	 *
	 * @param string $text The text to parse
	 * @return string The text parsed
	 * @see _doAbbreviations_callback()
	 */
	public function doAbbreviations($text) 
	{
		if ($this->abbr_word_re) {
			// cannot use the /x modifier because abbr_word_re may 
			// contain significant spaces:
			$text = preg_replace_callback('{'.
				'(?<![\w\x1A])'.
				'(?:'.$this->abbr_word_re.')'.
				'(?![\w\x1A])'.
				'}', 
				array(&$this, '_doAbbreviations_callback'), $text);
		}
		return $text;
	}

	/**
	 * Process each abbreviation
	 *
	 * @param array $matches One set of results form the `doAbbreviations()` function
	 * @return string The abbreviation entry parsed
	 * @see hashPart()
	 * @see encodeAttribute()
	 */
	protected function _doAbbreviations_callback($matches) 
	{
		$abbr = $matches[0];
		if (isset($this->abbr_desciptions[$abbr])) {
			$desc = $this->abbr_desciptions[$abbr];
			if (!empty($desc)) {
				$desc = $this->encodeAttribute($desc);
//				return $this->hashPart('<abbr>'.$abbr.'</abbr>');
//			} else {
//				$desc = $this->encodeAttribute($desc);
//				return $this->hashPart('<abbr title="'.$desc.'">'.$abbr.'</abbr>');
			}
			return $this->hashPart(
				$this->runFormaterMethod('buildAbbreviation', $abbr, array('title'=>$desc))
			);

		} else {
			return $matches[0];
		}
	}

	/**
	 * Strips abbreviations from text, stores titles in hash references.
	 *
	 * Link defs are in the form: [id]*: url "optional title"
	 *
	 * @param string $text The text to parse
	 * @return string The text parsed
	 * @see _stripAbbreviations_callback()
	 */
	public function stripAbbreviations($text) 
	{
		$less_than_tab = $this->getOption('tab_width') - 1;
		return preg_replace_callback('{
				^[ ]{0,'.$less_than_tab.'}\*\[(.+?)\][ ]?:	# abbr_id = $1
				(.*)					# text = $2 (no blank lines allowed)	
			}xm',
			array(&$this, '_stripAbbreviations_callback'),
			$text);
	}

	/**
	 * Strips abbreviations from text, stores titles in hash references.
	 *
	 * @param array $matches Results from the `stripAbbreviations()` function
	 * @return string The text parsed
	 */
	protected function _stripAbbreviations_callback($matches) 
	{
		$abbr_word = $matches[1];
		$abbr_desc = $matches[2];
		if ($this->abbr_word_re)
			$this->abbr_word_re .= '|';
		$this->abbr_word_re .= preg_quote($abbr_word);
		$this->abbr_desciptions[$abbr_word] = trim($abbr_desc);
		return ''; // String that will replace the block
	}
	
##@emd@## !GRAMMAR ##@emd@##

// Endfile