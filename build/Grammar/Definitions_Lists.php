<?php
/**
 */

##@emd@## CONFIG ##@emd@##

blockGamut[doDefinitionsLists]=45

##@emd@## !CONFIG ##@emd@##

##@emd@## OUTPUTFORMAT_INTERFACE ##@emd@##

	/**
	 * @param string $text The definition list text content
	 * @param array $attrs The definition list attributes if so
	 * @return string The definition list tag string
	 */
	public static function buildDefinitionList( $text, $attrs=array() );

	/**
	 * @param string $text The definition term text content
	 * @param array $attrs The definition term attributes if so
	 * @return string The definition term tag string
	 */
	public static function buildDefinitionTerm( $text, $attrs=array() );

	/**
	 * @param string $text The definition description text content
	 * @param array $attrs The definition description attributes if so
	 * @return string The definition description tag string
	 */
	public static function buildDefinitionDescription( $text, $attrs=array() );

##@emd@## !OUTPUTFORMAT_INTERFACE ##@emd@##

##@emd@## GRAMMAR ##@emd@##

	/**
	 * Form HTML definition lists.
	 *
	 * @param string $text The text to parse
	 * @return string The text parsed
	 * @see _doDefinitionsLists_callback()
	 */
	public function doDefinitionsLists($text) 
	{
		$less_than_tab = $this->getOption('tab_width') - 1;

		// Re-usable pattern to match any entire dl list:
		$whole_list_re = '(?>
			(								                    # $1 = whole list
			  (								                  # $2
				[ ]{0,'.$less_than_tab.'}
				((?>.*\S.*\n)+)				            # $3 = defined term
				\n?
				[ ]{0,'.$less_than_tab.'}:[ ]+    # colon starting definition
			  )
			  (?s:.+?)
			  (								                  # $4
				  \z
				|
				  \n{2,}
				  (?=\S)
				  (?!						                 # Negative lookahead for another term
					[ ]{0,'.$less_than_tab.'}
					(?: \S.*\n )+?			           # defined term
					\n?
					[ ]{0,'.$less_than_tab.'}:[ ]+ # colon starting definition
				  )
				  (?!						                 # Negative lookahead for another definition
					[ ]{0,'.$less_than_tab.'}:[ ]+ # colon starting definition
				  )
			  )
			)
		)'; // mx

		return preg_replace_callback('{
				(?>\A\n?|(?<=\n\n))
				'.$whole_list_re.'
			}mx',
			array(&$this, '_doDefinitionsLists_callback'), $text);
	}

	/**
	 * Turn double returns into triple returns, so that we can make a
	 * paragraph for the last item in a list, if necessary
	 *
	 * @param array $matches The results form the doDefinitionsLists()` `preg_replace_callback()` command
	 * @return function Pass its result to the `hashBlock()` function
	 * @see hashBlock()
	 * @see doDefinitionsLists()
	 * @see processDefinitionsListItems()
	 */
	protected function _doDefinitionsLists_callback($matches) 
	{
		// Re-usable patterns to match list item bullets and number markers:
		$list = $matches[1];
		$result = trim($this->processDefinitionsListItems($list));
//		$result = '<dl>'."\n" . $result . "\n".'</dl>';
//		return $this->hashBlock($result) . "\n\n";

		return $this->hashBlock(
			$this->runFormaterMethod('buildDefinitionList', "\n".$result."\n")
		)."\n\n";
	}


	/**
	 * Process the contents of a single definition list, splitting it
	 * into individual term and definition list items.
	 *
	 * @param string $list_str The result string form the _doDefinitionsLists_callback()` function
	 * @return string Parsed list string
	 * @see _doDefinitionsLists_callback()
	 * @see _processDefinitionsListItems_callback_dt()
	 * @see _processDefinitionsListItems_callback_dd()
	 */
	public function processDefinitionsListItems($list_str) 
	{
		$less_than_tab = $this->getOption('tab_width') - 1;
		
		// trim trailing blank lines:
		$list_str = preg_replace("/\n{2,}\\z/", "\n", $list_str);

		// Process definition terms.
		$list_str = preg_replace_callback('{
			(?>\A\n?|\n\n+)					    # leading line
			(								            # definition terms = $1
				[ ]{0,'.$less_than_tab.'}	# leading whitespace
				(?![:][ ]|[ ])				    # negative lookahead for a definition 
											            # mark (colon) or more whitespace.
				(?> \S.* \n)+?				    # actual term (not whitespace).	
			)			
			(?=\n?[ ]{0,3}:[ ])				  # lookahead for following line feed 
											            # with a definition mark.
			}xm',
			array(&$this, '_processDefinitionsListItems_callback_dt'), $list_str);

		// Process actual definitions.
		$list_str = preg_replace_callback('{
			\n(\n+)?						        # leading line = $1
			(								            # marker space = $2
				[ ]{0,'.$less_than_tab.'}	# whitespace before colon
				[:][ ]+						        # definition mark (colon)
			)
			((?s:.+?))					 	      # definition text = $3
			(?= \n+ 						        # stop at next definition mark,
				(?:							          # next term or end of text
					[ ]{0,'.$less_than_tab.'} [:][ ]	|
					<dt> | \z
				)						
			)					
			}xm',
			array(&$this, '_processDefinitionsListItems_callback_dd'), $list_str);

		return $list_str;
	}

	/**
	 * Process the dt contents.
	 *
	 * @param array $matches The results form the `processDefinitionsListItems()` function
	 * @return string Parsed dt string
	 * @see processDefinitionsListItems()
	 * @see runSpanGamut()
	 */
	protected function _processDefinitionsListItems_callback_dt($matches) 
	{
		$terms = explode("\n", trim($matches[1]));
		$text = '';
		foreach ($terms as $term) {
			$term = $this->runGamut( 'spanGamut', trim($term) );
//			$text .= "\n".'<dt>' . $term . '</dt>';
			$text .= "\n".$this->runFormaterMethod('buildDefinitionTerm', $term);
		}
		return $text . "\n";

	}

	/**
	 * Process the dd contents.
	 *
	 * @param array $matches The results form the `processDefinitionsListItems()` function
	 * @return string Parsed dd string
	 * @see processDefinitionsListItems()
	 * @see runSpanGamut()
	 */
	protected function _processDefinitionsListItems_callback_dd($matches) 
	{
		$leading_line	= $matches[1];
		$marker_space	= $matches[2];
		$def			= $matches[3];

		if ($leading_line || preg_match('/\n{2,}/', $def)) {
			// Replace marker with the appropriate whitespace indentation
			$def = str_repeat(' ', strlen($marker_space)) . $def;
			$def = $this->runGamut( 'blockGamut', $this->doOutdent($def . "\n\n"));
			$def = "\n". $def ."\n";
		}
		else {
			$def = rtrim($def);
			$def = $this->runGamut( 'spanGamut', $this->doOutdent($def));
		}

//		return "\n".'<dd>' . $def . '</dd>'."\n";
		return "\n".$this->runFormaterMethod('buildDefinitionDescription', $def)."\n";
	}

##@emd@## !GRAMMAR ##@emd@##

// Endfile