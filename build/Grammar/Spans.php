<?php
/**
 */

##@emd@## CONFIG ##@emd@##

; Change to `true` to disallow markup
no_markup = false

; Process character escapes, code spans, and inline HTML in one shot.
spanGamut[parseSpan]=-30

##@emd@## !CONFIG ##@emd@##

##@emd@## GRAMMAR ##@emd@##

	/**
	 * Take the string $str and parse it into tokens, hashing embeded HTML,
	 * escaped characters and handling code spans.
	 *
	 * @param string $str The text to be parsed
	 * @return string The text parsed
	 * @see handleSpanToken()
	 */
	public function parseSpan($str) 
	{
		$output = '';
		
		$span_re = '{
				(
					\\\\'.$this->getOption('escape_chars_re').'
				|
					(?<![`\\\\])
					`+						          # code span marker
			'.( $this->getOption('no_markup') ? '' : '
				|
					<!--    .*?     -->		  # comment
				|
					<\?.*?\?> | <%.*?%>		  # processing instruction
				|
					<[/!$]?[-a-zA-Z0-9:_]+	# regular tags
					(?>
						\s
						(?>[^"\'>]+|"[^"]*"|\'[^\']*\')*
					)?
					>
			').'
				)
				}xs';

		while (1) {

			// Each loop iteration seach for either the next tag, the next 
			// openning code span marker, or the next escaped character. 
			// Each token is then passed to handleSpanToken.
			$parts = preg_split($span_re, $str, 2, PREG_SPLIT_DELIM_CAPTURE);
			
			// Create token from text preceding tag.
			if ($parts[0] != '') {
				$output .= $parts[0];
			}
			
			// Check if we reach the end.
			if (isset($parts[1])) {
				$output .= $this->handleSpanToken($parts[1], $parts[2]);
				$str = $parts[2];
			}
			else {
				break;
			}
		}
		
		return $output;
	}
	
	/**
	 * Handle $token provided by parseSpan by determining its nature and 
	 * returning the corresponding value that should replace it.
	 *
	 * @param string $token The token string to use
	 * @param string $str The text to be parsed (by reference)
	 * @return string The text parsed
	 * @see hashPart()
	 */
	public function handleSpanToken($token, &$str) 
	{
		switch ($token{0}) {
			case '\\':
				return $this->hashPart('&#'. ord($token{1}). ';');
			case '`':
				// Search for end marker in remaining text.
				if (preg_match('/^(.*?[^`])'.preg_quote($token).'(?!`)(.*)$/sm', 
					$str, $matches))
				{
					$str = $matches[2];
					$codespan = $this->makeCodeSpan($matches[1]);
					return $this->hashPart($codespan);
				}
				return $token; // return as text since no ending marker found.
			default:
				return $this->hashPart($token);
		}
	}

##@emd@## !GRAMMAR ##@emd@##

// Endfile