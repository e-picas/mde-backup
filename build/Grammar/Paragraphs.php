<?php
/**
 */

##@emd@## OUTPUTFORMAT_INTERFACE ##@emd@##

	/**
	 * @param string $text The paragraph text content
	 * @param array $attrs The paragraph attributes if so
	 * @return string The paragraph tag string
	 */
	public static function buildParagraph( $text, $attrs=array() );

##@emd@## !OUTPUTFORMAT_INTERFACE ##@emd@##

##@emd@## GRAMMAR ##@emd@##

	/**
	 * Process paragraphs
	 *
	 * @param string $text The text to parse
	 * @return string The text parsed
	 * @see runSpanGamut()
	 * @see unhash()
	 */
	public function formParagraphs($text) 
	{
		// Strip leading and trailing lines:
		$text = preg_replace('/\A\n+|\n+\z/', '', $text);
		
		$grafs = preg_split('/\n{2,}/', $text, -1, PREG_SPLIT_NO_EMPTY);

		// Wrap <p> tags and unhashify HTML blocks
		foreach ($grafs as $key => $value) {
			$value = trim($this->runGamut( 'spanGamut', $value));
			
			// Check if this should be enclosed in a paragraph.
			// Clean tag hashes & block tag hashes are left alone.
			$is_p = !preg_match('/^B\x1A[0-9]+B|^C\x1A[0-9]+C$/', $value);
			
			if ($is_p) {
//				$value = '<p>'.$value.'</p>';
				$value = $this->runFormaterMethod('buildParagraph', $value);

			}
			$grafs[$key] = $value;
		}
		
		// Join grafs in one text, then unhash HTML tags. 
		$text = implode("\n\n", $grafs);
		
		// Finish by removing any tag hashes still present in $text.
		$text = $this->unhash($text);
		
		return $text;
	}
	
##@emd@## !GRAMMAR ##@emd@##

##@emd@## CONFIG ##@emd@##

blockGamut[formParagraphs]=100

##@emd@## !CONFIG ##@emd@##

// Endfile