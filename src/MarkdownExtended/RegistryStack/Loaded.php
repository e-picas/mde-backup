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
class Loaded extends AbstractStack
{

	/**
	 * @param string $var
	 * @param misc $val
	 */
	public function set($var, $val)
	{
        if (is_object($val)) {
            $this->data[$var] = $val;
        } else {
            throw new \InvalidArgumentException(sprintf(
                "New registry entry in the 'loaded' stack must be an object instance, <%s> given!", gettype($val)
            ));
        }
	    return $this;
	}

	/**
	 * @param string $var
	 * @param misc $val
	 */
	public function add($var, $val)
	{
        throw new \RuntimeException("Registry entry in the 'load' stack can not be extended!");
    	return $this;
	}

	/**
	 * @param string $var
	 * @param string $val
	 */
	public function remove($var, $val = null)
	{
        throw new \RuntimeException("Registry entry in the 'load' stack can not be removed!");
        return $this;
	}

}

// Endfile
