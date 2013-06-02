<?php
/**
 * PHP Markdown Extended
 * Copyright (c) 2004-2013 Pierre Cassat
 *
 * original MultiMarkdown
 * Copyright (c) 2005-2009 Fletcher T. Penney
 * <http://fletcherpenney.net/>
 *
 * original PHP Markdown & Extra
 * Copyright (c) 2004-2012 Michel Fortin  
 * <http://michelf.com/projects/php-markdown/>
 *
 * original Markdown
 * Copyright (c) 2004-2006 John Gruber  
 * <http://daringfireball.net/projects/markdown/>
 */
namespace MarkdownExtended\RegistryStack;

/**
 */
abstract class AbstractStack
{

	/**
	 * Array of the registry stacks
	 * @var array
	 */
	protected $data = array();

	/**
	 * @param string $var
	 * @param misc $val
	 */
	public function set($var, $val)
	{
	    $this->data[$var] = $val;
	    return $this;
	}

	/**
	 * @param string $var
	 * @param misc $val
	 */
	public function add($var, $val)
	{
	    if (isset($this->data[$var])) {
    	    $this->data[$var] = $this->extend($this->data[$var], $val);
    	} else {
    	    $this->set($var, $val);
    	}
    	return $this;
	}

	/**
	 * @param string $var
	 * @param string $val
	 */
	public function remove($var, $val = null)
	{
        if (isset($this->data[$var])) {
            if (!empty($val) && is_string($val) && isset($this->data[$var][$val])) {
                unset($this->data[$var][$val]);
            } elseif (empty($val)) {
                unset($this->data[$var]);
            }
        }
        return $this;
	}

	/**
	 * @param string $var
	 * @param misc $default
	 */
	public function get($var, $default = null)
	{
	    return isset($this->data[$var]) ? $this->data[$var] : $default;
	}

	/**
	 * Extend a value with another, if types match
	 *
	 * @param misc $what
	 * @param misc $add
	 */
	protected function extend($what, $add)
	{
		if (empty($what)) return $add;
		switch (gettype($what)) {
			case 'string': return $what.$add; break;
			case 'numeric': return ($what+$add); break;
			case 'array': 
				if (is_array($add)) {
					$what += $add;
					return $what; 
				} else {
					throw new InvalidArgumentException(
    					"Trying to extend an array with not an array!"
			    	);
				}
				break;
			case 'object': 
				throw new InvalidArgumentException("Trying to extend an object!");
				break;
			default: 
				throw new InvalidArgumentException(sprintf(
  	  				"No extending definition found for type <%s>!", gettype($what)
		    	));
				break;
		}
		return $what;
	}

}

// Endfile
