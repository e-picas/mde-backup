<?php
/**
 */

##@emd@## CONFIG ##@emd@##

; The mask used for MetaData named Title
metadata_mask_title='<%1$s>%2$s</%1$s>'

; The default mask used for MetaData
metadata_mask='<meta name="%s" content="%s" />'

documentGamut[stripMetaData]=1
documentGamut[appendMetaData]=55

##@emd@## !CONFIG ##@emd@##

##@emd@## GRAMMAR ##@emd@##

	protected $metadata=array();
	
	protected static $inMetaData=0;
	
	// SPECIAL METADATA
	var $specials_metadata = array(
		'baseheaderlevel', 'quoteslanguage'
	);

	/**
	 * @param string $text Text to parse
	 * @return string The text parsed
	 */
	public function stripMetaData($text) 
	{
		$lines = preg_split('/\n/', $text);
		$text='';
		self::$inMetaData=1;
		foreach ($lines as $line) {
			if (self::$inMetaData===0) {
				$text .= $line."\n";
			} else {
				$text .= self::_stripMetaData($line);
				if (preg_match('/^$/', $line)) {
					self::$inMetaData = 0;
				}
			}
		}
		return $text;
	}

	/**
	 * @param string $text Text to parse
	 * @return string The text parsed
	 */
	public function _stripMetaData($line) 
	{
		$line = preg_replace_callback(
			'{^([a-zA-Z0-9][0-9a-zA-Z _-]*?):\s*(.*)$}i',
			array($this, '_callbackMetaData'), $line);

		if (strlen($line))
			$line = preg_replace_callback(
				'/^\s*(.+)$/', array($this, '_callbackMetaData_nextline'), $line);

		if (strlen($line)) $line .= "\n";
		return $line;
	}

	/**
	 * @param array $matches A set of results of the `transform` function
	 * @return string The text parsed
	 */
	protected function _callbackMetaData($matches) 
	{
		$meta_key = strtolower(str_replace(' ', '', $matches[1]));
		$this->metadata[$meta_key] = trim($matches[2]);
		return '';
	}

	/**
	 * @param array $matches A set of results of the `transform` function
	 * @return string The text parsed
	 */
	protected function _callbackMetaData_nextline($matches) 
	{
		$meta_key = array_search(end($this->metadata), $this->metadata );
		$this->metadata[$meta_key] .= ' '.trim($matches[1]);
		return '';
	}

	public function appendMetaData($text)
	{
		$metadata = $this->metadata;
//		$this->metadata=array();
		if (!empty($metadata)) {
			$metadata_str='';
			foreach($metadata as $meta_name=>$meta_value) {
				if (!empty($meta_name) && is_string($meta_name)) {
					if (in_array($meta_name, $this->specials_metadata))
						$this->specials_metadata[$meta_name] = $meta_value;
					else
						$metadata_str .= "\n".$this->doMetaData($meta_name.':'.$meta_value);
				}
			}
			$text = $metadata_str."\n\n".$text;
		}
		return $text;
	}

	/**
	 *
	 * @param string $text The text to parse
	 * @return string The text parsed
	 */
	public function doMetaData($text) 
	{
		return preg_replace_callback('{^([0-9a-zA-Z_-]*?):(.*)$}', 
			array($this, '_doMetaDataCallback'), $text);
	}

	protected function _doMetaDataCallback($matches)
	{
		return self::buildMetaDataString( $matches[1], $matches[2] );
		return $this->runFormaterMethod('buildMetaData', array(
			'name'=>$matches[1],
			'value'=>$matches[2]
		));
	}

	public function buildMetaDataString( $meta_name, $meta_value )
	{
		$special_mask = 'metadata_mask_'.strtolower($meta_name);
		$special_mask_opt = $this->getOption($special_mask);
		if (!empty($special_mask_opt)) {
			eval("\$_meta = sprintf('$special_mask_opt', \$meta_name, \$meta_value);");
			return $_meta;
		} else
			return sprintf($this->getOption('metadata_mask'), $meta_name, $meta_value);
	}

##@emd@## !GRAMMAR ##@emd@##

##@emd@## DEV_MANUAL ##@emd@##

Une entrée de manuel du développeur ...

##@emd@## !DEV_MANUAL ##@emd@##

##@emd@## USER_MANUAL ##@emd@##

Une entrée de manuel ...

##@emd@## !USER_MANUAL ##@emd@##

// Endfile