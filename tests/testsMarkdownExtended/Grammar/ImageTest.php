<?php
/*
 * This file is part of the PHP-MarkdownExtended package.
 *
 * (c) Pierre Cassat <me@e-piwi.fr> and contributors
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace testsMarkdownExtended\Grammar;

use \testsMarkdownExtended\MarkdownExtendedBaseTest;

class ImageTest extends MarkdownExtendedBaseTest
{

    public function testCreate()
    {
        $this->processParseTest(
            "
This is a definition with two paragraphs. Lorem ipsum
dolor sit amet, consectetuer adipiscing elit. Aliquam
hendrerit mi posuere lectus.
![Alt text](http://upload.wikimedia.org/wikipedia/commons/7/70/Example.png 'Optional image title')

Vestibulum enim wisi, viverra nec, fringilla in, laoreet
vitae, risus.
        ",
            '<p>This is a definition with two paragraphs. Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aliquam hendrerit mi posuere lectus. <img alt="Alt text" src="http://upload.wikimedia.org/wikipedia/commons/7/70/Example.png" title="Optional image title" /></p><p>Vestibulum enim wisi, viverra nec, fringilla in, laoreet vitae, risus.</p>',
            'Image fails!',
            true,
            true
        );

    }
    
}
