<?php
/**
 */

##@emd@## CONFIG ##@emd@##

blockGamut[doHorizontalRules]=20

##@emd@## !CONFIG ##@emd@##

##@emd@## GRAMMAR ##@emd@##

	/**
	 * @param string $text The text to be parsed
	 * @return string The text parsed
	 * @see hashBlock()
	 */
	public function doHorizontalRules($text) 
	{
		// Do Horizontal Rules:
		return preg_replace(
			'{
				^[ ]{0,3}	  # Leading space
				([-*_])		  # $1: First marker
				(?>			    # Repeated marker group
					[ ]{0,2}	# Zero, one, or two spaces.
					\1			  # Marker character
				){2,}		    # Group repeated at least twice
				[ ]*		    # Tailing spaces
				$			      # End of line.
			}mx',
			"\n".$this->hashBlock('<hr'.$this->getOption('empty_element_suffix'))."\n", 
			$text);
	}

##@emd@## !GRAMMAR ##@emd@##

// Endfile