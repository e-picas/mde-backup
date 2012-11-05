<?php
/**
 */

##@emd@## CONFIG ##@emd@##

documentGamut[stripLinkDefinitions]=20

##@emd@## !CONFIG ##@emd@##

##@emd@## GRAMMAR ##@emd@##

	/**
	 * Strips link definitions from text, stores the URLs and titles in
	 * hash references.
	 *
	 * Link defs are in the form: ^[id]: url "optional title"
	 *
	 * @param string $text The text to be parsed
	 * @return string The text parsed
	 * @see _stripLinkDefinitions_callback()
	 * @todo Manage attributes (not working for now)
	 */
	public function stripLinkDefinitions($text) 
	{
		$less_than_tab = $this->getOption('tab_width') - 1;
		return preg_replace_callback('{
							^[ ]{0,'.$less_than_tab.'}\[(.+)\][ ]?:	# id = $1
							  [ ]*
							  \n?				# maybe *one* newline
							  [ ]*
							(?:
							  <(.+?)>		# url = $2
							|
							  (\S+?)		# url = $3
							)
							  [ ]*
							  \n?				# maybe one newline
							  [ ]*
							(?:
								(?<=\s)		# lookbehind for whitespace
								["(]
								(.*?)			# title = $4
								[")]
								[ ]*
							)?	        # title is optional
							  [ ]*
							  \n?				# maybe one newline
							  [ ]*
							(?:				  # Attributes = $5
								(?<=\s)	  # lookbehind for whitespace
								(
									([ ]*\n)?
									((?:\S+?=\S+?)|(?:.+?=.+?)|(?:.+?=".*?")|(?:\S+?=".*?"))
								)
							  [ ]*
							)?	        # attributes are optional
							(\n+|\Z)
			}xm',
			array(&$this, '_stripLinkDefinitions_callback'), $text);
	}

	/**
	 * Add each link reference to `$urls` and `$titles` tables with index `$link_id`
	 *
	 * @param array $matches A set of results of the `stripLinkDefinitions()` function
	 * @return empty
	 */
	protected function _stripLinkDefinitions_callback($matches) 
	{
		$link_id = strtolower($matches[1]);
		$url = $matches[2] == '' ? $matches[3] : $matches[2];
		$this->urls[$link_id] = $url;
		$this->titles[$link_id] =& $matches[4];
		$this->attributes[$link_id] = $matches[5];
		return ''; // String that will replace the block
	}

##@emd@## !GRAMMAR ##@emd@##

// Endfile