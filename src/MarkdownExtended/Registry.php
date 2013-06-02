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
namespace MarkdownExtended;

/**
 */
class Registry
{

	/**
	 * Array of the registry stacks
	 * @var array
	 */
	private $stacks;

    /**
     * @static array
     */
    public static $registry_stacks = array('loaded', 'config', 'parser');

	/**
	 * Initialize the registry stacks to empty arrays
	 */
	public function __construct()
	{
	    foreach (self::$registry_stacks as $stack) {
    	 	$this->stacks[$stack] = $this->getStackInstance($stack);
	    }
	}

    /**
     * Create, check and get a new registry stack object instance
     */
    protected function getStackInstance($stack_name)
    {
        if (!$this->isStack($stack_name)) {
            throw new \InvalidArgumentException(
                sprintf('Unknown stack <%s> in registry!', $stack)
            );
        }
        $_cls = '\MarkdownExtended\\RegistryStack\\'.ucfirst($stack_name);
        if (class_exists($_cls)) {
            $stack = new $_cls;
            if (!is_subclass_of($_cls, '\MarkdownExtended\\RegistryStack\\AbstractStack')) {
                throw new \LogicException(
                    sprintf('Registry stack class <%s> must extend abstract class <%s>!',
                        $_cls, '\MarkdownExtended\\RegistryStack\\AbstractStack')
                );
            }
            return $stack;
        } else {
            throw new \InvalidArgumentException(
                sprintf('Registry stack class <%s> not found!', $_cls)
            );
        }
    }

    /**
     * Test if a stack exists
     *
     * @param string $stack_name
     *
     * @return bool
     */
    public function isStack($stack_name)
    {
        return in_array($stack_name, self::$registry_stacks);
    }

    /**
     * @param string $stack_name
     */
    public function getStack($stack_name)
    {
        if (!$this->isStack($stack_name)) {
            throw new \InvalidArgumentException(
                sprintf('Unknown stack <%s> in registry!', $stack)
            );
        }
        return $this->stacks[$stack_name];
    }

    /**
     * Validate a stack entry name
     * @param string $var
     */
    public function validateEntryName($var)
    {
		if (!is_string($var) || !ctype_alnum(str_replace(array('_', '\\'), '', $var))) {
            throw new \InvalidArgumentException(
                sprintf("Registry entry must be named by alpha-numeric string, <%s> given!", $var)
            );
            return false;
        }
        return true;
    }

	/**
	 * Set or reset a new instance in global registry
	 */
	public function set($var, $val, $stack)
	{
	    $stack_obj = $this->getStack($stack);
	    if ($this->validateEntryName($var)) {
    	    $stack_obj->set($var, $val);
    	}
	}

	/**
	 * Add something to an existing entry of the global registry, the entry is created if it not exist
	 */
	public function add($var, $val, $stack)
	{
	    $stack_obj = $this->getStack($stack);
	    if ($this->validateEntryName($var)) {
    	    $stack_obj->add($var, $val);
    	}
	}

	/**
	 * Remove something to an existing entry of the global registry, the entry is created if it not exist
	 */
	public function remove($var, $val = null, $stack = null)
	{
	    $stack_obj = $this->getStack($stack);
	    if ($this->validateEntryName($var)) {
    	    $stack_obj->remove($var, $val);
    	}
	}

	/**
	 * Get an entry from the global registry
	 */
	public function get($var, $stack, $default = null)
	{
	    $stack_obj = $this->getStack($stack);
	    if ($this->validateEntryName($var)) {
    	    return $stack_obj->get($var, $default);
    	}
    	return $default;
	}

}

// Endfile
