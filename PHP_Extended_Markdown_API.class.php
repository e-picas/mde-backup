<?php
/**
 * PHP Extended Markdown
 * Copyright (c) 2012 Pierre Cassat
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
 *
 * @package 	PHP_Extended_Markdown
 * @license   	BSD
 * @link      	https://github.com/PieroWbmstr/Extended_Markdown
 */

/**
 * Get the Extended Markdown Grammar PHP class
 */
if (!@class_exists('PHP_Extended_Markdown'))
	require_once __DIR__."/PHP_Extended_Markdown.class.php";

/**
 */
class PHP_Extended_Markdown_API
{

    protected $parser           =null;
    protected $file             =null;
    protected $file_content     =null;
    protected $content          =null;

    public function __construct( array $options=array() )
    {
        $this->parser = PHP_Extended_Markdown::getInstance( $options );
    }

    public function load( $file_path )
    {
        if (@file_exists($file_path))
        {
            $this->setFile( new SplFileInfo($file_path) );
        }
        else
        {
            throw new InvalidArgumentException(
                sprintf('File "%s" not found!', $file_path)
            );
        }
        return $this;
    }

    public function parse()
    {
        if ($this->getFileContent()!==null)
        {
            $this->content = $this->parser->transform( $this->getFileContent() );
        }
        return $this;
    }

// ---------------------
// Getters / Setters
// ---------------------

    public function setFile( SplFileInfo $file )
    {
        $this->file = $file;
        $this->file_content = null;
        return $this;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function getFileContent()
    {
        if ($this->file_content===null && $this->file!==null)
        {
            $this->file_content = file_get_contents( $this->file->getRealPath() );
        }
        return $this->file_content;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function getParser()
    {
        return $this->parser;
    }

}

// Endfile
