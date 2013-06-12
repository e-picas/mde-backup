<?php
/**
 * PHP Markdown Extended
 * Copyright (c) 2008-2013 Pierre Cassat
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

use MarkdownExtended\ContentInterface,
    MarkdownExtended\Helper as MDE_Helper,
    MarkdownExtended\Exception as MDE_Exception;

/**
 * Class defining a content object for the Parser
 */
class Content implements ContentInterface
{
    /**
     * @var misc
     */
    protected $id;

    /**
     * @var string
     */
    protected $filepath = '';

    /**
     * @var string
     */
    protected $source = '';

    /**
     * @var string
     */
    protected $body = '';

    /**
     * @var string
     */
    protected $charset = 'utf-8';

    /**
     * @var string
     */
    protected $title;

    /**
     * @var array
     */
    protected $metadata = array();

    /**
     * @var string
     */
    protected $metadata_html;

    /**
     * @var array
     */
    protected $notes = array();

    /**
     * @var string
     */
    protected $notes_html;

    /**
     * @var array
     */
    protected $footnotes = array();

    /**
     * @var array
     */
    protected $glossaries = array();

    /**
     * @var array
     */
    protected $citations = array();

    /**
     * @var array
     */
    protected $menu = array();

    /**
     * @var array
     */
    protected $urls = array();

    /**
     * @var array
     */
    protected $dom_ids = array();

    /**
     * @static array
     */
    protected static $name_mapping = array(
        'note'=>'notes',
        'toc'=>'menu',
        'footnote'=>'footnotes',
        'glossary'=>'glossaries',
        'citation'=>'citations',
        'url'=>'urls'
    );

// -------------------------
// Constructor
// -------------------------

    /**
     * @param string $source A string implementing Markdown syntax
     * @param string $filepath The path of a file where to get content to parse
     * @param misc $id The ID of the content item
     */
    public function __construct($source = null, $filepath = null, $id = null)
    {
        $this
            ->setSource($source)
            ->setFilepath($filepath)
            ->setId($id);
    }

// -------------------------
// Magic setter / getter
// -------------------------

    /**
     * Magic method to handle any `getXX()`, `setXX()` or `addXX()` method call on the object
     *
     * @see self::getVariable()
     * @see self::setVariable()
     * @see self::addVariable()
     */
    public function __call($name, array $arguments = null)
    {
        $method = substr($name, 0, 3) . 'Variable';
        $variable = MDE_Helper::fromCamelCase(substr($name, 3));
        array_unshift($arguments, $variable);
        if (method_exists($this, $method)) {
            return call_user_func_array(array($this, $method), $arguments);
        } else {
            throw new MDE_Exception\InvalidArgumentException(
                sprintf('Call of an unknown method "%s" on a %s instance!', $name, __CLASS__)
            );
        }
    }

    /**
     * Get an object property value
     *
     * This will return `null` if the variable doesn't exist, and `false` if it is empty.
     *
     * @param string $name
     *
     * @return misc|null|false
     */
    protected function getVariable($name)
    {
        if (property_exists($this, $name)) {
            return !empty($this->{$name}) ? $this->{$name} : false;
        }
        return null;
    }

    /**
     * Set an object property value
     *
     * @param string $name
     * @param misc $value
     *
     * @return self
     */
    protected function setVariable($name, $value)
    {
        if (property_exists($this, $name)) {
            $this->{$name} = $value;
        }
        return $this;
    }

    /**
     * Extend an object property value
     *
     * @param string $name
     * @param misc $value
     * @param misc $index
     *
     * @return self
     */
    protected function addVariable($name, $value, $index = null)
    {
        if (array_key_exists($name, self::$name_mapping)) {
            $name = self::$name_mapping[$name];
        }
        if (property_exists($this, $name)) {
            if (is_array($this->{$name})) {
                if (!empty($index)) {
                    $this->{$name}[$index] = $value;
                } else {
                    $this->{$name}[] = $value;
                }
            } elseif (is_string($this->{$name})) {
                $this->{$name} .= $value;
            }
        }
        return $this;
    }

// -------------------------
// Classic setter / getter
// -------------------------

    /**
     * @param misc $id
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return misc
     */
    public function getId()
    {
        if (empty($this->id)) {
            $this->setId(uniqid());
        }
        return $this->id;
    }

    /**
     * @return string
     * @throws InvalidArgumentException if the file can not be found or red
     */
    public function getSource()
    {
        if (!empty($this->source)) {
            return $this->source;
        }
        if (!empty($this->filepath)) {
            if (file_exists($this->filepath) && is_readable($this->filepath)) {
                $this->source = file_get_contents($this->filepath);
                $this->id = $this->filepath;
                return $this->source;
            } else {
                throw new MDE_Exception\InvalidArgumentException(
                    sprintf('Source file "%s" not found or is not readable!', $this->filepath)
                );
            }
        }
        return '';
    }
    
// -------------------------
// DOM IDs construction
// -------------------------

    /**
     * Verify if a reference is already defined in the DOM IDs register
     *
     * @param string $reference The reference to search
     * @return bool True if the reference exists in the register, false otherwise
     */
    public function hasDomId($reference)
    {
        return isset($this->dom_ids[$reference]);
    }

    /**
     * Get a DOM unique ID 
     *
     * @param string $reference A reference used to store the ID (and retrieve it - by default, a uniqid)
     *
     * @return str The unique ID created or the existing one for the reference if so
     */
    public function getDomId($reference, $id = null)
    {
        return $this->hasDomId($reference) ?
            $this->dom_ids[$reference] : $this->setNewDomId(
                !empty($id) ? $id : $reference, $reference
            );
    }

    /**
     * Create and get a new DOM unique ID 
     *
     * @param string $id A string that will be used to construct the ID
     * @param string $reference A reference used to store the ID (and retrieve it - by default `$id`)
     * @param bool $return_array Allow to return an array in case of existaing reference
     *
     * @return array|str The unique ID created if the reference was empty
     *                   An array like (id=>XXX, reference=>YYY) if it was not
     */
    public function setNewDomId($id, $reference = null, $return_array = true)
    {
        $_reference = $reference;
        if (empty($_reference)) {
            $_reference = $id;
        }
        $new_id = $id;
        while (in_array($new_id, $this->dom_ids)) {
            $new_id = $id.'_'.uniqid();
        }
        if ($this->hasDomId($_reference)) {
            while (isset($this->dom_ids[$_reference])) {
                $_reference = $reference.'_'.uniqid();
            }
            $return = true===$return_array ? array(
                'id'=>$new_id, 'reference'=>$_reference
            ) : $new_id;
        } else {
            $return = $new_id;
        }
        $this->dom_ids[$_reference] = $new_id;
        return $return;
    }

// -------------------------
// HACK : full formated HTML content
// -------------------------

    /**
     * Get a complete version of parsed content, including metadata, body and notes
     *
     * @param bool $full_html Defines if the meatdata must be returned in a `<head>` block
     *
     * @return string
     */
    public function getFullContent($full_html = true, $html_tag = '<!DOCTYPE html>')
    {
        $content = '';
        $title_done = false;

        if ($full_html) {
            $content .= "$html_tag\n<head>\n";
            if ($this->getCharset()) {
                $content .= "<meta charset=\"{$this->getCharset()}\" />\n";
            }
        }

        // metadata
        if ($this->getMetadata()) {
	        $special_metadata = MarkdownExtended::getConfig('special_metadata');
            foreach ($this->getMetadata() as $meta_name=>$meta_content) {
		        if (!in_array($meta_name, $special_metadata)) {
                    if ($meta_name=='title') {
                        $content .= "<title>$meta_content</title>\n";
                        $title_done = true;
                    } else {
                        $content .= "<meta name=\"$meta_name\" content=\"$meta_content\" />\n";
                    }
                }
            }
        }

        // force title if so
        if (!$title_done && $this->getTitle()) {
            $content .= "<title>{$this->getTitle()}</title>\n";
        }

        if ($full_html) $content .= "</head><body>\n";

        // body
        if ($this->getNotes()) {
			$content .= $this->getBody();
        }

        // notes
        if ($this->getNotes()) {
			$content .= "\n\n<div class=\"footnotes\">\n<ol>\n";
            foreach ($this->getNotes() as $id=>$note_content) {
			    $content .= "<li id=\"{$note_content['note-id']}\">{$note_content['text']}</li>";
            }
			$content .= "</ol>\n</div>\n\n";
		}

        if ($full_html) $content .= "</body>\n</html>\n";

        return $content;
    }

}

// Endfile