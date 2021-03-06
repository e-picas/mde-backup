<?php
/*
 * This file is part of the PHP-MarkdownExtended package.
 *
 * (c) Pierre Cassat <me@e-piwi.fr> and contributors
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MarkdownExtended\Grammar\Filter;

use \MarkdownExtended\Grammar\Filter;
use \MarkdownExtended\Util\Helper;
use \MarkdownExtended\API\Kernel;

/**
 * Process the inclusion of third-party Markdown files
 *
 * Search any tag in the content written using the `block_inclusion_mask` config entry mask
 * and replace it by the parsing result if its content.
 *
 * The default inclusion mask is "<!-- @file_name.md@ -->"
 *
 * @package MarkdownExtended\Grammar\Filter
 */
class BlockInclusion
    extends Filter
{

    /**
     * Find defined inclusion blocks
     *
     * @param   string  $text
     * @return  string
     */
    public function transform($text)
    {
        $mask = Kernel::getConfig('block_inclusion_mask');
        if (!empty($mask)) {
            $regex = Helper::buildRegex($mask);
            $text = preg_replace_callback($regex, array($this, '_callback'), $text);
        }
        return $text;
    }

    /**
     * Process each inclusion, errors are written as comments
     *
     * @param   array   $matches    One set of results form the `transform()` function
     * @return  string              The result of the inclusion parsed if so
     */
    protected function _callback($matches)
    {
        $filename = $matches[1];
        if (!file_exists($filename)) {
            $base_path = Kernel::getConfig('base_path');
            if (!is_array($base_path)) {
                $base_path = array($base_path);
            }
            foreach ($base_path as $path) {
                $file = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;
                if (file_exists($file)) {
                    $filename = $file;
                    break;
                }
            }
        }

        $content_collection = Kernel::get('ContentCollection');
        $index = $content_collection->key();
        try {
            $parsed_content = Kernel::get('MarkdownExtended')
                ->transformSource($filename, false);
        } catch (\Exception $e) {
            $parsed_content = Kernel::get('OutputFormatBag')
                ->buildTag('comment', "ERROR while parsing $filename : '{$e->getMessage()}'");
        }
        Kernel::get('ContentCollection')->seek($index);

        return $parsed_content;
    }

}
