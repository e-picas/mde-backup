<?php
/**
 */

##@emd@## CONFIG ##@emd@##

initGamut[prepareEmphasis]=5
spanGamut[doEmphasis]=50

##@emd@## !CONFIG ##@emd@##

##@emd@## OUTPUTFORMAT_INTERFACE ##@emd@##

	/**
	 * @param string $type The emphasis type : 'em', 'strong' or 'both'
	 * @param string $text The emphasis text
	 * @param array $attrs The emphasis attributes if so
	 * @return string The emphasis tag string
	 */
	public static function buildEmphasis( $type, $text, $attrs=array() );

##@emd@## !OUTPUTFORMAT_INTERFACE ##@emd@##

##@emd@## GRAMMAR ##@emd@##

	/**#@+
	 * Redefining emphasis markers so that emphasis by underscore does not
	 * work in the middle of a word.
	 */
	protected $em_relist = array(
		''  => '(?:(?<!\*)\*(?!\*)|(?<![a-zA-Z0-9_])_(?!_))(?=\S|$)(?![\.,:;]\s)',
		'*' => '(?<=\S|^)(?<!\*)\*(?!\*)',
		'_' => '(?<=\S|^)(?<!_)_(?![a-zA-Z0-9_])',
	);

	protected $strong_relist = array(
		''   => '(?:(?<!\*)\*\*(?!\*)|(?<![a-zA-Z0-9_])__(?!_))(?=\S|$)(?![\.,:;]\s)',
		'**' => '(?<=\S|^)(?<!\*)\*\*(?!\*)',
		'__' => '(?<=\S|^)(?<!_)__(?![a-zA-Z0-9_])',
	);

	protected $em_strong_relist = array(
		''    => '(?:(?<!\*)\*\*\*(?!\*)|(?<![a-zA-Z0-9_])___(?!_))(?=\S|$)(?![\.,:;]\s)',
		'***' => '(?<=\S|^)(?<!\*)\*\*\*(?!\*)',
		'___' => '(?<=\S|^)(?<!_)___(?![a-zA-Z0-9_])',
	);

	protected $em_strong_prepared_relist;
	/**#@-*/

	/**
	 * Prepare regular expressions for searching emphasis tokens in any context.
	 */
	public function prepareEmphasis() 
	{
		foreach ($this->em_relist as $em => $em_re) {
			foreach ($this->strong_relist as $strong => $strong_re) {
				// Construct list of allowed token expressions.
				$token_relist = array();
				$_index = (string) $em.$strong;
				if (isset($this->em_strong_relist[$_index])) {
					$token_relist[] = $this->em_strong_relist[$_index];
				}
				$token_relist[] = $em_re;
				$token_relist[] = $strong_re;
				
				// Construct master expression from list.
				$token_re = '{('. implode('|', $token_relist) .')}';
				$this->em_strong_prepared_relist[$_index] = $token_re;
			}
		}
	}
	
	/**
	 * @param string $text The text to be parsed
	 * @return string The text parsed
	 * @see runSpanGamut()
	 * @see hashPart()
	 */
	public function doEmphasis($text) 
	{
		$token_stack = array('');
		$text_stack = array('');
		$em = '';
		$strong = '';
		$tree_char_em = false;
		
		while (1) {

			// Get prepared regular expression for seraching emphasis tokens in current context.
			$_index = (string) $em.$strong;
			$token_re = $this->em_strong_prepared_relist[$_index];
			
			// Each loop iteration search for the next emphasis token. 
			// Each token is then passed to handleSpanToken.
			$parts = preg_split($token_re, $text, 2, PREG_SPLIT_DELIM_CAPTURE);
			$text_stack[0] .= $parts[0];
			$token =& $parts[1];
			$text =& $parts[2];
			
			if (empty($token)) {
				// Reached end of text span: empty stack without emitting any more emphasis.
				while ($token_stack[0]) {
					$text_stack[1] .= array_shift($token_stack);
					$text_stack[0] .= array_shift($text_stack);
				}
				break;
			}
			
			$token_len = strlen($token);
			if ($tree_char_em) {
				// Reached closing marker while inside a three-char emphasis.
				if ($token_len == 3) {
					// Three-char closing marker, close em and strong.
					array_shift($token_stack);
					$span = array_shift($text_stack);
					$span = $this->runGamut( 'spanGamut', $span);
//					$span = '<strong><em>'.$span.'</em></strong>';

					$span = $this->runFormaterMethod('buildEmphasis', 'both', $span);

					$text_stack[0] .= $this->hashPart($span);
					$em = '';
					$strong = '';
				} else {
					// Other closing marker: close one em or strong and
					// change current token state to match the other
					$token_stack[0] = str_repeat($token{0}, 3-$token_len);
					$tag = $token_len == 2 ? 'strong' : 'em';
					$span = $text_stack[0];
					$span = $this->runGamut( 'spanGamut', $span);
//					$span = '<'.$tag.'>'.$span.'</'.$tag.'>';

					$span = $this->runFormaterMethod('buildEmphasis', $tag, $span);

					$text_stack[0] = $this->hashPart($span);
					$$tag = ''; // $$tag stands for $em or $strong
				}
				$tree_char_em = false;
			} else if ($token_len == 3) {
				if ($em) {
					// Reached closing marker for both em and strong.
					// Closing strong marker:
					for ($i = 0; $i < 2; ++$i) {
						$shifted_token = array_shift($token_stack);
						$tag = strlen($shifted_token) == 2 ? 'strong' : 'em';
						$span = array_shift($text_stack);
						$span = $this->runGamut( 'spanGamut', $span);
//						$span = '<'.$tag.'>'.$span.'</'.$tag.'>';

						$span = $this->runFormaterMethod('buildEmphasis', $tag, $span);

						$text_stack[0] .= $this->hashPart($span);
						$$tag = ''; // $$tag stands for $em or $strong
					}
				} else {
					// Reached opening three-char emphasis marker. Push on token 
					// stack; will be handled by the special condition above.
					$em = $token{0};
					$strong = (string) $em.$em;
					array_unshift($token_stack, $token);
					array_unshift($text_stack, '');
					$tree_char_em = true;
				}
			} else if ($token_len == 2) {
				if ($strong) {
					// Unwind any dangling emphasis marker:
					if (strlen($token_stack[0]) == 1) {
						$text_stack[1] .= array_shift($token_stack);
						$text_stack[0] .= array_shift($text_stack);
					}
					// Closing strong marker:
					array_shift($token_stack);
					$span = array_shift($text_stack);
					$span = $this->runGamut( 'spanGamut', $span);
//					$span = '<strong>'.$span.'</strong>';

					$span = $this->runFormaterMethod('buildEmphasis', 'strong', $span);

					$text_stack[0] .= $this->hashPart($span);
					$strong = '';
				} else {
					array_unshift($token_stack, $token);
					array_unshift($text_stack, '');
					$strong = $token;
				}
			} else {
				// Here $token_len == 1
				if ($em) {
					if (strlen($token_stack[0]) == 1) {
						// Closing emphasis marker:
						array_shift($token_stack);
						$span = array_shift($text_stack);
						$span = $this->runGamut( 'spanGamut', $span);
//						$span = '<em>'.$span.'</em>';

						$span = $this->runFormaterMethod('buildEmphasis', 'em', $span);

						$text_stack[0] .= $this->hashPart($span);
						$em = '';
					} else {
						$text_stack[0] .= $token;
					}
				} else {
					array_unshift($token_stack, $token);
					array_unshift($text_stack, '');
					$em = $token;
				}
			}
		}
		return $text_stack[0];
	}

##@emd@## !GRAMMAR ##@emd@##

// Endfile