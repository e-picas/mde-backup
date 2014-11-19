<?php
/**
 * PHP Markdown Extended
 * Copyright (c) 2008-2014 Pierre Cassat
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
namespace MarkdownExtended\Grammar\Filter;

use MarkdownExtended\MarkdownExtended;
use MarkdownExtended\Grammar\Filter;
use MarkdownExtended\Helper as MDE_Helper;
use MarkdownExtended\Exception as MDE_Exception;

/**
 * Process Markdown meta data
 *
 * @package MarkdownExtended\Grammar\Filter
 */
class MetaData
    extends Filter
{

    /**
     * @var     array
     */
    protected $metadata;

    /**
     * @var     array
     */
    protected $special_metadata;

    /**
     * @var     int
     */
    protected static $inMetaData = -1;

    /**
     * Prepare object with configuration
     */
    public function _setup()
    {
        MarkdownExtended::setVar('metadata', array());
        $this->metadata = array();
        $this->special_metadata = MarkdownExtended::getConfig('special_metadata');
        if (empty($this->special_metadata)) $this->special_metadata = array();
        self::$inMetaData = -1;
    }

    /**
     * @param   string  $text
     * @return  string
     */
    public function strip($text)
    {
        $lines = preg_split('/\n/', $text);
        $first_line = $lines[0];
        if (preg_match('/^([a-zA-Z0-9][0-9a-zA-Z _-]*?):\s*(.*)$/', $first_line)) {
            $text='';
            self::$inMetaData = 1;
            foreach ($lines as $line) {
                if (self::$inMetaData === 0) {
                    $text .= $line."\n";
                } else {
                    $text .= self::transform($line);
                    if (preg_match('/^$/', $line)) {
                        self::$inMetaData = 0;
                    }
                }
            }
        }
        if (!empty($this->metadata)) {
            MarkdownExtended::setVar('metadata', $this->metadata);
            MarkdownExtended::getContent()->setMetadata($this->metadata);
        }
        return $text;
    }

    /**
     * @param   string  $line
     * @return  string
     */
    public function transform($line)
    {
        $line = preg_replace_callback(
            '{^([a-zA-Z0-9][0-9a-zA-Z _-]*?):\s*(.*)$}i',
            array($this, '_callback'), $line);
        if (strlen($line)) {
            $line = preg_replace_callback(
                '/^\s*(.+)$/', array($this, '_callback_nextline'), $line);
        }
        if (strlen($line)) $line .= "\n";
        return $line;
    }

    /**
     * @param   array   $matches    A set of results of the `transform` function
     * @return  string
     */
    protected function _callback($matches)
    {
        $meta_key = strtolower(str_replace(' ', '', $matches[1]));
        $this->metadata[$meta_key] = trim($matches[2]);
        if (in_array($meta_key, $this->special_metadata)) {
            MarkdownExtended::setVar($meta_key, $this->metadata[$meta_key]);
        }
        return '';
    }

    /**
     * @param   array   $matches    A set of results of the `transform` function
     * @return  string
     */
    protected function _callback_nextline($matches)
    {
        $meta_key = array_search(end($this->metadata), $this->metadata);
        $this->metadata[$meta_key] .= ' '.trim($matches[1]);
        return '';
    }

    /**
     * Build meta data strings
     */
    public function append($text)
    {
        $metadata = MarkdownExtended::getVar('metadata');
        if (!empty($metadata)) {
            $metadata_str='';
            foreach($metadata as $meta_name=>$meta_value) {
                if (!empty($meta_name) && is_string($meta_name)) {
                    if (in_array($meta_name, $this->special_metadata)) {
                        MarkdownExtended::setConfig($meta_name, $meta_value);
                    } else {
                        if ($meta_name=='title') {
                            MarkdownExtended::getContent()
                                ->setTitle($meta_value);
                        } else {
                            $metadata_str .= "\n" . MarkdownExtended::get('OutputFormatBag')
                                ->buildTag('meta_data', null, array(
                                    'name'=>$meta_name,
                                    'content'=>$meta_value
                                ));
                        }
                    }
                }
            }
            MarkdownExtended::getContent()
                ->setMetadataToString($metadata_str);
        }
        return $text;
    }

}

// Endfile