<?php
/**
 */

##@emd@## CONFIG ##@emd@##

; Optional title attribute for footnote links and backlinks.
footnote_link_title="See footnote %%"
footnote_backlink_title="Return to content"

; Optional class attribute for footnote links and backlinks.
footnote_link_class="footnote"
footnote_backlink_class="reverse_footnote"

; Optional id attribute prefix for footnote links and backlinks.
footnote_id_prefix=""

; Optional title attribute for glossary footnote links and backlinks.
glossary_link_title="See glossary entry %%"
glossary_backlink_title="Return to content"

; Optional class attribute for glossary footnote links and backlinks.
glossary_link_class="footnote_glossary"
glossary_backlink_class="reverse_footnote_glossary"

; Optional id attribute prefix for glossary footnote links and backlinks.
glossary_id_prefix=""

; Optional title attribute for citation footnote links and backlinks.
bibliography_link_title="See bibliography reference %%"
bibliography_backlink_title="Return to content"

; Optional class attribute for citation footnote links and backlinks.
bibliography_link_class="footnote_bibliography"
bibliography_backlink_class="reverse_footnote_bibliography"

; Optional id attribute prefix for citation footnote links and backlinks.
bibliography_id_prefix=""

setupGamut[_setupNotes]=1
teardownGamut[_teardownNotes]=1
documentGamut[stripNotes]=10
documentGamut[appendNotes]=60
spanGamut[doNotes]=5

##@emd@## !CONFIG ##@emd@##

##@emd@## OUTPUTFORMAT_INTERFACE ##@emd@##

	/**
	 * @param string $text The sup text content
	 * @param array $attrs The sup attributes if so
	 * @return string The sup tag string
	 */
	public static function buildSup( $text, $attrs=array() );

	/**
	 * @param string $text The span text content
	 * @param array $attrs The span attributes if so
	 * @return string The span tag string
	 */
	public static function buildSpan( $text, $attrs=array() );

	/**
	 * @param string $text The div text content
	 * @param array $attrs The div attributes if so
	 * @return string The div tag string
	 */
	public static function buildDiv( $text, $attrs=array() );

##@emd@## !OUTPUTFORMAT_INTERFACE ##@emd@##

##@emd@## GRAMMAR ##@emd@##

	/**
	 * Extra variables used during extra transformations.
	 */
	protected $footnotes = array();
	protected $glossaries = array();
	protected $citations = array();
	protected $notes_ordered = array();
	
	/**
	 * Remind all written notes and node_id for multi-references
	 */
	protected $written_notes = array();
		
	/**
	 * Give the current footnote, glossary or citation number.
	 */
	protected $footnote_counter = 1;
		
	/**
	 * Give the total parsed notes number.
	 */
	protected $notes_counter = 0;
		
	protected function _setupNotes() 
	{
		$this->footnotes = array();
		$this->glossaries = array();
		$this->citations = array();
		$this->notes_ordered = array();
		$this->footnote_counter = 1;
		$this->notes_counter = 0;
	}
	
	/**
	 * Clearing Extra-specific variables and run teardownGamuts
	 */
	protected function _teardownNotes() 
	{
		$this->footnotes = array();
		$this->glossaries = array();
		$this->citations = array();
		$this->notes_ordered = array();
	}

	/**
	 * Strips link definitions from text, stores the URLs and titles in hash references.
	 *
	 * @param string $text The text to parse
	 * @return string The text parsed
	 * @see _stripFootnotes_callback()
	 */
	public function stripNotes($text) 
	{
		$this->written_notes = array();
		$less_than_tab = $this->getOption('tab_width') - 1;

		// Link defs are in the form: [^id]: url "optional title"
		$text = preg_replace_callback('{
			^[ ]{0,'.$less_than_tab.'}\[\^(.+?)\][ ]?:	# note_id = $1
			  [ ]*
			  \n?					        # maybe *one* newline
			(						          # text = $2 (no blank lines allowed)
				(?:					
					.+				        # actual text
				|
					\n				        # newlines but 
					(?!\[\^.+?\]:\s)  # negative lookahead for footnote marker.
					(?!\n+[ ]{0,3}\S) # ensure line is not blank and followed 
									          # by non-indented content
				)*
			)		
			}xm',
			array(&$this, '_stripNotes_callback'),
			$text);

		// Link defs are in the form: [#id]: url "optional title"
		$text = preg_replace_callback('{
			^[ ]{0,'.$less_than_tab.'}\[(\#.+?)\][ ]?:	# note_id = $1
			  [ ]*
			  \n?					        # maybe *one* newline
			(						          # text = $2 (no blank lines allowed)
				(?:					
					.+				        # actual text
				|
					\n				        # newlines but 
					(?!\[\^.+?\]:\s)  # negative lookahead for footnote marker.
					(?!\n+[ ]{0,3}\S) # ensure line is not blank and followed 
									          # by non-indented content
				)*
			)		
			}xm',
			array(&$this, '_stripNotes_callback'),
			$text);

		return $text;
	}

	/**
	 * Build the footnote and strip it from content
	 *
	 * @param array $matches Results from the `stripFootnotes()` function
	 * @return string The text parsed
	 * @see doOutdent()
	 */
	protected function _stripNotes_callback($matches) 
	{
		if (0 != preg_match('/^(<p>)?glossary:/i', $matches[2])) {
			$this->glossaries[ $this->getOption('glossary_id_prefix') . $matches[1] ] = $this->doOutdent($matches[2]);
		} elseif (0 != preg_match('/^\#(.*)?/i', $matches[1])) {
			$this->citations[ $this->getOption('bibliography_id_prefix') . substr($matches[1],1) ] = $this->doOutdent($matches[2]);
		} else {
			$this->footnotes[ $this->getOption('footnote_id_prefix') . $matches[1] ] = $this->doOutdent($matches[2]);
		}
		return ''; // String that will replace the block
	}

	/**
	 * Replace footnote references in $text [^id] with a special text-token 
	 * which will be replaced by the actual footnote marker in appendFootnotes.
	 *
	 * @param string $text The text to parse
	 * @return string The text parsed
	 * @todo Manage multi-calls of same note ID
	 */
	public function doNotes($text) 
	{
		if (!$this->in_anchor) {
			$text = preg_replace('{\[\^(.+?)\]}', "F\x1Afn:\\1\x1A:", $text);
//			$text = preg_replace('{\[\#(.+?)\]}', "F\x1Afn:\\1\x1A:", $text);
			$text = preg_replace('{\[(.+?)\]\[\#(.+?)\]}', " [\\1, F\x1Afn:\\2\x1A:]", $text);
		}
		return $text;
	}

	/**
	 * Append footnote list to text.
	 *
	 * @param string $text The text to parse
	 * @return string The text parsed
	 * @see _appendFootnotes_callback()
	 * @see encodeAttribute()
	 * @see runBlockGamut()
	 */
	public function appendNotes($text) 
	{
		// First loop for references
		if (!empty($this->notes_ordered)) 
		{
			$tmp_notes_ordered = $this->notes_ordered;
			$_counter=0;
			while (!empty($tmp_notes_ordered)) 
			{
				$note_id = key($tmp_notes_ordered);
				unset($tmp_notes_ordered[$note_id]);
				if (!array_key_exists($note_id, $this->written_notes))
					$this->written_notes[$note_id] = $_counter++;
			}
		}
	
		$text = preg_replace_callback('{F\x1Afn:(.*?)\x1A:}', 
			array(&$this, '_appendNotes_callback'), $text);
	
		if (!empty($this->notes_ordered)) 
		{
//			$text .= "\n\n" . '<div class="footnotes">'."\n"
//				. '<hr'. $this->getOption('empty_element_suffix') ."\n" . '<ol>'."\n\n";
			$notes_ctt='';
			while (!empty($this->notes_ordered)) 
			{
				$note = reset($this->notes_ordered);
				$note_id = key($this->notes_ordered);
				unset($this->notes_ordered[$note_id]);

				// footnotes
				if (isset($this->footnotes[$note_id]))
//					$text .= self::_doFootnote( $note_id );
					$notes_ctt .= self::_doFootnote( $note_id, 'footnote' );

				// glossary
				elseif (isset($this->glossaries[$note_id]))
//					$text .= self::_doGlossary( $note_id );
					$notes_ctt .= self::_doFootnote( $note_id, 'glossary' );

				// citations
				elseif (isset($this->citations[$note_id]))
//					$text .= self::_doCitation( $note_id );
					$notes_ctt .= self::_doFootnote( $note_id, 'citation' );
			}

//			$text .= '</ol>'."\n" . '</div>';

			$text .= "\n\n".$this->runFormaterMethod(
				'buildDiv', 
				'<hr'.$this->getOption('empty_element_suffix')."\n".$this->runFormaterMethod('buildOrderedList', $notes_ctt)."\n", 
				array( 'class'=>'footnotes' )
			)."\n\n";
		}
		return $text;
	}

	/**
	 * Append footnote list to text.
	 *
	 * @param string $text The text to parse
	 * @return string The text parsed
	 * @see _appendFootnotes_callback()
	 * @see encodeAttribute()
	 * @see runBlockGamut()
	 */
	protected function _doFootnote( $note_id, $type='footnote' ) 
	{
		$text='';
		
		switch($type)
		{
			case 'footnote': default:
				$type_attr = 'footnote';
				$class = $this->encodeAttribute( $this->getOption('footnote_link_class') );
				$title = $this->encodeAttribute( $this->getOption('footnote_link_title') );
				$backclass = $this->encodeAttribute( $this->getOption('footnote_backlink_class') );
				$backtitle = $this->encodeAttribute( $this->getOption('footnote_backlink_title') );
				$note_str = $this->footnotes[$note_id];
				$list =& $this->footnotes;
				$back_ref_prefix = 'fnref';
				$ref_prefix = 'fn';
				break;
			case 'glossary':
				$type_attr = 'glossary';
				$class = $this->encodeAttribute( $this->getOption('glossary_link_class') );
				$title = $this->encodeAttribute( $this->getOption('glossary_link_title') );
				$backclass = $this->encodeAttribute( $this->getOption('glossary_backlink_class') );
				$backtitle = $this->encodeAttribute( $this->getOption('glossary_backlink_title') );
				$note_str = substr( $this->glossaries[$note_id], strlen('glossary:') );				
				$list =& $this->glossaries;
				$back_ref_prefix = 'fngref';
				$ref_prefix = 'fng';
				break;
			case 'citation': 
				$type_attr = 'bibliography';
				$class = $this->encodeAttribute( $this->getOption('bibliography_link_class') );
				$title = $this->encodeAttribute( $this->getOption('bibliography_link_title') );
				$backclass = $this->encodeAttribute( $this->getOption('bibliography_backlink_class') );
				$backtitle = $this->encodeAttribute( $this->getOption('bibliography_backlink_title') );
				$note_str = $this->citations[$note_id];
				$list =& $this->citations;
				$back_ref_prefix = 'fncref';
				$ref_prefix = 'fnc';
				break;
		}
		
		
		if (!empty($list[$note_id])) 
		{
//			$attr = ' rev="'.$type_attr.'"';
			$encoded_note_id = $this->encodeAttribute($note_id);
			$attrs = array(
				'href'=>'#'.$back_ref_prefix.':'.$encoded_note_id,
				'rev'=>$type_attr
			);
//			if ($backclass != '') $attr .= ' class="'.$backclass.'"';
//			if ($backtitle != '') $attr .= ' title="'.$backtitle.'"';
			if ($backclass != '') $attrs['class'] = $backclass;
			if ($backtitle != '') $attrs['title'] = $backtitle;
			
			if ($type=='glossary')
			{
				$note_str = preg_replace_callback('{
					^(.*?)				              # $1 = term
					\s*
					(?:\(([^\(\)]*)\)[^\n]*)?		# $2 = optional sort key
					\n{1,}
					(.*?)
					}x',
					array(&$this, '_doGlossary_callback'), $note_str);
			}
			elseif ($type=='citation')
			{
				$note_str = preg_replace_callback('{
					^\#(.*?)				              # $1 = term
					\s*
					(?:\(([^\(\)]*)\)[^\n]*)?		# $2 = optional sort key
					\n{1,}
					(.*?)
					}x',
					array(&$this, '_doCitation_callback'), $note_str);
			}
			
			$note_str .= "\n"; // Need to append newline before parsing.
			$note_str = $this->runGamut( 'blockGamut', $note_str."\n");				
			$note_str = preg_replace_callback('{F\x1Afn:(.*?)\x1A:}', 
					array(&$this, '_appendNotes_callback'), $note_str);
				
//			$attr = str_replace('%%', ++$this->notes_counter, $attr);
			foreach($attrs as $i=>$j)
			{
				$attrs[$i] = str_replace('%%', ++$this->notes_counter, $j);
			}

			$this->written_notes[$note_id] = $this->notes_counter;
				
			// Add backlink to last paragraph; create new paragraph if needed.
//			$backlink = '<a href="#'.$back_ref_prefix.':'.$note_id.'"'.$attr.'>&#8617;</a>';
			$backlink = $this->runFormaterMethod('buildLink', '&#8617;', $attrs);

			if (preg_match('{</p>$}', $note_str)) {
				$note_str = substr($note_str, 0, -4) . '&#160;'.$backlink.'</p>';
			} else {
//				$note_str .= "\n\n".'<p>'.$backlink.'</p>';
				$note_str .= "\n\n".$this->runFormaterMethod('buildParagraph', $backlink);
			}
				
//			$text = '<li id="'.$ref_prefix.':'.$note_id.'">'."\n" . $note_str . "\n" . '</li>'."\n\n";
			$text = $this->runFormaterMethod('buildListItem', "\n".$note_str."\n", array( 'id'=>$ref_prefix.':'.$encoded_note_id ))."\n\n";
		}
		return $text;
	}

	/**
	 * Build the glossary entry
	 *
	 * @param array $matches Results form the `appendGlossaries` function
	 * @return string The text parsed
	 */
	protected function _doGlossary_callback($matches)
	{
		return 
//			'<span class="glossary name">'.trim($matches[1]).'</span>'
			$this->runFormaterMethod('buildSpan', trim($matches[1]), array( 'class'=>'glossary name'))
//			.(isset($matches[3]) ? '<span class="glossary sort" style="display:none">'.$matches[2].'</span>' : '')
			.(isset($matches[3]) ? $this->runFormaterMethod('buildSpan', $matches[2], array(
				'class'=>'glossary sort',
				'style'=>'display:none'
			)) : '')
			."\n\n"
			.(isset($matches[3]) ? $matches[3] : $matches[2]);
	}

	/**
	 * Build the citation entry
	 *
	 * @param array $matches Results form the `appendGlossaries` function
	 * @return string The text parsed
	 */
	protected function _doCitation_callback($matches)
	{
		return 
//			'<span class="bibliography name">'.trim($matches[1]).'</span>'."\n\n".$matches[2];
			$this->runFormaterMethod('buildSpan', trim($matches[1]), array( 'class'=>'bibliography name'))
			."\n\n".$matches[2];
	}

	/**
	 * Append footnote and glossary list to text.
	 *
	 * @param array $matches Results form the `appendFootnotes()` or `appendGlossaries` functions
	 * @return string The text parsed
	 * @see encodeAttribute()
	 */
	protected function _appendNotes_callback($matches) 
	{
		// Create footnote marker only if it has a corresponding footnote *and*
		// the footnote hasn't been used by another marker.
		$footnote_node_id = $this->getOption('footnote_id_prefix') . $matches[1];
		$glossary_node_id = $this->getOption('glossary_id_prefix') . $matches[1];
		$citation_node_id = $this->getOption('bibliography_id_prefix') . $matches[1];

		$type=null;
		if (isset($this->footnotes[$footnote_node_id])) 
		{
			$type = 'footnote';
			$node_id = $footnote_node_id;
			// Transfert footnote content to the ordered list.
			$this->notes_ordered[$node_id] = $this->footnotes[$node_id];
		}
		elseif (isset($this->glossaries[$glossary_node_id])) 
		{
			$type = 'glossary';
			$node_id = $glossary_node_id;
			// Transfert footnote content to the ordered list.
			$this->notes_ordered[$glossary_node_id] = $this->glossaries[$glossary_node_id];
		}
		elseif (isset($this->citations[$citation_node_id])) 
		{
			$type = 'citation';
			$node_id = $citation_node_id;
			// Transfert footnote content to the ordered list.
			$this->notes_ordered[$citation_node_id] = $this->citations[$citation_node_id];
		}

		if (!is_null($type))
		{
			switch($type)
			{
			case 'footnote': default:
				$type_attr = 'footnote';
				$class = $this->encodeAttribute( $this->getOption('footnote_link_class') );
				$title = $this->encodeAttribute( $this->getOption('footnote_link_title') );
				$backclass = $this->encodeAttribute( $this->getOption('footnote_backlink_class') );
				$backtitle = $this->encodeAttribute( $this->getOption('footnote_backlink_title') );
				$note_str = $this->footnotes[$note_id];
				$list =& $this->footnotes;
				$back_ref_prefix = 'fnref';
				$ref_prefix = 'fn';
				break;
			case 'glossary':
				$type_attr = 'glossary';
				$class = $this->encodeAttribute( $this->getOption('glossary_link_class') );
				$title = $this->encodeAttribute( $this->getOption('glossary_link_title') );
				$backclass = $this->encodeAttribute( $this->getOption('glossary_backlink_class') );
				$backtitle = $this->encodeAttribute( $this->getOption('glossary_backlink_title') );
				$note_str = substr( $this->glossaries[$note_id], strlen('glossary:') );				
				$list =& $this->glossaries;
				$back_ref_prefix = 'fngref';
				$ref_prefix = 'fng';
				break;
			case 'citation': 
				$type_attr = 'bibliography';
				$class = $this->encodeAttribute( $this->getOption('bibliography_link_class') );
				$title = $this->encodeAttribute( $this->getOption('bibliography_link_title') );
				$backclass = $this->encodeAttribute( $this->getOption('bibliography_backlink_class') );
				$backtitle = $this->encodeAttribute( $this->getOption('bibliography_backlink_title') );
				$note_str = $this->citations[$note_id];
				$list =& $this->citations;
				$back_ref_prefix = 'fncref';
				$ref_prefix = 'fnc';
				break;
			}
			
			$num = array_key_exists($matches[1], $this->written_notes) ?
				$this->written_notes[$matches[1]] : $this->footnote_counter++;
			$encoded_node_id = $this->encodeAttribute($node_id);
//			$attr = ' rel="'.$type_attr.'"';
			$attrs = array(
				'href'=>'#'.$ref_prefix.':'.$encoded_node_id,
				'rel'=>$type_attr
			);
//			if ($class != '') $attr .= ' class="'.$class.'"';
//			if ($title != '') $attr .= ' title="'.$title.'"';
			if ($class != '') $attrs['class'] = $class;
			if ($title != '') $attrs['title'] = $title;

//			$attr = str_replace('%%', $num, $attr);
			foreach($attrs as $i=>$j)
			{
				$attrs[$i] = str_replace('%%', $num, $j);
			}
			
//			return
//				'<sup id="'.$back_ref_prefix.':'.$node_id.'">'.
//				'<a href="#'.$ref_prefix.':'.$node_id.'"'.$attr.'>'.$num.'</a>'.
//				'</sup>';
			return 
				$this->runFormaterMethod('buildSup', 
					$this->runFormaterMethod('buildLink', $num, $attrs), 
					array('id'=>$back_ref_prefix.':'.$node_id)
				);

		}

		return '[^'.$matches[1].']';
	}
		
##@emd@## !GRAMMAR ##@emd@##

// Endfile